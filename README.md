# 🚨 Smart Rescue Dispatch System (GR29)

[![Laravel](https://img.shields.io/badge/Laravel-10-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-green.svg)](https://vuejs.org)
[![MySQL](https://img.shields.io/badge/MySQL-8-blue.svg)](https://www.mysql.com)
[![PHP](https://img.shields.io/badge/PHP-8.1-purple.svg)](https://www.php.net)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

> **Hệ thống điều phối cứu hộ thông minh trong thiên tai** - Giải pháp công nghệ giúp kết nối người dân với đội cứu hộ một cách hiệu quả, sử dụng AI để ưu tiên hóa và bản đồ nhiệt real-time.

## 📋 Mục lục

- [Tổng quan](#-tổng-quan)
- [Tính năng chính](#-tính-năng-chính)
- [Công nghệ sử dụng](#-công-nghệ-sử-dụng)
- [Kiến trúc hệ thống](#-kiến-trúc-hệ-thống)
- [Cài đặt và chạy](#-cài-đặt-và-chạy)
- [API Documentation](#-api-documentation)
- [Cấu trúc dự án](#-cấu-trúc-dự-án)
- [Đóng góp](#-đóng-góp)
- [Giấy phép](#-giấy-phép)

## 🌟 Tổng quan

Hệ thống **SOS Rescue Dispatch** là nền tảng công nghệ tiên tiến giúp quản lý và điều phối hoạt động cứu hộ trong các tình huống thiên tai một cách tự động và hiệu quả. Hệ thống sử dụng trí tuệ nhân tạo (AI) để phân loại mức độ khẩn cấp của các yêu cầu cứu hộ, tạo bản đồ nhiệt khu vực nguy hiểm, và tối ưu hóa việc phân công đội cứu hộ.

### 🎯 Mục tiêu chính
- **Giảm thời gian phản ứng**: Tự động ưu tiên hóa yêu cầu dựa trên AI
- **Tối ưu hóa nguồn lực**: Phân công đội cứu hộ thông minh
- **Theo dõi real-time**: GPS tracking và cập nhật tiến độ
- **Báo cáo toàn diện**: Thống kê và phân tích hiệu suất

## ✨ Tính năng chính

### 👥 Cho Người Dân
- ✅ Đăng ký/đăng nhập tài khoản an toàn
- ✅ Gửi yêu cầu cứu hộ với GPS và hình ảnh
- ✅ Theo dõi trạng thái yêu cầu real-time
- ✅ Đánh giá chất lượng dịch vụ sau khi được cứu hộ

### 👨‍💼 Cho Admin/Điều Phối Viên
- ✅ Dashboard với KPI và thống kê real-time
- ✅ Quản lý hàng đợi ưu tiên thông minh
- ✅ Phân công đội cứu hộ tự động và thủ công
- ✅ Giám sát bản đồ nhiệt khu vực nguy hiểm
- ✅ Báo cáo và phân tích chi tiết

### 🚒 Cho Đội Cứu Hộ
- ✅ Nhận thông báo nhiệm vụ tức thời
- ✅ Cập nhật vị trí GPS real-time
- ✅ Báo cáo tiến độ và kết quả xử lý
- ✅ Upload ảnh minh chứng và tài liệu

### 🤖 AI Engine (Tự động)
- ✅ Phân loại mức độ khẩn cấp tự động
- ✅ Tính điểm ưu tiên (Priority Score)
- ✅ Cập nhật hàng đợi định kỳ
- ✅ Tạo bản đồ nhiệt real-time

## 🛠️ Công nghệ sử dụng

| Thành phần | Công nghệ | Mô tả |
|------------|-----------|--------|
| **Backend** | Laravel 10 + PHP 8.1 | Framework PHP hiện đại với kiến trúc MVC |
| **Frontend** | Vue.js 3 + Vite | Framework JavaScript reactive với build tool nhanh |
| **Database** | MySQL 8 | Hệ quản trị cơ sở dữ liệu quan hệ |
| **Authentication** | Laravel Sanctum | API token authentication bảo mật |
| **Real-time** | Laravel Reverb | WebSocket cho cập nhật real-time |
| **Maps** | Leaflet + OpenStreetMap | Bản đồ tương tác miễn phí |
| **Testing** | PHPUnit + Pest | Framework testing toàn diện |
| **Deployment** | Docker | Container hóa cho dễ dàng triển khai |

## 🏗️ Kiến trúc hệ thống

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Người Dân     │    │     Admin       │    │  Đội Cứu Hộ    │
│   (Mobile/Web)  │    │   (Dashboard)   │    │   (Mobile)      │
└─────────┬───────┘    └─────────┬───────┘    └─────────┬───────┘
          │                      │                      │
          └──────────────────────┼──────────────────────┘
                                 │
                    ┌────────────┴────────────┐
                    │      API Backend       │
                    │    (Laravel 10)        │
                    └────────────┬────────────┘
                                 │
                    ┌────────────┴────────────┐
                    │       Database         │
                    │       MySQL 8          │
                    └────────────┬────────────┘
                                 │
                    ┌────────────┴────────────┐
                    │      AI Engine         │
                    │  (Priority Scoring)    │
                    └─────────────────────────┘
```

### 📊 Cấu trúc Database (21 bảng)
- **Authentication**: `admin`, `nguoi_dung`, `chuc_vu`, `chuc_nang`
- **Core**: `yeu_cau_cuu_ho`, `loai_su_co`, `chi_tiet_loai_su_co`
- **AI**: `phan_loai_ais`, `trong_so_phan_loai`, `hang_doi_xu_ly`
- **Teams**: `doi_cuu_ho`, `nang_luc_doi`, `thanh_vien_doi`
- **Operations**: `phan_cong_cuu_ho`, `ket_qua_cuu_ho`, `vi_tri_doi_cuu_ho`
- **Resources**: `tai_nguyen_cuu_ho`
- **Analytics**: `danh_gia_cuu_ho`, `du_lieu_heatmap`

## 🚀 Cài đặt và chạy

### 📋 Yêu cầu hệ thống
- PHP 8.1+
- Composer
- Node.js 16+
- MySQL 8.0+
- Git

### ⚡ Quick Start

1. **Clone repository**
   ```bash
   git clone <repository-url>
   cd GR29
   ```

2. **Cài đặt Backend (Laravel)**
   ```bash
   cd BE_SOS
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

3. **Cấu hình Database**
   ```bash
   # Chỉnh sửa .env
   DB_CONNECTION=mysql
   DB_DATABASE=k28_be
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Chạy Migrations**
   ```bash
   php artisan migrate --seed
   ```

5. **Khởi động Backend**
   ```bash
   php artisan serve
   # API chạy tại: http://localhost:8000
   ```

6. **Cài đặt Frontend (Vue.js)**
   ```bash
   cd ../FE_SOS
   npm install
   npm run dev
   # Frontend chạy tại: http://localhost:5173
   ```

### 🧪 Testing
```bash
# Backend tests
cd BE_SOS
php artisan test

# Frontend tests (nếu có)
cd FE_SOS
npm run test
```

### 📖 API Documentation
- **Swagger UI**: `http://localhost:8000/api/docs`
- **Postman Collection**: Xem file `BE_SOS/postman_collection_full.json`
- **Chi tiết**: Xem `BE_SOS/API_DOCUMENTATION.md`

## 📁 Cấu trúc dự án

```
GR29/
├── BE_SOS/                          # Backend Laravel
│   ├── app/
│   │   ├── Http/Controllers/        # API Controllers
│   │   ├── Models/                  # Eloquent Models
│   │   ├── Services/                # Business Logic
│   │   └── Events/                  # Event Handlers
│   ├── database/
│   │   ├── migrations/              # Database Migrations
│   │   └── seeders/                 # Database Seeders
│   ├── routes/api.php               # API Routes
│   ├── tests/                       # Unit & Feature Tests
│   └── docs/                        # Documentation
├── FE_SOS/                          # Frontend Vue.js
│   ├── src/
│   │   ├── components/              # Vue Components
│   │   ├── views/                   # Page Views
│   │   ├── stores/                  # Pinia Stores
│   │   └── utils/                   # Utilities
│   ├── public/                      # Static Assets
│   └── tests/                       # E2E Tests
└── README.md                        # Project Documentation
```

## 🤝 Đóng góp

Chúng tôi hoan nghênh mọi đóng góp! Vui lòng:

1. Fork dự án
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

### 📝 Quy tắc đóng góp
- Tuân thủ PSR-12 coding standards
- Viết tests cho mọi feature mới
- Cập nhật documentation
- Sử dụng meaningful commit messages


## 📞 Liên hệ

- **Tác giả**: Nhóm phát triển Nhóm 29
- **Email**: sos.gr29.khoaluan@gmail.com
- **GitHub**: [https://github.com/KLTN-03-2026/GR29]

---

**Lưu ý**: Đây là dự án học thuật phục vụ mục đích nghiên cứu và phát triển. Không sử dụng trong môi trường production thực tế mà chưa qua kiểm tra bảo mật đầy đủ.

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
