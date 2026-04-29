# Chức Năng: Tìm Đội Cứu Hộ Gần Nhất (timDoiGanNhat)

## Mô Tả Chung
Khi admin chọn một yêu cầu cứu hộ trong trang `/admin/assignments`, hệ thống sẽ:
1. Tự động tìm và tính khoảng cách từ vị trí yêu cầu tới tất cả các đội cứu hộ
2. Sắp xếp các đội theo ưu tiên: **cùng loại sự cố + cùng quận + gần nhất (km)**
3. Hiển thị badge km với định dạng "X.XX km" cho mỗi đội
4. Thêm indicator ưu tiên (#1, #2, #3...) để giúp admin chọn đội nhanh chóng

---

## Thành Phần Kỹ Thuật

### Backend - Laravel
**File:** `app/Http/Controllers/YeuCauCuuHoController.php`
**Function:** `timDoiGanNhat(Request $request)`

**Chức năng:**
- Nhận: `id_yeu_cau`, `id_loai_su_co` (optional)
- Trích xuất tọa độ (lat, lng) của vị trí yêu cầu
- Tính khoảng cách từ yêu cầu tới từng đội using Google Distance Matrix API (DistanceService)
- Sắp xếp ưu tiên:
  1. **Khoảng cách hợp lệ** (có GPS) > không có GPS
  2. **Cùng loại sự cố** (true) > chưa rõ (null) > khác loại (false)
  3. **Cùng quận** + điểm bonus
  4. **Gần nhất** (km thấp nhất)
- Trả về: Danh sách đội với `khoang_cach_km`, `cung_loai_su_co`, `cung_quan`, `khoang_cach_display`

**Route:** 
```
POST /api/yeu-cau-cuu-ho/tim-doi-gan-nhat
```

### Frontend - Vue 3
**File:** `src/components/Admin/Assignments/index.vue`

**API Call:**
```javascript
const res = await rescueRequestAPI.findNearestTeams({
  id_yeu_cau: req.id,
  id_loai_su_co: req.idLoaiSuCo || null
});
```

**Data Flow:**
1. User chọn yêu cầu → trigger `selectRequest(req)`
2. Watcher `selectedReq` → gọi `fetchNearestTeams(newReq)`
3. API trả về danh sách → update `teams[].khoang_cach_km`
4. Computed property `sortedAvailableTeams` sắp xếp lại danh sách
5. Template hiển thị với priority badge + distance badge

**Methods Mới:**
- `formatDistance(km)`: Format khoảng cách (e.g., "5.23 km")
- `getPriorityBadgeClass(index)`: Xác định màu priority badge

**Computed Properties:**
- `sortedAvailableTeams`: Sắp xếp theo type + quận + distance

---

## UI Enhancements

### 1. Distance Badge (Badge Khoảng Cách)
```vue
<span class="distance-badge">
  <i class="fa-solid fa-location-arrow me-1"></i>{{ formatDistance(team.khoang_cach_km) }}
</span>
```

**Styling:**
- Gradient xanh dương (0ea5e9 → 0284c7)
- Bóng mềm với hiệu ứng fade-in
- Font weight: 700
- Padding: 4px 10px

### 2. Priority Badge (Chỉ Báo Ưu Tiên)
```vue
<div class="priority-badge" :class="getPriorityBadgeClass(index)">
  #{{ index + 1 }}
</div>
```

**Classes & Colors:**
- `priority-1st`: Đỏ (#dc2626) + animation pulse
- `priority-2nd`: Cam (#f59e0b)
- `priority-3rd`: Xanh lá (#10b981)
- `priority-default`: Tím (#6366f1)

---

## Quy Trình Sắp Xếp Chi Tiết

### Algorithm (Backend)
```
1. Đội có khoảng cách hợp lệ lên trước
2. Nếu cách nhau ≤ 1km, xem xét type matching:
   - Cùng loại (true): +2 điểm
   - Chưa rõ (null): +1 điểm
   - Khác loại (false): 0 điểm
   - Cùng quận: +1 điểm thêm
3. Nếu điểm bằng nhau: sắp xếp theo khoảng cách (gần nhất lên trước)
4. Đội không có khoảng cách đẩy xuống cuối
```

### Frontend Sorting
```javascript
sortedAvailableTeams() {
  return [...this.availableTeams].sort((a, b) => {
    // Type score
    const aTypeScore = a.cung_loai_su_co === true ? 2 : 
                       (a.cung_loai_su_co === false ? 0 : 1);
    const bTypeScore = b.cung_loai_su_co === true ? 2 : 
                       (b.cung_loai_su_co === false ? 0 : 1);
    const aScore = aTypeScore + (a.cung_quan ? 1 : 0);
    const bScore = bTypeScore + (b.cung_quan ? 1 : 0);
    
    if (aScore !== bScore) return bScore - aScore;
    
    // Distance
    const aDist = a.khoang_cach_km ?? Infinity;
    const bDist = b.khoang_cach_km ?? Infinity;
    return aDist - bDist;
  });
}
```

---

## Ví Dụ Thực Tế

### Scenario 1: 4 Đội - Sắp Xếp Ưu Tiên
```
Request Location: Q.1, 10.7500, 106.6700
Incident Type: Fire/Cháy

Kết Quả:
1. 🔴 Team A: Cùng loại + Cùng quận + 2.3 km → Ưu tiên #1
2. 🟠 Team B: Cùng loại + Khác quận + 3.5 km → Ưu tiên #2
3. 🟢 Team C: Khác loại + Cùng quận + 1.8 km → Ưu tiên #3
4. 🟣 Team D: Khác loại + Khác quận + 5.2 km → Ưu tiên #4
```

### Scenario 2: Cập Nhật Khi Thay Đổi Yêu Cầu
```
1. Admin chọn Request #5 → fetchNearestTeams(req5)
2. API tính toán & trả về danh sách mới
3. Teams được cập nhật khoang_cach_km
4. UI re-render với priority mới
5. Admin nhìn thấy badge km + priority badge cập nhật
```

---

## API Response Format

```json
{
  "teams": [
    {
      "id": 1,
      "ten_doi": "Đội Cứu Hộ A",
      "khu_vuc_quan_ly": "Quận 1",
      "so_dien_thoai_hotline": "0123456789",
      "trang_thai": "SAN_SANG",
      "thanh_viens": [...],
      "phan_congs": [...],
      "loai_su_co": ["Fire", "Rescue"],
      "cung_loai_su_co": true,
      "cung_quan": true,
      "khoang_cach_km": 2.45,
      "khoang_cach_display": "2.45 km",
      "active_count": 1,
      "capacity": 9,
      "trang_thai_theo_nang_luc": "available"
    },
    ...
  ]
}
```

---

## Troubleshooting

### Badge km không hiển thị?
- Kiểm tra: `team.khoang_cach_km` có dữ liệu không
- Kiểm tra GPS của team & request (vi_tri_lat, vi_tri_lng)
- Kiểm tra Google Distance Matrix API credentials

### Sắp xếp không đúng?
- Xác nhận backend trả về `cung_loai_su_co` & `cung_quan` đúng
- Xác nhận `khoang_cach_km` là number/float, không phải string
- Debug: In `sortedAvailableTeams` để xem thứ tự

### Priority badge màu sai?
- Kiểm tra class trong `getPriorityBadgeClass(index)`
- Xác nhận CSS có áp dụng đúng (`.priority-1st`, `.priority-2nd`, etc.)

---

## Performance Considerations

- ✅ **Một lần tính toán**: Backend gọi Google Distance Matrix API một lần cho tất cả team
- ✅ **Caching ưu tiên**: Frontend lưu cache khoang_cach_km khi đã tính
- ✅ **Re-fetch khi cần**: Chỉ gọi API khi người dùng chọn yêu cầu khác
- ⚠️ **API limit**: Cần giám sát Google Distance Matrix API quota

---

## Files Modified

1. **Frontend:**
   - `src/components/Admin/Assignments/index.vue`
     - Method: `formatDistance()`
     - Method: `getPriorityBadgeClass()`
     - Template: Priority badge
     - CSS: `.distance-badge`, `.priority-badge`

2. **Backend:**
   - `app/Http/Controllers/YeuCauCuuHoController.php`
     - Function: `timDoiGanNhat()` (đã triển khai)

3. **Routes:**
   - `routes/api.php`
     - `Route::post('yeu-cau-cuu-ho/tim-doi-gan-nhat', [...])` (đã định nghĩa)

4. **API Service:**
   - `src/services/api.js`
     - `findNearestTeams()` (đã định nghĩa)

---

## Testing Checklist

- [ ] Admin đăng nhập vào `/admin/assignments`
- [ ] Chọn một yêu cầu (request) từ hàng đợi
- [ ] Kiểm tra badge km hiển thị đúng format (X.XX km)
- [ ] Kiểm tra priority badge (#1, #2, #3...)
- [ ] Kiểm tra màu sắc priority badge (đỏ #1, cam #2, xanh #3, tím default)
- [ ] Kiểm tra sắp xếp theo ưu tiên:
  - Top #1: Cùng loại + cùng quận + gần nhất
  - Các #2, #3... phải giảm dần theo km
- [ ] Chọn yêu cầu khác → priority badge & badge km cập nhật
- [ ] Đội bận (overload) vẫn hiển thị nhưng có indicator
- [ ] Hovers & animations hoạt động mượt mà

---

## Notes

- Chức năng này giúp admin chọn đội cứu hộ phù hợp nhanh chóng
- Priority badge là visual indicator để giảm cognitive load
- Distance badge cho phép admin kiểm soát chất lượng phân công
- Backend logic tập trung vào accuracy, frontend tập trung vào UX
