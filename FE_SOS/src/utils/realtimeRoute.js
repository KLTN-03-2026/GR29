import { loadGoogleMaps } from "./googleMaps.js";

const GOOGLE_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY?.trim() || '';

// Decode Google-encoded polyline
function giaiMaPolylineGoogle(encoded) {
    if (!encoded) return [];
    const poly = [];
    let index = 0, len = encoded.length;
    let lat = 0, lng = 0;
    while (index < len) {
        let b, shift = 0, result = 0;
        do {
            b = encoded.charCodeAt(index++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);
        const dlat = ((result & 1) !== 0 ? ~(result >> 1) : (result >> 1));
        lat += dlat;
        shift = 0;
        result = 0;
        do {
            b = encoded.charCodeAt(index++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);
        const dlng = ((result & 1) !== 0 ? ~(result >> 1) : (result >> 1));
        lng += dlng;
        // MapLibre uses [lng, lat]
        poly.push([lng / 1e5, lat / 1e5]);
    }
    return poly;
}

// Decode polyline — kept for backward compat
export function giaiMaPolyline(encoded) {
    return giaiMaPolylineGoogle(encoded);
}

// OSRM FALLBACK — works without API key
async function layDuongDiOSRM(tuViTri, denViTri) {
    if (!tuViTri || !denViTri) return null;
    try {
        const response = await fetch(
            `https://router.project-osrm.org/route/v1/driving/${Number(tuViTri.lng)},${Number(tuViTri.lat)};${Number(denViTri.lng)},${Number(denViTri.lat)}?overview=full&geometries=geojson`
        );
        const data = await response.json();
        if (data.routes?.length > 0) {
            // OSRM returns GeoJSON directly — no decoding needed
            return { type: 'geojson', coordinates: data.routes[0].geometry.coordinates };
        }
        return null;
    } catch (e) {
        return null;
    }
}

// 4. ROUTING (Google primary, OSRM fallback)
export async function layDuongDi(tuViTri, denViTri) {
    if (!tuViTri || !denViTri) return null;

    // Try Google first if key is available
    if (GOOGLE_API_KEY) {
        // Try using Google Maps JS DirectionsService first.
        try {
            const google = await loadGoogleMaps();
            if (google?.maps?.DirectionsService) {
                const service = new google.maps.DirectionsService();
                const request = {
                    origin: { lat: Number(tuViTri.lat), lng: Number(tuViTri.lng) },
                    destination: { lat: Number(denViTri.lat), lng: Number(denViTri.lng) },
                    travelMode: google.maps.TravelMode.DRIVING,
                };
                const result = await new Promise((resolve, reject) => {
                    service.route(request, (response, status) => {
                        if (status === 'OK' && response?.routes?.length > 0) {
                            resolve(response);
                        } else {
                            reject(new Error(status || 'DirectionsService failed'));
                        }
                    });
                });
                const encoded = result.routes[0]?.overview_polyline?.points;
                if (encoded) {
                    return { type: 'google', polyline: encoded };
                }
            }
        } catch (error) {
            console.warn('[Route] Google Maps JS failed. Fallback to REST API.', error);
        }

        // Try Google Directions REST API
        try {
            const url = `https://maps.googleapis.com/maps/api/directions/json?origin=${tuViTri.lat},${tuViTri.lng}&destination=${denViTri.lat},${denViTri.lng}&mode=driving&key=${GOOGLE_API_KEY}`;
            const response = await fetch(url);
            const data = await response.json();
            if (data.routes && data.routes.length > 0) {
                const encoded = data.routes[0].overview_polyline.points;
                if (encoded) {
                    return { type: 'google', polyline: encoded };
                }
            }
        } catch (error) {
            console.warn('[Route] Google Directions REST failed.', error);
        }
    } else {
        console.warn('[Route] VITE_GOOGLE_MAPS_API_KEY not configured — using OSRM fallback');
    }

    // OSRM fallback — no API key required
    const osrmResult = await layDuongDiOSRM(tuViTri, denViTri);
    if (osrmResult) {
        console.log('[Route] Route drawn via OSRM fallback');
        return osrmResult;
    }

    console.error('[Route] All routing providers failed');
    return null;
}

export function tinhKhoangCach(pos1, pos2) {
    const R = 6371e3;
    const lat1 = pos1.lat * Math.PI / 180;
    const lat2 = pos2.lat * Math.PI / 180;
    const deltaLat = (pos2.lat - pos1.lat) * Math.PI / 180;
    const deltaLng = (pos2.lng - pos1.lng) * Math.PI / 180;

    const a = Math.sin(deltaLat/2) * Math.sin(deltaLat/2) +
              Math.cos(lat1) * Math.cos(lat2) *
              Math.sin(deltaLng/2) * Math.sin(deltaLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

export function kiemTraCanCapNhatDuong(viTriMoi, viTriCu, lanCapNhatCuoi) {
    if (!viTriCu || !lanCapNhatCuoi) {
        return true;
    }

    const khoangCach = tinhKhoangCach(viTriCu, viTriMoi);
    const thoiGian = Date.now() - lanCapNhatCuoi;

    if (khoangCach < 2) {
        return false;
    }

    if (khoangCach > 5) {
        return true;
    }

    if (thoiGian > 3000) {
        return true;
    }

    return false;
}

export async function veDuongDiTrenMap(map, tuViTri, denViTri, layerId = 'duong-di-route') {
    if (!map || !tuViTri || !denViTri) return null;

    const result = await layDuongDi(tuViTri, denViTri);
    if (!result) {
        console.warn('[Route] veDuongDiTrenMap: khong lay duoc duong di');
        return null;
    }

    // Support both Google polyline (needs decoding) and OSRM GeoJSON (direct use)
    let dsToaDo;
    if (result.type === 'google') {
        dsToaDo = giaiMaPolylineGoogle(result.polyline);
    } else {
        dsToaDo = result.coordinates;
    }

    if (!dsToaDo || dsToaDo.length === 0) return null;

    const geojson = {
        type: 'Feature',
        properties: {},
        geometry: {
            type: 'LineString',
            coordinates: dsToaDo
        }
    };

    if (map.getSource(layerId)) {
        map.getSource(layerId).setData(geojson);
    } else {
        map.addSource(layerId, {
            type: 'geojson',
            data: geojson
        });
        map.addLayer({
            id: `${layerId}-line`,
            type: 'line',
            source: layerId,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': '#3b82f6',
                'line-width': 5
            }
        });
    }
    return dsToaDo;
}
