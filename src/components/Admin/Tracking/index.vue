<template>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0 fw-semibold">Theo dõi vị trí đội cứu hộ real-time</h5>
        <small class="text-muted">Placeholder để tích hợp bản đồ + socket sau</small>
      </div>
    </div>
    <div class="card-body">
      <div class="border rounded-4 overflow-hidden mb-3 bg-light-subtle">
        <div class="small text-muted p-2 px-3 bg-white border-bottom">Bản đồ (Mapbox) — nối WebSocket sau</div>
        <div class="tracking-map-wrap">
          <MapboxMap :zoom="12" />
        </div>
      </div>
      <div class="small text-muted">
        Gợi ý tính năng:
        <ul>
          <li>Hiển thị vị trí từng đội trên bản đồ.</li>
          <li>Filter theo trạng thái (rảnh, đang di chuyển, đang làm nhiệm vụ).</li>
          <li>Hiển thị route di chuyển gần nhất.</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";

export default {
  name: "AdminTracking",
  components: { MapboxMap },
};
</script>

<style scoped>
.tracking-map-wrap {
  height: 400px;
}
</style>

