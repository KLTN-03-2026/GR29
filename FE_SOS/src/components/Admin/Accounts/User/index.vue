<template>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-semibold">Quản lý User</h5>
                <small class="text-muted">Người dùng hệ thống</small>
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
                        <th>Trạng thái</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(item, index) in users" :key="item.id_nguoi_dung">
                        <td>{{ index + 1 }}</td>
                        <td>{{ item.ho_ten }}</td>
                        <td>{{ item.email }}</td>
                        <td>{{ item.so_dien_thoai }}</td>

                        <!-- TRẠNG THÁI -->
                        <td>
                            <button class="btn w-100"
                                :class="item.trang_thai == 1 ? 'btn-success' : 'btn-danger'"
                                @click="toggleStatus(item)">
                                {{ item.trang_thai == 1 ? 'Hoạt động' : 'Bị khóa' }}
                            </button>
                        </td>

                        <!-- ACTION -->
                        <td class="text-center">
                            <button class="btn btn-warning btn-sm me-1"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEdit"
                                @click="selected = { ...item }">
                                <i class="fa fa-pen"></i>
                            </button>

                            <button class="btn btn-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalDelete"
                                @click="selected = item">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
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
                    <h5>Thêm User</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input v-model="form.ho_ten" class="form-control mb-2" placeholder="Họ tên">
                    <input v-model="form.email" class="form-control mb-2" placeholder="Email">
                    <input v-model="form.so_dien_thoai" class="form-control mb-2" placeholder="SĐT">
                    <input v-model="form.mat_khau" type="password" class="form-control" placeholder="Mật khẩu">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button class="btn btn-primary" @click="addUser" data-bs-dismiss="modal">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SỬA -->
    <div class="modal fade" id="modalEdit">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Sửa User</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input v-model="selected.ho_ten" class="form-control mb-2">
                    <input v-model="selected.email" class="form-control mb-2">
                    <input v-model="selected.so_dien_thoai" class="form-control">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button class="btn btn-warning" @click="updateUser" data-bs-dismiss="modal">Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <!-- XÓA -->
    <div class="modal fade" id="modalDelete">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Xóa User</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Xác nhận xóa <b>{{ selected.ho_ten }}</b> ?
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-danger" @click="deleteUser" data-bs-dismiss="modal">Xóa</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { userAPI } from "../../../../services/api";

export default {
    data() {
        return {
            users: [],
            selected: {},
            form: {
                ho_ten: '',
                email: '',
                so_dien_thoai: '',
                mat_khau: '',
            }
        };
    },
    mounted() {
        this.fetchUsers();
    },
    methods: {
        async fetchUsers() {
            try {
                const response = await userAPI.getList();
                this.users = response.data.data;
            } catch (error) {
                console.error("Lỗi khi tải danh sách user", error);
            }
        },

        async toggleStatus(item) {
            try {
                await userAPI.changeStatus(item.id_nguoi_dung);
                item.trang_thai = item.trang_thai == 1 ? 0 : 1;
                this.$toast.success("Đổi trạng thái thành công!");
            } catch (error) {
                console.error("Lỗi khi thay đổi trạng thái", error);
                this.$toast.error("Lỗi thay đổi trạng thái!");
            }
        },

        async addUser() {
            try {
                const res = await userAPI.create(this.form);
                if (res.data.status) {
                    this.fetchUsers();
                    this.form = {};
                    this.$toast.success("Thêm User thành công!");
                } else {
                    this.$toast.error(res.data.message || "Lỗi khi thêm");
                }
            } catch (error) {
                console.error("Lỗi khi thêm user", error);
                this.$toast.error(error.response?.data?.message || "Lỗi hệ thống");
            }
        },

        async updateUser() {
            try {
                const res = await userAPI.update(this.selected.id_nguoi_dung, this.selected);
                if (res.data.status) {
                    this.fetchUsers();
                    this.$toast.success("Cập nhật thành công!");
                } else {
                    this.$toast.error(res.data.message || "Lỗi cập nhật");
                }
            } catch (error) {
                console.error("Lỗi khi cập nhật user", error);
                this.$toast.error(error.response?.data?.message || "Lỗi hệ thống");
            }
        },

        async deleteUser() {
            try {
                const res = await userAPI.delete(this.selected.id_nguoi_dung);
                if (res.data.status) {
                    this.users = this.users.filter(u => u.id_nguoi_dung != this.selected.id_nguoi_dung);
                    this.$toast.success("Đã xóa User!");
                } else {
                    this.$toast.error(res.data.message || "Lỗi khi xóa");
                }
            } catch (error) {
                console.error("Lỗi khi xóa user", error);
                this.$toast.error(error.response?.data?.message || "Lỗi hệ thống");
            }
        }
    }
};
</script>