<template>
  <div class="admin-dang-xu-ly-wrapper py-4 px-3 px-md-4">
    <div class="header-section d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div class="title-wrapper d-flex align-items-start gap-3">
        <div class="icon-box">
          <i class="bi bi-clock-history text-white fs-4"></i>
        </div>
        <div>
          <h4 class="mb-1 fw-bolder page-title">Đang Xử Lý</h4>
          <p class="text-muted mb-0 page-subtitle">Quản lý và theo dõi các yêu cầu đang được thực hiện</p>
        </div>
      </div>
      <button class="btn btn-refresh d-flex align-items-center gap-2 align-self-md-auto align-self-start" @click="loadRequests" :disabled="loading">
        <i class="bi bi-arrow-clockwise" :class="{ 'spin-animation': loading }"></i>
        <span>Làm mới dữ liệu</span>
      </button>
    </div>

    <!-- Search / Filter -->
    <div class="search-filter-section bg-white p-3 rounded-4 shadow-sm border border-light mb-5">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label text-muted small fw-bold mb-1">Tìm kiếm chung</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input v-model="searchQuery" type="text" class="form-control border-start-0 ps-0 bg-light" placeholder="Mã sự cố, địa chỉ..." @input="onSearchInput">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label text-muted small fw-bold mb-1">Loại sự cố</label>
          <select v-model="searchType" class="form-select bg-light" @change="onSearchInput">
            <option value="">Tất cả loại sự cố</option>
            <option v-for="type in uniqueTypes" :key="type" :value="type">{{ type }}</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label text-muted small fw-bold mb-1">Thời gian</label>
          <input v-model="searchDate" type="date" class="form-control bg-light" @change="onSearchInput">
        </div>
        <div class="col-md-2 d-flex align-items-end gap-2">
          <button type="button" class="btn btn-outline-secondary w-100" title="Xóa bộ lọc" @click="resetFilters">
            <i class="bi bi-x-lg me-1"></i> xem tất cả
          </button>
        </div>
      </div>
    </div>

    <!-- Alerts and Loading States -->
    <div v-if="error" class="alert custom-alert-danger mb-4 shadow-sm border-0 d-flex align-items-center gap-3 rounded-3">
      <i class="bi bi-exclamation-triangle-fill fs-5"></i>
      <div>{{ error }}</div>
    </div>

    <div v-if="loading" class="loading-state py-5 text-center d-flex flex-column align-items-center gap-3">
      <div class="spinner"></div>
      <div class="text-muted fw-medium fs-5">Đang đồng bộ dữ liệu...</div>
    </div>

    <div v-if="!loading && requests.length === 0" class="empty-state py-5 my-4 text-center d-flex flex-column align-items-center justify-content-center">
      <div class="empty-icon text-muted mb-3">
        <i class="bi bi-shield-check" style="font-size: 4rem; opacity: 0.3;"></i>
      </div>
      <h5 class="fw-bold text-dark mb-1">Không có dữ liệu</h5>
      <p class="text-muted mb-0">Hiện tại không có yêu cầu cứu hộ nào đang trong quá trình xử lý.</p>
    </div>

    <!-- Requests List using the provided card UI -->
    <div class="row g-4">
      <div v-for="request in requests" :key="request.key" class="col-lg-4 col-md-6">
        <div class="incident-card card h-100 border-0 shadow-sm">
          <div class="row g-0 h-100 flex-column flex-sm-row">
            <!-- Image Area -->
            <div class="col-sm-5 col-md-4 image-container position-relative bg-light">
              <img v-if="request.imageUrl" :src="request.imageUrl" class="incident-img w-100 h-100 position-absolute" style="top:0; left:0; object-fit: cover;" alt="Hình ảnh sự cố" />
              <div v-else class="empty-image h-100 w-100 position-absolute d-flex flex-column align-items-center justify-content-center text-muted p-4" style="top:0; left:0;">
                <i class="bi bi-images fs-1 mb-2 text-secondary opacity-25"></i>
                <span class="small fw-medium opacity-50">Chưa có hình ảnh</span>
              </div>
              <!-- Floating Priority Badge -->
              <div class="position-absolute top-0 start-0 m-3 z-1">
                <span class="badge priority-badge shadow-sm" :class="{'bg-danger text-white': request.priorityLabel?.toLowerCase().includes('khẩn') || request.priorityLabel?.toLowerCase().includes('cao'), 'bg-warning text-dark': !request.priorityLabel?.toLowerCase().includes('khẩn') && !request.priorityLabel?.toLowerCase().includes('cao')}">
                  <i class="bi bi-shield-exclamation me-1"></i> {{ request.priorityLabel || 'Mức độ ưu tiên' }}
                </span>
              </div>
            </div>

            <!-- Content Area -->
            <div class="col-sm-7 col-md-8 card-right-body d-flex flex-column">
              <div class="card-body p-4 p-md-4 d-flex flex-column h-100">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-3 gap-2 flex-wrap">
                  <div class="id-badge bg-secondary bg-opacity-10 text-dark fw-bold px-3 py-1 rounded-pill d-flex align-items-center gap-1">
                    <i class="bi bi-hash text-primary"></i>SOS-{{ request.id }}
                  </div>
                  <span class="status-badge rounded-pill px-3 py-1 fw-bold border border-1 border-opacity-10" :class="request.statusClass">
                    <span class="status-dot flex-shrink-0 me-2" :class="request.statusClass.includes('text-dark') ? 'bg-dark' : 'bg-white'"></span>
                    <span>{{ request.statusLabel }}</span>
                  </span>
                </div>

                <!-- Title & Time -->
                <h4 class="incident-title fw-bolder mb-2 text-dark">{{ request.type }}</h4>
                <div class="time-stamp d-flex align-items-center text-muted small mb-4">
                  <i class="bi bi-clock-history me-2 text-primary opacity-75"></i>
                  <span class="fw-medium">{{ request.time }}</span>
                </div>

                <!-- Details -->
                <div class="info-list d-flex flex-column gap-3 mb-4 flex-grow-1">
                  <div class="info-item d-flex gap-3 align-items-start">
                    <div class="info-icon bg-blue-light text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                      <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="info-content">
                      <div class="info-label text-muted small fw-bold text-uppercase mb-1">Cần giúp đỡ về : </div>
                      <div class="info-value text-dark fw-semibold">{{ request.chiTiet || 'Không có chi tiết' }}</div>
                    </div>
                  </div>

                  <div class="info-item d-flex gap-3 align-items-start">
                    <div class="info-icon bg-red-light text-danger rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                      <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="info-content w-100">
                      <div class="info-label text-muted small fw-bold text-uppercase mb-1">Vị trí sự cố</div>
                      <div class="info-value text-dark fw-semibold text-truncate-2" :title="request.address">{{ request.address }}</div>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="mt-auto action-footer border-top pt-4">
                  <button 
                    class="btn action-btn w-100 d-flex align-items-center justify-content-center gap-2 fw-bold" 
                    :class="['CHO_XU_LY', 'WAITING'].includes(request.status) ? 'btn-primary text-white hover-elevate' : request.buttonClass" 
                    @click="updateToComplete(request)" :disabled="request.updating">
                    <i class="bi bi-check-circle-fill fs-5" v-if="!request.updating"></i>
                    <span class="spinner-border spinner-border-sm" v-else role="status" aria-hidden="true"></span>
                    <span>Theo dõi quá trình</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescueRequestAPI } from "../../../services/api";

const BASE_URL = 'http://localhost:8000';

const STATUS_META = {
  CHO_XU_LY: { label: "Chờ xử lý", badge: "bg-info text-dark", button: "btn-outline-info", buttonLabel: "Chờ xử lý" },
  DANG_XU_LY: { label: "Đang xử lý", badge: "bg-warning text-dark", button: "btn-outline-warning", buttonLabel: "Đang xử lý" },
  HOAN_THANH: { label: "Hoàn thành", badge: "bg-success text-white", button: "btn-outline-success", buttonLabel: "Hoàn thành" },
  HUY_BO: { label: "Đã huỷ", badge: "bg-danger text-white", button: "btn-outline-danger", buttonLabel: "Đã huỷ" },
  DONE: { label: "Hoàn thành", badge: "bg-success text-white", button: "btn-outline-success", buttonLabel: "Hoàn thành" },
  PROCESSING: { label: "Đang xử lý", badge: "bg-warning text-dark", button: "btn-outline-warning", buttonLabel: "Đang xử lý" },
  WAITING: { label: "Chờ xử lý", badge: "bg-info text-dark", button: "btn-outline-info", buttonLabel: "Chờ xử lý" },
};

function normalizeText(value, fallback = "") {
  if (value === null || value === undefined) return fallback;
  if (typeof value === "object") {
    return normalizeText(value.ten_danh_muc || value.ten_loai_su_co || value.title || value.name || fallback, fallback);
  }
  return String(value).trim();
}

function formatTime(value) {
  if (!value) return "Không xác định";
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return normalizeText(value, "Không xác định");
  return parsed.toLocaleString("vi-VN", {
    hour: "2-digit",
    minute: "2-digit",
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

function normalizeStatus(status) {
  if (!status) return "CHO_XU_LY";
  return normalizeText(status, "CHO_XU_LY").toUpperCase().replace(/\s+/g, "_");
}

function getStatusMeta(status) {
  const key = normalizeStatus(status);
  return STATUS_META[key] || { label: normalizeText(status, "Không rõ"), badge: "bg-secondary text-white", button: "btn-outline-secondary", buttonLabel: normalizeText(status, "Không rõ") };
}

function getImageUrl(image) {
  const raw = normalizeText(image);
  if (!raw) return null;
  if (/^(https?:|data:)/i.test(raw)) {
    return raw;
  }
  if (raw.startsWith('uploads/') || raw.startsWith('/uploads/')) {
    return BASE_URL + (raw.startsWith('/') ? '' : '/') + raw;
  }
  return null;
}

function parseRequests(payload) {
  const rawData = payload;
  const items = Array.isArray(rawData)
    ? rawData
    : Array.isArray(rawData?.data)
      ? rawData.data
      : Array.isArray(rawData?.data?.data)
        ? rawData.data.data
        : [];

  return items.map((item) => {
    const id = item.id_yeu_cau || item.id || item.request_id || "-";
    const statusMeta = getStatusMeta(item.trang_thai || item.status);
    const type = normalizeText(item.loai_su_co?.ten_danh_muc || item.loai_su_co?.ten_loai || item.loai || "Không rõ");
    const chiTiet = normalizeText(item.chi_tiet || item.chiTiet || item.chi_tiet_su_co || "");
    const address = normalizeText(item.vi_tri_dia_chi || item.dia_chi || "Không có địa chỉ");
    const time = formatTime(item.thoi_gian_gui || item.created_at || item.updated_at || item.thoi_gian || item.time);
    const priority = normalizeText(item.muc_do_khan_cap || item.diem_uu_tien || "Không xác định");

    return {
      key: `${id}-${Math.random()}`,
      id,
      type,
      chiTiet,
      address,
      time,
      priorityLabel: priority,
      status: normalizeStatus(item.trang_thai || item.status),
      statusLabel: statusMeta.label,
      statusClass: statusMeta.badge,
      buttonClass: statusMeta.button,
      buttonLabel: statusMeta.buttonLabel,
      imageUrl: getImageUrl(item.hinh_anh),
      raw: item,
      updating: false,
    };
  });
}

export default {
  name: "AdminDangXuLy",
  data() {
    return {
      requests: [],
      searchQuery: "",
      searchType: "",
      searchDate: "",
      loading: false,
      error: "",
    };
  },
  computed: {
    uniqueTypes() {
      const types = this.requests.map(r => r.type).filter(t => t && t !== "Không rõ");
      return [...new Set(types)];
    },
  },
  async created() {
    await this.loadRequests();
  },
  methods: {
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
    async loadRequests() {
      this.loading = true;
      this.error = "";
      try {
        const response = await rescueRequestAPI.getList();
        let all = parseRequests(response?.data || response);
        all = all.filter(r => r.status === "DANG_XU_LY" || r.status === "PROCESSING");

        if (this.searchQuery) {
          const q = this.searchQuery.toLowerCase();
          all = all.filter(r =>
            r.id?.toString().includes(q) ||
            r.address?.toLowerCase().includes(q) ||
            r.type?.toLowerCase().includes(q)
          );
        }
        if (this.searchType) {
          all = all.filter(r => r.type === this.searchType);
        }
        if (this.searchDate) {
          all = all.filter(r => {
            const rawTime = r.raw?.thoi_gian_gui || r.raw?.created_at || r.raw?.updated_at || r.raw?.thoi_gian || r.raw?.time;
            if (!rawTime) return false;
            const d = new Date(rawTime);
            if (Number.isNaN(d.getTime())) return false;
            const dateStr = d.toISOString().slice(0, 10);
            return dateStr === this.searchDate;
          });
        }

        this.requests = all;
      } catch (error) {
        console.error("Không tải được yêu cầu đang xử lý:", error);
        this.error = "Không tải được danh sách yêu cầu. Vui lòng thử lại.";
        this.hienToast("error", this.error);
      } finally {
        this.loading = false;
      }
    },
    onSearchInput() {
      this.loadRequests();
    },
    resetFilters() {
      this.searchQuery = "";
      this.searchType = "";
      this.searchDate = "";
      this.loadRequests();
    },
    async updateToComplete(request) {
      if (!confirm(`Xác nhận đánh dấu yêu cầu SOS-${request.id} đã hoàn thành?`)) {
        return;
      }
      request.updating = true;
      try {
        const id = request.raw.id_yeu_cau || request.raw.id || request.id;
        await rescueRequestAPI.changeStatus(id, { trang_thai: "HOAN_THANH" });
        this.requests = this.requests.filter(r => r.id !== request.id);
        this.hienToast("success", `Đã cập nhật yêu cầu SOS-${request.id} thành Hoàn thành.`);
      } catch (error) {
        console.error("Cập nhật trạng thái thất bại:", error);
        this.hienToast("error", "Không thể cập nhật trạng thái. Vui lòng thử lại.");
        request.updating = false;
      }
    },
  },
};
</script>

<style scoped>
.admin-dang-xu-ly-wrapper {
  background-color: transparent;
  min-height: 100%;
}

/* Header Styles */
.header-section {
  padding-bottom: 1.5rem;
  border-bottom: 2px dashed #e2e8f0;
}

.icon-box {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px -4px rgba(217, 119, 6, 0.4);
}

.page-title {
  color: #0f172a;
  letter-spacing: -0.5px;
}

.page-subtitle {
  font-size: 0.95rem;
  color: #64748b;
}

.btn-refresh {
  background: white;
  color: #d97706;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.75rem 1.25rem;
  font-weight: 600;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.btn-refresh:hover:not(:disabled) {
  background: #fdfaea;
  border-color: #fcd34d;
  color: #b45309;
  transform: translateY(-2px);
  box-shadow: 0 6px 10px -2px rgba(0, 0, 0, 0.05);
}

.spin-animation {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}

/* Search Area */
.search-filter-section {
  background-color: #ffffff;
  border-radius: 16px;
  padding: 1.5rem;
}

.search-filter-section .form-control,
.search-filter-section .form-select {
  border-radius: 8px;
  padding: 0.6rem 1rem;
  border-color: #e2e8f0;
  transition: all 0.2s ease;
}

.search-filter-section .form-control:focus,
.search-filter-section .form-select:focus {
  border-color: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
}

.search-filter-section .input-group-text {
  border-color: #e2e8f0;
  background-color: #f8fafc;
}

/* Card Styles */
.incident-card {
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: white;
  border: 1px solid #f1f5f9 !important;
  overflow: hidden;
}

.incident-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
  border-color: #e2e8f0 !important;
}

/* Image Area */
.image-container {
  min-height: 280px;
}

@media (min-width: 576px) {
  .image-container {
    background: #f8fafc;
    border-right: 1px solid #f1f5f9;
  }
}

.incident-img {
  transition: transform 0.6s ease;
}

.incident-card:hover .incident-img {
  transform: scale(1.08);
}

.priority-badge {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 700;
  letter-spacing: 0.3px;
  backdrop-filter: blur(8px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  font-size: 0.8rem;
}

/* Content Area */
.id-badge {
  font-size: 0.88rem;
  letter-spacing: 0.5px;
  border: 1px dashed #cbd5e1;
}

.status-badge {
  font-size: 0.8rem;
  display: flex;
  align-items: center;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.incident-title {
  color: #0f172a;
  line-height: 1.4;
  font-size: 1.3rem;
  letter-spacing: -0.3px;
}

/* Info List */
.info-icon {
  width: 42px;
  height: 42px;
  min-width: 42px;
  font-size: 1.2rem;
  transition: all 0.2s ease;
}

.incident-card:hover .info-icon {
  transform: scale(1.1);
}

.bg-blue-light { background: #eff6ff; }
.bg-red-light { background: #fef2f2; }
.bg-yellow-light { background: #fffdeb; }
.text-warning { color: #d97706 !important; }

.info-label {
  letter-spacing: 0.8px;
  font-size: 0.72rem;
}

.info-value {
  font-size: 0.95rem;
  line-height: 1.5;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Action Button */
.action-btn {
  border-radius: 12px;
  padding: 0.8rem 1rem;
  transition: all 0.2s ease;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  border-width: 2px;
}

.action-btn.hover-elevate:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3), 0 4px 6px -2px rgba(245, 158, 11, 0.15);
  background-color: var(--bs-btn-hover-bg, #f59e0b);
  border-color: var(--bs-btn-hover-border-color, #f59e0b);
  color: white;
}

.action-btn:hover:not(:disabled):not(.hover-elevate) {
  background-color: var(--bs-btn-hover-bg, #28a745);
  border-color: var(--bs-btn-hover-border-color, #28a745);
  color: #fff !important;
}

/* Loading & Empty States */
.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #fef3c7;
  border-top-color: #f59e0b;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.custom-alert-danger {
  background-color: #fef2f2;
  color: #991b1b;
  border-left: 5px solid #ef4444 !important;
}
</style>