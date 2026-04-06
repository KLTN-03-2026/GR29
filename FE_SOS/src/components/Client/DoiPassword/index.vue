<template>
  <div class="auth-container user-theme">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="auth-card">
      <div class="brand">
        <h1 class="logo">now<span>SOS</span></h1>
        <p class="subtitle">Đổi mật khẩu</p>
      </div>

      <form @submit.prevent="doiMatKhau" class="auth-form">
        <div class="input-group">
          <label for="old_password">MẬT KHẨU CŨ</label>
          <div class="input-wrapper">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <input v-model="payload.mat_khau_cu" type="password" id="old_password" placeholder="Nhập mật khẩu hiện tại" required>
          </div>
        </div>

        <div class="input-group">
          <label for="new_password">MẬT KHẨU MỚI</label>
          <div class="input-wrapper">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <input v-model="payload.mat_khau_moi" type="password" id="new_password" placeholder="Nhập mật khẩu mới" required>
          </div>
        </div>

        <div class="input-group">
          <label for="confirm_password">XÁC NHẬN MẬT KHẨU MỚI</label>
          <div class="input-wrapper">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <input v-model="payload.xac_nhan_mat_khau" type="password" id="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
          </div>
        </div>

        <button type="submit" class="btn-primary">Đổi Mật Khẩu</button>
      </form>

      <p class="switch-page">
        <router-link to="/">Quay về trang chủ</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import { authAPI } from "../../../services/api.js";

export default {
  data() {
    return {
      payload: {
        mat_khau_cu: "",
        mat_khau_moi: "",
        xac_nhan_mat_khau: "",
      },
    };
  },
  methods: {
    async doiMatKhau() {
      if (this.payload.mat_khau_moi !== this.payload.xac_nhan_mat_khau) {
        this.$toast.error("Mật khẩu mới và xác nhận mật khẩu không khớp!");
        return;
      }
      try {
        const res = await authAPI.changeClientPassword({
          current_password: this.payload.mat_khau_cu,
          new_password: this.payload.mat_khau_moi,
          new_password_confirmation: this.payload.xac_nhan_mat_khau,
        });
        const body = res.data;
        if (body.status) {
          this.$toast.success(body.message || "Đổi mật khẩu thành công!");
          this.payload.mat_khau_cu = "";
          this.payload.mat_khau_moi = "";
          this.payload.xac_nhan_mat_khau = "";
          this.$router.push("/");
        } else {
          this.$toast.error(body.message || "Đổi mật khẩu thất bại!");
        }
      } catch (err) {
        const errors = err.response?.data?.errors;
        if (errors) {
            const firstError = Object.values(errors)[0][0];
            this.$toast.error(firstError);
        } else {
            const msg = err.response?.data?.message || "Không kết nối được máy chủ!";
            this.$toast.error(msg);
        }
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
  color: #3b82f6;
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
}
</style>