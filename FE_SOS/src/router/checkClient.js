import axios from "axios";
import { createToaster } from "@meforma/vue-toaster";
const toaster = createToaster({ position: "top-right" });
export default function (to, from, next) {
    var token = localStorage.getItem("user_token");
    axios
        .get("http://127.0.0.1:8000/api/nguoi-dung/check-client", {
            headers: {
                Authorization: "Bearer " + token,
            },
        })
        .then((res) => {
            if (res.data.status) {
                localStorage.setItem("ho_ten", res.data.ho_ten);
                localStorage.setItem("email_kh", res.data.email);
                localStorage.setItem("check_kh", res.data.status);
                next();
            } else {
                toaster.error(res.data.message);
                next("/client/login");
            }
        });
}