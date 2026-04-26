// ===========================
// ADMIN ROLES (id_chuc_vu)
// ===========================
export const ADMIN = 1;
export const MANAGER_OPERATOR = 2;
export const OPERATOR = 3;

// ===========================
// RESCUER ROLES (vai_tro_trong_doi)
// ===========================
export const MANAGER_TEAM = 0;
export const TEAMLEAD = 1;
export const MEMBER = 2;

// ===========================
// HELPER: Check admin role
// ===========================
export function isAdmin(role) {
  return role === ADMIN;
}

export function isManagerOperator(role) {
  return role === MANAGER_OPERATOR;
}

export function isOperator(role) {
  return role === OPERATOR;
}

export function isManagerTeam(role) {
  return role === MANAGER_TEAM;
}

export function isTeamlead(role) {
  return role === TEAMLEAD;
}

export function isMember(role) {
  return role === MEMBER;
}
