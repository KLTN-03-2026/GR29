<template>
  <div>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h4 class="mb-1 fw-semibold">Dieu phoi doi cuu ho</h4>
        <p class="text-muted mb-0">Chon yeu cau, doi cuu ho va tao phan cong ngay tai day.</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" @click="$router.push('/admin/queue')">
          <i class="bi bi-arrow-left me-2"></i>Ve hang doi
        </button>
        <button class="btn btn-primary" @click="loadPage" :disabled="loading || submitting">
          <i class="bi bi-arrow-clockwise me-2"></i>Lam moi
        </button>
      </div>
    </div>

    <div v-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-if="loading" class="card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="small text-muted mt-3">Dang tai du lieu dieu phoi...</div>
      </div>
    </div>

    <div v-else class="row g-4">
      <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0">
            <h5 class="mb-1 fw-semibold">Yeu cau</h5>
            <input v-model.trim="requestSearch" class="form-control" placeholder="Tim ma SOS, dia chi..." />
          </div>
          <div class="card-body list-pane">
            <div v-if="filteredRequests.length === 0" class="text-center text-muted py-4">Khong co yeu cau.</div>
            <button
              v-for="r in filteredRequests"
              :key="r.key"
              type="button"
              class="pick-card w-100 text-start"
              :class="{ active: String(r.id) === String(selectedRequestId) }"
              @click="pickRequest(r)"
            >
              <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <strong>SOS-{{ r.id }}</strong>
                <span class="badge rounded-pill" :class="r.statusClass">{{ r.statusLabel }}</span>
              </div>
              <div class="fw-semibold mb-1">{{ r.type }}</div>
              <div class="small text-muted mb-1"><i class="bi bi-geo-alt me-1"></i>{{ r.address }}</div>
              <div class="small text-muted">{{ r.time }}</div>
            </button>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-8">
        <div v-if="!selectedRequest" class="card border-0 shadow-sm">
          <div class="card-body text-center py-5 text-muted">
            Chon mot yeu cau o cot ben trai de bat dau dieu phoi.
          </div>
        </div>

        <template v-else>
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                  <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge text-bg-dark">SOS-{{ selectedRequest.id }}</span>
                    <span class="badge rounded-pill" :class="selectedRequest.statusClass">{{ selectedRequest.statusLabel }}</span>
                    <span class="badge text-bg-light border">{{ selectedRequest.priorityLabel }}</span>
                  </div>
                  <h5 class="mb-1 fw-semibold">{{ selectedRequest.type }}</h5>
                  <p class="text-muted mb-0">{{ selectedRequest.description }}</p>
                </div>
                <div class="small text-muted">
                  <div><i class="bi bi-clock me-1"></i>{{ selectedRequest.time }}</div>
                  <div><i class="bi bi-person me-1"></i>{{ selectedRequest.requester }}</div>
                </div>
              </div>
              <div class="row g-3 small">
                <div class="col-md-6"><div class="info-box"><strong>Dia diem</strong><div>{{ selectedRequest.address }}</div></div></div>
                <div class="col-md-6"><div class="info-box"><strong>Chi tiet</strong><div>{{ selectedRequest.detail }}</div></div></div>
              </div>
              <div v-if="!canCreate" class="alert alert-warning mt-3 mb-0">
                Yeu cau nay da huy hoac hoan thanh, khong the tao phan cong moi.
              </div>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-12 col-xxl-7">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                  <div class="d-flex flex-wrap justify-content-between gap-2">
                    <h5 class="mb-0 fw-semibold">Doi cuu ho</h5>
                    <div class="btn-group btn-group-sm">
                      <button class="btn" :class="teamFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary'" @click="teamFilter='all'">Tat ca</button>
                      <button class="btn" :class="teamFilter === 'available' ? 'btn-primary' : 'btn-outline-secondary'" @click="teamFilter='available'">Co san</button>
                    </div>
                  </div>
                  <input v-model.trim="teamSearch" class="form-control mt-3" placeholder="Tim ten doi, khu vuc..." />
                </div>
                <div class="card-body list-pane">
                  <div v-if="filteredTeams.length === 0" class="text-center text-muted py-4">Khong co doi phu hop.</div>
                  <button
                    v-for="t in filteredTeams"
                    :key="t.id"
                    type="button"
                    class="pick-card w-100 text-start"
                    :class="{ active: isTeamSelected(t.id) }"
                    @click="toggleTeam(t.id)"
                  >
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                      <div class="d-flex align-items-center gap-2">
                        <i class="bi" :class="isTeamSelected(t.id) ? 'bi-check-square-fill text-primary' : 'bi-square text-muted'"></i>
                        <strong>{{ t.name }}</strong>
                      </div>
                      <span class="badge rounded-pill" :class="t.statusClass">{{ t.statusLabel }}</span>
                    </div>
                    <div class="small text-muted mb-1"><i class="bi bi-geo-alt me-1"></i>{{ t.area }}</div>
                    <div class="small text-muted">{{ t.contact }}</div>
                  </button>
                </div>
              </div>
            </div>

            <div class="col-12 col-xxl-5">
              <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                  <h5 class="mb-1 fw-semibold">Tao phan cong</h5>
                  <p class="text-muted small mb-0">Da chon: {{ selectedTeams.length ? selectedTeams.map((t) => t.name).join(', ') : 'Chua chon doi' }}</p>
                </div>
                <div class="card-body">
                  <textarea
                    v-model.trim="assignmentNote"
                    class="form-control"
                    rows="4"
                    placeholder="Ghi chu dieu phoi..."
                  ></textarea>
                  <button class="btn btn-primary w-100 mt-3" :disabled="!canSubmit || submitting" @click="submitAssignment">
                    <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                    <i v-else class="bi bi-send me-2"></i>Tao phan cong
                  </button>
                </div>
              </div>

              <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                  <h5 class="mb-1 fw-semibold">Lich su phan cong</h5>
                  <p class="text-muted small mb-0">{{ assignments.length }} ban ghi</p>
                </div>
                <div class="card-body">
                  <div v-if="assignmentLoading" class="text-center text-muted py-4">Dang tai lich su...</div>
                  <div v-else-if="assignments.length === 0" class="text-center text-muted py-4">Chua co phan cong nao.</div>
                  <div v-else class="vstack gap-3">
                    <div v-for="a in assignments" :key="a.key" class="history-item">
                      <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                          <div class="fw-semibold">{{ a.teamName }}</div>
                          <div class="small text-muted">{{ a.note }}</div>
                        </div>
                        <span class="badge rounded-pill" :class="a.statusClass">{{ a.statusLabel }}</span>
                      </div>
                      <div class="small text-muted mt-2">{{ a.time }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { assignmentAPI, rescueRequestAPI, rescueTeamAPI } from "../../../services/api";

function txt(v, d = "") {
  if (v === null || v === undefined) return d;
  if (typeof v === "object") return txt(v.ten || v.ten_doi || v.ten_danh_muc || v.ten_loai || v.name, d);
  const s = String(v).trim();
  return s || d;
}
function pick(o, ks, d = "") {
  for (const k of ks) if (o?.[k] !== undefined && o?.[k] !== null && o?.[k] !== "") return o[k];
  return d;
}
function items(r) {
  return Array.isArray(r) ? r : Array.isArray(r?.data) ? r.data : Array.isArray(r?.data?.data) ? r.data.data : [];
}
function status(v) {
  return txt(v, "CHO_XU_LY").toUpperCase().replace(/[\s-]+/g, "_");
}
function time(v) {
  if (!v) return "Khong xac dinh";
  const d = new Date(v);
  return Number.isNaN(d.getTime()) ? txt(v, "Khong xac dinh") : d.toLocaleString("vi-VN");
}
function reqMeta(s) {
  const m = {
    CHO_XU_LY: ["Cho xu ly", "bg-info text-dark"],
    DANG_XU_LY: ["Dang xu ly", "bg-warning text-dark"],
    PROCESSING: ["Dang xu ly", "bg-warning text-dark"],
    HOAN_THANH: ["Hoan thanh", "bg-success text-white"],
    DONE: ["Hoan thanh", "bg-success text-white"],
    HUY_BO: ["Da huy", "bg-danger text-white"],
  }[s];
  return m || [txt(s, "Khong ro"), "bg-secondary text-white"];
}
function teamMeta(s) {
  const avail = /(SAN_SANG|RANH|AVAILABLE)/.test(s);
  return { label: avail ? "Co san" : txt(s, "Khong ro"), cls: avail ? "bg-success-subtle text-success-emphasis" : "bg-secondary-subtle text-secondary-emphasis", avail };
}

export default {
  name: "AdminAssignments",
  data() {
    return {
      loading: false,
      submitting: false,
      assignmentLoading: false,
      error: "",
      requests: [],
      teams: [],
      assignments: [],
      requestSearch: "",
      teamSearch: "",
      teamFilter: "all",
      selectedRequestId: "",
      selectedTeamIds: [],
      assignmentNote: "",
    };
  },
  computed: {
    filteredRequests() {
      const q = this.requestSearch.toLowerCase();
      return this.requests.filter((r) => !q || [r.id, r.type, r.address, r.description].join(" ").toLowerCase().includes(q));
    },
    selectedRequest() {
      return this.requests.find((r) => String(r.id) === String(this.selectedRequestId)) || null;
    },
    filteredTeams() {
      const q = this.teamSearch.toLowerCase();
      return this.teams.filter((t) => {
        const okQ = !q || [t.name, t.area, t.contact].join(" ").toLowerCase().includes(q);
        return okQ && (this.teamFilter !== "available" || t.available);
      });
    },
    selectedTeams() {
      return this.teams.filter((t) => this.selectedTeamIds.includes(String(t.id)));
    },
    canCreate() {
      return this.selectedRequest && !["HUY_BO", "HOAN_THANH", "DONE"].includes(this.selectedRequest.status);
    },
    canSubmit() {
      return this.canCreate && this.selectedTeams.length > 0;
    },
  },
  watch: {
    "$route.query.requestId"() {
      this.selectedRequestId = txt(this.$route.query.requestId, "");
      this.ensureSelection();
    },
  },
  async created() {
    this.selectedRequestId = txt(this.$route.query.requestId, "");
    await this.loadPage();
  },
  methods: {
    toast(type, message) {
      if (this.$toast?.[type]) return this.$toast[type](message, { position: "top-right", duration: 3500 });
      alert(message);
    },
    mapRequest(it, i) {
      const st = status(pick(it, ["trang_thai", "status"], "CHO_XU_LY"));
      const [label, cls] = reqMeta(st);
      return {
        key: `r-${pick(it, ["id_yeu_cau", "id", "request_id"], i)}-${i}`,
        id: pick(it, ["id_yeu_cau", "id", "request_id"], `REQ-${i + 1}`),
        type: txt(pick(it, ["loai", "chi_tiet", "chi_tiet_su_co"], "") || it.loai_su_co?.ten_danh_muc || it.loai_su_co?.ten_loai, "Khong ro"),
        description: txt(pick(it, ["mo_ta", "description", "noi_dung"], ""), "Khong co mo ta"),
        detail: txt(pick(it, ["chi_tiet", "chi_tiet_su_co"], "") || it.loai_su_co?.ten_danh_muc, "Khong co chi tiet"),
        address: txt(pick(it, ["vi_tri_dia_chi", "dia_chi", "address"], ""), "Khong co dia chi"),
        time: time(pick(it, ["thoi_gian_gui", "created_at", "updated_at", "thoi_gian", "time"], "")),
        priorityLabel: txt(pick(it, ["muc_do_khan_cap", "diem_uu_tien", "priority"], ""), "Khong xac dinh"),
        requester: txt(pick(it, ["ten_nguoi_gui", "ten_nguoi_dung", "ho_ten"], "") || it.nguoi_dung?.ho_ten || it.nguoi_dung?.ten, "Khong ro"),
        status: st,
        statusLabel: label,
        statusClass: cls,
      };
    },
    mapTeam(it, i) {
      const st = status(pick(it, ["trang_thai", "status", "trang_thai_hoat_dong"], "SAN_SANG"));
      const meta = teamMeta(st);
      return {
        id: String(pick(it, ["id_doi_cuu_ho", "id", "team_id"], `TEAM-${i + 1}`)),
        name: txt(pick(it, ["ten_doi", "ten", "name"], ""), `Doi ${i + 1}`),
        area: txt(pick(it, ["khu_vuc_phu_trach", "khu_vuc", "dia_chi", "vi_tri"], ""), "Chua ro"),
        contact: txt(pick(it, ["so_dien_thoai", "lien_he", "phone"], ""), "Chua co lien he"),
        statusLabel: meta.label,
        statusClass: meta.cls,
        available: meta.avail,
      };
    },
    mapAssignment(it, i) {
      const st = status(pick(it, ["trang_thai", "status"], "CHO_XU_LY"));
      const [label, cls] = reqMeta(st);
      return {
        key: `a-${pick(it, ["id_phan_cong", "id"], i)}-${i}`,
        teamName: txt(pick(it, ["ten_doi", "team_name", "doi_cuu_ho"], "") || it.doi_cuu_ho?.ten_doi, "Khong ro doi"),
        note: txt(pick(it, ["ghi_chu", "mo_ta", "description"], ""), "Khong co ghi chu"),
        time: time(pick(it, ["created_at", "thoi_gian", "updated_at"], "")),
        statusLabel: label,
        statusClass: cls,
      };
    },
    async loadPage() {
      this.loading = true;
      this.error = "";
      try {
        const [rq, tm] = await Promise.all([rescueRequestAPI.getList(), rescueTeamAPI.getList()]);
        this.requests = items(rq?.data || rq).map((it, i) => this.mapRequest(it, i));
        this.teams = items(tm?.data || tm).map((it, i) => this.mapTeam(it, i));
        this.ensureSelection();
      } catch (e) {
        console.error("load assignments page", e);
        this.error = "Khong tai duoc du lieu dieu phoi. Vui long thu lai.";
      } finally {
        this.loading = false;
      }
    },
    ensureSelection() {
      if (!this.requests.length) return;
      const routeId = txt(this.$route.query.requestId, "");
      const chosen = this.requests.find((r) => String(r.id) === String(routeId || this.selectedRequestId)) || this.requests.find((r) => r.status !== "HUY_BO") || this.requests[0];
      this.selectedRequestId = String(chosen.id);
      if (String(routeId) !== String(chosen.id)) this.$router.replace({ path: "/admin/assignments", query: { requestId: String(chosen.id) } });
      this.selectedTeamIds = [];
      this.assignmentNote = "";
      this.loadAssignments();
    },
    pickRequest(r) {
      this.selectedRequestId = String(r.id);
      this.$router.replace({ path: "/admin/assignments", query: { requestId: String(r.id) } });
      this.selectedTeamIds = [];
      this.assignmentNote = "";
      this.loadAssignments();
    },
    isTeamSelected(teamId) {
      return this.selectedTeamIds.includes(String(teamId));
    },
    toggleTeam(teamId) {
      const id = String(teamId);
      if (this.isTeamSelected(id)) {
        this.selectedTeamIds = this.selectedTeamIds.filter((item) => item !== id);
        return;
      }
      this.selectedTeamIds = [...this.selectedTeamIds, id];
    },
    async loadAssignments() {
      if (!this.selectedRequestId) return (this.assignments = []);
      this.assignmentLoading = true;
      try {
        const rs = await assignmentAPI.getByRequest(this.selectedRequestId);
        this.assignments = items(rs?.data || rs).map((it, i) => this.mapAssignment(it, i));
      } catch (e) {
        console.error("load assignments", e);
        this.assignments = [];
      } finally {
        this.assignmentLoading = false;
      }
    },
    async submitAssignment() {
      if (!this.canSubmit) return;
      this.submitting = true;
      try {
        const payload = {
          id_yeu_cau: this.selectedRequest.id,
          request_id: this.selectedRequest.id,
          id_yeu_cau_cuu_ho: this.selectedRequest.id,
          ghi_chu: this.assignmentNote || undefined,
          mo_ta: this.assignmentNote || undefined,
          trang_thai: "CHO_XU_LY",
        };
        const payloads = this.selectedTeams.map((team) => ({
          ...payload,
          id_doi_cuu_ho: team.id,
          id_doi: team.id,
          team_id: team.id,
        }));
        await Promise.all(
          payloads.map((item) => {
            const clean = { ...item };
            Object.keys(clean).forEach((k) => clean[k] === undefined && delete clean[k]);
            return assignmentAPI.create(clean);
          })
        );
        this.assignmentNote = "";
        this.selectedTeamIds = [];
        this.toast("success", "Da tao phan cong cho cac doi da chon.");
        await this.loadAssignments();
      } catch (e) {
        console.error("create assignment", e);
        this.toast("error", e?.response?.data?.message || "Khong tao duoc phan cong. Vui long kiem tra API.");
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>

<style scoped>
.list-pane { max-height: 70vh; overflow: auto; }
.pick-card, .history-item, .info-box {
  border: 1px solid #e9ecef; border-radius: 16px; background: #fff; padding: 1rem; transition: .2s ease;
}
.pick-card + .pick-card { margin-top: .85rem; }
.pick-card:hover { border-color: #9ec5fe; box-shadow: 0 10px 24px rgba(13,110,253,.08); }
.pick-card.active { border-color: #0d6efd; background: rgba(13,110,253,.04); box-shadow: 0 12px 28px rgba(13,110,253,.12); }
</style>
