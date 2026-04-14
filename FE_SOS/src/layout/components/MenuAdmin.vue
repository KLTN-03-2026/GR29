<template>
  <div class="admin-menu d-flex flex-column h-100">
    <div class="px-3 pt-3 pb-2 d-flex align-items-center">
      <div class="brand-icon me-2">
        <i class="fa-solid fa-wave-square"></i>
      </div>
      <div>
        <div class="fw-semibold small text-white">nowSOS</div>
        <div class="text-secondary-emphasis text-uppercase" style="font-size: 10px;">Điều phối cứu hộ</div>
      </div>
    </div>

    <hr class="border-secondary border-opacity-25 my-2" />

    <nav class="flex-grow-1 px-2 pb-3 overflow-auto">
      <div class="text-uppercase text-secondary-emphasis fw-semibold small px-2 mb-2">Tổng quan</div>
      <router-link v-slot="{ href, navigate, isExactActive }" to="/admin" custom>
        <a :href="href" class="nav-item-link" :class="{ 'nav-item-link--active': isExactActive }"
          @click="(e) => { e.preventDefault(); navigate(); }">
          <i class="fa-solid fa-gauge-high me-2"></i>Dashboard tổng quan
        </a>
      </router-link>
      <router-link class="nav-item-link" to="/admin/queue">
        <i class="fa-solid fa-list-check me-2"></i>Hàng đợi theo ưu tiên
      </router-link>
      <router-link class="nav-item-link" to="/admin/dang-xu-ly">
        <i class="fa-solid fa-spinner me-2"></i>Đang Xử Lý
      </router-link>
      <router-link class="nav-item-link" to="/admin/assignments">
        <i class="fa-solid fa-person-military-pointing me-2"></i>Phân công đội cứu hộ
      </router-link>
      <router-link class="nav-item-link" to="/admin/da-hoan-thanh">
        <i class="fa-solid fa-circle-check me-2"></i>Đã Hoàn Thành
      </router-link>

      <div class="text-uppercase text-secondary-emphasis fw-semibold small px-2 mt-3 mb-2">Giám sát</div>
      <router-link class="nav-item-link" to="/admin/heatmap">
        <i class="fa-solid fa-fire me-2"></i>Bản đồ nhiệt nguy hiểm
      </router-link>
      <router-link class="nav-item-link" to="/admin/tracking">
        <i class="fa-solid fa-route me-2"></i>Vị trí đội (real-time)
      </router-link>
      <router-link class="nav-item-link" to="/admin/reports">
        <i class="fa-solid fa-chart-column me-2"></i>Báo cáo thống kê
      </router-link>

      <div class="text-uppercase text-secondary-emphasis fw-semibold small px-2 mt-3 mb-2">Cấu hình</div>
      <router-link class="nav-item-link" to="/admin/incident-types">
        <i class="fa-solid fa-bolt me-2"></i>Loại sự cố
      </router-link>
      <router-link class="nav-item-link" to="/admin/resources">
        <i class="fa-solid fa-helmet-safety me-2"></i>Đội cứu hộ & tài nguyên
      </router-link>
      <router-link class="nav-item-link" to="/admin/ai-scoring">
        <i class="fa-solid fa-brain me-2"></i>Trọng số AI scoring
      </router-link>


      <div class="text-uppercase text-secondary-emphasis fw-semibold small px-2 mt-3 mb-2">Tài khoản & phân quyền</div>
      <router-link class="nav-item-link" to="/admin/accounts/admin">
        <i class="fa-solid fa-user-shield me-2"></i>Tài khoản ADMIN
      </router-link>

      <router-link class="nav-item-link" to="/admin/accounts/user">
        <i class="fa-solid fa-user me-2"></i>Tài khoản USER
      </router-link>

      <router-link class="nav-item-link" to="/admin/accounts/rescuer">
        <i class="fa-solid fa-user-nurse me-2"></i>Tài khoản RESCUER
      </router-link>
    </nav>
  </div>
</template>

<script>
export default {
  name: "MenuAdmin",
};
</script>

<style scoped>
.admin-menu {
  background: radial-gradient(circle at top, #111827 0, #020617 48%, #020617 100%);
  height: 100%;
}

.brand-icon {
  width: 30px;
  height: 30px;
  border-radius: 10px;
  background: rgba(248, 250, 252, 0.1);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #f97316;
  font-size: 14px;
}

.nav-item-link {
  display: flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 0.6rem;
  margin-bottom: 0.1rem;
  color: #9ca3af;
  text-decoration: none;
  font-size: 0.85rem;
  transition: background-color 0.16s ease, color 0.16s ease;
}

.nav-item-link:hover {
  background-color: rgba(55, 65, 81, 0.65);
  color: #e5e7eb;
}

.nav-item-link.router-link-active,
.nav-item-link.router-link-exact-active,
.nav-item-link.nav-item-link--active {
  background: linear-gradient(90deg, #f97316, #fb923c);
  color: #111827;
  font-weight: 600;
}
</style>
