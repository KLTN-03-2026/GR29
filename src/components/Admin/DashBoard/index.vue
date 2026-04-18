<template>
  <div class="container-fluid px-0">
    <div class="row g-3 mb-3">
      <div class="col-12 col-lg-3" v-for="card in summaryCards" :key="card.title">
        <div class="card summary-card h-100 border-0 shadow-sm">
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <div class="text-muted text-uppercase small fw-semibold">{{ card.title }}</div>
                <div class="h4 mb-0 fw-bold">{{ card.value }}</div>
              </div>
              <div class="summary-icon" :class="card.iconBg">
                <i :class="card.icon"></i>
              </div>
            </div>
            <div class="d-flex align-items-center small">
              <span :class="card.trendClass">
                <i :class="card.trendIcon" class="me-1"></i>{{ card.trend }}
              </span>
              <span class="text-muted ms-2">{{ card.caption }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-0 fw-semibold">Hàng đợi yêu cầu theo mức ưu tiên</h6>
                <small class="text-muted">Tổng quan các yêu cầu đang chờ xử lý</small>
              </div>
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary active">Hôm nay</button>
                <button class="btn btn-outline-secondary">7 ngày</button>
                <button class="btn btn-outline-secondary">30 ngày</button>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr class="small text-muted">
                    <th>Mã yêu cầu</th>
                    <th>Loại sự cố</th>
                    <th>Khu vực</th>
                    <th>Mức ưu tiên</th>
                    <th>Thời gian chờ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in queuePreview" :key="item.code">
                    <td class="fw-semibold">{{ item.code }}</td>
                    <td>{{ item.type }}</td>
                    <td>{{ item.area }}</td>
                    <td>
                      <span class="badge rounded-pill" :class="item.badgeClass">
                        {{ item.priority }}
                      </span>
                    </td>
                    <td class="text-muted small">{{ item.wait }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pb-0">
            <h6 class="mb-0 fw-semibold">Bản đồ nhiệt khu vực nguy hiểm</h6>
            <small class="text-muted">Demo UI (sẽ gắn map sau)</small>
          </div>
          <div class="card-body">
            <div class="heatmap-map rounded-4 mb-3 overflow-hidden border">
              <MapboxMap :zoom="11" />
            </div>
            <ul class="list-unstyled small mb-0">
              <li v-for="zone in dangerZones" :key="zone.name" class="d-flex justify-content-between mb-1">
                <span>
                  <span class="legend-dot me-2" :style="{ background: zone.color }"></span>{{ zone.name }}
                </span>
                <span class="text-muted">{{ zone.count }} yêu cầu</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pb-0">
            <h6 class="mb-0 fw-semibold">Trạng thái đội cứu hộ</h6>
            <small class="text-muted">Theo dõi real-time (sẽ kết nối sau)</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr class="small text-muted">
                    <th>Đội</th>
                    <th>Trạng thái</th>
                    <th>Vị trí</th>
                    <th>Nhiệm vụ hiện tại</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="team in teams" :key="team.name">
                    <td class="fw-semibold">{{ team.name }}</td>
                    <td>
                      <span class="badge rounded-pill" :class="team.badgeClass">
                        {{ team.status }}
                      </span>
                    </td>
                    <td class="text-muted small">{{ team.location }}</td>
                    <td class="small">{{ team.mission }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-0 fw-semibold">Tóm tắt cấu hình hệ thống</h6>
              <small class="text-muted">AI scoring, loại sự cố, phân quyền</small>
            </div>
            <router-link to="/admin/ai-scoring" class="btn btn-sm btn-outline-primary">
              Chi tiết
            </router-link>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mb-0 small">
              <li class="d-flex justify-content-between align-items-center mb-2">
                <span>Trọng số AI scoring</span>
                <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill">
                  Đang dùng bản cấu hình 1.2
                </span>
              </li>
              <li class="d-flex justify-content-between align-items-center mb-2">
                <span>Loại sự cố được cấu hình</span>
                <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                  8 loại
                </span>
              </li>
              <li class="d-flex justify-content-between align-items-center mb-2">
                <span>Đội cứu hộ & tài nguyên</span>
                <span class="badge bg-success-subtle text-success-emphasis rounded-pill">
                  12 đội • 34 phương tiện
                </span>
              </li>
              <li class="d-flex justify-content-between align-items-center">
                <span>Tài khoản & phân quyền</span>
                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">
                  3 vai trò • 18 tài khoản
                </span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";

export default {
  name: "AdminDashboard",
  components: { MapboxMap },
  data() {
    return {
      summaryCards: [
        {
          title: "Yêu cầu hôm nay",
          value: "128",
          trend: "+18%",
          caption: "so với hôm qua",
          icon: "fa-solid fa-signal",
          trendClass: "text-success",
          trendIcon: "fa-solid fa-arrow-trend-up",
          iconBg: "bg-primary-subtle text-primary-emphasis",
        },
        {
          title: "Đang xử lý",
          value: "37",
          trend: "-5%",
          caption: "thời gian chờ trung bình",
          icon: "fa-solid fa-helmet-safety",
          trendClass: "text-success",
          trendIcon: "fa-solid fa-arrow-down",
          iconBg: "bg-info-subtle text-info-emphasis",
        },
        {
          title: "Hoàn tất hôm nay",
          value: "91",
          trend: "+9%",
          caption: "tỷ lệ hoàn thành",
          icon: "fa-solid fa-circle-check",
          trendClass: "text-success",
          trendIcon: "fa-solid fa-arrow-up",
          iconBg: "bg-success-subtle text-success-emphasis",
        },
        {
          title: "Báo động ưu tiên cao",
          value: "6",
          trend: "3 đang chờ",
          caption: "cần ưu tiên xử lý",
          icon: "fa-solid fa-triangle-exclamation",
          trendClass: "text-danger",
          trendIcon: "fa-solid fa-bolt",
          iconBg: "bg-danger-subtle text-danger-emphasis",
        },
      ],
      queuePreview: [
        {
          code: "#REQ-2026-001",
          type: "Tai nạn giao thông",
          area: "Cầu Rồng - ĐN",
          priority: "Rất cao",
          wait: "05 phút",
          badgeClass: "bg-danger-subtle text-danger-emphasis",
        },
        {
          code: "#REQ-2026-014",
          type: "Ngập lụt",
          area: "Hòa Khánh Nam",
          priority: "Cao",
          wait: "18 phút",
          badgeClass: "bg-warning-subtle text-warning-emphasis",
        },
        {
          code: "#REQ-2026-023",
          type: "Cấp cứu y tế",
          area: "Ngũ Hành Sơn",
          priority: "Cao",
          wait: "09 phút",
          badgeClass: "bg-warning-subtle text-warning-emphasis",
        },
      ],
      dangerZones: [
        { name: "Hải Châu", count: 24, color: "#ef4444" },
        { name: "Thanh Khê", count: 17, color: "#f97316" },
        { name: "Liên Chiểu", count: 12, color: "#eab308" },
      ],
      teams: [
        {
          name: "Đội 01 - Trung tâm",
          status: "Đang làm nhiệm vụ",
          location: "Gần Cầu Rồng",
          mission: "Tai nạn giao thông nhiều phương tiện",
          badgeClass: "bg-primary-subtle text-primary-emphasis",
        },
        {
          name: "Đội 03 - Cứu hỏa",
          status: "Sẵn sàng",
          location: "Trạm Hòa Khánh",
          mission: "Chưa có",
          badgeClass: "bg-success-subtle text-success-emphasis",
        },
        {
          name: "Đội 07 - Y tế",
          status: "Đang di chuyển",
          location: "Hướng Ngũ Hành Sơn",
          mission: "Hỗ trợ cấp cứu khẩn cấp",
          badgeClass: "bg-info-subtle text-info-emphasis",
        },
      ],
    };
  },
};
</script>

<style scoped>
.summary-card {
  border-radius: 1rem;
}

.summary-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.9rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.heatmap-map {
  height: 250px;
}

.legend-dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 999px;
}
</style>