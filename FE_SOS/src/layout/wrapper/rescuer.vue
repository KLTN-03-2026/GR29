<template>
  <div class="admin-shell">
    <aside class="admin-sidebar d-none d-md-flex flex-column">
      <MenuRescuer />
    </aside>
    <div class="admin-main">
      <TopRescuer />
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
import TopRescuer from "../components/TopRescuer.vue";
import MenuRescuer from "../components/MenuRescuer.vue";

export default {
  name: "RescuerLayout",
  components: {
    TopRescuer,
    MenuRescuer,
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
}

@media (max-width: 767.98px) {
  .admin-shell {
    flex-direction: column;
    height: auto;
    overflow: auto;
  }

  .admin-sidebar {
    position: static;
    width: 100%;
    height: auto;
  }

  .admin-main {
    height: auto;
    overflow: visible;
  }

  .admin-content {
    overflow: visible;
  }
}
</style>