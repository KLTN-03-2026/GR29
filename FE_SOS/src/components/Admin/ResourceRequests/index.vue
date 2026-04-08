<template>
  <div class="container-fluid px-0">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h5 class="mb-0 fw-semibold">Yêu cầu bổ sung tài nguyên</h5>
          <small class="text-muted">Đội cứu hộ gửi từ hiện trường — điều phối cập nhật trạng thái</small>
        </div>
        <select v-model="filterStatus" class="form-select form-select-sm" style="max-width: 200px" @change="load">
          <option value="">Tất cả trạng thái</option>
          <option value="CHO_XU_LY">Chờ xử lý</option>
          <option value="DANG_XU_LY">Đang xử lý</option>
          <option value="HOAN_THANH">Hoàn thành</option>
          <option value="TU_CHOI">Từ chối</option>
        </select>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-5 text-muted">Đang tải...</div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light text-muted text-uppercase">
              <tr>
                <th>ID</th>
                <th>Đội</th>
                <th>Mức độ</th>
                <th>Nội dung</th>
                <th>Trạng thái</th>
                <th>Thời gian</th>
                <th class="text-end">Điều phối</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in rows" :key="row.id_yeu_cau_bs">
                <td class="text-muted">{{ row.id_yeu_cau_bs }}</td>
                <td class="fw-semibold">{{ row.doi_cuu_ho?.ten_doi || row.doiCuuHo?.ten_doi || "—" }}</td>
                <td><span class="badge bg-warning-subtle text-dark">{{ row.muc_do_khan_cap }}</span></td>
                <td style="max-width: 280px">
                  <div class="text-truncate" :title="row.noi_dung">{{ row.noi_dung }}</div>
                </td>
                <td>{{ labelStatus(row.trang_thai) }}</td>
                <td class="text-muted">{{ formatTime(row.created_at) }}</td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" @click="openEdit(row)">Xử lý</button>
                  </div>
                </td>
              </tr>
              <tr v-if="!rows.length">
                <td colspan="7" class="text-center text-muted py-4">Chưa có yêu cầu.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal fade" id="rrModal" tabindex="-1" ref="modalEl">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cập nhật yêu cầu #{{ editRow?.id_yeu_cau_bs }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" v-if="editRow">
            <p class="small text-muted mb-2">{{ editRow.noi_dung }}</p>
            <label class="form-label small">Trạng thái</label>
            <select v-model="editForm.trang_thai" class="form-select form-select-sm mb-2">
              <option value="CHO_XU_LY">Chờ xử lý</option>
              <option value="DANG_XU_LY">Đang xử lý</option>
              <option value="HOAN_THANH">Hoàn thành</option>
              <option value="TU_CHOI">Từ chối</option>
            </select>
            <label class="form-label small">Ghi chú điều phối</label>
            <textarea v-model="editForm.ghi_chu_dieu_phoi" class="form-control form-control-sm" rows="3" placeholder="VD: Đã điều xe bổ sung từ đội 2"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="saving" @click="saveEdit">Lưu</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { resourceRequestAPI } from "../../../services/api.js";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

export default {
  name: "AdminResourceRequests",
  data() {
    return {
      rows: [],
      loading: false,
      filterStatus: "",
      editRow: null,
      editForm: { trang_thai: "CHO_XU_LY", ghi_chu_dieu_phoi: "" },
      saving: false,
      modal: null,
    };
  },
  mounted() {
    this.load();
    this.$nextTick(() => {
      const el = this.$refs.modalEl;
      const B = window.bootstrap;
      if (el && B?.Modal) this.modal = new B.Modal(el);
    });
  },
  methods: {
    labelStatus(s) {
      const m = {
        CHO_XU_LY: "Chờ xử lý",
        DANG_XU_LY: "Đang xử lý",
        HOAN_THANH: "Hoàn thành",
        TU_CHOI: "Từ chối",
      };
      return m[s] || s || "—";
    },
    formatTime(t) {
      if (!t) return "—";
      return new Date(t).toLocaleString("vi-VN");
    },
    async load() {
      this.loading = true;
      try {
        const params = { per_page: 50 };
        if (this.filterStatus) params.trang_thai = this.filterStatus;
        const res = await resourceRequestAPI.list(params);
        const pag = res.data;
        this.rows = Array.isArray(pag?.data) ? pag.data : [];
      } catch (e) {
        console.error(e);
        this.rows = [];
        toaster.error("Không tải được danh sách.");
      } finally {
        this.loading = false;
      }
    },
    openEdit(row) {
      this.editRow = row;
      this.editForm = {
        trang_thai: row.trang_thai || "CHO_XU_LY",
        ghi_chu_dieu_phoi: row.ghi_chu_dieu_phoi || "",
      };
      this.modal?.show();
    },
    async saveEdit() {
      if (!this.editRow) return;
      this.saving = true;
      try {
        await resourceRequestAPI.update(this.editRow.id_yeu_cau_bs, {
          trang_thai: this.editForm.trang_thai,
          ghi_chu_dieu_phoi: this.editForm.ghi_chu_dieu_phoi || null,
        });
        toaster.success("Đã cập nhật.");
        this.modal?.hide();
        await this.load();
      } catch (e) {
        console.error(e);
        toaster.error(e?.response?.data?.message || "Lưu thất bại.");
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>
