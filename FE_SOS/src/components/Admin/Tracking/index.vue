<template>
  <div class="dashboard-container">
    <div class="container-fluid  tracking-wrapper d-flex flex-column">
      <!-- Header -->
      <div class="row align-items-end mb-1 flex-shrink-0">
        <div class="col-xl-6 mb-3 mb-xl-0">
          <h2 class="page-title text-dark fw-bolder mb-1">Giám Sát Vị Trí Hiện Trường</h2>
          <p class="text-muted mb-0 fs-6">Hệ thống radar và bản đồ theo dõi lực lượng trực tiếp</p>
        </div>
        <div class="col-xl-6">
          <div class="d-flex justify-content-xl-end gap-3 stats-wrapper flex-wrap">
            <div class="stats-card-tracking">
              <div class="icon-circle bg-danger-subtle text-danger"><i class="fa-solid fa-truck-medical"></i></div>
              <div>
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Đang Nhiệm Vụ</span>
                <h4 class="mb-0 fw-bold">
                  <span v-if="loadingTeams" class="spinner-border spinner-border-sm text-secondary ms-1"></span>
                  <span v-else>{{ teamsInAction.length }}</span>
                </h4>
              </div>
            </div>
            <div class="stats-card-tracking">
              <div class="icon-circle bg-success-subtle text-success"><i class="fa-solid fa-shield-halved"></i></div>
              <div>
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Trực Sẵn Sàng</span>
                <h4 class="mb-0 fw-bold">
                  <span v-if="loadingTeams" class="spinner-border spinner-border-sm text-secondary ms-1"></span>
                  <span v-else>{{ teamsReady.length }}</span>
                </h4>
              </div>
            </div>
            <div class="stats-card-tracking">
              <div class="icon-circle bg-dark-subtle text-dark"><i class="fa-solid fa-satellite-dish"></i></div>
              <div>
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Lưới Tín Hiệu</span>
                <h4 class="mb-0 fw-bold">Ổn định</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Layout -->
      <div class="row g-4 flex-grow-1 min-h-0">
        <!-- Sidebar -->
        <div class="col-xl-4 col-lg-5 h-100 d-flex flex-column">
          <div class="panel-left-tracking h-100 p-0 border">
            <!-- Filter Section -->
            <div class="p-4 border-bottom bg-light bg-opacity-50" style="border-radius: 1.25rem 1.25rem 0 0;">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="small fw-bold text-muted text-uppercase tracking-wider">Bộ lọc</span>
                <button class="btn btn-light btn-icon rounded-circle" @click="initData" :disabled="loadingTeams">
                  <i class="fa-solid fa-rotate-right text-secondary" :class="{ 'spin': loadingTeams }"></i>
                </button>
              </div>
              <div class="search-box-wrap mb-3">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" class="form-control" placeholder="Tìm kiếm đơn vị cứu hộ, khu vực..." v-model="searchQuery">
              </div>
              <div class="d-flex gap-2 filter-tabs">
                <button class="btn btn-sm flex-grow-1 fw-medium"
                        :class="filter === 'all' ? 'btn-dark' : 'btn-outline-secondary bg-white'"
                        @click="filter = 'all'">Tất cả</button>
                <button class="btn btn-sm flex-grow-1 fw-medium"
                        :class="filter === 'active' ? 'btn-danger' : 'btn-outline-secondary bg-white'"
                        @click="filter = 'active'">Đang làm</button>
                <button class="btn btn-sm flex-grow-1 fw-medium"
                        :class="filter === 'ready' ? 'btn-success' : 'btn-outline-secondary bg-white'"
                        @click="filter = 'ready'">Sẵn sàng</button>
              </div>
            </div>

            <!-- Loading -->
            <div v-if="loadingTeams" class="d-flex justify-content-center align-items-center py-5 flex-column gap-3">
              <div class="spinner"></div>
              <span class="text-muted fw-medium">Đang đồng bộ dữ liệu...</span>
            </div>

            <!-- Error -->
            <div v-if="error && !loadingTeams" class="alert alert-danger custom-alert-warning m-3">
              <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ error }}
            </div>

            <!-- List Section -->
            <div class="flex-grow-1 overflow-auto p-3 custom-scrollbar">
              <div class="text-muted small fw-bolder text-uppercase tracking-wider mb-3 px-2">
                Danh sách lực lượng ({{ filteredTeams.length }})
              </div>

              <div class="d-flex flex-column gap-3">
                <div v-for="team in filteredTeams" :key="team.id" class="team-item p-3 border"
                     :class="{'active': selectedTeam && selectedTeam.id === team.id}"
                     @click="selectTeam(team)">

                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-3">
                      <div class="team-initial border text-dark fw-bolder bg-white shadow-sm d-flex align-items-center justify-content-center rounded-circle"
                           style="width: 42px; height: 42px; font-size: 1.1rem;">
                        {{ (team.ten_doi || team.ten_co || team.name || 'Đ').charAt(0) }}
                      </div>
                      <div>
                        <h6 class="mb-0 fw-bold">{{ team.ten_doi || team.ten_co || team.name }}</h6>
                        <div class="small fw-medium mt-1 d-flex align-items-center" :class="getStatusTextColor(team.status)">
                          <span class="status-dot-tracking me-2" :class="getStatusDotClass(team.status)"></span>
                          {{ getStatusLabel(team.status) }}
                        </div>
                      </div>
                    </div>
                    <div class="text-end text-nowrap mt-1">
                      <span class="badge bg-light text-secondary border fw-bold px-2 py-1" v-if="team.khoang_cach_km"><i class="fa-solid fa-location-arrow me-1"></i>{{ team.khoang_cach_km }}km</span>
                    </div>
                  </div>

                  <div class="mt-3 p-2 bg-light-subtle rounded-3 small">
                    <div class="d-flex align-items-center gap-2 mb-2 text-muted">
                      <i class="fa-solid fa-users text-primary"></i> <span class="fw-medium text-dark">{{ team.thanh_viens?.length || team.members || 0 }} thành viên</span> <span class="text-muted opacity-50">•</span> {{ team.type }}
                    </div>
                    <div class="d-flex align-items-center gap-2 text-muted" v-if="team.assigned">
                      <i class="fa-solid fa-triangle-exclamation text-danger mt-1 align-self-start"></i>
                      <span class="fw-bold text-danger text-wrap">{{ team.assigned }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-muted" v-else>
                      <i class="fa-solid fa-location-crosshairs text-success"></i> <span class="fw-medium text-muted">Đang túc trực tại cơ sở</span>
                    </div>
                  </div>
                </div>

                <div v-if="filteredTeams.length === 0" class="text-center p-5 text-muted">
                  <i class="fa-solid fa-filter-circle-xmark fs-1 mb-3 opacity-50"></i>
                  <h6 class="fw-bold">Không tìm thấy đơn vị</h6>
                  <p class="small mb-0">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Map Area -->
        <div class="col-xl-8 col-lg-7 h-100">
          <div class="map-container-box w-100 h-100 bg-light">
            <div class="floating-overlay" v-if="selectedTeam">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge px-2 py-1 fw-bold font-monospace" :class="getStatusBadgeClass(selectedTeam.status)">
                  <span class="status-dot-tracking me-1" :class="getStatusDotClass(selectedTeam.status)"></span>
                  {{ getStatusLabel(selectedTeam.status) }}
                </span>
                <button class="btn-close" @click="selectedTeam = null" style="font-size: 0.75rem;"></button>
              </div>
              <h5 class="fw-bolder mb-1">{{ selectedTeam.ten_doi || selectedTeam.ten_co || selectedTeam.name }}</h5>
              <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1 text-primary"></i> Tọa độ GPS: {{ selectedTeam.lat.toFixed(4) }}, {{ selectedTeam.lng.toFixed(4) }}</p>

              <div class="border-top pt-3 mt-2">
                <div v-if="selectedTeam.assigned" class="bg-danger-subtle p-3 rounded-4 border border-danger border-opacity-10">
                  <div class="small fw-semibold text-danger text-uppercase tracking-wider"><i class="fa-solid fa-route me-1"></i> Mục tiêu hiện tại</div>
                  <div class="fw-bold text-dark mt-1 fs-6">{{ selectedTeam.assigned }}</div>
                </div>
                <div v-else class="bg-success-subtle p-3 rounded-4 border border-success border-opacity-10">
                   <div class="small fw-bold text-success text-uppercase tracking-wider"><i class="fa-solid fa-check-circle me-1"></i> Sẵn sàng nhận lệnh mới</div>
                   <div class="text-muted mt-1 small">Đơn vị đang chờ điều phối ở khu vực trung tâm.</div>
                </div>
              </div>

              <div class="mt-3 p-3 bg-light rounded-3 small" v-if="selectedTeam.thanh_viens && selectedTeam.thanh_viens.length > 0">
                <div class="small fw-semibold text-muted text-uppercase tracking-wider mb-2"><i class="fa-solid fa-users me-1"></i> Thành viên ({{ selectedTeam.thanh_viens.length }})</div>
                <div class="d-flex flex-wrap gap-1">
                  <span v-for="(member, idx) in selectedTeam.thanh_viens.slice(0, 6)" :key="idx"
                    class="badge bg-white text-dark border fw-medium">
                    {{ member.ho_ten || member.name || member.ho_ten || 'TV' + (idx + 1) }}
                  </span>
                  <span v-if="selectedTeam.thanh_viens.length > 6" class="badge bg-light text-secondary">+{{ selectedTeam.thanh_viens.length - 6 }}</span>
                </div>
              </div>

              <button class="btn btn-primary w-100 mt-3 fw-bolder shadow-sm py-2"><i class="fa-solid fa-walkie-talkie me-2"></i>Liên lạc bộ đàm</button>
            </div>
            
            <MapboxMap ref="mapRef" :zoom="13" :center="defaultCenter" :showMarker="false" @load="onMapLoad" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";
import maplibregl from "@openmapvn/openmapvn-gl";
import { rescueTeamAPI, rescueRequestAPI } from "../../../services/api";

export default {
  name: "AdminTracking",
  components: { MapboxMap },
  created() {
    this._markers = [];
    this.initData();
  },
  data() {
    return {
      mapLoaded: false,
      searchQuery: "",
      filter: "all",
      selectedTeam: null,
      defaultCenter: [108.2022, 16.0544],
      loadingTeams: false,
      loadingRequests: false,
      error: null,
      requests: [],
      teams: [],
    };
  },
  computed: {
    teamsInAction() {
      return this.teams.filter(t => {
        const s = String(t.trangThai || t.trang_thai || t.status || "").toUpperCase().replace(/\s+|_/g, "");
        return s === "DANGXULY" || s === "DANG_XU_LY" || s === "DAP HANCONG" || s === "DA_PHAN_CONG" || s === "DADENHIENTRUONG" || s === "DA_DEN_HIEN_TRUONG";
      });
    },
    teamsReady() {
      return this.teams.filter(t => {
        const s = String(t.trangThai || t.trang_thai || t.status || "").toUpperCase().replace(/\s+|_/g, "");
        return s === "SANSANG" || s === "SAN_SANG" || s === "SẴNSÀNG" || s === "READY";
      });
    },
    filteredTeams() {
      let result = [...this.teams];

      if (this.filter === "active") {
        result = result.filter(t => {
          const s = String(t.trangThai || t.trang_thai || t.status || "").toUpperCase().replace(/\s+|_/g, "");
          return s === "DANGXULY" || s === "DANG_XU_LY" || s === "DAP HANCONG" || s === "DA_PHAN_CONG" || s === "DADENHIENTRUONG" || s === "DA_DEN_HIEN_TRUONG";
        });
      } else if (this.filter === "ready") {
        result = result.filter(t => {
          const s = String(t.trangThai || t.trang_thai || t.status || "").toUpperCase().replace(/\s+|_/g, "");
          return s === "SANSANG" || s === "SAN_SANG" || s === "SẴNSÀNG" || s === "READY";
        });
      }

      if (this.searchQuery) {
        const query = this.normalizeSearch(this.searchQuery);
        result = result.filter(t => {
          const name = this.normalizeSearch(t.tenCo || t.ten_co || t.name || "");
          const assigned = this.normalizeSearch(t.assignedTitle || t.assigned_title || t.assigned || "");
          const location = this.normalizeSearch(t.khuVuc || t.khu_vuc || t.location || "");
          return name.includes(query) || assigned.includes(query) || location.includes(query);
        });
      }

      const sortedByDist = result.sort((a, b) => {
        const distA = a.khoangCachKm ?? a.khoang_cach_km ?? a.distance ?? 999;
        const distB = b.khoangCachKm ?? b.khoang_cach_km ?? b.distance ?? 999;
        return distA - distB;
      });

      return sortedByDist;
    }
  },
  watch: {
    filteredTeams: {
      deep: true,
      handler() {
        if (this.mapLoaded) {
          this.syncMarkers();
        }
      }
    }
  },
  methods: {
    removeDiacritics(str) {
      return str.normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, m => m === 'đ' ? 'd' : 'D');
    },
    normalizeSearch(text) {
      return this.removeDiacritics(String(text || '').toLowerCase());
    },
    async initData() {
      this.error = null;
      await Promise.all([this.loadTeams(), this.loadRequests()]);
    },
    async loadTeams() {
      this.loadingTeams = true;
      try {
        const res = await rescueTeamAPI.getList({ get_all: true });
        const rawData = res?.data?.data || res?.data || [];
        this.teams = rawData.map(item => {
          // Luôn dùng vị trí trung tâm (HQ) đã seeder cho đội,
          // không dùng vị trí thực tế của rescuer
          const lat = item.vi_tri_lat || item.lat || null;
          const lng = item.vi_tri_lng || item.lng || null;
          // Xác định trạng thái thực tế từ capacity (backend gửi về)
          const activeCount = item.active_count ?? 0;
          const pendingCount = item.pending_count ?? 0;
          const capacity = item.capacity ?? 0;
          const soThanhVien = item.thanhViens?.length ?? item.thanh_viens?.length ?? 0;
          // Nếu có active_count > 0 thì trạng thái thực tế là ACTIVE
          let actualStatus = "READY";
          if (activeCount > 0) {
            actualStatus = "ACTIVE";
          } else if (capacity > 0 && activeCount >= capacity) {
            actualStatus = "OFFLINE";
          }
          // Tìm assignment đang active của team này
          const teamAssignments = this.requests.filter(r =>
            r.phanCongs?.some(pc => String(pc.id_doi_cuu_ho || pc.doiCuuHo?.id_doi_cuu_ho) === String(item.id_doi_cuu_ho || item.id))
          );
          const activeAssignment = teamAssignments.find(r => {
            const st = String(r.trangThai || "").toUpperCase().replace(/\s+|_/g, "");
            return st === "DANGXULY" || st === "DAP HANCONG" || st === "DADENHIENTRUONG";
          });
          return {
            id: item.id_doi_cuu_ho || item.id,
            name: item.ten_co || item.ten_doi || item.name || "Đội cứu hộ",
            ten_co: item.ten_co || item.ten_doi || item.name || "Đội cứu hộ",
            status: actualStatus,
            trangThai: actualStatus,
            lat: lat ? parseFloat(lat) : 16.0544,
            lng: lng ? parseFloat(lng) : 108.2022,
            vi_tri_lat: lat,
            vi_tri_lng: lng,
            type: (item.loaiSuCos || item.loai_su_cos || []).map(s => s.ten_danh_muc || s.ten_loai_su_co || s.ten || "").filter(Boolean).join(", ") || "Hỗ trợ chung",
            loaiSuCos: item.loaiSuCos || item.loai_su_cos || [],
            assigned: activeAssignment ? activeAssignment.tieuDe || activeAssignment.title || "Đang xử lý sự cố" : null,
            assignedTitle: activeAssignment ? activeAssignment.tieuDe || activeAssignment.title || "Đang xử lý sự cố" : null,
            assignedId: activeAssignment ? (activeAssignment.id_yeu_cau || activeAssignment.id) : null,
            members: soThanhVien,
            thanhViens: item.thanhViens || item.thanh_viens || [],
            thanh_viens: item.thanhViens || item.thanh_viens || [],
            distance: item.khoang_cach_km ?? item.khoangCachKm ?? 0,
            khoang_cach_km: item.khoang_cach_km ?? item.khoangCachKm ?? 0,
            khuVuc: item.khu_vuc_quan_ly || item.khuVucQuanLy || "",
            khu_vuc: item.khu_vuc_quan_ly || item.khuVucQuanLy || "",
            active_count: activeCount,
            pending_count: pendingCount,
            capacity: capacity,
            trang_thai_theo_nang_luc: item.trang_thai_theo_nang_luc,
            phone: item.so_dien_thoai_hotline || item.phone || "",
            raw: item,
          };
        });
      } catch (error) {
        console.error("Lỗi tải đội cứu hộ:", error);
        this.error = "Không thể tải danh sách đội cứu hộ.";
      } finally {
        this.loadingTeams = false;
      }
    },
    async loadRequests() {
      this.loadingRequests = true;
      try {
        const res = await rescueRequestAPI.getTrackingList();
        this.requests = res?.data?.data || res?.data || [];
      } catch (error) {
        console.error("Lỗi tải yêu cầu theo dõi:", error);
        // fallback: lấy tất cả request đang active
        try {
          const fallbackRes = await rescueRequestAPI.getList();
          const all = fallbackRes?.data?.data || fallbackRes?.data || [];
          this.requests = all.filter(r => {
            const st = String(r.trangThai || r.trang_thai || "").toUpperCase().replace(/\s+|_/g, "");
            return st === "DAP HANCONG" || st === "DA_PHAN_CONG" || st === "DANGXULY" || st === "DANG_XU_LY" || st === "DADENHIENTRUONG" || st === "DA_DEN_HIEN_TRUONG";
          });
        } catch (e2) {
          console.error("Fallback cũng lỗi:", e2);
        }
      } finally {
        this.loadingRequests = false;
      }
    },
    onMapLoad() {
      this.mapLoaded = true;
      this.syncMarkers();
    },
    syncMarkers() {
      if (!this.$refs.mapRef) return;
      const mapInstance = this.$refs.mapRef.map();
      if (!mapInstance) return;

      // Xóa tất cả marker cũ
      this._markers.forEach(m => m.remove());
      this._markers = [];

      // Render marker cho từng đội
      this.filteredTeams.forEach(team => {
        const el = document.createElement('div');
        el.className = `custom-map-marker marker-${(team.status || "").toLowerCase()}`;
        
        let iconMarkup = '';
        if (team.status === 'ACTIVE') {
          iconMarkup = '<i class="fa-solid fa-truck-medical"></i>';
        } else if (team.status === 'READY') {
          iconMarkup = '<i class="fa-solid fa-shield-halved"></i>';
        } else {
          iconMarkup = '<i class="fa-solid fa-bed"></i>';
        }
        
        el.innerHTML = `
          <div class="marker-pulse"></div>
          <div class="marker-icon">${iconMarkup}</div>
        `;
        
        el.addEventListener('click', () => {
          this.selectTeam(team);
        });

        const marker = new maplibregl.Marker({ element: el })
          .setLngLat([team.lng, team.lat])
          .addTo(mapInstance);
          
        this._markers.push(marker);
      });
    },
    selectTeam(team) {
      this.selectedTeam = team;
      if (this.$refs.mapRef && typeof this.$refs.mapRef.flyTo === 'function') {
        this.$refs.mapRef.flyTo(team.lng, team.lat, 14);
      } else {
        this.defaultCenter = [team.lng, team.lat];
      }
    },
    getStatusLabel(status) {
      if (status === 'ACTIVE') return 'Đang xử lý sự cố';
      if (status === 'READY') return 'Trực tác chiến';
      return 'Ngoại tuyến';
    },
    getStatusTextColor(status) {
      if (status === 'ACTIVE') return 'text-danger';
      if (status === 'READY') return 'text-success';
      return 'text-muted';
    },
    getStatusDotClass(status) {
      if (status === 'ACTIVE') return 'st-active';
      if (status === 'READY') return 'st-ready';
      return 'st-offline';
    },
    getStatusBadgeClass(status) {
      if (status === 'ACTIVE') return 'bg-danger-subtle text-danger border-danger border-opacity-25';
      if (status === 'READY') return 'bg-success-subtle text-success border-success border-opacity-25';
      return 'bg-light text-secondary border';
    }
  }
};
</script>

<style scoped>
.dashboard-container {
  overflow: hidden;
}
.min-h-0 {
  min-height: 0 !important;
}
.tracking-wrapper {
  height: calc(100vh - 130px);
  min-height: 0;
  overflow: hidden;
}
.map-container-box {
  position: relative;
  border-radius: 1.25rem;
  overflow: hidden;
  box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0,0,0,0.05);
}
.floating-overlay {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  z-index: 10;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(16px);
  border-radius: 1.25rem;
  box-shadow: 0 12px 40px rgba(0,0,0,0.12);
  border: 1px solid rgba(255,255,255,0.8);
  padding: 1.5rem;
  width: 340px;
  animation: slideIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

@keyframes slideIn {
  from { opacity: 0; transform: translateX(30px) scale(0.95); }
  to { opacity: 1; transform: translateX(0) scale(1); }
}

.team-item {
  transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
  cursor: pointer;
  border-radius: 1rem;
  border: 2px solid transparent !important;
  background-color: #fff;
}
.team-item:hover {
  background-color: #f8f9fa;
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}
.team-item.active {
  background-color: #f0f7ff;
  border-color: rgba(13, 110, 253, 0.3) !important;
  box-shadow: 0 4px 15px rgba(13, 110, 253, 0.08);
}
.status-dot-tracking {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
  box-shadow: 0 0 0 2px #fff;
}
.st-active { 
  background-color: #dc3545; 
  box-shadow: 0 0 0 2px #fff, 0 0 8px #dc3545; 
  animation: pulse-danger 2s infinite; 
}
.st-ready { 
  background-color: #198754; 
  box-shadow: 0 0 0 2px #fff, 0 0 8px rgba(25, 135, 84, 0.8);
}
.st-offline { background-color: #adb5bd; }

@keyframes pulse-danger {
  0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
  70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
  100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

.search-box-wrap {
  position: relative;
}
.search-box-wrap i {
  position: absolute;
  left: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  font-size: 1.1rem;
}
.search-box-wrap input {
  padding-left: 3rem;
  border-radius: 2rem;
  border: 1px solid #dee2e6;
  padding-top: 0.6rem;
  padding-bottom: 0.6rem;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.02);
  background: #f8f9fa;
  font-weight: 500;
  transition: all 0.2s;
}
.search-box-wrap input:focus {
  background: #fff;
  box-shadow: 0 0 0 0.3rem rgba(13, 110, 253, 0.15);
  border-color: #86b7fe;
  outline: none;
}
.filter-tabs .btn {
  border-radius: 0.5rem;
}
.panel-left-tracking {
  background: white;
  border-radius: 1.25rem;
  box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
}
.stats-card-tracking {
  background: white;
  border-radius: 1.25rem;
  padding: 1.25rem 1.5rem;
  box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.04);
  border: 1px solid #f8f9fa;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  min-width: 220px;
  transition: transform 0.2s;
}
.stats-card-tracking:hover {
  transform: translateY(-2px);
}
.icon-circle {
  width: 54px;
  height: 54px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}
.tracking-wider {
  letter-spacing: 0.05em;
}
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #ced4da;
  border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #adb5bd;
}

/* Custom Marker Styles */
.custom-map-marker {
  position: relative;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 1;
}
.custom-map-marker:hover .marker-icon {
  transform: scale(1.1);
  box-shadow: 0 6px 14px rgba(0,0,0,0.25);
}

.marker-icon {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.15);
  z-index: 2;
  border: 2px solid;
  transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.marker-pulse {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  height: 100%;
  border-radius: 50%;
  z-index: 0;
  animation: markerPulse 2s infinite;
}

.marker-active .marker-icon {
  color: #dc3545;
  border-color: #dc3545;
}
.marker-active .marker-pulse {
  background: rgba(220, 53, 69, 0.4);
}

.marker-ready .marker-icon {
  color: #198754;
  border-color: #198754;
}
.marker-ready .marker-pulse {
  background: rgba(25, 135, 84, 0.4);
}

.marker-offline .marker-icon {
  color: #6c757d;
  border-color: #6c757d;
}
.marker-offline .marker-pulse {
  display: none;
}

@keyframes markerPulse {
  0% { transform: translate(-50%, -50%) scale(0.8); opacity: 1; }
  100% { transform: translate(-50%, -50%) scale(2.2); opacity: 0; }
}
</style>

