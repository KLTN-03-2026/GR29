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
                <h3 class="fw-bold text-dark mb-1">{{ user.ho_ten || "Chua cap nhat" }}</h3>
                <span class="badge rounded-pill bg-primary-light text-primary px-3 fw-medium">
                  <i class="fas fa-check-circle me-1 small"></i> Thanh vien chinh thuc
                </span>
              </div>

              <div class="text-start mt-4 px-md-3">
                <div class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-id-card"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">Ho va ten</label>
                    <span class="fw-semibold text-dark">{{ user.ho_ten || "Chua cap nhat" }}</span>
                  </div>
                </div>

                <div class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-phone-alt"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">So dien thoai</label>
                    <span class="fw-semibold text-dark">{{ user.so_dien_thoai || "Chua cap nhat" }}</span>
                  </div>
                </div>

                <div class="info-item d-flex align-items-center mb-3 p-3 rounded-4 shadow-sm bg-white">
                  <div class="icon-box-small me-3 rounded-3 bg-primary-light text-primary">
                    <i class="fas fa-envelope"></i>
                  </div>
                  <div class="flex-grow-1">
                    <label class="text-muted small d-block fw-bold text-uppercase tracking-wider">Dia chi email</label>
                    <span class="fw-semibold text-dark">{{ user.email || "Chua cap nhat" }}</span>
                  </div>
                </div>
              </div>

              <div class="d-grid gap-3 mt-4 px-md-3 pb-2">
                <button
                  class="btn btn-primary btn-lg fw-bold shadow-sm hover-lift rounded-3"
                  type="button"
                  data-bs-toggle="modal"
                  data-bs-target="#editProfileModal"
                  @click="openEditModal"
                >
                  <i class="fas fa-user-edit me-2 small"></i>Chinh sua ho so
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content mt-5">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Cap nhat thong tin</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form @submit.prevent="saveProfile">
          <div class="modal-body">
            <label>Ho va ten</label>
            <input v-model.trim="editForm.ho_ten" type="text" class="form-control mt-3" required />

            <label class="mt-2">So dien thoai</label>
            <input v-model.trim="editForm.so_dien_thoai" type="text" class="form-control mt-3" required />

            <label class="mt-2">Dia chi email</label>
            <input v-model.trim="editForm.email" type="email" class="form-control mt-3" required />
          </div>

          <div class="modal-footer">
            <button ref="closeModalButton" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Close
            </button>
            <button type="submit" class="btn btn-primary" :disabled="isSaving || isLoading">
              {{ isSaving ? "Dang luu..." : "Save changes" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { authAPI, userAPI } from "../../../services/api.js";

export default {
  name: "UserProfile",
  data() {
    return {
      user: {
        id_nguoi_dung: null,
        ho_ten: "",
        so_dien_thoai: "",
        email: "",
      },
      editForm: {
        ho_ten: "",
        so_dien_thoai: "",
        email: "",
      },
      isLoading: false,
      isSaving: false,
    };
  },
  mounted() {
    this.loadProfile();
  },
  methods: {
    getStoredUser() {
      try {
        return JSON.parse(localStorage.getItem("user") || "{}");
      } catch (error) {
        return {};
      }
    },
    normalizeUser(payload = {}) {
      return {
        id_nguoi_dung: payload.id_nguoi_dung || payload.id || null,
        ho_ten: payload.ho_ten || "",
        so_dien_thoai: payload.so_dien_thoai || "",
        email: payload.email || "",
      };
    },
    syncLocalUser(user) {
      const currentUser = this.getStoredUser();
      localStorage.setItem("user", JSON.stringify({ ...currentUser, ...user }));
      if (user.ho_ten) {
        localStorage.setItem("ho_ten", user.ho_ten);
      }
      if (user.email) {
        localStorage.setItem("email_kh", user.email);
      }
    },
    applyUser(user) {
      const normalizedUser = this.normalizeUser(user);
      this.user = normalizedUser;
      this.editForm = {
        ho_ten: normalizedUser.ho_ten,
        so_dien_thoai: normalizedUser.so_dien_thoai,
        email: normalizedUser.email,
      };
      this.syncLocalUser(normalizedUser);
    },
    openEditModal() {
      this.editForm = {
        ho_ten: this.user.ho_ten || "",
        so_dien_thoai: this.user.so_dien_thoai || "",
        email: this.user.email || "",
      };
    },
    async loadProfile() {
      this.isLoading = true;
      try {
        const storedUser = this.normalizeUser(this.getStoredUser());
        if (storedUser.id_nguoi_dung || storedUser.ho_ten || storedUser.email) {
          this.applyUser(storedUser);
        }

        const res = await authAPI.checkClient();
        const body = res.data || {};
        const fetchedUser = body.data || body.user || body.nguoi_dung || body;
        this.applyUser(fetchedUser);
      } catch (error) {
        const fallbackUser = this.normalizeUser(this.getStoredUser());
        if (fallbackUser.id_nguoi_dung || fallbackUser.ho_ten || fallbackUser.email) {
          this.applyUser(fallbackUser);
        } else {
          this.$toast.error(error.response?.data?.message || "Khong tai duoc thong tin tai khoan");
        }
      } finally {
        this.isLoading = false;
      }
    },
    async saveProfile() {
      if (!this.user.id_nguoi_dung) {
        this.$toast.error("Khong xac dinh duoc tai khoan de cap nhat");
        return;
      }

      this.isSaving = true;
      try {
        const payload = {
          ho_ten: this.editForm.ho_ten,
          so_dien_thoai: this.editForm.so_dien_thoai,
          email: this.editForm.email,
        };

        const res = await userAPI.update(this.user.id_nguoi_dung, payload);
        const body = res.data || {};
        const updatedUser = body.data || body.user || body.nguoi_dung || payload;

        this.applyUser({
          ...this.user,
          ...updatedUser,
        });

        this.$refs.closeModalButton?.click();
        this.$toast.success(body.message || "Cap nhat thong tin thanh cong");
      } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors) {
          Object.values(errors).forEach((messages) => {
            this.$toast.error(messages[0]);
          });
        } else {
          this.$toast.error(error.response?.data?.message || "Cap nhat thong tin that bai");
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

@media (max-width: 576px) {
  .card-body {
    padding: 1.5rem !important;
  }
}
</style>
