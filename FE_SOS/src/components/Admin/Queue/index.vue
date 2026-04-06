<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h5 class="mb-0 fw-semibold">Hàng đợi yêu cầu cứu hộ</h5>
        <p class="text-muted mb-0">Danh sách yêu cầu cứu hộ lấy từ API `/api/yeu-cau-cuu-ho`</p>
      </div>
      <button class="btn btn-outline-secondary" @click="taiDanhSachYeuCau" :disabled="loading">
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
              <div class="request-image-wrap bg-light d-flex align-items-center justify-content-center">
                <img
                  v-if="request.imageUrl"
                  :src="request.imageUrl"
                  :alt="`Ảnh sự cố ${request.id}`"
                  class="img-fluid h-100 request-image"
                  @error="khiLoiAnh(request)"
                />
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
                  <p class="mb-1 text-muted">
                    <i class="bi bi-geo-alt-fill me-1"></i>{{ request.address }}
                  </p>
                  <p class="mb-1 text-muted">
                    <i class="bi bi-list-check me-1"></i> {{ request.incidentDetails }}
                  </p>
                  <p class="mb-1 text-muted">
                    <i class="bi bi-card-text me-1"></i> {{ request.description }}
                  </p>
                </div>

                <div class="mt-auto d-flex flex-wrap gap-2">
                  <button
                    v-if="coTheHienPhanDieuPho(request)"
                    type="button"
                    class="btn btn-sm btn-primary d-flex align-items-center gap-2"
                    @click="chuyenToiDieuPho(request)"
                  >
                    <i class="bi bi-diagram-3"></i>
                    Điều phối
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
  CHO_XU_LY: { label: "Chờ xử lý", badge: "bg-info text-dark" },
  DANG_XU_LY: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  HOAN_THANH: { label: "Hoàn thành", badge: "bg-success text-white" },
  HUY_BO: { label: "Đã hủy", badge: "bg-danger text-white" },
  DONE: { label: "Hoàn thành", badge: "bg-success text-white" },
  PROCESSING: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  WAITING: { label: "Chờ xử lý", badge: "bg-info text-dark" },
};

function chuanHoaChuoi(value, fallback = "") {
  if (value === null || value === undefined) return fallback;
  if (typeof value === "object") {
    return chuanHoaChuoi(
      value.ten_danh_muc || value.ten_loai_su_co || value.title || value.name || fallback,
      fallback
    );
  }
  const normalized = String(value).trim();
  return normalized || fallback;
}

function dinhDangThoiGian(value) {
  if (!value) return "Không xác định";
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return chuanHoaChuoi(value, "Không xác định");
  return parsed.toLocaleString("vi-VN", {
    hour: "2-digit",
    minute: "2-digit",
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

function chuanHoaTrangThai(status) {
  if (!status) return "CHO_XU_LY";
  return chuanHoaChuoi(status, "CHO_XU_LY").toUpperCase().replace(/\s+/g, "_");
}

function layTrangThaiMeta(status) {
  const key = chuanHoaTrangThai(status);
  return STATUS_META[key] || {
    label: chuanHoaChuoi(status, "Không rõ"),
    badge: "bg-secondary text-white",
  };
}

function layNguonApi() {
  const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api";
  return apiBaseUrl.replace(/\/api\/?$/, "");
}

function xayDungDanhSachAnh(image) {
  const raw = chuanHoaChuoi(image);
  if (!raw) return [];
  if (/^(https?:|data:)/i.test(raw)) {
    return [raw];
  }

  const origin = layNguonApi();
  const cleaned = raw.replace(/^\/+/, "");
  const fileName = cleaned.split("/").pop();

  return [
    `${origin}/${cleaned}`,
    `${origin}/storage/${cleaned}`,
    `${origin}/storage/yeu-cau-cuu-ho/${cleaned}`,
    `${origin}/storage/requests/${cleaned}`,
    `${origin}/storage/rescue-requests/${cleaned}`,
    `${origin}/uploads/${cleaned}`,
    `${origin}/images/${cleaned}`,
    `${origin}/storage/${fileName}`,
    `${origin}/uploads/${fileName}`,
    `${origin}/images/${fileName}`,
  ].filter(Boolean);
}

function phanTichYeuCau(payload) {
  const rawData = payload;
  let items = Array.isArray(rawData)
    ? rawData
    : Array.isArray(rawData?.data)
    ? rawData.data
    : Array.isArray(rawData?.data?.data)
    ? rawData.data.data
    : [];

  // Lọc bỏ những yêu cầu đã bị hủy hoặc đã hoàn thành
  items = items.filter((item) => {
    const status = chuanHoaTrangThai(item.trang_thai || item.status);
    return !['HUY_BO', 'HOAN_THANH', 'DONE'].includes(status);
  });

  return items.map((item, index) => {
    const id = item.id_yeu_cau || item.id || item.request_id || "-";
const statusMeta = layTrangThaiMeta(item.trang_thai || item.status);
  const imageCandidates = xayDungDanhSachAnh(item.hinh_anh);

    return {
      key: `${id}-${index}`,
      id,
      type: chuanHoaChuoi(
        item.loai_su_co?.ten_danh_muc ||
          item.loai_su_co?.ten_loai ||
          item.loai ||
          item.chi_tiet ||
          item.chi_tiet_su_co,
        "Không rõ"
      ),
      incidentDetails: chuanHoaChuoi(
        item.chi_tiet || item.chi_tiet_su_co || item.loai_su_co?.ten_danh_muc,
        "Không có chi tiết sự cố"
      ),
      description: chuanHoaChuoi(item.mo_ta || item.description || item.noi_dung, "Không có mô tả"),
      address: chuanHoaChuoi(item.vi_tri_dia_chi || item.dia_chi, "Không có địa chỉ"),
      time: dinhDangThoiGian(item.thoi_gian_gui || item.created_at || item.updated_at || item.thoi_gian || item.time),
      priorityLabel: chuanHoaChuoi(item.muc_do_khan_cap || item.diem_uu_tien, "Không xác định"),
      status: chuanHoaTrangThai(item.trang_thai || item.status),
      statusLabel: statusMeta.label,
      statusClass: statusMeta.badge,
      imageCandidates,
      imageIndex: 0,
      imageUrl: imageCandidates[0] || null,
      raw: item,
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
    await this.taiDanhSachYeuCau();
  },
  methods: {
coTheHienPhanDieuPho(request) {
      return request?.status !== "HUY_BO";
    },
    khiLoiAnh(request) {
      if (!request?.imageCandidates?.length) {
        request.imageUrl = null;
        return;
      }

      request.imageIndex += 1;
      request.imageUrl = request.imageCandidates[request.imageIndex] || null;
    },
    chuyenToiDieuPho(request) {
      const requestId = request?.raw?.id_yeu_cau || request?.raw?.id || request?.id;
      this.$router.push({
        path: "/admin/assignments",
        query: requestId ? { requestId: String(requestId) } : {},
      });
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
    async taiDanhSachYeuCau() {
      this.loading = true;
      this.error = "";
      try {
        const response = await rescueRequestAPI.getList();
        this.requests = phanTichYeuCau(response?.data || response);
      } catch (error) {
        console.error("Không tải được yêu cầu cứu hộ:", error);
        this.error = "Không tải được danh sách yêu cầu. Vui lòng thử lại.";
        this.hienToast("error", this.error);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.card-body {
  min-height: 220px;
}

.request-image-wrap {
  width: 220px;
  height: 220px;
}

.request-image {
  width: 220px;
  object-fit: cover;
}
</style>
