<template>
  <div class="container-fluid py-4 assignments-page">
    <!-- Header & Statistics -->
    <div class="row mb-4 align-items-center">
      <div class="col-md-6 mb-3 mb-md-0">
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Điều Phối Cứu Hộ</h2>
        <p class="text-muted mb-0">Hệ thống phân công và giám sát lực lượng ứng cứu khẩn cấp</p>
      </div>
      <div class="col-md-6 text-md-end">
        <div class="d-flex justify-content-md-end gap-3">
          <div class="bg-gradient-warning px-4 py-3 rounded-4 shadow-lg border-0 position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 w-100 h-100 bg-gradient-warning-shine"></div>
            <span class="text-warning-dark small fw-bold z-1 position-relative"><i class="fa-solid fa-clock me-1"></i>Đang chờ</span>
            <div class="fw-bold fs-5 text-warning-dark z-1 position-relative">{{ pendingRequests.length }}</div>
          </div>
          <div class="bg-gradient-success px-4 py-3 rounded-4 shadow-lg border-0 position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 w-100 h-100 bg-gradient-success-shine"></div>
            <span class="text-success-dark small fw-bold z-1 position-relative"><i class="fa-solid fa-check-circle me-1"></i>Sẵn sàng</span>
            <div class="fw-bold fs-5 text-success-dark z-1 position-relative">{{ availableTeamsCount }}</div>
          </div>
          <div class="bg-gradient-primary px-4 py-3 rounded-4 shadow-lg border-0 position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 w-100 h-100 bg-gradient-primary-shine"></div>
            <span class="text-primary-dark small fw-bold z-1 position-relative"><i class="fa-solid fa-location-crosshairs me-1"></i>Đang nhiệm vụ</span>
            <div class="fw-bold fs-5 text-primary-dark z-1 position-relative">{{ busyTeamsCount }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loadingRequests || loadingTeams" class="d-flex justify-content-center align-items-center py-5 flex-column gap-3">
      <div class="spinner"></div>
      <span class="text-muted">Đang tải dữ liệu...</span>
    </div>

    <!-- Error -->
    <div v-if="error && !loadingRequests" class="alert custom-alert-danger mb-4 shadow-sm border-0 d-flex align-items-center gap-3 rounded-3">
      <i class="bi bi-exclamation-triangle-fill fs-5"></i>
      <div>{{ error }}</div>
      <button class="btn btn-sm btn-outline-danger ms-auto" @click="initData">Thử lại</button>
    </div>

    <!-- Main Content -->
    <div class="row g-4" v-if="!loadingRequests && !loadingTeams">
      <!-- Cột Left: Danh sách yêu cầu -->
      <div class="col-lg-4 d-flex flex-column">
        <div class="card border-0 shadow-sm rounded-4 flex-grow-1 overflow-hidden" style="max-height: calc(100vh - 180px);">
          <div class="card-header bg-white border-bottom-0 pt-4 pb-3">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="fw-bold mb-0">Danh sách chờ xử lý</h5>
              <button class="btn btn-sm btn-light rounded-circle" title="Làm mới" @click="initData" :disabled="loadingRequests">
                <i class="fa-solid fa-rotate-right text-secondary" :class="{ 'spin-animation': loadingRequests }"></i>
              </button>
            </div>

            <!-- Filters -->
            <div class="mt-3">
              <div class="input-group input-group-sm mb-2">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search"></i></span>
                <input type="text" class="form-control border-start-0 bg-light" placeholder="Tìm theo ID, vị trí..." v-model="searchQuery">
              </div>
              <div class="d-flex gap-2 overflow-auto custom-scrollbar pb-1">
                <span
                  v-for="f in severityFilters"
                  :key="f.value"
                  class="badge rounded-pill px-3 py-2 cursor-pointer"
                  :class="selectedSeverityFilter === f.value ? f.activeClass : 'bg-light text-dark'"
                  role="button"
                  @click="toggleSeverityFilter(f.value)"
                >{{ f.label }}</span>
              </div>
            </div>
          </div>

          <div class="card-body p-0 list-queue custom-scrollbar">
            <div
              v-for="req in filteredRequests"
              :key="req.id"
              class="request-item p-3 border-bottom position-relative transition-all"
              :class="{'selected-req bg-primary-subtle border-primary' : selectedReq && selectedReq.id === req.id}"
              @click="selectRequest(req)"
            >
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge" :class="getSeverityBadge(req.mucDoKhanCap)">{{ req.severityLabel }}</span>
                  <span class="text-secondary small fw-bold">#{{ req.id }}</span>
                </div>
                <small class="text-muted"><i class="fa-regular fa-clock me-1"></i>{{ req.time }}</small>
              </div>
              <h6 class="mb-1 fw-bold text-truncate" :title="req.title">{{ req.title }}</h6>
              <p class="mb-1 small text-muted text-truncate"><i class="fa-solid fa-location-dot me-2 text-danger"></i>{{ req.location }}</p>

              <!-- Indicator for selection -->
              <div v-if="selectedReq && selectedReq.id === req.id" class="position-absolute end-0 top-50 translate-middle-y me-3 text-primary">
                <i class="fa-solid fa-chevron-right"></i>
              </div>
            </div>

            <div v-if="filteredRequests.length === 0" class="p-5 text-center text-muted">
              <i class="fa-solid fa-box-open fs-1 mb-3 opacity-50"></i>
              <p>Không có yêu cầu phù hợp</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Cột Right: Chi tiết và Phân công -->
      <div class="col-lg-8 d-flex flex-column">
        <div class="card border-0 shadow-sm rounded-4 flex-grow-1 overflow-hidden">
          <template v-if="selectedReq">
            <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Chi tiết Yêu cầu #{{ selectedReq.id }}</h5>
                <span class="badge" :class="getSeverityBadge(selectedReq.mucDoKhanCap)">Cấp độ: {{ selectedReq.severityLabel }}</span>
              </div>
            </div>

            <div class="card-body p-4 custom-scrollbar" style="overflow-y: auto; max-height: calc(100vh - 280px);">
              <!-- Request Information -->
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <div class="p-3 bg-light rounded h-100 border-start border-4" :class="getBorderSeverity(selectedReq.mucDoKhanCap)">
                    <h6 class="text-muted small text-uppercase mb-3"><i class="fa-solid fa-info-circle me-2"></i>Thông tin sự cố</h6>
                    <h6 class="fw-bold">{{ selectedReq.title }}</h6>
                    <p class="mb-2 small"><i class="fa-solid fa-location-arrow me-2 text-primary"></i><strong>Vị trí:</strong> {{ selectedReq.location }}</p>
                    <p class="mb-2 small"><i class="fa-solid fa-align-left me-2 text-primary"></i><strong>Mô tả:</strong> {{ selectedReq.description || 'Không có mô tả chi tiết từ người dùng.' }}</p>
                    <p class="mb-0 small"><i class="fa-regular fa-calendar me-2 text-primary"></i><strong>Thời điểm:</strong> {{ selectedReq.date }} lúc {{ selectedReq.time }}</p>
                    <p class="mb-0 small" v-if="selectedReq.soNguoiBiAnhHuong">
                      <i class="fa-solid fa-users me-2 text-primary"></i><strong>Số người bị ảnh hưởng:</strong> {{ selectedReq.soNguoiBiAnhHuong }}
                    </p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="p-3 bg-light rounded h-100 border-start border-4 border-secondary">
                    <h6 class="text-muted small text-uppercase mb-3"><i class="fa-solid fa-user me-2"></i>Người yêu cầu</h6>
                    <p class="mb-2 font-monospace">{{ selectedReq.reporter }}</p>
                    <p class="mb-2 font-monospace"><i class="fa-solid fa-phone me-2 text-success"></i>{{ selectedReq.phone }}</p>
                    <div class="mt-3">
                      <button class="btn btn-sm btn-outline-primary me-2"><i class="fa-solid fa-phone-volume me-1"></i> Gọi ngay</button>
                      <button class="btn btn-sm btn-outline-info"><i class="fa-solid fa-location-crosshairs me-1"></i> Xem bản đồ</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Recommendation / Team Selection -->
              <h6 class="fw-bold mb-3 border-bottom pb-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-users-gear text-primary"></i>
                Đề xuất đội cứu hộ
                <span class="badge bg-primary rounded-pill ms-2">{{ sortedAvailableTeams.length }}</span>
                <span v-if="sameDistrictCount > 0" class="badge bg-success rounded-pill ms-1">
                  <i class="fa-solid fa-map-pin me-1"></i>{{ sameDistrictCount }} cùng quận
                </span>
              </h6>

              <!-- Select All / Deselect All Buttons -->
              <div v-if="!loadingTeams && availableTeams.length > 0 && selectedTeams.length > 0" class="mb-3 d-flex gap-2 justify-content-end">
                <button
                  v-if="selectedTeams.length < sortedAvailableTeams.length"
                  class="btn btn-sm btn-outline-primary"
                  @click="selectAllTeams"
                >
                  <i class="fa-solid fa-check-double me-1"></i>Chọn tất cả ({{ sortedAvailableTeams.length }})
                </button>
                <button
                  class="btn btn-sm btn-outline-secondary"
                  @click="deselectAllTeams"
                >
                  <i class="fa-solid fa-xmark me-1"></i>Bỏ chọn tất cả
                </button>
              </div>

              <!-- Loading teams -->
              <div v-if="loadingTeams" class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Đang tải danh sách đội cứu hộ...
              </div>

              <div v-else-if="availableTeams.length === 0" class="alert alert-warning mb-3">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>Hiện không có đội cứu hộ nào sẵn sàng. Vui lòng chờ một lát.
              </div>

              <!-- Danh sách đội cứu hộ - LUÔN hiển thị khi có dữ liệu -->
              <div v-else class="row g-3">
                <div
                  class="col-md-6"
                  v-for="team in sortedAvailableTeams"
                  :key="team.id"
                >
                  <div
                    class="card team-card h-100 border-2 transition-all cursor-pointer bg-white"
                    :class="{
                      'border-primary ring-focus shadow-lg' : isTeamSelected(team.id),
                      'border-light bg-light opacity-75' : team.trang_thai && team.trang_thai !== 'SanSang' && team.trang_thai !== 'Sẵn sàng'
                    }"
                    @click="selectTeam(team)"
                  >
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                          <div class="avatar bg-gradient-primary text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 40px; height: 40px; font-weight: bold; font-size: 14px;">
                            {{ getTeamInitial(team) }}
                          </div>
                          <div>
                            <h6 class="fw-bold mb-0 text-truncate" style="max-width: 150px;" :title="team.ten_co">{{ team.ten_co }}</h6>
                            <small class="text-muted"><i class="fa-solid fa-map me-1"></i>{{ team.khu_vuc_quan_ly || 'Chưa phân khu' }}</small>
                          </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1">
                          <span
                            class="badge rounded-pill px-3 py-2"
                            :class="getTeamStatusBadge(team.trang_thai)"
                          >
                            <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i>
                            {{ getTeamStatusLabel(team.trang_thai) }}
                          </span>
                          <span v-if="getDistrictMatch(team)" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                            <i class="fa-solid fa-location-dot me-1"></i>Cùng quận
                          </span>
                        </div>
                      </div>

                      <div class="mt-3 small">
                        <div class="d-flex flex-wrap gap-2">
                          <span class="badge bg-light text-dark border px-2 py-1" v-if="team.thanh_viens">
                            <i class="fa-solid fa-users me-1 text-primary"></i>{{ team.thanh_viens.length }} thành viên
                          </span>
                          <span class="badge bg-light text-dark border px-2 py-1" v-if="team.tai_nguyens && team.tai_nguyens.length">
                            <i class="fa-solid fa-truck me-1 text-success"></i>{{ team.tai_nguyens.map(t => t.ten_tai_nguyen).join(', ') }}
                          </span>
                        </div>
                      </div>
                    </div>
                    <!-- Indicator selected -->
                    <div class="position-absolute top-0 end-0 p-2" v-if="isTeamSelected(team.id)">
                      <i class="fa-solid fa-circle-check text-primary fs-5 bg-white rounded-circle"></i>
                    </div>
                    <!-- Selection indicator line -->
                    <div class="position-absolute bottom-0 start-0 end-0" v-if="isTeamSelected(team.id)" style="height: 3px; background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
                  </div>
                </div>
              </div>

            </div>

            <div class="card-footer bg-white border-top p-3 d-flex justify-content-end gap-2 align-items-center">
              <span class="text-muted small me-auto" v-if="selectedTeams.length > 0">
                Đã chọn: <strong class="text-primary">{{ selectedTeams.length }} đội</strong>
              </span>
              <span class="text-warning small me-auto" v-else><i class="fa-solid fa-triangle-exclamation me-1"></i>Vui lòng chọn ít nhất 1 đội cứu hộ sẵn sàng</span>

              <button class="btn btn-light" @click="selectedReq = null">Hủy</button>
              <button class="btn btn-primary px-4 fw-bold shadow-sm" :disabled="selectedTeams.length === 0 || assigning" @click="assignTask">
                <span v-if="assigning">
                  <span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang phân công...
                </span>
                <span v-else>
                  <i class="fa-solid fa-paper-plane me-2"></i>Chốt Phân Công ({{ selectedTeams.length }})
                </span>
              </button>
            </div>
          </template>

          <template v-else>
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-5 opacity-75 h-100">
              <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mb-4" style="width: 100px; height: 100px;">
                <i class="fa-solid fa-hand-pointer fs-1 text-primary" style="animation: bounce 2s infinite;"></i>
              </div>
              <h4 class="text-secondary fw-bold">Chưa chọn yêu cầu</h4>
              <p class="text-muted mx-auto" style="max-width: 300px;">Vui lòng chọn một yêu cầu từ danh sách bên trái để xem chi tiết và tiến hành phân công lực lượng.</p>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescueRequestAPI, rescueTeamAPI, assignmentAPI } from "../../../services/api";

const BASE_URL = 'http://localhost:8000';

const SEVERITY_MAP = {
  'CRITICAL': { label: 'Khẩn cấp', badge: 'bg-danger', border: 'border-danger' },
  'HIGH':     { label: 'Nghiêm trọng', badge: 'bg-warning text-dark', border: 'border-warning' },
  'MEDIUM':   { label: 'Theo dõi', badge: 'bg-info bg-opacity-75 text-dark', border: 'border-info' },
  'LOW':      { label: 'Thấp', badge: 'bg-secondary', border: 'border-secondary' },
};

const SEVERITY_NUM_MAP = {
  4: 'CRITICAL',
  3: 'HIGH',
  2: 'MEDIUM',
  1: 'LOW',
};

const STATUS_TEAM_MAP = {
  'SAN_SANG': 'SanSang',
  'DANG_BAN': 'DangBan',
  'DANG_XU_LY': 'DangXuLy',
  'CHO_XU_LY': 'ChoXuLy',
  'Sẵn sàng': 'SanSang',
  'Đang bận': 'DangBan',
  'Đang xử lý': 'DangXuLy',
};

function normalizeText(value, fallback = "") {
  if (value === null || value === undefined) return fallback;
  if (typeof value === "object") {
    return normalizeText(
      value.ten_danh_muc || value.ten_loai_su_co || value.ten_co || value.title || value.name || fallback,
      fallback
    );
  }
  return String(value).trim();
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
      item.nguoi_dung?.so_dien_thoai || item.nguoi_dung?.phone || item.phone || "Không có"
    );
    return {
      id: item.id_yeu_cau || item.id || "-",
      raw: item,
      title: typeName,
      location: normalizeText(item.vi_tri_dia_chi || item.dia_chi || "Không có địa chỉ"),
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
    };
  });
}

function parseTeams(payload) {
  const rawData = payload?.data?.data || payload?.data || payload || [];
  const items = Array.isArray(rawData) ? rawData : [];
  return items.map((item) => ({
    id: item.id_doi_cuu_ho || item.id,
    raw: item,
    ten_co: normalizeText(item.ten_co || item.name || "Đội không tên"),
    khu_vuc_quan_ly: normalizeText(item.khu_vuc_quan_ly || item.area || ""),
    so_dien_thoai_hotline: item.so_dien_thoai_hotline || item.phone || "",
    trang_thai: item.trang_thai || "SanSang",
    thanh_viens: Array.isArray(item.thanh_viens) ? item.thanh_viens : [],
    tai_nguyens: Array.isArray(item.tai_nguyens) ? item.tai_nguyens : [],
    vi_tri_lat: item.vi_tri_lat || null,
    vi_tri_lng: item.vi_tri_lng || null,
  }));
}

export default {
  name: "AdminAssignments",
  data() {
    return {
      searchQuery: '',
      selectedReq: null,
      selectedTeams: [], // Multiple selection
      selectedSeverityFilter: 'all',

      pendingRequests: [],
      teams: [],
      loadingRequests: false,
      loadingTeams: false,
      assigning: false,
      error: '',

      severityFilters: [
        { value: 'all', label: 'Tất cả', activeClass: 'bg-primary text-white' },
        { value: 'CRITICAL', label: 'Khẩn cấp', activeClass: 'bg-danger text-white' },
        { value: 'HIGH', label: 'Nghiêm trọng', activeClass: 'bg-warning text-dark' },
        { value: 'MEDIUM', label: 'Theo dõi', activeClass: 'bg-info text-dark' },
        { value: 'LOW', label: 'Thấp', activeClass: 'bg-secondary text-white' },
      ],
    };
  },
  computed: {
    filteredRequests() {
      let reqs = this.pendingRequests;
      if (this.selectedSeverityFilter !== 'all') {
        reqs = reqs.filter(r => {
          const sev = String(r.mucDoKhanCap || '').toUpperCase();
          return sev === this.selectedSeverityFilter;
        });
      }
      if (this.searchQuery) {
        const q = this.searchQuery.toLowerCase();
        reqs = reqs.filter(r =>
          r.title.toLowerCase().includes(q) ||
          r.location.toLowerCase().includes(q) ||
          String(r.id).toLowerCase().includes(q)
        );
      }
      return reqs;
    },
    availableTeams() {
      return this.teams.filter(t => {
        const st = (t.trang_thai || '').toUpperCase().replace(/\s+/g, '_');
        return st === 'SAN_SANG' || st === 'SẵN_SÀNG' || st === 'SAN_SANG' || t.trang_thai === 'Sẵn sàng';
      });
    },
    busyTeams() {
      return this.teams.filter(t => {
        const st = (t.trang_thai || '').toUpperCase().replace(/\s+/g, '_');
        return st === 'DANG_BAN' || st === 'ĐANG_BẬN' || st === 'DANG_XU_LY' || st === 'ĐANG_XỬ_LÝ';
      });
    },
    availableTeamsCount() {
      return this.availableTeams.length;
    },
    busyTeamsCount() {
      return this.busyTeams.length;
    },
    sortedAvailableTeams() {
      if (!this.selectedReq) return [...this.availableTeams];
      
      // Extract district from request location
      const reqDistrict = this.extractDistrict(this.selectedReq.location);
      
      return [...this.availableTeams].sort((a, b) => {
        const teamADistrict = this.normalizeDistrict(a.khu_vuc_quan_ly);
        const teamBDistrict = this.normalizeDistrict(b.khu_vuc_quan_ly);
        
        const aMatch = reqDistrict && teamADistrict === reqDistrict;
        const bMatch = reqDistrict && teamBDistrict === reqDistrict;
        
        if (aMatch && !bMatch) return -1;
        if (!aMatch && bMatch) return 1;
        return 0;
      });
    },
    sameDistrictCount() {
      if (!this.selectedReq) return 0;
      const reqDistrict = this.extractDistrict(this.selectedReq.location);
      if (!reqDistrict) return 0;
      
      return this.availableTeams.filter(team => {
        const teamDistrict = this.normalizeDistrict(team.khu_vuc_quan_ly);
        return teamDistrict === reqDistrict;
      }).length;
    },
  },
  async created() {
    await this.initData();
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
    selectedReq() {
      this.selectedTeams = [];
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
    async initData() {
      this.error = '';
      await Promise.all([this.loadRequests(), this.loadTeams()]);
    },
    async loadRequests() {
      this.loadingRequests = true;
      try {
        const response = await rescueRequestAPI.getList();
        const all = parseRequests(response?.data || response);
        // Chỉ lấy yêu cầu CHƯA phân công hoặc đang chờ xử lý
        this.pendingRequests = all.filter(r => {
          const st = (r.trangThai || '').toUpperCase().replace(/\s+/g, '_');
          return st === 'CHO_XU_LY' || st === 'MOI' || st === 'WAITING';
        });
      } catch (error) {
        console.error('Lỗi tải yêu cầu:', error);
        this.error = 'Không tải được danh sách yêu cầu. Vui lòng thử lại.';
      } finally {
        this.loadingRequests = false;
      }
    },
    async loadTeams() {
      this.loadingTeams = true;
      try {
        // Lấy TẤT CẢ đội cứu hộ (không phân trang)
        const response = await rescueTeamAPI.getList({ get_all: true });
        const rawData = response?.data || response;
        this.teams = parseTeams(rawData);
        console.log('Loaded teams count:', this.teams.length);
      } catch (error) {
        console.error('Lỗi tải đội cứu hộ:', error);
      } finally {
        this.loadingTeams = false;
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
        const st = (team.trang_thai || '').toUpperCase().replace(/\s+/g, '_');
        if (st === 'SAN_SANG' || st === 'SẴN_SÀNG' || team.trang_thai === 'Sẵn sàng') {
          this.selectedTeams.push(team);
        }
      }
    },
    getTeamInitial(team) {
      return team.ten_co ? team.ten_co.charAt(0).toUpperCase() : '?';
    },
    getTeamStatusBadge(status) {
      if (!status) return 'bg-secondary';
      const st = String(status).toUpperCase().replace(/\s+/g, '_');
      if (st === 'SAN_SANG' || st === 'SẴN_SÀNG' || status === 'Sẵn sàng') return 'bg-success';
      if (st === 'DANG_BAN' || st === 'ĐANG_BẬN' || status === 'Đang bận') return 'bg-secondary';
      if (st === 'DANG_XU_LY' || st === 'ĐANG_XỬ_LÝ') return 'bg-warning text-dark';
      return 'bg-secondary';
    },
    getTeamStatusLabel(status) {
      if (!status) return 'Không rõ';
      const st = String(status).toUpperCase().replace(/\s+/g, '_');
      if (st === 'SAN_SANG' || st === 'SẴN_SÀNG' || status === 'Sẵn sàng') return 'Sẵn sàng';
      if (st === 'DANG_BAN' || st === 'ĐANG_BẬN' || status === 'Đang bận') return 'Đang bận';
      if (st === 'DANG_XU_LY' || st === 'ĐANG_XỬ_LÝ') return 'Đang xử lý';
      return normalizeText(status, 'Không rõ');
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
    toggleSeverityFilter(value) {
      this.selectedSeverityFilter = value;
    },
    selectAllTeams() {
      this.selectedTeams = [...this.sortedAvailableTeams];
    },
    deselectAllTeams() {
      this.selectedTeams = [];
    },
    normalizeDistrict(value) {
      if (!value) return '';
      // Normalize: "Quận 1", "Q.1", "Quận Bình Thạnh" -> "quận 1", "quận bình thạnh"
      let normalized = String(value).toLowerCase().trim();
      // Handle common variations
      normalized = normalized.replace(/^q\.\s*/i, 'quận ');
      normalized = normalized.replace(/^quận\s+/i, 'quận ');
      return normalized;
    },
    extractDistrict(location) {
      if (!location) return null;
      const loc = String(location).toLowerCase();
      // Try to match "quận X" pattern
      const match = loc.match(/quận\s+([a-z0-9\sđêêôưàảãáạầẩẫấậằẳẵắặẻẽéẹềểễếệỉĩíịòỏóọồổỗốộờỡớợùủúụừửữứựỳỷỹỵ]+)/i);
      if (match && match[1]) {
        return 'quận ' + match[1].trim();
      }
      // Try to match "Q.X" pattern
      const match2 = loc.match(/(?:q\.|q)\s*([a-z0-9\sđêêôưàảãáạầẩẫấậằẳẵắặẻẽéẹềểễếệỉĩíịòỏóọồổỗốộờỡớợùủúụừửữứựỳỷỹỵ]+)/i);
      if (match2 && match2[1]) {
        return 'quận ' + match2[1].trim();
      }
      // Try "Quận" followed by number (with or without space)
      const match3 = loc.match(/quận\s*([0-9]+)/i);
      if (match3 && match3[1]) {
        return 'quận ' + match3[1];
      }
      return null;
    },
    getDistrictMatch(team) {
      if (!this.selectedReq) return false;
      const reqDistrict = this.extractDistrict(this.selectedReq.location);
      if (!reqDistrict) return false;
      const teamDistrict = this.normalizeDistrict(team.khu_vuc_quan_ly);
      return teamDistrict === reqDistrict;
    },
    async assignTask() {
      if (!this.selectedReq || this.selectedTeams.length === 0) return;

      this.assigning = true;
      try {
        const reqId = this.selectedReq.id;

        // Assign to all selected teams
        for (const team of this.selectedTeams) {
          // 1. Tạo phân công
          await assignmentAPI.create({
            id_yeu_cau: reqId,
            id_doi_cuu_ho: team.id,
            mo_ta: `Phân công đội ${team.ten_co} xử lý yêu cầu #${reqId}`,
            trang_thai_nhiem_vu: 'DANG_XU_LY',
          });

          // 2. Cập nhật trạng thái đội → Đang bận
          await rescueTeamAPI.update(team.id, { trang_thai: 'DANG_BAN' });

          // 3. Cập nhật UI
          const teamIdx = this.teams.findIndex(t => t.id === team.id);
          if (teamIdx !== -1) {
            this.teams[teamIdx].trang_thai = 'Đang bận';
          }
        }

        // 4. Cập nhật trạng thái yêu cầu → DANG_XU_LY
        await rescueRequestAPI.changeStatus(reqId, { trang_thai: 'DANG_XU_LY' });

        // 5. Toast thành công
        const teamNames = this.selectedTeams.map(t => t.ten_co).join(', ');
        this.$toast?.success?.(`Đã phân công thành công ${this.selectedTeams.length} đội (${teamNames}) cho yêu cầu #${reqId}`, {
          position: 'top-right',
          duration: 3000,
        });

        // 6. Cập nhật UI - xóa request khỏi danh sách
        this.pendingRequests = this.pendingRequests.filter(r => r.id !== reqId);

        this.selectedReq = null;
        this.selectedTeams = [];
      } catch (error) {
        console.error('Lỗi phân công:', error);
        this.$toast?.error?.('Phân công thất bại. Vui lòng thử lại.', {
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
.assignments-page {
  background: linear-gradient(135deg, #e0f2fe 0%, #fef3c7 50%, #fce7f3 100%);
  min-height: calc(100vh - 60px);
  position: relative;
}

.assignments-page::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background:
    radial-gradient(circle at 20% 80%, rgba(14, 165, 233, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(168, 85, 247, 0.1) 0%, transparent 50%);
  pointer-events: none;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
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

.list-queue {
  overflow-y: auto;
}

.request-item:hover {
  background-color: #fef3c7;
  cursor: pointer;
}

.selected-req {
  background: linear-gradient(90deg, #dbeafe 0%, #bfdbfe 100%) !important;
  border-left: 4px solid #0284c7 !important;
  box-shadow: 0 2px 8px rgba(14, 165, 233, 0.15);
}

.team-card {
  border-radius: 12px;
  border-width: 2px !important;
}
.team-card:hover:not(.opacity-75) {
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.15) !important;
  transform: translateY(-3px);
  border-color: #3b82f6 !important;
  background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%) !important;
}

.ring-focus {
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15) !important;
}

.cursor-pointer {
  cursor: pointer;
}

.transition-all {
  transition: all 0.2s ease;
}

.bg-primary-subtle {
  background-color: #dbeafe !important;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.spin-animation {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e0e7ff;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.custom-alert-danger {
  background-color: #fef2f2;
  color: #991b1b;
  border-left: 5px solid #ef4444 !important;
}

/* Gradient Stats Cards */
.bg-gradient-warning {
  background: linear-gradient(135deg, #fcd34d 0%, #f59e0b 50%, #ea580c 100%);
}
.bg-gradient-success {
  background: linear-gradient(135deg, #86efac 0%, #22c55e 50%, #16a34a 100%);
}
.bg-gradient-primary {
  background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #1d4ed8 100%);
}
.bg-gradient-warning-shine {
  background: linear-gradient(135deg, transparent 30%, rgba(255,255,255,0.2) 100%);
}
.bg-gradient-success-shine {
  background: linear-gradient(135deg, transparent 30%, rgba(255,255,255,0.2) 100%);
}
.bg-gradient-primary-shine {
  background: linear-gradient(135deg, transparent 30%, rgba(255,255,255,0.2) 100%);
}
.text-warning-dark { color: #78350f !important; }
.text-success-dark { color: #14532d !important; }
.text-primary-dark { color: #1e3a5f !important; }
.z-1 { z-index: 1; }
</style>
