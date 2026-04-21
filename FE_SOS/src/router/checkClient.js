import axios from "axios";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });
export default function (to, from, next) {
    const token = localStorage.getItem("token") || localStorage.getItem("user_token");
    if (!token) {
        next("/client/login");
        return;
    }

    axios
        .get("http://127.0.0.1:8000/api/nguoi-dung/check-client", {
            headers: {
                Authorization: "Bearer " + token,
            },
        })
        .then((res) => {
            if (res.data?.status) {
                // Update stored user data with fresh data from server
                if (res.data.data) {
                    localStorage.setItem("user", JSON.stringify(res.data.data));
                }
                next();
            } else {
                if (res.data?.message) {
                    toaster.error(res.data.message);
                }
                localStorage.removeItem("token");
                localStorage.removeItem("user_token");
                localStorage.removeItem("user");
                next("/client/login");
            }
        })
        .catch((error) => {
            console.error("Client auth check failed:", error);
            localStorage.removeItem("token");
            localStorage.removeItem("user_token");
            localStorage.removeItem("user");
            next("/client/login");
        });
}
