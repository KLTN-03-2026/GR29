<template>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0 fw-semibold">Cấu hình trọng số AI scoring</h5>
        <small class="text-muted">Điều chỉnh mức độ ảnh hưởng của từng yếu tố</small>
      </div>
      <button class="btn btn-sm btn-primary">
        <i class="fa-solid fa-floppy-disk me-1"></i>Lưu cấu hình
      </button>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-3" v-for="w in weights" :key="w.label">
          <div class="border rounded-3 p-3 h-100">
            <div class="small text-muted mb-1">{{ w.label }}</div>
            <div class="d-flex align-items-baseline gap-1">
              <div class="h4 mb-0">{{ w.value }}</div>
              <span class="text-muted small">/ 1.0</span>
            </div>
            <div class="small text-muted mt-2">{{ w.desc }}</div>
          </div>
        </div>
      </div>
      <div class="alert alert-info small mt-3 mb-0">
        Sau này bạn có thể:
        <ul class="mb-0">
          <li>Thay slider để chỉnh trọng số.</li>
          <li>Lưu nhiều profile cấu hình và A/B test.</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminAIScoring",
  data() {
    return {
      weights: [
        {
          label: "Mức độ nguy hiểm",
          value: 0.4,
          desc: "Tính mạng, số người bị nạn, rủi ro lan rộng.",
        },
        {
          label: "Khoảng cách đội gần nhất",
          value: 0.25,
          desc: "Ảnh hưởng đến thời gian tiếp cận.",
        },
        {
          label: "Điều kiện thời tiết / địa hình",
          value: 0.2,
          desc: "Mưa bão, ngập lụt, khu vực khó tiếp cận.",
        },
        {
          label: "Lịch sử tần suất sự cố",
          value: 0.15,
          desc: "Khu vực thường xuyên xảy ra sự cố.",
        },
      ],
    };
  },
};
</script>

