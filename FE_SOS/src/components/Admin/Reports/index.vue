<template>
  <div class="container-fluid px-0">
    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-xl-3" v-for="card in stats" :key="card.title">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small text-uppercase fw-semibold mb-1">{{ card.title }}</div>
            <div class="h4 mb-0 fw-bold">{{ card.value }}</div>
            <small :class="card.hintClass">{{ card.hint }}</small>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0">
            <h6 class="mb-0 fw-semibold">Yêu cầu cứu hộ theo tháng (demo)</h6>
            <small class="text-muted">Có thể nối API thống kê sau — không dùng doanh thu / sản phẩm</small>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-end gap-1" style="height: 220px">
              <div
                v-for="(h, i) in monthlyBars"
                :key="i"
                class="flex-grow-1 rounded-top"
                :style="{
                  height: h + '%',
                  background: 'linear-gradient(180deg, #f97316, #fb923c)',
                  minHeight: '8px',
                }"
                :title="'Tháng ' + (i + 1)"
              />
            </div>
            <div class="d-flex justify-content-between small text-muted mt-2">
              <span v-for="m in 12" :key="m">{{ m }}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0">
            <h6 class="mb-0 fw-semibold">Theo loại sự cố</h6>
            <small class="text-muted">Tỷ lệ ước lượng</small>
          </div>
          <div class="card-body">
            <div v-for="row in typeBreakdown" :key="row.label" class="mb-3">
              <div class="d-flex justify-content-between small mb-1">
                <span>{{ row.label }}</span>
                <span class="text-muted">{{ row.pct }}%</span>
              </div>
              <div class="progress" style="height: 6px">
                <div
                  class="progress-bar bg-warning"
                  role="progressbar"
                  :style="{ width: row.pct + '%' }"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0">
        <h6 class="mb-0 fw-semibold">Gợi ý báo cáo (SOS)</h6>
        <small class="text-muted">Giống hướng SmartReportAI: thống kê, không phải kho inventory</small>
      </div>
      <div class="card-body small text-muted">
        <ul class="mb-0">
          <li>Thời gian phản hồi &amp; xử lý theo loại sự cố / khu vực.</li>
          <li>Hiệu suất từng đội cứu hộ (số ca, thời gian trung bình).</li>
          <li>Mật độ sự cố theo thời gian (heatmap dữ liệu).</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminReports",
  data() {
    return {
      stats: [
        {
          title: "Tổng yêu cầu (30 ngày)",
          value: "—",
          hint: "Nối API /thong-ke",
          hintClass: "text-muted",
        },
        {
          title: "Đang xử lý",
          value: "—",
          hint: "Theo trạng thái",
          hintClass: "text-muted",
        },
        {
          title: "Hoàn tất",
          value: "—",
          hint: "Trong kỳ",
          hintClass: "text-muted",
        },
        {
          title: "Ưu tiên cao chờ",
          value: "—",
          hint: "Cần điều phối",
          hintClass: "text-warning",
        },
      ],
      monthlyBars: [45, 52, 48, 61, 55, 70, 66, 72, 68, 74, 80, 76],
      typeBreakdown: [
        { label: "Tai nạn giao thông", pct: 32 },
        { label: "Hỏa hoạn / cháy", pct: 24 },
        { label: "Y tế khẩn cấp", pct: 18 },
        { label: "Ngập lụt / thiên tai", pct: 16 },
        { label: "Khác", pct: 10 },
      ],
    };
  },
};
</script>

<style scoped>
.card:hover {
  transform: translateY(-1px);
  transition: transform 0.2s;
}
</style>
