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

// Bearer: ưu tiên admin_token (trang /admin), sau đó token người dùng
api.interceptors.request.use((config) => {
  const adminToken = localStorage.getItem("admin_token");
  const userToken = localStorage.getItem("token");
  const t = adminToken || userToken;
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
  getList: () => api.get('/yeu-cau-cuu-ho'),
  getByUser: (userId) => api.get('/yeu-cau-cuu-ho', { params: { id_nguoi_dung: userId } }),
  getDetail: (id) => api.get(`/yeu-cau-cuu-ho/${id}`),
  create: (data) => api.post('/yeu-cau-cuu-ho', data),
  update: (id, data) => api.put(`/yeu-cau-cuu-ho/${id}`, data),
  changeStatus: (id, data) => api.put(`/yeu-cau-cuu-ho/${id}/trang-thai`, data),
  getByStatus: (status) => api.get('/yeu-cau-cuu-ho/theo-trang-thai', { params: { trang_thai: status } }),
  getByPriority: (priority) => api.get('/yeu-cau-cuu-ho/theo-muc-do-khan-cap', { params: { muc_do_khan_cap: priority } }),
  getAIClassification: () => api.get('/yeu-cau-cuu-ho/phan-loai-ai'),
  getQueue: () => api.get('/yeu-cau-cuu-ho/hang-doi'),
  search: (query) => api.get('/yeu-cau-cuu-ho/tim-kiem', { params: { noi_dung_tim: query } }),
  delete: (id) => api.delete(`/yeu-cau-cuu-ho/${id}`),
};

// Rescue Teams (Đội Cứu hộ)
export const rescueTeamAPI = {
  getList: () => api.get('/doi-cuu-ho'),
  getDetail: (id) => api.get(`/doi-cuu-ho/${id}`),
  create: (data) => api.post('/doi-cuu-ho', data),
  update: (id, data) => api.put(`/doi-cuu-ho/${id}`, data),
  getMembers: (id) => api.get(`/doi-cuu-ho/thanh-vien/${id}`),
  addMember: (id, data) => api.post(`/doi-cuu-ho/thanh-vien/${id}`, data),
  removeMember: (id, memberId) => api.delete(`/doi-cuu-ho/thanh-vien/${id}/${memberId}`),
  getResources: (id) => api.get(`/doi-cuu-ho/tai-nguyen/${id}`),
  addResource: (id, data) => api.post(`/doi-cuu-ho/tai-nguyen/${id}`, data),
  updateResource: (id, resourceId, data) => api.put(`/doi-cuu-ho/tai-nguyen/${id}/${resourceId}`, data),
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
};

// Analytics & Reports
export const analyticsAPI = {
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

export default api;