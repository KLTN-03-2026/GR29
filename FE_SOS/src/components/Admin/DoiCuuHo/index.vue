<template>
  <div class="dashboard-container py-4 px-3 px-md-4 h-100">
    <!-- Header -->
    <div class="row align-items-end mb-4 g-3">
      <div class="col-lg-6">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="brand-icon-box bg-white text-primary border border-primary-subtle shadow-sm">
            <i class="fa-solid fa-people-roof fs-4"></i>
          </div>
          <div>
            <h2 class="mb-0 fw-bolder page-title text-dark">Đội Cứu Hộ </h2>
            <p class="text-muted mb-0 page-subtitle fw-medium">Quản lý lực lượng</p>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="d-flex justify-content-lg-end gap-3 flex-wrap">
          <div class="stat-chip bg-white border border-light-subtle shadow-sm">
            <i class="fa-solid fa-people-group text-primary"></i>
            <span class="stat-chip-label">Tổng đội</span>
            <span class="stat-chip-value">{{ teams.length }}</span>
          </div>

          <div class="stat-chip bg-white border border-light-subtle shadow-sm">
            <i class="fa-solid fa-circle-check text-success"></i>
            <span class="stat-chip-label">Sẵn sàng</span>
            <span class="stat-chip-value">{{ availableTeams }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-nav-wrapper mb-4">
      <div class="d-flex gap-2 flex-wrap">
        <button v-for="tab in tabs" :key="tab.id" class="tab-btn" :class="{ active: activeTab === tab.id }"
          @click="activeTab = tab.id">
          <i :class="tab.icon"></i>
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- ========== TAB 1: DOI CUU HO ========== -->
    <div v-if="activeTab === 'teams'">
      <div class="card panel-card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h5 class="fw-bolder text-dark mb-0">
              <i class="fa-solid fa-people-group text-primary me-2"></i>Danh sách đội cứu hộ
            </h5>
            <div class="d-flex gap-2 align-items-center">
              <div class="search-box">
                <span class="search-icon"><i class="fa-solid fa-search"></i></span>
                <input v-model="searchTeams" type="text" class="form-control" placeholder="Tìm tên đội, khu vực...">
              </div>
              <button class="btn btn-primary fw-bolder d-flex align-items-center gap-2 px-3" @click="openAddTeamModal">
                <i class="fa-solid fa-plus"></i>
                <span class="d-none d-sm-inline">Thêm đội</span>
              </button>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div v-if="loadingTeams" class="text-center py-5">
            <div class="spinner"></div>
            <p class="text-muted mt-2 fw-medium">Đang tải dữ liệu đội cứu hộ...</p>
          </div>
          <div v-else-if="filteredTeams.length === 0" class="text-center py-5">
            <div class="empty-icon-wrap mx-auto mb-3">
              <i class="fa-solid fa-users-slash fs-1"></i>
            </div>
            <h6 class="fw-bold text-dark">Chưa có đội cứu hộ nào</h6>
            <p class="text-muted small">Nhấn "Thêm đội" để bắt đầu tạo đội cứu hộ mới</p>
          </div>
          <div v-else class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="fw-bolder text-muted text-uppercase small ps-4">#</th>
                  <th class="fw-bolder text-muted text-uppercase small">Tên đội</th>
                  <th class="fw-bolder text-muted text-uppercase small">Khu vực</th>
                  <th class="fw-bolder text-muted text-uppercase small">Hotline</th>
                  <th class="fw-bolder text-muted text-uppercase small">Thành viên</th>
                  <th class="fw-bolder text-muted text-uppercase small">Trạng thái</th>
                  <th class="fw-bolder text-muted text-uppercase small text-end pe-4">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(team, index) in filteredTeams" :key="team.id_doi_cuu_ho" class="table-row-hover">
                  <td class="ps-4 text-muted small fw-bold">{{ index + 1 }}</td>
                  <td>
                    <div class="fw-bolder text-dark">{{ team.ten_doi }}</div>
                    <div class="small text-muted" v-if="team.mo_ta">{{ team.mo_ta }}</div>
                  </td>
                  <td>
                    <span class="badge bg-primary-subtle text-primary-emphasis fw-medium">
                      <i class="fa-solid fa-map-pin me-1"></i>{{ team.khu_vuc_quan_ly }}
                    </span>
                  </td>
                  <td class="small text-muted">{{ team.so_dien_thoai_hotline || '—' }}</td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="member-avatars">
                        <span v-for="(m, i) in (team.thanhViens || []).slice(0, 3)" :key="i" class="member-avatar">{{
                          m.ho_ten ? m.ho_ten.charAt(0).toUpperCase() : '?' }}</span>
                      </div>
                      <span class="badge bg-secondary-subtle text-secondary-emphasis small fw-medium">
                        {{ (team.thanhViens || []).length }} thành viên
                      </span>
                    </div>
                  </td>
                  <td>
                    <span class="badge rounded-pill fw-medium" :class="getTeamStatusBadge(team.trang_thai)">
                      {{ team.trang_thai || 'Không rõ' }}
                    </span>
                  </td>
                  <td class="text-end pe-4">
                    <div class="d-flex gap-1 justify-content-end">
                      <button class="btn btn-sm btn-outline-primary action-btn" @click="openEditTeamModal(team)"
                        title="Sửa">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger action-btn" @click="confirmDeleteTeam(team)"
                        title="Xóa">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>



    <!-- ========== MODAL: ADD/EDIT TEAM ========== -->
    <div v-if="showTeamModal" class="modal-overlay" @click.self="closeTeamModal">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-xl">
          <div class="modal-header border-bottom-0 bg-white py-3 px-4">
            <h5 class="modal-title fw-bolder text-dark">
              <i class="fa-solid fa-people-group text-primary me-2"></i>
              {{ editingTeam ? 'Sửa thông tin đội' : 'Thêm đội cứu hộ mới' }}
            </h5>
            <button type="button" class="btn-close" @click="closeTeamModal"></button>
          </div>
          <div class="modal-body p-4 bg-white">
            <div class="row g-4">
              <div class="col-md-6">
                <label class="">Tên đội <span class="text-danger">*</span></label>
                <input v-model="teamForm.ten_doi" type="text" class="form-control custom-input"
                  placeholder="VD: Đội cứu hộ số 1">
              </div>
              <div class="col-md-6">
                <label class="">Hotline</label>
                <input v-model="teamForm.so_dien_thoai_hotline" type="text" class="form-control custom-input"
                  placeholder="VD: 0123456789">
              </div>
              <div class="col-md-6">
                <label class="">Khu vực quản lý <span class="text-danger">*</span></label>
                <input v-model="teamForm.khu_vuc_quan_ly" type="text" class="form-control custom-input"
                  placeholder="VD: Quận Hải Châu, Đà Nẵng">
              </div>
              <div class="col-md-6">
                <label class="form-label">Trạng thái</label>
                <select v-model="teamForm.trang_thai" class="form-select custom-input">
                  <option value="SAN_SANG">Sẵn sàng</option>
                  <option value="BAN_CHI_VAO">Bận chỉ đạo</option>
                  <option value="TAM_NGHI">Tạm nghỉ</option>
                  <option value="BAN_PHAN_CONG">Bàn phân công</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Mô tả</label>
                <textarea v-model="teamForm.mo_ta" class="form-control custom-input" rows="2"
                  placeholder="Mô tả ngắn về đội cứu hộ..."></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Vị trí trên bản đồ</label>
                <p class=" small mb-2">Click vào bản đồ để chọn vị trí hoặc nhập tọa độ</p>
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label small  fw-medium">Vĩ độ (Lat)</label>
                    <input v-model.number="teamForm.vi_tri_lat" type="number" step="0.000001"
                      class="form-control custom-input" placeholder="16.0544" @input="onTeamCoordsChange">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small  fw-medium">Kinh độ (Lng)</label>
                    <input v-model.number="teamForm.vi_tri_lng" type="number" step="0.000001"
                      class="form-control custom-input" placeholder="108.2022" @input="onTeamCoordsChange">
                  </div>
                  <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100 fw-medium" @click="centerMapOnTeam">
                      <i class="fa-solid fa-crosshairs me-1"></i>Cập nhật map
                    </button>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="mini-map-container rounded-3 overflow-hidden border" ref="teamMapContainer"
                  style="height: 280px;"></div>
                <p class=" small mt-2 mb-0 text-center">
                  <i class="fa-solid fa-hand-pointer me-1"></i>Click trên bản đồ để chọn vị trí đội cứu hộ
                </p>
              </div>
            </div>
          </div>
          <div class="modal-footer border-top-0 bg-light py-3 px-4">
            <button type="button" class="btn btn-light fw-medium px-4" @click="closeTeamModal">Hủy</button>
            <button type="button" class="btn btn-primary fw-bolder px-4" :disabled="submittingTeam" @click="submitTeam">
              <span v-if="submittingTeam"><i class="fa-solid fa-spinner fa-spin me-2"></i>Đang xử lý...</span>
              <span v-else>
                <i :class="editingTeam ? 'fa-solid fa-floppy-disk' : 'fa-solid fa-plus'" class="me-2"></i>
                {{ editingTeam ? 'Lưu thay đổi' : 'Tạo đội mới' }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    



    <!-- ========== CONFIRM DELETE MODAL ========== -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-xl">
          <div class="modal-body p-4 text-center">
            <div class="mb-3">
              <div class="delete-icon-wrap mx-auto">
                <i class="fa-solid fa-trash text-danger fs-1"></i>
              </div>
            </div>
            <h5 class="fw-bolder text-dark mb-2">Xác nhận xóa?</h5>
            <p class="text-muted mb-0">
              Bạn có chắc muốn xóa <strong>{{ deleteTarget.name }}</strong>?
              <br>Hành động này không thể hoàn tác.
            </p>
          </div>
          <div class="modal-footer border-top-0 bg-light py-3 px-4 justify-content-center gap-2">
            <button type="button" class="btn btn-light fw-medium px-4" @click="showDeleteModal = false">Hủy</button>
            <button type="button" class="btn btn-danger fw-bolder px-4" :disabled="submittingDelete"
              @click="executeDelete">
              <span v-if="submittingDelete"><i class="fa-solid fa-spinner fa-spin me-2"></i>Đang xóa...</span>
              <span v-else><i class="fa-solid fa-trash me-2"></i>Xóa</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
      <div v-for="toast in toasts" :key="toast.id"
        class="toast show align-items-center text-white border-0 mb-2 shadow-lg"
        :class="toast.type === 'success' ? 'bg-success' : toast.type === 'error' ? 'bg-danger' : 'bg-warning'"
        role="alert">
        <div class="d-flex align-items-center gap-2 px-3 py-2">
          <i
            :class="toast.type === 'success' ? 'fa-solid fa-check-circle' : toast.type === 'error' ? 'fa-solid fa-circle-xmark' : 'fa-solid fa-circle-info'"></i>
          <span class="fw-medium">{{ toast.message }}</span>
          <button type="button" class="btn-close btn-close-white ms-2" @click="removeToast(toast.id)"></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { adminResourcesAPI } from "../../../services/api.js";
import { loadOpenMap, createOpenMap, createOpenMapMarker } from "../../../utils/openMap.js";


export default {
  name: "AdminResources",
  data() {
    return {
      activeTab: 'teams',
      tabs: [
        { id: 'teams', label: 'Đội cứu hộ', icon: 'fa-solid fa-people-group' },

      ],

      // Teams
      teams: [],
      loadingTeams: false,
      searchTeams: '',
      allResources: [],
      loadingResources: false,
      searchResources: '',
      filterResourceTeam: '',

      // Warehouse
      warehouseItems: [],
      loadingWarehouse: false,

      // Allocation
      allocationHistory: [],
      loadingAllocation: false,
      allocationForm: {
        id_doi_cuu_ho: '',
        loai_tai_nguyen: '',
        so_luong_cap: null,
        ghi_chu: '',
      },
      submittingAllocation: false,

      // Team modal
      showTeamModal: false,
      editingTeam: null,
      submittingTeam: false,
      teamForm: {
        ten_doi: '',
        khu_vuc_quan_ly: '',
        so_dien_thoai_hotline: '',
        vi_tri_lat: 16.0544,
        vi_tri_lng: 108.2022,
        trang_thai: 'SAN_SANG',
        mo_ta: '',
        email: '',
        mat_khau: '',
      },
      teamMap: null,
      teamMarker: null,

      // Resource modal
      showResourceModal: false,
      editingResource: null,
      submittingResource: false,
      resourceForm: {
        id_doi_cuu_ho: '',
        loai_tai_nguyen: 'Vehicle',
        ten_tai_nguyen: '',
        so_luong: 0,
        trang_thai: 1,
      },

      // Warehouse modal
      showWarehouseModal: false,
      submittingWarehouse: false,
      warehouseForm: {
        loai_tai_nguyen: '',
        ten_hien_thi: '',
        tong_so_luong_hien_tai: 0,
        so_luong_moi: null,
      },

      // Delete modal
      showDeleteModal: false,
      submittingDelete: false,
      deleteTarget: { type: '', id: null, name: '' },

      // Toasts
      toasts: [],
      toastCounter: 0,
    };
  },
  computed: {
    totalResources() {
      return this.allResources.reduce((sum, r) => sum + (parseInt(r.so_luong) || 0), 0);
    },
    availableTeams() {
      return this.teams.filter(t => t.trang_thai === 'SAN_SANG').length;
    },
    filteredTeams() {
      if (!this.searchTeams) return this.teams;
      const q = this.searchTeams.toLowerCase();
      return this.teams.filter(t =>
        (t.ten_doi || '').toLowerCase().includes(q) ||
        (t.khu_vuc_quan_ly || '').toLowerCase().includes(q)
      );
    },
    filteredResources() {
      let list = this.allResources;
      if (this.filterResourceTeam) {
        list = list.filter(r => r.id_doi_cuu_ho == this.filterResourceTeam);
      }
      if (this.searchResources) {
        const q = this.searchResources.toLowerCase();
        list = list.filter(r =>
          (r.ten_tai_nguyen || '').toLowerCase().includes(q) ||
          (r.loai_tai_nguyen || '').toLowerCase().includes(q) ||
          (this.getTeamName(r.id_doi_cuu_ho) || '').toLowerCase().includes(q)
        );
      }
      return list;
    },
    canSubmitAllocation() {
      return this.allocationForm.id_doi_cuu_ho &&
        this.allocationForm.loai_tai_nguyen &&
        this.allocationForm.so_luong_cap > 0;
    },
    canSubmitResource() {
      return this.editingResource
        ? (this.resourceForm.loai_tai_nguyen && this.resourceForm.so_luong >= 0)
        : (this.resourceForm.id_doi_cuu_ho && this.resourceForm.loai_tai_nguyen && this.resourceForm.so_luong >= 0);
    },
  },
  watch: {
    activeTab(tab) {
      if (tab === 'teams') this.loadTeams();
      else if (tab === 'resources') this.loadResources();
      else if (tab === 'warehouse') this.loadWarehouse();
      else if (tab === 'allocation') this.loadAllocationHistory();
    },
    'teamForm.vi_tri_lat'(val) { this.updateTeamMarker(); },
    'teamForm.vi_tri_lng'(val) { this.updateTeamMarker(); },
  },
  async mounted() {
    await this.loadTeams();
  },
  beforeUnmount() {
    if (this.teamMap) {
      this.teamMap.remove();
      this.teamMap = null;
    }
  },
  methods: {
    // ============ API ============
    async loadTeams() {
      this.loadingTeams = true;
      try {
        const res = await adminResourcesAPI.getList({ per_page: 1000 });
        const data = res.data?.data?.data ?? res.data?.data ?? res.data ?? [];
        this.teams = Array.isArray(data) ? data : [];
      } catch (e) {
        console.error('loadTeams', e);
        this.showToast('Không thể tải danh sách đội', 'error');
      } finally {
        this.loadingTeams = false;
      }
    },
    async loadResources() {
      this.loadingResources = true;
      try {
        const res = await adminResourcesAPI.getList({ per_page: 500 });
        const teamsData = res.data?.data?.data ?? res.data?.data ?? [];
        this.teams = Array.isArray(teamsData) ? teamsData : [];

        let allRes = [];
        for (const team of this.teams) {
          try {
            const r = await adminResourcesAPI.getByDoi(team.id_doi_cuu_ho);
            const resources = r.data?.data ?? [];
            allRes = allRes.concat(resources);
          } catch (_) { }
        }
        this.allResources = allRes;
      } catch (e) {
        console.error('loadResources', e);
        this.showToast('Không thể tải tài nguyên', 'error');
      } finally {
        this.loadingResources = false;
      }
    },
    async loadWarehouse() {
      this.loadingWarehouse = true;
      try {
        const res = await adminResourcesAPI.getKho();
        this.warehouseItems = res.data?.data ?? [];
      } catch (e) {
        console.error('loadWarehouse', e);
        this.showToast('Không thể tải dữ liệu kho', 'error');
      } finally {
        this.loadingWarehouse = false;
      }
    },
    async loadAllocationHistory() {
      this.loadingAllocation = true;
      try {
        const res = await adminResourcesAPI.getLichSuCapPhat({ per_page: 50 });
        const data = res.data?.data?.data ?? res.data?.data ?? res.data ?? [];
        this.allocationHistory = Array.isArray(data) ? data : [];
      } catch (e) {
        console.error('loadAllocationHistory', e);
      } finally {
        this.loadingAllocation = false;
      }
    },

    // ============ TEAM MODAL ============
    async openAddTeamModal() {
      this.editingTeam = null;
      this.teamForm = {
        ten_doi: '',
        khu_vuc_quan_ly: '',
        so_dien_thoai_hotline: '',
        vi_tri_lat: 16.0544,
        vi_tri_lng: 108.2022,
        trang_thai: 'SAN_SANG',
        mo_ta: '',
        email: '',
        mat_khau: '',
      };
      this.showTeamModal = true;
      await this.$nextTick();
      this.initTeamMap();
    },
    async openEditTeamModal(team) {
      this.editingTeam = team;
      this.teamForm = {
        id: team.id_doi_cuu_ho,
        ten_doi: team.ten_doi || '',
        khu_vuc_quan_ly: team.khu_vuc_quan_ly || '',
        so_dien_thoai_hotline: team.so_dien_thoai_hotline || '',
        vi_tri_lat: parseFloat(team.vi_tri_lat) || 16.0544,
        vi_tri_lng: parseFloat(team.vi_tri_lng) || 108.2022,
        trang_thai: team.trang_thai || 'SAN_SANG',
        mo_ta: team.mo_ta || '',
        email: team.email || '',
        mat_khau: '',
      };
      this.showTeamModal = true;
      await this.$nextTick();
      this.initTeamMap();
    },
    closeTeamModal() {
      this.showTeamModal = false;
      if (this.teamMap) {
        this.teamMap.remove();
        this.teamMap = null;
        this.teamMarker = null;
      }
    },
    async initTeamMap() {
      await this.$nextTick();
      const container = this.$refs.teamMapContainer;
      if (!container || this.teamMap) return;

      try {
        await loadOpenMap();
        this.teamMap = createOpenMap(container, {
          center: [this.teamForm.vi_tri_lng, this.teamForm.vi_tri_lat],
          zoom: 13,
        });

        this.teamMap.on('load', () => {
          this.updateTeamMarker();
        });

        this.teamMap.on('click', (e) => {
          const { lng, lat } = e.lngLat;
          this.teamForm.vi_tri_lng = parseFloat(lng.toFixed(6));
          this.teamForm.vi_tri_lat = parseFloat(lat.toFixed(6));
        });
      } catch (e) {
        console.error('initTeamMap', e);
      }
    },
    updateTeamMarker() {
      if (!this.teamMap) return;
      if (this.teamMarker) {
        this.teamMarker.setLngLat([this.teamForm.vi_tri_lng, this.teamForm.vi_tri_lat]);
      } else {
        this.teamMarker = createOpenMapMarker({
          type: 'home',
          position: { lng: this.teamForm.vi_tri_lng, lat: this.teamForm.vi_tri_lat },
          title: 'Vị trí đội cứu hộ',
        }).addTo(this.teamMap);
      }
    },
    centerMapOnTeam() {
      if (!this.teamMap || !this.teamForm.vi_tri_lat || !this.teamForm.vi_tri_lng) return;
      this.teamMap.flyTo({
        center: [this.teamForm.vi_tri_lng, this.teamForm.vi_tri_lat],
        zoom: 15,
        duration: 800,
      });
      this.updateTeamMarker();
    },
    onTeamCoordsChange() {
      this.updateTeamMarker();
    },
    async submitTeam() {
      if (!this.teamForm.ten_doi || !this.teamForm.khu_vuc_quan_ly) {
        this.showToast('Vui lòng nhập tên đội và khu vực quản lý', 'error');
        return;
      }
      this.submittingTeam = true;
      try {
        const payload = { ...this.teamForm };
        if (!payload.mat_khau) delete payload.mat_khau;
        if (!this.editingTeam) delete payload.id;

        if (this.editingTeam) {
          await adminResourcesAPI.update(payload);
          this.showToast('Cập nhật đội cứu hộ thành công!', 'success');
        } else {
          await adminResourcesAPI.create(payload);
          this.showToast('Tạo đội cứu hộ mới thành công!', 'success');
        }
        this.closeTeamModal();
        await this.loadTeams();
      } catch (e) {
        console.error('submitTeam', e);
        const msg = e.response?.data?.message || 'Có lỗi xảy ra';
        this.showToast(msg, 'error');
      } finally {
        this.submittingTeam = false;
      }
    },

    // ============ RESOURCE MODAL ============
    openAddResourceModal() {
      this.editingResource = null;
      this.resourceForm = {
        id_doi_cuu_ho: this.filterResourceTeam || '',
        loai_tai_nguyen: 'Vehicle',
        ten_tai_nguyen: '',
        so_luong: 0,
        trang_thai: 1,
      };
      this.showResourceModal = true;
    },
    openEditResourceModal(res) {
      this.editingResource = res;
      this.resourceForm = {
        id: res.id_tai_nguyen,
        id_doi_cuu_ho: res.id_doi_cuu_ho,
        loai_tai_nguyen: res.loai_tai_nguyen || 'Vehicle',
        ten_tai_nguyen: res.ten_tai_nguyen || '',
        so_luong: parseInt(res.so_luong) || 0,
        trang_thai: res.trang_thai ? true : false,
      };
      this.showResourceModal = true;
    },
    closeResourceModal() {
      this.showResourceModal = false;
      this.editingResource = null;
    },
    async submitResource() {
      this.submittingResource = true;
      try {
        const payload = {
          ...this.resourceForm,
          trang_thai: this.resourceForm.trang_thai ? 1 : 0,
        };
        if (this.editingResource) {
          await adminResourcesAPI.updateTaiNguyen(payload);
          this.showToast('Cập nhật tài nguyên thành công!', 'success');
        } else {
          await adminResourcesAPI.createTaiNguyen(payload);
          this.showToast('Thêm tài nguyên mới thành công!', 'success');
        }
        this.closeResourceModal();
        await this.loadResources();
        await this.loadWarehouse();
      } catch (e) {
        console.error('submitResource', e);
        const msg = e.response?.data?.message || 'Có lỗi xảy ra';
        this.showToast(msg, 'error');
      } finally {
        this.submittingResource = false;
      }
    },

    // ============ WAREHOUSE MODAL ============
    openUpdateWarehouseModal(item) {
      this.warehouseForm = {
        loai_tai_nguyen: item.loai_tai_nguyen,
        ten_hien_thi: item.ten_hien_thi,
        tong_so_luong_hien_tai: item.tong_so_luong,
        so_luong_moi: null,
      };
      this.showWarehouseModal = true;
    },
    closeWarehouseModal() {
      this.showWarehouseModal = false;
    },
    async submitWarehouse() {
      if (this.warehouseForm.so_luong_moi === null || this.warehouseForm.so_luong_moi < 0) {
        this.showToast('Vui lòng nhập số lượng hợp lệ', 'error');
        return;
      }
      this.submittingWarehouse = true;
      try {
        await adminResourcesAPI.capNhatKho({
          loai_tai_nguyen: this.warehouseForm.loai_tai_nguyen,
          so_luong: this.warehouseForm.so_luong_moi,
        });
        this.showToast('Cập nhật kho thành công!', 'success');
        this.closeWarehouseModal();
        await this.loadWarehouse();
        await this.loadResources();
      } catch (e) {
        console.error('submitWarehouse', e);
        const msg = e.response?.data?.message || 'Có lỗi xảy ra';
        this.showToast(msg, 'error');
      } finally {
        this.submittingWarehouse = false;
      }
    },

    // ============ ALLOCATION ============
    async submitAllocation() {
      if (!this.canSubmitAllocation) return;
      this.submittingAllocation = true;
      try {
        await adminResourcesAPI.capPhat({
          id_doi_cuu_ho: this.allocationForm.id_doi_cuu_ho,
          loai_tai_nguyen: this.allocationForm.loai_tai_nguyen,
          so_luong_cap: this.allocationForm.so_luong_cap,
          ghi_chu: this.allocationForm.ghi_chu,
        });
        this.showToast('Cấp phát tài nguyên thành công!', 'success');
        this.allocationForm = { id_doi_cuu_ho: '', loai_tai_nguyen: '', so_luong_cap: null, ghi_chu: '' };
        await this.loadAllocationHistory();
        await this.loadResources();
        await this.loadWarehouse();
      } catch (e) {
        console.error('submitAllocation', e);
        const msg = e.response?.data?.message || 'Có lỗi xảy ra';
        this.showToast(msg, 'error');
      } finally {
        this.submittingAllocation = false;
      }
    },

    // ============ DELETE ============
    confirmDeleteTeam(team) {
      this.deleteTarget = { type: 'team', id: team.id_doi_cuu_ho, name: team.ten_doi };
      this.showDeleteModal = true;
    },
    confirmDeleteResource(res) {
      this.deleteTarget = { type: 'resource', id: res.id_tai_nguyen, name: res.ten_tai_nguyen };
      this.showDeleteModal = true;
    },
    async executeDelete() {
      if (!this.deleteTarget.id) return;
      this.submittingDelete = true;
      try {
        if (this.deleteTarget.type === 'team') {
          await adminResourcesAPI.delete({ id: this.deleteTarget.id });
          this.showToast('Xóa đội cứu hộ thành công!', 'success');
          await this.loadTeams();
        } else {
          await adminResourcesAPI.deleteTaiNguyen({ id: this.deleteTarget.id });
          this.showToast('Xóa tài nguyên thành công!', 'success');
          await this.loadResources();
          await this.loadWarehouse();
        }
        this.showDeleteModal = false;
      } catch (e) {
        console.error('executeDelete', e);
        const msg = e.response?.data?.message || 'Có lỗi xảy ra';
        this.showToast(msg, 'error');
      } finally {
        this.submittingDelete = false;
      }
    },

    // ============ HELPERS ============
    getTeamName(id) {
      const t = this.teams.find(x => x.id_doi_cuu_ho == id);
      return t ? t.ten_doi : '—';
    },
    getTeamStatusBadge(status) {
      const map = {
        'SAN_SANG': 'bg-success-subtle text-success-emphasis',
        'BAN_CHI_VAO': 'bg-warning-subtle text-warning-emphasis',
        'TAM_NGHI': 'bg-secondary-subtle text-secondary-emphasis',
        'BAN_PHAN_CONG': 'bg-info-subtle text-info-emphasis',
      };
      return map[status] || 'bg-secondary-subtle text-secondary-emphasis';
    },
    getResourceTypeBadge(type) {
      const map = {
        'Vehicle': 'bg-primary-subtle text-primary-emphasis',
        'Supply': 'bg-success-subtle text-success-emphasis',
        'Medical': 'bg-danger-subtle text-danger-emphasis',
        'Equipment': 'bg-warning-subtle text-warning-emphasis',
      };
      return map[type] || 'bg-secondary-subtle text-secondary-emphasis';
    },
    getResourceTypeIcon(type) {
      const map = {
        'Vehicle': 'fa-solid fa-truck-medical',
        'Supply': 'fa-solid fa-bottle-water',
        'Medical': 'fa-solid fa-kit-medical',
        'Equipment': 'fa-solid fa-screwdriver-wrench',
      };
      return map[type] || 'fa-solid fa-box';
    },
    getResourceTypeLabel(type) {
      return TEN_HIEN_THI[type] || type || '—';
    },
    getDefaultResourceName(type) {
      return TEN_MACC_DINH[type] || '';
    },
    getWarehouseIcon(type) {
      const map = {
        'Vehicle': 'fa-solid fa-truck-medical',
        'Supply': 'fa-solid fa-bottle-water',
        'Medical': 'fa-solid fa-kit-medical',
        'Equipment': 'fa-solid fa-screwdriver-wrench',
      };
      return map[type] || 'fa-solid fa-box';
    },
    getWarehouseIconClass(type) {
      const map = {
        'Vehicle': 'bg-primary-subtle text-primary',
        'Supply': 'bg-success-subtle text-success',
        'Medical': 'bg-danger-subtle text-danger',
        'Equipment': 'bg-warning-subtle text-warning',
      };
      return map[type] || 'bg-secondary-subtle text-secondary';
    },
    getWarehouseDesc(type) {
      const map = {
        'Vehicle': 'Phương tiện cứu hộ giao thông',
        'Supply': 'Lương thực, nước uống, vật dụng',
        'Medical': 'Băng, thuốc, dụng cụ y tế',
        'Equipment': 'Dụng cụ cứu hộ chuyên dụng',
      };
      return map[type] || '';
    },
    getWarehouseUnit(type) {
      const map = { Vehicle: 'chiếc', Supply: 'bộ', Medical: 'bộ', Equipment: 'bộ' };
      return map[type] || 'bộ';
    },
    getWarehouseMax(type) {
      const map = { Vehicle: 20, Supply: 500, Medical: 500, Equipment: 200 };
      return map[type] || 100;
    },
    getWarehouseProgressClass(type) {
      const map = {
        'Vehicle': 'bg-primary',
        'Supply': 'bg-success',
        'Medical': 'bg-danger',
        'Equipment': 'bg-warning',
      };
      return map[type] || 'bg-secondary';
    },
    showToast(message, type = 'success') {
      const id = ++this.toastCounter;
      this.toasts.push({ id, message, type });
      setTimeout(() => this.removeToast(id), 4000);
    },
    removeToast(id) {
      const idx = this.toasts.findIndex(t => t.id === id);
      if (idx > -1) this.toasts.splice(idx, 1);
    },
  },
};
</script>

<style scoped>
/* ===== STAT CHIPS ===== */
.stat-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 12px;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.stat-chip:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.stat-chip i {
  font-size: 1.1rem;
}

.stat-chip-label {
  font-size: 0.8rem;
  color: #6c757d;
  font-weight: 500;
}

.stat-chip-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
}

/* ===== BRAND ICON BOX ===== */
.brand-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* ===== TAB NAVIGATION ===== */
.tab-nav-wrapper {
  background: white;
  padding: 6px;
  border-radius: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #f1f3f4;
  display: inline-flex;
  width: 100%;
}

.tab-btn {
  flex: 1;
  border: none;
  background: transparent;
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 600;
  color: #6c757d;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  white-space: nowrap;
}

.tab-btn:hover {
  background: #f8f9fa;
  color: #1a1a2e;
}

.tab-btn.active {
  background: #0d6efd;
  color: white;
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

/* ===== TABLE STYLES ===== */
.table-row-hover:hover {
  background-color: #f8faff;
}

.table th {
  font-size: 0.72rem;
  padding: 10px 12px;
  border-top: none;
}

.table td {
  padding: 12px 12px;
}

.table {
  font-size: 0.875rem;
}

/* ===== MEMBER AVATARS ===== */
.member-avatars {
  display: flex;
}

.member-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #0d6efd;
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
  margin-left: -8px;
  cursor: pointer;
  transition: transform 0.15s ease;
}

.member-avatar:first-child {
  margin-left: 0;
}

.member-avatar:hover {
  transform: translateY(-2px);
  z-index: 1;
}

/* ===== ACTION BUTTONS ===== */
.action-btn {
  width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.action-btn:hover {
  transform: translateY(-1px);
}

/* ===== WAREHOUSE CARDS ===== */
.warehouse-card {
  border-radius: 16px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: default;
}

.warehouse-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
}

.warehouse-icon {
  width: 52px;
  height: 52px;
  font-size: 1.4rem;
}

.warehouse-qty {
  font-size: 2.5rem;
}

/* ===== EMPTY STATE ===== */
.empty-icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #f1f5f9;
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ===== MODAL STYLES ===== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.shadow-xl {
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

/* ===== DELETE MODAL ===== */
.delete-icon-wrap {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #fee2e2;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ===== SEARCH BOX ===== */
.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box .search-icon {
  position: absolute;
  left: 12px;
  color: #adb5bd;
  font-size: 0.875rem;
  pointer-events: none;
}

.search-box input {
  padding-left: 36px !important;
  border-radius: 10px !important;
  font-size: 0.875rem;
}

/* ===== MINI MAP ===== */
.mini-map-container {
  border: 2px solid #e9ecef;
  transition: border-color 0.2s ease;
}

.mini-map-container:hover {
  border-color: #0d6efd;
}

/* ===== FORM INPUTS ===== */
.custom-input {
  border-radius: 10px !important;
  font-size: 0.875rem;
  border-color: #e9ecef;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.custom-input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
}

/* ===== SPINNER ===== */
.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e9ecef;
  border-top-color: #0d6efd;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.spin {
  animation: spin 0.7s linear infinite;
}

/* ===== TOAST ===== */
.toast {
  border-radius: 10px;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* ===== PAGE STYLES ===== */
.page-title {
  font-size: 1.5rem;
}

.page-subtitle {
  font-size: 0.85rem;
}

.panel-card {
  border-radius: 16px;
  overflow: hidden;
}

/* ===== BADGE VARIANTS ===== */
.bg-primary-subtle {
  background-color: #e7f1ff !important;
  color: #0d6efd !important;
}

.bg-success-subtle {
  background-color: #d1e7dd !important;
  color: #198754 !important;
}

.bg-danger-subtle {
  background-color: #f8d7da !important;
  color: #dc3545 !important;
}

.bg-warning-subtle {
  background-color: #fff3cd !important;
  color: #fd7e14 !important;
}

.bg-secondary-subtle {
  background-color: #e9ecef !important;
  color: #6c757d !important;
}

.bg-info-subtle {
  background-color: #cff4fc !important;
  color: #0dcaf0 !important;
}

.bg-dark-subtle {
  background-color: #d3d3d4 !important;
  color: #495057 !important;
}

.text-primary-emphasis {
  color: #0d6efd !important;
}

.text-success-emphasis {
  color: #198754 !important;
}

.text-danger-emphasis {
  color: #dc3545 !important;
}

.text-warning-emphasis {
  color: #fd7e14 !important;
}

.text-secondary-emphasis {
  color: #6c757d !important;
}

.text-info-emphasis {
  color: #0dcaf0 !important;
}

.text-dark-emphasis {
  color: #495057 !important;
}

.border-light-subtle {
  border-color: #f1f5f9 !important;
}

.border-primary-subtle {
  border-color: #e7f1ff !important;
}
</style>
