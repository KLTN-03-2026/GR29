import React, { useEffect, useState } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { useRealtimeRoute } from './useRealtimeRoute';

const API_KEY = import.meta.env.VITE_OPENMAP_API_KEY || '';
const STYLE_URL = `https://maptiles.openmap.vn/styles/day-v1/style.json?apikey=${API_KEY}`;

export default function RescuerMap({ viTriBanDau, viTriYeuCau, isTracking }) {
    const {
        banDoRef,
        duongDiRef,
        markerCuuHoRef,
        markerYeuCauRef,
        capNhatViTriCuuHo
    } = useRealtimeRoute();

    // 2. MAP CORE - Initialize EXACTLY ONCE
    useEffect(() => {
        if (banDoRef.current) return; // Prevent re-render blank map

        banDoRef.current = L.map('rescuer-map', {
            center: [viTriBanDau?.lat || 16.0544, viTriBanDau?.lng || 108.2022],
            zoom: 15
        });

        // ONLY use OpenMap tile layer
        L.tileLayer(STYLE_URL, {
            attribution: '&copy; OpenMap'
        }).addTo(banDoRef.current);

        // 3. MARKERS - Created once
        if (viTriBanDau) {
            markerCuuHoRef.current = L.marker([viTriBanDau.lat, viTriBanDau.lng], { title: 'Cứu hộ' })
                .addTo(banDoRef.current);
        }

        if (viTriYeuCau) {
            markerYeuCauRef.current = L.marker([viTriYeuCau.lat, viTriYeuCau.lng], { title: 'Yêu cầu' })
                .addTo(banDoRef.current);
        }

        // Initial Route
        if (viTriBanDau && viTriYeuCau) {
            capNhatViTriCuuHo(viTriBanDau, viTriYeuCau);
        }

        return () => {
            // Cleanup if component unmounts entirely
            if (banDoRef.current) {
                banDoRef.current.remove();
                banDoRef.current = null;
            }
        };
    }, []); // Empty dependency array ensures it runs exactly once

    // 7. REALTIME UPDATE (REVERB) - Example integration
    useEffect(() => {
        if (!isTracking || !window.Echo) return;

        const channel = window.Echo.channel(`rescuer-tracking`);
        
        channel.listen('.LocationUpdated', (e) => {
            // Expected e.location = { lat, lng }
            if (e.location) {
                // This updates marker and route without re-rendering map
                capNhatViTriCuuHo(e.location, viTriYeuCau);
            }
        });

        return () => {
            channel.stopListening('.LocationUpdated');
            window.Echo.leave(`rescuer-tracking`);
        };
    }, [isTracking, viTriYeuCau]);

    // DO NOT return changing states that re-render the map container
    return (
        <div 
            id="rescuer-map" 
            style={{ width: '100%', height: '500px', borderRadius: '12px' }}
        />
    );
}
