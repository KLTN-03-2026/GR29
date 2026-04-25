<template>
  <div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-3 p-4">
        <div>
          <h5 class="mb-1 fw-bold text-dark">Quản Lý Đội Cứu Hộ</h5>
          <small class="text-muted">Xem, thêm, sửa, xóa đội cứu hộ & xem chi tiết thành viên, tài nguyên.</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <div class="input-group" style="width: 250px;">
            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
            <input type="text" class="form-control border-start-0 bg-light" placeholder="Tìm tên, khu vực..." v-model="searchQuery" @keyup.enter="searchTeams">
          </div>
          <button type="button" class="btn btn-primary rounded-3 px-3 fw-medium shadow-sm" @click="openAddModal">
            <i class="fa-solid fa-plus me-1"></i>Thêm đội
          </button>
        </div>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5 text-muted">
          <div class="spinner-border text-primary mb-2" role="status"></div>
          <div>Đang tải dữ liệu...</div>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
              <tr>
                <th class="ps-4">Tên đội</th>
                <th>Khu vực</th>
                <th>Hotline</th>
                <th>Trạng thái</th>
                <th class="text-center">Thành viên</th>
                <th class="text-center">Tài nguyên</th>
                <th class="text-end pe-4">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="team in teams" :key="team.id_doi_cuu_ho">
                <td class="ps-4 fw-semibold text-dark">{{ team.ten_co || '—' }}</td>
                <td>{{ team.khu_vuc_quan_ly || '—' }}</td>
                <td>{{ team.so_dien_thoai_hotline || '—' }}</td>
                <td>
                  <span class="badge rounded-pill px-3 py-2 fw-medium" :class="getStatusClass(team.trang_thai)">
                    {{ team.trang_thai || '—' }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1">
                    {{ team.thanh_viens ? team.thanh_viens.length : 0 }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1">
                    {{ team.tai_nguyens ? team.tai_nguyens.length : 0 }}
                  </span>
                </td>
                <td class="text-end pe-4">
                  <button class="btn btn-sm btn-outline-info rounded-3 me-1" title="Chi tiết" @click="viewDetails(team)">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-primary rounded-3 me-1" title="Sửa" @click="editTeam(team)">
                    <i class="fa-solid fa-pen"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger rounded-3" title="Xóa" @click="deleteTeam(team)">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!teams.length">
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="fa-solid fa-box-open fs-1 text-light mb-3"></i>
                  <br>Chưa có dữ liệu đội cứu hộ.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Phân trang -->
      <div v-if="pagination.last_page > 1" class="card-footer bg-white border-0 py-3">
        <ul class="pagination justify-content-end mb-0">
          <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
            <a class="page-link" href="#" @click.prevent="fetchTeams(pagination.current_page - 1)">Trang trước</a>
          </li>
          <li class="page-item disabled">
            <span class="page-link">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
          </li>
          <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
            <a class="page-link" href="#" @click.prevent="fetchTeams(pagination.current_page + 1)">Trang sau</a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Modal Thêm / Sửa Đội -->
    <div class="modal fade" id="modalTeamForm" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold">{{ isEditing ? 'Cập nhật Đội Cứu Hộ' : 'Thêm Đội Cứu Hộ' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-medium small text-muted">Tên đội <span class="text-danger">*</span></label>
              <input type="text" class="form-control" v-model="form.ten_co" placeholder="Nhập tên đội">
            </div>
            <div class="mb-3">
              <label class="form-label fw-medium small text-muted">Khu vực quản lý <span class="text-danger">*</span></label>
              <input type="text" class="form-control" v-model="form.khu_vuc_quan_ly" placeholder="Ví dụ: Quận 1, TP.HCM">
            </div>
            <div class="row mb-3">
              <div class="col-6">
                <label class="form-label fw-medium small text-muted">Số điện thoại (Hotline)</label>
                <input type="text" class="form-control" v-model="form.so_dien_thoai_hotline" placeholder="SĐT liên hệ">
              </div>
              <div class="col-6">
                <label class="form-label fw-medium small text-muted">Trạng thái</label>
                <select class="form-select" v-model="form.trang_thai">
                  <option value="Sẵn sàng">Sẵn sàng</option>
                  <option value="Bận">Bận</option>
                  <option value="Nghỉ">Nghỉ</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-medium small text-muted">Email (dành cho đăng nhập đội) <span class="text-danger">*</span></label>
              <input type="email" class="form-control" v-model="form.email" placeholder="Email đội">
            </div>
            <div class="mb-3" v-if="!isEditing">
              <label class="form-label fw-medium small text-muted">Mật khẩu <span class="text-danger">*</span></label>
              <input type="password" class="form-control" v-model="form.mat_khau" placeholder="Mật khẩu">
            </div>
          </div>
          <div class="modal-footer border-top-0 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-medium" @click="saveTeam" :disabled="saving">
              {{ saving ? 'Đang lưu...' : 'Lưu thông tin' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Chi Tiết Đội -->
    <div class="modal fade" id="modalTeamDetails" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header bg-light border-bottom-0 pb-3">
            <div>
              <h5 class="modal-title fw-bold mb-1">{{ selectedTeam.ten_co }}</h5>
              <span class="text-muted small"><i class="fa-solid fa-location-dot me-1"></i>{{ selectedTeam.khu_vuc_quan_ly }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-0">
            <ul class="nav nav-tabs nav-fill bg-light px-3 border-bottom-0" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active fw-medium text-dark py-3" data-bs-toggle="tab" data-bs-target="#tab-members" type="button">
                  Thành viên ({{ selectedTeam.thanh_viens ? selectedTeam.thanh_viens.length : 0 }})
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link fw-medium text-dark py-3" data-bs-toggle="tab" data-bs-target="#tab-resources" type="button">
                  Tài nguyên ({{ selectedTeam.tai_nguyens ? selectedTeam.tai_nguyens.length : 0 }})
                </button>
              </li>
            </ul>
            <div class="tab-content p-4">
              <!-- Tab Thành Viên -->
              <div class="tab-pane fade show active" id="tab-members">
                <div v-if="!selectedTeam.thanh_viens || !selectedTeam.thanh_viens.length" class="text-center text-muted py-4">
                  Đội chưa có thành viên nào.
                </div>
                <div v-else class="table-responsive">
                  <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Họ tên</th>
                        <th>Vai trò</th>
                        <th>Số điện thoại</th>
                        <th>Trạng thái</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="mem in selectedTeam.thanh_viens" :key="mem.id_thanh_vien_doi">
                        <td class="fw-medium">{{ mem.ho_ten }}</td>
                        <td><span class="badge bg-secondary">{{ mem.vai_tro_trong_doi }}</span></td>
                        <td>{{ mem.so_dien_thoai }}</td>
                        <td>
                          <span class="badge" :class="mem.trang_thai == 1 ? 'bg-success' : 'bg-danger'">
                            {{ mem.trang_thai == 1 ? 'Hoạt động' : 'Tạm khóa' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              
              <!-- Tab Tài Nguyên -->
              <div class="tab-pane fade" id="tab-resources">
                <div v-if="!selectedTeam.tai_nguyens || !selectedTeam.tai_nguyens.length" class="text-center text-muted py-4">
                  Đội chưa có tài nguyên nào.
                </div>
                <div v-else class="row g-3">
                  <div class="col-md-6" v-for="res in selectedTeam.tai_nguyens" :key="res.id_tai_nguyen">
                    <div class="card border border-light shadow-sm">
                      <div class="card-body p-3 d-flex justify-content-between align-items-center">
                        <div>
                          <div class="fw-bold text-dark">{{ res.ten_tai_nguyen }}</div>
                          <div class="small text-muted">{{ res.loai_tai_nguyen }} • SL: {{ res.so_luong }}</div>
                        </div>
                        <span class="badge" :class="res.trang_thai == 1 ? 'bg-success' : 'bg-warning text-dark'">
                          {{ res.trang_thai == 1 ? 'Sẵn sàng' : 'Hỏng/Bảo trì' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-top-0 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescueTeamAPI } from "../../../services/api.js";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

export default {
  name: "AdminResources",
  data() {
    return {
      teams: [],
      loading: false,
      saving: false,
      searchQuery: "",
      pagination: {
        current_page: 1,
        last_page: 1
      },
      isEditing: false,
      form: {
        id_doi_cuu_ho: null,
        ten_co: "",
        khu_vuc_quan_ly: "",
        so_dien_thoai_hotline: "",
        trang_thai: "Sẵn sàng",
        email: "",
        mat_khau: ""
      },
      selectedTeam: {}
    };
  },
  async mounted() {
    await this.fetchTeams();
  },
  methods: {
    async fetchTeams(page = 1) {
      this.loading = true;
      try {
        const response = await rescueTeamAPI.getList({ page });
        const resData = response.data?.data;
        if (resData && resData.data) {
          this.teams = resData.data;
          this.pagination = {
            current_page: resData.current_page,
            last_page: resData.last_page
          };
        } else if (Array.isArray(resData)) {
          this.teams = resData;
        } else {
          this.teams = [];
        }
      } catch (e) {
        console.error("fetchTeams", e);
        toaster.error("Không thể tải danh sách đội cứu hộ.");
        this.teams = [];
      } finally {
        this.loading = false;
      }
    },
    async searchTeams() {
      if (!this.searchQuery.trim()) {
        return this.fetchTeams();
      }
      this.loading = true;
      try {
        const response = await rescueTeamAPI.search(this.searchQuery);
        const resData = response.data?.data;
        if (resData && resData.data) {
          this.teams = resData.data;
          this.pagination = {
            current_page: resData.current_page,
            last_page: resData.last_page
          };
        } else if (Array.isArray(resData)) {
          this.teams = resData;
        } else {
          this.teams = [];
        }
      } catch (e) {
        toaster.error("Lỗi khi tìm kiếm.");
      } finally {
        this.loading = false;
      }
    },
    getStatusClass(status) {
      const lower = (status || "").toLowerCase();
      if (lower.includes("sẵn sàng") || lower === "sansang") return "bg-success bg-opacity-10 text-success";
      if (lower.includes("bận")) return "bg-warning bg-opacity-10 text-warning text-dark";
      if (lower.includes("nghỉ")) return "bg-secondary bg-opacity-10 text-secondary";
      return "bg-light text-dark border";
    },
    openAddModal() {
      this.isEditing = false;
      this.form = {
        id_doi_cuu_ho: null,
        ten_co: "",
        khu_vuc_quan_ly: "",
        so_dien_thoai_hotline: "",
        trang_thai: "Sẵn sàng",
        email: "",
        mat_khau: ""
      };
      new window.bootstrap.Modal(document.getElementById('modalTeamForm')).show();
    },
    editTeam(team) {
      this.isEditing = true;
      this.form = {
        id_doi_cuu_ho: team.id_doi_cuu_ho,
        ten_co: team.ten_co || "",
        khu_vuc_quan_ly: team.khu_vuc_quan_ly || "",
        so_dien_thoai_hotline: team.so_dien_thoai_hotline || "",
        trang_thai: team.trang_thai || "Sẵn sàng",
        email: team.email || ""
      };
      new window.bootstrap.Modal(document.getElementById('modalTeamForm')).show();
    },
    async saveTeam() {
      if (!this.form.ten_co || !this.form.khu_vuc_quan_ly || !this.form.email) {
        toaster.warning("Vui lòng điền đủ các trường bắt buộc (*).");
        return;
      }
      if (!this.isEditing && !this.form.mat_khau) {
        toaster.warning("Vui lòng nhập mật khẩu cho đội mới.");
        return;
      }
      
      this.saving = true;
      try {
        if (this.isEditing) {
          await rescueTeamAPI.update(this.form.id_doi_cuu_ho, this.form);
          toaster.success("Cập nhật thành công!");
        } else {
          await rescueTeamAPI.create(this.form);
          toaster.success("Thêm đội thành công!");
        }
        window.bootstrap.Modal.getInstance(document.getElementById('modalTeamForm')).hide();
        this.fetchTeams(this.pagination.current_page);
      } catch (e) {
        console.error(e);
        toaster.error(e.response?.data?.message || "Lỗi khi lưu thông tin.");
      } finally {
        this.saving = false;
      }
    },
    async deleteTeam(team) {
      if (!confirm(`Bạn có chắc chắn muốn xóa đội "${team.ten_co}"? Tất cả thành viên và tài nguyên của đội sẽ bị ảnh hưởng.`)) return;
      try {
        await rescueTeamAPI.delete(team.id_doi_cuu_ho);
        toaster.success("Đã xóa đội cứu hộ!");
        this.fetchTeams(this.pagination.current_page);
      } catch (e) {
        toaster.error("Lỗi khi xóa đội.");
      }
    },
    async viewDetails(team) {
      this.selectedTeam = { ...team };
      new window.bootstrap.Modal(document.getElementById('modalTeamDetails')).show();
      try {
        const res = await rescueTeamAPI.getDetail(team.id_doi_cuu_ho);
        if (res.data?.data) {
          this.selectedTeam = res.data.data;
        }
      } catch (e) {
        console.error("Lỗi tải chi tiết đội", e);
      }
    }
  }
};
</script>

<style scoped>
.nav-tabs .nav-link {
  border: none;
  border-bottom: 2px solid transparent;
  color: #6c757d;
}
.nav-tabs .nav-link.active {
  background: transparent;
  border-bottom: 2px solid #0d6efd;
  color: #0d6efd;
  font-weight: 600 !important;
}
</style>
