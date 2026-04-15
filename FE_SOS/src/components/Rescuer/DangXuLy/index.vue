<template>
  <div class="processing-wrapper">
    <div class="mission-layout h-100 d-flex flex-column flex-lg-row">

      <!-- Left: Mission Detail Panel -->
      <div class="detail-panel d-flex flex-column bg-white z-3 shadow-lg">
        <div class="detail-header p-4 pb-3 border-bottom position-relative overflow-hidden">
          <div class="bg-shape bg-danger rounded-circle position-absolute opacity-10" style="width: 180px; height: 180px; top: -80px; right: -60px;"></div>
          <div class="d-flex align-items-center justify-content-between position-relative z-1">
            <div class="d-flex align-items-center">
              <div class="icon-box bg-danger bg-opacity-10 text-danger me-3 d-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px;">
                <i class="fa-solid fa-spinner fa-spin fs-5"></i>
              </div>
              <div>
                <h5 class="fw-bolder mb-1 text-dark">Nhiệm Vụ Đang Xử Lý</h5>
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 small fw-bold">
                  <span class="pulse-dot bg-danger me-1"></span> ĐANG XỬ LÝ
                </span>
              </div>
            </div>
            <router-link to="/rescuer/home" class="btn btn-sm btn-outline-secondary rounded-circle">
              <i class="fa-solid fa-arrow-left"></i>
            </router-link>
          </div>
        </div>

        <div class="detail-body flex-grow-1 overflow-auto p-4" v-if="currentMission">
          <!-- Priority & Time -->
          <div class="d-flex justify-content-between align-items-start mb-4">
            <span class="badge rounded-pill px-3 py-2 fw-bold border" :class="getPriorityBadge()">
              <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ getPriorityText() }}
            </span>
            <div class="text-end">
              <div class="text-muted small fw-medium"><i class="fa-regular fa-clock me-1 text-danger"></i> Bắt đầu: {{ currentMission.created_at }}</div>
            </div>
          </div>

          <!-- Incident Title -->
          <h4 class="fw-bold text-dark mb-1">{{ currentMission.yeu_cau?.loai_su_co?.ten_loai_su_co || 'Yêu cầu cứu hộ' }}</h4>
          <p class="text-muted small mb-4">
            <i class="fa-solid fa-layer-group me-1 text-danger"></i>
            {{ currentMission.yeu_cau?.muc_do_khan_cap || '-' }}
          </p>

          <!-- Victim Card -->
          <div class="victim-card bg-light rounded-4 p-4 mb-4 border border-light shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="d-flex align-items-center">
                <div class="victim-avatar me-3 d-flex align-items-center justify-content-center rounded-circle bg-white text-danger shadow-sm fw-bold" style="width: 50px; height: 50px; font-size: 18px;">
                  {{ (currentMission.yeu_cau?.ho_ten_nguoi_dung || 'N').charAt(0) }}
                </div>
                <div>
                  <div class="text-muted fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">NGƯỜI GẶP NẠN</div>
                  <div class="fw-bold text-dark fs-6">{{ currentMission.yeu_cau?.ho_ten_nguoi_dung || '-' }}</div>
                </div>
              </div>
              <a v-if="currentMission.yeu_cau?.so_dien_thoai_nguoi_dung" :href="'tel:' + currentMission.yeu_cau.so_dien_thoai_nguoi_dung" class="btn btn-success rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="fa-solid fa-phone"></i>
              </a>
            </div>
            <div class="small text-secondary" v-if="currentMission.yeu_cau?.so_dien_thoai_nguoi_dung">
              <i class="fa-solid fa-phone me-1 text-secondary"></i>
              <a :href="'tel:' + currentMission.yeu_cau.so_dien_thoai_nguoi_dung" class="text-secondary text-decoration-none">{{ currentMission.yeu_cau.so_dien_thoai_nguoi_dung }}</a>
            </div>
          </div>

          <!-- Location Card -->
          <div class="location-card bg-light rounded-4 p-4 mb-4 border border-light shadow-sm">
            <div class="d-flex align-items-start">
              <div class="location-icon me-3 mt-1 d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger" style="width: 44px; height: 44px;">
                <i class="fa-solid fa-location-dot fs-5"></i>
              </div>
              <div class="flex-grow-1">
                <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">ĐỊA ĐIỂM SỰ CỐ</div>
                <div class="fw-semibold text-dark mb-2">{{ currentMission.yeu_cau?.dia_chi || '-' }}</div>
                <div class="d-flex gap-2">
                  <a v-if="currentMission.yeu_cau?.dia_chi" :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(currentMission.yeu_cau.dia_chi)" target="_blank" class="btn btn-danger btn-sm rounded-3 fw-bold shadow-sm">
                    <i class="fa-solid fa-route me-1"></i> Chỉ đường
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Mission Progress -->
          <div class="progress-section mb-4">
            <div class="text-muted fw-bold mb-3" style="font-size: 10px; letter-spacing: 0.5px;">TIẾN TRÌNH NHIỆM VỤ</div>
            <div class="progress-steps d-flex justify-content-between position-relative mb-4">
              <div class="step done text-center" :class="{ active: missionStep >= 1 }">
                <div class="step-icon mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" :class="missionStep >= 1 ? 'bg-danger text-white' : 'bg-light text-secondary'">
                  <i class="fa-solid fa-bell"></i>
                </div>
                <div class="small fw-bold" :class="missionStep >= 1 ? 'text-danger' : 'text-secondary'">Tiếp nhận</div>
              </div>
              <div class="step-line position-absolute" style="top: 18px; left: 15%; right: 15%; height: 2px; background: #e5e7eb; z-index: 0;"></div>
              <div class="step done text-center" :class="{ active: missionStep >= 2 }">
                <div class="step-icon mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" :class="missionStep >= 2 ? 'bg-danger text-white' : 'bg-light text-secondary'">
                  <i class="fa-solid fa-truck-medical"></i>
                </div>
                <div class="small fw-bold" :class="missionStep >= 2 ? 'text-danger' : 'text-secondary'">Đang di chuyển</div>
              </div>
              <div class="step done text-center" :class="{ active: missionStep >= 3 }">
                <div class="step-icon mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" :class="missionStep >= 3 ? 'bg-danger text-white' : 'bg-light text-secondary'">
                  <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="small fw-bold" :class="missionStep >= 3 ? 'text-danger' : 'text-secondary'">Đã đến nơi</div>
              </div>
              <div class="step done text-center" :class="{ active: missionStep >= 4 }">
                <div class="step-icon mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" :class="missionStep >= 4 ? 'bg-danger text-white' : 'bg-light text-secondary'">
                  <i class="fa-solid fa-check"></i>
                </div>
                <div class="small fw-bold" :class="missionStep >= 4 ? 'text-danger' : 'text-secondary'">Hoàn thành</div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="notes-section mb-4">
            <div class="text-muted fw-bold mb-2" style="font-size: 10px; letter-spacing: 0.5px;">GHI CHÚ</div>
            <textarea class="form-control rounded-3 border-secondary-subtle" rows="3" placeholder="Nhập ghi chú về tình trạng nạn nhân..." v-model="note"></textarea>
          </div>

          <!-- Action Buttons -->
          <div class="action-buttons d-flex flex-column gap-2">
            <button class="btn btn-danger w-100 fw-bold py-3 rounded-3 shadow-danger transition-all hover-lift" @click="arrived" v-if="missionStep < 3">
              <i class="fa-solid fa-location-dot me-2"></i> ĐÃ ĐẾN NƠI
            </button>
            <button class="btn btn-success w-100 fw-bold py-3 rounded-3 shadow-sm transition-all hover-lift" @click="completeMission" v-if="missionStep >= 3 && missionStep < 4">
              <i class="fa-solid fa-check-circle me-2"></i> HOÀN THÀNH NHIỆM VỤ
            </button>
            <div class="d-flex gap-2" v-if="missionStep < 3">
              <button class="btn btn-warning w-50 fw-bold py-2 rounded-3 transition-all hover-lift">
                <i class="fa-solid fa-phone me-1"></i> Gọi y tế 115
              </button>
              <button class="btn btn-outline-secondary w-50 fw-bold py-2 rounded-3 transition-all hover-lift">
                <i class="fa-solid fa-flag me-1"></i> Báo cáo
              </button>
            </div>
          </div>
        </div>

        <!-- Loading -->
        <div v-else-if="loading" class="flex-grow-1 d-flex align-items-center justify-content-center p-4">
          <div class="text-center">
            <div class="spinner-border text-danger" role="status"></div>
            <p class="mt-2 text-muted">Đang tải...</p>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex-grow-1 d-flex align-items-center justify-content-center p-4">
          <div class="text-center">
            <div class="mb-3">
              <i class="fa-solid fa-inbox text-secondary opacity-25" style="font-size: 64px;"></i>
            </div>
            <h6 class="text-secondary fw-bold">Không có nhiệm vụ đang xử lý</h6>
            <p class="text-muted small mb-3">Tiếp nhận nhiệm vụ từ thông báo để bắt đầu</p>
            <router-link to="/rescuer/thong-bao-nhiem-vu" class="btn btn-danger rounded-pill px-4 fw-bold">
              <i class="fa-solid fa-bell me-2"></i> Xem thông báo
            </router-link>
          </div>
        </div>
      </div>

      <!-- Right: Map Area -->
      <div class="map-area flex-grow-1 position-relative bg-light">
        <MapboxMap
          class="w-100 h-100 absolute-map"
          :center="[105.8342, 21.0285]"
          :zoom="14"
        />

        <!-- Mini Mission Info Floating -->
        <div class="mini-info-card position-absolute m-3 m-lg-4 z-3 rounded-4" style="max-width: 300px;" v-if="currentMission">
          <div class="card border-0 shadow-lg bg-white rounded-4 overflow-hidden">
            <div class="card-body p-3">
              <div class="d-flex align-items-center mb-2">
                <div class="pulse-indicator bg-danger me-2"></div>
                <span class="fw-bold text-danger small text-uppercase">Đang xử lý</span>
              </div>
              <h6 class="fw-bold text-dark mb-1">{{ currentMission.title }}</h6>
              <p class="text-muted small mb-0">
                <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                {{ currentMission.address.split(',')[0] }}
              </p>
            </div>
          </div>
        </div>

        <!-- Map Controls -->
        <div class="map-controls position-absolute bottom-0 end-0 m-4 d-flex flex-column gap-2 z-3 d-none d-md-flex">
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-dark btn-map-control hover-bg-gray"><i class="fa-solid fa-plus"></i></button>
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-dark btn-map-control hover-bg-gray"><i class="fa-solid fa-minus"></i></button>
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-danger mt-2 btn-map-control hover-bg-gray"><i class="fa-solid fa-crosshairs"></i></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";
import { rescuerAPI } from "../../../services/api.js";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

export default {
  components: { MapboxMap },
  name: "DangXuLy",
  data() {
    return {
      note: "",
      loading: false,
      currentMission: null,
      missionStep: 1,
    };
  },
  async mounted() {
    await this.loadActiveMission();
  },
  methods: {
    async loadActiveMission() {
      this.loading = true;
      try {
        const res = await rescuerAPI.getAssignments({
          per_page: 50,
          trang_thai: "DANG_XU_LY",
        });
        if (res.data?.data?.data?.length > 0) {
          this.currentMission = res.data.data.data.find(
            (m) => m.trang_thai_nhiem_vu === "DANG_XU_LY"
          );
        }
        if (!this.currentMission) {
          const all = await rescuerAPI.getAssignments({ per_page: 50 });
          if (all.data?.data?.data?.length > 0) {
            this.currentMission = all.data.data.data[0];
          }
        }
      } catch (e) {
        console.error("Lỗi tải nhiệm vụ:", e);
      } finally {
        this.loading = false;
      }
    },
    async arrived() {
      if (!this.currentMission) return;
      try {
        await rescuerAPI.updateAssignmentStatus(this.currentMission.id_phan_cong, {
          trang_thai_nhiem_vu: "DANG_DEN",
        });
        this.missionStep = 3;
        toaster.success("Đã cập nhật: Đã đến nơi");
      } catch (e) {
        toaster.error("Không thể cập nhật trạng thái");
      }
    },
    async completeMission() {
      if (!this.currentMission) return;
      try {
        await rescuerAPI.updateAssignmentStatus(this.currentMission.id_phan_cong, {
          trang_thai_nhiem_vu: "HOAN_THANH",
        });
        toaster.success("Nhiệm vụ hoàn thành!");
        this.$router.push("/rescuer/da-xu-ly");
      } catch (e) {
        toaster.error("Không thể hoàn thành nhiệm vụ");
      }
    },
    getPriorityBadge() {
      const mucDo = this.currentMission?.yeu_cau?.muc_do_khan_cap || "THUONG";
      if (mucDo === "KHA_CAP")
        return "bg-danger bg-opacity-10 text-danger border-danger border-opacity-25";
      if (mucDo === "TRUNG_BINH")
        return "bg-warning bg-opacity-10 text-warning border-warning border-opacity-25";
      return "bg-info bg-opacity-10 text-info border-info border-opacity-25";
    },
    getPriorityText() {
      const mucDo = this.currentMission?.yeu_cau?.muc_do_khan_cap || "THUONG";
      if (mucDo === "KHA_CAP") return "KHẨN CẤP";
      if (mucDo === "TRUNG_BINH") return "TRUNG BÌNH";
      return "THƯỜNG";
    },
  },
};
</script>

<style scoped>
.processing-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px);
  overflow: hidden;
  font-family: 'Inter', 'Roboto', sans-serif;
}

.detail-panel {
  width: 100%;
  max-width: 480px;
  border-right: 1px solid rgba(0,0,0,0.05);
}

@media (max-width: 991.98px) {
  .detail-panel {
    height: 50%;
    max-width: 100%;
    border-right: none;
    border-bottom: 1px solid rgba(0,0,0,0.1);
  }
}

.hover-bg-gray:hover { background-color: #f3f4f6 !important; }
.hover-lift:hover { transform: translateY(-2px); }
.shadow-danger { box-shadow: 0 8px 16px rgba(239, 68, 68, 0.25) !important; }

.map-area { position: relative; }
.absolute-map { position: absolute; top: 0; left: 0; right: 0; bottom: 0; }

.pulse-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  display: inline-block;
  animation: pulse 1.5s infinite;
}

.pulse-indicator {
  width: 10px; height: 10px;
  border-radius: 50%;
  box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
  100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

.step-icon {
  width: 36px; height: 36px;
  position: relative; z-index: 1;
}

.btn-map-control {
  width: 40px; height: 40px;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.btn-map-control:hover { background-color: #f8f9fa !important; transform: translateX(-2px); }
</style>
