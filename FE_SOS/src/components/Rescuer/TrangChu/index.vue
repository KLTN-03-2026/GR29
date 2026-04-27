<template>
    <div class="rescuer-home">
        <!-- Top Status Bar -->
        <div class="status-bar">
            <div class="status-bar-inner">
                <div class="status-brand">
                    <div class="brand-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div class="brand-text">
                        <span class="brand-name">SOS Rescuer</span>
                        <span class="brand-sub">Điều phối cứu hộ</span>
                    </div>
                </div>

                <div class="status-indicators">
                    <div class="status-chip" :class="hasActiveRequest ? 'chip-warning' : 'chip-success'">
                        <span class="chip-dot"></span>
                        <span>{{ hasActiveRequest ? 'Đang xử lý' : 'Sẵn sàng' }}</span>
                    </div>
                    <div class="mission-count">
                        <span class="count-num text-danger fs-2">{{ radarAssignments.length }}</span>
                        <span class="count-label">nhiệm vụ đang chờ</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Mission Banner -->
        <div v-if="hasActiveRequest" class="active-banner" @click="$router.push('/rescuer/dang-xu-ly')">
            <div class="banner-left">
                <div class="pulse-ring"></div>
                <div class="banner-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
            </div>
            <div class="banner-center">
                <span class="banner-title">Bạn đang có nhiệm vụ đang xử lý</span>
                <span class="banner-sub">Nhấn để xem chi tiết</span>
            </div>
            <div class="banner-right">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </div>
        </div>

        <!-- Main Layout -->
        <div class="main-layout">
            <!-- Sidebar -->
            <div class="sidebar" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
                <!-- Sidebar Header -->
                <div class="sidebar-header">
                    <div class="filter-tabs">
                        <button class="filter-tab" :class="{ active: activeTab === 'all' }" @click="activeTab = 'all'">
                            Tất cả
                            <span class="tab-count">{{ radarAssignments.length }}</span>
                        </button>
                        <button class="filter-tab filter-tab-danger" :class="{ active: activeTab === 'critical' }" @click="activeTab = 'critical'">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                            Khẩn cấp
                            <span class="tab-count">{{ criticalCount }}</span>
                        </button>
                    </div>

                    <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path v-if="sidebarCollapsed" d="M9 18l6-6-6-6"/>
                            <path v-else d="M15 18l-6-6 6-6"/>
                        </svg>
                    </button>
                </div>

                <!-- Mission List -->
                <div class="mission-list" ref="missionListEl">
                    <!-- Loading -->
                    <div v-if="loading" class="list-state">
                        <div class="skeleton-card" v-for="i in 3" :key="i">
                            <div class="skeleton-line skeleton-title"></div>
                            <div class="skeleton-line skeleton-short"></div>
                            <div class="skeleton-line skeleton-medium"></div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-else-if="displayAssignments.length === 0" class="list-state list-empty">
                        <div class="empty-illustration">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                                <line x1="9" y1="9" x2="9.01" y2="9"/>
                                <line x1="15" y1="9" x2="15.01" y2="9"/>
                            </svg>
                        </div>
                        <h6 class="empty-title">Không có nhiệm vụ</h6>
                        <p class="empty-sub">Hệ thống sẽ thông báo khi có nhiệm vụ mới</p>
                    </div>

                    <!-- Cards -->
                    <div v-else class="cards-container">
                        <div
                            v-for="item in displayAssignments"
                            :key="item.id_phan_cong"
                            class="mission-card"
                            :class="[
                                getCardSeverityClass(item),
                                selectedMission && selectedMission.id_phan_cong === item.id_phan_cong ? 'card-selected' : ''
                            ]"
                            @click="selectMission(item)"
                        >
                            <div class="card-top">
                                <div class="card-urgency" :class="getUrgencyDotClass(item)">
                                    <span class="urgency-dot"></span>
                                    <span class="urgency-label">{{ getSeverityLabel(item) }}</span>
                                </div>
                                <div class="card-id">#{{ item.yeu_cau?.id_yeu_cau || '-' }}</div>
                            </div>

                            <!-- <h5 class="card-title">{{ item.yeu_cau?.chi_tiet || getIncidentTypeName(item) }}</h5> -->

                            <div class="card-type-badge">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                </svg>
                                {{ getIncidentTypeName(item) }}
                            </div>

                            <div class="card-info-grid">
                                <div class="info-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span>{{ getRequestAddress(item) }}</span>
                                </div>
                                <div class="info-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span>{{ getReporterName(item) }}</span>
                                </div>
                                <div class="info-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span>NGƯỜI ĐIỀU PHỐI: </span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="footer-left">
                                    
                                    <div class="footer-chip footer-chip-time">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <polyline points="12 6 12 12 16 14"/>
                                        </svg>
                                        {{ item.created_at ? formatTime(item.created_at) : '-' }}
                                    </div>
                                </div>
                                <button
                                    v-if="item.trang_thai_nhiem_vu === 'MOI'"
                                    class="card-action"
                                    :class="{ 'action-disabled': hasActiveRequest }"
                                    :disabled="hasActiveRequest"
                                    @click.stop="acceptMission(item)"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Tiếp nhận
                                </button>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Area -->
            <div class="map-area">
                <div ref="mapContainer" class="map-canvas"></div>

                <!-- Map Loading / Error -->
                <div v-if="mapLoading || mapError" class="map-overlay">
                    <div class="map-overlay-card">
                        <template v-if="mapLoading">
                            <div class="overlay-spinner"></div>
                            <div class="overlay-title">Đang tải bản đồ</div>
                            <div class="overlay-sub">Vui lòng đợi trong giây lát...</div>
                        </template>
                        <template v-else>
                            <div class="overlay-icon overlay-icon-error">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                            </div>
                            <div class="overlay-title overlay-title-error">Không tải được bản đồ</div>
                            <div class="overlay-sub">{{ mapError }}</div>
                            <button class="overlay-btn" @click="retryMap">Thử lại</button>
                        </template>
                    </div>
                </div>

                <!-- Selected Mission Card (floating on map) -->
                <div v-if="selectedMission" class="map-detail-card" :class="{ 'card-visible': selectedMission }">
                    <div class="detail-card-header" :class="getDetailHeaderClass(selectedMission)">
                        <div class="detail-urgency">
                            <span class="urgency-dot"></span>
                            <span>{{ getSeverityLabel(selectedMission) }}</span>
                        </div>
                        <div class="detail-id">#{{ selectedMission.yeu_cau?.id_yeu_cau || '-' }}</div>
                        <button class="detail-close" @click="selectedMission = null">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <div class="detail-card-body">
                        

                        <div class="detail-badges">
                            <div class="badge-chip badge-chip-type">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                </svg>
                                {{ getIncidentTypeName(selectedMission) }}
                            </div>
                            <div class="detail-row" style="margin-top: 8px; margin-bottom: 4px;">
                                <div class="badge-container">
                                    <span v-for="(badge, index) in getIncidentBadges(selectedMission)" 
                                          :key="index" 
                                          class="incident-badge" 
                                          :title="badge">
                                        {{ badge }}
                                    </span>
                                </div>
                            </div>
                            <div class="badge-chip badge-chip-dispatch mt-1" v-if="selectedMission.nguoi_dieu_phoi">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                {{ selectedMission.nguoi_dieu_phoi }}
                            </div>
                        </div>

                        <div class="detail-info-list">
                            <div class="info-row">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span>{{ getRequestAddress(selectedMission) }}</span>
                            </div>
                            <div class="info-row" v-if="selectedMission.yeu_cau">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <span>{{ getReporterName(selectedMission) }}</span>
                                <span class="info-phone" v-if="selectedMission.yeu_cau.so_dien_thoai_nguoi_dung || getReporterPhone(selectedMission)">
                                    {{ selectedMission.yeu_cau.so_dien_thoai_nguoi_dung || getReporterPhone(selectedMission) }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-actions">
                            <a v-if="selectedMission.yeu_cau?.so_dien_thoai_nguoi_dung || getReporterPhone(selectedMission)"
                                :href="'tel:' + (selectedMission.yeu_cau.so_dien_thoai_nguoi_dung || getReporterPhone(selectedMission))"
                                class="action-btn action-btn-call">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.77 13.1 19.79 19.79 0 0 1 1.71 4.49 2 2 0 0 1 3.68 2.3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                Gọi ngay
                            </a>
                            <a v-if="getRequestAddress(selectedMission)"
                                :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(getRequestAddress(selectedMission))"
                                target="_blank"
                                class="action-btn action-btn-nav">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polygon points="3 11 22 2 13 21 11 13 3 11"/>
                                </svg>
                                Chỉ đường
                            </a>
                        </div>

                        <div class="detail-section" v-if="selectedMission.yeu_cau?.mo_ta">
                            <div class="detail-icon-label">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                                Mô tả
                            </div>

                            <div class="mb-3" v-if="selectedMission.doi_cuu_ho">
                                <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">ĐỘI
                                    CHỈ THỊ</div>
                                <div class="text-dark small fw-medium">{{ selectedMission.doi_cuu_ho.ten_co }}</div>
                            </div>

                        <button v-if="selectedMission.trang_thai_nhiem_vu === 'MOI'"
                            class="detail-action-btn"
                            :class="{ 'btn-disabled': hasActiveRequest }"
                            :disabled="hasActiveRequest"
                            @click="acceptMission(selectedMission)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Tiếp nhận nhiệm vụ
                        </button>
                        <button v-else class="detail-action-btn detail-action-btn-danger"
                            @click="goToMission(selectedMission)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polygon points="3 11 22 2 13 21 11 13 3 11"/>
                            </svg>
                            Xem chi tiết
                        </button>
                    </div>
                </div>

                <!-- Map Controls -->
                <div class="map-controls">
                    <button class="map-btn" @click="zoomIn" title="Phóng to">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                    <button class="map-btn" @click="zoomOut" title="Thu nhỏ">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                    <div class="map-btn-divider"></div>
                    <button class="map-btn map-btn-primary" @click="locateMe" title="Vị trí của tôi">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 2v4M12 18v4M2 12h4M18 12h4"/>
                        </svg>
                    </button>
                </div>

                <!-- Map Legend -->
                <div class="map-legend">
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#dc2626"></span>
                        <span>Khẩn cấp</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#f97316"></span>
                        <span>Cao</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#eab308"></span>
                        <span>Trung bình</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#22c55e"></span>
                        <span>Thường</span>
                    </div>
                    <div class="legend-divider"></div>
                    <div class="legend-item">
                        <span class="legend-icon legend-icon-home"></span>
                        <span>Trung tâm đội</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-icon legend-icon-gps"></span>
                        <span>Vị trí của bạn</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { rescuerAPI } from "../../../services/api.js";
import { loadOpenMap, createOpenMap, createOpenMapMarker, createOpenMapPopup, fitBoundsToMap } from "../../../utils/openMap.js";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

export default {
    name: "RescuerHome",
    data() {
        return {
            loading: false,
            assignments: [],
            selectedMission: null,
            activeTab: 'all',
            sidebarCollapsed: false,
            map: null,
            teamMarker: null,
            gpsMarker: null,
            requestMarkers: [],
            routeLayer: null,
            routeSource: null,
            teamId: null,
            teamLat: null,
            teamLng: null,
            memberLat: null,
            memberLng: null,
            hasActiveRequest: false,
            mapLoading: false,
            mapError: "",
            mapResizeObserver: null,
        };
    },
    computed: {
        radarAssignments() {
            const urgencyOrder = { 'CRITICAL': 4, 'HIGH': 3, 'MEDIUM': 2, 'LOW': 1 };
            const teamId = this.teamId;

            const processingYeuCauIds = new Set();
            this.assignments.forEach(a => {
                const st = (a.trang_thai_nhiem_vu || '').toUpperCase().replace(/\s+/g, '_');
                if (st === 'DANG_XU_LY' || st === 'DA_DEN_HIEN_TRUONG') {
                    if (a.yeu_cau?.id_yeu_cau) {
                        processingYeuCauIds.add(a.yeu_cau.id_yeu_cau);
                    }
                }
            });

            const filtered = this.assignments.filter(a => {
                if (teamId && Number(a.id_doi_cuu_ho) !== Number(teamId)) return false;
                const st = (a.trang_thai_nhiem_vu || '').toUpperCase().replace(/\s+/g, '_');
                if (st !== 'DA_PHAN_CONG' && st !== 'MOI') return false;
                const ycId = a.yeu_cau?.id_yeu_cau;
                if (ycId && processingYeuCauIds.has(ycId)) return false;
                return true;
            });

            const latestByYeuCau = new Map();
            filtered.forEach(a => {
                const key = a.yeu_cau?.id_yeu_cau;
                if (!key) return;
                const existing = latestByYeuCau.get(key);
                if (!existing || new Date(a.created_at || 0) > new Date(existing.created_at || 0)) {
                    latestByYeuCau.set(key, a);
                }
            });
            const deduped = Array.from(latestByYeuCau.values());

            deduped.sort((a, b) => {
                const sevA = (a.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
                const sevB = (b.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
                const urgA = urgencyOrder[sevA] ?? 0;
                const urgB = urgencyOrder[sevB] ?? 0;
                if (urgA !== urgB) return urgB - urgA;
                const timeA = new Date(a.created_at || 0).getTime();
                const timeB = new Date(b.created_at || 0).getTime();
                return timeA - timeB;
            });
            return deduped;
        },
        criticalCount() {
            return this.radarAssignments.filter(a => {
                const sev = a.yeu_cau?.muc_do_khan_cap?.toUpperCase() || '';
                return sev === 'CRITICAL' || sev === 'HIGH';
            }).length;
        },
        displayAssignments() {
            if (this.activeTab === 'critical') {
                return this.radarAssignments.filter(a => {
                    const sev = a.yeu_cau?.muc_do_khan_cap?.toUpperCase() || '';
                    return sev === 'CRITICAL' || sev === 'HIGH';
                });
            }
            return this.radarAssignments;
        },
    },
    async mounted() {
        this.loadTeamData();
        this.$nextTick(() => {
            this.initMap();
        });
        await this.fetchAssignments();
    },
    beforeUnmount() {
        this.disconnectMapObserver();
        this.cleanupRoute();
        if (this.teamMarker) { this.teamMarker.remove(); this.teamMarker = null; }
        if (this.gpsMarker) { this.gpsMarker.remove(); this.gpsMarker = null; }
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
    },
    methods: {
        disconnectMapObserver() {
            if (this.mapResizeObserver) {
                this.mapResizeObserver.disconnect();
                this.mapResizeObserver = null;
            }
        },
        async waitForMapContainer(attempts = 10) {
            const mapEl = this.$refs.mapContainer;
            if (!mapEl) return null;
            const rect = mapEl.getBoundingClientRect();
            if (rect.width > 0 && rect.height > 0) return mapEl;
            if (attempts <= 0) return mapEl;
            await new Promise(resolve => setTimeout(resolve, 150));
            return this.waitForMapContainer(attempts - 1);
        },
        observeMapContainer() {
            this.disconnectMapObserver();
            const mapEl = this.$refs.mapContainer;
            if (!mapEl || typeof ResizeObserver === "undefined") return;
            this.mapResizeObserver = new ResizeObserver((entries) => {
                const entry = entries[0];
                if (!entry || !this.map) return;
                const { width, height } = entry.contentRect;
                if (width > 0 && height > 0) {
                    this.map.resize();
                }
            });
            this.mapResizeObserver.observe(mapEl);
        },
        retryMap() {
            this.mapError = "";
            this.initMap();
        },
        loadTeamData() {
            const teamStr = localStorage.getItem("rescuer_team");
            if (teamStr) {
                try {
                    const team = JSON.parse(teamStr);
                    this.teamId = team.id_doi_cuu_ho || team.id;
                    this.teamLat = team.vi_tri_lat || null;
                    this.teamLng = team.vi_tri_lng || null;
                } catch (e) {
                    console.error('Error parsing team data', e);
                }
            }
        },
        async fetchAssignments() {
            this.loading = true;
            this.assignments = [];
            try {
                let all = [];
                if (this.teamId) {
                    const res = await rescuerAPI.getAssignmentByTeam(this.teamId, { per_page: 100 });
                    if (res.data) {
                        const rawItems = res.data.data ?? res.data;
                        if (Array.isArray(rawItems)) all = rawItems;
                        else if (Array.isArray(rawItems?.data)) all = rawItems.data;
                    }
                } else {
                    const res = await rescuerAPI.getAssignments({ per_page: 100 });
                    if (res.data) {
                        const rawItems = res.data.data ?? res.data;
                        if (Array.isArray(rawItems)) all = rawItems;
                        else if (Array.isArray(rawItems?.data)) all = rawItems.data;
                    }
                }
                const seen = new Set();
                this.assignments = all.filter(item => {
                    if (!item || seen.has(item.id_phan_cong)) return false;
                    seen.add(item.id_phan_cong);
                    return true;
                });
                this.updateMapMarkers();
                await this.checkActiveAssignment();
            } catch (e) {
                console.error("Lỗi tải phân công:", e);
                toaster.error("Không thể tải danh sách nhiệm vụ");
            } finally {
                this.loading = false;
            }
        },
        async checkActiveAssignment() {
            try {
                if (!this.teamId) return;
                const res = await rescuerAPI.getActiveAssignment(this.teamId);
                this.hasActiveRequest = res.data?.has_active === true;
            } catch (e) {
                console.error("Lỗi kiểm tra yêu cầu đang xử lý:", e);
            }
        },
        async initMap() {
            this.mapLoading = true;
            this.mapError = "";
            try {
                const mapEl = await this.waitForMapContainer();
                if (!mapEl) throw new Error("Không tìm thấy map container");
                await loadOpenMap();
                this.initOpenMap(mapEl);
                this.observeMapContainer();
            } catch (err) {
                this.mapError = err?.message || "Không thể tải bản đồ OpenMap";
                console.error('[TrangChu] OpenMap load error:', err);
            } finally {
                this.mapLoading = false;
            }
        },
        initOpenMap(mapEl) {
            const defaultCenter = [108.2022, 16.0544];

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        let lat = pos.coords.latitude;
                        let lng = pos.coords.longitude;
                        // Fix for desktop IP-based GPS incorrectly returning Ho Chi Minh City
                        if (lat < 12 && lng < 107.5) {
                            lat = this.teamLat ? Number(this.teamLat) : 16.0544;
                            lng = this.teamLng ? Number(this.teamLng) : 108.2022;
                        }
                        this.memberLat = lat;
                        this.memberLng = lng;
                        const center = [this.memberLng, this.memberLat];
                        this.initMapWithCenter(mapEl, center);
                    },
                    () => {
                        const center = (this.teamLat && this.teamLng)
                            ? [Number(this.teamLng), Number(this.teamLat)]
                            : defaultCenter;
                        this.initMapWithCenter(mapEl, center);
                    }
                );
            } else {
                const center = (this.teamLat && this.teamLng)
                    ? [Number(this.teamLng), Number(this.teamLat)]
                    : defaultCenter;
                this.initMapWithCenter(mapEl, center);
            }
        },
        initMapWithCenter(mapEl, center) {
            this.map = createOpenMap(mapEl, {
                center,
                zoom: 14,
                mapStyle: "day-v1",
            });

            this.map.on("load", () => {
                if (this.teamLat && this.teamLng) {
                    this.addTeamMarker(this.teamLat, this.teamLng);
                }
                if (this.memberLat && this.memberLng) {
                    this.addGpsMarker(this.memberLat, this.memberLng);
                    if (!this.teamLat && !this.teamLng) {
                        this.addTeamMarker(this.memberLat, this.memberLng);
                    }
                }
                this.updateMapMarkers();
            });
        },
        addTeamMarker(lat, lng) {
            if (this.teamMarker) this.teamMarker.remove();
            this.teamMarker = createOpenMapMarker({
                type: "home",
                position: { lng: Number(lng), lat: Number(lat) },
                fillColor: '#2563eb',
                title: 'Trung tâm đội',
            }).setPopup(createOpenMapPopup({ offset: 15 }).setHTML('<b>Trung tâm đội</b>'))
              .addTo(this.map);
        },
        addGpsMarker(lat, lng) {
            if (this.gpsMarker) this.gpsMarker.remove();
            this.gpsMarker = createOpenMapMarker({
                type: "gps",
                position: { lng: Number(lng), lat: Number(lat) },
                fillColor: '#2563eb',
                title: 'Vị trí của bạn',
            }).setPopup(createOpenMapPopup({ offset: 15 }).setHTML('<b>Vị trí của bạn</b>'))
              .addTo(this.map);
        },
        refreshGpsMarker() {
            if (!this.map) return;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    let lat = pos.coords.latitude;
                    let lng = pos.coords.longitude;
                    // Fix for desktop IP-based GPS incorrectly returning Ho Chi Minh City
                    if (lat < 12 && lng < 107.5) {
                        lat = this.teamLat ? Number(this.teamLat) : 16.0544;
                        lng = this.teamLng ? Number(this.teamLng) : 108.2022;
                    }
                    this.memberLat = lat;
                    this.memberLng = lng;
                    this.addGpsMarker(this.memberLat, this.memberLng);
                    this.map.flyTo({
                        center: [this.memberLng, this.memberLat],
                        zoom: this.map.getZoom(),
                        essential: true,
                    });
                }, (err) => {
                    console.warn('GPS unavailable:', err);
                }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
            }
        },
        updateMapMarkers() {
            if (!this.map) return;

            this.requestMarkers.forEach(m => m.remove());
            this.requestMarkers = [];

            this.radarAssignments.forEach(item => {
                if (item.yeu_cau?.vi_tri_lat && item.yeu_cau?.vi_tri_lng) {
                    const sev = item.yeu_cau.muc_do_khan_cap?.toUpperCase() || '';
                    const colorMap = { 'CRITICAL': '#7f1d1d', 'HIGH': '#dc2626', 'MEDIUM': '#f97316', 'LOW': '#22c55e' };
                    const color = colorMap[sev] || '#22c55e';

                    const marker = createOpenMapMarker({
                        position: { lng: Number(item.yeu_cau.vi_tri_lng), lat: Number(item.yeu_cau.vi_tri_lat) },
                        fillColor: color,
                        label: { text: '!' },
                        title: item.yeu_cau?.chi_tiet || this.getIncidentTypeName(item),
                    });

                    const typeName = item.yeu_cau?.chi_tiet || this.getIncidentTypeName(item);
                    const address = this.getRequestAddress(item);
                    const reporterName = this.getReporterName(item);
                    const phone = item.yeu_cau?.so_dien_thoai_nguoi_dung || '';
                    const popup = createOpenMapPopup()
                        .setHTML(`<div style="min-width:220px;font-family:Inter,sans-serif;padding:4px"><h6 style="margin:0 0 8px;font-weight:700;color:#1e293b;font-size:14px">${typeName}</h6><p style="margin:0 0 4px;font-size:12px;color:#64748b"><b>ID:</b> ${item.yeu_cau?.id_yeu_cau || '-'}</p><p style="margin:0 0 4px;font-size:12px;color:#64748b">${address || 'Chưa có địa chỉ'}</p><p style="margin:0;font-size:12px;color:#64748b">${reporterName || 'Không rõ'}</p>${phone ? `<p style="margin:4px 0 0;font-size:12px;color:#2563eb">${phone}</p>` : ''}</div>`);

                    marker.getElement().addEventListener('click', () => {
                        this.map.flyTo({ center: [Number(item.yeu_cau.vi_tri_lng), Number(item.yeu_cau.vi_tri_lat)], zoom: 15 });
                        popup.setLngLat([Number(item.yeu_cau.vi_tri_lng), Number(item.yeu_cau.vi_tri_lat)]).addTo(this.map);
                        this.selectMission(item);
                    });

                    marker.setPopup(popup);
                    marker.addTo(this.map);
                    this.requestMarkers.push(marker);
                }
            });
        },
        async selectMission(item) {
            this.selectedMission = item;
            if (item?.yeu_cau?.vi_tri_lat && item?.yeu_cau?.vi_tri_lng) {
                const srcLat = this.memberLat;
                const srcLng = this.memberLng;
                if (srcLat && srcLng) {
                    await this.drawFlightRoute(srcLat, srcLng, item.yeu_cau.vi_tri_lat, item.yeu_cau.vi_tri_lng);
                }
                this.map.flyTo({
                    center: [Number(item.yeu_cau.vi_tri_lng), Number(item.yeu_cau.vi_tri_lat)],
                    zoom: 15,
                    essential: true,
                });
            }
        },
        async drawFlightRoute(lat1, lng1, lat2, lng2) {
            this.cleanupRoute();
            this.drawStraightLine(lat1, lng1, lat2, lng2);
            this.fitBoundsToMap(this.map, [
                [lng1, lat1],
                [lng2, lat2]
            ]);
        },
        drawStraightLine(lat1, lng1, lat2, lng2) {
            const coords = [[Number(lng1), Number(lat1)], [Number(lng2), Number(lat2)]];
            const routeGeoJSON = { type: 'Feature', geometry: { type: 'LineString', coordinates: coords } };
            this.map.addSource('route', { type: 'geojson', data: routeGeoJSON });
            this.map.addLayer({
                id: 'route-line',
                type: 'line',
                source: 'route',
                paint: {
                    'line-color': '#ef4444',
                    'line-width': 3,
                    'line-opacity': 0.7,
                    'line-dasharray': [2, 2],
                },
            });
            this.routeSource = 'route';
            this.routeLayer = 'route-line';
        },
        fitBoundsToMap(map, coordinates) {
            if (!coordinates || coordinates.length === 0) return;
            const bounds = new (map.constructor.LatLngBounds)();
            coordinates.forEach((coord) => {
                const lng = typeof coord.lng === "number" ? coord.lng : coord[0];
                const lat = typeof coord.lat === "number" ? coord.lat : coord[1];
                bounds.extend([lng, lat]);
            });
            map.fitBounds(bounds, { padding: 50 });
        },
        cleanupRoute() {
            if (!this.map) return;
            if (this.routeLayer && this.map.getLayer(this.routeLayer)) {
                this.map.removeLayer(this.routeLayer);
                this.routeLayer = null;
            }
            if (this.routeSource && this.map.getSource(this.routeSource)) {
                this.map.removeSource(this.routeSource);
                this.routeSource = null;
            }
        },
        async acceptMission(item) {
            try {
                await rescuerAPI.updateAssignmentStatus(item.id_phan_cong, {
                    trang_thai_nhiem_vu: 'DANG_XU_LY'
                });
                toaster.success("Đã tiếp nhận nhiệm vụ");
                await this.fetchAssignments();
                this.selectedMission = null;
                this.$router.push("/rescuer/dang-xu-ly");
            } catch (e) {
                console.error("Lỗi tiếp nhận:", e);
                const msg = e.response?.data?.message || e.response?.data?.msg || "Không thể tiếp nhận nhiệm vụ";
                toaster.error(msg);
            }
        },
        goToMission(item) {
            this.$router.push("/rescuer/dang-xu-ly");
        },
        zoomIn() { if (this.map) this.map.zoomIn(); },
        zoomOut() { if (this.map) this.map.zoomOut(); },
        locateMe() {
            this.refreshGpsMarker();
        },
        formatTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });
        },
        getSeverityLabel(item) {
            const sev = (item.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
            if (sev === 'CRITICAL') return 'Khẩn cấp';
            if (sev === 'HIGH') return 'Cao';
            if (sev === 'MEDIUM') return 'Trung bình';
            return 'Thường';
        },
        getSeverityBadgeClass(item) {
            const sev = (item.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
            if (sev === 'CRITICAL') return 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
            if (sev === 'HIGH') return 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
            if (sev === 'MEDIUM') return 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25';
            return 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
        },
        getCardClass(item) {
            const sev = (item.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
            if (sev === 'CRITICAL' || sev === 'HIGH') return 'border-danger border-2';
            return '';
        },
        getCardSeverityClass(item) {
            const sev = (item.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
            if (sev === 'CRITICAL') return 'card-critical';
            if (sev === 'HIGH') return 'card-high';
            if (sev === 'MEDIUM') return 'card-medium';
            return 'card-low';
        },
        getUrgencyDotClass(item) {
            const sev = (item.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
            if (sev === 'CRITICAL') return 'urgency-critical';
            if (sev === 'HIGH') return 'urgency-high';
            if (sev === 'MEDIUM') return 'urgency-medium';
            return 'urgency-low';
        },
        getDetailHeaderClass(item) {
            const sev = (item.yeu_cau?.muc_do_khan_cap || 'LOW').toUpperCase();
            if (sev === 'CRITICAL' || sev === 'HIGH') return 'header-danger';
            if (sev === 'MEDIUM') return 'header-warning';
            return 'header-success';
        },
        getIncidentBadges(mission) {
            const chiTiet = mission?.yeu_cau?.chi_tiet;
            if (chiTiet) {
                const parts = chiTiet.split(/[,.;\n]/).map(p => p.trim()).filter(p => p.length > 0);
                if (parts.length > 0) return parts;
            }
            return [this.getIncidentTypeName(mission)];
        },
        getIncidentTypeName(item) {
            if (item.yeu_cau?.loai_su_co) {
                return item.yeu_cau.loai_su_co.ten_danh_muc || item.yeu_cau.loai_su_co.ten_loai_su_co || 'Yêu cầu cứu hộ';
            }
            return 'Yêu cầu cứu hộ';
        },
        getRequestAddress(item) {
            if (item.yeu_cau) {
                if (item.yeu_cau.vi_tri_dia_chi) return item.yeu_cau.vi_tri_dia_chi;
                if (item.yeu_cau.dia_chi) return item.yeu_cau.dia_chi;
                if (item.yeu_cau.vi_tri) return item.yeu_cau.vi_tri;
            }
            return 'Chưa có địa chỉ';
        },
        getReporterName(item) {
            if (item.yeu_cau) {
                if (item.yeu_cau.ho_ten_nguoi_dung) return item.yeu_cau.ho_ten_nguoi_dung;
                if (item.yeu_cau.hoTenNguoiDung) return item.yeu_cau.hoTenNguoiDung;
                if (item.yeu_cau.nguoi_dung) {
                    return item.yeu_cau.nguoi_dung.ho_ten || item.yeu_cau.nguoi_dung.hoTen || 'Không rõ';
                }
            }
            return 'Không rõ';
        },
        getReporterPhone(item) {
            if (item.yeu_cau) {
                if (item.yeu_cau.so_dien_thoai_nguoi_dung) return item.yeu_cau.so_dien_thoai_nguoi_dung;
                if (item.yeu_cau.soDienThoaiNguoiDung) return item.yeu_cau.soDienThoaiNguoiDung;
                if (item.yeu_cau.nguoi_dung) {
                    return item.yeu_cau.nguoi_dung.so_dien_thoai || item.yeu_cau.nguoi_dung.soDienThoai || '';
                }
            }
            return '';
        },
    },
};
</script>

<style scoped>
/* ─── Wrapper ──────────────────────────────────────────────────────── */
.rescuer-home {
    height: 100vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    font-family: 'Inter', 'Roboto', sans-serif;
    background: #f8fafc;
    margin: -1.5rem -1.5rem -2rem;
}

/* ─── Status Bar ────────────────────────────────────────────────────── */
.status-bar {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    padding: 0.75rem 1.5rem;
    flex-shrink: 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.status-bar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.status-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.brand-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.brand-name {
    display: block;
    font-size: 0.95rem;
    font-weight: 800;
    color: white;
    line-height: 1.2;
}

.brand-sub {
    display: block;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.5);
    font-weight: 500;
}

.status-indicators {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-chip {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
}

.chip-success { background: rgba(34,197,94,0.15); color: #4ade80; }
.chip-warning { background: rgba(250,204,21,0.15); color: #facc15; }

.chip-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    animation: chip-pulse 2s infinite;
}

@keyframes chip-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.mission-count {
    text-align: right;
}

.count-num {
    display: block;
    font-size: 1.25rem;
    font-weight: 800;
    line-height: 1;
}

.count-label {
    display: block;
    font-size: 0.65rem;
    color: rgba(255,255,255,0.4);
    font-weight: 500;
}

/* ─── Active Banner ────────────────────────────────────────────────── */
.active-banner {
    display: flex;
    align-items: center;
    padding: 0.6rem 1.5rem;
    background: linear-gradient(90deg, #dc2626, #b91c1c);
    color: white;
    cursor: pointer;
    gap: 0.75rem;
    flex-shrink: 0;
    transition: all 0.2s;
}

.active-banner:hover { background: linear-gradient(90deg, #b91c1c, #991b1b); }

.banner-left {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex-shrink: 0;
}

.pulse-ring {
    position: absolute;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    animation: banner-pulse 1.5s infinite;
}

@keyframes banner-pulse {
    0% { transform: scale(0.8); opacity: 1; }
    100% { transform: scale(1.6); opacity: 0; }
}

.banner-icon {
    position: relative;
    z-index: 1;
}

.banner-title {
    display: block;
    font-size: 0.78rem;
    font-weight: 700;
    color: white;
}

.banner-sub {
    display: block;
    font-size: 0.68rem;
    color: rgba(255,255,255,0.7);
}

.banner-right { margin-left: auto; }

/* ─── Main Layout ──────────────────────────────────────────────────── */
.main-layout {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

/* ─── Sidebar ─────────────────────────────────────────────────────── */
.sidebar {
    width: 380px;
    min-width: 380px;
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border-right: 1px solid #e2e8f0;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), min-width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.sidebar-collapsed {
    width: 0;
    min-width: 0;
}

.sidebar-header {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    flex-shrink: 0;
}

.filter-tabs {
    display: flex;
    gap: 0.5rem;
    flex: 1;
}

.filter-tab {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.9rem;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    background: transparent;
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-tab:hover { border-color: #cbd5e1; color: #334155; }
.filter-tab.active { background: #1e293b; border-color: #1e293b; color: white; }

.filter-tab-danger.active { background: #dc2626; border-color: #dc2626; color: white; }

.tab-count {
    padding: 0.1rem 0.45rem;
    border-radius: 10px;
    background: rgba(0,0,0,0.08);
    font-size: 0.7rem;
}

.filter-tab.active .tab-count { background: rgba(255,255,255,0.2); }

.collapse-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    background: transparent;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.2s;
}

.collapse-btn:hover { background: #f1f5f9; }

/* ─── Mission List ────────────────────────────────────────────────── */
.mission-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.75rem;
    min-height: 0;
}

.mission-list::-webkit-scrollbar { width: 4px; }
.mission-list::-webkit-scrollbar-track { background: transparent; }
.mission-list::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

.cards-container {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* ─── Mission Card ────────────────────────────────────────────────── */
.mission-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 1rem;
    border: 1.5px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.mission-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    border-radius: 4px 0 0 4px;
    transition: width 0.2s;
}

.card-critical::before { background: #dc2626; }
.card-high::before { background: #ea580c; }
.card-medium::before { background: #eab308; }
.card-low::before { background: #22c55e; }

.mission-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}

.mission-card.card-selected {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.12), 0 4px 16px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.6rem;
}

.card-urgency {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.urgency-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.urgency-critical .urgency-dot { background: #dc2626; animation: urgency-pulse 1.5s infinite; }
.urgency-critical .urgency-label { color: #dc2626; }
.urgency-high .urgency-dot { background: #ea580c; animation: urgency-pulse 2s infinite; }
.urgency-high .urgency-label { color: #ea580c; }
.urgency-medium .urgency-dot { background: #eab308; }
.urgency-medium .urgency-label { color: #a16207; }
.urgency-low .urgency-dot { background: #22c55e; }
.urgency-low .urgency-label { color: #16a34a; }

@keyframes urgency-pulse {
    0%, 100% { box-shadow: 0 0 0 0 currentColor; }
    50% { box-shadow: 0 0 0 4px transparent; }
}

.card-id {
    font-size: 0.72rem;
    font-weight: 700;
    color: #94a3b8;
    font-family: monospace;
}

.card-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.45rem;
    line-height: 1.35;
}

.card-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 0.68rem;
    font-weight: 600;
    margin-bottom: 0.65rem;
    width: fit-content;
}

.card-critical .card-type-badge { background: #fef2f2; color: #dc2626; }
.card-high .card-type-badge { background: #fff7ed; color: #ea580c; }
.card-medium .card-type-badge { background: #fefce8; color: #a16207; }
.card-low .card-type-badge { background: #f0fdf4; color: #16a34a; }

.card-info-grid {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-bottom: 0.7rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
    line-height: 1.35;
}

.info-item svg { flex-shrink: 0; }

.card-critical .info-item svg { stroke: #dc2626; }
.card-high .info-item svg { stroke: #ea580c; }
.card-medium .info-item svg { stroke: #ca8a04; }
.card-low .info-item svg { stroke: #16a34a; }

.card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding-top: 0.65rem;
    border-top: 1px solid #f1f5f9;
}

.footer-left {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    flex: 1;
    min-width: 0;
}

.footer-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    background: #eff6ff;
    color: #3b82f6;
    font-size: 0.68rem;
    font-weight: 600;
    max-width: 160px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.footer-chip svg { flex-shrink: 0; }

.footer-chip-time {
    background: #f8fafc;
    color: #94a3b8;
}

.card-action {
    padding: 0.4rem 0.85rem;
    border-radius: 8px;
    border: none;
    background: #1e293b;
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.card-action:hover:not(.action-disabled) { background: #334155; }

.card-action.action-disabled,
.card-action:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    opacity: 0.8;
}

.card-action-secondary {
    background: #f1f5f9;
    color: #475569;
}

.card-action-secondary:hover {
    background: #e2e8f0;
    color: #1e293b;
}

/* ─── Skeleton Loading ──────────────────────────────────────────────── */
.list-state { padding: 0.5rem; }

.skeleton-card {
    background: white;
    border-radius: 14px;
    padding: 1rem;
    margin-bottom: 0.6rem;
    border: 1.5px solid #e2e8f0;
}

.skeleton-line {
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.skeleton-title { height: 16px; width: 65%; }
.skeleton-short { height: 12px; width: 45%; }
.skeleton-medium { height: 12px; width: 80%; }

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* ─── Empty State ──────────────────────────────────────────────────── */
.list-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    text-align: center;
    height: 100%;
}

.empty-illustration { color: #cbd5e1; margin-bottom: 1rem; }

.empty-title {
    font-size: 1rem;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 0.35rem;
}

.empty-sub {
    font-size: 0.78rem;
    color: #94a3b8;
    margin: 0;
}

/* ─── Map Area ─────────────────────────────────────────────────────── */
.map-area {
    flex: 1;
    position: relative;
    min-width: 0;
}

.map-canvas {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
}

/* ─── Map Overlay ──────────────────────────────────────────────────── */
.map-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(248,250,252,0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.map-overlay-card {
    text-align: center;
    padding: 2rem;
}

.overlay-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e2e8f0;
    border-top-color: #2563eb;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin { to { transform: rotate(360deg); } }

.overlay-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
}

.overlay-icon-error { background: #fef2f2; color: #dc2626; }

.overlay-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.35rem;
}

.overlay-title-error { color: #dc2626; }

.overlay-sub {
    font-size: 0.78rem;
    color: #94a3b8;
    margin-bottom: 1rem;
}

.overlay-btn {
    padding: 0.5rem 1.25rem;
    border-radius: 10px;
    border: none;
    background: #dc2626;
    color: white;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
}

.overlay-btn:hover { background: #b91c1c; }

/* ─── Map Detail Card ──────────────────────────────────────────────── */
.map-detail-card {
    position: absolute;
    top: 1rem;
    left: 1rem;
    width: 320px;
    background: white;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    overflow: hidden;
    z-index: 20;
    animation: slideInCard 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    opacity: 0;
    transform: translateY(-8px);
    pointer-events: none;
}

.card-visible {
    opacity: 1;
    transform: translateY(0);
    pointer-events: all;
}

@keyframes slideInCard {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

.detail-card-header {
    padding: 0.85rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.header-danger { background: linear-gradient(135deg, #7f1d1d, #991b1b); }
.header-warning { background: linear-gradient(135deg, #713f12, #a16207); }
.header-success { background: linear-gradient(135deg, #14532d, #166534); }

.detail-urgency {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.72rem;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.detail-urgency .urgency-dot { background: rgba(255,255,255,0.7); }

.detail-id {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(255,255,255,0.8);
    font-family: monospace;
    margin: 0 0.5rem;
}

.detail-close {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: rgba(255,255,255,0.15);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.detail-close:hover { background: rgba(255,255,255,0.25); }

.detail-card-body { padding: 1rem; }

.detail-title {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.5rem;
    line-height: 1.3;
    flex: 1;
}

.detail-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.6rem;
}

.detail-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-bottom: 0.75rem;
}

.badge-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    font-size: 0.68rem;
    font-weight: 600;
}

.badge-chip svg { flex-shrink: 0; }

.badge-chip-type { background: #f1f5f9; color: #64748b; }
.badge-chip-dispatch { background: #eff6ff; color: #3b82f6; }

.detail-info-list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 0.75rem;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.75rem;
    color: #475569;
    font-weight: 500;
    line-height: 1.35;
}

.info-row svg { flex-shrink: 0; stroke: #94a3b8; }

.info-phone {
    margin-left: auto;
    color: #3b82f6;
    font-weight: 600;
    font-size: 0.72rem;
    background: #eff6ff;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
}

.detail-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #f1f5f9;
}

.action-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}

.action-btn-call { background: #dcfce7; color: #16a34a; }
.action-btn-call:hover { background: #bbf7d0; }

.action-btn-nav { background: #eff6ff; color: #2563eb; }
.action-btn-nav:hover { background: #dbeafe; }

.detail-section { margin-bottom: 0.85rem; }

.detail-icon-label {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.68rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.25rem;
}

.detail-value {
    font-size: 0.82rem;
    color: #334155;
    font-weight: 500;
    line-height: 1.4;
}

.detail-action-btn {
    width: 100%;
    padding: 0.75rem;
    border-radius: 12px;
    border: none;
    background: #1e293b;
    color: white;
    font-size: 0.85rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 0.75rem;
}

.detail-action-btn:hover:not(.btn-disabled) { background: #334155; }

.detail-action-btn.btn-disabled,
.detail-action-btn:disabled {
    background: #94a3b8;
    cursor: not-allowed;
}

.detail-action-btn-danger { background: #dc2626; }
.detail-action-btn-danger:hover:not(.btn-disabled) { background: #b91c1c; }

/* ─── Map Controls ──────────────────────────────────────────────────── */
.map-controls {
    position: absolute;
    bottom: 5rem;
    right: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    z-index: 15;
}

.map-btn {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    border: none;
    background: white;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    transition: all 0.2s;
}

.map-btn:hover { background: #f8fafc; transform: scale(1.05); }

.map-btn-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 0.1rem 0;
}

.map-btn-primary { color: #2563eb; }
.map-btn-primary:hover { background: #eff6ff; color: #1d4ed8; }

/* ─── Map Legend ────────────────────────────────────────────────────── */
.map-legend {
    position: absolute;
    bottom: 5rem;
    left: 1rem;
    background: white;
    border-radius: 12px;
    padding: 0.6rem 0.85rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 15;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: #475569;
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.legend-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 0.2rem 0;
}

.legend-icon {
    width: 14px;
    height: 14px;
    border-radius: 50% 50% 0 0;
    background: #2563eb;
    border: 2px solid #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    flex-shrink: 0;
    display: inline-block;
}

.legend-icon-gps {
    border-radius: 50%;
    background: #2563eb;
}

.badge-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.incident-badge {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 500;
    background-color: #e2e8f0;
    color: #334155;
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border: 1px solid #cbd5e1;
}

/* ─── Responsive ────────────────────────────────────────────────────── */
@media (max-width: 991.98px) {
    .main-layout { flex-direction: column; }

    .sidebar {
        width: 100%;
        max-width: 100%;
        height: 45%;
        border-right: none;
        border-bottom: 1px solid #e2e8f0;
    }

    .sidebar-header { padding: 0.75rem; }

    .map-area { height: 55%; }

    .map-detail-card {
        width: calc(100% - 2rem);
        left: 1rem;
        right: 1rem;
        top: auto;
        bottom: 1rem;
        max-height: 60vh;
    }

    .map-controls { bottom: 4rem; right: 0.75rem; }
    .map-btn { width: 36px; height: 36px; }
    .map-legend { bottom: 4rem; left: 0.75rem; }
}
</style>
