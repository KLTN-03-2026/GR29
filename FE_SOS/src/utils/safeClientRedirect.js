/**
 * Chỉ cho phép redirect nội bộ (tránh open redirect).
 * @param {unknown} raw — giá trị query.redirect
 * @returns {string|null}
 */
export function getSafeClientRedirect(raw) {
    if (raw == null || typeof raw !== "string") return null;
    let path = raw.trim();
    if (!path) return null;
    try {
        path = decodeURIComponent(path);
    } catch {
        return null;
    }
    if (!path.startsWith("/") || path.startsWith("//")) return null;
    if (path.includes("://")) return null;

    const lower = path.split("?")[0].toLowerCase();
    const blockedPrefixes = [
        "/client/login",
        "/client/register",
        "/client/forgot-password",
        "/admin",
        "/rescuer",
    ];
    for (const p of blockedPrefixes) {
        if (lower === p || lower.startsWith(p + "/")) return null;
    }
    return path;
}
