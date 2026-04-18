<template>
  <div class="container-fluid px-0">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h5 class="mb-0 fw-semibold">Quản lý theo chi nhánh (đội)</h5>
          <small class="text-muted">Chọn chi nhánh (Hải Châu/Thanh Khê/…) để xem và quản lý tài nguyên của chi nhánh đó</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <input v-model="keyword" class="form-control form-control-sm" style="max-width: 260px" placeholder="Tìm đội / khu vực…" />
          <button type="button" class="btn btn-sm btn-primary" @click="openBranchModal">
            <i class="fa-solid fa-plus me-1"></i>Thêm chi nhánh
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary" :disabled="loading" @click="fetchTeams">
            <i class="fa-solid fa-rotate me-1"></i>Tải lại
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-lg-4">
            <div class="border rounded-4 overflow-hidden bg-white h-100">
              <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                <div class="fw-semibold">Danh sách chi nhánh</div>
                <div class="small text-muted">{{ filteredTeams.length }} đội</div>
              </div>
              <div v-if="loading" class="p-3 text-muted">Đang tải…</div>
              <div v-else class="list-group list-group-flush admin-team-list">
                <button
                  v-for="t in filteredTeams"
                  :key="t.id_doi_cuu_ho ?? t.id"
                  type="button"
                  class="list-group-item list-group-item-action d-flex justify-content-between align-items-start"
                  :class="{ active: selectedTeamId === (t.id_doi_cuu_ho ?? t.id) }"
                  @click="selectTeam(t)"
                >
                  <div class="me-2">
                    <div class="fw-semibold">{{ t.ten_doi || t.ten || t.ten_co || "—" }}</div>
                    <div class="small opacity-75">
                      {{ t.khu_vuc_phu_trach || t.khu_vuc || t.khu_vuc_quan_ly || "—" }}
                      · {{ t.so_dien_thoai || t.so_dien_thoai_hotline || t.lien_he || "—" }}
                    </div>
                  </div>
                  <span class="badge rounded-pill" :class="badgeStatus(t.trang_thai)">
                    {{ t.trang_thai || "—" }}
                  </span>
                </button>
                <div v-if="!filteredTeams.length" class="p-3 text-muted">Không có đội phù hợp.</div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-8">
            <div class="border rounded-4 bg-white h-100">
              <div class="p-3 border-bottom d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <div>
                  <div class="text-muted small">Chi nhánh đang chọn</div>
                  <div class="fw-bold fs-5">{{ selectedTeam?.ten_doi || selectedTeam?.ten || selectedTeam?.ten_co || "Chưa chọn đội" }}</div>
                  <div class="small text-muted">
                    Khu vực: <strong>{{ selectedTeam?.khu_vuc_phu_trach || selectedTeam?.khu_vuc || selectedTeam?.khu_vuc_quan_ly || "—" }}</strong>
                    · Hotline: <strong>{{ selectedTeam?.so_dien_thoai || selectedTeam?.so_dien_thoai_hotline || selectedTeam?.lien_he || "—" }}</strong>
                  </div>
                </div>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="!selectedTeamId || resLoading" @click="loadResources">
                    <i class="fa-solid fa-boxes-stacked me-1"></i>Tải tài nguyên
                  </button>
                  <button type="button" class="btn btn-sm btn-primary" :disabled="!selectedTeamId" @click="openResourceModalForCreate">
                    <i class="fa-solid fa-plus me-1"></i>Thêm tài nguyên
                  </button>
                </div>
              </div>

              <div class="p-3">
                <div v-if="!selectedTeamId" class="text-muted py-5 text-center">Chọn 1 chi nhánh ở bên trái để xem tài nguyên.</div>
                <div v-else>
                  <div v-if="resLoading" class="text-muted py-3">Đang tải danh sách tài nguyên…</div>
                  <div v-else class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                      <thead class="table-light small">
                        <tr>
                          <th>Tên tài nguyên</th>
                          <th>Loại</th>
                          <th class="text-end">Số lượng</th>
                          <th>Trạng thái</th>
                          <th class="text-end">Thao tác</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="r in resources" :key="r.id_tai_nguyen">
                          <td class="fw-semibold">{{ r.ten_tai_nguyen }}</td>
                          <td class="text-muted">{{ r.loai_tai_nguyen || "—" }}</td>
                          <td class="text-end">{{ r.so_luong }}</td>
                          <td>
                            <span class="badge bg-success-subtle text-success" v-if="Number(r.trang_thai) === 1">Sẵn sàng</span>
                            <span class="badge bg-secondary-subtle text-secondary" v-else>Ngưng</span>
                          </td>
                          <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-link btn-sm p-0 me-2" @click="openResourceModalForEdit(r)">Sửa</button>
                            <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="removeRes(r)">Xóa</button>
                          </td>
                        </tr>
                        <tr v-if="!resources.length">
                          <td colspan="5" class="text-center text-muted py-3">Chưa có tài nguyên (hãy seed theo file mẫu hoặc thêm mới).</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <div class="text-muted small">Dùng nút <strong>Thêm tài nguyên</strong> để cập nhật nhanh bằng modal.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Thêm/Sửa tài nguyên -->
    <div class="modal fade" id="resourceCrudModal" tabindex="-1" ref="resourceModalEl">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold mb-0">{{ resourceEditingId ? "Cập nhật tài nguyên" : "Thêm tài nguyên" }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2 small text-muted">
              Chi nhánh: <strong>{{ selectedTeam?.ten_co || selectedTeam?.ten_doi || "—" }}</strong>
            </div>
            <div class="row g-2">
              <div class="col-12">
                <label class="form-label small">Tên</label>
                <input v-model="resourceForm.ten_tai_nguyen" type="text" class="form-control form-control-sm" placeholder="VD: Xe cứu thương" />
              </div>
              <div class="col-12">
                <label class="form-label small">Loại</label>
                <select v-model="resourceForm.loai_tai_nguyen" class="form-select form-select-sm">
                  <option value="Vehicle">Vehicle</option>
                  <option value="Equipment">Equipment</option>
                  <option value="Medical">Medical</option>
                  <option value="Communication">Communication</option>
                  <option value="Water">Water</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label small">Số lượng</label>
                <input v-model.number="resourceForm.so_luong" type="number" min="0" class="form-control form-control-sm" />
              </div>
              <div class="col-6">
                <label class="form-label small">Trạng thái</label>
                <select v-model.number="resourceForm.trang_thai" class="form-select form-select-sm">
                  <option :value="1">Sẵn sàng</option>
                  <option :value="0">Ngưng</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="saving || !selectedTeamId" @click="saveResource">
              {{ resourceEditingId ? "Lưu" : "Thêm" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Thêm chi nhánh -->
    <div class="modal fade" id="branchModal" tabindex="-1" ref="branchModalEl">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold mb-0">Thêm chi nhánh (đội cứu hộ)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-12">
                <label class="form-label small">Tên đội (ten_co)</label>
                <input v-model="branchForm.ten_co" type="text" class="form-control form-control-sm" placeholder="VD: Đội Sơ Cứu Y Tế Cấp Cứu Thanh Khê" />
              </div>
              <div class="col-12">
                <label class="form-label small">Khu vực quản lý</label>
                <input v-model="branchForm.khu_vuc_quan_ly" type="text" class="form-control form-control-sm" placeholder="VD: Thanh Khê" />
              </div>
              <div class="col-12">
                <label class="form-label small">Hotline</label>
                <input v-model="branchForm.so_dien_thoai_hotline" type="text" class="form-control form-control-sm" placeholder="VD: 0236-3847-003" />
              </div>
              <div class="col-6">
                <label class="form-label small">Vĩ độ</label>
                <input v-model.number="branchForm.vi_tri_lat" type="number" step="0.000001" class="form-control form-control-sm" placeholder="16.0700" />
              </div>
              <div class="col-6">
                <label class="form-label small">Kinh độ</label>
                <input v-model.number="branchForm.vi_tri_lng" type="number" step="0.000001" class="form-control form-control-sm" placeholder="108.2000" />
              </div>
              <div class="col-12">
                <label class="form-label small">Trạng thái</label>
                <select v-model="branchForm.trang_thai" class="form-select form-select-sm">
                  <option value="SAN_SANG">SAN_SANG</option>
                  <option value="DANG_NHIEM_VU">DANG_NHIEM_VU</option>
                  <option value="TAM_NGUNG">TAM_NGUNG</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small">Mô tả</label>
                <textarea v-model="branchForm.mo_ta" class="form-control form-control-sm" rows="3" placeholder="Mô tả ngắn…"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="branchSaving" @click="createBranch">Tạo chi nhánh</button>
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
      keyword: "",
      selectedTeam: null,
      selectedTeamId: null,
      resources: [],
      resLoading: false,
      saving: false,
      resourceModal: null,
      resourceEditingId: null,
      resourceForm: {
        ten_tai_nguyen: "",
        loai_tai_nguyen: "Equipment",
        so_luong: 0,
        trang_thai: 1,
      },

      branchModal: null,
      branchSaving: false,
      branchForm: {
        ten_co: "",
        khu_vuc_quan_ly: "",
        so_dien_thoai_hotline: "",
        vi_tri_lat: 16.0544,
        vi_tri_lng: 108.2022,
        trang_thai: "SAN_SANG",
        mo_ta: "",
      },
    };
  },
  async mounted() {
    await this.fetchTeams();
    this.$nextTick(() => this.initModals());
  },
  computed: {
    filteredTeams() {
      const k = (this.keyword || "").trim().toLowerCase();
      if (!k) return this.teams;
      return this.teams.filter((t) => {
        const name = String(t.ten_doi || t.ten || t.ten_co || "").toLowerCase();
        const area = String(t.khu_vuc_phu_trach || t.khu_vuc || t.khu_vuc_quan_ly || "").toLowerCase();
        return name.includes(k) || area.includes(k);
      });
    },
  },
  methods: {
    initModals() {
      const B = typeof window !== "undefined" && window.bootstrap;
      if (!B?.Modal) return;
      if (this.$refs.resourceModalEl) this.resourceModal = new B.Modal(this.$refs.resourceModalEl);
      if (this.$refs.branchModalEl) this.branchModal = new B.Modal(this.$refs.branchModalEl);
    },
    badgeStatus(s) {
      const v = String(s || "").toUpperCase();
      if (v.includes("SAN_SANG") || v.includes("ONLINE")) return "bg-success-subtle text-success";
      if (v.includes("DANG")) return "bg-warning-subtle text-dark";
      return "bg-secondary-subtle text-secondary";
    },
    async fetchTeams() {
      this.loading = true;
      try {
        const response = await rescueTeamAPI.getList({ per_page: 100, lite: 1, sort_by: "id_doi_cuu_ho", sort_order: "asc" });
        const pag = response.data?.data;
        this.teams = Array.isArray(pag?.data) ? pag.data : [];
        if (!this.selectedTeamId && this.teams.length) {
          this.selectTeam(this.teams[0]);
        }
      } catch (e) {
        console.error(e);
        this.teams = [];
        toaster.error("Không tải được danh sách đội.");
      } finally {
        this.loading = false;
      }
    },
    selectTeam(team) {
      this.selectedTeam = team;
      this.selectedTeamId = team.id_doi_cuu_ho ?? team.id;
      this.resourceEditingId = null;
      this.loadResources();
    },
    async loadResources() {
      if (!this.selectedTeamId) return;
      this.resLoading = true;
      try {
        const res = await rescueTeamAPI.getResources(this.selectedTeamId, { per_page: 200 });
        const pag = res.data?.data;
        this.resources = Array.isArray(pag?.data) ? pag.data : [];
      } catch (e) {
        console.error(e);
        this.resources = [];
        toaster.error("Không tải được tài nguyên đội.");
      } finally {
        this.resLoading = false;
      }
    },
    resetResourceForm() {
      this.resourceEditingId = null;
      this.resourceForm = { ten_tai_nguyen: "", loai_tai_nguyen: "Equipment", so_luong: 0, trang_thai: 1 };
    },
    openResourceModalForCreate() {
      if (!this.resourceModal) this.initModals();
      this.resetResourceForm();
      this.resourceModal?.show();
    },
    openResourceModalForEdit(r) {
      if (!this.resourceModal) this.initModals();
      this.resourceEditingId = r.id_tai_nguyen;
      this.resourceForm = {
        ten_tai_nguyen: r.ten_tai_nguyen || "",
        loai_tai_nguyen: r.loai_tai_nguyen || "",
        so_luong: Number(r.so_luong) || 0,
        trang_thai: Number(r.trang_thai) !== 0 ? 1 : 0,
      };
      this.resourceModal?.show();
    },
    async saveResource() {
      if (!this.selectedTeamId) return;
      const { ten_tai_nguyen, loai_tai_nguyen, so_luong, trang_thai } = this.resourceForm;
      if (!ten_tai_nguyen?.trim()) {
        toaster.warning("Nhập tên tài nguyên.");
        return;
      }
      this.saving = true;
      try {
        if (this.resourceEditingId) {
          await rescueTeamAPI.updateResource(this.selectedTeamId, this.resourceEditingId, {
            ten_tai_nguyen: ten_tai_nguyen.trim(),
            loai_tai_nguyen: loai_tai_nguyen?.trim() || "Other",
            so_luong,
            trang_thai,
          });
          toaster.success("Đã cập nhật tài nguyên.");
        } else {
          await rescueTeamAPI.addResource(this.selectedTeamId, {
            ten_tai_nguyen: ten_tai_nguyen.trim(),
            loai_tai_nguyen: loai_tai_nguyen?.trim() || "Other",
            so_luong,
            trang_thai,
          });
          toaster.success("Đã thêm tài nguyên.");
        }
        this.resetResourceForm();
        this.resourceModal?.hide();
        await this.loadResources();
      } catch (e) {
        console.error(e);
        toaster.error(e?.response?.data?.message || "Thao tác thất bại.");
      } finally {
        this.saving = false;
      }
    },
    async removeRes(r) {
      if (!confirm(`Xóa tài nguyên "${r.ten_tai_nguyen}"?`)) return;
      try {
        await rescueTeamAPI.deleteResource(this.selectedTeamId, r.id_tai_nguyen);
        toaster.success("Đã xóa.");
        if (this.resourceEditingId === r.id_tai_nguyen) this.resetResourceForm();
        await this.loadResources();
      } catch (e) {
        console.error(e);
        toaster.error("Không xóa được.");
      }
    },

    openBranchModal() {
      if (!this.branchModal) this.initModals();
      this.branchForm = {
        ten_co: "",
        khu_vuc_quan_ly: "",
        so_dien_thoai_hotline: "",
        vi_tri_lat: 16.0544,
        vi_tri_lng: 108.2022,
        trang_thai: "SAN_SANG",
        mo_ta: "",
      };
      this.branchModal?.show();
    },
    async createBranch() {
      const payload = { ...this.branchForm };
      if (!payload.ten_co?.trim() || !payload.khu_vuc_quan_ly?.trim()) {
        toaster.warning("Nhập tên đội và khu vực quản lý.");
        return;
      }
      this.branchSaving = true;
      try {
        await rescueTeamAPI.create({
          ten_co: payload.ten_co.trim(),
          khu_vuc_quan_ly: payload.khu_vuc_quan_ly.trim(),
          so_dien_thoai_hotline: payload.so_dien_thoai_hotline?.trim() || null,
          vi_tri_lat: payload.vi_tri_lat || null,
          vi_tri_lng: payload.vi_tri_lng || null,
          trang_thai: payload.trang_thai || "SAN_SANG",
          mo_ta: payload.mo_ta || null,
        });
        toaster.success("Đã tạo chi nhánh.");
        this.branchModal?.hide();
        await this.fetchTeams();
      } catch (e) {
        console.error(e);
        toaster.error(e?.response?.data?.message || "Tạo chi nhánh thất bại.");
      } finally {
        this.branchSaving = false;
      }
    },
  },
};
</script>

<style scoped>
.table th {
  font-weight: 600;
  font-size: 0.8rem;
}
.admin-team-list .list-group-item.active {
  background: #1a73e8;
  border-color: #1a73e8;
}
</style>
