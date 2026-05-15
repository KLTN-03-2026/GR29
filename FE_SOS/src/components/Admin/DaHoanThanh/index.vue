<template>
  <div class="admin-da-hoan-thanh-wrapper py-4 px-3 px-md-4">
    <div class="header-section d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div class="title-wrapper d-flex align-items-start gap-3">
        <div class="icon-box">
          <i class="bi bi-check-circle-fill text-white fs-4"></i>
        </div>
        <div>
          <h4 class="mb-1 fw-bolder page-title">Đã Hoàn Thành</h4>
          <p class="text-muted mb-0 page-subtitle">Danh sách các yêu cầu cứu hộ đã được xử lý xong</p>
        </div>
      </div>
      <button class="btn btn-refresh d-flex align-items-center gap-2 align-self-md-auto align-self-start" @click="loadRequests" :disabled="loading">
        <i class="bi bi-arrow-clockwise" :class="{ 'spin-animation': loading }"></i>
        <span>Làm mới dữ liệu</span>
      </button>
    </div>

    <!-- Search / Filter Functionality -->
    <form @submit.prevent="onTimKiem" class="search-filter-section bg-white p-3 rounded-4 shadow-sm border border-light mb-5">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label text-muted small fw-bold mb-1">Tìm kiếm chung</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input v-model="searchQuery" type="text" class="form-control border-start-0 ps-0 bg-light" placeholder="Mã sự cố, địa chỉ...">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label text-muted small fw-bold mb-1">Loại sự cố</label>
          <select v-model="searchType" class="form-select bg-light">
            <option value="">Tất cả loại sự cố</option>
            <option v-for="type in incidentTypes" :key="type.id" :value="type.id">{{ type.ten_danh_muc || type.ten_loai }}</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label text-muted small fw-bold mb-1">Thời gian</label>
          <input v-model="searchDate" type="date" class="form-control bg-light">
        </div>
        <div class="col-md-2 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary w-100 action-btn hover-elevate border-0">
            <i class="bi bi-funnel-fill me-1"></i> Lọc
          </button>
          <button type="button" class="btn btn-outline-secondary px-3" title="Xóa bộ lọc" @click="resetFilters">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </div>
    </form>

    <!-- Alerts and Loading States -->
    <div v-if="error" class="alert custom-alert-danger mb-4 shadow-sm border-0 d-flex align-items-center gap-3 rounded-3">
      <i class="bi bi-exclamation-triangle-fill fs-5"></i>
      <div>{{ error }}</div>
    </div>

    <div v-if="loading" class="loading-state py-5 text-center d-flex flex-column align-items-center gap-3">
      <div class="spinner"></div>
      <div class="text-muted fw-medium fs-5">Đang đồng bộ dữ liệu...</div>
    </div>

    <div v-if="!loading && filteredRequests.length === 0" class="empty-state py-5 my-4 text-center d-flex flex-column align-items-center justify-content-center">
      <div class="empty-icon text-muted mb-3">
        <i class="bi bi-shield-check" style="font-size: 4rem; opacity: 0.3;"></i>
      </div>
      <h5 class="fw-bold text-dark mb-1">Không có dữ liệu</h5>
      <p class="text-muted mb-0">Hiện tại chưa có yêu cầu cứu hộ nào được hoàn thành.</p>
    </div>

    <!-- Requests List -->
    <div class="row g-4">
      <div v-for="request in filteredRequests" :key="request.key" class="col-lg-4 col-md-6">
        <div class="incident-card card h-100 border-0 shadow-sm">
          <div class="row g-0 h-100 flex-column flex-sm-row">
            <!-- Image Area -->
            <div class="col-sm-5 col-md-4 image-container position-relative bg-light">
              <img v-if="request.imageUrl" :src="request.imageUrl" class="incident-img w-100 h-100 position-absolute" style="top:0; left:0; object-fit: cover;" alt="Hình ảnh sự cố" />
              <div v-else class="empty-image h-100 w-100 position-absolute d-flex flex-column align-items-center justify-content-center text-muted p-4" style="top:0; left:0;">
                <i class="bi bi-images fs-1 mb-2 text-secondary opacity-25"></i>
                <span class="small fw-medium opacity-50">Chưa có hình ảnh</span>
              </div>
              <!-- Floating Priority Badge -->
              <div class="position-absolute top-0 start-0 m-3 z-1">
                <span class="badge priority-badge shadow-sm" :class="{'bg-danger text-white': request.priorityLabel?.toLowerCase().includes('khẩn') || request.priorityLabel?.toLowerCase().includes('cao'), 'bg-warning text-dark': !request.priorityLabel?.toLowerCase().includes('khẩn') && !request.priorityLabel?.toLowerCase().includes('cao')}">
                  <i class="bi bi-shield-exclamation me-1"></i> {{ request.priorityLabel || 'Mức độ ưu tiên' }}
                </span>
              </div>
            </div>

            <!-- Content Area -->
            <div class="col-sm-7 col-md-8 card-right-body d-flex flex-column">
              <div class="card-body p-4 p-md-4 d-flex flex-column h-100">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-3 gap-2 flex-wrap">
                  <div class="id-badge bg-secondary bg-opacity-10 text-dark fw-bold px-3 py-1 rounded-pill d-flex align-items-center gap-1">
                    <i class="bi bi-hash text-primary"></i>SOS-{{ request.id }}
                  </div>
                  <span class="status-badge rounded-pill px-3 py-1 fw-bold border border-1 border-opacity-10" :class="request.statusClass">
                    <span class="status-dot flex-shrink-0 me-2" :class="request.statusClass.includes('text-dark') ? 'bg-dark' : 'bg-white'"></span>
                    <span>{{ request.statusLabel }}</span>
                  </span>
                </div>

                <!-- Title & Time -->
                <h4 class="incident-title fw-bolder mb-2 text-dark">{{ request.type }}</h4>
                <div class="time-stamp d-flex align-items-center text-muted small mb-4">
                  <i class="bi bi-clock-history me-2 text-primary opacity-75"></i>
                  <span class="fw-medium">{{ request.time }}</span>
                </div>

                <!-- Details -->
                <div class="info-list d-flex flex-column gap-3 mb-4 flex-grow-1">
                  <div class="info-item d-flex gap-3 align-items-start">
                    <div class="info-icon bg-blue-light text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                      <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="info-content">
                      <div class="info-label text-muted small fw-bold text-uppercase mb-1">Cần giúp đỡ về : </div>
                      <div class="info-value text-dark fw-semibold">{{ request.chiTiet || 'Không có chi tiết' }}</div>
                    </div>
                  </div>

                  <div class="info-item d-flex gap-3 align-items-start">
                    <div class="info-icon bg-red-light text-danger rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                      <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="info-content w-100">
                      <div class="info-label text-muted small fw-bold text-uppercase mb-1">Vị trí sự cố</div>
                      <div class="info-value text-dark fw-semibold text-truncate-2" :title="request.address">{{ request.address }}</div>
                    </div>
                  </div>

                  <div class="info-item d-flex gap-3 align-items-start" v-if="request.description">
                    <div class="info-icon bg-yellow-light text-warning rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                      <i class="bi bi-card-text"></i>
                    </div>
                    <div class="info-content w-100">
                      <div class="info-label text-muted small fw-bold text-uppercase mb-1">Mô tả tóm tắt</div>
                      <div class="info-value text-muted text-truncate-2" :title="request.description">{{ request.description }}</div>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="mt-auto action-footer border-top pt-4">
                  <button
                    class="btn action-btn w-100 d-flex align-items-center justify-content-center gap-2 fw-bold"
                    :class="request.buttonClass"
                    @click="viewDetail(request)"
                    :disabled="request.loading"
                  >
                    <i class="bi bi-eye-fill fs-5" v-if="!request.loading"></i>
                    <span class="spinner-border spinner-border-sm" v-else role="status" aria-hidden="true"></span>
                    <span>Xem chi tiết</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="adminDetailModal" tabindex="-1" ref="detailModalEl">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <!-- Premium Header with Gradient -->
          <div class="modal-header-custom">
            <div class="d-flex align-items-center gap-3">
              <div class="modal-header-icon">
                <i class="bi bi-shield-check"></i>
              </div>
              <div>
                <h5 class="fw-bolder text-white mb-0">Chi Tiết Yêu Cầu Cứu Hộ</h5>
                <div class="text-white-50 small" v-if="detailItem">SOS-{{ detailItem.id }}</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span v-if="detailItem" class="badge rounded-pill px-3 py-2 fw-bold modal-status-badge" :class="detailItem.status === 'THAT_BAI' ? 'bg-danger' : 'bg-white text-success'">
                <i class="bi me-1" :class="detailItem.status === 'THAT_BAI' ? 'bi-x-circle-fill' : 'bi-check-circle-fill'"></i>{{ detailItem.statusLabel }}
              </span>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
          </div>

          <div class="modal-body p-0" v-if="detailItem">
            <!-- Loading State -->
            <div v-if="detailLoading" class="text-center py-5">
              <div class="spinner-border text-primary"></div>
              <p class="text-muted mt-2 mb-0">Đang tải thông tin chi tiết...</p>
            </div>

            <div v-else>
              <!-- Top Info Banner -->
              <div class="modal-info-banner px-4 py-3 d-flex flex-wrap gap-4 align-items-center">
                <div class="info-chip">
                  <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                  <span class="fw-bold">{{ detailItem.type }}</span>
                </div>
                <div class="info-chip">
                  <i class="bi bi-speedometer2 text-danger me-1"></i>
                  <span>{{ detailItem.priorityLabel }}</span>
                </div>
                <div class="info-chip">
                  <i class="bi bi-clock-history text-primary me-1"></i>
                  <span>{{ detailItem.time }}</span>
                </div>
              </div>

              <div class="detail-layout px-4 py-4">
                <!-- LEFT COLUMN -->
                <div class="detail-left">
                  <!-- Người gặp nạn section -->
                  <div class="detail-card mb-4">
                    <div class="detail-card-header">
                      <i class="bi bi-person-exclamation me-2"></i>THÔNG TIN NGƯỜI GẶP NẠN
                    </div>
                    <div class="detail-card-body">
                      <div class="info-row">
                        <div class="info-row-icon bg-primary bg-opacity-10"><i class="bi bi-person-fill text-primary"></i></div>
                        <div><div class="info-row-label">Họ tên</div><div class="info-row-value">{{ getReporterName(detailItem.raw) }}</div></div>
                      </div>
                      <div class="info-row">
                        <div class="info-row-icon bg-success bg-opacity-10"><i class="bi bi-telephone-fill text-success"></i></div>
                        <div>
                          <div class="info-row-label">Số điện thoại</div>
                          <div class="info-row-value">{{ getReporterPhone(detailItem.raw) || 'Chưa cập nhật' }}</div>
                        </div>
                      </div>
                      <div class="info-row">
                        <div class="info-row-icon bg-danger bg-opacity-10"><i class="bi bi-geo-alt-fill text-danger"></i></div>
                        <div><div class="info-row-label">Địa điểm</div><div class="info-row-value">{{ detailItem.address }}</div></div>
                      </div>
                    </div>
                  </div>

                  <!-- Chi tiết sự cố -->
                  <div class="detail-card mb-4">
                    <div class="detail-card-header">
                      <i class="bi bi-file-earmark-text me-2"></i>CHI TIẾT SỰ CỐ
                    </div>
                    <div class="detail-card-body">
                      <div class="info-row">
                        <div class="info-row-icon bg-info bg-opacity-10"><i class="bi bi-question-circle-fill text-info"></i></div>
                        <div><div class="info-row-label">Cần giúp đỡ về</div><div class="info-row-value">{{ detailItem.chiTiet || 'Không có chi tiết' }}</div></div>
                      </div>
                      <div class="info-row" v-if="detailItem.description && detailItem.description !== 'Không có mô tả'">
                        <div class="info-row-icon bg-secondary bg-opacity-10"><i class="bi bi-card-text text-secondary"></i></div>
                        <div><div class="info-row-label">Mô tả thêm</div><div class="info-row-value text-muted">{{ detailItem.description }}</div></div>
                      </div>
                    </div>
                  </div>

                  <!-- Đội cứu hộ / Người nhận nhiệm vụ -->
                  <div class="detail-card" v-if="detailAssignment">
                    <div class="detail-card-header text-success">
                      <i class="bi bi-people-fill me-2"></i>NGƯỜI NHẬN NHIỆM VỤ CỨU HỘ
                    </div>
                    <div class="detail-card-body">
                      <div class="rescuer-profile d-flex align-items-center gap-3 mb-3 p-3 rounded-3" v-if="detailAssignment.doi_cuu_ho">
                        <div class="rescuer-avatar">
                          <i class="bi bi-shield-fill-check"></i>
                        </div>
                        <div>
                          <div class="fw-bold text-dark fs-6">{{ detailAssignment.doi_cuu_ho.ten_doi || detailAssignment.doi_cuu_ho.ten_co || '-' }}</div>
                          <div class="text-muted small" v-if="detailAssignment.doi_cuu_ho.so_dien_thoai">
                            <i class="bi bi-telephone me-1"></i>{{ detailAssignment.doi_cuu_ho.so_dien_thoai }}
                          </div>
                          <div class="text-muted small" v-if="detailAssignment.doi_cuu_ho.khu_vuc">
                            <i class="bi bi-pin-map me-1"></i>{{ detailAssignment.doi_cuu_ho.khu_vuc }}
                          </div>
                        </div>
                      </div>
                      <!-- Thành viên tiếp nhận -->
                      <div v-if="detailAssignment.thanh_vien_doi" class="info-row">
                        <div class="info-row-icon bg-warning bg-opacity-10"><i class="bi bi-person-badge-fill text-warning"></i></div>
                        <div>
                          <div class="info-row-label">Thành viên tiếp nhận</div>
                          <div class="info-row-value">{{ detailAssignment.thanh_vien_doi.ho_ten || detailAssignment.thanh_vien_doi.ten || '-' }}</div>
                        </div>
                      </div>
                      <div class="info-row">
                        <div class="info-row-icon bg-primary bg-opacity-10"><i class="bi bi-calendar-check text-primary"></i></div>
                        <div>
                          <div class="info-row-label">Thời gian phân công</div>
                          <div class="info-row-value">{{ formatAssignmentTime(detailAssignment.created_at) }}</div>
                        </div>
                      </div>
                      <div class="info-row" v-if="detailAssignment.updated_at">
                        <div class="info-row-icon bg-success bg-opacity-10"><i class="bi bi-check2-circle text-success"></i></div>
                        <div>
                          <div class="info-row-label">Thời gian hoàn thành</div>
                          <div class="info-row-value">{{ formatAssignmentTime(detailAssignment.updated_at) }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- RIGHT COLUMN: Report Info -->
                <div class="detail-right">
                  <div v-if="detailAssignment && detailAssignment.bao_cao">
                    <div class="detail-card mb-4">
                      <div class="detail-card-header text-primary">
                        <i class="bi bi-clipboard-data-fill me-2"></i>BÁO CÁO HIỆN TRƯỜNG
                      </div>
                      <div class="detail-card-body">
                        <div v-if="detailAssignment.bao_cao.mo_ta_hien_truong" class="mb-3">
                          <div class="detail-label">MÔ TẢ HIỆN TRƯỜNG</div>
                          <div class="report-text-box">{{ detailAssignment.bao_cao.mo_ta_hien_truong }}</div>
                        </div>
                        <div v-if="detailAssignment.trang_thai_nhiem_vu === 'THAT_BAI' && detailAssignment.bao_cao.ly_do_that_bai" class="mb-3">
                          <div class="failure-title mb-2"><i class="bi bi-exclamation-circle-fill me-1"></i>LÝ DO THẤT BẠI</div>
                          <div class="failure-box">{{ detailAssignment.bao_cao.ly_do_that_bai }}</div>
                        </div>
                        <div>
                          <div class="detail-label mb-2">HÌNH ẢNH MINH CHỨNG</div>
                          <div v-if="detailAssignment.bao_cao.hinh_anh" class="evidence-image-wrapper">
                            <img :src="getFullImageUrl(detailAssignment.bao_cao.hinh_anh)" class="evidence-image" @click="openImageModal(detailAssignment.bao_cao.hinh_anh)">
                            <div class="evidence-overlay"><i class="bi bi-zoom-in fs-4"></i></div>
                          </div>
                          <div v-else class="no-image-box">
                            <i class="bi bi-image text-muted opacity-25 fs-3"></i>
                            <span class="text-muted small">Không có hình ảnh</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else-if="!detailLoading" class="no-report-box">
                    <i class="bi bi-clipboard text-muted opacity-25 fs-1 mb-2"></i>
                    <p class="text-muted small mb-0">Chưa có báo cáo hiện trường</p>
                  </div>

                  <!-- Resources Used -->
                  <div v-if="detailAssignment && detailAssignment.tai_nguyen_dang_su_dung && detailAssignment.tai_nguyen_dang_su_dung.length > 0">
                    <div class="detail-card">
                      <div class="detail-card-header text-warning">
                        <i class="bi bi-box-seam-fill me-2"></i>TÀI NGUYÊN ĐÃ SỬ DỤNG
                        <span class="badge bg-warning text-dark rounded-pill ms-2 small">{{ detailAssignment.tai_nguyen_dang_su_dung.length }}</span>
                      </div>
                      <div class="detail-card-body p-0">
                        <div v-for="(res, idx) in detailAssignment.tai_nguyen_dang_su_dung" :key="res.id_tai_nguyen" class="resource-row d-flex align-items-center justify-content-between px-3 py-2" :class="{ 'border-top': idx > 0 }">
                          <div class="d-flex align-items-center gap-2">
                            <div class="resource-dot"></div>
                            <span class="fw-semibold text-dark small">{{ res.ten_tai_nguyen || res.slug_tai_nguyen }}</span>
                          </div>
                          <span class="badge bg-dark bg-opacity-75 rounded-pill">{{ res.so_luong_dang_su_dung }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer border-0 px-4 py-3">
            <button type="button" class="btn btn-outline-secondary fw-bold rounded-3 px-4" data-bs-dismiss="modal">
              <i class="bi bi-x-lg me-1"></i>Đóng
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="adminImageModal" tabindex="-1" ref="imageModalEl">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
          <div class="modal-header">
            <h5 class="modal-title fw-bold"><i class="bi bi-image me-2"></i>Hình Ảnh Minh Chứng</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center p-0">
            <img v-if="modalImageUrl" :src="modalImageUrl" style="max-width: 100%; border-radius: 8px;">
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescueRequestAPI, incidentTypeAPI, assignmentAPI } from "../../../services/api";

const BASE_URL = 'http://localhost:8000';

const STATUS_META = {
  CHO_XU_LY: { label: "Chờ xử lý", badge: "bg-info text-dark", button: "btn-outline-info", buttonLabel: "Chờ xử lý" },
  DANG_XU_LY: { label: "Đang xử lý", badge: "bg-warning text-dark", button: "btn-outline-warning", buttonLabel: "Đang xử lý" },
  HOAN_THANH: { label: "Hoàn thành", badge: "bg-success text-white", button: "btn-outline-success", buttonLabel: "Hoàn thành" },
  HUY_BO: { label: "Đã huỷ", badge: "bg-danger text-white", button: "btn-outline-danger", buttonLabel: "Đã huỷ" },
  THAT_BAI: { label: "Thất bại", badge: "bg-danger text-white", button: "btn-outline-danger", buttonLabel: "Thất bại" },
  DONE: { label: "Hoàn thành", badge: "bg-success text-white", button: "btn-outline-success", buttonLabel: "Hoàn thành" },
  PROCESSING: { label: "Đang xử lý", badge: "bg-warning text-dark", button: "btn-outline-warning", buttonLabel: "Đang xử lý" },
  WAITING: { label: "Chờ xử lý", badge: "bg-info text-dark", button: "btn-outline-info", buttonLabel: "Chờ xử lý" },
};

function normalizeText(value, fallback = "") {
  if (value === null || value === undefined) return fallback;
  if (typeof value === "object") {
    return normalizeText(value.ten_danh_muc || value.ten_loai_su_co || value.title || value.name || fallback, fallback);
  }
  return String(value).trim();
}

function formatTime(value) {
  if (!value) return "Không xác định";
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return normalizeText(value, "Không xác định");
  return parsed.toLocaleString("vi-VN", {
    hour: "2-digit",
    minute: "2-digit",
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

function normalizeStatus(status) {
  if (!status) return "CHO_XU_LY";
  return normalizeText(status, "CHO_XU_LY").toUpperCase().replace(/\s+/g, "_");
}

function getStatusMeta(status) {
  const key = normalizeStatus(status);
  return STATUS_META[key] || { label: normalizeText(status, "Không rõ"), badge: "bg-secondary text-white", button: "btn-outline-secondary", buttonLabel: normalizeText(status, "Không rõ") };
}

function getImageUrl(image) {
  const raw = normalizeText(image);
  if (!raw) return null;
  if (/^(https?:|data:)/i.test(raw)) {
    return raw;
  }
  if (raw.startsWith('uploads/') || raw.startsWith('/uploads/')) {
    return BASE_URL + (raw.startsWith('/') ? '' : '/') + raw;
  }
  return null;
}

function parseRequests(payload) {
  const rawData = payload;
  const items = Array.isArray(rawData)
    ? rawData
    : Array.isArray(rawData?.data)
      ? rawData.data
      : Array.isArray(rawData?.data?.data)
        ? rawData.data.data
        : [];

  return items.map((item) => {
    const id = item.id_yeu_cau || item.id || item.request_id || "-";
    const statusMeta = getStatusMeta(item.trang_thai || item.status || item.trang_thai);
    const type = normalizeText(item.loai_su_co?.ten_danh_muc || item.loai_su_co?.ten_loai || item.loai || "Không rõ");
    const chiTiet = normalizeText(item.chi_tiet || item.chiTiet || item.chi_tiet_su_co || "");
    const description = normalizeText(item.mo_ta || item.moTa || "Không có mô tả");
    const address = normalizeText(item.vi_tri_dia_chi || item.dia_chi || "Không có địa chỉ");
    const time = formatTime(item.thoi_gian_gui || item.created_at || item.updated_at || item.thoi_gian || item.time);
    const people = item.so_nguoi_bi_anh_huong || 0;
    const priority = normalizeText(item.muc_do_khan_cap || item.diem_uu_tien || "Không xác định");

    return {
      key: `${id}-${Math.random()}`,
      id,
      type,
      chiTiet,
      description,
      address,
      time,
      people,
      priorityLabel: priority,
      status: normalizeStatus(item.trang_thai || item.status || item.trang_thai),
      statusLabel: statusMeta.label,
      statusClass: statusMeta.badge,
      buttonClass: statusMeta.button,
      buttonLabel: statusMeta.buttonLabel,
      imageUrl: item.hinhAnhUrl || item.imageUrl || getImageUrl(item.hinh_anh),
      raw: item,
      loading: false,
    };
  });
}

export default {
  name: "AdminDaHoanThanh",
  data() {
    return {
      allRequests: [],
      incidentTypes: [],
      searchQuery: "",
      searchType: "",
      searchDate: "",
      loading: false,
      error: "",
      detailItem: null,
      detailAssignment: null,
      detailLoading: false,
      modalImageUrl: "",
    };
  },
  computed: {
    filteredRequests() {
      let result = this.allRequests;
      if (this.searchQuery) {
        const q = this.searchQuery.toLowerCase();
        result = result.filter(r =>
          r.id?.toString().includes(q) ||
          r.address?.toLowerCase().includes(q) ||
          r.description?.toLowerCase().includes(q) ||
          r.type?.toLowerCase().includes(q) ||
          r.chiTiet?.toLowerCase().includes(q)
        );
      }
      if (this.searchType) {
        const st = this.searchType.toString();
        result = result.filter(r => r.raw?.id_loai_su_co?.toString() === st || r.raw?.loai_su_co?.id?.toString() === st);
      }
      if (this.searchDate) {
        const d = this.searchDate;
        result = result.filter(r => {
          const rawDate = r.raw?.thoi_gian_gui || r.raw?.created_at || r.raw?.updated_at || '';
          return rawDate.startsWith(d);
        });
      }
      return result;
    },
  },
  async created() {
    await Promise.all([
      this.loadIncidentTypes(),
      this.loadRequests()
    ]);
  },
  mounted() {
    // Bootstrap modal elements will be accessed via $refs in methods
  },
  methods: {
    hienToast(type, message) {
      if (this.$toast?.[type]) {
        this.$toast[type](message, {
          position: "top-right",
          duration: 3500,
        });
        return;
      }
      alert(message);
    },
    async loadIncidentTypes() {
      try {
        const response = await incidentTypeAPI.getList();
        const data = response?.data?.data || response?.data || [];
        this.incidentTypes = Array.isArray(data) ? data : [];
      } catch (error) {
        console.error("Không tải được danh mục sự cố:", error);
      }
    },
    async loadRequests() {
      this.loading = true;
      this.error = "";
      try {
        const response = await rescueRequestAPI.getList();
        let all = parseRequests(response?.data || response);
        this.allRequests = all.filter(r => r.status === "HOAN_THANH" || r.status === "DONE" || r.status === "THAT_BAI");
      } catch (error) {
        console.error("Không tải được yêu cầu đã hoàn thành:", error);
        this.error = "Không tải được danh sách yêu cầu. Vui lòng thử lại.";
        this.hienToast("error", this.error);
      } finally {
        this.loading = false;
      }
    },
    onTimKiem() {
      // Filters are now reactive via computed, but keep button for UX
      // Could also force refresh from API if needed
    },
    resetFilters() {
      this.searchQuery = "";
      this.searchType = "";
      this.searchDate = "";
    },
    async viewDetail(request) {
      this.detailItem = request;
      this.detailAssignment = null;
      this.detailLoading = true;
      // Wait for DOM to update with detailItem, then show modal
      this.$nextTick(() => {
        const el = this.$refs.detailModalEl;
        const bs = window.bootstrap || (typeof bootstrap !== 'undefined' ? bootstrap : null);
        if (el && bs) {
          const modal = new bs.Modal(el);
          modal.show();
        }
      });
      // Fetch assignment data for this request
      try {
        const res = await assignmentAPI.getByRequest(request.id);
        const assignments = res?.data?.data || res?.data || [];
        const arr = Array.isArray(assignments) ? assignments : (assignments.data || []);
        if (arr.length > 0) {
          this.detailAssignment = arr[0];
        }
      } catch (e) {
        console.error('Không tải được chi tiết phân công:', e);
      } finally {
        this.detailLoading = false;
      }
    },
    openImageModal(imagePath) {
      this.modalImageUrl = this.getFullImageUrl(imagePath);
      this.$nextTick(() => {
        const el = this.$refs.imageModalEl;
        const bs = window.bootstrap || (typeof bootstrap !== 'undefined' ? bootstrap : null);
        if (el && bs) {
          const modal = new bs.Modal(el);
          modal.show();
        }
      });
    },
    getFullImageUrl(path) {
      if (!path) return '';
      if (path.startsWith('http')) return path;
      return BASE_URL + '/' + path;
    },
    getReporterName(raw) {
      if (!raw) return 'Không rõ';
      if (raw.ho_ten_nguoi_dung) return raw.ho_ten_nguoi_dung;
      if (raw.hoTenNguoiDung) return raw.hoTenNguoiDung;
      if (raw.nguoi_dung) return raw.nguoi_dung.ho_ten || raw.nguoi_dung.hoTen || 'Không rõ';
      return 'Không rõ';
    },
    getReporterPhone(raw) {
      if (!raw) return '';
      if (raw.nguoi_dung) return raw.nguoi_dung.so_dien_thoai || '';
      return raw.so_dien_thoai || '';
    },
    formatAssignmentTime(dateStr) {
      if (!dateStr) return '-';
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return dateStr;
      return d.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
    },
  },
};
</script>

<style scoped>
.admin-da-hoan-thanh-wrapper {
  background-color: transparent;
  min-height: 100%;
}

/* Header Styles */
.header-section {
  padding-bottom: 1.5rem;
  border-bottom: 2px dashed #e2e8f0;
}

.icon-box {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px -4px rgba(16, 185, 129, 0.4);
}

.page-title {
  color: #0f172a;
  letter-spacing: -0.5px;
}

.page-subtitle {
  font-size: 0.95rem;
  color: #64748b;
}

.btn-refresh {
  background: white;
  color: #10b981;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.75rem 1.25rem;
  font-weight: 600;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.btn-refresh:hover:not(:disabled) {
  background: #f0fdf4;
  border-color: #6ee7b7;
  color: #059669;
  transform: translateY(-2px);
  box-shadow: 0 6px 10px -2px rgba(0, 0, 0, 0.05);
}

.spin-animation {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}

/* Search Area */
.search-filter-section {
  background-color: #ffffff;
  border-radius: 16px;
  padding: 1.5rem;
}

.search-filter-section .form-control,
.search-filter-section .form-select {
  border-radius: 8px;
  padding: 0.6rem 1rem;
  border-color: #e2e8f0;
  transition: all 0.2s ease;
}

.search-filter-section .form-control:focus,
.search-filter-section .form-select:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

.search-filter-section .input-group-text {
  border-color: #e2e8f0;
  background-color: #f8fafc;
}

/* Card Styles */
.incident-card {
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: white;
  border: 1px solid #f1f5f9 !important;
  overflow: hidden;
}

.incident-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
  border-color: #e2e8f0 !important;
}

/* Image Area */
.image-container {
  min-height: 280px;
}

@media (min-width: 576px) {
  .image-container {
    background: #f8fafc;
    border-right: 1px solid #f1f5f9;
  }
}

.incident-img {
  transition: transform 0.6s ease;
}

.incident-card:hover .incident-img {
  transform: scale(1.08);
}

.priority-badge {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 700;
  letter-spacing: 0.3px;
  backdrop-filter: blur(8px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  font-size: 0.8rem;
}

/* Content Area */
.id-badge {
  font-size: 0.88rem;
  letter-spacing: 0.5px;
  border: 1px dashed #cbd5e1;
}

.status-badge {
  font-size: 0.8rem;
  display: flex;
  align-items: center;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.incident-title {
  color: #0f172a;
  line-height: 1.4;
  font-size: 1.3rem;
  letter-spacing: -0.3px;
}

/* Info List */
.info-icon {
  width: 42px;
  height: 42px;
  min-width: 42px;
  font-size: 1.2rem;
  transition: all 0.2s ease;
}

.incident-card:hover .info-icon {
  transform: scale(1.1);
}

.bg-blue-light { background: #eff6ff; }
.bg-red-light { background: #fef2f2; }
.bg-yellow-light { background: #fffdeb; }
.text-warning { color: #d97706 !important; }

.info-label {
  letter-spacing: 0.8px;
  font-size: 0.72rem;
}

.info-value {
  font-size: 0.95rem;
  line-height: 1.5;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Action Button */
.action-btn {
  border-radius: 12px;
  padding: 0.8rem 1rem;
  transition: all 0.2s ease;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  border-width: 2px;
}

.action-btn.hover-elevate:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3), 0 4px 6px -2px rgba(16, 185, 129, 0.15);
  background-color: #059669;
  border-color: #059669;
  color: white;
}

/* Loading & Empty States */
.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #d1fae5;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.custom-alert-danger {
  background-color: #fef2f2;
  color: #991b1b;
  border-left: 5px solid #ef4444 !important;
}

/* ===== PREMIUM DETAIL MODAL ===== */
.modal-header-custom {
  background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-header-icon {
  width: 44px;
  height: 44px;
  background: rgba(255,255,255,0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.3rem;
  backdrop-filter: blur(4px);
}

.modal-status-badge {
  font-size: 0.8rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.modal-info-banner {
  background: #f0fdf4;
  border-bottom: 1px solid #d1fae5;
}

.info-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: white;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.detail-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 28px;
}

.detail-left {
  border-right: 1px solid #e9ecef;
  padding-right: 28px;
}

.detail-right {
  padding-left: 4px;
}

/* Detail Cards */
.detail-card {
  border: 1px solid #e9ecef;
  border-radius: 12px;
  overflow: hidden;
  background: white;
}

.detail-card-header {
  font-weight: 700;
  font-size: 11px;
  letter-spacing: 0.8px;
  color: #374151;
  background: #f8fafc;
  padding: 10px 16px;
  border-bottom: 1px solid #e9ecef;
}

.detail-card-body {
  padding: 16px;
}

/* Info Rows */
.info-row {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 8px 0;
}

.info-row + .info-row {
  border-top: 1px solid #f1f5f9;
}

.info-row-icon {
  width: 36px;
  height: 36px;
  min-width: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
}

.info-row-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.5px;
  color: #9ca3af;
  margin-bottom: 2px;
  text-transform: uppercase;
}

.info-row-value {
  font-size: 0.92rem;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.4;
}

/* Rescuer Profile */
.rescuer-profile {
  background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
  border: 1px solid #a7f3d0;
}

.rescuer-avatar {
  width: 48px;
  height: 48px;
  min-width: 48px;
  background: linear-gradient(135deg, #059669, #10b981);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.4rem;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
}

.detail-label {
  font-weight: 700;
  font-size: 10px;
  letter-spacing: 0.5px;
  color: #6c757d;
  margin-bottom: 4px;
}

.report-text-box {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid #e9ecef;
  color: #4b5563;
  font-size: 0.88rem;
  line-height: 1.6;
}

.failure-title {
  font-weight: 700;
  font-size: 12px;
  letter-spacing: 0.5px;
  color: #dc3545;
  display: flex;
  align-items: center;
}

.failure-box {
  background: #fff5f5;
  border: 1px solid #fcc2c3;
  border-left: 4px solid #dc3545;
  color: #c92a2a;
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
}

/* Evidence Image with Hover */
.evidence-image-wrapper {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
}

.evidence-image {
  width: 100%;
  max-height: 220px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.evidence-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.evidence-image-wrapper:hover .evidence-image {
  transform: scale(1.05);
}

.evidence-image-wrapper:hover .evidence-overlay {
  opacity: 1;
}

.no-image-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: #f8f9fa;
  border: 2px dashed #dee2e6;
  border-radius: 10px;
  gap: 6px;
}

.no-report-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 16px;
  background: #f8f9fa;
  border: 1px dashed #dee2e6;
  border-radius: 12px;
  text-align: center;
}

/* Resource Row */
.resource-row {
  transition: background 0.15s ease;
}

.resource-row:hover {
  background: #f0fdf4 !important;
}

.resource-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  flex-shrink: 0;
}

@media (max-width: 576px) {
  .detail-layout {
    grid-template-columns: 1fr;
  }
  .detail-left {
    border-right: none;
    padding-right: 0;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 16px;
    margin-bottom: 16px;
  }
  .detail-right {
    padding-left: 0;
  }
  .modal-header-custom {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }
}
</style>
