<template>
    <div>
        <nav class="navbar navbar-expand-lg navbar-dark py-0 px-4 now-navbar">
        <div class="container-fluid">
            <!-- Brand -->
            <router-link class="navbar-brand d-flex align-items-center fw-bold now-brand" to="/">
                <span class="now-brand__mark me-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <span class="now-brand__text">
                    <span class="now-brand__now">now</span><span class="now-brand__sos">SOS</span>
                </span>
            </router-link>

            <!-- Right wrapper: luôn nằm cùng hàng với brand, nội dung ẩn/hiện theo breakpoint -->
            <div class="d-flex align-items-center ms-auto gap-2">
                <div v-if="isLoggedIn" class="d-flex d-lg-none align-items-center now-mobile-topbar-user">
                    <img class="now-mobile-topbar-avatar" :src="avatarSrc" alt="avatar" />
                    <span class="now-mobile-topbar-name ms-2">{{ displayName }}</span>
                </div>
                <!-- Bootstrap tự ẩn toggler trên lg+ nhờ .navbar-expand-lg CSS -->
                <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#nowSosNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="nowSosNav">
                <!-- Nav links -->
                <ul class="navbar-nav me-auto mb-0 now-nav">
                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Gửi yêu cầu cứu hộ
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/client/requests">
                            <i class="fa-regular fa-paper-plane me-1"></i>Yêu cầu đã gửi
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/client/dang-xu-ly">
                            <i class="fa-solid fa-spinner me-1"></i>Yêu cầu đang xử lý
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/client/history">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Lịch sử
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/safety">
                            <i class="fa-solid fa-shield-halved me-1"></i>An toàn
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link class="nav-link now-nav__link" to="/contact">
                            <i class="fa-solid fa-envelope me-1"></i>Liên hệ
                        </router-link>
                    </li>
                </ul>

                <!-- Right side: chỉ hiện trên desktop (lg+) -->
                <div class="d-none d-lg-flex align-items-center gap-2">
                    <template v-if="!isLoggedIn">
                        <router-link to="/client/login"
                            class="btn now-btn-login btn-sm rounded-pill px-3 fw-semibold">Đăng nhập</router-link>
                        <router-link to="/client/register"
                            class="btn now-btn-register btn-sm rounded-pill px-3 fw-semibold">Đăng ký</router-link>
                    </template>
                    <template v-else>
                        <!-- Desktop dropdown -->
                        <div class="dropdown" ref="dropdownRef">
                            <button class="btn now-avatar-btn d-flex align-items-center gap-2" type="button"
                                @click="toggleDropdown">
                                <span class="now-username">{{ displayName }}</span>
                                <img class="now-avatar" :src="avatarSrc" alt="User avatar" />
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end now-user-menu shadow-lg"
                                :class="{ 'show': isDropdownOpen }">
                                <li class="px-3 pt-3 pb-2">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <img class="now-avatar" :src="avatarSrc" alt="avatar" style="width:36px;height:36px;" />
                                        <div>
                                            <div class="fw-semibold text-dark small">{{ displayName }}</div>
                                            <div class="text-muted" style="font-size:0.7rem;">Tài khoản khách</div>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider my-1" /></li>
                                <li>
                                    <router-link class="dropdown-item now-menu-item" to="/client/profile" @click="closeDropdown">
                                        <i class="fa-solid fa-user me-2"></i>Hồ sơ cá nhân
                                    </router-link>
                                </li>
                                <li>
                                    <router-link class="dropdown-item now-menu-item" to="/client/change-password" @click="closeDropdown">
                                        <i class="fa-solid fa-key me-2"></i>Đổi mật khẩu
                                    </router-link>
                                </li>
                                <li><hr class="dropdown-divider my-1" /></li>
                                <li>
                                    <button class="dropdown-item now-menu-item now-menu-item--danger" type="button"
                                        data-bs-toggle="modal" data-bs-target="#logoutModal" @click="closeDropdown">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </template>
                </div>

                <!-- Mobile: nút đăng nhập / đăng ký (khi chưa đăng nhập) -->
                <div v-if="!isLoggedIn" class="d-flex d-lg-none flex-column gap-2 py-3 now-mobile-auth-btns">
                    <router-link to="/client/login"
                        class="btn now-btn-login btn-sm rounded-pill px-3 fw-semibold w-100 text-center">Đăng nhập</router-link>
                    <router-link to="/client/register"
                        class="btn now-btn-register btn-sm rounded-pill px-3 fw-semibold w-100 text-center">Đăng ký</router-link>
                </div>

                <!-- Mobile: User panel (khi đã đăng nhập) -->
                <div v-if="isLoggedIn" class="d-lg-none now-mobile-user-panel">
                    <div class="now-mobile-panel-divider"></div>
                    <div class="now-mobile-user-card">
                        <img class="now-mobile-user-avatar" :src="avatarSrc" alt="avatar" />
                        <div class="now-mobile-user-info">
                            <div class="now-mobile-user-name">{{ displayName }}</div>
                            <div class="now-mobile-user-role">Tài khoản khách</div>
                        </div>
                    </div>
                    <div class="now-mobile-user-actions">
                        <router-link class="now-mobile-action-item" to="/client/profile" @click="closeMobileMenu">
                            <span class="now-mobile-action-icon"><i class="fa-solid fa-user"></i></span>
                            <span>Hồ sơ cá nhân</span>
                        </router-link>
                        <router-link class="now-mobile-action-item" to="/client/change-password" @click="closeMobileMenu">
                            <span class="now-mobile-action-icon"><i class="fa-solid fa-key"></i></span>
                            <span>Đổi mật khẩu</span>
                        </router-link>
                        <button class="now-mobile-action-item now-mobile-action-danger" type="button"
                            data-bs-toggle="modal" data-bs-target="#logoutModal" @click="closeMobileMenu">
                            <span class="now-mobile-action-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                            <span>Đăng xuất</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Modal xác nhận đăng xuất -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title w-100 text-center fw-bold text-dark" id="logoutModalLabel">
                        <i class="fa-solid fa-circle-question text-warning me-2"></i>
                        Xác nhận đăng xuất
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-right-from-bracket fa-3x text-danger mb-3"></i>
                    </div>
                    <h6 class="fw-semibold text-dark mb-2">Bạn muốn đăng xuất?</h6>
                    <p class="text-muted mb-0 small">Bạn sẽ cần đăng nhập lại để tiếp tục sử dụng dịch vụ.</p>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <div class="d-flex w-100 gap-2 px-3">
                        <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-1"></i> Hủy
                        </button>
                        <button type="button" class="btn btn-danger w-100" @click="logout">
                            <i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
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

// Đóng mobile collapse menu
function closeMobileMenu() {
    const collapse = document.getElementById("nowSosNav");
    if (collapse && collapse.classList.contains("show")) {
        const bsCollapse = bootstrap.Collapse.getInstance(collapse);
        if (bsCollapse) bsCollapse.hide();
    }
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
    // Đóng modal nếu đang mở
    const modal = document.getElementById('logoutModal');
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    }
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    localStorage.removeItem("client");
    router.push("/client/login");
}
</script>

<style scoped>
/* ─── Navbar shell ─────────────────────────────────────── */
.now-navbar {
    background: linear-gradient(90deg, #1d5278 0%, #225f8a 50%, #1e5680 100%);
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1050;
    min-height: 56px;
}

/* ─── Brand ────────────────────────────────────────────── */
.now-brand {
    letter-spacing: 0.3px;
    text-decoration: none;
}

.now-brand__mark {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: rgba(255, 255, 255, 0.18);
    color: #ffca28;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.now-brand__text {
    font-size: 1.2rem;
    font-weight: 800;
    line-height: 1;
}

.now-brand__now {
    color: #ffffff;
    letter-spacing: -0.3px;
}

.now-brand__sos {
    color: #ff6b35;
    letter-spacing: -0.3px;
}

/* ─── Nav links ────────────────────────────────────────── */
.now-nav {
    gap: 2px;
}

.now-nav__link {
    position: relative;
    color: rgba(255, 255, 255, 0.80);
    padding: 16px 13px;
    font-size: 0.78rem;
    font-weight: 500;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    border-radius: 0;
    transition: color 160ms ease, background-color 160ms ease;
    white-space: nowrap;
}

.now-nav__link:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.08);
}

/* active underline pill */
.now-nav__link::after {
    content: "";
    position: absolute;
    left: 12px;
    right: 12px;
    bottom: 0;
    height: 3px;
    border-radius: 4px 4px 0 0;
    background: #ffca28;
    transform: scaleX(0);
    transform-origin: center;
    transition: transform 200ms ease;
}

.now-nav__link.router-link-active,
.now-nav__link.router-link-exact-active {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.10);
}

.now-nav__link.router-link-active::after,
.now-nav__link.router-link-exact-active::after {
    transform: scaleX(1);
}

/* Mobile nav links - dễ tap hơn */
@media (max-width: 991.98px) {
    .now-nav__link {
        padding: 12px 16px;
        font-size: 0.82rem;
        border-radius: 8px;
        margin: 2px 0;
    }
    .now-nav__link::after {
        display: none;
    }
    .now-nav__link.router-link-active,
    .now-nav__link.router-link-exact-active {
        background: rgba(255, 255, 255, 0.15);
        border-left: 3px solid #ffca28;
        padding-left: 13px;
    }
}

/* ─── Status pill ──────────────────────────────────────── */
.now-status-pill {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.90);
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 999px;
    padding: 4px 12px;
    font-size: 0.67rem;
    font-weight: 600;
    letter-spacing: 0.6px;
    white-space: nowrap;
}

.now-dot {
    font-size: 0.42rem;
    color: #4ade80;
    vertical-align: middle;
}

/* ─── Auth buttons ─────────────────────────────────────── */
.now-btn-login {
    color: #ffffff;
    border: 1.5px solid rgba(255, 255, 255, 0.55);
    background: transparent;
    transition: background 160ms ease, border-color 160ms ease;
}

.now-btn-login:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.85);
    color: #ffffff;
}

.now-btn-register {
    background: #ff6b35;
    color: #ffffff;
    border: none;
    transition: background 160ms ease, transform 160ms ease;
}

.now-btn-register:hover {
    background: #e55b28;
    color: #ffffff;
    transform: translateY(-1px);
}

/* ─── Mobile Auth Buttons ──────────────────────────────── */
.now-mobile-auth-btns {
    padding: 12px 0 8px;
    border-top: 1px solid rgba(255,255,255,0.12);
    margin-top: 4px;
}

.now-mobile-status-row {
    margin-bottom: 4px;
}

/* ─── Mobile Topbar User (bên cạnh toggler) ────────────── */
.now-mobile-topbar-user {
    gap: 0;
    max-width: calc(100vw - 160px);
    overflow: hidden;
}

.now-mobile-topbar-avatar {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    border: 2px solid rgba(255,255,255,0.45);
    object-fit: cover;
    flex-shrink: 0;
}

.now-mobile-topbar-name {
    color: #ffffff;
    font-size: 0.78rem;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 120px;
}

/* ─── Avatar button (desktop) ──────────────────────────── */
.now-avatar-btn {
    background: rgba(255, 255, 255, 0.13);
    border: 1.5px solid rgba(255, 255, 255, 0.30);
    border-radius: 999px;
    padding: 5px 10px 5px 5px;
    transition: background 160ms ease, border-color 160ms ease;
}

.now-avatar-btn:hover {
    background: rgba(255, 255, 255, 0.20);
    border-color: rgba(255, 255, 255, 0.55);
}

.now-avatar-btn:focus {
    box-shadow: 0 0 0 3px rgba(255, 202, 40, 0.30);
    outline: none;
}

.now-username {
    color: #ffffff;
    font-size: 0.82rem;
    font-weight: 600;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.now-avatar {
    width: 30px;
    height: 30px;
    border-radius: 999px;
    display: block;
    border: 2px solid rgba(255, 255, 255, 0.40);
    object-fit: cover;
}

/* ─── Dropdown menu (desktop) ──────────────────────────── */
.now-user-menu {
    min-width: 230px;
    border-radius: 14px;
    border: 1px solid rgba(0, 0, 0, 0.07);
    overflow: hidden;
    position: absolute !important;
    top: 100%;
    right: 0;
    margin-top: 8px;
    z-index: 1060;
    box-shadow: 0 12px 32px rgba(0,0,0,0.14);
}

.now-menu-item {
    padding: 9px 18px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
    display: flex;
    align-items: center;
    transition: background 140ms ease, color 140ms ease;
}

.now-menu-item:hover {
    background: #f0fdfd;
    color: #0a7a7a;
}

.now-menu-item--danger {
    color: #dc2626 !important;
    width: 100%;
    text-align: left;
    background: none;
    border: none;
}

.now-menu-item--danger:hover {
    background: #fff5f5 !important;
    color: #b91c1c !important;
}

/* ─── Mobile User Panel ────────────────────────────────── */
.now-mobile-user-panel {
    padding-bottom: 12px;
}

.now-mobile-panel-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.15);
    margin: 8px 0 12px;
}

.now-mobile-user-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    margin-bottom: 8px;
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.now-mobile-user-avatar {
    width: 42px;
    height: 42px;
    border-radius: 999px;
    border: 2px solid rgba(255, 255, 255, 0.45);
    object-fit: cover;
    flex-shrink: 0;
}

.now-mobile-user-info {
    overflow: hidden;
}

.now-mobile-user-name {
    color: #ffffff;
    font-size: 0.9rem;
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.now-mobile-user-role {
    color: rgba(255, 255, 255, 0.60);
    font-size: 0.72rem;
    margin-top: 1px;
}

.now-mobile-user-actions {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.now-mobile-action-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 10px;
    color: rgba(255, 255, 255, 0.88);
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    transition: background 140ms ease, color 140ms ease;
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.now-mobile-action-item:hover,
.now-mobile-action-item:active {
    background: rgba(255, 255, 255, 0.10);
    color: #ffffff;
}

.now-mobile-action-icon {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.12);
    font-size: 0.82rem;
    flex-shrink: 0;
}

.now-mobile-action-danger {
    color: #fca5a5 !important;
}

.now-mobile-action-danger:hover,
.now-mobile-action-danger:active {
    background: rgba(220, 38, 38, 0.15) !important;
    color: #fca5a5 !important;
}

.now-mobile-action-danger .now-mobile-action-icon {
    background: rgba(220, 38, 38, 0.18);
    color: #fca5a5;
}

/* ─── Logout modal ─────────────────────────────────────── */
#logoutModal .modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

#logoutModal .modal-header {
    border-radius: 16px 16px 0 0;
    padding: 1.5rem 1.5rem 1rem;
}

#logoutModal .modal-body {
    padding: 1.5rem;
}

#logoutModal .modal-footer {
    border-radius: 0 0 16px 16px;
    padding: 1rem 1.5rem 1.5rem;
}

#logoutModal .btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

#logoutModal .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#logoutModal .fa-3x {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* ─── MOBILE NAVBAR FIX: giữ brand + right wrapper trên cùng 1 hàng ────── */
@media (max-width: 991.98px) {
    /* Ngăn không cho flex wrap xuống dòng */
    .now-navbar > .container-fluid {
        flex-wrap: nowrap !important;
        align-items: center;
    }

    /* Collapse menu nằm dưới navbar theo kiểu absolute — không làm xáo trộn flex */
    .now-navbar .navbar-collapse {
        position: absolute !important;
        top: 56px;
        left: 0;
        right: 0;
        z-index: 1049;
        background: linear-gradient(180deg, #1a4e72 0%, #205a84 100%);
        padding: 8px 16px 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        border-top: 1px solid rgba(255, 255, 255, 0.10);
        max-height: calc(100vh - 56px);
        overflow-y: auto;
    }

    /* Brand không giãn ra toàn bộ độ rộng */
    .now-navbar .navbar-brand {
        flex-shrink: 0;
    }
}

</style>
