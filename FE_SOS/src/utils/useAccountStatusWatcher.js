import { onMounted, onUnmounted } from "vue";
import axios from "axios";
import { emitAccountLocked } from "./accountLockedEvent";

const POLL_INTERVAL = 30000; // 30 giây

export function useAccountStatusWatcher() {
    let timer = null;

    async function checkStatus() {
        const token = localStorage.getItem("token") || localStorage.getItem("user_token");
        if (!token) return;

        try {
            const res = await axios.get("http://127.0.0.1:8000/api/nguoi-dung/check-client", {
                headers: { Authorization: "Bearer " + token },
            });

            if (!res.data?.status) {
                const isLocked = res.data?.data?.trang_thai === 0
                    || res.data?.data?.trang_thai === "0"
                    || res.data?.locked === true
                    || res.data?.message?.toLowerCase().includes("khóa")
                    || res.data?.message?.toLowerCase().includes("locked");

                if (isLocked) {
                    stopWatcher();
                    emitAccountLocked();
                }
            }
        } catch {
            // Bỏ qua lỗi mạng, không logout
        }
    }

    function startWatcher() {
        timer = setInterval(checkStatus, POLL_INTERVAL);
    }

    function stopWatcher() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    onMounted(startWatcher);
    onUnmounted(stopWatcher);
}
