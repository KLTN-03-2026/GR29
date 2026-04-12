<template>
  <div class="auth-container user-theme">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="auth-card">
      <!-- Step 1: Nhập Email -->
      <div v-if="currentStep === 'email'" class="step-content">
        <div class="brand">
          <h1 class="logo">GR29<span>SOS</span></h1>
          <p class="subtitle">Khôi phục mật khẩu</p>
        </div>

        <form @submit.prevent="guiMaOtp" class="auth-form">
          <div class="step-indicator">
            <span class="step active">1</span>
            <div class="step-line"></div>
            <span class="step">2</span>
            <div class="step-line"></div>
            <span class="step">3</span>
          </div>

          <div class="info-box">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <p>Nhập email đã đăng ký. Chúng tôi sẽ gửi mã xác nhận đến email của bạn.</p>
          </div>

          <div class="input-group">
            <label for="email">EMAIL</label>
            <div class="input-wrapper">
              <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              <input v-model="email" type="email" id="email" placeholder="Nhập email của bạn" required>
            </div>
          </div>

          <button type="submit" class="btn-primary" :disabled="isLoading">
            <span v-if="isLoading" class="loading-spinner"></span>
            <span v-else>Gửi mã xác nhận</span>
          </button>

          <p class="switch-page">
            <router-link to="/client/login">← Quay lại đăng nhập</router-link>
          </p>
        </form>
      </div>

      <!-- Step 2: Nhập mã OTP -->
      <div v-if="currentStep === 'otp'" class="step-content">
        <div class="brand">
          <h1 class="logo">GR29<span>SOS</span></h1>
          <p class="subtitle">Nhập mã xác nhận</p>
        </div>

        <form @submit.prevent="xacNhanOtp" class="auth-form">
          <div class="step-indicator">
            <span class="step completed">✓</span>
            <div class="step-line active"></div>
            <span class="step active">2</span>
            <div class="step-line"></div>
            <span class="step">3</span>
          </div>

          <div class="info-box success">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <p>Mã xác nhận đã được gửi đến <strong>{{ email }}</strong></p>
          </div>

          <div class="otp-container">
            <input
              v-for="(digit, index) in 6"
              :key="index"
              :ref="el => otpInputs[index] = el"
              v-model="otpDigits[index]"
              type="text"
              maxlength="1"
              class="otp-input"
              @input="handleOtpInput(index)"
              @keydown="handleOtpKeydown($event, index)"
              @paste="handleOtpPaste($event)"
            >
          </div>

          <p class="otp-timer">Mã có hiệu lực trong <strong>{{ formatTime(countdown) }}</strong></p>

          <button type="submit" class="btn-primary" :disabled="isLoading || otpCode.length !== 6">
            <span v-if="isLoading" class="loading-spinner"></span>
            <span v-else>Xác nhận</span>
          </button>

          <div class="resend-section">
            <button type="button" class="btn-link" @click="guiLaiMaOtp" :disabled="countdown > 0">
              Gửi lại mã
            </button>
          </div>

          <p class="switch-page">
            <router-link to="/client/login">← Quay lại đăng nhập</router-link>
          </p>
        </form>
      </div>

      <!-- Step 3: Đặt lại mật khẩu -->
      <div v-if="currentStep === 'reset'" class="step-content">
        <div class="brand">
          <h1 class="logo">GR29<span>SOS</span></h1>
          <p class="subtitle">Đặt mật khẩu mới</p>
        </div>

        <form @submit.prevent="datLaiMatKhau" class="auth-form">
          <div class="step-indicator">
            <span class="step completed">✓</span>
            <div class="step-line active"></div>
            <span class="step completed">✓</span>
            <div class="step-line active"></div>
            <span class="step active">3</span>
          </div>

          <div class="input-group">
            <label for="newPassword">MẬT KHẨU MỚI</label>
            <div class="input-wrapper">
              <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              <input
                v-model="matKhauMoi"
                :type="showPassword ? 'text' : 'password'"
                id="newPassword"
                placeholder="Nhập mật khẩu mới (ít nhất 6 ký tự)"
                required
                minlength="6"
              >
              <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
          </div>

          <div class="input-group">
            <label for="confirmPassword">XÁC NHẬN MẬT KHẨU</label>
            <div class="input-wrapper">
              <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
              <input
                v-model="xacNhanMatKhau"
                :type="showConfirmPassword ? 'text' : 'password'"
                id="confirmPassword"
                placeholder="Nhập lại mật khẩu mới"
                required
              >
              <button type="button" class="toggle-password" @click="showConfirmPassword = !showConfirmPassword">
                <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
          </div>

          <div v-if="matKhauMoi && xacNhanMatKhau" class="password-match">
            <span v-if="matKhauMoi === xacNhanMatKhau" class="match success">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              Mật khẩu khớp
            </span>
            <span v-else class="match error">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
              Mật khẩu không khớp
            </span>
          </div>

          <button
            type="submit"
            class="btn-primary"
            :disabled="isLoading || matKhauMoi !== xacNhanMatKhau || matKhauMoi.length < 6"
          >
            <span v-if="isLoading" class="loading-spinner"></span>
            <span v-else>Đặt lại mật khẩu</span>
          </button>

          <p class="switch-page">
            <router-link to="/client/login">← Quay lại đăng nhập</router-link>
          </p>
        </form>
      </div>

      <!-- Success State -->
      <div v-if="currentStep === 'success'" class="step-content success-content">
        <div class="success-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
        </div>

        <h2>Đặt lại mật khẩu thành công!</h2>
        <p>Mật khẩu của bạn đã được thay đổi. Bây giờ bạn có thể đăng nhập với mật khẩu mới.</p>

        <router-link to="/client/login" class="btn-primary">Đăng nhập ngay</router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { passwordAPI } from "../../../services/api.js";

export default {
  data() {
    return {
      currentStep: "email",
      email: "",
      otpDigits: ["", "", "", "", "", ""],
      otpInputs: [],
      matKhauMoi: "",
      xacNhanMatKhau: "",
      resetToken: "",
      isLoading: false,
      countdown: 300,
      timerInterval: null,
      showPassword: false,
      showConfirmPassword: false,
    };
  },

  computed: {
    otpCode() {
      return this.otpDigits.join("");
    },
  },

  methods: {
    async guiMaOtp() {
      this.isLoading = true;
      try {
        const res = await passwordAPI.sendOtp(this.email);
        if (res.data.status) {
          this.$toast.success(res.data.message);
          this.currentStep = "otp";
          this.startCountdown();
        } else {
          this.$toast.error(res.data.message);
        }
      } catch (err) {
        const msg = err.response?.data?.message || "Không thể gửi mã xác nhận";
        this.$toast.error(msg);
      } finally {
        this.isLoading = false;
      }
    },

    async xacNhanOtp() {
      this.isLoading = true;
      try {
        const res = await passwordAPI.verifyOtp(this.email, this.otpCode);
        if (res.data.status) {
          this.resetToken = res.data.reset_token;
          this.currentStep = "reset";
          this.stopCountdown();
          this.$toast.success(res.data.message);
        } else {
          this.$toast.error(res.data.message);
        }
      } catch (err) {
        const msg = err.response?.data?.message || "Mã xác nhận không đúng";
        this.$toast.error(msg);
      } finally {
        this.isLoading = false;
      }
    },

    async datLaiMatKhau() {
      if (this.matKhauMoi !== this.xacNhanMatKhau) {
        this.$toast.error("Mật khẩu không khớp");
        return;
      }
      this.isLoading = true;
      try {
        const res = await passwordAPI.resetPassword(
          this.email,
          this.resetToken,
          this.matKhauMoi,
          this.xacNhanMatKhau
        );
        if (res.data.status) {
          this.currentStep = "success";
          this.$toast.success(res.data.message);
        } else {
          this.$toast.error(res.data.message);
        }
      } catch (err) {
        const msg = err.response?.data?.message || "Không thể đặt lại mật khẩu";
        this.$toast.error(msg);
      } finally {
        this.isLoading = false;
      }
    },

    async guiLaiMaOtp() {
      this.isLoading = true;
      try {
        const res = await passwordAPI.resendOtp(this.email);
        if (res.data.status) {
          this.$toast.success("Đã gửi lại mã xác nhận");
          this.otpDigits = ["", "", "", "", "", ""];
          this.countdown = 300;
          this.startCountdown();
          this.$nextTick(() => this.otpInputs[0]?.focus());
        } else {
          this.$toast.error(res.data.message);
        }
      } catch (err) {
        this.$toast.error("Không thể gửi lại mã");
      } finally {
        this.isLoading = false;
      }
    },

    handleOtpInput(index) {
      const value = this.otpDigits[index];
      if (value && index < 5) {
        this.$nextTick(() => this.otpInputs[index + 1]?.focus());
      }
    },

    handleOtpKeydown(event, index) {
      if (event.key === "Backspace" && !this.otpDigits[index] && index > 0) {
        this.$nextTick(() => this.otpInputs[index - 1]?.focus());
      }
    },

    handleOtpPaste(event) {
      event.preventDefault();
      const pastedData = event.clipboardData.getData("text").slice(0, 6);
      for (let i = 0; i < pastedData.length; i++) {
        if (/\d/.test(pastedData[i])) {
          this.otpDigits[i] = pastedData[i];
        }
      }
      const lastFilledIndex = Math.min(pastedData.length - 1, 5);
      this.$nextTick(() => this.otpInputs[lastFilledIndex]?.focus());
    },

    startCountdown() {
      this.stopCountdown();
      this.countdown = 300;
      this.timerInterval = setInterval(() => {
        if (this.countdown > 0) {
          this.countdown--;
        } else {
          this.stopCountdown();
        }
      }, 1000);
    },

    stopCountdown() {
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
        this.timerInterval = null;
      }
    },

    formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${mins}:${secs.toString().padStart(2, "0")}`;
    },
  },

  beforeUnmount() {
    this.stopCountdown();
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

.step-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 20px;
}

.step {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #e2e8f0;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 700;
}

.step.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.step.completed {
  background: #22c55e;
  color: white;
  font-size: 16px;
}

.step-line {
  width: 40px;
  height: 3px;
  background: #e2e8f0;
  border-radius: 2px;
}

.step-line.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.info-box {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 15px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 12px;
  font-size: 14px;
  color: #1e40af;
}

.info-box svg {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  margin-top: 2px;
}

.info-box.success {
  background: #f0fdf4;
  border-color: #bbf7d0;
  color: #166534;
}

.info-box.success svg {
  color: #22c55e;
}

.info-box p {
  margin: 0;
  line-height: 1.5;
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

.toggle-password {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
}

.toggle-password svg {
  width: 20px;
  height: 20px;
  color: #94a3b8;
}

.toggle-password:hover svg {
  color: #64748b;
}

.otp-container {
  display: flex;
  justify-content: center;
  gap: 10px;
}

.otp-input {
  width: 48px;
  height: 56px;
  text-align: center;
  font-size: 24px;
  font-weight: 700;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.8);
  transition: all 0.3s ease;
  outline: none;
}

.otp-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
  background: white;
}

.otp-timer {
  text-align: center;
  color: #64748b;
  font-size: 14px;
  margin: 0;
}

.otp-timer strong {
  color: #3b82f6;
}

.resend-section {
  text-align: center;
}

.btn-link {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: underline;
}

.btn-link:hover {
  color: #2563eb;
}

.btn-link:disabled {
  color: #94a3b8;
  cursor: not-allowed;
}

.password-match {
  text-align: center;
  margin-top: -10px;
}

.match {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
}

.match svg {
  width: 16px;
  height: 16px;
}

.match.success {
  color: #22c55e;
}

.match.error {
  color: #ef4444;
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
  text-decoration: none;
  display: block;
  text-align: center;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.btn-primary:active {
  transform: translateY(0);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.loading-spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.switch-page {
  text-align: center;
  margin-top: 20px;
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

.success-content {
  text-align: center;
}

.success-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: scaleIn 0.5s ease;
}

.success-icon svg {
  width: 40px;
  height: 40px;
  color: white;
}

@keyframes scaleIn {
  0% { transform: scale(0); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.success-content h2 {
  color: #166534;
  font-size: 24px;
  margin: 0 0 15px;
}

.success-content p {
  color: #64748b;
  font-size: 15px;
  line-height: 1.6;
  margin-bottom: 25px;
}

@media (max-width: 480px) {
  .auth-card {
    padding: 30px 20px;
  }

  .otp-input {
    width: 42px;
    height: 50px;
    font-size: 20px;
  }

  .otp-container {
    gap: 6px;
  }
}
</style>