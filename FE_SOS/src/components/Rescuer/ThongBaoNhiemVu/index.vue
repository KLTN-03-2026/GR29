<template>
  <div class="premium-notification-wrapper d-flex flex-column h-100 position-relative">
    <!-- Animated Background Map/Mesh -->
    <div class="bg-radar position-absolute w-100 h-100 pe-none">
      <div class="radar-circle rc-1"></div>
      <div class="radar-circle rc-2"></div>
      <div class="radar-circle rc-3"></div>
      <div class="radar-sweep"></div>
    </div>

    <!-- Premium Header -->
    <div class="premium-header position-relative z-2 px-4 py-4 d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-4">
        <div class="glow-icon-container position-relative">
          <div class="bg-pulse-glow"></div>
          <div class="icon-glass d-flex align-items-center justify-content-center text-white shadow-lg">
            <i class="fa-solid fa-satellite-dish fs-3 bell-wiggle"></i>
          </div>
        </div>
        <div>
          <h2 class="fw-bolder mb-1 text-gradient-primary" style="letter-spacing: -1px;">Trung Tâm Nhiệm Vụ</h2>
          <p class="text-secondary fw-semibold mb-0 d-flex align-items-center gap-2">
            <span class="live-dot pulse-danger"></span>
            Hệ thống vệ tinh & điều phối trực tiếp đang hoạt động
          </p>
        </div>
      </div>
      <div>
        <button class="btn btn-glassmorphism rounded-pill px-4 fw-bold shadow-hover text-dark d-flex align-items-center gap-2 hover-scale">
          <i class="fa-solid fa-check-double text-primary"></i> Đánh dấu tất cả đã đọc
        </button>
      </div>
    </div>

    <!-- Floating Filter Tabs -->
    <div class="filter-glass-dock mx-4 mb-4 z-2 position-relative d-flex justify-content-between align-items-center rounded-pill px-2 py-2 shadow-sm">
      <div class="d-flex gap-1" style="overflow-x: auto; scrollbar-width: none;">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          class="btn tab-btn rounded-pill px-4 py-2 fw-bold transition-all d-flex align-items-center border-0 flex-nowrap"
          :class="activeTab === tab.key ? 'active-tab shadow text-white' : 'inactive-tab text-secondary'"
          @click="activeTab = tab.key"
        >
          <i :class="[tab.icon, 'me-2']" :style="{ color: activeTab === tab.key ? 'white' : 'inherit' }"></i> 
          {{ tab.label }}
          <div
            v-if="tab.count >= 0"
            class="tab-badge ms-2 rounded-pill shadow-sm d-flex align-items-center justify-content-center fw-bolder"
            :class="activeTab === tab.key ? 'bg-white text-' + (tab.key==='pending'?'danger':'primary') : 'bg-light text-dark'"
          >
            {{ tab.count }}
          </div>
        </button>
      </div>
      <div class="text-muted small fw-bold me-3 text-uppercase letter-spacing-1 d-none d-md-block">
        <i class="fa-solid fa-list-check me-1"></i> {{ filteredNotifications.length }} Kết quả
      </div>
    </div>

    <!-- Notification List Area -->
    <div class="list-container px-4 pb-4 flex-grow-1 overflow-auto z-2">
      <!-- Loading State -->
      <div v-if="loading" class="d-flex flex-column align-items-center justify-content-center h-100">
        <div class="cyber-spinner mb-4"></div>
        <h5 class="fw-bolder text-muted tracking-widest text-uppercase">Đang mã hóa dữ liệu...</h5>
      </div>
      
      <!-- Empty State -->
      <div v-else-if="filteredNotifications.length === 0" class="empty-glass h-100 d-flex flex-column align-items-center justify-content-center p-5 text-center">
        <div class="empty-icon-hexagon mb-4 d-flex align-items-center justify-content-center">
          <i class="fa-solid fa-mug-hot fs-1 text-primary"></i>
        </div>
        <h3 class="fw-bolder text-dark mb-2">Chưa có nhiệm vụ</h3>
        <p class="text-muted fw-medium fs-5" style="max-width: 500px;">
          Đội của bạn hiện đang không có nhiệm vụ nào. Hệ thống sẽ tự động cập nhật khi có tín hiệu cấp cứu mới.
        </p>
      </div>

      <!-- Missions Grid -->
      <div v-else class="row g-4 pb-4">
        <div
          v-for="(item, index) in filteredNotifications"
          :key="item.id_phan_cong"
          class="col-12 col-xxl-6"
        >
          <div 
            class="mission-card h-100 d-flex flex-column position-relative overflow-hidden fade-in-up" 
            :style="{ animationDelay: `${index * 0.08}s` }"
            :class="[getMissionBorderClass(item), item.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN' ? 'urgent-pulse-bg' : '']"
          >
            <!-- Severity Indicator Line -->
            <div class="severity-line" :class="getSeverityBg(item)"></div>
            
            <!-- Card Body -->
            <div class="card-body p-4 d-flex flex-column h-100 position-relative z-1">
              
              <!-- Top Row: Badge & Time -->
              <div class="d-flex justify-content-between align-items-start mb-4">
                <div class="d-flex flex-column gap-2">
                   <div class="d-flex align-items-center gap-2">
                      <span class="premium-badge fw-bolder px-3 py-1 text-uppercase" :class="getSeverityBg(item)">
                        <i class="me-1" :class="getSeverityIcon(item)"></i> {{ getSeverityText(item) }}
                      </span>
                      <span v-if="!item.is_read && item.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN'" class="badge new-badge bg-danger rounded-pill shadow-danger">MỚI TỚI</span>
                   </div>
                   <h4 class="fw-black text-dark mb-0 mt-2 d-flex align-items-center gap-2" style="font-size: 1.35rem;">
                      {{ item.yeu_cau?.loai_su_co?.ten_loai_su_co || 'YÊU CẦU CỨU HỘ KHẨN' }}
                   </h4>
                </div>
                <div class="time-glass px-3 py-1 rounded-pill fw-bold text-muted small d-flex align-items-center gap-1 shadow-sm">
                  <i class="fa-regular fa-clock text-primary"></i> <span class="time-text">{{ item.created_at || 'Vừa xong' }}</span>
                </div>
              </div>

              <!-- Context Box (Quote) -->
              <div class="context-box p-3 rounded-4 mb-4 position-relative">
                <div class="quote-mark position-absolute top-0 start-0 opacity-10 ms-2 mt-1">
                  <i class="fa-solid fa-quote-left fs-1 text-dark"></i>
                </div>
                <p class="mb-0 text-dark fw-medium position-relative z-1 ps-2" style="font-size: 0.95rem; line-height: 1.5;">
                  {{ item.yeu_cau?.chi_tiet || item.mo_ta || 'Không có mô tả chi tiết, vui lòng liên hệ trực tiếp người báo nạn ngay lập tức.' }}
                </p>
              </div>

              <!-- Info Grid -->
              <div class="row g-3 mb-4 mt-auto">
                <div class="col-sm-6">
                  <div class="info-block d-flex align-items-center p-3 rounded-4 h-100">
                    <div class="icon-blob blob-blue me-3 d-flex justify-content-center align-items-center">
                       <i class="fa-solid fa-user-injured fs-5 text-primary"></i>
                    </div>
                    <div class="w-100 overflow-hidden">
                      <div class="label-tiny">NẠN NHÂN</div>
                      <div class="fw-bold text-dark text-truncate fs-6">{{ item.yeu_cau?.ho_ten_nguoi_dung || 'Không rõ lai lịch' }}</div>
                      <div class="mt-1">
                        <a v-if="item.yeu_cau?.so_dien_thoai_nguoi_dung" :href="'tel:' + item.yeu_cau.so_dien_thoai_nguoi_dung" class="phone-chip text-decoration-none fw-bold small">
                          <i class="fa-solid fa-phone me-1"></i> GỌI NGAY
                        </a>
                        <span v-else class="text-muted small fw-medium">Không có SĐT</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-sm-6">
                  <div class="info-block d-flex align-items-center p-3 rounded-4 h-100">
                    <div class="icon-blob blob-red me-3 d-flex justify-content-center align-items-center">
                       <i class="fa-solid fa-location-crosshairs fs-5 text-danger"></i>
                    </div>
                    <div class="w-100 overflow-hidden">
                      <div class="label-tiny">ĐỊA ĐIỂM (GPS CAO)</div>
                      <div class="fw-bold text-dark small text-truncate-2 mt-1" :title="item.yeu_cau?.dia_chi" style="line-height: 1.4;">
                        {{ item.yeu_cau?.dia_chi || 'Chưa cập nhật tọa độ' }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Bottom Actions -->
              <div class="action-dock p-2 rounded-4" v-if="item.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN'">
                <div class="row g-2">
                  <div class="col-7">
                    <button class="btn btn-accept-ultimate w-100 h-100 rounded-3 fw-bold d-flex flex-column justify-content-center align-items-center position-relative overflow-hidden group-hover" @click="acceptMission(item)">
                      <div class="sweep-bg"></div>
                      <span class="z-1 d-flex align-items-center gap-2 fs-5"><i class="fa-solid fa-truck-fast"></i> TIẾP NHẬN</span>
                      <small class="z-1 fw-medium opacity-75" style="font-size: 10px;">KHỞI HÀNH NGAY</small>
                    </button>
                  </div>
                  <div class="col-5">
                    <button class="btn btn-decline-ultimate w-100 h-100 rounded-3 fw-bold d-flex flex-column justify-content-center align-items-center" @click="declineMission(item)">
                      <span class="d-flex align-items-center gap-2"><i class="fa-solid fa-ban"></i> BỎ QUA</span>
                      <small class="fw-medium text-muted" style="font-size: 10px;">TỪ CHỐI NHIỆM VỤ</small>
                    </button>
                  </div>
                </div>
              </div>

              <div v-else-if="item.trang_thai_nhiem_vu === 'DANG_XU_LY'" class="status-dock accepted-bg p-3 rounded-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                  <div class="status-icon bg-success text-white d-flex justify-content-center align-items-center shadow-success">
                    <i class="fa-solid fa-shield-check fs-5"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bolder text-success fs-5">Đội Đang Xử Lý</h6>
                    <small class="text-success text-opacity-75 fw-semibold">Đảm bảo an toàn tuyệt đối</small>
                  </div>
                </div>
                <router-link :to="'/rescuer/dang-xu-ly'" class="btn btn-success-glass fw-bold rounded-pill px-4 py-2 hover-scale">
                  Xem live <i class="fa-solid fa-arrow-right ms-1"></i>
                </router-link>
              </div>

              <div v-else-if="item.trang_thai_nhiem_vu === 'TU_CHOI'" class="status-dock declined-bg p-3 rounded-4 d-flex justify-content-center align-items-center gap-3">
                <div class="status-icon bg-secondary text-white d-flex justify-content-center align-items-center">
                  <i class="fa-solid fa-xmark fs-5"></i>
                </div>
                <div class="text-center text-md-start">
                  <h6 class="mb-0 fw-bolder text-secondary fs-5">Nhiệm Vụ Đã Từ Chối</h6>
                  <small class="text-muted fw-medium">Hệ thống đã điều phối cho đội khác.</small>
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
  name: "ThongBaoNhiemVuPremium",
  data() {
    return {
      loading: false,
      activeTab: 'all',
      notifications: [],
      tabs: [
        { key: 'all', label: 'Tổng Quan', icon: 'fa-solid fa-border-all', count: 0 },
        { key: 'pending', label: 'Chờ Chỉ Thị', icon: 'fa-solid fa-tower-broadcast', count: 0 },
        { key: 'accepted', label: 'Đang Thực Thi', icon: 'fa-solid fa-truck-fast', count: 0 },
        { key: 'declined', label: 'Bỏ Qua', icon: 'fa-solid fa-clock-rotate-left', count: 0 },
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
      if(!confirm("Cảnh báo: Từ chối có thể ảnh hưởng đến tỷ lệ phản ứng của đội. Bạn có chắc chắn?")) return;
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
    getSeverity(item) {
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || "THUONG").toUpperCase();
      if (mucDo === "KHA_CAP") return "urgent";
      if (mucDo === "TRUNG_BINH") return "medium";
      return "low";
    },
    getSeverityText(item) {
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || "THUONG").toUpperCase();
      if (mucDo === "KHA_CAP") return "CẤP CỨU ĐỎ";
      if (mucDo === "TRUNG_BINH") return "NGHIÊM TRỌNG";
      return "THÔNG THƯỜNG";
    },
    getSeverityBg(item) {
      const s = this.getSeverity(item);
      if (s === 'urgent') return 'bg-danger text-white border-danger shadow-danger';
      if (s === 'medium') return 'bg-warning text-dark border-warning shadow-warning';
      return 'bg-info text-white border-info shadow-info';
    },
    getSeverityIcon(item) {
      const s = this.getSeverity(item);
      if (s === 'urgent') return 'fa-solid fa-siren-on fa-burst';
      if (s === 'medium') return 'fa-solid fa-triangle-exclamation';
      return 'fa-solid fa-flag';
    },
    getMissionBorderClass(item) {
       const stt = item.trang_thai_nhiem_vu;
       const s = this.getSeverity(item);
       if(stt === 'CHUA_TIEP_NHAN') {
          if (s === 'urgent') return 'border-danger glow-border-danger';
          if (s === 'medium') return 'border-warning glow-border-warning';
          return 'border-info glow-border-info';
       }
       if(stt === 'DANG_XU_LY') return 'border-success opacity-90';
       return 'border-secondary opacity-75 grayscale-sm';
    }
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

.premium-notification-wrapper {
  margin: -0.5rem -0.5rem;
  height: calc(100vh - 80px);
  font-family: 'Outfit', sans-serif;
  background: #f8fafc;
  overflow: hidden;
  border-radius: 1.5rem;
  box-shadow: inset 0 0 50px rgba(0,0,0,0.02);
}

/* Background Radar Effect */
.bg-radar {
  top: 0; left: 0;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 0.4;
}

.radar-circle {
  position: absolute;
  border-radius: 50%;
  border: 1px solid rgba(14, 165, 233, 0.1);
  box-shadow: inset 0 0 40px rgba(14, 165, 233, 0.05);
}
.rc-1 { width: 300px; height: 300px; }
.rc-2 { width: 600px; height: 600px; }
.rc-3 { width: 900px; height: 900px; }

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.radar-sweep {
  position: absolute;
  width: 450px; height: 450px;
  border-radius: 50% 0 50% 0;
  background: conic-gradient(from 0deg, transparent 70%, rgba(14, 165, 233, 0.2) 100%);
  transform-origin: center;
  animation: spin 8s linear infinite;
}

/* Header */
.text-gradient-primary {
  background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.bg-pulse-glow {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 100%; height: 100%;
  background: #0ea5e9;
  border-radius: 50%;
  filter: blur(15px);
  opacity: 0.5;
  animation: pulse-op 3s infinite;
}

.icon-glass {
  width: 65px; height: 65px;
  background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
  border-radius: 1.2rem;
  border: 1px solid rgba(255,255,255,0.4);
  backdrop-filter: blur(10px);
  position: relative;
  z-index: 1;
}

.live-dot {
  width: 10px; height: 10px;
  background: #ef4444;
  border-radius: 50%;
  display: inline-block;
}

.pulse-danger {
  box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  animation: pulse-danger-anim 2s infinite;
}

@keyframes pulse-danger-anim {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

@keyframes wiggle {
  0%, 10%, 100% { transform: rotate(0); }
  2%, 6% { transform: rotate(-15deg) scale(1.1); }
  4%, 8% { transform: rotate(15deg) scale(1.1); }
}
.bell-wiggle {
  animation: wiggle 4s infinite;
}

/* Glass Buttons */
.btn-glassmorphism {
  background: rgba(255,255,255,0.7);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,1);
}
.hover-scale { transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.hover-scale:hover { transform: scale(1.05); }

/* Filter Tabs Dock */
.filter-glass-dock {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.8);
}

.tab-btn {
  font-size: 0.95rem;
}
.active-tab {
  background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
}
.inactive-tab:hover {
  background: rgba(255,255,255,0.8);
}
.tab-badge {
  min-width: 24px;
  height: 24px;
  font-size: 0.75rem;
}
.letter-spacing-1 { letter-spacing: 1px; }

/* Mission Cards */
.mission-card {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(16px);
  border-radius: 1.5rem;
  border-width: 2px;
  border-style: solid;
  transition: all 0.3s ease;
  box-shadow: 0 10px 30px rgba(0,0,0,0.03);
}
.mission-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

/* Glow Borders */
.glow-border-danger { box-shadow: 0 0 25px rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.5) !important; }
.glow-border-warning { box-shadow: 0 0 25px rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.5) !important; }
.glow-border-info    { box-shadow: 0 0 25px rgba(14, 165, 233, 0.15); border-color: rgba(14, 165, 233, 0.5) !important; }

.urgent-pulse-bg {
  animation: urgent-bg 3s infinite alternate;
}
@keyframes urgent-bg {
  from { background: rgba(255, 255, 255, 0.85); }
  to { background: rgba(255, 241, 242, 0.95); }
}

.severity-line {
  position: absolute;
  top: 0; left: 0; bottom: 0;
  width: 6px;
  z-index: 2;
}

/* Badges & Text */
.premium-badge {
  border-radius: 0.5rem;
  font-size: 0.8rem;
  letter-spacing: 0.5px;
}
.time-glass {
  background: rgba(255,255,255,0.6);
  border: 1px solid rgba(255,255,255,0.8);
}
.time-text { font-family: monospace; font-size: 0.9rem; }

.fw-black { font-weight: 900; }

.context-box {
  background: rgba(241, 245, 249, 0.5);
  border: 1px solid rgba(226, 232, 240, 0.8);
}

/* Info Blocks */
.info-block {
  background: rgba(255, 255, 255, 0.5);
  border: 1px solid rgba(0,0,0,0.03);
  transition: all 0.2s;
}
.info-block:hover {
  background: white;
  box-shadow: 0 5px 15px rgba(0,0,0,0.03);
}

.label-tiny {
  font-size: 0.65rem;
  font-weight: 800;
  color: #64748b;
  letter-spacing: 1px;
}

.icon-blob {
  width: 45px; height: 45px;
  border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
  animation: morph 3s ease-in-out infinite;
}
.blob-blue { background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.2); }
.blob-red { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); }

@keyframes morph {
  0%, 100% { border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; }
  34% { border-radius: 70% 30% 50% 50% / 30% 30% 70% 70%; }
  67% { border-radius: 100% 60% 60% 100% / 100% 100% 60% 60%; }
}

.phone-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.6rem;
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border-radius: 1rem;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Actions */
.action-dock {
  background: rgba(241, 245, 249, 0.6);
  border: 1px solid rgba(255,255,255,0.7);
}

.btn-accept-ultimate {
  background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
  color: white;
  border: none;
  padding: 0.8rem;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
  box-shadow: 0 10px 20px rgba(239, 68, 68, 0.25);
  transition: all 0.3s;
}
.btn-accept-ultimate:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 25px rgba(239, 68, 68, 0.4);
}

.sweep-bg {
  position: absolute;
  top: 0; left: -100%;
  width: 50%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transform: skewX(-20deg);
  transition: all 0.5s;
}
.btn-accept-ultimate:hover .sweep-bg { left: 200%; }

.btn-decline-ultimate {
  background: white;
  color: #64748b;
  border: 2px solid #e2e8f0;
  padding: 0.8rem;
  transition: all 0.2s;
}
.btn-decline-ultimate:hover {
  background: #f8fafc;
  color: #475569;
  border-color: #cbd5e1;
}

/* Status Dock */
.status-dock {
  border: 1px solid rgba(255,255,255,0.8);
}
.accepted-bg { background: rgba(220, 252, 231, 0.6); border-color: rgba(134, 239, 172, 0.5) !important;}
.declined-bg { background: rgba(241, 245, 249, 0.8); }

.status-icon {
  width: 48px; height: 48px;
  border-radius: 1rem;
}
.shadow-success { box-shadow: 0 8px 15px rgba(34, 197, 94, 0.25); }

.btn-success-glass {
  background: white;
  color: #16a34a;
  border: 1px solid #16a34a;
  box-shadow: 0 4px 10px rgba(22, 163, 74, 0.1);
}

/* Utilities & Animations */
.shadow-danger { box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); }
.shadow-warning { box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3); }
.shadow-info { box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3); }

.fade-in-up {
  opacity: 0;
  animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(40px); }
  to { opacity: 1; transform: translateY(0); }
}

.grayscale-sm { filter: grayscale(40%); }

/* Spinner */
.cyber-spinner {
  width: 60px; height: 60px;
  border: 4px solid rgba(14, 165, 233, 0.2);
  border-top-color: #0ea5e9;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  box-shadow: 0 0 20px rgba(14, 165, 233, 0.3);
}

/* Scrollbar */
.list-container::-webkit-scrollbar { width: 6px; }
.list-container::-webkit-scrollbar-track { background: transparent; }
.list-container::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
.list-container::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.2); }
</style>

