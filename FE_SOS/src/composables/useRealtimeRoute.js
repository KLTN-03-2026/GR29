import { ref } from 'vue';
import maplibregl from '@openmapvn/openmapvn-gl';
import { createOpenMapMarker, createOpenMapPopup } from '../utils/openMap';
import { loadGoogleMaps } from '../utils/googleMaps';

export function useRealtimeRoute() {
    const banDoRef = ref(null);
    const duongDiRef = ref(null);
    const markerCuuHoRef = ref(null);
    const markerYeuCauRef = ref(null);
    const viTriCuuHoCu = ref(null);
    const lastUpdateDuongDi = ref(0);
    
    const GOOGLE_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY || '';

    // 5. DECODE POLYLINE
    function giaiMaPolyline(encoded) {
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

            // MapLibre needs [lng, lat]
            poly.push([lng / 1e5, lat / 1e5]);
        }
        return poly;
    }

    // 4. GOOGLE ROUTING (DIRECTIONS API REST)
    async function layDuongDi(tuViTri, denViTri) {
        if (!tuViTri || !denViTri) return null;
        if (!GOOGLE_API_KEY) {
            console.error('VITE_GOOGLE_MAPS_API_KEY chua duoc cau hinh');
            return null;
        }

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
                return result.routes[0]?.overview_polyline?.points || null;
            }
        } catch (error) {
            console.warn('Google Maps JS DirectionsService failed. Fallback to REST API.', error);
        }

        try {
            const url = `https://maps.googleapis.com/maps/api/directions/json?origin=${tuViTri.lat},${tuViTri.lng}&destination=${denViTri.lat},${denViTri.lng}&mode=driving&key=${GOOGLE_API_KEY}`;
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.routes && data.routes.length > 0) {
                return data.routes[0].overview_polyline.points;
            }
            console.warn('Google Directions REST returned no routes:', data);
            return null;
        } catch (error) {
            console.error('Lỗi khi lấy đường đi Google:', error);
            return null;
        }
    }

    // Khoảng cách Haversine tính bằng mét
    function tinhKhoangCach(pos1, pos2) {
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

    function kiemTraCanCapNhatDuong(viTriMoi) {
        const now = Date.now();
        
        if (!viTriCuuHoCu.current) {
            viTriCuuHoCu.current = viTriMoi;
            return true;
        }
        
        const khoangCach = tinhKhoangCach(viTriCuuHoCu.current, viTriMoi);
        const thoiGianTroiQua = (now - lastUpdateDuongDi.value) / 1000;

        // moved > 30m OR last update > 10s
        if (khoangCach > 30 || thoiGianTroiQua > 10) {
            viTriCuuHoCu.current = viTriMoi;
            return true;
        }
        return false;
    }

    async function capNhatDuongDi(viTriMoi, viTriYeuCau) {
        if (!viTriYeuCau) return;
        
        const duongEncoded = await layDuongDi(viTriMoi, viTriYeuCau);
        if (!duongEncoded) return;
        
        const dsToaDo = giaiMaPolyline(duongEncoded);
        
        if (!banDoRef.value) return;
        
        const geojson = {
            type: 'Feature',
            properties: {},
            geometry: {
                type: 'LineString',
                coordinates: dsToaDo
            }
        };

        const map = banDoRef.value;
        if (map.getSource('duong-di')) {
            map.getSource('duong-di').setData(geojson);
        } else {
            map.addSource('duong-di', {
                type: 'geojson',
                data: geojson
            });
            map.addLayer({
                id: 'duong-di-layer',
                type: 'line',
                source: 'duong-di',
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
        
        lastUpdateDuongDi.value = Date.now();
    }

    function capNhatViTriCuuHo(viTriMoi, viTriYeuCau) {
        if (!markerCuuHoRef.value && banDoRef.value) {
            markerCuuHoRef.value = createOpenMapMarker({
                type: "gps",
                position: { lng: viTriMoi.lng, lat: viTriMoi.lat },
                fillColor: '#2563eb',
                title: 'Vị trí của bạn',
            }).addTo(banDoRef.value);
        } else if (markerCuuHoRef.value) {
            markerCuuHoRef.value.setLngLat([viTriMoi.lng, viTriMoi.lat]);
        }

        if (kiemTraCanCapNhatDuong(viTriMoi)) {
            capNhatDuongDi(viTriMoi, viTriYeuCau);
        }
    }
    
    function capNhatViTriYeuCau(viTriYeuCau) {
        if (!markerYeuCauRef.value && banDoRef.value) {
            markerYeuCauRef.value = createOpenMapMarker({
                position: { lng: viTriYeuCau.lng, lat: viTriYeuCau.lat },
                fillColor: '#dc2626',
                label: { text: '!' },
                title: 'Nhiệm vụ',
            }).addTo(banDoRef.value);
        } else if (markerYeuCauRef.value) {
            markerYeuCauRef.value.setLngLat([viTriYeuCau.lng, viTriYeuCau.lat]);
        }
    }

    return {
        banDoRef,
        duongDiRef,
        markerCuuHoRef,
        markerYeuCauRef,
        layDuongDi,
        giaiMaPolyline,
        kiemTraCanCapNhatDuong,
        capNhatDuongDi,
        capNhatViTriCuuHo,
        capNhatViTriYeuCau
    };
}
