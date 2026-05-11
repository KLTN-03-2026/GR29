<template>
    <Teleport to="body">
        <Transition name="auth-req-fade">
            <div
                v-if="open"
                class="auth-req-root"
                role="dialog"
                aria-modal="true"
                aria-labelledby="authReqTitle"
            >
                <div class="auth-req-backdrop" @click="close" />
                <div class="auth-req-dialog">
                    <button type="button" class="auth-req-close" aria-label="Đóng" @click="close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <div class="auth-req-icon-wrap">
                        <i class="fa-solid fa-user-lock auth-req-icon"></i>
                    </div>
                    <h2 id="authReqTitle" class="auth-req-title">Bạn đã có tài khoản chưa?</h2>
                    <p class="auth-req-text">
                        Chức năng này dành cho người dùng đã đăng nhập. Bạn có thể đăng nhập nếu đã có tài khoản,
                        hoặc đăng ký tài khoản mới để theo dõi yêu cầu cứu hộ.
                    </p>
                    <div class="auth-req-actions">
                        <button type="button" class="btn-auth btn-auth--outline" @click="goRegister">
                            <i class="fa-solid fa-user-plus me-2" />Đăng ký
                        </button>
                        <button type="button" class="btn-auth btn-auth--primary" @click="goLogin">
                            <i class="fa-solid fa-right-to-bracket me-2" />Đăng nhập
                        </button>
                    </div>
                    <button type="button" class="auth-req-later" @click="close">Để sau</button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import { useRouter } from "vue-router";
import { CLIENT_AUTH_REQUIRED_EVENT } from "../../utils/clientAuthPrompt";
import { getSafeClientRedirect } from "../../utils/safeClientRedirect";

const router = useRouter();
const open = ref(false);
const pendingRedirect = ref("/");

function onRequired(e) {
    const path = e?.detail?.redirectPath || "/";
    pendingRedirect.value = path;
    open.value = true;
}

onMounted(() => {
    window.addEventListener(CLIENT_AUTH_REQUIRED_EVENT, onRequired);
});

onUnmounted(() => {
    window.removeEventListener(CLIENT_AUTH_REQUIRED_EVENT, onRequired);
});

function close() {
    open.value = false;
}

function redirectQuery() {
    const safe = getSafeClientRedirect(pendingRedirect.value);
    if (!safe) return {};
    return { redirect: safe };
}

function goLogin() {
    const q = redirectQuery();
    close();
    router.push({ path: "/client/login", query: q });
}

function goRegister() {
    const q = redirectQuery();
    close();
    router.push({ path: "/client/register", query: q });
}
</script>

<style scoped>
.auth-req-root {
    position: fixed;
    inset: 0;
    z-index: 20000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.auth-req-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(4px);
}

.auth-req-dialog {
    position: relative;
    width: 100%;
    max-width: 420px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
    padding: 1.75rem 1.5rem 1.25rem;
    text-align: center;
}

.auth-req-close {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 10px;
    background: #f1f5f9;
    color: #64748b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease, color 0.15s ease;
}

.auth-req-close:hover {
    background: #e2e8f0;
    color: #334155;
}

.auth-req-icon-wrap {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    border-radius: 16px;
    background: linear-gradient(135deg, #e0f2fe 0%, #cffafe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-req-icon {
    font-size: 1.75rem;
    color: #0e7490;
}

.auth-req-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.65rem;
    line-height: 1.35;
}

.auth-req-text {
    font-size: 0.9rem;
    color: #64748b;
    margin: 0 0 1.35rem;
    line-height: 1.55;
}

.auth-req-actions {
    display: flex;
    flex-direction: column-reverse;
    gap: 0.65rem;
}

@media (min-width: 400px) {
    .auth-req-actions {
        flex-direction: row;
        justify-content: center;
    }
}

.btn-auth {
    flex: 1;
    min-height: 46px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: transform 0.12s ease, box-shadow 0.12s ease;
}

.btn-auth:active {
    transform: scale(0.98);
}

.btn-auth--primary {
    background: linear-gradient(90deg, #1d5278 0%, #225f8a 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(29, 82, 120, 0.35);
}

.btn-auth--primary:hover {
    box-shadow: 0 6px 20px rgba(29, 82, 120, 0.4);
}

.btn-auth--outline {
    background: #fff;
    color: #1d5278;
    border: 2px solid rgba(29, 82, 120, 0.35);
}

.btn-auth--outline:hover {
    background: #f8fafc;
}

.auth-req-later {
    margin-top: 1rem;
    padding: 0.35rem 0.5rem;
    border: none;
    background: none;
    color: #94a3b8;
    font-size: 0.85rem;
    cursor: pointer;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.auth-req-later:hover {
    color: #64748b;
}

.auth-req-fade-enter-active,
.auth-req-fade-leave-active {
    transition: opacity 0.2s ease;
}

.auth-req-fade-enter-active .auth-req-dialog,
.auth-req-fade-leave-active .auth-req-dialog {
    transition: transform 0.22s ease, opacity 0.22s ease;
}

.auth-req-fade-enter-from,
.auth-req-fade-leave-to {
    opacity: 0;
}

.auth-req-fade-enter-from .auth-req-dialog,
.auth-req-fade-leave-to .auth-req-dialog {
    transform: translateY(12px) scale(0.98);
    opacity: 0;
}
</style>
