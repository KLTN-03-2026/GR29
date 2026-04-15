<template>
  <div class="heatmap-wrapper">
    <!-- Header -->
    <div class="heatmap-header px-4 py-3 d-flex align-items-center justify-content-between border-bottom bg-white shadow-sm">
      <div class="d-flex align-items-center">
        <div class="header-icon me-3 d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px;">
          <i class="fa-solid fa-fire fs-4"></i>
        </div>
        <div>
          <h5 class="fw-bold mb-0 text-dark">Bản Đồ Nhiệt - Hotspot Cứu Hộ</h5>
          <span class="text-muted small">{{ heatmapData.length }} vị trí nóng được phát hiện</span>
        </div>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" @click="refreshData">
          <i class="fa-solid fa-sync me-1"></i> Làm mới
        </button>
        <select class="form-select form-select-sm rounded-pill px-3" style="width: auto;" v-model="timeRange">
          <option value="today">Hôm nay</option>
          <option value="week">7 ngày qua</option>
          <option value="month">30 ngày qua</option>
        </select>
      </div>
    </div>

    <!-- Map Area -->
    <div class="heatmap-body flex-grow-1 position-relative">
      <MapboxMap
        class="w-100 h-100"
        :center="[105.8342, 21.0285]"
        :zoom="13"
        :heatmapData="heatmapPoints"
      />

      <!-- Stats Overlay -->
      <div class="stats-overlay position-absolute top-0 start-0 m-4 bg-white rounded-4 shadow-lg p-3" style="max-width: 280px; z-index: 1000;">
        <h6 class="fw-bold mb-3 text-dark">Thống kê hotspot</h6>
        <div class="stat-item d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Tổng sự cố</span>
          <span class="fw-bold text-dark">{{ heatmapData.length }}</span>
        </div>
        <div class="stat-item d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Khẩn cấp</span>
          <span class="fw-bold text-danger">{{ urgentCount }}</span>
        </div>
        <div class="stat-item d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Trung bình</span>
          <span class="fw-bold text-warning">{{ mediumCount }}</span>
        </div>
        <div class="stat-item d-flex align-items-center justify-content-between">
          <span class="text-muted small">Thường</span>
          <span class="fw-bold text-info">{{ normalCount }}</span>
        </div>
      </div>

      <!-- Legend -->
      <div class="legend-overlay position-absolute bottom-0 end-0 m-4 bg-white rounded-4 shadow-lg p-3" style="z-index: 1000;">
        <h6 class="fw-bold mb-2 text-dark">Màu độ khẩn cấp</h6>
        <div class="legend-item d-flex align-items-center gap-2 mb-2">
          <div class="color-box" style="width: 20px; height: 20px; background: #ef4444; border-radius: 4px;"></div>
          <span class="small text-secondary">Khẩn cấp</span>
        </div>
        <div class="legend-item d-flex align-items-center gap-2 mb-2">
          <div class="color-box" style="width: 20px; height: 20px; background: #f59e0b; border-radius: 4px;"></div>
          <span class="small text-secondary">Trung bình</span>
        </div>
        <div class="legend-item d-flex align-items-center gap-2">
          <div class="color-box" style="width: 20px; height: 20px; background: #3b82f6; border-radius: 4px;"></div>
          <span class="small text-secondary">Thường</span>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75" style="z-index: 2000;">
      <div class="text-center">
        <div class="spinner-border text-danger" role="status" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-2 text-muted">Đang tải dữ liệu...</p>
      </div>
    </div>
  </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";
import { rescuerAPI } from "../../../services/api.js";

export default {
  components: { MapboxMap },
  name: "HeatMap",
  data() {
    return {
      loading: false,
      heatmapData: [],
      timeRange: 'week',
    };
  },
  computed: {
    heatmapPoints() {
      return this.heatmapData.map(item => ({
        lat: item.vi_do || 21.0285,
        lng: item.kinh_do || 105.8342,
        intensity: this.getIntensity(item.muc_do_khan_cap),
        yeuCau: item,
      }));
    },
    urgentCount() {
      return this.heatmapData.filter(d => (d.muc_do_khan_cap || '').toUpperCase() === 'KHA_CAP').length;
    },
    mediumCount() {
      return this.heatmapData.filter(d => (d.muc_do_khan_cap || '').toUpperCase() === 'TRUNG_BINH').length;
    },
    normalCount() {
      return this.heatmapData.filter(d => (d.muc_do_khan_cap || '').toUpperCase() === 'THUONG').length;
    },
  },
  watch: {
    timeRange() {
      this.fetchHeatmapData();
    },
  },
  async mounted() {
    await this.fetchHeatmapData();
  },
  methods: {
    async fetchHeatmapData() {
      this.loading = true;
      try {
        const res = await rescuerAPI.getHeatmap();
        if (res.data?.data?.data) {
          this.heatmapData = res.data.data.data;
        } else if (res.data?.data) {
          this.heatmapData = res.data.data;
        } else if (Array.isArray(res.data)) {
          this.heatmapData = res.data;
        }
      } catch (e) {
        console.error("Lỗi tải heatmap:", e);
      } finally {
        this.loading = false;
      }
    },
    getIntensity(mucDo) {
      const level = (mucDo || 'THUONG').toUpperCase();
      if (level === 'KHA_CAP') return 1.0;
      if (level === 'TRUNG_BINH') return 0.7;
      return 0.4;
    },
    refreshData() {
      this.fetchHeatmapData();
    },
  },
};
</script>

<style scoped>
.heatmap-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px);
  overflow: hidden;
  background: #f8f9fa;
  position: relative;
}

.heatmap-header {
  flex-shrink: 0;
  z-index: 10;
  position: relative;
}

.heatmap-body {
  height: 100%;
  width: 100%;
}

.stats-overlay {
  z-index: 1000;
}

.legend-overlay {
  z-index: 1000;
}

.stat-item {
  padding: 0.25rem 0;
}

.legend-item {
  padding: 0.25rem 0;
}

.color-box {
  transition: transform 0.2s;
}

.legend-item:hover .color-box {
  transform: scale(1.1);
}

.loading-overlay {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(4px);
}
</style>
