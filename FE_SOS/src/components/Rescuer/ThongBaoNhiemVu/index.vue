<template>
  <div class="notification-wrapper">
    <!-- Header -->
    <div class="notif-header px-4 py-4 d-flex align-items-center justify-content-between border-bottom position-relative overflow-hidden">
      <!-- Background decoration -->
      <div class="header-decoration-1"></div>
      <div class="header-decoration-2"></div>
      
      <div class="d-flex align-items-center position-relative z-1">
        <div class="notif-icon-glass me-3 d-flex align-items-center justify-content-center bg-gradient-danger text-white rounded-circle shadow-lg">
          <i class="fa-solid fa-bell fs-4 bell-ring"></i>
        </div>
        <div>
          <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">Thông Báo Nhiệm Vụ</h4>
          <span class="text-muted fw-medium"><i class="fa-solid fa-bolt text-warning me-1"></i> {{ notifications.length }} nhiệm vụ cần chú ý</span>
        </div>
      </div>
      <button class="btn btn-white rounded-pill px-4 fw-bold shadow-sm border border-light position-relative z-1 hover-lift">
        <i class="fa-solid fa-check-double me-2 text-success"></i> Đánh dấu đã đọc
      </button>
    </div>

    <!-- Filter Tabs -->
    <div class="notif-tabs px-4 py-3 bg-white shadow-sm z-1 position-relative border-bottom">
      <div class="d-flex gap-2 flex-wrap">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          class="btn btn-sm rounded-pill px-4 py-2 fw-bold transition-all d-flex align-items-center border-0"
          :class="activeTab === tab.key ? 'active-tab shadow text-white' : 'inactive-tab text-secondary bg-light'"
          @click="activeTab = tab.key"
        >
          <i :class="[tab.icon, 'me-2', { 'text-white': activeTab === tab.key }]"></i> {{ tab.label }}
          <span
            v-if="tab.count >= 0"
            class="badge ms-2 rounded-pill shadow-sm"
            :class="activeTab === tab.key ? 'bg-white text-danger' : 'bg-white text-dark border'"
            style="font-size: 11px; padding: 0.35em 0.65em;"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>
    </div>

    <!-- Notification List -->
    <div class="notif-body px-4 py-4 flex-grow-1 overflow-auto bg-soft">
      <div v-if="loading" class="d-flex flex-column align-items-center justify-content-center h-100">
        <div class="spinner-grow text-danger mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
        <h5 class="fw-bold text-muted">Đang tải dữ liệu...</h5>
      </div>
      
      <div v-else-if="filteredNotifications.length === 0" class="empty-state d-flex flex-column align-items-center justify-content-center h-100">
        <div class="empty-icon-container mb-4">
          <i class="fa-regular fa-face-smile-wink text-muted opacity-50"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">Chưa có nhiệm vụ nào</h4>
        <p class="text-muted text-center fw-medium" style="max-width: 400px;">
          Đội của bạn hiện đang rảnh. Khi có yêu cầu điều phối từ Admin, thông báo sẽ hiển thị tại đây!
        </p>
      </div>

      <div v-else class="row g-4 pb-4">
        <!-- Card -->
        <div
          v-for="(item, index) in filteredNotifications"
          :key="item.id_phan_cong"
          class="col-12 col-xl-6"
          :style="{ animationDelay: `${index * 0.05}s` }"
        >
          <div
            class="notif-card card border-0 rounded-4 overflow-hidden h-100 animate-slide-up bg-white shadow-hover"
            :class="getCardClass(item)"
          >
            <div class="card-body p-0 d-flex flex-column h-100">
              <!-- Header of Card -->
              <div class="card-header-custom p-4 d-flex justify-content-between align-items-center border-bottom bg-opacity-10"
                   :class="getCardHeaderBg(item)">
                <div class="d-flex align-items-center gap-3">
                  <span class="priority-badge fw-bold" :class="getPriorityClass(item)">
                    <i class="me-1" :class="getPriorityIcon(item)"></i>
                    {{ getPriorityText(item) }}
                  </span>
                  
                  <div v-if="!item.is_read && item.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN'">
                    <span class="badge bg-danger rounded-pill pulse-badge shadow-sm">MỚI</span>
                  </div>
                </div>
                <div class="time-badge text-muted fw-bold small bg-white px-3 py-1 rounded-pill border shadow-sm">
                  <i class="fa-regular fa-clock me-1 text-primary"></i> {{ item.created_at || 'Vừa xong' }}
                </div>
              </div>

              <!-- Content of Card -->
              <div class="card-content p-4 flex-grow-1 position-relative">
                <!-- Watermark Icon -->
                <div class="position-absolute end-0 top-0 opacity-5 p-4 pe-none">
                  <i class="fa-solid fa-truck-medical text-muted" style="font-size: 100px;"></i>
                </div>
                
                <h4 class="fw-bold text-dark mb-3 d-flex align-items-center gap-3 position-relative z-1">
                  <div class="icon-square bg-danger bg-opacity-10 text-danger rounded-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                     <i class="fa-solid fa-shield-halved fs-5"></i>
                  </div>
                  {{ item.yeu_cau?.loai_su_co?.ten_loai_su_co || 'Yêu cầu cứu hộ khẩn' }}
                </h4>

                <div class="info-row p-3 bg-light rounded-4 border border-light mb-4 position-relative z-1">
                  <p class="mb-0 text-dark fw-medium small lh-base">
                    <i class="fa-solid fa-quote-left text-danger me-2 opacity-50"></i>
                    {{ item.yeu_cau?.chi_tiet || item.mo_ta || 'Không có mô tả chi tiết cho sự cố này.' }}
                  </p>
                </div>

                <div class="row g-3 mb-0 position-relative z-1">
                  <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-4 border border-light bg-white h-100 card-info-hover shadow-sm">
                      <div class="icon-circle bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-user-injured"></i>
                      </div>
                      <div class="overflow-hidden">
                        <div class="text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;">NẠN NHÂN</div>
                        <div class="fw-bold text-dark mb-1 text-truncate">{{ item.yeu_cau?.ho_ten_nguoi_dung || 'Không rõ' }}</div>
                        <a v-if="item.yeu_cau?.so_dien_thoai_nguoi_dung" :href="'tel:' + item.yeu_cau.so_dien_thoai_nguoi_dung" class="text-primary fw-bold text-decoration-none small phone-link">
                          <i class="fa-solid fa-phone me-1"></i> {{ item.yeu_cau.so_dien_thoai_nguoi_dung }}
                        </a>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-4 border border-light bg-white h-100 card-info-hover shadow-sm">
                      <div class="icon-circle bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-location-dot"></i>
                      </div>
                      <div class="overflow-hidden">
                        <div class="text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;">ĐỊA ĐIỂM</div>
                        <div class="fw-bold text-dark small text-truncate-2" :title="item.yeu_cau?.dia_chi">
                          {{ item.yeu_cau?.dia_chi || 'Chưa cập nhật' }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer Actions -->
              <div class="card-footer-custom p-4 bg-light bg-opacity-50 border-top mt-auto z-1">
                <div class="d-flex gap-3" v-if="item.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN'">
                  <button class="btn btn-danger flex-grow-1 fw-bold py-3 rounded-4 shadow-danger btn-accept d-flex justify-content-center align-items-center gap-2 text-uppercase" @click="acceptMission(item)">
                    <i class="fa-solid fa-bolt"></i> Tiếp nhận ngay
                  </button>
                  <!-- Nút từ chối -->
                  <button class="btn bg-white border border-2 flex-grow-1 fw-bold py-3 rounded-4 btn-decline text-secondary d-flex justify-content-center align-items-center gap-2 text-uppercase hover-bg-light" @click="declineMission(item)">
                    <i class="fa-solid fa-xmark"></i> Từ chối
                  </button>
                </div>

                <div v-else-if="item.trang_thai_nhiem_vu === 'DANG_XU_LY'" class="accepted-state p-3 rounded-4 d-flex justify-content-between align-items-center bg-success bg-opacity-10 border border-success border-opacity-25">
                  <div class="d-flex align-items-center gap-3">
                    <div class="success-icon-animated bg-success text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                      <i class="fa-solid fa-check fs-5"></i>
                    </div>
                    <div>
                      <h6 class="mb-0 fw-bold text-success">Đã tiếp nhận</h6>
                      <small class="text-success text-opacity-75 fw-medium">Nhiệm vụ đang thực hiện</small>
                    </div>
                  </div>
                  <router-link :to="'/rescuer/dang-xu-ly'" class="btn btn-success fw-bold rounded-pill px-4 btn-sm shadow-sm d-flex align-items-center gap-2 py-2">
                    Chi tiết <i class="fa-solid fa-arrow-right"></i>
                  </router-link>
                </div>

                <div v-else-if="item.trang_thai_nhiem_vu === 'TU_CHOI'" class="declined-state p-3 rounded-4 d-flex align-items-center gap-3 justify-content-center bg-secondary bg-opacity-10 border border-secondary border-opacity-25">
                  <div class="icon-circle bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-ban"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold text-secondary">Đã từ chối</h6>
                    <small class="text-muted fw-medium">Bạn đã từ chối nhiệm vụ này</small>
                  </div>
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
export default {
  name: "ThongBaoNhiemVu",
  data() {
    return {
      loading: false,
      activeTab: 'all',
      notifications: [],
      tabs: [
        { key: 'all', label: 'Tất cả', icon: 'fa-solid fa-layer-group', count: 0 },
        { key: 'pending', label: 'Chờ xử lý', icon: 'fa-solid fa-hourglass-half', count: 0 },
        { key: 'accepted', label: 'Đã tiếp nhận', icon: 'fa-solid fa-check-double', count: 0 },
        { key: 'declined', label: 'Đã từ chối', icon: 'fa-solid fa-ban', count: 0 },
      ],
    };
  },
  computed: {
    filteredNotifications() {
      if (this.activeTab === 'all') return this.notifications;
      if (this.activeTab === 'pending') return this.notifications.filter(n => n.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN');
      if (this.activeTab === 'accepted') return this.notifications.filter(n => n.trang_thai_nhiem_vu === 'DANG_XU_LY');
      if (this.activeTab === 'declined') return this.notifications.filter(n => n.trang_thai_nhiem_vu === 'TU_CHOI');
      return this.notifications;
    },
  },
  watch: {
    notifications: {
      handler() {
        this.tabs[0].count = this.notifications.length;
        this.tabs[1].count = this.notifications.filter(n => n.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN').length;
        this.tabs[2].count = this.notifications.filter(n => n.trang_thai_nhiem_vu === 'DANG_XU_LY').length;
        this.tabs[3].count = this.notifications.filter(n => n.trang_thai_nhiem_vu === 'TU_CHOI').length;
      },
      deep: true,
    },
  },
  async mounted() {
    await this.fetchNotifications();
  },
  methods: {
    async fetchNotifications() {
      this.loading = true;
      try {
        const { rescuerAPI } = await import("../../../services/api.js");
        const res = await rescuerAPI.getAssignments({ per_page: 50 });
        if (res.data?.data?.data) {
          this.notifications = res.data.data.data;
        } else if (res.data?.data) {
          this.notifications = res.data.data;
        }
      } catch (e) {
        console.error("Lỗi tải thông báo:", e);
      } finally {
        this.loading = false;
      }
    },
    async acceptMission(assignment) {
      try {
        const { rescuerAPI } = await import("../../../services/api.js");
        await rescuerAPI.updateAssignmentStatus(assignment.id_phan_cong, {
          trang_thai_nhiem_vu: 'DANG_XU_LY'
        });
        assignment.trang_thai_nhiem_vu = 'DANG_XU_LY';
        this.$router.push("/rescuer/dang-xu-ly");
      } catch (e) {
        console.error("Lỗi tiếp nhận:", e);
      }
    },
    async declineMission(assignment) {
      if(!confirm("Bạn có chắc chắn muốn từ chối nhiệm vụ này?")) return;
      try {
        const { rescuerAPI } = await import("../../../services/api.js");
        await rescuerAPI.updateAssignmentStatus(assignment.id_phan_cong, {
          trang_thai_nhiem_vu: 'TU_CHOI'
        });
        assignment.trang_thai_nhiem_vu = 'TU_CHOI';
      } catch (e) {
        console.error("Lỗi từ chối:", e);
      }
    },
    getPriority(item) {
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || "THUONG").toUpperCase();
      if (mucDo === "KHA_CAP") return "urgent";
      if (mucDo === "TRUNG_BINH") return "medium";
      return "low";
    },
    getPriorityText(item) {
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || "THUONG").toUpperCase();
      if (mucDo === "KHA_CAP") return "KHẨN CẤP";
      if (mucDo === "TRUNG_BINH") return "TRUNG BÌNH";
      return "BÌNH THƯỜNG";
    },
    getPriorityClass(item) {
      const p = this.getPriority(item);
      if (p === 'urgent') return 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1 rounded-pill';
      if (p === 'medium') return 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-1 rounded-pill';
      return 'bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-1 rounded-pill';
    },
    getPriorityIcon(item) {
      const p = this.getPriority(item);
      if (p === 'urgent') return 'fa-solid fa-circle-exclamation';
      if (p === 'medium') return 'fa-solid fa-triangle-exclamation';
      return 'fa-solid fa-circle-info';
    },
    getCardClass(item) {
      const stt = item.trang_thai_nhiem_vu;
      if (stt === 'CHUA_TIEP_NHAN') return 'border border-danger border-opacity-25 shadow';
      if (stt === 'DANG_XU_LY') return 'border border-success border-opacity-25 shadow-sm';
      if (stt === 'TU_CHOI') return 'opacity-75 border border-secondary border-opacity-25';
      return 'border border-light shadow-sm';
    },
    getCardHeaderBg(item) {
      const p = this.getPriority(item);
      if (p === 'urgent') return 'bg-danger';
      if (p === 'medium') return 'bg-warning';
      return 'bg-info';
    }
  },
};
</script>

<style scoped>
.notification-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.bg-soft {
  background: #f4f7f6;
}

/* Header Styling */
.notif-header {
  background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
}

.header-decoration-1 {
  position: absolute;
  top: -50px;
  right: -50px;
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0) 70%);
  border-radius: 50%;
}

.header-decoration-2 {
  position: absolute;
  bottom: -30px;
  left: 10%;
  width: 150px;
  height: 150px;
  background: radial-gradient(circle, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0) 70%);
  border-radius: 50%;
}

.bg-gradient-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.notif-icon-glass {
  width: 56px;
  height: 56px;
}

/* Animations */
@keyframes ring {
  0% { transform: rotate(0); }
  10% { transform: rotate(15deg); }
  20% { transform: rotate(-10deg); }
  30% { transform: rotate(5deg); }
  40% { transform: rotate(-5deg); }
  50% { transform: rotate(0); }
  100% { transform: rotate(0); }
}

.bell-ring {
  animation: ring 3s infinite ease-in-out;
  transform-origin: top center;
}

@keyframes slideUpFade {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-up {
  opacity: 0;
  animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes pulse {
  0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { transform: scale(1.05); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
  100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

.pulse-badge {
  animation: pulse 2s infinite;
}

/* Tabs */
.active-tab {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}
.inactive-tab:hover {
  background: #e2e8f0 !important;
}

/* Cards */
.shadow-hover {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.shadow-hover:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
}

.shadow-danger {
  box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3) !important;
}

.hover-lift {
  transition: transform 0.2s ease;
}
.hover-lift:hover {
  transform: translateY(-2px);
}

.hover-bg-light:hover {
  background-color: #f8f9fa !important;
}

.card-info-hover {
  transition: all 0.2s ease;
}

.card-info-hover:hover {
  background: #f8f9fa !important;
  border-color: #e2e8f0 !important;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Empty State */
.empty-icon-container {
  font-size: 80px;
  background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
</style>

