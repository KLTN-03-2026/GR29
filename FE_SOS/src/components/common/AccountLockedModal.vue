<template>
    <Teleport to="body">
        <Transition name="locked-fade">
            <div
                v-if="open"
                class="locked-root"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="lockedTitle"
            >
                <div class="locked-backdrop" />
                <div class="locked-dialog">
                    <div class="locked-icon-wrap">
                        <i class="fa-solid fa-ban locked-icon"></i>
                    </div>
                    <h2 id="lockedTitle" class="locked-title">Tài khoản bị khóa</h2>
                    <p class="locked-text">
                        Tài khoản của bạn đã bị khóa, bạn không thể sử dụng dịch vụ của nowSOS.
                    </p>
                    <button type="button" class="btn-locked-confirm" @click="confirm">
                        Chấp nhận
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import { useRouter } from "vue-router";
import { ACCOUNT_LOCKED_EVENT } from "../../utils/accountLockedEvent";

const router = useRouter();
const open = ref(false);

function onLocked() {
    open.value = true;
}

onMounted(() => {
    window.addEventListener(ACCOUNT_LOCKED_EVENT, onLocked);
});

onUnmounted(() => {
    window.removeEventListener(ACCOUNT_LOCKED_EVENT, onLocked);
});

function confirm() {
    open.value = false;
    localStorage.removeItem("token");
    localStorage.removeItem("user_token");
    localStorage.removeItem("user");
    router.push("/client/login");
}
</script>

<style scoped>
.locked-root {
    position: fixed;
    inset: 0;
    z-index: 20001;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.locked-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
}

.locked-dialog {
    position: relative;
    width: 100%;
    max-width: 400px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.25);
    padding: 2rem 1.5rem 1.5rem;
    text-align: center;
}

.locked-icon-wrap {
    width: 68px;
    height: 68px;
    margin: 0 auto 1.1rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.locked-icon {
    font-size: 1.9rem;
    color: #dc2626;
}

.locked-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.65rem;
}

.locked-text {
    font-size: 0.93rem;
    color: #475569;
    margin: 0 0 1.5rem;
    line-height: 1.6;
}

.btn-locked-confirm {
    width: 100%;
    min-height: 46px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    border: none;
    background: linear-gradient(90deg, #dc2626 0%, #b91c1c 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
    transition: box-shadow 0.15s ease, transform 0.12s ease;
}

.btn-locked-confirm:hover {
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.45);
}

.btn-locked-confirm:active {
    transform: scale(0.98);
}

.locked-fade-enter-active,
.locked-fade-leave-active {
    transition: opacity 0.2s ease;
}

.locked-fade-enter-active .locked-dialog,
.locked-fade-leave-active .locked-dialog {
    transition: transform 0.22s ease, opacity 0.22s ease;
}

.locked-fade-enter-from,
.locked-fade-leave-to {
    opacity: 0;
}

.locked-fade-enter-from .locked-dialog,
.locked-fade-leave-to .locked-dialog {
    transform: translateY(12px) scale(0.98);
    opacity: 0;
}
</style>
