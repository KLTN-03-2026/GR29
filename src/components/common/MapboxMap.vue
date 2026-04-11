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
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
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
  /** Chế độ cũ: một marker đỏ tại center (khi không truyền user/đích) */
  showMarker: {
    type: Boolean,
    default: true,
  },
  /** [lng, lat] — vị trí cứu hộ viên (xanh dương) */
  userLngLat: {
    type: Array,
    default: null,
  },
  /** [lng, lat] — hiện trường / đích (đỏ) */
  destinationLngLat: {
    type: Array,
    default: null,
  },
  mapStyle: {
    type: String,
    default: "day-v1",
  },
});

const containerEl = ref(null);
const hasToken = ref(!!import.meta.env.VITE_OPENMAP_API_KEY);

let map = null;
let legacyMarker = null;
let userMarker = null;
let destMarker = null;
function validLngLat(c) {
  return Array.isArray(c) && c.length >= 2 && Number.isFinite(Number(c[0])) && Number.isFinite(Number(c[1]));
}

const useDualMarkers = computed(() => validLngLat(props.userLngLat) || validLngLat(props.destinationLngLat));

function removeLegacyMarker() {
  legacyMarker?.remove();
  legacyMarker = null;
}

/** Kiểu Google Maps: tròn trắng viền đen */
function makeUserDotEl() {
  const el = document.createElement("div");
  el.style.width = "18px";
  el.style.height = "18px";
  el.style.borderRadius = "50%";
  el.style.background = "#ffffff";
  el.style.border = "2px solid #202124";
  el.style.boxShadow = "0 2px 6px rgba(0,0,0,.35)";
  return el;
}

function makeDotEl(background, border = "2px solid white") {
  const el = document.createElement("div");
  el.style.width = "16px";
  el.style.height = "16px";
  el.style.borderRadius = "50%";
  el.style.background = background;
  el.style.border = border;
  el.style.boxShadow = "0 1px 4px rgba(0,0,0,.45)";
  return el;
}

function syncDualMarkers() {
  if (!map) return;

  if (!useDualMarkers.value) {
    userMarker?.remove();
    userMarker = null;
    destMarker?.remove();
    destMarker = null;
    return;
  }

  removeLegacyMarker();

  if (validLngLat(props.userLngLat)) {
    if (!userMarker) {
      userMarker = new maplibregl.Marker({ element: makeUserDotEl(), anchor: "center" })
        .setLngLat(props.userLngLat)
        .addTo(map);
    } else {
      userMarker.setLngLat(props.userLngLat);
    }
  } else {
    userMarker?.remove();
    userMarker = null;
  }

  if (validLngLat(props.destinationLngLat)) {
    if (!destMarker) {
      destMarker = new maplibregl.Marker({ color: "#ea4335" })
        .setLngLat(props.destinationLngLat)
        .addTo(map);
    } else {
      destMarker.setLngLat(props.destinationLngLat);
    }
  } else {
    destMarker?.remove();
    destMarker = null;
  }
}

function syncLegacyMarker() {
  if (!map) return;
  if (useDualMarkers.value) return;
  if (!props.showMarker) {
    legacyMarker?.remove();
    legacyMarker = null;
    return;
  }
  if (!legacyMarker) {
    legacyMarker = new maplibregl.Marker({ color: "#dc3545" })
      .setLngLat(props.center)
      .addTo(map);
  } else {
    legacyMarker.setLngLat(props.center);
  }
}

function styleLoaded() {
  return typeof map.isStyleLoaded !== "function" || map.isStyleLoaded();
}

function clearAlternativeRoutes() {
  if (!map) return;
  if (map.getLayer("rescuer-route-alts-line")) map.removeLayer("rescuer-route-alts-line");
  if (map.getSource("rescuer-alts")) map.removeSource("rescuer-alts");
}

/**
 * Style OpenMap đôi khi thiếu sprite (town_hall-11, road_6, …) → cảnh báo console.
 * Gắn ảnh trong suốt 1×1 để map không lỗi khi vẽ symbol.
 */
function installStyleImageFallback() {
  if (!map) return;
  map.on("styleimagemissing", (e) => {
    if (map.hasImage(e.id)) return;
    try {
      map.addImage(e.id, {
        width: 1,
        height: 1,
        data: new Uint8Array(4),
      });
    } catch {
      /* ignore */
    }
  });
}

function initRouteLayer() {
  if (!map || !styleLoaded() || map.getSource("rescuer-route")) return;
  map.addSource("rescuer-route", {
    type: "geojson",
    data: {
      type: "Feature",
      properties: {},
      geometry: { type: "LineString", coordinates: [] },
    },
  });
  /* Không truyền beforeId: thêm vào cuối stack = vẽ trên cùng (tránh bị chìm dưới layer nền) */
  map.addLayer({
    id: "rescuer-route-casing",
    type: "line",
    source: "rescuer-route",
    layout: { "line-join": "round", "line-cap": "round" },
    paint: {
      "line-color": "#ffffff",
      "line-width": 12,
      "line-opacity": 1,
    },
  });
  map.addLayer({
    id: "rescuer-route-line",
    type: "line",
    source: "rescuer-route",
    layout: { "line-join": "round", "line-cap": "round" },
    paint: {
      "line-color": "#1a73e8",
      "line-width": 7,
      "line-opacity": 1,
    },
  });
}

/** Tuyến phụ (màu nhạt, giống gợi ý phụ trên Google Maps) */
function setAlternativeRoutes(alternatives) {
  if (!map || !styleLoaded()) return;
  clearAlternativeRoutes();
  if (!alternatives?.length) return;
  const features = alternatives
    .filter((c) => Array.isArray(c) && c.length > 1)
    .map((coordinates) => ({
      type: "Feature",
      properties: {},
      geometry: { type: "LineString", coordinates },
    }));
  if (!features.length) return;
  map.addSource("rescuer-alts", {
    type: "geojson",
    data: { type: "FeatureCollection", features },
  });
  map.addLayer({
    id: "rescuer-route-alts-line",
    type: "line",
    source: "rescuer-alts",
    layout: { "line-join": "round", "line-cap": "round" },
    paint: {
      "line-color": "#A8C7FA",
      "line-width": 6,
      "line-opacity": 0.9,
    },
  });
  if (map.getLayer("rescuer-route-casing")) {
    map.moveLayer("rescuer-route-alts-line", "rescuer-route-casing");
  }
}

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
  installStyleImageFallback();

  map.on("load", () => {
    map.resize();
    initRouteLayer();
    syncDualMarkers();
    syncLegacyMarker();
  });
}

function flyTo(lng, lat, zoom = 15) {
  if (!map) return;
  map.flyTo({ center: [lng, lat], zoom, essential: true });
  if (!useDualMarkers.value && legacyMarker) {
    legacyMarker.setLngLat([lng, lat]);
  }
}

/** Đưa tuyến chính lên trên cùng (tránh bị layer của style thêm sau che mất) */
function bringRouteLayersToFront() {
  if (!map) return;
  try {
    if (map.getLayer("rescuer-route-casing")) map.moveLayer("rescuer-route-casing");
    if (map.getLayer("rescuer-route-line")) map.moveLayer("rescuer-route-line");
  } catch {
    /* ignore */
  }
}

/**
 * @param {{ primary: number[][], alternatives?: number[][] }} payload
 */
function setRouteData(payload) {
  const coords = payload?.primary;
  const alternatives = payload?.alternatives;
  if (!map || !coords?.length) return;
  const apply = () => {
    if (!map || !styleLoaded()) return;
    clearAlternativeRoutes();
    if (!map.getSource("rescuer-route")) {
      initRouteLayer();
    }
    const src = map.getSource("rescuer-route");
    if (!src) return;
    src.setData({
      type: "Feature",
      properties: {},
      geometry: { type: "LineString", coordinates: coords },
    });
    if (alternatives?.length) setAlternativeRoutes(alternatives);
    bringRouteLayersToFront();
    map.once("idle", bringRouteLayersToFront);
  };
  if (styleLoaded()) apply();
  else map.once("load", apply);
}

/** Tương thích cũ: chỉ một LineString */
function setRouteCoordinates(coords) {
  setRouteData({ primary: coords });
}

function clearRoute() {
  if (!map) return;
  clearAlternativeRoutes();
  const src = map.getSource("rescuer-route");
  if (src) {
    src.setData({
      type: "Feature",
      properties: {},
      geometry: { type: "LineString", coordinates: [] },
    });
  }
}

/** Giới hạn khung nhìn để thấy tất cả điểm [lng, lat] */
function fitBounds(points, padding = 72, maxZoom = 15) {
  if (!map || !points?.length) return;
  const valid = points.filter(validLngLat);
  if (!valid.length) return;
  if (valid.length === 1) {
    map.flyTo({ center: valid[0], zoom: maxZoom, essential: true });
    return;
  }
  const bounds = new maplibregl.LngLatBounds(valid[0], valid[0]);
  valid.forEach((p) => bounds.extend(p));
  map.fitBounds(bounds, { padding, maxZoom, essential: true });
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
  legacyMarker?.remove();
  legacyMarker = null;
  userMarker?.remove();
  userMarker = null;
  destMarker?.remove();
  destMarker = null;
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
      if (!useDualMarkers.value) syncLegacyMarker();
    }
  }
);

watch(
  () => [props.userLngLat, props.destinationLngLat, props.showMarker],
  () => {
    if (!map) return;
    syncDualMarkers();
    syncLegacyMarker();
  },
  { deep: true }
);

defineExpose({
  flyTo,
  locateUser,
  setRouteData,
  setRouteCoordinates,
  clearRoute,
  fitBounds,
  map: () => map,
});
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
