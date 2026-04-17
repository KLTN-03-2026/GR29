<template>
  <div class="emergency-dashboard-wrapper">
    <!-- Header -->
    <div class="dashboard-header d-flex justify-content-between align-items-center">
      <div>
        <h1 class="dashboard-title">Hệ Thống Nhiệm Vụ</h1>
        <p class="dashboard-subtitle">Trung tâm điều phối & phản ứng nhanh trực tuyến</p>
      </div>
    </div>

    <!-- 1. SUMMARY SECTION (TOP) -->
    <div class="summary-grid">
      <!-- Total -->
      <div class="summary-card">
        <div class="s-label">TỔNG NHIỆM VỤ</div>
        <div class="s-val">{{ notifications.length }}</div>
      </div>
      <!-- CRITICAL -->
      <div class="summary-card s-critical">
        <div class="s-label">YÊU CẦU KHẨN CẤP / ĐỎ</div>
        <div class="s-val">{{ notifications.filter(n => (n.yeu_cau?.muc_do_khan_cap || 'THUONG').toUpperCase() === 'KHA_CAP').length }}</div>
      </div>
      <!-- Active -->
      <div class="summary-card s-active">
        <div class="s-label">ĐANG THỰC THI</div>
        <div class="s-val" style="color: #1d4ed8;">{{ notifications.filter(n => n.trang_thai_nhiem_vu === 'DANG_XU_LY').length }}</div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="tabs-nav">
      <button 
        v-for="tab in tabs" :key="tab.key"
        class="tab-btn" :class="{ 'active': activeTab === tab.key }"
        @click="activeTab = tab.key"
      >
        <i :class="tab.icon"></i> {{ tab.label }}
        <span class="badge-count" v-if="tab.count >= 0">{{ tab.count }}</span>
      </button>
    </div>

    <!-- 2. INCIDENT LIST (MAIN) -->
    <div class="incident-list-container">
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <h5 class="mt-3 text-muted fw-bold">Đang đồng bộ dữ liệu hệ thống...</h5>
      </div>
      
      <div v-else-if="filteredNotifications.length === 0" class="empty-state text-center py-5">
        <div class="empty-icon text-muted mb-3">
          <i class="fa-solid fa-bed-pulse fa-4x opacity-50"></i>
        </div>
        <h3 class="fw-bold text-dark mb-2">Chưa có nhiệm vụ</h3>
        <p class="text-secondary fw-medium fs-5">Hệ thống sẽ tự động phát tín hiệu khi có yêu cầu cấp cứu.</p>
      </div>

      <div v-else class="incident-list">
        <!-- Flexbox order ensures critical is on top -->
        <div 
          v-for="item in filteredNotifications" 
          :key="item.id_phan_cong"
          class="incident-card"
          :class="{ 'is-critical': getSeverity(item) === 'urgent' }"
          :style="{ order: getSeverity(item) === 'urgent' ? -1 : 0 }"
        >
          <!-- 1. Emergency level (MOST PROMINENT) -->
          <div class="level-banner d-flex align-items-center justify-content-between" :class="'level-' + getSeverity(item)">
            <span class="d-flex align-items-center gap-2">
              <i :class="getSeverityIcon(item)"></i>
              {{ getSeverityText(item) }}
            </span>
            <span class="time-stamp">
              <i class="fa-regular fa-clock me-1"></i> {{ item.created_at || 'Vừa xong' }}
            </span>
          </div>

          <!-- Card Content Body -->
          <div class="card-content">
             <!-- 2. Title -->
             <h2 class="event-title mb-2">
               {{ item.yeu_cau?.loai_su_co?.ten_loai_su_co || 'YÊU CẦU CỨU HỘ KHẨN' }}
             </h2>
             
             <!-- 3. Status -->
             <div class="mb-3">
               <span class="status-pill" :class="'status-' + item.trang_thai_nhiem_vu.toLowerCase()">
                 <i v-if="item.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN'" class="fa-solid fa-radar fa-spin me-1"></i>
                 <i v-if="item.trang_thai_nhiem_vu === 'DANG_XU_LY'" class="fa-solid fa-truck-fast me-1"></i>
                 <i v-if="item.trang_thai_nhiem_vu === 'TU_CHOI'" class="fa-solid fa-xmark me-1"></i>
                 {{ item.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN' ? 'Đang Chờ Tiếp Nhận' : (item.trang_thai_nhiem_vu === 'DANG_XU_LY' ? 'Đang Xử Lý' : 'Đã Từ Chối') }}
               </span>
             </div>

             <!-- 4. Details -->
             <div class="details-layout">
                <!-- Thumbnail -->
                <div class="thumbnail-col d-none d-md-block">
                   <div class="image-box">
                      <img v-if="item.yeu_cau?.hinh_anh" :src="item.yeu_cau.hinh_anh" alt="Tham chiếu sự cố" />
                      <div v-else class="image-placeholder">
                         <i class="fa-solid fa-image fa-2x"></i>
                      </div>
                   </div>
                </div>
                
                <!-- Info content -->
                <div class="info-col">
                   <div class="description-box mb-3">
                      <i class="fa-solid fa-quote-left text-black-50 me-2"></i>
                      {{ item.yeu_cau?.chi_tiet || item.mo_ta || 'Không có mô tả chi tiết, vui lòng liên hệ trực tiếp người báo nạn ngay lập tức.' }}
                   </div>

                   <div class="meta-data-grid">
                      <!-- GPS -->
                      <div class="meta-item">
                         <div class="meta-label">VỊ TRÍ (TỌA ĐỘ GPS)</div>
                         <div class="meta-value text-dark fw-bold">
                            <i class="fa-solid fa-location-crosshairs text-danger me-1"></i> 
                            {{ item.yeu_cau?.dia_chi || 'Chưa cập nhật tọa độ' }}
                         </div>
                      </div>
                      <!-- Caller -->
                      <div class="meta-item">
                         <div class="meta-label">THÔNG TIN NẠN NHÂN</div>
                         <div class="meta-value text-dark fw-bold d-flex flex-wrap align-items-center gap-2">
                            <i class="fa-solid fa-user-injured text-primary"></i>
                            <span>{{ item.yeu_cau?.ho_ten_nguoi_dung || 'Không rõ lai lịch' }}</span>
                            <a v-if="item.yeu_cau?.so_dien_thoai_nguoi_dung" :href="'tel:' + item.yeu_cau.so_dien_thoai_nguoi_dung" class="action-link outline ms-auto">
                               <i class="fa-solid fa-phone me-1"></i> GỌI NGAY
                            </a>
                         </div>
                      </div>
                   </div>
                </div>
             </div>

             <!-- 5. Action Bar -->
             <div class="action-footer mt-4" v-if="item.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN'">
                <button class="btn btn-primary-action w-100" @click="acceptMission(item)">
                   <i class="fa-solid fa-bolt me-2"></i> XỬ LÝ NGAY
                </button>
                <div class="text-center">
                  <button class="btn btn-decline w-100 mt-2" @click="declineMission(item)">
                     Bỏ qua nhiệm vụ này
                  </button>
                </div>
             </div>
             
             <div class="action-footer mt-4 active-state" v-else-if="item.trang_thai_nhiem_vu === 'DANG_XU_LY'">
                <div class="text-success fw-bolder fs-5 d-flex align-items-center gap-2">
                  <span class="pulse-indicator"></span> Đội đang xử lý nhiệm vụ
                </div>
                <router-link to="/rescuer/dang-xu-ly" class="btn btn-track">
                  Live Tracking <i class="fa-solid fa-arrow-right ms-2"></i>
                </router-link>
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
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');

.emergency-dashboard-wrapper {
  background-color: #e2e8f0;
  min-height: calc(100vh - 60px);
  padding: 2rem;
  box-sizing: border-box;
  font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
  overflow-x: hidden;
}

.dashboard-header {
  margin-bottom: 2rem;
}
.dashboard-title {
  font-size: 2.25rem;
  font-weight: 900;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.5px;
}
.dashboard-subtitle {
  font-size: 1.05rem;
  color: #64748b;
  font-weight: 600;
  margin-top: 0.25rem;
}

/* 1. Summary Section */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}
@media (max-width: 991px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }
}

.summary-card {
  background-color: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  padding: 1.5rem 2rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}
.summary-card .s-label {
  font-size: 0.85rem;
  font-weight: 800;
  color: #64748b;
  letter-spacing: 1px;
  margin-bottom: 0.5rem;
}
.summary-card .s-val {
  font-size: 3rem;
  font-weight: 900;
  color: #0f172a;
  line-height: 1;
}

.summary-card.s-critical {
  background-color: #dc2626;
  border-color: #b91c1c;
  box-shadow: 0 10px 20px -5px rgba(220, 38, 38, 0.35);
}
.summary-card.s-critical .s-label { color: rgba(255,255,255,0.9); }
.summary-card.s-critical .s-val { color: #ffffff; }

.summary-card.s-active {
  background-color: #f1f5f9;
  border-bottom: 4px solid #1d4ed8;
}

/* Tabs */
.tabs-nav {
  display: flex;
  gap: 0.5rem;
  background-color: #f1f5f9;
  padding: 0.5rem;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  margin-bottom: 2.5rem;
  overflow-x: auto;
  scrollbar-width: none;
}
.tab-btn {
  background: transparent;
  color: #475569;
  border: none;
  font-weight: 700;
  padding: 0.75rem 1.25rem;
  border-radius: 6px;
  white-space: nowrap;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
}
.tab-btn:hover {
  background-color: #e2e8f0;
  color: #0f172a;
}
.tab-btn.active {
  background-color: #ffffff;
  color: #1d4ed8;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  border: 1px solid #cbd5e1;
}
.badge-count {
  background-color: #1d4ed8;
  color: #fff;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  margin-left: 8px;
  font-weight: 800;
}

/* 2. Incident List & Cards */
.incident-list-container {
  display: flex;
  flex-direction: column;
}

.incident-list {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  padding-bottom: 3rem;
}

.incident-card {
  background-color: #ffffff;
  border-radius: 16px;
  border: 1px solid #cbd5e1;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  overflow: hidden;
}
.incident-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 15px 30px -5px rgba(0,0,0,0.1), 0 8px 10px -5px rgba(0,0,0,0.05);
}

.incident-card.is-critical {
  border: 2px solid #dc2626;
  box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
}
.incident-card.is-critical:hover {
  box-shadow: 0 20px 40px -10px rgba(220, 38, 38, 0.3);
}

/* Hierarchy 1: Emergency Banner */
.level-banner {
  padding: 1.25rem 2rem;
  font-size: 1.15rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #fff;
  letter-spacing: 0.5px;
}
.level-urgent {
  background-color: #dc2626;
  font-size: 1.4rem;
}
.level-medium { background-color: #f59e0b; }
.level-low { background-color: #22c55e; }

.time-stamp {
  font-size: 0.85rem;
  font-weight: 700;
  background-color: rgba(0,0,0,0.25);
  padding: 6px 12px;
  border-radius: 6px;
  letter-spacing: 0;
}

/* Hierarchy 2-4: Content */
.card-content {
  padding: 2rem;
}

.event-title {
  font-size: 1.75rem;
  font-weight: 900;
  color: #0f172a;
  margin-top: 0;
  margin-bottom: 0.75rem;
  letter-spacing: -0.5px;
}
.is-critical .event-title { color: #b91c1c; }

.status-pill {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 800;
  text-transform: uppercase;
  border: 1px solid #cbd5e1;
}
.status-chua_tiep_nhan {
  background-color: #fef08a;
  color: #854d0e;
  border-color: #fde047;
}
.status-dang_xu_ly {
  background-color: #dcfce7;
  color: #166534;
  border-color: #86efac;
}
.status-tu_choi {
  background-color: #f1f5f9;
  color: #64748b;
  text-decoration: line-through;
}

.details-layout {
  display: flex;
  gap: 2rem;
  margin-top: 1.5rem;
}
@media (max-width: 768px) {
  .details-layout {
    flex-direction: column;
  }
}

.thumbnail-col {
  width: 160px;
  height: 160px;
  flex-shrink: 0;
}
.image-box {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #cbd5e1;
  background-color: #f1f5f9;
}
.image-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
}

.info-col {
  flex-grow: 1;
}
.description-box {
  padding: 1.25rem;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1.05rem;
  color: #334155;
  line-height: 1.6;
  font-weight: 600;
}

.meta-data-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-top: 1.5rem;
}
@media (max-width: 991px) {
  .meta-data-grid { grid-template-columns: 1fr; }
}

.meta-item {
  padding: 1.25rem;
  border-radius: 8px;
  background-color: #f1f5f9;
  border: 1px solid #e2e8f0;
}
.meta-label {
  font-size: 0.75rem;
  font-weight: 800;
  color: #64748b;
  margin-bottom: 0.5rem;
  letter-spacing: 0.5px;
}
.meta-value {
  font-size: 1.05rem;
  color: #0f172a;
}
.action-link.outline {
  font-size: 0.8rem;
  font-weight: 800;
  color: #1d4ed8;
  border: 2px solid #1d4ed8;
  padding: 4px 10px;
  border-radius: 6px;
  text-decoration: none;
  background-color: transparent;
  transition: all 0.2s;
  white-space: nowrap;
}
.action-link.outline:hover {
  background-color: #1d4ed8;
  color: #ffffff;
}

/* Hierarchy 5: Action Button */
.action-footer {
  border-top: 1px solid #e2e8f0;
  padding-top: 2rem;
  margin-top: 2rem;
}

.btn-primary-action {
  background-color: #1d4ed8;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 1.2rem;
  font-weight: 800;
  padding: 1.25rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0 10px 15px -3px rgba(29, 78, 216, 0.3);
  transition: all 0.2s ease;
  cursor: pointer;
}
.btn-primary-action:hover {
  background-color: #1e40af;
  box-shadow: 0 15px 25px -4px rgba(30, 64, 175, 0.4);
  transform: translateY(-2px);
  color: #ffffff;
}

.btn-decline {
  background-color: transparent;
  color: #64748b;
  border: 2px solid transparent;
  font-weight: 700;
  font-size: 0.95rem;
  padding: 0.75rem;
  border-radius: 8px;
  transition: all 0.2s;
}
.btn-decline:hover {
  background-color: #f1f5f9;
  color: #334155;
}

.active-state {
  background-color: #f0fdf4;
  border-radius: 8px;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid #bbf7d0;
}

.pulse-indicator {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background-color: #22c55e;
  box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  animation: pulse-green 2s infinite;
}
@keyframes pulse-green {
  0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
  70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
  100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}

.btn-track {
  display: inline-flex;
  align-items: center;
  background-color: #ffffff;
  color: #16a34a;
  border: 2px solid #16a34a;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 800;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-track:hover {
  background-color: #16a34a;
  color: #ffffff;
  box-shadow: 0 4px 10px rgba(22, 163, 74, 0.2);
}
</style>
