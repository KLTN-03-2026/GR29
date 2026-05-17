import axios from "axios";
import { createToaster } from "@meforma/vue-toaster";
import { emitClientAuthRequired } from "../utils/clientAuthPrompt";
import { emitAccountLocked } from "../utils/accountLockedEvent";

const toaster = createToaster({ position: "top-right" });
export default function (to, from, next) {
    const token = localStorage.getItem("token") || localStorage.getItem("user_token");
    if (!token) {
        emitClientAuthRequired(to.fullPath);
        next(false);
        return;
    }

    axios
        .get("https://bekltn.nowsos.site/api/nguoi-dung/check-client", {
            headers: {
                Authorization: "Bearer " + token,
            },
        })
        .then((res) => {
            if (res.data?.status) {
                if (res.data.data) {
                    localStorage.setItem("user", JSON.stringify(res.data.data));
                }
                next();
            } else {
                const isLocked = res.data?.data?.trang_thai === 0
                    || res.data?.data?.trang_thai === "0"
                    || res.data?.locked === true;

                if (isLocked) {
                    next(false);
                    emitAccountLocked();
                } else {
                    if (res.data?.message) {
                        toaster.error(res.data.message);
                    }
                    localStorage.removeItem("token");
                    localStorage.removeItem("user_token");
                    localStorage.removeItem("user");
                    next("/client/login");
                }
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
