<template>
  <div class="profile-wrapper min-vh-100 py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">

          <div class="card border-0 shadow-lg profile-card overflow-hidden">
            <div class="profile-cover"></div>

            <div class="card-body p-4 pt-0 text-center">
              <div class="avatar-wrapper">
                <div class="avatar-main rounded-circle shadow-sm">
                  <i class="fas fa-user-circle text-primary"></i>
                </div>
              </div>

              <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1">{{ user.ho_ten }}</h3>
                <span class="badge rounded-pill bg-primary-light text-primary px-3 fw-medium">
                  <i class="fas fa-check-circle me-1 small"></i> Thành viên chính thức
                </span>
              </div>

              <div class="text-start mt-4 px-md-3">
                <div class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-id-card"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">Họ và tên</label>
                    <span class="fw-semibold text-dark">{{ user.ho_ten }}</span>
                  </div>
                </div>

                <div class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-phone-alt"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">Số điện thoại</label>
                    <span class="fw-semibold text-dark">{{ user.so_dien_thoai }}</span>
                  </div>
                </div>

                <div class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-envelope"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">Địa chỉ Email</label>
                    <span class="fw-semibold text-dark">{{ user.email }}</span>
                  </div>
                </div>
              </div>

              <div class="d-grid gap-3 mt-4 px-md-3 pb-2">
                <button class="btn btn-primary btn-lg fw-bold shadow-sm hover-lift rounded-3" type="button"
                  data-bs-toggle="modal" data-bs-target="#editPro5">
                  <i class="fas fa-user-edit me-2 small"></i>Chỉnh sửa hồ sơ
                </button>
                <!-- <button class="btn btn-outline-primary btn-md fw-semibold border-2 hover-lift rounded-3" type="button">
                  <i class="fas fa-key me-2 small"></i>Đổi mật khẩu
                </button> -->
              </div>
            </div>
          </div>

          <!-- <div class="text-center mt-4">
            <button class="btn btn-link text-danger text-decoration-none small fw-bold hover-underline">
              <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất tài khoản
            </button>
          </div> -->

        </div>
      </div>
    </div>
  </div>

  <!-- editPro5 -->
  <div class="modal fade " id="editPro5" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content mt-5">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Cập Nhật Thông Tin</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label>Họ và tên</label>
          <input type="text" class="form-control mt-3" >
          <label class="mt-2">Số điện thoại</label>
          <input type="text" class="form-control mt-3" >
          <label class="mt-2">Địa chỉ Email</label>
          <input type="text" class="form-control mt-3" >
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserProfile',
  data() {
    return {
      user: {
        ho_ten: 'Nguyễn Văn A',
        so_dien_thoai: '0123 456 789',
        email: 'nguyenvana@example.com'
      }
    }
  }
}
</script>

<style scoped>
/* Nền texture nhẹ */
.profile-wrapper {
  background-color: #f8fafc;
  background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
  background-size: 24px 24px;
}

.profile-card {
  border-radius: 28px;
  background: #ffffff;
}

.profile-cover {
  height: 130px;
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

/* Avatar Floating */
.avatar-wrapper {
  margin-top: -65px;
  margin-bottom: 15px;
}

.avatar-main {
  width: 120px;
  height: 120px;
  background-color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 5px solid #ffffff;
  font-size: 5rem;
  /* Tăng kích thước icon FA */
}

/* Info Items */
.info-item {
  border: 1px solid #f1f5f9;
  transition: all 0.25s ease;
}

.info-item:hover {
  border-color: #3b82f6;
  transform: translateY(-3px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05) !important;
}

.icon-box-small {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1.2rem;
  /* Cỡ icon Font Awesome */
}

.bg-primary-light {
  background-color: #eff6ff;
}

.hover-lift {
  transition: all 0.2s ease;
}

.hover-lift:hover {
  transform: translateY(-2px);
  filter: brightness(1.05);
}

.hover-underline:hover {
  text-decoration: underline !important;
}

label {
  font-size: 0.65rem;
  letter-spacing: 1px;
  margin-bottom: 2px;
}

@media (max-width: 576px) {
  .card-body {
    padding: 1.5rem !important;
  }
}
</style>