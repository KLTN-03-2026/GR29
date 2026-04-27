import axios from "axios";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

export default function (to, from, next) {
    const token = localStorage.getItem("rescuer_token");
    if (!token) {
        next("/rescuer/login");
        return;
    }

    axios
        .get("http://127.0.0.1:8000/api/rescuer/check-token", {
            headers: {
                Authorization: "Bearer " + token,
            },
        })
        .then((res) => {
            if (res.data?.status) {
                if (res.data.ho_ten) {
                    localStorage.setItem("rescuer_name", res.data.ho_ten);
                }
                if (res.data.data) {
                    localStorage.setItem("rescuer_user", JSON.stringify(res.data.data));
                    if (res.data.data.doi_cuu_ho || res.data.data.doiCuuHo) {
                        localStorage.setItem("rescuer_team", JSON.stringify(res.data.data.doi_cuu_ho || res.data.data.doiCuuHo));
                    }
                }
                next();
            } else {
                if (res.data?.message) {
                    toaster.error(res.data.message);
                }
                localStorage.removeItem("rescuer_token");
                localStorage.removeItem("rescuer_user");
                localStorage.removeItem("rescuer_team");
                next("/rescuer/login");
            }
        })
        .catch((error) => {
            console.error("Rescuer auth check failed:", error);
            localStorage.removeItem("rescuer_token");
            localStorage.removeItem("rescuer_user");
            localStorage.removeItem("rescuer_team");
            next("/rescuer/login");
        });
}
