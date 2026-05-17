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

const emit = defineEmits(["mapClick", "load"]);

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
  /** Bật click trên map để lấy tọa độ */
  enableClick: {
    type: Boolean,
    default: false,
  },
  /** Danh sách sự cố cần hiển thị marker (chỉ hiển thị, không phá vỡ chức năng có sẵn) */
  incidents: {
    type: Array,
    default: () => [],
  },
  /** Tự fit bounds theo danh sách incidents */
  fitToIncidents: {
    type: Boolean,
    default: true,
  },
});

const containerEl = ref(null);
const hasToken = ref(!!import.meta.env.VITE_OPENMAP_API_KEY);

let map = null;
let marker = null;
let incidentMarkers = [];

function statusColor(status) {
  const s = String(status || "").trim().toUpperCase().replace(/\s+/g, "_");
  if (s === "CHO_XU_LY" || s === "PENDING" || s === "MOI") return "#0ea5e9";
  if (s === "DANG_XU_LY" || s === "PROCESSING") return "#f59e0b";
  if (s === "HOAN_THANH" || s === "COMPLETED" || s === "DONE") return "#22c55e";
  if (s === "HUY_BO" || s === "CANCELLED" || s === "CANCELED") return "#94a3b8";
  return "#ef4444";
}

function escapeHtml(value) {
  if (value === null || value === undefined) return "";
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

function clearIncidentMarkers() {
  incidentMarkers.forEach((m) => m.remove());
  incidentMarkers = [];
}

function renderIncidentMarkers() {
  if (!map) return;
  clearIncidentMarkers();
  const list = Array.isArray(props.incidents) ? props.incidents : [];
  const valid = list.filter(
    (i) => i && i.lat != null && i.lng != null && !Number.isNaN(parseFloat(i.lat)) && !Number.isNaN(parseFloat(i.lng))
  );
  valid.forEach((i) => {
    const color = statusColor(i.status);
    const el = document.createElement("div");
    el.className = "mapbox-incident-marker";
    el.style.background = color;
    el.title = i.type || "";
    const m = new maplibregl.Marker({ element: el })
      .setLngLat([parseFloat(i.lng), parseFloat(i.lat)])
      .addTo(map);
    const popupHtml = `
      <div class="incident-popup">
        <div class="incident-popup-title">#${escapeHtml(i.id)} • ${escapeHtml(i.type || "Sự cố")}</div>
        <div class="incident-popup-row"><i class="fa-solid fa-location-dot"></i> ${escapeHtml(i.address || "—")}</div>
        ${i.severityLabel ? `<div class="incident-popup-row"><span class="incident-popup-badge" style="background:${color}">${escapeHtml(i.severityLabel)}</span></div>` : ""}
        ${i.time ? `<div class="incident-popup-row text-muted"><i class="fa-solid fa-clock"></i> ${escapeHtml(i.time)}</div>` : ""}
      </div>
    `;
    const popup = new maplibregl.Popup({ offset: 14, closeButton: false }).setHTML(popupHtml);
    m.setPopup(popup);
    incidentMarkers.push(m);
  });

  if (props.fitToIncidents && valid.length > 1) {
    const bounds = new maplibregl.LngLatBounds();
    valid.forEach((i) => bounds.extend([parseFloat(i.lng), parseFloat(i.lat)]));
    try {
      map.fitBounds(bounds, { padding: 40, maxZoom: 14, duration: 400 });
    } catch (_) { /* ignore */ }
  } else if (props.fitToIncidents && valid.length === 1) {
    map.easeTo({ center: [parseFloat(valid[0].lng), parseFloat(valid[0].lat)], duration: 400 });
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

  // Xử lý click trên map
  if (props.enableClick) {
    map.on("click", (e) => {
      const { lng, lat } = e.lngLat;
      setMarkerPosition(lng, lat);
      emit("mapClick", { lng, lat });
    });
  }

  if (props.showMarker) {
    marker = new maplibregl.Marker({ color: "#dc3545" })
      .setLngLat(props.center)
      .addTo(map);
  }

  map.on("load", () => {
    map.resize();
    renderIncidentMarkers();
    emit("load");
  });

  map.on("styleimagemissing", (e) => {
    const id = e.id;
    if (id && !map.hasImage(id)) {
      const size = 20;
      const data = new Uint8Array(size * size * 4);
      for (let i = 0; i < data.length; i++) data[i] = 200;
      map.addImage(id, { width: size, height: size, data }, { pixelRatio: 1 });
    }
  });
}

function flyTo(lng, lat, zoom = 15) {
  if (!map) return;
  map.flyTo({ center: [lng, lat], zoom, essential: true });
  setMarkerPosition(lng, lat);
}

function setMarkerPosition(lng, lat) {
  if (!marker) {
    marker = new maplibregl.Marker({ color: "#dc3545" })
      .setLngLat([lng, lat])
      .addTo(map);
  } else {
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
  clearIncidentMarkers();
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

watch(
  () => props.incidents,
  () => {
    if (map && map.isStyleLoaded()) {
      renderIncidentMarkers();
    } else if (map) {
      map.once("load", () => renderIncidentMarkers());
    }
  },
  { deep: true }
);

defineExpose({ flyTo, locateUser, setMarkerPosition, map: () => map });
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

<style>
.mapbox-incident-marker {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid #ffffff;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.25), 0 2px 6px rgba(0, 0, 0, 0.25);
  cursor: pointer;
  transition: transform 0.15s ease;
}
.mapbox-incident-marker:hover {
  transform: scale(1.25);
}
.maplibregl-popup .incident-popup {
  font-size: 12px;
  min-width: 180px;
  max-width: 240px;
}
.maplibregl-popup .incident-popup-title {
  font-weight: 700;
  color: #111827;
  margin-bottom: 4px;
  font-size: 12.5px;
}
.maplibregl-popup .incident-popup-row {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 2px;
  color: #374151;
  line-height: 1.35;
}
.maplibregl-popup .incident-popup-row.text-muted {
  color: #6b7280;
}
.maplibregl-popup .incident-popup-row i {
  width: 12px;
  text-align: center;
  color: #6b7280;
}
.maplibregl-popup .incident-popup-badge {
  display: inline-block;
  padding: 1px 8px;
  border-radius: 999px;
  color: #fff;
  font-weight: 600;
  font-size: 10.5px;
}
</style>
