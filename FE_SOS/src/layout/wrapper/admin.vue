<template>
  <div class="admin-shell">
    <aside class="admin-sidebar d-none d-md-flex flex-column" :class="{ open: isSidebarOpen }">
      <MenuAdmin />
    </aside>
    <div v-if="isSidebarOpen" class="admin-sidebar-backdrop d-md-none" @click="closeSidebar"></div>
    <div class="admin-main">
      <TopAdmin @toggle-sidebar="toggleSidebar" />
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
import "../../assets/css/badge-utils.css";
import "../../assets/css/app.css";
import "../../assets/css/icons.css";
import "../../assets/plugins/apexcharts-bundle/js/apexcharts.js";
import "../../assets/plugins/apexcharts-bundle/css/apexcharts.css";
import TopAdmin from "../components/TopAdmin.vue";
import MenuAdmin from "../components/MenuAdmin.vue";

export default {
  name: "AdminLayout",
  components: {
    TopAdmin,
    MenuAdmin,
  },
  data() {
    return {
      isSidebarOpen: false,
    };
  },
  methods: {
    toggleSidebar() {
      this.isSidebarOpen = !this.isSidebarOpen;
    },
    closeSidebar() {
      this.isSidebarOpen = false;
    },
  },
};
</script>

<style scoped>
.admin-shell {
  display: flex;
  min-height: 100vh;
  height: 100vh;
  overflow: hidden;
  background-color: #f3f4f6;
}

.admin-sidebar {
  width: 260px;
  flex-shrink: 0;
  background: #111827;
  color: #e5e7eb;
  border-right: 1px solid rgba(31, 41, 55, 0.8);
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
}

.admin-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  height: 100vh;
  overflow: hidden;
}

.admin-content {
  padding: 1.5rem 1.5rem 2rem;
  overflow-y: auto;
  flex: 1;
  min-width: 0;
}

.admin-sidebar-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  z-index: 1040;
}

@media (max-width: 767.98px) {
  .admin-shell {
    flex-direction: column;
    height: auto;
    overflow: auto;
  }

  .admin-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 260px;
    max-width: 85vw;
    transform: translateX(-100%);
    transition: transform 0.22s ease;
    z-index: 1050;
    background: #111827;
    display: flex !important;
    overflow-y: auto;
  }

  .admin-sidebar.open {
    transform: translateX(0);
  }

  .admin-main {
    height: auto;
    overflow: visible;
  }

  .admin-content {
    overflow: visible;
    padding-top: 1rem;
  }
}
</style>