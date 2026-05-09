# Auto Dispatch System (Hệ Thống Điều Phối Tự Động)

## Mục lục

- [Tổng quan](#tổng-quan)
- [Kiến trúc hệ thống](#kiến-trúc-hệ-thống)
- [Quy trình xử lý](#quy-trình-xử-lý)
- [Tính điểm (Scoring)](#tính-điểm-scoring)
- [API Endpoints](#api-endpoints)
- [Cấu hình](#cấu-hình)
- [Realtime Broadcasting](#realtime-broadcasting)
- [Retry Logic](#retry-logic)
- [Hướng dẫn triển khai](#hướng-dẫn-triển-khai)

---

## Tổng quan

Hệ thống **AutoDispatch** tự động gán đội cứu hộ phù hợp nhất cho yêu cầu cứu hộ dựa trên thuật toán tính điểm ưu tiên. Hệ thống hoạt động theo thời gian thực (realtime) qua WebSocket và xử lý bất đồng bộ qua Queue.

### Các thành phần chính

| Thành phần | File | Mô tả |
|---|---|---|
| `AutoDispatchService` | `app/Services/AutoDispatchService.php` | Core business logic - tính điểm, chọn đội |
| `AutoDispatchJob` | `app/Jobs/AutoDispatchJob.php` | Queue job - xử lý async |
| `AutoDispatchController` | `app/Http/Controllers/AutoDispatchController.php` | REST API controller |
| `AssignTeamEvent` | `app/Events/AssignTeamEvent.php` | WebSocket broadcast event |

---

## Kiến trúc hệ thống

```
[Yêu cầu mới được tạo]
        │
        ▼
[YeuCauCuuHoController::store()]
        │
        ├──► Tạo bản ghi YeuCauCuuHo
        ├──► Tạo HangDoiXuLy
        ├──► Broadcast RescueRequestUpdated
        │
        └──► [AutoDispatchService::daBat()] ?
                │
                ├──► TRUE: Dispatch AutoDispatchJob (queue)
                │
                └──► FALSE: Bỏ qua

              ▼

    [AutoDispatchJob] ──► [AutoDispatchService::xuLyDieuPhoiTuDong()]
                                    │
                                    ├──► Lấy top 5 đội gần nhất (cache 10s)
                                    ├──► Tính điểm từng đội
                                    ├──► Chọn đội có điểm cao nhất
                                    ├──► DB::transaction → gán đội
                                    │
                                    └──► [thanh_cong] ?
                                            │
                                            ├──► TRUE: Broadcast AssignTeamEvent
                                            │          (→ RescueRequestUpdated → FE)
                                            │
                                            └──► FALSE: Retry sau 30 phút (max 3 lần)
                                                       └──► Hết retry → Cảnh báo Admin
```

---

## Quy trình xử lý

### 1. Tạo yêu cầu cứu hộ

Khi `YeuCauCuuHoController::store()` được gọi:

1. Tạo bản ghi `YeuCauCuuHo`
2. Tạo bản ghi `HangDoiXuLy` trong hàng đợi xử lý
3. Broadcast `RescueRequestUpdated` đến channel `rescue-requests`
4. **Nếu `AutoDispatch` đang bật** → dispatch `AutoDispatchJob` vào Redis queue

### 2. Xử lý AutoDispatchJob

1. Kiểm tra điều phối tự động có bật không
2. Kiểm tra yêu cầu còn tồn tại và chưa được gán
3. Gọi `AutoDispatchService::xuLyDieuPhoiTuDong()`
4. Nếu thành công → broadcast `AssignTeamEvent`
5. Nếu thất bại → retry sau 30 phút (tối đa 3 lần)
6. Nếu hết retry → cảnh báo admin

### 3. Điều phối trong Service

1. **Lấy top 5 đội gần nhất** (cache 10 giây)
2. **Tính điểm** cho từng đội
3. **Chọn đội tốt nhất** (điểm cao nhất, chưa quá tải)
4. **Gán đội** với DB transaction (tránh double assignment)

---

## Tính điểm (Scoring)

### Công thức tổng

```
Điểm_Tổng = Điểm_NguyHiểm + Điểm_KhoảngCách + Điểm_Tải + Điểm_ThờiGian + Điểm_LoạiSựCố
```

### 1. Điểm Nguy Hiểm (Danger Score)

Lấy **MAX** giá trị `diem_uu_tien` từ bảng `chi_tiet_loai_su_co` dựa trên loại sự cố của yêu cầu.

```php
private function tinhDiemNguyHiemInternal(YeuCauCuuHo $yeuCau): float
{
    // Ưu tiên từ chi tiết của yêu cầu
    if ($yeuCau->chiTiet && $yeuCau->chiTiet->isNotEmpty()) {
        return (float) $yeuCau->chiTiet->max('diem_uu_tien');
    }

    // Fallback: truy vấn DB dựa trên loại sự cố
    $maxDiem = DB::table('chi_tiet_loai_su_co')
        ->where('id_loai_su_co', $yeuCau->id_loai_su_co)
        ->max('diem_uu_tien');

    return is_numeric($maxDiem) ? (float) $maxDiem : 0.0;
}
```

**Bảng điểm nguy hiểm (từ seeder):**

| Chi tiết | Điểm ưu tiên |
|---|---|
| Cứu nạn - mắc kẹt | 10 |
| Hỗ trợ y tế | 9 |
| Hỗ trợ di dời | 8 |
| Hỗ trợ người yếu thế | 8 |
| Cung cấp nước sạch | 7 |
| Cung cấp lương thực | 6 |
| Hỗ trợ sinh hoạt | 5 |
| Khắc phục giao thông | 4 |
| Khắc phục công trình | 4 |
| Khắc phục nhà cửa | 3 |

### 2. Điểm Khoảng Cách (Distance Weight)

Dựa trên khoảng cách km từ Google Distance Matrix API (hoặc Haversine fallback).
**Được tăng điểm để ưu tiên khoảng cách hơn.**

```php
private function tinhDiemKhoangCachInternal(DoiCuuHo $doi): int
{
    $km = $doi->distance ?? 0;

    if ($km <= 1) return 10;   // Rất gần
    if ($km <= 3) return 7;    // Gần
    if ($km <= 5) return 4;    // Trung bình
    return 1;                   // Xa nhưng vẫn có điểm
}
```

| Khoảng cách | Điểm |
|---|---|
| <= 1 km | 10 |
| <= 3 km | 7 |
| <= 5 km | 4 |
| > 5 km | 1 |

### 3. Điểm Loại Sự Cố (Incident Type Match)

Ưu tiên đội chuyên xử lý đúng loại sự cố.

```php
private function tinhDiemLoaiSuCoInternal(YeuCauCuuHo $yeuCau, DoiCuuHo $doi): int
{
    return in_array((int) $yeuCau->id_loai_su_co, $doi->loaiSuCoIds, true) ? 6 : 0;
}
```

| Khớp loại sự cố | Điểm |
|---|---|
| Có | 6 |
| Không | 0 |

### 4. Điểm Tải (Load Weight)

Tính dựa trên capacity của đội: `sucChua = soThanhVien * 4`

```php
private function tinhDiemTaiInternal(DoiCuuHo $doi): int
{
    $soThanhVien = $doi->thanhViens ? $doi->thanhViens->count() : 0;
    $sucChua = $soThanhVien * 4;
    $tai = $doi->so_nhiem_vu_dang_xu_ly ?? 0;

    // Quá tải → loại bỏ hoàn toàn
    if ($tai >= $sucChua && $sucChua > 0) {
        return -100;
    }

    if ($sucChua === 0) return -100; // Không thành viên

    $tyLe = $tai / $sucChua;

    if ($tyLe <= 0.25) return 2;  // Dưới 25% tải
    if ($tyLe <= 0.5)  return 1;   // Dưới 50% tải
    return 0;                       // Trên 50% tải
}
```

| Tỷ lệ tải | Điểm |
|---|---|
| >= 100% (quá tải) | **-100 (loại)** |
| <= 25% | 2 |
| 26% - 50% | 1 |
| 51% - 99% | 0 |

### 4. Điểm Thời Gian (Anti-Starvation)

Ưu tiên yêu cầu đang chờ lâu.

```php
private function tinhDiemThoiGianInternal(YeuCauCuuHo $yeuCau): float
{
    $phut = now()->diffInMinutes($yeuCau->created_at);
    return min($phut * 0.2, 3); // Tối đa 3 điểm
}
```

| Thời gian chờ | Điểm |
|---|---|
| 0 phút | 0 |
| 5 phút | 1.0 |
| 10 phút | 2.0 |
| 15+ phút | 3.0 (tối đa) |

---

## API Endpoints

Tất cả endpoint đều yêu cầu **Admin authentication**.

### Lấy trạng thái

```
GET /api/auto-dispatch/status
```

Response:
```json
{
  "thanh_cong": true,
  "du_lieu": {
    "dieu_phoi_tu_dong": true,
    "thong_diep": "Điều phối tự động đang BẬT"
  }
}
```

### Toggle ON/OFF

```
POST /api/auto-dispatch/toggle
```

### Bật điều phối tự động

```
POST /api/auto-dispatch/enable
```

### Tắt điều phối tự động

```
POST /api/auto-dispatch/disable
```

### Trigger điều phối thủ công (async - qua queue)

```
POST /api/auto-dispatch/dispatch/{id}
```

### Trigger điều phối thủ công (sync - xem kết quả ngay)

```
POST /api/auto-dispatch/dispatch-sync/{id}
```

Response:
```json
{
  "thanh_cong": true,
  "thong_diep": "Đã gán đội Đội Cứu Hộ A cho yêu cầu #5",
  "du_lieu": {
    "doi_id": 3,
    "phan_cong_id": 12,
    "diem_tong": 14.5
  }
}
```

### Danh sách yêu cầu cần can thiệp admin

```
GET /api/auto-dispatch/admin-escalations
```

### Xóa cảnh báo can thiệp

```
DELETE /api/auto-dispatch/admin-escalations/{id}
```

### Debug - Xem điểm chấm

```
GET /api/auto-dispatch/debug/{id}
```

Response:
```json
{
  "thanh_cong": true,
  "yeu_cau": {
    "id": 5,
    "trang_thai": "CHO_XU_LY",
    "vi_tri": { "lat": 10.76, "lng": 106.70, "dia_chi": "123 Nguyễn Trãi" },
    "thoi_gian_cho_phut": 8
  },
  "diem_nguy_hiem_toi_da": 10,
  "diem_thoi_gian": 1.6,
  "danh_sach_doi": [
    {
      "id": 3,
      "ten_doi": "Đội Cứu Hộ A",
      "khoang_cach_km": 1.2,
      "so_thanh_vien": 5,
      "suc_chua": 20,
      "so_nhiem_vu_hien_tai": 2,
      "diem_nguy_hiem": 10,
      "diem_khoang_cach": 3,
      "diem_tai": 2,
      "diem_thoi_gian": 1.6,
      "diem_tong": 16.6,
      "da_loai_bo": false
    }
  ]
}
```

### Cập nhật cấu hình

```
PUT /api/auto-dispatch/config
```

Body:
```json
{
  "so_doi_toi_da": 5,
  "thoi_gian_retry_phut": 30,
  "so_lan_retry_toi_da": 3
}
```

---

## Cấu hình

### Environment Variables

```env
# Queue driver - Nên dùng Redis cho production
QUEUE_CONNECTION=redis

# Redis (nếu dùng Redis queue)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Google Maps API Key (cho Distance Matrix)
GOOGLE_MAPS_API_KEY=your_api_key_here
```

### Queue Configuration

```php
// config/queue.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => env('REDIS_QUEUE', 'auto-dispatch'),
    'retry_after' => 90,
    'block_for' => null,
],
```

### Chạy Queue Worker

```bash
# Development - chạy trực tiếp
php artisan queue:work

# Production - dùng Supervisor
php artisan queue:work redis --queue=auto-dispatch --tries=5 --timeout=60
```

### Cấu hình Supervisor (Linux)

```ini
[program:autodispatch-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --queue=auto-dispatch --tries=5 --timeout=60
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/autodispatch-worker.log
```

---

## Realtime Broadcasting

### Channels

| Channel | Ai nghe | Mô tả |
|---|---|---|
| `rescue-requests` | Tất cả admin | Tất cả thay đổi yêu cầu |
| `rescue-requests.{userId}` | Người dùng gửi yêu cầu | Thông báo được gán đội |
| `rescue-requests.{requestId}` | Admin xem chi tiết | Cập nhật trạng thái |
| `team.{teamId}` | Đội cứu hộ được gán | Thông báo có nhiệm vụ mới |

### Event Payload

```json
{
  "action": "auto_dispatched",
  "id_yeu_cau": 5,
  "id_doi_cuu_ho": 3,
  "ten_doi": "Đội Cứu Hộ A",
  "sdt_hotline": "0123456789",
  "id_phan_cong": 12,
  "phan_cong_tu_dong": true,
  "vi_tri_dia_chi": "123 Nguyễn Trãi, Q1",
  "vi_tri_lat": 10.7629,
  "vi_tri_lng": 106.7080,
  "muc_do_khan_cap": "HIGH",
  "phan_congs": [...],
  "message": "Hệ thống đã tự động phân công đội Đội Cứu Hộ A đến xử lý yêu cầu #5"
}
```

---

## Retry Logic

```
Yêu cầu mới tạo
        │
        ▼
   Lần thử 1 ──► Thành công ✓
        │
        ▼ (thất bại)
   Chờ 30 phút
        │
        ▼
   Lần thử 2 ──► Thành công ✓
        │
        ▼ (thất bại)
   Chờ 30 phút
        │
        ▼
   Lần thử 3 ──► Thành công ✓
        │
        ▼ (thất bại)
   ⚠️ CAN THIỆP ADMIN
```

### Điều kiện retry

- Team không tiếp nhận nhiệm vụ sau 30 phút
- Không có đội nào trong khu vực

### Cảnh báo Admin

Khi vượt quá số lần retry, hệ thống:
1. Lưu thông tin vào cache với key `admin_escalation_{id}`
2. Log cảnh báo với mức `ERROR`
3. Hiển thị trong danh sách `admin-escalations`

---

## Concurrency Control

### Double Assignment Prevention

Sử dụng **DB Transaction** để đảm bảo atomicity:

```php
return DB::transaction(function () use ($yeuCau, $doi, $diemTong) {
    $yeuCau->refresh();
    $doi->refresh('phanCongs');

    // Kiểm tra lại lần cuối
    if ($yeuCau->phanCongs()->exists()) {
        return ['thanh_cong' => false, ...];
    }

    // Tạo phân công
    $phanCong = PhanCongCuuHo::create([...]);
    $yeuCau->update(['trang_thai' => 'DA_PHAN_CONG']);
    $doi->update(['trang_thai' => 'BAN_CHI_DINH']);

    return ['thanh_cong' => true, ...];
});
```

### Dispatch Debounce

Dùng **Cache lock** để ngăn xử lý trùng lặp:

```php
$dispatchLockKey = "dispatch_lock_{$idYeuCau}";
if (Cache::has($dispatchLockKey)) {
    return ['thanh_cong' => false, ...];
}
Cache::put($dispatchLockKey, true, 5); // Lock 5 giây
```

---

## Performance Optimization

| Kỹ thuật | Cài đặt | Mô tả |
|---|---|---|
| Eager Loading | `->with(['chiTiet', 'phanCongs'])` | Tránh N+1 query |
| Distance Matrix Cache | 10 giây | Giảm API calls đến Google |
| Dispatch Lock | 5 giây | Ngăn duplicate processing |
| Giới hạn đội đánh giá | Top 5 | Giảm computation |
| Queue async | Redis | Non-blocking processing |

---

## Hướng dẫn triển khai

### Bước 1: Cài đặt Dependencies

```bash
# Cài đặt Laravel Reverb (WebSocket server)
composer require laravel/reverb

# Cài đặt predis cho Redis
composer require predis/predis
```

### Bước 2: Cấu hình `.env`

```env
BROADCAST_DRIVER=reverb
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

GOOGLE_MAPS_API_KEY=your_key_here
```

### Bước 3: Chạy Migration

```bash
php artisan migrate
```

### Bước 4: Chạy Seeder

```bash
# Đảm bảo ChiTietLoaiSuCo có điểm ưu tiên
php artisan db:seed --class=ChiTietLoaiSuCoSeeder
```

### Bước 5: Khởi động Services

```bash
# Terminal 1: Queue worker
php artisan queue:work redis --queue=auto-dispatch --tries=5

# Terminal 2: Reverb WebSocket server
php artisan reverb:start

# Terminal 3: Laravel dev server
php artisan serve
```

### Bước 6: Kiểm tra

1. Bật điều phối tự động: `POST /api/auto-dispatch/enable`
2. Tạo yêu cầu cứu hộ mới: `POST /api/yeu-cau-cuu-ho`
3. Kiểm tra điểm chấm: `GET /api/auto-dispatch/debug/{id}`
4. Kiểm tra WebSocket trên frontend (channel `rescue-requests`)

---

## Mở rộng trong tương lai

- **Thêm loại điểm**: Thời tiết, loại phương tiện đội
- **Cân bằng tải**: Phân bổ đều yêu cầu giữa các đội
- **Machine Learning**: Dự đoán thời gian phản hồi tối ưu
- **Multi-dispatch**: Gán nhiều đội cho 1 yêu cầu lớn
- **Geofencing**: Giới hạn bán kính tìm đội
