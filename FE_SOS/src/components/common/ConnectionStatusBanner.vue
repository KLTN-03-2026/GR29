<template>
  <div v-if="showBanner" class="connection-status-banner"
       :class="bannerClass">
    <div class="banner-content">
      <div class="status-icon">
        <i :class="iconClass"></i>
      </div>
      <div class="status-text">
        <strong>{{ statusText }}</strong>
        <div class="status-subtitle">{{ subtitle }}</div>
      </div>
      <button v-if="canRetry" @click="retryConnection"
              class="btn btn-sm btn-outline-light ms-3">
        <i class="fa-solid fa-refresh"></i> Thử lại
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConnectionStatusBanner',
  data() {
    return {
      status: 'connecting', // connecting, connected, disconnected, failed, unavailable
      reconnectAttempts: 0,
      maxReconnectAttempts: 3
    };
  },
  computed: {
    showBanner() {
      return this.status !== 'connected';
    },
    bannerClass() {
      const baseClass = 'alert alert-dismissible fade show position-fixed w-100 mb-0 border-0 rounded-0 z-index-1050';
      switch (this.status) {
        case 'connecting':
          return `${baseClass} alert-warning`;
        case 'disconnected':
        case 'failed':
        case 'unavailable':
          return `${baseClass} alert-danger`;
        default:
          return `${baseClass} alert-info`;
      }
    },
    iconClass() {
      switch (this.status) {
        case 'connecting':
          return 'fa-solid fa-spinner fa-spin';
        case 'connected':
          return 'fa-solid fa-check-circle';
        case 'disconnected':
          return 'fa-solid fa-exclamation-triangle';
        case 'failed':
          return 'fa-solid fa-times-circle';
        case 'unavailable':
          return 'fa-solid fa-wifi-slash';
        default:
          return 'fa-solid fa-info-circle';
      }
    },
    statusText() {
      switch (this.status) {
        case 'connecting':
          return 'Đang kết nối realtime...';
        case 'connected':
          return 'Đã kết nối realtime';
        case 'disconnected':
          return 'Mất kết nối realtime';
        case 'failed':
          return 'Kết nối realtime thất bại';
        case 'unavailable':
          return 'Dịch vụ realtime không khả dụng';
        default:
          return 'Trạng thái kết nối không xác định';
      }
    },
    subtitle() {
      switch (this.status) {
        case 'connecting':
          return 'Đang thiết lập kết nối với máy chủ';
        case 'disconnected':
          return 'Các cập nhật sẽ bị chậm trễ. Đang tự động kết nối lại...';
        case 'failed':
          return 'Không thể kết nối. Vui lòng kiểm tra kết nối mạng';
        case 'unavailable':
          return 'Dịch vụ realtime tạm thời không khả dụng';
        default:
          return '';
      }
    },
    canRetry() {
      return ['disconnected', 'failed', 'unavailable'].includes(this.status) &&
             this.reconnectAttempts < this.maxReconnectAttempts;
    }
  },
  mounted() {
    // Listen for connection status changes
    window.addEventListener('realtime-connection-change', this.handleConnectionChange);

    // Set initial status
    this.status = window.realtimeConnectionStatus || 'connecting';
  },
  beforeUnmount() {
    window.removeEventListener('realtime-connection-change', this.handleConnectionChange);
  },
  methods: {
    handleConnectionChange(event) {
      this.status = event.detail.status;

      if (this.status === 'connected') {
        this.reconnectAttempts = 0;
        // Auto-hide success banner after 3 seconds
        setTimeout(() => {
          if (this.status === 'connected') {
            this.status = 'connected'; // Keep it hidden
          }
        }, 3000);
      }
    },
    retryConnection() {
      if (this.reconnectAttempts >= this.maxReconnectAttempts) return;

      this.reconnectAttempts++;
      this.status = 'connecting';

      // Force reconnection
      if (window.Echo?.connector?.pusher?.connection) {
        const connection = window.Echo.connector.pusher.connection;
        if (connection.state !== 'connected') {
          connection.connect();
        }
      }

      console.log(`[Connection] Manual retry attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);
    }
  }
};
</script>

<style scoped>
.connection-status-banner {
  top: 0;
  left: 0;
  right: 0;
  z-index: 1050;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.banner-content {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  max-width: 1200px;
  margin: 0 auto;
}

.status-icon {
  margin-right: 0.75rem;
  font-size: 1.1rem;
}

.status-text strong {
  font-size: 0.95rem;
  margin-bottom: 0.125rem;
}

.status-subtitle {
  font-size: 0.8rem;
  opacity: 0.8;
  margin-top: 0.125rem;
}

.z-index-1050 {
  z-index: 1050 !important;
}

@media (max-width: 768px) {
  .banner-content {
    padding: 0.5rem 0.75rem;
  }

  .status-text strong {
    font-size: 0.9rem;
  }

  .status-subtitle {
    font-size: 0.75rem;
  }
}
</style>