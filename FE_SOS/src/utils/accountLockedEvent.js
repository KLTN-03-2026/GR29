export const ACCOUNT_LOCKED_EVENT = "account-locked";

export function emitAccountLocked() {
    window.dispatchEvent(new CustomEvent(ACCOUNT_LOCKED_EVENT));
}
