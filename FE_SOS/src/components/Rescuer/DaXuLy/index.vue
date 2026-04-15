<template>
  <div class="completed-wrapper">
    <!-- Header -->
    <div class="completed-header px-4 py-3 d-flex align-items-center justify-content-between border-bottom bg-white shadow-sm">
      <div class="d-flex align-items-center">
        <div class="header-icon me-3 d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 48px; height: 48px;">
          <i class="fa-solid fa-circle-check fs-4"></i>
        </div>
        <div>
          <h5 class="fw-bold mb-0 text-dark">Nhiệm Vụ Đã Xử Lý</h5>
          <span class="text-muted small">{{ completedMissions.length }} nhiệm vụ hoàn thành</span>
        </div>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <select class="form-select form-select-sm rounded-pill px-3" style="width: auto;" v-model="filterMonth">
          <option value="all">Tất cả tháng</option>
          <option value="04">Tháng 4/2026</option>
          <option value="03">Tháng 3/2026</option>
          <option value="02">Tháng 2/2026</option>
        </select>
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" @click="exportReport">
          <i class="fa-solid fa-download me-1"></i> Xuất báo cáo
        </button>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row px-4 py-3 border-bottom bg-white">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-check"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ completedMissions.length }}</div>
            <div class="text-muted small fw-medium">Hoàn thành</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ urgentCount }}</div>
            <div class="text-muted small fw-medium">Khẩn cấp</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-info bg-opacity-10 text-info" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-clock"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ avgTime }}</div>
            <div class="text-muted small fw-medium">TB thời gian</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-star"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ avgRating }}<span class="fs-6">/5</span></div>
            <div class="text-muted small fw-medium">Đánh giá TB</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="completed-body px-4 py-3 flex-grow-1 overflow-auto">
      <!-- Filter Tabs -->
      <div class="filter-tabs d-flex gap-2 mb-4 flex-wrap">
        <button
          v-for="tab in filterTabs"
          :key="tab.key"
          class="btn btn-sm rounded-pill px-3 fw-semibold transition-all"
          :class="activeFilter === tab.key ? 'btn-danger text-white shadow-sm' : 'bg-light text-secondary border-0'"
          @click="activeFilter = tab.key"
        >
          {{ tab.label }}
          <span class="badge bg-white text-danger ms-1 rounded-pill py-1 px-2" style="font-size: 10px;">{{ tab.count }}</span>
        </button>
      </div>

      <!-- Mission Cards -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-danger" role="status"></div>
      </div>
      <div v-else-if="filteredMissions.length === 0" class="text-center py-5">
        <div class="mb-3">
          <i class="fa-solid fa-clipboard text-secondary opacity-25" style="font-size: 64px;"></i>
        </div>
        <h6 class="text-secondary fw-bold">Không có nhiệm vụ nào</h6>
        <p class="text-muted small">Các nhiệm vụ đã hoàn thành sẽ hiển thị tại đây</p>
      </div>

      <div v-else class="d-flex flex-column gap-3">
        <div
          v-for="item in filteredMissions"
          :key="item.id_phan_cong"
          class="mission-card card border-0 shadow-sm rounded-4 overflow-hidden hover-card transition-all"
        >
          <div class="card-body p-4">
            <!-- Top Row -->
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-3 py-2 fw-bold border" :class="getPriorityClass(item)">
                  <i class="fa-solid fa-circle-exclamation me-1" v-if="getPriority(item) === 'urgent'"></i>
                  {{ getPriorityText(item) }}
                </span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2 fw-bold">
                  <i class="fa-solid fa-check me-1"></i> HOÀN THÀNH
                </span>
              </div>
              <div class="text-end">
                <div class="text-muted small fw-medium"><i class="fa-regular fa-calendar me-1 text-secondary"></i>{{ item.updated_at || item.created_at }}</div>
              </div>
            </div>

            <!-- Title & Type -->
            <h5 class="fw-bold text-dark mb-2">{{ item.yeu_cau?.loai_su_co?.ten_loai_su_co || 'Yêu cầu cứu hộ' }}</h5>
            <p class="text-muted small mb-3">
              <i class="fa-solid fa-layer-group me-1 text-danger"></i>
              {{ item.yeu_cau?.muc_do_khan_cap || '-' }}
            </p>

            <!-- Info Grid -->
            <div class="info-grid mb-3">
              <div class="info-item">
                <div class="d-flex align-items-start">
                  <div class="info-icon me-2 text-danger mt-1"><i class="fa-solid fa-location-dot"></i></div>
                  <div>
                    <div class="text-muted fw-bold" style="font-size: 9px; letter-spacing: 0.5px;">ĐỊA ĐIỂM</div>
                    <div class="fw-semibold text-dark small">{{ item.yeu_cau?.dia_chi || '-' }}</div>
                  </div>
                </div>
              </div>
              <div class="info-item">
                <div class="d-flex align-items-start">
                  <div class="info-icon me-2 text-primary mt-1"><i class="fa-solid fa-user-injured"></i></div>
                  <div>
                    <div class="text-muted fw-bold" style="font-size: 9px; letter-spacing: 0.5px;">NGƯỜI GẶP NẠN</div>
                    <div class="fw-semibold text-dark small">{{ item.yeu_cau?.ho_ten_nguoi_dung || '-' }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Rating -->
            <div class="rating-row d-flex align-items-center justify-content-between bg-light rounded-3 p-3 border border-light">
              <div class="d-flex align-items-center">
                <div class="text-muted fw-bold me-3" style="font-size: 10px; letter-spacing: 0.5px;">TRẠNG THÁI</div>
                <span class="badge bg-success rounded-pill px-3 py-1">{{ item.trang_thai_nhiem_vu }}</span>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold" @click="viewDetail(item)">
                  <i class="fa-solid fa-eye me-1"></i> Chi tiết
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-row px-4 py-3 border-top bg-white" v-if="filteredMissions.length > 0">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted small fw-medium">
          Hiển thị {{ filteredMissions.length }} / {{ completedMissions.length }} nhiệm vụ
        </div>
        <nav>
          <ul class="pagination pagination-sm mb-0 gap-1">
            <li class="page-item disabled"><a class="page-link rounded-pill px-3" href="#"><i class="fa-solid fa-chevron-left"></i></a></li>
            <li class="page-item active"><a class="page-link rounded-pill bg-danger border-danger px-3" href="#">1</a></li>
            <li class="page-item"><a class="page-link rounded-pill px-3 text-secondary" href="#">2</a></li>
            <li class="page-item"><a class="page-link rounded-pill px-3 text-secondary" href="#">3</a></li>
            <li class="page-item"><a class="page-link rounded-pill px-3 text-secondary" href="#"><i class="fa-solid fa-chevron-right"></i></a></li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "DaXuLy",
  data() {
    return {
      loading: false,
      filterMonth: 'all',
      activeFilter: 'all',
      completedMissions: [],
    };
  },
  computed: {
    filterTabs() {
      return [
        { key: 'all', label: 'Tất cả', count: this.completedMissions.length },
        { key: 'urgent', label: 'Khẩn cấp', count: this.completedMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'KHA_CAP').length },
        { key: 'medium', label: 'Trung bình', count: this.completedMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'TRUNG_BINH').length },
        { key: 'low', label: 'Thường', count: this.completedMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'THUONG').length },
      ];
    },
    filteredMissions() {
      if (this.activeFilter === 'all') return this.completedMissions;
      if (this.activeFilter === 'urgent') return this.completedMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'KHA_CAP');
      if (this.activeFilter === 'medium') return this.completedMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'TRUNG_BINH');
      if (this.activeFilter === 'low') return this.completedMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'THUONG');
      return this.completedMissions;
    },
    urgentCount() {
      return this.completedMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'KHA_CAP').length;
    },
    avgTime() {
      return '28 ph';
    },
    avgRating() {
      return 4.8;
    },
  },
  async mounted() {
    await this.fetchCompletedMissions();
  },
  methods: {
    async fetchCompletedMissions() {
      this.loading = true;
      try {
        const { rescuerAPI } = await import("../../../services/api.js");
        const res = await rescuerAPI.getAssignments({ per_page: 50 });
        if (res.data?.data?.data) {
          this.completedMissions = res.data.data.data.filter(
            (m) => m.trang_thai_nhiem_vu === "HOAN_THANH"
          );
        }
      } catch (e) {
        console.error("Lỗi tải nhiệm vụ hoàn thành:", e);
      } finally {
        this.loading = false;
      }
    },
    viewDetail(item) {
      console.log("View detail:", item);
    },
    exportReport() {
      console.log("Export report");
    },
    getPriorityClass(item) {
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || "THUONG").toUpperCase();
      if (mucDo === "KHA_CAP") return "bg-danger bg-opacity-10 text-danger border-danger border-opacity-25";
      if (mucDo === "TRUNG_BINH") return "bg-warning bg-opacity-10 text-warning border-warning border-opacity-25";
      return "bg-info bg-opacity-10 text-info border-info border-opacity-25";
    },
    getPriorityText(item) {
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || "THUONG").toUpperCase();
      if (mucDo === "KHA_CAP") return "KHẨN CẤP";
      if (mucDo === "TRUNG_BINH") return "TRUNG BÌNH";
      return "THƯỜNG";
    },
    getPriority(item) {
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || "THUONG").toUpperCase();
      if (mucDo === "KHA_CAP") return "urgent";
      if (mucDo === "TRUNG_BINH") return "medium";
      return "low";
    },
  },
};
</script>

<style scoped>
.completed-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #f8f9fa;
}

.completed-header {
  flex-shrink: 0;
}

.completed-body {
  flex: 1;
  overflow-y: auto;
}

.pagination-row {
  flex-shrink: 0;
}

.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
  border-color: rgba(0,0,0,0.04);
}

.transition-all {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

@media (max-width: 576px) {
  .info-grid {
    grid-template-columns: 1fr;
  }
}
</style>
