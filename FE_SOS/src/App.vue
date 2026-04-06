<template>
  <component :is="layout">

  </component>
</template>

<script>
import ClientLayout from "./layout/wrapper/client.vue";
import RescuerLayout from "./layout/wrapper/rescuer.vue";
import AdminLayout from "./layout/wrapper/admin.vue";
import DefaultLayout from "./layout/wrapper/index.vue";

const default_layout = "default";

export default {
  components: {
    "client-layout": ClientLayout,
    "rescuer-layout": RescuerLayout,
    "admin-layout": AdminLayout,
    "default-layout": DefaultLayout,
  },
  computed: {
    layout() {
      return (this.$route.meta.layout || default_layout) + "-layout";
    },
  },
};
</script>
