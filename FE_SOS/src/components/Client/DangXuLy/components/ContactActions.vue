<template>
  <transition name="actions-slide-up">
    <div v-if="show" class="contact-actions">
      <!-- Toggle FAB (shown when collapsed on mobile) -->
      <button
        v-if="collapsed"
        class="fab-toggle"
        @click="collapsed = false"
        title="Liên hệ đội cứu hộ"
      >
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.77 13.1 19.79 19.79 0 0 1 1.71 4.49 2 2 0 0 1 3.68 2.3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
        </svg>
        <span class="fab-badge" v-if="!collapsed === false"></span>
      </button>

      <!-- Expanded Actions -->
      <div v-if="!collapsed" class="actions-group">
        <div class="actions-header">
          <span class="actions-title">Liên hệ đội cứu hộ</span>
          <button class="actions-close" @click="collapsed = true" title="Đóng">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>

        <div class="actions-body">
          <!-- Call Button -->
          <a
            v-if="phone"
            :href="'tel:' + phone"
            class="action-btn action-btn-call"
            @click.stop
          >
            <div class="action-btn-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.77 13.1 19.79 19.79 0 0 1 1.71 4.49 2 2 0 0 1 3.68 2.3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
              </svg>
            </div>
            <div class="action-btn-text">
              <span class="action-label">Gọi điện</span>
              <span class="action-sub">{{ phone }}</span>
            </div>
            <div class="action-btn-arrow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M9 18l6-6-6-6" />
              </svg>
            </div>
          </a>

          <!-- Chat Button -->
          <a
            v-if="chatUrl"
            :href="chatUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="action-btn action-btn-chat"
            @click.stop
          >
            <div class="action-btn-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
              </svg>
            </div>
            <div class="action-btn-text">
              <span class="action-label">Nhắn tin</span>
              <span class="action-sub">Zalo / SMS</span>
            </div>
            <div class="action-btn-arrow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M9 18l6-6-6-6" />
              </svg>
            </div>
          </a>

          <!-- Navigate Button -->
          <a
            v-if="navigateUrl"
            :href="navigateUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="action-btn action-btn-nav"
            @click.stop
          >
            <div class="action-btn-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polygon points="3 11 22 2 13 21 11 13 3 11" />
              </svg>
            </div>
            <div class="action-btn-text">
              <span class="action-label">Chỉ đường</span>
              <span class="action-sub">Mở Google Maps</span>
            </div>
            <div class="action-btn-arrow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M9 18l6-6-6-6" />
              </svg>
            </div>
          </a>

          <!-- No contact available -->
          <div v-if="!phone && !chatUrl && !navigateUrl" class="actions-empty">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="8" x2="12" y2="12" />
              <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <span>Chưa có thông tin liên hệ</span>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: "ContactActions",
  props: {
    show: { type: Boolean, default: false },
    phone: { type: String, default: "" },
    chatUrl: { type: String, default: "" },
    navigateUrl: { type: String, default: "" },
  },
  data() {
    return {
      collapsed: true,
    };
  },
  watch: {
    show(val) {
      if (val) this.collapsed = true;
    },
  },
};
</script>

<style scoped>
.contact-actions {
  position: fixed;
  bottom: 1.5rem;
  right: 1.25rem;
  z-index: 100;
}

/* ─── FAB Toggle ─────────────────────────── */
.fab-toggle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #10b981, #059669);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
  transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
  position: relative;
}

.fab-toggle:hover {
  transform: scale(1.08);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
}

.fab-toggle:active {
  transform: scale(0.95);
}

.fab-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #ef4444;
  border: 2px solid #ffffff;
}

/* ─── Actions Group ─────────────────────────── */
.actions-group {
  background: #ffffff;
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  min-width: 280px;
  max-width: 320px;
}

.actions-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem 1rem 0;
}

.actions-title {
  font-size: 0.82rem;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.actions-close {
  width: 28px;
  height: 28px;
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

.actions-close:hover {
  background: #e2e8f0;
  color: #334155;
}

.actions-body {
  padding: 0.75rem 0.75rem 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

/* ─── Action Buttons ─────────────────────────── */
.action-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 0.875rem;
  border-radius: 14px;
  border: none;
  background: #f8fafc;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
  width: 100%;
}

.action-btn:hover {
  transform: translateX(-2px);
}

.action-btn:active {
  transform: translateX(0) scale(0.98);
}

.action-btn-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.action-btn-call .action-btn-icon {
  background: #dcfce7;
  color: #16a34a;
}

.action-btn-call:hover {
  background: #f0fdf4;
}

.action-btn-chat .action-btn-icon {
  background: #dbeafe;
  color: #2563eb;
}

.action-btn-chat:hover {
  background: #eff6ff;
}

.action-btn-nav .action-btn-icon {
  background: #fee2e2;
  color: #dc2626;
}

.action-btn-nav:hover {
  background: #fef2f2;
}

.action-btn-text {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  min-width: 0;
}

.action-label {
  font-size: 0.875rem;
  font-weight: 700;
  color: #1e293b;
}

.action-sub {
  font-size: 0.72rem;
  font-weight: 500;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.action-btn-arrow {
  color: #cbd5e1;
  flex-shrink: 0;
}

/* ─── Empty State ─────────────────────────── */
.actions-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  color: #94a3b8;
  font-size: 0.8rem;
  font-weight: 500;
}

/* ─── Transitions ─────────────────────────── */
.actions-slide-up-enter-active {
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.actions-slide-up-leave-active {
  transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.actions-slide-up-enter-from,
.actions-slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.9);
}

/* ─── Responsive ─────────────────────────────── */
@media (max-width: 480px) {
  .contact-actions {
    bottom: 1rem;
    right: 1rem;
    left: 1rem;
  }

  .actions-group {
    min-width: unset;
    width: 100%;
  }

  .fab-toggle {
    width: 52px;
    height: 52px;
  }
}

@media (min-width: 769px) {
  .fab-toggle {
    width: 60px;
    height: 60px;
  }
}
</style>
