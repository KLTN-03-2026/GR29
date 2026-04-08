<template>
  <div class="d-flex flex-column flex-grow-1 overflow-hidden bg-light">
    <div class="container-fluid p-0 d-flex flex-column flex-lg-row flex-grow-1 overflow-hidden" style="height: calc(100vh - 140px);">
      
      <div class="col-lg-4 col-xl-3 bg-white border-end d-flex flex-column shadow-sm">
        <div class="p-3 border-bottom bg-light">
          <h6 class="fw-bold mb-0 text-uppercase small text-secondary">
            <i class="bi bi-asterisk text-danger me-2"></i>Danh sách yêu cầu cứu hộ
          </h6>
          <p v-if="hasActiveMission" class="small text-muted mb-0 mt-2">
            Đang xử lý 1 nhiệm vụ.
          </p>
        </div>

        <div v-if="gpsBanner" class="px-3 pt-2">
          <div class="alert alert-warning py-2 px-3 small mb-0 d-flex align-items-start gap-2">
            <i class="bi bi-geo-alt-fill flex-shrink-0 mt-1"></i>
            <div>
              Bật <strong>định vị (GPS)</strong> trên thiết bị và cho phép trình duyệt truy cập vị trí để chỉ đường và cập nhật tọa độ đội.
              <button type="button" class="btn btn-sm btn-outline-dark ms-2" @click="gpsBanner = false">Đã hiểu</button>
            </div>
          </div>
        </div>
        
        <div class="flex-grow-1 overflow-y-auto p-3 d-flex flex-column gap-3">
          <div v-if="loading" class="text-muted small">Đang tải danh sách nhiệm vụ...</div>
          <div v-else-if="missions.length === 0" class="text-muted small">Chưa có nhiệm vụ cho đội này.</div>
          <div
            v-for="task in missions"
            :key="task.id"
            class="card border-0 shadow-sm rounded-4 hover-shadow transition-all cursor-pointer"
            :class="{ 'border border-primary': selectedAssignmentId === task.id }"
            @click="chonNhiemVu(task)"
          >
            <div class="card-body p-3">
              <div class="d-flex justify-content-between mb-2">
                <span :class="['badge rounded-pill px-2 py-1', task.priorityBg]">{{ task.priorityText }}</span>
                <span class="text-muted small fw-bold">{{ task.distance }}</span>
              </div>
              <h6 class="fw-bold mb-1">{{ task.type }}</h6>
              <p class="text-muted mb-3" style="font-size: 11px;"><i class="bi bi-clock me-1"></i>{{ task.time }}</p>
              <button
                class="btn w-100 fw-bold btn-sm rounded-3 py-2"
                :class="task.trangThaiNhiemVu === 'MOI' ? 'btn-dark' : 'btn-success'"
                :disabled="task.trangThaiNhiemVu !== 'MOI' || accepting || hasActiveMission"
                @click.stop="tiepNhanNhiemVu(task)"
              >
                {{
                  task.trangThaiNhiemVu === "MOI"
                    ? "TIẾP NHẬN"
                    : "ĐANG XỬ LÝ"
                }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-8 col-xl-9 position-relative bg-secondary bg-opacity-10 p-0">
        <div class="w-100 h-100 bg-white position-relative">
          <MapboxMap
            ref="mapRef"
            class="w-100 h-100"
            :center="mapCenter"
            :zoom="14"
            :user-lng-lat="userLngLat"
            :destination-lng-lat="destinationLngLat"
            :show-marker="showLegacySingleMarker"
          />
          <div
            v-if="routeSummary"
            class="route-nav-pill position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill shadow-sm bg-white border d-flex align-items-center gap-2"
          >
            <i class="bi bi-car-front-fill text-primary fs-5"></i>
            <span class="fw-semibold text-dark">{{ routeSummary.durationText }}</span>
            <span class="text-muted">·</span>
            <span class="text-secondary">{{ routeSummary.distanceText }}</span>
          </div>
        </div>

        <div class="position-absolute top-0 end-0 m-3 col-11 col-md-5 col-lg-4">
          <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-body p-3">
              <div class="d-flex align-items-center mb-3">
                <div class="bg-danger bg-opacity-10 p-2 rounded-3 me-3 text-danger">
                  <i class="bi bi-file-earmark-text-fill fs-5"></i>
                </div>
                <div>
                  <div class="fw-bold small text-muted text-uppercase" style="font-size: 9px;">Nhiệm vụ hiện tại</div>
                  <div class="text-success fw-bold small">
                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                    {{ currentMission ? currentMission.statusLabel : "CHƯA CÓ" }}
                  </div>
                </div>
              </div>

              <div class="bg-light rounded-3 p-3 mb-3">
                <div class="text-muted x-small fw-bold">NẠN NHÂN</div>
                <div class="fw-bold mb-2">{{ currentMission?.nguoiDan || "Chưa rõ" }}</div>
                <div class="text-muted x-small fw-bold">SỐ ĐIỆN THOẠI</div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-danger">{{ currentMission?.sdtNguoiDan || "—" }}</span>
                  <a :href="`tel:${currentMission?.sdtNguoiDan || ''}`" class="btn btn-white btn-sm shadow-sm border rounded-circle"><i class="bi bi-telephone-fill text-muted"></i></a>
                </div>
              </div>

              <button class="btn btn-danger w-100 py-3 fw-bold rounded-3 shadow-sm border-0" @click="chiDuong" :disabled="!selectedMission">
                <i class="bi bi-send-fill me-2"></i> CHỈ ĐƯỜNG ĐẾN HIỆN TRƯỜNG
              </button>
            </div>
          </div>
        </div>

        <div class="position-absolute bottom-0 end-0 m-3 d-flex flex-column gap-2 shadow-sm">
          <button class="btn btn-white bg-white p-2 border-0 rounded-top shadow-none"><i class="bi bi-plus-lg"></i></button>
          <button class="btn btn-white bg-white p-2 border-0 rounded-0 border-top border-bottom shadow-none"><i class="bi bi-dash-lg"></i></button>
          <button class="btn btn-white bg-white p-2 border-0 rounded-bottom shadow-none text-danger"><i class="bi bi-crosshair"></i></button>
        </div>
      </div>
    </div>

    <div class="bg-white border-top py-3 px-4 shadow-lg mt-auto">
      <div class="container-fluid">
        <div class="row g-3 justify-content-center">
          <div class="col-12 col-md-4 col-lg-3">
            <button class="btn btn-danger w-100 py-3 fw-bold rounded-3 shadow border-0" @click="danhDauDaDen" :disabled="!currentMission">
              <i class="bi bi-geo-alt-fill me-2"></i> ĐÃ ĐẾN HIỆN TRƯỜNG
            </button>
          </div>
          <div class="col-12 col-md-4 col-lg-3">
            <button class="btn btn-warning w-100 py-3 fw-bold rounded-3 shadow border-0 text-dark" @click="yeuCauHoTro" :disabled="!currentMission">
              <i class="bi bi-megaphone-fill me-2"></i> YÊU CẦU HỖ TRỢ
            </button>
          </div>
          <div class="col-12 col-md-4 col-lg-3">
            <button class="btn btn-success w-100 py-3 fw-bold rounded-3 shadow border-0" @click="denTrangBaoCao" :disabled="!currentMission">
              <i class="bi bi-check-circle-fill me-2"></i> HOÀN THÀNH NHIỆM VỤ
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: yêu cầu bổ sung tài nguyên -->
    <div class="modal fade" id="supportModal" tabindex="-1" ref="supportModalEl">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title fw-bold mb-0">Yêu cầu bổ sung tài nguyên</h5>
              <small class="text-muted">
                Đội #{{ teamId }} · Phân công #{{ currentMission?.idPhanCong || "—" }}
              </small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label small fw-semibold">Mức độ khẩn cấp</label>
              <select v-model="supportForm.muc_do_khan_cap" class="form-select">
                <option value="LOW">Thấp</option>
                <option value="MEDIUM">Trung bình</option>
                <option value="HIGH">Cao</option>
                <option value="CRITICAL">Khẩn cấp</option>
              </select>
            </div>

            <div class="mb-2 d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Chọn tài nguyên &amp; số lượng cần bổ sung</div>
              <button type="button" class="btn btn-sm btn-outline-secondary" @click="taiTaiNguyen" :disabled="supportLoading">
                <i class="bi bi-arrow-repeat me-1"></i>Tải lại
              </button>
            </div>

            <div v-if="supportLoading" class="text-muted small py-3">Đang tải danh sách tài nguyên...</div>
            <div v-else class="table-responsive border rounded-3 mb-3">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light small text-muted text-uppercase">
                  <tr>
                    <th style="width: 32px"></th>
                    <th>Tài nguyên</th>
                    <th>Loại</th>
                    <th class="text-end">Đang có</th>
                    <th style="width: 140px" class="text-end">Cần bổ sung</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in teamResources" :key="r.id_tai_nguyen">
                    <td>
                      <input class="form-check-input" type="checkbox" v-model="supportPicked[r.id_tai_nguyen]" />
                    </td>
                    <td class="fw-semibold">{{ r.ten_tai_nguyen }}</td>
                    <td class="text-muted">{{ r.loai_tai_nguyen || "—" }}</td>
                    <td class="text-end">{{ r.so_luong }}</td>
                    <td class="text-end">
                      <input
                        type="number"
                        min="0"
                        class="form-control form-control-sm text-end"
                        style="max-width: 120px; margin-left: auto;"
                        v-model.number="supportQty[r.id_tai_nguyen]"
                        :disabled="!supportPicked[r.id_tai_nguyen]"
                        placeholder="0"
                      />
                    </td>
                  </tr>
                  <tr v-if="!teamResources.length">
                    <td colspan="5" class="text-center text-muted py-3">Chưa có tài nguyên cho đội.</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Ghi chú thêm (tuỳ chọn)</label>
              <textarea v-model="supportForm.ghi_chu" class="form-control" rows="3" placeholder="VD: Thiếu bình oxygen do có nhiều nạn nhân..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-warning text-dark fw-bold" @click="guiYeuCauBoSung" :disabled="supportSending">
              <i class="bi bi-send-fill me-2"></i>Gửi yêu cầu
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";
import { createToaster } from "@meforma/vue-toaster";
import { assignmentAPI, rescueRequestAPI, rescueTeamAPI, resourceRequestAPI } from "../../../services/api";

const toaster = createToaster({ position: "top-right" });

export default {
  components: { MapboxMap },
  data() {
    return {
      loading: false,
      accepting: false,
      teamId: Number(localStorage.getItem("rescuer_team_id") || 1),
      assignments: [],
      selectedAssignmentId: null,
      mapCenter: [108.2022, 16.0544],
      /** [lng, lat] — vị trí GPS cứu hộ viên (chấm xanh trên bản đồ) */
      userLngLat: null,
      /** Toast “đã đồng bộ vị trí” chỉ 1 lần / phiên (sessionStorage) */
      locationToastStorageKey: "rescuer_team_location_toast_once",
      /** Cập nhật vị trí đội lên server mỗi 10 giây */
      locationPingTimer: null,
      /** Nhắc bật GPS (ẩn khi user bấm Đã hiểu) */
      gpsBanner: true,
      /** Ước lượng thời gian / quãng đường (OSRM), hiển thị nhãn kiểu Google Maps */
      routeSummary: null,

      // support modal
      supportModal: null,
      supportLoading: false,
      supportSending: false,
      teamResources: [],
      supportPicked: {},
      supportQty: {},
      supportForm: {
        muc_do_khan_cap: "MEDIUM",
        ghi_chu: "",
      },
    };
  },
  computed: {
    /** Chỉ 1 nhiệm vụ DANG_XU_LY / đội tại một thời điểm */
    hasActiveMission() {
      return this.assignments.some((a) => String(a.trang_thai_nhiem_vu || "").toUpperCase() === "DANG_XU_LY");
    },
    missions() {
      const rows = this.assignments.filter((a) =>
        ["MOI", "DANG_XU_LY"].includes(String(a.trang_thai_nhiem_vu || "").toUpperCase())
      );
      const active = rows.find((a) => String(a.trang_thai_nhiem_vu || "").toUpperCase() === "DANG_XU_LY");
      if (active) {
        return [this.mapAssignmentToCard(active)];
      }
      return rows
        .filter((a) => String(a.trang_thai_nhiem_vu || "").toUpperCase() === "MOI")
        .map((a) => this.mapAssignmentToCard(a));
    },
    currentMission() {
      const picked = this.assignments.find((a) => String(a.trang_thai_nhiem_vu || "").toUpperCase() === "DANG_XU_LY");
      if (!picked) return null;
      const yc = picked.yeu_cau || picked.yeuCau || {};
      return {
        idPhanCong: picked.id_phan_cong,
        idYeuCau: picked.id_yeu_cau || yc.id_yeu_cau,
        statusLabel: "ĐANG XỬ LÝ",
        nguoiDan: yc.nguoi_dung?.ho_ten || yc.nguoiDung?.ho_ten || "Nạn nhân",
        sdtNguoiDan: yc.nguoi_dung?.so_dien_thoai || yc.nguoiDung?.so_dien_thoai || "",
      };
    },
    selectedMission() {
      if (!this.selectedAssignmentId) return null;
      return this.assignments.find((a) => Number(a.id_phan_cong) === Number(this.selectedAssignmentId)) || null;
    },
    /** Hiện trường — chấm đỏ */
    destinationLngLat() {
      const a = this.selectedMission;
      if (!a) return null;
      const yc = a.yeu_cau || a.yeuCau || {};
      const lng = parseFloat(yc.vi_tri_lng);
      const lat = parseFloat(yc.vi_tri_lat);
      if (Number.isNaN(lng) || Number.isNaN(lat)) return null;
      return [lng, lat];
    },
    /** Một marker đỏ tại center (chỉ khi chưa có đích / chưa có GPS — giống trang khác) */
    showLegacySingleMarker() {
      return !this.destinationLngLat && !this.userLngLat;
    },
  },
  async mounted() {
    await this.taiNhiemVu();
    this.kiemTraQuyenGPS();
    this.batCapNhatViTriDinhKy();
    this.$nextTick(() => this.initSupportModal());
  },
  beforeUnmount() {
    if (this.locationPingTimer) {
      clearInterval(this.locationPingTimer);
      this.locationPingTimer = null;
    }
  },
  methods: {
    initSupportModal() {
      const el = this.$refs.supportModalEl;
      const B = typeof window !== "undefined" && window.bootstrap;
      if (el && B?.Modal) {
        this.supportModal = new B.Modal(el);
      }
    },
    mapAssignmentToCard(a) {
      const yc = a.yeu_cau || a.yeuCau || {};
      const urgency = String(yc.muc_do_khan_cap || "").toUpperCase();
      const urgencyMap = {
        CRITICAL: { text: "KHẨN CẤP", cls: "bg-danger text-white" },
        HIGH: { text: "CAO", cls: "bg-danger bg-opacity-10 text-danger" },
        MEDIUM: { text: "TRUNG BÌNH", cls: "bg-warning bg-opacity-10 text-warning" },
        LOW: { text: "THẤP", cls: "bg-info bg-opacity-10 text-info" },
      };
      const u = urgencyMap[urgency] || { text: urgency || "—", cls: "bg-secondary bg-opacity-10 text-secondary" };
      return {
        id: a.id_phan_cong,
        idYeuCau: a.id_yeu_cau || yc.id_yeu_cau,
        type: yc.loai_su_co?.ten_danh_muc || yc.loaiSuCo?.ten_danh_muc || "Yêu cầu cứu hộ",
        priorityText: u.text,
        priorityBg: u.cls,
        distance: yc.vi_tri_dia_chi ? String(yc.vi_tri_dia_chi).slice(0, 36) + (String(yc.vi_tri_dia_chi).length > 36 ? "…" : "") : "—",
        time: a.created_at ? new Date(a.created_at).toLocaleString("vi-VN") : "Vừa cập nhật",
        trangThaiNhiemVu: String(a.trang_thai_nhiem_vu || "").toUpperCase(),
      };
    },
    async taiNhiemVu() {
      this.loading = true;
      try {
        const res = await assignmentAPI.getByTeam(this.teamId, { per_page: 100 });
        this.assignments = Array.isArray(res.data?.data) ? res.data.data : [];
        const current = this.assignments.find((a) => String(a.trang_thai_nhiem_vu || "").toUpperCase() === "DANG_XU_LY");
        if (current) {
          this.selectedAssignmentId = current.id_phan_cong;
          this.setMapFromAssignment(current);
        } else {
          const firstMoi = this.assignments.find((a) => String(a.trang_thai_nhiem_vu || "").toUpperCase() === "MOI");
          if (firstMoi) {
            this.selectedAssignmentId = firstMoi.id_phan_cong;
            this.setMapFromAssignment(firstMoi);
          } else {
            this.selectedAssignmentId = null;
          }
        }
      } catch (e) {
        console.error(e);
        toaster.error("Không tải được nhiệm vụ của đội. Kiểm tra lại teamId hoặc token.");
      } finally {
        this.loading = false;
      }
    },
    setMapFromAssignment(assignment) {
      this.routeSummary = null;
      const yc = assignment.yeu_cau || assignment.yeuCau || {};
      const lng = parseFloat(yc.vi_tri_lng);
      const lat = parseFloat(yc.vi_tri_lat);
      if (!Number.isNaN(lng) && !Number.isNaN(lat)) {
        this.mapCenter = [lng, lat];
        this.$nextTick(() => {
          this.$refs.mapRef?.clearRoute?.();
          this.$refs.mapRef?.flyTo(lng, lat, 14);
        });
      }
    },
    formatRouteMeta(distanceM, durationSec) {
      const distanceText =
        distanceM >= 1000 ? `${(distanceM / 1000).toFixed(1)} km` : `${Math.round(distanceM)} m`;
      const minTotal = Math.max(1, Math.round(durationSec / 60));
      let durationText;
      if (minTotal < 60) {
        durationText = `${minTotal} phút`;
      } else {
        const h = Math.floor(minTotal / 60);
        const m = minTotal % 60;
        durationText = m ? `${h} giờ ${m} phút` : `${h} giờ`;
      }
      return { distanceText, durationText };
    },
    /** Khoảng cách đại cương (m) — dùng khi không gọi được OSRM */
    haversineMeters(lat1, lng1, lat2, lng2) {
      const R = 6371000;
      const rad = (d) => (d * Math.PI) / 180;
      const dLat = rad(lat2 - lat1);
      const dLng = rad(lng2 - lng1);
      const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(dLng / 2) ** 2;
      return 2 * R * Math.asin(Math.sqrt(a));
    },
    async kiemTraQuyenGPS() {
      if (!navigator.geolocation) {
        toaster.warning("Trình duyệt không hỗ trợ định vị. Dùng trình duyệt khác hoặc cập nhật phiên bản.", { duration: 6000 });
        return;
      }
      try {
        const perm = navigator.permissions && (await navigator.permissions.query({ name: "geolocation" }));
        if (perm && perm.state === "denied") {
          toaster.error("Quyền vị trí đang bị chặn. Vào cài đặt trình duyệt → Quyền → Vị trí → Cho phép cho trang này.", { duration: 9000 });
        }
      } catch {
        /* Permissions API không khả dụng trên một số trình duyệt — dùng banner GPS trong giao diện */
      }
    },
    async tiepNhanNhiemVu(task) {
      if (this.hasActiveMission) {
        toaster.warning("Đội đang xử lý một nhiệm vụ. Hoàn thành trước khi nhận thêm.");
        return;
      }
      this.accepting = true;
      try {
        await assignmentAPI.changeStatus(task.id, { trang_thai_nhiem_vu: "DANG_XU_LY" });
        if (task.idYeuCau) {
          await rescueRequestAPI.changeStatus(task.idYeuCau, { trang_thai: "DANG_XU_LY" });
        }
        toaster.success("Đã ghi nhận đội tiếp nhận nhiệm vụ và chuyển sang ĐANG_XỬ_LÝ.");
        await this.taiNhiemVu();
      } catch (e) {
        console.error(e);
        toaster.error(e?.response?.data?.message || "Không thể tiếp nhận nhiệm vụ.");
      } finally {
        this.accepting = false;
      }
    },
    danhDauDaDen() {
      toaster.success("Đã ghi nhận trạng thái: đội đã đến hiện trường.");
    },
    chonNhiemVu(task) {
      this.selectedAssignmentId = task.id;
      const assignment = this.assignments.find((a) => Number(a.id_phan_cong) === Number(task.id));
      if (assignment) this.setMapFromAssignment(assignment);
    },
    async dongBoViTriLenServer(lat, lng) {
      try {
        await rescueTeamAPI.addLocation(this.teamId, { vi_tri_lat: lat, vi_tri_lng: lng });
        if (!sessionStorage.getItem(this.locationToastStorageKey)) {
          toaster.success("Đã đồng bộ vị trí đội lên hệ thống.");
          sessionStorage.setItem(this.locationToastStorageKey, "1");
        }
      } catch (e) {
        console.error(e);
        toaster.warning("Chưa lưu được vị trí đội lên máy chủ. Kiểm tra kết nối hoặc đăng nhập rescuer.");
      }
    },
    /** Định kỳ 10s: cập nhật chấm xanh + API (toast thành công chỉ 1 lần / phiên) */
    batCapNhatViTriDinhKy() {
      if (this.locationPingTimer) clearInterval(this.locationPingTimer);
      const tick = () => {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(
          (pos) => {
            const lat = Number(pos.coords.latitude);
            const lng = Number(pos.coords.longitude);
            this.userLngLat = [lng, lat];
            this.dongBoViTriLenServer(lat, lng);
          },
          () => {},
          { enableHighAccuracy: true, maximumAge: 8000, timeout: 12000 }
        );
      };
      setTimeout(tick, 500);
      this.locationPingTimer = setInterval(tick, 10000);
    },
    /**
     * OSRM: tuyến chính + tuyến phụ (alternatives), thời gian & quãng đường gợi ý.
     * Gần giống Google Maps (đường xanh đậm + nhánh phụ nhạt).
     */
    async layDuongDiOSRM(fromLng, fromLat, toLng, toLat) {
      const url = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson&alternatives=true`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.code !== "Ok" || !data.routes?.[0]?.geometry?.coordinates?.length) {
        throw new Error("OSRM");
      }
      const routes = data.routes;
      const primary = routes[0].geometry.coordinates;
      const alternatives = routes
        .slice(1)
        .map((r) => r.geometry?.coordinates)
        .filter((c) => Array.isArray(c) && c.length > 1);
      return {
        primary,
        alternatives,
        durationSec: routes[0].duration,
        distanceM: routes[0].distance,
      };
    },
    async chiDuong() {
      if (!this.selectedMission) return;
      const dest = this.destinationLngLat;
      if (!dest || dest.length < 2) {
        toaster.warning("Thiếu tọa độ hiện trường trên bản đồ.");
        return;
      }
      if (!navigator.geolocation) {
        toaster.warning("Thiết bị không hỗ trợ GPS.");
        return;
      }
      navigator.geolocation.getCurrentPosition(
        async (pos) => {
          const lat = Number(pos.coords.latitude);
          const lng = Number(pos.coords.longitude);
          this.userLngLat = [lng, lat];
          await this.dongBoViTriLenServer(lat, lng);

          const mapRef = this.$refs.mapRef;
          let routePayload;
          try {
            routePayload = await this.layDuongDiOSRM(lng, lat, dest[0], dest[1]);
          } catch {
            const distM = this.haversineMeters(lat, lng, dest[1], dest[0]);
            const speedMs = 30 / 3.6;
            routePayload = {
              primary: [
                [lng, lat],
                [dest[0], dest[1]],
              ],
              alternatives: [],
              durationSec: distM / speedMs,
              distanceM: distM,
            };
          }
          this.routeSummary = this.formatRouteMeta(routePayload.distanceM, routePayload.durationSec);
          await this.$nextTick();
          await new Promise((resolve) => setTimeout(resolve, 200));
          mapRef?.clearRoute?.();
          mapRef?.setRouteData?.({
            primary: routePayload.primary,
            alternatives: routePayload.alternatives,
          });
          mapRef?.fitBounds?.(
            [
              [lng, lat],
              [dest[0], dest[1]],
            ],
            80,
            15
          );
        },
        (err) => {
          if (err?.code === 1) {
            toaster.error("Quyền vị trí bị từ chối. Mở cài đặt trình duyệt → Quyền riêng tư → Cho phép vị trí cho localhost.", { duration: 8000 });
          } else if (err?.code === 2) {
            toaster.warning("Không lấy được vị trí (tín hiệu yếu). Hãy bật GPS trên thiết bị.");
          } else {
            toaster.warning("Không lấy được GPS. Hãy bật định vị và thử lại.");
          }
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
      );
    },
    yeuCauHoTro() {
      if (!this.supportModal) this.initSupportModal();
      this.supportForm = { muc_do_khan_cap: "MEDIUM", ghi_chu: "" };
      this.supportPicked = {};
      this.supportQty = {};
      this.supportModal?.show();
      this.taiTaiNguyen();
    },
    async taiTaiNguyen() {
      this.supportLoading = true;
      try {
        const res = await rescueTeamAPI.getResources(this.teamId, { per_page: 200 });
        const pag = res.data?.data;
        const items = Array.isArray(pag?.data) ? pag.data : [];
        this.teamResources = items.filter((x) => Number(x.trang_thai) !== 0);
        // init quantities
        this.teamResources.forEach((r) => {
          if (this.supportQty[r.id_tai_nguyen] === undefined) this.supportQty[r.id_tai_nguyen] = 0;
          if (this.supportPicked[r.id_tai_nguyen] === undefined) this.supportPicked[r.id_tai_nguyen] = false;
        });
      } catch (e) {
        console.error(e);
        this.teamResources = [];
        toaster.error("Không tải được tài nguyên của đội.");
      } finally {
        this.supportLoading = false;
      }
    },
    buildSupportContent() {
      const lines = [];
      for (const r of this.teamResources) {
        if (!this.supportPicked[r.id_tai_nguyen]) continue;
        const qty = Number(this.supportQty[r.id_tai_nguyen] || 0);
        if (!qty || qty <= 0) continue;
        const type = r.loai_tai_nguyen ? ` (${r.loai_tai_nguyen})` : "";
        lines.push(`- ${r.ten_tai_nguyen}${type}: +${qty} (đang có ${r.so_luong})`);
      }
      const note = (this.supportForm.ghi_chu || "").trim();
      if (note) {
        lines.push("", `Ghi chú: ${note}`);
      }
      return lines.join("\n").trim();
    },
    async guiYeuCauBoSung() {
      if (!this.currentMission?.idPhanCong) {
        toaster.warning("Chỉ gửi yêu cầu bổ sung khi đang có nhiệm vụ đang xử lý.");
        return;
      }
      const content = this.buildSupportContent();
      if (!content) {
        toaster.warning("Chọn ít nhất 1 tài nguyên và nhập số lượng cần bổ sung.");
        return;
      }
      this.supportSending = true;
      try {
        await resourceRequestAPI.create({
          id_doi_cuu_ho: this.teamId,
          id_phan_cong: this.currentMission.idPhanCong,
          id_yeu_cau: this.currentMission.idYeuCau || undefined,
          noi_dung: content,
          muc_do_khan_cap: this.supportForm.muc_do_khan_cap,
        });
        toaster.success("Đã gửi yêu cầu bổ sung tài nguyên.");
        this.supportModal?.hide();
      } catch (e) {
        console.error(e);
        toaster.error(e?.response?.data?.message || "Gửi yêu cầu thất bại.");
      } finally {
        this.supportSending = false;
      }
    },
    denTrangBaoCao() {
      if (!this.currentMission) return;
      this.$router.push({
        path: "/rescuer/bao-cao",
        query: {
          id_phan_cong: this.currentMission.idPhanCong,
          id_yeu_cau: this.currentMission.idYeuCau,
        },
      });
    },
  },
};
</script>

<style scoped>
.x-small { font-size: 10px; }
.hover-shadow:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
.transition-all { transition: all 0.2s ease-in-out; }
.cursor-pointer { cursor: pointer; }
.route-nav-pill {
  z-index: 5;
  font-size: 0.95rem;
  pointer-events: none;
}
</style>
