export const ACCOUNT_LOCKED_EVENT = "account-locked";

export function emitAccountLocked(role = "client") {
    window.dispatchEvent(new CustomEvent(ACCOUNT_LOCKED_EVENT, { detail: { role } }));
}
