<template>
  <div class="processing-wrapper">
    <div class="mission-layout h-100 d-flex flex-column flex-lg-row">

      <!-- Left: Mission Detail Panel -->
      <div class="detail-panel d-flex flex-column bg-white z-3 shadow-lg">
        <div class="detail-header p-4 pb-3 border-bottom position-relative overflow-hidden">
          <div class="bg-shape bg-danger rounded-circle position-absolute opacity-10" style="width: 180px; height: 180px; top: -80px; right: -60px;"></div>
          <div class="d-flex align-items-center justify-content-between position-relative z-1">
            <div class="d-flex align-items-center">
              <div class="icon-box bg-danger bg-opacity-10 text-danger me-3 d-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px;">
                <i class="fa-solid fa-spinner fa-spin fs-5"></i>
              </div>
              <div>
                <h5 class="fw-bolder mb-1 text-dark">Nhiệm Vụ Đang Xử Lý</h5>
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 small fw-bold">
                  <span class="pulse-dot bg-danger me-1"></span> ĐANG XỬ LÝ
                </span>
              </div>
            </div>
            <router-link to="/rescuer/home" class="btn btn-sm btn-outline-secondary rounded-circle">
              <i class="fa-solid fa-arrow-left"></i>
            </router-link>
          </div>
        </div>

        <div class="detail-body flex-grow-1 overflow-auto p-4" v-if="currentMission">
          <!-- Priority & Time -->
          <div class="d-flex justify-content-between align-items-start mb-4">
            <span class="badge rounded-pill px-3 py-2 fw-bold border" :class="getPriorityBadge()">
              <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ getPriorityText() }}
            </span>
            <div class="text-end">
              <div class="text-muted small fw-medium"><i class="fa-regular fa-clock me-1 text-danger"></i> Bắt đầu: {{ formatTime(currentMission.created_at) }}</div>
            </div>
          </div>

          <!-- Incident Title -->
          <h4 class="fw-bold text-dark mb-1">{{ getIncidentTypeName(currentMission) }}</h4>
          <p class="text-muted small mb-4">
            <i class="fa-solid fa-layer-group me-1 text-danger"></i>
            {{ currentMission.yeu_cau && currentMission.yeu_cau.muc_do_khan_cap ? currentMission.yeu_cau.muc_do_khan_cap : '-' }}
          </p>

          <!-- Victim Card -->
          <div class="victim-card bg-light rounded-4 p-4 mb-4 border border-light shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="d-flex align-items-center">
                <div class="victim-avatar me-3 d-flex align-items-center justify-content-center rounded-circle bg-white text-danger shadow-sm fw-bold" style="width: 50px; height: 50px; font-size: 18px;">
                  {{ (getReporterName(currentMission) || 'N').charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="text-muted fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">NGƯỜI GẶP NẠN</div>
                  <div class="fw-bold text-dark fs-6">{{ getReporterName(currentMission) }}</div>
                </div>
              </div>
              <a v-if="getReporterPhone(currentMission)" :href="'tel:' + getReporterPhone(currentMission)" class="btn btn-success rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="fa-solid fa-phone"></i>
              </a>
            </div>
            <div class="small text-secondary" v-if="getReporterPhone(currentMission)">
              <i class="fa-solid fa-phone me-1 text-secondary"></i>
              <a :href="'tel:' + getReporterPhone(currentMission)" class="text-secondary text-decoration-none">{{ getReporterPhone(currentMission) }}</a>
            </div>
          </div>

          <!-- Location Card -->
          <div class="location-card bg-light rounded-4 p-4 mb-4 border border-light shadow-sm">
            <div class="d-flex align-items-start">
              <div class="location-icon me-3 mt-1 d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger" style="width: 44px; height: 44px;">
                <i class="fa-solid fa-location-dot fs-5"></i>
              </div>
              <div class="flex-grow-1">
                <div class="text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">ĐỊA ĐIỂM SỰ CỐ</div>
                <div class="fw-semibold text-dark mb-2">{{ getRequestAddress(currentMission) }}</div>
                <div class="d-flex gap-2">
                  <a v-if="getRequestAddress(currentMission)" :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(getRequestAddress(currentMission))" target="_blank" class="btn btn-danger btn-sm rounded-3 fw-bold shadow-sm">
                    <i class="fa-solid fa-route me-1"></i> Chỉ đường
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Mission Progress -->
          <div class="progress-section mb-4">
            <div class="text-muted fw-bold mb-3" style="font-size: 10px; letter-spacing: 0.5px;">TIẾN TRÌNH NHIỆM VỤ</div>
            <div class="progress-steps d-flex justify-content-between position-relative mb-4">
              <div class="step done text-center" :class="{ active: missionStep >= 1 }">
                <div class="step-icon mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" :class="missionStep >= 1 ? 'bg-danger text-white' : 'bg-light text-secondary'">
                  <i class="fa-solid fa-bell"></i>
                </div>
                <div class="small fw-bold" :class="missionStep >= 1 ? 'text-danger' : 'text-secondary'">Tiếp nhận</div>
              </div>
              <div class="step-line position-absolute" style="top: 18px; left: 15%; right: 15%; height: 2px; background: #e5e7eb; z-index: 0;"></div>
              <div class="step done text-center" :class="{ active: missionStep >= 2 }">
                <div class="step-icon mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" :class="missionStep >= 2 ? 'bg-danger text-white' : 'bg-light text-secondary'">
                  <i class="fa-solid fa-truck-medical"></i>
                </div>
                <div class="small fw-bold" :class="missionStep >= 2 ? 'text-danger' : 'text-secondary'">Đang di chuyển</div>
              </div>
              <div class="step done text-center" :class="{ active: missionStep >= 3 }">
                <div class="step-icon mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" :class="missionStep >= 3 ? 'bg-danger text-white' : 'bg-light text-secondary'">
                  <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="small fw-bold" :class="missionStep >= 3 ? 'text-danger' : 'text-secondary'">Đã đến nơi</div>
              </div>
              <div class="step done text-center" :class="{ active: missionStep >= 4 }">
                <div class="step-icon mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" :class="missionStep >= 4 ? 'bg-danger text-white' : 'bg-light text-secondary'">
                  <i class="fa-solid fa-check"></i>
                </div>
                <div class="small fw-bold" :class="missionStep >= 4 ? 'text-danger' : 'text-secondary'">Hoàn thành</div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="action-buttons d-flex flex-column gap-2">
            <button class="btn btn-danger w-100 fw-bold py-3 rounded-3 shadow-danger transition-all hover-lift" @click="markArrived" v-if="missionStep < 3">
              <i class="fa-solid fa-location-dot me-2"></i> ĐÃ ĐẾN HIỆN TRƯỜNG
            </button>
            <button class="btn btn-success w-100 fw-bold py-3 rounded-3 shadow-sm transition-all hover-lift" @click="openReportModal" v-if="missionStep >= 3 && missionStep < 4">
              <i class="fa-solid fa-flag me-2"></i> BÁO CÁO KẾT QUẢ
            </button>
            <button class="btn btn-warning w-100 fw-bold py-2 rounded-3 transition-all hover-lift" @click="openReinforceModal" v-if="missionStep < 4">
              <i class="fa-solid fa-phone me-1"></i> Yêu cầu tiếp viện
            </button>
          </div>
        </div>

        <!-- Loading -->
        <div v-else-if="loading" class="flex-grow-1 d-flex align-items-center justify-content-center p-4">
          <div class="text-center">
            <div class="spinner-border text-danger" role="status"></div>
            <p class="mt-2 text-muted">Đang tải...</p>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex-grow-1 d-flex align-items-center justify-content-center p-4">
          <div class="text-center">
            <div class="mb-3">
              <i class="fa-solid fa-inbox text-secondary opacity-25" style="font-size: 64px;"></i>
            </div>
            <h6 class="text-secondary fw-bold">Không có nhiệm vụ đang xử lý</h6>
            <p class="text-muted small mb-3">Tiếp nhận nhiệm vụ từ trang chủ để bắt đầu</p>
            <router-link to="/rescuer/home" class="btn btn-danger rounded-pill px-4 fw-bold">
              <i class="fa-solid fa-bell me-2"></i> Nhận nhiệm vụ
            </router-link>
          </div>
        </div>
      </div>

      <!-- Right: Map Area -->
      <div class="map-area flex-grow-1 position-relative bg-light">
        <div ref="mapContainer" id="rescuer-dangxuly-map" style="width: 100%; height: 100%;"></div>

        <!-- Map Controls -->
        <div class="map-controls position-absolute bottom-0 end-0 m-4 d-flex flex-column gap-2 z-3 d-none d-md-flex">
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-dark btn-map-control hover-bg-gray" @click="zoomIn"><i class="fa-solid fa-plus"></i></button>
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-dark btn-map-control hover-bg-gray" @click="zoomOut"><i class="fa-solid fa-minus"></i></button>
          <button class="btn btn-white shadow-sm bg-white p-2 border-0 rounded-3 text-danger mt-2 btn-map-control hover-bg-gray" @click="locateMe"><i class="fa-solid fa-crosshairs"></i></button>
        </div>
      </div>
    </div>

    <!-- Report Result Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" ref="reportModalEl">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-white">
            <h5 class="modal-title fw-bold"><i class="fa-solid fa-flag me-2 text-danger"></i>Báo Cáo Kết Quả</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <!-- Result Type Selection -->
            <div class="mb-4">
              <label class="form-label fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">KẾT QUẢ NHIỆM VỤ</label>
              <div class="d-flex gap-3">
                <button class="btn flex-grow-1 py-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2" :class="reportResult === 'success' ? 'btn-success shadow-sm' : 'btn-outline-success'" @click="reportResult = 'success'">
                  <i class="fa-solid fa-check-circle"></i> Thành công
                </button>
                <button class="btn flex-grow-1 py-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2" :class="reportResult === 'failure' ? 'btn-danger shadow-sm' : 'btn-outline-danger'" @click="reportResult = 'failure'">
                  <i class="fa-solid fa-times-circle"></i> Thất bại
                </button>
              </div>
            </div>

            <!-- Failure Reason -->
            <div class="mb-3" v-if="reportResult === 'failure'">
              <label class="form-label fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">LÝ DO THẤT BẠI <span class="text-danger">*</span></label>
              <textarea class="form-control rounded-3" rows="3" v-model="reportReason" placeholder="Mô tả lý do không thể hoàn thành nhiệm vụ..."></textarea>
            </div>

            <!-- Report Notes -->
            <div class="mb-3">
              <label class="form-label fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">BÁO CÁO HIỆN TRƯỜNG</label>
              <textarea class="form-control rounded-3" rows="3" v-model="reportNotes" :placeholder="reportResult === 'failure' ? 'Mô tả tình trạng hiện trường...' : 'Ghi chép tình trạng cứu hộ...'"></textarea>
            </div>

            <!-- Image Upload -->
            <div class="mb-3">
              <label class="form-label fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">
                HÌNH ẢNH MINH CHỨNG <span class="text-danger" v-if="reportResult === 'failure'">*</span>
              </label>
              <div class="border rounded-3 p-4 text-center bg-light" style="border-style: dashed;">
                <input type="file" class="form-control mb-2" accept="image/*" @change="handleImageUpload" ref="imageInput">
                <div v-if="reportImagePreview" class="mt-3">
                  <img :src="reportImagePreview" class="rounded-3" style="max-height: 150px; max-width: 100%;">
                </div>
                <p class="text-muted small mb-0 mt-2">Chấp nhận file JPG, PNG, WEBP (tối đa 10MB)</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary fw-bold rounded-3" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn fw-bold rounded-3" :class="reportResult === 'success' ? 'btn-success' : 'btn-danger'" @click="submitReport" :disabled="submittingReport">
              <span v-if="submittingReport" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="fa-solid fa-paper-plane me-2"></i>
              Gửi Báo Cáo
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reinforce Request Modal -->
    <div class="modal fade" id="reinforceModal" tabindex="-1" ref="reinforceModalEl">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-phone-volume me-2"></i>Yêu Cầu Tiếp Viện</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <span>Hệ thống sẽ thông báo cho quản trị viên để điều động đội cứu hộ bổ sung.</span>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">NỘI DUNG YÊU CẦU</label>
              <textarea class="form-control rounded-3" rows="4" v-model="reinforceMessage" placeholder="Mô tả tình trạng cần hỗ trợ, số lượng nhân sự, thiết bị cần thiết..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary fw-bold rounded-3" data-bs-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-warning fw-bold rounded-3" @click="submitReinforce" :disabled="submittingReinforce">
              <span v-if="submittingReinforce" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="fa-solid fa-paper-plane me-2"></i>
              Gửi Yêu Cầu
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { rescuerAPI } from "../../../services/api.js";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

export default {
  name: "DangXuLy",
  data() {
    return {
      note: "",
      loading: false,
      currentMission: null,
      missionStep: 1,
      map: null,
      teamMarker: null,
      requestMarker: null,
      routeLine: null,
      teamLat: null,
      teamLng: null,
      reportModalEl: null,
      reinforceModalEl: null,
      reportResult: 'success',
      reportReason: '',
      reportNotes: '',
      reportImage: null,
      reportImagePreview: null,
      submittingReport: false,
      reinforceMessage: '',
      submittingReinforce: false,
      teamId: null,
      pollInterval: null,
      isSyncing: false,
    };
  },
  async mounted() {
    this.loadTeamData();
    await this.loadActiveMission();
    this.initMap();
    this.$nextTick(() => {
      if (typeof bootstrap !== 'undefined') {
        this.reportModalEl = document.getElementById('reportModal');
        this.reinforceModalEl = document.getElementById('reinforceModal');
      }
    });
    this.startPolling();
  },
  beforeUnmount() {
    this.stopPolling();
    if (this.map) {
      this.map.remove();
    }
  },
  methods: {
    // ─── Smart Delta Polling (15s) ─────────────────────────────────────────────
    // Only polls while a mission is active. Fetches the single active assignment
    // by team ID (targeted call, not the full list), then updates in-place.
    startPolling() {
      this.stopPolling();
      // Poll every 15s — only matters while there's an active mission
      this.pollInterval = setInterval(() => {
        this.pollMissionStatus();
      }, 15000);
    },
    stopPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval);
        this.pollInterval = null;
      }
    },
    async pollMissionStatus() {
      if (this.isSyncing || !this.currentMission) return;
      this.isSyncing = true;
      try {
        // Targeted poll: fetch only the active assignment for this team
        const teamId = this.teamId;
        if (!teamId) {
          // No team context — stop polling and fall back to full reload next time
          this.stopPolling();
          return;
        }

        // Use getActiveAssignment for a single-record query (lightweight)
        let assignment = null;
        try {
          const resp = await rescuerAPI.getActiveAssignment(teamId);
          assignment = resp?.data?.data || resp?.data || null;
        } catch {
          assignment = null;
        }

        if (!assignment) {
          // Mission completed / cancelled by admin — navigate away
          this.stopPolling();
          this.currentMission = null;
          toaster.info("Nhiệm vụ đã hoàn thành hoặc bị hủy.");
          return;
        }

        // Detect status changes
        const newStatus = assignment.trang_thai_nhiem_vu || "";
        const oldStatus = this.currentMission.trang_thai_nhiem_vu || "";
        if (newStatus !== oldStatus) {
          this.currentMission = assignment;
          this.updateMissionStep();
          this.updateMapMission();
        }
      } catch (e) {
        // Silent — don't interrupt user
      } finally {
        this.isSyncing = false;
      }
    },
    loadTeamData() {
      const teamStr = localStorage.getItem("rescuer_team");
      if (teamStr) {
        try {
          const team = JSON.parse(teamStr);
          this.teamId = team.id_doi_cuu_ho || team.id;
          this.teamLat = team.vi_tri_lat || null;
          this.teamLng = team.vi_tri_lng || null;
        } catch (e) {
          console.error('Error parsing team data', e);
        }
      }
    },
    async loadActiveMission(silent = false) {
      if (!silent) this.loading = true;
      try {
        let teamId = this.teamId;
        if (!teamId) {
          const teamStr = localStorage.getItem("rescuer_team");
          if (teamStr) {
            const team = JSON.parse(teamStr);
            teamId = team.id_doi_cuu_ho || team.id;
          }
        }

        let assignments = [];
        if (teamId) {
          const res = await rescuerAPI.getAssignmentByTeam(teamId, { per_page: 100 });
          if (res.data && res.data.data) {
            assignments = res.data.data.data || res.data.data;
          }
        } else {
          const res = await rescuerAPI.getAssignments({ per_page: 100 });
          if (res.data && res.data.data) {
            assignments = res.data.data.data || res.data.data;
          }
        }

        this.currentMission = null;
        this.missionStep = 1;

        if (Array.isArray(assignments)) {
          for (let i = 0; i < assignments.length; i++) {
            const item = assignments[i];
            const status = item.trang_thai_nhiem_vu || '';
            if (status === 'DANG_XU_LY') {
              this.currentMission = item;
              this.missionStep = 2;
              break;
            } else if (status === 'DA_DEN_HIEN_TRUONG' && !this.currentMission) {
              this.currentMission = item;
              this.missionStep = 3;
              break;
            }
          }
        }

        if (!this.currentMission) {
          const allRes = await rescuerAPI.getAssignments({ per_page: 100 });
          if (allRes.data && allRes.data.data) {
            const all = allRes.data.data.data || allRes.data.data;
            for (let i = 0; i < all.length; i++) {
              const item = all[i];
              const status = item.trang_thai_nhiem_vu || '';
              if (status === 'DANG_XU_LY') {
                this.currentMission = item;
                this.missionStep = 2;
                break;
              } else if (status === 'DA_DEN_HIEN_TRUONG' && !this.currentMission) {
                this.currentMission = item;
                this.missionStep = 3;
                break;
              }
            }
          }
        }
      } catch (e) {
        console.error("Lỗi tải nhiệm vụ:", e);
      } finally {
        this.loading = false;
        if (!silent) {
          this.updateMapMission();
        }
      }
    },
    async markArrived() {
      if (!this.currentMission) return;
      try {
        await rescuerAPI.updateAssignmentStatus(this.currentMission.id_phan_cong, {
          trang_thai_nhiem_vu: 'DA_DEN_HIEN_TRUONG'
        });
        this.currentMission = { ...this.currentMission, trang_thai_nhiem_vu: 'DA_DEN_HIEN_TRUONG' };
        this.updateMissionStep();
        toaster.success("Đã cập nhật: Đã đến hiện trường");
      } catch (e) {
        console.error("Lỗi cập nhật:", e);
        toaster.error("Không thể cập nhật trạng thái");
      }
    },
    updateMissionStep() {
      const status = this.currentMission?.trang_thai_nhiem_vu || "";
      if (status === "DANG_XU_LY") this.missionStep = 2;
      else if (status === "DA_DEN_HIEN_TRUONG") this.missionStep = 3;
      else this.missionStep = 1;
    },
    openReportModal() {
      this.reportResult = 'success';
      this.reportReason = '';
      this.reportNotes = '';
      this.reportImage = null;
      this.reportImagePreview = null;
      if (this.reportModalEl && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(this.reportModalEl);
        modal.show();
      }
    },
    openReinforceModal() {
      this.reinforceMessage = '';
      if (this.reinforceModalEl && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(this.reinforceModalEl);
        modal.show();
      }
    },
    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 10 * 1024 * 1024) {
          toaster.error("File quá lớn. Vui lòng chọn file dưới 10MB.");
          return;
        }
        this.reportImage = file;
        const reader = new FileReader();
        reader.onload = (e) => {
          this.reportImagePreview = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    async submitReport() {
      if (this.reportResult === 'failure' && !this.reportReason.trim() && !this.reportNotes.trim()) {
        toaster.error("Vui lòng nhập lý do thất bại");
        return;
      }

      this.submittingReport = true;
      try {
        const formData = new FormData();
        formData.append('id_phan_cong', this.currentMission.id_phan_cong);
        formData.append('ket_qua', this.reportResult === 'success' ? 'HOAN_THANH' : 'THAT_BAI');
        if (this.reportResult === 'failure') {
          formData.append('ly_do_that_bai', this.reportReason || this.reportNotes);
        }
        formData.append('bao_cao_hien_truong', this.reportNotes || '');
        if (this.reportImage) {
          formData.append('hinh_anh', this.reportImage);
        }

        // Use new guiBaoCao API (Issue #4)
        await rescuerAPI.guiBaoCao(formData);

        if (this.reportModalEl && typeof bootstrap !== 'undefined') {
          const modal = bootstrap.Modal.getInstance(this.reportModalEl);
          if (modal) modal.hide();
        }

        toaster.success("Đã gửi báo cáo kết quả");
        this.$router.push("/rescuer/da-xu-ly");
      } catch (e) {
        console.error("Lỗi gửi báo cáo:", e);
        toaster.error("Không thể gửi báo cáo. Vui lòng thử lại.");
      } finally {
        this.submittingReport = false;
      }
    },
    async submitReinforce() {
      if (!this.reinforceMessage.trim()) {
        toaster.error("Vui lòng nhập nội dung yêu cầu tiếp viện");
        return;
      }
      this.submittingReinforce = true;
      try {
        toaster.success("Đã gửi yêu cầu tiếp viện. Quản trị viên sẽ được thông báo.");
        if (this.reinforceModalEl && typeof bootstrap !== 'undefined') {
          const modal = bootstrap.Modal.getInstance(this.reinforceModalEl);
          if (modal) modal.hide();
        }
      } catch (e) {
        console.error("Lỗi gửi yêu cầu:", e);
        toaster.error("Không thể gửi yêu cầu tiếp viện");
      } finally {
        this.submittingReinforce = false;
      }
    },
    initMap() {
      if (typeof L === 'undefined') {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = () => this.initLeafletMap();
        document.head.appendChild(script);
      } else {
        this.initLeafletMap();
      }
    },
    initLeafletMap() {
      const defaultCenter = [16.0544, 108.2022];
      this.map = L.map('rescuer-dangxuly-map').setView(defaultCenter, 14);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
      }).addTo(this.map);

      if (this.teamLat && this.teamLng) {
        this.addTeamMarker(this.teamLat, this.teamLng);
      } else {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition((pos) => {
            this.teamLat = pos.coords.latitude;
            this.teamLng = pos.coords.longitude;
            this.addTeamMarker(this.teamLat, this.teamLng);
            this.map.setView([this.teamLat, this.teamLng], 14);
          }, () => {
            this.addTeamMarker(defaultCenter[0], defaultCenter[1]);
          });
        }
      }

      this.updateMapMission();
    },
    addTeamMarker(lat, lng) {
      if (this.teamMarker) this.map.removeLayer(this.teamMarker);
      const icon = L.divIcon({
        html: '<div style="width:20px;height:20px;background:#2563eb;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3)"></div>',
        className: '', iconSize: [20, 20], iconAnchor: [10, 10],
      });
      this.teamMarker = L.marker([lat, lng], { icon: icon }).addTo(this.map);
      this.teamMarker.bindPopup('<b>Vị trí đội của bạn</b>');
    },
    updateMapMission() {
      if (!this.map || !this.currentMission) return;

      const lat = this.currentMission.yeu_cau && this.currentMission.yeu_cau.vi_tri_lat;
      const lng = this.currentMission.yeu_cau && this.currentMission.yeu_cau.vi_tri_lng;

      if (lat && lng) {
        if (this.requestMarker) this.map.removeLayer(this.requestMarker);
        const sev = this.currentMission.yeu_cau && this.currentMission.yeu_cau.muc_do_khan_cap ? this.currentMission.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
        let color = '#dc2626';
        if (sev === 'CRITICAL') color = '#7f1d1d';
        else if (sev === 'HIGH') color = '#dc2626';
        else if (sev === 'MEDIUM') color = '#f59e0b';
        else color = '#16a34a';

        const icon = L.divIcon({
          html: `<div style="width:32px;height:32px;background:${color};border-radius:50%;border:3px solid white;box-shadow:0 2px 10px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;color:white;font-size:14px;font-weight:bold;">!</div>`,
          className: '', iconSize: [32, 32], iconAnchor: [16, 16],
        });
        this.requestMarker = L.marker([lat, lng], { icon: icon }).addTo(this.map);

        const typeName = this.getIncidentTypeName(this.currentMission);
        const address = this.getRequestAddress(this.currentMission);
        this.requestMarker.bindPopup(`
          <div>
            <h6 style="margin:0 0 4px;font-weight:bold">${typeName}</h6>
            <p style="margin:0;font-size:12px;color:#64748b"><i class="bi bi-geo-alt"></i> ${address || 'Chưa có địa chỉ'}</p>
          </div>
        `);

        if (this.teamLat && this.teamLng) {
          if (this.routeLine) this.map.removeLayer(this.routeLine);
          this.routeLine = L.polyline([[this.teamLat, this.teamLng], [lat, lng]], {
            color: '#2563eb', weight: 4, opacity: 0.7, dashArray: '10, 10',
          }).addTo(this.map);
        }

        this.map.flyTo([lat, lng], 15);
      }
    },
    zoomIn() { if (this.map) this.map.zoomIn(); },
    zoomOut() { if (this.map) this.map.zoomOut(); },
    locateMe() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
          this.teamLat = pos.coords.latitude;
          this.teamLng = pos.coords.longitude;
          this.addTeamMarker(this.teamLat, this.teamLng);
          this.map.setView([this.teamLat, this.teamLng], 15);
        });
      }
    },
    formatTime(dateStr) {
      if (!dateStr) return '-';
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return dateStr;
      return d.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
    },
    getPriorityBadge() {
      const mucDo = this.currentMission && this.currentMission.yeu_cau && this.currentMission.yeu_cau.muc_do_khan_cap ? this.currentMission.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
      if (mucDo === 'CRITICAL' || mucDo === 'HIGH') return 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
      if (mucDo === 'MEDIUM') return 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25';
      return 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
    },
    getPriorityText() {
      const mucDo = this.currentMission && this.currentMission.yeu_cau && this.currentMission.yeu_cau.muc_do_khan_cap ? this.currentMission.yeu_cau.muc_do_khan_cap.toUpperCase() : '';
      if (mucDo === 'CRITICAL' || mucDo === 'HIGH') return 'KHẨN CẤP';
      if (mucDo === 'MEDIUM') return 'TRUNG BÌNH';
      return 'THƯỜNG';
    },
    getIncidentTypeName(item) {
      if (item && item.yeu_cau && item.yeu_cau.loai_su_co) {
        return item.yeu_cau.loai_su_co.ten_danh_muc || item.yeu_cau.loai_su_co.ten_loai_su_co || 'Yêu cầu cứu hộ';
      }
      return 'Yêu cầu cứu hộ';
    },
    getRequestAddress(item) {
      if (item && item.yeu_cau) {
        if (item.yeu_cau.vi_tri_dia_chi) return item.yeu_cau.vi_tri_dia_chi;
        if (item.yeu_cau.dia_chi) return item.yeu_cau.dia_chi;
        if (item.yeu_cau.vi_tri) return item.yeu_cau.vi_tri;
      }
      return 'Chưa có địa chỉ';
    },
    getReporterName(item) {
      if (item && item.yeu_cau) {
        if (item.yeu_cau.ho_ten_nguoi_dung) return item.yeu_cau.ho_ten_nguoi_dung;
        if (item.yeu_cau.hoTenNguoiDung) return item.yeu_cau.hoTenNguoiDung;
        if (item.yeu_cau.nguoi_dung) {
          return item.yeu_cau.nguoi_dung.ho_ten || item.yeu_cau.nguoi_dung.hoTen || 'Không rõ';
        }
      }
      return 'Không rõ';
    },
    getReporterPhone(item) {
      if (item && item.yeu_cau) {
        if (item.yeu_cau.so_dien_thoai_nguoi_dung) return item.yeu_cau.so_dien_thoai_nguoi_dung;
        if (item.yeu_cau.soDienThoaiNguoiDung) return item.yeu_cau.soDienThoaiNguoiDung;
        if (item.yeu_cau.nguoi_dung) {
          return item.yeu_cau.nguoi_dung.so_dien_thoai || item.yeu_cau.nguoi_dung.soDienThoai || '';
        }
      }
      return '';
    },
  },
};
</script>

<style scoped>
.processing-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px);
  overflow: hidden;
  font-family: 'Inter', 'Roboto', sans-serif;
}

.detail-panel {
  width: 100%;
  max-width: 480px;
  border-right: 1px solid rgba(0,0,0,0.05);
}

@media (max-width: 991.98px) {
  .detail-panel {
    height: 50%;
    max-width: 100%;
    border-right: none;
    border-bottom: 1px solid rgba(0,0,0,0.1);
  }
}

.detail-body {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
}

.hover-bg-gray:hover { background-color: #f3f4f6 !important; }
.hover-lift:hover { transform: translateY(-2px); }
.shadow-danger { box-shadow: 0 8px 16px rgba(239, 68, 68, 0.25) !important; }

.map-area { position: relative; }

.pulse-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  display: inline-block;
  animation: pulse 1.5s infinite;
}

.pulse-indicator {
  width: 10px; height: 10px;
  border-radius: 50%;
  box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(239, 68, 69, 0.7); }
  70% { box-shadow: 0 0 0 8px rgba(239, 68, 69, 0); }
  100% { box-shadow: 0 0 0 0 rgba(239, 68, 69, 0); }
}

.step-icon {
  width: 36px; height: 36px;
  position: relative; z-index: 1;
}

.btn-map-control {
  width: 40px; height: 40px;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.btn-map-control:hover { background-color: #f8f9fa !important; transform: translateX(-2px); }
</style>
