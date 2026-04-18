<template>
  <div class="tracking-dashboard container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h2 class="fw-bold mb-1 d-flex align-items-center gap-3">
          <i class="fa-solid fa-satellite-dish text-primary pulse-icon"></i>
          Giám sát cứu hộ thời gian thực
        </h2>
        <p class="text-muted mb-0">
          Theo dõi tiến độ, vị trí và trao đổi trực tiếp với các yêu cầu đang xử lý
        </p>
      </div>
      <button class="btn btn-light bg-white border border-light-subtle shadow-sm fw-bolder d-flex align-items-center gap-2 px-4 py-2" @click="loadTrackingList" :disabled="loadingList">
        <i class="fa-solid fa-arrow-rotate-right text-primary" :class="{ 'spin': loadingList }"></i>
        <span>Đồng bộ</span>
      </button>
    </div>

    <!-- Empty state -->
    <div v-if="!loadingList && trackingList.length === 0" class="empty-state text-center py-5 my-5">
      <div class="empty-icon text-success bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
        <i class="fa-solid fa-check-double fs-2"></i>
      </div>
      <h4 class="fw-bold text-dark mb-2">Không có nhiệm vụ theo dõi</h4>
      <p class="text-muted fw-medium">Hiện tại không có yêu cầu cứu hộ nào đang trong quá trình xử lý.</p>
    </div>

    <!-- Loading state -->
    <div v-if="loadingList" class="d-flex flex-column align-items-center justify-content-center py-5">
      <div class="spinner"></div>
      <p class="text-muted fw-medium mt-3">Đang đồng bộ dữ liệu...</p>
    </div>

    <div class="row g-4" v-if="!loadingList && trackingList.length > 0">
      <!-- Cột trái: Danh sách yêu cầu -->
      <div class="col-xl-4 col-lg-5">
        <div class="card border-0 shadow-sm custom-card h-100">
          <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h6 class="text-uppercase fw-bold text-muted mb-0" style="letter-spacing: 1px; font-size: 0.8rem;">
              <i class="fa-solid fa-list-ul me-2 text-primary"></i> Yêu cầu đang theo dõi
              <span class="badge bg-primary-subtle text-primary ms-2">{{ trackingList.length }}</span>
            </h6>
          </div>
          <div class="card-body p-0 overflow-auto custom-scrollbar" style="max-height: 75vh;">
            <div
              v-for="item in trackingList"
              :key="item.id"
              class="request-list-item px-4 py-3 border-bottom cursor-pointer"
              :class="{ 'active': selectedId === item.id }"
              @click="selectRequest(item)"
            >
              <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="d-flex align-items-center gap-2">
                  <span class="level-badge" :class="getSeverityBadge(item.muc_do_khan_cap)">
                    {{ getSeverityLabel(item.muc_do_khan_cap) }}
                  </span>
                  <span class="fw-bold text-dark">#{{ item.id }}</span>
                </div>
                <span class="text-muted small fw-medium">
                  <i class="fa-regular fa-clock me-1"></i>{{ formatTimeShort(item.thoi_gian_cap_nhat) }}
                </span>
              </div>
              <div class="text-dark fw-medium small mb-1">{{ item.loai_su_co }}</div>
              <div class="text-muted small text-truncate" style="max-width: 90%;">
                <i class="fa-solid fa-location-dot me-1 text-primary"></i>{{ item.vi_tri_dia_chi || 'Chưa có địa chỉ' }}
              </div>
              <div v-if="item.doi_cuu_ho" class="mt-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small fw-medium">
                  <i class="fa-solid fa-truck-medical me-1"></i>{{ item.doi_cuu_ho.ten_co }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cột phải: Chi tiết theo dõi -->
      <div class="col-xl-8 col-lg-7">
        <!-- Chưa chọn yêu cầu -->
        <div v-if="!selectedId" class="card border-0 shadow-sm h-100 d-flex flex-column justify-content-center align-items-center text-center p-5">
          <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 88px; height: 88px;">
            <i class="fa-solid fa-satellite-dish fs-1"></i>
          </div>
          <h4 class="fw-bold text-dark mb-2">Chọn yêu cầu để theo dõi</h4>
          <p class="text-muted max-w-sm mb-0">Nhấp vào một yêu cầu trong danh sách bên trái để xem chi tiết và vị trí của đội cứu hộ.</p>
        </div>

        <!-- Đang tải chi tiết -->
        <div v-else-if="loadingDetail" class="card border-0 shadow-sm h-100 d-flex flex-column justify-content-center align-items-center text-center p-5">
          <div class="spinner"></div>
          <p class="text-muted fw-medium mt-3">Đang tải chi tiết yêu cầu...</p>
        </div>

        <!-- Chi tiết yêu cầu -->
        <template v-else-if="trackingDetail">
          <!-- Card Thông tin đơn vị & Timeline -->
          <div class="d-flex flex-column gap-4">

            <!-- Card Rescuer & Timeline -->
            <div class="card border-0 shadow-sm custom-card">
              <div class="card-header bg-white border-0 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="text-uppercase fw-bold text-muted mb-0" style="letter-spacing: 1px; font-size: 0.8rem;">
                    <i class="fa-solid fa-truck-medical text-danger me-2"></i> Thông tin đơn vị cứu hộ
                  </h6>
                  <span class="badge px-3 py-2 rounded-pill" :class="statusBadgeClass(trackingDetail.trang_thai)">
                    {{ statusLabel(trackingDetail.trang_thai) }}
                  </span>
                </div>
              </div>
              <div class="card-body">
                <!-- Team info -->
                <div v-if="trackingDetail.phan_congs && trackingDetail.phan_congs.length > 0">
                  <div v-for="pc in trackingDetail.phan_congs" :key="pc.id_phan_cong" class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                      <div class="avatar bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                        <i class="fa-solid fa-user-shield"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h5 class="mb-1 fw-bold">{{ pc.ten_doi }}</h5>
                        <p class="text-muted mb-0 small">
                          <i class="fa-solid fa-phone me-1"></i> {{ pc.sdt_hotline || 'N/A' }}
                          <span v-if="pc.khu_vuc"> &bull; {{ pc.khu_vuc }}</span>
                        </p>
                      </div>
                      <button class="btn btn-light rounded-circle shadow-sm btn-icon" title="Gọi điện" @click="callTeam(pc.sdt_hotline)">
                        <i class="fa-solid fa-phone text-success"></i>
                      </button>
                    </div>

                    <!-- Members -->
                    <div v-if="pc.thanh_viens && pc.thanh_viens.length > 0" class="mb-3">
                      <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-users me-1"></i> Thành viên ({{ pc.thanh_viens.length }})
                      </small>
                      <div class="d-flex flex-wrap gap-2 mt-2">
                        <span v-for="m in pc.thanh_viens" :key="m.id" class="badge bg-light text-dark border px-2 py-1 fw-medium">
                          <i class="fa-solid fa-user me-1 text-primary"></i>{{ m.ho_ten }}
                        </span>
                      </div>
                    </div>

                    <!-- Timeline -->
                    <div class="tracking-timeline mt-4 position-relative">
                      <div class="timeline-line"></div>
                      <div class="timeline-step"
                        :class="{
                          'done': step.status === 'done',
                          'current': step.status === 'current'
                        }"
                        v-for="(step, index) in computedTimeline(trackingDetail)"
                        :key="index"
                      >
                        <div class="step-indicator">
                          <i v-if="step.status === 'done'" class="fa-solid fa-check small"></i>
                          <div v-else-if="step.status === 'current'" class="pulse-dot"></div>
                        </div>
                        <div class="step-content">
                          <h6 class="mb-0 fw-semibold">{{ step.title }}</h6>
                          <small class="text-muted">{{ step.time || 'Chưa thực hiện' }}</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Không có đội -->
                <div v-else class="text-center py-3 text-muted">
                  <i class="fa-solid fa-info-circle me-2"></i>Chưa có đội cứu hộ được phân công cho yêu cầu này.
                </div>
              </div>
            </div>

            <!-- Card Chi tiết yêu cầu -->
            <div class="card border-0 shadow-sm custom-card">
              <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="text-uppercase fw-bold text-muted mb-0" style="letter-spacing: 1px; font-size: 0.8rem;">
                  <i class="fa-solid fa-file-lines text-warning me-2"></i> Chi tiết sự cố
                </h6>
                <div class="d-flex gap-2 align-items-center">
                  <span class="level-badge" :class="getSeverityBadge(trackingDetail.muc_do_khan_cap)">
                    {{ getSeverityLabel(trackingDetail.muc_do_khan_cap) }}
                  </span>
                  <span class="badge bg-light text-dark border px-2 py-1 small fw-bold font-monospace">#{{ trackingDetail.id }}</span>
                  <span class="text-muted small fw-medium">
                    <i class="fa-regular fa-clock me-1"></i>{{ formatTimeShort(trackingDetail.thoi_gian_tao) }}
                  </span>
                </div>
              </div>
              <div class="card-body">
                <div class="row g-3 mb-3">
                  <div class="col-sm-6">
                    <div class="info-box p-3 rounded-3 bg-light border-start border-4 border-primary h-100">
                      <small class="text-muted d-block mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem;">
                        <i class="fa-solid fa-fire me-1"></i> Loại sự cố
                      </small>
                      <span class="fw-bold text-dark">{{ trackingDetail.loai_su_co?.ten || 'N/A' }}</span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="info-box p-3 rounded-3 bg-light border-start border-4 border-warning h-100">
                      <small class="text-muted d-block mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem;">
                        <i class="fa-solid fa-circle-info me-1"></i> Chi tiết
                      </small>
                      <span class="fw-bold text-dark">{{ trackingDetail.chi_tiet || 'Không có' }}</span>
                    </div>
                  </div>
                </div>

                <!-- Customer info -->
                <div class="info-group mb-3">
                  <label class="text-muted small mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-user me-1"></i> Người gửi yêu cầu
                  </label>
                  <div class="d-flex align-items-center justify-content-between bg-light rounded-3 p-2 px-3">
                    <div class="fw-medium text-dark">
                      <i class="fa-regular fa-user me-2 text-primary"></i>{{ trackingDetail.nguoi_yeu_cau?.ho_ten || 'N/A' }}
                    </div>
                    <div class="text-dark">
                      <i class="fa-solid fa-phone me-2 text-success"></i>{{ trackingDetail.nguoi_yeu_cau?.so_dien_thoai || 'N/A' }}
                    </div>
                  </div>
                </div>

                <!-- Location -->
                <div class="p-3 bg-light rounded-3 border-start border-4 border-danger mb-3">
                  <small class="text-muted d-block mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-map-location-dot me-1"></i> Vị trí hiện trường
                  </small>
                  <span class="text-dark fw-medium">{{ trackingDetail.vi_tri_dia_chi || 'Chưa có địa chỉ' }}</span>
                </div>

                <!-- Description -->
                <div v-if="trackingDetail.mo_ta" class="p-3 bg-light rounded-3 border-start border-4 border-info">
                  <small class="text-muted d-block mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-align-left me-1"></i> Mô tả sự cố
                  </small>
                  <span class="text-dark fst-italic">"{{ trackingDetail.mo_ta }}"</span>
                </div>
              </div>
            </div>

            <!-- Hành động -->
            <div class="d-flex flex-column gap-2">
              <div class="row g-2">
                <div class="col-6">
                  <button class="btn btn-outline-primary w-100 py-3 rounded-3 fw-medium btn-hover-rise" @click="contactClient(trackingDetail.nguoi_yeu_cau)">
                    <i class="fa-solid fa-phone me-2"></i>Liên hệ khách hàng
                  </button>
                </div>
                <div class="col-6">
                  <button class="btn btn-outline-success w-100 py-3 rounded-3 fw-medium btn-hover-rise" @click="contactTeam(trackingDetail.phan_congs)">
                    <i class="fa-solid fa-phone me-2"></i>Liên hệ đội cứu hộ
                  </button>
                </div>
              </div>
              <div class="row g-2">
                <!-- <div class="col-6">
                  <button class="btn btn-success w-100 py-3 rounded-3 fw-bold btn-hover-rise shadow-sm" @click="markComplete" :disabled="completing">
                    <span v-if="completing" class="spinner-border spinner-border-sm me-2"></span>
                    <i v-else class="fa-solid fa-check-double me-2"></i>Đánh dấu hoàn thành
                  </button>
                </div> -->
                <div class="col-6">
                  <button class="btn btn-danger w-100 py-3 rounded-3 fw-bold btn-hover-rise bg-gradient-danger border-0 shadow-sm" @click="cancelTask">
                    <i class="fa-solid fa-xmark me-2"></i>Huỷ nhiệm vụ
                  </button>
                </div>
              </div>
            </div>

          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { rescueRequestAPI } from "../../../services/api";

const SEVERITY_MAP = {
  'CRITICAL': { label: 'CRITICAL', badge: 'lv-critical' },
  'HIGH':     { label: 'HIGH',     badge: 'lv-high' },
  'MEDIUM':   { label: 'MEDIUM',   badge: 'lv-medium' },
  'LOW':      { label: 'LOW',      badge: 'lv-low' },
};

const SEVERITY_NUM_MAP = {
  4: 'CRITICAL', 3: 'HIGH', 2: 'MEDIUM', 1: 'LOW',
};

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
  if (!value) return "";
  const t = new Date(value);
  if (Number.isNaN(t.getTime())) return value;
  return t.toLocaleString("vi-VN", {
    hour: "2-digit", minute: "2-digit", day: "2-digit", month: "2-digit", year: "numeric",
  });
}

function formatTimeShort(value) {
  if (!value) return "";
  const t = new Date(value);
  if (Number.isNaN(t.getTime())) return value;
  return t.toLocaleString("vi-VN", {
    hour: "2-digit", minute: "2-digit", day: "2-digit", month: "2-digit",
  });
}

export default {
  name: "AdminTheoDoiYeuCau",
  data() {
    return {
      trackingList: [],
      selectedId: null,
      trackingDetail: null,
      loadingList: false,
      loadingDetail: false,
      completing: false,
    };
  },
  created() {
    this.loadTrackingList();
  },
  mounted() {
    const queryId = this.$route.query.id;
    if (queryId) {
      this.$nextTick(async () => {
        await this.loadTrackingList();
        const item = this.trackingList.find(r => String(r.id) === String(queryId) || String(r.id_yeu_cau) === String(queryId));
        if (item) {
          this.selectRequest(item);
        }
      });
    }
  },
  watch: {
    trackingList(val) {
      const queryId = this.$route.query.id;
      if (queryId && !this.selectedId && val.length > 0) {
        const item = val.find(r => String(r.id) === String(queryId) || String(r.id_yeu_cau) === String(queryId));
        if (item) {
          this.selectRequest(item);
        }
      }
    },
  },
  methods: {
    getSeverityBadge(sev) {
      return getSeverityInfo(sev).badge;
    },
    getSeverityLabel(sev) {
      return getSeverityInfo(sev).label;
    },
    formatTimeShort,
    statusLabel(status) {
      const map = {
        'CHO_XU_LY': 'Chờ xử lý',
        'DA_PHAN_CONG': 'Đã phân công',
        'DANG_XU_LY': 'Đang xử lý',
        'DA_DEN_HIEN_TRUONG': 'Đã tới hiện trường',
        'HOAN_THANH': 'Hoàn thành',
        'HUY_BO': 'Đã huỷ',
        'THAT_BAI': 'Thất bại',
      };
      return map[status] || status || 'N/A';
    },
    statusBadgeClass(status) {
      const map = {
        'CHO_XU_LY': 'bg-warning-subtle text-warning border border-warning',
        'DA_PHAN_CONG': 'bg-primary-subtle text-primary border border-primary',
        'DANG_XU_LY': 'bg-info-subtle text-info border border-info',
        'DA_DEN_HIEN_TRUONG': 'bg-success-subtle text-success border border-success',
        'HOAN_THANH': 'bg-success text-white',
        'HUY_BO': 'bg-secondary text-white',
        'THAT_BAI': 'bg-danger text-white',
      };
      return map[status] || 'bg-light text-dark';
    },
    computedTimeline(detail) {
      if (!detail) return [];
      const pc = detail.phan_congs && detail.phan_congs.length > 0 ? detail.phan_congs[0] : null;
      const pcStatus = pc ? pc.trang_thai_nhiem_vu : null;
      const steps = [
        { title: 'Tiếp nhận yêu cầu', time: formatTime(detail.thoi_gian_tao), status: 'done' },
        { title: 'Đã phân công cứu hộ', time: pc ? formatTime(pc.thoi_gian_phan_cong) : '', status: 'done' },
        { title: 'Đang di chuyển', time: pc && pc.thoi_gian_tiep_nhan ? formatTime(pc.thoi_gian_tiep_nhan) : '', status: 'pending' },
        { title: 'Đã tới hiện trường', time: '', status: 'pending' },
        { title: 'Hoàn thành sự cố', time: '', status: 'pending' },
      ];

      if (pcStatus === 'HOAN_THANH') {
        steps[2].status = 'done';
        steps[3].status = 'done';
        steps[4].status = 'done';
        steps[4].time = pc?.ket_qua?.thoi_gian_hoan_thanh ? formatTime(pc.ket_qua.thoi_gian_hoan_thanh) : formatTime(detail.thoi_gian_cap_nhat);
        return steps;
      }
      if (pcStatus === 'DA_DEN_HIEN_TRUONG') {
        steps[2].status = 'done';
        steps[3].status = 'current';
        return steps;
      }
      if (pcStatus === 'DANG_XU_LY' || detail.trang_thai === 'DANG_XU_LY') {
        steps[2].status = 'current';
        return steps;
      }
      return steps;
    },
    async loadTrackingList() {
      this.loadingList = true;
      try {
        const response = await rescueRequestAPI.getTrackingList();
        const rawData = response?.data?.data || response?.data || [];
        this.trackingList = Array.isArray(rawData) ? rawData : [];
      } catch (error) {
        console.error('Lỗi tải danh sách theo dõi:', error);
      } finally {
        this.loadingList = false;
      }
    },
    async selectRequest(item) {
      this.selectedId = item.id;
      this.trackingDetail = null;
      this.loadingDetail = true;
      try {
        const response = await rescueRequestAPI.getTrackingDetail(item.id);
        this.trackingDetail = response?.data?.data || response?.data;
      } catch (error) {
        console.error('Lỗi tải chi tiết:', error);
      } finally {
        this.loadingDetail = false;
      }
    },
    callTeam(phone) {
      if (!phone) return;
      window.open(`tel:${phone}`);
    },
    contactClient(client) {
      if (!client?.so_dien_thoai) return;
      window.open(`tel:${client.so_dien_thoai}`);
    },
    contactTeam(phanCongs) {
      if (!phanCongs || phanCongs.length === 0) return;
      const first = phanCongs[0];
      if (first.sdt_hotline) {
        window.open(`tel:${first.sdt_hotline}`);
      }
    },
    async markComplete() {
      if (!this.selectedId) return;
      if (!confirm(`Xác nhận đánh dấu hoàn thành yêu cầu #${this.selectedId}?`)) return;
      this.completing = true;
      try {
        await rescueRequestAPI.changeStatus(this.selectedId, { trang_thai: 'HOAN_THANH' });
        this.trackingDetail = null;
        this.selectedId = null;
        await this.loadTrackingList();
      } catch (error) {
        console.error('Lỗi đánh dấu hoàn thành:', error);
      } finally {
        this.completing = false;
      }
    },
    cancelTask() {
      if (!this.selectedId) return;
      if (!confirm("Bạn có chắc chắn muốn huỷ nhiệm vụ này? Hành động này không thể hoàn tác.")) return;
      // Implement cancel via API if available
      alert("Chức năng hủy nhiệm vụ đang được phát triển.");
    },
  },
};
</script>

<style scoped>
.tracking-dashboard {
  background-color: #f8f9fa;
  min-height: calc(100vh - 60px);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.text-primary { color: #2563eb !important; }
.bg-primary-subtle { background-color: #eff6ff !important; }
.border-primary-subtle { border-color: #bfdbfe !important; }
.bg-danger-subtle { background-color: #fef2f2 !important; }
.bg-success-subtle { background-color: #f0fdf4 !important; }
.bg-info-subtle { background-color: #f0f9ff !important; }
.border-danger-subtle { border-color: #fecaca !important; }
.bg-warning-subtle { background-color: #fffbeb !important; }
.border-warning { border-color: #f59e0b !important; }
.border-info { border-color: #0ea5e9 !important; }

.custom-card {
  border-radius: 1rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.custom-card:hover {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
}

.btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  transition: all 0.2s;
}
.btn-icon:hover { transform: scale(1.1); }
.btn-hover-rise {
  transition: all 0.3s ease;
}
.btn-hover-rise:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.bg-gradient-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.tracking-timeline {
  padding-left: 20px;
}
.timeline-line {
  position: absolute;
  left: 6px;
  top: 10px;
  bottom: 20px;
  width: 2px;
  background-color: #e5e7eb;
  z-index: 1;
}
.timeline-step {
  position: relative;
  margin-bottom: 1.5rem;
  padding-left: 1.5rem;
  z-index: 2;
}
.timeline-step:last-child { margin-bottom: 0; }
.step-indicator {
  position: absolute;
  left: -20px;
  top: 0;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: #e5e7eb;
  border: 4px solid #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 10px;
  transition: all 0.3s ease;
}
.timeline-step.done .step-indicator { background-color: #22c55e; }
.timeline-step.current .step-indicator {
  background-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}
.step-content h6 {
  font-size: 0.95rem;
  color: #4b5563;
}
.timeline-step.done .step-content h6 { color: #111827; }
.timeline-step.current .step-content h6 { color: #2563eb; }

@keyframes pulse-dot {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(59, 130, 246, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
}
.pulse-dot {
  width: 8px;
  height: 8px;
  background-color: white;
  border-radius: 50%;
  animation: pulse-dot 2s infinite;
}

.pulse-icon {
  animation: pulse-icon 2s infinite alternate;
}
@keyframes pulse-icon {
  0% { filter: drop-shadow(0 0 2px rgba(37, 99, 235, 0.4)); transform: scale(1); }
  100% { filter: drop-shadow(0 0 8px rgba(37, 99, 235, 0.8)); transform: scale(1.05); }
}

/* Request list */
.request-list-item {
  cursor: pointer;
  transition: background-color 0.15s ease;
}
.request-list-item:hover { background-color: #f8fafc; }
.request-list-item.active {
  background-color: #eff6ff;
  border-left: 4px solid #2563eb;
  padding-left: calc(1rem - 4px);
}

/* Severity badges */
.level-badge {
  padding: 3px 8px;
  border-radius: 6px;
  font-weight: 800;
  font-size: 10px;
  letter-spacing: 0.5px;
}
.lv-critical { background: #7f1d1d; color: white; }
.lv-high { background: #dc2626; color: white; }
.lv-medium { background: #f59e0b; color: white; }
.lv-low { background: #16a34a; color: white; }

/* Spinner */
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

/* Scrollbars */
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
