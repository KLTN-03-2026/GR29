import axios from "axios";
import { createToaster } from "@meforma/vue-toaster";
const toaster = createToaster({ position: "top-right" });
export default function (to, from, next) {
    var token = localStorage.getItem("key_rescuer");
    axios
        .get("http://127.0.0.1:8000/api/doi-cuu-ho/check-token", {
            headers: {
                Authorization: "Bearer " + token,
            },
        })
        .then((res) => {
            if (res.data.status) {
                localStorage.setItem("ho_ten", res.data.ho_ten);
                localStorage.setItem("email_tv", res.data.email);
                localStorage.setItem("check_tv", res.data.status);
                next();
            } else {
                toaster.error(res.data.message);
                next("/rescuser/login");
            }
        });
}