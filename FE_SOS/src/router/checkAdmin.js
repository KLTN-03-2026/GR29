import axios from "axios";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });
export default function (to, from, next) {
    const token = localStorage.getItem("admin_token");
    if (!token) {
        next("/admin/login");
        return;
    }

    axios
        .get("https://bekltn.nowsos.site/api/admin/check-token", {
            headers: {
                Authorization: "Bearer " + token,
            },
        })
        .then((res) => {
            if (res.data?.status) {
                localStorage.setItem("ho_ten", res.data.ho_ten);
                if (res.data.data) {
                    localStorage.setItem("admin_user", JSON.stringify(res.data.data));
                }
                next();
            } else {
                if (res.data?.message) {
                    toaster.error(res.data.message);
                }
                localStorage.removeItem("admin_token");
                localStorage.removeItem("admin_user");
                next("/admin/login");
            }
        })
        .catch((error) => {
            console.error("Admin auth check failed:", error);
            localStorage.removeItem("admin_token");
            localStorage.removeItem("admin_user");
            next("/admin/login");
        });
}
