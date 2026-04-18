<template>
  <div class="bg-light min-vh-100 py-4 py-md-5 px-3 px-md-5" style="background: #f8f9fa;">
    <div class="container-fluid px-0">
      <!-- Header Section -->
      <div class="mb-5 d-flex justify-content-between align-items-end flex-wrap gap-3">
        <div>
          <h1 class="fw-bold font-headline tracking-tight mb-2" style="color: #111827; font-size: 2.5rem;">
            Theo Dõi Tiến Độ
          </h1>
          <p class="text-secondary text-uppercase small fw-bold tracking-wider mb-0 flex-grow-1">
            <span class="pulse-dot me-2"></span>CÁC YÊU CẦU ĐANG TRONG QUÁ TRÌNH XỬ LÝ
          </p>
        </div>
        
        <!-- Thanh tìm kiếm -->
        <div class="position-relative" style="min-width: 280px;">
          <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3"
             style="color: #6b7280; font-size: 1rem;"></i>
          <input
            type="text"
            class="form-control ps-5 py-2.5 rounded-pill border-0 shadow-sm"
            style="font-size: 0.95rem; background: #ffffff;"
            placeholder="Tìm theo ID, loại sự cố..."
            v-model="searchQuery"
          >
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="d-flex flex-column align-items-center justify-content-center py-5" style="min-height: 40vh;">
        <div class="spinner-grow text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
          <span class="visually-hidden">Đang tải...</span>
        </div>
        <h5 class="fw-bold text-dark">Đang đồng bộ dữ liệu...</h5>
        <p class="text-secondary">Vui lòng đợi trong giây lát</p>
      </div>

      <!-- Không có yêu cầu đang xử lý -->
      <div v-else-if="filteredList.length === 0" class="d-flex flex-column align-items-center justify-content-center py-5 text-center" style="min-height: 40vh;">
        <div class="empty-state-icon mb-4 rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
          <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
        </div>
        <h3 class="fw-bold text-dark mb-2">Bạn đang an toàn!</h3>
        <p class="text-secondary mb-4" style="max-width: 400px;">
          Hiện tại bạn không có yêu cầu cứu hộ nào đang chờ hoặc đang được xử lý.
        </p>
        <button class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm" @click="$router.push('/gui-yeu-cau')">
          <i class="bi bi-plus-circle me-2"></i>Tạo Yêu Cầu Mới
        </button>
      </div>

      <!-- Danh sách yêu cầu đang xử lý -->
      <div v-else class="row g-4">
        <div v-for="item in filteredList" :key="item.id" class="col-12 col-xl-6">
          <div class="active-card rounded-4 p-4 position-relative overflow-hidden bg-white shadow-sm h-100 d-flex flex-column flex-sm-row gap-4">
            <!-- Dải màu trạng thái bên trái -->
            <div class="position-absolute top-0 bottom-0 start-0" :style="{ width: '5px', backgroundColor: item.trangThaiColor }"></div>

            <!-- Cột Trái: Ảnh & Icons -->
            <div class="shrink-0 position-relative">
              <div class="rounded-4 overflow-hidden position-relative shadow-sm" style="width: 150px; height: 150px;">
                <img v-if="item.anh_hien_truong"
                     :src="item.anh_hien_truong"
                     class="w-100 h-100 object-fit-cover"
                     alt="Ảnh hiện trường">
                <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center" :style="{ backgroundColor: item.iconBg }">
                  <i :class="['bi fs-1', item.icon]" :style="{ color: item.iconColor }"></i>
                </div>
              </div>
              <div class="position-absolute top-0 start-0 translate-middle badge rounded-pill shadow-sm bg-white border" style="padding: 0.4rem;">
                <i :class="['bi fs-5', item.icon]" :style="{ color: item.iconColor }"></i>
              </div>
            </div>

            <!-- Cột Phải: Nội Dung & Tiến Độ -->
            <div class="d-flex flex-column flex-grow-1 justify-content-between py-1">
              <div>
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span class="badge bg-light text-dark border px-2 py-1 rounded-2 fw-bold font-monospace me-2">#{{ item.id }}</span>
                    <span class="badge rounded-pill fw-bold text-white px-3 py-1 bg-primary d-inline-flex gap-2 align-items-center shadow-sm" :style="{ backgroundColor: item.trangThaiColor + ' !important' }">
                      <span class="pulse-dot-small bg-white"></span>
                      {{ item.trangThaiText }}
                    </span>
                  </div>
                  <span class="text-secondary small fw-medium" style="font-size: 0.8rem;">
                    <i class="bi bi-clock me-1"></i>{{ item.time }}
                  </span>
                </div>

                <h3 class="fw-bold mb-2 text-dark" style="font-size: 1.35rem; font-family: 'Manrope', sans-serif;">
                  {{ item.loai }}
                </h3>

                <div class="d-flex align-items-center gap-2 text-secondary mb-3">
                  <div class="bg-light p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                     <i class="bi bi-geo-alt-fill text-danger" style="font-size: 0.85rem;"></i>
                  </div>
                  <span class="fw-medium text-truncate" style="font-size: 0.9rem; max-width: 300px;">{{ item.address }}</span>
                </div>
              </div>

              <!-- Thanh Tiến Độ -->
              <div class="mt-auto">
                <div class="progress mb-2 rounded-pill" style="height: 8px; background-color: #f1f5f9;">
                  <div class="progress-bar progress-bar-striped progress-bar-animated rounded-pill" 
                       role="progressbar" 
                       :style="{ width: item.progress + '%', backgroundColor: item.trangThaiColor }"></div>
                </div>
                <div class="d-flex justify-content-between text-secondary small fw-bold" style="font-size: 0.75rem;">
                  <span>Đã tiếp nhận</span>
                  <span>Đội cứu hộ đã tới</span>
                  <span>Vấn đề đang được giải quyết</span>
                </div>
              </div>
              
              <!-- Buttons -->
              <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button class="btn btn-outline-secondary rounded-pill px-4 fw-bold flex-grow-1 flex-sm-grow-0" style="font-size: 0.85rem;" @click="showDetailModal(item)">
                  <i class="bi bi-info-circle me-1"></i>Chi Tiết
                </button>
                <button class="btn text-white rounded-pill px-4 fw-bold flex-grow-1 flex-sm-grow-0" 
                        style="font-size: 0.85rem;"
                        :style="{ backgroundColor: item.trangThaiColor, border: 'none' }"
                        disabled>
                  <i class="bi bi-telephone me-1"></i>Liên hệ Đội Cứu Hộ
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Chi tiết -->
    <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="max-width: 650px;">
        <!-- Modal Header -->
        <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center sticky-top">
          <h4 class="fw-bold mb-0 text-dark" style="font-family: 'Manrope', sans-serif;">
            <i class="bi bi-file-earmark-text text-primary me-2"></i>Chi Tiết Yêu Cầu Khẩn Cấp
          </h4>
          <button class="btn-close shadow-none" @click="closeModal"></button>
        </div>

        <!-- Modal Body -->
        <div class="p-4" style="background-color: #f9fafb;">
          
          <!-- Request ID & Map Preview Placeholder -->
          <div class="bg-white p-3 rounded-4 shadow-sm mb-4 border d-flex gap-3 align-items-center">
            <div class="map-placeholder rounded-3 bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
               <i class="bi bi-map text-secondary fs-3"></i>
            </div>
            <div>
               <div class="fw-bold font-monospace bg-light border px-2 py-1 rounded d-inline-block mb-1">Mã Yêu Cầu: #{{ selectedItem?.id }}</div>
               <div class="fw-bold fs-5 text-dark">{{ selectedItem?.loai }}</div>
            </div>
          </div>

          <div class="row g-4 mb-4">
            <div class="col-sm-6">
              <div class="bg-white p-3 rounded-4 shadow-sm border h-100">
                <label class="fw-bold text-secondary text-uppercase mb-2 small d-flex align-items-center">
                  <i class="bi bi-clock-history me-2"></i>Trạng thái hiện tại
                </label>
                <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                  <span class="pulse-dot-small" :style="{ backgroundColor: selectedItem?.trangThaiColor }"></span>
                  {{ selectedItem?.trangThaiText }}
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="bg-white p-3 rounded-4 shadow-sm border h-100">
                <label class="fw-bold text-secondary text-uppercase mb-2 small d-flex align-items-center">
                  <i class="bi bi-calendar-event me-2"></i>Gửi lúc
                </label>
                <div class="fw-bold text-dark fs-6">{{ selectedItem?.time }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white p-3 rounded-4 shadow-sm border mb-4">
            <label class="fw-bold text-secondary text-uppercase mb-2 small d-flex align-items-center">
              <i class="bi bi-geo-alt me-2"></i>Vị Trí Cần Cứu Hộ
            </label>
            <div class="fw-medium text-dark fs-6 lh-base">{{ selectedItem?.address }}</div>
          </div>

          <div class="bg-white p-3 rounded-4 shadow-sm border mb-4">
            <label class="fw-bold text-secondary text-uppercase mb-2 small d-flex align-items-center">
              <i class="bi bi-card-text me-2"></i>Mô Tả Sự Cố
            </label>
            <div class="fw-medium text-dark fs-6 lh-base" style="white-space: pre-line;">
              {{ selectedItem?.moTa || 'Không có mô tả chi tiết.' }}
            </div>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="p-3 bg-white border-top d-flex justify-content-end gap-2">
          <button class="btn btn-light rounded-pill px-4 fw-bold border" @click="closeModal">
            Đóng Lại
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { rescueRequestAPI } from "../../../services/api";

const BASE_URL = "http://localhost:8000";

const TYPE_ICON = {
  "y te": { icon: "bi-heart-pulse-fill", color: "#dc3545", bg: "#f8d7da" },
  "chay no": { icon: "bi-fire", color: "#dc3545", bg: "#f8d7da" },
  "chay": { icon: "bi-fire", color: "#dc3545", bg: "#f8d7da" },
  "lu": { icon: "bi-water", color: "#0dcaf0", bg: "#cff4fc" },
  "lu lut": { icon: "bi-water", color: "#0dcaf0", bg: "#cff4fc" },
  "song than": { icon: "bi-tsunami", color: "#0dcaf0", bg: "#cff4fc" },
  "han han": { icon: "bi-sun-fill", color: "#ffc107", bg: "#fff3cd" },
  "tai nan": { icon: "bi-car-front-fill", color: "#ffc107", bg: "#fff3cd" },
  "giao thong": { icon: "bi-cone-striped", color: "#fd7e14", bg: "#ffe5d0" },
  "dong dat": { icon: "bi-house-slash-fill", color: "#6f42c1", bg: "#e0d4f5" },
};

function normalizeValue(value, fallback = "") {
  if (!value) return fallback;
  if (typeof value === "object") {
    return normalizeValue(value.ten_danh_muc || value.ten_loai_su_co || value.ten || value.name || fallback, fallback);
  }
  return String(value);
}

function extractUserId(parsed) {
  if (!parsed || typeof parsed !== "object") return null;
  const keys = ["id_nguoi_dung", "id", "user_id", "ma_nguoi_dung", "nguoi_dung_id"];
  for (const key of keys) {
    if (parsed[key] !== undefined && parsed[key] !== null && parsed[key] !== "") {
      return Number(parsed[key]);
    }
  }
  return null;
}

function getCurrentUserId() {
  const sources = ["user_token", "user", "client"];
  for (const key of sources) {
    const raw = localStorage.getItem(key);
    if (!raw) continue;
    try {
      const parsed = JSON.parse(raw);
      const id = extractUserId(parsed);
      if (id) return id;
    } catch (error) {
      // ignore invalid json
    }
  }
  return null;
}

function formatTime(value) {
  if (!value) return "";
  const time = new Date(value);
  if (Number.isNaN(time.getTime())) return normalizeValue(value);
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
    if (type.includes(key)) return TYPE_ICON[key];
  }
  return { icon: "bi-exclamation-triangle-fill", color: "#6c757d", bg: "#e2e3e5" };
}

function normalizeStatusCode(value) {
  return String(value || "")
    .trim()
    .toUpperCase()
    .replace(/\s+/g, "_");
}

function buildStatusMeta(requestStatus, assignmentStatus = "") {
  const req = normalizeStatusCode(requestStatus);
  const mission = normalizeStatusCode(assignmentStatus);
  const status = mission || req;

  if (status === "HOAN_THANH" || status === "DONE") {
    return { text: "Đã hoàn thành", color: "#22c55e", progress: 100 };
  }
  if (status === "DA_DEN_HIEN_TRUONG") {
    return { text: "Đội cứu hộ đã tới", color: "#2563eb", progress: 70 };
  }
  if (status === "DANG_XU_LY" || status === "PROCESSING") {
    return { text: "Vấn đề đang được giải quyết", color: "#0ea5e9", progress: 90 };
  }
  if (
    status === "DA_PHAN_CONG" ||
    status === "DA_XAC_NHAN" ||
    status === "DA_GIAO_VIEC" ||
    status === "DANG_DI_CHUYEN"
  ) {
    return { text: "Đang điều phối đội cứu hộ", color: "#3b82f6", progress: 45 };
  }
  if (
    status === "CHO_XU_LY" ||
    status === "CHUA_XU_LY" ||
    status === "CHO_XAC_NHAN" ||
    status === "PENDING" ||
    status === "WAITING"
  ) {
    return { text: "Đã tiếp nhận", color: "#f59e0b", progress: 20 };
  }
  return { text: "Đang theo dõi", color: "#6b7280", progress: 30 };
}

function getTrackingRequestId(item) {
  return item?.id_yeu_cau || item?.id || item?.request_id || item?.ma_yeu_cau || null;
}

function buildTrackingMapFromResponse(rawData) {
  const records = Array.isArray(rawData?.data) ? rawData.data : Array.isArray(rawData) ? rawData : [];
  return records.reduce((acc, trackingItem) => {
    const requestId = getTrackingRequestId(trackingItem);
    if (!requestId) return acc;
    acc[String(requestId)] = trackingItem;
    return acc;
  }, {});
}

function getImageUrl(image) {
  if (!image) return null;
  const raw = String(image);
  if (/^(https?:|data:)/i.test(raw)) return raw;
  if (raw.startsWith("uploads/") || raw.startsWith("/uploads/")) {
    return BASE_URL + (raw.startsWith("/") ? "" : "/") + raw;
  }
  return null;
}

export default {
  data() {
    return {
      danhsach: [],
      loading: false,
      searchQuery: "",
      isModalOpen: false,
      selectedItem: null,
      pollInterval: null,
    };
  },
  computed: {
    filteredList() {
      let result = [...this.danhsach];
      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase().trim();
        result = result.filter((item) => {
          const loai = (item.loai || "").toLowerCase();
          const id = String(item.id || "").toLowerCase();
          return loai.includes(query) || id.includes(query);
        });
      }
      return result.sort((a, b) => Number(b.id || 0) - Number(a.id || 0));
    },
  },
  async created() {
    await this.loadActiveRequests();
    this.pollInterval = setInterval(() => {
      this.loadActiveRequests(true);
    }, 10000);
  },
  beforeUnmount() {
    if (this.pollInterval) clearInterval(this.pollInterval);
  },
  methods: {
    normalizeResults(items, trackingMap = {}) {
      if (!Array.isArray(items)) return [];
      return items.map((item) => {
        const id = item.id_yeu_cau || item.id || item.ma_ket_qua || "";
        const tracking = trackingMap[String(id)] || {};

        let typeLabel = "Không rõ";
        if (item.loaiSuCo) {
          typeLabel = normalizeValue(item.loaiSuCo.ten_danh_muc || item.loaiSuCo.ten_loai_su_co || item.loaiSuCo.ten || "Không rõ");
        } else {
          typeLabel = normalizeValue(item.ten_loai_su_co || item.loai_su_co || item.loai || item.chi_tiet || "Không rõ");
        }

        const typeMeta = parseTypeIcon(typeLabel);

        let address = item.vi_tri_dia_chi || item.dia_chi || item.address || "Chưa xác định";
        if (!item.vi_tri_dia_chi && !item.dia_chi && item.vi_tri_lat && item.vi_tri_lng) {
          address = `${item.vi_tri_lat}, ${item.vi_tri_lng}`;
        }

        const time = formatTime(item.created_at || item.thoi_gian || item.time || item.updated_at);
        const anhHienTruong = getImageUrl(item.hinh_anh) || getImageUrl(item.anh_hien_truong) || getImageUrl(item.anh) || getImageUrl(item.image);

        const assignmentStatus =
          tracking?.trang_thai_nhiem_vu ||
          tracking?.phan_congs?.[0]?.trang_thai_nhiem_vu ||
          item?.phan_congs?.[0]?.trang_thai_nhiem_vu ||
          "";
        const requestStatus = tracking?.trang_thai || item.trang_thai || item.status;
        const statusMeta = buildStatusMeta(requestStatus, assignmentStatus);

        const apiProgress = Number(
          tracking?.tien_do ?? tracking?.progress ?? tracking?.phan_tram_hoan_thanh ?? Number.NaN
        );
        const progress = Number.isFinite(apiProgress)
          ? Math.max(0, Math.min(100, apiProgress))
          : statusMeta.progress;

        return {
          id,
          loai: typeLabel,
          moTa: normalizeValue(item.mo_ta || item.moTa || item.description || ""),
          time: time || "Mới đây",
          address,
          icon: typeMeta.icon,
          iconColor: typeMeta.color,
          iconBg: typeMeta.bg,
          anh_hien_truong: anhHienTruong,
          trang_thai: requestStatus,
          trang_thai_nhiem_vu: assignmentStatus,
          trangThaiText: statusMeta.text,
          trangThaiColor: statusMeta.color,
          progress,
          raw: item,
          trackingRaw: tracking,
        };
      });
    },

    async loadActiveRequests(silent = false) {
      if (!silent) this.loading = true;
      try {
        const currentUserId = getCurrentUserId();
        if (!currentUserId) {
          this.danhsach = [];
          return;
        }

        let requestResponse;
        try {
          requestResponse = await rescueRequestAPI.getByUser(currentUserId);
        } catch (apiError) {
          requestResponse = await rescueRequestAPI.getList();
        }

        const rawData = requestResponse?.data;
        const items = Array.isArray(rawData)
          ? rawData
          : Array.isArray(rawData?.data)
          ? rawData.data
          : Array.isArray(rawData?.data?.data)
          ? rawData.data.data
          : [];

        let trackingMap = {};
        try {
          const trackingResponse = await rescueRequestAPI.getTrackingList();
          trackingMap = buildTrackingMapFromResponse(trackingResponse?.data?.data || trackingResponse?.data || []);
        } catch (trackingError) {
          trackingMap = {};
        }

        const activeItems = items.filter((item) => {
          const itemUserId = extractUserId(item) || extractUserId(item?.nguoi_dung);
          if (itemUserId !== currentUserId) return false;

          const id = item.id_yeu_cau || item.id || item.ma_ket_qua || "";
          const tracking = trackingMap[String(id)] || {};

          const reqStatus = normalizeStatusCode(tracking?.trang_thai || item.trang_thai || item.status);
          const missionStatus = normalizeStatusCode(
            tracking?.trang_thai_nhiem_vu || tracking?.phan_congs?.[0]?.trang_thai_nhiem_vu || item?.phan_congs?.[0]?.trang_thai_nhiem_vu
          );

          const closed = new Set(["HOAN_THANH", "DA_HOAN_THANH", "HUY_BO", "DA_HUY", "TU_CHOI", "THAT_BAI", "DONE"]);
          if (closed.has(reqStatus) || closed.has(missionStatus)) return false;

          return true;
        });

        this.danhsach = this.normalizeResults(activeItems, trackingMap);

        if (this.selectedItem?.id) {
          const freshItem = this.danhsach.find((item) => String(item.id) === String(this.selectedItem.id));
          if (freshItem) this.selectedItem = freshItem;
        }
      } catch (error) {
        console.error("Loi khi tai yeu cau dang xu ly:", error);
      } finally {
        if (!silent) this.loading = false;
      }
    },

    showDetailModal(item) {
      this.selectedItem = item;
      this.isModalOpen = true;
      document.body.style.overflow = "hidden";
    },

    closeModal() {
      this.isModalOpen = false;
      this.selectedItem = null;
      document.body.style.overflow = "";
    },
  },
};
</script>

<style scoped>
/* Typography */
h1, h2, h3, h4, h5, .font-headline {
  font-family: 'Manrope', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  letter-spacing: -0.02em;
}

/* Animations */
@keyframes pulse-dot {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

@keyframes pulse-light {
  0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
  70% { box-shadow: 0 0 0 6px rgba(255, 255, 255, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
}

.pulse-dot {
  width: 10px;
  height: 10px;
  background-color: #ef4444;
  border-radius: 50%;
  display: inline-block;
  animation: pulse-dot 2s infinite;
}

.pulse-dot-small {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
  animation: pulse-light 2s infinite;
}

/* Cards */
.active-card {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  border: 1px solid rgba(0,0,0,0.05);
}

.active-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.08) !important;
  border-color: rgba(0,0,0,0.1);
}

.object-fit-cover {
  object-fit: cover;
}

/* Modal styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(17, 24, 39, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}

.modal-content {
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  animation: modal-enter 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modal-enter {
  from { opacity: 0; transform: translateY(20px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Utilities */
.tracking-wider { letter-spacing: 0.05em; }
.tracking-tight { letter-spacing: -0.02em; }
.shrink-0 { flex-shrink: 0; }
</style>
