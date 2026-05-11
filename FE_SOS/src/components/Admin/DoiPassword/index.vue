<template>
  <div class="cp-page">
    <!-- Background decoration -->
    <div class="cp-bg-circle cp-bg-circle-1"></div>
    <div class="cp-bg-circle cp-bg-circle-2"></div>

    <div class="cp-card">
      <!-- Header -->
      <div class="cp-header">
        <div class="cp-icon-wrap">
          <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h2 class="cp-title">Đổi mật khẩu</h2>
        <p class="cp-subtitle">Cập nhật mật khẩu để bảo vệ tài khoản của bạn</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleChangePassword" class="cp-form">

        <!-- Current Password -->
        <div class="cp-field">
          <label class="cp-label">Mật khẩu hiện tại</label>
          <div class="cp-input-wrap" :class="{ focused: focusState.current }">
            <i class="bi bi-key-fill cp-input-icon"></i>
            <input
              type="password"
              class="cp-input"
              placeholder="Nhập mật khẩu hiện tại"
              v-model="form.current_password"
              @focus="focusState.current = true"
              @blur="focusState.current = false"
              required
            />
          </div>
        </div>

        <div class="cp-divider">
          <span class="cp-divider-text">Mật khẩu mới</span>
        </div>

        <!-- New Password -->
        <div class="cp-field">
          <label class="cp-label">Mật khẩu mới</label>
          <div class="cp-input-wrap" :class="{ focused: focusState.newPass }">
            <i class="bi bi-lock-fill cp-input-icon"></i>
            <input
              type="password"
              class="cp-input"
              placeholder="Tối thiểu 8 ký tự"
              v-model="form.new_password"
              @focus="focusState.newPass = true"
              @blur="focusState.newPass = false"
              required
            />
          </div>
          <!-- Strength Bar -->
          <div class="cp-strength" v-if="form.new_password">
            <div class="cp-strength-bar">
              <div
                class="cp-strength-fill"
                :style="{ width: passwordStrength + '%', background: strengthColor }"
              ></div>
            </div>
            <div class="cp-strength-label">
              Độ mạnh: <span :style="{ color: strengthColor }">{{ strengthText }}</span>
            </div>
          </div>
        </div>

        <!-- Confirm Password -->
        <div class="cp-field">
          <label class="cp-label">Xác nhận mật khẩu mới</label>
          <div class="cp-input-wrap" :class="{
            focused: focusState.confirm,
            error: form.confirm_password && form.new_password !== form.confirm_password,
            success: form.confirm_password && form.new_password === form.confirm_password && form.new_password
          }">
            <i class="bi bi-patch-check-fill cp-input-icon"></i>
            <input
              type="password"
              class="cp-input"
              placeholder="Nhập lại mật khẩu mới"
              v-model="form.confirm_password"
              @focus="focusState.confirm = true"
              @blur="focusState.confirm = false"
              required
            />
            <i
              v-if="form.confirm_password && form.new_password === form.confirm_password"
              class="bi bi-check-circle-fill cp-check-icon"
            ></i>
          </div>
          <p class="cp-error-msg" v-if="form.confirm_password && form.new_password !== form.confirm_password">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>Mật khẩu xác nhận không khớp!
          </p>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="cp-btn-submit"
          :disabled="form.new_password !== form.confirm_password || !isPasswordStrong || loading"
        >
          <span v-if="!loading">
            <i class="bi bi-arrow-repeat me-2"></i>Cập nhật mật khẩu
          </span>
          <span v-else>
            <i class="bi bi-hourglass-split me-2"></i>Đang xử lý...
          </span>
        </button>
      </form>

      <!-- Back Link -->
      <div class="cp-footer">
        <router-link to="/admin/" class="cp-back-link">
          <i class="bi bi-arrow-left-circle-fill me-2"></i>Quay lại trang quản lý
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { adminAPI } from "../../../services/api.js";

export default {
  name: 'AdminChangePassword',
  data() {
    return {
      loading: false,
      focusState: {
        current: false,
        newPass: false,
        confirm: false,
      },
      form: {
        current_password: '',
        new_password: '',
        confirm_password: ''
      }
    }
  },
  computed: {
    passwordStrength() {
      if (!this.form.new_password) return 0;
      let strength = 0;
      if (this.form.new_password.length >= 8) strength += 25;
      if (/[A-Z]/.test(this.form.new_password)) strength += 25;
      if (/[0-9]/.test(this.form.new_password)) strength += 25;
      if (/[!@#$%^&*(),.?":{}|<>]/.test(this.form.new_password)) strength += 25;
      return strength;
    },
    strengthColor() {
      if (this.passwordStrength <= 25) return '#ef4444';
      if (this.passwordStrength <= 50) return '#f59e0b';
      if (this.passwordStrength <= 75) return '#3b82f6';
      return '#10b981';
    },
    strengthText() {
      if (this.passwordStrength <= 25) return 'Yếu';
      if (this.passwordStrength <= 50) return 'Trung bình';
      if (this.passwordStrength <= 75) return 'Khá';
      return 'Mạnh';
    },
    isPasswordStrong() {
      return this.passwordStrength >= 50;
    }
  },
  methods: {
    async handleChangePassword() {
      if (this.form.new_password !== this.form.confirm_password) {
        alert('Mật khẩu xác nhận không khớp!');
        return;
      }
      if (!this.isPasswordStrong) {
        alert('Mật khẩu mới phải mạnh hơn (tối thiểu trung bình)!');
        return;
      }

      this.loading = true;
      try {
        const response = await adminAPI.updateProfile({
          current_password: this.form.current_password,
          new_password: this.form.new_password,
          new_password_confirmation: this.form.confirm_password
        });

        if (response.data.status) {
          alert('Đã cập nhật mật khẩu thành công!');
          this.form.current_password = '';
          this.form.new_password = '';
          this.form.confirm_password = '';
          this.$router.push('/admin/');
        } else {
          alert(response.data.message || 'Có lỗi xảy ra!');
        }
      } catch (error) {
        console.error('Change password error:', error);
        alert(error.response?.data?.message || 'Có lỗi xảy ra khi đổi mật khẩu!');
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

/* ===== PAGE ===== */
.cp-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f0f4ff 0%, #fafaff 50%, #f0f7ff 100%);
  position: relative;
  overflow: hidden;
  padding: 2rem 1rem;
  font-family: 'Inter', 'Segoe UI', sans-serif;
}

/* ===== BACKGROUND DECORATION ===== */
.cp-bg-circle {
  position: absolute;
  border-radius: 50%;
  opacity: 0.5;
  pointer-events: none;
}
.cp-bg-circle-1 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, #e0e7ff, transparent 70%);
  top: -150px; left: -150px;
}
.cp-bg-circle-2 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, #dbeafe, transparent 70%);
  bottom: -120px; right: -120px;
}

/* ===== CARD ===== */
.cp-card {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 460px;
  background: #ffffff;
  border: 1px solid #e8ecf4;
  border-radius: 24px;
  box-shadow:
    0 4px 6px rgba(0, 0, 0, 0.04),
    0 20px 60px rgba(99, 102, 241, 0.1),
    0 0 0 1px rgba(99, 102, 241, 0.05);
  padding: 2.5rem 2.25rem;
}

/* ===== HEADER ===== */
.cp-header {
  text-align: center;
  margin-bottom: 2rem;
}
.cp-icon-wrap {
  width: 68px;
  height: 68px;
  border-radius: 20px;
  background: linear-gradient(135deg, #6366f1, #818cf8);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  color: #fff;
  margin-bottom: 1rem;
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
}
.cp-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.4rem;
  letter-spacing: -0.3px;
}
.cp-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

/* ===== FORM ===== */
.cp-form { display: flex; flex-direction: column; gap: 1.25rem; }

/* ===== FIELD ===== */
.cp-field { display: flex; flex-direction: column; gap: 0.45rem; }
.cp-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

/* ===== INPUT WRAP ===== */
.cp-input-wrap {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 0 1rem;
  gap: 0.75rem;
  transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}
.cp-input-wrap.focused {
  border-color: #6366f1;
  background: #fafbff;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}
.cp-input-wrap.error {
  border-color: #ef4444;
  background: #fff5f5;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}
.cp-input-wrap.success {
  border-color: #10b981;
  background: #f0fdf9;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.cp-input-icon { color: #94a3b8; font-size: 1rem; flex-shrink: 0; }
.cp-check-icon { color: #10b981; font-size: 1rem; flex-shrink: 0; }

.cp-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  color: #1e293b;
  font-size: 0.925rem;
  font-family: 'Inter', sans-serif;
  padding: 0.85rem 0;
  caret-color: #6366f1;
}
.cp-input::placeholder { color: #cbd5e1; }

/* ===== DIVIDER ===== */
.cp-divider {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 0.1rem 0;
}
.cp-divider::before,
.cp-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e8ecf4;
}
.cp-divider-text {
  font-size: 0.7rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 1px;
  white-space: nowrap;
}

/* ===== STRENGTH BAR ===== */
.cp-strength { display: flex; flex-direction: column; gap: 0.35rem; }
.cp-strength-bar {
  height: 5px;
  border-radius: 999px;
  background: #e2e8f0;
  overflow: hidden;
}
.cp-strength-fill {
  height: 100%;
  border-radius: 999px;
  transition: width 0.4s ease, background 0.4s ease;
}
.cp-strength-label {
  font-size: 0.75rem;
  color: #94a3b8;
}
.cp-strength-label span { font-weight: 700; }

/* ===== ERROR MSG ===== */
.cp-error-msg {
  font-size: 0.78rem;
  color: #ef4444;
  margin: 0;
  display: flex;
  align-items: center;
}

/* ===== SUBMIT BUTTON ===== */
.cp-btn-submit {
  width: 100%;
  padding: 0.9rem;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #6366f1, #818cf8);
  color: #fff;
  font-size: 0.95rem;
  font-weight: 700;
  font-family: 'Inter', sans-serif;
  cursor: pointer;
  letter-spacing: 0.3px;
  margin-top: 0.5rem;
  transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
}
.cp-btn-submit:hover:not(:disabled) {
  opacity: 0.92;
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(99, 102, 241, 0.4);
}
.cp-btn-submit:active:not(:disabled) {
  transform: translateY(0);
}
.cp-btn-submit:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  box-shadow: none;
}

/* ===== FOOTER ===== */
.cp-footer {
  margin-top: 1.75rem;
  padding-top: 1.25rem;
  border-top: 1px solid #f1f5f9;
  text-align: center;
}
.cp-back-link {
  display: inline-flex;
  align-items: center;
  font-size: 0.875rem;
  color: #64748b;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s, transform 0.15s;
}
.cp-back-link:hover {
  color: #6366f1;
  transform: translateX(-3px);
}
</style>
