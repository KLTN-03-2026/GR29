import { createRouter, createWebHistory } from "vue-router";
import checkClient from "./checkClient";
import axios from "axios";
import { createToaster } from "@meforma/vue-toaster";
import {
  canAccessAdminRoute,
  canAccessRescuerRoute,
  getAdminRole,
  getRescuerRole,
} from "../utils/permissions";

const toaster = createToaster({ position: "top-right" });

function fetchAdminUserAndProceed(to, from, next) {
  const token = localStorage.getItem("admin_token");
  if (!token) {
    next("/admin/login");
    return;
  }

  axios
    .get("https://bekltn.nowsos.site/api/admin/check-token", {
      headers: { Authorization: "Bearer " + token },
    })
    .then((res) => {
      if (res.data?.status) {
        localStorage.setItem("ho_ten", res.data.ho_ten);
        if (res.data.data) {
          localStorage.setItem("admin_user", JSON.stringify(res.data.data));
        }
        const role = getAdminRole();
        if (!canAccessAdminRoute(role, to.path)) {
          toaster.error("Bạn không có quyền sử dụng chức năng này");
          next(false);
          return;
        }
        next();
      } else {
        if (res.data?.message) toaster.error(res.data.message);
        localStorage.removeItem("admin_token");
        localStorage.removeItem("admin_user");
        next("/admin/login");
      }
    })
    .catch(() => {
      localStorage.removeItem("admin_token");
      localStorage.removeItem("admin_user");
      next("/admin/login");
    });
}

function fetchRescuerUserAndProceed(to, from, next) {
  const token = localStorage.getItem("rescuer_token");
  if (!token) {
    next("/rescuer/login");
    return;
  }

  axios
    .get("https://bekltn.nowsos.site/api/rescuer/check-token", {
      headers: { Authorization: "Bearer " + token },
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
        const role = getRescuerRole();
        if (!canAccessRescuerRoute(role, to.path)) {
          toaster.error("Bạn không có quyền sử dụng chức năng này");
          next(false);
          return;
        }
        next();
      } else {
        if (res.data?.message) toaster.error(res.data.message);
        localStorage.removeItem("rescuer_token");
        localStorage.removeItem("rescuer_user");
        localStorage.removeItem("rescuer_team");
        next("/rescuer/login");
      }
    })
    .catch(() => {
      localStorage.removeItem("rescuer_token");
      localStorage.removeItem("rescuer_user");
      localStorage.removeItem("rescuer_team");
      next("/rescuer/login");
    });
}

const routes = [
    // client
    {
        path: "/client/register",
        component: () => import("../components/Client/DangKy/index.vue"),
    },
    {
        path: "/client/login",
        component: () => import("../components/Client/DangNhap/index.vue"),
    },
    {
        path: "/",
        component: () => import("../components/Client/TrangChu/index.vue"),
        meta: { layout: "client" },
    },
    {
        path: "/client/dang-xu-ly",
        component: () => import("../components/Client/DangXuLy/index.vue"),
        meta: { layout: "client" },
    },
    {
        path: "/client/requests",
        component: () => import("../components/Client/Request/index.vue"),
        meta: { layout: "client" },
    },
    {
        path: "/client/profile",
        component: () => import("../components/Client/Profile/index.vue"),
        meta: { layout: "client" },
    },
    {
        path: "/client/change-password",
        component: () => import("../components/Client/DoiPassword/index.vue"),
        meta: { layout: "client" },
    },
    {
        path: "/client/forgot-password",
        component: () => import("../components/Client/QuenMatKhau/index.vue"),
    },
    {
        path: "/client/history",
        component: () => import("../components/Client/LichSuYeuCau/index.vue"),
        meta: { layout: "client" },
    },
    {
        path: "/safety",
        component: () => import("../components/Client/ThongTinAnToan/index.vue"),
        meta: { layout: "client" },
    },
    {
        path: "/contact",
        component: () => import("../components/Client/LienHe/index.vue"),
        meta: { layout: "client" },
    },

    // admin 
    {
        path: "/admin/login",
        component: () => import("../components/Admin/DangNhap/index.vue"),
    },
    {
        path: "/admin",
        component: () => import("../components/Admin/DashBoard/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/queue",
        component: () => import("../components/Admin/Queue/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/theo-doi-cuu-ho",
        component: () => import("../components/Admin/TheoDoiYeuCau/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/dang-xu-ly",
        component: () => import("../components/Admin/DangXuLy/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/da-hoan-thanh",
        component: () => import("../components/Admin/DaHoanThanh/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/assignments",
        component: () => import("../components/Admin/Assignments/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/heatmap",
        component: () => import("../components/Admin/Heatmap/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/tracking",
        component: () => import("../components/Admin/Tracking/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/reports",
        component: () => import("../components/Admin/Reports/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/realtime-dispatch",
        component: () => import("../components/Admin/RealtimeDispatch/index.vue"),
        meta: { layout: "admin", roles: ["admin", "manager_operator"] },
    },
    {
        path: "/admin/tai-nguyen",
        component: () => import("../components/Admin/TaiNguyen/index.vue"),
        meta: { layout: "admin", roles: ["admin", "manager_operator"] },
    },
    {
        path: "/admin/doi-cuu-ho",
        component: () => import("../components/Admin/DoiCuuHo/index.vue"),
        meta: { layout: "admin", roles: ["admin", "manager_operator"] },
    },
    
    {
        path: "/admin/accounts/admin",
        component: () => import("../components/Admin/Accounts/Admin/index.vue"),
        meta: { layout: "admin", roles: ["admin"] },
    },
    {
        path: "/admin/accounts/user",
        component: () => import("../components/Admin/Accounts/User/index.vue"),
        meta: { layout: "admin", roles: ["admin"] },
    },
    {
        path: "/admin/accounts/rescuer",
        component: () => import("../components/Admin/Accounts/Rescuer/index.vue"),
        meta: { layout: "admin", roles: ["admin"] },
    },
    {
        path: "/admin/change-password",
        component: () => import("../components/Admin/DoiPassword/index.vue"),
        meta: { layout: "admin" },
    },

    // 
    { path: "/dashboard", redirect: "/admin" },
    { path: "/inventory", redirect: "/admin/resources" },
    { path: "/add-product", redirect: "/admin/resources" },
    { path: "/reports", redirect: "/admin/reports" },

    // rescuer
    {
        path: "/rescuer/login",
        component: () => import("../components/Rescuer/DangNhap/index.vue"),
    },
    {
        path: "/rescuer/home",
        component: () => import("../components/Rescuer/TrangChu/index.vue"),
        meta: { layout: "rescuer" },
    },
    {
        path: "/rescuer/dang-xu-ly",
        component: () => import("../components/Rescuer/DangXuLy/index.vue"),
        meta: { layout: "rescuer" },
    },
    {
        path: "/rescuer/da-xu-ly",
        component: () => import("../components/Rescuer/DaXuLy/index.vue"),
        meta: { layout: "rescuer" },
    },
    {
        path: "/rescuer/thong-bao-nhiem-vu",
        component: () => import("../components/Rescuer/ThongBaoNhiemVu/index.vue"),
        meta: { layout: "rescuer" },
    },
    {
        path: "/rescuer/tai-nguyen",
        component: () => import("../components/Rescuer/TaiNguyen/index.vue"),
        meta: { layout: "rescuer" },
    },
    {
        path: "/rescuer/heatmap",
        component: () => import("../components/Rescuer/HeatMap/index.vue"),
        meta: { layout: "rescuer" },
    },
    {
        path: "/rescuer/reports",
        component: () => import("../components/Rescuer/Report/index.vue"),
        meta: { layout: "rescuer" },
    },
    {
        path: "/rescuer/quan-ly-thanh-vien",
        component: () => import("../components/Rescuer/QuanLy/index.vue"),
        meta: { layout: "rescuer", roles: ["rescuer_non_member"] },
    },
    {
        path: "/rescuer/change-password",
        component: () => import("../components/Rescuer/DoiPassword/index.vue"),
        meta: { layout: "rescuer" },
    },

];

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
});

router.beforeEach((to, from, next) => {
    if (to.path.startsWith("/admin") && to.path !== "/admin/login") {
        return fetchAdminUserAndProceed(to, from, next);
    }
    if (to.path.startsWith("/rescuer") && to.path !== "/rescuer/login") {
        return fetchRescuerUserAndProceed(to, from, next);
    }
    const clientProtected = ["/client/profile", "/client/change-password", "/client/history", "/client/dang-xu-ly", "/client/requests"];
    if (clientProtected.some(p => to.path === p)) {
        return checkClient(to, from, next);
    }
    // Prevent logged-in admin from accessing admin login page
    if (to.path === "/admin/login") {
        const token = localStorage.getItem("admin_token");
        if (token) {
            next("/admin/");
            return;
        }
    }
    // Prevent logged-in users from accessing login page
    if (to.path === "/client/login") {
        const token = localStorage.getItem("token") || localStorage.getItem("user_token");
        if (token) {
            next("/");
            return;
        }
    }
    // Prevent logged-in rescuer from accessing rescuer login page
    if (to.path === "/rescuer/login") {
        const token = localStorage.getItem("rescuer_token");
        if (token) {
            next("/rescuer/home");
            return;
        }
    }
    next();
});

export default router;
