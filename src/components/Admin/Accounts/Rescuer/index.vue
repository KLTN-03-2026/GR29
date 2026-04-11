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
                    <h5>Thêm Rescuer</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input v-model="form.ho_ten" class="form-control mb-2" placeholder="Họ tên">
                    <input v-model="form.email" class="form-control mb-2" placeholder="Email">
                    <input v-model="form.so_dien_thoai" class="form-control mb-2" placeholder="SĐT">
                    <input v-model="form.vai_tro_trong_doi" class="form-control mb-2" placeholder="Vai trò">
                    <input v-model="form.mat_khau" type="password" class="form-control" placeholder="Mật khẩu">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button class="btn btn-primary" @click="addRescuer" data-bs-dismiss="modal">Lưu</button>
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
                    <input v-model="selected.ho_ten" class="form-control mb-2">
                    <input v-model="selected.email" class="form-control mb-2">
                    <input v-model="selected.so_dien_thoai" class="form-control mb-2">
                    <input v-model="selected.vai_tro_trong_doi" class="form-control">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button class="btn btn-warning" @click="updateRescuer" data-bs-dismiss="modal">Cập nhật</button>
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
import { rescuerAccountAPI } from "../../../../services/api";
export default {
    data() {
        return {
            rescuers: [],
            selected: {},
            form: {
                ho_ten: '',
                email: '',
                so_dien_thoai: '',
                vai_tro_trong_doi: '',
                mat_khau: '',
            }
        };
    },
    mounted() {
        this.fetchRescuers();
    },
    methods: {
        async fetchRescuers() {
            try {
                const response = await rescuerAccountAPI.getList();
                this.rescuers = response.data;
            } catch (error) {
                console.error("Lỗi khi tải danh sách rescuer", error);
            }
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

        async addRescuer() {
            try {
                const res = await rescuerAccountAPI.create(this.form);
                if (res.data.status) {
                    this.fetchRescuers();
                    this.form = {};
                    this.$toast.success("Thêm rescuer thành công!");
                } else {
                    this.$toast.error(res.data.message || "Lỗi khi thêm");
                }
            } catch (error) {
                console.error("Lỗi khi thêm rescuer", error);
                this.$toast.error(error.response?.data?.message || "Lỗi hệ thống");
            }
        },

        async updateRescuer() {
            try {
                const res = await rescuerAccountAPI.update(this.selected.id_thanh_vien_doi, this.selected);
                if (res.data.status) {
                    this.fetchRescuers();
                    this.$toast.success("Cập nhật thành công!");
                } else {
                    this.$toast.error(res.data.message || "Lỗi cập nhật");
                }
            } catch (error) {
                console.error("Lỗi khi cập nhật rescuer", error);
                this.$toast.error(error.response?.data?.message || "Lỗi hệ thống");
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
        }
    }
};
</script>