<template>
  <div class="bg-light min-vh-100 p-4 p-md-5">
    <div class="mb-5">
      <h1 class="fw-bold" style="color: #1a2a40;"> Yêu cầu của bạn</h1>
      <p class="text-secondary text-uppercase small fw-bold tracking-wider">THEO DÕI CÁC TÍN HIỆU KHẨN CẤP CỦA BẠN</p>
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

            <div class="row">
                <div class="col-md-6 lg-6">
                    <button class="btn btn-secondary">
                        Hủy Yêu Cầu
                    </button>
                </div>
            </div>
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
        
      ]
    }
  }
}
</script>

<style>

</style>