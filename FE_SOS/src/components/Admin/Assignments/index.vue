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
            <div class="stat-card shadow-sm border-0">
              <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-clock"></i></div>
              <div class="stat-info">
                <span class="stat-label">Đang chờ</span>
                <h4 class="stat-value">{{ pendingRequests.length }}</h4>
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
                <span class="stat-label">Nhiệm vụ</span>
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
                            <div class="d-flex flex-wrap gap-1" v-if="selectedReq.chiTietSuCo && selectedReq.chiTietSuCo.length > 0">
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
                        <i class="fa-solid fa-user me-1"></i> Người Tới Báo</div>
                      <div
                        class="reporter-avatar bg-primary text-white fw-bolder rounded-circle d-flex align-items-center justify-content-center mb-3 shadow"
                        style="width: 56px; height: 56px; font-size: 24px;">
                        {{ selectedReq.reporter.charAt(0).toUpperCase() }}
                      </div>
                      <h6 class="fw-bolder text-dark mb-1">{{ selectedReq.reporter }}</h6>
                      <div class="fw-bold text-primary mb-3"><i class="fa-solid fa-phone me-1"></i> {{ selectedReq.phone
                        }}</div>
                      <div class="d-flex gap-2 w-100 mt-auto">
                        <button class="btn btn-outline-primary btn-sm fw-medium flex-grow-1"><i
                            class="fa-solid fa-phone me-1"></i>Liên hệ</button>
                        <button class="btn btn-outline-secondary btn-sm fw-medium flex-grow-1"><i
                            class="fa-solid fa-map me-1"></i>Bản đồ</button>
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
                    <span class="text-muted small"><strong>{{ sortedAvailableTeams.length }}</strong> đơn vị sẵn sàng ·
                      ưu tiên: cùng loại + cùng quận &gt; cùng loại &gt; cùng quận &gt; khoảng cách</span>
                  </div>
                  <div class="d-flex gap-2" v-if="availableTeams.length > 0">
                    <button v-if="selectedTeams.length < sortedAvailableTeams.length"
                      class="btn btn-sm btn-light text-primary fw-bold px-3" @click="selectAllTeams">Chọn tất
                      cả</button>
                    <button class="btn btn-sm btn-light text-danger fw-bold px-3" @click="deselectAllTeams">Bỏ
                      chọn</button>
                  </div>
                </div>

                <div v-if="loadingTeams" class="text-center py-5">
                  <div class="spinner-border text-primary mb-2"></div>
                  <div class="text-muted fw-medium">Đang tìm đơn vị phù hợp...</div>
                </div>

                <div v-else-if="availableTeams.length === 0"
                  class="alert custom-alert-warning fw-medium d-flex align-items-center">
                  <i class="fa-solid fa-circle-exclamation fs-5 me-3"></i> Hiện không có đội cứu hộ nào nhàn rỗi. Vui
                  lòng đợi hoặc sử dụng lực lượng dự phòng.
                </div>

                <div class="row g-3" v-else>
                  <div class="col-md-6 col-lg-6 col-xl-6" v-for="team in sortedAvailableTeams" :key="team.id">
                    <div class="team-card h-100"
                      :class="{ 'selected': isTeamSelected(team.id), 'busy': team.trang_thai !== 'SanSang' && team.trang_thai !== 'Sẵn sàng' }"
                      @click="selectTeam(team)">
                      <div class="d-flex gap-3">
                        <div class="team-avatar fw-bold icon-box"
                          :class="isTeamSelected(team.id) ? 'bg-primary text-white' : 'bg-light text-secondary border'">
                          {{ team.ten_co.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                          <div class="d-flex justify-content-between align-items-start">
                            <h6 class="fw-bold text-dark mb-0 text-truncate pe-2">{{ team.ten_co }}</h6>
                            <span class="status-dot" :class="getTeamStatusClass(team.trang_thai)"
                              :title="getTeamStatusLabel(team.trang_thai)"></span>
                          </div>
                          <div class="text-muted small fw-medium mt-1 text-truncate"><i
                              class="fa-solid fa-map-location-dot me-1"></i>{{ team.khu_vuc_quan_ly || 'Hỗ trợ toàn khu vực' }}</div>

                          <div class="d-flex flex-wrap gap-1 mt-2">
                            <span class="meta-tag"><i class="fa-solid fa-users text-primary me-1"></i>{{
                              team.thanh_viens ? team.thanh_viens.length : 0 }} người</span>
                            <span class="meta-tag text-success bg-success-subtle bg-opacity-50" v-if="team.cung_quan"><i
                                class="fa-solid fa-bolt me-1"></i>Cùng quận</span>
                            <span class="meta-tag text-info bg-info-subtle bg-opacity-25"
                              v-if="team.khoang_cach_km !== null"><i class="fa-solid fa-location-arrow me-1"></i>{{
                              team.khoang_cach_km }} km</span>
                            <span class="meta-tag text-danger bg-danger-subtle bg-opacity-25"
                              v-if="team.cung_loai_su_co"><i class="fa-solid fa-fire me-1"></i>Đúng loại</span>
                          </div>
                          <div class="d-flex flex-wrap gap-1 mt-2" v-if="team.loai_su_co && team.loai_su_co.length > 0">
                            <span class="type-tag" v-for="(type, idx) in team.loai_su_co" :key="idx"
                              :class="{ 'type-match': team.cung_loai_su_co && team.loai_su_co.length === 1, 'type-other': !team.cung_loai_su_co || team.loai_su_co.length > 1 }">{{
                              type }}</span>
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
              <div
                class="empty-selection d-flex flex-column justify-content-center align-items-center text-center p-5">
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
import { rescueRequestAPI, rescueTeamAPI, assignmentAPI } from "../../../services/api";

const BASE_URL = 'http://localhost:8000';

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
      item.nguoi_dung?.so_dien_thoai || item.nguoi_dung?.phone || item.phone || "Không có dữ liệu"
    );

    let chiTietSuCo = [];
    if (Array.isArray(item.chi_tiet_loai_su_co)) {
      chiTietSuCo = item.chi_tiet_loai_su_co.map(c => c.ten_chi_tiet || c.ten || c.name || c.label).filter(Boolean);
    } else if (Array.isArray(item.chi_tiet)) {
      chiTietSuCo = item.chi_tiet.map(c => c.ten_chi_tiet || c.ten || c.name || c.label).filter(Boolean);
    } else if (typeof item.chi_tiet === 'string' && item.chi_tiet.trim()) {
      // Handle case where chi_tiet is a string (e.g., comma-separated or single value)
      chiTietSuCo = item.chi_tiet.split(',').map(s => s.trim()).filter(Boolean);
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

    return {
      id: item.id_doi_cuu_ho || item.id,
      raw: item,
      ten_co: normalizeText(item.ten_co || item.ten_doi || item.name || "Đội cứu hộ"),
      khu_vuc_quan_ly: normalizeText(item.khu_vuc_quan_ly || item.area || ""),
      so_dien_thoai_hotline: item.so_dien_thoai_hotline || item.phone || "",
      trang_thai: item.trang_thai || "SanSang",
      thanh_viens: Array.isArray(item.thanh_viens) ? item.thanh_viens : [],
      tai_nguyens: Array.isArray(item.tai_nguyens) ? item.tai_nguyens : [],
      vi_tri_lat: item.vi_tri_lat || null,
      vi_tri_lng: item.vi_tri_lng || null,
      khoang_cach_km: item.khoang_cach_km !== undefined && item.khoang_cach_km !== null ? item.khoang_cach_km : null,
      cung_loai_su_co: item.cung_loai_su_co === true || item.cung_loai_su_co === 1 || item.cung_loai_su_co === '1',
      cung_quan: item.cung_quan === true || item.cung_quan === 1 || item.cung_quan === '1',
      loai_su_co: loaiSuCo,
    };
  });
}

export default {
  name: "AdminAssignments",
  data() {
    return {
      searchQuery: '',
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
        return st === 'SAN_SANG' || st === 'SẴN_SÀNG' || st === 'SAN_SANG' || t.trang_thai === 'Sẵn sàng';
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

      return [...this.availableTeams].sort((a, b) => {
        // Score: cùng type=2, cùng quận=1
        const aScore = (a.cung_loai_su_co ? 2 : 0) + (a.cung_quan ? 1 : 0);
        const bScore = (b.cung_loai_su_co ? 2 : 0) + (b.cung_quan ? 1 : 0);

        if (aScore !== bScore) {
          return bScore - aScore;
        }

        // Cùng score: ưu tiên gần nhất
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
    isTeamAvailable(id) {
      return this.availableTeams.some(t => Number(t.id) === Number(id));
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
    },
    async loadRequests() {
      this.loadingRequests = true;
      try {
        const response = await rescueRequestAPI.getList();
        const all = parseRequests(response?.data || response);
        // Lấy sự cố đang ở trạng thái mới chờ xử lý
        this.pendingRequests = all.filter(r => {
          const st = (r.trangThai || '').toUpperCase().replace(/\s+/g, '_');
          return st === 'CHO_XU_LY' || st === 'MOI' || st === 'WAITING';
        });
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
    getTeamStatusClass(status) {
      if (!status) return 'st-unknown';
      const st = String(status).toUpperCase().replace(/\s+/g, '_');
      if (st === 'SAN_SANG' || st === 'SẴN_SÀNG' || status === 'Sẵn sàng') return 'st-ready';
      if (st === 'DANG_BAN' || st === 'ĐANG_BẬN' || status === 'Đang bận') return 'st-busy';
      if (st === 'DANG_XU_LY' || st === 'ĐANG_XỬ_LÝ') return 'st-processing';
      return 'st-unknown';
    },
    getTeamStatusLabel(status) {
      if (!status) return 'Offline';
      const st = String(status).toUpperCase().replace(/\s+/g, '_');
      if (st === 'SAN_SANG' || st === 'SẴN_SÀNG' || status === 'Sẵn sàng') return 'Sẵn sàng';
      if (st === 'DANG_BAN' || st === 'ĐANG_BẬN' || status === 'Đang bận') return 'Đang bận';
      if (st === 'DANG_XU_LY' || st === 'ĐANG_XỬ_LÝ') return 'Đang xử lý';
      return normalizeText(status, 'Offline');
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
    async assignTask() {
      if (!this.selectedReq || this.selectedTeams.length === 0) return;

      this.assigning = true;
      try {
        const reqId = this.selectedReq.id;

        for (const team of this.selectedTeams) {
          await assignmentAPI.create({
            id_yeu_cau: reqId,
            id_doi_cuu_ho: team.id,
            mo_ta: `Chỉ thị đội ${team.ten_co} xử lý sự cố cấp độ ${this.selectedReq.severityLabel}`,
            trang_thai_nhiem_vu: 'MOI',
          });

          await rescueTeamAPI.update(team.id, { trang_thai: 'DANG_CUU_HO' });

          const teamIdx = this.teams.findIndex(t => t.id === team.id);
          if (teamIdx !== -1) {
            this.teams[teamIdx].trang_thai = 'DangCuuHo';
          }
        }

        await rescueRequestAPI.changeStatus(reqId, { trang_thai: 'DA_PHAN_CONG' });

        this.$toast?.success?.(`Đã gửi lệnh xuất phát cho ${this.selectedTeams.length} đội tới hiện trường.`, {
          position: 'top-right',
          duration: 3000,
        });

        this.pendingRequests = this.pendingRequests.filter(r => r.id !== reqId);
        this.selectedReq = null;
        this.selectedTeams = [];
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
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-3px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
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
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.03), 0 2px 4px -2px rgb(0 0 0 / 0.03) !important;
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
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
  outline: none;
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
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.2) !important;
  transform: translateY(-1px);
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
}

.team-card:hover:not(.busy) {
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgb(0 0 0 / 0.05);
  cursor: pointer;
}

.team-card.busy {
  opacity: 0.6;
  cursor: not-allowed;
}

.team-card.selected {
  border-color: #2563eb;
  background: #eff6ff;
  box-shadow: 0 0 0 1px #2563eb, 0 4px 12px rgba(37, 99, 235, 0.1);
  cursor: pointer;
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

.st-busy {
  background: #f59e0b;
  box-shadow: 0 0 0 3px #fef3c7;
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

.type-tag.type-other {
  background: #f1f5f9;
  color: #475569;
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

.panel-right-full > .card-body {
  overflow: visible !important;
  flex: 1 1 auto;
  min-height: 0;
}

/* Fix: Footer phải luôn hiển thị, không bị overflow cắt */
.panel-right-full > .card-footer,
.panel-footer {
  flex-shrink: 0;
  position: relative;
  z-index: 10;
}

/* Fix: Icon overlay check khi chọn team - đảm bảo pointer-events trên overlay */
.team-card {
  position: relative;
  overflow: visible;
  cursor: pointer;
}

.team-card.selected {
  border-color: #2563eb;
  background: #eff6ff;
  box-shadow: 0 0 0 1px #2563eb, 0 4px 12px rgba(37, 99, 235, 0.1);
  position: relative;
  z-index: 1;
}
</style>
