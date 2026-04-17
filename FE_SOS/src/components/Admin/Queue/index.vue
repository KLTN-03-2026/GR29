<template>
  <div class="dashboard-container py-4 px-3 px-md-4 h-100">
    <div class="header-section d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div class="title-wrapper d-flex align-items-center gap-3">
        <div class="icon-box bg-white text-primary border border-primary-subtle shadow-sm flex-shrink-0">
          <i class="fa-solid fa-layer-group fs-4"></i>
        </div>
        <div>
          <h2 class="mb-1 fw-bolder page-title text-dark">Hàng Đợi Cứu Hộ</h2>
          <p class="text-muted mb-0 page-subtitle fw-medium">Danh sách các sự cố khẩn cấp đang chờ phản hồi</p>
        </div>
      </div>
      <button class="btn btn-light bg-white border border-light-subtle shadow-sm fw-bolder d-flex align-items-center gap-2 align-self-md-auto align-self-start px-4 py-2" @click="loadRequests" :disabled="loading">
        <i class="fa-solid fa-arrow-rotate-right text-primary" :class="{ 'spin': loading }"></i>
        <span>Đồng bộ</span>
      </button>
    </div>

    <!-- Alerts and Loading States -->
    <div v-if="error" class="alert custom-alert-danger mb-4 shadow-sm border-0 d-flex align-items-center gap-3 rounded-3">
      <i class="fa-solid fa-triangle-exclamation fs-4"></i>
      <div class="fw-medium">{{ error }}</div>
    </div>

    <div v-if="loading" class="loading-state py-5 text-center d-flex flex-column align-items-center gap-3">
      <div class="spinner"></div>
      <div class="text-muted fw-medium fs-6">Đang tải dữ liệu hiện trường...</div>
    </div>

    <div v-if="!loading && requests.length === 0" class="empty-state py-5 my-4 text-center d-flex flex-column align-items-center justify-content-center bg-white rounded-4 border border-light-subtle shadow-sm mx-auto" style="max-width: 600px;">
      <div class="empty-icon text-success bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
        <i class="fa-solid fa-check-double fs-1"></i>
      </div>
      <h4 class="fw-bolder text-dark mb-2">Hàng đợi trống</h4>
      <p class="text-muted mb-0 fw-medium">Tất cả yêu cầu đã được tiếp nhận và xử lý.</p>
    </div>

    <!-- Requests List -->
    <div class="row g-4" v-if="!loading && requests.length > 0">
      <div v-for="request in requests" :key="request.key" class="col-xl-3 col-lg-4 col-md-6">
        <div class="incident-card card panel-card h-100 flex-column overflow-hidden">
          <!-- Image Area (Top) -->
          <div class="image-wrapper position-relative bg-light">
            <img v-if="request.imageUrl" :src="request.imageUrl" class="incident-img w-100 h-100 object-fit-cover position-absolute top-0 start-0" alt="Hình ảnh sự cố" crossorigin="anonymous" />
            <div v-else class="empty-image w-100 h-100 position-absolute d-flex flex-column align-items-center justify-content-center text-muted" style="top:0; left:0;">
              <i class="fa-regular fa-image fs-1 mb-2 opacity-25"></i>
              <span class="small fw-medium opacity-50">Không đính kèm ảnh</span>
            </div>
            
            <!-- Outstanding Emergency Badge Overlay -->
            <div class="position-absolute top-0 start-0 m-3 z-1">
              <span class="level-badge shadow-sm px-3 py-2 fs-6 fw-black text-uppercase" :class="getSeverityBadge(request.mucDoKhanCap)">
                {{ request.severityLabel }}
              </span>
            </div>
            
            <div class="gradient-overlay position-absolute bottom-0 start-0 w-100 p-3 pt-5 d-flex justify-content-between align-items-end z-1">
              <span class="id-tag text-white bg-dark bg-opacity-75 px-2 py-1 rounded small fw-bold shadow-sm">
                #{{ request.id }}
              </span>
            </div>
          </div>

          <!-- Content Area -->
          <div class="card-body p-4 d-flex flex-column bg-white h-100">
            <h5 class="incident-title fw-bolder mb-2 text-dark">{{ request.type }}</h5>
            
            <div class="d-flex align-items-center text-muted small fw-medium mb-3 border-bottom pb-3">
              <i class="fa-regular fa-clock me-1 text-primary"></i> 
              <span class="me-auto">{{ request.time }}</span>
            </div>

            <!-- Details -->
            <div class="info-group d-flex flex-column gap-2 mb-4 flex-grow-1">
              <div class="d-flex align-items-start gap-2">
                <i class="fa-solid fa-location-dot mt-1 text-primary w-15px text-center"></i>
                <div class="text-dark fw-medium text-truncate-2" :title="request.address">{{ request.address }}</div>
              </div>
              <div class="d-flex align-items-start gap-2" v-if="request.description">
                <i class="fa-solid fa-align-left mt-1 text-primary w-15px text-center"></i>
                <div class="text-muted small text-truncate-2" :title="request.description">{{ request.description }}</div>
              </div>
              <div class="d-flex align-items-start gap-2" v-if="request.people > 0">
                <i class="fa-solid fa-users mt-1 text-danger w-15px text-center"></i>
                <div class="text-danger fw-bold small">{{ request.people }} nạn nhân</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="mt-auto pt-3">
              <button 
                class="btn btn-primary btn-action w-100 d-flex align-items-center justify-content-center gap-2 fw-bolder py-2 shadow-sm" 
                @click="navigateToAssignments(request)" :disabled="request.updating"
              >
                <span class="spinner-border spinner-border-sm" v-if="request.updating"></span>
                <template v-else>
                   <i class="fa-solid fa-bolt"></i> Phân Bổ Ngay
                </template>
              </button>
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

const SEVERITY_MAP = {
  'CRITICAL': { label: 'CRITICAL', badge: 'lv-critical' },
  'HIGH':     { label: 'HIGH', badge: 'lv-high' },
  'MEDIUM':   { label: 'MEDIUM', badge: 'lv-medium' },
  'LOW':      { label: 'LOW', badge: 'lv-low' },
};

const SEVERITY_NUM_MAP = {
  4: 'CRITICAL',
  3: 'HIGH',
  2: 'MEDIUM',
  1: 'LOW',
};

function normalizeText(value, fallback = "") {
  if (value === null || value === undefined) return fallback;
  if (typeof value === "object") {
    return normalizeText(value.ten_danh_muc || value.ten_loai_su_co || value.title || value.name || fallback, fallback);
  }
  return String(value).trim();
}

function getSeverityInfo(rawSev) {
  if (!rawSev) return SEVERITY_MAP['MEDIUM'];
  if (isNaN(rawSev)) {
    const upper = String(rawSev).toUpperCase().trim();
    if (upper === 'CRITICAL') return SEVERITY_MAP['CRITICAL'];
    if (upper === 'HIGH') return SEVERITY_MAP['HIGH'];
    if (upper === 'MEDIUM') return SEVERITY_MAP['MEDIUM'];
    if (upper === 'LOW') return SEVERITY_MAP['LOW'];
    return SEVERITY_MAP['MEDIUM'];
  }
  const n = parseInt(rawSev);
  if (n <= 1) return SEVERITY_MAP['LOW'];
  if (n === 2) return SEVERITY_MAP['MEDIUM'];
  if (n === 3) return SEVERITY_MAP['HIGH'];
  return SEVERITY_MAP['CRITICAL'];
}

function formatTime(value) {
  if (!value) return "Không xác định";
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return normalizeText(value, "Không xác định");
  return parsed.toLocaleString("vi-VN", {
    hour: "2-digit", minute: "2-digit", day: "2-digit", month: "2-digit", year: "numeric",
  });
}

function getImageUrl(image) {
  const raw = normalizeText(image);
  if (!raw) return null;
  if (/^(https?:|data:)/i.test(raw)) return raw;
  if (raw.startsWith('uploads/') || raw.startsWith('/uploads/')) {
    return BASE_URL + (raw.startsWith('/') ? '' : '/') + raw;
  }
  return null;
}

function parseRequests(payload) {
  const rawData = payload;
  const items = Array.isArray(rawData) ? rawData : Array.isArray(rawData?.data) ? rawData.data : Array.isArray(rawData?.data?.data) ? rawData.data.data : [];

  return items.map((item) => {
    const id = item.id_yeu_cau || item.id || item.request_id || "-";
    const type = normalizeText(item.loai_su_co?.ten_danh_muc || item.loai_su_co?.ten_loai || item.loai || "Sự cố khẩn cấp");
    const description = normalizeText(item.mo_ta || item.moTa || "Không có mô tả chi tiết");
    const address = normalizeText(item.vi_tri_dia_chi || item.dia_chi || "Không xác định vị trí");
    const time = formatTime(item.thoi_gian_gui || item.created_at || item.updated_at || item.thoi_gian || item.time);
    const people = parseInt(item.so_nguoi_bi_anh_huong) || 0;
    
    // Determine severity
    const sevValue = item.muc_do_khan_cap || item.muc_do || item.diem_uu_tien || 2;
    const sevInfo = getSeverityInfo(sevValue);

    return {
      key: `${id}-${Math.random()}`,
      id,
      type,
      description,
      address,
      time,
      people,
      mucDoKhanCap: sevValue,
      severityLabel: sevInfo.label,
      status: String(item.trang_thai || item.status || 'CHO_XU_LY').toUpperCase().replace(/\s+/g, '_'),
      imageUrl: getImageUrl(item.hinh_anh),
      raw: item,
      updating: false,
    };
  });
}

export default {
  name: "AdminQueue",
  data() {
    return {
      requests: [],
      loading: false,
      error: "",
    };
  },
  async created() {
    await this.loadRequests();
  },
  methods: {
    getSeverityBadge(sev) {
      return getSeverityInfo(sev).badge;
    },
    hienToast(type, message) {
      if (this.$toast?.[type]) {
        this.$toast[type](message, { position: "top-right", duration: 3500 });
        return;
      }
      alert(message);
    },
    async loadRequests() {
      this.loading = true;
      this.error = "";
      try {
        const response = await rescueRequestAPI.getList();
        const all = parseRequests(response?.data || response);
        // Only show waiting requests in the queue
        this.requests = all.filter(r => r.status === 'CHO_XU_LY' || r.status === 'WAITING' || r.status === 'MOI');
      } catch (error) {
        console.error("Không tải được yêu cầu:", error);
        this.error = "Mất kết nối máy chủ. Thử lại sau.";
        this.hienToast("error", this.error);
      } finally {
        this.loading = false;
      }
    },
    navigateToAssignments(request) {
      const id = request.id;
      if (!id) return;
      this.$router.push({ path: '/admin/assignments', query: { id } });
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.dashboard-container {
  background-color: #f8fafc;
  min-height: calc(100vh - 60px);
  font-family: 'Inter', sans-serif;
}

.title-wrapper .icon-box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.panel-card {
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.03), 0 2px 4px -2px rgb(0 0 0 / 0.03) !important;
  transition: all 0.2s ease;
}

.incident-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05) !important;
  border-color: #cbd5e1;
}

.image-wrapper {
  height: 180px;
  width: 100%;
  border-bottom: 1px solid #f1f5f9;
}

.gradient-overlay {
  background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 100%);
}

.w-15px { width: 16px; display: inline-block; }

.fw-black { font-weight: 900; }

.level-badge {
  border-radius: 8px;
  letter-spacing: 0.5px;
}
.lv-critical { background: #7f1d1d; color: white; }
.lv-high { background: #dc2626; color: white; }
.lv-medium { background: #f59e0b; color: white; }
.lv-low { background: #16a34a; color: white; }

.btn-action {
  border-radius: 12px;
  transition: all 0.2s;
  background-color: #2563eb;
  border-color: #2563eb;
}
.btn-action:hover:not(:disabled) {
  background-color: #1d4ed8;
  border-color: #1d4ed8;
  box-shadow: 0 4px 12px rgba(37,99,235,0.2) !important;
  transform: translateY(-1px);
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.custom-alert-danger {
  background-color: #fef2f2;
  color: #991b1b;
  border-left: 5px solid #ef4444 !important;
}

.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
.spinner {
  width: 44px;
  height: 44px;
  border: 3px solid #e0e7ff;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
</style>
