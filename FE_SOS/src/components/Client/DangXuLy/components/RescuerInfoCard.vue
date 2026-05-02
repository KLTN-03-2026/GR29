<template>
  <transition name="card-fade">
    <div v-if="show" class="rescuer-info-card">
      <div class="card-inner">
        <!-- Header -->
        <div class="card-header">
          <div class="header-left">
            <div class="rescuer-avatar" :style="avatarStyle">
              <span class="avatar-initial">{{ initial }}</span>
              <span class="avatar-online-dot" v-if="isOnline"></span>
            </div>
            <div class="rescuer-meta">
              <span class="rescuer-role">{{ roleText }}</span>
              <h3 class="rescuer-name">{{ name || "Đang tìm đội cứu hộ..." }}</h3>
              <div class="rescuer-team" v-if="teamName">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                {{ teamName }}
              </div>
            </div>
          </div>

          <!-- ETA Badge -->
          <div class="eta-badge" v-if="etaText">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
            <span>{{ etaText }}</span>
          </div>
        </div>

        <!-- Stats Row -->
        <div class="stats-row" v-if="showStats">
          <div class="stat-item">
            <span class="stat-value">{{ distanceText }}</span>
            <span class="stat-label">Khoảng cách</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <span class="stat-value">{{ etaMinutesText }}</span>
            <span class="stat-label">Dự kiến</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <span class="stat-value stat-value-status">{{ statusText }}</span>
            <span class="stat-label">Trạng thái</span>
          </div>
        </div>

        <!-- Footer -->
        <div class="card-footer" v-if="phone">
          <a :href="'tel:' + phone" class="footer-call-btn" @click.stop>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.77 13.1 19.79 19.79 0 0 1 1.71 4.49 2 2 0 0 1 3.68 2.3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            Gọi ngay
          </a>
          <div class="footer-phone">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.77 13.1 19.79 19.79 0 0 1 1.71 4.49 2 2 0 0 1 3.68 2.3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            {{ phone }}
          </div>
        </div>

        <!-- Loading skeleton -->
        <div class="skeleton-footer" v-else-if="loading">
          <div class="skeleton-line skeleton-line-short"></div>
          <div class="skeleton-line"></div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: "RescuerInfoCard",
  props: {
    show: { type: Boolean, default: false },
    name: { type: String, default: "" },
    phone: { type: String, default: "" },
    teamName: { type: String, default: "" },
    role: { type: String, default: "" },
    eta: { type: Number, default: null },
    distance: { type: Number, default: null },
    status: { type: String, default: "" },
    loading: { type: Boolean, default: false },
    showStats: { type: Boolean, default: true },
  },
  computed: {
    initial() {
      if (!this.name) return "?";
      const parts = this.name.trim().split(" ");
      if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
      }
      return this.name.substring(0, 2).toUpperCase();
    },
    avatarStyle() {
      if (!this.name) return { background: "#94a3b8" };
      const colors = [
        { bg: "#1e40af", text: "#60a5fa" },
        { bg: "#0f766e", text: "#5eead4" },
        { bg: "#7c3aed", text: "#c4b5fd" },
        { bg: "#b45309", text: "#fcd34d" },
        { bg: "#be185d", text: "#f9a8d4" },
      ];
      const idx =
        this.name
          .split("")
          .reduce((acc, ch) => acc + ch.charCodeAt(0), 0) % colors.length;
      return { background: colors[idx].bg };
    },
    isOnline() {
      return this.status && this.status !== "offline";
    },
    roleText() {
      const map = {
        leader: "Trưởng đội",
        member: "Thành viên",
        rescuer: "Nhân viên cứu hộ",
        team: "Đội cứu hộ",
      };
      return map[this.role?.toLowerCase()] || this.role || "Đội cứu hộ";
    },
    etaText() {
      if (this.eta === null || this.eta === undefined) return "";
      if (this.eta < 1) return "< 1 phút";
      return `${Math.round(this.eta)} phút`;
    },
    distanceText() {
      if (this.distance === null || this.distance === undefined) return "--";
      if (this.distance < 1) {
        return `${Math.round(this.distance * 1000)}m`;
      }
      return `${this.distance.toFixed(1)}km`;
    },
    etaMinutesText() {
      if (this.eta === null || this.eta === undefined) return "--";
      return `${Math.round(this.eta)}p`;
    },
    statusText() {
      const map = {
        accepted: "Đã nhận",
        moving: "Di chuyển",
        arrived: "Đã tới",
        processing: "Xử lý",
      };
      return map[this.status?.toLowerCase()] || this.status || "--";
    },
  },
};
</script>

<style scoped>
.rescuer-info-card {
  width: 100%;
}

.card-inner {
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.3s ease;
}

/* ─── Header ─────────────────────────────── */
.card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 1rem;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
  min-width: 0;
}

.rescuer-avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  position: relative;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.avatar-initial {
  font-size: 1rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.02em;
}

.avatar-online-dot {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #10b981;
  border: 2.5px solid #ffffff;
}

.rescuer-meta {
  flex: 1;
  min-width: 0;
}

.rescuer-role {
  font-size: 0.65rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  display: block;
  margin-bottom: 0.15rem;
}

.rescuer-name {
  font-size: 0.95rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 0.25rem;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.rescuer-team {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.72rem;
  font-weight: 600;
  color: #64748b;
}

/* ─── ETA Badge ─────────────────────────────── */
.eta-badge {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.35rem 0.75rem;
  background: #fef3c7;
  border: 1px solid #fde047;
  border-radius: 20px;
  color: #92400e;
  font-size: 0.72rem;
  font-weight: 700;
  flex-shrink: 0;
  white-space: nowrap;
}

/* ─── Stats Row ─────────────────────────────── */
.stats-row {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
  border-bottom: 1px solid #f1f5f9;
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.15rem;
}

.stat-value {
  font-size: 0.95rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1;
}

.stat-value-status {
  font-size: 0.8rem;
}

.stat-label {
  font-size: 0.65rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.stat-divider {
  width: 1px;
  height: 28px;
  background: #e2e8f0;
  flex-shrink: 0;
}

/* ─── Footer ─────────────────────────────── */
.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  gap: 0.75rem;
}

.footer-call-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1rem;
  background: #10b981;
  color: #ffffff;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s;
}

.footer-call-btn:hover {
  background: #059669;
  color: #ffffff;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.footer-phone {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: #64748b;
}

/* ─── Skeleton ─────────────────────────────── */
.skeleton-footer {
  padding: 0.75rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.skeleton-line {
  height: 12px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  border-radius: 6px;
  animation: skeleton-shimmer 1.5s infinite;
  width: 100%;
}

.skeleton-line-short {
  width: 60%;
}

@keyframes skeleton-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ─── Transitions ─────────────────────────── */
.card-fade-enter-active {
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.card-fade-leave-active {
  transition: all 0.25s ease;
}

.card-fade-enter-from,
.card-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.97);
}

/* ─── Responsive ─────────────────────────────── */
@media (max-width: 480px) {
  .rescuer-avatar {
    width: 46px;
    height: 46px;
  }

  .avatar-initial {
    font-size: 0.9rem;
  }

  .rescuer-name {
    font-size: 0.88rem;
  }

  .card-header {
    padding: 0.875rem;
  }

  .stats-row {
    padding: 0.625rem 0.875rem;
  }

  .card-footer {
    padding: 0.625rem 0.875rem;
  }
}
</style>
