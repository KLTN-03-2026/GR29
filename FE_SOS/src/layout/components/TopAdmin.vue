<template>
  <header class="admin-topbar navbar navbar-expand px-4 py-0">
    <div class="d-flex align-items-center gap-3 flex-grow-1">
      <button class="btn btn-sm btn-icon-light d-md-none" type="button" @click="$emit('toggle-sidebar')">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="d-flex align-items-center gap-2">
        <span class="topbar-badge">
          <i class="fa-solid fa-shield-halved me-1"></i>
          ADMIN CONSOLE
        </span>
        <span class="topbar-subtitle d-none d-sm-inline">
          Giám sát &amp; điều phối cứu hộ
        </span>
      </div>
    </div>

    <div class="d-flex align-items-center gap-2">
      <div class="d-none d-md-flex align-items-center gap-2 topbar-duty-tag">
        <span class="duty-dot"></span>
        <span>Trực 24/7</span>
      </div>

      <button class="btn btn-icon-light" title="Thông báo">
        <i class="bi bi-bell"></i>
      </button>

      <div class="dropdown">
        <button
          class="btn user-pill d-flex align-items-center gap-2"
          type="button"
          data-bs-toggle="dropdown"
        >
          <span class="avatar-initial">AD</span>
          <span class="user-name d-none d-sm-inline">{{ adminName }}</span>
          <i class="fa-solid fa-chevron-down chevron-icon d-none d-sm-inline"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm topbar-dropdown">
          <li class="dropdown-label">
            <i class="fa-solid fa-circle-user me-2 text-primary"></i>
            Khu vực quản trị
          </li>
          <li><hr class="dropdown-divider my-1" /></li>
          <li>
            <router-link to="/admin/change-password" class="dropdown-item dropdown-item-custom">
              <i class="fa-solid fa-key me-2"></i>Đổi mật khẩu
            </router-link>
          </li>
          <li>
<<<<<<< HEAD
            <button class="dropdown-item dropdown-item-danger" type="button" @click="showLogoutModal">
=======
            <router-link to="/admin/change-password" class="dropdown-item small">
              <i class="fa-solid fa-key me-2"></i>Đổi mật khẩu
            </router-link>
          </li>
          <li>
            <!-- Dùng @click programmatic vì nằm trong Bootstrap dropdown -->
            <button class="dropdown-item text-danger small" type="button" @click="showLogoutModal">
>>>>>>> 74db8ef975880c0480377c95277d7ae63ecbbde2
              <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
            </button>
          </li>
        </ul>
      </div>
    </div>
  </header>

<<<<<<< HEAD
  <!-- Modal xác nhận đăng xuất -->
  <div class="modal fade" id="adminLogoutModal" tabindex="-1" aria-labelledby="adminLogoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg logout-modal-content">
        <div class="modal-header border-0 logout-modal-header">
          <h5 class="modal-title w-100 text-center fw-bold" id="adminLogoutModalLabel">
=======
  <!-- Modal xác nhận đăng xuất – đúng cấu trúc Bootstrap chuẩn như Client -->
  <div class="modal fade" id="adminLogoutModal" tabindex="-1" aria-labelledby="adminLogoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-light border-0">
          <h5 class="modal-title w-100 text-center fw-bold text-dark" id="adminLogoutModalLabel">
>>>>>>> 74db8ef975880c0480377c95277d7ae63ecbbde2
            <i class="fa-solid fa-circle-question text-warning me-2"></i>
            Xác nhận đăng xuất
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body text-center py-4">
<<<<<<< HEAD
          <div class="logout-icon-wrap mb-3">
            <i class="fa-solid fa-right-from-bracket fa-2x text-danger"></i>
=======
          <div class="mb-3">
            <i class="fa-solid fa-right-from-bracket fa-3x text-danger mb-3"></i>
>>>>>>> 74db8ef975880c0480377c95277d7ae63ecbbde2
          </div>
          <h6 class="fw-semibold text-dark mb-2">Bạn muốn đăng xuất?</h6>
          <p class="text-muted mb-0 small">Bạn sẽ cần đăng nhập lại để tiếp tục quản trị hệ thống.</p>
        </div>
<<<<<<< HEAD
        <div class="modal-footer border-0 logout-modal-footer">
          <div class="d-flex w-100 gap-2">
            <button type="button" class="btn btn-light w-100 fw-medium" data-bs-dismiss="modal">
              <i class="fa-solid fa-xmark me-1"></i> Hủy
            </button>
            <button type="button" class="btn btn-danger w-100 fw-medium" @click="logout">
=======
        <div class="modal-footer border-0 bg-light">
          <div class="d-flex w-100 gap-2 px-3">
            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
              <i class="fa-solid fa-xmark me-1"></i> Hủy
            </button>
            <button type="button" class="btn btn-danger w-100" @click="logout">
>>>>>>> 74db8ef975880c0480377c95277d7ae63ecbbde2
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
<<<<<<< HEAD
=======
      // Đóng modal trước khi đăng xuất
>>>>>>> 74db8ef975880c0480377c95277d7ae63ecbbde2
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
/* ─── Topbar Shell ─────────────────────────────────────── */
.admin-topbar {
  background: #ffffff;
  border-bottom: 1px solid #e9ecef;
  position: sticky;
  top: 0;
  z-index: 1020;
  flex-shrink: 0;
  height: 56px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}

/* ─── Badge & subtitle ─────────────────────────────────── */
.topbar-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 6px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.5px;
  border: 1px solid #bfdbfe;
}

.topbar-subtitle {
  font-size: 13px;
  color: #94a3b8;
  font-weight: 400;
}

/* ─── Duty indicator ───────────────────────────────────── */
.topbar-duty-tag {
  font-size: 12.5px;
  color: #64748b;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 4px 12px;
}

.duty-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #22c55e;
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.25);
  display: inline-block;
  animation: blink 2s infinite;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.4; }
}

/* ─── Icon-only button ─────────────────────────────────── */
.btn-icon-light {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
  padding: 0;
}

.btn-icon-light:hover {
  background: #eff6ff;
  color: #2563eb;
  border-color: #bfdbfe;
}

/* ─── User pill ────────────────────────────────────────── */
.user-pill {
  height: 36px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #f8fafc;
  padding: 0 12px;
  transition: background 0.15s, border-color 0.15s;
}

.user-pill:hover,
.user-pill:focus {
  background: #eff6ff;
  border-color: #bfdbfe;
  box-shadow: none;
}

.avatar-initial {
  width: 26px;
  height: 26px;
  border-radius: 8px;
  background: linear-gradient(135deg, #2563eb, #3b82f6);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
}

.user-name {
  font-size: 13px;
  font-weight: 500;
  color: #334155;
}

.chevron-icon {
  font-size: 10px;
  color: #94a3b8;
}

/* ─── Dropdown ─────────────────────────────────────────── */
.topbar-dropdown {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 6px;
  min-width: 190px;
  margin-top: 6px !important;
}

.dropdown-label {
  font-size: 12px;
  font-weight: 600;
  color: #94a3b8;
  padding: 6px 10px 4px;
  display: block;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.dropdown-item-custom {
  border-radius: 8px;
  font-size: 13.5px;
  color: #334155;
  padding: 8px 12px;
  display: flex;
  align-items: center;
  transition: background 0.13s, color 0.13s;
  text-decoration: none;
}

.dropdown-item-custom:hover {
  background: #eff6ff;
  color: #2563eb;
}

.dropdown-item-danger {
  border-radius: 8px;
  font-size: 13.5px;
  color: #ef4444;
  padding: 8px 12px;
  display: flex;
  align-items: center;
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  transition: background 0.13s;
}

.dropdown-item-danger:hover {
  background: #fff1f2;
  color: #dc2626;
}

/* ─── Logout Modal ─────────────────────────────────────── */
.logout-modal-content {
  border-radius: 16px;
  overflow: hidden;
}

.logout-modal-header {
  background: #f8fafc;
  padding: 1.5rem 1.5rem 1rem;
  border-radius: 16px 16px 0 0;
}

.logout-modal-footer {
  background: #f8fafc;
  padding: 1rem 1.5rem 1.5rem;
  border-radius: 0 0 16px 16px;
}

.logout-icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #fff1f2;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  animation: pulse-icon 2s infinite;
}

@keyframes pulse-icon {
  0%, 100% { transform: scale(1); }
  50%       { transform: scale(1.06); }
}

#adminLogoutModal .btn {
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s ease;
}

#adminLogoutModal .btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
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