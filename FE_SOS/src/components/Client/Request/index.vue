<template>
  <div class="bg-light min-vh-100 p-4 p-md-5">
    <div class="mb-5">
      <h1 class="fw-bold" style="color: #1a2a40;"> Yêu cầu của bạn</h1>
      <p class="text-secondary text-uppercase small fw-bold tracking-wider">THEO DÕI CÁC TÍN HIỆU KHẨN CẤP CỦA BẠN</p>
    </div>

    

    <div class="row g-4">
      <div v-for="item in danhsach" :key="item.id" class="col-12 col-md-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 border">SOS-{{ item.id }}</span>
              <span :class="['badge rounded-pill px-3 py-2', item.statusBg]">
                <i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i> {{ item.statusText }}
              </span>
            </div>

            <div class="d-flex align-items-start mb-4">
              <div :class="['rounded-3 p-3 me-3 d-flex align-items-center justify-content-center', item.iconBg]" style="width:56px; height:56px;">
                <i :class="['bi fs-4', item.icon, item.iconColor]"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-1">{{ item.loai }}</h5>
                <p class="text-muted small mb-0">{{ item.time }}</p>
              </div>
            </div>

            <div class="d-flex align-items-center text-muted small mb-4">
              <i class="bi bi-geo-alt me-2 text-danger"></i>
              <span class="text-truncate">{{ item.address }}</span>
            </div>

            <div class="row">
                <div class="col-md-6 lg-6">
                    <button class="btn btn-outline-danger d-flex align-items-center gap-2" @click="huyYeuCau(item)">
                        <i class="bi bi-x-circle"></i>
                        Hủy Yêu Cầu
                    </button>
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

const STATUS_META = {
  hoan_thanh: { label: "Hoàn thành", badge: "bg-success text-white" },
  dang_xu_ly: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  cho_xu_ly: { label: "Chờ xử lý", badge: "bg-info text-dark" },
  huy_bo: { label: "Đã huỷ", badge: "bg-danger text-white" },
  processing: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  waiting: { label: "Chờ xử lý", badge: "bg-info text-dark" },
  done: { label: "Hoàn thành", badge: "bg-success text-white" },
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
        const typeLabel = normalizeValue(item.ten_loai_su_co || item.loai_su_co || item.loai || item.chi_tiet || item.chi_tiet_su_co || "Không rõ");
        const rawStatus = item.trang_thai || item.status || item.statusText || "CHO_XU_LY";
        const statusMeta = parseStatus(rawStatus);
        const typeMeta = parseTypeIcon(typeLabel);
        const address = normalizeValue(item.vi_tri_dia_chi || item.dia_chi || item.address || `${item.vi_tri_lat || ""}, ${item.vi_tri_lng || ""}`);
        const time = formatTime(item.created_at || item.ngay_tao || item.thoi_gian || item.updated_at || item.time);

        return {
          id,
          loai: typeLabel,
          time: time || "Không xác định",
          address: address || "Chưa có địa chỉ",
          statusText: statusMeta.label,
          statusBg: statusMeta.badge,
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
          // fallback nếu API chưa hỗ trợ query theo user
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
        await rescueRequestAPI.delete(requestId);
        this.hienToast("success", "Hủy yêu cầu thành công.");
        await this.loadRequests();
      } catch (error) {
        console.error("Hủy yêu cầu thất bại:", error);
        const message =
          error?.response?.data?.message ||
          "Không thể hủy yêu cầu. Vui lòng thử lại.";
        this.hienToast("error", message);
      }
    },
  },
};
</script>

<style>

</style>