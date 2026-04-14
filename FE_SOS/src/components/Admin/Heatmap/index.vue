<template>
  <div class="heatmap-page">
    <!-- Header -->
    <div class="page-header bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-3">
        <div class="icon-box">
          <i class="bi bi-geo-alt-fill text-white fs-5"></i>
        </div>
        <div>
          <h4 class="mb-0 fw-bolder">Bản Đồ Nhiệt Sự Cố</h4>
          <p class="text-muted mb-0 small">Phân tích mật độ & phân bố yêu cầu cứu hộ theo khu vực</p>
        </div>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2" @click="refreshData" :disabled="loading">
          <i class="bi bi-arrow-clockwise" :class="{ 'spin-animation': loading }"></i>
          <span class="d-none d-sm-inline">Làm mới</span>
        </button>
        <div class="dropdown">
          <button class="btn btn-outline-dark dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
            <i class="bi bi-sliders"></i>
            <span class="d-none d-sm-inline">Bộ lọc</span>
            <span v-if="activeFilterCount > 0" class="badge bg-danger rounded-pill">{{ activeFilterCount }}</span>
          </button>
          <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
            <div class="mb-3">
              <label class="form-label fw-bold small text-uppercase text-secondary">Loại sự cố</label>
              <select v-model="filterType" class="form-select form-select-sm">
                <option value="">Tất cả loại sự cố</option>
                <option v-for="t in incidentTypes" :key="t.id" :value="t.id">{{ t.ten_danh_muc || t.ten_loai }}</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold small text-uppercase text-secondary">Mức độ ưu tiên</label>
              <select v-model="filterPriority" class="form-select form-select-sm">
                <option value="">Tất cả mức độ</option>
                <option value="Cao">Cao</option>
                <option value="Trung bình">Trung bình</option>
                <option value="Thấp">Thấp</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold small text-uppercase text-secondary">Khoảng thời gian</label>
              <select v-model="filterTimeRange" class="form-select form-select-sm">
                <option value="">Tất cả thời gian</option>
                <option value="today">Hôm nay</option>
                <option value="week">7 ngày qua</option>
                <option value="month">30 ngày qua</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold small text-uppercase text-secondary">Trạng thái</label>
              <select v-model="filterStatus" class="form-select form-select-sm">
                <option value="">Tất cả trạng thái</option>
                <option value="CHO_XU_LY">Chờ xử lý</option>
                <option value="DANG_XU_LY">Đang xử lý</option>
                <option value="HOAN_THANH">Hoàn thành</option>
                <option value="HUY_BO">Đã huỷ</option>
              </select>
            </div>
            <button class="btn btn-sm btn-outline-danger w-100" @click="resetFilters">Xóa bộ lọc</button>
          </div>
        </div>
        <button class="btn btn-dark d-flex align-items-center gap-2" @click="toggleLayerPanel">
          <i class="bi bi-layers-half"></i>
          <span class="d-none d-sm-inline">Lớp bản đồ</span>
        </button>
      </div>
    </div>

    <div class="heatmap-layout d-flex gap-4">
      <!-- Sidebar Stats -->
      <div class="heatmap-sidebar" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
        <div class="sidebar-toggle" @click="sidebarCollapsed = !sidebarCollapsed">
          <i :class="sidebarCollapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'"></i>
        </div>

        <!-- Stats Cards -->
        <div v-if="!sidebarCollapsed" class="stats-section">
          <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-bar-chart-fill text-primary"></i> Tổng quan
          </h6>
          <div class="stat-card stat-total mb-2">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.total }}</div>
              <div class="stat-label">Tổng sự cố</div>
            </div>
          </div>
          <div class="stat-card stat-critical mb-2">
            <div class="stat-icon"><i class="bi bi-shield-exclamation"></i></div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.critical }}</div>
              <div class="stat-label">Mức độ cao</div>
            </div>
          </div>
          <div class="stat-card stat-processing mb-2">
            <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.processing }}</div>
              <div class="stat-label">Đang xử lý</div>
            </div>
          </div>
          <div class="stat-card stat-pending mb-2">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.pending }}</div>
              <div class="stat-label">Chờ xử lý</div>
            </div>
          </div>
        </div>

        <!-- Incident Type Breakdown -->
        <div v-if="!sidebarCollapsed" class="type-breakdown-section">
          <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-pie-chart-fill text-warning"></i> Theo loại sự cố
          </h6>
          <div class="type-breakdown">
            <div v-for="(item, idx) in typeBreakdown" :key="idx" class="type-item" @click="filterByType(item.typeId)">
              <div class="type-dot" :style="{ background: typeColors[idx % typeColors.length] }"></div>
              <div class="type-info">
                <span class="type-name">{{ item.name }}</span>
                <span class="type-count">{{ item.count }}</span>
              </div>
              <div class="type-bar">
                <div class="type-bar-fill" :style="{ width: (item.count / Math.max(stats.total, 1) * 100) + '%', background: typeColors[idx % typeColors.length] }"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Legend -->
        <div v-if="!sidebarCollapsed" class="legend-section">
          <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill text-info"></i> Chú thích
          </h6>
          <div class="legend-items">
            <div class="legend-item">
              <span class="legend-dot" style="background: rgba(255, 0, 0, 0.8);"></span>
              <span>Mật độ cao</span>
            </div>
            <div class="legend-item">
              <span class="legend-dot" style="background: rgba(255, 165, 0, 0.7);"></span>
              <span>Mật độ trung bình</span>
            </div>
            <div class="legend-item">
              <span class="legend-dot" style="background: rgba(255, 255, 0, 0.5);"></span>
              <span>Mật độ thấp</span>
            </div>
            <div class="legend-item">
              <span class="legend-marker cluster"></span>
              <span>Cụm sự cố</span>
            </div>
            <div class="legend-item">
              <span class="legend-marker marker-pending"></span>
              <span>Chờ xử lý</span>
            </div>
            <div class="legend-item">
              <span class="legend-marker marker-processing"></span>
              <span>Đang xử lý</span>
            </div>
            <div class="legend-item">
              <span class="legend-marker marker-completed"></span>
              <span>Hoàn thành</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Map Container -->
      <div class="heatmap-map-container flex-grow-1">
        <div class="map-card card border-0 shadow-sm rounded-4 overflow-hidden">
          <!-- Map Controls Overlay -->
          <div class="map-controls-overlay">
            <div class="map-search-box">
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input v-model="searchQuery" type="text" class="form-control border-start-0" placeholder="Tìm kiếm địa chỉ, tên sự cố..." @input="onSearch">
              </div>
            </div>
            <div class="map-zoom-controls btn-group-vertical shadow-sm">
              <button class="btn btn-white" @click="zoomIn" title="Phóng to"><i class="bi bi-plus-lg"></i></button>
              <button class="btn btn-white" @click="zoomOut" title="Thu nhỏ"><i class="bi bi-dash-lg"></i></button>
              <button class="btn btn-white" @click="resetView" title="Vị trí mặc định"><i class="bi bi-crosshair"></i></button>
              <button class="btn btn-white" @click="toggleFullscreen" title="Toàn màn hình"><i :class="isFullscreen ? 'bi bi-fullscreen-exit' : 'bi bi-fullscreen'"></i></button>
            </div>
          </div>

          <!-- Layer Panel -->
          <transition name="slide-fade">
            <div v-if="showLayerPanel" class="layer-panel bg-white shadow rounded-3 p-3">
              <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-layers-half me-2"></i>Lớp bản đồ</h6>
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="layerHeatmap" v-model="layers.heatmap" @change="updateLayers">
                <label class="form-check-label" for="layerHeatmap">Lớp nhiệt</label>
              </div>
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="layerCluster" v-model="layers.cluster" @change="updateLayers">
                <label class="form-check-label" for="layerCluster">Cụm marker</label>
              </div>
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="layerMarker" v-model="layers.marker" @change="updateLayers">
                <label class="form-check-label" for="layerMarker">Marker đơn</label>
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="layerSatellite" v-model="layers.satellite" @change="toggleSatellite">
                <label class="form-check-label" for="layerSatellite">Vệ tinh</label>
              </div>
              <div class="mb-2">
                <label class="form-label small fw-bold text-secondary">Độ mạnh nhiệt</label>
                <input type="range" class="form-range" min="0.1" max="2" step="0.1" v-model="heatmapIntensity" @input="updateHeatmapIntensity">
              </div>
            </div>
          </transition>

          <!-- Map -->
          <div ref="mapContainerEl" class="maplibre-map" :class="{ 'fullscreen-map': isFullscreen }"></div>

          <!-- Loading Overlay -->
          <div v-if="loading" class="map-loading-overlay">
            <div class="spinner-lg"></div>
            <div class="text-white fw-bold mt-3">Đang tải dữ liệu...</div>
          </div>

          <!-- Hotspot Panel -->
          <transition name="fade-slide">
            <div v-if="selectedHotspot && !sidebarCollapsed" class="hotspot-detail-panel bg-white shadow rounded-3">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt-fill text-danger me-2"></i>{{ selectedHotspot.name }}</h6>
                <button class="btn-close btn-sm" @click="selectedHotspot = null"></button>
              </div>
              <div class="d-flex gap-2 flex-wrap mb-2">
                <span class="badge bg-danger">{{ selectedHotspot.count }} sự cố</span>
                <span class="badge bg-warning text-dark">Ưu tiên cao: {{ selectedHotspot.critical }}</span>
              </div>
              <div class="small text-muted mb-2">Loại phổ biến: <strong class="text-dark">{{ selectedHotspot.topType }}</strong></div>
              <button class="btn btn-sm btn-primary w-100" @click="viewHotspotIncidents(selectedHotspot)">
                <i class="bi bi-list-ul me-1"></i> Xem danh sách sự cố
              </button>
            </div>
          </transition>
        </div>
      </div>
    </div>

    <!-- Incident Detail Modal -->
    <div class="modal fade" id="incidentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content" v-if="selectedIncident">
          <div class="modal-header border-0 pb-0">
            <div>
              <h5 class="modal-title fw-bold">
                <i class="bi bi-geo-alt-fill text-danger me-2"></i>SOS-{{ selectedIncident.id }}
              </h5>
              <span class="badge mt-1" :class="selectedIncident.statusClass">{{ selectedIncident.statusLabel }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-4">
              <div class="col-md-8">
                <div class="info-group mb-3">
                  <label class="text-muted small fw-bold text-uppercase">Loại sự cố</label>
                  <div class="fw-semibold text-dark">{{ selectedIncident.type }}</div>
                </div>
                <div class="info-group mb-3">
                  <label class="text-muted small fw-bold text-uppercase">Mức độ ưu tiên</label>
                  <div class="fw-semibold text-danger">{{ selectedIncident.priority }}</div>
                </div>
                <div class="info-group mb-3">
                  <label class="text-muted small fw-bold text-uppercase">Mô tả</label>
                  <div class="text-dark">{{ selectedIncident.description }}</div>
                </div>
                <div class="info-group mb-3">
                  <label class="text-muted small fw-bold text-uppercase">Vị trí</label>
                  <div class="text-dark">{{ selectedIncident.address }}</div>
                  <div class="small text-muted">Tọa độ: {{ selectedIncident.lat }}, {{ selectedIncident.lng }}</div>
                </div>
              </div>
              <div class="col-md-4 text-center">
                <div class="p-3 bg-light rounded-4 mb-3">
                  <div class="text-muted small mb-1">Thời gian gửi</div>
                  <div class="fw-bold text-dark">{{ selectedIncident.time }}</div>
                </div>
                <div v-if="selectedIncident.imageUrl" class="incident-image-thumb rounded-4 overflow-hidden">
                  <img :src="selectedIncident.imageUrl" class="w-100" alt="Hình ảnh sự cố">
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button class="btn btn-outline-danger" data-bs-dismiss="modal">Đóng</button>
            <button class="btn btn-primary" @click="navigateToAssignment(selectedIncident)" data-bs-dismiss="modal">
              <i class="bi bi-arrow-right-circle me-1"></i> Điều phối ngay
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, onUnmounted, ref, computed, watch, nextTick } from "vue";
import maplibregl from "@openmapvn/openmapvn-gl";
import "@openmapvn/openmapvn-gl/dist/maplibre-gl.css";
import { rescueRequestAPI, incidentTypeAPI } from "../../../services/api";

const BASE_URL = "http://localhost:8000";
const DEFAULT_CENTER = [108.2022, 16.0544]; // Da Nang
const DEFAULT_ZOOM = 12;

const STATUS_META = {
  CHO_XU_LY: { label: "Chờ xử lý", badge: "bg-info text-dark" },
  DANG_XU_LY: { label: "Đang xử lý", badge: "bg-warning text-dark" },
  HOAN_THANH: { label: "Hoàn thành", badge: "bg-success text-white" },
  HUY_BO: { label: "Đã huỷ", badge: "bg-danger text-white" },
};

const typeColors = [
  "#ef4444", "#f97316", "#eab308", "#22c55e",
  "#06b6d4", "#3b82f6", "#8b5cf6", "#ec4899",
  "#14b8a6", "#f43f5e",
];

function normalizeStatus(status) {
  if (!status) return "CHO_XU_LY";
  return String(status).trim().toUpperCase().replace(/\s+/g, "_");
}

function formatTime(value) {
  if (!value) return "Không xác định";
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return String(value);
  return parsed.toLocaleString("vi-VN", {
    hour: "2-digit", minute: "2-digit",
    day: "2-digit", month: "2-digit", year: "numeric",
  });
}

export default {
  name: "AdminHeatmap",
  setup() {
    const mapContainerEl = ref(null);
    let map = null;

    // State
    const allIncidents = ref([]);
    const incidentTypes = ref([]);
    const loading = ref(false);
    const searchQuery = ref("");
    const isFullscreen = ref(false);
    const sidebarCollapsed = ref(false);
    const showLayerPanel = ref(false);

    // Filters
    const filterType = ref("");
    const filterPriority = ref("");
    const filterTimeRange = ref("");
    const filterStatus = ref("");

    // Selected
    const selectedIncident = ref(null);
    const selectedHotspot = ref(null);

    // Layers
    const layers = ref({
      heatmap: true,
      cluster: true,
      marker: false,
      satellite: false,
    });
    const heatmapIntensity = ref(1.0);

    // Computed
    const activeFilterCount = computed(() => {
      let c = 0;
      if (filterType.value) c++;
      if (filterPriority.value) c++;
      if (filterTimeRange.value) c++;
      if (filterStatus.value) c++;
      return c;
    });

    const filteredIncidents = computed(() => {
      let items = [...allIncidents.value];

      if (filterType.value) {
        items = items.filter(i => i.typeId === filterType.value);
      }
      if (filterPriority.value) {
        items = items.filter(i => i.priority === filterPriority.value);
      }
      if (filterStatus.value) {
        items = items.filter(i => i.status === filterStatus.value);
      }
      if (filterTimeRange.value) {
        const now = new Date();
        const cutoff = new Date();
        if (filterTimeRange.value === "today") {
          cutoff.setHours(0, 0, 0, 0);
        } else if (filterTimeRange.value === "week") {
          cutoff.setDate(now.getDate() - 7);
        } else if (filterTimeRange.value === "month") {
          cutoff.setDate(now.getDate() - 30);
        }
        items = items.filter(i => new Date(i.rawTime) >= cutoff);
      }
      if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        items = items.filter(i =>
          i.address.toLowerCase().includes(q) ||
          i.type.toLowerCase().includes(q) ||
          String(i.id).includes(q)
        );
      }
      return items;
    });

    const geojsonData = computed(() => ({
      type: "FeatureCollection",
      features: filteredIncidents.value
        .filter(i => i.lat && i.lng)
        .map(i => ({
          type: "Feature",
          geometry: { type: "Point", coordinates: [i.lng, i.lat] },
          properties: {
            id: i.id,
            type: i.type,
            address: i.address,
            priority: i.priority,
            status: i.status,
            statusLabel: i.statusLabel,
            statusClass: i.statusClass,
            time: i.time,
            description: i.description,
            imageUrl: i.imageUrl,
          },
        })),
    }));

    const stats = computed(() => {
      const items = filteredIncidents.value;
      return {
        total: items.length,
        critical: items.filter(i => i.priority === "Cao" || i.priority?.toLowerCase().includes("khẩn")).length,
        processing: items.filter(i => i.status === "DANG_XU_LY").length,
        pending: items.filter(i => i.status === "CHO_XU_LY").length,
      };
    });

    const typeBreakdown = computed(() => {
      const map = {};
      filteredIncidents.value.forEach(i => {
        if (!map[i.typeId]) {
          map[i.typeId] = { typeId: i.typeId, name: i.type, count: 0 };
        }
        map[i.typeId].count++;
      });
      return Object.values(map).sort((a, b) => b.count - a.count).slice(0, 8);
    });

    // Parse incidents from API response
    function parseIncidents(rawData) {
      const items = Array.isArray(rawData)
        ? rawData
        : Array.isArray(rawData?.data) ? rawData.data
        : Array.isArray(rawData?.data?.data) ? rawData.data.data
        : [];

      return items.map((item) => {
        const id = item.id_yeu_cau || item.id || item.request_id || "-";
        const status = normalizeStatus(item.trang_thai || item.status);
        const statusMeta = STATUS_META[status] || { label: String(item.trang_thai || "Không rõ"), badge: "bg-secondary text-white" };
        const type = item.loai_su_co?.ten_danh_muc || item.loai_su_co?.ten_loai || item.loai || "Không rõ";
        const typeId = item.id_loai_su_co || item.loai_su_co?.id || null;
        const priority = item.muc_do_khan_cap || item.diem_uu_tien || "Trung bình";
        const address = item.vi_tri_dia_chi || item.dia_chi || "Không có địa chỉ";
        const rawTime = item.thoi_gian_gui || item.created_at || item.updated_at;

        // Lay toa do tu nhieu truong
        let lat = null, lng = null;
        if (item.vi_tri_lat != null && item.vi_tri_lng != null) {
          lat = parseFloat(item.vi_tri_lat);
          lng = parseFloat(item.vi_tri_lng);
        }
        if (!lat && item.toa_do) {
          const parts = String(item.toa_do).split(",").map(s => parseFloat(s.trim()));
          if (parts.length >= 2) { lat = parts[0]; lng = parts[1]; }
        }
        if (!lat && item.latitude) lat = parseFloat(item.latitude);
        if (!lng && item.longitude) lng = parseFloat(item.longitude);

        const imageRaw = item.hinh_anh;
        let imageUrl = null;
        if (imageRaw && /^(https?:|data:)/i.test(imageRaw)) imageUrl = imageRaw;
        else if (imageRaw && (imageRaw.startsWith("uploads/") || imageRaw.startsWith("/uploads/")))
          imageUrl = BASE_URL + "/" + imageRaw;

        return {
          id, type, typeId, priority, address, rawTime,
          lat, lng,
          status, statusLabel: statusMeta.label, statusClass: statusMeta.badge,
          time: formatTime(rawTime),
          description: item.mo_ta || item.moTa || "",
          imageUrl,
          raw: item,
        };
      }).filter(i => i.lat && i.lng);
    }

    // Map initialization
    function initMap() {
      const apiKey = import.meta.env.VITE_OPENMAP_API_KEY;
      if (!apiKey || !mapContainerEl.value) return;

      const styleUrl = `https://maptiles.openmap.vn/styles/${layers.value.satellite ? "satellite" : "day-v1"}/style.json?apikey=${apiKey}`;
      map = new maplibregl.Map({
        container: mapContainerEl.value,
        style: styleUrl,
        center: DEFAULT_CENTER,
        zoom: DEFAULT_ZOOM,
      });

      map.addControl(new maplibregl.NavigationControl(), "top-right");
      map.addControl(new maplibregl.ScaleControl(), "bottom-right");

      map.on("load", () => {
        addHeatmapLayer();
        addClusterLayer();
        updateLayers();
      });
    }

    function addHeatmapLayer() {
      if (map.getSource("incidents-heat")) return;
      map.addSource("incidents-heat", {
        type: "geojson",
        data: geojsonData.value,
      });

      map.addLayer({
        id: "incidents-heat-layer",
        type: "heatmap",
        source: "incidents-heat",
        maxzoom: 18,
        paint: {
          "heatmap-weight": [
            "match", ["get", "priority"],
            "Cao", 1.0,
            "Khẩn cấp", 1.0,
            "Trung bình", 0.5,
            "Thấp", 0.2,
            0.3,
          ],
          "heatmap-intensity": [
            "interpolate", ["linear"], ["zoom"],
            0, heatmapIntensity.value,
            15, heatmapIntensity.value * 1.5,
          ],
          "heatmap-color": [
            "interpolate", ["linear"], ["heatmap-density"],
            0, "rgba(33,102,172,0)",
            0.1, "rgba(103,169,207,0.6)",
            0.3, "rgba(209,229,140,0.7)",
            0.5, "rgba(253,231,37,0.8)",
            0.7, "rgba(255,173,0,0.85)",
            0.9, "rgba(255,87,20,0.9)",
            1, "rgba(239,0,0,0.95)",
          ],
          "heatmap-radius": [
            "interpolate", ["linear"], ["zoom"],
            0, 2,
            9, 15,
            15, 30,
          ],
          "heatmap-opacity": 0.75,
        },
      });

      // Click on heatmap to show hotspot
      map.on("click", "incidents-heat-layer", (e) => {
        if (e.features && e.features.length > 0) {
          const coords = e.features[0].geometry.coordinates;
          showHotspotInfo(coords);
        }
      });

      map.on("mouseenter", "incidents-heat-layer", () => {
        map.getCanvas().style.cursor = "pointer";
      });
      map.on("mouseleave", "incidents-heat-layer", () => {
        map.getCanvas().style.cursor = "";
      });
    }

    function addClusterLayer() {
      if (map.getSource("incidents-points")) return;
      map.addSource("incidents-points", {
        type: "geojson",
        data: geojsonData.value,
        cluster: true,
        clusterMaxZoom: 14,
        clusterRadius: 50,
      });

      // Cluster circles
      map.addLayer({
        id: "clusters",
        type: "circle",
        source: "incidents-points",
        filter: ["has", "point_count"],
        paint: {
          "circle-color": [
            "step", ["get", "point_count"],
            "#ef4444", 3,
            "#f97316", 7,
            "#eab308", 15,
          ],
          "circle-radius": [
            "step", ["get", "point_count"],
            20, 3,
            30, 7,
            40, 15,
          ],
          "circle-stroke-width": 3,
          "circle-stroke-color": "#fff",
          "circle-opacity": 0.9,
        },
      });

      // Cluster count labels
      map.addLayer({
        id: "cluster-count",
        type: "symbol",
        source: "incidents-points",
        filter: ["has", "point_count"],
        layout: {
          "text-field": "{point_count_abbreviated}",
          "text-font": ["Open Sans Bold"],
          "text-size": 14,
        },
        paint: {
          "text-color": "#ffffff",
        },
      });

      // Individual markers
      map.addLayer({
        id: "unclustered-point",
        type: "circle",
        source: "incidents-points",
        filter: ["!", ["has", "point_count"]],
        paint: {
          "circle-color": [
            "match", ["get", "status"],
            "CHO_XU_LY", "#0ea5e9",
            "DANG_XU_LY", "#f59e0b",
            "HOAN_THANH", "#22c55e",
            "HUY_BO", "#94a3b8",
            "#ef4444",
          ],
          "circle-radius": 10,
          "circle-stroke-width": 3,
          "circle-stroke-color": "#fff",
        },
      });

      // Click cluster to zoom
      map.on("click", "clusters", (e) => {
        const features = map.queryRenderedFeatures(e.point, { layers: ["clusters"] });
        if (!features.length) return;
        const clusterId = features[0].properties.cluster_id;
        map.getSource("incidents-points").getClusterExpansionZoom(clusterId, (err, zoom) => {
          if (err) return;
          map.easeTo({ center: features[0].geometry.coordinates, zoom: zoom });
        });
      });

      // Click unclustered point
      map.on("click", "unclustered-point", (e) => {
        if (!e.features || !e.features.length) return;
        const props = e.features[0].properties;
        const coords = e.features[0].geometry.coordinates;
        showIncidentDetail(props, coords);
      });

      // Cursor
      map.on("mouseenter", "clusters", () => { map.getCanvas().style.cursor = "pointer"; });
      map.on("mouseleave", "clusters", () => { map.getCanvas().style.cursor = ""; });
      map.on("mouseenter", "unclustered-point", () => { map.getCanvas().style.cursor = "pointer"; });
      map.on("mouseleave", "unclustered-point", () => { map.getCanvas().style.cursor = ""; });
    }

    function showHotspotInfo(coords) {
      const RADIUS = 0.005;
      const nearby = filteredIncidents.value.filter(i => {
        const d = Math.sqrt(Math.pow(i.lng - coords[0], 2) + Math.pow(i.lat - coords[1], 2));
        return d <= RADIUS;
      });

      if (nearby.length === 0) return;

      const topTypeMap = {};
      let topType = "";
      let topCount = 0;
      nearby.forEach(n => {
        topTypeMap[n.type] = (topTypeMap[n.type] || 0) + 1;
        if (topTypeMap[n.type] > topCount) {
          topCount = topTypeMap[n.type];
          topType = n.type;
        }
      });

      selectedHotspot.value = {
        name: `Khu vực ${coords[1].toFixed(4)}, ${coords[0].toFixed(4)}`,
        count: nearby.length,
        critical: nearby.filter(n => n.priority === "Cao" || n.priority?.toLowerCase().includes("khẩn")).length,
        topType,
        incidents: nearby,
      };
    }

    function showIncidentDetail(props, coords) {
      selectedIncident.value = {
        id: props.id,
        type: props.type,
        address: props.address,
        priority: props.priority,
        status: props.status,
        statusLabel: props.statusLabel,
        statusClass: props.statusClass,
        time: props.time,
        description: props.description,
        imageUrl: props.imageUrl,
        lat: coords[1],
        lng: coords[0],
      };
      const modalEl = document.getElementById("incidentModal");
      if (modalEl && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
      }
    }

    function updateLayers() {
      if (!map || !map.isStyleLoaded()) return;
      const heatmapLayer = map.getLayer("incidents-heat-layer");
      const clusterLayer = map.getLayer("clusters");
      const clusterCountLayer = map.getLayer("cluster-count");
      const unclusteredLayer = map.getLayer("unclustered-point");

      if (heatmapLayer) {
        map.setLayoutProperty("incidents-heat-layer", "visibility", layers.value.heatmap ? "visible" : "none");
      }
      const clusterVisible = layers.value.cluster ? "visible" : "none";
      if (clusterLayer) map.setLayoutProperty("clusters", "visibility", clusterVisible);
      if (clusterCountLayer) map.setLayoutProperty("cluster-count", "visibility", clusterVisible);

      if (unclusteredLayer) {
        map.setLayoutProperty("unclustered-point", "visibility", layers.value.marker ? "visible" : "none");
      }
    }

    function updateHeatmapIntensity() {
      if (!map || !map.isStyleLoaded()) return;
      const heatmapLayer = map.getLayer("incidents-heat-layer");
      if (heatmapLayer) {
        map.setPaintProperty("incidents-heat-layer", "heatmap-intensity", heatmapIntensity.value);
      }
    }

    function toggleSatellite() {
      if (!map) return;
      const apiKey = import.meta.env.VITE_OPENMAP_API_KEY;
      const newStyle = `https://maptiles.openmap.vn/styles/${layers.value.satellite ? "satellite" : "day-v1"}/style.json?apikey=${apiKey}`;
      // Remove old layers/sources before switching style to avoid "already exists" errors
      ["incidents-heat-layer", "incidents-heat", "clusters", "cluster-count", "unclustered-point", "incidents-points"].forEach(id => {
        if (map.getLayer(id)) map.removeLayer(id);
        if (map.getSource(id)) map.removeSource(id);
      });
      map.setStyle(newStyle);
      map.once("styledata", () => {
        addHeatmapLayer();
        addClusterLayer();
        updateLayers();
        updateMapData();
      });
    }

    function updateMapData() {
      if (!map) return;
      // Wait for map to be ready before updating sources
      if (!map.isStyleLoaded()) {
        map.once("styledata", () => updateMapData());
        return;
      }
      const heatSource = map.getSource("incidents-heat");
      const pointSource = map.getSource("incidents-points");
      if (heatSource) heatSource.setData(geojsonData.value);
      if (pointSource) pointSource.setData(geojsonData.value);
    }

    // Controls
    function zoomIn() { map?.zoomIn(); }
    function zoomOut() { map?.zoomOut(); }
    function resetView() { map?.flyTo({ center: DEFAULT_CENTER, zoom: DEFAULT_ZOOM, essential: true }); }
    function toggleFullscreen() { isFullscreen.value = !isFullscreen.value; }
    function toggleLayerPanel() { showLayerPanel.value = !showLayerPanel.value; }
    function resetFilters() {
      filterType.value = "";
      filterPriority.value = "";
      filterTimeRange.value = "";
      filterStatus.value = "";
      searchQuery.value = "";
    }
    function filterByType(typeId) { filterType.value = typeId; }

    function onSearch() {
      updateMapData();
    }

    function viewHotspotIncidents(hotspot) {
      if (!hotspot || !hotspot.incidents || !hotspot.incidents.length) return;
      const first = hotspot.incidents[0];
      if (first.lat && first.lng) {
        map.flyTo({ center: [first.lng, first.lat], zoom: 16, essential: true });
      }
    }

    function navigateToAssignment(incident) {
      window.location.href = `/admin/assignments?id=${incident.id}`;
    }

    async function loadData() {
      loading.value = true;
      try {
        const [incRes, typeRes] = await Promise.all([
          rescueRequestAPI.getList(),
          incidentTypeAPI.getList(),
        ]);
        allIncidents.value = parseIncidents(incRes.data);
        incidentTypes.value = typeRes.data?.data || typeRes.data || [];
        console.log("[Heatmap] Incidents loaded:", allIncidents.value.length, "with coords:", allIncidents.value.filter(i => i.lat && i.lng).length);
        updateMapData();
      } catch (err) {
        console.error("Heatmap load error:", err);
      } finally {
        loading.value = false;
      }
    }

    function refreshData() { loadData(); }

    // Watch filtered data changes
    watch(filteredIncidents, () => {
      updateMapData();
    }, { deep: true });

    onMounted(() => {
      initMap();
      loadData();
    });

    onUnmounted(() => {
      if (map) { map.remove(); map = null; }
    });

    return {
      mapContainerEl,
      allIncidents, incidentTypes, loading,
      searchQuery, isFullscreen, sidebarCollapsed, showLayerPanel,
      filterType, filterPriority, filterTimeRange, filterStatus,
      selectedIncident, selectedHotspot,
      layers, heatmapIntensity,
      activeFilterCount, filteredIncidents, stats, typeBreakdown, typeColors,
      zoomIn, zoomOut, resetView, toggleFullscreen, toggleLayerPanel,
      resetFilters, filterByType, onSearch, refreshData,
      updateLayers, updateHeatmapIntensity, toggleSatellite,
      viewHotspotIncidents, navigateToAssignment,
    };
  },
};
</script>

<style scoped>
.heatmap-page {
  padding: 1.5rem;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.page-header {
  flex-shrink: 0;
}

.icon-box {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.heatmap-layout {
  flex: 1;
  min-height: 0;
  display: flex;
}

/* Sidebar */
.heatmap-sidebar {
  width: 300px;
  flex-shrink: 0;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  overflow-y: auto;
  transition: width 0.3s ease;
  position: relative;
}

.heatmap-sidebar.sidebar-collapsed {
  width: 40px;
  padding: 0.5rem 0;
  align-items: center;
}

.sidebar-toggle {
  position: absolute;
  right: -14px;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  z-index: 10;
  font-size: 12px;
  transition: all 0.2s;
}

.sidebar-toggle:hover {
  background: #f3f4f6;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

/* Stat Cards */
.stats-section,
.type-breakdown-section,
.legend-section {
  animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.875rem 1rem;
  border-radius: 12px;
  background: #f9fafb;
  border: 1px solid #f3f4f6;
  transition: all 0.2s;
  cursor: default;
}

.stat-card:hover {
  transform: translateX(3px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.stat-card.stat-total { border-left: 3px solid #3b82f6; }
.stat-card.stat-critical { border-left: 3px solid #ef4444; }
.stat-card.stat-processing { border-left: 3px solid #f59e0b; }
.stat-card.stat-pending { border-left: 3px solid #0ea5e9; }

.stat-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
}

.stat-total .stat-icon { background: #dbeafe; color: #3b82f6; }
.stat-critical .stat-icon { background: #fee2e2; color: #ef4444; }
.stat-processing .stat-icon { background: #fef3c7; color: #f59e0b; }
.stat-pending .stat-icon { background: #e0f2fe; color: #0ea5e9; }

.stat-value {
  font-size: 1.375rem;
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.stat-info { flex: 1; }

/* Type Breakdown */
.type-breakdown {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.type-item {
  display: grid;
  grid-template-columns: 10px 1fr;
  grid-template-rows: auto auto;
  gap: 2px 8px;
  padding: 0.5rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.15s;
}

.type-item:hover {
  background: #f9fafb;
}

.type-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-top: 5px;
  flex-shrink: 0;
  grid-row: span 2;
}

.type-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.type-name {
  font-size: 0.8125rem;
  font-weight: 500;
  color: #374151;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 140px;
}

.type-count {
  font-size: 0.8125rem;
  font-weight: 700;
  color: #111827;
  background: #f3f4f6;
  padding: 1px 8px;
  border-radius: 20px;
  flex-shrink: 0;
}

.type-bar {
  height: 4px;
  background: #f3f4f6;
  border-radius: 2px;
  overflow: hidden;
}

.type-bar-fill {
  height: 100%;
  border-radius: 2px;
  transition: width 0.4s ease;
}

/* Legend */
.legend-items {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  color: #4b5563;
}

.legend-dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  flex-shrink: 0;
  display: inline-block;
}

.legend-marker {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  flex-shrink: 0;
  display: inline-block;
}

.legend-marker.cluster { background: #ef4444; }
.legend-marker.marker-pending { background: #0ea5e9; }
.legend-marker.marker-processing { background: #f59e0b; }
.legend-marker.marker-completed { background: #22c55e; }

/* Map Container */
.heatmap-map-container {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.map-card {
  flex: 1;
  display: flex;
  flex-direction: column;
  position: relative;
  min-height: 500px;
}

.maplibre-map {
  flex: 1;
  min-height: 500px;
  transition: all 0.3s ease;
}

.maplibre-map.fullscreen-map {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  z-index: 9999;
}

/* Map Controls Overlay */
.map-controls-overlay {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 5;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.map-search-box {
  width: 280px;
}

.map-search-box .input-group {
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.map-search-box .form-control:focus,
.map-search-box .input-group-text {
  border-color: #e5e7eb;
}

.map-zoom-controls {
  width: 36px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  overflow: hidden;
}

.map-zoom-controls .btn {
  border-radius: 0 !important;
  border: none;
  border-bottom: 1px solid #f3f4f6;
  font-size: 14px;
  padding: 6px 8px;
}

.map-zoom-controls .btn:last-child {
  border-bottom: none;
}

/* Layer Panel */
.layer-panel {
  position: absolute;
  top: 60px;
  right: 12px;
  z-index: 5;
  width: 200px;
  border: 1px solid #e5e7eb;
}

/* Loading Overlay */
.map-loading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 10;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(2px);
}

.spinner-lg {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Hotspot Detail */
.hotspot-detail-panel {
  position: absolute;
  bottom: 24px;
  left: 12px;
  z-index: 5;
  padding: 1rem;
  width: 280px;
  border: 1px solid #e5e7eb;
  animation: slideInUp 0.3s ease;
}

@keyframes slideInUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Incident Modal */
.info-group label {
  color: #9ca3af;
}

.incident-image-thumb img {
  max-height: 200px;
  object-fit: cover;
}

/* Spin Animation */
.spin-animation {
  animation: spin 0.8s linear infinite;
}

/* Transitions */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.25s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(10px);
}

/* Responsive */
@media (max-width: 768px) {
  .heatmap-page {
    padding: 0.75rem;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .heatmap-layout {
    flex-direction: column;
  }

  .heatmap-sidebar {
    width: 100%;
    max-height: 200px;
  }

  .heatmap-sidebar.sidebar-collapsed {
    width: 100%;
    max-height: 40px;
  }

  .sidebar-toggle {
    right: auto;
    bottom: -14px;
    top: auto;
    transform: none;
  }

  .map-controls-overlay {
    top: 8px;
    left: 8px;
  }

  .map-search-box {
    width: 200px;
  }

  .layer-panel {
    top: 80px;
    right: 8px;
    width: 180px;
  }

  .hotspot-detail-panel {
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    width: calc(100% - 24px);
  }
}
</style>
