<template>
  <header class="admin-topbar navbar navbar-expand px-3 py-2">
    <div class="d-flex align-items-center gap-3 flex-grow-1">
      <button class="btn btn-sm btn-outline-light d-md-none" type="button" @click="$emit('toggle-sidebar')">
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
            <router-link to="/admin/change-password" class="dropdown-item small">
              <i class="fa-solid fa-key me-2"></i>Đổi mật khẩu
            </router-link>
          </li>
          <li>
            <!-- Dùng @click programmatic vì nằm trong Bootstrap dropdown -->
            <button class="dropdown-item text-danger small" type="button" @click="showLogoutModal">
              <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
            </button>
          </li>
        </ul>
      </div>
    </div>
  </header>

  <!-- Modal xác nhận đăng xuất – đúng cấu trúc Bootstrap chuẩn như Client -->
  <div class="modal fade" id="adminLogoutModal" tabindex="-1" aria-labelledby="adminLogoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-light border-0">
          <h5 class="modal-title w-100 text-center fw-bold text-dark" id="adminLogoutModalLabel">
            <i class="fa-solid fa-circle-question text-warning me-2"></i>
            Xác nhận đăng xuất
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body text-center py-4">
          <div class="mb-3">
            <i class="fa-solid fa-right-from-bracket fa-3x text-danger mb-3"></i>
          </div>
          <h6 class="fw-semibold text-dark mb-2">Bạn muốn đăng xuất?</h6>
          <p class="text-muted mb-0 small">Bạn sẽ cần đăng nhập lại để tiếp tục quản trị hệ thống.</p>
        </div>
        <div class="modal-footer border-0 bg-light">
          <div class="d-flex w-100 gap-2 px-3">
            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
              <i class="fa-solid fa-xmark me-1"></i> Hủy
            </button>
            <button type="button" class="btn btn-danger w-100" @click="logout">
              <i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
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
    showLogoutModal() {
      const modalEl = document.getElementById("adminLogoutModal");
      if (modalEl) {
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();
      }
    },
    logout() {
      // Đóng modal trước khi đăng xuất
      const modalEl = document.getElementById("adminLogoutModal");
      if (modalEl) {
        const bsModal = bootstrap.Modal.getInstance(modalEl);
        if (bsModal) bsModal.hide();
      }
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

/* Style cho modal logout giống Client */
#adminLogoutModal .modal-content {
  border-radius: 16px;
}

#adminLogoutModal .modal-header {
  border-radius: 16px 16px 0 0;
  padding: 1.5rem 1.5rem 1rem;
}

#adminLogoutModal .modal-body {
  padding: 1.5rem;
}

#adminLogoutModal .modal-footer {
  border-radius: 0 0 16px 16px;
  padding: 1rem 1.5rem 1.5rem;
}

#adminLogoutModal .btn {
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s ease;
}

#adminLogoutModal .btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#adminLogoutModal .fa-3x {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%   { transform: scale(1); }
  50%  { transform: scale(1.05); }
  100% { transform: scale(1); }
}
</style>