<template>
  <div class="container-fluid px-0">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h5 class="mb-0 fw-semibold">Đội cứu hộ &amp; tài nguyên</h5>
          <small class="text-muted">Quản lý đội, phương tiện và trang thiết bị (SOS — không phải kho hàng)</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Sắp có">
            <i class="fa-solid fa-filter me-1"></i>Lọc
          </button>
          <button type="button" class="btn btn-sm btn-primary" disabled title="Thêm qua API sau">
            <i class="fa-solid fa-plus me-1"></i>Thêm đội / tài nguyên
          </button>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-5 text-muted">Đang tải...</div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-muted text-uppercase">
              <tr>
                <th>Tên đội</th>
                <th>Khu vực</th>
                <th>Trạng thái</th>
                <th>Liên hệ</th>
                <th class="text-end">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="team in teams" :key="team.id ?? team.ma_doi">
                <td class="fw-semibold">{{ team.ten_doi || team.ten || "—" }}</td>
                <td>{{ team.khu_vuc_phu_trach || team.khu_vuc || "—" }}</td>
                <td>
                  <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">
                    {{ team.trang_thai || "—" }}
                  </span>
                </td>
                <td class="small text-muted">{{ team.so_dien_thoai || team.lien_he || "—" }}</td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-primary me-1" disabled title="Sắp có">Sửa</button>
                  <button type="button" class="btn btn-sm btn-outline-danger" disabled title="Sắp có">Xóa</button>
                </td>
              </tr>
              <tr v-if="!teams.length">
                <td colspan="5" class="text-center text-muted py-4">
                  Chưa có dữ liệu đội cứu hộ hoặc API chưa phản hồi.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescueTeamAPI } from "../../../services/api.js";

export default {
  name: "AdminResources",
  data() {
    return {
      teams: [],
      loading: false,
    };
  },
  async mounted() {
    await this.fetchTeams();
  },
  methods: {
    async fetchTeams() {
      this.loading = true;
      try {
        const response = await rescueTeamAPI.getList();
        const raw = response.data?.data ?? response.data ?? [];
        this.teams = Array.isArray(raw) ? raw : [];
      } catch (e) {
        console.error("fetchTeams", e);
        this.teams = [];
      } finally {
        this.loading = false;
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
</style>
