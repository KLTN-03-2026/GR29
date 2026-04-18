<template>
  <div class="rescuer-dashboard-wrapper">
    <div class="mission-layout h-100 d-flex flex-column flex-lg-row">

      <!-- Sidebar -->
      <div class="mission-sidebar d-flex flex-column bg-white z-3 shadow-lg">
        <div class="sidebar-header p-4 pb-3 border-bottom position-relative overflow-hidden">
          <div class="bg-shape bg-primary rounded-circle position-absolute opacity-10" style="width: 150px; height: 150px; top: -50px; right: -50px;"></div>

          <div class="d-flex align-items-center mb-3 position-relative z-1">
            <div class="icon-box bg-primary bg-opacity-10 text-primary me-3 d-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px;">
              <i class="bi bi-radar fs-4"></i>
            </div>
            <div>
              <h5 class="fw-bolder mb-1 text-dark">Radar Cứu Hộ</h5>
                <span class="text-muted small fw-medium">Có {{ radarAssignments.length }} nhiệm vụ chờ tiếp nhận</span>
            </div>
          </div>

          <!-- Active request warning banner -->
          <div v-if="hasActiveRequest" class="alert alert-danger py-2 px-3 mb-0 small fw-bold d-flex align-items-center gap-2 position-relative z-1">
            <i class="bi bi-exclamation-octagon-fill"></i>
            Bạn đang có nhiệm vụ đang xử lý — không thể tiếp nhận thêm.
          </div>

          <div class="d-flex gap-2 position-relative z-1 overflow-auto pb-1 hide-scrollbar">
            <button class="btn btn-sm btn-dark rounded-pill px-3 fw-bold shadow-sm" @click="activeTab = 'all'">Tất cả</button>
            <button class="btn btn-sm bg-danger bg-opacity-10 text-danger border-0 rounded-pill px-3 fw-bold" @click="activeTab = 'critical'">Khẩn cấp</button>
          </div>
        </div>

        <div class="mission-list flex-grow-1 overflow-auto p-3 d-flex flex-column gap-3 bg-secondary bg-opacity-10">
          <!-- Loading -->
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-danger" role="status"></div>
            <p class="mt-2 text-muted small">Đang tải...</p>
          </div>

          <!-- Mission Cards -->
          <div v-else-if="displayAssignments.length === 0" class="text-center py-5">
            <div class="mb-3">
              <i class="bi bi-check-circle text-success" style="font-size: 48px;"></i>
            </div>
            <h6 class="text-secondary fw-bold">Không có nhiệm vụ nào</h6>
            <p class="text-muted small">Hệ thống sẽ thông báo khi có nhiệm vụ mới</p>
          </div>

          <div v-else v-for="item in displayAssignments" :key="item.id_phan_cong" class="mission-card card border-0 shadow-sm rounded-4 transition-all" :class="getCardClass(item)" @click="selectMission(item)">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <span :class="['badge rounded-pill px-3 py-2 fw-bold border', getSeverityBadgeClass(item)]">
                  <i class="bi bi-exclamation-triangle-fill me-1" v-if="getSeverityLabel(item) === 'KHẨN CẤP'"></i>
                  {{ getSeverityLabel(item) }}
                </span>
                <div class="text-end">
                  <div class="fw-bold text-dark fs-5">{{ item.yeu_cau && item.yeu_cau.muc_do_khan_cap ? item.yeu_cau.muc_do_khan_cap : '-' }}</div>
                  <div class="text-muted small fw-medium"><i class="bi bi-clock me-1 text-primary"></i>{{ item.created_at ? formatTime(item.created_at) : '-' }}</div>
                </div>
              </div>
              <h6 class="fw-bold fs-6 mb-2 text-dark">
                {{ getIncidentTypeName(item) }}
              </h6>
              <div class="d-flex align-items-start mb-2 text-secondary small fw-medium">
                <i class="bi bi-geo-alt-fill me-2 text-danger mt-1"></i>
                <span>{{ getRequestAddress(item) }}</span>
              </div>
              <div class="d-flex align-items-center text-secondary small fw-medium mb-3">
                <i class="bi bi-person-fill me-2 text-primary"></i>
                <span>{{ getReporterName(item) }}</span>
              </div>
              <button
                v-if="item.trang_thai_nhiem_vu === 'MOI'"
                class="btn btn-primary w-100 fw-bold btn-lg rounded-3 py-2 btn-accept shadow-sm"
                :class="{ 'disabled': hasActiveRequest }"
                :disabled="hasActiveRequest"
                @click.stop="acceptMission(item)"
              >
                <i class="bi bi-check-circle me-2"></i>TIẾP NHẬN NGAY
              </button>
              <button
                v-else
                class="btn btn-outline-secondary w-100 fw-bold btn-lg rounded-3 py-2"
                @click.stop="goToMission(item)"
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
        <div ref="mapContainer" id="rescuer-home-map" style="width: 100%; height: 100%;"></div>

        <!-- Selected Mission Info Card -->
        <div class="mission-popup-card position-absolute m-3 m-lg-4 z-3 rounded-4" style="max-width: 360px;" v-if="selectedMission">
          <div class="card border-0 shadow-lg bg-white rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <div class="pulse-indicator bg-danger me-2 shadow-sm"></div>
                <span class="fw-bold text-danger small text-uppercase">Nhiệm vụ đã chọn</span>
              </div>
              <button class="btn btn-sm btn-icon btn-light rounded-circle text-muted" @click="selectedMission = null"><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="card-body px-4 pb-4 pt-3">
              <h5 class="fw-bold mb-2 text-dark">{{ getIncidentTypeName(selectedMission) }}</h5>
              <p class="text-secondary small mb-3 fw-medium">
                <i class="bi bi-map text-primary me-1"></i>
                {{ getRequestAddress(selectedMission) }}
              </p>

              <div class="victim-info bg-light rounded-3 p-3 mb-3 border border-light shadow-sm" v-if="selectedMission.yeu_cau">
                <div class="d-flex align-items-center mb-2">
                  <div class="avatar-circle bg-white text-primary shadow-sm me-3 fw-bold fs-5 border d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: 50%;">
                    {{ (getReporterName(selectedMission) || 'N').charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <div class="text-muted fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">NGƯỜI GẶP NẠN</div>
                    <div class="fw-bold text-dark">{{ getReporterName(selectedMission) }}</div>
                  </div>
                </div>
                <div class="small text-secondary mb-2" v-if="selectedMission.yeu_cau.so_dien_thoai_nguoi_dung || getReporterPhone(selectedMission)">
                  <i class="bi bi-telephone me-1"></i>
                  {{ selectedMission.yeu_cau.so_dien_thoai_nguoi_dung || getReporterPhone(selectedMission) }}
                </div>
                <div class="small text-secondary" v-if="selectedMission.yeu_cau.so_nguoi_bi_anh_huong">
                  <i class="bi bi-people me-1"></i>
                  {{ selectedMission.yeu_cau.so_nguoi_bi_anh_huong }} người bị ảnh hưởng
                </div>
                <div class="d-flex gap-2 mt-3">
                  <a v-if="selectedMission.yeu_cau.so_dien_thoai_nguoi_dung || getReporterPhone(selectedMission)"
                     :href="'tel:' + (selectedMission.yeu_cau.so_dien_thoai_nguoi_dung || getReporterPhone(selectedMission))"
                     class="btn btn-success rounded-3 btn-sm fw-bold flex-grow-1">
                    <i class="bi bi-telephone-fill me-1"></i> Gọi
                  </a>
                  <a v-if="getRequestAddress(selectedMission)"
                     :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(getRequestAddress(selectedMission))"
                     target="_blank"
                     class="btn btn-danger rounded-3 btn-sm fw-bold flex-grow-1">
                    <i class="bi bi-cursor me-1"></i> Chỉ đường
                  </a>
                </div>
              </div>

              <div class="mb-3" v-if="selectedMission.yeu_cau && selectedMission.yeu_cau.mo_ta">
                <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">MÔ TẢ</div>
                <div class="text-secondary small">{{ selectedMission.yeu_cau.mo_ta }}</div>
              </div>

              <div class="mb-3" v-if="selectedMission.doi_cuu_ho">
                <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">ĐỘI CHỈ THỊ</div>
                <div class="text-dark small fw-medium">{{ selectedMission.doi_cuu_ho.ten_co }}</div>
              </div>

              <button
                v-if="selectedMission.trang_thai_nhiem_vu === 'MOI'"
                class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-danger"
                :class="{ 'disabled': hasActiveRequest }"
                :disabled="hasActiveRequest"
                @click="acceptMission(selectedMission)"
              >
                <i class="bi bi-check-circle-fill me-2"></i> TIẾP NHẬN NHIỆM VỤ
              </button>
              <button v-else @click="goToMission(selectedMission)" class="btn btn-danger w-100 py-3 fw-bold rounded-3 shadow-danger">
                <i class="bi bi-cursor-fill me-2"></i> XEM CHI TIẾT
              </button>
            </div>
          </div>
        </div>

        <!-- Map Controls -->
        <div class="map-controls position-absolute bottom-0 end-0 m-4 d-flex flex-column gap-2 z-3 d-none d-md-flex">
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-dark btn-map-control hover-bg-gray" @click="zoomIn"><i class="bi bi-plus-lg"></i></button>
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-dark btn-map-control hover-bg-gray" @click="zoomOut"><i class="bi bi-dash-lg"></i></button>
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-primary mt-2 btn-map-control hover-bg-gray" @click="locateMe"><i class="bi bi-crosshair"></i></button>
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
  name: "RescuerHome",
  data() {
    return {
      loading: false,
      assignments: [],
      selectedMission: null,
      activeTab: 'all',
      map: null,
      teamMarker: null,
      requestMarkers: [],
      routeLine: null,
      teamId: null,
      teamLat: null,
      teamLng: null,
      hasActiveRequest: false,
    };
  },
  computed: {
    // Issue #5 fix: filter assignments by current team + status DA_PHAN_CONG
    radarAssignments() {
      const urgencyOrder = { 'CRITICAL': 4, 'HIGH': 3, 'MEDIUM': 2, 'LOW': 1 };
      const teamId = this.teamId;

      let filtered = this.assignments.filter(a => {
        // Filter: must be assigned to current team
        if (teamId && Number(a.id_doi_cuu_ho) !== Number(teamId)) return false;
        // Filter: status must be DA_PHAN_CONG (dispatched, awaiting acceptance)
        const st = (a.trang_thai_nhiem_vu || '').toUpperCase().replace(/\s+/g, '_');
        return st === 'DA_PHAN_CONG' || st === 'MOI';
      });

      // Issue #6 fix: sort by emergency level first (CRITICAL > HIGH > MEDIUM > LOW),
      // then by created time ascending (older first)
      filtered.sort((a, b) => {
        const sevA = (a.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
        const sevB = (b.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
        const urgA = urgencyOrder[sevA] ?? 0;
        const urgB = urgencyOrder[sevB] ?? 0;
        if (urgA !== urgB) return urgB - urgA;

        // Same urgency: older first (ascending by created_at)
        const timeA = new Date(a.created_at || 0).getTime();
        const timeB = new Date(b.created_at || 0).getTime();
        return timeA - timeB;
      });

      return filtered;
    },
    displayAssignments() {
      if (this.activeTab === 'critical') {
        return this.radarAssignments.filter(a => {
          const sev = a.yeu_cau && a.yeu_cau.muc_do_khan_cap ? a.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
          return sev === 'CRITICAL' || sev === 'HIGH';
        });
      }
      return this.radarAssignments;
    },
    activeAssignments() {
      return this.assignments.filter(a => {
        const status = a.trang_thai_nhiem_vu || '';
        return status === 'DANG_XU_LY' || status === 'DA_DEN_HIEN_TRUONG';
      });
    },
  },
  async mounted() {
    this.loadTeamData();
    await this.fetchAssignments();
    this.initMap();
  },
  beforeUnmount() {
    if (this.map) {
      this.map.remove();
    }
  },
  methods: {
    loadTeamData() {
      const teamStr = localStorage.getItem("rescuer_team");
      if (teamStr) {
        try {
          const team = JSON.parse(teamStr);
          this.teamId = team.id_doi_cuu_ho || team.id;
          this.teamLat = team.vi_tri_lat || null;
          this.teamLng = team.vi_tri_lng || null;
        } catch (e) {
          console.error('Error parsing team data', e);
        }
      }
    },
    async fetchAssignments() {
      this.loading = true;
      try {
        // Issue #5 fix: Only fetch assignments assigned to current team
        // and only those with status DA_PHAN_CONG (dispatched, awaiting acceptance)
        let all = [];
        if (this.teamId) {
          const res = await rescuerAPI.getAssignmentByTeam(this.teamId, { per_page: 100 });
          if (res.data && res.data.data) {
            const rawItems = res.data.data.data || res.data.data;
            all = Array.isArray(rawItems) ? rawItems : [];
          }
        } else {
          const res = await rescuerAPI.getAssignments({ per_page: 100 });
          if (res.data && res.data.data) {
            const rawItems = res.data.data.data || res.data.data;
            all = Array.isArray(rawItems) ? rawItems : [];
          }
        }
        this.assignments = all;
        this.updateMapMarkers();

        // Step 3 fix: check if team already has an active assignment
        // to disable "Tiếp nhận" buttons and show correct state
        await this.checkActiveAssignment();
      } catch (e) {
        console.error("Lỗi tải phân công:", e);
        toaster.error("Không thể tải danh sách nhiệm vụ");
      } finally {
        this.loading = false;
      }
    },
    async checkActiveAssignment() {
      try {
        if (!this.teamId) return;
        const res = await rescuerAPI.getActiveAssignment(this.teamId);
        this.hasActiveRequest = res.data?.has_active === true;
      } catch (e) {
        console.error("Lỗi kiểm tra yêu cầu đang xử lý:", e);
      }
    },
    initMap() {
      if (typeof L === 'undefined') {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = () => this.initLeafletMap();
        document.head.appendChild(script);
      } else {
        this.initLeafletMap();
      }
    },
    initLeafletMap() {
      const defaultCenter = [16.0544, 108.2022];
      this.map = L.map('rescuer-home-map').setView(defaultCenter, 14);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
      }).addTo(this.map);

      if (this.teamLat && this.teamLng) {
        this.addTeamMarker(this.teamLat, this.teamLng);
      } else {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition((pos) => {
            this.teamLat = pos.coords.latitude;
            this.teamLng = pos.coords.longitude;
            this.addTeamMarker(this.teamLat, this.teamLng);
            this.map.setView([this.teamLat, this.teamLng], 14);
          }, () => {
            this.addTeamMarker(defaultCenter[0], defaultCenter[1]);
          });
        }
      }

      this.updateMapMarkers();
    },
    addTeamMarker(lat, lng) {
      if (this.teamMarker) {
        this.map.removeLayer(this.teamMarker);
      }
      const icon = L.divIcon({
        html: '<div style="width:20px;height:20px;background:#2563eb;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3)"></div>',
        className: '',
        iconSize: [20, 20],
        iconAnchor: [10, 10],
      });
      this.teamMarker = L.marker([lat, lng], { icon: icon }).addTo(this.map);
      this.teamMarker.bindPopup('<b>Vị trí đội của bạn</b>');
    },
    updateMapMarkers() {
      if (!this.map) return;

      this.requestMarkers.forEach(m => this.map.removeLayer(m));
      this.requestMarkers = [];

      // Issue #5 fix: only show markers for current team's radar assignments on the map
      this.radarAssignments.forEach(item => {
        if (item.yeu_cau && item.yeu_cau.vi_tri_lat && item.yeu_cau.vi_tri_lng) {
          const sev = item.yeu_cau.muc_do_khan_cap ? item.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
          let color = '#16a34a';
          if (sev === 'CRITICAL') color = '#7f1d1d';
          else if (sev === 'HIGH') color = '#dc2626';
          else if (sev === 'MEDIUM') color = '#f59e0b';
          else if (sev === 'LOW') color = '#16a34a';

          const icon = L.divIcon({
            html: `<div style="width:28px;height:28px;background:${color};border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:white;font-size:12px;font-weight:bold;">!</div>`,
            className: '',
            iconSize: [28, 28],
            iconAnchor: [14, 14],
          });

          const marker = L.marker([item.yeu_cau.vi_tri_lat, item.yeu_cau.vi_tri_lng], { icon: icon })
            .addTo(this.map);

          const typeName = this.getIncidentTypeName(item);
          const address = this.getRequestAddress(item);
          const reporterName = this.getReporterName(item);
          const phone = item.yeu_cau && item.yeu_cau.so_dien_thoai_nguoi_dung ? item.yeu_cau.so_dien_thoai_nguoi_dung : (item.yeu_cau && item.yeu_cau.nguoi_dung ? item.yeu_cau.nguoi_dung.so_dien_thoai : '');
          const victims = item.yeu_cau && item.yeu_cau.so_nguoi_bi_anh_huong ? item.yeu_cau.so_nguoi_bi_anh_huong : '';

          marker.bindPopup(`
            <div style="min-width:220px">
              <h6 style="margin:0 0 8px;font-weight:bold;color:#1e293b">${typeName}</h6>
              <p style="margin:0 0 4px;font-size:12px;color:#64748b"><i class="bi bi-geo-alt"></i> ${address || 'Chưa có địa chỉ'}</p>
              <p style="margin:0 0 4px;font-size:12px;color:#64748b"><i class="bi bi-person"></i> ${reporterName || 'Không rõ'}</p>
              ${phone ? `<p style="margin:0 0 4px;font-size:12px;color:#2563eb"><i class="bi bi-telephone"></i> ${phone}</p>` : ''}
              ${victims ? `<p style="margin:0;font-size:12px;color:#dc2626"><i class="bi bi-people"></i> ${victims} người bị ảnh hưởng</p>` : ''}
            </div>
          `);

          marker.on('click', () => {
            this.selectMission(item);
          });

          this.requestMarkers.push(marker);
        }
      });
    },
    selectMission(item) {
      this.selectedMission = item;
      if (item && item.yeu_cau && item.yeu_cau.vi_tri_lat && item.yeu_cau.vi_tri_lng) {
        if (this.teamLat && this.teamLng) {
          this.drawRoute(this.teamLat, this.teamLng, item.yeu_cau.vi_tri_lat, item.yeu_cau.vi_tri_lng);
        }
        this.map.flyTo([item.yeu_cau.vi_tri_lat, item.yeu_cau.vi_tri_lng], 15);
      }
    },
    drawRoute(lat1, lng1, lat2, lng2) {
      if (this.routeLine) {
        this.map.removeLayer(this.routeLine);
        this.routeLine = null;
      }

      L.polyline([[lat1, lng1], [lat2, lng2]], {
        color: '#2563eb',
        weight: 4,
        opacity: 0.7,
        dashArray: '10, 10',
      }).addTo(this.map);

      this.routeLine = L.polyline([[lat1, lng1], [lat2, lng2]], {
        color: '#2563eb',
        weight: 2,
        opacity: 0.9,
      });
    },
    async acceptMission(item) {
      try {
        await rescuerAPI.updateAssignmentStatus(item.id_phan_cong, {
          trang_thai_nhiem_vu: 'DANG_XU_LY'
        });
        toaster.success("Đã tiếp nhận nhiệm vụ");
        await this.fetchAssignments();
        this.selectedMission = null;
        this.$router.push("/rescuer/dang-xu-ly");
      } catch (e) {
        console.error("Lỗi tiếp nhận:", e);
        const msg = e.response?.data?.message || e.response?.data?.msg || "Không thể tiếp nhận nhiệm vụ";
        toaster.error(msg);
      }
    },
    goToMission(item) {
      this.$router.push("/rescuer/dang-xu-ly");
    },
    zoomIn() {
      if (this.map) this.map.zoomIn();
    },
    zoomOut() {
      if (this.map) this.map.zoomOut();
    },
    locateMe() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
          this.teamLat = pos.coords.latitude;
          this.teamLng = pos.coords.longitude;
          this.addTeamMarker(this.teamLat, this.teamLng);
          this.map.setView([this.teamLat, this.teamLng], 15);
        });
      }
    },
    formatTime(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return dateStr;
      return d.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });
    },
    getSeverityLabel(item) {
      const sev = item.yeu_cau && item.yeu_cau.muc_do_khan_cap ? item.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
      if (sev === 'CRITICAL') return 'KHẨN CẤP';
      if (sev === 'HIGH') return 'KHẨN CẤP';
      if (sev === 'MEDIUM') return 'TRUNG BÌNH';
      return 'THƯỜNG';
    },
    getSeverityBadgeClass(item) {
      const sev = item.yeu_cau && item.yeu_cau.muc_do_khan_cap ? item.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
      if (sev === 'CRITICAL') return 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
      if (sev === 'HIGH') return 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
      if (sev === 'MEDIUM') return 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25';
      return 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
    },
    getCardClass(item) {
      const sev = item.yeu_cau && item.yeu_cau.muc_do_khan_cap ? item.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
      if (sev === 'CRITICAL' || sev === 'HIGH') return 'border-danger border-2';
      return '';
    },
    getIncidentTypeName(item) {
      if (item.yeu_cau && item.yeu_cau.loai_su_co) {
        return item.yeu_cau.loai_su_co.ten_danh_muc || item.yeu_cau.loai_su_co.ten_loai_su_co || 'Yêu cầu cứu hộ';
      }
      return 'Yêu cầu cứu hộ';
    },
    getRequestAddress(item) {
      if (item.yeu_cau) {
        if (item.yeu_cau.vi_tri_dia_chi) return item.yeu_cau.vi_tri_dia_chi;
        if (item.yeu_cau.dia_chi) return item.yeu_cau.dia_chi;
        if (item.yeu_cau.vi_tri) return item.yeu_cau.vi_tri;
      }
      return 'Chưa có địa chỉ';
    },
    getReporterName(item) {
      if (item.yeu_cau) {
        if (item.yeu_cau.ho_ten_nguoi_dung) return item.yeu_cau.ho_ten_nguoi_dung;
        if (item.yeu_cau.hoTenNguoiDung) return item.yeu_cau.hoTenNguoiDung;
        if (item.yeu_cau.nguoi_dung) {
          return item.yeu_cau.nguoi_dung.ho_ten || item.yeu_cau.nguoi_dung.hoTen || 'Không rõ';
        }
      }
      return 'Không rõ';
    },
    getReporterPhone(item) {
      if (item.yeu_cau) {
        if (item.yeu_cau.so_dien_thoai_nguoi_dung) return item.yeu_cau.so_dien_thoai_nguoi_dung;
        if (item.yeu_cau.soDienThoaiNguoiDung) return item.yeu_cau.soDienThoaiNguoiDung;
        if (item.yeu_cau.nguoi_dung) {
          return item.yeu_cau.nguoi_dung.so_dien_thoai || item.yeu_cau.nguoi_dung.soDienThoai || '';
        }
      }
      return '';
    },
  },
};
</script>

<style scoped>
.rescuer-dashboard-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px);
  overflow: hidden;
  font-family: 'Inter', 'Roboto', sans-serif;
}

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

.mission-sidebar .mission-list {
  flex: 1;
  min-height: 0;
}

.hover-bg-gray:hover { background-color: #f3f4f6 !important; }
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.mission-card {
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
  border: 1px solid rgba(0,0,0,0.03);
}
.mission-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}
.mission-card.border-2 {
  border-width: 2px !important;
}

.btn-accept {
  background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
  border: none;
}
.btn-accept:hover {
  background: linear-gradient(135deg, #0b5ed7 0%, #084298 100%);
  transform: translateY(-1px);
}

.map-area {
  position: relative;
  min-height: 400px;
}

.mission-popup-card {
  max-height: calc(100vh - 200px);
  overflow-y: auto;
}

.map-controls {
  position: absolute;
  bottom: 0;
  right: 0;
}

.btn-map-control {
  width: 40px; height: 40px;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.btn-map-control:hover { background-color: #f8f9fa !important; transform: translateX(-2px); }

.pulse-indicator {
  width: 10px; height: 10px;
  border-radius: 50%;
  box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
  animation: pulse 1.5s infinite;
}
@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
  70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
  100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}
</style>
