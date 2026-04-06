<template>
  <div class="request-page min-vh-100 p-4 p-md-5">
    <div class="mb-5">
      <h1 class="fw-bold page-title">Yêu cầu của bạn</h1>
      <p class="page-subtitle text-uppercase small fw-bold tracking-wider">THEO DÕI CÁC TÍN HIỆU KHẨN CẤP CỦA BẠN</p>
    </div>

    <div v-if="loading" class="request-state-card">
      <div class="spinner-border text-danger" role="status"></div>
      <p class="mb-0 mt-3 text-muted fw-semibold">Đang tải danh sách yêu cầu...</p>
    </div>

    <div v-else-if="error" class="request-state-card">
      <div class="state-icon bg-danger-subtle text-danger">
        <i class="bi bi-exclamation-octagon"></i>
      </div>
      <p class="mb-0 mt-3 text-muted fw-semibold">{{ error }}</p>
    </div>

    <div v-else-if="danhsach.length === 0" class="request-state-card">
      <div class="state-icon bg-primary-subtle text-primary">
        <i class="bi bi-inbox"></i>
      </div>
      <p class="mb-1 mt-3 fw-bold text-dark">Chưa có yêu cầu đang xử lý</p>
      <p class="mb-0 text-muted">Các yêu cầu đã hoàn thành hoặc đã hủy sẽ được chuyển sang lịch sử.</p>
    </div>

    <div v-else class="row g-4">
      <div v-for="item in danhsach" :key="item.id" class="col-12 col-md-6 col-xl-4">
        <div class="request-card card h-100 border-0 rounded-4 overflow-hidden">
          <div class="request-card__glow"></div>
          <div class="card-body p-4 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
              <div>
                <span class="request-code">SOS-{{ item.id }}</span>
                <p class="request-label mb-0 mt-2">Đang theo dõi cứu hộ</p>
              </div>
              <span :class="['badge rounded-pill px-3 py-2 fw-semibold request-badge', item.statusBg]">
                <i class="bi bi-circle-fill me-2 request-badge__dot"></i>{{ item.statusText }}
              </span>
            </div>

            <div class="d-flex align-items-start gap-3 mb-4">
              <!-- <div :class="['request-icon d-flex align-items-center justify-content-center flex-shrink-0', item.iconBg]">
                <i :class="['bi fs-4', item.icon, item.iconColor]"></i>
              </div> -->
              <div class="flex-grow-1">
                <h5 class="fw-bold mb-2 request-type">{{ item.loai }}</h5>
                <div class="request-meta">
                  <i class="bi bi-clock-history"></i>
                  <span>{{ item.time }}</span>
                </div>
              </div>
            </div>

            <div v-if="item.chiTiet" class="request-detail mb-4">
              <div class="request-detail__label">Chi tiết sự cố</div>
              <div class="request-detail__content">{{ item.chiTiet }}</div>
            </div>

            <div class="request-location mb-4">
              <div class="request-meta align-items-start">
                <i class="bi bi-geo-alt-fill text-danger mt-1"></i>
                <span>{{ item.address }}</span>
              </div>
            </div>

            <div class="mt-auto pt-2">
              <button
                v-if="item.canCancel"
                class="btn request-action-btn request-action-btn--danger w-100 d-flex align-items-center justify-content-center gap-2"
                @click="huyYeuCau(item)"
              >
                <i class="bi bi-x-circle"></i>
                Hủy yêu cầu
              </button>
              <button
                v-else
                class="btn request-action-btn request-action-btn--muted w-100 d-flex align-items-center justify-content-center gap-2"
                disabled
              >
                <i class="bi bi-check-circle"></i>
                Không thể hủy
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

const STATUS_META = {
  hoan_thanh: { label: "Hoàn thành", badge: "bg-success text-white" },
  dang_xu_ly: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  cho_xu_ly: { label: "Chờ xử lý", badge: "bg-info text-dark" },
  huy_bo: { label: "Đã hủy", badge: "bg-danger text-white" },
  processing: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  waiting: { label: "Chờ xử lý", badge: "bg-info text-dark" },
  done: { label: "Hoàn thành", badge: "bg-success text-white" },
};

const TYPE_ICON = {
  "y te": { icon: "bi-heart-pulse-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  "y tế": { icon: "bi-heart-pulse-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  "chay no": { icon: "bi-flame-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  "cháy nổ": { icon: "bi-flame-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  chay: { icon: "bi-flame-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  "cháy": { icon: "bi-flame-fill", color: "text-danger", bg: "bg-danger bg-opacity-10" },
  lu: { icon: "bi-water", color: "text-primary", bg: "bg-info bg-opacity-10" },
  "lũ": { icon: "bi-water", color: "text-primary", bg: "bg-info bg-opacity-10" },
  "lu lut": { icon: "bi-water", color: "text-primary", bg: "bg-info bg-opacity-10" },
  "lũ lụt": { icon: "bi-water", color: "text-primary", bg: "bg-info bg-opacity-10" },
  "song than": { icon: "bi-brightness-high-fill", color: "text-info", bg: "bg-info bg-opacity-10" },
  "sóng thần": { icon: "bi-brightness-high-fill", color: "text-info", bg: "bg-info bg-opacity-10" },
  "han han": { icon: "bi-sun-fill", color: "text-warning", bg: "bg-warning bg-opacity-10" },
  "hạn hán": { icon: "bi-sun-fill", color: "text-warning", bg: "bg-warning bg-opacity-10" },
  "tai nan": { icon: "bi-geo-alt-fill", color: "text-warning", bg: "bg-warning bg-opacity-10" },
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
  const candidates = [parsed, parsed.data, parsed.user];
  const keys = ["id_nguoi_dung", "id", "user_id"];
  for (const item of candidates) {
    if (!item || typeof item !== "object") continue;
    for (const key of keys) {
      const value = item[key];
      if (value !== undefined && value !== null && value !== "") {
        return Number(value);
      }
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

function canCancelRequest(rawStatus) {
  const key = normalizeStatusKey(rawStatus);
  return !["huy_bo", "hoan_thanh", "done"].includes(key);
}

function isHistoryStatus(rawStatus) {
  const key = normalizeStatusKey(rawStatus);
  return ["huy_bo", "hoan_thanh", "done"].includes(key);
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

export default {
  data() {
    return {
      danhsach: [],
      loading: false,
      error: "",
    };
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
    normalizeRequests(items) {
      if (!Array.isArray(items)) {
        return [];
      }
      return items.map((item) => {
        const id = item.id || item.id_yeu_cau || item.ma_yeu_cau || item.request_id || "";
        const typeLabel = normalizeValue(
          item.ten_loai_su_co || item.loai_su_co || item.loai || item.ten_danh_muc || item.type || "Không rõ"
        );
        const incidentDetail = normalizeValue(
          item.chi_tiet ||
          item.chi_tiet_su_co ||
          item.mo_ta_chi_tiet ||
          item.detail ||
          item.details ||
          ""
        );
        const rawStatus = item.trang_thai || item.status || item.statusText || "CHO_XU_LY";
        const statusMeta = parseStatus(rawStatus);
        const typeMeta = parseTypeIcon(typeLabel);
        const address = normalizeValue(
          item.vi_tri_dia_chi || item.dia_chi || item.address || `${item.vi_tri_lat || ""}, ${item.vi_tri_lng || ""}`
        );
        const time = formatTime(item.created_at || item.ngay_tao || item.thoi_gian || item.updated_at || item.time);

        return {
          id,
          loai: typeLabel,
          chiTiet: incidentDetail,
          time: time || "Không xác định",
          address: address || "Chưa có địa chỉ",
          statusText: statusMeta.label,
          statusBg: statusMeta.badge,
          canCancel: canCancelRequest(rawStatus),
          icon: typeMeta.icon,
          iconColor: typeMeta.color,
          iconBg: typeMeta.bg,
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
        const activeRequests = filtered.filter((item) => !isHistoryStatus(item.trang_thai || item.status || item.statusText));
        this.danhsach = this.normalizeRequests(activeRequests);

        if (filtered.length > 0 && activeRequests.length === 0) {
          this.$router.push("/client/history");
        }
      } catch (error) {
        console.error("Không tải được danh sách yêu cầu:", error);
        this.error = "Không tải được yêu cầu. Vui lòng thử lại.";
        this.hienToast("error", this.error);
      } finally {
        this.loading = false;
      }
    },
    async huyYeuCau(item) {
      const requestId = item?.id;
      if (!requestId) {
        this.hienToast("warning", "Không xác định được yêu cầu cần hủy.");
        return;
      }
      const confirmed = window.confirm(`Bạn có chắc muốn hủy yêu cầu #${requestId}?`);
      if (!confirmed) {
        return;
      }
      try {
        try {
          await rescueRequestAPI.changeStatus(requestId, { trang_thai: "HUY_BO" });
        } catch (error) {
          await rescueRequestAPI.changeStatus(requestId, { status: "HUY_BO" });
        }
        this.hienToast("success", "Hủy yêu cầu thành công.");
        await this.loadRequests();
      } catch (error) {
        console.error("Hủy yêu cầu thất bại:", error);
        const message = error?.response?.data?.message || "Không thể hủy yêu cầu. Vui lòng thử lại.";
        this.hienToast("error", message);
      }
    },
  },
};
</script>

<style scoped>
.request-page {
  background:
    radial-gradient(circle at top left, rgba(225, 91, 79, 0.12), transparent 28%),
    radial-gradient(circle at top right, rgba(26, 42, 64, 0.08), transparent 26%),
    #f6f8fb;
}

.page-title {
  color: #1a2a40;
  letter-spacing: -0.03em;
}

.page-subtitle {
  color: #7a8594;
  letter-spacing: 0.18em;
}

.request-card {
  position: relative;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(10px);
  box-shadow: 0 20px 45px rgba(22, 34, 51, 0.08);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.request-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 26px 55px rgba(22, 34, 51, 0.14);
}

.request-card__glow {
  position: absolute;
  inset: 0 auto auto 0;
  width: 100%;
  height: 5px;
  background: linear-gradient(90deg, #e15b4f 0%, #f1b24a 50%, #5b8def 100%);
}

.request-code {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.8rem;
  border-radius: 999px;
  background: #f4f7fb;
  border: 1px solid #d9e2ef;
  color: #506176;
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.06em;
}

.request-label {
  color: #97a3b6;
  font-size: 0.78rem;
  font-weight: 600;
}

.request-badge {
  white-space: nowrap;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
}

.request-badge__dot {
  font-size: 0.45rem;
}

.request-icon {
  width: 58px;
  height: 58px;
  border-radius: 18px;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
}

.request-type {
  color: #17263c;
  line-height: 1.3;
}

.request-meta {
  display: flex;
  gap: 0.6rem;
  color: #66768b;
  font-size: 0.92rem;
}

.request-location {
  padding: 0.95rem 1rem;
  border-radius: 18px;
  background: #f8fafc;
  border: 1px solid #e6edf5;
}

.request-detail {
  padding: 0.95rem 1rem;
  border-radius: 18px;
  background: linear-gradient(180deg, #fff8f3 0%, #ffffff 100%);
  border: 1px solid #f2e4d9;
}

.request-detail__label {
  margin-bottom: 0.35rem;
  color: #a26d49;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.request-detail__content {
  color: #4f5e73;
  font-size: 0.94rem;
  line-height: 1.5;
}

.request-action-btn {
  min-height: 48px;
  border-radius: 14px;
  font-weight: 700;
  transition: all 0.2s ease;
}

.request-action-btn--danger {
  background: linear-gradient(135deg, #fdf1f0 0%, #fff 100%);
  border: 1px solid rgba(225, 91, 79, 0.28);
  color: #cc4d43;
}

.request-action-btn--danger:hover {
  background: #e15b4f;
  border-color: #e15b4f;
  color: #fff;
}

.request-action-btn--muted {
  background: #edf2f7;
  border: 1px solid #d8e0ea;
  color: #6f7d8f;
}

.request-state-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 240px;
  padding: 2rem;
  border-radius: 28px;
  background: rgba(255, 255, 255, 0.82);
  border: 1px solid rgba(220, 228, 238, 0.95);
  box-shadow: 0 18px 40px rgba(22, 34, 51, 0.06);
  text-align: center;
}

.state-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  border-radius: 20px;
  font-size: 1.5rem;
}

@media (max-width: 767.98px) {
  .request-page {
    padding: 1.25rem !important;
  }

  .request-card:hover {
    transform: none;
  }
}
</style>
