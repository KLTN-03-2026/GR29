<template>
  <div class="container-fluid px-0">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0 fw-semibold">Điều phối nhiệm vụ cứu hộ</h5>
          <small class="text-muted">Admin theo dõi đội đã nhận và trạng thái đang xử lý</small>
        </div>
        <button class="btn btn-sm btn-outline-secondary" :disabled="loading" @click="taiLai">
          <i class="fa-solid fa-rotate-right me-1"></i>Tải lại
        </button>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0">
            <h6 class="mb-0 fw-semibold">Nhiệm vụ đã giao (chờ đội nhận)</h6>
            <small class="text-muted">trang_thai_nhiem_vu = MOI</small>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0 small">
                <thead class="table-light">
                  <tr class="text-muted text-uppercase" style="font-size:10px">
                    <th>Phân công</th>
                    <th>Yêu cầu</th>
                    <th>Đội</th>
                    <th>Trạng thái</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="a in choNhan" :key="a.id_phan_cong">
                    <td class="fw-semibold">#{{ a.id_phan_cong }}</td>
                    <td>#{{ a.id_yeu_cau }}</td>
                    <td>{{ tenDoi(a) }}</td>
                    <td><span class="badge bg-warning text-dark">{{ a.trang_thai_nhiem_vu }}</span></td>
                  </tr>
                </tbody>
              </table>
              <div v-if="!choNhan.length && !loading" class="text-center text-muted py-4 small">Không có nhiệm vụ chờ nhận.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0">
            <h6 class="mb-0 fw-semibold">Đội đang xử lý</h6>
            <small class="text-muted">Khi rescuer bấm TIẾP NHẬN sẽ hiện tại đây</small>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0 small">
                <thead class="table-light">
                  <tr class="text-muted text-uppercase" style="font-size:10px">
                    <th>Phân công</th>
                    <th>Yêu cầu</th>
                    <th>Đội đang xử lý</th>
                    <th>Trạng thái</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="a in dangXuLy" :key="a.id_phan_cong">
                    <td class="fw-semibold">#{{ a.id_phan_cong }}</td>
                    <td>#{{ a.id_yeu_cau }}</td>
                    <td>{{ tenDoi(a) }}</td>
                    <td><span class="badge bg-success">{{ a.trang_thai_nhiem_vu }}</span></td>
                  </tr>
                </tbody>
              </table>
              <div v-if="!dangXuLy.length && !loading" class="text-center text-muted py-4 small">Chưa có đội nào đang xử lý.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { createToaster } from "@meforma/vue-toaster";
import { assignmentAPI } from "../../../services/api";

const toaster = createToaster({ position: "top-right" });

export default {
  name: "AdminAssignments",
  data() {
    return {
      loading: false,
      rows: [],
    };
  },
  computed: {
    choNhan() {
      return this.rows.filter((r) => String(r.trang_thai_nhiem_vu || "").toUpperCase() === "MOI");
    },
    dangXuLy() {
      return this.rows.filter((r) => String(r.trang_thai_nhiem_vu || "").toUpperCase() === "DANG_XU_LY");
    },
  },
  mounted() {
    this.taiLai();
  },
  methods: {
    tenDoi(a) {
      return a.doi_cuu_ho?.ten_co || a.doiCuuHo?.ten_co || `Đội #${a.id_doi_cuu_ho}`;
    },
    async taiLai() {
      this.loading = true;
      try {
        const res = await assignmentAPI.getList({ per_page: 100 });
        this.rows = Array.isArray(res.data?.data) ? res.data.data : [];
      } catch (e) {
        console.error(e);
        toaster.error("Không tải được dữ liệu phân công.");
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

