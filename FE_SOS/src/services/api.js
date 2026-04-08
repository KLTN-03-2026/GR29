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

// Bearer: ưu tiên admin_token, sau đó user token, sau cùng rescuer token
api.interceptors.request.use((config) => {
  const adminToken = localStorage.getItem("admin_token");
  const userToken = localStorage.getItem("token");
  const rescuerToken = localStorage.getItem("key_rescuer");
  const t = adminToken || userToken || rescuerToken;
  if (t) {
    config.headers.Authorization = `Bearer ${t}`;
  }
  return config;
});

// Authentication
export const authAPI = {
  loginAdmin: (data) => api.post('/admin/login', data),
  loginUser: (data) => api.post('/nguoi-dung/login', data),
  registerUser: (data) => api.post('/nguoi-dung/register', data),
};

// Admin Management
export const adminAPI = {
  getList: () => api.get('/admin/list'),
  getDetail: (id) => api.get(`/admin/chi-tiet/${id}`),
  create: (data) => api.post('/admin/create', data),
  update: (id, data) => api.put(`/admin/update/${id}`, data),
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
  getDetail: (id) => api.get(`/loai-su-co/${id}`),
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

// Rescue Requests (Yêu cầu Cứu hộ)
export const rescueRequestAPI = {
  getList: (params) => api.get('/yeu-cau-cuu-ho', { params }),
  getDetail: (id) => api.get(`/yeu-cau-cuu-ho/${id}`),
  create: (data) => api.post('/yeu-cau-cuu-ho', data),
  update: (id, data) => api.put(`/yeu-cau-cuu-ho/${id}`, data),
  changeStatus: (id, data) => api.put(`/yeu-cau-cuu-ho/${id}/trang-thai`, data),
  getByStatus: (status) => api.get(`/yeu-cau-cuu-ho/theo-trang-thai/${status}`),
  getByPriority: (muc_do) => api.get(`/yeu-cau-cuu-ho/theo-muc-do-khan-cap/${muc_do}`),
  getAIClassification: () => api.get('/yeu-cau-cuu-ho/phan-loai-ai'),
  getProcessingQueue: (params) => api.get('/hang-doi-xu-ly', { params }),
  search: (params) => api.get('/tim-kiem/yeu-cau', { params: typeof params === 'string' ? { keyword: params } : params }),
  delete: (id) => api.delete(`/yeu-cau-cuu-ho/${id}`),
};

// Rescue Teams (Đội Cứu hộ)
export const rescueTeamAPI = {
  getList: (params) => api.get('/doi-cuu-ho', { params }),
  getDetail: (id) => api.get(`/doi-cuu-ho/${id}`),
  create: (data) => api.post('/doi-cuu-ho', data),
  update: (id, data) => api.put(`/doi-cuu-ho/${id}`, data),
  getMembers: (id) => api.get(`/get-doi-cuu-ho/${id}/thanh-vien`),
  addMember: (id, data) => api.post(`/post-doi-cuu-ho/${id}/thanh-vien`, data),
  removeMember: (id, memberId) => api.delete(`/delete-doi-cuu-ho/${id}/thanh-vien/${memberId}`),
  getResources: (id, params) => api.get(`/get-doi-cuu-ho/${id}/tai-nguyen`, { params }),
  addResource: (id, data) => api.post(`/post-doi-cuu-ho/${id}/tai-nguyen`, data),
  updateResource: (id, resourceId, data) => api.put(`/put-doi-cuu-ho/${id}/tai-nguyen/${resourceId}`, data),
  deleteResource: (id, resourceId) => api.delete(`/delete-doi-cuu-ho/${id}/tai-nguyen/${resourceId}`),
  getLocations: (id) => api.get(`/get-doi-cuu-ho/${id}/vi-tri`),
  addLocation: (id, data) => api.post(`/post-doi-cuu-ho/${id}/vi-tri`, data),
  getCapabilities: (id) => api.get(`/get-doi-cuu-ho/${id}/nang-luc`),
  updateCapability: (id, data) => api.put(`/put-doi-cuu-ho/${id}/nang-luc`, data),
  getIncidentTypes: (id) => api.get(`/get-doi-cuu-ho/${id}/loai-su-co-dung-xu-ly`),
  getByStatus: (trang_thai) => api.get(`/doi-cuu-ho/theo-trang-thai/${trang_thai}`),
  getByArea: (khu_vuc) => api.get(`/doi-cuu-ho/theo-khu-vuc/${encodeURIComponent(khu_vuc)}`),
  search: (query) => api.get('/tim-kiem/doi-cuu-ho', { params: { q: query, noi_dung_tim: query } }),
  delete: (id) => api.delete(`/doi-cuu-ho/${id}`),
};

// Assignments (Phân công Cứu hộ)
export const assignmentAPI = {
  getList: (params) => api.get('/phan-cong-cuu-ho', { params }),
  getDetail: (id) => api.get(`/phan-cong-cuu-ho/${id}`),
  create: (data) => api.post('/phan-cong-cuu-ho', data),
  update: (id, data) => api.put(`/phan-cong-cuu-ho/${id}`, data),
  changeStatus: (id, data) => api.put(`/phan-cong-cuu-ho/${id}/trang-thai`, data),
  getByRequest: (requestId, params) => api.get(`/phan-cong-cuu-ho/theo-yeu-cau/${requestId}`, { params }),
  getByTeam: (teamId, params) => api.get(`/phan-cong-cuu-ho/theo-doi/${teamId}`, { params }),
  getByStatus: (trang_thai, params) => api.get(`/phan-cong-cuu-ho/theo-trang-thai/${trang_thai}`, { params }),
  delete: (id) => api.delete(`/phan-cong-cuu-ho/${id}`),
};

// Analytics & Reports (params: from_date, to_date — Y-m-d)
export const analyticsAPI = {
  getTotalRequests: (params) => api.get('/thong-ke/tong-so-yeu-cau', { params }),
  getRequestsByType: (params) => api.get('/thong-ke/yeu-cau-theo-loai', { params }),
  getRequestsByUrgency: (params) => api.get('/thong-ke/yeu-cau-theo-muc-do-khan-cap', { params }),
  getProcessingStatus: (params) => api.get('/thong-ke/trang-thai-xu-ly', { params }),
  getTeamPerformance: () => api.get('/thong-ke/hieu-suat-doi-cuu-ho'),
  getAvailableTeams: () => api.get('/thong-ke/danh-sach-doi-co-san'),
  getHeatmapData: () => api.get('/thong-ke/heatmap'),
};

export const rescueResultAPI = {
  createForAssignment: (assignmentId, data) => api.post(`/post-ket-qua-cuu-ho/phan-cong/${assignmentId}`, data),
  getByAssignment: (assignmentId) => api.get(`/get-ket-qua-cuu-ho/phan-cong/${assignmentId}`),
  update: (id, data) => api.put(`/ket-qua-cuu-ho/${id}`, data),
};

/** Yêu cầu bổ sung tài nguyên */
export const resourceRequestAPI = {
  list: (params) => api.get('/yeu-cau-bo-sung-tai-nguyen', { params }),
  create: (data) => api.post('/yeu-cau-bo-sung-tai-nguyen', data),
  update: (id, data) => api.put(`/yeu-cau-bo-sung-tai-nguyen/${id}`, data),
};

export const rescuerAccountAPI = {
  getList: () => api.get('/thanh-vien-doi/list'),
  create: (data) => api.post('/thanh-vien-doi/create', data),
  update: (id, data) => api.put(`/thanh-vien-doi/update/${id}`, data),
  changeStatus: (id) => api.put(`/thanh-vien-doi/change-status/${id}`),
  delete: (id) => api.delete(`/thanh-vien-doi/delete/${id}`),
};

export default api;