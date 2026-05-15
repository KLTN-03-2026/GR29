<template>
  <div class="report-wrapper">
    <!-- Header -->
    <div class="report-header px-4 py-3 d-flex align-items-center justify-content-between border-bottom bg-white shadow-sm">
      <div class="d-flex align-items-center">
        <div class="header-icon me-3 d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-white" style="width: 48px; height: 48px;">
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
              <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-white" style="width: 56px; height: 56px;">
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
              <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-white" style="width: 56px; height: 56px;">
                <i class="fa-solid fa-check-circle fs-4"></i>
              </div>
              <div class="fw-bold text-dark fs-2 mb-1">{{ completionRate }}%</div>
              <div class="text-muted small fw-medium">Tỷ lệ hoàn thành</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="stat-card bg-white rounded-4 p-4 text-center border border-light shadow-sm hover-lift">
              <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-white" style="width: 56px; height: 56px;">
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
                  <th class="ps-4 py-3 fw-bold text-secondary">Thời gian</th>
                  <th class="py-3 fw-bold text-secondary">ID sự cố</th>
                  <th class="py-3 fw-bold text-secondary">Địa điểm</th>
                  <th class="py-3 fw-bold text-secondary">Mức độ</th>
                  <th class="py-3 fw-bold text-secondary">Trạng thái</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="mission in recentMissions" :key="mission.id_phan_cong">
                  <td class="ps-4">
                    <div class="fw-medium">{{ formatDate(mission.created_at) }}</div>
                    <div class="text-muted small">{{ formatTime(mission.created_at) }}</div>
                  </td>
                  <td>
                    <span class="fw-medium">{{ mission.yeu_cau?.id_yeu_cau || '-' }}</span>
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
                    <span class="badge rounded-pill px-3 py-2 fw-bold text-white" :class="getStatusClass(mission.trang_thai_nhiem_vu)">
                      {{ getStatusText(mission.trang_thai_nhiem_vu) }}
                    </span>
                  </td>
                  <!-- <td class="text-end pe-4">
                    <button class="btn btn-sm btn-outline-secondary rounded-3" @click="viewDetail(mission)">
                      <i class="fa-solid fa-eye"></i>
                    </button>
                  </td> -->
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
      teamId: null,
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
      const critical = this.allMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'CRITICAL').length;
      const high = this.allMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'HIGH').length;
      const medium = this.allMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'MEDIUM').length;
      const low = this.allMissions.filter(m => (m.yeu_cau?.muc_do_khan_cap || '').toUpperCase() === 'LOW').length;
      return [
        { label: 'Khẩn cấp', count: critical, class: 'bg-danger' },
        { label: 'Cao', count: high, class: 'bg-warning' },
        { label: 'Trung bình', count: medium, class: 'bg-info' },
        { label: 'Thấp', count: low, class: 'bg-secondary' },
      ];
    },
    statusStats() {
      const normalize = (s) => (s || '').toUpperCase().replace(/\s+/g, '_');
      const pendingStatuses = new Set([
        'DA_PHAN_CONG', 'MOI', 'CHO_NHAN', 'PENDING', 'ASSIGNED',
        'WAITING', 'CHO_XU_LY', 'DA_DUOC_PHAN_CONG', 'CHUA_TIEP_NHAN',
      ]);

      const processingYeuCauIds = new Set();
      this.allMissions.forEach(m => {
        const st = normalize(m.trang_thai_nhiem_vu);
        if (st === 'DANG_XU_LY' || st === 'DA_DEN_HIEN_TRUONG') {
          if (m.yeu_cau?.id_yeu_cau) processingYeuCauIds.add(m.yeu_cau.id_yeu_cau);
        }
      });

      const pendingYeuCauIds = new Set();
      this.allMissions.forEach(m => {
        const st = normalize(m.trang_thai_nhiem_vu);
        if (!pendingStatuses.has(st)) return;
        const ycId = m.yeu_cau?.id_yeu_cau;
        if (!ycId || processingYeuCauIds.has(ycId)) return;
        pendingYeuCauIds.add(ycId);
      });

      const processing = this.allMissions.filter(m => {
        const st = normalize(m.trang_thai_nhiem_vu);
        return st === 'DANG_XU_LY' || st === 'DA_DEN_HIEN_TRUONG';
      }).length;
      const completed = this.allMissions.filter(m => normalize(m.trang_thai_nhiem_vu) === 'HOAN_THANH').length;
      const declined = this.allMissions.filter(m => normalize(m.trang_thai_nhiem_vu) === 'TU_CHOI').length;
      return [
        { label: 'Chờ xử lý', count: pendingYeuCauIds.size, class: 'bg-secondary' },
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
    this.loadTeamData();
    await this.fetchReportData();
  },
  methods: {
    loadTeamData() {
      const teamStr = localStorage.getItem("rescuer_team");
      if (teamStr) {
        try {
          const team = JSON.parse(teamStr);
          this.teamId = team.id_doi_cuu_ho || team.id;
        } catch (e) {
          console.error('Error parsing team data', e);
        }
      }
    },
    async fetchReportData() {
      this.loading = true;
      try {
        const res = this.teamId
          ? await rescuerAPI.getAssignmentByTeam(this.teamId, { per_page: 100 })
          : await rescuerAPI.getAssignments({ per_page: 100 });
        if (res.data?.data?.data) {
          this.allMissions = res.data.data.data;
        } else if (res.data?.data) {
          this.allMissions = res.data.data;
        } else if (Array.isArray(res.data)) {
          this.allMissions = res.data;
        }
        if (this.teamId) {
          this.allMissions = this.allMissions.filter(
            m => Number(m.id_doi_cuu_ho) === Number(this.teamId)
          );
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
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
      if (mucDo === 'CRITICAL') return 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
      if (mucDo === 'HIGH') return 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25';
      if (mucDo === 'MEDIUM') return 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
      return 'bg-secondary bg-opacity-10 text-secondary border-secondary border-opacity-25';
    },
    getPriorityText(item) {
      const mucDo = (item.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
      if (mucDo === 'CRITICAL') return 'Khẩn cấp';
      if (mucDo === 'HIGH') return 'Cao';
      if (mucDo === 'MEDIUM') return 'Trung bình';
      return 'Thấp';
    },
    getStatusClass(status) {
      const st = (status || '').toUpperCase().replace(/\s+/g, '_');
      const pendingStatuses = new Set([
        'DA_PHAN_CONG', 'MOI', 'CHO_NHAN', 'PENDING', 'ASSIGNED',
        'WAITING', 'CHO_XU_LY', 'DA_DUOC_PHAN_CONG', 'CHUA_TIEP_NHAN',
      ]);
      if (st === 'HOAN_THANH') return 'bg-success bg-opacity-10 text-success';
      if (st === 'DANG_XU_LY' || st === 'DA_DEN_HIEN_TRUONG') return 'bg-primary bg-opacity-10 text-primary';
      if (pendingStatuses.has(st)) return 'bg-secondary bg-opacity-10 text-dark';
      return 'bg-dark bg-opacity-10 text-dark';
    },
    getStatusText(status) {
      const st = (status || '').toUpperCase().replace(/\s+/g, '_');
      const pendingStatuses = new Set([
        'DA_PHAN_CONG', 'MOI', 'CHO_NHAN', 'PENDING', 'ASSIGNED',
        'WAITING', 'CHO_XU_LY', 'DA_DUOC_PHAN_CONG', 'CHUA_TIEP_NHAN',
      ]);
      if (st === 'HOAN_THANH') return 'Hoàn thành';
      if (st === 'DANG_XU_LY' || st === 'DA_DEN_HIEN_TRUONG') return 'Đang xử lý';
      if (pendingStatuses.has(st)) return 'Chờ xử lý';
      if (st === 'TU_CHOI') return 'Từ chối';
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
