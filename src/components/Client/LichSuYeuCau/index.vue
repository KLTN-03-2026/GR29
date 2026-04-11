<template>
  <div class="bg-light min-vh-100 p-4 p-md-5">
    <div class="mb-5">
      <h1 class="fw-bold" style="color: #1a2a40;">Lịch sử Yêu cầu Cứu hộ</h1>
      <p class="text-secondary text-uppercase small fw-bold tracking-wider">QUẢN LÝ VÀ THEO DÕI CÁC TÍN HIỆU KHẨN CẤP CỦA BẠN</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-3 mb-5">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-lg-6">
          <label class="form-label small fw-bold text-muted">TÌM KIẾM MÃ YÊU CẦU</label>
          <div class="input-group border rounded-2 bg-white">
            <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control border-0 shadow-none p-2" placeholder="SOS-XXXXX">
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label small fw-bold text-muted">LOẠI SỰ CỐ</label>
          <select class="form-select border rounded-2 shadow-none p-2">
            <option selected>Tất cả</option>
            <option>Lương thực</option>
            <option>Ngập lụt</option>
            <option>Y tế</option>
            <option>Cháy nổ</option>
          </select>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label small fw-bold text-muted">TRẠNG THÁI</label>
          <select class="form-select border rounded-2 shadow-none p-2">
            <option selected>Tất cả</option>
            <option>Hoàn thành</option>
            <option>Đang xử lý</option>
            <option>Đã hủy</option>
          </select>
        </div>
        <div class="col-12 col-lg-2">
          <button class="btn btn-dark w-100 py-2 fw-bold d-flex align-items-center justify-content-center h-100" style="background-color: #1a2a40;">
            <i class="bi bi-filter-left fs-5 me-2 text-white"></i> Lọc kết quả
          </button>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div v-for="item in danhsach" :key="item.id" class="col-12 col-md-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 border">SOS-{{ item.id }}</span>
              <span :class="['badge rounded-pill px-3 py-2', item.statusBg]">
                <i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i> {{ item.statusText }}
              </span>
            </div>

            <div class="d-flex align-items-start mb-4">
              <div :class="['rounded-3 p-3 me-3', item.iconBg]">
                <i :class="['bi fs-4', item.icon, item.iconColor]"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-1">{{ item.loai }}</h5>
                <p class="text-muted small mb-0">{{ item.time }}</p>
              </div>
            </div>

            <div class="d-flex align-items-center text-muted small mb-4">
              <i class="bi bi-geo-alt me-2 text-danger"></i>
              <span class="text-truncate">{{ item.address }}</span>
            </div>

            <button class="btn btn-outline-dark w-100 py-2 fw-bold rounded-2 text-uppercase">
              Đánh Giá
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      danhsach: [
        {
          id: '12345',
          loai: 'Ngập lụt',
          time: '14:30 - 25/12/2023',
          address: '123 Lê Lợi, Quận 1, TP.HCM',
          statusText: 'Hoàn thành',
          statusBg: 'bg-success bg-opacity-10 text-success',
          icon: 'bi-water',
          iconColor: 'text-primary',
          iconBg: 'bg-primary bg-opacity-10'
        },
        {
          id: '12346',
          loai: 'Y tế',
          time: '10:15 - 26/12/2023',
          address: '456 Nguyễn Huệ, Quận 1, TP.HCM',
          statusText: 'Đang xử lý',
          statusBg: 'bg-warning bg-opacity-10 text-warning',
          icon: 'bi-hospital-fill',
          iconColor: 'text-warning',
          iconBg: 'bg-warning bg-opacity-10'
        },
        {
          id: '12347',
          loai: 'Cháy nổ',
          time: '21:45 - 24/12/2023',
          address: '789 Trần Hưng Đạo, Quận 5, TP.HCM',
          statusText: 'Đã hủy',
          statusBg: 'bg-secondary bg-opacity-10 text-secondary border',
          icon: 'bi-fire',
          iconColor: 'text-danger',
          iconBg: 'bg-danger bg-opacity-10'
        },
        {
          id: '12348',
          loai: 'Lương thực',
          time: '08:00 - 20/12/2023',
          address: 'Cao tốc Long Thành - Dầu Giây',
          statusText: 'Hoàn thành',
          statusBg: 'bg-success bg-opacity-10 text-success',
          icon: 'bi-box-seam-fill',
          iconColor: 'text-success',
          iconBg: 'bg-success bg-opacity-10'
        }
      ]
    }
  }
}
</script>

<style>

</style>