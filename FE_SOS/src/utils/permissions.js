import { ADMIN, MANAGER_OPERATOR, OPERATOR } from "../constants/roles.js";
import { MANAGER_TEAM, TEAMLEAD, MEMBER } from "../constants/roles.js";

// ===========================
// ADMIN ROUTE PERMISSIONS
// ===========================
// Routes that ADMIN (id=1) can access (all)
const ADMIN_ALL_ROUTES = [];

// Routes that MANAGER_OPERATOR (id=2) CANNOT access
const MANAGER_OPERATOR_BLOCKED = [
  "/admin/accounts/",
];

// Routes that OPERATOR (id=3) CANNOT access
const OPERATOR_BLOCKED = [
  "/admin/accounts/",
  "/admin/ai-scoring",
  "/admin/incident-types",
  "/admin/resources",
];

export function canAccessAdminRoute(role, path) {
  // ADMIN has full access
  if (role === ADMIN) return true;

  // MANAGER_OPERATOR: blocked routes
  if (role === MANAGER_OPERATOR) {
    return !MANAGER_OPERATOR_BLOCKED.some(prefix => path.startsWith(prefix));
  }

  // OPERATOR: blocked routes
  if (role === OPERATOR) {
    return !OPERATOR_BLOCKED.some(prefix => path.startsWith(prefix));
  }

  // Unknown role: deny
  return false;
}

// ===========================
// RESCUER ROUTE PERMISSIONS
// ===========================
// Routes that MEMBER (vai_tro_trong_doi=2) CANNOT access
const MEMBER_BLOCKED_RESCUER = [
  "/rescuer/quan-ly",
];

export function canAccessRescuerRoute(role, path) {
  // MANAGER_TEAM(0) and TEAMLEAD(1): full access
  if (role === MANAGER_TEAM || role === TEAMLEAD) return true;

  // MEMBER(2): blocked routes
  if (role === MEMBER) {
    return !MEMBER_BLOCKED_RESCUER.some(prefix => path.startsWith(prefix));
  }

  return false;
}

// ===========================
// ROLE STORAGE HELPERS
// ===========================
export function getAdminRole() {
  try {
    const raw = localStorage.getItem("admin_user");
    if (!raw) return null;
    const user = JSON.parse(raw);
    return user.id_chuc_vu || user.chuc_vu?.id_chuc_vu || null;
  } catch {
    return null;
  }
}

export function getRescuerRole() {
  try {
    const raw = localStorage.getItem("rescuer_user");
    if (!raw) return null;
    const user = JSON.parse(raw);
    return user.vai_tro_trong_doi ?? user.vaiTro ?? null;
  } catch {
    return null;
  }
}
