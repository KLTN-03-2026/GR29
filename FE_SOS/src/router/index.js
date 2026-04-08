import { createRouter, createWebHistory } from "vue-router";

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
        path: "/admin/incident-types",
        component: () => import("../components/Admin/IncidentTypes/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/resources",
        component: () => import("../components/Admin/Resources/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/resource-requests",
        component: () => import("../components/Admin/ResourceRequests/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/ai-scoring",
        component: () => import("../components/Admin/AIScoring/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/accounts/admin",
        component: () => import("../components/Admin/Accounts/Admin/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/accounts/user",
        component: () => import("../components/Admin/Accounts/User/index.vue"),
        meta: { layout: "admin" },
    },
    {
        path: "/admin/accounts/rescuer",
        component: () => import("../components/Admin/Accounts/Rescuer/index.vue"),
        meta: { layout: "admin" },
    },

    // 
    { path: "/dashboard", redirect: "/admin" },
    { path: "/inventory", redirect: "/admin/resources" },
    { path: "/add-product", redirect: "/admin/resources" },
    { path: "/reports", redirect: "/admin/reports" },

    // rescuer
    {
        path: "/rescuser/login",
        component: () => import("../components/Rescuer/DangNhap/index.vue"),
    },
    {
        path: "/rescuer/home",
        component: () => import("../components/Rescuer/TrangChu/index.vue"),
        meta: { layout: "rescuer" },
    },
    {
        path: "/rescuer/bao-cao",
        component: () => import("../components/Rescuer/Baocao/index.vue"),
        meta: { layout: "rescuer" },
    },
    // đã chuyển sang modal tại /rescuer/home (giữ route cũ nếu cần dùng lại)
];

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
});

export default router;
