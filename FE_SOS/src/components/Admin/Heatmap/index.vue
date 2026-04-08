<template>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0 fw-semibold">Bản đồ nhiệt khu vực nguy hiểm</h5>
        <small class="text-muted">UI nền để tích hợp heatmap (Leaflet/Mapbox...) sau</small>
      </div>
    </div>
    <div class="card-body">
      <div class="heatmap-map rounded-4 mb-3 overflow-hidden border">
        <MapboxMap :zoom="11" />
      </div>
      <div class="small text-muted">
        Bạn có thể tích hợp:
        <ul>
          <li>Layer heatmap theo mật độ yêu cầu.</li>
          <li>Lọc theo thời gian, loại sự cố, mức ưu tiên.</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";

export default {
  name: "AdminHeatmap",
  components: { MapboxMap },
};
</script>

<style scoped>
.heatmap-map {
  height: 380px;
}
</style>

