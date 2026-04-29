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
  const numericRole = role !== null && role !== undefined ? Number(role) : null;
  // ADMIN has full access
  if (numericRole === ADMIN) return true;

  // MANAGER_OPERATOR: blocked routes
  if (numericRole === MANAGER_OPERATOR) {
    return !MANAGER_OPERATOR_BLOCKED.some(prefix => path.startsWith(prefix));
  }

  // OPERATOR: blocked routes
  if (numericRole === OPERATOR) {
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
  if (role === null || role === undefined) return false;
  
  const upperRole = String(role).toUpperCase().trim();
  const isManager = upperRole === "0" || upperRole === "MANAGER_TEAM" || upperRole === "MANAGER";
  const isLead = upperRole === "1" || upperRole === "TEAMLEAD" || upperRole === "TEAM LEADER" || upperRole === "TEAM_LEADER";
  const isMem = upperRole === "2" || upperRole === "MEMBER" || upperRole === "THANH_VIEN" || upperRole === "THÀNH VIÊN";

  // MANAGER_TEAM(0) and TEAMLEAD(1): full access
  if (isManager || isLead) return true;

  // MEMBER(2): blocked routes
  if (isMem) {
    return !MEMBER_BLOCKED_RESCUER.some(prefix => path.startsWith(prefix));
  }

  // Nếu vai trò hợp lệ (không rỗng), cấp quyền tương đương MEMBER
  if (upperRole.length > 0) {
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
