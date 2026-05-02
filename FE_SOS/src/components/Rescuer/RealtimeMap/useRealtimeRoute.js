import { useRef } from 'react';
import L from 'leaflet'; // Fallback to Leaflet if they explicitly want L.map

export function useRealtimeRoute() {
    const banDoRef = useRef(null);
    const duongDiRef = useRef(null);
    const markerCuuHoRef = useRef(null);
    const markerYeuCauRef = useRef(null);

    const viTriCuuHoCu = useRef(null);
    const thoiGianCapNhatCu = useRef(0);

    const GOOGLE_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY || '';

    // 4. GOOGLE ROUTING (DIRECTIONS API)
    async function layDuongDi(tuViTri, denViTri) {
        try {
            const url = `https://maps.googleapis.com/maps/api/directions/json?origin=${tuViTri.lat},${tuViTri.lng}&destination=${denViTri.lat},${denViTri.lng}&mode=driving&key=${GOOGLE_API_KEY}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.routes && data.routes.length > 0) {
                return data.routes[0].overview_polyline.points;
            }
            return null;
        } catch (error) {
            console.error('Lỗi lấy đường đi:', error);
            return null;
        }
    }

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

            poly.push([lat / 1e5, lng / 1e5]); // Leaflet uses [lat, lng]
        }
        return poly;
    }

    function tinhKhoangCach(pos1, pos2) {
        const R = 6371e3; // metres
        const lat1 = pos1.lat * Math.PI / 180;
        const lat2 = pos2.lat * Math.PI / 180;
        const dLat = (pos2.lat - pos1.lat) * Math.PI / 180;
        const dLng = (pos2.lng - pos1.lng) * Math.PI / 180;

        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1) * Math.cos(lat2) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    // 7. REALTIME UPDATE
    function kiemTraCanCapNhatDuong(viTriMoi) {
        const now = Date.now();

        if (!viTriCuuHoCu.current) {
            viTriCuuHoCu.current = viTriMoi;
            thoiGianCapNhatCu.current = now;
            return true;
        }

        const distance = tinhKhoangCach(viTriCuuHoCu.current, viTriMoi);
        const timeElapsed = (now - thoiGianCapNhatCu.current) / 1000;

        // moved > 30m OR last update > 10s
        if (distance > 30 || timeElapsed > 10) {
            viTriCuuHoCu.current = viTriMoi;
            thoiGianCapNhatCu.current = now;
            return true;
        }

        return false;
    }

    async function capNhatDuongDi(viTriMoi, viTriYeuCau) {
        if (!viTriYeuCau) return;
        
        const duongEncoded = await layDuongDi(viTriMoi, viTriYeuCau);
        if (!duongEncoded) return;

        const dsToaDo = giaiMaPolyline(duongEncoded);

        if (duongDiRef.current) {
            duongDiRef.current.remove();
        }

        if (banDoRef.current && L.polyline) {
            // 6. DRAW ROUTE
            duongDiRef.current = L.polyline(dsToaDo, { color: '#3b82f6', weight: 5 }).addTo(banDoRef.current);
        }
    }

    function capNhatViTriCuuHo(viTriMoi, viTriYeuCau) {
        if (markerCuuHoRef.current) {
            markerCuuHoRef.current.setLatLng([viTriMoi.lat, viTriMoi.lng]);
        }

        if (kiemTraCanCapNhatDuong(viTriMoi)) {
            capNhatDuongDi(viTriMoi, viTriYeuCau);
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
        capNhatViTriCuuHo
    };
}
