<template>
  <div class="report-wrapper">
    <!-- Header -->
    <div class="report-header px-4 py-3 d-flex align-items-center justify-content-between border-bottom bg-white shadow-sm">
      <div class="d-flex align-items-center">
        <div class="header-icon me-3 d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 48px; height: 48px;">
          <i class="fa-solid fa-chart-line fs-4"></i>
        </div>
        <div>
          <h5 class="fw-bold mb-0 text-dark">Báo Cáo & Thống Kê</h5>
          <span class="text-muted small">Hiệu suất đội cứu hộ</span>
        </div>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" @click="exportReport">
          <i class="fa-solid fa-download me-2"></i> Xuất báo cáo
        </button>
      </div>
    </div>

    <!-- Body -->
    <div class="report-body px-4 py-3 flex-grow-1 overflow-auto">
      <!-- Loading -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-danger" role="status"></div>
        <p class="mt-2 text-muted">Đang tải dữ liệu...</p>
      </div>

      <!-- Stats Overview -->
      <div v-else class="stats-grid mb-4">
        <div class="row g-3">
          <div class="col-6 col-md-3">
            <div class="stat-card bg-white rounded-4 p-4 text-center border border-light shadow-sm hover-lift">
              <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width: 56px; height: 56px;">
                <i class="fa-solid fa-clipboard-list fs-4"></i>
              </div>
              <div class="fw-bold text-dark fs-2 mb-1">{{ totalMissions }}</div>
              <div class="text-muted small fw-medium">Tổng nhiệm vụ</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="stat-card bg-white rounded-4 p-4 text-center border border-light shadow-sm hover-lift">
              <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning" style="width: 56px; height: 56px;">
                <i class="fa-solid fa-clock fs-4"></i>
              </div>
              <div class="fw-bold text-dark fs-2 mb-1">{{ avgResponseTime }}</div>
              <div class="text-muted small fw-medium">TG phản ứng TB</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="stat-card bg-white rounded-4 p-4 text-center border border-light shadow-sm hover-lift">
              <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 56px; height: 56px;">
                <i class="fa-solid fa-check-circle fs-4"></i>
              </div>
              <div class="fw-bold text-dark fs-2 mb-1">{{ completionRate }}%</div>
              <div class="text-muted small fw-medium">Tỷ lệ hoàn thành</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="stat-card bg-white rounded-4 p-4 text-center border border-light shadow-sm hover-lift">
              <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger" style="width: 56px; height: 56px;">
                <i class="fa-solid fa-star fs-4"></i>
              </div>
              <div class="fw-bold text-dark fs-2 mb-1">{{ avgRating }}/5</div>
              <div class="text-muted small fw-medium">Đánh giá TB</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="row g-3 mb-4">
        <!-- Missions by Priority -->
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-transparent border-0 pb-0">
              <h6 class="fw-bold mb-0">Nhiệm vụ theo mức độ</h6>
            </div>
            <div class="card-body">
              <div class="chart-bars">
                <div v-for="item in priorityStats" :key="item.label" class="chart-bar-item mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">{{ item.label }}</span>
                    <span class="small fw-bold">{{ item.count }}</span>
                  </div>
                  <div class="progress" style="height: 8px; border-radius: 4px;">
                    <div class="progress-bar" :class="item.class" role="progressbar"
                      :style="{ width: (item.count / Math.max(...priorityStats.map(p => p.count), 1)) * 100 + '%' }">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Missions by Status -->
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-transparent border-0 pb-0">
              <h6 class="fw-bold mb-0">Nhiệm vụ theo trạng thái</h6>
            </div>
            <div class="card-body">
              <div class="chart-bars">
                <div v-for="item in statusStats" :key="item.label" class="chart-bar-item mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">{{ item.label }}</span>
                    <span class="small fw-bold">{{ item.count }}</span>
                  </div>
                  <div class="progress" style="height: 8px; border-radius: 4px;">
                    <div class="progress-bar" :class="item.class" role="progressbar"
                      :style="{ width: (item.count / Math.max(...statusStats.map(s => s.count), 1)) * 100 + '%' }">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Missions Table -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-transparent border-bottom pb-3">
          <h6 class="fw-bold mb-0">Lịch sử nhiệm vụ gần đây</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4 py-3 fw-bold text-secondary">Ngày</th>
                  <th class="py-3 fw-bold text-secondary">Loại sự cố</th>
                  <th class="py-3 fw-bold text-secondary">Địa điểm</th>
                  <th class="py-3 fw-bold text-secondary">Mức độ</th>
                  <th class="py-3 fw-bold text-secondary">Trạng thái</th>
                  <th class="text-end pe-4 py-3 fw-bold text-secondary">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="mission in recentMissions" :key="mission.id_phan_cong">
                  <td class="ps-4">
                    <div class="fw-medium">{{ formatDate(mission.created_at) }}</div>
                    <div class="text-muted small">{{ formatTime(mission.created_at) }}</div>
                  </td>
                  <td>
                    <span class="fw-medium">{{ mission.yeu_cau?.loai_su_co?.ten_loai_su_co || 'Yêu cầu cứu hộ' }}</span>
                  </td>
                  <td class="text-secondary">
                    {{ mission.yeu_cau?.dia_chi ? mission.yeu_cau.dia_chi.substring(0, 30) + '...' : '-' }}
                  </td>
                  <td>
                    <span class="badge rounded-pill px-3 py-2 fw-bold" :class="getPriorityClass(mission)">
                      {{ getPriorityText(mission) }}
                    </span>
                  </td>
                  <td>
                    <span class="badge rounded-pill px-3 py-2 fw-bold" :class="getStatusClass(mission.trang_thai_nhiem_vu)">
                      {{ getStatusText(mission.trang_thai_nhiem_vu) }}
                    </span>
                  </td>
                  <td class="text-end pe-4">
                    <button class="btn btn-sm btn-outline-secondary rounded-3" @click="viewDetail(mission)">
                      <i class="fa-solid fa-eye"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="recentMissions.length === 0">
                  <td colspan="6" class="text-center py-4 text-muted">
                    Chưa có dữ liệu nhiệm vụ
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescuerAPI } from "../../../services/api.js";

export default {
  name: "Report",
  data() {
    return {
      loading: false,
      allMissions: [],
      stats: {
        total: 0,
        urgent: 0,
        completed: 0,
        avgTime: '0 ph',
        avgRating: 0,
      }
    };
  },
  computed: {
    totalMissions() {
      return this.allMissions.length;
    },
    priorityStats() {
      const urgent = this.allMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'KHA_CAP').length;
      const medium = this.allMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'TRUNG_BINH').length;
      const normal = this.allMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'THUONG').length;
      return [
        { label: 'Khẩn cấp', count: urgent, class: 'bg-danger' },
        { label: 'Trung bình', count: medium, class: 'bg-warning' },
        { label: 'Thường', count: normal, class: 'bg-info' },
      ];
    },
    statusStats() {
      const pending = this.allMissions.filter(m => m.trang_thai_nhiem_vu === 'CHUA_TIEP_NHAN').length;
      const processing = this.allMissions.filter(m => m.trang_thai_nhiem_vu === 'DANG_XU_LY').length;
      const completed = this.allMissions.filter(m => m.trang_thai_nhiem_vu === 'HOAN_THANH').length;
      const declined = this.allMissions.filter(m => m.trang_thai_nhiem_vu === 'TU_CHOI').length;
      return [
        { label: 'Chờ xử lý', count: pending, class: 'bg-secondary' },
        { label: 'Đang xử lý', count: processing, class: 'bg-primary' },
        { label: 'Hoàn thành', count: completed, class: 'bg-success' },
        { label: 'Từ chối', count: declined, class: 'bg-dark' },
      ];
    },
    recentMissions() {
      return this.allMissions
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 10);
    },
    avgResponseTime() {
      return '15 ph'; // Placeholder - có thể tính từ data
    },
    completionRate() {
      if (this.totalMissions === 0) return 0;
      const completed = this.allMissions.filter(m => m.trang_thai_nhiem_vu === 'HOAN_THANH').length;
      return Math.round((completed / this.totalMissions) * 100);
    },
    avgRating() {
      return '4.8'; // Placeholder - có thể tính từ data đánh giá
    },
  },
  async mounted() {
    await this.fetchReportData();
  },
  methods: {
    async fetchReportData() {
      this.loading = true;
      try {
        const res = await rescuerAPI.getAssignments({ per_page: 100 });
        if (res.data?.data?.data) {
          this.allMissions = res.data.data.data;
        } else if (res.data?.data) {
          this.allMissions = res.data.data;
        } else if (Array.isArray(res.data)) {
          this.allMissions = res.data;
        }
      } catch (e) {
        console.error("Lỗi tải báo cáo:", e);
      } finally {
        this.loading = false;
      }
    },
    viewDetail(mission) {
      console.log("View mission detail:", mission);
    },
    exportReport() {
      // Xuất báo cáo dạng CSV/Excel
      const csvContent = this.generateCSV();
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `bao-cao-cuu-ho-${new Date().toISOString().split('T')[0]}.csv`;
      link.click();
    },
    generateCSV() {
      const headers = ['Ngày', 'Loại sự cố', 'Địa điểm', 'Mức độ', 'Trạng thái'];
      const rows = this.allMissions.map(m => [
        m.created_at,
        m.yeu_cau?.loai_su_co?.ten_loai_su_co || '',
        m.yeu_cau?.dia_chi || '',
        m.yeu_cau?.muc_do_khan_cap || '',
        m.trang_thai_nhiem_vu || '',
      ]);
      return [headers, ...rows].map(row => row.join(',')).join('\n');
    },
    getPriorityClass(item) {
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || 'THUONG').toUpperCase();
      if (mucDo === 'KHA_CAP') return 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
      if (mucDo === 'TRUNG_BINH') return 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25';
      return 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
    },
    getPriorityText(item) {
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || 'THUONG').toUpperCase();
      if (mucDo === 'KHA_CAP') return 'KHẨN CẤP';
      if (mucDo === 'TRUNG_BINH') return 'TRUNG BÌNH';
      return 'THƯỜNG';
    },
    getStatusClass(status) {
      if (status === 'HOAN_THANH') return 'bg-success bg-opacity-10 text-success';
      if (status === 'DANG_XU_LY') return 'bg-primary bg-opacity-10 text-primary';
      if (status === 'CHUA_TIEP_NHAN') return 'bg-secondary bg-opacity-10 text-secondary';
      return 'bg-dark bg-opacity-10 text-dark';
    },
    getStatusText(status) {
      if (status === 'HOAN_THANH') return 'Hoàn thành';
      if (status === 'DANG_XU_LY') return 'Đang xử lý';
      if (status === 'CHUA_TIEP_NHAN') return 'Chờ xử lý';
      if (status === 'TU_CHOI') return 'Từ chối';
      return status;
    },
    formatDate(dateString) {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    },
    formatTime(dateString) {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    },
  },
};
</script>

<style scoped>
.report-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #f8f9fa;
}

.report-header {
  flex-shrink: 0;
  z-index: 10;
}

.report-body {
  flex: 1;
  overflow-y: auto;
}

.stats-grid {
  margin-bottom: 1.5rem;
}

.stat-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
}

.chart-bars {
  padding: 0.5rem 0;
}

.chart-bar-item {
  margin-bottom: 1rem;
}

.progress-bar {
  transition: width 0.6s ease;
}

.table th {
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.table td {
  vertical-align: middle;
  font-size: 0.9rem;
}

.badge {
  font-size: 0.75rem;
  font-weight: 600;
}

@media (max-width: 768px) {
  .stats-grid .col-6 {
    margin-bottom: 1rem;
  }
}
</style>
