<template>
  <div class="container-fluid px-0">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1 fw-bold text-dark"><i class="fa-solid fa-gauge-high text-primary me-2"></i>Dashboard Tổng Quan</h4>
        <p class="text-muted mb-0 small">Giám sát toàn diện hoạt động cứu hộ</p>
      </div>
      <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2" @click="loadAllData" :disabled="loading">
        <i class="fa-solid fa-rotate" :class="{ 'spin': loading }"></i>
        Làm mới
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-lg mx-auto mb-3"></div>
      <p class="text-muted">Đang tải dữ liệu dashboard...</p>
    </div>

    <template v-else>
      <!-- Summary Cards Row -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="card summary-card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="summary-icon bg-primary-subtle text-primary">
                  <i class="fa-solid fa-bell"></i>
                </div>
                <span class="badge rounded-pill fs-09" :class="stats.pending > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'">
                  {{ stats.pending > 0 ? 'Cần xử lý' : 'Tốt' }}
                </span>
              </div>
              <div>
                <div class="text-muted small fw-semibold text-uppercase">Chờ xử lý</div>
                <div class="h3 mb-0 fw-bold text-dark">{{ stats.pending }}</div>
                <div class="small text-muted mt-1">
                  <span class="text-danger fw-semibold">{{ stats.criticalPending }}</span> khẩn cấp
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="card summary-card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="summary-icon bg-warning-subtle text-warning">
                  <i class="fa-solid fa-spinner"></i>
                </div>
                <span class="badge rounded-pill bg-warning-subtle text-warning fs-09">Đang hoạt động</span>
              </div>
              <div>
                <div class="text-muted small fw-semibold text-uppercase">Đang xử lý</div>
                <div class="h3 mb-0 fw-bold text-dark">{{ stats.processing }}</div>
                <div class="small text-muted mt-1">
                  <span class="text-warning fw-semibold">{{ stats.teamsBusy }}</span> đội đang nhiệm vụ
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="card summary-card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="summary-icon bg-success-subtle text-success">
                  <i class="fa-solid fa-circle-check"></i>
                </div>
                <span class="badge rounded-pill bg-success-subtle text-success fs-09">Hoàn thành</span>
              </div>
              <div>
                <div class="text-muted small fw-semibold text-uppercase">Hoàn thành</div>
                <div class="h3 mb-0 fw-bold text-dark">{{ stats.completed }}</div>
                <div class="small text-muted mt-1">tổng số {{ stats.total }} yêu cầu
                  <span class="text-success fw-semibold">({{ completionRate }}%)</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="card summary-card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="summary-icon bg-info-subtle text-info">
                  <i class="fa-solid fa-users-gear"></i>
                </div>
                <span class="badge rounded-pill bg-info-subtle text-info fs-09">Đội cứu hộ</span>
              </div>
              <div>
                <div class="text-muted small fw-semibold text-uppercase">Đội sẵn sàng</div>
                <div class="h3 mb-0 fw-bold text-dark">{{ stats.teamsAvailable }}</div>
                <div class="small text-muted mt-1">trên {{ stats.totalTeams }} đội đã đăng ký
                  <span class="text-info fw-semibold">({{ teamAvailabilityRate }}%)</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="row g-3 mb-4">
        <!-- Incidents by Type -->
        <div class="col-12 col-lg-8">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0 fw-semibold">Phân bố sự cố theo loại</h6>
                  <small class="text-muted">Top loại sự cố phổ biến nhất</small>
                </div>
                <div class="d-flex gap-2">
                  <button
                    v-for="range in timeRanges"
                    :key="range.value"
                    class="btn btn-sm"
                    :class="timeRange === range.value ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="changeTimeRange(range.value)"
                  >
                    {{ range.label }}
                  </button>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div v-if="typeStats.length > 0" class="d-flex flex-column gap-3">
                <div v-for="(item, idx) in typeStats" :key="item.name" class="d-flex align-items-center gap-3">
                  <div class="type-dot rounded-circle flex-shrink-0" :style="{ background: typeColors[idx % typeColors.length] }"></div>
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="small fw-semibold text-dark">{{ item.name }}</span>
                      <span class="small fw-bold text-dark">{{ item.count }} yêu cầu</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 8px;">
                      <div
                        class="progress-bar rounded-pill"
                        :style="{ width: (item.count / Math.max(stats.total, 1) * 100) + '%', background: typeColors[idx % typeColors.length] }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4 text-muted">
                <i class="fa-solid fa-chart-pie fs-1 opacity-25 mb-2 d-block"></i>
                <p class="mb-0 small">Chưa có dữ liệu sự cố</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Processing Status -->
        <div class="col-12 col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
              <h6 class="mb-0 fw-semibold">Tỷ lệ xử lý</h6>
              <small class="text-muted">Phân bố trạng thái hiện tại</small>
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <div class="donut-wrapper position-relative mb-3">
                <svg viewBox="0 0 100 100" width="160" height="160">
                  <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                  <circle
                    v-for="(seg, i) in donutSegments"
                    :key="i"
                    cx="50" cy="50" r="40"
                    fill="none"
                    :stroke="seg.color"
                    stroke-width="14"
                    :stroke-dasharray="seg.dashArray"
                    :stroke-dashoffset="seg.dashOffset"
                    stroke-linecap="round"
                    transform="rotate(-90 50 50)"
                  />
                </svg>
                <div class="donut-center position-absolute top-50 start-50 translate-middle text-center">
                  <div class="h4 mb-0 fw-bold text-dark">{{ stats.total }}</div>
                  <div class="small text-muted">Tổng</div>
                </div>
              </div>
              <div class="w-100">
                <div v-for="seg in donutSegments" :key="seg.label" class="d-flex justify-content-between align-items-center mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <span class="legend-dot rounded-circle" :style="{ background: seg.color }"></span>
                    <span class="small text-dark">{{ seg.label }}</span>
                  </div>
                  <span class="small fw-bold text-dark">{{ seg.count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Row -->
      <div class="row g-3">
        <!-- Recent Requests Queue -->
        <div class="col-12 col-xl-8">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0 fw-semibold">Yêu cầu chờ xử lý</h6>
                  <small class="text-muted">Danh sách ưu tiên cần điều phối</small>
                </div>
                <router-link to="/admin/queue" class="btn btn-sm btn-outline-primary">
                  Xem tất cả <i class="fa-solid fa-arrow-right ms-1"></i>
                </router-link>
              </div>
            </div>
            <div class="card-body p-0">
              <div v-if="pendingRequests.length > 0" class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead class="bg-light">
                    <tr class="small text-muted text-uppercase">
                      <th class="border-0 py-3 px-3">Mã</th>
                      <th class="border-0 py-3">Loại sự cố</th>
                      <th class="border-0 py-3">Vị trí</th>
                      <th class="border-0 py-3">Mức độ</th>
                      <th class="border-0 py-3">Thời gian</th>
                      <th class="border-0 py-3 px-3"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="req in pendingRequests.slice(0, 8)" :key="req.id" class="hover-row">
                      <td class="px-3 py-3">
                        <span class="fw-bold text-primary">#{{ req.id }}</span>
                      </td>
                      <td class="py-3">
                        <span class="fw-semibold text-dark">{{ req.type }}</span>
                      </td>
                      <td class="py-3">
                        <span class="text-muted small text-truncate d-inline-block" style="max-width: 150px;">{{ req.address }}</span>
                      </td>
                      <td class="py-3">
                        <span class="badge rounded-pill" :class="req.severityBadge">{{ req.severityLabel }}</span>
                      </td>
                      <td class="py-3">
                        <span class="text-muted small">{{ req.time }}</span>
                      </td>
                      <td class="px-3 py-3">
                        <router-link :to="'/admin/assignments?id=' + req.id" class="btn btn-sm btn-primary">
                          Điều phối
                        </router-link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-5 text-muted">
                <i class="fa-solid fa-inbox fs-1 opacity-25 mb-2 d-block"></i>
                <p class="mb-0 small">Không có yêu cầu nào đang chờ</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Rescue Teams Status -->
        <div class="col-12 col-xl-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0 fw-semibold">Trạng thái đội cứu hộ</h6>
                  <small class="text-muted">Tổng {{ stats.totalTeams }} đội</small>
                </div>
                <router-link to="/admin/resources" class="btn btn-sm btn-outline-primary">
                  Quản lý <i class="fa-solid fa-arrow-right ms-1"></i>
                </router-link>
              </div>
            </div>
            <div class="card-body">
              <div v-if="teams.length > 0" class="d-flex flex-column gap-2">
                <div v-for="team in teams.slice(0, 6)" :key="team.id" class="team-item p-2 rounded-3 d-flex align-items-center gap-3">
                  <div class="team-avatar rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" :class="team.statusColor">
                    {{ team.initial }}
                  </div>
                  <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-dark small text-truncate" :title="team.name">{{ team.name }}</div>
                    <div class="text-muted" style="font-size: 11px;">{{ team.area || 'Chưa phân khu vực' }}</div>
                  </div>
                  <span class="badge rounded-pill flex-shrink-0" :class="team.statusBadge">
                    {{ team.statusLabel }}
                  </span>
                </div>
              </div>
              <div v-else class="text-center py-4 text-muted">
                <i class="fa-solid fa-users-gear fs-1 opacity-25 mb-2 d-block"></i>
                <p class="mb-0 small">Chưa có thông tin đội cứu hộ</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Mini Heatmap Map -->
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-fire text-danger me-2"></i>Bản đồ vị trí sự cố</h6>
                  <small class="text-muted">{{ mapIncidents.length }} sự cố có tọa độ • Click marker để xem chi tiết</small>
                </div>
                <router-link to="/admin/heatmap" class="btn btn-sm btn-outline-danger">
                  <i class="fa-solid fa-expand me-1"></i>Bản đồ nhiệt đầy đủ
                </router-link>
              </div>
            </div>
            <div class="card-body p-2">
              <div class="map-wrapper rounded-4 overflow-hidden position-relative" style="height: 340px;">
                <MapboxMap
                  :center="mapCenter"
                  :zoom="12"
                  :showMarker="false"
                  :key="'dashboard-map-' + mapRefreshKey"
                />
                <!-- Map Stats Overlay -->
                <div class="map-stats-overlay position-absolute top-0 start-0 m-3 z-3">
                  <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                      <i class="fa-solid fa-fire me-1"></i>{{ stats.criticalPending }} khẩn cấp
                    </span>
                    <span class="badge bg-info-subtle text-info border border-info px-2 py-1">
                      <i class="fa-solid fa-clock me-1"></i>{{ stats.pending }} chờ xử lý
                    </span>
                    <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1">
                      <i class="fa-solid fa-spinner me-1"></i>{{ stats.processing }} đang xử lý
                    </span>
                  </div>
                </div>
                <!-- Zone Legend -->
                <div class="map-legend-overlay position-absolute bottom-0 end-0 m-3 z-3 bg-white bg-opacity-90 rounded-3 p-2 shadow-sm">
                  <div class="small fw-semibold text-dark mb-1">Khu vực nhiều sự cố</div>
                  <div v-for="zone in zoneStats.slice(0, 5)" :key="zone.name" class="d-flex justify-content-between align-items-center mb-1 gap-3">
                    <div class="d-flex align-items-center gap-1">
                      <span class="legend-dot rounded-circle" :style="{ background: getZoneColor(zone.count) }"></span>
                      <span class="small text-dark">{{ zone.name }}</span>
                    </div>
                    <span class="badge bg-dark rounded-pill px-2" style="font-size: 10px;">{{ zone.count }}</span>
                  </div>
                  <div v-if="zoneStats.length === 0" class="small text-muted">Chưa có dữ liệu</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { rescueRequestAPI, rescueTeamAPI, incidentTypeAPI } from "../../../services/api";
import MapboxMap from "../../common/MapboxMap.vue";

const BASE_URL = "http://localhost:8000";

const typeColors = [
  "#ef4444", "#f97316", "#eab308", "#22c55e",
  "#06b6d4", "#3b82f6", "#8b5cf6", "#ec4899",
];

const STATUS_META = {
  CHO_XU_LY: { label: "Chờ xử lý", badge: "bg-info text-dark" },
  DANG_XU_LY: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  HOAN_THANH: { label: "Hoàn thành", badge: "bg-success text-white" },
  HUY_BO: { label: "Đã huỷ", badge: "bg-danger text-white" },
  SAN_SANG: { label: "Sẵn sàng", badge: "bg-success" },
  DANG_BAN: { label: "Đang bận", badge: "bg-secondary" },
  DANG_XU_LY: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  CHO_XU_LY: { label: "Chờ xử lý", badge: "bg-info text-dark" },
};

function normalizeText(value, fallback = "") {
  if (value === null || value === undefined) return fallback;
  if (typeof value === "object") {
    return normalizeText(
      value.ten_danh_muc || value.ten_loai_su_co || value.ten_loai || value.title || value.name || fallback,
      fallback
    );
  }
  return String(value).trim();
}

function formatTime(value) {
  if (!value) return "—";
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return String(value);
  return parsed.toLocaleString("vi-VN", {
    hour: "2-digit", minute: "2-digit",
    day: "2-digit", month: "2-digit",
  });
}

function normalizeStatus(status) {
  if (!status) return "CHO_XU_LY";
  return String(status).trim().toUpperCase().replace(/\s+/g, "_");
}

function getSeverityBadge(sev) {
  if (!sev) return "bg-secondary text-white";
  const upper = String(sev).toUpperCase();
  if (upper === "CRITICAL" || upper === "KHẨN CẤP" || upper === "CAO" || sev == 4 || sev == 3) return "bg-danger text-white";
  if (upper === "HIGH" || upper === "NGHIÊM TRỌNG") return "bg-warning text-dark";
  if (upper === "MEDIUM" || upper === "TRUNG BÌNH" || sev == 2) return "bg-info text-dark";
  return "bg-secondary text-white";
}

function getSeverityLabel(sev) {
  if (!sev) return "Không rõ";
  const upper = String(sev).toUpperCase();
  if (upper === "CRITICAL" || upper === "KHẨN CẤP" || upper === "CAO" || sev == 4 || sev == 3) return "Khẩn cấp";
  if (upper === "HIGH" || upper === "NGHIÊM TRỌNG") return "Nghiêm trọng";
  if (upper === "MEDIUM" || upper === "TRUNG BÌNH" || sev == 2) return "Trung bình";
  if (upper === "LOW" || upper === "THẤP" || sev == 1) return "Thấp";
  return normalizeText(sev, "Không rõ");
}

export default {
  name: "AdminDashboard",
  components: { MapboxMap },
  data() {
    return {
      mapRef: null,
      mapRefreshKey: 0,
      allRequests: [],
      teams: [],
      incidentTypes: [],
      loading: false,
      timeRange: "today",
      timeRanges: [
        { value: "today", label: "Hôm nay" },
        { value: "week", label: "7 ngày" },
        { value: "month", label: "30 ngày" },
      ],
      typeColors,
      selectedZone: null,
    };
  },
  computed: {
    mapIncidents() {
      return this.allRequests
        .filter(r => r.lat != null && r.lng != null)
        .map(r => ({
          id: r.id,
          type: r.type,
          address: r.address,
          lat: r.lat,
          lng: r.lng,
          severityLabel: r.severityLabel,
          severityBadge: r.severityBadge,
        }));
    },
    zoneStats() {
      const zones = {};
      this.allRequests
        .filter(r => r.lat != null && r.lng != null)
        .forEach(r => {
          const zone = r.zone || "Khác";
          if (!zones[zone]) zones[zone] = { name: zone, count: 0, critical: 0 };
          zones[zone].count++;
          if (r.mucDoKhanCap == 4 || r.mucDoKhanCap == 3) zones[zone].critical++;
        });
      return Object.values(zones).sort((a, b) => b.count - a.count);
    },
    mapCenter() {
      const withCoords = this.allRequests.filter(r => r.lat != null && r.lng != null);
      if (withCoords.length === 0) return [108.2022, 16.0544];
      const avgLng = withCoords.reduce((s, r) => s + r.lng, 0) / withCoords.length;
      const avgLat = withCoords.reduce((s, r) => s + r.lat, 0) / withCoords.length;
      return [avgLng, avgLat];
    },
    filteredRequests() {
      let items = [...this.allRequests];
      if (this.timeRange === "today") {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        items = items.filter(r => {
          const t = new Date(r.rawTime);
          return t >= today;
        });
      } else if (this.timeRange === "week") {
        const cutoff = new Date();
        cutoff.setDate(cutoff.getDate() - 7);
        items = items.filter(r => new Date(r.rawTime) >= cutoff);
      } else if (this.timeRange === "month") {
        const cutoff = new Date();
        cutoff.setDate(cutoff.getDate() - 30);
        items = items.filter(r => new Date(r.rawTime) >= cutoff);
      }
      return items;
    },
    stats() {
      const pending = this.allRequests.filter(r => normalizeStatus(r.status) === "CHO_XU_LY" || normalizeStatus(r.status) === "MOI" || normalizeStatus(r.status) === "WAITING");
      const processing = this.allRequests.filter(r => normalizeStatus(r.status) === "DANG_XU_LY" || normalizeStatus(r.status) === "PROCESSING");
      const completed = this.allRequests.filter(r => normalizeStatus(r.status) === "HOAN_THANH" || normalizeStatus(r.status) === "DONE");
      const cancelled = this.allRequests.filter(r => normalizeStatus(r.status) === "HUY_BO");
      const criticalPending = pending.filter(r => {
        const upper = String(r.mucDoKhanCap || "").toUpperCase();
        return upper === "CRITICAL" || upper === "KHẨN CẤP" || upper === "CAO" || r.mucDoKhanCap == 4 || r.mucDoKhanCap == 3;
      }).length;

      const teamsReady = this.teams.filter(t => {
        const st = normalizeStatus(t.trangThai);
        return st === "SAN_SANG" || st === "SẴN_SÀNG" || t.trangThai === "Sẵn sàng";
      }).length;
      const teamsBusy = this.teams.filter(t => {
        const st = normalizeStatus(t.trangThai);
        return st === "DANG_BAN" || st === "ĐANG_BẬN" || st === "DANG_XU_LY" || st === "ĐANG_XỬ_LÝ" || t.trangThai === "Đang bận" || t.trangThai === "Đang xử lý";
      }).length;

      const total = this.allRequests.length || 1;
      return {
        total,
        pending: pending.length,
        processing: processing.length,
        completed: completed.length,
        cancelled: cancelled.length,
        criticalPending,
        teamsAvailable: teamsReady,
        teamsBusy,
        totalTeams: this.teams.length,
      };
    },
    completionRate() {
      const t = this.stats.total;
      if (!t) return 0;
      return Math.round((this.stats.completed / t) * 100);
    },
    teamAvailabilityRate() {
      const t = this.stats.totalTeams;
      if (!t) return 0;
      return Math.round((this.stats.teamsAvailable / t) * 100);
    },
    typeStats() {
      const map = {};
      this.filteredRequests.forEach(r => {
        if (!map[r.type]) map[r.type] = { name: r.type, count: 0 };
        map[r.type].count++;
      });
      return Object.values(map).sort((a, b) => b.count - a.count).slice(0, 8);
    },
    donutSegments() {
      const { pending, processing, completed, cancelled } = this.stats;
      const total = pending + processing + completed + cancelled || 1;
      const circumference = 2 * Math.PI * 40;
      let offset = 0;
      const segments = [];
      const parts = [
        { count: pending, label: "Chờ xử lý", color: "#0ea5e9" },
        { count: processing, label: "Đang xử lý", color: "#f59e0b" },
        { count: completed, label: "Hoàn thành", color: "#22c55e" },
        { count: cancelled, label: "Đã huỷ", color: "#94a3b8" },
      ];
      for (const p of parts) {
        const pct = p.count / total;
        segments.push({
          ...p,
          dashArray: `${pct * circumference} ${circumference}`,
          dashOffset: -offset,
        });
        offset += pct * circumference;
      }
      return segments;
    },
    pendingRequests() {
      return this.allRequests
        .filter(r => {
          const st = normalizeStatus(r.status);
          return st === "CHO_XU_LY" || st === "MOI" || st === "WAITING";
        })
        .sort((a, b) => {
          const aSev = a.mucDoKhanCap || 0;
          const bSev = b.mucDoKhanCap || 0;
          if (aSev !== bSev) return bSev - aSev;
          return new Date(b.rawTime) - new Date(a.rawTime);
        })
        .map(r => ({
          ...r,
          severityBadge: getSeverityBadge(r.mucDoKhanCap),
          severityLabel: getSeverityLabel(r.mucDoKhanCap),
        }));
    },
  },
  async created() {
    await this.loadAllData();
  },
  methods: {
    async loadAllData() {
      this.loading = true;
      try {
        const [reqRes, teamRes, typeRes] = await Promise.all([
          rescueRequestAPI.getList().catch(() => ({ data: [] })),
          rescueTeamAPI.getList().catch(() => ({ data: [] })),
          incidentTypeAPI.getList().catch(() => ({ data: [] })),
        ]);

        this.allRequests = this.parseRequests(reqRes?.data);
        this.teams = this.parseTeams(teamRes?.data);
        this.incidentTypes = typeRes?.data?.data || typeRes?.data || [];
        this.mapRefreshKey++; // Refresh map after data load
      } catch (err) {
        console.error("Dashboard load error:", err);
      } finally {
        this.loading = false;
      }
    },
    changeTimeRange(val) {
      this.timeRange = val;
    },
    getZoneColor(count) {
      if (count >= 10) return "#ef4444";
      if (count >= 5) return "#f97316";
      if (count >= 3) return "#eab308";
      return "#22c55e";
    },
    parseRequests(rawData) {
      const items = Array.isArray(rawData)
        ? rawData
        : Array.isArray(rawData?.data) ? rawData.data
        : Array.isArray(rawData?.data?.data) ? rawData.data.data
        : [];
      return items.map(item => {
        const id = item.id_yeu_cau || item.id || "-";
        const type = normalizeText(
          item.loai_su_co?.ten_danh_muc || item.loai_su_co?.ten_loai || item.loai || "Không rõ"
        );
        const address = normalizeText(item.vi_tri_dia_chi || item.dia_chi || "—");
        const sev = item.muc_do_khan_cap || item.muc_do;
        const rawTime = item.thoi_gian_gui || item.created_at || item.updated_at;

        // Extract coordinates
        let lat = null, lng = null;
        if (item.vi_tri_lat != null && item.vi_tri_lng != null) {
          lat = parseFloat(item.vi_tri_lat);
          lng = parseFloat(item.vi_tri_lng);
        } else if (item.toa_do) {
          const parts = String(item.toa_do).split(",").map(s => parseFloat(s.trim()));
          if (parts.length >= 2) { lat = parts[0]; lng = parts[1]; }
        }
        if (!lat && item.latitude) lat = parseFloat(item.latitude);
        if (!lng && item.longitude) lng = parseFloat(item.longitude);

        // Determine zone based on lat/lng
        let zone = "Khác";
        if (lat && lng) {
          if (lat > 16.08 && lng > 108.18 && lat < 16.12 && lng < 108.24) zone = "Hải Châu";
          else if (lat > 16.03 && lng > 108.12 && lat < 16.08 && lng < 108.18) zone = "Thanh Khê";
          else if (lat > 16.05 && lng > 108.07 && lat < 16.1 && lng < 108.15) zone = "Liên Chiểu";
          else if (lat > 16.0 && lng > 108.1 && lat < 16.07 && lng < 108.2) zone = "Ngũ Hành Sơn";
          else if (lat > 16.05 && lng > 107.95 && lat < 16.1 && lng < 108.07) zone = "Cẩm Lệ";
          else if (lat > 15.95 && lng > 108.1 && lat < 16.05 && lng < 108.2) zone = "Hòa Vang";
          else zone = "Khác";
        }

        return {
          id, type, address,
          mucDoKhanCap: sev,
          status: item.trang_thai || item.status,
          rawTime,
          time: formatTime(rawTime),
          lat, lng, zone,
        };
      });
    },
    parseTeams(rawData) {
      const items = Array.isArray(rawData)
        ? rawData
        : Array.isArray(rawData?.data) ? rawData.data
        : Array.isArray(rawData?.data?.data) ? rawData.data.data
        : [];
      return items.map(item => {
        const name = normalizeText(item.ten_co || item.name || "Đội không tên");
        const area = normalizeText(item.khu_vuc_quan_ly || item.area || "");
        const st = item.trang_thai || "SanSang";
        const stNorm = normalizeStatus(st);
        let statusBadge = "bg-secondary";
        let statusLabel = "Không rõ";
        let statusColor = "bg-secondary-subtle text-secondary";
        if (stNorm === "SAN_SANG" || stNorm === "SẴN_SÀNG" || st === "Sẵn sàng") {
          statusBadge = "bg-success-subtle text-success";
          statusLabel = "Sẵn sàng";
          statusColor = "bg-success-subtle text-success";
        } else if (stNorm === "DANG_BAN" || st === "Đang bận") {
          statusBadge = "bg-secondary-subtle text-secondary";
          statusLabel = "Đang bận";
          statusColor = "bg-secondary-subtle text-secondary";
        } else if (stNorm === "DANG_XU_LY" || stNorm === "ĐANG_XỬ_LÝ" || st === "Đang xử lý") {
          statusBadge = "bg-warning-subtle text-warning";
          statusLabel = "Đang xử lý";
          statusColor = "bg-warning-subtle text-warning";
        }
        return {
          id: item.id_doi_cuu_ho || item.id,
          name, area, trangThai: st,
          statusBadge, statusLabel, statusColor,
          initial: name.charAt(0).toUpperCase(),
        };
      });
    },
  },
};
</script>

<style scoped>
.summary-card {
  border-radius: 1rem;
  transition: transform 0.2s;
}
.summary-card:hover {
  transform: translateY(-2px);
}
.summary-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.9rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}
.fs-09 { font-size: 0.75rem; }

.progress { height: 8px; background: #f1f5f9; }
.progress-bar { transition: width 0.6s ease; }

.donut-wrapper {
  width: 160px;
  height: 160px;
}
.donut-center {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.legend-dot {
  width: 10px;
  height: 10px;
  display: inline-block;
  flex-shrink: 0;
}

.type-dot {
  width: 12px;
  height: 12px;
  flex-shrink: 0;
}

.hover-row:hover { background-color: #f8fafc; cursor: default; }
.team-item { background: #f8fafc; transition: background 0.15s; }
.team-item:hover { background: #f1f5f9; }
.team-avatar {
  width: 36px;
  height: 36px;
  font-weight: 700;
  font-size: 0.85rem;
  flex-shrink: 0;
}

.spin {
  animation: spin 1s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

.spinner-lg {
  width: 44px;
  height: 44px;
  border: 4px solid #e0e7ff;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.map-wrapper {
  position: relative;
}
.map-stats-overlay {
  pointer-events: none;
}
.map-legend-overlay {
  min-width: 150px;
}
</style>
