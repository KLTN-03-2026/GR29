<template>
  <div class="container py-4 py-lg-5">
    <div class="row g-4 align-items-stretch">
      <div class="col-12 col-lg-5">
        <div class="p-4 p-lg-5 rounded-4 shadow-sm border bg-white h-100">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center"
              style="width: 44px; height: 44px; background: rgba(255,152,0,.12); color: #ff9800;">
              <i class="fa-solid fa-headset"></i>
            </div>
            <div>
              <h3 class="fw-bold mb-0">Liên hệ</h3>
              <div class="text-muted small">Gửi phản hồi hoặc yêu cầu hỗ trợ</div>
            </div>
          </div>

          <div class="d-grid gap-2">
            <a class="btn btn-dark rounded-3 fw-semibold py-2" href="tel:113">
              <i class="fa-solid fa-phone me-2"></i>Gọi 113
            </a>
            <a class="btn btn-outline-dark rounded-3 fw-semibold py-2" href="tel:114">
              <i class="fa-solid fa-fire-extinguisher me-2"></i>Gọi 114
            </a>
            <a class="btn btn-outline-dark rounded-3 fw-semibold py-2" href="tel:115">
              <i class="fa-solid fa-truck-medical me-2"></i>Gọi 115
            </a>
          </div>

          <hr class="my-4" />

          <div class="small text-muted">
            - Thời gian phản hồi phụ thuộc tình hình thực tế.<br />
            - Hãy cung cấp thông tin ngắn gọn, rõ ràng, kèm địa chỉ/mốc gần nhất.
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-7">
        <div class="rounded-4 shadow-sm border bg-white overflow-hidden h-100">
          <div class="p-4 border-bottom">
            <div class="fw-semibold">Form hỗ trợ</div>
            <div class="text-muted small">Mình sẽ lưu lại nội dung (demo UI)</div>
          </div>

          <form class="p-4" @submit.prevent="submit">
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Họ và tên</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="fa-solid fa-user"></i></span>
                  <input v-model.trim="form.name" class="form-control" placeholder="VD: Nguyễn Văn A" />
                </div>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Số điện thoại</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="fa-solid fa-phone"></i></span>
                  <input v-model.trim="form.phone" class="form-control" placeholder="VD: 09xxxxxxx" />
                </div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Chủ đề</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="fa-solid fa-tag"></i></span>
                  <input v-model.trim="form.subject" class="form-control" placeholder="VD: Không gửi được yêu cầu" />
                </div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Nội dung</label>
                <textarea v-model.trim="form.message" class="form-control" rows="5"
                  placeholder="Mô tả ngắn gọn vấn đề bạn gặp..."></textarea>
              </div>
            </div>

            <div class="d-flex gap-2 justify-content-end mt-4">
              <button class="btn btn-outline-secondary rounded-3" type="button" @click="reset">
                <i class="fa-solid fa-rotate-left me-2"></i>Nhập lại
              </button>
              <button class="btn btn-warning rounded-3 fw-semibold" type="submit">
                <i class="fa-solid fa-paper-plane me-2"></i>Gửi
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ContactClient",
  data() {
    return {
      form: {
        name: "",
        phone: "",
        subject: "",
        message: "",
      },
    };
  },
  methods: {
    reset() {
      this.form = { name: "", phone: "", subject: "", message: "" };
    },
    submit() {
      // demo UI: không gọi API
      this.reset();
    },
  },
};
</script>

<style scoped>
.container {
  max-width: 1120px;
}
</style>

