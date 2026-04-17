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
          <span class="text-muted small">{{ completedMissions.length }} nhiệm vụ</span>
        </div>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" @click="fetchCompletedMissions">
          <i class="fa-solid fa-rotate me-1"></i> Làm mới
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
            <div class="fw-bold text-dark fs-4">{{ completedCount }}</div>
            <div class="text-muted small fw-medium">Hoàn thành</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ failedCount }}</div>
            <div class="text-muted small fw-medium">Thất bại</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-fire"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ urgentCount }}</div>
            <div class="text-muted small fw-medium">Khẩn cấp</div>
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

      <!-- Loading -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-danger" role="status"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredMissions.length === 0" class="text-center py-5">
        <div class="mb-3">
          <i class="fa-solid fa-clipboard text-secondary opacity-25" style="font-size: 64px;"></i>
        </div>
        <h6 class="text-secondary fw-bold">Không có nhiệm vụ nào</h6>
        <p class="text-muted small">Các nhiệm vụ đã hoàn thành sẽ hiển thị tại đây</p>
      </div>

      <!-- Mission Cards -->
      <div v-else class="d-flex flex-column gap-3">
        <div
          v-for="item in filteredMissions"
          :key="item.id_phan_cong"
          class="mission-card card border-0 shadow-sm rounded-4 overflow-hidden hover-card transition-all"
          :class="getCardClass(item)"
        >
          <div class="card-body p-4">
            <!-- Top Row -->
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge rounded-pill px-3 py-2 fw-bold border" :class="getPriorityClass(item)">
                  <i class="fa-solid fa-circle-exclamation me-1" v-if="getPriority(item) === 'urgent'"></i>
                  {{ getPriorityText(item) }}
                </span>
                <span v-if="item.trang_thai_nhiem_vu === 'HOAN_THANH'" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2 fw-bold">
                  <i class="fa-solid fa-check me-1"></i> HOÀN THÀNH
                </span>
                <span v-else-if="item.trang_thai_nhiem_vu === 'THAT_BAI'" class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-2 fw-bold">
                  <i class="fa-solid fa-times me-1"></i> THẤT BẠI
                </span>
              </div>
              <div class="text-end">
                <div class="text-muted small fw-medium"><i class="fa-regular fa-calendar me-1 text-secondary"></i>{{ formatTime(item.updated_at || item.created_at) }}</div>
              </div>
            </div>

            <!-- Title & Type -->
            <h5 class="fw-bold text-dark mb-2">{{ getIncidentTypeName(item) }}</h5>
            <p class="text-muted small mb-3">
              <i class="fa-solid fa-layer-group me-1 text-danger"></i>
              {{ item.yeu_cau && item.yeu_cau.muc_do_khan_cap ? item.yeu_cau.muc_do_khan_cap : '-' }}
            </p>

            <!-- Info Grid -->
            <div class="info-grid mb-3">
              <div class="info-item">
                <div class="d-flex align-items-start">
                  <div class="info-icon me-2 text-danger mt-1"><i class="fa-solid fa-location-dot"></i></div>
                  <div>
                    <div class="text-muted fw-bold" style="font-size: 9px; letter-spacing: 0.5px;">ĐỊA ĐIỂM</div>
                    <div class="fw-semibold text-dark small">{{ getRequestAddress(item) }}</div>
                  </div>
                </div>
              </div>
              <div class="info-item">
                <div class="d-flex align-items-start">
                  <div class="info-icon me-2 text-primary mt-1"><i class="fa-solid fa-user-injured"></i></div>
                  <div>
                    <div class="text-muted fw-bold" style="font-size: 9px; letter-spacing: 0.5px;">NGƯỜI GẶP NẠN</div>
                    <div class="fw-semibold text-dark small">{{ getReporterName(item) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Result Details -->
            <div v-if="item.ket_qua" class="result-section mb-3 p-3 bg-light rounded-3 border border-light">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fa-solid fa-clipboard-check text-primary"></i>
                <span class="fw-bold text-dark small" style="letter-spacing: 0.5px;">KẾT QUẢ</span>
              </div>
              <div v-if="item.ket_qua.bao_cao_hien_truong" class="text-secondary small mb-2">
                {{ item.ket_qua.bao_cao_hien_truong }}
              </div>
              <div v-if="item.trang_thai_nhiem_vu === 'THAT_BAI'" class="alert alert-danger py-2 px-3 mb-0 small" role="alert">
                <i class="fa-solid fa-exclamation-circle me-1"></i>
                <strong>Lý do:</strong> {{ item.ket_qua.bao_cao_hien_truong || 'Không có thông tin' }}
              </div>
              <div v-if="item.ket_qua.hinh_anh_minh_chung" class="mt-2">
                <div class="text-muted fw-bold mb-1" style="font-size: 9px; letter-spacing: 0.5px;">HÌNH ẢNH MINH CHỨNG</div>
                <img :src="getImageUrl(item.ket_qua.hinh_anh_minh_chung)" class="rounded-3" style="max-height: 150px; max-width: 100%; cursor: pointer;" @click="openImageModal(item.ket_qua.hinh_anh_minh_chung)">
              </div>
            </div>

            <!-- Actions -->
            <div class="rating-row d-flex align-items-center justify-content-between bg-light rounded-3 p-3 border border-light">
              <div class="d-flex align-items-center">
                <div class="text-muted fw-bold me-3" style="font-size: 10px; letter-spacing: 0.5px;">TRẠNG THÁI</div>
                <span class="badge rounded-pill px-3 py-1" :class="item.trang_thai_nhiem_vu === 'HOAN_THANH' ? 'bg-success' : 'bg-danger'">{{ item.trang_thai_nhiem_vu }}</span>
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

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" ref="imageModalEl">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold"><i class="fa-solid fa-image me-2"></i>Hình Ảnh Minh Chứng</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center p-0">
            <img v-if="modalImageUrl" :src="modalImageUrl" style="max-width: 100%; border-radius: 8px;">
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" ref="detailModalEl">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold"><i class="fa-solid fa-info-circle me-2"></i>Chi Tiết Nhiệm Vụ</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" v-if="detailItem">
            <div class="mb-3">
              <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">LOẠI SỰ CỐ</div>
              <div class="fw-bold text-dark">{{ getIncidentTypeName(detailItem) }}</div>
            </div>
            <div class="mb-3">
              <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">MỨC ĐỘ</div>
              <div class="fw-bold text-dark">{{ detailItem.yeu_cau && detailItem.yeu_cau.muc_do_khan_cap ? detailItem.yeu_cau.muc_do_khan_cap : '-' }}</div>
            </div>
            <div class="mb-3">
              <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">ĐỊA ĐIỂM</div>
              <div class="fw-bold text-dark">{{ getRequestAddress(detailItem) }}</div>
            </div>
            <div class="mb-3">
              <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">NGƯỜI GẶP NẠN</div>
              <div class="fw-bold text-dark">{{ getReporterName(detailItem) }}</div>
            </div>
            <div class="mb-3" v-if="detailItem.doi_cuu_ho">
              <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">ĐỘI THỰC HIỆN</div>
              <div class="fw-bold text-dark">{{ detailItem.doi_cuu_ho.ten_co }}</div>
            </div>
            <div class="mb-3">
              <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">THỜI GIAN HOÀN THÀNH</div>
              <div class="fw-bold text-dark">{{ formatTime(detailItem.updated_at || detailItem.created_at) }}</div>
            </div>
            <div v-if="detailItem.ket_qua">
              <div class="mb-3">
                <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">BÁO CÁO</div>
                <div class="text-secondary small">{{ detailItem.ket_qua.bao_cao_hien_truong || 'Không có' }}</div>
              </div>
              <div v-if="detailItem.ket_qua.hinh_anh_minh_chung">
                <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">HÌNH ẢNH</div>
                <img :src="getImageUrl(detailItem.ket_qua.hinh_anh_minh_chung)" class="rounded-3" style="max-height: 200px; max-width: 100%;">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary fw-bold rounded-3" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescuerAPI } from "../../../services/api.js";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

export default {
  name: "DaXuLy",
  data() {
    return {
      loading: false,
      activeFilter: 'all',
      completedMissions: [],
      imageModalEl: null,
      detailModalEl: null,
      modalImageUrl: '',
      detailItem: null,
    };
  },
  computed: {
    completedCount() {
      return this.completedMissions.filter(m => m.trang_thai_nhiem_vu === 'HOAN_THANH').length;
    },
    failedCount() {
      return this.completedMissions.filter(m => m.trang_thai_nhiem_vu === 'THAT_BAI').length;
    },
    urgentCount() {
      return this.completedMissions.filter(m => {
        const sev = m.yeu_cau && m.yeu_cau.muc_do_khan_cap ? m.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
        return sev === 'CRITICAL' || sev === 'HIGH';
      }).length;
    },
    avgRating() {
      return 4.8;
    },
    filterTabs() {
      return [
        { key: 'all', label: 'Tất cả', count: this.completedMissions.length },
        { key: 'success', label: 'Thành công', count: this.completedCount },
        { key: 'failed', label: 'Thất bại', count: this.failedCount },
        { key: 'urgent', label: 'Khẩn cấp', count: this.urgentCount },
      ];
    },
    filteredMissions() {
      if (this.activeFilter === 'all') return this.completedMissions;
      if (this.activeFilter === 'success') return this.completedMissions.filter(m => m.trang_thai_nhiem_vu === 'HOAN_THANH');
      if (this.activeFilter === 'failed') return this.completedMissions.filter(m => m.trang_thai_nhiem_vu === 'THAT_BAI');
      if (this.activeFilter === 'urgent') {
        return this.completedMissions.filter(m => {
          const sev = m.yeu_cau && m.yeu_cau.muc_do_khan_cap ? m.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
          return sev === 'CRITICAL' || sev === 'HIGH';
        });
      }
      return this.completedMissions;
    },
  },
  async mounted() {
    await this.fetchCompletedMissions();
    this.$nextTick(() => {
      if (typeof bootstrap !== 'undefined') {
        this.imageModalEl = document.getElementById('imageModal');
        this.detailModalEl = document.getElementById('detailModal');
      }
    });
  },
  methods: {
    async fetchCompletedMissions() {
      this.loading = true;
      try {
        let teamId = null;
        const teamStr = localStorage.getItem("rescuer_team");
        if (teamStr) {
          const team = JSON.parse(teamStr);
          teamId = team.id_doi_cuu_ho || team.id;
        }

        let assignments = [];
        if (teamId) {
          const res = await rescuerAPI.getAssignmentByTeam(teamId, { per_page: 100 });
          if (res.data && res.data.data) {
            assignments = res.data.data.data || res.data.data;
          }
        } else {
          const res = await rescuerAPI.getAssignments({ per_page: 100 });
          if (res.data && res.data.data) {
            assignments = res.data.data.data || res.data.data;
          }
        }

        this.completedMissions = [];
        if (Array.isArray(assignments)) {
          assignments.forEach(item => {
            const status = item.trang_thai_nhiem_vu || '';
            if (status === 'HOAN_THANH' || status === 'THAT_BAI') {
              this.completedMissions.push(item);
            }
          });
        }
      } catch (e) {
        console.error("Lỗi tải nhiệm vụ hoàn thành:", e);
        toaster.error("Không thể tải danh sách nhiệm vụ");
      } finally {
        this.loading = false;
      }
    },
    viewDetail(item) {
      this.detailItem = item;
      if (this.detailModalEl && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(this.detailModalEl);
        modal.show();
      }
    },
    openImageModal(imagePath) {
      this.modalImageUrl = this.getImageUrl(imagePath);
      if (this.imageModalEl && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(this.imageModalEl);
        modal.show();
      }
    },
    getImageUrl(path) {
      if (!path) return '';
      if (path.startsWith('http')) return path;
      return 'http://localhost:8000/' + path;
    },
    formatTime(dateStr) {
      if (!dateStr) return '-';
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return dateStr;
      return d.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
    },
    getCardClass(item) {
      if (item.trang_thai_nhiem_vu === 'THAT_BAI') return 'border-danger border-2';
      const sev = item.yeu_cau && item.yeu_cau.muc_do_khan_cap ? item.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
      if (sev === 'CRITICAL' || sev === 'HIGH') return 'border-danger border-2';
      return '';
    },
    getPriorityClass(item) {
      const mucDo = item.yeu_cau && item.yeu_cau.muc_do_khan_cap ? item.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
      if (mucDo === 'CRITICAL' || mucDo === 'HIGH') return 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
      if (mucDo === 'MEDIUM') return 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25';
      return 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
    },
    getPriorityText(item) {
      const mucDo = item.yeu_cau && item.yeu_cau.muc_do_khan_cap ? item.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
      if (mucDo === 'CRITICAL' || mucDo === 'HIGH') return 'KHẨN CẤP';
      if (mucDo === 'MEDIUM') return 'TRUNG BÌNH';
      return 'THƯỜNG';
    },
    getPriority(item) {
      const mucDo = item.yeu_cau && item.yeu_cau.muc_do_khan_cap ? item.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
      if (mucDo === 'CRITICAL' || mucDo === 'HIGH') return 'urgent';
      if (mucDo === 'MEDIUM') return 'medium';
      return 'low';
    },
    getIncidentTypeName(item) {
      if (item && item.yeu_cau && item.yeu_cau.loai_su_co) {
        return item.yeu_cau.loai_su_co.ten_danh_muc || item.yeu_cau.loai_su_co.ten_loai_su_co || 'Yêu cầu cứu hộ';
      }
      return 'Yêu cầu cứu hộ';
    },
    getRequestAddress(item) {
      if (item && item.yeu_cau) {
        if (item.yeu_cau.vi_tri_dia_chi) return item.yeu_cau.vi_tri_dia_chi;
        if (item.yeu_cau.dia_chi) return item.yeu_cau.dia_chi;
        if (item.yeu_cau.vi_tri) return item.yeu_cau.vi_tri;
      }
      return 'Chưa có địa chỉ';
    },
    getReporterName(item) {
      if (item && item.yeu_cau) {
        if (item.yeu_cau.ho_ten_nguoi_dung) return item.yeu_cau.ho_ten_nguoi_dung;
        if (item.yeu_cau.hoTenNguoiDung) return item.yeu_cau.hoTenNguoiDung;
        if (item.yeu_cau.nguoi_dung) {
          return item.yeu_cau.nguoi_dung.ho_ten || item.yeu_cau.nguoi_dung.hoTen || 'Không rõ';
        }
      }
      return 'Không rõ';
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
