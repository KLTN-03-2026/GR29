<template>
  <header class="admin-topbar navbar navbar-expand px-3 py-2">
    <div class="d-flex align-items-center gap-3 flex-grow-1">
      <button class="btn btn-sm btn-outline-light d-md-none" type="button">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="d-flex align-items-center gap-2">
        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis small fw-semibold">
          ADMIN CONSOLE
        </span>
        <span class="text-white-50 small d-none d-sm-inline">
          Giám sát & điều phối cứu hộ
        </span>
      </div>
    </div>

    <div class="d-flex align-items-center gap-3">
      <div class="d-none d-md-flex align-items-center gap-2 text-white-50 small">
        <i class="fa-regular fa-clock"></i>
        <span>Trực 24/7</span>
      </div>
      <button class="btn btn-sm btn-outline-light rounded-circle">
        <i class="bi bi-bell"></i>
      </button>
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-light d-flex align-items-center rounded-pill px-2" type="button"
          data-bs-toggle="dropdown">
          <span class="avatar-initial me-2">AD</span>
          <span class="small text-white d-none d-sm-inline">{{ adminName }}</span>
          <i class="fa-solid fa-chevron-down ms-2 small text-white-50 d-none d-sm-inline"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li class="px-3 py-2 small text-muted">Khu vực quản trị</li>
          <li>
            <hr class="dropdown-divider my-1" />
          </li>
          <li>
            <button class="dropdown-item text-danger small" type="button" @click="logout">
              <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
            </button>
          </li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script>
export default {
  name: "TopAdmin",
  computed: {
    adminName() {
      try {
        const raw = localStorage.getItem("admin_user");
        if (!raw) return "Admin";
        const u = JSON.parse(raw);
        return u.ho_ten || u.email || "Admin";
      } catch {
        return "Admin";
      }
    },
  },
  methods: {
    logout() {
      localStorage.removeItem("admin_token");
      localStorage.removeItem("admin_user");
      this.$router.push("/admin/login");
    },
  },
};
</script>

<style scoped>
.admin-topbar {
  background: #111827;
  border-bottom: 1px solid rgba(31, 41, 55, 0.9);
  position: sticky;
  top: 0;
  z-index: 1020;
  flex-shrink: 0;
}

.avatar-initial {
  width: 26px;
  height: 26px;
  border-radius: 999px;
  background: #f97316;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
  color: #111827;
}
</style>