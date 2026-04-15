<template>
  <div class="members-wrapper">
    <!-- Header -->
    <div class="members-header px-4 py-3 d-flex align-items-center justify-content-between border-bottom bg-white shadow-sm">
      <div class="d-flex align-items-center">
        <div class="header-icon me-3 d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px;">
          <i class="fa-solid fa-users fs-4"></i>
        </div>
        <div>
          <h5 class="fw-bold mb-0 text-dark">Quản Lý Thành Viên Đội</h5>
          <span class="text-muted small">{{ members.length }} thành viên</span>
        </div>
      </div>
      <button class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" @click="showAddModal = true">
        <i class="fa-solid fa-user-plus me-2"></i>Thêm thành viên
      </button>
    </div>

    <!-- Body -->
    <div class="members-body px-4 py-3 flex-grow-1 overflow-auto">
      <!-- Loading -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-danger" role="status"></div>
        <p class="mt-2 text-muted">Đang tải...</p>
      </div>

      <!-- Empty -->
      <div v-else-if="members.length === 0" class="text-center py-5">
        <div class="mb-3">
          <i class="fa-solid fa-users text-secondary opacity-25" style="font-size: 64px;"></i>
        </div>
        <h6 class="text-secondary fw-bold">Chưa có thành viên nào</h6>
        <p class="text-muted small">Thêm thành viên để quản lý đội</p>
      </div>

      <!-- Member Table -->
      <div v-else class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="bg-light">
              <tr>
                <th class="border-0 fw-bold text-secondary ps-4 py-3">Thành viên</th>
                <th class="border-0 fw-bold text-secondary py-3">Chức vụ</th>
                <th class="border-0 fw-bold text-secondary py-3">Email</th>
                <th class="border-0 fw-bold text-secondary py-3">Điện thoại</th>
                <th class="border-0 fw-bold text-secondary py-3">Trạng thái</th>
                <th class="border-0 fw-bold text-secondary text-end pe-4 py-3">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="member in members" :key="member.id_thanh_vien_doi || member.id" class="align-middle">
                <td class="ps-4">
                  <div class="d-flex align-items-center">
                    <div class="member-avatar me-3 d-flex align-items-center justify-content-center rounded-circle text-white fw-bold" :style="{ background: getAvatarColor(member.ho_ten) }" style="width: 42px; height: 42px; font-size: 16px;">
                      {{ getInitials(member.ho_ten) }}
                    </div>
                    <div>
                      <div class="fw-bold text-dark">{{ member.ho_ten }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">{{ member.vai_tro_trong_doi || '-' }}</span>
                </td>
                <td class="text-secondary">{{ member.email }}</td>
                <td class="text-secondary">{{ member.so_dien_thoai || '-' }}</td>
                <td>
                  <span class="badge rounded-pill px-3 py-2 fw-bold" :class="member.trang_thai === 1 ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'">
                    {{ member.trang_thai === 1 ? 'Hoạt động' : 'Tạm khóa' }}
                  </span>
                </td>
                <td class="text-end pe-4">
                  <div class="d-flex gap-2 justify-content-end">
                    <button class="btn btn-sm btn-outline-primary rounded-3" @click="editMember(member)" title="Sửa">
                      <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="btn btn-sm rounded-3" :class="member.trang_thai === 1 ? 'btn-outline-warning' : 'btn-outline-success'" @click="toggleStatus(member)" :title="member.trang_thai === 1 ? 'Khóa' : 'Mở khóa'">
                      <i :class="member.trang_thai === 1 ? 'fa-solid fa-lock' : 'fa-solid fa-unlock'"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger rounded-3" @click="removeMember(member)" title="Xóa">
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

    <!-- Add/Edit Modal -->
    <div class="modal fade" :class="{ show: showAddModal || showEditModal }" :style="{ display: showAddModal || showEditModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">{{ showEditModal ? 'Cập nhật thành viên' : 'Thêm thành viên mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModals"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Họ tên</label>
              <input type="text" class="form-control rounded-3" v-model="form.ho_ten" placeholder="Nhập họ tên">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" class="form-control rounded-3" v-model="form.email" placeholder="Nhập email">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Số điện thoại</label>
              <input type="text" class="form-control rounded-3" v-model="form.so_dien_thoai" placeholder="Nhập số điện thoại">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Chức vụ trong đội</label>
              <select class="form-select rounded-3" v-model="form.vai_tro_trong_doi">
                <option value="">Chọn chức vụ</option>
                <option value="Đội trưởng">Đội trưởng</option>
                <option value="Đội phó">Đội phó</option>
                <option value="Thành viên">Thành viên</option>
                <option value="Tài xế">Tài xế</option>
                <option value="Y tá">Y tá</option>
              </select>
            </div>
            <div class="mb-3" v-if="!showEditModal">
              <label class="form-label fw-semibold">Mật khẩu</label>
              <input type="password" class="form-control rounded-3" v-model="form.mat_khau" placeholder="Nhập mật khẩu">
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary rounded-pill" @click="closeModals">Hủy</button>
            <button type="button" class="btn btn-danger rounded-pill fw-bold" @click="showEditModal ? updateMember() : addMember()" :disabled="saving">
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
  name: "QuanLyThanhVien",
  data() {
    return {
      loading: false,
      saving: false,
      showAddModal: false,
      showEditModal: false,
      members: [],
      form: {
        id: null,
        ho_ten: "",
        email: "",
        so_dien_thoai: "",
        vai_tro_trong_doi: "",
        mat_khau: "",
      },
    };
  },
  async mounted() {
    await this.fetchMembers();
  },
  methods: {
    async fetchMembers() {
      this.loading = true;
      try {
        const res = await rescuerAPI.getMembers();
        if (res.data?.data?.data) {
          this.members = res.data.data.data;
        } else if (res.data?.data) {
          this.members = res.data.data;
        } else if (Array.isArray(res.data)) {
          this.members = res.data;
        }
      } catch (e) {
        console.error("Lỗi tải thành viên:", e);
      } finally {
        this.loading = false;
      }
    },
    async addMember() {
      if (!this.form.ho_ten || !this.form.email || !this.form.vai_tro_trong_doi) {
        toaster.warning("Vui lòng nhập đầy đủ thông tin");
        return;
      }
      this.saving = true;
      try {
        await rescuerAPI.addMember(this.form);
        toaster.success("Thêm thành viên thành công");
        this.closeModals();
        await this.fetchMembers();
      } catch (e) {
        toaster.error("Không thể thêm thành viên");
      } finally {
        this.saving = false;
      }
    },
    editMember(member) {
      this.form = { ...member, mat_khau: "" };
      this.showEditModal = true;
    },
    async updateMember() {
      if (!this.form.ho_ten || !this.form.email || !this.form.vai_tro_trong_doi) {
        toaster.warning("Vui lòng nhập đầy đủ thông tin");
        return;
      }
      this.saving = true;
      try {
        const id = this.form.id_thanh_vien_doi || this.form.id;
        await rescuerAPI.updateMember(id, this.form);
        toaster.success("Cập nhật thành công");
        this.closeModals();
        await this.fetchMembers();
      } catch (e) {
        toaster.error("Không thể cập nhật");
      } finally {
        this.saving = false;
      }
    },
    async toggleStatus(member) {
      const id = member.id_thanh_vien_doi || member.id;
      try {
        await rescuerAPI.toggleMemberStatus(id);
        toaster.success("Đã cập nhật trạng thái");
        await this.fetchMembers();
      } catch (e) {
        toaster.error("Không thể cập nhật trạng thái");
      }
    },
    async removeMember(member) {
      if (!confirm("Xóa thành viên này?")) return;
      const id = member.id_thanh_vien_doi || member.id;
      try {
        await rescuerAPI.removeMember(id);
        toaster.success("Đã xóa thành viên");
        await this.fetchMembers();
      } catch (e) {
        toaster.error("Không thể xóa thành viên");
      }
    },
    closeModals() {
      this.showAddModal = false;
      this.showEditModal = false;
      this.form = { id: null, ho_ten: "", email: "", so_dien_thoai: "", vai_tro_trong_doi: "", mat_khau: "" };
    },
    getInitials(name) {
      if (!name) return "?";
      return name.split(" ").map((n) => n[0]).join("").substring(0, 2).toUpperCase();
    },
    getAvatarColor(name) {
      const colors = ["#ef4444", "#f97316", "#eab308", "#22c55e", "#3b82f6", "#8b5cf6", "#ec4899"];
      const idx = (name || "").charCodeAt(0) % colors.length;
      return colors[idx];
    },
  },
};
</script>

<style scoped>
.members-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #f8f9fa;
}

.members-header { flex-shrink: 0; }
.members-body { flex: 1; overflow-y: auto; }

.modal.show { background: rgba(0,0,0,0.5); }

tr:hover {
  background-color: rgba(239, 68, 68, 0.03) !important;
}
</style>
