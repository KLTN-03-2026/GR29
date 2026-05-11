<template>
  <nav class="navbar navbar-expand px-3 py-2 shadow-sm" style="background-color: #1a2a40;">
    <div class="container-fluid">
      <div class="d-flex align-items-center gap-3 flex-grow-1">
        <button class="btn btn-sm btn-outline-light d-md-none" type="button" @click="$emit('toggle-sidebar')">
          <i class="fa-solid fa-bars"></i>
        </button>
        <router-link class="navbar-brand d-flex align-items-center fw-bold fs-4" to="/rescuer/home">
          nowSOS <span class="text-danger ms-1">RESCUER</span>
        </router-link>
      </div>

      <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end">
        <template v-if="!isLoggedIn">
          <router-link to="/rescuer/login" class="btn btn-outline-light btn-sm rounded-pill px-3">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Đăng nhập
          </router-link>
        </template>

        <template v-else>
          <div class="d-none d-lg-flex align-items-center bg-warning bg-opacity-25 rounded-pill px-3 py-1 border border-warning border-opacity-50">
            <i class="fa-solid fa-user-shield text-warning me-2"></i>
            <span class="text-white small fw-semibold">Khu vực thành viên</span>
          </div>

          <div class="d-none d-lg-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-1 border border-white border-opacity-25">
            <span class="text-white-50 small fw-bold me-2">TRẠNG THÁI TRỰC:</span>
            <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
              <input class="form-check-input shadow-none cursor-pointer" type="checkbox" checked role="switch" id="statusSwitch">
              <label class="form-check-label text-white small fw-bold ms-2 cursor-pointer" for="statusSwitch">ONLINE</label>
            </div>
          </div>

          <div class="d-none d-md-flex flex-column text-end">
            <div class="text-white-50 fw-bold" style="font-size: 10px;">ĐỘI CỨU HỘ</div>
            <div class="text-white fw-bold">{{ teamName || 'Chưa có đội' }}</div>
          </div>

          <button class="btn btn-link text-white p-0 position-relative shadow-none d-none d-lg-inline-flex">
            <i class="bi bi-bell-fill fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5rem;">1</span>
          </button>

          <div class="dropdown">
            <button class="btn btn-link p-0 border-0 shadow-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <img :src="avatarSrc" class="rounded-circle border border-warning border-2" width="38" height="38" :alt="rescuerName">
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
              <li class="px-3 py-2 border-bottom">
                <div class="fw-semibold text-dark">{{ rescuerName }}</div>
                <div class="text-muted small">
                  <i class="fa-solid fa-star text-warning me-1"></i>
                  Thành viên đội cứu hộ
                </div>
                <div class="text-muted small" v-if="teamName">
                  <i class="fa-solid fa-users text-info me-1"></i>
                  {{ teamName }}
                </div>
              </li>
              <li><hr class="dropdown-divider my-1"></li>
              <li><a class="dropdown-item text-danger" href="#" @click.prevent="logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a></li>
            </ul>
          </div>
        </template>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'TopRescuer',
  data() {
    return {
      avatarColors: ['1abc9c', '2ecc71', '3498db', '9b59b6', 'f39c12', 'e74c3c', '16a085', '27ae60'],
    };
  },
  computed: {
    isLoggedIn() {
      return !!localStorage.getItem("rescuer_token");
    },
    rescuerName() {
      try {
        const raw = localStorage.getItem("rescuer_user");
        if (!raw) return localStorage.getItem("rescuer_name") || "Thành viên";
        const u = JSON.parse(raw);
        return u.ho_ten || u.name || localStorage.getItem("rescuer_name") || "Thành viên";
      } catch {
        return localStorage.getItem("rescuer_name") || "Thành viên";
      }
    },
    teamName() {
      try {
        const raw = localStorage.getItem("rescuer_team");
        if (!raw) return "";
        const team = JSON.parse(raw);
        return team.ten_doi || team.name || team.ma_doi || "";
      } catch {
        return "";
      }
    },
    avatarSrc() {
      const name = encodeURIComponent(this.rescuerName);
      const color = this.avatarColors[this.rescuerName.length % this.avatarColors.length];
      return `https://ui-avatars.com/api/?name=${name}&background=${color}&color=fff&bold=true`;
    },
  },
  methods: {
    logout() {
      localStorage.removeItem("rescuer_token");
      localStorage.removeItem("rescuer_user");
      localStorage.removeItem("rescuer_team");
      localStorage.removeItem("rescuer_name");
      this.$router.push("/rescuer/login");
    },
  },
};
</script>
