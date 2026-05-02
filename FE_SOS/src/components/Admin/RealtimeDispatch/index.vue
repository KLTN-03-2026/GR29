<template>
  <div class="dispatch-page">
    <div class="container-fluid py-4">

      <!-- ===== HEADER ===== -->
      <div class="row align-items-start mb-4 g-3">
        <div class="col-xl-5">
          <div class="d-flex align-items-center gap-3 mb-1">
            <div class="header-icon-wrap">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M13 3L4 14H12L11 21L20 10H12L13 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div>
              <h2 class="page-title mb-0">Điều Phối Tự Động</h2>
              <p class="text-muted mb-0" style="font-size: 0.85rem;">Cấu hình & giám sát hệ thống Auto-dispatch</p>
            </div>
          </div>
        </div>
        <div class="col-xl-7">
          <div class="d-flex justify-content-xl-end align-items-center gap-3 flex-wrap">

            <!-- Master Toggle -->
            <div class="master-toggle-card" :class="{ 'is-active': dispatchEnabled }">
              <div class="d-flex align-items-center gap-3">
                <div class="master-toggle-icon" :class="dispatchEnabled ? 'icon-active' : 'icon-inactive'">
                  <svg v-if="dispatchEnabled" width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M13 3L4 14H12L11 21L20 10H12L13 3Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                    <path d="M9 9L15 15M15 9L9 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </div>
                <div>
                  <span class="master-toggle-label">Auto-dispatch</span>
                  <div class="master-toggle-status" :class="dispatchEnabled ? 'status-on' : 'status-off'">
                    <span class="status-dot"></span>
                    {{ dispatchEnabled ? 'Đang hoạt động' : 'Đã tắt' }}
                  </div>
                </div>
              </div>
              <button
                class="master-toggle-btn"
                :class="{ 'active': dispatchEnabled }"
                @click="toggleDispatch"
                :aria-label="dispatchEnabled ? 'Tắt Auto-dispatch' : 'Bật Auto-dispatch'"
              >
                <span class="toggle-track">
                  <span class="toggle-thumb"></span>
                </span>
              </button>
            </div>

            <!-- Refresh -->
            <button class="icon-action-btn" @click="refreshAll" :disabled="loading" title="Làm mới">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" :class="{ 'spin': loading }">
                <path d="M4 12C4 7.58172 7.58172 4 12 4C15.0736 4 17.7248 5.80151 19 8.4M20 12C20 16.4183 16.4183 20 12 20C8.92638 20 6.27515 18.1985 5 15.6M4 4V8H8M20 20V16H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- ===== STATS ROW ===== -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3" v-for="(stat, idx) in statsCards" :key="stat.label">
          <div class="stat-card" :style="{ '--delay': idx * 60 + 'ms' }">
            <div class="stat-top">
              <div class="stat-icon-wrap" :style="{ background: stat.iconBg, color: stat.iconColor }">
                <svg v-if="stat.icon === 'total'" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M9 12h6M9 16h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <svg v-else-if="stat.icon === 'auto'" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M13 3L4 14H12L11 21L20 10H12L13 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <svg v-else-if="stat.icon === 'pending'" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <svg v-else-if="stat.icon === 'escalate'" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 19V5M5 12l7-7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <svg v-else-if="stat.icon === 'retry'" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 12C4 7.58172 7.58172 4 12 4C15.0736 4 17.7248 5.80151 19 8.4M20 12C20 16.4183 16.4183 20 12 20C8.92638 20 6.27515 18.1985 5 15.6M4 4V8H8M20 20V16H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </div>
              <div class="stat-trend-badge" :style="{ background: stat.trendBg, color: stat.trendColor }" v-if="stat.trendText">
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path :d="stat.trendIcon === 'up' ? 'M12 19V5M5 12l7-7 7 7' : 'M12 5v14M19 12l-7 7-7-7'" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ stat.trendText }}
              </div>
            </div>
            <h3 class="stat-value mb-1" :style="{ color: stat.valueColor }">{{ stat.value }}</h3>
            <p class="stat-label mb-0">{{ stat.label }}</p>
            <div v-if="stat.progress !== undefined" class="stat-progress">
              <div class="progress-track">
                <div class="progress-fill" :style="{ width: stat.progress + '%', background: stat.progressColor }"></div>
              </div>
              <span class="progress-label">{{ stat.progress }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== MAIN CONTENT ===== -->
      <div class="row g-4">

        <!-- ===== LEFT PANEL: Cấu hình ===== -->
        <div class="col-xl-5">
          <div class="config-panel">

            <!-- Panel Header -->
            <div class="panel-header">
              <div class="d-flex align-items-center gap-2">
                <div class="panel-icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                </div>
                <h5 class="panel-title mb-0">Cấu Hình Hệ Thống</h5>
              </div>
              <button class="btn-save-float" :class="{ 'saving': saving }" @click="saveConfig" :disabled="saving">
                <svg v-if="!saving" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/><polyline points="17 21 17 13 7 13 7 21" stroke="currentColor" stroke-width="2"/><polyline points="7 3 7 8 15 8" stroke="currentColor" stroke-width="2"/></svg>
                <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" class="spin"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="40" stroke-dashoffset="20"/></svg>
                {{ saving ? 'Đang lưu...' : 'Lưu' }}
              </button>
            </div>

            <!-- Panel Body -->
            <div class="panel-body">

              <!-- ===== DISPATCH MODE SELECTOR ===== -->
              <div class="config-section">
                <div class="section-title">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  Chế độ điều phối
                </div>

                <div class="mode-selector">
                  <button
                    class="mode-option"
                    :class="{ 'active': dispatchMode === 'normal' }"
                    @click="dispatchMode = 'normal'"
                  >
                    <div class="mode-option-icon mode-icon-normal" :class="{ 'active': dispatchMode === 'normal' }">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M13 3L4 14H12L11 21L20 10H12L13 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div class="mode-option-info">
                      <span class="mode-option-title">Auto-dispatch tiêu chuẩn</span>
                      <span class="mode-option-desc">Ưu tiên đội có điểm tổng cao nhất</span>
                    </div>
                    <div class="mode-option-check" v-if="dispatchMode === 'normal'">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                  </button>

                  <button
                    class="mode-option"
                    :class="{ 'active': dispatchMode === 'custom' }"
                    @click="dispatchMode = 'custom'"
                  >
                    <div class="mode-option-icon mode-icon-custom" :class="{ 'active': dispatchMode === 'custom' }">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div class="mode-option-info">
                      <span class="mode-option-title">Tùy chỉnh nâng cao</span>
                      <span class="mode-option-desc">Cấu hình chi tiết thứ tự ưu tiên theo loại sự cố</span>
                    </div>
                    <div class="mode-option-check" v-if="dispatchMode === 'custom'">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                  </button>
                </div>
              </div>

              <!-- ===== CUSTOM MODE: Priority Rules ===== -->
              <div v-if="dispatchMode === 'custom'" class="config-section">
                <div class="section-title">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" stroke="currentColor" stroke-width="2"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                  Quy tắc ưu tiên tùy chỉnh
                  <span class="badge-count">{{ customRules.length }}</span>
                </div>
                <p class="section-desc">Kéo thả để sắp xếp thứ tự ưu tiên. Loại sự cố trên cùng sẽ được xử lý trước.</p>

                <!-- Add Rule -->
                <div class="add-rule-zone" @click="showRuleModal = true">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  <span>Thêm quy tắc ưu tiên</span>
                </div>

                <!-- Rules List -->
                <div class="rules-list" v-if="customRules.length > 0">
                  <div
                    v-for="(rule, idx) in customRules"
                    :key="rule.id"
                    class="rule-card"
                  >
                    <div class="rule-drag-handle">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="6" r="1.5" fill="currentColor"/><circle cx="15" cy="6" r="1.5" fill="currentColor"/><circle cx="9" cy="12" r="1.5" fill="currentColor"/><circle cx="15" cy="12" r="1.5" fill="currentColor"/><circle cx="9" cy="18" r="1.5" fill="currentColor"/><circle cx="15" cy="18" r="1.5" fill="currentColor"/></svg>
                    </div>
                    <div class="rule-rank">#{{ idx + 1 }}</div>
                    <div class="rule-color-bar" :style="{ background: rule.color }"></div>
                    <div class="rule-info flex-grow-1">
                      <div class="rule-name">{{ rule.name }}</div>
                      <div class="rule-meta">
                        <span class="rule-category">{{ rule.category }}</span>
                        <span v-if="rule.detailType" class="rule-dot-sep"></span>
                        <span v-if="rule.detailType" class="rule-detail-type">{{ rule.detailType }}</span>
                      </div>
                    </div>
                    <div class="rule-actions">
                      <button class="rule-action-btn" @click="editRule(rule)" title="Sửa">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2"/></svg>
                      </button>
                      <button class="rule-action-btn rule-action-delete" @click="removeRule(rule.id)" title="Xóa">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" stroke="currentColor" stroke-width="2"/></svg>
                      </button>
                    </div>
                  </div>
                </div>

                <div v-else class="empty-rules">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" stroke="currentColor" stroke-width="1.5"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="1.5"/></svg>
                  <p>Chưa có quy tắc ưu tiên</p>
                  <small>Bấm "Thêm quy tắc" để bắt đầu cấu hình</small>
                </div>
              </div>

              <!-- ===== SCORING CONFIG (Normal mode) ===== -->
              <div v-if="dispatchMode === 'normal'" class="config-section">
                <div class="section-title">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  Trọng số tính điểm
                </div>
                <p class="section-desc">Cấu hình trọng số cho từng yếu tố khi tính điểm tổng của đội cứu hộ.</p>

                <div class="weight-items">
                  <div class="weight-item" v-for="w in scoringWeights" :key="w.key">
                    <div class="weight-header">
                      <span class="weight-label">{{ w.label }}</span>
                      <span class="weight-value-badge" :style="{ color: w.color, background: w.color + '18' }">{{ w.value }}%</span>
                    </div>
                    <input
                      type="range"
                      class="weight-slider"
                      :style="{ '--w-color': w.color }"
                      min="0"
                      max="100"
                      step="5"
                      v-model.number="w.value"
                    />
                    <div class="weight-hints">
                      <small>{{ w.minLabel }}</small>
                      <small>{{ w.maxLabel }}</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ===== General Settings ===== -->
              <div class="config-section config-section-last">
                <div class="section-title">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="2"/></svg>
                  Cài đặt chung
                </div>

                <!-- Delay -->
                <div class="setting-item">
                  <div class="setting-info">
                    <span class="setting-label">Thời gian chờ tự động gán</span>
                    <small class="setting-desc">Sau khi có yêu cầu mới</small>
                  </div>
                  <div class="setting-control">
                    <input type="number" class="setting-input" v-model.number="generalConfig.autoAssignDelay" min="5" max="300" />
                    <span class="setting-unit">giây</span>
                  </div>
                </div>

                <!-- Max Distance -->
                <div class="setting-item">
                  <div class="setting-info">
                    <span class="setting-label">Bán kính tìm kiếm tối đa</span>
                    <small class="setting-desc">Giới hạn bán kính tìm đội cứu hộ</small>
                  </div>
                  <div class="setting-control">
                    <input type="number" class="setting-input" v-model.number="generalConfig.maxDistance" min="1" max="100" />
                    <span class="setting-unit">km</span>
                  </div>
                </div>

                <!-- Max Teams -->
                <div class="setting-item">
                  <div class="setting-info">
                    <span class="setting-label">Số đội tối đa mỗi sự cố</span>
                    <small class="setting-desc">Số lượng đội cứu hộ được gán tối đa</small>
                  </div>
                  <div class="setting-control">
                    <input type="number" class="setting-input" v-model.number="generalConfig.maxTeamsPerIncident" min="1" max="10" />
                    <span class="setting-unit">đội</span>
                  </div>
                </div>

                <!-- Auto Escalate -->
                <div class="setting-item">
                  <div class="setting-info">
                    <span class="setting-label">Tự động leo thang</span>
                    <small class="setting-desc">Leo thang lên điều phối viên khi hết thời gian chờ</small>
                  </div>
                  <button class="toggle-mini" :class="{ 'active': generalConfig.autoEscalate }" @click="generalConfig.autoEscalate = !generalConfig.autoEscalate">
                    <span class="toggle-mini-thumb"></span>
                  </button>
                </div>

                <!-- Notify Operator -->
                <div class="setting-item setting-item-last">
                  <div class="setting-info">
                    <span class="setting-label">Thông báo điều phối viên</span>
                    <small class="setting-desc">Gửi thông báo khi có auto-dispatch</small>
                  </div>
                  <button class="toggle-mini" :class="{ 'active': generalConfig.notifyOperator }" @click="generalConfig.notifyOperator = !generalConfig.notifyOperator">
                    <span class="toggle-mini-thumb"></span>
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- ===== RIGHT PANEL: Giám sát ===== -->
        <div class="col-xl-7">

          <!-- Status Banner -->
          <div class="status-banner" :class="dispatchEnabled ? 'banner-active' : 'banner-inactive'">
            <div class="banner-left">
              <div class="banner-icon-wrap">
                <div class="banner-icon" :class="dispatchEnabled ? 'icon-running' : 'icon-stopped'">
                  <svg v-if="dispatchEnabled" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M13 3L4 14H12L11 21L20 10H12L13 3Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M9 9L15 15M15 9L9 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div v-if="dispatchEnabled" class="banner-pulse"></div>
              </div>
              <div>
                <h5 class="banner-title mb-1">{{ dispatchEnabled ? 'Hệ thống Auto-dispatch đang hoạt động' : 'Hệ thống Auto-dispatch đã tắt' }}</h5>
                <p class="banner-sub mb-0">
                  <template v-if="dispatchEnabled">
                    {{ dispatchMode === 'normal' ? 'Chế độ tiêu chuẩn' : 'Chế độ tùy chỉnh' }} ·
                    {{ customRules.length > 0 ? customRules.length + ' quy tắc ưu tiên' : 'Điểm tổng cao nhất' }} ·
                    {{ monitoredStats.totalRequests }} yêu cầu giám sát
                  </template>
                  <template v-else>Bật công tắc để kích hoạt điều phối tự động</template>
                </p>
              </div>
            </div>
            <div v-if="dispatchEnabled" class="banner-right">
              <div class="uptime-block">
                <small class="uptime-label">Uptime</small>
                <div class="uptime-value">{{ uptimeDisplay }}</div>
              </div>
              <div class="dispatch-rate-block">
                <small class="uptime-label">Tỷ lệ thành công</small>
                <div class="uptime-value" style="color: #16a34a;">{{ successRate }}%</div>
              </div>
            </div>
          </div>

          

          <!-- Priority Chain Preview -->
          <div class="priority-preview-panel mb-4" v-if="dispatchMode === 'custom' && customRules.length > 0">
            <div class="panel-header-sm">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" stroke="currentColor" stroke-width="2"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
              Thứ tự ưu tiên đang áp dụng
            </div>
            <div class="priority-chain">
              <div v-for="(rule, idx) in customRules" :key="rule.id" class="priority-chain-item">
                <div class="chain-rank">{{ idx + 1 }}</div>
                <div class="chain-bar" :style="{ background: rule.color }"></div>
                <div class="chain-text">
                  <span class="chain-name">{{ rule.name }}</span>
                  <span class="chain-type">{{ rule.detailType || 'Tất cả chi tiết' }}</span>
                </div>
              </div>
              <div class="priority-chain-item chain-default">
                <div class="chain-rank">*</div>
                <div class="chain-bar" style="background: #94a3b8;"></div>
                <div class="chain-text">
                  <span class="chain-name text-muted">Các loại còn lại</span>
                  <span class="chain-type text-muted">Sắp xếp theo điểm tổng</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Activity Log -->
          <div class="activity-panel">
            <div class="activity-panel-header">
              <div class="d-flex align-items-center gap-2">
                <div class="panel-icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <h5 class="panel-title mb-0">Nhật Ký Hoạt Động</h5>
                <span class="activity-count">{{ filteredActivities.length }}</span>
              </div>
              <div class="activity-filters">
                <button v-for="f in activityFilters" :key="f.value" class="filter-chip" :class="{ 'active': activityFilter === f.value }" @click="activityFilter = f.value" :style="activityFilter === f.value ? { background: f.color, borderColor: f.color, color: '#fff' } : {}">
                  {{ f.label }}
                </button>
              </div>
            </div>

            <div class="activity-list">
              <!-- Loading -->
              <div v-if="loading" class="activity-loading">
                <div class="activity-skeleton" v-for="i in 4" :key="i">
                  <div class="skeleton-circle"></div>
                  <div class="skeleton-lines">
                    <div class="skeleton-line" style="width: 60%;"></div>
                    <div class="skeleton-line" style="width: 80%;"></div>
                  </div>
                </div>
              </div>

              <!-- Empty -->
              <div v-else-if="filteredActivities.length === 0" class="activity-empty">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5"/></svg>
                <h6>Không có sự kiện</h6>
                <small>Nhật ký sẽ hiển thị khi có hoạt động auto-dispatch</small>
              </div>

              <!-- Activity Items -->
              <div v-else>
                <div v-for="(act, idx) in filteredActivities" :key="act.id" class="activity-item" :class="{ 'border-bottom': idx < filteredActivities.length - 1 }">
                  <div class="activity-dot" :style="{ background: act.dotBg, color: act.dotColor }">
                    <svg v-if="act.icon === 'bolt'" width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M13 3L4 14H12L11 21L20 10H12L13 3Z" stroke="currentColor" stroke-width="2.5"/></svg>
                    <svg v-else-if="act.icon === 'check'" width="11" height="11" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.5"/></svg>
                    <svg v-else-if="act.icon === 'arrow'" width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M12 19V5M5 12l7-7 7 7" stroke="currentColor" stroke-width="2.5"/></svg>
                    <svg v-else width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2"/></svg>
                  </div>
                  <div class="activity-content">
                    <div class="d-flex justify-content-between align-items-start w-100">
                      <div>
                        <span class="activity-title">{{ act.title }}</span>
                        <small class="activity-desc d-block">{{ act.description }}</small>
                      </div>
                      <div class="activity-meta">
                        <span class="activity-badge" :style="{ background: act.badgeBg, color: act.badgeColor }">{{ act.typeLabel }}</span>
                        <small class="activity-time">{{ act.timeAgo }}</small>
                      </div>
                    </div>
                    <div v-if="act.details && act.details.length" class="activity-tags">
                      <span class="act-tag" v-for="tag in act.details" :key="tag.text">{{ tag.text }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- ===== RULE MODAL ===== -->
    <Teleport to="body">
      <div v-if="showRuleModal" class="modal-overlay" @click.self="closeRuleModal">
        <div class="modal-box">
          <div class="modal-header">
            <h5 class="modal-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              {{ editingRule ? 'Sửa quy tắc' : 'Thêm quy tắc ưu tiên' }}
            </h5>
            <button class="modal-close" @click="closeRuleModal">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
          </div>
          <div class="modal-body">
            <!-- Incident Type -->
            <div class="form-group-custom">
              <label class="form-label-custom">Loại sự cố chính</label>
              <div class="search-select-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="search-select-icon"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" class="form-input-custom" placeholder="Tìm loại sự cố..." v-model="ruleForm.typeSearch" @focus="ruleForm.typeDropdown = true" />
                <div v-if="ruleForm.typeDropdown" class="search-select-dropdown">
                  <div v-for="t in ruleFilteredTypes" :key="t.id" class="search-select-item" @click="selectIncidentType(t)">
                    <span class="ss-dot" :style="{ background: t.mau_sac || t.color || '#6b7280' }"></span>
                    <div>
                      <span class="ss-name">{{ t.ten_loai_su_co || t.name }}</span>
                      <small class="ss-cat">{{ t.ten_danh_muc || t.category }}</small>
                    </div>
                  </div>
                  <div v-if="ruleFilteredTypes.length === 0" class="search-select-empty"><small>Không tìm thấy</small></div>
                </div>
              </div>
              <div v-if="ruleForm.selectedType" class="selected-type-chip">
                <span class="chip-dot" :style="{ background: ruleForm.selectedType.mau_sac || '#6b7280' }"></span>
                {{ ruleForm.selectedType.ten_loai_su_co || ruleForm.selectedType.name }}
                <button class="chip-remove" @click="ruleForm.selectedType = null">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2"/></svg>
                </button>
              </div>
            </div>

            <!-- Detail Type -->
            <div class="form-group-custom" v-if="ruleForm.selectedType">
              <label class="form-label-custom">Loại chi tiết <span class="optional-tag">(tùy chọn)</span></label>
              <div class="search-select-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="search-select-icon"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" class="form-input-custom" placeholder="Tìm loại chi tiết (VD: Cấp nước sạch)..." v-model="ruleForm.detailSearch" @focus="ruleForm.detailDropdown = true" />
                <div v-if="ruleForm.detailDropdown" class="search-select-dropdown">
                  <div v-for="d in ruleFilteredDetails" :key="d.id" class="search-select-item" @click="selectDetailType(d)">
                    <span class="ss-dot" :style="{ background: d.mau_sac || ruleForm.selectedType.mau_sac || '#6b7280' }"></span>
                    <span class="ss-name">{{ d.ten_chi_tiet || d.name }}</span>
                  </div>
                  <div v-if="ruleFilteredDetails.length === 0" class="search-select-empty"><small>Không có chi tiết hoặc không tìm thấy</small></div>
                </div>
              </div>
              <div v-if="ruleForm.selectedDetail" class="selected-type-chip selected-type-chip-sm">
                <span class="chip-dot" :style="{ background: ruleForm.selectedDetail.mau_sac || ruleForm.selectedType.mau_sac || '#6b7280' }"></span>
                {{ ruleForm.selectedDetail.ten_chi_tiet || ruleForm.selectedDetail.name }}
                <button class="chip-remove" @click="ruleForm.selectedDetail = null">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2"/></svg>
                </button>
              </div>
            </div>

            <!-- Priority Level -->
            <div class="form-group-custom">
              <label class="form-label-custom">Mức ưu tiên</label>
              <div class="priority-selector">
                <button v-for="p in priorityLevels" :key="p.level" class="priority-level-btn" :class="{ 'active': ruleForm.priority === p.level }" :style="ruleForm.priority === p.level ? { background: p.color, borderColor: p.color, color: '#fff' } : {}" @click="ruleForm.priority = p.level">
                  {{ p.label }}
                </button>
              </div>
            </div>

            <!-- Color -->
            <div class="form-group-custom">
              <label class="form-label-custom">Màu hiển thị</label>
              <div class="color-swatches">
                <button v-for="c in colorPalette" :key="c" class="color-swatch" :class="{ 'active': ruleForm.color === c }" :style="{ background: c }" @click="ruleForm.color = c"></button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-modal-cancel" @click="closeRuleModal">Hủy</button>
            <button class="btn-modal-save" @click="saveRule" :disabled="!ruleForm.selectedType">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.5"/></svg>
              {{ editingRule ? 'Cập nhật' : 'Thêm quy tắc' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import { incidentTypeAPI, autoDispatchAPI, rescueRequestAPI, assignmentAPI } from "../../../services/api";
import api from "../../../services/api";

export default {
  name: "RealtimeDispatch",
  data() {
    return {
      dispatchEnabled: false,
      dispatchMode: "normal",
      loading: false,
      saving: false,
      uptimeSeconds: 0,
      uptimeInterval: null,
      showRuleModal: false,
      editingRule: null,
      customRules: [],
      incidentTypes: [],
      incidentTypeDetails: [],

      scoringWeights: [
        { key: "diemTong", label: "Điểm tổng đội", value: 40, minLabel: "0%", maxLabel: "100%", color: "#16a34a" },
        { key: "khoangCach", label: "Khoảng cách", value: 30, minLabel: "Gần nhất", maxLabel: "Xa nhất", color: "#2563eb" },
        { key: "soLuongThanhVien", label: "Số thành viên", value: 20, minLabel: "Ít nhất", maxLabel: "Nhiều nhất", color: "#d97706" },
        { key: "thoiGianPhanCong", label: "Thời gian phân công", value: 10, minLabel: "Lâu nhất", maxLabel: "Mới nhất", color: "#dc2626" },
      ],

      generalConfig: {
        autoAssignDelay: 30,
        maxDistance: 10,
        maxTeamsPerIncident: 2,
        autoEscalate: true,
        notifyOperator: true,
      },

      activityFilter: "all",
      activityFilters: [
        { label: "Tất cả", value: "all", color: "#475569" },
        { label: "Auto", value: "auto", color: "#16a34a" },
        { label: "Thành công", value: "success", color: "#2563eb" },
        { label: "Leo thang", value: "escalate", color: "#d97706" },
        { label: "Cảnh báo", value: "warning", color: "#dc2626" },
      ],

      activities: [],
      monitoredStats: { totalRequests: 0, autoDispatched: 0, pendingAuto: 0, escalated: 0, pendingRequests: 0, over30Mins: 0, retryDispatch: 0 },
      uptimeStart: null,

      ruleForm: {
        typeSearch: "", detailSearch: "",
        typeDropdown: false, detailDropdown: false,
        selectedType: null, selectedDetail: null,
        priority: 3, color: "#16a34a",
      },

      priorityLevels: [
        { level: 1, label: "Rất thấp", color: "#16a34a" },
        { level: 2, label: "Thấp", color: "#65a30d" },
        { level: 3, label: "Trung bình", color: "#ca8a04" },
        { level: 4, label: "Cao", color: "#ea580c" },
        { level: 5, label: "Rất cao", color: "#dc2626" },
      ],

      colorPalette: [
        "#dc2626", "#ea580c", "#ca8a04", "#16a34a",
        "#0d9488", "#2563eb", "#7c3aed", "#db2777",
        "#0891b2", "#9333ea",
      ],
    };
  },

  computed: {
    uptimeDisplay() {
      const h = Math.floor(this.uptimeSeconds / 3600);
      const m = Math.floor((this.uptimeSeconds % 3600) / 60);
      const s = this.uptimeSeconds % 60;
      if (h > 0) return `${String(h).padStart(2, "0")}:${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
      return `${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
    },

    successRate() {
      if (this.monitoredStats.totalRequests === 0) return 0;
      return Math.round((this.monitoredStats.autoDispatched / this.monitoredStats.totalRequests) * 100);
    },

    statsCards() {
      const autoRate = this.successRate;
      return [
        {
          label: "Yêu cầu đang chờ", value: this.monitoredStats.pendingRequests, icon: "pending",
          iconBg: "rgba(217, 119, 6, 0.12)", iconColor: "#d97706",
          valueColor: "#d97706",
          trendText: this.monitoredStats.pendingRequests > 0 ? "Cần xử lý" : "Trống",
          trendBg: this.monitoredStats.pendingRequests > 0 ? "rgba(217, 119, 6, 0.12)" : "rgba(22, 163, 74, 0.12)", 
          trendColor: this.monitoredStats.pendingRequests > 0 ? "#d97706" : "#16a34a", trendIcon: "up",
          progress: undefined, progressColor: "",
        },
        {
          label: "Đã auto-dispatch", value: this.monitoredStats.autoDispatched, icon: "auto",
          iconBg: "rgba(22, 163, 74, 0.12)", iconColor: "#16a34a",
          valueColor: "#16a34a",
          trendText: autoRate > 0 ? autoRate + "% tỷ lệ" : null,
          trendBg: "rgba(37, 99, 235, 0.12)", trendColor: "#2563eb", trendIcon: "up",
          progress: autoRate, progressColor: "#16a34a",
        },
        {
          label: ">30p chưa tiếp nhận", value: this.monitoredStats.over30Mins, icon: "escalate",
          iconBg: this.monitoredStats.over30Mins > 0 ? "rgba(220, 38, 38, 0.1)" : "rgba(148, 163, 184, 0.1)",
          iconColor: this.monitoredStats.over30Mins > 0 ? "#dc2626" : "#94a3b8",
          valueColor: this.monitoredStats.over30Mins > 0 ? "#dc2626" : "#94a3b8",
          trendText: this.monitoredStats.over30Mins > 0 ? "Cần can thiệp" : "Bình thường",
          trendBg: this.monitoredStats.over30Mins > 0 ? "rgba(220, 38, 38, 0.1)" : "rgba(22, 163, 74, 0.1)",
          trendColor: this.monitoredStats.over30Mins > 0 ? "#dc2626" : "#16a34a", trendIcon: "up",
          progress: undefined, progressColor: "",
        },
        {
          label: "Retry dispatch", value: this.monitoredStats.retryDispatch, icon: "retry",
          iconBg: "rgba(147, 51, 234, 0.12)", iconColor: "#9333ea",
          valueColor: "#9333ea",
          trendText: this.monitoredStats.retryDispatch > 0 ? "Đang tìm lại" : "Ổn định",
          trendBg: this.monitoredStats.retryDispatch > 0 ? "rgba(147, 51, 234, 0.12)" : "rgba(22, 163, 74, 0.12)", 
          trendColor: this.monitoredStats.retryDispatch > 0 ? "#9333ea" : "#16a34a", trendIcon: "up",
          progress: undefined, progressColor: "",
        },
      ];
    },

    monitoringCards() {
      const total = this.monitoredStats.totalRequests;
      const autoRate = this.successRate;
      const pendingRate = total > 0 ? Math.round((this.monitoredStats.pendingAuto / total) * 100) : 0;
      return [
        { label: "Tổng yêu cầu", value: total, sub: `${this.customRules.length} quy tắc ưu tiên`, icon: "total", iconBg: "rgba(37, 99, 235, 0.12)", iconColor: "#2563eb", valueColor: "#0f172a", progress: undefined, progressColor: "" },
        { label: "Tỷ lệ auto", value: autoRate + "%", sub: `${this.monitoredStats.autoDispatched} đã gán tự động`, icon: "auto", iconBg: "rgba(22, 163, 74, 0.12)", iconColor: "#16a34a", valueColor: "#16a34a", progress: autoRate, progressColor: "#16a34a" },
        { label: "Chờ xử lý", value: this.monitoredStats.pendingAuto, sub: `${pendingRate}% trên tổng yêu cầu`, icon: "pending", iconBg: this.monitoredStats.pendingAuto > 0 ? "rgba(217, 119, 6, 0.12)" : "rgba(148, 163, 184, 0.1)", iconColor: this.monitoredStats.pendingAuto > 0 ? "#d97706" : "#94a3b8", valueColor: this.monitoredStats.pendingAuto > 0 ? "#d97706" : "#94a3b8", progress: pendingRate, progressColor: this.monitoredStats.pendingAuto > 0 ? "#d97706" : "#94a3b8" },
      ];
    },

    filteredActivities() {
      if (this.activityFilter === "all") return this.activities;
      return this.activities.filter(a => a.type === this.activityFilter);
    },

    ruleFilteredTypes() {
      if (!this.ruleForm.typeSearch) return this.incidentTypes.slice(0, 10);
      const q = this.ruleForm.typeSearch.toLowerCase();
      return this.incidentTypes.filter(t =>
        (t.ten_loai_su_co || t.name || "").toLowerCase().includes(q) ||
        (t.ten_danh_muc || t.category || "").toLowerCase().includes(q)
      );
    },

    ruleFilteredDetails() {
      if (!this.ruleForm.selectedType) return [];
      const typeId = this.ruleForm.selectedType.id;
      const type = this.incidentTypes.find(t => t.id === typeId);
      const details = type?.chi_tiet_loai_su_co || type?.details || [];
      if (!this.ruleForm.detailSearch) return details.slice(0, 10);
      const q = this.ruleForm.detailSearch.toLowerCase();
      return details.filter(d => (d.ten_chi_tiet || d.name || "").toLowerCase().includes(q));
    },
  },

  mounted() {
    this.loadConfig();
    this.loadIncidentTypes();
    this.loadMonitoredStats();
    this.loadActivities();
    document.addEventListener("click", this.handleOutsideClick);
  },

  beforeUnmount() {
    if (this.uptimeInterval) clearInterval(this.uptimeInterval);
    document.removeEventListener("click", this.handleOutsideClick);
  },

  methods: {
    async loadIncidentTypes() {
      try {
        const res = await incidentTypeAPI.getList();
        this.incidentTypes = res.data?.data || res.data || [];
      } catch { this.incidentTypes = []; }
    },

    async loadConfig() {
      // Load saved local config (weights, rules, general settings)
      try {
        const saved = localStorage.getItem("realtimeDispatchConfig");
        if (saved) {
          const parsed = JSON.parse(saved);
          this.dispatchMode = parsed.dispatchMode || "normal";
          this.customRules = parsed.customRules || [];
          this.generalConfig = { ...this.generalConfig, ...parsed.generalConfig };
          if (parsed.scoringWeights) {
            parsed.scoringWeights.forEach(w => {
              const found = this.scoringWeights.find(s => s.key === w.key);
              if (found) found.value = w.value;
            });
          }
        }
      } catch {}

      // Fetch real dispatch status from BE
      try {
        const res = await autoDispatchAPI.getStatus();
        if (res.data?.thanh_cong) {
          this.dispatchEnabled = res.data.du_lieu?.dieu_phoi_tu_dong ?? false;
        } else {
          // Fallback to saved if BE call fails
          const saved = localStorage.getItem("realtimeDispatchConfig");
          if (saved) {
            const parsed = JSON.parse(saved);
            this.dispatchEnabled = parsed.dispatchEnabled || false;
          }
        }
      } catch {
        const saved = localStorage.getItem("realtimeDispatchConfig");
        if (saved) {
          const parsed = JSON.parse(saved);
          this.dispatchEnabled = parsed.dispatchEnabled || false;
        }
      }

      if (this.dispatchEnabled) this.startUptimeTimer();
    },

    async saveConfig() {
      this.saving = true;
      try {
        localStorage.setItem("realtimeDispatchConfig", JSON.stringify({
          dispatchMode: this.dispatchMode,
          customRules: this.customRules,
          generalConfig: this.generalConfig,
          scoringWeights: this.scoringWeights.map(w => ({ key: w.key, value: w.value })),
        }));

        // Sync dispatch enabled/disabled to BE
        if (this.dispatchEnabled) {
          await autoDispatchAPI.enable();
        } else {
          await autoDispatchAPI.disable();
        }

        // Sync config weights to BE if needed
        await autoDispatchAPI.updateConfig({
          so_doi_toi_da: this.generalConfig.maxTeamsPerIncident,
        });

        await new Promise(r => setTimeout(r, 600));
        this.$toaster?.success?.("Đã lưu cấu hình thành công!");
      } catch (err) {
        console.error("Save config error:", err);
        this.$toaster?.error?.("Lưu cấu hình thất bại!");
      } finally { this.saving = false; }
    },

    toggleDispatch() {
      this.dispatchEnabled = !this.dispatchEnabled;
      if (this.dispatchEnabled) { this.startUptimeTimer(); }
      else {
        if (this.uptimeInterval) { clearInterval(this.uptimeInterval); this.uptimeInterval = null; }
        this.uptimeSeconds = 0;
      }
      autoDispatchAPI.toggle().catch(err => {
        console.error("Toggle dispatch error:", err);
        this.$toaster?.error?.("Không thể toggle auto-dispatch!");
      });
    },

    startUptimeTimer() {
      this.uptimeStart = Date.now();
      this.uptimeInterval = setInterval(() => {
        this.uptimeSeconds = Math.floor((Date.now() - this.uptimeStart) / 1000);
      }, 1000);
    },

    async refreshAll() {
      this.loading = true;
      await Promise.all([
        this.loadIncidentTypes(),
        this.loadMonitoredStats(),
        this.loadActivities(),
      ]);
      this.loading = false;
    },

    selectIncidentType(type) {
      this.ruleForm.selectedType = type;
      this.ruleForm.typeSearch = type.ten_loai_su_co || type.name || "";
      this.ruleForm.typeDropdown = false;
      this.ruleForm.selectedDetail = null;
      this.ruleForm.detailSearch = "";
      this.ruleForm.color = type.mau_sac || "#16a34a";
    },

    selectDetailType(detail) {
      this.ruleForm.selectedDetail = detail;
      this.ruleForm.detailSearch = detail.ten_chi_tiet || detail.name || "";
      this.ruleForm.detailDropdown = false;
    },

    closeRuleModal() {
      this.showRuleModal = false;
      this.editingRule = null;
      this.ruleForm = { typeSearch: "", detailSearch: "", typeDropdown: false, detailDropdown: false, selectedType: null, selectedDetail: null, priority: 3, color: "#16a34a" };
    },

    editRule(rule) {
      this.editingRule = rule;
      const type = this.incidentTypes.find(t => t.id === rule.typeId);
      this.ruleForm.selectedType = type || null;
      this.ruleForm.typeSearch = type ? (type.ten_loai_su_co || type.name || "") : "";
      this.ruleForm.selectedDetail = rule.detailId ? { id: rule.detailId, ten_chi_tiet: rule.detailType, name: rule.detailType } : null;
      this.ruleForm.detailSearch = rule.detailType || "";
      this.ruleForm.priority = rule.priority;
      this.ruleForm.color = rule.color;
      this.showRuleModal = true;
    },

    saveRule() {
      if (!this.ruleForm.selectedType) return;
      const rule = {
        id: this.editingRule ? this.editingRule.id : Date.now(),
        typeId: this.ruleForm.selectedType.id,
        name: this.ruleForm.selectedType.ten_loai_su_co || this.ruleForm.selectedType.name || "Không rõ",
        category: this.ruleForm.selectedType.ten_danh_muc || this.ruleForm.selectedType.category || "Loại sự cố",
        detailId: this.ruleForm.selectedDetail ? this.ruleForm.selectedDetail.id : null,
        detailType: this.ruleForm.selectedDetail ? (this.ruleForm.selectedDetail.ten_chi_tiet || this.ruleForm.selectedDetail.name || "") : "",
        priority: this.ruleForm.priority,
        color: this.ruleForm.color,
      };
      if (this.editingRule) {
        const idx = this.customRules.findIndex(r => r.id === rule.id);
        if (idx !== -1) this.customRules.splice(idx, 1, rule);
      } else { this.customRules.push(rule); }
      this.closeRuleModal();
      this.saveConfig();
    },

    removeRule(id) { this.customRules = this.customRules.filter(r => r.id !== id); this.saveConfig(); },

    handleOutsideClick(e) {
      if (!e.target.closest(".search-select-wrap")) {
        this.ruleForm.typeDropdown = false;
        this.ruleForm.detailDropdown = false;
      }
    },

    async loadMonitoredStats() {
      try {
        const [escRes, reqRes] = await Promise.all([
          autoDispatchAPI.getEscalations().catch(() => ({ data: {} })),
          rescueRequestAPI.getList().catch(() => ({ data: [] })),
        ]);

        const escalationEvents = Array.isArray(escRes.data?.du_lieu)
          ? escRes.data.du_lieu
          : Array.isArray(escRes.data)
            ? escRes.data
            : [];

        const allRequests = Array.isArray(reqRes.data?.data)
          ? reqRes.data.data
          : Array.isArray(reqRes.data)
            ? reqRes.data
            : [];

        const pendingStatuses = new Set(['CHO_XU_LY', 'MOI', 'WAITING']);
        const now = new Date();

        let totalRequests = allRequests.length;
        let pendingRequestsCount = 0;
        let over30MinsCount = 0;
        let autoDispatchedCount = 0;
        let retryDispatchCount = 0;

        allRequests.forEach(req => {
          const status = String(req.trang_thai || '').toUpperCase().trim();
          const isPending = pendingStatuses.has(status);

          if (isPending) {
            pendingRequestsCount += 1;
          }

          const createdDate = new Date(req.thoi_gian_gui || req.created_at || req.updated_at || req.created_at_old);
          if (!isNaN(createdDate.getTime())) {
            const diffMins = (now - createdDate) / (1000 * 60);
            if (diffMins > 30 && isPending) {
              over30MinsCount += 1;
            }
          }

          if (req.retry_count || req.retry || req.so_lan_thu) {
            const retryValue = Number(req.retry_count || req.retry || req.so_lan_thu);
            if (!Number.isNaN(retryValue) && retryValue > 0) {
              retryDispatchCount += 1;
            }
          }

          if (req.tu_dong_phan_cong || req.auto_dispatched || req.da_auto_dispatch) {
            const autoDispatchedFlag = req.tu_dong_phan_cong || req.auto_dispatched || req.da_auto_dispatch;
            if (autoDispatchedFlag === true || String(autoDispatchedFlag) === '1' || String(autoDispatchedFlag).toLowerCase() === 'true') {
              autoDispatchedCount += 1;
            }
          }
        });

        if (autoDispatchedCount === 0 && escalationEvents.length > 0 && totalRequests > 0) {
          // Fallback khi backend chưa trả flag auto-dispatched
          autoDispatchedCount = Math.max(0, totalRequests - escalationEvents.length);
        }

        this.monitoredStats = {
          totalRequests,
          autoDispatched: autoDispatchedCount,
          pendingAuto: escalationEvents.length,
          escalated: escalationEvents.length,
          pendingRequests: pendingRequestsCount,
          over30Mins: over30MinsCount,
          retryDispatch: retryDispatchCount,
        };
      } catch (err) {
        console.error('Load monitored stats error:', err);
      }
    },

    formatTimeAgo(dateStr) {
      if (!dateStr) return "Vừa xong";
      const diff = (new Date() - new Date(dateStr)) / 1000;
      if (diff < 60) return "Vừa xong";
      if (diff < 3600) return Math.floor(diff / 60) + " phút trước";
      if (diff < 86400) return Math.floor(diff / 3600) + " giờ trước";
      return Math.floor(diff / 86400) + " ngày trước";
    },

    async loadActivities() {
      this.loading = true;
      try {
        const [escRes, assignRes] = await Promise.all([
          autoDispatchAPI.getEscalations().catch(() => ({ data: {} })),
          assignmentAPI.getList().catch(() => ({ data: [] })),
        ]);

        const combined = [];

        // --- Phân công đã tạo (dispatch thành công) ---
        const rawAssignments = Array.isArray(assignRes.data?.data)
          ? assignRes.data.data
          : Array.isArray(assignRes.data)
            ? assignRes.data
            : [];

        rawAssignments.forEach((item, idx) => {
          const teamName = item.doi_cuu_ho?.ten_doi
            || item.ten_doi
            || (item.doi_cuu_ho_id ? `Đội #${item.doi_cuu_ho_id}` : null)
            || "Chưa xác định";
          const reqId = item.yeu_cau_cuu_ho?.id_yeu_cau
            || item.id_yeu_cau
            || item.yeu_cau_id
            || item.id;
          const loaiSuCo = item.yeu_cau_cuu_ho?.loai_su_co
            || item.loai_su_co
            || "N/A";
          const mucDo = item.yeu_cau_cuu_ho?.muc_do_khan_cap
            || item.muc_do_khan_cap
            || "N/A";
          const diaChi = item.yeu_cau_cuu_ho?.vi_tri_dia_chi
            || item.vi_tri_dia_chi
            || "N/A";
          const isAuto = item.tu_dong_phan_cong || item.auto_dispatched || item.da_auto_dispatch;
          const trangThai = String(item.trang_thai_nhiem_vu || item.trang_thai || "").toUpperCase();
          const isSuccess = ["DANG_XU_LY", "HOAN_THANH", "DA_NHAN"].includes(trangThai);

          combined.push({
            id: `assign-${item.id_phan_cong || item.id || idx}`,
            type: isAuto ? "auto" : "success",
            icon: isSuccess ? "check" : "bolt",
            typeLabel: isAuto ? "Auto" : "Thủ công",
            title: isAuto
              ? `Auto-dispatch → ${teamName}`
              : `Điều phối thủ công → ${teamName}`,
            description: `Yêu cầu #${reqId} (${loaiSuCo}) tại ${diaChi} — Trạng thái: ${trangThai || "CHỜ"}`,
            timeAgo: this.formatTimeAgo(item.created_at || item.thoi_gian_phan_cong),
            dotBg: isAuto ? "rgba(22, 163, 74, 0.12)" : "rgba(37, 99, 235, 0.12)",
            dotColor: isAuto ? "#16a34a" : "#2563eb",
            badgeBg: isAuto ? "rgba(22, 163, 74, 0.1)" : "rgba(37, 99, 235, 0.08)",
            badgeColor: isAuto ? "#16a34a" : "#2563eb",
            details: [
              { text: teamName },
              { text: loaiSuCo },
              { text: mucDo },
            ],
            _sortTime: new Date(item.created_at || item.thoi_gian_phan_cong || 0).getTime(),
          });
        });

        // --- Leo thang / Cảnh báo ---
        const escalations = Array.isArray(escRes.data?.du_lieu)
          ? escRes.data.du_lieu
          : Array.isArray(escRes.data)
            ? escRes.data
            : [];

        escalations.forEach((item, idx) => {
          const isLongWait = item.thoi_gian_cho_phut > 60;
          combined.push({
            id: `esc-${item.id_yeu_cau || idx}`,
            type: isLongWait ? "escalate" : "warning",
            icon: isLongWait ? "arrow" : "warn",
            typeLabel: isLongWait ? "Leo thang" : "Cảnh báo",
            title: isLongWait ? "Leo thang tự động" : "Cần can thiệp",
            description: isLongWait
              ? `Yêu cầu #${item.id_yeu_cau} chờ hơn ${item.thoi_gian_cho_phut} phút — chuyển lên điều phối viên`
              : `Yêu cầu #${item.id_yeu_cau} (${item.loai_su_co || 'N/A'}) tại ${item.vi_tri_dia_chi || 'N/A'} chưa được xử lý`,
            timeAgo: item.thoi_gian_cho || "Vừa xong",
            dotBg: isLongWait ? "rgba(217, 119, 6, 0.12)" : "rgba(220, 38, 38, 0.1)",
            dotColor: isLongWait ? "#d97706" : "#dc2626",
            badgeBg: isLongWait ? "rgba(217, 119, 6, 0.1)" : "rgba(220, 38, 38, 0.08)",
            badgeColor: isLongWait ? "#d97706" : "#dc2626",
            details: [
              { text: item.loai_su_co || "N/A" },
              { text: item.muc_do_khan_cap || "MEDIUM" },
              { text: item.thoi_gian_cho_phut + " phút" },
            ],
            _sortTime: 0,
          });
        });

        // Sắp xếp mới nhất lên đầu
        combined.sort((a, b) => (b._sortTime || 0) - (a._sortTime || 0));
        this.activities = combined;
      } catch (err) {
        console.error("Load activities error:", err);
      } finally {
        this.loading = false;
      }
    }
  },
};
</script>

<style scoped>
/* ===== BASE ===== *//* ===== HEADER ===== */
.header-icon-wrap {
  width: 42px; height: 42px;
  border-radius: 12px;
  background: rgba(217, 119, 6, 0.12);
  border: 1px solid rgba(217, 119, 6, 0.2);
  display: flex; align-items: center; justify-content: center;
  color: #d97706;
  flex-shrink: 0;
}
.page-title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: -0.02em;
}

/* ===== MASTER TOGGLE ===== */
.master-toggle-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 10px 18px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  transition: all 0.25s ease;
}
.master-toggle-card.is-active {
  border-color: rgba(22, 163, 74, 0.3);
  background: linear-gradient(135deg, #f0fdf4, #fafafa);
  box-shadow: 0 2px 12px rgba(22, 163, 74, 0.12);
}
.master-toggle-icon {
  width: 36px; height: 36px;
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.icon-active { background: rgba(22, 163, 74, 0.12); color: #16a34a; }
.icon-inactive { background: #f1f5f9; color: #94a3b8; }
.master-toggle-label { font-weight: 700; font-size: 0.875rem; color: #0f172a; display: block; }
.master-toggle-status { display: flex; align-items: center; gap: 5px; font-size: 0.72rem; font-weight: 500; margin-top: 2px; }
.status-on { color: #16a34a; }
.status-off { color: #94a3b8; }
.status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.status-on .status-dot { animation: blink-dot 1.5s ease-in-out infinite; }
@keyframes blink-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
.master-toggle-btn { background: none; border: none; cursor: pointer; padding: 0; }
.toggle-track { display: block; width: 48px; height: 26px; border-radius: 9999px; background: #e2e8f0; position: relative; transition: background 0.3s ease; }
.master-toggle-btn.active .toggle-track { background: #16a34a; box-shadow: 0 0 12px rgba(22, 163, 74, 0.3); }
.toggle-thumb { display: block; width: 20px; height: 20px; border-radius: 50%; background: white; position: absolute; top: 3px; left: 3px; transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 1px 4px rgba(0,0,0,0.2); }
.master-toggle-btn.active .toggle-thumb { transform: translateX(22px); }

/* ===== ICON ACTION BTN ===== */
.icon-action-btn {
  width: 40px; height: 40px;
  border-radius: 10px;
  background: white;
  border: 1px solid #e2e8f0;
  color: #64748b;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.icon-action-btn:hover { background: #f8fafc; color: #0f172a; }
.icon-action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* ===== STAT CARDS ===== */
.stat-card {
  background: white;
  border-radius: 14px;
  padding: 18px 20px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  transition: all 0.25s ease;
  animation: slide-up-anim 0.45s ease backwards;
  animation-delay: var(--delay, 0ms);
  display: flex;
  flex-direction: column;
  min-height: 180px;
}
@keyframes slide-up-anim { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
.stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-1px); }
.stat-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.stat-icon-wrap { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-trend-badge { display: inline-flex; align-items: center; gap: 3px; font-size: 0.68rem; font-weight: 600; padding: 3px 8px; border-radius: 9999px; }
.stat-value { font-size: 1.9rem; font-weight: 800; line-height: 1.1; letter-spacing: -0.03em; }
.stat-label { font-size: 0.72rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-progress { display: flex; align-items: center; gap: 8px; margin-top: 10px; }
.progress-track { flex: 1; height: 4px; background: #f1f5f9; border-radius: 9999px; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 9999px; transition: width 0.8s cubic-bezier(0.22, 1, 0.36, 1); }
.progress-label { font-size: 0.68rem; font-weight: 600; color: #94a3b8; flex-shrink: 0; }

/* ===== CONFIG PANEL ===== */
.config-panel {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  height: 100%;
  display: flex;
  flex-direction: column;
}
.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 22px;
  border-bottom: 1px solid #f1f5f9;
  background: white;
  position: sticky;
  top: 0;
  z-index: 10;
}
.panel-icon { width: 34px; height: 34px; border-radius: 9px; background: rgba(37, 99, 235, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.panel-title { font-size: 0.95rem; font-weight: 700; color: #0f172a; }
.btn-save-float {
  display: flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: 9px;
  background: linear-gradient(135deg, #16a34a, #15803d);
  border: none; color: white; font-weight: 600; font-size: 0.8rem;
  cursor: pointer; transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(22, 163, 74, 0.25);
}
.btn-save-float:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(22, 163, 74, 0.3); }
.btn-save-float:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-save-float.saving { background: linear-gradient(135deg, #2563eb, #1d4ed8); box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25); }
.panel-body { padding: 20px 22px; overflow-y: auto; flex: 1; max-height: 72vh; }
.panel-body::-webkit-scrollbar { width: 4px; }
.panel-body::-webkit-scrollbar-track { background: transparent; }
.panel-body::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

/* ===== CONFIG SECTION ===== */
.config-section { margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #f1f5f9; }
.config-section-last { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.section-title { display: flex; align-items: center; gap: 7px; font-size: 0.8rem; font-weight: 700; color: #374151; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
.section-desc { font-size: 0.75rem; color: #94a3b8; margin-bottom: 14px; line-height: 1.5; }
.badge-count { display: inline-flex; align-items: center; justify-content: center; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 9999px; background: rgba(37, 99, 235, 0.1); color: #2563eb; font-size: 0.68rem; font-weight: 700; margin-left: 3px; }

/* ===== MODE SELECTOR ===== */
.mode-selector { display: flex; flex-direction: column; gap: 8px; }
.mode-option {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; border-radius: 12px;
  border: 1px solid #e2e8f0; background: #f8fafc;
  cursor: pointer; transition: all 0.2s ease;
  text-align: left; width: 100%;
}
.mode-option:hover { border-color: #cbd5e1; background: white; }
.mode-option.active { border-color: rgba(22, 163, 74, 0.35); background: #f0fdf4; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.06); }
.mode-option-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s ease; }
.mode-icon-normal { background: rgba(22, 163, 74, 0.1); color: #16a34a; }
.mode-icon-custom { background: rgba(124, 58, 237, 0.1); color: #7c3aed; }
.mode-option-icon.active { transform: scale(1.05); }
.mode-icon-normal.active { background: rgba(22, 163, 74, 0.18); box-shadow: 0 0 14px rgba(22, 163, 74, 0.15); }
.mode-icon-custom.active { background: rgba(124, 58, 237, 0.18); box-shadow: 0 0 14px rgba(124, 58, 237, 0.15); }
.mode-option-info { flex: 1; }
.mode-option-title { display: block; font-weight: 700; color: #1e293b; font-size: 0.83rem; }
.mode-option-desc { display: block; font-size: 0.72rem; color: #94a3b8; margin-top: 2px; }
.mode-option-check { width: 22px; height: 22px; border-radius: 50%; background: rgba(22, 163, 74, 0.12); color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* ===== ADD RULE ===== */
.add-rule-zone {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 12px; border: 2px dashed #e2e8f0; border-radius: 12px;
  color: #94a3b8; cursor: pointer; transition: all 0.2s ease;
  font-size: 0.82rem; font-weight: 600; margin-bottom: 10px;
}
.add-rule-zone:hover { border-color: #16a34a; color: #16a34a; background: #f0fdf4; }

/* ===== RULES LIST ===== */
.rules-list { display: flex; flex-direction: column; gap: 7px; }
.rule-card {
  display: flex; align-items: center; gap: 9px;
  padding: 10px 12px; border-radius: 10px;
  background: #f8fafc; border: 1px solid #e2e8f0;
  transition: all 0.15s ease; cursor: grab;
}
.rule-card:hover { border-color: #cbd5e1; background: white; }
.rule-drag-handle { color: #cbd5e1; cursor: grab; flex-shrink: 0; }
.rule-rank { font-size: 0.68rem; font-weight: 800; color: #94a3b8; min-width: 18px; text-align: center; }
.rule-color-bar { width: 4px; height: 28px; border-radius: 9999px; flex-shrink: 0; }
.rule-info { min-width: 0; }
.rule-name { font-weight: 600; color: #1e293b; font-size: 0.82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rule-meta { display: flex; align-items: center; gap: 5px; margin-top: 2px; }
.rule-category { font-size: 0.7rem; color: #94a3b8; }
.rule-dot-sep { width: 3px; height: 3px; border-radius: 50%; background: #cbd5e1; }
.rule-detail-type { font-size: 0.7rem; color: #94a3b8; }
.rule-actions { display: flex; gap: 3px; flex-shrink: 0; }
.rule-action-btn { width: 26px; height: 26px; border-radius: 7px; border: 1px solid #e2e8f0; background: white; color: #94a3b8; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease; }
.rule-action-btn:hover { background: #f1f5f9; color: #0f172a; }
.rule-action-delete:hover { background: rgba(220, 38, 38, 0.08); color: #dc2626; border-color: rgba(220, 38, 38, 0.2); }

/* ===== EMPTY RULES ===== */
.empty-rules { text-align: center; padding: 24px 14px; color: #cbd5e1; }
.empty-rules svg { margin-bottom: 8px; }
.empty-rules p { font-weight: 600; font-size: 0.82rem; margin-bottom: 3px; }
.empty-rules small { font-size: 0.72rem; }

/* ===== WEIGHT ITEMS ===== */
.weight-items { display: flex; flex-direction: column; gap: 14px; }
.weight-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.weight-label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.weight-value-badge { font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; }
.weight-slider { width: 100%; height: 5px; border-radius: 9999px; background: #f1f5f9; outline: none; -webkit-appearance: none; appearance: none; cursor: pointer; accent-color: var(--w-color, #16a34a); }
.weight-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 15px; height: 15px; border-radius: 50%; background: var(--w-color, #16a34a); cursor: pointer; box-shadow: 0 1px 4px rgba(0,0,0,0.2); transition: transform 0.15s ease; }
.weight-slider::-webkit-slider-thumb:hover { transform: scale(1.15); }
.weight-slider::-moz-range-thumb { width: 15px; height: 15px; border-radius: 50%; background: var(--w-color, #16a34a); cursor: pointer; border: none; }
.weight-hints { display: flex; justify-content: space-between; margin-top: 3px; }
.weight-hints small { font-size: 0.65rem; color: #cbd5e1; }

/* ===== SETTING ITEMS ===== */
.setting-item { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; border-bottom: 1px solid #f8fafc; }
.setting-item-last { border-bottom: none; }
.setting-label { display: block; font-weight: 600; font-size: 0.78rem; color: #374151; }
.setting-desc { display: block; font-size: 0.7rem; color: #94a3b8; margin-top: 2px; }
.setting-control { display: flex; align-items: center; gap: 5px; flex-shrink: 0; }
.setting-input { width: 60px; padding: 5px 8px; border-radius: 7px; border: 1px solid #e2e8f0; background: #f8fafc; color: #0f172a; font-size: 0.78rem; font-weight: 600; text-align: center; outline: none; transition: border-color 0.2s ease; }
.setting-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); background: white; }
.setting-unit { font-size: 0.7rem; color: #94a3b8; font-weight: 500; }

/* ===== TOGGLE MINI ===== */
.toggle-mini { width: 38px; height: 21px; border-radius: 9999px; background: #e2e8f0; border: none; position: relative; cursor: pointer; transition: background 0.25s ease; flex-shrink: 0; }
.toggle-mini.active { background: #2563eb; }
.toggle-mini-thumb { display: block; width: 15px; height: 15px; border-radius: 50%; background: white; position: absolute; top: 3px; left: 3px; transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
.toggle-mini.active .toggle-mini-thumb { transform: translateX(17px); }

/* ===== STATUS BANNER ===== */
.status-banner { border-radius: 16px; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; transition: all 0.3s ease; }
.banner-active { background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 1px solid rgba(22, 163, 74, 0.2); box-shadow: 0 2px 12px rgba(22, 163, 74, 0.08); }
.banner-inactive { background: linear-gradient(135deg, #f8fafc, #f1f5f9); border: 1px solid #e2e8f0; }
.banner-left { display: flex; align-items: center; gap: 14px; }
.banner-icon-wrap { position: relative; }
.banner-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.icon-running { background: rgba(22, 163, 74, 0.1); color: #16a34a; }
.icon-stopped { background: #f1f5f9; color: #94a3b8; }
.banner-pulse { position: absolute; inset: -3px; border-radius: 15px; background: rgba(22, 163, 74, 0.12); animation: pulse-banner 2s ease-in-out infinite; z-index: -1; }
@keyframes pulse-banner { 0%, 100% { transform: scale(1); opacity: 0.6; } 50% { transform: scale(1.08); opacity: 0.2; } }
.banner-title { font-size: 1rem; font-weight: 700; color: #0f172a; }
.banner-sub { font-size: 0.75rem; color: #64748b; }
.banner-right { display: flex; gap: 20px; }
.uptime-block, .dispatch-rate-block { text-align: center; }
.uptime-label { display: block; font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
.uptime-value { font-size: 1.1rem; font-weight: 800; color: #16a34a; letter-spacing: 0.03em; }

/* ===== MONITOR CARDS ===== */
.monitor-card {
  border-radius: 12px; padding: 14px 16px;
  background: white; border: 1px solid #e2e8f0;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  transition: all 0.2s ease;
}
.monitor-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
.monitor-top { display: flex; align-items: center; gap: 7px; margin-bottom: 8px; }
.monitor-icon { width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.monitor-label { font-size: 0.72rem; font-weight: 600; color: #64748b; }
.monitor-value { font-size: 1.6rem; font-weight: 800; letter-spacing: -0.02em; }
.monitor-sub { font-size: 0.7rem; color: #94a3b8; }
.monitor-progress { margin-top: 8px; }
.monitor-progress-track { height: 4px; background: #f1f5f9; border-radius: 9999px; overflow: hidden; }
.monitor-progress-fill { height: 100%; border-radius: 9999px; transition: width 0.8s cubic-bezier(0.22, 1, 0.36, 1); }

/* ===== PRIORITY PREVIEW ===== */
.priority-preview-panel { border-radius: 12px; background: white; border: 1px solid #e2e8f0; overflow: hidden; }
.panel-header-sm { display: flex; align-items: center; gap: 7px; padding: 12px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.priority-chain { display: flex; flex-direction: column; padding: 10px 16px; gap: 5px; }
.priority-chain-item { display: flex; align-items: center; gap: 9px; padding: 4px 0; }
.chain-rank { font-size: 0.68rem; font-weight: 800; color: #94a3b8; min-width: 16px; text-align: center; }
.chain-bar { width: 4px; height: 26px; border-radius: 9999px; flex-shrink: 0; }
.chain-name { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; }
.chain-type { display: block; font-size: 0.68rem; color: #94a3b8; }
.chain-default { opacity: 0.5; }

/* ===== ACTIVITY PANEL ===== */
.activity-panel { border-radius: 16px; background: white; border: 1px solid #e2e8f0; overflow: hidden; }
.activity-panel-header { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; gap: 10px; }
.activity-count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 9999px; background: rgba(37, 99, 235, 0.1); color: #2563eb; font-size: 0.7rem; font-weight: 700; }
.activity-filters { display: flex; gap: 5px; flex-wrap: wrap; }
.filter-chip { height: 26px; padding: 0 10px; border-radius: 9999px; border: 1px solid #e2e8f0; background: white; color: #64748b; font-size: 0.7rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; line-height: 26px; }
.filter-chip:hover { border-color: #cbd5e1; color: #0f172a; }
.filter-chip.active { font-weight: 700; }
.activity-list { max-height: 380px; overflow-y: auto; }
.activity-list::-webkit-scrollbar { width: 4px; }
.activity-list::-webkit-scrollbar-track { background: transparent; }
.activity-list::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

/* ===== ACTIVITY ITEMS ===== */
.activity-item { display: flex; gap: 12px; padding: 13px 18px; transition: background 0.15s ease; }
.activity-item:hover { background: #f8fafc; }
.activity-dot { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.activity-content { flex: 1; min-width: 0; }
.activity-title { font-weight: 600; font-size: 0.8rem; color: #1e293b; }
.activity-desc { color: #94a3b8; font-size: 0.72rem; margin-top: 2px; }
.activity-meta { text-align: right; flex-shrink: 0; }
.activity-badge { display: inline-block; font-size: 0.62rem; font-weight: 700; padding: 2px 7px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.3px; }
.activity-time { display: block; font-size: 0.65rem; color: #cbd5e1; margin-top: 3px; }
.activity-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 7px; }
.act-tag { display: inline-flex; align-items: center; font-size: 0.65rem; background: #f1f5f9; color: #64748b; padding: 2px 7px; border-radius: 5px; font-weight: 500; }

/* ===== ACTIVITY LOADING ===== */
.activity-loading { padding: 18px; display: flex; flex-direction: column; gap: 14px; }
.activity-skeleton { display: flex; align-items: center; gap: 12px; }
.skeleton-circle { width: 30px; height: 30px; border-radius: 50%; background: #f1f5f9; flex-shrink: 0; animation: shimmer 1.5s infinite; }
.skeleton-lines { flex: 1; display: flex; flex-direction: column; gap: 5px; }
.skeleton-line { height: 9px; border-radius: 5px; background: #f1f5f9; animation: shimmer 1.5s infinite; }
@keyframes shimmer { 0%, 100% { opacity: 0.5; } 50% { opacity: 1; } }

/* ===== ACTIVITY EMPTY ===== */
.activity-empty { text-align: center; padding: 40px 20px; color: #cbd5e1; }
.activity-empty svg { margin-bottom: 10px; }
.activity-empty h6 { font-weight: 700; font-size: 0.85rem; margin-bottom: 3px; color: #94a3b8; }
.activity-empty small { font-size: 0.75rem; }

/* ===== MODAL ===== */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(3px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; animation: fade-in 0.2s ease; }
@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
.modal-box { background: white; border: 1px solid #e2e8f0; border-radius: 18px; width: 100%; max-width: 460px; max-height: 90vh; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.15); animation: slide-up-modal 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
@keyframes slide-up-modal { from { opacity: 0; transform: translateY(24px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 18px 22px; border-bottom: 1px solid #f1f5f9; }
.modal-title { display: flex; align-items: center; gap: 9px; font-size: 0.95rem; font-weight: 700; color: #0f172a; }
.modal-title svg { color: #7c3aed; }
.modal-close { width: 30px; height: 30px; border-radius: 7px; border: 1px solid #e2e8f0; background: white; color: #94a3b8; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease; }
.modal-close:hover { background: rgba(220,38,38,0.06); color: #dc2626; border-color: rgba(220,38,38,0.2); }
.modal-body { padding: 22px; overflow-y: auto; max-height: 58vh; }
.modal-footer { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 22px; border-top: 1px solid #f1f5f9; }
.btn-modal-cancel { padding: 9px 18px; border-radius: 9px; border: 1px solid #e2e8f0; background: white; color: #64748b; font-weight: 600; font-size: 0.82rem; cursor: pointer; transition: all 0.2s ease; }
.btn-modal-cancel:hover { background: #f8fafc; color: #0f172a; }
.btn-modal-save { display: flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 9px; background: linear-gradient(135deg, #16a34a, #15803d); border: none; color: white; font-weight: 700; font-size: 0.82rem; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 10px rgba(22,163,74,0.25); }
.btn-modal-save:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(22,163,74,0.3); }
.btn-modal-save:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

/* ===== FORM ===== */
.form-group-custom { margin-bottom: 18px; }
.form-label-custom { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 7px; }
.optional-tag { font-weight: 400; color: #94a3b8; font-size: 0.7rem; }
.search-select-wrap { position: relative; }
.search-select-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; z-index: 1; }
.form-input-custom { width: 100%; padding: 9px 11px 9px 36px; border-radius: 9px; border: 1px solid #e2e8f0; background: #f8fafc; color: #0f172a; font-size: 0.82rem; outline: none; transition: all 0.2s ease; }
.form-input-custom:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); background: white; }
.search-select-dropdown { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: white; border: 1px solid #e2e8f0; border-radius: 9px; max-height: 190px; overflow-y: auto; z-index: 100; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
.search-select-item { display: flex; align-items: center; gap: 9px; padding: 9px 13px; cursor: pointer; transition: background 0.15s ease; border-bottom: 1px solid #f8fafc; }
.search-select-item:last-child { border-bottom: none; }
.search-select-item:hover { background: #f0f9ff; }
.ss-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.ss-name { display: block; font-weight: 600; font-size: 0.8rem; color: #1e293b; }
.ss-cat { display: block; font-size: 0.68rem; color: #94a3b8; }
.search-select-empty { padding: 14px; text-align: center; color: #94a3b8; }
.selected-type-chip { display: inline-flex; align-items: center; gap: 7px; margin-top: 7px; padding: 5px 10px; border-radius: 7px; background: rgba(37,99,235,0.06); border: 1px solid rgba(37,99,235,0.15); color: #2563eb; font-size: 0.78rem; font-weight: 600; }
.selected-type-chip-sm { background: rgba(124,58,237,0.06); border-color: rgba(124,58,237,0.15); color: #7c3aed; }
.chip-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.chip-remove { background: none; border: none; color: inherit; opacity: 0.6; cursor: pointer; padding: 0; display: flex; align-items: center; transition: opacity 0.15s ease; }
.chip-remove:hover { opacity: 1; }

/* ===== PRIORITY SELECTOR ===== */
.priority-selector { display: flex; gap: 7px; flex-wrap: wrap; }
.priority-level-btn { padding: 6px 13px; border-radius: 7px; border: 1px solid #e2e8f0; background: white; color: #64748b; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
.priority-level-btn:hover { border-color: #cbd5e1; color: #0f172a; }

/* ===== COLOR SWATCHES ===== */
.color-swatches { display: flex; gap: 7px; flex-wrap: wrap; }
.color-swatch { width: 30px; height: 30px; border-radius: 7px; border: 2px solid transparent; cursor: pointer; transition: all 0.15s ease; }
.color-swatch:hover { transform: scale(1.12); }
.color-swatch.active { border-color: #0f172a; transform: scale(1.08); box-shadow: 0 0 0 2px rgba(15,23,42,0.15); }

/* ===== SPIN ===== */
.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* ===== RESPONSIVE ===== */
@media (max-width: 767px) {
  .banner-right { display: none; }
  .panel-body { max-height: 55vh; }
}
</style>
