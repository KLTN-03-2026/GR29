<template>
  <div class="rescue-map" ref="mapWrap">
    <!-- Map Container -->
    <div ref="mapContainer" class="map-canvas"></div>

    <!-- Loading Overlay -->
    <transition name="overlay-fade">
      <div v-if="loading" class="map-overlay">
        <div class="overlay-card">
          <div class="overlay-spinner"></div>
          <span class="overlay-text">Đang tải bản đồ...</span>
        </div>
      </div>
    </transition>

    <!-- Error Overlay -->
    <transition name="overlay-fade">
      <div v-if="errorMsg" class="map-overlay">
        <div class="overlay-card overlay-card-error">
          <svg class="overlay-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          <span class="overlay-text overlay-text-error">{{ errorMsg }}</span>
          <button class="overlay-retry-btn" @click="$emit('retry')">Thử lại</button>
        </div>
      </div>
    </transition>

    <!-- Status Badge -->
    <transition name="badge-pop">
      <div v-if="showStatusBadge" class="map-status-badge">
        <span class="badge-dot" :class="badgeClass"></span>
        <span class="badge-label">{{ badgeText }}</span>
      </div>
    </transition>

    <!-- Map Controls -->
    <div class="map-controls" v-if="!loading && !errorMsg">
      <button class="map-ctrl-btn" @click="$emit('zoom-in')" title="Phóng to">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <line x1="12" y1="5" x2="12" y2="19" />
          <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
      </button>
      <button class="map-ctrl-btn" @click="$emit('zoom-out')" title="Thu nhỏ">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
      </button>
      <div class="ctrl-divider"></div>
      <button class="map-ctrl-btn map-ctrl-btn-accent" @click="$emit('locate')" title="Vị trí của tôi">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="12" cy="12" r="3" />
          <path d="M12 2v4M12 18v4M2 12h4M18 12h4" />
        </svg>
      </button>
    </div>

    <!-- Legend -->
    <div class="map-legend" v-if="!loading && !errorMsg">
      <div class="legend-row" v-if="showClientMarker">
        <span class="legend-dot legend-dot-client"></span>
        <span class="legend-label">Vị trí của bạn</span>
      </div>
      <div class="legend-row" v-if="showRescuerMarker">
        <span class="legend-dot legend-dot-rescuer"></span>
        <span class="legend-label">Đội cứu hộ</span>
      </div>
      <div class="legend-row">
        <span class="legend-dot legend-dot-incident"></span>
        <span class="legend-label">Hiện trường</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "RescueMap",
  props: {
    loading: { type: Boolean, default: false },
    errorMsg: { type: String, default: "" },
    clientMarker: { type: Object, default: null },
    rescuerMarker: { type: Object, default: null },
    incidentMarker: { type: Object, default: null },
    showRescuerMarker: { type: Boolean, default: false },
    showClientMarker: { type: Boolean, default: true },
    badgeText: { type: String, default: "" },
    badgeClass: { type: String, default: "" },
  },
  emits: ["zoom-in", "zoom-out", "locate", "retry", "map-ready"],
  computed: {
    showStatusBadge() {
      return this.badgeText && this.badgeText.length > 0;
    },
  },
  mounted() {
    this.$emit("map-ready", this.$refs.mapContainer);
  },
};
</script>

<style scoped>
.rescue-map {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 16px;
  overflow: hidden;
  background: #e2e8f0;
}

.map-canvas {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
}

/* ─── Overlay ─────────────────────────────── */
.map-overlay {
  position: absolute;
  inset: 0;
  background: rgba(248, 250, 252, 0.92);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.overlay-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 2rem;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  min-width: 200px;
}

.overlay-card-error {
  background: #fff;
}

.overlay-spinner {
  width: 36px;
  height: 36px;
  border: 3px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.overlay-icon {
  color: #dc2626;
}

.overlay-text {
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
}

.overlay-text-error {
  color: #dc2626;
  text-align: center;
}

.overlay-retry-btn {
  margin-top: 0.5rem;
  padding: 0.5rem 1.25rem;
  border-radius: 10px;
  border: none;
  background: #2563eb;
  color: #ffffff;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.overlay-retry-btn:hover {
  background: #1d4ed8;
}

/* ─── Status Badge ─────────────────────────── */
.map-status-badge {
  position: absolute;
  top: 0.75rem;
  left: 50%;
  transform: translateX(-50%);
  z-index: 15;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  border-radius: 20px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  white-space: nowrap;
}

.badge-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.badge-dot.badge-blue { background: #3b82f6; animation: dot-pulse 1.5s ease-in-out infinite; }
.badge-dot.badge-orange { background: #f97316; animation: dot-pulse 1.5s ease-in-out infinite; }
.badge-dot.badge-green { background: #10b981; }
.badge-dot.badge-red { background: #ef4444; animation: dot-pulse 1s ease-in-out infinite; }

@keyframes dot-pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.badge-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: #1e293b;
}

/* ─── Map Controls ─────────────────────────── */
.map-controls {
  position: absolute;
  bottom: 1rem;
  right: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  z-index: 15;
}

.map-ctrl-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: none;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s;
}

.map-ctrl-btn:hover {
  background: #ffffff;
  color: #1e293b;
  transform: scale(1.05);
}

.map-ctrl-btn-accent {
  color: #2563eb;
}

.map-ctrl-btn-accent:hover {
  background: #eff6ff;
  color: #1d4ed8;
}

.ctrl-divider {
  height: 1px;
  background: #e2e8f0;
  margin: 0.1rem 0;
}

/* ─── Legend ─────────────────────────────── */
.map-legend {
  position: absolute;
  bottom: 1rem;
  left: 0.75rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  border-radius: 12px;
  padding: 0.5rem 0.75rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 15;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.legend-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: 2px solid #ffffff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  flex-shrink: 0;
}

.legend-dot-client { background: #2563eb; }
.legend-dot-rescuer { background: #10b981; }
.legend-dot-incident { background: #ef4444; }

.legend-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #475569;
}

/* ─── Transitions ─────────────────────────── */
.overlay-fade-enter-active,
.overlay-fade-leave-active {
  transition: opacity 0.3s ease;
}

.overlay-fade-enter-from,
.overlay-fade-leave-to {
  opacity: 0;
}

.badge-pop-enter-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.badge-pop-leave-active {
  transition: all 0.2s ease;
}

.badge-pop-enter-from,
.badge-pop-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(-10px) scale(0.9);
}

/* ─── Responsive ─────────────────────────────── */
@media (max-width: 480px) {
  .map-controls {
    bottom: 0.75rem;
    right: 0.5rem;
  }

  .map-legend {
    bottom: 0.75rem;
    left: 0.5rem;
    padding: 0.4rem 0.6rem;
  }

  .legend-label {
    font-size: 0.65rem;
  }

  .map-status-badge {
    top: 0.5rem;
  }
}
</style>
