<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h5 class="mb-0 fw-semibold">Hàng đợi yêu cầu cứu hộ</h5>
        <p class="text-muted mb-0">Danh sách yêu cầu cứu hộ lấy từ API /api/yeu-cau-cuu-ho</p>
      </div>
      <button class="btn btn-outline-secondary" @click="loadRequests" :disabled="loading">
        <i class="bi bi-arrow-clockwise me-2"></i>
        Làm mới
      </button>
    </div>

    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <div class="small text-muted mt-2">Đang tải danh sách yêu cầu...</div>
    </div>

    <div v-if="!loading && requests.length === 0" class="alert alert-warning">
      Không tìm thấy yêu cầu cứu hộ nào.
    </div>

    <div class="row g-4">
      <div v-for="request in requests" :key="request.key" class="col-12 col-xl-6">
        <div class="card h-100 border-0 shadow-sm overflow-hidden">
          <div class="row g-0">
            <div class="col-auto">
              <div class="bg-light d-flex align-items-center justify-content-center" style="width: 220px; height: 220px;">
                <img v-if="request.imageUrl" :src="request.imageUrl" class="img-fluid h-100" style="object-fit: cover; width: 220px;" />
                <div v-else class="text-center text-muted px-3">
                  <i class="bi bi-image fs-1"></i>
                  <div class="small mt-2">Không có hình ảnh</div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card-body d-flex flex-column h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary me-2">SOS-{{ request.id }}</span>
                    <span class="badge rounded-pill px-3 py-2" :class="request.statusClass">{{ request.statusLabel }}</span>
                  </div>
                  <span class="badge bg-dark bg-opacity-10 text-dark">{{ request.priorityLabel }}</span>
                </div>

                <h5 class="fw-semibold mb-2">{{ request.type }}</h5>
                <div class="small text-muted mb-3">
                  <i class="bi bi-clock me-1"></i> {{ request.time }}
                </div>
                <div class="mb-3">
                  <p class="mb-1 text-muted"><i class="bi bi-geo-alt-fill me-1"></i>{{ request.address }}</p>
                  <p class="mb-1 text-muted"><i class="bi bi-people-fill me-1"></i> {{ request.people }} người</p>
                  <p class="mb-1 text-muted"><i class="bi bi-card-text me-1"></i> {{ request.description }}</p>
                </div>

                <div class="mt-auto d-flex flex-wrap gap-2">
                  <button
                    type="button"
                    class="btn btn-sm d-flex align-items-center gap-2"
                    :class="request.buttonClass"
                    @click="changeRequestStatus(request)"
                    :disabled="request.updating"
                  >
                    <i class="bi bi-arrow-repeat"></i>
                    {{ request.buttonLabel }}
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
  return `https://via.placeholder.com/440x260.png?text=${encodeURIComponent(raw)}`;
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
    const statusMeta = getStatusMeta(item.trang_thai || item.status || item.trang_thai);
    const type = normalizeText(item.loai_su_co?.ten_danh_muc || item.loai_su_co?.ten_loai || item.loai || item.chi_tiet || item.chi_tiet_su_co || "Không rõ");
    const description = normalizeText(item.mo_ta || item.chi_tiet || "Không có mô tả");
    const address = normalizeText(item.vi_tri_dia_chi || item.dia_chi || "Không có địa chỉ");
    const time = formatTime(item.thoi_gian_gui || item.created_at || item.updated_at || item.thoi_gian || item.time);
    const people = item.so_nguoi_bi_anh_huong || 0;
    const priority = normalizeText(item.muc_do_khan_cap || item.diem_uu_tien || "Không xác định");

    return {
      key: `${id}-${Math.random()}`,
      id,
      type,
      description,
      address,
      time,
      people,
      priorityLabel: priority,
      status: normalizeStatus(item.trang_thai || item.status || item.trang_thai),
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
        this.requests = parseRequests(response?.data || response);
      } catch (error) {
        console.error("Không tải được yêu cầu cứu hộ:", error);
        this.error = "Không tải được danh sách yêu cầu. Vui lòng thử lại.";
        this.hienToast("error", this.error);
      } finally {
        this.loading = false;
      }
    },
    getNextStatus(currentStatus) {
      const map = {
        CHO_XU_LY: 'DANG_XU_LY',
        WAITING: 'DANG_XU_LY',
        DANG_XU_LY: 'HOAN_THANH',
        PROCESSING: 'HOAN_THANH',
        HOAN_THANH: 'HOAN_THANH',
        DONE: 'DONE',
        HUY_BO: 'HUY_BO',
      };
      return map[currentStatus] || 'DANG_XU_LY';
    },
    async changeRequestStatus(request) {
      request.updating = true;
      try {
        const id = request.raw.id_yeu_cau || request.raw.id || request.id;
        const nextStatus = this.getNextStatus(request.status);
        const response = await rescueRequestAPI.changeStatus(id, { trang_thai: nextStatus });
        const data = response?.data?.data || response?.data || {};
        const newStatus = normalizeStatus(data.trang_thai || data.status || response?.data?.trang_thai || response?.data?.data?.trang_thai || nextStatus);
        const statusMeta = getStatusMeta(newStatus);
        request.status = newStatus;
        request.statusLabel = statusMeta.label;
        request.statusClass = statusMeta.badge;
        request.buttonClass = statusMeta.button;
        request.buttonLabel = statusMeta.buttonLabel;
        request.raw.trang_thai = newStatus;
        this.hienToast("success", `Đã cập nhật trạng thái thành ${statusMeta.label}.`);
      } catch (error) {
        console.error("Cập nhật trạng thái thất bại:", error);
        this.hienToast("error", "Không thể thay đổi trạng thái. Vui lòng thử lại.");
      } finally {
        request.updating = false;
      }
    },
  },
};
</script>

<style scoped>
.card-body {
  min-height: 220px;
}
</style>

