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

                // =============================================================
                // Role-based access control for rescuer panel
                // Roles (slug_chuc_vu): manager | team_leader | member
                // vai_tro_trong_doi: 0=manager, 1=team_leader, 2=member
                // =============================================================

                // quan-ly-thanh-vien: manager + team_leader only
                if (to.path === "/rescuer/quan-ly-thanh-vien") {
                    const raw = localStorage.getItem("rescuer_user");
                    if (raw) {
                        try {
                            const u = JSON.parse(raw);
                            const roleMap = { 0: 'manager', 1: 'team_leader', 2: 'member' };
                            const role = u.slug_chuc_vu || roleMap[u.vai_tro_trong_doi] || "";
                            if (role !== "manager" && role !== "team_leader") {
                                toaster.error("Bạn không có quyền truy cập trang này");
                                next("/rescuer/home");
                                return;
                            }
                        } catch (e) {
                            next("/rescuer/home");
                            return;
                        }
                    }
                }

                // reports: manager + team_leader only
                if (to.path === "/rescuer/reports") {
                    const raw = localStorage.getItem("rescuer_user");
                    if (raw) {
                        try {
                            const u = JSON.parse(raw);
                            const roleMap = { 0: 'manager', 1: 'team_leader', 2: 'member' };
                            const role = u.slug_chuc_vu || roleMap[u.vai_tro_trong_doi] || "";
                            if (role !== "manager" && role !== "team_leader") {
                                toaster.error("Bạn không có quyền truy cập trang này");
                                next("/rescuer/home");
                                return;
                            }
                        } catch (e) {
                            next("/rescuer/home");
                            return;
                        }
                    }
                }

                // heatmap: manager + team_leader only
                if (to.path === "/rescuer/heatmap") {
                    const raw = localStorage.getItem("rescuer_user");
                    if (raw) {
                        try {
                            const u = JSON.parse(raw);
                            const roleMap = { 0: 'manager', 1: 'team_leader', 2: 'member' };
                            const role = u.slug_chuc_vu || roleMap[u.vai_tro_trong_doi] || "";
                            if (role !== "manager" && role !== "team_leader") {
                                toaster.error("Bạn không có quyền truy cập trang này");
                                next("/rescuer/home");
                                return;
                            }
                        } catch (e) {
                            next("/rescuer/home");
                            return;
                        }
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
