<template>
  <div class="rescuer-dashboard-wrapper">
    <div class="mission-layout h-100 d-flex flex-column flex-lg-row">
      
      <!-- Sidebar -->
      <div class="mission-sidebar d-flex flex-column bg-white z-3 shadow-lg">
        <div class="sidebar-header p-4 pb-3 border-bottom position-relative overflow-hidden">
          <!-- Decorative circle background -->
          <div class="bg-shape bg-primary rounded-circle position-absolute opacity-10" style="width: 150px; height: 150px; top: -50px; right: -50px;"></div>
          
          <div class="d-flex align-items-center mb-4 position-relative z-1">
            <div class="icon-box bg-primary bg-opacity-10 text-primary me-3 d-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px;">
              <i class="bi bi-radar fs-4"></i>
            </div>
            <div>
              <h5 class="fw-bolder mb-1 text-dark">Radar Cứu Hộ</h5>
              <span class="text-muted small fw-medium">Phát hiện {{ assignments.length }} yêu cầu gần bạn</span>
            </div>
          </div>
          
          <div class="d-flex gap-2 position-relative z-1 overflow-auto pb-1 hide-scrollbar">
            <button class="btn btn-sm btn-dark rounded-pill px-3 fw-bold shadow-sm">Tất cả</button>
            <button class="btn btn-sm bg-light border-0 text-secondary rounded-pill px-3 fw-bold hover-bg-gray">Gần nhất</button>
            <button class="btn btn-sm bg-danger bg-opacity-10 text-danger border-0 rounded-pill px-3 fw-bold hover-bg-danger">Khẩn cấp</button>
          </div>
        </div>

        <div class="mission-list flex-grow-1 overflow-auto p-3 d-flex flex-column gap-3 bg-secondary bg-opacity-10">
          <!-- Loading -->
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-danger" role="status"></div>
            <p class="mt-2 text-muted small">Đang tải...</p>
          </div>

          <!-- Mission Cards -->
          <div v-else v-for="item in assignments" :key="item.id_phan_cong" class="mission-card card border-0 shadow-sm rounded-4 transition-all">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <span :class="['badge rounded-pill px-3 py-2 fw-bold border', getPriorityClass(item)]">
                  <i class="bi bi-exclamation-triangle-fill me-1" v-if="getPriorityText(item) === 'KHẨN CẤP'"></i>
                  {{ getPriorityText(item) }}
                </span>
                <div class="text-end">
                  <div class="fw-bold text-dark fs-5">{{ item.yeu_cau?.muc_do_khan_cap || '-' }}</div>
                  <div class="text-muted small fw-medium"><i class="bi bi-clock me-1 text-primary"></i>{{ item.created_at }}</div>
                </div>
              </div>
              <h6 class="fw-bold fs-6 mb-2 text-dark">{{ item.yeu_cau?.loai_su_co?.ten_loai_su_co || 'Yêu cầu cứu hộ' }}</h6>
              <div class="d-flex align-items-start mb-4 text-secondary small fw-medium">
                <i class="bi bi-geo-alt-fill me-2 text-danger mt-1"></i>
                <span>{{ item.yeu_cau?.dia_chi || item.yeu_cau?.vi_tri }}</span>
              </div>
              <button
                v-if="item.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN'"
                class="btn btn-primary w-100 fw-bold btn-lg rounded-3 py-2 btn-accept shadow-sm"
                @click="acceptMission(item.id_phan_cong)"
              >
                TIẾP NHẬN NGAY
              </button>
              <button
                v-else
                class="btn btn-outline-secondary w-100 fw-bold btn-lg rounded-3 py-2"
                @click="goToMission(item)"
              >
                XEM CHI TIẾT
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- END mission-list + sidebar -->

      <!-- Map Area -->
      <div class="map-area flex-grow-1 position-relative bg-light">
        <MapboxMap
          class="w-100 h-100 absolute-map"
          :center="[108.2022, 16.0544]"
          :zoom="14"
        />

        <!-- Current Active Mission Floating Card -->
        <div class="active-mission-card position-absolute m-3 m-lg-4 z-3 rounded-4" style="max-width: 360px;" v-if="activeAssignments.length > 0">
          <div class="card border-0 shadow-lg bg-white rounded-4 overflow-hidden glass-card">
            <div class="card-header bg-transparent border-bottom-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <div class="pulse-indicator bg-danger me-2 shadow-sm"></div>
                <span class="fw-bold text-danger small text-uppercase" style="letter-spacing: 0.5px;">Đang xử lý</span>
              </div>
              <button class="btn btn-sm btn-icon btn-light rounded-circle text-muted"><i class="bi bi-three-dots"></i></button>
            </div>

            <div class="card-body px-4 pb-4 pt-3">
              <h5 class="fw-bold mb-2 text-dark">{{ activeAssignments[0].yeu_cau?.loai_su_co?.ten_loai_su_co || 'Yêu cầu cứu hộ' }}</h5>
              <p class="text-secondary small mb-3 fw-medium"><i class="bi bi-map text-primary me-1"></i> {{ activeAssignments[0].yeu_cau?.dia_chi || '-' }}</p>

              <div class="victim-info bg-light rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between border border-light shadow-sm" v-if="activeAssignments[0].yeu_cau">
                <div class="d-flex align-items-center">
                  <div class="avatar-circle bg-white text-primary shadow-sm me-3 fw-bold fs-5 border" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">{{ (activeAssignments[0].yeu_cau.ho_ten_nguoi_dung || 'N').charAt(0) }}</div>
                  <div>
                    <div class="text-muted fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">NGƯỜI GẶP NẠN</div>
                    <div class="fw-bold text-dark">{{ activeAssignments[0].yeu_cau.ho_ten_nguoi_dung || '-' }}</div>
                  </div>
                </div>
                <a v-if="activeAssignments[0].yeu_cau.so_dien_thoai_nguoi_dung" :href="'tel:' + activeAssignments[0].yeu_cau.so_dien_thoai_nguoi_dung" class="btn btn-success rounded-circle btn-call shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                  <i class="bi bi-telephone-fill"></i>
                </a>
              </div>

              <button @click="$router.push('/rescuer/dang-xu-ly')" class="btn btn-danger w-100 py-3 fw-bold rounded-3 shadow-danger btn-navigate d-flex align-items-center justify-content-center">
                <i class="bi bi-cursor-fill me-2 fs-5"></i> XEM CHI TIẾT
              </button>
            </div>
          </div>
        </div>

        <!-- Map Controls -->
        <div class="map-controls position-absolute bottom-0 end-0 m-4 d-flex flex-column gap-2 z-3 d-none d-md-flex">
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-dark btn-map-control hover-bg-gray"><i class="bi bi-plus-lg"></i></button>
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-dark btn-map-control hover-bg-gray"><i class="bi bi-dash-lg"></i></button>
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-primary mt-2 btn-map-control hover-bg-gray"><i class="bi bi-crosshair"></i></button>
        </div>

        <!-- Action Bottom Bar -->
        <div class="bottom-actions-bar position-absolute bottom-0 w-100 z-3 p-3 p-md-4 d-flex justify-content-center pointer-events-none">
          <div class="actions-container bg-white bg-opacity-90 backdrop-blur p-2 rounded-pill shadow-lg d-flex gap-2 pointer-events-auto border border-white">
            <button class="btn btn-warning rounded-pill px-3 px-md-4 py-2 fw-bold text-dark d-flex align-items-center transition-all hover-scale">
              <i class="bi bi-megaphone-fill me-2"></i> <span class="d-none d-sm-inline">Gọi y tế</span>
            </button>
            <button class="btn btn-dark rounded-pill px-3 px-md-4 py-2 fw-bold d-flex align-items-center btn-arrived transition-all hover-scale">
              <i class="bi bi-geo-alt-fill me-2 text-danger"></i> Đã đến nơi
            </button>
            <button class="btn btn-success rounded-pill px-3 px-md-4 py-2 fw-bold d-flex align-items-center transition-all hover-scale shadow-sm">
              <i class="bi bi-check-circle-fill me-2"></i> <span class="d-none d-sm-inline">Hoàn thành</span>
            </button>
          </div>
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
  name: "RescuerHome",
  data() {
    return {
      loading: false,
      assignments: [],
      selectedMission: null,
      teamId: null,
    };
  },
  computed: {
    pendingAssignments() {
      return this.assignments.filter(a => a.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN');
    },
    activeAssignments() {
      return this.assignments.filter(a => a.trang_thai_nhiem_vu === 'DANG_XU_LY');
    },
  },
  async mounted() {
    this.loadTeamData();
    await this.fetchAssignments();
  },
  methods: {
    loadTeamData() {
      const teamStr = localStorage.getItem("rescuer_team");
      if (teamStr) {
        try {
          const team = JSON.parse(teamStr);
          this.teamId = team.id_doi_cuu_ho || team.id;
        } catch {
          // fallback
        }
      }
    },
    async fetchAssignments() {
      this.loading = true;
      try {
        const res = await rescuerAPI.getAssignments({ per_page: 50 });
        if (res.data?.data) {
          this.assignments = res.data.data.data || res.data.data;
        }
      } catch (e) {
        console.error("Lỗi tải phân công:", e);
      } finally {
        this.loading = false;
      }
    },
    async acceptMission(assignmentId) {
      try {
        await rescuerAPI.updateAssignmentStatus(assignmentId, {
          trang_thai_nhiem_vu: 'DANG_XU_LY'
        });
        toaster.success("Đã tiếp nhận nhiệm vụ");
        await this.fetchAssignments();
        this.$router.push("/rescuer/dang-xu-ly");
      } catch (e) {
        toaster.error("Không thể tiếp nhận nhiệm vụ");
      }
    },
    goToMission(assignment) {
      this.selectedMission = assignment;
      this.$router.push("/rescuer/dang-xu-ly");
    },
    getPriorityClass(assignment) {
      const mucDo = assignment.yeu_cau?.muc_do_khan_cap || 'THUONG';
      if (mucDo === 'KHA_CAP') return 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
      if (mucDo === 'TRUNG_BINH') return 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25';
      return 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
    },
    getPriorityText(assignment) {
      const mucDo = assignment.yeu_cau?.muc_do_khan_cap || 'THUONG';
      if (mucDo === 'KHA_CAP') return 'KHẨN CẤP';
      if (mucDo === 'TRUNG_BINH') return 'TRUNG BÌNH';
      return 'THƯỜNG';
    },
  },
};
</script>

<style scoped>
/* Layout resets to bypass layout padding */
.rescuer-dashboard-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px); /* Adjust based on your topbar height */
  overflow: hidden;
  font-family: 'Inter', 'Roboto', sans-serif;
}

/* Sidebar styling */
.mission-sidebar {
  width: 100%;
  max-width: 400px;
  border-right: 1px solid rgba(0,0,0,0.05);
}
@media (max-width: 991.98px) {
  .mission-sidebar {
    height: 40%;
    max-width: 100%;
    border-right: none;
    border-bottom: 1px solid rgba(0,0,0,0.1);
  }
}

.hover-bg-gray:hover { background-color: #f3f4f6 !important; }
.hover-bg-danger:hover { background-color: rgba(220, 53, 69, 0.2) !important; }
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Mission Card */
.mission-card {
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
  border: 1px solid rgba(0,0,0,0.03);
}
.mission-card:hover { 
  transform: translateY(-3px); 
  box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; 
  border-color: rgba(13, 110, 253, 0.2);
}
.btn-accept {
  background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
  border: none;
}
.btn-accept:hover {
  background: linear-gradient(135deg, #0b5ed7 0%, #084298 100%);
  transform: translateY(-1px);
}

/* Map area */
.map-area {
  position: relative;
}
.absolute-map {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
}

/* Active Mission Card - Glass effect */
.glass-card {
  background: rgba(255, 255, 255, 0.95) !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.5);
}
.pulse-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
  animation: pulse 1.5s infinite;
}
@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
  70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
  100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}
.shadow-danger {
  box-shadow: 0 8px 16px rgba(220, 53, 69, 0.25) !important;
}

/* Bottom Actions */
.pointer-events-none { pointer-events: none; }
.pointer-events-auto { pointer-events: auto; }
.backdrop-blur {
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}
.hover-scale:hover {
  transform: scale(1.03);
}

/* Map Controls */
.btn-map-control {
  width: 40px; height: 40px;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.btn-map-control:hover { background-color: #f8f9fa !important; transform: translateX(-2px); }
</style>
