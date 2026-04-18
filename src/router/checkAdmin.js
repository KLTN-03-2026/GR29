import axios from "axios";
import { createToaster } from "@meforma/vue-toaster";
const toaster = createToaster({ position: "top-right" });
export default function (to, from, next) {
    var token = localStorage.getItem("admin_token");
    console.log(token);
    
    axios
        .get("http://127.0.0.1:8000/api/admin/check-token", {
            headers: {
                Authorization: "Bearer " + token,
            },
        })
        .then((res) => {
            if (res.data.status) {
                // localStorage.setItem("ho_ten", res.data.ho_ten);
                localStorage.setItem("email", res.data.email);
                next();
            } else {
                toaster.error(res.data.message);
                next("/admin/login");
            }
        });
}