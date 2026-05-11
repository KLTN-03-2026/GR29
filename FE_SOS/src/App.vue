<template>
  <ConnectionStatusBanner />
  <ClientAuthRequiredModal />
  <client-layout v-if="layoutName === 'client-layout'" />
  <admin-layout v-else-if="layoutName === 'admin-layout'" />
  <rescuer-layout v-else-if="layoutName === 'rescuer-layout'" />
  <default-layout v-else />
</template>

<script>
import ClientLayout from "./layout/wrapper/client.vue";
import RescuerLayout from "./layout/wrapper/rescuer.vue";
import AdminLayout from "./layout/wrapper/admin.vue";
import DefaultLayout from "./layout/wrapper/index.vue";
import ConnectionStatusBanner from "./components/common/ConnectionStatusBanner.vue";
import ClientAuthRequiredModal from "./components/common/ClientAuthRequiredModal.vue";

const default_layout = "default";

export default {
  components: {
    ClientLayout,
    RescuerLayout,
    AdminLayout,
    DefaultLayout,
    ConnectionStatusBanner,
    ClientAuthRequiredModal,
  },
  computed: {
    layoutName() {
      return (this.$route.meta.layout || default_layout) + "-layout";
    },
  },
};
</script>
