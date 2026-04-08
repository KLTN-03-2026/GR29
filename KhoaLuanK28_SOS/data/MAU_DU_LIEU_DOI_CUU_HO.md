# Dữ liệu mẫu — Đội cứu hộ (phục vụ báo cáo / demo)

Tài liệu mô tả **bộ dữ liệu minh họa** cho các bảng: `doi_cuu_ho`, `thanh_vien_doi`, `tai_nguyen_cuu_ho`, `nang_luc_doi`, `doi_cuu_ho_loai_su_co`, `vi_tri_doi_cuu_ho`.  
**Lưu ý:** `id_doi_cuu_ho`, `id_loai_su_co` trong DB là auto-increment; khi import, map theo thứ tự seed `LoaiSuCo` (1=Lũ lụt … 6=Hạn hán) hoặc tra cứu `slug_danh_muc`.

**Khu vực tham chiếu:** Đà Nẵng (tọa độ minh họa quanh trung tâm thành phố).

---

## 1. Bảng `doi_cuu_ho` — Danh sách đội

| id* | ten_co | khu_vuc_quan_ly | so_dien_thoai_hotline | vi_tri_lat | vi_tri_lng | trang_thai | mo_ta |
|-----|--------|-----------------|------------------------|------------|------------|------------|--------|
| 1 | Đội Cứu Hộ Lũ Lụt Hải Châu | Hải Châu | 0236-3847-001 | 16.0600 | 108.2200 | SAN_SANG | Thuyền cứu hộ, bơm chìm, ứng phó ngập sâu đô thị |
| 2 | Đội Cứu Nạn Sạt Lở Liên Chiểu | Liên Chiểu | 0236-3847-002 | 16.0900 | 108.1500 | SAN_SANG | Leo núi, neo đất, cảnh báo khu vực trũng |
| 3 | Đội Sơ Cứu Y Tế Cấp Cứu Thanh Khê | Thanh Khê | 0236-3847-003 | 16.0700 | 108.2000 | SAN_SANG | Xe cứu thương, túi CPR, băng cá nhân, oxygen |
| 4 | Đội Ứng Phó Bão & Gió Mạnh Sơn Trà | Sơn Trà | 0236-3847-004 | 16.1000 | 108.2500 | DANG_NHIEM_VU | Cưa cây, mái che, lều cứu hộ, máy phát điện |
| 5 | Đội Hỗ Trợ Động Đất & Sóng Thần Ngũ Hành Sơn | Ngũ Hành Sơn | 0236-3847-005 | 16.0000 | 108.2600 | SAN_SANG | Tìm kiếm cứu nạn, di tản bờ biển, đèn pin đội |
| 6 | Đội Nước Sạch & Hạn Hán Cẩm Lệ | Cẩm Lệ | 0236-3847-006 | 16.0150 | 108.1850 | SAN_SANG | Xe bồn, lọc nước di động, tiếp nước cộng đồng |
| 7 | Đội Cứu Hộ Đa Năng Hoà Vang | Hoà Vang | 0236-3847-007 | 15.9950 | 108.1150 | SAN_SANG | Kết hợp lũ + sạt lở nông thôn, xe địa hình |
| 8 | Đội Dự Phòng Tổng Hợp Liên Ngành | Trung tâm Đà Nẵng | 0236-3847-000 | 16.0544 | 108.2022 | SAN_SANG | Hỗ trợ khi các đội khu vực quá tải; điều phối liên đội |

\*Cột `id` chỉ là thứ tự logic trong báo cáo; khi seed DB có thể khác.

---

## 2. Bảng `doi_cuu_ho_loai_su_co` — Đội xử lý loại sự cố nào

Tham chiếu `id_loai_su_co` theo seed chuẩn dự án: **1** Lũ lụt, **2** Bão, **3** Sạt lở đất, **4** Động đất, **5** Sóng thần, **6** Hạn hán.

| id_doi_cuu_ho (logic) | id_loai_su_co | Ghi chú |
|----------------------|---------------|---------|
| 1 | 1 | Chuyên lũ |
| 2 | 3 | Chuyên sạt lở |
| 3 | 1, 2, 3, 4, 5, 6 | Y tế hỗ trợ mọi loại (đa năng) |
| 4 | 2 | Chuyên bão |
| 5 | 4, 5 | Động đất + sóng thần |
| 6 | 6 | Hạn hán |
| 7 | 1, 3 | Đa năng vùng núi ngoại ô |
| 8 | 1, 2, 3 | Dự phòng tổng hợp |

---

## 3. Bảng `nang_luc_doi` — Năng lực vận hành (một dòng / đội)

| id_doi_cuu_ho | so_viec_dang_xu_ly | so_viec_toi_da | ty_le_hoan_thanh | thoi_gian_xu_ly_tb (phút) |
|---------------|-------------------|----------------|------------------|---------------------------|
| 1 | 0 | 3 | 0.92 | 28 |
| 2 | 0 | 2 | 0.88 | 35 |
| 3 | 1 | 4 | 0.95 | 22 |
| 4 | 1 | 2 | 0.87 | 40 |
| 5 | 0 | 3 | 0.90 | 45 |
| 6 | 0 | 2 | 0.93 | 30 |
| 7 | 0 | 3 | 0.89 | 32 |
| 8 | 0 | 4 | 0.91 | 25 |

---

## 4. Bảng `thanh_vien_doi` — Thành viên (mẫu đủ vai trò)

Mật khẩu demo (nếu dùng trong hệ thống): `member123` (hash trong DB). `trang_thai`: 1 = hoạt động.

### Đội 1 — Hải Châu
| ho_ten | so_dien_thoai | email | vai_tro_trong_doi |
|--------|---------------|-------|-------------------|
| Nguyễn Văn Thông | 0903111001 | thong.nv@cuuho-haichau.vn | Đội trưởng |
| Trần Thị Mai | 0903111002 | mai.tt@cuuho-haichau.vn | Phó đội — điều phối thuyền |
| Lê Hoàng Nam | 0903111003 | nam.lh@cuuho-haichau.vn | Thuyền trưởng |
| Phạm Quốc Huy | 0903111004 | huy.pq@cuuho-haichau.vn | Kỹ thuật máy bơm |

### Đội 2 — Liên Chiểu
| ho_ten | so_dien_thoai | email | vai_tro_trong_doi |
|--------|---------------|-------|-------------------|
| Hoàng Đức Anh | 0903222001 | anh.hd@cuuho-lienchieu.vn | Đội trưởng |
| Võ Thị Lan | 0903222002 | lan.vt@cuuho-lienchieu.vn | An toàn hiện trường |
| Đinh Công Thành | 0903222003 | thanh.dc@cuuho-lienchieu.vn | Leo núi — cứu hộ |

### Đội 3 — Thanh Khê
| ho_ten | so_dien_thoai | email | vai_tro_trong_doi |
|--------|---------------|-------|-------------------|
| Bùi Thị Hồng | 0903333001 | hong.bt@cuuho-thanhkhe.vn | Điều dưỡng trưởng |
| Ngô Văn Kiệt | 0903333002 | kiet.nv@cuuho-thanhkhe.vn | Bác sĩ sơ cứu |
| Dương Minh Tuấn | 0903333003 | tuan.dm@cuuho-thanhkhe.vn | Tài xế xe cứu thương |

### Đội 4 — Sơn Trà
| ho_ten | so_dien_thoai | email | vai_tro_trong_doi |
|--------|---------------|-------|-------------------|
| Lý Quốc Việt | 0903444001 | viet.lq@cuuho-sontra.vn | Đội trưởng |
| Mai Thị Thu | 0903444002 | thu.mt@cuuho-sontra.vn | Hậu cần |
| Vũ Đình Long | 0903444003 | long.vd@cuuho-sontra.vn | Vận hành máy cưa |

### Đội 5 — Ngũ Hành Sơn
| ho_ten | so_dien_thoai | email | vai_tro_trong_doi |
|--------|---------------|-------|-------------------|
| Phan Hải Đăng | 0903555001 | dang.ph@cuuho-nhs.vn | Đội trưởng |
| Trương Ngọc Anh | 0903555002 | anh.tn@cuuho-nhs.vn | Tìm kiếm USAR |
| Lâm Tiến Dũng | 0903555003 | dung.lt@cuuho-nhs.vn | Liên lạc — GPS |

### Đội 6 — Cẩm Lệ
| ho_ten | so_dien_thoai | email | vai_tro_trong_doi |
|--------|---------------|-------|-------------------|
| Đặng Thu Hà | 0903666001 | ha.dt@cuuho-camle.vn | Đội trưởng |
| Châu Bảo Khang | 0903666002 | khang.cb@cuuho-camle.vn | Kỹ thuật lọc nước |
| Huỳnh Gia Bảo | 0903666003 | bao.hg@cuuho-camle.vn | Tài xế xe bồn |

### Đội 7 — Hoà Vang
| ho_ten | so_dien_thoai | email | vai_tro_trong_doi |
|--------|---------------|-------|-------------------|
| Tôn Thất Hiếu | 0903777001 | hieu.tt@cuuho-hoavang.vn | Đội trưởng |
| Nguyễn Thị Yến | 0903777002 | yen.nt@cuuho-hoavang.vn | Điều phối hiện trường |
| Cao Văn Thịnh | 0903777003 | thinh.cv@cuuho-hoavang.vn | Lái xe địa hình |

### Đội 8 — Dự phòng
| ho_ten | so_dien_thoai | email | vai_tro_trong_doi |
|--------|---------------|-------|-------------------|
| Trịnh Quang Minh | 0903888001 | minh.tq@cuuho-duphong.vn | Trưởng ban chỉ huy dự phòng |
| Lê Thị Kim Oanh | 0903888002 | oanh.ltk@cuuho-duphong.vn | Liên lạc liên ngành |
| Đỗ Hữu Phước | 0903888003 | phuoc.dh@cuuho-duphong.vn | Kỹ thuật thiết bị chung |

---

## 5. Bảng `tai_nguyen_cuu_ho` — Tài nguyên theo đội

`loai_tai_nguyen` gợi ý: `Vehicle` | `Equipment` | `Medical` | `Communication` | `Water` | `Other`.  
`trang_thai`: 1 = sẵn sàng sử dụng.

| id_doi_cuu_ho | ten_tai_nguyen | loai_tai_nguyen | so_luong | trang_thai |
|---------------|----------------|-----------------|----------|------------|
| 1 | Thuyền cứu hộ nhôm | Equipment | 3 | 1 |
| 1 | Máy bơm chìm công suất lớn | Equipment | 2 | 1 |
| 1 | Xe bán tải chở thiết bị | Vehicle | 2 | 1 |
| 1 | Bộ đàm công suất | Communication | 6 | 1 |
| 2 | Dây neo — móng chống lở | Equipment | 10 | 1 |
| 2 | Mũ cứng — dây an toàn | Equipment | 12 | 1 |
| 2 | Xe chở đội leo núi | Vehicle | 1 | 1 |
| 3 | Xe cứu thương | Vehicle | 1 | 1 |
| 3 | Túi sơ cứu loại A | Medical | 8 | 1 |
| 3 | Bình oxygen di động | Medical | 2 | 1 |
| 4 | Máy cưa xích | Equipment | 4 | 1 |
| 4 | Lều cứu hộ 20 người | Equipment | 3 | 1 |
| 4 | Máy phát điện 5kVA | Equipment | 2 | 1 |
| 5 | Đèn pin đội đầu | Equipment | 15 | 1 |
| 5 | Dụng cụ phá dỡ nhẹ | Equipment | 2 | 1 |
| 5 | Loa cầm tay | Communication | 4 | 1 |
| 6 | Xe bồn 5m³ | Vehicle | 1 | 1 |
| 6 | Hệ thống lọc nước di động | Water | 2 | 1 |
| 6 | Bồn chứa nước gấp | Equipment | 4 | 1 |
| 7 | Xe địa hình | Vehicle | 2 | 1 |
| 7 | Thang cứu hộ | Equipment | 2 | 1 |
| 7 | Máy bơm dã chiến | Equipment | 2 | 1 |
| 8 | Container thiết bị dự phòng | Equipment | 1 | 1 |
| 8 | Xe chở đội 16 chỗ | Vehicle | 2 | 1 |
| 8 | Bộ đàm dự phòng | Communication | 10 | 1 |

---

## 6. Bảng `vi_tri_doi_cuu_ho` — Căn cứ / điểm neo GPS (mẫu)

Mỗi dòng: tọa độ một điểm gắn với đội (căn cứ hoặc bản ghi lịch sử). Dưới đây là **vị trí căn cứ** trùng hoặc gần `vi_tri_lat`/`vi_tri_lng` của đội.

| id_doi_cuu_ho | vi_tri_lat | vi_tri_lng | Ghi chú |
|---------------|------------|------------|---------|
| 1 | 16.0600 | 108.2200 | Căn cứ Hải Châu |
| 2 | 16.0900 | 108.1500 | Căn cứ Liên Chiểu |
| 3 | 16.0700 | 108.2000 | Trạm y tế cứu hộ Thanh Khê |
| 4 | 16.1000 | 108.2500 | Kho thiết bị Sơn Trà |
| 5 | 16.0000 | 108.2600 | Điểm tập kết Ngũ Hành Sơn |
| 6 | 16.0150 | 108.1850 | Trạm nước Cẩm Lệ |
| 7 | 15.9950 | 108.1150 | Trạm Hoà Vang |
| 8 | 16.0544 | 108.2022 | Trung tâm điều phối dự phòng |

---

## 7. JSON gọn (tham chiếu nhanh — đội + loại sự cố)

```json
{
  "doi_cuu_ho": [
    {
      "ten_co": "Đội Cứu Hộ Lũ Lụt Hải Châu",
      "khu_vuc_quan_ly": "Hải Châu",
      "so_dien_thoai_hotline": "0236-3847-001",
      "vi_tri_lat": 16.06,
      "vi_tri_lng": 108.22,
      "trang_thai": "SAN_SANG",
      "loai_su_co_slug": ["lu-lut"]
    },
    {
      "ten_co": "Đội Cứu Nạn Sạt Lở Liên Chiểu",
      "khu_vuc_quan_ly": "Liên Chiểu",
      "so_dien_thoai_hotline": "0236-3847-002",
      "vi_tri_lat": 16.09,
      "vi_tri_lng": 108.15,
      "trang_thai": "SAN_SANG",
      "loai_su_co_slug": ["sat-lo-dat"]
    },
    {
      "ten_co": "Đội Sơ Cứu Y Tế Cấp Cứu Thanh Khê",
      "khu_vuc_quan_ly": "Thanh Khê",
      "so_dien_thoai_hotline": "0236-3847-003",
      "vi_tri_lat": 16.07,
      "vi_tri_lng": 108.2,
      "trang_thai": "SAN_SANG",
      "loai_su_co_slug": ["lu-lut", "bao", "sat-lo-dat", "dong-dat", "song-than", "han-han"]
    },
    {
      "ten_co": "Đội Ứng Phó Bão & Gió Mạnh Sơn Trà",
      "khu_vuc_quan_ly": "Sơn Trà",
      "so_dien_thoai_hotline": "0236-3847-004",
      "vi_tri_lat": 16.1,
      "vi_tri_lng": 108.25,
      "trang_thai": "DANG_NHIEM_VU",
      "loai_su_co_slug": ["bao"]
    },
    {
      "ten_co": "Đội Hỗ Trợ Động Đất & Sóng Thần Ngũ Hành Sơn",
      "khu_vuc_quan_ly": "Ngũ Hành Sơn",
      "so_dien_thoai_hotline": "0236-3847-005",
      "vi_tri_lat": 16.0,
      "vi_tri_lng": 108.26,
      "trang_thai": "SAN_SANG",
      "loai_su_co_slug": ["dong-dat", "song-than"]
    },
    {
      "ten_co": "Đội Nước Sạch & Hạn Hán Cẩm Lệ",
      "khu_vuc_quan_ly": "Cẩm Lệ",
      "so_dien_thoai_hotline": "0236-3847-006",
      "vi_tri_lat": 16.015,
      "vi_tri_lng": 108.185,
      "trang_thai": "SAN_SANG",
      "loai_su_co_slug": ["han-han"]
    },
    {
      "ten_co": "Đội Cứu Hộ Đa Năng Hoà Vang",
      "khu_vuc_quan_ly": "Hoà Vang",
      "so_dien_thoai_hotline": "0236-3847-007",
      "vi_tri_lat": 15.995,
      "vi_tri_lng": 108.115,
      "trang_thai": "SAN_SANG",
      "loai_su_co_slug": ["lu-lut", "sat-lo-dat"]
    },
    {
      "ten_co": "Đội Dự Phòng Tổng Hợp Liên Ngành",
      "khu_vuc_quan_ly": "Trung tâm Đà Nẵng",
      "so_dien_thoai_hotline": "0236-3847-000",
      "vi_tri_lat": 16.0544,
      "vi_tri_lng": 108.2022,
      "trang_thai": "SAN_SANG",
      "loai_su_co_slug": ["lu-lut", "bao", "sat-lo-dat"]
    }
  ]
}
```

---

## 8. Gợi ý dùng trong báo cáo

- **Chương dữ liệu mẫu:** Trích bảng mục 1 + mục 5 (đội + tài nguyên).  
- **UC13:** Thêm sơ đồ quan hệ: một đội — nhiều thành viên / nhiều tài nguyên — nhiều loại sự cố (bảng nối).  
- **UC17:** Dùng mục 6 làm “căn cứ”; mô tả thêm bản ghi GPS cập nhật theo thời gian khi làm luồng real-time.  
- **Thống kê:** Đếm `so_luong` tài nguyên theo `loai_tai_nguyen`, hoặc `ty_le_hoan_thanh` theo đội (mục 3).

