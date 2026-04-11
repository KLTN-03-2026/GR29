<template>
  <div class="mapbox-wrap position-relative w-100 h-100">
    <div v-if="!hasToken" class="mapbox-fallback d-flex align-items-center justify-content-center bg-dark text-white-50 small p-4 text-center">
      <div>
        <i class="fa-solid fa-map-location-dot fs-2 mb-2 d-block text-warning"></i>
        Chưa cấu hình <code class="text-warning">VITE_OPENMAP_API_KEY</code> trong file <code>.env</code>
        (FE). Tạo API Key tại
        <a href="https://enterprise.openmap.vn/" target="_blank" rel="noopener" class="text-info">enterprise.openmap.vn</a>.
      </div>
    </div>
    <div v-show="hasToken" ref="containerEl" class="mapbox-canvas w-100 h-100 rounded-4"></div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from "vue";
import maplibregl from "@openmapvn/openmapvn-gl";
import "@openmapvn/openmapvn-gl/dist/maplibre-gl.css";

const props = defineProps({
  center: {
    type: Array,
    default: () => [108.2022, 16.0544],
  },
  zoom: {
    type: Number,
    default: 12,
  },
  /** Hiển thị marker đỏ tại center */
  showMarker: {
    type: Boolean,
    default: true,
  },
  /** Style OpenMap (day-v1, night-v1, satellite, ...) */
  mapStyle: {
    type: String,
    default: "day-v1",
  },
});

const containerEl = ref(null);
const hasToken = ref(!!import.meta.env.VITE_OPENMAP_API_KEY);

let map = null;
let marker = null;

function initMap() {
  const apiKey = import.meta.env.VITE_OPENMAP_API_KEY;
  if (!apiKey || !containerEl.value) return;

  const styleUrl = `https://maptiles.openmap.vn/styles/${props.mapStyle}/style.json?apikey=${apiKey}`;
  map = new maplibregl.Map({
    container: containerEl.value,
    style: styleUrl,
    center: props.center,
    zoom: props.zoom,
  });
  map.addControl(new maplibregl.NavigationControl(), "top-right");

  if (props.showMarker) {
    marker = new maplibregl.Marker({ color: "#dc3545" })
      .setLngLat(props.center)
      .addTo(map);
  }

  map.on("load", () => {
    map.resize();
  });
}

function flyTo(lng, lat, zoom = 15) {
  if (!map) return;
  map.flyTo({ center: [lng, lat], zoom, essential: true });
  if (marker) {
    marker.setLngLat([lng, lat]);
  }
}

function locateUser() {
  if (!navigator.geolocation) {
    return Promise.reject(new Error("Trình duyệt không hỗ trợ GPS"));
  }
  return new Promise((resolve, reject) => {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const { longitude, latitude } = pos.coords;
        flyTo(longitude, latitude, 15);
        resolve({ lng: longitude, lat: latitude });
      },
      (err) => reject(err),
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
  });
}

onMounted(() => {
  initMap();
  window.addEventListener("resize", onResize);
});

onUnmounted(() => {
  window.removeEventListener("resize", onResize);
  marker?.remove();
  marker = null;
  map?.remove();
  map = null;
});

function onResize() {
  map?.resize();
}

watch(
  () => [...props.center],
  (c) => {
    if (map && c?.length === 2) {
      map.setCenter(c);
      if (marker) marker.setLngLat(c);
    }
  }
);

defineExpose({ flyTo, locateUser, map: () => map });
</script>

<style scoped>
.mapbox-wrap {
  min-height: 280px;
}
.mapbox-canvas {
  min-height: inherit;
}
.mapbox-fallback {
  min-height: 280px;
  border-radius: 1rem;
}
</style>
