<template>
  <div class="resources-wrapper">
    <!-- Header -->
    <div class="resources-header px-4 py-3 d-flex align-items-center justify-content-between border-bottom bg-white shadow-sm">
      <div class="d-flex align-items-center">
        <div class="header-icon me-3 d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning" style="width: 48px; height: 48px;">
          <i class="fa-solid fa-toolbox fs-4"></i>
        </div>
        <div>
          <h5 class="fw-bold mb-0 text-dark">Tài Nguyên & Trang Thiết Bị</h5>
          <span class="text-muted small">{{ resources.length }} thiết bị trong đội</span>
        </div>
      </div>
      <button class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" @click="showAddModal = true">
        <i class="fa-solid fa-plus me-2"></i>Thêm thiết bị
      </button>
    </div>

    <!-- Stats Row -->
    <div class="stats-row px-4 py-3 border-bottom bg-white">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-truck-medical"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ resources.length }}</div>
            <div class="text-muted small fw-medium">Tổng thiết bị</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-check-circle"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ availableCount }}</div>
            <div class="text-muted small fw-medium">Sẵn sàng</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-clock"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ inUseCount }}</div>
            <div class="text-muted small fw-medium">Đang sử dụng</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ brokenCount }}</div>
            <div class="text-muted small fw-medium">Hỏng / Bảo trì</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="resources-body px-4 py-3 flex-grow-1 overflow-auto">
      <!-- Loading -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-danger" role="status"></div>
        <p class="mt-2 text-muted">Đang tải...</p>
      </div>

      <!-- Empty -->
      <div v-else-if="resources.length === 0" class="text-center py-5">
        <div class="mb-3">
          <i class="fa-solid fa-toolbox text-secondary opacity-25" style="font-size: 64px;"></i>
        </div>
        <h6 class="text-secondary fw-bold">Chưa có thiết bị nào</h6>
        <p class="text-muted small">Thêm thiết bị để quản lý tài nguyên đội</p>
      </div>

      <!-- Resource Cards -->
      <div v-else class="row g-3">
        <div v-for="item in resources" :key="item.id_tai_nguyen" class="col-12 col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 hover-card">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center">
                  <div class="resource-icon me-3 d-flex align-items-center justify-content-center rounded-3 text-white" :class="getTypeClass(item.loai_tai_nguyen)" style="width: 48px; height: 48px;">
                    <i :class="getTypeIcon(item.loai_tai_nguyen)"></i>
                  </div>
                  <div>
                    <h6 class="fw-bold text-dark mb-1">{{ item.ten_tai_nguyen }}</h6>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1 small">{{ item.loai_tai_nguyen }}</span>
                  </div>
                </div>
                <span class="badge rounded-pill px-3 py-2 fw-bold" :class="getStatusClass(item.trang_thai)">
                  {{ getStatusText(item.trang_thai) }}
                </span>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <span class="text-muted small">Số lượng:</span>
                  <span class="fw-bold text-dark ms-2">{{ item.so_luong }}</span>
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-secondary rounded-3" @click="editResource(item)">
                    <i class="fa-solid fa-pen"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger rounded-3" @click="deleteResource(item)">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" :class="{ show: showAddModal || showEditModal }" :style="{ display: showAddModal || showEditModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">{{ showEditModal ? 'Cập nhật thiết bị' : 'Thêm thiết bị mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModals"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Tên thiết bị</label>
              <input type="text" class="form-control rounded-3" v-model="form.ten_tai_nguyen" placeholder="Nhập tên thiết bị">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Loại thiết bị</label>
              <select class="form-select rounded-3" v-model="form.loai_tai_nguyen">
                <option value="">Chọn loại</option>
                <option value="Xe cứu thương">Xe cứu thương</option>
                <option value="Bình chữa cháy">Bình chữa cháy</option>
                <option value="Dụng cụ y tế">Dụng cụ y tế</option>
                <option value="Thiết bị cứu hộ">Thiết bị cứu hộ</option>
                <option value="Phương tiện liên lạc">Phương tiện liên lạc</option>
                <option value="Khác">Khác</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Số lượng</label>
              <input type="number" class="form-control rounded-3" v-model="form.so_luong" min="1" placeholder="Nhập số lượng">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Trạng thái</label>
              <select class="form-select rounded-3" v-model="form.trang_thai">
                <option :value="1">Sẵn sàng</option>
                <option :value="0">Hỏng / Bảo trì</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary rounded-pill" @click="closeModals">Hủy</button>
            <button type="button" class="btn btn-danger rounded-pill fw-bold" @click="showEditModal ? updateResource() : addResource()" :disabled="saving">
              {{ saving ? 'Đang lưu...' : (showEditModal ? 'Cập nhật' : 'Thêm mới') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescuerAPI } from "../../../services/api.js";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

export default {
  name: "TaiNguyen",
  data() {
    return {
      loading: false,
      saving: false,
      showAddModal: false,
      showEditModal: false,
      resources: [],
      form: {
        id: null,
        ten_tai_nguyen: "",
        loai_tai_nguyen: "",
        so_luong: 1,
        trang_thai: 1,
      },
    };
  },
  computed: {
    availableCount() {
      return this.resources.filter(r => r.trang_thai === 1).length;
    },
    inUseCount() {
      return this.resources.filter(r => r.trang_thai === 1).length;
    },
    brokenCount() {
      return this.resources.filter(r => r.trang_thai === 0).length;
    },
    teamId() {
      const teamStr = localStorage.getItem("rescuer_team");
      if (teamStr) {
        try {
          const team = JSON.parse(teamStr);
          return team.id_doi_cuu_ho || team.id;
        } catch { return null; }
      }
      return null;
    },
  },
  async mounted() {
    await this.fetchResources();
  },
  methods: {
    async fetchResources() {
      this.loading = true;
      try {
        if (this.teamId) {
          const res = await rescuerAPI.getTeamResources(this.teamId);
          this.resources = res.data?.data?.data || res.data?.data || res.data || [];
        }
      } catch (e) {
        console.error("Lỗi tải tài nguyên:", e);
      } finally {
        this.loading = false;
      }
    },
    async addResource() {
      if (!this.form.ten_tai_nguyen || !this.form.loai_tai_nguyen) {
        toaster.warning("Vui lòng nhập đầy đủ thông tin");
        return;
      }
      this.saving = true;
      try {
        await rescuerAPI.addTeamResource(this.teamId, this.form);
        toaster.success("Thêm thiết bị thành công");
        this.closeModals();
        await this.fetchResources();
      } catch (e) {
        toaster.error("Không thể thêm thiết bị");
      } finally {
        this.saving = false;
      }
    },
    editResource(item) {
      this.form = { ...item };
      this.showEditModal = true;
    },
    async updateResource() {
      if (!this.form.ten_tai_nguyen || !this.form.loai_tai_nguyen) {
        toaster.warning("Vui lòng nhập đầy đủ thông tin");
        return;
      }
      this.saving = true;
      try {
        await rescuerAPI.updateTeamResource(this.teamId, this.form.id_tai_nguyen, this.form);
        toaster.success("Cập nhật thành công");
        this.closeModals();
        await this.fetchResources();
      } catch (e) {
        toaster.error("Không thể cập nhật");
      } finally {
        this.saving = false;
      }
    },
    async deleteResource(item) {
      if (!confirm("Xóa thiết bị này?")) return;
      try {
        await this.$refs.api.delete(`/tai-nguyen/${item.id_tai_nguyen}`);
        toaster.success("Đã xóa thiết bị");
        await this.fetchResources();
      } catch (e) {
        toaster.error("Không thể xóa");
      }
    },
    closeModals() {
      this.showAddModal = false;
      this.showEditModal = false;
      this.form = { id: null, ten_tai_nguyen: "", loai_tai_nguyen: "", so_luong: 1, trang_thai: 1 };
    },
    getTypeIcon(type) {
      const icons = {
        "Xe cứu thương": "fa-solid fa-truck-medical",
        "Bình chữa cháy": "fa-solid fa-fire-extinguisher",
        "Dụng cụ y tế": "fa-solid fa-kit-medical",
        "Thiết bị cứu hộ": "fa-solid fa-person-drowning",
        "Phương tiện liên lạc": "fa-solid fa-radio",
      };
      return icons[type] || "fa-solid fa-box";
    },
    getTypeClass(type) {
      const classes = {
        "Xe cứu thương": "bg-danger",
        "Bình chữa cháy": "bg-warning",
        "Dụng cụ y tế": "bg-success",
        "Thiết bị cứu hộ": "bg-primary",
        "Phương tiện liên lạc": "bg-info",
      };
      return classes[type] || "bg-secondary";
    },
    getStatusClass(status) {
      return status === 1 ? "bg-success bg-opacity-10 text-success" : "bg-danger bg-opacity-10 text-danger";
    },
    getStatusText(status) {
      return status === 1 ? "Sẵn sàng" : "Hỏng / Bảo trì";
    },
  },
};
</script>

<style scoped>
.resources-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #f8f9fa;
}

.resources-header { flex-shrink: 0; }
.stats-row { flex-shrink: 0; }
.resources-body { flex: 1; overflow-y: auto; }

.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
}

.modal.show { background: rgba(0,0,0,0.5); }
</style>
