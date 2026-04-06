<template>
  <div class="mapbox-wrap position-relative w-100 h-100">
    <div
      v-if="!hasToken"
      class="mapbox-fallback d-flex align-items-center justify-content-center bg-dark text-white-50 small p-4 text-center"
    >
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

const HEATMAP_SOURCE_ID = "codex-heatmap-source";
const HEATMAP_LAYER_ID = "codex-heatmap-layer";
const POINT_LAYER_ID = "codex-heatmap-points";

const props = defineProps({
  center: {
    type: Array,
    default: () => [108.2022, 16.0544],
  },
  zoom: {
    type: Number,
    default: 12,
  },
  showMarker: {
    type: Boolean,
    default: true,
  },
  mapStyle: {
    type: String,
    default: "day-v1",
  },
  points: {
    type: Array,
    default: () => [],
  },
  useHeatmap: {
    type: Boolean,
    default: false,
  },
  fitToPoints: {
    type: Boolean,
    default: false,
  },
});

const containerEl = ref(null);
const hasToken = ref(!!import.meta.env.VITE_OPENMAP_API_KEY);

let map = null;
let marker = null;
let popup = null;

function getIntensityLevel(count) {
  if (count >= 15) return "Cực cao";
  if (count >= 11) return "Rất cao";
  if (count >= 8) return "Cao";
  if (count >= 5) return "Trung bình";
  if (count >= 3) return "Thấp";
  return "Rất thấp";
}

function buildFeatureCollection(points = []) {
  return {
    type: "FeatureCollection",
    features: points
      .filter((point) => Number.isFinite(Number(point?.lng)) && Number.isFinite(Number(point?.lat)))
      .map((point) => ({
        type: "Feature",
        geometry: {
          type: "Point",
          coordinates: [Number(point.lng), Number(point.lat)],
        },
        properties: {
          weight: Number(point.weight || point.count || 1),
          title: point.title || "",
          description: point.description || "",
          color: point.color || "#ef4444",
          count: Number(point.count || point.weight || 1),
        },
      })),
  };
}

function ensureMarker() {
  if (!map) return;
  if (props.showMarker) {
    if (!marker) {
      marker = new maplibregl.Marker({ color: "#dc3545" })
        .setLngLat(props.center)
        .addTo(map);
    }
    marker.setLngLat(props.center);
  } else if (marker) {
    marker.remove();
    marker = null;
  }
}

function ensureHeatmapLayers() {
  if (!map || !map.isStyleLoaded()) return;
  const data = buildFeatureCollection(props.points);

  if (!map.getSource(HEATMAP_SOURCE_ID)) {
    map.addSource(HEATMAP_SOURCE_ID, {
      type: "geojson",
      data,
    });
  } else {
    map.getSource(HEATMAP_SOURCE_ID).setData(data);
  }

  if (props.useHeatmap) {
    if (!map.getLayer(HEATMAP_LAYER_ID)) {
      map.addLayer({
        id: HEATMAP_LAYER_ID,
        type: "heatmap",
        source: HEATMAP_SOURCE_ID,
        maxzoom: 17,
        paint: {
          "heatmap-weight": [
            "interpolate",
            ["linear"],
            ["get", "weight"],
            0,
            0,
            20,
            1,
          ],
          "heatmap-intensity": [
            "interpolate",
            ["linear"],
            ["zoom"],
            0,
            1.0,
            8,
            1.3,
            12,
            1.8,
            17,
            2.5,
          ],
          "heatmap-color": [
            "interpolate",
            ["linear"],
            ["heatmap-density"],
            0,
            "rgba(59,130,246,0)",
            0.1,
            "rgba(59,130,246,0.4)",
            0.2,
            "rgba(16,185,129,0.6)",
            0.3,
            "rgba(245,158,11,0.7)",
            0.4,
            "rgba(249,115,22,0.75)",
            0.5,
            "rgba(239,68,68,0.8)",
            0.6,
            "rgba(220,38,38,0.85)",
            0.7,
            "rgba(185,28,28,0.9)",
            0.8,
            "rgba(153,27,27,0.95)",
            0.9,
            "rgba(127,29,29,0.98)",
            1,
            "rgba(69,10,10,1.0)",
          ],
          "heatmap-radius": [
            "interpolate",
            ["linear"],
            ["zoom"],
            0,
            20,
            8,
            40,
            12,
            55,
            17,
            80,
          ],
          "heatmap-opacity": 1.0,
        },
      });
    }
  } else if (map.getLayer(HEATMAP_LAYER_ID)) {
    map.removeLayer(HEATMAP_LAYER_ID);
  }

  if (!map.getLayer(POINT_LAYER_ID)) {
    map.addLayer({
      id: POINT_LAYER_ID,
      type: "circle",
      source: HEATMAP_SOURCE_ID,
      paint: {
        "circle-radius": [
          "interpolate",
          ["linear"],
          ["get", "weight"],
          1,
          8,
          5,
          12,
          10,
          16,
          15,
          20,
          20,
          25,
        ],
        "circle-color": [
          "coalesce",
          ["get", "color"],
          "#ef4444",
        ],
        "circle-stroke-width": 1.5,
        "circle-stroke-color": "#ffffff",
        "circle-opacity": props.useHeatmap ? 0.55 : 0.85,
      },
    });
  }
}

function fitBoundsToPoints() {
  if (!map || !props.fitToPoints || !props.points.length) return;
  const validPoints = props.points.filter(
    (point) => Number.isFinite(Number(point?.lng)) && Number.isFinite(Number(point?.lat))
  );
  if (!validPoints.length) return;
  if (validPoints.length === 1) {
    map.flyTo({
      center: [Number(validPoints[0].lng), Number(validPoints[0].lat)],
      zoom: Math.max(props.zoom, 13),
      essential: true,
    });
    return;
  }
  const bounds = new maplibregl.LngLatBounds(
    [Number(validPoints[0].lng), Number(validPoints[0].lat)],
    [Number(validPoints[0].lng), Number(validPoints[0].lat)]
  );
  validPoints.forEach((point) => bounds.extend([Number(point.lng), Number(point.lat)]));
  map.fitBounds(bounds, { padding: 40, maxZoom: 14, duration: 800 });
}

function bindPointPopup() {
  if (!map) return;

  map.on("click", POINT_LAYER_ID, (event) => {
    const feature = event.features?.[0];
    if (!feature) return;

    const coordinates = feature.geometry.coordinates.slice();
    const title = feature.properties?.title || "Điểm cảnh báo";
    const description = feature.properties?.description || "Không có mô tả";
    const count = feature.properties?.count || 1;

    popup?.remove();
    popup = new maplibregl.Popup({ closeButton: true, closeOnClick: true })
      .setLngLat(coordinates)
      .setHTML(`
        <div style="min-width:180px">
          <div style="font-weight:600; margin-bottom:6px;">${title}</div>
          <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">${description}</div>
          <div style="font-size:12px;">Mật độ: <strong>${count}</strong> yêu cầu</div>
          <div style="font-size:11px; color:#6b7280; margin-top:2px;">Cấp độ: ${getIntensityLevel(count)}</div>
        </div>
      `)
      .addTo(map);
    map.flyTo({ center: coordinates, zoom: Math.max(map.getZoom(), 13), essential: true });
  });

  map.on("mouseenter", POINT_LAYER_ID, () => {
    map.getCanvas().style.cursor = "pointer";
  });

  map.on("mouseleave", POINT_LAYER_ID, () => {
    map.getCanvas().style.cursor = "";
  });
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

  ensureMarker();

  map.on("load", () => {
    ensureHeatmapLayers();
    fitBoundsToPoints();
    bindPointPopup();
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

function onResize() {
  map?.resize();
}

onMounted(() => {
  initMap();
  window.addEventListener("resize", onResize);
});

onUnmounted(() => {
  window.removeEventListener("resize", onResize);
  popup?.remove();
  popup = null;
  marker?.remove();
  marker = null;
  map?.remove();
  map = null;
});

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
  () => props.showMarker,
  () => {
    ensureMarker();
  }
);

watch(
  () => props.points,
  () => {
    if (!map || !map.isStyleLoaded()) return;
    ensureHeatmapLayers();
    fitBoundsToPoints();
  },
  { deep: true }
);

watch(
  () => props.useHeatmap,
  () => {
    if (!map || !map.isStyleLoaded()) return;
    ensureHeatmapLayers();
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
