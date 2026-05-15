<template>
    <div class="wrapper client-shell">
        <MenuClient></MenuClient>
        <main class="client-content">
            <router-view></router-view>
        </main>
        <BotClient></BotClient>
    </div>
</template>
<script>

import MenuClient from "../components/MenuClient.vue";
import BotClient from "../components/BotClient.vue";
import "../../assets/js/bootstrap.bundle.min.js";
import "../../assets/js/jquery.min.js";
import "../../assets/plugins/simplebar/js/simplebar.min.js";
import "../../assets/plugins/metismenu/js/metisMenu.min.js";
import "../../assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js";
import "../../assets/js/index.js";
import "../../assets/js/app.js";
import "../../assets/js/pace.min.js";
import "../../bootstrap-echo.js";
import { emitAccountLocked } from "../../utils/accountLockedEvent";

export default {
    name: "app",
    components: {
        MenuClient, BotClient
    },
    mounted() {
        const user = JSON.parse(localStorage.getItem("user") || "null");
        const userId = user?.id_nguoi_dung;
        if (!userId || !window.Echo) return;

        window.Echo.channel(`user.${userId}`)
            .listen('.user_status_changed', (e) => {
                if (e.trang_thai === 0 || e.trang_thai === "0") {
                    window.Echo.leaveChannel(`user.${userId}`);
                    emitAccountLocked();
                }
            });
    },
    beforeUnmount() {
        const user = JSON.parse(localStorage.getItem("user") || "null");
        const userId = user?.id_nguoi_dung;
        if (userId && window.Echo) {
            window.Echo.leaveChannel(`user.${userId}`);
        }
    }
}
</script>
<style>
@import "../../assets/plugins/simplebar/css/simplebar.css";
@import "../../assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css";
@import "../../assets/plugins/metismenu/css/metisMenu.min.css";
@import "../../assets/css/pace.min.css";
@import "../../assets/css/bootstrap.min.css";
@import "../../assets/css/bootstrap-extended.css";
@import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap");
@import "../../assets/css/app.css";
@import "../../assets/css/icons.css";
@import "../../assets/css/dark-theme.css";
@import "../../assets/css/semi-dark.css";
@import "../../assets/css/header-colors.css";

.client-shell {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.client-content {
    flex: 1 1 auto;
    min-height: 0;
    padding-top: 56px;
    padding-bottom: 60px;
}
</style>
