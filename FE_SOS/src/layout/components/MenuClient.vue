<template>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-2 px-4 now-navbar">
        <div class="container-fluid">
            <router-link class="navbar-brand d-flex align-items-center fw-bold now-brand" to="/">
                <span class="now-brand__mark me-2">
                    <i class="fa-solid fa-location-dot"></i>
                </span>
                nowSOS
            </router-link>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nowSosNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nowSosNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 small text-uppercase now-nav">
                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/">
                            <i class="fa-solid fa-bullhorn me-2"></i>Gửi yêu cầu cứu hộ
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/client/requests">
                            <i class="fa-solid fa-bullhorn me-2"></i>Yêu cầu đã gửi
                        </router-link>
                    </li>

                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/client/history">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử yêu cầu
                        </router-link>
                    </li>

                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/safety">
                            <i class="fa-solid fa-shield-halved me-2"></i>Thông tin an toàn
                        </router-link>
                    </li>

                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/contact">
                            <i class="fa-solid fa-headset me-2"></i>Liên hệ
                        </router-link>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <template v-if="!isLoggedIn">
                        <div class="d-none d-md-flex align-items-center me-3 border-end border-light border-opacity-25 pe-3">
                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 me-3" style="font-size: 0.7rem;">
                                <i class="fa-solid fa-circle me-2 now-dot"></i> <p class="text-dark">HỆ THỐNG: ĐANG HOẠT ĐỘNG</p>
                            </span>
                        </div>
                        <div class="d-flex">
                            <router-link to="/client/login" class="btn btn-outline-light btn-sm me-2 rounded-pill px-3 fw-semibold">Đăng nhập</router-link>
                            <router-link to="/client/register" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-semibold">Đăng ký</router-link>
                        </div>
                    </template>
                    <template v-else>
                        <div class="dropdown" ref="dropdownRef">
                            <button class="btn now-avatar-btn dropdown-toggle d-flex align-items-center" type="button" @click="toggleDropdown">
                                <span class="text-white me-2 d-none d-md-inline-block fw-semibold small">{{ displayName }}</span>
                                <img class="now-avatar" :src="avatarSrc" alt="User avatar" />
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end now-user-menu shadow" :class="{ 'show': isDropdownOpen }">
                                <li class="px-3 py-2">
                                    <div class="fw-semibold text-dark small">{{ displayName }}</div>
                                    <div class="text-muted small">Tài khoản khách</div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider my-1" />
                                </li>
                                <li>
                                    <router-link class="dropdown-item" to="/client/profile" @click="closeDropdown">
                                        <i class="fa-solid fa-user me-2"></i>Profile
                                    </router-link>
                                </li>
                                <li>
                                    <router-link class="dropdown-item" to="/client/change-password" @click="closeDropdown">
                                        <i class="fa-solid fa-key me-2"></i>Đổi mật khẩu
                                    </router-link>
                                </li>
                                <!-- <li>
                                    <router-link class="dropdown-item" to="/client/profile" @click="closeDropdown">
                                        <i class="fa-solid fa-pen-to-square me-2"></i>Cập nhật thông tin
                                    </router-link>
                                </li> -->
                                <!-- <li>
                                    <router-link class="dropdown-item" to="/client/history" @click="closeDropdown">
                                        <i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử yêu cầu
                                    </router-link>
                                </li> -->
                                <li>
                                    <hr class="dropdown-divider my-1" />
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger" type="button" @click="logout">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import avatarSrc from "../../assets/images/avatar-default.svg";

const router = useRouter();
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

const isLoggedIn = computed(() => {
    return !!localStorage.getItem("token") || !!localStorage.getItem("user") || !!localStorage.getItem("client");
});

const displayName = computed(() => {
    try {
        const raw = localStorage.getItem("user") || localStorage.getItem("client") || "";
        if (!raw) return "Khách";
        const parsed = JSON.parse(raw);
        return (
            parsed?.ho_ten ||
            parsed?.name ||
            parsed?.fullName ||
            parsed?.username ||
            parsed?.email ||
            "Khách"
        );
    } catch {
        return "Khách";
    }
});

function toggleDropdown() {
    isDropdownOpen.value = !isDropdownOpen.value;
}

function closeDropdown() {
    isDropdownOpen.value = false;
}

function handleClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});

function logout() {
    closeDropdown();
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    localStorage.removeItem("client");
    router.push("/client/login");
}
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.now-navbar {
    background: linear-gradient(135deg, #15253b 0%, #1a2a40 40%, #16253a 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    position: relative;
    z-index: 1;
    width: 100%;
}

.now-brand {
    letter-spacing: 0.2px;
}

.now-brand__mark {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.12);
    color: #ff9800;
}

.now-nav {
    gap: 6px;
}

.now-nav__link {
    position: relative;
    color: rgba(255, 255, 255, 0.65);
    padding: 10px 12px;
    border-radius: 12px;
    transition: color 180ms ease, background-color 180ms ease;
}

.now-nav__link:hover {
    color: rgba(255, 255, 255, 0.92);
    background: rgba(255, 255, 255, 0.06);
}

.now-nav__link::after {
    content: "";
    position: absolute;
    left: 12px;
    right: 12px;
    bottom: 3px;
    height: 3px;
    border-radius: 999px;
    background: #ff9800;
    transform: scaleX(0);
    transform-origin: left center;
    transition: transform 220ms ease;
    opacity: 0.95;
}

.now-nav__link.router-link-active,
.now-nav__link.router-link-exact-active {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.08);
}

.now-nav__link.router-link-active::after,
.now-nav__link.router-link-exact-active::after {
    transform: scaleX(1);
}

.now-dot {
    font-size: 0.5rem;
}

.now-avatar-btn {
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.08);
    padding: 6px;
    border-radius: 999px;
    line-height: 1;
}

.now-avatar-btn:focus {
    box-shadow: 0 0 0 0.25rem rgba(255, 152, 0, 0.18);
}

.now-avatar-btn::after {
    display: none;
}

.now-avatar {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    display: block;
}

.now-user-menu {
    min-width: 240px;
    border-radius: 14px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    overflow: hidden;
    position: absolute !important;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    z-index: 1060; /* Ensure dropdown displays above everything */
}
</style>
