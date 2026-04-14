<template>
  <div class="bg-light min-vh-100 py-4 py-md-5 px-3 px-md-5">
    <div class="container-fluid px-0">
      <!-- Header Section -->
      <div class="mb-5">
        <h1 class="fw-bold font-headline tracking-tight" style="color: #1a2a40; font-size: 2.5rem;">Lịch sử Yêu cầu Cứu hộ</h1>
        <p class="text-secondary text-uppercase small fw-bold tracking-wider">THEO DÕI CÁC TÍN HIỆU KHẨN CẤP ĐÃ HOÀN THÀNH</p>
      </div>

      <!-- Filter Bar - All in one row -->
      <div class="d-flex flex-wrap align-items-end gap-3 mb-4 p-3 rounded-4"
           style="background: #f1f4f6;">
        <!-- Thanh tìm kiếm -->
        <div class="position-relative flex-grow-1" style="min-width: 250px; max-width: 350px;">
          <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3"
             style="color: #737687; font-size: 1rem;"></i>
          <input
            type="text"
            class="form-control ps-5 py-2.5 rounded-3 border-0"
            style="font-size: 0.875rem; background: #ffffff;"
            placeholder="Tìm ID hoặc tiêu đề..."
            v-model="searchQuery"
            @input="onSearchInput"
          >
        </div>

        <!-- Lọc theo loại sự cố -->
        <div style="min-width: 180px;">
          <select class="form-select py-2.5 px-4 rounded-3 border-0"
                  style="font-size: 0.875rem; background: #ffffff; color: #434655;"
                  v-model="selectedType" @change="onFilterChange">
            <option value="">Loại sự cố</option>
            <option v-for="type in uniqueTypes" :key="type" :value="type">{{ type }}</option>
          </select>
        </div>

        <!-- Lọc theo đánh giá -->
        <div style="min-width: 180px;">
          <select class="form-select py-2.5 px-4 rounded-3 border-0"
                  style="font-size: 0.875rem; background: #ffffff; color: #434655;"
                  v-model="selectedRating" @change="onFilterChange">
            <option value="">Đánh giá</option>
            <option value="5">5 sao</option>
            <option value="4">4 sao</option>
            <option value="3">3 sao</option>
            <option value="2">2 sao</option>
            <option value="1">1 sao</option>
            <option value="0">Chưa đánh giá</option>
          </select>
        </div>

        <!-- Nút xem tất cả -->
        <button class="btn rounded-pill px-4 py-2.5 fw-bold shadow-sm"
                style="background: #feb700; color: #6b4b00; font-size: 0.875rem; white-space: nowrap;"
                @click="viewAll">
          Xem tất cả
        </button>
      </div>

      <!-- Hiển thị số kết quả -->
      <div class="mb-3" v-if="searchQuery || selectedType || selectedRating">
        <p class="text-secondary mb-0">
          <i class="bi bi-info-circle me-1"></i>
          Tìm thấy <strong>{{ filteredList.length }}</strong> kết quả
        </p>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Đang tải...</span>
        </div>
        <p class="text-secondary mt-3">Đang tải lịch sử yêu cầu...</p>
      </div>

      <!-- 2x2 Bento Grid of Request Cards -->
      <div v-else-if="filteredList.length > 0" class="row g-4">
        <div v-for="item in filteredList" :key="item.id" class="col-12 col-md-6">
          <div class="glass-card rounded-4 p-4 d-flex flex-column flex-sm-row gap-4 hover-card">
            <!-- Hình ảnh -->
            <div class="rounded-3 overflow-hidden shrink-0 position-relative"
                 style="width: 100%; height: 180px; max-width: 180px;">
              <img v-if="item.anh_hien_truong"
                   :src="item.anh_hien_truong"
                   class="w-100 h-100 object-fit-cover hover-image"
                   alt="Ảnh hiện trường">
              <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center" style="background: #e0e3e5;">
                <i :class="['bi fs-1', item.icon, item.iconColor]"></i>
              </div>
              <div class="position-absolute top-2 start-2 badge-completed">
                <i class="bi bi-check-circle-fill me-1"></i>
                Hoàn thành
              </div>
            </div>

            <!-- Nội dung -->
            <div class="d-flex flex-column justify-content-between flex-grow py-1">
              <div>
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <span class="px-2.5 py-1 rounded"
                        style="font-size: 0.75rem; font-weight: 700; background: #e0e3e5; color: #434655;">
                    SOS-{{ item.id }}
                  </span>
                  <span class="badge-success-completed">
                    <i class="bi bi-check-lg me-1"></i>
                    Hoàn thành
                  </span>
                </div>

                <h3 class="fw-bold mb-1" style="font-size: 1.25rem; font-family: 'Manrope', sans-serif; color: #181c1e;">
                  {{ item.loai }}
                </h3>

                <div class="d-flex align-items-center gap-2 text-secondary mb-3" style="font-size: 0.75rem;">
                  <i class="bi bi-clock" style="font-size: 0.875rem;"></i>
                  <span>{{ item.time }}</span>
                  <span class="mx-1">•</span>
                  <i class="bi bi-geo-alt text-danger" style="font-size: 0.875rem;"></i>
                  <span>{{ item.address }}</span>
                </div>

                <p class="text-secondary line-clamp-2 mb-0" style="font-size: 0.875rem; line-height: 1.5;">
                  {{ item.moTa }}
                </p>

                <!-- Hiển thị đánh giá nếu có -->
                <div v-if="item.danh_gia" class="d-flex align-items-center gap-1 mt-2">
                  <span class="text-warning">
                    <i v-for="n in 5" :key="n" :class="['bi', n <= item.danh_gia ? 'bi-star-fill' : 'bi-star']"></i>
                  </span>
                  <span class="text-secondary small ms-1">({{ item.danh_gia }} sao)</span>
                </div>
              </div>

              <!-- Nút hành động -->
              <div class="d-flex gap-3 mt-3">
                <button class="btn text-white rounded-pill fw-bold flex-fill"
                        style="background: #0042b3; font-size: 0.875rem; padding: 0.5rem 1rem;"
                        @click="showDetailModal(item)">
                  Chi tiết
                </button>
                <button class="btn text-warning border rounded-pill fw-bold flex-fill"
                        style="font-size: 0.875rem; border-color: rgba(195, 198, 216, 0.2); padding: 0.5rem 1rem;">
                  <i class="bi bi-star me-1"></i>
                  Đánh Giá
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Thông báo chưa đăng nhập -->
      <div v-else-if="error && error.includes('đăng nhập')" class="text-center py-5">
        <div class="mb-4">
          <i class="bi bi-person-circle fs-1 text-secondary"></i>
        </div>
        <h5 class="text-secondary mb-3">Vui lòng đăng nhập</h5>
        <p class="text-secondary mb-4">Bạn cần đăng nhập để xem lịch sử yêu cầu cứu hộ</p>
        <button class="btn rounded-pill px-4 py-2 fw-bold"
                style="background: #0042b3; color: white;"
                @click="$router.push('/dang-nhap')">
          <i class="bi bi-box-arrow-in-right me-2"></i>
          Đăng nhập ngay
        </button>
      </div>

      <!-- Thông báo lỗi -->
      <div v-else-if="error" class="text-center py-5">
        <div class="mb-4">
          <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
        </div>
        <h5 class="text-secondary mb-3">{{ error }}</h5>
        <button class="btn btn-outline-primary rounded-pill mt-2" @click="loadHistory">
          <i class="bi bi-arrow-clockwise me-2"></i>
          Thử lại
        </button>
      </div>

      <!-- Thông báo không có kết quả -->
      <div v-else-if="danhsach.length === 0 && !loading" class="text-center py-5">
        <div class="mb-4">
          <i class="bi bi-inbox fs-1 text-secondary"></i>
        </div>
        <h5 class="text-secondary mb-3">Chưa có yêu cầu hoàn thành</h5>
        <p class="text-secondary mb-4">Bạn chưa có yêu cầu cứu hộ nào được hoàn thành</p>
        <button class="btn rounded-pill px-4 py-2 fw-bold"
                style="background: #0042b3; color: white;"
                @click="$router.push('/gui-yeu-cau')">
          <i class="bi bi-plus-circle me-2"></i>
          Gửi yêu cầu cứu hộ
        </button>
      </div>

    </div>

    <!-- Modal Chi tiết -->
    <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="fw-bold mb-0" style="font-family: 'Manrope', sans-serif; color: #181c1e;">
            Chi tiết yêu cầu #{{ selectedItem?.id }}
          </h4>
          <button class="btn-close" @click="closeModal"></button>
        </div>

        <div class="mb-3">
          <span class="badge-modal-completed">
            <i class="bi bi-check-circle-fill me-2"></i>
            Hoàn thành
          </span>
        </div>

        <div class="mb-3">
          <label class="fw-bold text-secondary small text-uppercase mb-2">Loại sự cố</label>
          <p class="mb-0" style="font-size: 1rem;">{{ selectedItem?.loai }}</p>
        </div>

        <div class="mb-3">
          <label class="fw-bold text-secondary small text-uppercase mb-2">Địa chỉ</label>
          <p class="mb-0" style="font-size: 1rem;">
            <i class="bi bi-geo-alt text-danger me-2"></i>{{ selectedItem?.address }}
          </p>
        </div>

        <div class="mb-3">
          <label class="fw-bold text-secondary small text-uppercase mb-2">Thời gian hoàn thành</label>
          <p class="mb-0" style="font-size: 1rem;">
            <i class="bi bi-check-circle text-success me-2"></i>{{ selectedItem?.time }}
          </p>
        </div>

        <div class="mb-3">
          <label class="fw-bold text-secondary small text-uppercase mb-2">Mô tả</label>
          <div class="p-3 rounded-3" style="background: #f1f4f6;">
            <p class="mb-0" style="font-size: 1rem; line-height: 1.6;">{{ selectedItem?.moTa || 'Không có mô tả' }}</p>
          </div>
        </div>

        <div v-if="selectedItem?.ketQua" class="mb-3">
          <label class="fw-bold text-secondary small text-uppercase mb-2">Kết quả cứu hộ</label>
          <div class="p-3 rounded-3" style="background: #e8f5e9;">
            <p class="mb-0" style="font-size: 1rem; line-height: 1.6;">{{ selectedItem.ketQua }}</p>
          </div>
        </div>

        <div v-if="selectedItem?.danh_gia" class="mb-3">
          <label class="fw-bold text-secondary small text-uppercase mb-2">Đánh giá của bạn</label>
          <div class="d-flex align-items-center gap-2">
            <span class="text-warning">
              <i v-for="n in 5" :key="n" :class="['bi fs-5', n <= selectedItem.danh_gia ? 'bi-star-fill' : 'bi-star']"></i>
            </span>
            <span class="text-secondary">({{ selectedItem.danh_gia }} sao)</span>
          </div>
          <p v-if="selectedItem?.nhan_xet" class="mt-2 mb-0 text-secondary">{{ selectedItem.nhan_xet }}</p>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
          <button class="btn btn-secondary rounded-pill px-4" @click="closeModal">
            Đóng
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescueRequestAPI } from "../../../services/api";

const BASE_URL = 'http://localhost:8000';

const TYPE_ICON = {
  "y tế": { icon: "bi-heart-pulse-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  "cháy nổ": { icon: "bi-flame-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  "cháy": { icon: "bi-flame-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  "lũ": { icon: "bi-water", color: "text-primary", bg: "bg-info bg-opacity-10" },
  "lũ lụt": { icon: "bi-water", color: "text-primary", bg: "bg-info bg-opacity-10" },
  "sóng thần": { icon: "bi-brightness-high-fill", color: "text-info", bg: "bg-info bg-opacity-10" },
  "hạn hán": { icon: "bi-sun-fill", color: "text-warning", bg: "bg-warning bg-opacity-10" },
  "tai nạn": { icon: "bi-geo-alt-fill", color: "text-warning", bg: "bg-warning bg-opacity-10" },
  "giao thông": { icon: "bi-car-front-fill", color: "text-secondary", bg: "bg-secondary bg-opacity-10" },
  "động đất": { icon: "bi-activity", color: "text-danger", bg: "bg-danger bg-opacity-10" },
};

function normalizeValue(value, fallback = "") {
  if (value === null || value === undefined) {
    return fallback;
  }
  if (typeof value === "object") {
    return normalizeValue(value.ten_danh_muc || value.ten_loai_su_co || value.ten || value.name || fallback, fallback);
  }
  return String(value);
}

function extractUserId(parsed) {
  if (!parsed || typeof parsed !== "object") {
    return null;
  }
  // Thứ tự ưu tiên các trường ID
  const keys = [
    "id_nguoi_dung",
    "id_nguoi_dung",
    "id",
    "user_id",
    "ma_nguoi_dung",
    "nguoi_dung_id"
  ];
  for (const key of keys) {
    const value = parsed[key];
    if (value !== undefined && value !== null && value !== "") {
      return Number(value);
    }
  }
  return null;
}

function getCurrentUserId() {
  // Thứ tự ưu tiên: user_token, user, client
  const sources = ["user_token", "user", "client"];
  for (const key of sources) {
    const raw = localStorage.getItem(key);
    if (!raw) continue;
    try {
      const parsed = JSON.parse(raw);
      const id = extractUserId(parsed);
      if (id) {
        return id;
      }
    } catch (error) {
      // ignore invalid JSON
    }
  }
  return null;
}

function formatTime(value) {
  if (!value) return "";
  const time = new Date(value);
  if (Number.isNaN(time.getTime())) {
    return normalizeValue(value);
  }
  return time.toLocaleString("vi-VN", {
    hour: "2-digit",
    minute: "2-digit",
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

function parseTypeIcon(rawType) {
  const type = normalizeValue(rawType).toLowerCase();
  for (const key of Object.keys(TYPE_ICON)) {
    if (type.includes(key)) {
      return TYPE_ICON[key];
    }
  }
  return { icon: "bi-shield-fill-check", color: "text-secondary", bg: "bg-secondary bg-opacity-15" };
}

function getImageUrl(image) {
  if (!image) return null;
  const raw = String(image);
  if (/^(https?:|data:)/i.test(raw)) {
    return raw;
  }
  if (raw.startsWith('uploads/') || raw.startsWith('/uploads/')) {
    return BASE_URL + (raw.startsWith('/') ? '' : '/') + raw;
  }
  return null;
}

export default {
  data() {
    return {
      danhsach: [],
      loading: false,
      error: "",
      searchQuery: "",
      selectedType: "",
      selectedRating: "",
      isModalOpen: false,
      selectedItem: null,
    };
  },
  computed: {
    uniqueTypes() {
      const types = this.danhsach.map(item => item.loai);
      return [...new Set(types)].filter(type => type && type !== "Không rõ");
    },
    filteredList() {
      let result = [...this.danhsach];

      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase().trim();
        result = result.filter(item => {
          const loai = (item.loai || "").toLowerCase();
          const address = (item.address || "").toLowerCase();
          const moTa = (item.moTa || "").toLowerCase();
          const id = String(item.id || "").toLowerCase();

          return loai.includes(query) ||
                 address.includes(query) ||
                 moTa.includes(query) ||
                 id.includes(query);
        });
      }

      if (this.selectedType) {
        result = result.filter(item => item.loai === this.selectedType);
      }

      if (this.selectedRating) {
        const rating = parseInt(this.selectedRating);
        if (rating === 0) {
          result = result.filter(item => !item.danh_gia || item.danh_gia === 0);
        } else {
          result = result.filter(item => item.danh_gia === rating);
        }
      }

      return result;
    },
  },
  async created() {
    await this.loadHistory();
  },
  methods: {
    onSearchInput() {},
    onFilterChange() {},

    viewAll() {
      this.searchQuery = "";
      this.selectedType = "";
      this.selectedRating = "";
    },

    hienToast(type, message) {
      if (this.$toast?.[type]) {
        this.$toast[type](message, {
          position: "top-right",
          duration: 3500,
        });
        return;
      }
      alert(message);
    },

    normalizeResults(items) {
      if (!Array.isArray(items)) {
        return [];
      }
      return items.map((item) => {
        // Xử lý ID - có thể là id_yeu_cau hoặc id
        const id = item.id_yeu_cau || item.id || item.ma_ket_qua || item.result_id || "";

        // Xử lý loại sự cố - có thể từ object loaiSuCo hoặc trường trực tiếp
        let typeLabel = "Không rõ";
        if (item.loaiSuCo) {
          typeLabel = normalizeValue(
            item.loaiSuCo.ten_danh_muc ||
            item.loaiSuCo.ten_loai_su_co ||
            item.loaiSuCo.ten ||
            "Không rõ"
          );
        } else {
          typeLabel = normalizeValue(
            item.ten_loai_su_co ||
            item.loai_su_co ||
            item.loai ||
            item.chi_tiet ||
            item.chi_tiet_su_co ||
            "Không rõ"
          );
        }

        const typeMeta = parseTypeIcon(typeLabel);

        // Xử lý địa chỉ
        let address = "";
        if (item.vi_tri_dia_chi) {
          address = item.vi_tri_dia_chi;
        } else if (item.dia_chi || item.address) {
          address = item.dia_chi || item.address;
        } else if (item.vi_tri_lat && item.vi_tri_lng) {
          address = `${item.vi_tri_lat}, ${item.vi_tri_lng}`;
        }

        // Xử lý thời gian hoàn thành
        const time = formatTime(
          item.thoi_gian_hoan_thanh ||
          item.ngay_hoan_thanh ||
          item.updated_at ||
          item.created_at ||
          item.thoi_gian ||
          item.time
        );

        // Xử lý đánh giá (có thể nằm trong object danhGias)
        let danhGia = null;
        let nhanXet = "";
        if (item.danhGias && item.danhGias.length > 0) {
          danhGia = item.danhGias[0].diem_danh_gia ||
                    item.danhGias[0].so_sao ||
                    item.danhGias[0].danh_gia ||
                    null;
          nhanXet = item.danhGias[0].noi_dung_danh_gia ||
                    item.danhGias[0].nhan_xet ||
                    item.danhGias[0].feedback ||
                    "";
        }
        danhGia = danhGia || item.diem_danh_gia || item.danh_gia || item.danh_gia_sao || item.rating || null;

        // Xử lý hình ảnh - ưu tiên hinh_anh từ backend, rồi ghép với BASE_URL nếu là đường dẫn tương đối
        let anhHienTruong = null;
        if (item.hinh_anh) {
          anhHienTruong = getImageUrl(item.hinh_anh);
        } else if (item.anh_hien_truong) {
          anhHienTruong = getImageUrl(item.anh_hien_truong);
        } else if (item.anh) {
          anhHienTruong = getImageUrl(item.anh);
        } else if (item.image) {
          anhHienTruong = getImageUrl(item.image);
        }

        return {
          id,
          loai: typeLabel,
          moTa: normalizeValue(item.mo_ta || item.moTa || item.mota || item.description || ""),
          time: time || "Không xác định",
          address: address || "Chưa có địa chỉ",
          danh_gia: danhGia,
          nhan_xet: nhanXet || item.nhan_xet || item.feedback || "",
          icon: typeMeta.icon,
          iconColor: typeMeta.color,
          iconBg: typeMeta.bg,
          anh_hien_truong: anhHienTruong,
          trang_thai: item.trang_thai,
          raw: item,
        };
      });
    },

    async loadHistory() {
      this.loading = true;
      this.error = "";
      try {
        const currentUserId = getCurrentUserId();
        if (!currentUserId) {
          this.danhsach = [];
          this.error = "Vui lòng đăng nhập để xem lịch sử yêu cầu.";
          return;
        }

        // Lấy tất cả yêu cầu hoàn thành của người dùng
        let response;
        try {
          // Thử gọi API theo user trước
          response = await rescueRequestAPI.getByUser(currentUserId);
        } catch (apiError) {
          // Fallback: lấy tất cả rồi lọc ở frontend
          response = await rescueRequestAPI.getList();
        }

        const rawData = response?.data;
        let items = Array.isArray(rawData)
          ? rawData
          : Array.isArray(rawData?.data)
          ? rawData.data
          : Array.isArray(rawData?.data?.data)
          ? rawData.data.data
          : [];

        // Lọc chỉ lấy yêu cầu HOÀN THÀNH của user hiện tại
        const completedItems = items.filter((item) => {
          const trangThai = (item.trang_thai || "").toUpperCase();
          const isCompleted = trangThai === "HOAN_THANH" || trangThai === "DA_HOAN_THANH";
          const itemUserId = extractUserId(item) || extractUserId(item?.nguoi_dung);
          return isCompleted && itemUserId === currentUserId;
        });

        this.danhsach = this.normalizeResults(completedItems);
      } catch (error) {
        console.error("Không tải được lịch sử yêu cầu:", error);
        this.error = "Không tải được yêu cầu. Vui lòng thử lại.";
        this.hienToast("error", this.error);
      } finally {
        this.loading = false;
      }
    },

    showDetailModal(item) {
      this.selectedItem = item;
      this.isModalOpen = true;
      document.body.style.overflow = 'hidden';
    },

    closeModal() {
      this.isModalOpen = false;
      this.selectedItem = null;
      document.body.style.overflow = 'auto';
    },
  },
};
</script>

<style scoped>

/* Glass card effect */
.glass-card {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.glass-card:hover {
  box-shadow: 0 1rem 2rem rgba(0, 66, 179, 0.08);
}

/* Card hover effect */
.hover-card {
  transition: all 0.3s ease;
}

.hover-card:hover {
  box-shadow: 0 1rem 2rem rgba(0, 66, 179, 0.12);
  transform: translateY(-2px);
}

/* Image hover zoom */
.hover-image {
  transition: transform 0.5s ease;
}

.hover-image:hover {
  transform: scale(1.05);
}

/* Search input focus style */
.form-control:focus,
.form-select:focus {
  border-color: #0042b3;
  box-shadow: 0 0 0 0.15rem rgba(0, 66, 179, 0.15);
}

/* Line clamp */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Object fit */
.object-fit-cover {
  object-fit: cover;
}

/* Button hover */
.btn:hover {
  filter: brightness(1.05);
}

.btn:active {
  transform: scale(0.98);
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.modal-content {
  background: #ffffff;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 2rem 4rem rgba(0, 0, 0, 0.2);
}

/* Status colors */
.text-success {
  color: #00591c !important;
}

.text-warning {
  color: #7c5800 !important;
}

.text-danger {
  color: #ba1a1a !important;
}

.text-secondary {
  color: #434655 !important;
}

/* Badge Hoàn thành - ảnh */
.badge-completed {
  display: inline-flex;
  align-items: center;
  padding: 0.35rem 0.75rem;
  font-size: 0.65rem;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 50px;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

/* Badge Hoàn thành - trong card */
.badge-success-completed {
  display: inline-flex;
  align-items: center;
  padding: 0.35rem 0.75rem;
  font-size: 0.7rem;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 50px;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

/* Badge Hoàn thành - modal */
.badge-modal-completed {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 50px;
  box-shadow: 0 3px 12px rgba(16, 185, 129, 0.4);
}

/* Container spacing */
.container-fluid {
  max-width: 1400px;
  margin: 0 auto;
}
</style>
