<template>
  <div class="tracking-dashboard container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h2 class="fw-bold mb-1 d-flex align-items-center gap-3">
          <i class="fa-solid fa-satellite-dish text-primary pulse-icon"></i>
          Giám sát cứu hộ thời gian thực
          <span v-if="selectedId" class="live-badge">
            <span class="live-dot"></span> LIVE
          </span>
        </h2>
        <p class="text-muted mb-0">
          Theo dõi tiến độ, vị trí và trao đổi trực tiếp với các yêu cầu đang xử lý
        </p>
      </div>
      <button class="btn btn-light bg-white border border-light-subtle shadow-sm fw-bolder d-flex align-items-center gap-2 px-4 py-2" @click="loadTrackingList" :disabled="loadingList">
        <i class="fa-solid fa-arrow-rotate-right text-primary" :class="{ 'spin': loadingList }"></i>
        <span>Đồng bộ</span>
      </button>
    </div>

    <!-- Empty state -->
    <div v-if="!loadingList && trackingList.length === 0" class="empty-state text-center py-5 my-5">
      <div class="empty-icon text-success bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
        <i class="fa-solid fa-check-double fs-2"></i>
      </div>
      <h4 class="fw-bold text-dark mb-2">Không có nhiệm vụ theo dõi</h4>
      <p class="text-muted fw-medium">Hiện tại không có yêu cầu cứu hộ nào đang trong quá trình xử lý.</p>
    </div>

    <!-- Loading state -->
    <div v-if="loadingList" class="d-flex flex-column align-items-center justify-content-center py-5">
      <div class="spinner"></div>
      <p class="text-muted fw-medium mt-3">Đang đồng bộ dữ liệu...</p>
    </div>

    <div class="row g-4" v-if="!loadingList && trackingList.length > 0">
      <!-- Cột trái: Danh sách yêu cầu -->
      <div class="col-xl-4 col-lg-5">
        <div class="card border-0 shadow-sm custom-card h-100">
          <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h6 class="text-uppercase fw-bold text-muted mb-0" style="letter-spacing: 1px; font-size: 0.8rem;">
              <i class="fa-solid fa-list-ul me-2 text-primary"></i> Yêu cầu đang theo dõi
              <span class="badge bg-primary-subtle text-primary ms-2">{{ trackingList.length }}</span>
            </h6>
          </div>
          <div class="card-body p-0 overflow-auto custom-scrollbar" style="max-height: 75vh;">
            <div
              v-for="item in trackingList"
              :key="item.id"
              class="request-list-item px-4 py-3 border-bottom cursor-pointer"
              :class="{ 'active': selectedId === item.id }"
              @click="selectRequest(item)"
            >
              <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="d-flex align-items-center gap-2">
                  <span class="level-badge" :class="getSeverityBadge(item.muc_do_khan_cap)">
                    {{ getSeverityLabel(item.muc_do_khan_cap) }}
                  </span>
                  <span class="fw-bold text-dark">#{{ item.id }}</span>
                </div>
                <span class="text-muted small fw-medium">
                  <i class="fa-regular fa-clock me-1"></i>{{ formatTimeShort(item.thoi_gian_cap_nhat) }}
                </span>
              </div>
                  <div class="text-dark fw-medium small mb-1">{{ item.loai_su_co?.ten || item.loai_su_co || 'N/A' }}</div>
              <div class="text-muted small text-truncate" style="max-width: 90%;">
                <i class="fa-solid fa-location-dot me-1 text-primary"></i>{{ item.vi_tri_dia_chi || 'Chưa có địa chỉ' }}
              </div>
              <div v-if="item.doi_cuu_ho" class="mt-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small fw-medium">
                  <i class="fa-solid fa-truck-medical me-1"></i>{{ item.doi_cuu_ho.ten_co }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cột phải: Chi tiết theo dõi -->
      <div class="col-xl-8 col-lg-7">
        <!-- Chưa chọn yêu cầu -->
        <div v-if="!selectedId" class="card border-0 shadow-sm h-100 d-flex flex-column justify-content-center align-items-center text-center p-5">
          <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 88px; height: 88px;">
            <i class="fa-solid fa-satellite-dish fs-1"></i>
          </div>
          <h4 class="fw-bold text-dark mb-2">Chọn yêu cầu để theo dõi</h4>
          <p class="text-muted max-w-sm mb-0">Nhấp vào một yêu cầu trong danh sách bên trái để xem chi tiết và vị trí của đội cứu hộ.</p>
        </div>

        <!-- Đang tải chi tiết -->
        <div v-else-if="loadingDetail" class="card border-0 shadow-sm h-100 d-flex flex-column justify-content-center align-items-center text-center p-5">
          <div class="spinner"></div>
          <p class="text-muted fw-medium mt-3">Đang tải chi tiết yêu cầu...</p>
        </div>

        <!-- Chi tiết yêu cầu -->
        <template v-else-if="trackingDetail">
          <!-- Card Thông tin đơn vị & Timeline -->
          <div class="d-flex flex-column gap-4" :class="{ 'detail-refreshing': isDetailRefreshing }">

            <!-- Card Rescuer & Timeline -->
            <div class="card border-0 shadow-sm custom-card">
              <div class="card-header bg-white border-0 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="text-uppercase fw-bold text-muted mb-0" style="letter-spacing: 1px; font-size: 0.8rem;">
                    <i class="fa-solid fa-truck-medical text-danger me-2"></i> Thông tin đơn vị cứu hộ
                  </h6>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge px-3 py-2 rounded-pill" :class="statusBadgeClass(trackingDetail.trang_thai)">
                      <span v-if="isDetailRefreshing" class="spinner-border spinner-border-sm me-1" style="width:12px;height:12px;border-width:2px;"></span>
                      {{ statusLabel(trackingDetail.trang_thai) }}
                    </span>
                    <button class="btn btn-sm btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width:30px;height:30px;" title="Đóng" @click="clearSelection">
                      <i class="fa-solid fa-xmark small"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <!-- Team info -->
                <div v-if="trackingDetail.phan_congs && trackingDetail.phan_congs.length > 0">
                  <div v-for="pc in trackingDetail.phan_congs" :key="pc.id_phan_cong" class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                      <div class="avatar bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                        <i class="fa-solid fa-user-shield"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h5 class="mb-1 fw-bold">{{ pc.ten_doi }}</h5>
                        <p class="text-muted mb-0 small">
                          <i class="fa-solid fa-phone me-1"></i> {{ pc.sdt_hotline || 'N/A' }}
                          <span v-if="pc.khu_vuc"> &bull; {{ pc.khu_vuc }}</span>
                        </p>
                      </div>
                      <button class="btn btn-light rounded-circle shadow-sm btn-icon" title="Gọi điện" @click="callTeam(pc.sdt_hotline)">
                        <i class="fa-solid fa-phone text-success"></i>
                      </button>
                    </div>

                    <!-- Members -->
                    <div v-if="pc.thanh_viens && pc.thanh_viens.length > 0" class="mb-3">
                      <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-users me-1"></i> Thành viên ({{ pc.thanh_viens.length }})
                      </small>
                      <div class="d-flex flex-wrap gap-2 mt-2">
                        <span v-for="m in pc.thanh_viens" :key="m.id" class="badge bg-light text-dark border px-2 py-1 fw-medium">
                          <i class="fa-solid fa-user me-1 text-primary"></i>{{ m.ho_ten }}
                        </span>
                      </div>
                    </div>

                    <!-- Timeline -->
                    <div class="tracking-timeline mt-4 position-relative">
                      <div class="timeline-line"></div>
                      <div class="timeline-step"
                        :class="{
                          'done': step.status === 'done',
                          'current': step.status === 'current'
                        }"
                        v-for="(step, index) in computedTimeline(trackingDetail)"
                        :key="index"
                      >
                        <div class="step-indicator">
                          <i v-if="step.status === 'done'" class="fa-solid fa-check small"></i>
                          <div v-else-if="step.status === 'current'" class="pulse-dot"></div>
                        </div>
                        <div class="step-content">
                          <h6 class="mb-0 fw-semibold">{{ step.title }}</h6>
                          <small class="text-muted">{{ step.time || 'Chưa thực hiện' }}</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Không có đội -->
                <div v-else class="text-center py-3 text-muted">
                  <i class="fa-solid fa-info-circle me-2"></i>Chưa có đội cứu hộ được phân công cho yêu cầu này.
                </div>
              </div>
            </div>

            <!-- Card Chi tiết yêu cầu -->
            <div class="card border-0 shadow-sm custom-card">
              <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="text-uppercase fw-bold text-muted mb-0" style="letter-spacing: 1px; font-size: 0.8rem;">
                  <i class="fa-solid fa-file-lines text-warning me-2"></i> Chi tiết sự cố
                </h6>
                <div class="d-flex gap-2 align-items-center">
                  <span class="level-badge" :class="getSeverityBadge(trackingDetail.muc_do_khan_cap)">
                    {{ getSeverityLabel(trackingDetail.muc_do_khan_cap) }}
                  </span>
                  <span class="badge bg-light text-dark border px-2 py-1 small fw-bold font-monospace">#{{ trackingDetail.id }}</span>
                  <span class="text-muted small fw-medium">
                    <i class="fa-regular fa-clock me-1"></i>{{ formatTimeShort(trackingDetail.thoi_gian_tao) }}
                  </span>
                </div>
              </div>
              <div class="card-body">
                <div class="row g-3 mb-3">
                  <div class="col-sm-6">
                    <div class="info-box p-3 rounded-3 bg-light border-start border-4 border-primary h-100">
                      <small class="text-muted d-block mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem;">
                        <i class="fa-solid fa-fire me-1"></i> Loại sự cố
                      </small>
                      <span class="fw-bold text-dark">{{ trackingDetail.loai_su_co?.ten || 'N/A' }}</span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="info-box p-3 rounded-3 bg-light border-start border-4 border-warning h-100">
                      <small class="text-muted d-block mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem;">
                        <i class="fa-solid fa-circle-info me-1"></i> Chi tiết
                      </small>
                      <span class="fw-bold text-dark">{{ trackingDetail.chi_tiet || 'Không có' }}</span>
                    </div>
                  </div>
                </div>

                <!-- Customer info -->
                <div class="info-group mb-3">
                  <label class="text-muted small mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-user me-1"></i> Người gửi yêu cầu
                  </label>
                  <div class="d-flex align-items-center justify-content-between bg-light rounded-3 p-2 px-3">
                    <div class="fw-medium text-dark">
                      <i class="fa-regular fa-user me-2 text-primary"></i>{{ trackingDetail.nguoi_yeu_cau?.ho_ten || 'N/A' }}
                    </div>
                    <div class="text-dark">
                      <i class="fa-solid fa-phone me-2 text-success"></i>{{ trackingDetail.nguoi_yeu_cau?.so_dien_thoai || 'N/A' }}
                    </div>
                  </div>
                </div>

                <!-- Location -->
                <div class="p-3 bg-light rounded-3 border-start border-4 border-danger mb-3">
                  <small class="text-muted d-block mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-map-location-dot me-1"></i> Vị trí hiện trường
                  </small>
                  <span class="text-dark fw-medium">{{ trackingDetail.vi_tri_dia_chi || 'Chưa có địa chỉ' }}</span>
                </div>

                <!-- Description -->
                <div v-if="trackingDetail.mo_ta" class="p-3 bg-light rounded-3 border-start border-4 border-info">
                  <small class="text-muted d-block mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-align-left me-1"></i> Mô tả sự cố
                  </small>
                  <span class="text-dark fst-italic">"{{ trackingDetail.mo_ta }}"</span>
                </div>
              </div>
            </div>

            <!-- Hành động -->
            <div class="d-flex flex-column gap-2">
              <div class="row g-2">
                <div class="col-6">
                  <button class="btn btn-outline-primary w-100 py-3 rounded-3 fw-medium btn-hover-rise" @click="contactClient(trackingDetail.nguoi_yeu_cau)">
                    <i class="fa-solid fa-phone me-2"></i>Liên hệ khách hàng
                  </button>
                </div>
                <div class="col-6">
                  <button class="btn btn-outline-success w-100 py-3 rounded-3 fw-medium btn-hover-rise" @click="contactTeam(trackingDetail.phan_congs)">
                    <i class="fa-solid fa-phone me-2"></i>Liên hệ đội cứu hộ
                  </button>
                </div>
              </div>
              <div class="row g-2">
                <!-- <div class="col-6">
                  <button class="btn btn-success w-100 py-3 rounded-3 fw-bold btn-hover-rise shadow-sm" @click="markComplete" :disabled="completing">
                    <span v-if="completing" class="spinner-border spinner-border-sm me-2"></span>
                    <i v-else class="fa-solid fa-check-double me-2"></i>Đánh dấu hoàn thành
                  </button>
                </div> -->
                <div class="col-6">
                  <button class="btn btn-danger w-100 py-3 rounded-3 fw-bold btn-hover-rise bg-gradient-danger border-0 shadow-sm" @click="cancelTask">
                    <i class="fa-solid fa-xmark me-2"></i>Huỷ nhiệm vụ
                  </button>
                </div>
              </div>
            </div>

          </div>
        </template>
      </div>
    </div>

    <!-- ===== MODAL ĐÁNH GIÁ ===== -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isRatingModalOpen" class="modal-overlay" @click.self="closeRatingModal">
          <div class="rating-modal">
            <!-- Header -->
            <div class="rating-header">
              <div class="rating-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
              </div>
              <div>
                <h3 class="rating-title">Đánh giá dịch vụ cứu hộ</h3>
                <p class="rating-subtitle">#{{ ratingItem?.id }} · {{ ratingItem?.loai_su_co?.ten || ratingItem?.loai_su_co || "N/A" }}</p>
              </div>
              <button class="rating-close-btn" @click="closeRatingModal" aria-label="Đóng">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Stars -->
            <div class="rating-stars-section">
              <p class="rating-label">Bạn hài lòng với dịch vụ cứu hộ không?</p>
              <div class="stars-row">
                <button
                  v-for="n in 5" :key="n" type="button"
                  class="star-btn"
                  :class="{ active: n <= hoveredStar, selected: n <= ratingData.selectedRating }"
                  @mouseenter="ratingData.hoveredStar = n"
                  @mouseleave="ratingData.hoveredStar = 0"
                  @click="selectRating(n)"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" :fill="n <= ratingData.selectedRating || n <= ratingData.hoveredStar ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                  </svg>
                </button>
              </div>
              <Transition name="label-fade">
                <p v-if="ratingData.selectedRating > 0" class="rating-feedback-text" :class="`rating-${ratingData.selectedRating}`">
                  {{ ratingLabel }}
                </p>
              </Transition>
            </div>

            <!-- Tags -->
            <div class="rating-tags-section">
              <p class="rating-label">Chia sẻ trải nghiệm của bạn</p>
              <div class="tags-grid">
                <button
                  v-for="tag in allTags" :key="tag.label"
                  type="button"
                  class="tag-chip"
                  :class="{ selected: ratingData.selectedTags.includes(tag.label) }"
                  @click="toggleTag(tag.label)"
                >
                  <span>{{ tag.icon }}</span>
                  {{ tag.label }}
                </button>
              </div>
            </div>

            <!-- Comment -->
            <div class="rating-comment-section">
              <label class="rating-label">Nhận xét thêm <span class="optional-label">(tùy chọn)</span></label>
              <textarea
                v-model="ratingData.comment"
                class="rating-textarea"
                :class="{ 'has-content': ratingData.comment.length > 0 }"
                placeholder="Mô tả chi tiết trải nghiệm của bạn..."
                rows="3" maxlength="500"
                :disabled="ratingData.submitting"
              ></textarea>
              <div class="char-counter" :class="{ warning: ratingData.comment.length > 450 }">
                {{ ratingData.comment.length }}/500
              </div>
            </div>

            <!-- Actions -->
            <div class="rating-actions">
              <button type="button" class="btn-cancel" @click="closeRatingModal" :disabled="ratingData.submitting">Hủy</button>
              <button
                type="button"
                class="btn-submit"
                :disabled="ratingData.selectedRating === 0 || ratingData.submitting"
                @click="submitRating"
              >
                <span v-if="ratingData.submitting" class="btn-spinner"></span>
                <span v-else>
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                  </svg>
                  Gửi đánh giá
                </span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script>
import { rescueRequestAPI } from "../../../services/api";

const SEVERITY_MAP = {
  'CRITICAL': { label: 'CRITICAL', badge: 'lv-critical' },
  'HIGH':     { label: 'HIGH',     badge: 'lv-high' },
  'MEDIUM':   { label: 'MEDIUM',   badge: 'lv-medium' },
  'LOW':      { label: 'LOW',      badge: 'lv-low' },
};

function getSeverityInfo(rawSev) {
  if (!rawSev) return SEVERITY_MAP['MEDIUM'];
  if (isNaN(rawSev)) {
    const upper = String(rawSev).toUpperCase().trim();
    if (upper === 'CRITICAL') return SEVERITY_MAP['CRITICAL'];
    if (upper === 'HIGH') return SEVERITY_MAP['HIGH'];
    if (upper === 'MEDIUM') return SEVERITY_MAP['MEDIUM'];
    if (upper === 'LOW') return SEVERITY_MAP['LOW'];
    return SEVERITY_MAP['MEDIUM'];
  }
  const n = parseInt(rawSev);
  if (n <= 1) return SEVERITY_MAP['LOW'];
  if (n === 2) return SEVERITY_MAP['MEDIUM'];
  if (n === 3) return SEVERITY_MAP['HIGH'];
  return SEVERITY_MAP['CRITICAL'];
}

function formatTimeShort(value) {
  if (!value) return "";
  const t = new Date(value);
  if (Number.isNaN(t.getTime())) return value;
  return t.toLocaleString("vi-VN", {
    hour: "2-digit", minute: "2-digit", day: "2-digit", month: "2-digit",
  });
}

function formatTime(value) {
  if (!value) return "";
  const t = new Date(value);
  if (Number.isNaN(t.getTime())) return value;
  return t.toLocaleString("vi-VN", {
    hour: "2-digit", minute: "2-digit", day: "2-digit", month: "2-digit", year: "numeric",
  });
}

export default {
  name: "AdminTheoDoiYeuCau",
  data() {
    return {
      trackingList: [],
      selectedId: null,
      trackingDetail: null,
      loadingList: false,
      loadingDetail: false,
      completing: false,
      realtimeChannel: null,
      isDetailRefreshing: false,
      prevStatusMap: {},
      isRatingModalOpen: false,
      ratingItem: null,
      ratingData: {
        selectedRating: 0,
        hoveredStar: 0,
        selectedTags: [],
        comment: "",
        submitting: false,
      },
      allTags: [
        { label: "Phản hồi nhanh", icon: "⚡" },
        { label: "Thái độ tốt", icon: "😊" },
        { label: "Chuyên nghiệp", icon: "🏅" },
        { label: "Trang thiết bị đầy đủ", icon: "🔧" },
        { label: "Xử lý hiệu quả", icon: "✅" },
        { label: "Thông tin rõ ràng", icon: "📋" },
        { label: "Hỗ trợ tận tâm", icon: "🤝" },
        { label: "An toàn", icon: "🛡" },
      ],
    };
  },
  created() {
    this.loadTrackingList();
    this.subscribeToReverb();
  },
  beforeUnmount() {
    this.unsubscribeFromReverb();
  },
  mounted() {
    const queryId = this.$route.query.id;
    if (queryId && this.trackingList.length > 0) {
      this.$nextTick(() => {
        const item = this.trackingList.find(r => String(r.id) === String(queryId) || String(r.id_yeu_cau) === String(queryId));
        if (item) this.selectRequest(item);
      });
    }
  },
  computed: {
    ratingLabel() {
      const labels = { 1: "Rất không hài lòng", 2: "Không hài lòng", 3: "Bình thường", 4: "Hài lòng", 5: "Rất hài lòng" };
      return labels[this.ratingData.selectedRating] || "";
    },
  },
  watch: {
    trackingList(val) {
      const queryId = this.$route.query.id;
      if (queryId && !this.selectedId && val.length > 0) {
        const item = val.find(r => String(r.id) === String(queryId) || String(r.id_yeu_cau) === String(queryId));
        if (item) this.selectRequest(item);
      }
    },
  },
  methods: {
    // ─── Reverb WebSocket ────────────────────────────────────────────────────────
    subscribeToReverb() {
      if (!window.Echo) {
        console.warn('[Reverb] Echo not available');
        return;
      }

      const connect = () => {
        this.realtimeChannel = window.Echo.channel('rescue-requests');
        this.realtimeChannel.listen('RescueRequestUpdated', (data) => {
          this.handleReverbEvent(data);
        });
      };

      const conn = window.Echo.connector?.pusher?.connection;
      if (conn?.state === 'connected') {
        connect();
      } else if (conn) {
        conn.bind('connected', () => connect());
        setTimeout(() => { if (!this.realtimeChannel) connect(); }, 5000);
      } else {
        setTimeout(() => {
          const retryConn = window.Echo?.connector?.pusher?.connection;
          if (retryConn?.state === 'connected') connect();
          else if (retryConn) retryConn.bind('connected', () => connect());
        }, 2000);
      }
    },

    unsubscribeFromReverb() {
      if (this.realtimeChannel) {
        this.realtimeChannel.stopListening('RescueRequestUpdated');
        window.Echo.leave('rescue-requests');
        this.realtimeChannel = null;
      }
    },

    handleReverbEvent(data) {
      const requestId = Number(data.id_yeu_cau ?? data.id ?? 0);
      if (!requestId) return;

      const closed = new Set(["HOAN_THANH", "DA_HOAN_THANH", "HUY_BO", "DA_HUY", "TU_CHOI", "THAT_BAI", "DONE"]);

      if (closed.has(data.trang_thai)) {
        const idx = this.trackingList.findIndex(i => Number(i.id) === requestId);
        if (idx !== -1) this.trackingList.splice(idx, 1);
        if (this.selectedId === requestId) this.clearSelection();
        return;
      }

      const idx = this.trackingList.findIndex(i => Number(i.id) === requestId);
      if (idx !== -1) {
        const prevStatus = this.prevStatusMap[requestId];
        const newStatus = data.trang_thai;

        Object.assign(this.trackingList[idx], {
          trang_thai: data.trang_thai,
          trang_thai_nhiem_vu: data.trang_thai_nhiem_vu,
          thoi_gian_cap_nhat: data.updated_at,
        });
        this.prevStatusMap[requestId] = newStatus;

        if (prevStatus && prevStatus !== newStatus && requestId === Number(this.selectedId)) {
          this.refreshDetailSilently();
        }
      } else {
        this.trackingList.unshift({
          id: requestId,
          trang_thai: data.trang_thai,
          trang_thai_nhiem_vu: data.trang_thai_nhiem_vu,
          thoi_gian_cap_nhat: data.updated_at,
          loai_su_co: data.loai_su_co,
          vi_tri_dia_chi: data.vi_tri_dia_chi,
          doi_cuu_ho: data.ten_doi ? { ten_co: data.ten_doi } : null,
        });
        this.prevStatusMap[requestId] = data.trang_thai;
      }
    },

    // ─── Helpers ─────────────────────────────────────────────────────────────────
    getSeverityBadge(sev) { return getSeverityInfo(sev).badge; },
    getSeverityLabel(sev) { return getSeverityInfo(sev).label; },
    formatTimeShort,
    statusLabel(status) {
      const map = {
        'CHO_XU_LY': 'Chờ xử lý',
        'DA_PHAN_CONG': 'Đã phân công',
        'DANG_XU_LY': 'Đang xử lý',
        'DA_DEN_HIEN_TRUONG': 'Đã tới hiện trường',
        'HOAN_THANH': 'Hoàn thành',
        'HUY_BO': 'Đã huỷ',
        'THAT_BAI': 'Thất bại',
      };
      return map[status] || status || 'N/A';
    },
    statusBadgeClass(status) {
      const map = {
        'CHO_XU_LY': 'bg-warning-subtle text-warning border border-warning',
        'DA_PHAN_CONG': 'bg-primary-subtle text-primary border border-primary',
        'DANG_XU_LY': 'bg-info-subtle text-info border border-info',
        'DA_DEN_HIEN_TRUONG': 'bg-success-subtle text-success border border-success',
        'HOAN_THANH': 'bg-success text-white',
        'HUY_BO': 'bg-secondary text-white',
        'THAT_BAI': 'bg-danger text-white',
      };
      return map[status] || 'bg-light text-dark';
    },
    computedTimeline(detail) {
      if (!detail) return [];
      const pc = detail.phan_congs && detail.phan_congs.length > 0 ? detail.phan_congs[0] : null;
      const pcStatus = pc ? pc.trang_thai_nhiem_vu : null;
      const steps = [
        { title: 'Tiếp nhận yêu cầu', time: formatTime(detail.thoi_gian_tao), status: 'done' },
        { title: 'Đã phân công cứu hộ', time: pc ? formatTime(pc.thoi_gian_phan_cong) : '', status: 'done' },
        { title: 'Đang di chuyển', time: pc && pc.thoi_gian_tiep_nhan ? formatTime(pc.thoi_gian_tiep_nhan) : '', status: 'pending' },
        { title: 'Đã tới hiện trường', time: '', status: 'pending' },
        { title: 'Hoàn thành sự cố', time: '', status: 'pending' },
      ];

      if (pcStatus === 'HOAN_THANH') {
        steps[2].status = steps[3].status = steps[4].status = 'done';
        steps[4].time = pc?.ket_qua?.thoi_gian_hoan_thanh ? formatTime(pc.ket_qua.thoi_gian_hoan_thanh) : formatTime(detail.thoi_gian_cap_nhat);
        return steps;
      }
      if (pcStatus === 'DA_DEN_HIEN_TRUONG') {
        steps[2].status = steps[3].status = 'done';
        steps[3].status = 'current';
        return steps;
      }
      if (pcStatus === 'DANG_XU_LY' || detail.trang_thai === 'DANG_XU_LY') {
        steps[2].status = 'current';
        return steps;
      }
      return steps;
    },

    // ─── Data Loading ─────────────────────────────────────────────────────────────
    async loadTrackingList() {
      if (this.loadingList) return;
      this.loadingList = true;
      try {
        const response = await rescueRequestAPI.getTrackingList();
        const rawData = response?.data?.data || response?.data || [];
        const newList = Array.isArray(rawData) ? rawData : [];

        if (this.selectedId && !newList.find(i => Number(i.id) === Number(this.selectedId))) {
          this.clearSelection();
        }

        this.trackingList = newList;
        this.prevStatusMap = {};
        newList.forEach(item => {
          this.prevStatusMap[Number(item.id)] = item.trang_thai;
        });
      } catch (error) {
        console.error('Lỗi tải danh sách theo dõi:', error);
      } finally {
        this.loadingList = false;
      }
    },

    async selectRequest(item) {
      this.selectedId = item.id;
      this.trackingDetail = null;
      this.loadingDetail = true;
      try {
        const response = await rescueRequestAPI.getTrackingDetail(item.id);
        const detail = response?.data?.data || response?.data;
        if (detail && detail.trang_thai === 'HOAN_THANH') {
          this.clearSelection();
          return;
        }
        this.trackingDetail = detail;
        const idx = this.trackingList.findIndex(i => Number(i.id) === Number(item.id));
        if (idx !== -1) {
          this.trackingList[idx] = { ...this.trackingList[idx], ...detail };
          this.prevStatusMap[Number(item.id)] = detail.trang_thai;
        }
      } catch (error) {
        console.error('Lỗi tải chi tiết:', error);
      } finally {
        this.loadingDetail = false;
      }
    },

    async refreshDetailSilently() {
      if (!this.selectedId) return;
      this.isDetailRefreshing = true;
      try {
        const response = await rescueRequestAPI.getTrackingDetail(this.selectedId);
        const detail = response?.data?.data || response?.data;
        this.trackingDetail = detail;
        if (detail && detail.trang_thai === 'HOAN_THANH') {
          const idx = this.trackingList.findIndex(i => Number(i.id) === Number(this.selectedId));
          if (idx !== -1) this.trackingList.splice(idx, 1);
          this.clearSelection();
        }
      } catch { /* silent */ } finally {
        this.isDetailRefreshing = false;
      }
    },

    clearSelection() {
      this.selectedId = null;
      this.trackingDetail = null;
    },

    // ─── Actions ──────────────────────────────────────────────────────────────────
    callTeam(phone) {
      if (!phone) return;
      window.open(`tel:${phone}`);
    },
    contactClient(client) {
      if (!client?.so_dien_thoai) return;
      window.open(`tel:${client.so_dien_thoai}`);
    },
    contactTeam(phanCongs) {
      if (!phanCongs || phanCongs.length === 0) return;
      const first = phanCongs[0];
      if (first.sdt_hotline) window.open(`tel:${first.sdt_hotline}`);
    },
    async markComplete() {
      if (!this.selectedId) return;
      if (!confirm(`Xác nhận hoàn thành yêu cầu #${this.selectedId}?`)) return;
      this.completing = true;
      try {
        await rescueRequestAPI.changeStatus(this.selectedId, { trang_thai: 'HOAN_THANH' });
        this.clearSelection();
        await this.loadTrackingList();
      } catch (error) {
        console.error('Lỗi đánh dấu hoàn thành:', error);
      } finally {
        this.completing = false;
      }
    },
    cancelTask() {
      if (!this.selectedId) return;
      if (!confirm("Huỷ nhiệm vụ này? Thao tác không thể hoàn tác.")) return;
      alert("Chức năng đang phát triển.");
    },

    // ─── Rating Modal ────────────────────────────────────────────────────────────
    openRatingModal(item) {
      this.ratingItem = item;
      this.ratingData.selectedRating = 0;
      this.ratingData.hoveredStar = 0;
      this.ratingData.selectedTags = [];
      this.ratingData.comment = "";
      this.ratingData.submitting = false;
      this.isRatingModalOpen = true;
      document.body.style.overflow = "hidden";
    },

    closeRatingModal() {
      this.isRatingModalOpen = false;
      this.ratingItem = null;
      document.body.style.overflow = "";
    },

    selectRating(n) {
      this.ratingData.selectedRating = n;
    },

    toggleTag(label) {
      const idx = this.ratingData.selectedTags.indexOf(label);
      if (idx > -1) {
        this.ratingData.selectedTags.splice(idx, 1);
      } else {
        this.ratingData.selectedTags.push(label);
      }
    },

    async submitRating() {
      if (this.ratingData.selectedRating === 0 || this.ratingData.submitting) return;
      this.ratingData.submitting = true;
      try {
        await rescueRequestAPI.submitRating(this.ratingItem.id, {
          diem_danh_gia: this.ratingData.selectedRating,
          noi_dung: this.ratingData.comment,
          tags: this.ratingData.selectedTags.join(", "),
        });
        this.closeRatingModal();
        if (this.$toast?.success) {
          this.$toast.success("Cảm ơn bạn! Đánh giá đã được gửi.", { position: "top-right", duration: 3500 });
        } else {
          alert("Cảm ơn bạn! Đánh giá đã được gửi.");
        }
      } catch (error) {
        console.error("Lỗi gửi đánh giá:", error);
        if (this.$toast?.error) {
          this.$toast.error(error?.response?.data?.message || "Gửi đánh giá thất bại. Vui lòng thử lại.", { position: "top-right", duration: 3500 });
        } else {
          alert("Gửi đánh giá thất bại. Vui lòng thử lại.");
        }
      } finally {
        this.ratingData.submitting = false;
      }
    },
  },
};
</script>

<style scoped>
.tracking-dashboard {
  background-color: #f8f9fa;
  min-height: calc(100vh - 60px);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.text-primary { color: #2563eb !important; }
.bg-primary-subtle { background-color: #eff6ff !important; }
.border-primary-subtle { border-color: #bfdbfe !important; }
.bg-danger-subtle { background-color: #fef2f2 !important; }
.bg-success-subtle { background-color: #f0fdf4 !important; }
.bg-info-subtle { background-color: #f0f9ff !important; }
.border-danger-subtle { border-color: #fecaca !important; }
.bg-warning-subtle { background-color: #fffbeb !important; }
.border-warning { border-color: #f59e0b !important; }
.border-info { border-color: #0ea5e9 !important; }

.custom-card {
  border-radius: 1rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.custom-card:hover {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
}

.btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  transition: all 0.2s;
}
.btn-icon:hover { transform: scale(1.1); }
.btn-hover-rise {
  transition: all 0.3s ease;
}
.btn-hover-rise:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.bg-gradient-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.tracking-timeline {
  padding-left: 20px;
}
.timeline-line {
  position: absolute;
  left: 6px;
  top: 10px;
  bottom: 20px;
  width: 2px;
  background-color: #e5e7eb;
  z-index: 1;
}
.timeline-step {
  position: relative;
  margin-bottom: 1.5rem;
  padding-left: 1.5rem;
  z-index: 2;
}
.timeline-step:last-child { margin-bottom: 0; }
.step-indicator {
  position: absolute;
  left: -20px;
  top: 0;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: #e5e7eb;
  border: 4px solid #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 10px;
  transition: all 0.3s ease;
}
.timeline-step.done .step-indicator { background-color: #22c55e; }
.timeline-step.current .step-indicator {
  background-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}
.step-content h6 {
  font-size: 0.95rem;
  color: #4b5563;
}
.timeline-step.done .step-content h6 { color: #111827; }
.timeline-step.current .step-content h6 { color: #2563eb; }

@keyframes pulse-dot {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(59, 130, 246, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
}
.pulse-dot {
  width: 8px;
  height: 8px;
  background-color: white;
  border-radius: 50%;
  animation: pulse-dot 2s infinite;
}

.pulse-icon {
  animation: pulse-icon 2s infinite alternate;
}
@keyframes pulse-icon {
  0% { filter: drop-shadow(0 0 2px rgba(37, 99, 235, 0.4)); transform: scale(1); }
  100% { filter: drop-shadow(0 0 8px rgba(37, 99, 235, 0.8)); transform: scale(1.05); }
}

/* Live badge */
.live-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 1px;
  color: #ef4444;
  background: #fef2f2;
  border: 1px solid #fecaca;
  padding: 4px 10px;
  border-radius: 20px;
}
.live-dot {
  width: 8px;
  height: 8px;
  background-color: #ef4444;
  border-radius: 50%;
  animation: live-pulse 1.5s ease-in-out infinite;
}
@keyframes live-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.7); }
}

/* Detail refresh flash */
@keyframes refresh-flash {
  0% { background-color: transparent; }
  30% { background-color: rgba(37, 99, 235, 0.06); }
  100% { background-color: transparent; }
}
.detail-refreshing {
  animation: refresh-flash 0.6s ease-out;
}

/* Request list */
.request-list-item {
  cursor: pointer;
  transition: background-color 0.15s ease;
}
.request-list-item:hover { background-color: #f8fafc; }
.request-list-item.active {
  background-color: #eff6ff;
  border-left: 4px solid #2563eb;
  padding-left: calc(1rem - 4px);
}

/* Severity badges */
.level-badge {
  padding: 3px 8px;
  border-radius: 6px;
  font-weight: 800;
  font-size: 10px;
  letter-spacing: 0.5px;
}
.lv-critical { background: #7f1d1d; color: white; }
.lv-high { background: #dc2626; color: white; }
.lv-medium { background: #f59e0b; color: white; }
.lv-low { background: #16a34a; color: white; }

/* Spinner */
.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
.spinner {
  width: 44px;
  height: 44px;
  border: 3px solid #e0e7ff;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Scrollbars */
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* ===== RATING MODAL ===== */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.25s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-from .rating-modal, .modal-fade-leave-to .rating-modal { transform: scale(0.95) translateY(8px); }
.label-fade-enter-active, .label-fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.label-fade-enter-from, .label-fade-leave-to { opacity: 0; transform: translateY(-4px); }

.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999; padding: 1rem;
}

.rating-modal {
  background: #ffffff; border-radius: 20px;
  width: 100%; max-width: 520px; max-height: 90vh;
  overflow-y: auto; box-shadow: 0 24px 64px rgba(0,0,0,0.18);
  overscroll-behavior: contain;
}

.rating-header {
  display: flex; align-items: center; gap: 12px;
  padding: 22px 22px 18px; border-bottom: 1px solid #f1f5f9;
}

.rating-header-icon {
  width: 44px; height: 44px; min-width: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0369a1, #0ea5e9);
  display: flex; align-items: center; justify-content: center;
  color: #ffffff; box-shadow: 0 4px 14px rgba(3,105,161,0.35);
  flex-shrink: 0;
}

.rating-title {
  font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0;
}

.rating-subtitle {
  font-size: 0.78rem; color: #64748b; margin: 2px 0 0;
}

.rating-close-btn {
  margin-left: auto; background: #f1f5f9; border: none;
  border-radius: 10px; width: 34px; height: 34px;
  display: flex; align-items: center; justify-content: center;
  color: #64748b; cursor: pointer; transition: all 0.2s ease; flex-shrink: 0;
}

.rating-close-btn:hover { background: #e2e8f0; color: #0f172a; }
.rating-close-btn:focus-visible { outline: 2px solid #0369a1; outline-offset: 2px; }

.rating-stars-section, .rating-tags-section, .rating-comment-section {
  padding: 18px 22px 0;
}

.rating-label {
  font-size: 0.78rem; font-weight: 600; color: #475569;
  text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 10px;
}

.optional-label { font-weight: 400; text-transform: none; letter-spacing: 0; color: #94a3b8; }

.stars-row { display: flex; align-items: center; gap: 4px; margin-bottom: 8px; }

.star-btn {
  background: none; border: none; padding: 4px;
  cursor: pointer; color: #e2e8f0;
  transition: all 0.15s ease; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
}

.star-btn:hover, .star-btn.active { color: #f59e0b; transform: scale(1.12); }
.star-btn.selected { color: #f59e0b; }
.star-btn:focus-visible { outline: 2px solid #0369a1; outline-offset: 2px; }
.star-btn:active { transform: scale(0.9); }

.rating-feedback-text { font-size: 0.875rem; font-weight: 600; margin: 0 0 2px; }
.rating-1 { color: #dc2626; }
.rating-2 { color: #f97316; }
.rating-3 { color: #eab308; }
.rating-4 { color: #22c55e; }
.rating-5 { color: #10b981; }

.tags-grid { display: flex; flex-wrap: wrap; gap: 7px; }

.tag-chip {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 5px 11px; border-radius: 20px;
  border: 1.5px solid #e2e8f0; background: #f8fafc;
  font-size: 0.78rem; font-weight: 500; color: #475569;
  cursor: pointer; transition: all 0.15s ease;
}

.tag-chip:hover { border-color: #0369a1; background: #eff6ff; color: #0369a1; }
.tag-chip.selected { border-color: #0369a1; background: #0369a1; color: #ffffff; }
.tag-chip:focus-visible { outline: 2px solid #0369a1; outline-offset: 2px; }

.rating-textarea {
  width: 100%; border: 1.5px solid #e2e8f0;
  border-radius: 12px; padding: 10px 12px;
  font-size: 0.875rem; font-family: inherit; color: #0f172a;
  resize: vertical; min-height: 76px; background: #f8fafc;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  box-sizing: border-box;
}

.rating-textarea::placeholder { color: #94a3b8; }
.rating-textarea:focus { outline: none; border-color: #0369a1; box-shadow: 0 0 0 3px rgba(3,105,161,0.1); background: #fff; }
.rating-textarea:disabled { opacity: 0.6; cursor: not-allowed; }

.char-counter { text-align: right; font-size: 0.73rem; color: #94a3b8; margin-top: 3px; transition: color 0.2s ease; }
.char-counter.warning { color: #f59e0b; }

.rating-actions { display: flex; align-items: center; gap: 10px; padding: 18px 22px 22px; }

.btn-cancel {
  flex: 0 0 auto; padding: 9px 18px; border-radius: 12px;
  border: 1.5px solid #e2e8f0; background: #fff;
  font-size: 0.875rem; font-weight: 600; color: #475569;
  cursor: pointer; transition: all 0.2s ease;
}

.btn-cancel:hover:not(:disabled) { border-color: #94a3b8; color: #0f172a; background: #f8fafc; }
.btn-cancel:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-submit {
  flex: 1; padding: 10px 18px; border-radius: 12px; border: none;
  background: linear-gradient(135deg, #0369a1, #0ea5e9);
  color: #fff; font-size: 0.875rem; font-weight: 700;
  cursor: pointer; transition: all 0.2s ease;
  display: flex; align-items: center; justify-content: center; gap: 6px;
  box-shadow: 0 4px 14px rgba(3,105,161,0.3);
}

.btn-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(3,105,161,0.4); }
.btn-submit:active:not(:disabled) { transform: translateY(0); }
.btn-submit:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }

.btn-spinner {
  width: 17px; height: 17px;
  border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
  border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block;
}

@keyframes spin { to { transform: rotate(360deg); } }

.rating-modal::-webkit-scrollbar { width: 4px; }
.rating-modal::-webkit-scrollbar-track { background: transparent; }
.rating-modal::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

@media (max-width: 480px) {
  .rating-modal { max-height: 85vh; border-radius: 20px 20px 0 0; position: fixed; bottom: 0; left: 0; right: 0; max-width: 100%; }
  .modal-overlay { align-items: flex-end; padding: 0; }
  .rating-header, .rating-stars-section, .rating-tags-section, .rating-comment-section, .rating-actions { padding-left: 16px; padding-right: 16px; }
}
</style>
