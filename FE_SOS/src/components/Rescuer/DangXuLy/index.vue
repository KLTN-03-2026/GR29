<template>
    <div class="processing-page">
        <!-- Header Bar -->
        <div class="page-header">
            <div class="header-inner">
                <div class="header-left">
                    <router-link to="/rescuer/home" class="back-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                    </router-link>
                    <div class="header-title-group">
                        <div class="header-status-indicator">
                            <span class="status-bar-dot"></span>
                            <span class="status-bar-label">Đang xử lý</span>
                        </div>
                        <h2 class="header-title">Nhiệm Vụ Cứu Hộ</h2>
                    </div>
                </div>

                <div class="header-right" v-if="currentMission">
                    <div class="mission-id-tag">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        #{{ currentMission.yeu_cau?.id_yeu_cau || '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Layout -->
        <div class="content-layout">
            <!-- Detail Panel -->
            <div class="detail-panel" :class="{ 'panel-empty': !currentMission && !loading }">

                <!-- Loading State -->
                <div v-if="loading" class="panel-loading">
                    <div class="loading-spinner"></div>
                    <p class="loading-text">Đang tải dữ liệu...</p>
                </div>

                <!-- Mission Content -->
                <template v-else-if="currentMission">
                    <div class="mission-content">

                        <!-- Priority Banner -->
                        <div class="priority-banner" :class="getPriorityBannerClass()">
                            <div class="banner-priority-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                    <line x1="12" y1="9" x2="12" y2="13"/>
                                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                            </div>
                            <div class="banner-text">
                                <span class="banner-level">{{ getPriorityText() }}</span>
                                <span class="banner-type">{{ currentMission.yeu_cau?.muc_do_khan_cap || '-' }}</span>
                            </div>
                        </div>

                        <!-- Incident Info -->
                        <div class="incident-section">
                            <div class="incident-top-row">
                                <div class="badge-container">
                                    <span v-for="(badge, index) in getIncidentBadges(currentMission)" 
                                          :key="index" 
                                          class="incident-badge" 
                                          :title="badge">
                                        {{ badge }}
                                    </span>
                                </div>
                                <div class="incident-id-chip">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    #{{ currentMission.yeu_cau?.id_yeu_cau || '-' }}
                                </div>
                            </div>
                            <div class="incident-meta">
                                <div class="meta-chip" v-if="currentMission.nguoi_dieu_phoi">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                    Điều phối: {{ currentMission.nguoi_dieu_phoi }}
                                </div>
                                <div class="meta-chip">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    Bắt đầu: {{ formatTime(currentMission.created_at) }}
                                </div>
                            </div>
                        </div>

                        <!-- Victim Card -->
                        <div class="info-card">
                            <div class="card-header-row">
                                <div class="section-label">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    Người gặp nạn
                                </div>
                            </div>
                            <div class="victim-row">
                                <div class="victim-avatar-lg">
                                    {{ (getReporterName(currentMission) || 'N').charAt(0).toUpperCase() }}
                                </div>
                                <div class="victim-details">
                                    <div class="victim-name-lg">{{ getReporterName(currentMission) }}</div>
                                    <div class="victim-phone-lg" v-if="getReporterPhone(currentMission)">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.77 13.1 19.79 19.79 0 0 1 1.71 4.49 2 2 0 0 1 3.68 2.3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                                        </svg>
                                        {{ getReporterPhone(currentMission) }}
                                    </div>
                                </div>
                                <a v-if="getReporterPhone(currentMission)"
                                    :href="'tel:' + getReporterPhone(currentMission)"
                                    class="call-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.77 13.1 19.79 19.79 0 0 1 1.71 4.49 2 2 0 0 1 3.68 2.3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Location Card -->
                        <div class="info-card">
                            <div class="card-header-row">
                                <div class="section-label">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    Địa điểm sự cố
                                </div>
                            </div>
                            <div class="location-text">{{ getRequestAddress(currentMission) }}</div>
                            <a v-if="getRequestAddress(currentMission)"
                                :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(getRequestAddress(currentMission))"
                                target="_blank"
                                class="nav-btn">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polygon points="3 11 22 2 13 21 11 13 3 11"/>
                                </svg>
                                Chỉ đường đến hiện trường
                            </a>
                        </div>

                        <!-- Progress Tracker -->
                        <div class="info-card progress-card">
                            <div class="card-header-row">
                                <div class="section-label">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                    </svg>
                                    Tiến trình nhiệm vụ
                                </div>
                            </div>

                            <div class="progress-steps">
                                <div class="step" :class="{ 'step-done': missionStep >= 1, 'step-active': missionStep === 1 }">
                                    <div class="step-connector" :class="{ 'connector-active': missionStep >= 2 }"></div>
                                    <div class="step-node">
                                        <svg v-if="missionStep >= 1" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        <span v-else>1</span>
                                    </div>
                                    <div class="step-label">Tiếp nhận</div>
                                </div>
                                <div class="step" :class="{ 'step-done': missionStep >= 2, 'step-active': missionStep === 2 }">
                                    <div class="step-connector" :class="{ 'connector-active': missionStep >= 3 }"></div>
                                    <div class="step-node">
                                        <svg v-if="missionStep >= 2" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        <span v-else>2</span>
                                    </div>
                                    <div class="step-label">Di chuyển</div>
                                </div>
                                <div class="step" :class="{ 'step-done': missionStep >= 3, 'step-active': missionStep === 3 }">
                                    <div class="step-connector" :class="{ 'connector-active': missionStep >= 4 }"></div>
                                    <div class="step-node">
                                        <svg v-if="missionStep >= 3" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        <span v-else>3</span>
                                    </div>
                                    <div class="step-label">Tại hiện trường</div>
                                </div>
                                <div class="step" :class="{ 'step-done': missionStep >= 4, 'step-active': missionStep === 4 }">
                                    <div class="step-node">
                                        <svg v-if="missionStep >= 4" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        <span v-else>4</span>
                                    </div>
                                    <div class="step-label">Hoàn thành</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-group">
                            <button class="action-primary" @click="markArrived" v-if="missionStep < 3">
                                <div class="action-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                </div>
                                <div class="action-text">
                                    <span class="action-title">Đã đến hiện trường</span>
                                    <span class="action-sub">Cập nhật trạng thái</span>
                                </div>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>

                            <button class="action-success" @click="openReportModal" v-if="missionStep >= 3 && missionStep < 4">
                                <div class="action-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                        <polyline points="22 4 12 14.01 9 11.01"/>
                                    </svg>
                                </div>
                                <div class="action-text">
                                    <span class="action-title">Báo cáo kết quả</span>
                                    <span class="action-sub">Hoàn thành nhiệm vụ</span>
                                </div>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>

                            <button class="action-support" @click="openReinforceModal" v-if="missionStep < 4">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.77 13.1 19.79 19.79 0 0 1 1.71 4.49 2 2 0 0 1 3.68 2.3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                Yêu cầu tiếp viện
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div v-else class="panel-empty-state">
                    <div class="empty-icon-wrap">
                        <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                            <line x1="9" y1="9" x2="9.01" y2="9"/>
                            <line x1="15" y1="9" x2="15.01" y2="9"/>
                        </svg>
                    </div>
                    <h4 class="empty-heading">Không có nhiệm vụ đang xử lý</h4>
                    <p class="empty-desc">Tiếp nhận nhiệm vụ từ trang chủ để bắt đầu</p>
                    <router-link to="/rescuer/home" class="empty-action-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Nhận nhiệm vụ mới
                    </router-link>
                </div>
            </div>

            <!-- Map Area -->
            <div class="map-panel">
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

                <!-- Mission Status Overlay (top-right) -->
                <div v-if="currentMission && !mapLoading" class="mission-status-badge">
                    <div class="status-badge-inner" :class="getStatusBadgeClass()">
                        <span class="status-dot"></span>
                        <span class="status-text">{{ getStatusText() }}</span>
                    </div>
                </div>

                <!-- Map Legend -->
                <div class="map-legend">
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

        <!-- Report Result Modal -->
        <div class="modal fade" id="reportModal" tabindex="-1" ref="reportModalEl">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header-row">
                        <div class="modal-icon modal-icon-report">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        </div>
                        <h5 class="modal-title">Báo Cáo Kết Quả</h5>
                        <button type="button" class="modal-close-btn" data-bs-dismiss="modal">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="result-toggle">
                            <button class="result-btn" :class="{ 'result-btn-active result-btn-success': reportResult === 'success' }" @click="reportResult = 'success'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Thành công
                            </button>
                            <button class="result-btn" :class="{ 'result-btn-active result-btn-danger': reportResult === 'failure' }" @click="reportResult = 'failure'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                                Thất bại
                            </button>
                        </div>

                        <div class="form-group" v-if="reportResult === 'failure'">
                            <label class="form-label">Lý do thất bại <span class="text-danger">*</span></label>
                            <textarea class="form-textarea" rows="3" v-model="reportReason" placeholder="Mô tả lý do không thể hoàn thành nhiệm vụ..."></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Báo cáo hiện trường</label>
                            <textarea class="form-textarea" rows="3" v-model="reportNotes" :placeholder="reportResult === 'failure' ? 'Mô tả tình trạng hiện trường...' : 'Ghi chép tình trạng cứu hộ...'"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Hình ảnh minh chứng
                                <span class="text-danger" v-if="reportResult === 'failure'">*</span>
                            </label>
                            <div class="file-upload-area">
                                <input type="file" class="file-input" accept="image/*" @change="handleImageUpload" ref="imageInput">
                                <div v-if="!reportImagePreview" class="upload-placeholder">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                    <span>Tải lên hình ảnh</span>
                                    <span class="upload-hint">JPG, PNG, WEBP (tối đa 10MB)</span>
                                </div>
                                <div v-else class="image-preview">
                                    <img :src="reportImagePreview" alt="Preview">
                                    <button class="preview-remove" @click="reportImagePreview = null; reportImage = null;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer-row">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn-submit" :class="reportResult === 'success' ? 'btn-submit-success' : 'btn-submit-danger'" @click="submitReport" :disabled="submittingReport">
                            <span v-if="submittingReport" class="btn-spinner"></span>
                            <template v-else>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="22" y1="2" x2="11" y2="13"/>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                </svg>
                                Gửi Báo Cáo
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reinforce Request Modal -->
        <div class="modal fade" id="reinforceModal" tabindex="-1" ref="reinforceModalEl">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header-row modal-header-warning">
                        <div class="modal-icon modal-icon-warning">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.77 13.1 19.79 19.79 0 0 1 1.71 4.49 2 2 0 0 1 3.68 2.3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <h5 class="modal-title">Yêu Cầu Tiếp Viện</h5>
                        <button type="button" class="modal-close-btn" data-bs-dismiss="modal">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="reinforce-alert">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            Hệ thống sẽ thông báo cho quản trị viên để điều động đội cứu hộ bổ sung.
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nội dung yêu cầu</label>
                            <textarea class="form-textarea" rows="4" v-model="reinforceMessage" placeholder="Mô tả tình trạng cần hỗ trợ, số lượng nhân sự, thiết bị cần thiết..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer-row">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Đóng</button>
                        <button type="button" class="btn-submit btn-submit-warning" @click="submitReinforce" :disabled="submittingReinforce">
                            <span v-if="submittingReinforce" class="btn-spinner"></span>
                            <template v-else>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="22" y1="2" x2="11" y2="13"/>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                </svg>
                                Gửi Yêu Cầu
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { rescuerAPI, assignmentAPI } from "../../../services/api.js";
import { loadOpenMap, createOpenMap, createOpenMapMarker, createOpenMapPopup, fitBoundsToMap } from "../../../utils/openMap.js";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

export default {
    name: "DangXuLy",
    data() {
        return {
            note: "",
            loading: false,
            currentMission: null,
            missionStep: 1,
            map: null,
            teamMarker: null,
            gpsMarker: null,
            requestMarker: null,
            routeLayer: null,
            routeSource: null,
            teamLat: null,
            teamLng: null,
            memberLat: null,
            memberLng: null,
            reportModalEl: null,
            reinforceModalEl: null,
            reportResult: 'success',
            reportReason: '',
            reportNotes: '',
            reportImage: null,
            reportImagePreview: null,
            submittingReport: false,
            reinforceMessage: '',
            submittingReinforce: false,
            teamId: null,
            mapLoading: false,
            mapError: "",
            mapResizeObserver: null,
            locationBroadcastInterval: null,
            locationChannel: null,
        };
    },
    async mounted() {
        this.loadTeamData();
        this.$nextTick(() => {
            this.initMap();
        });
        await this.loadActiveMission();
        this.$nextTick(() => {
            if (typeof bootstrap !== 'undefined') {
                this.reportModalEl = document.getElementById('reportModal');
                this.reinforceModalEl = document.getElementById('reinforceModal');
            }
        });
        this.subscribeToRescueUpdates();
        this.startLocationBroadcasting();
    },
    beforeUnmount() {
        this.unsubscribeFromRescueUpdates();
        this.disconnectMapObserver();
        this.cleanupRoute();
        this.stopLocationBroadcasting();
        if (this.locationChannel) {
            this.locationChannel.stopListening('location.updated');
            window.Echo.leave(`rescuer-location.${this.teamId}`);
            this.locationChannel = null;
        }
        if (this.teamMarker) { this.teamMarker.remove(); this.teamMarker = null; }
        if (this.gpsMarker) { this.gpsMarker.remove(); this.gpsMarker = null; }
        if (this.requestMarker) { this.requestMarker.remove(); this.requestMarker = null; }
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
        subscribeToRescueUpdates() {
            if (!window.Echo) {
                console.warn('[RescuerDangXuLy] Echo chua san sang, thu lai sau...');
                setTimeout(() => this.subscribeToRescueUpdates(), 3000);
                return;
            }
            const connect = () => {
                if (this.realtimeChannel) return;

                // Subscribe to rescue requests updates
                this.realtimeChannel = window.Echo.channel('rescue-requests');
                this.realtimeChannel.listen('RescueRequestUpdated', (event) => {
                    this.handleRescueUpdate(event);
                });

                // Subscribe to location updates for this team (if we have team data)
                if (this.teamId) {
                    this.locationChannel = window.Echo.channel(`rescuer-location.${this.teamId}`);
                    this.locationChannel.listen('location.updated', (event) => {
                        this.handleLocationUpdate(event);
                    });
                }
            };
            const conn = window.Echo.connector?.pusher?.connection;
            if (conn?.state === 'connected') {
                connect();
            } else if (conn) {
                conn.bind('connected', connect);
                setTimeout(() => {
                    if (!this.realtimeChannel) connect();
                }, 5000);
            } else {
                setTimeout(() => this.subscribeToRescueUpdates(), 2000);
            }
        },
        unsubscribeFromRescueUpdates() {
            if (this.realtimeChannel) {
                this.realtimeChannel.stopListening('RescueRequestUpdated');
                window.Echo.leave('rescue-requests');
                this.realtimeChannel = null;
            }
        },
        handleRescueUpdate(event) {
            const eventTeamId = event.id_doi_cuu_ho ?? event.teamId;
            const isMyTeam = String(eventTeamId) === String(this.teamId);
            const isNewAssignment = event.action === 'assigned' && isMyTeam;
            const isMyTeamStatusUpdate = isMyTeam && event.action !== 'assigned';
            if (!isNewAssignment && !isMyTeamStatusUpdate) return;
            const closed = new Set(["HOAN_THANH", "DA_HOAN_THANH", "HUY_BO", "DA_HUY", "TU_CHOI", "THAT_BAI", "DONE"]);
            if (closed.has(event.trang_thai)) {
                this.currentMission = null;
                this.missionStep = 1;
                toaster.info("Nhiệm vụ đã hoàn thành hoặc bị hủy.");
                return;
            }
            if (this.currentMission) {
                this.refreshMissionData();
            }
        },
        handleLocationUpdate(event) {
            // Update team marker position on map when receiving location updates
            if (!this.map || !event.lat || !event.lng) return;

            const newLat = parseFloat(event.lat);
            const newLng = parseFloat(event.lng);

            if (this.teamMarker) {
                // Update existing marker position
                this.teamMarker.setLngLat([newLng, newLat]);
            } else {
                // Create new marker if it doesn't exist
                this.teamMarker = new mapboxgl.Marker({
                    color: '#10b981', // Green for rescuer team
                    draggable: false
                })
                .setLngLat([newLng, newLat])
                .addTo(this.map);
            }

            // Update stored coordinates
            this.teamLat = newLat;
            this.teamLng = newLng;

            console.log('[Location] Updated team marker position:', newLat, newLng);
        },
        async refreshMissionData() {
            if (!this.teamId) return;
            try {
                const resp = await rescuerAPI.getActiveAssignment(this.teamId);
                const d = resp?.data;
                let assignment = null;
                if (d?.has_active === true && d?.active?.id_phan_cong) {
                    assignment = d.active;
                } else if (d?.id_phan_cong) {
                    assignment = d;
                }
                if (!assignment) {
                    this.currentMission = null;
                    this.missionStep = 1;
                    toaster.info("Nhiệm vụ đã hoàn thành hoặc bị hủy.");
                    return;
                }
                const newStatus = assignment.trang_thai_nhiem_vu || "";
                const oldStatus = this.currentMission?.trang_thai_nhiem_vu || "";
                if (newStatus !== oldStatus) {
                    this.currentMission = assignment;
                    this.updateMissionStep();
                    this.updateMapMission();
                }
            } catch {
                // Silent failure
            }
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
        async loadActiveMission(silent = false) {
            if (!silent) this.loading = true;
            try {
                let teamId = this.teamId;
                if (!teamId) {
                    const teamStr = localStorage.getItem("rescuer_team");
                    if (teamStr) {
                        try {
                            const team = JSON.parse(teamStr);
                            teamId = team.id_doi_cuu_ho || team.id;
                            this.teamId = teamId;
                        } catch (e) {
                            console.error('Lỗi parse team data:', e);
                        }
                    }
                }
                this.currentMission = null;
                this.missionStep = 1;
                if (!teamId) return;

                try {
                    const activeRes = await rescuerAPI.getActiveAssignment(teamId);
                    const activeData = activeRes?.data;
                    let activeAssignment = null;
                    if (activeData?.has_active === true && activeData?.active) {
                        activeAssignment = activeData.active;
                    } else if (activeData?.id_phan_cong) {
                        activeAssignment = activeData;
                    }
                    if (activeAssignment && activeAssignment.id_phan_cong) {
                        const status = activeAssignment.trang_thai_nhiem_vu || '';
                        this.currentMission = activeAssignment;
                        if (status === 'DANG_XU_LY') this.missionStep = 2;
                        else if (status === 'DA_DEN_HIEN_TRUONG') this.missionStep = 3;
                        else this.missionStep = 2;
                        return;
                    }
                } catch (activeErr) {
                    console.warn('[DangXuLy] getActiveAssignment failed:', activeErr?.response?.status);
                }

                const res = await rescuerAPI.getAssignmentByTeam(teamId, { per_page: 100 });
                let assignments = [];
                const rd = res?.data;
                if (Array.isArray(rd)) assignments = rd;
                else if (Array.isArray(rd?.data)) assignments = rd.data;
                else if (rd?.data?.data) assignments = rd.data.data;
                else if (rd?.data) assignments = rd.data;

                for (const item of assignments) {
                    const status = (item.trang_thai_nhiem_vu || '').trim();
                    if (status === 'DANG_XU_LY') {
                        this.currentMission = item;
                        this.missionStep = 2;
                        break;
                    } else if (status === 'DA_DEN_HIEN_TRUONG') {
                        this.currentMission = item;
                        this.missionStep = 3;
                        break;
                    }
                }
            } catch (e) {
                console.error("Lỗi tải nhiệm vụ:", e);
            } finally {
                this.loading = false;
                if (!silent) {
                    this.updateMapMission();
                }
            }
        },
        async markArrived() {
            if (!this.currentMission) return;
            try {
                await rescuerAPI.updateAssignmentStatus(this.currentMission.id_phan_cong, {
                    trang_thai_nhiem_vu: 'DA_DEN_HIEN_TRUONG'
                });
                this.currentMission = { ...this.currentMission, trang_thai_nhiem_vu: 'DA_DEN_HIEN_TRUONG' };
                this.updateMissionStep();
                toaster.success("Đã cập nhật: Đã đến hiện trường");
            } catch (e) {
                console.error("Lỗi cập nhật:", e);
                toaster.error("Không thể cập nhật trạng thái");
            }
        },
        updateMissionStep() {
            const status = this.currentMission?.trang_thai_nhiem_vu || "";
            if (status === "DANG_XU_LY") this.missionStep = 2;
            else if (status === "DA_DEN_HIEN_TRUONG") this.missionStep = 3;
            else this.missionStep = 1;
        },
        openReportModal() {
            this.reportResult = 'success';
            this.reportReason = '';
            this.reportNotes = '';
            this.reportImage = null;
            this.reportImagePreview = null;
            if (this.reportModalEl && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(this.reportModalEl);
                modal.show();
            }
        },
        openReinforceModal() {
            this.reinforceMessage = '';
            if (this.reinforceModalEl && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(this.reinforceModalEl);
                modal.show();
            }
        },
        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) {
                    toaster.error("File quá lớn. Vui lòng chọn file dưới 10MB.");
                    return;
                }
                this.reportImage = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.reportImagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        async submitReport() {
            if (this.reportResult === 'failure' && !this.reportReason.trim() && !this.reportNotes.trim()) {
                toaster.error("Vui lòng nhập lý do thất bại");
                return;
            }
            this.submittingReport = true;
            try {
                const formData = new FormData();
                formData.append('id_phan_cong', this.currentMission.id_phan_cong);
                formData.append('ket_qua', this.reportResult === 'success' ? 'HOAN_THANH' : 'THAT_BAI');
                if (this.reportResult === 'failure') {
                    formData.append('ly_do_that_bai', this.reportReason || this.reportNotes);
                }
                formData.append('bao_cao_hien_truong', this.reportNotes || '');
                if (this.reportImage) {
                    formData.append('hinh_anh', this.reportImage);
                }
                await rescuerAPI.guiBaoCao(formData);
                if (this.reportModalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(this.reportModalEl);
                    if (modal) modal.hide();
                }
                toaster.success("Đã gửi báo cáo kết quả");
                this.$router.push("/rescuer/da-xu-ly");
            } catch (e) {
                console.error("Lỗi gửi báo cáo:", e);
                toaster.error("Không thể gửi báo cáo. Vui lòng thử lại.");
            } finally {
                this.submittingReport = false;
            }
        },
        async submitReinforce() {
            if (!this.reinforceMessage.trim()) {
                toaster.error("Vui lòng nhập nội dung yêu cầu tiếp viện");
                return;
            }
            this.submittingReinforce = true;
            try {
                toaster.success("Đã gửi yêu cầu tiếp viện. Quản trị viên sẽ được thông báo.");
                if (this.reinforceModalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(this.reinforceModalEl);
                    if (modal) modal.hide();
                }
            } catch (e) {
                console.error("Lỗi gửi yêu cầu:", e);
                toaster.error("Không thể gửi yêu cầu tiếp viện");
            } finally {
                this.submittingReinforce = false;
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
            } catch (error) {
                this.mapError = error?.message || "Không thể tải bản đồ OpenMap";
                console.error('[DangXuLy] OpenMap load error:', error);
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
                // Home marker: team HQ location
                if (this.teamLat && this.teamLng) {
                    this.addTeamMarker(this.teamLat, this.teamLng);
                }
                // GPS marker: current member location
                if (this.memberLat && this.memberLng) {
                    this.addGpsMarker(this.memberLat, this.memberLng);
                    if (!this.teamLat && !this.teamLng) {
                        this.addTeamMarker(this.memberLat, this.memberLng);
                    }
                }
                this.updateMapMission();
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
        async updateMapMission() {
            if (!this.map || !this.currentMission) return;
            const lat = this.currentMission.yeu_cau?.vi_tri_lat;
            const lng = this.currentMission.yeu_cau?.vi_tri_lng;
            if (lat && lng) {
                if (this.requestMarker) this.requestMarker.remove();
                const sev = this.currentMission.yeu_cau?.muc_do_khan_cap?.toUpperCase() || '';
                const colorMap = { 'CRITICAL': '#7f1d1d', 'HIGH': '#dc2626', 'MEDIUM': '#f97316', 'LOW': '#22c55e' };
                const color = colorMap[sev] || '#dc2626';

                this.requestMarker = createOpenMapMarker({
                    position: { lng: Number(lng), lat: Number(lat) },
                    fillColor: color,
                    label: { text: '!' },
                    title: this.currentMission.yeu_cau?.chi_tiet || this.getIncidentTypeName(this.currentMission),
                });

                const typeName = this.currentMission.yeu_cau?.chi_tiet || this.getIncidentTypeName(this.currentMission);
                const address = this.getRequestAddress(this.currentMission);
                const popup = createOpenMapPopup()
                    .setHTML(`<div style="min-width:200px;font-family:Inter,sans-serif;padding:4px"><h6 style="margin:0 0 4px;font-weight:700;color:#1e293b">${typeName}</h6><p style="margin:0;font-size:12px;color:#64748b">${address || 'Chưa có địa chỉ'}</p></div>`);

                this.requestMarker.getElement().addEventListener('click', () => {
                    popup.setLngLat([Number(lng), Number(lat)]).addTo(this.map);
                });

                this.requestMarker.addTo(this.map);

                // Draw driving route from current GPS location to request
                if (this.memberLat && this.memberLng) {
                    await this.drawDrivingRoute(this.memberLat, this.memberLng, lat, lng);
                }

                this.map.flyTo({ center: [Number(lng), Number(lat)], zoom: 15 });
            }
        },
        async drawDrivingRoute(lat1, lng1, lat2, lng2) {
            this.cleanupRoute();
            try {
                const response = await fetch(
                    `https://router.project-osrm.org/route/v1/driving/${lng1},${lat1};${lng2},${lat2}?overview=full&geometries=geojson`
                );
                const data = await response.json();
                if (data.routes && data.routes.length > 0) {
                    const coordinates = data.routes[0].geometry.coordinates.map(coord => [coord[0], coord[1]]);
                    this.addDrivingRouteToMap(coordinates);
                    this.fitBoundsToMap(this.map, [[lng1, lat1], ...coordinates, [lng2, lat2]]);
                } else {
                    this.drawDrivingFallback(lat1, lng1, lat2, lng2);
                }
            } catch (error) {
                this.drawDrivingFallback(lat1, lng1, lat2, lng2);
            }
        },
        addDrivingRouteToMap(coordinates) {
            const routeGeoJSON = { type: 'Feature', geometry: { type: 'LineString', coordinates } };
            this.map.addSource('route', { type: 'geojson', data: routeGeoJSON });
            this.map.addLayer({
                id: 'route-line',
                type: 'line',
                source: 'route',
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: { 'line-color': '#2563eb', 'line-width': 5, 'line-opacity': 0.8 },
            });
            this.routeSource = 'route';
            this.routeLayer = 'route-line';
        },
        drawDrivingFallback(lat1, lng1, lat2, lng2) {
            const coords = [[Number(lng1), Number(lat1)], [Number(lng2), Number(lat2)]];
            const routeGeoJSON = { type: 'Feature', geometry: { type: 'LineString', coordinates: coords } };
            if (this.map.getLayer('route-line')) this.map.removeLayer('route-line');
            if (this.map.getSource('route')) this.map.removeSource('route');
            this.map.addSource('route', { type: 'geojson', data: routeGeoJSON });
            this.map.addLayer({
                id: 'route-line',
                type: 'line',
                source: 'route',
                paint: { 'line-color': '#2563eb', 'line-width': 4, 'line-opacity': 0.6 },
            });
            this.routeSource = 'route';
            this.routeLayer = 'route-line';
        },
        startLocationBroadcasting() {
            this.stopLocationBroadcasting(); // Clear any existing interval
            if (!this.currentMission) return;

            this.locationBroadcastInterval = setInterval(async () => {
                if (!this.currentMission || !navigator.geolocation) return;

                try {
                    const position = await this.getCurrentPosition();
                    if (position && this.currentMission.id_phan_cong) {
                        await assignmentAPI.updateLocation(this.currentMission.id_phan_cong, {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        });
                        console.log('[Location] Broadcasted:', position.coords.latitude, position.coords.longitude);
                    }
                } catch (error) {
                    console.warn('[Location] Failed to broadcast location:', error);
                }
            }, 10000); // Broadcast every 10 seconds
        },
        stopLocationBroadcasting() {
            if (this.locationBroadcastInterval) {
                clearInterval(this.locationBroadcastInterval);
                this.locationBroadcastInterval = null;
            }
        },
        getCurrentPosition() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation not supported'));
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    resolve,
                    reject,
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 10000
                    }
                );
            });
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
        zoomIn() { if (this.map) this.map.zoomIn(); },
        zoomOut() { if (this.map) this.map.zoomOut(); },
        locateMe() {
            this.refreshGpsMarker();
        },
        formatTime(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
        },
        getPriorityBadge() {
            const mucDo = this.currentMission?.yeu_cau?.muc_do_khan_cap?.toUpperCase() || '';
            if (mucDo === 'CRITICAL' || mucDo === 'HIGH') return 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
            if (mucDo === 'MEDIUM') return 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25';
            return 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
        },
        getPriorityText() {
            const mucDo = this.currentMission?.yeu_cau?.muc_do_khan_cap?.toUpperCase() || '';
            if (mucDo === 'CRITICAL' || mucDo === 'HIGH') return 'Khẩn cấp';
            if (mucDo === 'MEDIUM') return 'Trung bình';
            return 'Thường';
        },
        getPriorityBannerClass() {
            const mucDo = this.currentMission?.yeu_cau?.muc_do_khan_cap?.toUpperCase() || '';
            if (mucDo === 'CRITICAL' || mucDo === 'HIGH') return 'banner-danger';
            if (mucDo === 'MEDIUM') return 'banner-warning';
            return 'banner-success';
        },
        getStatusBadgeClass() {
            if (this.missionStep === 1) return 'badge-step1';
            if (this.missionStep === 2) return 'badge-step2';
            if (this.missionStep === 3) return 'badge-step3';
            return 'badge-step4';
        },
        getStatusText() {
            if (this.missionStep === 1) return 'Đã tiếp nhận';
            if (this.missionStep === 2) return 'Đang di chuyển';
            if (this.missionStep === 3) return 'Tại hiện trường';
            return 'Hoàn thành';
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
            if (item?.yeu_cau?.loai_su_co) {
                return item.yeu_cau.loai_su_co.ten_danh_muc || item.yeu_cau.loai_su_co.ten_loai_su_co || 'Yêu cầu cứu hộ';
            }
            return 'Yêu cầu cứu hộ';
        },
        getRequestAddress(item) {
            if (item?.yeu_cau) {
                if (item.yeu_cau.vi_tri_dia_chi) return item.yeu_cau.vi_tri_dia_chi;
                if (item.yeu_cau.dia_chi) return item.yeu_cau.dia_chi;
                if (item.yeu_cau.vi_tri) return item.yeu_cau.vi_tri;
            }
            return 'Chưa có địa chỉ';
        },
        getReporterName(item) {
            if (item?.yeu_cau) {
                if (item.yeu_cau.ho_ten_nguoi_dung) return item.yeu_cau.ho_ten_nguoi_dung;
                if (item.yeu_cau.hoTenNguoiDung) return item.yeu_cau.hoTenNguoiDung;
                if (item.yeu_cau.nguoi_dung) {
                    return item.yeu_cau.nguoi_dung.ho_ten || item.yeu_cau.nguoi_dung.hoTen || 'Không rõ';
                }
            }
            return 'Không rõ';
        },
        getReporterPhone(item) {
            if (item?.yeu_cau) {
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
/* ─── Layout ─────────────────────────────────────────────────────────── */
.processing-page {
    margin: -1.5rem -1.5rem -2rem;
    height: calc(100vh - 72px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #f8fafc;
}

/* ─── Header Bar ────────────────────────────────────────────────────── */
.page-header {
    background: #1e293b;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    flex-shrink: 0;
}

.header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 60px;
    padding: 0 1.5rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.back-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1.5px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s;
}

.back-btn:hover {
    background: rgba(255,255,255,0.12);
    color: white;
    transform: translateX(-2px);
}

.header-title-group {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.header-status-indicator {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.status-bar-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #ef4444;
    animation: status-pulse 1.5s infinite;
}

@keyframes status-pulse {
    0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(239,68,69,0.6); }
    50% { opacity: 0.8; box-shadow: 0 0 0 5px rgba(239,68,69,0); }
}

.status-bar-label {
    font-size: 0.65rem;
    font-weight: 700;
    color: #ef4444;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.header-title {
    font-size: 1rem;
    font-weight: 700;
    color: #f8fafc;
    margin: 0;
    line-height: 1.2;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.mission-id-tag {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    background: rgba(37,99,235,0.25);
    color: #93c5fd;
    font-size: 0.72rem;
    font-weight: 700;
    font-family: monospace;
}

/* ─── Content Layout ─────────────────────────────────────────────────── */
.content-layout {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

/* ─── Detail Panel ──────────────────────────────────────────────────── */
.detail-panel {
    width: 420px;
    min-width: 420px;
    background: #ffffff;
    border-right: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.panel-empty {
    justify-content: center;
}

.panel-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1;
    gap: 1rem;
}

.loading-spinner {
    width: 36px;
    height: 36px;
    border: 3px solid #e2e8f0;
    border-top-color: #2563eb;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.loading-text {
    font-size: 0.82rem;
    color: #94a3b8;
    font-weight: 500;
}

.mission-content {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.mission-content::-webkit-scrollbar {
    width: 5px;
}
.mission-content::-webkit-scrollbar-track { background: transparent; }
.mission-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

/* ─── Priority Banner ────────────────────────────────────────────────── */
.priority-banner {
    border-radius: 14px;
    padding: 0.85rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.banner-danger { background: linear-gradient(135deg, #fef2f2, #fee2e2); border: 1px solid #fecaca; }
.banner-warning { background: linear-gradient(135deg, #fefce8, #fef9c3); border: 1px solid #fde047; }
.banner-success { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0; }

.banner-priority-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.banner-danger .banner-priority-icon { background: #dc2626; color: white; }
.banner-warning .banner-priority-icon { background: #ca8a04; color: white; }
.banner-success .banner-priority-icon { background: #16a34a; color: white; }

.banner-text {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.banner-level {
    font-size: 0.85rem;
    font-weight: 800;
}

.banner-danger .banner-level { color: #991b1b; }
.banner-warning .banner-level { color: #713f12; }
.banner-success .banner-level { color: #14532d; }

.banner-type {
    font-size: 0.7rem;
    font-weight: 600;
    opacity: 0.6;
}

/* ─── Incident Section ─────────────────────────────────────────────── */
.incident-section {
    padding: 0;
}

.incident-top-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.incident-name {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
    flex: 1;
    min-width: 0;
}

.incident-id-chip {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.65rem;
    border-radius: 20px;
    background: #1e293b;
    color: #93c5fd;
    font-size: 0.72rem;
    font-weight: 700;
    font-family: monospace;
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.incident-meta {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.meta-chip {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.65rem;
    border-radius: 20px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 0.72rem;
    font-weight: 600;
}

/* ─── Info Card ─────────────────────────────────────────────────────── */
.info-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 1rem;
}

.card-header-row {
    margin-bottom: 0.75rem;
}

.section-label {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.68rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.victim-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.victim-avatar-lg {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #1e293b;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.victim-details {
    flex: 1;
    min-width: 0;
}

.victim-name-lg {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
}

.victim-phone-lg {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
    margin-top: 0.15rem;
}

.call-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #22c55e;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s;
    flex-shrink: 0;
}

.call-btn:hover {
    background: #16a34a;
    transform: scale(1.05);
}

.location-text {
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
    line-height: 1.4;
    margin-bottom: 0.75rem;
}

.nav-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border-radius: 10px;
    background: #dc2626;
    color: white;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}

.nav-btn:hover {
    background: #b91c1c;
    color: white;
}

/* ─── Progress Tracker ──────────────────────────────────────────────── */
.progress-card {
    background: #f8fafc;
}

.progress-steps {
    display: flex;
    align-items: flex-start;
    position: relative;
    padding-top: 0.5rem;
}

.step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    position: relative;
}

.step-node {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2.5px solid #e2e8f0;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
    color: #94a3b8;
    position: relative;
    z-index: 1;
    transition: all 0.3s;
}

.step-done .step-node {
    background: #22c55e;
    border-color: #22c55e;
    color: white;
}

.step-active .step-node {
    background: #2563eb;
    border-color: #2563eb;
    color: white;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.15);
}

.step-label {
    font-size: 0.68rem;
    font-weight: 700;
    color: #94a3b8;
    text-align: center;
    transition: color 0.3s;
}

.step-done .step-label { color: #22c55e; }
.step-active .step-label { color: #2563eb; }

.step-connector {
    position: absolute;
    top: 18px;
    left: calc(-50% + 18px);
    right: calc(50% + 18px);
    height: 2.5px;
    background: #e2e8f0;
    z-index: 0;
    transition: background 0.3s;
}

.step-connector.connector-active {
    background: #22c55e;
}

.progress-card .step-connector:last-child {
    display: none;
}

/* ─── Action Buttons ────────────────────────────────────────────────── */
.action-group {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    padding-top: 0.25rem;
}

.action-primary,
.action-success {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: all 0.25s;
    width: 100%;
}

.action-primary {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
}

.action-primary:hover {
    background: linear-gradient(135deg, #b91c1c, #991b1b);
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(220,38,38,0.35);
}

.action-success {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white;
}

.action-success:hover {
    background: linear-gradient(135deg, #15803d, #166534);
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(22,163,74,0.35);
}

.action-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.action-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.action-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: white;
}

.action-sub {
    font-size: 0.72rem;
    font-weight: 500;
    color: rgba(255,255,255,0.7);
}

.action-support {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.7rem 1rem;
    border-radius: 12px;
    border: 1.5px dashed #e2e8f0;
    background: transparent;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}

.action-support:hover {
    border-color: #ca8a04;
    color: #ca8a04;
    background: #fefce8;
}

/* ─── Empty State ──────────────────────────────────────────────────── */
.panel-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
}

.empty-icon-wrap {
    color: #cbd5e1;
    margin-bottom: 1.25rem;
}

.empty-heading {
    font-size: 1rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 0.35rem;
}

.empty-desc {
    font-size: 0.78rem;
    color: #94a3b8;
    font-weight: 500;
    margin-bottom: 1.5rem;
}

.empty-action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.25rem;
    border-radius: 12px;
    background: #dc2626;
    color: white;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}

.empty-action-btn:hover {
    background: #b91c1c;
    color: white;
    transform: translateY(-1px);
}

/* ─── Map Panel ─────────────────────────────────────────────────────── */
.map-panel {
    flex: 1;
    position: relative;
    background: #e2e8f0;
}

.map-canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.map-overlay {
    position: absolute;
    inset: 0;
    background: rgba(248,250,252,0.92);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.map-overlay-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    text-align: center;
    max-width: 320px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.overlay-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e2e8f0;
    border-top-color: #2563eb;
    border-radius: 50%;
    margin: 0 auto 1rem;
    animation: spin 0.8s linear infinite;
}

.overlay-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.overlay-icon-error {
    background: #fef2f2;
    color: #dc2626;
}

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

/* ─── Map Controls ──────────────────────────────────────────────────── */
.map-controls {
    position: absolute;
    bottom: 1.5rem;
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

.map-btn:hover {
    background: #f8fafc;
    transform: scale(1.05);
}

.map-btn-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 0.1rem 0;
}

.map-btn-primary {
    color: #2563eb;
}

.map-btn-primary:hover {
    background: #eff6ff;
    color: #1d4ed8;
}

/* ─── Map Legend ────────────────────────────────────────────────────── */
.map-legend {
    position: absolute;
    bottom: 1.5rem;
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

/* ─── Mission Status Badge ─────────────────────────────────────────── */
.mission-status-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 15;
}

.status-badge-inner {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.85rem;
    border-radius: 20px;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    font-size: 0.72rem;
    font-weight: 700;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.badge-step1 .status-dot { background: #3b82f6; }
.badge-step1 { color: #1d4ed8; }
.badge-step2 .status-dot { background: #f97316; }
.badge-step2 { color: #c2410c; }
.badge-step3 .status-dot { background: #22c55e; }
.badge-step3 { color: #16a34a; }
.badge-step4 .status-dot { background: #10b981; animation: status-pulse 1.5s infinite; }
.badge-step4 { color: #059669; }

/* ─── Modal Styles ──────────────────────────────────────────────────── */
.modal-content {
    border: none;
    border-radius: 20px;
    box-shadow: 0 24px 48px rgba(0,0,0,0.15);
    font-family: 'Inter', sans-serif;
}

.modal-header-row {
    padding: 1.25rem 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: none;
}

.modal-header-warning {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 20px 20px 0 0;
    padding-bottom: 1rem;
}

.modal-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.modal-icon-report {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #2563eb;
}

.modal-icon-warning {
    background: rgba(180, 83, 9, 0.15);
    color: #92400e;
}

.modal-title {
    flex: 1;
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}

.modal-close-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.modal-close-btn:hover {
    background: #e2e8f0;
    color: #334155;
}

.modal-body {
    padding: 1.25rem 1.5rem;
}

.modal-footer-row {
    padding: 0 1.5rem 1.5rem;
    display: flex;
    gap: 0.6rem;
    justify-content: flex-end;
}

/* Result Toggle */
.result-toggle {
    display: flex;
    gap: 0.6rem;
    margin-bottom: 1.25rem;
}

.result-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.75rem;
    border-radius: 12px;
    border: 1.5px solid #e2e8f0;
    background: transparent;
    font-size: 0.82rem;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}

.result-btn:hover {
    border-color: #cbd5e1;
    color: #334155;
}

.result-btn-active { font-weight: 800; }

.result-btn-success {
    background: #f0fdf4;
    border-color: #22c55e;
    color: #16a34a;
}

.result-btn-danger {
    background: #fef2f2;
    border-color: #dc2626;
    color: #dc2626;
}

/* Form Groups */
.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.4rem;
}

.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border-radius: 12px;
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    font-size: 0.85rem;
    font-family: 'Inter', sans-serif;
    color: #334155;
    resize: vertical;
    transition: border-color 0.2s;
}

.form-textarea:focus {
    outline: none;
    border-color: #2563eb;
    background: white;
}

/* File Upload */
.file-upload-area {
    border: 2px dashed #e2e8f0;
    border-radius: 14px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}

.file-upload-area:hover {
    border-color: #2563eb;
    background: #eff6ff;
}

.file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: #94a3b8;
}

.upload-placeholder span {
    font-size: 0.82rem;
    font-weight: 600;
}

.upload-hint {
    font-size: 0.7rem !important;
    font-weight: 500 !important;
    color: #cbd5e1 !important;
}

.image-preview {
    position: relative;
    display: inline-block;
}

.image-preview img {
    max-height: 150px;
    max-width: 100%;
    border-radius: 10px;
}

.preview-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: none;
    background: #dc2626;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.preview-remove:hover { background: #b91c1c; }

/* Reinforce Alert */
.reinforce-alert {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    background: #fef3c7;
    color: #92400e;
    font-size: 0.78rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

/* Modal Buttons */
.btn-cancel {
    padding: 0.6rem 1.25rem;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    background: transparent;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Inter', sans-serif;
}

.btn-cancel:hover {
    border-color: #cbd5e1;
    color: #334155;
    background: #f8fafc;
}

.btn-submit {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.25rem;
    border-radius: 10px;
    border: none;
    color: white;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Inter', sans-serif;
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-submit-success { background: #16a34a; }
.btn-submit-success:hover:not(:disabled) { background: #15803d; }
.btn-submit-danger { background: #dc2626; }
.btn-submit-danger:hover:not(:disabled) { background: #b91c1c; }
.btn-submit-warning { background: #d97706; }
.btn-submit-warning:hover:not(:disabled) { background: #b45309; }

.btn-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
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
    .content-layout {
        flex-direction: column;
    }

    .detail-panel {
        width: 100%;
        min-width: 100%;
        height: 50%;
        border-right: none;
        border-bottom: 1px solid #e2e8f0;
    }

    .map-panel {
        height: 50%;
        flex: 1;
    }

    .mission-status-badge {
        top: 0.75rem;
        right: 0.75rem;
    }
}

@media (max-width: 640px) {
    .page-header {
        padding: 0 1rem;
    }

    .action-primary,
    .action-success {
        padding: 0.75rem;
    }

    .action-icon {
        width: 38px;
        height: 38px;
    }

    .map-controls {
        bottom: 1rem;
        right: 0.75rem;
    }

    .mission-content {
        padding: 1rem;
    }
}
</style>
