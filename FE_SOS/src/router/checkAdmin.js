import axios from "axios";
import { createToaster } from "@meforma/vue-toaster"

const toaster = createToaster({ position: "top-right" });
export default function (to, from, next) {
    const token = localStorage.getItem("admin_token");
    if (!token) {
        next("/admin/login");
        return;
    }

    axios
        .get("http://127.0.0.1:8000/api/admin/check-token", {
            headers: {
                Authorization: "Bearer " + token,
            },
        })
        .then((res) => {
            if (res.data?.status) {
                localStorage.setItem("ho_ten", res.data.ho_ten);
                next();
            } else {
                if (res.data?.message) {
                    toaster.error(res.data.message);
                }
                next("/admin/login");
            }
        })
        .catch((error) => {
            console.error("Admin auth check failed:", error);
            next("/admin/login");
        });
}
