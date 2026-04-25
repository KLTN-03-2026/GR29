<template>
  <div class="profile-wrapper min-vh-100 py-5">
    <!-- Loading Overlay -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Đang tải...</span>
      </div>
      <p class="mt-3 text-muted small">Đang tải thông tin...</p>
    </div>

    <div class="container" v-else>
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
                <span class="badge rounded-pill px-3 fw-medium" :class="user.trang_thai == 1 ? 'bg-success bg-opacity-10 text-white border border-success border-opacity-25' : 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25'">
                  <i :class="user.trang_thai == 1 ? 'fas fa-check-circle me-1 small' : 'fas fa-ban me-1 small'"></i>
                  {{ user.trang_thai == 1 ? 'Đang hoạt động' : 'Không hoạt động' }}
                </span>
              </div>

              <div class="text-start mt-4 px-md-3">
                <div class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-id-card"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">Họ và tên</label>
                    <span class="fw-semibold text-dark">{{ user.ho_ten || '—' }}</span>
                  </div>
                </div>

                <div class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-phone-alt"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">Số điện thoại</label>
                    <span class="fw-semibold text-dark">{{ user.so_dien_thoai || '—' }}</span>
                  </div>
                </div>

                <div class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-envelope"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">Địa chỉ Email</label>
                    <span class="fw-semibold text-dark">{{ user.email || '—' }}</span>
                  </div>
                </div>

                <div v-if="user.dia_chi" class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-map-marker-alt"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">Địa chỉ</label>
                    <span class="fw-semibold text-dark">{{ user.dia_chi }}</span>
                  </div>
                </div>

                <div v-if="user.khu_vuc" class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-map"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">Khu vực</label>
                    <span class="fw-semibold text-dark">{{ user.khu_vuc }}</span>
                  </div>
                </div>
              </div>

              <div class="d-grid gap-3 mt-4 px-md-3 pb-2">
                <button class="btn btn-primary btn-lg fw-bold shadow-sm hover-lift rounded-3" type="button"
                  @click="openEditModal">
                  <i class="fas fa-user-edit me-2 small"></i>Chỉnh sửa hồ sơ
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Edit Profile Modal (Vue-based, no Bootstrap JS needed) -->
    <Transition name="modal-fade">
      <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
        <div class="modal-dialog-custom">
          <div class="modal-content-custom">
            <div class="modal-header-custom">
              <h5 class="modal-title-custom">
                <i class="fas fa-user-edit me-2 text-primary"></i>Cập nhật thông tin
              </h5>
              <button type="button" class="btn-close-custom" @click="closeEditModal" :disabled="isSaving">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div class="modal-body-custom">
              <div class="mb-3">
                <label class="form-label fw-semibold small text-muted text-uppercase tracking-wider">Họ và tên</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-user text-muted"></i>
                  </span>
                  <input v-model="form.ho_ten" type="text" class="form-control border-start-0 ps-0"
                    placeholder="Nhập họ và tên" :class="{ 'is-invalid': errors.ho_ten }" />
                  <div v-if="errors.ho_ten" class="invalid-feedback d-block">{{ errors.ho_ten }}</div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small text-muted text-uppercase tracking-wider">Số điện thoại</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-phone text-muted"></i>
                  </span>
                  <input v-model="form.so_dien_thoai" type="tel" class="form-control border-start-0 ps-0"
                    placeholder="Nhập số điện thoại" :class="{ 'is-invalid': errors.so_dien_thoai }" />
                  <div v-if="errors.so_dien_thoai" class="invalid-feedback d-block">{{ errors.so_dien_thoai }}</div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small text-muted text-uppercase tracking-wider">Địa chỉ Email</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-envelope text-muted"></i>
                  </span>
                  <input v-model="form.email" type="email" class="form-control border-start-0 ps-0"
                    placeholder="Nhập địa chỉ email" :class="{ 'is-invalid': errors.email }" />
                  <div v-if="errors.email" class="invalid-feedback d-block">{{ errors.email }}</div>
                </div>
              </div>
            </div>
            <div class="modal-footer-custom">
              <button type="button" class="btn btn-secondary" @click="closeEditModal" :disabled="isSaving">
                Hủy
              </button>
              <button type="button" class="btn btn-primary" @click="saveProfile" :disabled="isSaving">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2" role="status"></span>
                <i v-else class="fas fa-save me-2"></i>
                {{ isSaving ? 'Đang lưu...' : 'Lưu thay đổi' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script>
import { clientAPI } from "../../../services/api.js";

export default {
  name: "UserProfile",
  data() {
    return {
      user: {
        ho_ten: "",
        so_dien_thoai: "",
        email: "",
        trang_thai: 1,
        dia_chi: null,
        khu_vuc: null,
      },
      form: {
        ho_ten: "",
        so_dien_thoai: "",
        email: "",
      },
      errors: {
        ho_ten: "",
        so_dien_thoai: "",
        email: "",
      },
      isLoading: false,
      isSaving: false,
      showEditModal: false,
    };
  },
  mounted() {
    this.loadUserFromStorage();
    this.fetchProfile();
  },
  methods: {
    loadUserFromStorage() {
      try {
        const raw = localStorage.getItem("user");
        if (raw) {
          const stored = JSON.parse(raw);
          this.user = {
            ho_ten: stored.ho_ten || "",
            so_dien_thoai: stored.so_dien_thoai || "",
            email: stored.email || "",
            trang_thai: stored.trang_thai ?? 1,
            dia_chi: stored.dia_chi || null,
            khu_vuc: stored.khu_vuc || null,
          };
        }
      } catch (e) {
        console.warn("Failed to parse stored user data:", e);
      }
    },

    async fetchProfile() {
      this.isLoading = true;
      try {
        const res = await clientAPI.getProfile();
        if (res.data?.status && res.data?.data) {
          const fresh = res.data.data;
          this.user = {
            ho_ten: fresh.ho_ten || "",
            so_dien_thoai: fresh.so_dien_thoai || "",
            email: fresh.email || "",
            trang_thai: fresh.trang_thai ?? 1,
            dia_chi: fresh.dia_chi || null,
            khu_vuc: fresh.khu_vuc || null,
          };
          localStorage.setItem("user", JSON.stringify(fresh));
        }
      } catch (err) {
        console.error("Failed to fetch profile:", err);
        if (err.response?.status === 401 || err.response?.status === 403) {
          localStorage.removeItem("token");
          localStorage.removeItem("user_token");
          localStorage.removeItem("user");
          if (typeof this.$toast !== "undefined") {
            this.$toast.error("Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.");
          }
          this.$router.push("/client/login");
          return;
        }
        if (typeof this.$toast !== "undefined") {
          this.$toast.error(err.response?.data?.message || "Không thể tải thông tin hồ sơ.");
        }
      } finally {
        this.isLoading = false;
      }
    },

    openEditModal() {
      this.form = {
        ho_ten: this.user.ho_ten,
        so_dien_thoai: this.user.so_dien_thoai,
        email: this.user.email,
      };
      this.errors = { ho_ten: "", so_dien_thoai: "", email: "" };
      this.showEditModal = true;
      document.body.style.overflow = "hidden";
    },

    closeEditModal() {
      if (this.isSaving) return;
      this.showEditModal = false;
      this.resetForm();
      document.body.style.overflow = "";
    },

    resetForm() {
      this.form = { ho_ten: "", so_dien_thoai: "", email: "" };
      this.errors = { ho_ten: "", so_dien_thoai: "", email: "" };
    },

    validateForm() {
      let valid = true;
      this.errors = { ho_ten: "", so_dien_thoai: "", email: "" };

      if (!this.form.ho_ten || !this.form.ho_ten.trim()) {
        this.errors.ho_ten = "Họ và tên không được để trống.";
        valid = false;
      }

      if (!this.form.so_dien_thoai || !this.form.so_dien_thoai.trim()) {
        this.errors.so_dien_thoai = "Số điện thoại không được để trống.";
        valid = false;
      } else if (!/^0\d{9,10}$/.test(this.form.so_dien_thoai.trim())) {
        this.errors.so_dien_thoai = "Số điện thoại không hợp lệ (0xxx xxx xxxx).";
        valid = false;
      }

      if (!this.form.email || !this.form.email.trim()) {
        this.errors.email = "Email không được để trống.";
        valid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email.trim())) {
        this.errors.email = "Địa chỉ email không hợp lệ.";
        valid = false;
      }

      return valid;
    },

    async saveProfile() {
      if (!this.validateForm()) return;

      this.isSaving = true;
      try {
        const res = await clientAPI.updateProfile({
          ho_ten: this.form.ho_ten.trim(),
          so_dien_thoai: this.form.so_dien_thoai.trim(),
          email: this.form.email.trim(),
        });

        if (res.data?.status) {
          const updated = res.data.data;
          this.user = {
            ho_ten: updated.ho_ten || this.form.ho_ten.trim(),
            so_dien_thoai: updated.so_dien_thoai || this.form.so_dien_thoai.trim(),
            email: updated.email || this.form.email.trim(),
            trang_thai: updated.trang_thai ?? this.user.trang_thai,
            dia_chi: updated.dia_chi || this.user.dia_chi,
            khu_vuc: updated.khu_vuc || this.user.khu_vuc,
          };
          localStorage.setItem("user", JSON.stringify(updated));
          this.showEditModal = false;
          document.body.style.overflow = "";

          if (typeof this.$toast !== "undefined") {
            this.$toast.success(res.data.message || "Cập nhật hồ sơ thành công!");
          }
        } else {
          if (typeof this.$toast !== "undefined") {
            this.$toast.error(res.data?.message || "Cập nhật thất bại.");
          }
        }
      } catch (err) {
        console.error("Failed to save profile:", err);
        if (typeof this.$toast !== "undefined") {
          this.$toast.error(err.response?.data?.message || "Không thể cập nhật hồ sơ. Vui lòng thử lại.");
        }
      } finally {
        this.isSaving = false;
      }
    },
  },
};
</script>

<style scoped>
.profile-wrapper {
  background-color: #f8fafc;
  background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
  background-size: 24px 24px;
  position: relative;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(248, 250, 252, 0.85);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.profile-card {
  border-radius: 28px;
  background: #ffffff;
}

.profile-cover {
  height: 130px;
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

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
}

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

label {
  font-size: 0.65rem;
  letter-spacing: 1px;
  margin-bottom: 2px;
}

.input-group-text {
  border-radius: 12px 0 0 12px;
  border: 1px solid #e2e8f0;
  border-right: none;
  font-size: 0.875rem;
}

.form-control {
  border-radius: 0 12px 12px 0;
  border: 1px solid #e2e8f0;
  font-size: 0.95rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control.is-invalid {
  border-color: #ef4444;
}

/* ============ Vue Modal ============ */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.modal-dialog-custom {
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content-custom {
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.modal-header-custom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.modal-title-custom {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.btn-close-custom {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: #94a3b8;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 8px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
}

.btn-close-custom:hover {
  background: #f1f5f9;
  color: #475569;
}

.modal-body-custom {
  padding: 1.5rem;
}

.modal-footer-custom {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #f1f5f9;
  background: #fafafa;
}

/* Transition */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .modal-dialog-custom,
.modal-fade-leave-active .modal-dialog-custom {
  transition: transform 0.25s ease;
}

.modal-fade-enter-from .modal-dialog-custom {
  transform: scale(0.95) translateY(-20px);
}

.modal-fade-leave-to .modal-dialog-custom {
  transform: scale(0.95) translateY(-20px);
}

@media (max-width: 576px) {
  .card-body {
    padding: 1.5rem !important;
  }
}
</style>
