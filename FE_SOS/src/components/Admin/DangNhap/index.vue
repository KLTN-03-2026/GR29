<template>
  <div class="auth-container admin-theme">
    <!-- Grid background for technical feel -->
    <div class="grid-bg"></div>

    <!-- Glowing orbs for dark theme -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="auth-card">
      <div class="brand">
        <h1 class="logo">now<span>SOS</span></h1>
        <p class="subtitle">Hệ thống Quản trị & Điều phối</p>
      </div>

      <div class="security-badge">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
        <span>Khu vực Bảo mật</span>
      </div>

      <form @submit.prevent="handleAdminLogin" class="auth-form">
        <div class="input-group">
          <label for="admin-email">EMAIL QUẢN TRỊ</label>
          <div class="input-wrapper">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <input v-model="form.email" type="email" id="admin-email" placeholder="Email admin" required autocomplete="username">
          </div>
        </div>

        <div class="input-group">
          <label for="password">MẬT KHẨU QUẢN TRỊ</label>
          <div class="input-wrapper">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <input v-model="form.mat_khau" type="password" id="password" placeholder="Nhập mật khẩu" required autocomplete="current-password">
          </div>
        </div>

        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? "Đang đăng nhập..." : "Truy cập Hệ thống Admin" }}
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </button>
      </form>

      <div class="footer-note">
        <p>Bằng việc đăng nhập, bạn đồng ý tuân thủ các chính sách bảo mật của Hệ thống Điều phối Khẩn cấp.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { authAPI } from "../../../services/api.js";

export default {
  name: "AdminDangNhap",
  data() {
    return {
      form: { email: "", mat_khau: "" },
      loading: false,
    };
  },
  methods: {
    async handleAdminLogin() {
      this.loading = true;
      try {
        const res = await authAPI.loginAdmin({
          email: this.form.email,
          mat_khau: this.form.mat_khau,
        });
        const body = res.data;
        if (body.status && body.token) {
          localStorage.removeItem("admin_token");
          localStorage.removeItem("admin_user");
          localStorage.setItem("admin_token", body.token);
          localStorage.setItem("admin_user", JSON.stringify(body.data || {}));
          this.$toast.success(body.message || "Đăng nhập thành công");
          this.$router.push("/admin");
        } else {
          this.$toast.error(body.message || "Đăng nhập thất bại");
        }
      } catch (err) {
        this.$toast.error(
          err.response?.data?.message ||
            "Không kết nối được API. Chạy Laravel và kiểm tra CORS."
        );
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Quicksand:wght@400;500;600;700&display=swap');

* {
  box-sizing: border-box;
  font-family: 'Quicksand', sans-serif;
}

.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #0f172a; /* Slate 900 */
  position: relative;
  overflow: hidden;
  padding: 20px;
}

/* Admin Theme Colors - Dark/Navy gradients */
.admin-theme {
  background: radial-gradient(circle at center, #1e293b 0%, #020617 100%);
}

.grid-bg {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background-image: 
    linear-gradient(rgba(56, 189, 248, 0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(56, 189, 248, 0.05) 1px, transparent 1px);
  background-size: 40px 40px;
  z-index: 0;
}

.admin-theme .orb-1 {
  background: radial-gradient(circle, rgba(56,189,248,0.15) 0%, rgba(56,189,248,0) 70%);
  width: 600px;
  height: 600px;
  top: -200px;
  left: -200px;
}

.admin-theme .orb-2 {
  background: radial-gradient(circle, rgba(14,165,233,0.1) 0%, rgba(14,165,233,0) 70%);
  width: 500px;
  height: 500px;
  bottom: -150px;
  right: -100px;
}

.orb {
  position: absolute;
  border-radius: 50%;
  z-index: 0;
  filter: blur(40px);
  animation: pulse 10s ease-in-out infinite alternate;
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 0.8; }
  100% { transform: scale(1.1); opacity: 1; }
}

.auth-card {
  background: rgba(15, 23, 42, 0.7);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(56, 189, 248, 0.2);
  border-radius: 16px;
  padding: 45px 40px;
  width: 100%;
  max-width: 460px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(56, 189, 248, 0.1);
  z-index: 1;
  position: relative;
}

/* Accent top border */
.auth-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, transparent, #38bdf8, transparent);
  border-radius: 16px 16px 0 0;
}

.brand {
  text-align: center;
  margin-bottom: 25px;
}

.logo {
  font-size: 32px;
  font-weight: 700;
  color: #f8fafc;
  margin: 0;
  letter-spacing: 1px;
}

.logo span {
  color: #38bdf8; /* Admin accent color */
}

.subtitle {
  color: #94a3b8;
  font-size: 14px;
  margin-top: 6px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.security-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.2);
  color: #10b981;
  padding: 8px 16px;
  border-radius: 30px;
  width: fit-content;
  margin: 0 auto 30px auto;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.security-badge svg {
  width: 14px;
  height: 14px;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 22px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-group label {
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 1px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 16px;
  width: 18px;
  height: 18px;
  color: #475569;
  transition: color 0.3s ease;
}

.input-wrapper input {
  width: 100%;
  padding: 14px 16px 14px 44px;
  border-radius: 12px;
  border: 1px solid rgba(56, 189, 248, 0.1);
  background: rgba(30, 41, 59, 0.5);
  font-size: 15px;
  color: #f8fafc;
  transition: all 0.3s ease;
  outline: none;
  font-family: inherit;
}

/* Monospace font for admin fields might look cool but let's stick to Quicksand for consistency, 
   or maybe just for the ID */
#admin-id {
  font-family: 'JetBrains Mono', monospace;
  font-size: 14px;
}

.input-wrapper input::placeholder {
  color: #475569;
  font-family: 'Quicksand', sans-serif;
}

.input-wrapper input:focus {
  background: rgba(30, 41, 59, 0.8);
  border-color: #38bdf8;
  box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
}

.input-wrapper input:focus + .input-icon,
.input-wrapper input:focus ~ .input-icon {
  color: #38bdf8;
}

.btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  background: #38bdf8;
  color: #0f172a;
  border: none;
  border-radius: 12px;
  padding: 14px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 15px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-primary svg {
  width: 18px;
  height: 18px;
  transition: transform 0.2s;
}

.btn-primary:hover {
  background: #0ea5e9;
  box-shadow: 0 0 20px rgba(56, 189, 248, 0.4);
}

.btn-primary:hover svg {
  transform: translateX(4px);
}

.btn-primary:active {
  transform: translateY(1px);
}

.footer-note {
  margin-top: 30px;
  text-align: center;
  color: #475569;
  font-size: 12px;
  line-height: 1.5;
}

@media (max-width: 480px) {
  .auth-card {
    padding: 35px 25px;
  }
}
</style>