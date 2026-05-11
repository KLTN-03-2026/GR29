<template>
  <div class="dashboard-container">
    <div class="container-fluid py-4 h-100">
      <!-- Header -->
      <div class="row align-items-end mb-4">
        <div class="col-xl-6 mb-3 mb-xl-0">
          <h2 class="page-title text-dark fw-bolder mb-1">Điều Phối Cứu Hộ</h2>
          <p class="text-muted mb-0 fs-6">Hệ thống phân công và giám sát lực lượng hiện trường</p>
        </div>
        <div class="col-xl-6">
          <div class="d-flex justify-content-xl-end gap-3 stats-wrapper">
            <div class="stat-card shadow-sm border-0 cursor-pointer" @click="toggleAutoDispatch" :class="{ 'stat-active': dispatchEnabled }" :title="dispatchEnabled ? 'Nhấn để tắt' : 'Nhấn để bật'">
              <div class="stat-icon" :class="dispatchEnabled ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'"><i :class="dispatchEnabled ? 'fa-solid fa-power-off' : 'fa-solid fa-clock'"></i></div>
              <div class="stat-info">
                <span class="stat-label">Auto Dispatch</span>
                <h4 class="stat-value" :class="dispatchEnabled ? 'text-success' : 'text-warning'">{{ dispatchEnabled ? 'BẬT' : 'TẮT' }}</h4>
              </div>
            </div>
            <div class="stat-card shadow-sm border-0">
              <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-clock"></i></div>
              <div class="stat-info">
                <span class="stat-label">Chờ phân công</span>
                <h4 class="stat-value">{{ waitingForDispatchCount }}</h4>
              </div>
            </div>
            <div class="stat-card shadow-sm border-0">
              <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-shield-halved"></i></div>
              <div class="stat-info">
                <span class="stat-label">Sẵn sàng</span>
                <h4 class="stat-value">{{ availableTeamsCount }}</h4>
              </div>
            </div>
            <div class="stat-card shadow-sm border-0">
              <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-truck-fast"></i></div>
              <div class="stat-info">
                <span class="stat-label">Đang xử lý</span>
                <h4 class="stat-value">{{ busyTeamsCount }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading / Error -->
      <div v-if="loadingRequests || loadingTeams"
        class="d-flex justify-content-center align-items-center py-5 flex-column gap-3">
        <div class="spinner"></div>
        <span class="text-muted fw-medium">Đang đồng bộ dữ liệu...</span>
      </div>

      <div v-if="error && !loadingRequests" class="alert alert-danger custom-alert-warning mb-4">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3 float-end" @click="initData">Thử lại</button>
      </div>

      <!-- Main Layout -->
      <div class="row g-4" v-if="!loadingRequests && !loadingTeams">
        <!-- Cột Left: Queue -->
        <div class="col-xl-4 col-lg-5">
          <div class="card panel-card panel-left d-flex flex-column">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 shadow-sm z-1">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bolder mb-0 text-dark">Hàng đợi sự cố</h5>
                <button class="btn btn-light btn-icon rounded-circle" @click="initData" :disabled="loadingRequests">
                  <i class="fa-solid fa-rotate-right text-secondary" :class="{ 'spin': loadingRequests }"></i>
                </button>
              </div>

              <div class="search-box mb-3">
                <i class="fa-solid fa-search search-icon"></i>
                <input type="text" class="form-control shadow-none" placeholder="Tìm kiếm ID, khu vực..."
                  v-model="searchQuery">
              </div>

              <div class="filter-chips pb-3 custom-scrollbar">
                <button v-for="f in severityFilters" :key="f.value" class="chip fw-medium"
                  :class="[{ 'active': selectedSeverityFilter === f.value }]" @click="toggleSeverityFilter(f.value)">
                  {{ f.label }}
                </button>
              </div>
            </div>

            <div class="card-body p-0 list-queue custom-scrollbar">
              <div v-for="req in filteredRequests" :key="req.id" class="request-card"
                :class="{ 'active': selectedReq && selectedReq.id === req.id }" @click="selectRequest(req)">
                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                  <span class="level-badge" :class="getSeverityBadge(req.mucDoKhanCap)">{{ req.severityLabel }}</span>
                  <span class="text-secondary small fw-bolder">#{{ req.id }}</span>
                </div>
                <h6 class="request-title fw-bolder text-dark mb-1 text-truncate pe-2" :title="req.title">{{ req.title }}
                </h6>
                <div class="request-meta d-flex justify-content-between align-items-center mt-2">
                  <span class="text-truncate text-muted small fw-medium" style="max-width: 75%;"><i
                      class="fa-solid fa-location-dot me-1 text-primary"></i>{{ req.location }}</span>
                  <small class="text-muted text-nowrap fw-medium"><i class="fa-regular fa-clock me-1"></i>{{
                    req.time.split(' ')[0] }}</small>
                </div>
              </div>

              <div v-if="filteredRequests.length === 0" class="empty-state text-center p-5">
                <div
                  class="empty-icon mx-auto mb-3 text-success bg-success-subtle rounded-circle d-flex align-items-center justify-content-center"
                  style="width: 56px; height: 56px;"><i class="fa-solid fa-check-double fs-4"></i></div>
                <h6 class="fw-bold">Đã xử lý xong!</h6>
                <p class="text-muted small">Không có yêu cầu nào trong hàng đợi</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Cột Right: Info & Action -->
        <div class="col-xl-8 col-lg-7">
          <div class="card panel-card panel-right h-100 d-flex flex-column panel-right-full">
            <template v-if="selectedReq">
              <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center gap-3">
                    <span class="level-badge py-2 px-3 fw-bolder fs-6"
                      :class="getSeverityBadge(selectedReq.mucDoKhanCap)">{{ selectedReq.severityLabel }}</span>
                    <h4 class="fw-bolder text-dark mb-0">Yêu cầu #{{ selectedReq.id }}</h4>
                  </div>
                </div>
              </div>

              <div class="card-body p-4 custom-scrollbar overflow-auto">
                <!-- Info Box -->
                <div class="row g-4 mb-4">
                  <div class="col-md-7">
                    <div class="info-box bg-light h-100 p-4 rounded-4 list-item-left position-relative overflow-hidden"
                      :class="getBorderSeverity(selectedReq.mucDoKhanCap)">
                      <div class="box-label text-muted small fw-bolder text-uppercase tracking-wider mb-3"><i
                          class="fa-solid fa-circle-info me-1"></i> Chi tiết sự cố</div>
                      <h5 class="fw-bolder mb-3 text-dark">{{ selectedReq.title }}</h5>
                      <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-2">
                          <i class="fa-solid fa-location-arrow text-primary mt-1"></i>
                          <div>
                            <div class="small text-muted fw-semibold">Vị trí hiện trường</div>
                            <div class="fw-medium text-dark">{{ selectedReq.location }}</div>
                          </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                          <i class="fa-solid fa-truck-medical text-primary mt-1"></i>
                          <div class="w-100">
                            <div class="small text-muted fw-semibold mb-1">Nhu cầu cần giúp</div>
                            <div class="d-flex flex-wrap gap-1"
                              v-if="selectedReq.chiTietSuCo && selectedReq.chiTietSuCo.length > 0">
                              <span v-for="(detail, idx) in selectedReq.chiTietSuCo" :key="idx"
                                class="badge bg-light text-dark border border-secondary border-opacity-25 rounded-pill px-2 py-1 fw-medium">
                                {{ detail }}
                              </span>
                            </div>
                            <div v-else class="fw-medium text-dark">Không có chi tiết cụ thể</div>
                          </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                          <i class="fa-regular fa-clock text-primary mt-1"></i>
                          <div>
                            <div class="small text-muted fw-semibold">Thời gian phát sinh</div>
                            <div class="fw-medium text-dark">{{ selectedReq.time }}</div>
                          </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                          <i class="fa-solid fa-align-left text-primary mt-1"></i>
                          <div>
                            <div class="small text-muted fw-semibold">Thông tin thêm</div>
                            <div class="fw-medium text-dark">{{ selectedReq.description || 'Không có mô tả chi tiết từ người dùng.' }}</div>
                          </div>
                        </div>
                        <div class="d-flex align-items-start gap-2" v-if="selectedReq.soNguoiBiAnhHuong">
                          <i class="fa-solid fa-users text-danger mt-1"></i>
                          <div>
                            <div class="small text-muted fw-semibold">Số nạn nhân ước tính</div>
                            <div class="fw-bold text-danger">{{ selectedReq.soNguoiBiAnhHuong }} người</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-5">
                    <div
                      class="info-box bg-white border border-light h-100 p-4 rounded-4 d-flex flex-column justify-content-center align-items-center text-center shadow-sm">
                      <div
                        class="box-label text-muted small fw-bolder text-uppercase tracking-wider mb-3 w-100 text-start">
                        <i class="fa-solid fa-user me-1"></i> Người Gửi Yêu Cầu
                      </div>
                      <div
                        class="reporter-avatar bg-primary text-white fw-bolder rounded-circle d-flex align-items-center justify-content-center mb-3 shadow"
                        style="width: 56px; height: 56px; font-size: 24px;">
                        {{ selectedReq.reporter.charAt(0).toUpperCase() }}
                      </div>
                      <h6 class="fw-bolder text-dark mb-1">{{ selectedReq.reporter }}</h6>
                      <div class="fw-bold text-primary mb-3"><i class="fa-solid fa-phone me-1"></i> {{ selectedReq.phone }}</div>

                      <div v-if="selectedReq.hinhAnh" class="w-100 mb-3">
                        <img :src="selectedReq.hinhAnh" alt="Hình ảnh sự cố" class="rounded-3 w-100" style="max-height: 140px; object-fit: cover; border: 1px solid #dee2e6;">
                      </div>

                      <div class="d-flex gap-2 w-100 mt-auto">
                        <button class="btn btn-outline-primary btn-sm fw-medium flex-grow-1"><i
                            class="fa-solid fa-phone me-1"></i>Liên hệ</button>
                        <button class="btn btn-outline-secondary btn-sm fw-medium flex-grow-1"><i
                            class="fa-solid fa-map me-1"></i>Nhắn Tin</button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Teams List -->
                <div class="d-flex justify-content-between align-items-end mb-3 mt-2 border-bottom pb-3">
                  <div>
                    <h5 class="fw-bolder text-dark d-flex align-items-center gap-2 mb-1">
                      <i class="fa-solid fa-users-gear text-primary"></i> Phân Bổ Lực Lượng
                    </h5>
                    <span class="text-muted small"><strong>{{ sortedAvailableTeams.length }}
                      </strong> đơn vị hiển thị ·
                      <strong>{{ availableTeamsCount }}</strong> sẵn sàng ·
                      <!-- ưu tiên: cùng loại + cùng quận &gt; cùng loại &gt; cùng quận &gt; khoảng cách -->
                    </span>
                  </div>

                  <div class="d-flex gap-2 align-items-end" v-if="availableTeams.length > 0">
                    <div class="search-box" style="margin-bottom: 0; min-width: 240px;">
                      <i class="fa-solid fa-search search-icon"></i>
                      <input type="text" class="form-control shadow-none" placeholder="Tìm kiếm đội cứu hộ..."
                        v-model="searchTeamQuery" style="padding: 8px 16px 8px 36px;">
                    </div>
                  </div>
                </div>

                <div v-if="loadingTeams" class="text-center py-5">
                  <div class="spinner-border text-primary mb-2"></div>
                  <div class="text-muted fw-medium">Đang tìm đơn vị phù hợp...</div>
                </div>

                <div v-else-if="availableTeamsCount === 0"
                  class="alert custom-alert-warning fw-medium d-flex align-items-center">
                  <i class="fa-solid fa-circle-exclamation fs-5 me-3"></i> Tất cả đội cứu hộ đều đang bận. Vui lòng đợi
                  một đội hoàn thành nhiệm vụ hoặc sử dụng lực lượng dự phòng.
                </div>

                <div class="row g-3" v-else>
                  <div class="col-md-6 col-lg-6 col-xl-6" v-for="(team, index) in sortedAvailableTeams" :key="team.id">
                    <div class="team-card h-100"
                      :class="{ 'selected': isTeamSelected(team.id), 'busy': isTeamBusy(team.id) }"
                      @click="selectTeam(team)">
                      <div class="priority-badge" :class="getPriorityBadgeClass(index)">
                        #{{ index + 1 }}
                      </div>
                      <div class="d-flex gap-3">
                        <div class="team-avatar fw-bold icon-box"
                          :class="isTeamSelected(team.id) ? 'bg-primary text-white' : 'bg-light text-secondary border'">
                          {{ (team.ten_co || team.ten_doi).charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                          <div class="d-flex justify-content-between align-items-start">
                            <h6 class="fw-bold text-dark mb-0 text-truncate pe-2">{{ team.ten_co || team.ten_doi }}</h6>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                              <span class="status-dot" :class="getTeamStatusClass(team)"
                                :title="getTeamStatusLabel(team)"></span>
                            </div>
                          </div>
                          <div class="text-muted small fw-medium mt-1 text-truncate"><i
                              class="fa-solid fa-map-location-dot me-1"></i>{{ team.khu_vuc_quan_ly || 'Hỗ trợ toàn khu vực' }}</div>

                          <!-- Capacity progress bar -->
                          <div class="capacity-wrapper mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                              <span class="capacity-label small fw-semibold"
                                :class="isTeamBusy(team.id) ? 'text-danger' : 'text-secondary'">
                                <i class="fa-solid fa-list-check me-1"></i>Nhiệm vụ đang xử lý
                              </span>
                              <div class="d-flex align-items-center gap-2">
                                <!-- Pending: đã phân công nhưng chưa tiếp nhận -->
                                <span v-if="(team.pending_count ?? 0) > 0"
                                  class="capacity-pending-badge small fw-bolder">
                                  <i class="fa-regular fa-clock me-1"></i>{{ team.pending_count }} chờ
                                </span>
                                <!-- Active: đang xử lý -->
                                <span class="capacity-badge small fw-bolder"
                                  :class="isTeamBusy(team.id) ? 'bg-danger-subtle text-danger' : 'bg-light text-secondary'">
                                  {{ team.active_count ?? 0 }} / {{ getMaxCapacity(team) }}
                                </span>
                              </div>
                            </div>
                            <div class="capacity-bar-track">
                              <div class="capacity-bar-fill" :class="getCapacityBarClass(team)"
                                :style="{ width: getCapacityBarWidth(team) }"></div>
                            </div>
                            <div class="capacity-hint small mt-1"
                              :class="isTeamBusy(team.id) ? 'text-danger' : 'text-muted'">
                              <template v-if="isTeamBusy(team.id)">
                                <i class="fa-solid fa-circle-exclamation me-1"></i>Đội quá tải ({{
                                getTotalAssignments(team) }} / {{ getMaxCapacity(team) }} yêu cầu)
                              </template>
                              <template v-else>
                                <i class="fa-solid fa-check-circle me-1"></i>Còn {{ getMaxCapacity(team) -
                                getTotalAssignments(team) }} chỗ trống ({{ getTotalAssignments(team) }} / {{
                                getMaxCapacity(team) }})
                              </template>
                            </div>
                          </div>

                          <div class="d-flex flex-wrap gap-1 mt-2">
                            <span class="meta-tag"><i class="fa-solid fa-users text-primary me-1"></i>{{
                              team.thanh_viens ? team.thanh_viens.length : 0 }} người</span>
                            <!-- <span class="meta-tag text-success bg-success-subtle bg-opacity-50" v-if="team.cung_quan"><i
                                class="fa-solid fa-bolt me-1"></i>Cùng quận</span> -->
                            <span class="meta-tag distance-badge" v-if="team.khoang_cach_km != null"><i
                                class="fa-solid fa-location-arrow me-1"></i>{{
                                  formatDistance(team.khoang_cach_km) }}</span>
                            <span class="meta-tag text-danger bg-danger-subtle bg-opacity-25"
                              v-if="team.cung_loai_su_co === true"><i class="fa-solid fa-fire me-1"></i>Đúng loại</span>
                            <span class="meta-tag text-secondary bg-secondary-subtle bg-opacity-25"
                              v-if="team.cung_loai_su_co === false"><i class="fa-solid fa-fire me-1"></i>Khác
                              loại</span>
                            <span class="meta-tag text-muted bg-light" v-if="team.cung_loai_su_co === null"><i
                                class="fa-solid fa-circle-question me-1"></i>Chưa rõ</span>
                          </div>
                          <div class="d-flex flex-wrap gap-1 mt-2" v-if="team.loai_su_co && team.loai_su_co.length > 0">
                            <span class="type-tag" v-for="(type, idx) in team.loai_su_co" :key="idx" :class="{
                              'type-match': team.cung_loai_su_co === true,
                              'type-mismatch': team.cung_loai_su_co === false,
                              'type-unknown': team.cung_loai_su_co === null
                            }">{{ type }}</span>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div
                class="card-footer bg-white border-top px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3 panel-footer">
                <div>
                  <span class="fw-bolder fs-6 text-dark" v-if="selectedTeams.length > 0">
                    Đã chọn xuất phát: <span class="text-primary fs-5 ms-1">{{ selectedTeams.length }}</span> đội
                  </span>
                  <span class="text-danger fw-bolder d-flex align-items-center gap-2" v-else>
                    <i class="fa-solid fa-circle-exclamation"></i> Vui lòng lựa chọn lực lượng tham gia
                  </span>
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-outline-secondary fw-bolder px-4 py-2 rounded-3"
                    @click="selectedReq = null">Hủy bỏ</button>
                  <button
                    class="btn btn-primary btn-dispatch fw-bolder px-4 py-2 rounded-3 d-flex align-items-center gap-2 text-white shadow-sm"
                    :disabled="selectedTeams.length === 0 || assigning" @click="assignTask">
                    <span v-if="assigning" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="fa-solid fa-truck-fast"></i>
                    <span>Chốt & Xuất Phát Lệnh</span>
                  </button>
                </div>
              </div>
            </template>

            <template v-else>
              <div class="empty-selection d-flex flex-column justify-content-center align-items-center text-center p-5">
                <div
                  class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mb-4 icon-pulse shadow-sm"
                  style="width: 88px; height: 88px; font-size: 36px;">
                  <i class="fa-solid fa-headset"></i>
                </div>
                <h4 class="fw-bolder text-dark mb-2">Trung Tâm Điều Phối</h4>
                <p class="text-muted max-w-sm mb-0">Chọn một ca cấp cứu từ hàng đợi. Hệ thống sẽ tự động đề xuất lực
                  lượng phản ứng nhanh tại khu vực gần nhất.</p>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescueRequestAPI, rescueTeamAPI, assignmentAPI, autoDispatchAPI } from "../../../services/api";

const BASE_URL = 'http://localhost:8000';

function getImageUrl(image) {
  if (!image) return null;
  const raw = String(image).trim();
  if (!raw) return null;
  if (/^(https?:|data:|blob:)/i.test(raw)) return raw;

  const clean = raw.replace(/^\/+/, "");

  if (clean.startsWith("uploads/") || clean.startsWith("storage/")) {
    return `${BASE_URL}/${clean}`;
  }

  if (!clean.includes("/") && /\.(jpg|jpeg|png|gif|webp|bmp|svg)$/i.test(clean)) {
    return `${BASE_URL}/uploads/${clean}`;
  }

  if (clean.includes("/")) {
    return `${BASE_URL}/${clean}`;
  }

  return null;
}

const SEVERITY_MAP = {
  'CRITICAL': { label: 'CRITICAL', badge: 'lv-critical', border: 'border-critical' },
  'HIGH': { label: 'HIGH', badge: 'lv-high', border: 'border-high' },
  'MEDIUM': { label: 'MEDIUM', badge: 'lv-medium', border: 'border-medium' },
  'LOW': { label: 'LOW', badge: 'lv-low', border: 'border-low' },
};

const SEVERITY_NUM_MAP = {
  4: 'CRITICAL',
  3: 'HIGH',
  2: 'MEDIUM',
  1: 'LOW',
};

const PENDING_REQUEST_STATUSES = new Set(['CHO_XU_LY', 'MOI', 'WAITING']);

function normalizeStatus(value) {
  return String(value || '').toUpperCase().trim().replace(/\s+/g, '_');
}

function normalizeText(value, fallback = "") {
  if (value === null || value === undefined) return fallback;
  if (typeof value === "object") {
    return normalizeText(
      value.ten_danh_muc || value.ten_loai_su_co || value.ten_doi || value.ten_co || value.title || value.name || fallback,
      fallback
    );
  }
  return String(value).trim();
}

function toNullableNumber(value) {
  if (value === null || value === undefined || value === "") return null;
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : null;
}

function normalizeTriState(value) {
  if (value === true || value === 1 || value === '1') return true;
  if (value === false || value === 0 || value === '0') return false;
  return null;
}

function removeAccents(str) {
  return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function extractArrayPayload(payload, key) {
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload?.[key])) return payload[key];
  if (Array.isArray(payload?.data?.[key])) return payload.data[key];
  if (Array.isArray(payload?.data?.data)) return payload.data.data;
  if (Array.isArray(payload?.data)) return payload.data;
  return [];
}

function getSeverityInfo(rawSev) {
  if (!rawSev) return SEVERITY_MAP['MEDIUM'];

  if (isNaN(rawSev)) {
    const upper = String(rawSev).toUpperCase().trim();
    if (upper === 'CRITICAL') return SEVERITY_MAP['CRITICAL'];
    if (upper === 'HIGH') return SEVERITY_MAP['HIGH'];
    if (upper === 'MEDIUM') return SEVERITY_MAP['MEDIUM'];
    if (upper === 'LOW') return SEVERITY_MAP['LOW'];
    const mapped = SEVERITY_NUM_MAP[parseInt(rawSev)];
    return mapped ? SEVERITY_MAP[mapped] : SEVERITY_MAP['MEDIUM'];
  }

  const n = parseInt(rawSev);
  if (n <= 1) return SEVERITY_MAP['LOW'];
  if (n === 2) return SEVERITY_MAP['MEDIUM'];
  if (n === 3) return SEVERITY_MAP['HIGH'];
  return SEVERITY_MAP['CRITICAL'];
}

function formatTime(value) {
  if (!value) return "Không rõ";
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return normalizeText(value, "Không rõ");
  return parsed.toLocaleString("vi-VN", {
    hour: "2-digit", minute: "2-digit", day: "2-digit", month: "2-digit", year: "numeric"
  });
}

function parseRequests(payload) {
  const rawData = payload?.data?.data || payload?.data || payload || [];
  const items = Array.isArray(rawData) ? rawData : [];
  return items.map((item) => {
    const sev = item.muc_do_khan_cap || item.muc_do;
    const sevInfo = getSeverityInfo(sev);
    const typeName = normalizeText(
      item.loai_su_co?.ten_danh_muc || item.loai_su_co?.ten_loai_su_co || item.loai_su_co?.ten_loai || item.loai || "Sự cố"
    );
    const reporterName = normalizeText(
      item.nguoi_dung?.ho_ten || item.nguoi_dung?.name || item.reporter || "Khách hàng"
    );
    const reporterPhone = normalizeText(
      item.nguoi_dung?.so_dien_thoai || item.nguoi_dung?.phone || item.phone || "Không có dữ liệu"
    );

    let chiTietSuCo = [];
    if (Array.isArray(item.chi_tiet_loai_su_co)) {
      chiTietSuCo = item.chi_tiet_loai_su_co.map(c => c.ten_chi_tiet || c.ten || c.name || c.label).filter(Boolean);
    } else if (Array.isArray(item.chi_tiet)) {
      chiTietSuCo = item.chi_tiet.map(c => c.ten_chi_tiet || c.ten || c.name || c.label).filter(Boolean);
    } else if (typeof item.chi_tiet === 'string' && item.chi_tiet.trim()) {
      chiTietSuCo = item.chi_tiet.split(',').map(s => s.trim()).filter(Boolean);
    } else if (Array.isArray(item.loai_su_co?.chi_tiets)) {
      // Backend returns chi tiết under loaiSuCo.chiTiets relationship
      chiTietSuCo = item.loai_su_co.chi_tiets.map(c => c.ten_chi_tiet || c.ten || c.name || c.label).filter(Boolean);
    } else if (Array.isArray(item.loai_su_co?.chiTiets)) {
      chiTietSuCo = item.loai_su_co.chiTiets.map(c => c.ten_chi_tiet || c.ten || c.name || c.label).filter(Boolean);
    }

    return {
      id: item.id_yeu_cau || item.id || "-",
      raw: item,
      title: typeName,
      location: normalizeText(item.vi_tri_dia_chi || item.dia_chi || "Chưa xác định địa chỉ"),
      description: normalizeText(item.mo_ta || item.description || ""),
      mucDoKhanCap: sev,
      severityLabel: sevInfo.label,
      reporter: reporterName,
      phone: reporterPhone,
      time: formatTime(item.thoi_gian_gui || item.created_at || item.updated_at || item.thoi_gian),
      date: formatTime(item.thoi_gian_gui || item.created_at).split(',')[0] || "",
      soNguoiBiAnhHuong: item.so_nguoi_bi_anh_huong || null,
      diemUuTien: parseFloat(item.diem_uu_tien || 0),
      trangThai: item.trang_thai,
      chiTietSuCo: chiTietSuCo,
      idLoaiSuCo: item.id_loai_su_co || null,
      hinhAnh: getImageUrl(item.hinh_anh),
    };
  });
}

function parseTeams(payload) {
  const rawData = payload?.data?.data || payload?.data || payload || [];
  const items = Array.isArray(rawData) ? rawData : [];
  return items.map((item) => {
    const loaiSuCoRaw = item.loai_su_co;
    let loaiSuCo = [];
    if (Array.isArray(loaiSuCoRaw)) {
      loaiSuCo = loaiSuCoRaw
        .map(s => (typeof s === 'string' ? s : (s?.ten || s?.ten_danh_muc || '')))
        .filter(Boolean);
    }

    // phan_congs: backend returns this; fallback to item.raw.phan_congs if set
    const phanCongRaw = item.phan_congs ?? item.raw?.phan_congs ?? [];
    const phanCongs = Array.isArray(phanCongRaw) ? phanCongRaw : [];

    return {
      id: item.id_doi_cuu_ho || item.id,
      raw: item,
      ten_doi: normalizeText(item.ten_doi || item.ten_co || item.name || "Đội cứu hộ"),
      khu_vuc_quan_ly: normalizeText(item.khu_vuc_quan_ly || item.area || ""),
      so_dien_thoai_hotline: item.so_dien_thoai_hotline || item.phone || "",
      trang_thai: item.trang_thai || "SanSang",
      thanh_viens: Array.isArray(item.thanh_viens) ? item.thanh_viens : [],
      tai_nguyens: Array.isArray(item.tai_nguyens) ? item.tai_nguyens : [],
      vi_tri_lat: item.vi_tri_lat || null,
      vi_tri_lng: item.vi_tri_lng || null,
      khoang_cach_km: item.khoang_cach_km !== undefined && item.khoang_cach_km !== null ? item.khoang_cach_km : null,
      cung_loai_su_co: (() => {
        const v = item.cung_loai_su_co;
        if (v === true || v === 1 || v === '1') return true;
        if (v === false || v === 0 || v === '0') return false;
        return null; // chưa xác định (backend trả về null)
      })(),
      cung_quan: item.cung_quan === true || item.cung_quan === 1 || item.cung_quan === '1',
      loai_su_co: loaiSuCo,
      // Capacity fields — backend is the single source of truth
      phan_congs: phanCongs,
      // active_count: task thực sự đang xử lý (DANG_XU_LY / DA_DEN_HIEN_TRUONG)
      active_count: item.active_count ?? 0,
      // pending_count: task mới phân công chưa tiếp nhận (MOI / DA_PHAN_CONG)
      pending_count: item.pending_count ?? 0,
      // capacity: tổng số task tối đa = số thành viên
      capacity: item.capacity ?? 0,
      trang_thai_theo_nang_luc: item.trang_thai_theo_nang_luc || 'available',
    };
  });
}

export default {
  name: "AdminAssignments",
  data() {
    return {
      searchQuery: '',
      searchTeamQuery: '',
      selectedReq: null,
      selectedTeams: [],
      selectedSeverityFilter: 'all',

      pendingRequests: [],
      teams: [],
      loadingRequests: false,
      loadingTeams: false,
      suggestedTeamIds: [],
      assigning: false,
      error: '',
      realtimeChannel: null,
      teamChannel: null,
      dispatchEnabled: false,
      togglingDispatch: false,

      severityFilters: [
        { value: 'all', label: 'Tất cả' },
        { value: 'CRITICAL', label: 'CRITICAL' },
        { value: 'HIGH', label: 'HIGH' },
        { value: 'MEDIUM', label: 'MEDIUM' },
        { value: 'LOW', label: 'LOW' },
      ],
    };
  },
  computed: {
    filteredRequests() {
      let reqs = this.pendingRequests;
      // Chỉ hiển thị yêu cầu ở trạng thái CHO_XU_LY (chờ phân công)
      reqs = reqs.filter(r => {
        const status = normalizeStatus(r.trangThai);
        return status === 'CHO_XU_LY';
      });
      if (this.selectedSeverityFilter !== 'all') {
        reqs = reqs.filter(r => {
          const sev = String(r.mucDoKhanCap || '').toUpperCase();
          return sev === this.selectedSeverityFilter;
        });
      }
      if (this.searchQuery) {
        const q = this.searchQuery.toLowerCase();
        reqs = reqs.filter(r =>
          removeAccents(r.title).includes(removeAccents(q)) ||
          removeAccents(r.location).includes(removeAccents(q)) ||
          removeAccents(String(r.id)).includes(removeAccents(q))
        );
      }
      return reqs;
    },
    availableTeams() {
      // Always return ALL teams - never filter them out of the list.
      // Teams with full capacity will be marked as disabled (busy) in the template.
      // Issue #1+2 fix: teams must ALWAYS remain visible in the list.
      return this.teams;
    },
    busyTeams() {
      // Capacity = members * 1 (dong nhat voi backend AutoDispatchService)
      // Full = actual assignments >= capacity
      return this.teams.filter(t => {
        const members = t.thanh_viens?.length ?? 0;
        const capacity = members * 1;
        const total = this.getTotalAssignments(t);
        return capacity > 0 && total >= capacity;
      });
    },
    availableTeamsCount() {
      return this.teams.length - this.busyTeams.length;
    },
    busyTeamsCount() {
      return this.busyTeams.length;
    },
    waitingForDispatchCount() {
      return this.pendingRequests.filter(r => normalizeStatus(r.trangThai) === 'CHO_XU_LY').length;
    },
    sortedAvailableTeams() {
      // Only show teams that match the incident type (cung_loai_su_co === true).
      const search = this.searchTeamQuery ? this.searchTeamQuery.toLowerCase().trim() : '';

      const base = this.availableTeams.filter(team => {
        const searchMatch = !search || (
          removeAccents(team.ten_doi || team.ten_co || '').includes(removeAccents(search)) ||
          removeAccents(team.khu_vuc_quan_ly || '').includes(removeAccents(search))
        );
        if (!searchMatch) return false;
        // Filter by incident type only when no search term AND a request is selected
        if (!search && this.selectedReq && team.cung_loai_su_co !== true) return false;
        return true;
      });

      return [...base].sort((a, b) => {
        const aScore = (a.cung_quan ? 1 : 0);
        const bScore = (b.cung_quan ? 1 : 0);

        if (aScore !== bScore) {
          return bScore - aScore;
        }

        const aDist = a.khoang_cach_km ?? Infinity;
        const bDist = b.khoang_cach_km ?? Infinity;
        return aDist - bDist;
      });
    },
    sameDistrictCount() {
      if (!this.selectedReq) return 0;
      if (this.suggestedTeamIds && this.suggestedTeamIds.length > 0) {
        return this.suggestedTeamIds.filter(id => this.isTeamAvailable(id)).length;
      }
      return this.availableTeams.filter(t => t.cung_quan).length;
    },
  },
  async   created() {
    this.loadDispatchStatus();
    this.setupRealtimeSync();
    this.subscribeToRescueUpdates();
    this.initData();
  },
  beforeUnmount() {
    this.cleanupRealtimeSync();
    this.unsubscribeFromRescueUpdates();
  },
  mounted() {
    const queryId = this.$route.query.id;
    if (queryId) {
      const found = this.pendingRequests.find(r => String(r.id) === String(queryId));
      if (found) {
        this.selectedReq = found;
      } else {
        this.$nextTick(() => {
          const retry = this.pendingRequests.find(r => String(r.id) === String(queryId));
          if (retry) this.selectedReq = retry;
        });
      }
    }
  },
  watch: {
    selectedReq(newReq) {
      this.selectedTeams = [];
      this.fetchNearestTeams(newReq);
    },
    pendingRequests: {
      handler(val) {
        const queryId = this.$route.query.id;
        if (queryId && !this.selectedReq) {
          const found = val.find(r => String(r.id) === String(queryId));
          if (found) this.selectedReq = found;
        }
      },
      immediate: false,
    },
  },
  methods: {
    setupRealtimeSync() {
      window.addEventListener("storage", this.handleStorageChange);
      window.addEventListener("dispatch-status-changed", this.handleDispatchStatusChange);
    },
    cleanupRealtimeSync() {
      window.removeEventListener("storage", this.handleStorageChange);
      window.removeEventListener("dispatch-status-changed", this.handleDispatchStatusChange);
    },
    handleDispatchStatusChange(e) {
      if (e?.detail?.enabled !== undefined) {
        this.dispatchEnabled = e.detail.enabled;
        const saved = localStorage.getItem("realtimeDispatchConfig");
        const config = saved ? JSON.parse(saved) : {};
        config.dispatchEnabled = this.dispatchEnabled;
        localStorage.setItem("realtimeDispatchConfig", JSON.stringify(config));
      }
    },
    handleStorageChange(e) {
      if (e.key === "realtimeDispatchConfig" && e.newValue) {
        try {
          const parsed = JSON.parse(e.newValue);
          if (parsed.dispatchEnabled !== undefined) {
            this.dispatchEnabled = parsed.dispatchEnabled;
          }
        } catch {}
      }
    },
    subscribeToRescueUpdates() {
      if (!window.Echo) {
        console.warn('[AdminAssignments] Echo not available');
        return;
      }

      if (this.realtimeChannel) return;

      // Subscribe to rescue requests updates
      this.realtimeChannel = window.Echo.channel('rescue-requests');
      this.realtimeChannel.listen('RescueRequestUpdated', (event) => {
        this.handleRescueUpdate(event);
      });

      // Subscribe to task status changes
      this.realtimeChannel.listen('TaskStatusChanged', (event) => {
        this.handleTaskStatusChange(event);
      });

      // Subscribe to team updates for capacity changes
      if (!this.teamChannel) {
        this.teamChannel = window.Echo.channel('team-updates');
        this.teamChannel.listen('TeamCapacityUpdated', (event) => {
          this.handleTeamCapacityUpdate(event);
        });
      }
    },
    unsubscribeFromRescueUpdates() {
      if (this.realtimeChannel) {
        this.realtimeChannel.stopListening('RescueRequestUpdated');
        this.realtimeChannel.stopListening('TaskStatusChanged');
        window.Echo?.leave('rescue-requests');
        this.realtimeChannel = null;
      }
      if (this.teamChannel) {
        this.teamChannel.stopListening('TeamCapacityUpdated');
        window.Echo?.leave('team-updates');
        this.teamChannel = null;
      }
    },
    extractRequestIdFromEvent(event) {
      return Number(
        event?.id_yeu_cau ??
        event?.id ??
        event?.request_id ??
        event?.yeu_cau?.id_yeu_cau ??
        event?.yeu_cau?.id ??
        event?.request?.id_yeu_cau ??
        event?.request?.id ??
        0
      );
    },
    removePendingRequestById(requestId) {
      const idx = this.pendingRequests.findIndex((item) => Number(item.id) === Number(requestId));
      if (idx !== -1) {
        this.pendingRequests.splice(idx, 1);
      }

      if (this.selectedReq && Number(this.selectedReq.id) === Number(requestId)) {
        this.selectedReq = null;
        this.selectedTeams = [];
        this.suggestedTeamIds = [];
      }
    },
    buildPendingRequestFromEvent(event, requestId, requestStatus) {
      const eventRequest = event?.yeu_cau || event?.request || {};
      const parsed = parseRequests([{
        ...eventRequest,
        ...event,
        id: requestId,
        id_yeu_cau: requestId,
        trang_thai: requestStatus || eventRequest.trang_thai || 'CHO_XU_LY',
        loai_su_co: event?.loai_su_co ?? eventRequest.loai_su_co,
        nguoi_dung: event?.nguoi_dung ?? eventRequest.nguoi_dung,
        vi_tri_dia_chi: event?.vi_tri_dia_chi ?? eventRequest.vi_tri_dia_chi,
        dia_chi: event?.dia_chi ?? eventRequest.dia_chi,
        mo_ta: event?.mo_ta ?? event?.description ?? eventRequest.mo_ta,
        chi_tiet: event?.chi_tiet ?? eventRequest.chi_tiet,
        chi_tiet_loai_su_co: event?.chi_tiet_loai_su_co ?? eventRequest.chi_tiet_loai_su_co,
        thoi_gian_gui: event?.thoi_gian_gui ?? eventRequest.thoi_gian_gui ?? event?.created_at ?? eventRequest.created_at,
        created_at: event?.created_at ?? eventRequest.created_at,
        updated_at: event?.updated_at ?? eventRequest.updated_at,
        so_nguoi_bi_anh_huong: event?.so_nguoi_bi_anh_huong ?? eventRequest.so_nguoi_bi_anh_huong,
        muc_do_khan_cap: event?.muc_do_khan_cap ?? event?.mucDoKhanCap ?? eventRequest.muc_do_khan_cap,
        diem_uu_tien: event?.diem_uu_tien ?? eventRequest.diem_uu_tien,
        id_loai_su_co: event?.id_loai_su_co ?? eventRequest.id_loai_su_co,
      }]);

      return parsed[0] || null;
    },
    handleRescueUpdate(event) {
      const requestId = this.extractRequestIdFromEvent(event);
      if (!requestId) return;

      const eventAction = String(event?.action || '').toLowerCase().trim();
      const requestStatus = normalizeStatus(
        event?.trang_thai ??
        event?.status ??
        event?.yeu_cau?.trang_thai ??
        event?.request?.trang_thai ??
        (eventAction === 'created' ? 'CHO_XU_LY' : '')
      );

      if (!PENDING_REQUEST_STATUSES.has(requestStatus)) {
        this.removePendingRequestById(requestId);
        return;
      }

      const updatedRequest = this.buildPendingRequestFromEvent(event, requestId, requestStatus);
      if (!updatedRequest) return;

      const idx = this.pendingRequests.findIndex((item) => Number(item.id) === requestId);
      if (idx !== -1) {
        this.pendingRequests.splice(idx, 1, {
          ...this.pendingRequests[idx],
          ...updatedRequest,
          raw: {
            ...(this.pendingRequests[idx]?.raw || {}),
            ...(updatedRequest.raw || {}),
          },
        });

        if (this.selectedReq && Number(this.selectedReq.id) === requestId) {
          Object.assign(this.selectedReq, this.pendingRequests[idx]);
        }
        return;
      }

      this.pendingRequests.unshift(updatedRequest);
      this.syncSelectedRequestAfterRefresh();
      
      // Fallback: Refresh team data when requests are updated to ensure capacity is current
      this.refreshTeamDataSilently();
    },
    handleTeamCapacityUpdate(event) {
      console.log('[AdminAssignments] Team capacity update received:', event);
      
      const teamId = Number(event?.team_id || event?.id_doi_cuu_ho);
      if (!teamId) return;

      const teamIndex = this.teams.findIndex(t => Number(t.id) === teamId);
      if (teamIndex === -1) return;

      // Update team capacity data
      const updatedTeam = {
        ...this.teams[teamIndex],
        active_count: Number(event?.active_count) ?? this.teams[teamIndex].active_count,
        pending_count: Number(event?.pending_count) ?? this.teams[teamIndex].pending_count,
        trang_thai: event?.trang_thai ?? this.teams[teamIndex].trang_thai,
        trang_thai_theo_nang_luc: event?.trang_thai_theo_nang_luc ?? this.teams[teamIndex].trang_thai_theo_nang_luc,
      };

      this.teams.splice(teamIndex, 1, updatedTeam);
      
      console.log(`[AdminAssignments] Team ${teamId} capacity updated:`, {
        active_count: updatedTeam.active_count,
        pending_count: updatedTeam.pending_count,
        total: updatedTeam.active_count + updatedTeam.pending_count,
        capacity: this.getMaxCapacity(updatedTeam)
      });

      // Force Vue reactivity update
      this.$forceUpdate();
    },
    handleTaskStatusChange(event) {
      console.log('[AdminAssignments] Task status change received:', event);
      
      const teamId = Number(event?.team_id || event?.id_doi_cuu_ho);
      const oldStatus = event?.old_status;
      const newStatus = event?.new_status;
      
      if (!teamId || !oldStatus || !newStatus) return;
      
      const teamIndex = this.teams.findIndex(t => Number(t.id) === teamId);
      if (teamIndex === -1) return;
      
      const team = this.teams[teamIndex];
      let activeCount = team.active_count ?? 0;
      let pendingCount = team.pending_count ?? 0;
      
      // Update counts based on status transition
      if (oldStatus === 'MOI' && newStatus === 'DANG_XU_LY') {
        // Task moved from pending to active
        pendingCount = Math.max(0, pendingCount - 1);
        activeCount = activeCount + 1;
      } else if (oldStatus === 'DA_PHAN_CONG' && newStatus === 'DANG_XU_LY') {
        // Task moved from pending to active
        pendingCount = Math.max(0, pendingCount - 1);
        activeCount = activeCount + 1;
      } else if (oldStatus === 'DANG_XU_LY' && newStatus === 'DA_XU_LY') {
        // Task completed
        activeCount = Math.max(0, activeCount - 1);
      } else if (oldStatus === 'MOI' && newStatus === 'DA_XU_LY') {
        // Task completed directly from pending
        pendingCount = Math.max(0, pendingCount - 1);
      } else if (oldStatus === 'DA_PHAN_CONG' && newStatus === 'DA_XU_LY') {
        // Task completed directly from pending
        pendingCount = Math.max(0, pendingCount - 1);
      }
      
      const updatedTeam = {
        ...team,
        active_count: activeCount,
        pending_count: pendingCount,
      };
      
      this.teams.splice(teamIndex, 1, updatedTeam);
      
      console.log(`[AdminAssignments] Team ${teamId} capacity updated via task status change:`, {
        oldStatus,
        newStatus,
        active_count: updatedTeam.active_count,
        pending_count: updatedTeam.pending_count,
        total: updatedTeam.active_count + updatedTeam.pending_count,
        capacity: this.getMaxCapacity(updatedTeam)
      });
    },
    syncSelectedRequestAfterRefresh() {
      if (this.selectedReq) {
        const refreshedSelectedReq = this.pendingRequests.find(
          (item) => String(item.id) === String(this.selectedReq.id)
        );

        if (!refreshedSelectedReq) {
          this.selectedReq = null;
          return;
        }

        Object.assign(this.selectedReq, refreshedSelectedReq);
        return;
      }

      const queryId = this.$route.query.id;
      if (!queryId) return;

      const requestFromQuery = this.pendingRequests.find(
        (item) => String(item.id) === String(queryId)
      );

      if (requestFromQuery) {
        this.selectedReq = requestFromQuery;
      }
    },
    isTeamAvailable(id) {
      return this.availableTeams.some(t => Number(t.id) === Number(id));
    },
    isTeamBusy(id) {
      return this.busyTeams.some(t => Number(t.id) === Number(id));
    },
    async fetchNearestTeams(req) {
      if (!req) {
        this.suggestedTeamIds = [];
        return;
      }
      try {
        const payload = {
          id_yeu_cau: req.id,
          id_loai_su_co: req.idLoaiSuCo || null,
        };
        const res = await rescueRequestAPI.findNearestTeams(payload);
        const nearestTeams = res?.data?.teams || [];
        this.suggestedTeamIds = nearestTeams.map(t => Number(t.id || t.id_doi_cuu_ho));

        nearestTeams.forEach(nearby => {
          const teamId = Number(nearby.id || nearby.id_doi_cuu_ho);
          const idx = this.teams.findIndex(t => Number(t.id) === teamId);
          if (idx !== -1) {
            this.teams[idx].khoang_cach_km = nearby.khoang_cach_km !== undefined ? nearby.khoang_cach_km : null;
            this.teams[idx].cung_quan = nearby.cung_quan === true || nearby.cung_quan === 1 || nearby.cung_quan === '1';
            this.teams[idx].cung_loai_su_co = nearby.cung_loai_su_co === true || nearby.cung_loai_su_co === 1 || nearby.cung_loai_su_co === '1';
          }
        });
      } catch (error) {
        console.error('Lỗi tìm đội gần nhất:', error);
        this.suggestedTeamIds = [];
      }
    },
    async initData() {
      this.error = '';
      await Promise.all([this.loadRequests(), this.loadTeams()]);
      // Fix timing bug: after data loads, check if a request was navigated to via ?id=
      const queryId = this.$route.query.id;
      if (queryId && !this.selectedReq) {
        const found = this.pendingRequests.find(r => String(r.id) === String(queryId));
        if (found) {
          this.selectedReq = found;
        }
      }
    },
    async loadRequests(options = {}) {
      const { silent = false } = options;
      if (!silent) {
        this.loadingRequests = true;
      }
      try {
        const response = await rescueRequestAPI.getList();
        const all = parseRequests(response?.data || response);
        // Lấy sự cố đang ở trạng thái mới chờ xử lý
        this.pendingRequests = all.filter(r => {
          return PENDING_REQUEST_STATUSES.has(normalizeStatus(r.trangThai));
        });
        this.syncSelectedRequestAfterRefresh();
      } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
        this.error = 'Lỗi kết nối máy chủ. Không thể lấy dữ liệu phân công.';
      } finally {
        this.loadingRequests = false;
      }
    },
    async loadTeams() {
      this.loadingTeams = true;
      try {
        const response = await rescueTeamAPI.getList({ get_all: true });
        const rawData = response?.data || response;
        this.teams = parseTeams(rawData);
      } catch (error) {
        console.error('Lỗi lấy đội cứu hộ:', error);
      } finally {
        this.loadingTeams = false;
      }
    },
    async refreshTeamDataSilently() {
      try {
        const response = await rescueTeamAPI.getList({ get_all: true });
        const rawData = response?.data || response;
        const updatedTeams = parseTeams(rawData);
        
        // Update existing teams with new capacity data
        updatedTeams.forEach(updatedTeam => {
          const teamIndex = this.teams.findIndex(t => Number(t.id) === Number(updatedTeam.id));
          if (teamIndex !== -1) {
            this.teams.splice(teamIndex, 1, {
              ...this.teams[teamIndex],
              active_count: updatedTeam.active_count,
              pending_count: updatedTeam.pending_count,
              trang_thai: updatedTeam.trang_thai,
              trang_thai_theo_nang_luc: updatedTeam.trang_thai_theo_nang_luc,
            });
          }
        });
        
        console.log('[AdminAssignments] Team data refreshed silently');
      } catch (error) {
        console.error('Lỗi làm mới dữ liệu đội cứu hộ:', error);
      }
    },
    selectRequest(req) {
      this.selectedReq = req;
    },
    selectTeam(team) {
      const index = this.selectedTeams.findIndex(t => t.id === team.id);
      if (index > -1) {
        this.selectedTeams.splice(index, 1);
      } else {
        // Only allow selecting teams that are not fully occupied
        const isBusy = this.busyTeams.some(t => t.id === team.id);
        if (!isBusy) {
          this.selectedTeams.push(team);
        }
      }
    },
    getTeamStatusClass(team) {
      const total = this.getTotalAssignments(team);
      const maxCap = this.getMaxCapacity(team);
      if (maxCap > 0 && total >= maxCap) return 'st-overload';
      if (team.active_count > 0) return 'st-processing';
      return 'st-ready';
    },
    getTeamStatusLabel(team) {
      const total = this.getTotalAssignments(team);
      const maxCap = this.getMaxCapacity(team);
      if (maxCap > 0 && total >= maxCap) return 'Đội quá tải, chọn đội khác';
      if (team.active_count > 0) return 'Đang xử lý';
      return 'Sẵn sàng';
    },
    getMaxCapacity(team) {
      // Capacity = members * 1 (dong nhat voi backend AutoDispatchService)
      const members = team.thanh_viens?.length ?? 0;
      return members * 1;
    },
    getTotalAssignments(team) {
      // Override backend calculation with actual count from phan_congs data
      // Backend may return incorrect counts, so we count actual assignments
      const phanCongs = team.phan_congs || [];
      const validStatuses = ['MOI', 'DA_PHAN_CONG', 'DANG_XU_LY', 'DA_DEN_HIEN_TRUONG'];
      const actualCount = phanCongs.filter(pc => 
        validStatuses.includes(pc.trang_thai || pc.trang_thai_nhiem_vu)
      ).length;
      
      return actualCount;
    },
    getCapacityBarWidth(team) {
      // Use actual count from phan_congs data instead of backend counts
      const total = this.getTotalAssignments(team);
      const maxCap = this.getMaxCapacity(team);
      if (maxCap === 0) return '0%';
      const pct = Math.min((total / maxCap) * 100, 100);
      return pct + '%';
    },
    getCapacityBarClass(team) {
      const total = this.getTotalAssignments(team);
      const maxCap = this.getMaxCapacity(team);
      if (maxCap === 0) return 'bar-empty';
      const ratio = total / maxCap;
      if (ratio >= 1) return 'bar-full';
      if (ratio >= 0.5) return 'bar-warning';
      return 'bar-normal';
    },
    isTeamSelected(teamId) {
      return this.selectedTeams.some(t => t.id === teamId);
    },
    getSeverityBadge(sev) {
      const info = getSeverityInfo(sev);
      return info.badge;
    },
    getBorderSeverity(sev) {
      const info = getSeverityInfo(sev);
      return info.border;
    },
    async fetchNearestTeams(req) {
      if (!req) {
        this.suggestedTeamIds = [];
        this.teams = this.teams.map(team => ({
          ...team,
          khoang_cach_km: null,
          cung_quan: false,
          cung_loai_su_co: null,
        }));
        return;
      }

      try {
        const payload = {
          id_yeu_cau: req.id,
          id_loai_su_co: req.idLoaiSuCo || null,
        };
        const res = await rescueRequestAPI.findNearestTeams(payload);
        const nearestTeams = extractArrayPayload(res, 'teams');
        const nearestTeamsById = new Map(
          nearestTeams.map(team => [Number(team.id || team.id_doi_cuu_ho), team])
        );

        this.suggestedTeamIds = nearestTeams
          .map(team => Number(team.id || team.id_doi_cuu_ho))
          .filter(Number.isFinite);

        this.teams = this.teams.map(team => {
          const nearby = nearestTeamsById.get(Number(team.id));

          if (!nearby) {
            return {
              ...team,
              khoang_cach_km: null,
              cung_quan: false,
              cung_loai_su_co: null,
            };
          }

          return {
            ...team,
            khoang_cach_km: toNullableNumber(
              nearby.khoang_cach_km ?? nearby.khoangCachKm ?? nearby.distance
            ),
            cung_quan: normalizeTriState(nearby.cung_quan) === true,
            cung_loai_su_co: normalizeTriState(nearby.cung_loai_su_co),
          };
        });
      } catch (error) {
        console.error('Lá»—i tĂ¬m Ä‘á»™i gáº§n nháº¥t:', error);
        this.suggestedTeamIds = [];
      }
    },
    formatDistance(km) {
      const normalizedKm = toNullableNumber(km);
      if (normalizedKm === null) {
        return 'N/A';
      }
      return normalizedKm.toFixed(2) + ' km';
    },
    getPriorityBadgeClass(index) {
      // Màu sắc ưu tiên: Top 3 sáng, sau đó nhạt dần
      if (index === 0) return 'priority-1st';
      if (index === 1) return 'priority-2nd';
      if (index === 2) return 'priority-3rd';
      return 'priority-default';
    },
    toggleSeverityFilter(value) {
      this.selectedSeverityFilter = value;
    },
    selectAllTeams() {
      this.selectedTeams = [...this.sortedAvailableTeams];
    },
    deselectAllTeams() {
      this.selectedTeams = [];
    },
    async loadDispatchStatus() {
      try {
        const res = await autoDispatchAPI.getStatus();
        if (res.data?.thanh_cong) {
          this.dispatchEnabled = res.data.du_lieu?.dieu_phoi_tu_dong ?? false;
        } else {
          const saved = localStorage.getItem('realtimeDispatchConfig');
          if (saved) this.dispatchEnabled = JSON.parse(saved).dispatchEnabled || false;
        }
      } catch {
        const saved = localStorage.getItem('realtimeDispatchConfig');
        if (saved) this.dispatchEnabled = JSON.parse(saved).dispatchEnabled || false;
      }
    },
    async toggleAutoDispatch() {
      if (this.togglingDispatch) return;
      this.togglingDispatch = true;
      try {
        await autoDispatchAPI.toggle();
        this.dispatchEnabled = !this.dispatchEnabled;

        // Sync localStorage để RealtimeDispatch page đọc được
        const saved = localStorage.getItem('realtimeDispatchConfig');
        const config = saved ? JSON.parse(saved) : {};
        config.dispatchEnabled = this.dispatchEnabled;
        localStorage.setItem('realtimeDispatchConfig', JSON.stringify(config));

        // Notify RealtimeDispatch nếu đang mở cùng lúc
        window.dispatchEvent(new CustomEvent('dispatch-status-changed', {
          detail: { enabled: this.dispatchEnabled }
        }));

        const statusText = this.dispatchEnabled ? 'Bật' : 'Tắt';
        this.$toast?.success?.(`Auto Dispatch ${statusText}`, {
          position: 'top-right',
          duration: 2000,
        });
      } catch (error) {
        console.error('Lỗi toggle auto-dispatch:', error);
        this.$toast?.error?.('Lỗi cập nhật trạng thái!', {
          position: 'top-right',
          duration: 2000,
        });
      } finally {
        this.togglingDispatch = false;
      }
    },
    async assignTask() {
      if (!this.selectedReq || this.selectedTeams.length === 0) return;

      this.assigning = true;
      try {
        const reqId = this.selectedReq.id;

        for (const team of this.selectedTeams) {
          await assignmentAPI.create({
            id_yeu_cau: reqId,
            id_doi_cuu_ho: team.id,
            mo_ta: `Chỉ thị đội ${team.ten_doi || team.ten_co} xử lý sự cố cấp độ ${this.selectedReq.severityLabel}`,
            trang_thai_nhiem_vu: 'MOI',
          });
          // Team capacity and status are calculated by the backend — do NOT set manually here.
        }

        await rescueRequestAPI.changeStatus(reqId, { trang_thai: 'DA_PHAN_CONG' });

        this.$toast?.success?.(`Đã gửi lệnh xuất phát cho ${this.selectedTeams.length} đội tới hiện trường.`, {
          position: 'top-right',
          duration: 3000,
        });

        this.pendingRequests = this.pendingRequests.filter(r => r.id !== reqId);
        this.selectedReq = null;
        this.selectedTeams = [];

        this.$router.push('/admin/theo-doi-cuu-ho');
      } catch (error) {
        console.error('Lỗi chuyển phân công:', error);
        this.$toast?.error?.('Không thể phát lệnh! Vui lòng kiểm tra lại đường truyền.', {
          position: 'top-right',
          duration: 3000,
        });
      } finally {
        this.assigning = false;
      }
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.dashboard-container {
  background-color: #f8fafc;
  min-height: calc(100vh - 60px);
  font-family: 'Inter', sans-serif;
  color: #1e293b;
}

/* Base custom classes to match UI goals */
.max-w-sm {
  max-width: 384px;
}

.min-w-0 {
  min-width: 0;
}

.tracking-wider {
  letter-spacing: 0.05em;
}

/* Stats */
.stat-card {
  background: white;
  border-radius: 16px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 220px;
  transition: all 0.2s;
}

.stat-card:hover {
  transform: translateY(-3px);
}

.stat-card.cursor-pointer {
  cursor: pointer;
}

.stat-card.stat-active {
  border: 1px solid #10b981;
  background: linear-gradient(135deg, #f0fdf4, #fafafa);
  box-shadow: 0 0 12px rgba(16, 185, 129, 0.15);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  transition: all 0.2s ease;
}

.stat-card:hover .stat-icon {
  transform: scale(1.1);
}

.stat-label {
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  display: block;
}

.stat-value {
  margin: 0;
  font-weight: 800;
  color: #0f172a;
}

/* Panels */
.panel-card {
  border-radius: 16px;
  border: 1px solid #e2e8f0;
}

.panel-left,
.panel-right {
  max-height: calc(100vh - 160px);
}

.card-header {
  border-bottom: 1px solid #f1f5f9;
}

/* Custom Inputs & Buttons */
.search-box {
  position: relative;
}

.search-box input {
  padding: 12px 16px 12px 40px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  font-weight: 500;
  transition: all 0.2s;
}

.search-box input:focus {
  background: white;
  border-color: #2563eb;
  outline: 2px solid rgba(37, 99, 235, 0.2);
  outline-offset: 2px;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.btn-icon {
  width: 36px;
  height: 36px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Filters */
.filter-chips {
  display: flex;
  gap: 8px;
  overflow-x: auto;
}

.chip {
  padding: 6px 16px;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #475569;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.chip:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.chip.active {
  background: #1e293b;
  color: white;
  border-color: #1e293b;
}

/* Request Queue Layer */
.list-queue {
  overflow-y: auto;
}

.request-card {
  padding: 16px 24px;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: all 0.2s;
  background: white;
  position: relative;
}

.request-card:hover {
  background: #f8fafc;
  z-index: 2;
}

.request-card.active {
  background: #eff6ff;
  border-left: 4px solid #2563eb;
  padding-left: 20px;
}

/* Badges System (EMERGENCY) */
.level-badge {
  padding: 4px 10px;
  border-radius: 6px;
  font-weight: 800;
  font-size: 11px;
  letter-spacing: 0.5px;
}

.lv-critical {
  background: #7f1d1d;
  color: white;
}

.lv-high {
  background: #dc2626;
  color: white;
}

.lv-medium {
  background: #f59e0b;
  color: white;
}

.lv-low {
  background: #16a34a;
  color: white;
}

.border-critical {
  border-left: 5px solid #7f1d1d !important;
}

.border-high {
  border-left: 5px solid #dc2626 !important;
}

.border-medium {
  border-left: 5px solid #f59e0b !important;
}

.border-low {
  border-left: 5px solid #16a34a !important;
}

/* Right Panel System */
.info-box {
  border-left: 5px solid transparent;
}

.list-item-left {
  background: #f8fafc;
}

/* Action buttons */
.btn-primary.btn-dispatch {
  background: #2563eb;
  border-color: #2563eb;
  transition: all 0.2s;
}

.btn-primary.btn-dispatch:hover:not(:disabled) {
  background: #1d4ed8;
  border-color: #1d4ed8;
}

.btn-primary:disabled {
  background: #94a3b8;
  border-color: #94a3b8;
}

/* Team Card */
.team-card {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px;
  background: white;
  transition: all 0.2s;
  position: relative;
  overflow: hidden;
  cursor: pointer;
}

.team-card:hover:not(.busy) {
  border-color: #cbd5e1;
  cursor: pointer;
}

.team-card.busy {
  opacity: 0.6;
  cursor: not-allowed;
}

.team-card.selected {
  border-color: #2563eb;
  background: #eff6ff;
  outline: 2px solid #2563eb;
  outline-offset: -2px;
  cursor: pointer;
}

.priority-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  min-width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 12px;
  color: white;
  z-index: 10;
}

.priority-badge.priority-1st {
  background: #dc2626;
  font-size: 14px;
  animation: pulse-badge 2s ease-in-out infinite;
}

.priority-badge.priority-2nd {
  background: #f59e0b;
}

.priority-badge.priority-3rd {
  background: #10b981;
}

.priority-badge.priority-default {
  background: #6366f1;
  opacity: 0.8;
}

@keyframes pulse-badge {

  0%,
  100% {
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
  }

  50% {
    box-shadow: 0 2px 16px rgba(220, 38, 38, 0.5);
  }
}

.icon-box {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
}

/* Status Dot */
.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}

.st-ready {
  background: #16a34a;
  box-shadow: 0 0 0 3px #dcfce7;
}

.st-overload {
  background: #dc2626;
  box-shadow: 0 0 0 3px #fecaca;
}

.st-processing {
  background: #3b82f6;
  box-shadow: 0 0 0 3px #dbeafe;
}

.st-unknown {
  background: #94a3b8;
}

.type-tag {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  letter-spacing: 0.2px;
}

.type-tag.type-match {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.type-tag.type-mismatch {
  background: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
}

.type-tag.type-unknown {
  background: #f8fafc;
  color: #64748b;
  border: 1px solid #e2e8f0;
}

.meta-tag {
  background: #f1f5f9;
  color: #475569;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
}

.distance-badge {
  background: #0ea5e9;
  color: white;
  font-weight: 700 !important;
  padding: 4px 10px !important;
  border-radius: 8px !important;
  animation: fadeIn 0.3s ease-in-out;
}

.distance-badge i {
  color: rgba(255, 255, 255, 0.9);
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Capacity Bar */
.capacity-wrapper {
  background: #f8fafc;
  border-radius: 8px;
  padding: 8px 10px;
  border: 1px solid #e2e8f0;
}

.capacity-bar-track {
  width: 100%;
  height: 6px;
  background: #e2e8f0;
  border-radius: 10px;
  overflow: hidden;
}

.capacity-bar-fill {
  height: 100%;
  border-radius: 10px;
  transition: width 0.4s ease, background 0.3s ease;
}

.bar-empty {
  background: #e2e8f0;
}

.bar-normal {
  background: #16a34a;
}

.bar-warning {
  background: #f59e0b;
}

.bar-full {
  background: #dc2626;
}

.capacity-badge {
  padding: 2px 8px;
  border-radius: 12px;
  letter-spacing: 0.3px;
}

.capacity-pending-badge {
  padding: 2px 8px;
  border-radius: 12px;
  letter-spacing: 0.3px;
  background-color: #fef3c7;
  color: #b45309;
  border: 1px solid #fcd34d;
}

.bg-danger-subtle {
  background-color: #fef2f2;
}

.capacity-hint {
  font-size: 11px;
  line-height: 1.3;
}

.selected-overlay {
  position: absolute;
  top: 16px;
  right: 16px;
}

/* Animations */
.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% {
    transform: rotate(360deg);
  }
}

.icon-pulse {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.2);
  }

  70% {
    box-shadow: 0 0 0 20px rgba(37, 99, 235, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(37, 99, 235, 0);
  }
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e0e7ff;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.custom-alert-warning {
  background: #fffbeb;
  color: #b45309;
  border-left: 4px solid #f59e0b;
}

/* Scrollbars */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Fix: Panel right phải hiển thị full nội dung + footer */
.panel-card.panel-right,
.panel-right-full {
  max-height: none !important;
  overflow: visible !important;
  height: auto !important;
}

.panel-right-full>.card-body {
  overflow: visible !important;
  flex: 1 1 auto;
  min-height: 0;
}

/* Fix: Footer phải luôn hiển thị, không bị overflow cắt */
.panel-right-full>.card-footer,
.panel-footer {
  flex-shrink: 0;
  position: relative;
  z-index: 10;
}

/* Fix: Icon overlay check khi chọn team - đảm bảo pointer-events trên overlay */
.team-card.selected {
  border-color: #2563eb;
  background: #eff6ff;
  outline: 2px solid #2563eb;
  outline-offset: -2px;
  cursor: pointer;
}
</style>
