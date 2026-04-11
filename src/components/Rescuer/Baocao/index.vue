<template>
  <div class="container-fluid py-3">
    <div class="card border-0 shadow-sm mx-auto" style="max-width: 900px">
      <div class="card-header bg-white border-0">
        <h5 class="mb-0 fw-semibold">Báo cáo hoàn thành nhiệm vụ</h5>
        <small class="text-muted">Gửi minh chứng sau khi xử lý xong hiện trường</small>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-muted small">Đang tải thông tin phân công...</div>
        <div v-else-if="!assignmentId" class="alert alert-warning small mb-3">
          Thiếu <code>id_phan_cong</code>. Hãy quay về trang nhiệm vụ và bấm “Hoàn thành nhiệm vụ”.
        </div>
        <div v-else class="mb-3 p-3 bg-light rounded-3 small">
          <div><strong>Mã phân công:</strong> #{{ assignmentId }}</div>
          <div><strong>Mã yêu cầu:</strong> #{{ requestId || assignment?.id_yeu_cau || "—" }}</div>
          <div><strong>Đội xử lý:</strong> {{ assignment?.doi_cuu_ho?.ten_co || assignment?.doiCuuHo?.ten_co || "Đội cứu hộ" }}</div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Trạng thái kết quả</label>
          <select v-model="form.trang_thai_ket_qua" class="form-select">
            <option value="HOAN_THANH">HOÀN THÀNH</option>
            <option value="DANG_TIEP_TUC">ĐANG TIẾP TỤC</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Báo cáo hiện trường</label>
          <textarea
            v-model="form.bao_cao_hien_truong"
            rows="5"
            class="form-control"
            placeholder="Mô tả quá trình cứu hộ, số nạn nhân, biện pháp đã thực hiện..."
          />
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Ảnh minh chứng (upload từ máy)</label>
          <input class="form-control" type="file" accept="image/*" @change="onFileChange" />
          <div class="form-text">Tối đa 5MB. Nếu không chọn file, có thể nhập URL bên dưới.</div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Hoặc URL ảnh minh chứng</label>
          <input
            v-model="form.hinh_anh_minh_chung"
            type="url"
            class="form-control"
            placeholder="https://... (tùy chọn)"
          />
        </div>

        <div class="d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-outline-secondary" @click="$router.push('/rescuer/home')">Quay lại</button>
          <button type="button" class="btn btn-success" :disabled="submitting || !assignmentId" @click="guiBaoCao">
            <span v-if="submitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
            Gửi báo cáo & hoàn thành nhiệm vụ
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { createToaster } from "@meforma/vue-toaster";
import { assignmentAPI, rescueRequestAPI, rescueResultAPI } from "../../../services/api";

const toaster = createToaster({ position: "top-right" });

export default {
  name: "RescuerBaoCao",
  data() {
    return {
      loading: false,
      submitting: false,
      assignment: null,
      assignmentId: Number(this.$route.query.id_phan_cong || 0) || null,
      requestId: Number(this.$route.query.id_yeu_cau || 0) || null,
      form: {
        bao_cao_hien_truong: "",
        trang_thai_ket_qua: "HOAN_THANH",
        hinh_anh_minh_chung: "",
        thoi_gian_ket_thuc: "",
      },
      evidenceFile: null,
    };
  },
  async mounted() {
    if (!this.assignmentId) return;
    this.loading = true;
    try {
      const res = await assignmentAPI.getDetail(this.assignmentId);
      this.assignment = res.data || null;
      if (!this.requestId) this.requestId = this.assignment?.id_yeu_cau || null;
    } catch (e) {
      console.error(e);
    } finally {
      this.loading = false;
    }
  },
  methods: {
    onFileChange(event) {
      const file = event.target.files?.[0] || null;
      this.evidenceFile = file;
    },
    async guiBaoCao() {
      if (!this.assignmentId) return;
      if (!this.form.bao_cao_hien_truong?.trim()) {
        toaster.warning("Vui lòng nhập báo cáo hiện trường.");
        return;
      }
      this.submitting = true;
      try {
        const payload = new FormData();
        payload.append("bao_cao_hien_truong", this.form.bao_cao_hien_truong || "");
        payload.append("trang_thai_ket_qua", this.form.trang_thai_ket_qua || "HOAN_THANH");
        payload.append("thoi_gian_ket_thuc", new Date().toISOString().slice(0, 19).replace("T", " "));
        if (this.form.hinh_anh_minh_chung) {
          payload.append("hinh_anh_minh_chung", this.form.hinh_anh_minh_chung);
        }
        if (this.evidenceFile) {
          payload.append("hinh_anh_minh_chung_file", this.evidenceFile);
        }
        await rescueResultAPI.createForAssignment(this.assignmentId, payload);
        await assignmentAPI.changeStatus(this.assignmentId, { trang_thai_nhiem_vu: "HOAN_THANH" });
        if (this.requestId) {
          await rescueRequestAPI.changeStatus(this.requestId, { trang_thai: "HOAN_THANH" });
        }
        toaster.success("Đã gửi báo cáo minh chứng và hoàn thành nhiệm vụ.");
        this.$router.push("/rescuer/home");
      } catch (e) {
        console.error(e);
        toaster.error(e?.response?.data?.message || "Không gửi được báo cáo.");
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>
