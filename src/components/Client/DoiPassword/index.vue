<template>
  <div class="change-password-wrapper min-vh-100 d-flex align-items-center py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
          
          <div class="card border-0 shadow-lg profile-card">
            <div class="card-body p-4 p-md-5">
              
              <div class="text-center mb-4">
                <div class="icon-box rounded-circle bg-primary-light d-inline-flex align-items-center justify-content-center mb-3 shadow-sm">
                  <i class="bi bi-shield-lock text-primary fs-2"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Đổi mật khẩu</h3>
                <p class="text-muted small px-lg-4">Nhập mật khẩu mới để bảo mật tài khoản.</p>
              </div>
              
              <form @submit.prevent="handleChangePassword" class="mt-4">
                <div class="mb-4">
                  <label class="form-label fw-bold text-secondary small text-uppercase tracking-wider">Mật khẩu hiện tại</label>
                  <div class="input-group custom-input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" placeholder="••••••••" v-model="form.current_password" required>
                  </div>
                </div>

                <div class="d-flex align-items-center my-4">
                  <hr class="flex-grow-1 border-light opacity-50">
                  <span class="mx-3 text-muted fs-xs fw-bold text-uppercase">Mật khẩu mới</span>
                  <hr class="flex-grow-1 border-light opacity-50">
                </div>

                <div class="mb-4">
                  <div class="input-group custom-input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" placeholder="Tối thiểu 6 ký tự" v-model="form.new_password" required>
                  </div>
                  
                  <div v-if="form.new_password">
                    <div class="password-strength-bar mt-2 rounded-pill bg-light overflow-hidden">
                      <div class="progress-bar" role="progressbar" :style="{width: passwordStrength + '%', backgroundColor: strengthColor}"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                      <small class="text-muted fs-xs">Độ mạnh: <span :style="{color: strengthColor}" class="fw-bold">{{ strengthText }}</span></small>
                    </div>
                  </div>
                </div>

                <div class="mb-4">
                  <div class="input-group custom-input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-patch-check"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" placeholder="Xác nhận mật khẩu mới" v-model="form.confirm_password" required>
                  </div>
                  <small class="text-danger fs-xs mt-1 d-block" v-if="form.confirm_password && form.new_password !== form.confirm_password">
                    <i class="bi bi-exclamation-circle me-1"></i>Mật khẩu xác nhận không khớp!
                  </small>
                </div>

                <div class="d-grid gap-2 mt-5">
                  <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm hover-lift" :disabled="form.new_password !== form.confirm_password || !isPasswordStrong">
                    Cập nhật mật khẩu
                  </button>
                  <button type="button" class="btn btn-link btn-sm text-decoration-none text-secondary hover-underline mt-2" @click="forgotPassword">
                    Quên mật khẩu?
                  </button>
                </div>
              </form>

              <div class="text-center mt-4 pt-3 border-top border-light">
                <a href="#" class="text-secondary text-decoration-none small hover-underline d-inline-flex align-items-center">
                  <i class="bi bi-arrow-left me-2"></i> Quay lại trang cá nhân
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChangePasswordBeautiful',
  data() {
    return {
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
      if (this.passwordStrength <= 25) return '#dc3545';
      if (this.passwordStrength <= 50) return '#ffc107';
      if (this.passwordStrength <= 75) return '#0dcaf0';
      return '#198754';
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
    handleChangePassword() { alert("Đã cập nhật!"); },
    forgotPassword() { alert("Đã gửi email!"); }
  }
}
</script>

<style scoped>
.change-password-wrapper {
  background-color: #f4f7f6;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='4' height='4' viewBox='0 0 4 4'%3E%3Cpath fill='%23000' fill-opacity='0.03' d='M1 3h1v1H1V3zm2-2h1v1H2V1z'%3E%3C/path%3E%3C/svg%3E");
}

.profile-card { border-radius: 24px; }
.icon-box { width: 64px; height: 64px; }
.bg-primary-light { background-color: rgba(13, 110, 253, 0.08); }

/* SỬA LỖI VIỀN XANH BỊ MẤT KHÚC ĐẦU */
.custom-input-group {
  transition: all 0.2s ease;
  border: 1px solid #e9ecef;
  border-radius: 8px; /* Tùy chỉnh độ bo cho cả group */
  overflow: hidden;
}

.custom-input-group .input-group-text,
.custom-input-group .form-control {
  border: none; /* Bỏ border mặc định để dùng border của group */
  padding: 0.75rem 1rem;
  box-shadow: none !important; /* Tắt shadow mặc định của Bootstrap */
}

/* Khi ô input được click (focus) */
.custom-input-group:focus-within {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.custom-input-group .form-control:focus {
  background-color: #fff;
}

.password-strength-bar { height: 4px; }
.progress-bar { transition: all 0.4s ease; }
.hover-lift { transition: all 0.2s ease; }
.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2) !important;
}

.fs-xs { font-size: 0.75rem; }
.tracking-wider { letter-spacing: 0.08em; }

@media (max-width: 576px) {
  .card-body { padding: 1.5rem !important; }
}
</style>