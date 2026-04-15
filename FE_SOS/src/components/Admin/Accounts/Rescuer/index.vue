<template>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-semibold">Quản lý Rescuer</h5>
                <small class="text-muted">Thành viên đội cứu hộ</small>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="fa-solid fa-user-plus me-1"></i>Thêm
            </button>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Vai trò</th>
                        <th>Đội</th>
                        <th>Trạng thái</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(item, index) in rescuers" :key="item.id_thanh_vien_doi">
                        <td>{{ index + 1 }}</td>
                        <td>{{ item.ho_ten }}</td>
                        <td>{{ item.email }}</td>
                        <td>{{ item.so_dien_thoai }}</td>
                        <td>{{ item.vai_tro_trong_doi }}</td>
                        <td>{{ getTeamName(item.id_doi_cuu_ho) }}</td>

                        <!-- TRẠNG THÁI -->
                        <td>
                            <button class="btn btn-sm w-100"
                                :class="item.trang_thai == 1 ? 'btn-success' : 'btn-danger'"
                                @click="toggleStatus(item)">
                                {{ item.trang_thai == 1 ? 'Hoạt động' : 'Bị khóa' }}
                            </button>
                        </td>

                        <!-- ACTION -->
                        <td class="text-center">
                            <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal"
                                data-bs-target="#modalEdit" @click="selected = { ...item }">
                                <i class="fa fa-pen"></i>
                            </button>

                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalDelete"
                                @click="selected = item">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!rescuers.length">
                        <td colspan="8" class="text-center text-muted py-3">Chưa có dữ liệu</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- THÊM -->
    <div class="modal fade" id="modalAdd">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Thêm Rescuer</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Họ tên <span class="text-danger">*</span></label>
                        <input v-model="form.ho_ten" class="form-control" placeholder="Họ tên">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Email <span class="text-danger">*</span></label>
                        <input v-model="form.email" type="email" class="form-control" placeholder="Email">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Số điện thoại <span class="text-danger">*</span></label>
                        <input v-model="form.so_dien_thoai" class="form-control" placeholder="SĐT">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Vai trò <span class="text-danger">*</span></label>
                        <select v-model="form.vai_tro_trong_doi" class="form-control">
                            <option value="" disabled>Chọn vai trò</option>
                            <option value="Team Leader">Team Leader</option>
                            <option value="Member">Member</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Đội cứu hộ <span class="text-danger">*</span></label>
                        <select v-model="form.id_doi_cuu_ho" class="form-control">
                            <option value="" disabled>Chọn đội cứu hộ</option>
                            <option v-for="team in teams" :key="team.id_doi_cuu_ho" :value="team.id_doi_cuu_ho">
                                {{ team.ten_doi }}
                            </option>
                        </select>
                    </div>
                    <div class="mt-2">
                        <label class="form-label small">Mật khẩu <span class="text-danger">*</span></label>
                        <input v-model="form.mat_khau" type="password" class="form-control" placeholder="Mật khẩu">
                    </div>
                </div>

                <div class="modal-footer">
                    <button ref="closeAddBtn" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button class="btn btn-primary" @click="addRescuer" :disabled="isSubmitting">
                        <span v-if="isSubmitting"><i class="fa-solid fa-spinner fa-spin me-1"></i>Đang lưu...</span>
                        <span v-else>Lưu</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SỬA -->
    <div class="modal fade" id="modalEdit">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Sửa Rescuer</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Họ tên</label>
                        <input v-model="selected.ho_ten" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Email</label>
                        <input v-model="selected.email" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Số điện thoại</label>
                        <input v-model="selected.so_dien_thoai" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Vai trò</label>
                        <select v-model="selected.vai_tro_trong_doi" class="form-control">
                            <option value="Team Leader">Team Leader</option>
                            <option value="Member">Member</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Đội cứu hộ</label>
                        <select v-model="selected.id_doi_cuu_ho" class="form-control">
                            <option v-for="team in teams" :key="team.id_doi_cuu_ho" :value="team.id_doi_cuu_ho">
                                {{ team.ten_doi }}
                            </option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Mật khẩu mới <span class="text-muted">(bỏ trống nếu giữ nguyên)</span></label>
                        <input v-model="selected.mat_khau_moi" type="password" class="form-control" placeholder="Mật khẩu mới">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button class="btn btn-warning" @click="updateRescuer" :disabled="isSubmitting">
                        <span v-if="isSubmitting"><i class="fa-solid fa-spinner fa-spin me-1"></i>Đang cập nhật...</span>
                        <span v-else>Cập nhật</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- XÓA -->
    <div class="modal fade" id="modalDelete">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Xóa Rescuer</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Xác nhận xóa <b>{{ selected.ho_ten }}</b> ?
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-danger" @click="deleteRescuer" data-bs-dismiss="modal">Xóa</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import "../../../../assets/js/bootstrap.bundle.min.js";
import { rescuerAccountAPI, rescueTeamAPI } from "../../../../services/api";

export default {
    data() {
        return {
            rescuers: [],
            teams: [],
            selected: {},
            isSubmitting: false,
            form: {
                ho_ten: '',
                email: '',
                so_dien_thoai: '',
                vai_tro_trong_doi: '',
                id_doi_cuu_ho: '',
                mat_khau: '',
            }
        };
    },
    mounted() {
        this.fetchRescuers();
        this.fetchTeams();
    },
    methods: {
        async fetchRescuers() {
            try {
                const response = await rescuerAccountAPI.getList();
                this.rescuers = response.data?.data ?? response.data;
            } catch (error) {
                console.error("Lỗi khi tải danh sách rescuer", error);
                this.$toast.error("Không thể tải danh sách!");
            }
        },

        async fetchTeams() {
            try {
                const response = await rescueTeamAPI.getList();
                console.log("Teams response:", response.data);

                // Backend trả về: { success, message, data: { current_page, data: [...] } }
                // response.data = { success, message, data: { ...pagination } }
                // response.data.data = { current_page, data: [...], total, ... }
                // response.data.data.data = [...]
                const paginatedData = response.data?.data;

                if (Array.isArray(paginatedData)) {
                    // Trả về array trực tiếp
                    this.teams = paginatedData;
                } else if (paginatedData?.data && Array.isArray(paginatedData.data)) {
                    // Trả về pagination object
                    this.teams = paginatedData.data;
                } else {
                    this.teams = [];
                }
            } catch (error) {
                console.error("Lỗi khi tải danh sách đội", error);
                this.$toast.error("Không thể tải danh sách đội cứu hộ!");
            }
        },

        getTeamName(id) {
            const team = this.teams.find(t => t.id_doi_cuu_ho == id);
            return team ? team.ten_doi : '—';
        },

        async toggleStatus(item) {
            try {
                await rescuerAccountAPI.changeStatus(item.id_thanh_vien_doi);
                item.trang_thai = item.trang_thai == 1 ? 0 : 1;
                this.$toast.success("Đổi trạng thái thành công!");
            } catch (error) {
                console.error("Lỗi khi thay đổi trạng thái", error);
                this.$toast.error("Lỗi thay đổi trạng thái!");
            }
        },

        closeAddModal() {
            const modalEl = document.getElementById('modalAdd');
            if (modalEl) {
                const modal = window.bootstrap?.Modal.getInstance(modalEl);
                modal?.hide();
            }
        },

        async addRescuer() {
            if (!this.form.ho_ten || !this.form.email || !this.form.so_dien_thoai
                || !this.form.vai_tro_trong_doi || !this.form.id_doi_cuu_ho || !this.form.mat_khau) {
                this.$toast.error("Vui lòng điền đầy đủ thông tin!");
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.form.email)) {
                this.$toast.error("Email không hợp lệ!");
                return;
            }

            const phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(this.form.so_dien_thoai)) {
                this.$toast.error("Số điện thoại không hợp lệ (phải là 10 chữ số)!");
                return;
            }

            this.isSubmitting = true;
            try {
                const res = await rescuerAccountAPI.create(this.form);
                if (res.data.status) {
                    this.closeAddModal();
                    this.fetchRescuers();
                    this.resetForm();
                    this.$toast.success("Thêm rescuer thành công!");
                } else {
                    this.$toast.error(res.data.message || "Lỗi khi thêm");
                }
            } catch (error) {
                console.error("Lỗi khi thêm rescuer", error);
                const msg = error.response?.data?.message
                    || error.response?.data?.errors
                        ? Object.values(error.response.data.errors || {}).flat().join(', ')
                        : "Lỗi hệ thống";
                this.$toast.error(msg);
            } finally {
                this.isSubmitting = false;
            }
        },

        async updateRescuer() {
            if (!this.selected.ho_ten || !this.selected.email || !this.selected.so_dien_thoai
                || !this.selected.vai_tro_trong_doi || !this.selected.id_doi_cuu_ho) {
                this.$toast.error("Vui lòng điền đầy đủ thông tin!");
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.selected.email)) {
                this.$toast.error("Email không hợp lệ!");
                return;
            }

            const phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(this.selected.so_dien_thoai)) {
                this.$toast.error("Số điện thoại không hợp lệ (phải là 10 chữ số)!");
                return;
            }

            const updateData = {
                ho_ten: this.selected.ho_ten,
                email: this.selected.email,
                so_dien_thoai: this.selected.so_dien_thoai,
                vai_tro_trong_doi: this.selected.vai_tro_trong_doi,
                id_doi_cuu_ho: this.selected.id_doi_cuu_ho,
            };

            if (this.selected.mat_khau_moi) {
                updateData.mat_khau = this.selected.mat_khau_moi;
            }

            this.isSubmitting = true;
            try {
                const res = await rescuerAccountAPI.update(this.selected.id_thanh_vien_doi, updateData);
                if (res.data.status) {
                    const modalEl = document.getElementById('modalEdit');
                    if (modalEl) {
                        const modal = window.bootstrap?.Modal.getInstance(modalEl);
                        modal?.hide();
                    }
                    this.fetchRescuers();
                    this.$toast.success("Cập nhật thành công!");
                } else {
                    this.$toast.error(res.data.message || "Lỗi cập nhật");
                }
            } catch (error) {
                console.error("Lỗi khi cập nhật rescuer", error);
                const msg = error.response?.data?.message || "Lỗi hệ thống";
                this.$toast.error(msg);
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteRescuer() {
            try {
                const res = await rescuerAccountAPI.delete(this.selected.id_thanh_vien_doi);
                if (res.data.status) {
                    this.rescuers = this.rescuers.filter(r => r.id_thanh_vien_doi != this.selected.id_thanh_vien_doi);
                    this.$toast.success("Đã xóa Rescuer!");
                } else {
                    this.$toast.error(res.data.message || "Lỗi khi xóa");
                }
            } catch (error) {
                console.error("Lỗi khi xóa rescuer", error);
                this.$toast.error(error.response?.data?.message || "Lỗi hệ thống");
            }
        },

        resetForm() {
            this.form = {
                ho_ten: '',
                email: '',
                so_dien_thoai: '',
                vai_tro_trong_doi: '',
                id_doi_cuu_ho: '',
                mat_khau: '',
            };
        }
    }
};
</script>
