<template>
  <div class="container-fluid px-0">
    <div v-if="error" class="alert alert-warning d-flex justify-content-between align-items-center mb-3">
      <span>{{ error }}</span>
      <button class="btn btn-sm btn-outline-secondary" @click="loadDashboard" :disabled="loading">
        Thu lai
      </button>
    </div>

    <div class="row g-3 mb-3">
      <div v-for="card in summaryCards" :key="card.title" class="col-12 col-lg-3">
        <div class="card summary-card h-100 border-0 shadow-sm">
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <div class="text-muted text-uppercase small fw-semibold">{{ card.title }}</div>
                <div class="h4 mb-0 fw-bold">{{ card.value }}</div>
              </div>
              <div class="summary-icon" :class="card.iconBg">
                <i :class="card.icon"></i>
              </div>
            </div>
            <div class="d-flex align-items-center small">
              <span :class="card.trendClass">
                <i :class="card.trendIcon" class="me-1"></i>{{ card.trend }}
              </span>
              <span class="text-muted ms-2">{{ card.caption }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-0 fw-semibold">Hang doi yeu cau</h6>
                <small class="text-muted">Top yeu cau uu tien tu backend</small>
              </div>
              <button class="btn btn-sm btn-outline-secondary" @click="loadDashboard" :disabled="loading">
                <i class="bi bi-arrow-clockwise me-1"></i>
                Lam moi
              </button>
            </div>
          </div>
          <div class="card-body">
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status"></div>
              <div class="small text-muted mt-2">Dang tai dashboard...</div>
            </div>

            <div v-else class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr class="small text-muted">
                    <th>Ma yeu cau</th>
                    <th>Loai su co</th>
                    <th>Khu vuc</th>
                    <th>Muc khan cap</th>
                    <th>Cho xu ly</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="queuePreview.length === 0">
                    <td colspan="5" class="text-center text-muted py-4">Chua co du lieu hang doi</td>
                  </tr>
                  <tr v-for="item in queuePreview" :key="item.code">
                    <td class="fw-semibold">{{ item.code }}</td>
                    <td>{{ item.type }}</td>
                    <td>{{ item.area }}</td>
                    <td>
                      <span class="badge rounded-pill" :class="item.badgeClass">
                        {{ item.priority }}
                      </span>
                    </td>
                    <td class="text-muted small">{{ item.wait }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pb-0">
            <h6 class="mb-0 fw-semibold">Diem nong heatmap</h6>
            <small class="text-muted">Phan bo theo muc intensity</small>
          </div>
          <div class="card-body">
            <div class="heatmap-map rounded-4 mb-3 overflow-hidden border position-relative">
              <div v-if="loading" class="heatmap-loading d-flex align-items-center justify-content-center">
                <div class="text-center">
                  <div class="spinner-border text-primary" role="status"></div>
                  <div class="small text-muted mt-2">Dang tai heatmap...</div>
                </div>
              </div>
              <MapboxMap
                :center="mapCenter"
                :zoom="11"
                :show-marker="false"
                :points="mapPoints"
                :use-heatmap="true"
                :fit-to-points="true"
              />
            </div>
            <ul class="list-unstyled small mb-0">
              <li v-if="dangerZones.length === 0" class="text-muted">Chua co du lieu heatmap</li>
              <li v-for="zone in dangerZones" :key="zone.name" class="d-flex justify-content-between mb-1">
                <span>
                  <span class="legend-dot me-2" :style="{ background: zone.color }"></span>{{ zone.name }}
                </span>
                <span class="text-muted">{{ zone.count }} diem</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pb-0">
            <h6 class="mb-0 fw-semibold">Trang thai doi cuu ho</h6>
            <small class="text-muted">Du lieu doi co san va hieu suat</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr class="small text-muted">
                    <th>Doi</th>
                    <th>Trang thai</th>
                    <th>Khu vuc</th>
                    <th>Nhiem vu hien tai</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="teams.length === 0">
                    <td colspan="4" class="text-center text-muted py-4">Chua co du lieu doi cuu ho</td>
                  </tr>
                  <tr v-for="team in teams" :key="team.name">
                    <td class="fw-semibold">{{ team.name }}</td>
                    <td>
                      <span class="badge rounded-pill" :class="team.badgeClass">
                        {{ team.status }}
                      </span>
                    </td>
                    <td class="text-muted small">{{ team.location }}</td>
                    <td class="small">{{ team.mission }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-0 fw-semibold">Tong quan he thong</h6>
              <small class="text-muted">So lieu tong hop tu backend</small>
            </div>
            <router-link to="/admin/ai-scoring" class="btn btn-sm btn-outline-primary">
              Chi tiet
            </router-link>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mb-0 small">
              <li class="d-flex justify-content-between align-items-center mb-2">
                <span>Tong so yeu cau</span>
                <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill">
                  {{ dashboardMeta.totalRequests }}
                </span>
              </li>
              <li class="d-flex justify-content-between align-items-center mb-2">
                <span>Loai su co dang co du lieu</span>
                <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                  {{ dashboardMeta.incidentTypeCount }} loai
                </span>
              </li>
              <li class="d-flex justify-content-between align-items-center mb-2">
                <span>Doi cuu ho kha dung</span>
                <span class="badge bg-success-subtle text-success-emphasis rounded-pill">
                  {{ dashboardMeta.availableTeams }} doi
                </span>
              </li>
              <li class="d-flex justify-content-between align-items-center">
                <span>Diem heatmap dang theo doi</span>
                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">
                  {{ dashboardMeta.heatmapPoints }} diem
                </span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";
import { analyticsAPI } from "../../../services/api";

const DEFAULT_CENTER = [108.2022, 16.0544];

const FALLBACK_SUMMARY = [
  {
    title: "Tong yeu cau",
    value: "0",
    trend: "0",
    caption: "du lieu tu backend",
    icon: "fa-solid fa-signal",
    trendClass: "text-success",
    trendIcon: "fa-solid fa-chart-line",
    iconBg: "bg-primary-subtle text-primary-emphasis",
  },
  {
    title: "Dang xu ly",
    value: "0",
    trend: "0",
    caption: "trang thai hien tai",
    icon: "fa-solid fa-helmet-safety",
    trendClass: "text-info",
    trendIcon: "fa-solid fa-spinner",
    iconBg: "bg-info-subtle text-info-emphasis",
  },
  {
    title: "Hoan thanh",
    value: "0",
    trend: "0",
    caption: "da xu ly xong",
    icon: "fa-solid fa-circle-check",
    trendClass: "text-success",
    trendIcon: "fa-solid fa-check",
    iconBg: "bg-success-subtle text-success-emphasis",
  },
  {
    title: "Uu tien cao",
    value: "0",
    trend: "0",
    caption: "can theo doi",
    icon: "fa-solid fa-triangle-exclamation",
    trendClass: "text-danger",
    trendIcon: "fa-solid fa-bolt",
    iconBg: "bg-danger-subtle text-danger-emphasis",
  },
];

function unwrapResponse(response) {
  const payload = response?.data ?? response;
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload?.data)) return payload.data;
  if (Array.isArray(payload?.data?.data)) return payload.data.data;
  return payload?.data ?? payload ?? null;
}

function normalizeText(value, fallback = "Khong ro") {
  if (value === null || value === undefined || value === "") return fallback;
  if (typeof value === "object") {
    return normalizeText(
      value.ten ||
        value.ten_co ||
        value.ten_doi ||
        value.name ||
        value.ten_danh_muc ||
        value.loai_su_co ||
        value.khu_vuc ||
        value.vi_tri ||
        value.label,
      fallback
    );
  }
  return String(value).trim() || fallback;
}

function toNumber(value, fallback = 0) {
  const num = Number(value);
  return Number.isFinite(num) ? num : fallback;
}

function toNullableNumber(value) {
  const num = Number(value);
  return Number.isFinite(num) ? num : null;
}

function formatCount(value) {
  return new Intl.NumberFormat("vi-VN").format(toNumber(value, 0));
}

function getValueByKeys(source, keys, fallback = null) {
  if (!source || typeof source !== "object") return fallback;
  for (const key of keys) {
    if (source[key] !== undefined && source[key] !== null && source[key] !== "") {
      return source[key];
    }
  }
  return fallback;
}

function normalizeArray(payload) {
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload?.items)) return payload.items;
  if (Array.isArray(payload?.data)) return payload.data;
  if (Array.isArray(payload?.by_status)) return payload.by_status;
  if (Array.isArray(payload?.rows)) return payload.rows;
  return [];
}

function normalizeObject(payload) {
  if (!payload || typeof payload !== "object" || Array.isArray(payload)) return {};
  if (payload.data && typeof payload.data === "object" && !Array.isArray(payload.data)) {
    return payload.data;
  }
  return payload;
}

function formatWaitTime(value) {
  if (!value) return "Chua ro";
  if (typeof value === "number") return `${value} phut`;
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return normalizeText(value, "Chua ro");
  const diffMs = Date.now() - parsed.getTime();
  const diffMinutes = Math.max(0, Math.round(diffMs / 60000));
  return `${diffMinutes} phut`;
}

function priorityBadge(priority) {
  const text = normalizeText(priority, "Khong ro").toUpperCase();
  if (["CRITICAL", "RAT CAO"].includes(text)) return "bg-danger-subtle text-danger-emphasis";
  if (["HIGH", "CAO"].includes(text)) return "bg-warning-subtle text-warning-emphasis";
  if (["MEDIUM", "TRUNG BINH"].includes(text)) return "bg-info-subtle text-info-emphasis";
  return "bg-secondary-subtle text-secondary-emphasis";
}

function teamBadge(status) {
  const text = normalizeText(status, "Khong ro").toUpperCase();
  if (text.includes("SAN") || text.includes("AVAILABLE")) return "bg-success-subtle text-success-emphasis";
  if (text.includes("DANG") || text.includes("PROCESS")) return "bg-primary-subtle text-primary-emphasis";
  if (text.includes("MOVE")) return "bg-info-subtle text-info-emphasis";
  return "bg-secondary-subtle text-secondary-emphasis";
}

function zoneColor(index) {
  const colors = ["#ef4444", "#f97316", "#eab308", "#22c55e", "#0ea5e9", "#8b5cf6"];
  return colors[index % colors.length];
}

function getHeatmapPointColor(weight) {
  if (weight >= 15) return "#dc1e3c";
  if (weight >= 11) return "#ef4444";
  if (weight >= 8) return "#f97316";
  if (weight >= 5) return "#eab308";
  if (weight >= 3) return "#22c55e";
  return "#0ea5e9";
}

function buildSummaryCards(totalPayload, statusPayload, priorityPayload) {
  const totalSource = normalizeObject(unwrapResponse(totalPayload));
  const statusSource = normalizeObject(unwrapResponse(statusPayload));
  const priorityItems = normalizeArray(unwrapResponse(priorityPayload));

  const totalRequests = toNumber(
    getValueByKeys(totalSource, ["tong_so_yeu_cau", "total_requests", "total", "count"]),
    0
  );
  const processingCount = toNumber(
    getValueByKeys(statusSource, ["processing", "dang_xu_ly", "in_progress"]),
    0
  );
  const doneCount = toNumber(
    getValueByKeys(statusSource, ["completed", "hoan_thanh", "done"]),
    0
  );
  const highPriorityCount = priorityItems.reduce((sum, item) => {
    const level = normalizeText(getValueByKeys(item, ["muc_do_khan_cap", "priority", "name"]), "").toUpperCase();
    if (level.includes("HIGH") || level.includes("CRITICAL") || level.includes("CAO")) {
      return sum + toNumber(getValueByKeys(item, ["so_luong", "count", "tong_so"]), 0);
    }
    return sum;
  }, 0);

  return [
    {
      ...FALLBACK_SUMMARY[0],
      value: formatCount(totalRequests),
      trend: formatCount(totalRequests),
      caption: "tong so yeu cau",
    },
    {
      ...FALLBACK_SUMMARY[1],
      value: formatCount(processingCount),
      trend: formatCount(processingCount),
      caption: "yeu cau dang xu ly",
    },
    {
      ...FALLBACK_SUMMARY[2],
      value: formatCount(doneCount),
      trend: formatCount(doneCount),
      caption: "yeu cau hoan thanh",
    },
    {
      ...FALLBACK_SUMMARY[3],
      value: formatCount(highPriorityCount),
      trend: formatCount(highPriorityCount),
      caption: "muc uu tien cao",
    },
  ];
}

function buildQueuePreview(queuePayload) {
  const items = normalizeArray(unwrapResponse(queuePayload));

  return items.slice(0, 5).map((item, index) => ({
    code: normalizeText(getValueByKeys(item, ["ma_yeu_cau", "request_code", "id_yeu_cau", "id"]), `REQ-${index + 1}`),
    type: normalizeText(
      getValueByKeys(item, ["loai_su_co", "ten_loai_su_co", "ten_danh_muc", "chi_tiet"]) ||
        item.yeu_cau?.loaiSuCo?.ten_danh_muc ||
        item.yeu_cau?.loai_su_co?.ten_danh_muc,
      "Khong ro"
    ),
    area: normalizeText(
      getValueByKeys(item, ["khu_vuc", "vi_tri_dia_chi", "dia_chi", "area"]) || item.yeu_cau?.vi_tri_dia_chi,
      "Chua ro"
    ),
    priority: normalizeText(
      getValueByKeys(item, ["muc_khan_cap", "muc_do_khan_cap", "priority", "name"]),
      "Khong ro"
    ),
    wait: formatWaitTime(
      getValueByKeys(item, ["thoi_gian_vao", "thoi_gian_gui", "created_at", "thoi_gian", "wait_time"]) ||
        item.yeu_cau?.thoi_gian_gui ||
        item.yeu_cau?.created_at
    ),
    badgeClass: priorityBadge(getValueByKeys(item, ["muc_khan_cap", "muc_do_khan_cap", "priority", "name"])),
  }));
}

function buildDangerZones(heatmapPayload) {
  const items = normalizeArray(unwrapResponse(heatmapPayload));
  const buckets = [
    { name: "Nguy hiem rat cao", count: 0 },
    { name: "Nguy hiem cao", count: 0 },
    { name: "Nguy hiem trung binh", count: 0 },
    { name: "Nguy hiem thap", count: 0 },
  ];

  items.forEach((item) => {
    const intensity = toNumber(getValueByKeys(item, ["intensity", "muc_do", "value"]), 0);
    if (intensity >= 4) buckets[0].count += 1;
    else if (intensity >= 3) buckets[1].count += 1;
    else if (intensity >= 2) buckets[2].count += 1;
    else buckets[3].count += 1;
  });

  return buckets
    .filter((bucket) => bucket.count > 0)
    .map((bucket, index) => ({
      ...bucket,
      color: zoneColor(index),
    }));
}

function buildHeatmapPoints(heatmapPayload) {
  const items = normalizeArray(unwrapResponse(heatmapPayload));

  return items
    .map((item, index) => {
      const lat = toNullableNumber(getValueByKeys(item, ["vi_do", "lat", "latitude", "vi_tri_lat"]));
      const lng = toNullableNumber(getValueByKeys(item, ["kinh_do", "lng", "longitude", "vi_tri_lng"]));
      const count = Number(getValueByKeys(item, ["so_luong", "count", "tong_so", "weight", "intensity"], 1)) || 1;

      if (lat === null || lng === null) return null;

      return {
        id: getValueByKeys(item, ["id", "id_yeu_cau", "ma"], `heat-${index + 1}`),
        lat,
        lng,
        count,
        weight: count,
        district: normalizeText(
          getValueByKeys(item, ["quan", "district", "khu_vuc", "ten_khu_vuc", "phuong", "status"]),
          "Khong ro"
        ),
        incidentType: normalizeText(
          getValueByKeys(item, ["loai_su_co", "incident_type", "type", "loai", "category", "status"]),
          "Khong ro"
        ),
        title: normalizeText(
          getValueByKeys(item, ["khu_vuc", "ten_khu_vuc", "vi_tri_dia_chi", "name", "title", "status"]),
          `Diem nong ${index + 1}`
        ),
        description: normalizeText(
          getValueByKeys(item, ["mo_ta", "description", "ghi_chu", "chi_tiet", "status"]),
          "Khong co mo ta"
        ),
        color: getHeatmapPointColor(count),
      };
    })
    .filter(Boolean);
}

function buildTeams(availableTeamsPayload, performancePayload) {
  const availableItems = normalizeArray(unwrapResponse(availableTeamsPayload));
  const performanceItems = normalizeArray(unwrapResponse(performancePayload));

  const performanceMap = new Map();
  performanceItems.forEach((item) => {
    const key = String(getValueByKeys(item, ["id_doi_cuu_ho", "id"], ""));
    if (key) performanceMap.set(key, item);
  });

  return availableItems.slice(0, 6).map((item, index) => {
    const perf = performanceMap.get(String(getValueByKeys(item, ["id_doi_cuu_ho", "id"], ""))) || {};
    const status = normalizeText(getValueByKeys(item, ["trang_thai", "status", "trang_thai_hoat_dong"]), "Khong ro");

    return {
      name: normalizeText(getValueByKeys(item, ["ten_co", "ten_doi", "name"]), `Doi ${index + 1}`),
      status,
      location: normalizeText(getValueByKeys(item, ["khu_vuc_quan_ly", "khu_vuc", "vi_tri", "dia_chi"]), "Chua ro"),
      mission: `${formatCount(getValueByKeys(perf, ["so_nhiem_vu_dang_xy_ly", "so_nhiem_vu_dang_xu_ly"], 0))} nhiem vu dang xu ly`,
      badgeClass: teamBadge(status),
    };
  });
}

export default {
  name: "AdminDashboard",
  components: { MapboxMap },
  data() {
    return {
      loading: false,
      error: "",
      summaryCards: [...FALLBACK_SUMMARY],
      queuePreview: [],
      dangerZones: [],
      teams: [],
      mapCenter: DEFAULT_CENTER,
      mapPoints: [],
      dashboardMeta: {
        totalRequests: 0,
        incidentTypeCount: 0,
        availableTeams: 0,
        heatmapPoints: 0,
      },
    };
  },
  async created() {
    await this.loadDashboard();
  },
  methods: {
    async loadDashboard() {
      this.loading = true;
      this.error = "";

      try {
        const [
          totalRequestsRes,
          requestsByTypeRes,
          requestsByPriorityRes,
          processingStatusRes,
          teamPerformanceRes,
          availableTeamsRes,
          heatmapDataRes,
          queueRes,
        ] = await Promise.all([
          analyticsAPI.getTotalRequests(),
          analyticsAPI.getRequestsByType(),
          analyticsAPI.getRequestsByPriority(),
          analyticsAPI.getProcessingStatus(),
          analyticsAPI.getTeamPerformance(),
          analyticsAPI.getAvailableTeams(),
          analyticsAPI.getHeatmapData(),
          analyticsAPI.getQueue(),
        ]);

        this.summaryCards = buildSummaryCards(totalRequestsRes, processingStatusRes, requestsByPriorityRes);
        this.queuePreview = buildQueuePreview(queueRes);
        this.mapPoints = buildHeatmapPoints(heatmapDataRes);
        this.mapCenter = this.mapPoints.length > 0 ? [this.mapPoints[0].lng, this.mapPoints[0].lat] : DEFAULT_CENTER;
        this.dangerZones = buildDangerZones(heatmapDataRes);
        this.teams = buildTeams(availableTeamsRes, teamPerformanceRes);

        const typeItems = normalizeArray(unwrapResponse(requestsByTypeRes));
        const teamItems = normalizeArray(unwrapResponse(availableTeamsRes));
        const heatmapItems = normalizeArray(unwrapResponse(heatmapDataRes));
        const totalSource = normalizeObject(unwrapResponse(totalRequestsRes));

        this.dashboardMeta = {
          totalRequests: formatCount(
            getValueByKeys(totalSource, ["tong_so_yeu_cau", "total_requests", "total", "count"], 0)
          ),
          incidentTypeCount: formatCount(typeItems.length),
          availableTeams: formatCount(teamItems.length),
          heatmapPoints: formatCount(heatmapItems.length),
        };
      } catch (error) {
        console.error("Khong tai duoc du lieu dashboard:", error);
        this.error = "Khong tai duoc du lieu dashboard tu backend.";
        this.summaryCards = [...FALLBACK_SUMMARY];
        this.queuePreview = [];
        this.dangerZones = [];
        this.teams = [];
        this.mapCenter = DEFAULT_CENTER;
        this.mapPoints = [];
        this.dashboardMeta = {
          totalRequests: 0,
          incidentTypeCount: 0,
          availableTeams: 0,
          heatmapPoints: 0,
        };
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.summary-card {
  border-radius: 1rem;
}

.summary-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.9rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.heatmap-map {
  height: 250px;
}

.heatmap-loading {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.65);
  z-index: 2;
  backdrop-filter: blur(2px);
}

.legend-dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 999px;
}
</style>
