<template>
    <div class="admin-menu d-flex flex-column h-100">
        <!-- Thông tin thành viên -->
        <div class="px-3 pt-3 pb-2">
            <div class="d-flex align-items-center mb-2">
                <div class="brand-icon me-2">
                    <i class="fa-solid fa-kit-medical"></i>
                </div>
                <div>
                    <div class="fw-semibold small text-white">nowSOS</div>
                    <div class="text-secondary-emphasis text-uppercase" style="font-size: 10px;">Đội cứu hộ</div>
                </div>
            </div>

            <!-- Khu vực thành viên -->
            <div class="member-zone p-2 rounded-3 mb-2" v-if="isLoggedIn">
                <div class="d-flex align-items-center mb-2">
                    <img :src="avatarSrc" class="rounded-circle me-2" width="36" height="36" :alt="rescuerName">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-white small fw-semibold text-truncate">{{ rescuerName }}</div>
                        <div class="text-secondary small" style="font-size: 10px;">
                            <i class="fa-solid fa-star text-warning me-1"></i>Thành viên cứu hộ
                        </div>
                    </div>
                </div>
                <div class="text-secondary small" v-if="teamName" style="font-size: 10px;">
                    <i class="fa-solid fa-users text-info me-1"></i>{{ teamName }}
                </div>
            </div>

            <!-- Chưa đăng nhập -->
            <div class="text-center p-2" v-else>
                <router-link to="/rescuer/login" class="btn btn-sm btn-outline-light w-100 rounded-pill">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Đăng nhập
                </router-link>
            </div>
        </div>

        <hr class="border-secondary border-opacity-25 my-2" />

        <nav class="flex-grow-1 px-2 pb-3 overflow-auto">
            <div class="text-uppercase text-secondary-emphasis fw-semibold small px-2 mb-2">Tổng quan</div>
            <router-link v-slot="{ href, navigate, isExactActive }" to="/rescuer/home" custom>
                <a :href="href" class="nav-item-link" :class="{ 'nav-item-link--active': isExactActive }"
                    @click="(e) => { e.preventDefault(); navigate(); }">
                    <i class="fa-solid fa-house me-2"></i>Trang chủ
                </a>
            </router-link>
            <router-link class="nav-item-link" to="/rescuer/dang-xu-ly">
                <i class="fa-solid fa-spinner me-2"></i>Đang Xử Lý
            </router-link>
            <router-link class="nav-item-link" to="/rescuer/da-xu-ly">
                <i class="fa-solid fa-circle-check me-2"></i>Đã Xử Lý
            </router-link>
            <router-link class="nav-item-link" to="/rescuer/reports">
                <i class="fa-solid fa-chart-line me-2"></i>Báo Cáo
            </router-link>

            <div class="text-uppercase text-secondary-emphasis fw-semibold small px-2 mt-3 mb-2">Tài nguyên</div>
            <router-link class="nav-item-link" to="/rescuer/tai-nguyen">
                <i class="fa-solid fa-helmet-safety me-2"></i>Tài Nguyên & Trang Thiết Bị
            </router-link>

            <div class="text-uppercase text-secondary-emphasis fw-semibold small px-2 mt-3 mb-2">Quản Lý</div>
            <router-link class="nav-item-link" to="/rescuer/quan-ly-thanh-vien">
                <i class="fa-solid fa-shield-halved me-2"></i>Thành Viên
            </router-link>

            <div class="mt-auto pt-3 border-top border-secondary border-opacity-25 px-2">
                <a href="#" class="nav-item-link text-danger" @click.prevent="logout" v-if="isLoggedIn">
                    <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                </a>
                <router-link to="/rescuer/login" class="nav-item-link text-warning" v-else>
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Đăng nhập
                </router-link>
            </div>
        </nav>
    </div>
</template>

<script>
export default {
    name: "MenuRescuer",
    data() {
        return {
            avatarColors: ['1abc9c', '2ecc71', '3498db', '9b59b6', 'f39c12', 'e74c3c', '16a085', '27ae60'],
        };
    },
    computed: {
        isLoggedIn() {
            return !!localStorage.getItem("rescuer_token");
        },
        rescuerName() {
            try {
                const raw = localStorage.getItem("rescuer_user");
                if (!raw) return localStorage.getItem("rescuer_name") || "Thành viên";
                const u = JSON.parse(raw);
                return u.ho_ten || u.name || localStorage.getItem("rescuer_name") || "Thành viên";
            } catch {
                return localStorage.getItem("rescuer_name") || "Thành viên";
            }
        },
        teamName() {
            try {
                const raw = localStorage.getItem("rescuer_team");
                if (!raw) return "";
                const team = JSON.parse(raw);
                return team.ten_doi || team.name || team.ma_doi || "";
            } catch {
                return "";
            }
        },
        avatarSrc() {
            const name = encodeURIComponent(this.rescuerName);
            const color = this.avatarColors[this.rescuerName.length % this.avatarColors.length];
            return `https://ui-avatars.com/api/?name=${name}&background=${color}&color=fff&bold=true`;
        },
    },
    methods: {
        logout() {
            localStorage.removeItem("rescuer_token");
            localStorage.removeItem("rescuer_user");
            localStorage.removeItem("rescuer_team");
            localStorage.removeItem("rescuer_name");
            this.$router.push("/rescuer/login");
        },
    },
};
</script>

<style scoped>
.admin-menu {
    background: radial-gradient(circle at top, #111827 0, #020617 48%, #020617 100%);
    height: 100%;
}

.brand-icon {
    width: 30px;
    height: 30px;
    border-radius: 10px;
    background: rgba(248, 250, 252, 0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ef4444;
    font-size: 14px;
}

.member-zone {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-item-link {
    display: flex;
    align-items: center;
    padding: 0.45rem 0.75rem;
    border-radius: 0.6rem;
    margin-bottom: 0.1rem;
    color: #9ca3af;
    text-decoration: none;
    font-size: 0.85rem;
    transition: background-color 0.16s ease, color 0.16s ease;
}

.nav-item-link:hover {
    background-color: rgba(55, 65, 81, 0.65);
    color: #e5e7eb;
}

.nav-item-link.router-link-active,
.nav-item-link.router-link-exact-active,
.nav-item-link.nav-item-link--active {
    background: linear-gradient(90deg, #ef4444, #f87171);
    color: #ffffff;
    font-weight: 600;
}
</style>
