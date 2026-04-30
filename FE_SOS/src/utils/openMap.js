import maplibregl from "@openmapvn/openmapvn-gl";
import "@openmapvn/openmapvn-gl/dist/maplibre-gl.css";

const API_KEY = import.meta.env.VITE_OPENMAP_API_KEY?.trim();
const STYLE_URL = `https://maptiles.openmap.vn/styles/day-v1/style.json?apikey=${API_KEY}`;

let mapLoadPromise = null;

export async function loadOpenMap() {
    if (mapLoadPromise) return mapLoadPromise;

    mapLoadPromise = new Promise((resolve, reject) => {
        if (typeof maplibregl !== "undefined") {
            resolve(maplibregl);
            return;
        }
        reject(new Error("OpenMap GL not available"));
    });

    return mapLoadPromise;
}

export function createOpenMap(container, options = {}) {
    const defaultCenter = { lat: 16.0544, lng: 108.2022 };
    let center = [defaultCenter.lng, defaultCenter.lat];
    if (Array.isArray(options.center) && options.center.length >= 2) {
        center = [Number(options.center[0]), Number(options.center[1])];
    } else if (options.center) {
        center = [
            Number(options.center.lng ?? defaultCenter.lng),
            Number(options.center.lat ?? defaultCenter.lat),
        ];
    }

    const map = new maplibregl.Map({
        container,
        style: STYLE_URL,
        center,
        zoom: options.zoom ?? 14,
    });

    map.addControl(new maplibregl.NavigationControl(), "top-right");
    map.on("load", () => {
        map.resize();
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

    return map;
}

export function createOpenMapMarker(options = {}) {
    const color = options.fillColor || "#dc2626";
    const el = document.createElement("div");

    if (options.type === "home") {
        el.style.width = "36px";
        el.style.height = "36px";
        el.style.borderRadius = "50% 50% 0 0";
        el.style.backgroundColor = "#2563eb";
        el.style.border = "3px solid #ffffff";
        el.style.boxShadow = "0 3px 8px rgba(0,0,0,0.3)";
        el.style.display = "flex";
        el.style.alignItems = "center";
        el.style.justifyContent = "center";
        el.style.cursor = "pointer";
        el.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="1">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>`;
    } else if (options.type === "gps") {
        el.style.width = "20px";
        el.style.height = "20px";
        el.style.borderRadius = "50%";
        el.style.backgroundColor = color;
        el.style.border = "3px solid #ffffff";
        el.style.boxShadow = "0 2px 6px rgba(0,0,0,0.3)";
        el.style.cursor = "pointer";
    } else {
        el.style.width = "28px";
        el.style.height = "28px";
        el.style.borderRadius = "50%";
        el.style.backgroundColor = color;
        el.style.border = "3px solid #ffffff";
        el.style.boxShadow = "0 2px 6px rgba(0,0,0,0.3)";
        el.style.display = "flex";
        el.style.alignItems = "center";
        el.style.justifyContent = "center";
        el.style.fontSize = "14px";
        el.style.fontWeight = "700";
        el.style.color = "#ffffff";
        el.style.cursor = "pointer";
        if (options.label) {
            el.textContent = options.label.text || "!";
        }
    }

    const marker = new maplibregl.Marker({
        element: el,
        anchor: options.anchor || "center",
    });

    if (options.position) {
        const pos = options.position;
        const lng = typeof pos.lng === "number" ? pos.lng : pos[0];
        const lat = typeof pos.lat === "number" ? pos.lat : pos[1];
        marker.setLngLat([lng, lat]);
    }

    if (options.title) {
        el.title = options.title;
    }

    return marker;
}

export function createOpenMapPopup(options = {}) {
    return new maplibregl.Popup({
        closeButton: true,
        closeOnClick: false,
        offset: 15,
        ...options,
    });
}

export function createOpenMapBounds() {
    return new maplibregl.LngLatBounds();
}

export function fitBoundsToMap(map, coordinates) {
    if (!coordinates || coordinates.length === 0) return;
    const bounds = new maplibregl.LngLatBounds();
    coordinates.forEach((coord) => {
        const lng = typeof coord.lng === "number" ? coord.lng : coord.lon ?? coord[0];
        const lat = typeof coord.lat === "number" ? coord.lat : coord[1];
        bounds.extend([lng, lat]);
    });
    map.fitBounds(bounds, { padding: 50 });
}
