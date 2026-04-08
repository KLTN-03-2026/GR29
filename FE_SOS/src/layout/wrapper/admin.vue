<template>
  <div class="admin-shell">
    <aside class="admin-sidebar d-none d-md-flex flex-column">
      <MenuAdmin />
    </aside>
    <div class="admin-main">
      <TopAdmin />
      <main class="admin-content">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script>
import "../../assets/js/bootstrap.bundle.min.js";
import "../../assets/css/bootstrap.min.css";
import "../../assets/css/bootstrap-extended.css";
import "../../assets/css/app.css";
import "../../assets/css/icons.css";
import TopAdmin from "../components/TopAdmin.vue";
import MenuAdmin from "../components/MenuAdmin.vue";

export default {
  name: "AdminLayout",
  components: {
    TopAdmin,
    MenuAdmin,
  },
};
</script>

<style scoped>
.admin-shell {
  display: flex;
  min-height: 100vh;
  background-color: #f3f4f6;
}

.admin-sidebar {
  width: 260px;
  background: #111827;
  color: #e5e7eb;
  border-right: 1px solid rgba(31, 41, 55, 0.8);
}

.admin-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.admin-content {
  padding: 1.5rem 1.5rem 2rem;
}

@media (max-width: 767.98px) {
  .admin-shell {
    flex-direction: column;
  }
}
</style>