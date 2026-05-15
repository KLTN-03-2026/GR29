<template>
  <div class="auth-container user-theme">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="auth-card">
      <div class="brand">
        <h1 class="logo">now<span>SOS</span></h1>
        <p class="subtitle">Create your account</p>
      </div>

      <form class="auth-form">

  <div class="input-group">
    <label for="fullname">HỌ VÀ TÊN</label>
    <div class="input-wrapper">
      <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
      <input v-model="nguoi_dung.ho_ten" type="text" id="fullname" placeholder="Nhap ho va ten cua ban" required>
    </div>
  </div>

  <div class="input-group">
    <label for="phone">SO DIEN THOAI</label>
    <div class="input-wrapper">
      <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
        <line x1="12" y1="18" x2="12.01" y2="18"></line>
      </svg>
      <input v-model="nguoi_dung.so_dien_thoai" type="text" id="phone" placeholder="Nhap so dien thoai" required>
    </div>
  </div>

  <div class="input-group">
    <label for="email">EMAIL</label>
    <div class="input-wrapper">
      <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
        <polyline points="22,6 12,13 2,6"></polyline>
      </svg>
      <input v-model="nguoi_dung.email" type="email" id="email" placeholder="Nhap email" required>
    </div>
  </div>

  <div class="input-group">
    <label for="password">MAT KHAU</label>
    <div class="input-wrapper">
      <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
      </svg>
      <input v-model="nguoi_dung.mat_khau" :type="showPassword ? 'text' : 'password'" id="password"
        placeholder="Tao mat khau" required>
      <span class="eye-icon" @click="showPassword = !showPassword">
        <i :class="showPassword ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash'"></i>
      </span>
    </div>
  </div>

  <div class="input-group">
    <label for="repassword">NHAP LAI MAT KHAU</label>
    <div class="input-wrapper">
      <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
      </svg>
      <input v-model="nguoi_dung.repassword" :type="showRePassword ? 'text' : 'password'" id="repassword"
        placeholder="Nhap lai mat khau" required>
      <span class="eye-icon" @click="showRePassword = !showRePassword">
        <i :class="showRePassword ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash'"></i>
      </span>
    </div>
  </div>

  <!-- Thong bao link yeu cau cu -->
  <div v-if="linkedRequestsCount > 0" class="linked-banner">
    <i class="fa-solid fa-link"></i>
    <span>{{ linkedRequestsCount }} yeu cau cuu ho cua ban da duoc lien ket voi tai khoan nay.</span>
  </div>

  <!-- 🔥 BUTTON CLICK -->
  <button
    type="button"
    class="btn-primary"
    :disabled="isLoading"
    @click="dangKy"
  >
    {{ isLoading ? 'Dang xu ly...' : 'ĐĂNG KÝ' }}
  </button>

</form>

      

      

      <p class="switch-page">
        Đã có tài khoản?
        <router-link :to="{ path: '/client/login', query: $route.query }">Đăng nhập</router-link>
      </p>
    </div>
  </div>
</template>


<script>
import { authAPI } from "../../../services/api.js";
import { getSafeClientRedirect } from "../../../utils/safeClientRedirect.js";

export default {
  data() {
    return {
      nguoi_dung: {
        ho_ten: "",
        so_dien_thoai: "",
        email: "",
        mat_khau: "",
        repassword: "",
      },
      showPassword: false,
      showRePassword: false,
      isLoading: false,
      linkedRequestsCount: 0,
    };
  },

  mounted() {
    const raw = this.$route.query.phone;
    if (raw != null && String(raw).trim() !== "") {
      try {
        this.nguoi_dung.so_dien_thoai = decodeURIComponent(String(raw).trim());
      } catch {
        this.nguoi_dung.so_dien_thoai = String(raw).trim();
      }
    }
  },

  methods: {
    async dangKy() {
      if (this.nguoi_dung.mat_khau !== this.nguoi_dung.repassword) {
        this.$toast.error("Mật khẩu nhập lại không khớp!");
        return;
      }

      this.isLoading = true;

      try {
        const res = await authAPI.registerUser({
          ho_ten: this.nguoi_dung.ho_ten,
          so_dien_thoai: this.nguoi_dung.so_dien_thoai,
          email: this.nguoi_dung.email,
          mat_khau: this.nguoi_dung.mat_khau,
          device_id: localStorage.getItem('guest_device_id') || null,
        });

        const body = res.data;
        this.$toast.success(body.message || "Đăng ký thành công");
        if (body.linked_requests_count > 0) {
          this.linkedRequestsCount = body.linked_requests_count;
        }
        if (body.token) {
          localStorage.removeItem("admin_token");
          localStorage.removeItem("admin_user");
          localStorage.setItem("token", body.token);
          localStorage.setItem("user", JSON.stringify(body.data || {}));
          const nextPath = getSafeClientRedirect(this.$route.query.redirect);
          this.$router.push(nextPath || "/");
        } else {
          const safe = getSafeClientRedirect(this.$route.query.redirect);
          this.$router.push(
            safe ? { path: "/client/login", query: { redirect: safe } } : { path: "/client/login" }
          );
        }
      } catch (err) {
        const errors = err.response?.data?.errors;
        if (errors) {
          Object.values(errors).forEach((messages) => {
            this.$toast.error(messages[0]);
          });
        } else {
          this.$toast.error(
            err.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại!"
          );
        }
      } finally {
        this.isLoading = false;
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
  background: radial-gradient(circle, rgba(122, 178, 255, 0.8) 0%, rgba(122, 178, 255, 0) 70%);
  width: 500px;
  height: 500px;
  top: -150px;
  left: -150px;
}

.user-theme .orb-2 {
  background: radial-gradient(circle, rgba(162, 136, 255, 0.6) 0%, rgba(162, 136, 255, 0) 70%);
  width: 400px;
  height: 400px;
  bottom: -100px;
  right: -100px;
}

.user-theme .orb-3 {
  background: radial-gradient(circle, rgba(105, 232, 226, 0.5) 0%, rgba(105, 232, 226, 0) 70%);
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
  0% {
    transform: translate(0, 0);
  }

  50% {
    transform: translate(30px, -30px);
  }

  100% {
    transform: translate(0, 0);
  }
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
  color: #3b82f6;
  /* Accent color */
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
  border: 1px solid rgba(255, 255, 255, 0.5);
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

.input-wrapper input:focus+.input-icon,
.input-wrapper input:focus~.input-icon {
  color: #3b82f6;
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

.linked-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  background: linear-gradient(135deg, #d1fae5, #a7f3d0);
  border: 1px solid #6ee7b7;
  border-radius: 12px;
  padding: 12px 16px;
  color: #065f46;
  font-size: 14px;
  font-weight: 600;
}

.linked-banner i {
  color: #059669;
  font-size: 18px;
}
</style>