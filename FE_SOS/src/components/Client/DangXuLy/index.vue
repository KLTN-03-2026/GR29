<template>
  <div class="dang-xu-ly-page">
    <!-- Connection Banner -->
    <ConnectionStatusBanner />

    <!-- ─── HEADER ─────────────────────────────────────────── -->
    <header class="page-header">
      <div class="header-inner">
        <button class="back-btn" @click="goBack" :title="viewMode === 'detail' ? 'Quay lại danh sách' : 'Quay lại'">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path v-if="viewMode === 'list'" d="M19 12H5M12 19l-7-7 7-7" />
            <path v-else d="M19 12H5M12 19l-7-7 7-7" />
          </svg>
        </button>
        <div class="header-title-group">
          <div class="header-status-indicator" v-if="viewMode === 'detail'">
            <span class="status-pulse-dot"></span>
            <span class="status-label">Đang theo dõi</span>
          </div>
          <h1 class="header-title">{{ viewMode === 'detail' ? 'Chi Tiết Yêu Cầu' : 'Yêu Cầu Cứu Hộ' }}</h1>
        </div>
        <div class="header-right">
          <span class="request-count-chip" v-if="viewMode === 'list' && requests.length > 0">
            {{ requests.length }} yêu cầu
          </span>
          <span class="request-id-chip" v-if="viewMode === 'detail' && activeRequest">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            #{{ activeRequest.id }}
          </span>
          <button class="refresh-btn" @click="refreshData" :disabled="refreshing" title="Làm mới">
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              :class="{ 'spin': refreshing }"
            >
              <polyline points="23 4 23 10 17 10" />
              <polyline points="1 20 1 14 7 14" />
              <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
            </svg>
          </button>
        </div>
      </div>
    </header>

      <!-- ─── MAIN CONTENT ───────────────────────────────────── -->
    <main class="page-content">

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner-wrap">
          <div class="loading-spinner"></div>
          <p class="loading-text">Đang đồng bộ dữ liệu...</p>
        </div>
      </div>

      <!-- ─── LIST VIEW ──────────────────────────────────────── -->
      <div v-else-if="viewMode === 'list'" class="list-view">

        <!-- Empty State — No requests -->
        <div v-if="requests.length === 0" class="empty-state">
          <div class="empty-icon-wrap">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
              <circle cx="12" cy="12" r="10" />
              <path d="M8 14s1.5 2 4 2 4-2 4-2" />
              <line x1="9" y1="9" x2="9.01" y2="9" />
              <line x1="15" y1="9" x2="15.01" y2="9" />
            </svg>
          </div>
          <h3 class="empty-heading">Không có yêu cầu đang xử lý</h3>
          <p class="empty-desc">Bạn không có yêu cầu cứu hộ nào đang được xử lý.</p>
          <button class="empty-action-btn" @click="$router.push('/gui-yeu-cau')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            Tạo yêu cầu mới
          </button>
        </div>

        <!-- Request List -->
        <div v-else class="request-list">
          <div
            v-for="item in requests"
            :key="item.id"
            class="request-list-item"
            @click="selectRequest(item)"
          >
            <div class="list-item-priority-dot" :class="getPriorityClass(item)"></div>
            <div class="list-item-body">
              <div class="list-item-top">
                <div class="list-item-badges">
                  <span class="list-item-type-badge">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                      <line x1="12" y1="9" x2="12" y2="13" />
                      <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    {{ item.loai }}
                  </span>
                  <span class="list-item-status-badge" :class="getStatusClass(item)">
                    {{ getStatusText(item) }}
                  </span>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="list-item-arrow">
                  <polyline points="9 18 15 12 9 6" />
                </svg>
              </div>
              <div class="list-item-address">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
                {{ item.address }}
              </div>
              <div class="list-item-bottom">
                <span class="list-item-time">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                  </svg>
                  {{ formatTime(item.createdAt) }}
                </span>
                <span class="list-item-id">#{{ item.id }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── DETAIL VIEW ────────────────────────────────────── -->
      <div v-else class="detail-view">

        <!-- Empty State — No active request selected -->
        <div v-if="!activeRequest" class="empty-state">
          <div class="empty-icon-wrap">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
              <circle cx="12" cy="12" r="10" />
              <path d="M8 14s1.5 2 4 2 4-2 4-2" />
              <line x1="9" y1="9" x2="9.01" y2="9" />
              <line x1="15" y1="9" x2="15.01" y2="9" />
            </svg>
          </div>
          <h3 class="empty-heading">Không có yêu cầu nào được chọn</h3>
          <button class="empty-action-btn" @click="viewMode = 'list'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="15 18 9 12 15 6" />
            </svg>
            Quay lại danh sách
          </button>
        </div>

        <!-- Active Request View -->
        <div v-else class="active-layout" :class="{ 'map-visible': mapStep }">

          <!-- ─── TOP SECTION: Info Cards (scrollable on mobile) ─── -->
          <div class="info-section">

            <!-- Priority Banner -->
            <div class="priority-banner" :class="priorityBannerClass">
              <div class="banner-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                  <line x1="12" y1="9" x2="12" y2="13" />
                  <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
              </div>
              <div class="banner-content">
                <span class="banner-level">{{ priorityText }}</span>
                <span class="banner-type">{{ activeRequest.mucDoKhanCap }}</span>
              </div>
              <div class="banner-time">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <circle cx="12" cy="12" r="10" />
                  <polyline points="12 6 12 12 16 14" />
                </svg>
                {{ formatTime(activeRequest.createdAt) }}
              </div>
            </div>

            <!-- Incident Type & Address -->
            <div class="incident-card">
              <div class="incident-top">
                <div class="incident-badges">
                  <span class="incident-type-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                      <line x1="12" y1="9" x2="12" y2="13" />
                      <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    {{ activeRequest.loai }}
                  </span>
                </div>
              </div>
              <div class="incident-address">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
                <span>{{ activeRequest.address }}</span>
              </div>
              <div class="incident-description" v-if="activeRequest.moTa">
                {{ activeRequest.moTa }}
              </div>
            </div>

            <!-- Rescuer Info Card -->
            <RescuerInfoCard
              :show="true"
              :name="rescuerInfo.name"
              :phone="rescuerInfo.phone"
              :team-name="rescuerInfo.teamName"
              :role="rescuerInfo.role"
              :eta="rescuerInfo.eta"
              :distance="rescuerInfo.distance"
              :status="rescuerInfo.status"
              :loading="rescuerLoading"
              :show-stats="rescuerInfo.eta !== null || rescuerInfo.distance !== null"
            />

            <!-- Request Progress -->
            <div class="progress-card">
              <div class="card-section-header">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                </svg>
                <span>Tiến trình cứu hộ</span>
              </div>
              <RequestProgress :step="currentProgressStep" />
            </div>

          </div>

          <!-- ─── MAP SECTION ──────────────────────────────────── -->
          <div class="map-section" v-if="mapStep">
            <RescueMap
              :loading="mapLoading"
              :error-msg="mapError"
              :client-marker="clientMarker"
              :rescuer-marker="rescuerMarker"
              :incident-marker="incidentMarker"
              :show-rescuer-marker="showRescuerMarker"
              :show-client-marker="true"
              :badge-text="mapBadgeText"
              :badge-class="mapBadgeClass"
              @zoom-in="zoomIn"
              @zoom-out="zoomOut"
              @locate="locateMe"
              @retry="initMap"
              @map-ready="onMapReady"
            />
          </div>

        </div>
      </div>
    </main>

    <!-- ─── CONTACT ACTIONS (floating) ─────────────────────── -->
    <ContactActions
      :show="contactActionsVisible"
      :phone="rescuerInfo.phone"
      :chat-url="rescuerInfo.chatUrl"
      :navigate-url="navigateUrl"
    />

    <!-- ─── DETAIL MODAL ──────────────────────────────────── -->
    <div v-if="showDetailModal" class="modal-overlay" @click.self="showDetailModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-header-left">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <h3 class="modal-title">Chi Tiết Yêu Cầu</h3>
          </div>
          <button class="modal-close-btn" @click="showDetailModal = false">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>
        <div class="modal-body" v-if="activeRequest">
          <div class="detail-row">
            <span class="detail-label">Mã yêu cầu</span>
            <span class="detail-value font-mono">#{{ activeRequest.id }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Loại sự cố</span>
            <span class="detail-value">{{ activeRequest.loai }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Mức độ</span>
            <span class="detail-value">
              <span class="priority-pill" :class="priorityBannerClass">{{ activeRequest.mucDoKhanCap }}</span>
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Địa điểm</span>
            <span class="detail-value">{{ activeRequest.address }}</span>
          </div>
          <div class="detail-row" v-if="activeRequest.moTa">
            <span class="detail-label">Mô tả</span>
            <span class="detail-value">{{ activeRequest.moTa }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Thời gian gửi</span>
            <span class="detail-value">{{ formatTime(activeRequest.createdAt) }}</span>
          </div>
          <div class="detail-row" v-if="rescuerInfo.teamName">
            <span class="detail-label">Đội cứu hộ</span>
            <span class="detail-value">{{ rescuerInfo.teamName }}</span>
          </div>
          <div class="detail-row" v-if="rescuerInfo.name">
            <span class="detail-label">Người phụ trách</span>
            <span class="detail-value">{{ rescuerInfo.name }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="modal-btn-cancel" @click="showDetailModal = false">Đóng</button>
          <a v-if="navigateUrl" :href="navigateUrl" target="_blank" class="modal-btn-nav">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polygon points="3 11 22 2 13 21 11 13 3 11" />
            </svg>
            Chỉ đường
          </a>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import ConnectionStatusBanner from "../../common/ConnectionStatusBanner.vue";
import RequestProgress from "./components/RequestProgress.vue";
import RescueMap from "./components/RescueMap.vue";
import ContactActions from "./components/ContactActions.vue";
import RescuerInfoCard from "./components/RescuerInfoCard.vue";
import { rescueRequestAPI, assignmentAPI } from "../../../services/api.js";
import { loadOpenMap, createOpenMap, createOpenMapMarker, createOpenMapPopup } from "../../../utils/openMap.js";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

// ─── Helper constants ────────────────────────────────────────────────────────────

const STATUS_STEP = {
  CHO_XU_LY: 1,
  DA_PHAN_CONG: 2,
  DANG_XU_LY: 3,
  DA_DEN_HIEN_TRUONG: 4,
  HOAN_THANH: 5,
  DA_HOAN_THANH: 5,
  HUY_BO: 0,
  DA_HUY: 0,
  TU_CHOI: 0,
  THAT_BAI: 0,
  DONE: 5,
};

const CLOSED_STATUS = new Set(["HOAN_THANH", "DA_HOAN_THANH", "HUY_BO", "DA_HUY", "TU_CHOI", "THAT_BAI", "DONE"]);

// ─── Helper functions ───────────────────────────────────────────────────────────

function normalizeValue(value, fallback = "") {
  if (!value) return fallback;
  if (typeof value === "object") {
    return normalizeValue(
      value.ten_danh_muc || value.ten_loai_su_co || value.ten || value.name || fallback,
      fallback
    );
  }
  return String(value);
}

function normalizeStatusCode(value) {
  return String(value || "")
    .trim()
    .toUpperCase()
    .replace(/\s+/g, "_");
}

function formatTime(value) {
  if (!value) return "";
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return normalizeValue(value);
  return d.toLocaleString("vi-VN", {
    hour: "2-digit",
    minute: "2-digit",
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

function extractUserId() {
  const sources = ["user_token", "user", "client"];
  for (const key of sources) {
    const raw = localStorage.getItem(key);
    if (!raw) continue;
    try {
      const parsed = JSON.parse(raw);
      const id = parsed?.id_nguoi_dung || parsed?.id || parsed?.user_id || parsed?.ma_nguoi_dung || parsed?.nguoi_dung_id;
      if (id !== undefined && id !== null && id !== "") return Number(id);
    } catch { /* ignore */ }
  }
  return null;
}

function getStepFromStatus(status) {
  return STATUS_STEP[normalizeStatusCode(status)] ?? 1;
}

export default {
  name: "ClientDangXuLy",
  components: {
    ConnectionStatusBanner,
    RequestProgress,
    RescueMap,
    ContactActions,
    RescuerInfoCard,
  },
  data() {
    return {
      loading: false,
      refreshing: false,
      requests: [],
      viewMode: "list", // "list" | "detail"
      activeRequest: null,
      realtimeChannel: null,
      fallbackPollingInterval: null,
      map: null,
      mapLoading: false,
      mapError: "",
      mapResizeObserver: null,
      clientMarker: null,
      rescuerMarker: null,
      incidentMarker: null,
      rescuerMarkerData: null,
      routeLayer: null,
      routeSource: null,
      mapReady: false,
      // Throttle route updates to avoid duplicate API calls
      routeUpdateCooldown: false,
      routeUpdateTimeout: null,
      showDetailModal: false,
      rescuerLoading: false,
      rescuerLocationChannel: null,
    };
  },
  computed: {
    currentProgressStep() {
      if (!this.activeRequest) return 1;
      const status = this.activeRequest.trangThai;
      return getStepFromStatus(status);
    },
    mapStep() {
      return this.currentProgressStep >= 2;
    },
    showRescuerMarker() {
      return this.rescuerMarkerData && this.currentProgressStep >= 2;
    },
    contactActionsVisible() {
      return this.currentProgressStep >= 2 && this.rescuerInfo.phone;
    },
    mapBadgeText() {
      const step = this.currentProgressStep;
      if (step === 1) return "Đang tìm cứu hộ...";
      if (step === 2) return "Đã nhận nhiệm vụ";
      if (step === 3) return "Đang di chuyển";
      if (step === 4) return "Đã đến hiện trường";
      if (step >= 5) return "Hoàn thành";
      return "";
    },
    mapBadgeClass() {
      const step = this.currentProgressStep;
      if (step === 1) return "badge-blue";
      if (step === 2) return "badge-orange";
      if (step === 3) return "badge-orange";
      if (step === 4) return "badge-green";
      return "badge-blue";
    },
    priorityBannerClass() {
      const level = this.activeRequest?.mucDoKhanCap?.toUpperCase() || "";
      if (level === "CRITICAL" || level === "HIGH") return "banner-danger";
      if (level === "MEDIUM") return "banner-warning";
      return "banner-success";
    },
    priorityText() {
      const level = this.activeRequest?.mucDoKhanCap?.toUpperCase() || "";
      if (level === "CRITICAL" || level === "HIGH") return "Khẩn cấp";
      if (level === "MEDIUM") return "Trung bình";
      return "Thường";
    },
    navigateUrl() {
      if (!this.activeRequest?.address) return "";
      return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(this.activeRequest.address)}`;
    },
    rescuerInfo() {
      if (!this.activeRequest) {
        return { name: "", phone: "", teamName: "", role: "", eta: null, distance: null, status: "", chatUrl: "" };
      }
      const assignment = this.activeRequest.assignment;
      const team = this.activeRequest.team;

      let phone = "";
      let name = "";
      let status = "";
      let eta = null;
      let distance = null;

      if (assignment) {
        if (assignment.rescuer_phone || assignment.so_dien_thoai_thanh_vien) {
          phone = assignment.rescuer_phone || assignment.so_dien_thoai_thanh_vien;
        }
        if (assignment.rescuer_name || assignment.ten_thanh_vien) {
          name = assignment.rescuer_name || assignment.ten_thanh_vien;
        }
        status = assignment.trang_thai_nhiem_vu || assignment.status || "";
        eta = assignment.eta || assignment.duoc_gian || null;
        distance = assignment.distance || assignment.khoang_cach || null;
      }

      if (!phone && team?.so_dien_thoai) {
        phone = team.so_dien_thoai;
      }

      const teamName = team?.ten_doi_cuu_ho || team?.ten_doi || this.activeRequest.tenDoiCuuHo || "";

      let chatUrl = "";
      if (phone) {
        chatUrl = `https://zalo.me/${phone.replace(/\D/g, "")}`;
      }

      return { name, phone, teamName, role: "", eta, distance, status, chatUrl };
    },
  },
  watch: {
    activeRequest(newReq, oldReq) {
      const newTeamId = newReq?.team?.id || newReq?.team?.id_doi_cuu_ho || newReq?.assignment?.id_doi_cuu_ho;
      const oldTeamId = oldReq?.team?.id || oldReq?.team?.id_doi_cuu_ho || oldReq?.assignment?.id_doi_cuu_ho;
      if (String(newTeamId) !== String(oldTeamId)) {
        this.unsubscribeFromRescuerLocation();
        if (this.currentProgressStep >= 2) {
          this.subscribeToRescuerLocation();
        }
      }
    },
    currentProgressStep(step) {
      if (step >= 3 && this.mapReady && this.rescuerMarkerData && this.activeRequest?.lat) {
        this.drawRoute(
          this.rescuerMarkerData.lat,
          this.rescuerMarkerData.lng,
          this.activeRequest.lat,
          this.activeRequest.lng
        );
        this.calculateETA();
      }
    },
  },
  async created() {
    await this.loadData();
    this.subscribeToReverb();
    this.startFallbackPolling();
  },
  beforeUnmount() {
    this.unsubscribeFromReverb();
    this.stopFallbackPolling();
    this.cleanupMap();
    clearTimeout(this.routeUpdateTimeout);
  },
  methods: {
    goBack() {
      if (this.viewMode === "detail") {
        this.viewMode = "list";
        return;
      }
      if (window.history.length > 2) {
        this.$router.back();
      } else {
        this.$router.push("/");
      }
    },

    selectRequest(item) {
      this.activeRequest = item;
      this.viewMode = "detail";
      // If assigned, load team info
      if (this.currentProgressStep >= 2) {
        this.loadRescuerInfo();
        this.subscribeToRescuerLocation();
      } else {
        this.unsubscribeFromRescuerLocation();
      }
      // Update map if ready
      this.$nextTick(() => {
        if (this.mapReady) this.updateMapMarkers();
      });
    },

    // ─── Data Loading ─────────────────────────────────────────────────────────
    async loadData(silent = false) {
      if (!silent) this.loading = true;
      if (silent) this.refreshing = true;

      try {
        const userId = extractUserId();
        if (!userId) {
          this.activeRequest = null;
          return;
        }

        let requestResponse;
        try {
          requestResponse = await rescueRequestAPI.getByUser(userId);
        } catch {
          requestResponse = await rescueRequestAPI.getList();
        }

        const raw = requestResponse?.data;
        const items = Array.isArray(raw)
          ? raw
          : Array.isArray(raw?.data)
          ? raw.data
          : Array.isArray(raw?.data?.data)
          ? raw.data.data
          : [];

        // Filter active (non-closed) requests for this user
        const activeItems = items.filter((item) => {
          const itemUserId =
            item.id_nguoi_dung ||
            item.id ||
            item.user_id ||
            item.ma_nguoi_dung ||
            item.nguoi_dung_id ||
            item.nguoi_dung?.id_nguoi_dung ||
            item.nguoi_dung?.id;
          if (itemUserId && Number(itemUserId) !== Number(userId)) return false;
          const reqStatus = normalizeStatusCode(item.trang_thai || item.status);
          if (CLOSED_STATUS.has(reqStatus)) return false;
          return true;
        });

        // Store all active requests in the list
        this.requests = activeItems.map((item) => this.normalizeItem(item));

        // Use first active request as the detail view
        if (activeItems.length > 0) {
          const item = activeItems[0];
          // If viewing in detail mode already, keep the detail; otherwise start on list
          if (this.viewMode === "detail" && this.activeRequest) {
            // Keep the current detail view
          } else {
            this.activeRequest = this.normalizeItem(item);
          }

          // If assigned, load team info
          if (this.currentProgressStep >= 2) {
            this.loadRescuerInfo();
            this.subscribeToRescuerLocation();
          }

          // Update map if ready
          this.$nextTick(() => {
            if (this.mapReady) this.updateMapMarkers();
          });
        } else {
          this.activeRequest = null;
        }
      } catch (e) {
        console.error("Lỗi tải dữ liệu:", e);
      } finally {
        this.loading = false;
        this.refreshing = false;
      }
    },

    async refreshData() {
      await this.loadData(true);
    },

    async loadRescuerInfo() {
      if (!this.activeRequest?.id) return;
      this.rescuerLoading = true;
      try {
        const requestId =
          this.activeRequest.id_yeu_cau ||
          this.activeRequest.id ||
          this.activeRequest.yeu_cau?.id_yeu_cau;
        if (!requestId) return;

        const res = await rescueRequestAPI.getTrackingDetail(requestId);
        const data = res?.data?.data || res?.data;

        if (data) {
          // Support both camelCase and snake_case from API
          const phanCong =
            data.phanCongs?.[0] ||
            data.phan_congs?.[0] ||
            data.phanCong ||
            data.phan_cong ||
            {};
          const team =
            phanCong.doiCuuHo ||
            phanCong.doi_cuu_ho ||
            data.team ||
            data.doi_cuu_ho ||
            {};
          const members =
            phanCong.thanhViens ||
            phanCong.thanh_viens ||
            team.thanhViens ||
            team.thanh_viens ||
            [];
          const primaryMember =
            members.find((m) => m.trang_thai === "online") ||
            members[0];

          this.activeRequest = {
            ...this.activeRequest,
            team: {
              id: team.id_doi_cuu_ho || team.id || phanCong.id_doi,
              ten_doi_cuu_ho: team.ten_doi || team.ten_co || team.tenDoi || phanCong.ten_doi || "",
              so_dien_thoai: team.so_dien_thoai || team.sdt_hotline || "",
              vi_tri_lat: phanCong.vi_tri_lat || phanCong.viTriLat || team.vi_tri_lat || null,
              vi_tri_lng: phanCong.vi_tri_lng || phanCong.viTriLng || team.vi_tri_lng || null,
            },
            assignment: {
              ...this.activeRequest.assignment,
              id_phan_cong: phanCong.id_phan_cong || phanCong.id,
              trang_thai_nhiem_vu:
                phanCong.trang_thai_nhiem_vu || phanCong.trangThaiNhiemVu,
              rescuer_name: primaryMember?.ho_ten || phanCong.ten_nguoi_tiep_nhan || "",
              rescuer_phone:
                primaryMember?.so_dien_thoai || phanCong.sdt_hotline || "",
              thanh_viens: members,
            },
          };

          // Update rescuer location if available
          const rescuerLat = phanCong.vi_tri_lat || phanCong.viTriLat || team.vi_tri_lat;
          const rescuerLng = phanCong.vi_tri_lng || phanCong.viTriLng || team.vi_tri_lng;
          if (rescuerLat && rescuerLng) {
            this.rescuerMarkerData = {
              lat: Number(rescuerLat),
              lng: Number(rescuerLng),
            };
            if (this.mapReady) this.updateRescuerMarker();
          }

          // Calculate ETA from route API
          if (this.activeRequest.lat && this.activeRequest.lng && this.rescuerMarkerData) {
            this.calculateETA();
          }
        }
      } catch (e) {
        console.warn("Không thể tải thông tin rescuer:", e);
      } finally {
        this.rescuerLoading = false;
      }
    },

    normalizeItem(item) {
      const lat = item.vi_tri_lat || item.lat;
      const lng = item.vi_tri_lng || item.lng;
      const address =
        item.vi_tri_dia_chi || item.dia_chi || item.address || item.vi_tri || "Chưa xác định";

      // Laravel serializes relationships as camelCase in JSON
      // Support both camelCase (API response) and snake_case (internal)
      const phanCongRaw =
        item.phanCongs?.[0] ||
        item.phan_congs?.[0] ||
        item.phan_cong ||
        item.phanCong ||
        item.assignment ||
        {};
      const teamRaw =
        item.doiCuuHo ||
        item.doi_cuu_ho ||
        item.team ||
        phanCongRaw.doiCuuHo ||
        phanCongRaw.doi_cuu_ho ||
        {};

      // Get team location from assignment or team object
      const rescuerLat =
        phanCongRaw.vi_tri_lat ||
        phanCongRaw.viTriLat ||
        teamRaw.vi_tri_lat ||
        teamRaw.viTriLat ||
        null;
      const rescuerLng =
        phanCongRaw.vi_tri_lng ||
        phanCongRaw.viTriLng ||
        teamRaw.vi_tri_lng ||
        teamRaw.viTriLng ||
        null;

      // Get member info (thanhViens in camelCase)
      const members =
        phanCongRaw.thanhViens ||
        phanCongRaw.thanh_viens ||
        teamRaw.thanhViens ||
        teamRaw.thanh_viens ||
        [];
      const primaryMember =
        members.find((m) => m.trang_thai === "online") ||
        members.find((m) => m.trangThai === "online") ||
        members[0];

      let typeLabel = "Yêu cầu cứu hộ";
      if (item.loaiSuCo) {
        typeLabel = normalizeValue(
          item.loaiSuCo.ten_danh_muc || item.loaiSuCo.ten_loai_su_co || item.loaiSuCo.ten
        );
      } else if (item.loai_su_co) {
        typeLabel = normalizeValue(item.loai_su_co);
      } else {
        typeLabel = normalizeValue(
          item.ten_loai_su_co || item.loai_su_co || item.loai || item.chi_tiet
        );
      }

      return {
        id: item.id_yeu_cau || item.id || item.ma_yeu_cau,
        loai: typeLabel,
        moTa: normalizeValue(item.mo_ta || item.moTa || item.description),
        address,
        lat: lat ? Number(lat) : null,
        lng: lng ? Number(lng) : null,
        mucDoKhanCap: normalizeValue(
          item.muc_do_khan_cap || item.mucDoKhanCap || item.priority
        ),
        trangThai: item.trang_thai || "CHO_XU_LY",
        trangThaiNhiemVu: phanCongRaw.trang_thai_nhiem_vu || phanCongRaw.trangThaiNhiemVu || "",
        createdAt:
          item.thoi_gian_tao ||
          item.thoiGianTao ||
          item.created_at ||
          item.thoi_gian ||
          item.time,
        // Assignment data
        assignment: {
          id_phan_cong: phanCongRaw.id_phan_cong || phanCongRaw.id_phanCong || phanCongRaw.id,
          trang_thai_nhiem_vu:
            phanCongRaw.trang_thai_nhiem_vu || phanCongRaw.trangThaiNhiemVu || "",
          rescuer_name:
            primaryMember?.ho_ten ||
            phanCongRaw.ten_nguoi_tiep_nhan ||
            "",
          rescuer_phone:
            primaryMember?.so_dien_thoai ||
            phanCongRaw.sdt_hotline ||
            "",
          thanh_viens: members,
          eta: null,
          distance: null,
        },
        // Team data
        team: {
          id: teamRaw.id_doi_cuu_ho || teamRaw.id || phanCongRaw.id_doi,
          ten_doi_cuu_ho:
            teamRaw.ten_doi || teamRaw.ten_co || teamRaw.tenDoi || phanCongRaw.ten_doi || "",
          so_dien_thoai:
            teamRaw.so_dien_thoai ||
            teamRaw.sdt_hotline ||
            "",
          vi_tri_lat: rescuerLat,
          vi_tri_lng: rescuerLng,
        },
        // Backward compat
        phanCongs: item.phanCongs || item.phan_congs,
        raw: item,
      };
    },

    // ─── Map ───────────────────────────────────────────────────────────────────
    onMapReady(container) {
      if (this.mapReady) return;
      this.$nextTick(() => this.initMap(container));
    },

    async initMap(container) {
      if (!container) {
        container = this.$el?.querySelector(".map-canvas");
      }
      if (!container) return;

      this.mapLoading = true;
      this.mapError = "";

      try {
        const mapEl =
          typeof container === "object" && container.getBoundingClientRect
            ? container
            : container.$el || container;

        await loadOpenMap();
        this.map = createOpenMap(mapEl, {
          center: [108.2022, 16.0544],
          zoom: 14,
          mapStyle: "day-v1",
        });

        this.map.on("load", () => {
          this.mapReady = true;
          this.mapLoading = false;
          this.updateMapMarkers();
          this.observeMapContainer();
        });
      } catch (error) {
        this.mapError = error?.message || "Không thể tải bản đồ";
        this.mapLoading = false;
      }
    },

    updateMapMarkers() {
      if (!this.map || !this.activeRequest) return;

      // Client / incident marker
      if (this.activeRequest.lat && this.activeRequest.lng) {
        if (this.incidentMarker) this.incidentMarker.remove();

        const sev = this.activeRequest.mucDoKhanCap?.toUpperCase() || "";
        const colorMap = { CRITICAL: "#7f1d1d", HIGH: "#dc2626", MEDIUM: "#f97316", LOW: "#22c55e" };
        const color = colorMap[sev] || "#dc2626";

        this.incidentMarker = createOpenMapMarker({
          position: { lng: Number(this.activeRequest.lng), lat: Number(this.activeRequest.lat) },
          fillColor: color,
          label: { text: "!" },
          title: this.activeRequest.loai,
        }).addTo(this.map);

        this.map.flyTo({
          center: [Number(this.activeRequest.lng), Number(this.activeRequest.lat)],
          zoom: 15,
          essential: true,
        });

        // Draw route to incident if rescuer location known
        if (this.rescuerMarkerData && this.currentProgressStep >= 3) {
          this.drawRoute(
            this.rescuerMarkerData.lat,
            this.rescuerMarkerData.lng,
            this.activeRequest.lat,
            this.activeRequest.lng
          );
        }
      }

      this.updateRescuerMarker();
    },

    updateRescuerMarker() {
      if (!this.map || !this.showRescuerMarker || !this.rescuerMarkerData) return;

      const { lat, lng } = this.rescuerMarkerData;

      if (this.rescuerMarker) {
        this.rescuerMarker.setLngLat([Number(lng), Number(lat)]);
      } else {
        this.rescuerMarker = createOpenMapMarker({
          position: { lng: Number(lng), lat: Number(lat) },
          fillColor: "#10b981",
          title: "Đội cứu hộ",
        }).addTo(this.map);
      }
    },

    async drawRoute(lat1, lng1, lat2, lng2) {
      if (!this.map) return;
      this.cleanupRoute();
      try {
        const response = await fetch(
          `https://router.project-osrm.org/route/v1/driving/${lng1},${lat1};${lng2},${lat2}?overview=full&geometries=geojson`
        );
        const data = await response.json();
        if (data.routes?.length > 0) {
          const coordinates = data.routes[0].geometry.coordinates.map((c) => [c[0], c[1]]);
          const routeGeoJSON = {
            type: "Feature",
            geometry: { type: "LineString", coordinates },
          };
          this.map.addSource("rescue-route", { type: "geojson", data: routeGeoJSON });
          this.map.addLayer({
            id: "rescue-route-line",
            type: "line",
            source: "rescue-route",
            layout: { "line-join": "round", "line-cap": "round" },
            paint: { "line-color": "#10b981", "line-width": 5, "line-opacity": 0.8 },
          });
          this.routeSource = "rescue-route";
          this.routeLayer = "rescue-route-line";
        }
      } catch {
        // silent
      }
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

    observeMapContainer() {
      const mapEl = this.$el?.querySelector(".map-canvas");
      if (!mapEl || typeof ResizeObserver === "undefined") return;
      this.mapResizeObserver = new ResizeObserver(() => {
        if (this.map) this.map.resize();
      });
      this.mapResizeObserver.observe(mapEl);
    },

    cleanupMap() {
      if (this.mapResizeObserver) {
        this.mapResizeObserver.disconnect();
        this.mapResizeObserver = null;
      }
      if (this.map) {
        this.map.remove();
        this.map = null;
      }
      this.mapReady = false;
      this.clientMarker = null;
      this.rescuerMarker = null;
      this.incidentMarker = null;
    },

    zoomIn() { if (this.map) this.map.zoomIn(); },
    zoomOut() { if (this.map) this.map.zoomOut(); },
    locateMe() {
      if (!navigator.geolocation || !this.map) return;
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const { latitude: lat, longitude: lng } = pos.coords;
          if (lat > 12 && lng > 107.5) {
            this.map.flyTo({ center: [lng, lat], zoom: 15, essential: true });
          }
        },
        () => {},
        { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
      );
    },

    async calculateETA() {
      if (!this.rescuerMarkerData || !this.activeRequest?.lat) return;
      try {
        const { lat: lat1, lng: lng1 } = this.rescuerMarkerData;
        const lat2 = this.activeRequest.lat;
        const lng2 = this.activeRequest.lng;
        const response = await fetch(
          `https://router.project-osrm.org/route/v1/driving/${lng1},${lat1};${lng2},${lat2}?overview=false`
        );
        const data = await response.json();
        if (data.routes?.length > 0) {
          const durationSeconds = data.routes[0].duration;
          const distanceMeters = data.routes[0].distance;
          const etaMinutes = Math.ceil(durationSeconds / 60);
          const distanceKm = distanceMeters / 1000;
          this.activeRequest = {
            ...this.activeRequest,
            assignment: {
              ...this.activeRequest.assignment,
              eta: etaMinutes,
              distance: parseFloat(distanceKm.toFixed(1)),
            },
          };
        }
      } catch { /* silent */ }
    },

    // ─── Realtime ─────────────────────────────────────────────────────────────
    subscribeToReverb() {
      if (!window.Echo) {
        setTimeout(() => this.subscribeToReverb(), 2000);
        return;
      }
      const connect = () => {
        this.realtimeChannel = window.Echo.channel("rescue-requests");
        this.realtimeChannel.listen("RescueRequestUpdated", (data) => {
          this.handleReverbEvent(data);
        });
        // Subscribe to rescuer location updates for the current team
        this.subscribeToRescuerLocation();
      };
      const conn = window.Echo.connector?.pusher?.connection;
      if (conn?.state === "connected") {
        connect();
      } else if (conn) {
        conn.bind("connected", connect);
        setTimeout(() => { if (!this.realtimeChannel) connect(); }, 5000);
      } else {
        setTimeout(() => this.subscribeToReverb(), 2000);
      }
    },

    unsubscribeFromReverb() {
      if (this.realtimeChannel) {
        this.realtimeChannel.stopListening("RescueRequestUpdated");
        window.Echo.leave("rescue-requests");
        this.realtimeChannel = null;
      }
      this.unsubscribeFromRescuerLocation();
    },

    subscribeToRescuerLocation() {
      if (!window.Echo) return;
      this.unsubscribeFromRescuerLocation();

      const teamId = this.activeRequest?.team?.id || this.activeRequest?.team?.id_doi_cuu_ho || this.activeRequest?.assignment?.id_doi_cuu_ho;
      if (!teamId) return;

      this.rescuerLocationChannel = window.Echo.channel(`rescuer-location.${teamId}`);
      this.rescuerLocationChannel.listen("location.updated", (data) => {
        this.handleLocationEvent(data);
      });
    },

    unsubscribeFromRescuerLocation() {
      if (this.rescuerLocationChannel) {
        this.rescuerLocationChannel.stopListening("location.updated");
        const teamId = this.activeRequest?.team?.id || this.activeRequest?.team?.id_doi_cuu_ho || this.activeRequest?.assignment?.id_doi_cuu_ho;
        if (teamId) {
          window.Echo.leave(`rescuer-location.${teamId}`);
        }
        this.rescuerLocationChannel = null;
      }
    },

    handleReverbEvent(data) {
      const userId = extractUserId();
      if (!userId) return;
      const eventUserId = data.id_nguoi_dung ?? data.userId;
      if (eventUserId && Number(eventUserId) !== Number(userId)) return;

      const requestId = String(data.id_yeu_cau ?? data.id ?? "");
      const closed = new Set(["HOAN_THANH", "DA_HOAN_THANH", "HUY_BO", "DA_HUY", "TU_CHOI", "THAT_BAI", "DONE"]);

      if (closed.has(data.trang_thai)) {
        this.activeRequest = null;
        toaster.info("Yêu cầu đã hoàn thành hoặc bị hủy.");
        return;
      }

      if (this.activeRequest && String(this.activeRequest.id) === requestId) {
        this.activeRequest = {
          ...this.activeRequest,
          trangThai: data.trang_thai || this.activeRequest.trangThai,
          trangThaiNhiemVu: data.trang_thai_nhiem_vu || data.assignmentStatus || this.activeRequest.trangThaiNhiemVu,
          team: data.ten_doi_cuu_ho ? { ...this.activeRequest.team, ten_doi_cuu_ho: data.ten_doi_cuu_ho } : this.activeRequest.team,
          assignment: data.id_phan_cong
            ? {
                ...this.activeRequest.assignment,
                id_phan_cong: data.id_phan_cong,
                rescuer_name: data.rescuer_name || this.activeRequest.assignment.rescuer_name,
                rescuer_phone: data.rescuer_phone || this.activeRequest.assignment.rescuer_phone,
                trang_thai_nhiem_vu: data.trang_thai_nhiem_vu || this.activeRequest.assignment.trang_thai_nhiem_vu,
              }
            : this.activeRequest.assignment,
        };

        const newStep = getStepFromStatus(data.trang_thai || data.trang_thai_nhiem_vu);
        const oldStep = getStepFromStatus(this.activeRequest.trangThai);
        if (newStep !== oldStep) {
          const statusMessages = {
            2: "Đã có đội cứu hộ nhận nhiệm vụ!",
            3: "Đội cứu hộ đang di chuyển đến hiện trường!",
            4: "Đội cứu hộ đã đến hiện trường!",
            5: "Nhiệm vụ đã hoàn thành!",
          };
          if (statusMessages[newStep]) {
            toaster.success(statusMessages[newStep]);
          }
          // Load rescuer info when first assigned
          if (newStep >= 2) {
            this.loadRescuerInfo();
          }
          // Subscribe to rescuer location updates when assigned
          if (newStep >= 2) {
            this.subscribeToRescuerLocation();
          }
          // Update map when step changes
          this.$nextTick(() => {
            if (this.mapReady) this.updateMapMarkers();
          });
        }
      }
    },

    handleLocationEvent(data) {
      if (!this.activeRequest) return;
      const teamId = data.id_doi_cuu_ho ?? data.teamId;
      const currentTeamId =
        this.activeRequest.team?.id_doi_cuu_ho ||
        this.activeRequest.team?.id ||
        this.activeRequest.assignment?.id_doi_cuu_ho;
      if (teamId && currentTeamId && String(teamId) !== String(currentTeamId)) return;

      if (data.lat && data.lng) {
        this.rescuerMarkerData = { lat: Number(data.lat), lng: Number(data.lng) };
        if (this.mapReady) {
          this.updateRescuerMarker();
          this.throttledRouteUpdate();
        }
      }
    },

    throttledRouteUpdate() {
      if (this.routeUpdateCooldown || !this.rescuerMarkerData || !this.activeRequest?.lat) return;
      this.routeUpdateCooldown = true;
      this.calculateETA();
      if (this.currentProgressStep >= 3) {
        this.drawRoute(
          this.rescuerMarkerData.lat,
          this.rescuerMarkerData.lng,
          this.activeRequest.lat,
          this.activeRequest.lng
        );
      }
      clearTimeout(this.routeUpdateTimeout);
      this.routeUpdateTimeout = setTimeout(() => {
        this.routeUpdateCooldown = false;
      }, 3000);
    },

    startFallbackPolling() {
      this.stopFallbackPolling();
      this.fallbackPollingInterval = setInterval(async () => {
        const status = window.realtimeConnectionStatus || "connecting";
        if (status === "connected") return;
        try {
          await this.loadData(true);
        } catch { /* silent */ }
      }, 30000);
    },

    stopFallbackPolling() {
      if (this.fallbackPollingInterval) {
        clearInterval(this.fallbackPollingInterval);
        this.fallbackPollingInterval = null;
      }
    },

    formatTime,
    getPriorityClass(item) {
      const level = item?.mucDoKhanCap?.toUpperCase() || "";
      if (level === "CRITICAL" || level === "HIGH") return "dot-danger";
      if (level === "MEDIUM") return "dot-warning";
      return "dot-normal";
    },
    getStatusText(item) {
      const step = getStepFromStatus(item?.trangThai);
      const statusMap = { 1: "Chờ xử lý", 2: "Đã phân công", 3: "Đang xử lý", 4: "Đã đến hiện trường", 5: "Hoàn thành" };
      return statusMap[step] || "Chờ xử lý";
    },
    getStatusClass(item) {
      const step = getStepFromStatus(item?.trangThai);
      if (step >= 5) return "badge-success";
      if (step >= 3) return "badge-orange";
      if (step >= 2) return "badge-blue";
      return "badge-gray";
    },
  },
};
</script>

<style scoped>
/* ─── Page Layout ──────────────────────────────────────────────────────────── */
.dang-xu-ly-page {
  min-height: calc(100vh - 130px);
  display: flex;
  flex-direction: column;
  background: #f8fafc;
  font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  --page-px: 1rem;
}

/* ─── Header ─────────────────────────────────────────────────────────────── */
.page-header {
  background: #1e293b;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  position: sticky;
  top: 0;
  z-index: 50;
  flex-shrink: 0;
}

.header-inner {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.875rem 1.25rem;
}

.back-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1.5px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

.header-title-group {
  flex: 1;
  min-width: 0;
}

.header-status-indicator {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 0.1rem;
}

.status-pulse-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #ef4444;
  animation: header-pulse 1.5s ease-in-out infinite;
}

@keyframes header-pulse {
  0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(239, 68, 69, 0.6); }
  50% { opacity: 0.8; box-shadow: 0 0 0 5px rgba(239, 68, 69, 0); }
}

.status-label {
  font-size: 0.62rem;
  font-weight: 700;
  color: #ef4444;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.header-title {
  font-size: 1rem;
  font-weight: 800;
  color: #f1f5f9;
  margin: 0;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.request-id-chip {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.3rem 0.75rem;
  border-radius: 20px;
  background: rgba(37, 99, 235, 0.25);
  color: #93c5fd;
  font-size: 0.68rem;
  font-weight: 700;
  font-family: monospace;
  white-space: nowrap;
}

.refresh-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1.5px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.refresh-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

.refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ─── Content ────────────────────────────────────────────────────────────── */
.page-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ─── Loading State ─────────────────────────────────────────────────────── */
.loading-state {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.loading-spinner-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.loading-text {
  font-size: 0.875rem;
  font-weight: 600;
  color: #94a3b8;
}

/* ─── Empty State ───────────────────────────────────────────────────────── */
.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1.5rem;
  text-align: center;
}

.empty-icon-wrap {
  color: #cbd5e1;
  margin-bottom: 1.25rem;
}

.empty-heading {
  font-size: 1.1rem;
  font-weight: 800;
  color: #334155;
  margin-bottom: 0.5rem;
}

.empty-desc {
  font-size: 0.875rem;
  color: #94a3b8;
  font-weight: 500;
  margin-bottom: 1.5rem;
  max-width: 360px;
}

.empty-action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 1.25rem;
  border-radius: 12px;
  background: #dc2626;
  color: #ffffff;
  font-size: 0.875rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}

.empty-action-btn:hover {
  background: #b91c1c;
  color: #ffffff;
}

.empty-action-btn:active {
  opacity: 0.85;
}

/* ─── Active Layout ─────────────────────────────────────────────────────── */
.active-layout {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.active-layout.map-visible {
  display: grid;
  grid-template-rows: auto 1fr;
}

@media (min-width: 1024px) {
  .active-layout.map-visible {
    display: grid;
    grid-template-columns: 420px 1fr;
    grid-template-rows: 1fr;
    height: calc(100vh - 130px);
  }

  .info-section {
    overflow-y: auto;
    height: 100%;
  }

  .map-section {
    height: 100% !important;
  }
}

/* ─── Info Section ──────────────────────────────────────────────────────── */
.info-section {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1rem 5rem;
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.info-section::-webkit-scrollbar {
  width: 4px;
}
.info-section::-webkit-scrollbar-track { background: transparent; }
.info-section::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 2px; }

/* ─── Priority Banner ────────────────────────────────────────────────────── */
.priority-banner {
  border-radius: 14px;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.banner-danger { background: #fef2f2; border: 1px solid #fecaca; }
.banner-warning { background: #fefce8; border: 1px solid #fde047; }
.banner-success { background: #f0fdf4; border: 1px solid #bbf7d0; }

.banner-icon {
  width: 38px;
  height: 38px;
  border-radius: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.banner-danger .banner-icon { background: #dc2626; color: #ffffff; }
.banner-warning .banner-icon { background: #ca8a04; color: #ffffff; }
.banner-success .banner-icon { background: #16a34a; color: #ffffff; }

.banner-content {
  flex: 1;
  min-width: 0;
}

.banner-level {
  font-size: 0.85rem;
  font-weight: 800;
  display: block;
}

.banner-danger .banner-level { color: #991b1b; }
.banner-warning .banner-level { color: #713f12; }
.banner-success .banner-level { color: #14532d; }

.banner-type {
  font-size: 0.7rem;
  font-weight: 600;
  opacity: 0.6;
}

.banner-time {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.68rem;
  font-weight: 600;
  color: #94a3b8;
  white-space: nowrap;
  flex-shrink: 0;
}

/* ─── Incident Card ─────────────────────────────────────────────────────── */
.incident-card {
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 1rem;
}

.incident-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.incident-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
}

.incident-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.3rem 0.75rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.incident-address {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 600;
  line-height: 1.4;
  margin-bottom: 0.5rem;
}

.incident-address svg {
  color: #ef4444;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.incident-description {
  font-size: 0.82rem;
  color: #94a3b8;
  line-height: 1.5;
  padding-top: 0.5rem;
  border-top: 1px solid #f1f5f9;
}

/* ─── Progress Card ─────────────────────────────────────────────────────── */
.progress-card {
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 1rem;
}

.card-section-header {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.65rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.875rem;
}

/* ─── Map Section ───────────────────────────────────────────────────────── */
.map-section {
  height: 280px;
  flex-shrink: 0;
}

/* ─── Detail Modal ──────────────────────────────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(17, 24, 39, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 200;
  padding: 0;
}

.modal-card {
  background: #ffffff;
  border-radius: 20px 20px 0 0;
  width: 100%;
  max-width: 600px;
  max-height: 85vh;
  overflow-y: auto;
  animation: modal-enter 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@media (min-width: 640px) {
  .modal-overlay {
    align-items: center;
    padding: 1rem;
  }
  .modal-card {
    border-radius: 20px;
  }
}

@keyframes modal-enter {
  from { opacity: 0; transform: translateY(30px) scale(0.97); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.25rem 0;
  position: sticky;
  top: 0;
  background: #ffffff;
  z-index: 1;
}

.modal-header-left {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  color: #2563eb;
}

.modal-title {
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
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0;
}

.detail-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #94a3b8;
  flex-shrink: 0;
}

.detail-value {
  font-size: 0.82rem;
  font-weight: 700;
  color: #1e293b;
  text-align: right;
  line-height: 1.4;
}

.font-mono {
  font-family: monospace;
  font-weight: 800;
  color: #2563eb;
}

.priority-pill {
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
}

.priority-pill.banner-danger { background: #fef2f2; color: #991b1b; }
.priority-pill.banner-warning { background: #fefce8; color: #713f12; }
.priority-pill.banner-success { background: #f0fdf4; color: #14532d; }

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.6rem;
  padding: 0.75rem 1.25rem 1.25rem;
  position: sticky;
  bottom: 0;
  background: #ffffff;
}

.modal-btn-cancel {
  padding: 0.6rem 1.25rem;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: transparent;
  color: #64748b;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  font-family: "Inter", sans-serif;
}

.modal-btn-cancel:hover {
  border-color: #cbd5e1;
  color: #334155;
  background: #f8fafc;
}

.modal-btn-nav {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1.25rem;
  border-radius: 10px;
  border: none;
  background: #dc2626;
  color: #ffffff;
  font-size: 0.82rem;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
  font-family: "Inter", sans-serif;
}

.modal-btn-nav:hover {
  background: #b91c1c;
  color: #ffffff;
}

/* ─── List View ──────────────────────────────────────────────────────── */
.list-view {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.request-list {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.request-list::-webkit-scrollbar { width: 4px; }
.request-list::-webkit-scrollbar-track { background: transparent; }
.request-list::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 2px; }

.request-list-item {
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 0.875rem 1rem;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.request-list-item:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
}

.request-list-item:active {
  opacity: 0.85;
}

.list-item-priority-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 0.3rem;
}

.dot-danger { background: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 69, 0.2); }
.dot-warning { background: #f97316; box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.2); }
.dot-normal { background: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2); }

.list-item-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.list-item-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.list-item-badges {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  flex-wrap: wrap;
}

.list-item-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.25rem 0.625rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.list-item-status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.625rem;
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 700;
}

.badge-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.badge-orange { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
.badge-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.badge-gray { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

.list-item-arrow {
  color: #94a3b8;
  flex-shrink: 0;
}

.list-item-address {
  display: flex;
  align-items: flex-start;
  gap: 0.375rem;
  color: #64748b;
  font-size: 0.78rem;
  font-weight: 600;
  line-height: 1.4;
}

.list-item-address svg {
  color: #ef4444;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.list-item-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.list-item-time {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.68rem;
  font-weight: 600;
  color: #94a3b8;
}

.list-item-id {
  font-size: 0.65rem;
  font-weight: 700;
  font-family: monospace;
  color: #2563eb;
  background: #eff6ff;
  padding: 0.15rem 0.5rem;
  border-radius: 6px;
}

/* ─── Detail View ─────────────────────────────────────────────────────── */
.detail-view {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ─── Request Count Chip ─────────────────────────────────────────────── */
.request-count-chip {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.3rem 0.75rem;
  border-radius: 20px;
  background: rgba(37, 99, 235, 0.15);
  color: #93c5fd;
  font-size: 0.68rem;
  font-weight: 700;
  white-space: nowrap;
}

/* ─── Responsive ────────────────────────────────────────────────────────── */
@media (max-width: 480px) {
  .header-inner {
    padding: 0.75rem 1rem;
    gap: 0.75rem;
  }

  .header-title {
    font-size: 0.9rem;
  }

  .info-section {
    padding: 0.75rem 0.75rem 6rem;
    gap: 0.75rem;
  }

  .map-section {
    height: 240px;
  }

  .incident-card,
  .progress-card {
    padding: 0.875rem;
  }
}

@media (min-width: 640px) and (max-width: 1023px) {
  .map-section {
    height: 320px;
  }
}
</style>
