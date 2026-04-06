<template>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h5 class="mb-0 fw-semibold">📍 Bản đồ nhiệt khu vực nguy hiểm (6 cấp độ)</h5>
        <small class="text-muted">Hiển thị dữ liệu heatmap theo tọa độ từ backend với 6 cấp độ màu sắc</small>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-outline-secondary" @click="loadHeatmap" :disabled="loading">
          <i class="bi bi-arrow-clockwise me-1"></i>
          Làm mới
        </button>
        <button class="btn btn-sm" :class="useHeatmap ? 'btn-danger' : 'btn-outline-danger'" @click="useHeatmap = !useHeatmap">
          <i class="bi bi-fire me-1"></i>
            {{ useHeatmap ? "Ẩn lớp nhiệt (6 cấp)" : "Hiện lớp nhiệt (6 cấp)" }}
        </button>
      </div>
    </div>

    <div class="card-body">
      <div v-if="error" class="alert alert-warning">{{ error }}</div>

      <div class="heatmap-map rounded-4 mb-3 overflow-hidden border position-relative">
        <div v-if="loading" class="heatmap-loading d-flex align-items-center justify-content-center">
          <div class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="small text-muted mt-2">Đang tải dữ liệu bản đồ...</div>
          </div>
        </div>
        <MapboxMap
          :center="mapCenter"
          :zoom="11"
          :show-marker="false"
          :points="mapPoints"
          :use-heatmap="useHeatmap"
          :fit-to-points="true"
        />
      </div>

      <div class="row g-3">
        <div class="col-12 col-xl-4">
          <div class="legend-card rounded-4 border p-3 h-100">
            <div class="fw-semibold mb-2">Chú thích mật độ</div>
            <div class="small text-muted mb-3">Màu càng nóng thì khu vực càng có nhiều yêu cầu hơn. Từ xanh (ít) đến đỏ (nhiều).</div>
            <ul class="list-unstyled mb-0 small">
              <li v-for="item in legendItems" :key="item.label" class="d-flex justify-content-between align-items-center mb-2">
                <span class="d-flex align-items-center">
                  <span class="legend-dot me-2" :style="{ background: item.color }"></span>
                  {{ item.label }}
                </span>
                <span class="text-muted">{{ item.range }}</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- <div class="col-12 col-xl-8">
          <div class="rounded-4 border p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
              <div>
                <div class="fw-semibold">Điểm nóng nổi bật</div>
                <div class="small text-muted">Chọn một điểm trên bản đồ để xem chi tiết nhanh</div>
              </div>
              <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">
                {{ mapPoints.length }} điểm
              </span>
            </div>

            <div v-if="mapPoints.length === 0" class="text-muted small">Chưa có dữ liệu heatmap từ backend.</div>

            <div v-else class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr class="small text-muted">
                    <th>Quận</th>
                    <th>Loại sự cố</th>
                    <th>Mật độ</th>
                    <th>Tọa độ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="point in mapPoints.slice(0, 8)" :key="point.id">
                    <td class="fw-semibold">{{ point.district || point.title }}</td>
                    <td class="small">{{ point.incidentType || point.description }}</td>
                    <td>
                      <span class="badge rounded-pill" :style="{ background: point.color, color: '#fff' }">
                        {{ point.count }}
                      </span>
                    </td>
                    <td class="small text-muted">{{ point.lat.toFixed(5) }}, {{ point.lng.toFixed(5) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div> -->
      </div>
    </div>
  </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";
import { analyticsAPI } from "../../../services/api";

const DEFAULT_CENTER = [108.2022, 16.0544];

function unwrapResponse(response) {
  const payload = response?.data ?? response;
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload?.data)) return payload.data;
  if (Array.isArray(payload?.data?.data)) return payload.data.data;
  return payload?.data ?? payload ?? [];
}

function toNumber(value) {
  const num = Number(value);
  return Number.isFinite(num) ? num : null;
}

function normalizeText(value, fallback = "Không rõ") {
  if (value === null || value === undefined || value === "") return fallback;
  return String(value).trim() || fallback;
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

function getPointColor(weight) {
  if (weight >= 15) return "#dc1e3c";
  if (weight >= 11) return "#ef4444";
  if (weight >= 8) return "#f97316";
  if (weight >= 5) return "#eab308";
  if (weight >= 3) return "#22c55e";
  return "#0ea5e9";
}

function buildMapPoints(items) {
  return items
    .map((item, index) => {
      const lat = toNumber(getValueByKeys(item, ["vi_do", "lat", "latitude", "vi_tri_lat"]));
      const lng = toNumber(getValueByKeys(item, ["kinh_do", "lng", "longitude", "vi_tri_lng"]));
      const count = Number(getValueByKeys(item, ["so_luong", "count", "tong_so", "weight"], 1)) || 1;

      if (lat === null || lng === null) return null;

      return {
        id: getValueByKeys(item, ["id", "id_yeu_cau", "ma"], `heat-${index + 1}`),
        lat,
        lng,
        count,
        weight: count,
        district: normalizeText(
          getValueByKeys(item, ["quan", "district", "khu_vuc", "ten_khu_vuc", "phuong"]),
          "Không rõ"
        ),
        incidentType: normalizeText(
          getValueByKeys(item, ["loai_su_co", "incident_type", "type", "loai", "category"]),
          "Không rõ"
        ),
        title: normalizeText(
          getValueByKeys(item, ["khu_vuc", "ten_khu_vuc", "vi_tri_dia_chi", "name", "title"]),
          `Điểm nóng ${index + 1}`
        ),
        description: normalizeText(
          getValueByKeys(item, ["mo_ta", "description", "ghi_chu", "chi_tiet"]),
          "Không có mô tả"
        ),
        color: getPointColor(count),
      };
    })
    .filter(Boolean);
}

export default {
  name: "AdminHeatmap",
  components: { MapboxMap },
  data() {
    return {
      loading: false,
      error: "",
      useHeatmap: true,
      mapCenter: DEFAULT_CENTER,
      mapPoints: [],
      legendItems: [
        { label: "Rất thấp", range: "1 - 2", color: "#0ea5e9" },
        { label: "Thấp", range: "3 - 4", color: "#22c55e" },
        { label: "Trung bình", range: "5 - 7", color: "#eab308" },
        { label: "Cao", range: "8 - 10", color: "#f97316" },
        { label: "Rất cao", range: "11 - 15", color: "#ef4444" },
        { label: "Cực cao", range: "15+", color: "#dc1e3c" },
      ],
    };
  },
  async created() {
    await this.loadHeatmap();
  },
  methods: {
    async loadHeatmap() {
      this.loading = true;
      this.error = "";
      try {
        const response = await analyticsAPI.getHeatmapData();
        const items = unwrapResponse(response);
        const points = buildMapPoints(Array.isArray(items) ? items : []);
        this.mapPoints = points;

        if (points.length > 0) {
          this.mapCenter = [points[0].lng, points[0].lat];
        } else {
          this.mapCenter = DEFAULT_CENTER;
          this.error = "API heatmap không có điểm tọa độ hợp lệ để hiển thị.";
        }
      } catch (error) {
        console.error("Không tải được dữ liệu heatmap:", error);
        this.mapPoints = [];
        this.mapCenter = DEFAULT_CENTER;
        this.error = "Không tải được dữ liệu heatmap từ backend.";
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.heatmap-map {
  height: 480px;
}

.heatmap-loading {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.65);
  z-index: 2;
  backdrop-filter: blur(2px);
}

.legend-card {
  background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
}

.legend-dot {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 999px;
}
</style>
