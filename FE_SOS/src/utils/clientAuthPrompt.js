/** Tên sự kiện khi khách truy cập route client cần đăng nhập nhưng chưa có token. */
export const CLIENT_AUTH_REQUIRED_EVENT = "client-auth-required";

/**
 * @param {string} redirectPath — thường là to.fullPath (vd: /client/history)
 */
export function emitClientAuthRequired(redirectPath) {
    window.dispatchEvent(
        new CustomEvent(CLIENT_AUTH_REQUIRED_EVENT, {
            detail: { redirectPath: redirectPath || "/" },
        })
    );
}
