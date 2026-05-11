// API service for SOS Backend (KhoaLuanK28_SOS Laravel)
import axios from 'axios';

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Bearer: chọn token đúng theo route đang gọi, tránh gửi nhầm token giữa các role
api.interceptors.request.use((config) => {
  const url = config.url || '';

  // Helper: kiểm tra URL có match prefix (ưu tiên exact match trước)
  const match = (prefix) => url.startsWith(prefix);

  // === RESCUER endpoints — kiểm tra TRƯỚC để tránh bị Admin route bắt nhầm ===
  // Tất cả các route /phan-cong-cuu-ho/{id}/... đều là rescuer (tiếp nhận, cập nhật trạng thái...)
  const isRescuerRoute =
    match('/phan-cong-cuu-ho/theo-doi') ||        // GET theo-doi/{teamId}
    match('/phan-cong-cuu-ho/active') ||           // GET active/{teamId}
    match('/thanh-vien-doi/login') ||
    match('/rescuer/login') ||
    match('/rescuer/check-token') ||
    match('/rescuer/change-password') ||
    match('/rescuer/gui-bao-cao') ||
    match('/rescuer/bao-cao') ||
    match('/rescuer/members') ||
    match('/rescuer/tai-nguyen') ||
    match('/doi-cuu-ho/login') ||
    match('/doi-cuu-ho/check-token') ||
    match('/get-doi-cuu-ho') ||
    match('/post-doi-cuu-ho') ||
    match('/put-doi-cuu-ho') ||
    match('/post-ket-qua-cuu-ho') ||
    match('/get-ket-qua-cuu-ho') ||
    match('/get-danh-gia-cuu-ho') ||
    match('/post-danh-gia-cuu-ho') ||
    match('/tai-nguyen') ||
    // Các PUT/GET /phan-cong-cuu-ho/{id}/trang-thai và tương tự (có id số trong URL)
    /^\/phan-cong-cuu-ho\/\d+/.test(url);

  // === 1. ADMIN endpoints (auth:admin) ===
  // Chỉ dùng admin_token cho các route admin cụ thể — KHÔNG dùng match chung cho /phan-cong-cuu-ho
  const isAdminRoute =
    !isRescuerRoute && (
      match('/admin') ||
      match('/auto-dispatch') ||
      match('/yeu-cau-cuu-ho') ||
      match('/phan-cong-cuu-ho/theo-yeu-cau') ||
      match('/phan-cong-cuu-ho/theo-trang-thai') ||
      match('/phan-cong-cuu-ho') ||              // chỉ các route list/create (GET/POST root)
      match('/nguoi-dung/list') ||
      match('/nguoi-dung/chi-tiet') ||
      match('/nguoi-dung/create') ||
      match('/nguoi-dung/update') ||
      match('/nguoi-dung/delete') ||
      match('/nguoi-dung/change-status') ||
      match('/nguoi-dung/search') ||
      match('/thanh-vien-doi/list') ||
      match('/thanh-vien-doi/create') ||
      match('/thanh-vien-doi/update') ||
      match('/thanh-vien-doi/delete') ||
      match('/thanh-vien-doi/change-status') ||
      match('/thong-ke')
    );

  if (isRescuerRoute) {
    const t = localStorage.getItem('rescuer_token');
    if (t) config.headers.Authorization = `Bearer ${t}`;
  } else if (isAdminRoute) {
    const t = localStorage.getItem('admin_token');
    if (t) config.headers.Authorization = `Bearer ${t}`;
  } else {
    // === 3. USER endpoints (client) ===
    const t = localStorage.getItem('token') || localStorage.getItem('user_token');
    if (t) config.headers.Authorization = `Bearer ${t}`;
  }

  return config;
});

// Authentication
export const authAPI = {
  loginAdmin: (data) => api.post('/admin/login', data),
  loginUser: (data) => api.post('/nguoi-dung/login', data),
  registerUser: (data) => api.post('/nguoi-dung/register', data),
  loginRescuer: (data) => api.post('/rescuer/login', data),
};

// Guest Session (cho phép gửi yêu cầu khi chưa đăng nhập)
export const guestAPI = {
  createOrUpdateSession: (data) => api.post('/guest/session', data),
};

// Client Profile
export const clientAPI = {
  getProfile: () => api.get('/nguoi-dung/check-client'),
  updateProfile: (data) => api.post('/client/profile/update', data),
};

// Password Reset
export const passwordAPI = {
  sendOtp: (email) => api.post('/password/send-otp', { email }),
  verifyOtp: (email, ma_otp) => api.post('/password/verify-otp', { email, ma_otp }),
  resetPassword: (email, reset_token, mat_khau_moi, mat_khau_moi_confirmation) =>
    api.post('/password/reset', { email, reset_token, mat_khau_moi, mat_khau_moi_confirmation }),
  resendOtp: (email) => api.post('/password/resend-otp', { email }),
};

// Admin Management
export const adminAPI = {
  getList: () => api.get('/admin/list'),
  getDetail: (id) => api.get(`/admin/chi-tiet/${id}`),
  create: (data) => api.post('/admin/create', data),
  update: (id, data) => api.put(`/admin/update/${id}`, data),
  updateProfile: (data) => api.post('/admin/profile/update', data),
  changeStatus: (id) => api.put(`/admin/change-status/${id}`),
  activate: (id) => api.put(`/admin/active/${id}`),
  search: (query) => api.get('/admin/search', { params: { noi_dung_tim: query } }),
  delete: (id) => api.delete(`/admin/delete/${id}`),
};

// User Management
export const userAPI = {
  getList: () => api.get('/nguoi-dung/list'),
  getDetail: (id) => api.get(`/nguoi-dung/chi-tiet/${id}`),
  create: (data) => api.post('/nguoi-dung/create', data),
  update: (id, data) => api.put(`/nguoi-dung/update/${id}`, data),
  changeStatus: (id) => api.put(`/nguoi-dung/change-status/${id}`),
  search: (query) => api.get('/nguoi-dung/search', { params: { noi_dung_tim: query } }),
  delete: (id) => api.delete(`/nguoi-dung/delete/${id}`),
};

// Incident Types (Loại Sự Cố)
export const incidentTypeAPI = {
  getList: () => api.get('/loai-su-co'),
  getDetail: (id) => api.get(`/loai-su-co/${id}/chi-tiet`),
  create: (data) => api.post('/loai-su-co', data),
  update: (id, data) => api.put(`/loai-su-co/${id}`, data),
  changeStatus: (id) => api.put(`/loai-su-co/cap-nhat-trang-thai/${id}`),
  getDetails: (id) => api.get(`/loai-su-co/chi-tiet/${id}`),
  getByStatus: (status) => api.get('/loai-su-co/theo-trang-thai', { params: { trang_thai: status } }),
  getRequests: (id) => api.get(`/loai-su-co/yeu-cau-cuu-ho/${id}`),
  getTeams: (id) => api.get(`/loai-su-co/doi-cuu-ho/${id}`),
  search: (query) => api.get('/loai-su-co/tim-kiem', { params: { noi_dung_tim: query } }),
  delete: (id) => api.delete(`/loai-su-co/${id}`),
};

// Processing Queue (HangDoiXuLy)
export const hangDoiAPI = {
  getList: () => api.get('/hang-doi-xu-ly'),
  getByStatus: (status) => api.get(`/hang-doi-xu-ly/theo-trang-thai/${status}`),
};

// Rescue Requests (Yêu cầu Cứu hộ)
export const rescueRequestAPI = {
  getList: () => api.get('/yeu-cau-cuu-ho'),
  getByUser: (userId) => api.get('/yeu-cau-cuu-ho', { params: { id_nguoi_dung: userId } }),
  getDetail: (id) => api.get(`/yeu-cau-cuu-ho/${id}`),
  create: (data) => {
    // Nếu là FormData (có file upload) thì bỏ Content-Type để axios tự set boundary
    if (data instanceof FormData) {
      return api.post('/yeu-cau-cuu-ho', data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    }
    return api.post('/yeu-cau-cuu-ho', data);
  },
  update: (id, data) => api.put(`/yeu-cau-cuu-ho/${id}`, data),
  changeStatus: (id, data) => api.put(`/yeu-cau-cuu-ho/${id}/trang-thai`, data),
  getByStatus: (status, params = {}) => api.get('/yeu-cau-cuu-ho/theo-trang-thai', { params: { trang_thai: status, ...params } }),
  getByPriority: (priority) => api.get('/yeu-cau-cuu-ho/theo-muc-do-khan-cap', { params: { muc_do_khan_cap: priority } }),
  getAIClassification: () => api.get('/yeu-cau-cuu-ho/phan-loai-ai'),
  getQueue: () => api.get('/yeu-cau-cuu-ho/hang-doi'),
  search: (query) => api.get('/yeu-cau-cuu-ho/tim-kiem', { params: { noi_dung_tim: query } }),
  delete: (id) => api.delete(`/yeu-cau-cuu-ho/${id}`),
  findNearestTeams: (data) => api.post('/yeu-cau-cuu-ho/tim-doi-gan-nhat', data),
  getTrackingDetail: (id) => api.get(`/yeu-cau-cuu-ho/${id}/theo-doi`),
  getTrackingList: () => api.get('/yeu-cau-cuu-ho/theo-doi/danh-sach'),
  getTrackingDelta: (since) => api.get('/yeu-cau-cuu-ho/theo-doi/thay-doi', { params: { since } }),
  // Client review
  submitRating: (yeuCauId, data) =>
    api.post('/post-danh-gia-cuu-ho/yeu-cau/' + yeuCauId, data),
};

// Rescue Teams (Đội Cứu hộ)
export const rescueTeamAPI = {
  getList: (params = {}) => api.get('/doi-cuu-ho', { params }),
  getDetail: (id) => api.get(`/doi-cuu-ho/${id}`),
  create: (data) => api.post('/doi-cuu-ho', data),
  update: (id, data) => api.put(`/doi-cuu-ho/${id}`, data),
  getMembers: (id) => api.get(`/doi-cuu-ho/thanh-vien/${id}`),
  addMember: (id, data) => api.post(`/doi-cuu-ho/thanh-vien/${id}`, data),
  removeMember: (id, memberId) => api.delete(`/doi-cuu-ho/thanh-vien/${id}/${memberId}`),
  getResources: (id) => api.get(`/doi-cuu-ho/tai-nguyen/${id}`),
  getLocations: (id) => api.get(`/doi-cuu-ho/vi-tri/${id}`),
  addLocation: (id, data) => api.post(`/doi-cuu-ho/vi-tri/${id}`, data),
  getCapabilities: (id) => api.get(`/doi-cuu-ho/nang-luc/${id}`),
  updateCapability: (id, data) => api.put(`/doi-cuu-ho/nang-luc/${id}`, data),
  getIncidentTypes: (id) => api.get(`/doi-cuu-ho/loai-su-co/${id}`),
  getByStatus: (status) => api.get('/doi-cuu-ho/theo-trang-thai', { params: { trang_thai: status } }),
  getByArea: (area) => api.get('/doi-cuu-ho/theo-khu-vuc', { params: { khu_vuc: area } }),
  search: (query) => api.get('/doi-cuu-ho/tim-kiem', { params: { noi_dung_tim: query } }),
  delete: (id) => api.delete(`/doi-cuu-ho/${id}`),
};

// Assignments (Phân công Cứu hộ)
export const assignmentAPI = {
  getList: () => api.get('/phan-cong-cuu-ho'),
  getDetail: (id) => api.get(`/phan-cong-cuu-ho/${id}`),
  create: (data) => api.post('/phan-cong-cuu-ho', data),
  update: (id, data) => api.put(`/phan-cong-cuu-ho/${id}`, data),
  changeStatus: (id) => api.put(`/phan-cong-cuu-ho/cap-nhat-trang-thai/${id}`),
  getByRequest: (requestId) => api.get(`/phan-cong-cuu-ho/theo-yeu-cau/${requestId}`),
  getByTeam: (teamId) => api.get(`/phan-cong-cuu-ho/theo-doi/${teamId}`),
  getByStatus: (status) => api.get('/phan-cong-cuu-ho/theo-trang-thai', { params: { trang_thai: status } }),
  delete: (id) => api.delete(`/phan-cong-cuu-ho/${id}`),
  updateLocation: (id, data) => api.post(`/phan-cong-cuu-ho/${id}/location`, data),
};

// Analytics & Reports
export const analyticsAPI = {
  getDashboard: () => api.get('/thong-ke/dashboard'),
  getTotalRequests: () => api.get('/thong-ke/tong-so-yeu-cau'),
  getRequestsByType: () => api.get('/thong-ke/yeu-cau-theo-loai'),
  getRequestsByPriority: () => api.get('/thong-ke/yeu-cau-theo-muc-do-khan-cap'),
  getProcessingStatus: () => api.get('/thong-ke/trang-thai-xu-ly'),
  getTeamPerformance: () => api.get('/thong-ke/hieu-suat-doi-cuu-ho'),
  getAvailableTeams: () => api.get('/thong-ke/danh-sach-doi-co-san'),
  getHeatmapData: () => api.get('/thong-ke/heatmap'),
};

export const rescuerAccountAPI = {
  getList: () => api.get('/thanh-vien-doi/list'),
  create: (data) => api.post('/thanh-vien-doi/create', data),
  update: (id, data) => api.put(`/thanh-vien-doi/update/${id}`, data),
  changeStatus: (id) => api.put(`/thanh-vien-doi/change-status/${id}`),
  delete: (id) => api.delete(`/thanh-vien-doi/delete/${id}`),
};

// Rescue Results (Kết quả Cứu hộ)
export const rescueResultAPI = {
  getList: () => api.get('/ket-qua-cuu-ho'),
  getByUser: (userId) => api.get('/ket-qua-cuu-ho', { params: { id_nguoi_dung: userId } }),
  getDetail: (id) => api.get(`/ket-qua-cuu-ho/${id}`),
  create: (data) => api.post('/ket-qua-cuu-ho', data),
  update: (id, data) => api.put(`/ket-qua-cuu-ho/${id}`, data),
  search: (query) => api.get('/ket-qua-cuu-ho/tim-kiem', { params: { noi_dung_tim: query } }),
  delete: (id) => api.delete(`/ket-qua-cuu-ho/${id}`),
};

// ============ RESCUER DEDICATED APIs ============
export const rescuerAPI = {
  // Xác thực
  checkToken: () => api.get('/doi-cuu-ho/check-token'),

  // Nhiệm vụ được phân công cho đội
  getAssignments: (params = {}) => api.get('/phan-cong-cuu-ho', { params }),
  getAssignmentByTeam: (teamId, params = {}) =>
    api.get('/phan-cong-cuu-ho/theo-doi/' + teamId, { params }),
  getAssignmentByStatus: (status) =>
    api.get('/phan-cong-cuu-ho/theo-trang-thai/' + status),
  updateAssignmentStatus: (id, data) =>
    api.put('/phan-cong-cuu-ho/' + id + '/trang-thai', data),
  getActiveAssignment: (teamId) =>
    api.get('/phan-cong-cuu-ho/active/' + teamId),

  // Yêu cầu cứu hộ liên quan
  getYeuCauDetail: (id) => api.get('/yeu-cau-cuu-ho/' + id),
  updateYeuCauStatus: (id, data) =>
    api.put('/yeu-cau-cuu-ho/' + id + '/trang-thai', data),
  resNhanYeuCau: (data) =>
    api.post('/yeu-cau-cuu-ho/rescuer-nhan-yeu-cau', data),

  // Đội cứu hộ
  getTeamMembers: (id) => api.get('/get-doi-cuu-ho/' + id + '/thanh-vien'),
  getTeamResources: (id) => api.get('/get-doi-cuu-ho/' + id + '/tai-nguyen'),
  /** Tồn kho theo loại (để rescuer xem & chọn khi yêu cầu) */
  xemTonKhoTaiNguyen: () => api.get('/rescuer/tai-nguyen/kho-xem'),
  /** Gửi yêu cầu cấp phát lên admin */
  guiYeuCauCapPhatTaiNguyen: (data) => api.post('/rescuer/tai-nguyen/yeu-cau-cap-phat', data),
  getTeamLocations: (id) => api.get('/get-doi-cuu-ho/' + id + '/vi-tri'),
  addTeamLocation: (id, data) =>
    api.post('/post-doi-cuu-ho/' + id + '/vi-tri', data),
  getTeamCapabilities: (id) => api.get('/get-doi-cuu-ho/' + id + '/nang-luc'),
  updateTeamCapabilities: (id, data) =>
    api.put('/put-doi-cuu-ho/' + id + '/nang-luc', data),

  // Kết quả cứu hộ
  createResult: (phanCongId, data) => {
    if (data instanceof FormData) {
      return api.post('/post-ket-qua-cuu-ho/phan-cong/' + phanCongId, data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    }
    return api.post('/post-ket-qua-cuu-ho/phan-cong/' + phanCongId, data);
  },
  getResult: (phanCongId) =>
    api.get('/get-ket-qua-cuu-ho/phan-cong/' + phanCongId),

  // Báo cáo cứu hộ (Issue #4 - rescue_reports table)
  guiBaoCao: (data) => {
    if (data instanceof FormData) {
      return api.post('/rescuer/gui-bao-cao', data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    }
    return api.post('/rescuer/gui-bao-cao', data);
  },

  // Đánh giá cứu hộ
  getRatings: (yeuCauId) => api.get('/get-danh-gia-cuu-ho/yeu-cau/' + yeuCauId),

  // Đổi mật khẩu rescuer
  changePassword: (data) => api.post('/rescuer/change-password', data),

  // Thành viên đội (quản lý - role-filtered via API)
  getMembers: () => api.get('/rescuer/members'),
  addMember: (data) => api.post('/thanh-vien-doi/create', data),
  updateMember: (id, data) => api.put('/thanh-vien-doi/update/' + id, data),
  toggleMemberStatus: (id) => api.put('/thanh-vien-doi/change-status/' + id),
  removeMember: (id) => api.delete('/thanh-vien-doi/delete/' + id),

  // Thống kê heatmap
  getHeatmap: () => api.get('/thong-ke/heatmap'),

  // Tài nguyên - Sử dụng / Trả lại
  checkoutResource: (id, data) =>
    api.post('/tai-nguyen/' + id + '/checkout', data),
  returnResource: (id, data) =>
    api.post('/tai-nguyen/' + id + '/return', data),
  getTeamResourceHistory: (id) =>
    api.get('/tai-nguyen/' + id + '/history'),
};

// Auto Dispatch
export const autoDispatchAPI = {
  getStatus: () => api.get('/auto-dispatch/status'),
  toggle: () => api.post('/auto-dispatch/toggle'),
  enable: () => api.post('/auto-dispatch/enable'),
  disable: () => api.post('/auto-dispatch/disable'),
  dispatch: (id) => api.post(`/auto-dispatch/dispatch/${id}`),
  dispatchSync: (id) => api.post(`/auto-dispatch/dispatch-sync/${id}`),
  getEscalations: () => api.get('/auto-dispatch/admin-escalations'),
  removeEscalation: (id) => api.delete(`/auto-dispatch/admin-escalations/${id}`),
  debug: (id) => api.get(`/auto-dispatch/debug/${id}`),
  updateConfig: (data) => api.put('/auto-dispatch/config', data),
};

// Admin Resources (Doi Cuu Ho + Tai Nguyen)
export const adminResourcesAPI = {
  // Don vi cuu ho
  getList: (params = {}) => api.get('/admin/doi-cuu-ho/list', { params }),
  create: (data) => api.post('/admin/doi-cuu-ho/create', data),
  update: (data) => api.post('/admin/doi-cuu-ho/update', data),
  delete: (data) => api.post('/admin/doi-cuu-ho/delete', data),

  // Tai nguyen
  createTaiNguyen: (data) => api.post('/admin/tai-nguyen/create', data),
  updateTaiNguyen: (data) => api.post('/admin/tai-nguyen/update', data),
  deleteTaiNguyen: (data) => api.post('/admin/tai-nguyen/delete', data),
  getByDoi: (doiId) => api.get('/admin/tai-nguyen/doi/' + doiId),

  // Kho
  getKho: () => api.get('/admin/tai-nguyen/kho'),
  capNhatKho: (data) => api.post('/admin/tai-nguyen/kho/cap-nhat', data),
  getLichSuKho: (params = {}) => api.get('/admin/tai-nguyen/kho/lich-su', { params }),
  nhapKho: (data) => api.post('/admin/tai-nguyen/kho/nhap', data),

  // Yêu cầu cấp phát (TAB Cấp phát admin)
  layDanhSachYeuCauCapPhat: (params = {}) =>
    api.get('/admin/tai-nguyen/yeu-cau-cap-phat', { params }),
  capPhatTheoYeuCau: (id) => api.post(`/admin/tai-nguyen/yeu-cau-cap-phat/${id}/cap-phat`),
  tuChoiYeuCauCapPhat: (id, data = {}) =>
    api.post(`/admin/tai-nguyen/yeu-cau-cap-phat/${id}/tu-choi`, data),
  getLichSuCapPhat: (params = {}) => api.get('/admin/tai-nguyen/lich-su-cap', { params }),
};

export default api;