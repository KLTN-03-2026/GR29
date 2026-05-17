<template>
  <div class="admin-menu d-flex flex-column h-100">
    <!-- Brand -->
    <div class="menu-brand px-4 py-3 d-flex align-items-center gap-3">
      <div class="brand-icon">
        <i class="fa-solid fa-wave-square"></i>
      </div>
      <div>
        <div class="brand-name">nowSOS</div>
        <div class="brand-sub">Điều phối cứu hộ</div>
      </div>
    </div>

    <div class="menu-divider mx-3"></div>

    <!-- Navigation -->
    <nav class="flex-grow-1 px-3 pb-4 pt-2 overflow-auto">
      <!-- Tổng quan -->
      <div class="nav-section-label">Tổng quan</div>

      <router-link v-slot="{ href, navigate, isExactActive }" to="/admin" custom>
        <a :href="href" class="nav-item-link" :class="{ 'nav-item-link--active': isExactActive }"
          @click="(e) => { e.preventDefault(); navigate(); }">
          <i class="fa-solid fa-gauge-high nav-icon"></i>Dashboard tổng quan
        </a>
      </router-link>

      <router-link class="nav-item-link" to="/admin/assignments">
        <i class="fa-solid fa-person-military-pointing nav-icon"></i>Phân công đội cứu hộ
      </router-link>

      <router-link class="nav-item-link" to="/admin/theo-doi-cuu-ho">
        <i class="fa-solid fa-spinner nav-icon"></i>Theo dõi cứu hộ
      </router-link>

      <router-link class="nav-item-link" to="/admin/da-hoan-thanh">
        <i class="fa-solid fa-circle-check nav-icon"></i>Đã Hoàn Thành
      </router-link>

      <!-- Giám sát -->
      <div class="nav-section-label mt-3">Giám sát</div>

      <router-link class="nav-item-link" to="/admin/heatmap">
        <i class="fa-solid fa-fire nav-icon"></i>Bản đồ nhiệt nguy hiểm
      </router-link>

      <router-link class="nav-item-link" to="/admin/tracking">
        <i class="fa-solid fa-route nav-icon"></i>Vị trí đội
      </router-link>

      <router-link class="nav-item-link" to="/admin/reports">
        <i class="fa-solid fa-chart-column nav-icon"></i>Báo cáo thống kê
      </router-link>

      <!-- Cấu hình -->
      <div v-if="canViewConfig" class="nav-section-label mt-3">Cấu hình</div>

      <router-link v-if="canViewConfig" class="nav-item-link" to="/admin/realtime-dispatch">
        <i class="fa-solid fa-bolt nav-icon"></i>Auto Dispatch
      </router-link>

      <!-- Quản lý -->
      <div v-if="canViewAccounts" class="nav-section-label mt-3">Quản Lý</div>

      <router-link v-if="canViewConfig" class="nav-item-link" to="/admin/tai-nguyen">
        <i class="fa-solid fa-helmet-safety nav-icon"></i>Tài nguyên
      </router-link>

      <router-link v-if="canViewConfig" class="nav-item-link" to="/admin/doi-cuu-ho">
        <i class="fa-solid fa-helmet-safety nav-icon"></i>Đội cứu hộ
      </router-link>

      <router-link v-if="canViewAccounts" class="nav-item-link" to="/admin/accounts/admin">
        <i class="fa-solid fa-user-shield nav-icon"></i>Tài khoản ADMIN
      </router-link>

      <router-link v-if="canViewAccounts" class="nav-item-link" to="/admin/accounts/user">
        <i class="fa-solid fa-user nav-icon"></i>Tài khoản USER
      </router-link>

      <router-link v-if="canViewAccounts" class="nav-item-link" to="/admin/accounts/rescuer">
        <i class="fa-solid fa-user-nurse nav-icon"></i>Tài khoản RESCUER
      </router-link>
    </nav>

    <!-- Bottom version badge -->
    <div class="menu-footer px-4 py-3">
      <span class="version-tag">
        <i class="fa-solid fa-circle-check me-1"></i> nowSOS v1.0
      </span>
    </div>
  </div>
</template>

<script>
import { ADMIN, MANAGER_OPERATOR, OPERATOR } from "../../constants/roles.js";

const ROLE_SLUGS = {
  [ADMIN]: "admin",
  [MANAGER_OPERATOR]: "manager_operator",
  [OPERATOR]: "operator",
};

export default {
  name: "MenuAdmin",
  computed: {
    currentRole() {
      try {
        const raw = localStorage.getItem("admin_user");
        if (!raw) return null;
        const user = JSON.parse(raw);
        return user.chuc_vu?.slug_chuc_vu
          || user.chucVu?.slug_chuc_vu
          || ROLE_SLUGS[user.id_chuc_vu || user.chuc_vu?.id_chuc_vu || user.chucVu?.id_chuc_vu]
          || null;
      } catch {
        return null;
      }
    },
    canViewConfig() {
      return ["admin", "manager_operator"].includes(this.currentRole);
    },
    canViewAccounts() {
      return this.currentRole === "admin";
    },
  },
};
</script>

<style scoped>
/* ─── Sidebar Shell ────────────────────────────────────── */
.admin-menu {
  background: #ffffff;
  height: 100%;
  border-right: 1px solid #e9ecef;
}

/* ─── Brand ────────────────────────────────────────────── */
.menu-brand {
  height: 56px; /* match topbar height */
}

.brand-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: linear-gradient(135deg, #2563eb, #3b82f6);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  font-size: 15px;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
}

.brand-name {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  letter-spacing: -0.3px;
  line-height: 1.2;
}

.brand-sub {
  font-size: 10.5px;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  font-weight: 500;
}

/* ─── Divider ──────────────────────────────────────────── */
.menu-divider {
  height: 1px;
  background: #f1f5f9;
}

/* ─── Section label ────────────────────────────────────── */
.nav-section-label {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.7px;
  color: #94a3b8;
  padding: 2px 8px 6px;
}

/* ─── Nav links ────────────────────────────────────────── */
.nav-item-link {
  display: flex;
  align-items: center;
  padding: 8px 10px;
  border-radius: 8px;
  margin-bottom: 2px;
  color: #475569;
  text-decoration: none;
  font-size: 13.5px;
  font-weight: 500;
  transition: background 0.14s ease, color 0.14s ease;
  gap: 10px;
}

.nav-icon {
  font-size: 13px;
  width: 16px;
  text-align: center;
  flex-shrink: 0;
  color: #94a3b8;
  transition: color 0.14s;
}

.nav-item-link:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.nav-item-link:hover .nav-icon {
  color: #2563eb;
}

/* Active state */
.nav-item-link.router-link-active,
.nav-item-link.router-link-exact-active,
.nav-item-link.nav-item-link--active {
  background: #eff6ff;
  color: #2563eb;
  font-weight: 600;
}

.nav-item-link.router-link-active .nav-icon,
.nav-item-link.router-link-exact-active .nav-icon,
.nav-item-link.nav-item-link--active .nav-icon {
  color: #2563eb;
}

/* ─── Footer ───────────────────────────────────────────── */
.menu-footer {
  border-top: 1px solid #f1f5f9;
}

.version-tag {
  font-size: 11.5px;
  color: #94a3b8;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 4px 10px;
  display: inline-flex;
  align-items: center;
}
</style>
