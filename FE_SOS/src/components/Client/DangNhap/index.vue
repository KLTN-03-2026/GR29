<template>
  <div class="auth-container user-theme">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="auth-card">
      <div class="brand">
        <h1 class="logo">GR29 <span>SOS</span></h1>
        <p class="subtitle">Chào mừng bạn </p>
      </div>

      <form @submit.prevent="dangNhap" class="auth-form">
        <div class="input-group">
          <label for="email">GMAIL</label>
          <div class="input-wrapper">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <input v-model="thong_tin_dang_nhap.email" type="text" id="email" placeholder="Nhập gmail của bạn" required>
          </div>
        </div>

        <div class="input-group">
          <label for="password">MẬT KHẨU</label>
          <div class="input-wrapper">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <input v-model="thong_tin_dang_nhap.mat_khau" type="password" id="password" placeholder="Nhập mật khẩu" required>
          </div>
        </div>

        <div class="form-actions">
          <label class="remember-me">
            
          </label>
          <a href="#" class="forgot-password" @click.prevent="$router.push('/client/forgot-password')">Quên mật khẩu?</a>
        </div>

        <button type="submit" class="btn-primary">Đăng Nhập</button>
      </form>

      

      

      <p class="switch-page">
        Chưa có tài khoản?
        <router-link :to="{ path: '/client/register', query: $route.query }">Đăng ký ngay</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import { authAPI } from "../../../services/api.js";
import { getSafeClientRedirect } from "../../../utils/safeClientRedirect.js";
import { emitAccountLocked } from "../../../utils/accountLockedEvent.js";

export default {
  data() {
    return {
      thong_tin_dang_nhap: {
        email: "",
        mat_khau: "",
      },
    };
  },
  methods: {
    async dangNhap() {
      try {
        const res = await authAPI.loginUser({
          email: this.thong_tin_dang_nhap.email,
          mat_khau: this.thong_tin_dang_nhap.mat_khau,
        });
        const body = res.data;
        if (body.status && body.token) {
          localStorage.setItem("token", body.token);
          localStorage.setItem("user", JSON.stringify(body.data || {}));
          this.$toast.success(body.message || "Đăng nhập thành công");
          const nextPath = getSafeClientRedirect(this.$route.query.redirect);
          this.$router.push(nextPath || "/");
        } else {
          this.$toast.error(body.message || "Đăng nhập thất bại");
        }
      } catch (err) {
        if (err.response?.data?.account_locked) {
          emitAccountLocked();
          return;
        }
        const msg =
          err.response?.data?.message ||
          "Không kết nối được máy chủ. Kiểm tra Laravel (php artisan serve) và CORS.";
        this.$toast.error(msg);
      }
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap');

* {
  box-sizing: border-box;
  font-family: 'Quicksand', sans-serif;
}

.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f0f4f8;
  position: relative;
  overflow: hidden;
  padding: 20px;
}

/* User Theme Colors - Blue/Teal soft gradients */
.user-theme {
  background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
}

.user-theme .orb-1 {
  background: radial-gradient(circle, rgba(122,178,255,0.8) 0%, rgba(122,178,255,0) 70%);
  width: 500px;
  height: 500px;
  top: -150px;
  left: -150px;
}

.user-theme .orb-2 {
  background: radial-gradient(circle, rgba(162,136,255,0.6) 0%, rgba(162,136,255,0) 70%);
  width: 400px;
  height: 400px;
  bottom: -100px;
  right: -100px;
}

.user-theme .orb-3 {
  background: radial-gradient(circle, rgba(105,232,226,0.5) 0%, rgba(105,232,226,0) 70%);
  width: 300px;
  height: 300px;
  top: 40%;
  left: 30%;
  animation: float 8s ease-in-out infinite;
}

.orb {
  position: absolute;
  border-radius: 50%;
  z-index: 0;
  filter: blur(40px);
}

@keyframes float {
  0% { transform: translate(0, 0); }
  50% { transform: translate(30px, -30px); }
  100% { transform: translate(0, 0); }
}

.auth-card {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.8);
  border-radius: 24px;
  padding: 40px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
  z-index: 1;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.auth-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.12);
}

.brand {
  text-align: center;
  margin-bottom: 30px;
}

.logo {
  font-size: 36px;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
  letter-spacing: -1px;
}

.logo span {
  color: #3b82f6; /* Accent color */
}

.subtitle {
  color: #64748b;
  font-size: 15px;
  margin-top: 8px;
  font-weight: 500;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-group label {
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  letter-spacing: 0.5px;
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
  color: #94a3b8;
  transition: color 0.3s ease;
}

.input-wrapper input {
  width: 100%;
  padding: 14px 16px 14px 44px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.5);
  background: rgba(255, 255, 255, 0.6);
  font-size: 15px;
  color: #1e293b;
  transition: all 0.3s ease;
  outline: none;
}

.input-wrapper input::placeholder {
  color: #94a3b8;
}

.input-wrapper input:focus {
  background: rgba(255, 255, 255, 0.9);
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.input-wrapper input:focus + .input-icon,
.input-wrapper input:focus ~ .input-icon {
  color: #3b82f6;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #64748b;
  cursor: pointer;
  font-weight: 500;
}

.remember-me input {
  accent-color: #3b82f6;
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.forgot-password {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}

.forgot-password:hover {
  color: #2563eb;
  text-decoration: underline;
}

.btn-primary {
  background: linear-gradient(to right, #3b82f6, #60a5fa);
  color: white;
  border: none;
  border-radius: 12px;
  padding: 14px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  margin-top: 10px;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.btn-primary:active {
  transform: translateY(0);
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 25px 0;
  color: #94a3b8;
  font-size: 13px;
  font-weight: 600;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid rgba(148, 163, 184, 0.3);
}

.divider span {
  padding: 0 10px;
}

.social-login {
  display: flex;
  gap: 15px;
}

.btn-social {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.8);
  background: rgba(255, 255, 255, 0.5);
  font-size: 14px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-social:hover {
  background: rgba(255, 255, 255, 0.9);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.switch-page {
  text-align: center;
  margin-top: 25px;
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
}

.switch-page a {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 700;
  transition: color 0.2s;
}

.switch-page a:hover {
  color: #2563eb;
  text-decoration: underline;
}

@media (max-width: 480px) {
  .auth-card {
    padding: 30px 20px;
  }
  
  .social-login {
    flex-direction: column;
  }
}
</style>