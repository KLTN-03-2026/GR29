<template>
  <div class="request-progress">
    <div class="steps-container">
      <div
        v-for="(step, index) in steps"
        :key="step.key"
        class="step-wrapper"
      >
        <div
          class="step-item"
          :class="{
            'step-done': currentStep > index + 1,
            'step-active': currentStep === index + 1,
            'step-pending': currentStep < index + 1,
          }"
        >
          <!-- Node -->
          <div class="step-node">
            <svg
              v-if="currentStep > index + 1"
              class="step-icon-check"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="3"
            >
              <polyline points="20 6 9 17 4 12" />
            </svg>
            <span v-else class="step-number">{{ index + 1 }}</span>
          </div>

          <!-- Label -->
          <div class="step-label">
            <span class="step-title">{{ step.label }}</span>
            <span class="step-subtitle">{{ step.subtitle }}</span>
          </div>
        </div>

        <!-- Connector to next step -->
        <div v-if="index < steps.length - 1" class="step-connector-wrapper">
          <div
            class="step-connector"
            :class="{ 'connector-done': currentStep > index + 1 }"
          >
            <div v-if="currentStep === index + 2" class="connector-animate"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Status text -->
    <div class="current-status" v-if="currentStepText">
      <div class="status-pulse-wrapper">
        <span class="status-pulse-dot"></span>
      </div>
      <span class="status-message">{{ currentStepText }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: "RequestProgress",
  props: {
    step: {
      type: Number,
      default: 1,
    },
    showLabels: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      steps: [
        { key: "searching", label: "Đang tìm cứu hộ", subtitle: "Hệ thống đang tìm đội" },
        { key: "assigned", label: "Đã có người nhận", subtitle: "Đội đã tiếp nhận" },
        { key: "moving", label: "Đang di chuyển", subtitle: "Đội đang trên đường" },
        { key: "arrived", label: "Đã đến nơi", subtitle: "Đội đã tới hiện trường" },
        { key: "processing", label: "Đang xử lý", subtitle: "Cứu hộ đang làm việc" },
      ],
    };
  },
  computed: {
    currentStep() {
      return Math.min(Math.max(1, this.step), this.steps.length);
    },
    currentStepText() {
      if (this.step >= this.steps.length) {
        return "Đội cứu hộ đang xử lý tại hiện trường";
      }
      const s = this.steps[this.currentStep - 1];
      return s ? s.subtitle : "";
    },
  },
};
</script>

<style scoped>
.request-progress {
  width: 100%;
}

.steps-container {
  display: flex;
  flex-direction: column;
  gap: 0;
  position: relative;
}

/* ─── Step Item ─────────────────────────────── */
.step-wrapper {
  display: flex;
  flex-direction: column;
}

.step-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.25rem 0;
}

/* ─── Step Node ─────────────────────────────── */
.step-node {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 2.5px solid #e2e8f0;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  position: relative;
  z-index: 2;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.step-number {
  font-size: 0.8rem;
  font-weight: 700;
  color: #94a3b8;
  line-height: 1;
  transition: color 0.3s;
}

.step-icon-check {
  color: #ffffff;
  transition: color 0.3s;
}

/* Done state */
.step-done .step-node {
  background: #10b981;
  border-color: #10b981;
  box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
}

/* Active state */
.step-active .step-node {
  background: #2563eb;
  border-color: #2563eb;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
  animation: node-pulse 2s ease-in-out infinite;
}

.step-active .step-number {
  color: #ffffff;
}

@keyframes node-pulse {
  0%, 100% { box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15); }
  50% { box-shadow: 0 0 0 8px rgba(37, 99, 235, 0.08); }
}

/* Pending state */
.step-pending .step-node {
  background: #f8fafc;
  border-color: #e2e8f0;
}

/* ─── Step Label ─────────────────────────────── */
.step-label {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  min-width: 0;
}

.step-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #94a3b8;
  transition: color 0.3s;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.step-subtitle {
  font-size: 0.72rem;
  font-weight: 500;
  color: #cbd5e1;
  transition: color 0.3s;
}

.step-done .step-title { color: #10b981; }
.step-done .step-subtitle { color: #86efac; }
.step-active .step-title { color: #1e3a8a; }
.step-active .step-subtitle { color: #60a5fa; }

/* ─── Connector ─────────────────────────────── */
.step-connector-wrapper {
  padding-left: 1.125rem;
  margin: 0.15rem 0;
}

.step-connector {
  width: 2px;
  height: 28px;
  background: #e2e8f0;
  position: relative;
  overflow: hidden;
  transition: background 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.connector-done {
  background: #10b981;
}

.connector-animate {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 8px;
  background: linear-gradient(180deg, #10b981 0%, #34d399 100%);
  animation: connector-flow 1.5s ease-in-out infinite;
  border-radius: 2px;
}

@keyframes connector-flow {
  0% { transform: translateY(-100%); opacity: 0; }
  30% { opacity: 1; }
  70% { opacity: 1; }
  100% { transform: translateY(400%); opacity: 0; }
}

/* ─── Current Status ─────────────────────────── */
.current-status {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 12px;
}

.status-pulse-wrapper {
  flex-shrink: 0;
}

.status-pulse-dot {
  display: block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #2563eb;
  animation: status-dot-pulse 1.5s ease-in-out infinite;
}

@keyframes status-dot-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(0.85); }
}

.status-message {
  font-size: 0.82rem;
  font-weight: 700;
  color: #1e40af;
}

/* ─── Responsive ─────────────────────────────── */
@media (max-width: 480px) {
  .step-title {
    font-size: 0.8rem;
  }
  .step-subtitle {
    font-size: 0.68rem;
  }
  .step-node {
    width: 32px;
    height: 32px;
  }
  .step-connector {
    height: 24px;
  }
}
</style>
