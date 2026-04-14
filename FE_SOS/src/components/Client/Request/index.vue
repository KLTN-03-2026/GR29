<template>
  <div class="bg-light min-vh-100 py-4 py-md-5 px-3 px-md-5">
    <div class="container-fluid px-0">
      <!-- Header Section -->
      <div class="mb-5">
        <h1 class="fw-bold font-headline tracking-tight" style="color: #1a2a40; font-size: 2.5rem;">Yêu cầu của bạn</h1>
        <p class="text-secondary text-uppercase small fw-bold tracking-wider">THEO DÕI CÁC TÍN HIỆU KHẨN CẤP CỦA BẠN</p>
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
            <option value=""> Tất cả loại sự cố</option>
            <option v-for="type in uniqueTypes" :key="type" :value="type">{{ type }}</option>
          </select>
        </div>

        <!-- Tìm kiếm theo ngày tháng -->
        <div class="d-flex align-items-center gap-2" style="min-width: 280px;">
          <div class="position-relative flex-grow-1">
            <i class="bi bi-calendar3 position-absolute top-50 start-0 translate-middle-y ms-3" 
               style="color: #737687; font-size: 0.875rem;"></i>
            <input 
              type="date" 
              class="form-control ps-5 py-2.5 rounded-3 border-0"
              style="font-size: 0.875rem; background: #ffffff; color: #434655;"
              v-model="startDate"
              @change="onFilterChange"
            >
          </div>
          <span class="text-secondary fw-bold">-</span>
          <div class="position-relative flex-grow-1">
            <i class="bi bi-calendar3 position-absolute top-50 start-0 translate-middle-y ms-3" 
               style="color: #737687; font-size: 0.875rem;"></i>
            <input 
              type="date" 
              class="form-control ps-5 py-2.5 rounded-3 border-0"
              style="font-size: 0.875rem; background: #ffffff; color: #434655;"
              v-model="endDate"
              @change="onFilterChange"
            >
          </div>
        </div>

        <!-- Nút xem tất cả -->
        <button class="btn rounded-pill px-4 py-2.5 fw-bold shadow-sm" 
                style="background: #feb700; color: #6b4b00; font-size: 0.875rem; white-space: nowrap;"
                @click="viewAll">
          Xem tất cả
        </button>
      </div> 

      <!-- Hiển thị số kết quả -->
      <div class="mb-3" v-if="searchQuery || selectedType || startDate || endDate">
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
        <p class="text-secondary mt-3">Đang tải danh sách yêu cầu...</p>
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
              <div class="position-absolute top-2 start-2 bg-dark bg-opacity-50 backdrop-blur-md text-white px-2 py-1 rounded" 
                   style="font-size: 0.625rem; font-weight: 700;">
                ẢNH HIỆN TRƯỜNG
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
                <!-- Trạng thái với icon -->
                  <span :class="['status-badge px-3 py-2 rounded-3 fw-bold d-inline-flex align-items-center gap-2', item.statusBadgeClass]">
                    <i :class="['bi', item.statusIcon]" style="font-size: 0.875rem;"></i>
                    <span>{{ item.statusText }}</span>
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
              </div>

              <!-- Nút hành động -->
              <div class="d-flex gap-3 mt-3">
                <button class="btn text-white rounded-pill fw-bold flex-fill" 
                        style="background: #0042b3; font-size: 0.875rem; padding: 0.6rem 1rem; min-width: 140px;"
                        @click="showDetailModal(item)">
                  Chi tiết
                </button>
                <button v-if="item.statusKey !== 'HOAN_THANH' && item.statusKey !== 'huy_bo'"
                        class="btn text-danger border rounded-pill fw-bold flex-fill" 
                        style="font-size: 0.875rem; border-color: rgba(195, 198, 216, 0.2); padding: 0.6rem 1rem; min-width: 140px;"
                        @click="huyYeuCau(item)">
                  Hủy Yêu Cầu
                </button>
                <button v-else
                        class="btn text-secondary border rounded-pill fw-bold flex-fill opacity-50" 
                        style="font-size: 0.875rem; border-color: rgba(195, 198, 216, 0.2); padding: 0.6rem 1rem; min-width: 140px; cursor: not-allowed;"
                        disabled>
                  Đã kết thúc
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Thông báo không có kết quả -->
      <div v-else class="text-center py-5">
        <i class="bi bi-inbox fs-1 text-secondary"></i>
        <h5 class="text-secondary mt-3">Không tìm thấy yêu cầu nào</h5>
        <p class="text-secondary">Hãy thử thay đổi từ khóa tìm kiếm hoặc bộ lọc</p>
        <button class="btn btn-outline-primary mt-2" @click="viewAll">
          <i class="bi bi-arrow-clockwise me-2"></i>
          Xem tất cả yêu cầu
        </button>
      </div>

      <!-- Tải thêm yêu cầu -->
      
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
          <span :class="['px-3 py-2 rounded fw-bold', selectedItem?.statusBadgeClass]" 
                style="font-size: 0.875rem;">
            {{ selectedItem?.statusText }}
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
          <label class="fw-bold text-secondary small text-uppercase mb-2">Thời gian</label>
          <p class="mb-0" style="font-size: 1rem;">
            <i class="bi bi-clock me-2"></i>{{ selectedItem?.time }}
          </p>
        </div>

        <div class="mb-3">
          <label class="fw-bold text-secondary small text-uppercase mb-2">Mô tả</label>
          <div class="p-3 rounded-3" style="background: #f1f4f6;">
            <p class="mb-0" style="font-size: 1rem; line-height: 1.6;">{{ selectedItem?.moTa || 'Không có mô tả' }}</p>
          </div>
        </div>

        <div v-if="selectedItem?.chiTiet" class="mb-3">
          <label class="fw-bold text-secondary small text-uppercase mb-2">Chi tiết</label>
          <p class="mb-0" style="font-size: 1rem;">{{ selectedItem.chiTiet }}</p>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
          <button class="btn btn-secondary rounded-pill px-4" @click="closeModal">
            Đóng
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Xác nhận Hủy Yêu Cầu -->
    <div v-if="isCancelModalOpen" class="modal-overlay" @click.self="closeCancelModal">
      <div class="cancel-modal-content rounded-4 p-4 text-center">
        <div class="mb-4">
          <div class="warning-icon-wrapper mb-3">
            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
          </div>
          <h4 class="fw-bold mb-2" style="color: #181c1e;">Xác nhận hủy yêu cầu</h4>
          <p class="text-secondary mb-0" style="font-size: 0.9rem;">
            Bạn có chắc muốn hủy yêu cầu <strong>#{{ cancelItem?.id }}</strong> không?
          </p>
        </div>
        <div class="d-flex gap-3">
          <button class="btn flex-fill rounded-pill py-2.5 fw-bold" 
                  style="background: #f1f4f6; color: #434655;"
                  @click="closeCancelModal">
            Không, giữ lại
          </button>
          <button class="btn flex-fill rounded-pill py-2.5 fw-bold text-white" 
                  style="background: #dc2626;"
                  @click="confirmHuyYeuCau">
            <i class="bi bi-trash3 me-1"></i>
            Hủy yêu cầu
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescueRequestAPI } from "../../../services/api";

const BASE_URL = 'http://localhost:8000';

const STATUS_META = {
  hoan_thanh: { label: "Hoàn thành", badge: "bg-success text-white" },
  dang_xu_ly: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  cho_xu_ly: { label: "Chờ xử lý", badge: "bg-danger text-white" },
  huy_bo: { label: "Đã huỷ", badge: "bg-secondary text-white" },
  processing: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  waiting: { label: "Chờ xử lý", badge: "bg-danger text-white" },
  done: { label: "Hoàn thành", badge: "bg-success text-white" },
  hoan_thanh_v2: { label: "Hoàn thành", badge: "bg-success text-white" },
};

const TYPE_ICON = {
  "y tế": { icon: "bi-heart-pulse-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  "cháy nổ": { icon: "bi-flame-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  "cháy": { icon: "bi-flame-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  "lũ": { icon: "bi-water", color: "text-primary", bg: "bg-info bg-opacity-10" },
  "lũ lụt": { icon: "bi-water", color: "text-primary", bg: "bg-info bg-opacity-10" },
  "sóng thần": { icon: "bi-brightness-high-fill", color: "text-info", bg: "bg-info bg-opacity-10" },
  "hạn hán": { icon: "bi-sun-fill", color: "text-warning", bg: "bg-warning bg-opacity-10" },
  "tai nạn": { icon: "bi-geo-alt-fill", color: "text-warning", bg: "bg-warning bg-opacity-10" },
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
  const keys = ["id_nguoi_dung", "id", "user_id"];
  for (const key of keys) {
    const value = parsed[key];
    if (value !== undefined && value !== null && value !== "") {
      return Number(value);
    }
  }
  return null;
}

function getCurrentUserId() {
  const sources = ["user", "client"];
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

function normalizeStatusKey(value) {
  return normalizeValue(value)
    .toLowerCase()
    .replace(/[_\s]+/g, "_")
    .trim();
}

function parseStatus(rawStatus) {
  const key = normalizeStatusKey(rawStatus);
  return STATUS_META[key] || { label: normalizeValue(rawStatus), badge: "bg-secondary text-white" };
}

function parseStatusBadgeClass(rawStatus) {
  const key = normalizeStatusKey(rawStatus);
  switch (key) {
    case "hoan_thanh":
    case "done":
      return "status-badge status-completed";
    case "dang_xu_ly":
    case "processing":
      return "status-badge status-processing";
    case "cho_xu_ly":
    case "waiting":
      return "status-badge status-pending";
    case "huy_bo":
      return "status-badge status-cancelled";
    default:
      return "status-badge status-cancelled";
  }
}

function parseStatusBg(rawStatus) {
  const key = normalizeStatusKey(rawStatus);
  switch (key) {
    case "hoan_thanh":
    case "done":
    case "dang_xu_ly":
    case "processing":
      return "#dcfce7";
    case "cho_xu_ly":
    case "waiting":
      return "#fef3c7";
    case "huy_bo":
      return "#f3f4f6";
    default:
      return "#f3f4f6";
  }
}

function parseStatusIcon(rawStatus) {
  const key = normalizeStatusKey(rawStatus);
  switch (key) {
    case "hoan_thanh":
    case "done":
      return "bi-check-lg";
    case "dang_xu_ly":
    case "processing":
      return "bi-clock-fill";
    case "cho_xu_ly":
    case "waiting":
      return "bi-hourglass-split";
    case "huy_bo":
      return "bi-x-lg";
    default:
      return "bi-circle-fill";
  }
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
  const raw = String(image).trim();
  if (!raw) return null;
  // URL đầy đủ hoặc data URI
  if (/^(https?:|data:)/i.test(raw)) {
    return raw;
  }
  // Đường dẫn tương đối (uploads/...) → ghép với BASE_URL
  if (raw.startsWith('uploads/') || raw.startsWith('/uploads/')) {
    return BASE_URL + '/' + raw;
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
      startDate: "",
      endDate: "",
      isModalOpen: false,
      selectedItem: null,
      isCancelModalOpen: false,
      cancelItem: null,
    };
  },
  computed: {
    uniqueTypes() {
      const types = this.danhsach.map(item => item.loai);
      return [...new Set(types)].filter(type => type && type !== "Không rõ");
    },
    uniqueStatuses() {
      const statuses = this.danhsach.map(item => item.statusKey).filter(status => status);
      return [...new Set(statuses)];
    },
    filteredList() {
      let result = [...this.danhsach];

      // Ẩn các yêu cầu đã hoàn thành
      result = result.filter(item => {
        const key = normalizeStatusKey(item.statusKey);
        return key !== "hoan_thanh" && key !== "done";
      });

      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase().trim();
        result = result.filter(item => {
          const loai = (item.loai || "").toLowerCase();
          const address = (item.address || "").toLowerCase();
          const moTa = (item.moTa || "").toLowerCase();
          const chiTiet = (item.chiTiet || "").toLowerCase();
          const id = String(item.id || "").toLowerCase();
          
          return loai.includes(query) || 
                 address.includes(query) || 
                 moTa.includes(query) ||
                 chiTiet.includes(query) ||
                 id.includes(query);
        });
      }

      if (this.selectedType) {
        result = result.filter(item => item.loai === this.selectedType);
      }

      if (this.startDate || this.endDate) {
        result = result.filter(item => {
          const itemDate = new Date(item.raw?.created_at || item.raw?.ngay_tao || item.raw?.time);
          if (isNaN(itemDate.getTime())) return true;

          const start = this.startDate ? new Date(this.startDate) : null;
          const end = this.endDate ? new Date(this.endDate + "T23:59:59") : null;

          if (start && itemDate < start) return false;
          if (end && itemDate > end) return false;
          return true;
        });
      }

      return result;
    },
  },
  async created() {
    await this.loadRequests();
  },
  methods: {
    onSearchInput() {},
    onFilterChange() {},
    
    viewAll() {
      this.searchQuery = "";
      this.selectedType = "";
      this.startDate = "";
      this.endDate = "";
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

    normalizeRequests(items) {
      if (!Array.isArray(items)) {
        return [];
      }
      return items.map((item) => {
        const id = item.id || item.id_yeu_cau || item.ma_yeu_cau || item.request_id || "";
        const typeLabel = normalizeValue(item.ten_loai_su_co || item.loai_su_co || item.loai || item.chi_tiet || item.chi_tiet_su_co || "Không rõ");
        const rawStatus = item.trang_thai || item.status || item.statusText || "CHO_XU_LY";
        const statusMeta = parseStatus(rawStatus);
        const typeMeta = parseTypeIcon(typeLabel);
        const statusIcon = parseStatusIcon(rawStatus);
        const address = normalizeValue(item.vi_tri_dia_chi || item.dia_chi || item.address || `${item.vi_tri_lat || ""}, ${item.vi_tri_lng || ""}`);
        const time = formatTime(item.created_at || item.ngay_tao || item.thoi_gian || item.updated_at || item.time);

        return {
          id,
          loai: typeLabel,
          moTa: normalizeValue(item.mo_ta || item.moTa || item.mota || item.moTa || item.description || ""),
          chiTiet: normalizeValue(item.chi_tiet || item.chiTiet || item.chi_tiet_su_co || ""),
          time: time || "Không xác định",
          address: address || "Chưa có địa chỉ",
          statusText: statusMeta.label,
          statusBadgeClass: parseStatusBadgeClass(rawStatus),
          statusIcon: statusIcon,
          statusKey: rawStatus,
          icon: typeMeta.icon,
          iconColor: typeMeta.color,
          iconBg: typeMeta.bg,
          anh_hien_truong: item.anh_hien_truong || item.anh || item.image || getImageUrl(item.hinh_anh) || null,
          raw: item,
        };
      });
    },

    async loadRequests() {
      this.loading = true;
      this.error = "";
      try {
        const currentUserId = getCurrentUserId();
        if (!currentUserId) {
          this.danhsach = [];
          this.error = "Vui lòng đăng nhập để xem yêu cầu của bạn.";
          return;
        }
        let response;
        try {
          response = await rescueRequestAPI.getByUser(currentUserId);
        } catch (error) {
          response = await rescueRequestAPI.getList();
        }
        const rawData = response?.data;
        const items = Array.isArray(rawData)
          ? rawData
          : Array.isArray(rawData?.data)
          ? rawData.data
          : Array.isArray(rawData?.data?.data)
          ? rawData.data.data
          : [];
        const filtered = items.filter((item) => {
          const itemUserId = extractUserId(item) || extractUserId(item?.nguoi_dung);
          return itemUserId === currentUserId;
        });
        this.danhsach = this.normalizeRequests(filtered);
      } catch (error) {
        console.error("Không tải được danh sách yêu cầu:", error);
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

    huyYeuCau(item) {
      const requestId = item?.id;
      if (!requestId) {
        this.hienToast("warning", "Không xác định được yêu cầu cần hủy.");
        return;
      }
      this.cancelItem = item;
      this.isCancelModalOpen = true;
      document.body.style.overflow = 'hidden';
    },

    async confirmHuyYeuCau() {
      const requestId = this.cancelItem?.id;
      if (!requestId) {
        this.hienToast("warning", "Không xác định được yêu cầu cần hủy.");
        return;
      }
      this.isCancelModalOpen = false;
      document.body.style.overflow = 'auto';
      try {
        await rescueRequestAPI.delete(requestId);
        this.hienToast("success", "Hủy yêu cầu thành công.");
        await this.loadRequests();
      } catch (error) {
        console.error("Hủy yêu cầu thất bại:", error);
        const message =
          error?.response?.data?.message ||
          "Không thể hủy yêu cầu. Vui lòng thử lại.";
        this.hienToast("error", message);
      } finally {
        this.cancelItem = null;
      }
    },

    closeCancelModal() {
      this.isCancelModalOpen = false;
      this.cancelItem = null;
      document.body.style.overflow = 'auto';
    },
  },
};
</script>

<style scoped>
:root {
  --status-bg: transparent;
}

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

/* Status Badge - Giống Lịch sử Yêu cầu */
.status-badge {
  font-size: 0.75rem;
  letter-spacing: 0.02em;
}

/* Hoàn thành */
.status-badge.status-completed {
  color: #fff;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

/* Đang xử lý */
.status-badge.status-processing {
  color: #fff;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

/* Chờ xử lý */
.status-badge.status-pending {
  color: #fff;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

/* Đã hủy */
.status-badge.status-cancelled {
  color: #fff;
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
  box-shadow: 0 2px 8px rgba(107, 114, 128, 0.3);
}

/* Container spacing */
.container-fluid {
  max-width: 1400px;
  margin: 0 auto;
}

/* Cancel Modal Styles */
.cancel-modal-content {
  background: #ffffff;
  width: 100%;
  max-width: 400px;
  padding: 2rem;
  box-shadow: 0 2rem 4rem rgba(0, 0, 0, 0.2);
}

.warning-icon-wrapper {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #fef3c7;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

.warning-icon-wrapper i {
  font-size: 2.5rem;
}
</style>
