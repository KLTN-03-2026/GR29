<template>
  <div class="history-page min-vh-100 p-4 p-md-5">
    <div class="mb-5">
      <h1 class="fw-bold history-title">Lịch sử Yêu cầu Cứu hộ</h1>
      <p class="history-subtitle text-uppercase small fw-bold tracking-wider">QUẢN LÝ VÀ THEO DÕI CÁC TÍN HIỆU KHẨN CẤP CỦA BẠN</p>
    </div>

    <div class="history-filter card border-0 rounded-4 p-3 mb-5">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-lg-6">
          <label class="form-label small fw-bold text-muted">TÌM KIẾM MÃ YÊU CẦU</label>
          <div class="input-group history-input rounded-3 bg-white">
            <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control border-0 shadow-none p-2" placeholder="SOS-XXXXX">
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label small fw-bold text-muted">LOẠI SỰ CỐ</label>
          <select class="form-select history-select rounded-3 shadow-none p-2">
            <option selected>Tất cả</option>
            <option>Lương thực</option>
            <option>Ngập lụt</option>
            <option>Y tế</option>
            <option>Cháy nổ</option>
          </select>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label small fw-bold text-muted">TRẠNG THÁI</label>
          <select class="form-select history-select rounded-3 shadow-none p-2">
            <option selected>Tất cả</option>
            <option>Hoàn thành</option>
            <option>Đang xử lý</option>
            <option>Đã hủy</option>
          </select>
        </div>
        <div class="col-12 col-lg-2">
          <button class="btn history-filter-btn w-100 py-2 fw-bold d-flex align-items-center justify-content-center h-100">
            <i class="bi bi-filter-left fs-5 me-2 text-white"></i> Lọc kết quả
          </button>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div v-for="item in completedDanhSach" :key="item.id" class="col-12 col-md-6 col-xl-4">
        <div class="history-card card h-100 border-0 rounded-4 overflow-hidden">
          <div class="history-card__glow"></div>
          <div class="card-body p-4 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
              <div>
                <span class="history-code">SOS-{{ item.id }}</span>
                <p class="history-label mb-0 mt-2">Yêu cầu đã được lưu vào lịch sử</p>
              </div>
              <span :class="['badge rounded-pill px-3 py-2 fw-semibold history-badge text-light', item.statusBg]">
                <i class="bi bi-circle-fill me-2 history-badge__dot"></i>{{ item.statusText }}
              </span>
            </div>

            <div class="d-flex align-items-start gap-3 mb-4">
              <!-- <div :class="['history-icon d-flex align-items-center justify-content-center flex-shrink-0', item.iconBg]">
                <i :class="['bi fs-4', item.icon, item.iconColor]"></i>
              </div> -->
              <div class="flex-grow-1">
                <h5 class="fw-bold mb-2 history-type">{{ item.loai }}</h5>
                <div class="history-meta">
                  <i class="bi bi-clock-history"></i>
                  <span>{{ item.time }}</span>
                </div>
              </div>
            </div>

            <div class="history-location mb-4">
              <div class="history-meta align-items-start">
                <i class="bi bi-geo-alt-fill text-danger mt-1"></i>
                <span>{{ item.address }}</span>
              </div>
            </div>

            <div class="history-timeline mb-4">
              <span class="history-timeline__label">Kết quả xử lý</span>
              <span class="history-timeline__value">{{ item.statusText }}</span>
            </div>

            <button
              v-if="canReview(item)"
              class="btn history-review-btn w-100 py-2 fw-bold rounded-3 text-uppercase mt-auto"
            >
              <i class="bi bi-star me-2"></i>Đánh Giá
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    canReview(item) {
      return item?.statusText === 'Hoàn thành'
    }
  },
  computed: {
    completedDanhSach() {
      return this.danhsach.filter(item => item.statusText === 'Hoàn thành')
    }
  },
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

<style scoped>
.history-page {
  background:
    radial-gradient(circle at top left, rgba(79, 140, 255, 0.12), transparent 28%),
    radial-gradient(circle at top right, rgba(27, 184, 144, 0.09), transparent 26%),
    #f6f8fb;
}

.history-title {
  color: #1a2a40;
  letter-spacing: -0.03em;
}

.history-subtitle {
  color: #7a8594;
  letter-spacing: 0.18em;
}

.history-filter {
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 18px 42px rgba(22, 34, 51, 0.07);
  backdrop-filter: blur(10px);
}

.history-input,
.history-select {
  border: 1px solid #dde5f0;
  background: #fff;
}

.history-filter-btn {
  background: linear-gradient(135deg, #1a2a40 0%, #304864 100%);
  color: #fff;
  border: 0;
}

.history-card {
  position: relative;
  background: rgba(255, 255, 255, 0.94);
  box-shadow: 0 20px 45px rgba(22, 34, 51, 0.08);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.history-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 26px 55px rgba(22, 34, 51, 0.14);
}

.history-card__glow {
  position: absolute;
  inset: 0 auto auto 0;
  width: 100%;
  height: 5px;
  background: linear-gradient(90deg, #4f8cff 0%, #1bb890 50%, #f1b24a 100%);
}

.history-code {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.8rem;
  border-radius: 999px;
  background: #f4f7fb;
  border: 1px solid #d9e2ef;
  color: #506176;
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.06em;
}

.history-label {
  color: #97a3b6;
  font-size: 0.78rem;
  font-weight: 600;
}

.history-badge__dot {
  font-size: 0.45rem;
}

.history-icon {
  width: 58px;
  height: 58px;
  border-radius: 18px;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
}

.history-type {
  color: #17263c;
  line-height: 1.3;
}

.history-meta {
  display: flex;
  gap: 0.6rem;
  color: #66768b;
  font-size: 0.92rem;
}

.history-location,
.history-timeline {
  padding: 0.95rem 1rem;
  border-radius: 18px;
  border: 1px solid #e6edf5;
}

.history-location {
  background: #f8fafc;
}

.history-timeline {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
}

.history-timeline__label {
  color: #7a8aa0;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.history-timeline__value {
  color: #1e314a;
  font-weight: 700;
}

.history-review-btn {
  min-height: 48px;
  background: linear-gradient(135deg, #1a2a40 0%, #304864 100%);
  color: #fff;
  border: 0;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.history-review-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 14px 26px rgba(26, 42, 64, 0.22);
  color: #fff;
}

@media (max-width: 767.98px) {
  .history-page {
    padding: 1.25rem !important;
  }

  .history-card:hover {
    transform: none;
  }

  .history-timeline {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
