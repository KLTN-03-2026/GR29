# Hệ Thống Điều Phối Cứu Hộ Thông Minh Trong Thiên Tai

> **GR29 - Smart Rescue Dispatch System for Natural Disasters**

![Laravel](https://img.shields.io/badge/Laravel-10-red)
![Vue.js](https://img.shields.io/badge/Vue.js-3-green)
![MySQL](https://img.shields.io/badge/MySQL-8-blue)
![PHP](https://img.shields.io/badge/PHP-8.1-purple)

## Tổng Quan

Hệ thống **SOS Rescue Dispatch** giúp điều phối và quản lý hoạt động cứu hộ thiên tai một cách hiệu quả, kết nối người dân với đội cứu hộ thông qua AI phân loại ưu tiên và bản đồ nhiệt real-time.

## Tính Năng Chính

### Người Dân
- Đăng ký / Đăng nhập tài khoản
- Gửi yêu cầu cứu hộ kèm GPS, hình ảnh
- Theo dõi trạng thái yêu cầu
- Đánh giá sau khi được cứu hộ

### Admin / Điều Phối Viên
- Dashboard tổng quan với KPI real-time
- Xem & quản lý hàng đợi ưu tiên
- Phân công đội cứu hộ
- Theo dõi bản đồ nhiệt khu vực nguy hiểm
- Cấu hình trọng số AI scoring
- Báo cáo thống kê

### Đội Cứu Hộ
- Nhận thông báo nhiệm vụ
- Cập nhật vị trí GPS real-time
- Cập nhật tiến độ xử lý
- Nộp báo cáo kết quả + ảnh minh chứng

### AI Engine (Tự động)
- Phân loại mức độ khẩn cấp tự động
- Tính điểm ưu tiên (Priority Score)
- Cập nhật hàng đợi định kỳ
- Cập nhật cache heatmap

## Công Nghệ

| Thành phần | Công nghệ |
|------------|-----------|
| Backend | Laravel 10 + Sanctum Auth |
| Frontend | Vue.js 3 + Vite + Pinia |
| Database | MySQL 8 |
| Map | Leaflet + OpenStreetMap |

## Cấu Trúc Database (21 Bảng)

| Nhóm | Bảng |
|------|------|
| Phân quyền | `chuc_nang`, `chuc_vu`, `phan_quyen`, `admin` |
| Người dùng | `nguoi_dung`, `thanh_vien_doi` |
| Sự cố | `loai_su_co`, `chi_tiet_loai_su_co`, `yeu_cau_cuu_ho` |
| AI | `phan_loai_ais`, `trong_so_phan_loai`, `hang_doi_xu_ly` |
| Đội cứu hộ | `doi_cuu_ho`, `doi_cuu_ho_loai_su_co`, `nang_luc_doi`, `tai_nguyen_cuu_ho`, `vi_tri_doi_cuu_ho` |
| Kết quả | `phan_cong_cuu_ho`, `ket_qua_cuu_ho`, `danh_gia_cuu_ho`, `du_lieu_heatmap` |

## Cài Đặt

### Yêu Cầu
- PHP 8.1+
- Node.js 18+
- MySQL 8.0+
- Composer

### Backend (BE_SOS)

```bash
cd BE_SOS

# Cài đặt dependencies
composer install

# Copy và cấu hình .env
cp .env.example .env
php artisan key:generate

# Tạo database và chạy migration
php artisan migrate

# Seed dữ liệu mẫu (tùy chọn)
php artisan db:seed

# Khởi chạy server
php artisan serve
```

### Frontend (FE_SOS)

```bash
cd FE_SOS

# Cài đặt dependencies
npm install

# Chạy development server
npm run dev
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Đăng ký
- `POST /api/auth/login` - Đăng nhập
- `POST /api/auth/logout` - Đăng xuất

### Yêu Cầu Cứu Hộ
- `GET /api/yeu-cau` - Danh sách yêu cầu
- `POST /api/yeu-cau` - Tạo yêu cầu mới
- `GET /api/yeu-cau/{id}` - Chi tiết yêu cầu

### Admin
- `GET /api/admin/dashboard` - Dashboard stats
- `GET /api/admin/hang-doi` - Hàng đợi ưu tiên
- `POST /api/admin/phan-cong` - Phân công đội
- `GET /api/admin/heatmap` - Dữ liệu bản đồ nhiệt

### Đội Cứu Hộ
- `GET /api/doi/nhiem-vu` - Nhiệm vụ của đội
- `PUT /api/doi/vi-tri` - Cập nhật GPS
- `PUT /api/doi/tien-do` - Cập nhật tiến độ
- `POST /api/doi/ket-qua` - Nộp kết quả

## Trạng Thái Hệ Thống

```
Yêu cầu cứu hộ:
CHO_XU_LY → DANG_XU_LY → HOAN_THANH
              ↓
           HUY_BO

Phân công:
MOI → DANG_XU_LY → HOAN_THANH
  ↓         ↓
HUY       HUY

Đội cứu hộ:
SAN_SANG ↔ DANG_XU_LY → KHONG_KHA_DUNG

Hàng đợi:
WAITING → ASSIGNED → PROCESSING → DONE
```

## License

MIT License
