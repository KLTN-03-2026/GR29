<template>
  <div class="resources-wrapper">
    <div class="resources-header px-4 py-3 d-flex align-items-center justify-content-between border-bottom bg-white shadow-sm">
      <div class="d-flex align-items-center">
        <div class="header-icon me-3 d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning" style="width: 48px; height: 48px;">
          <i class="fa-solid fa-toolbox fs-4"></i>
        </div>
        <div>
          <h5 class="fw-bold mb-0 text-dark">Tài nguyên đội</h5>
          <span class="text-muted small">Xem tài nguyên, tồn kho và gửi yêu cầu cấp phát</span>
        </div>
      </div>
      <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" @click="moModalYeuCau">
        <i class="fa-solid fa-paper-plane me-2"></i>Yêu cầu tài nguyên
      </button>
    </div>

    <!-- <div class="stats-row px-4 py-3 border-bottom bg-white">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ tongSoTaiNguyenDoi }}</div>
            <div class="text-muted small fw-medium">Tổng SL trong đội</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-warehouse"></i>
            </div>
            <div class="fw-bold text-dark fs-4">{{ tongTonKho }}</div>
            <div class="text-muted small fw-medium">Tổng tồn kho hệ thống</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-info bg-opacity-10 text-info" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-users"></i>
            </div>
            <div class="fw-bold text-dark text-truncate fs-6">{{ tenDoiCuuHo || '—' }}</div>
            <div class="text-muted small fw-medium">Đội cứu hộ</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card bg-light rounded-4 p-3 text-center border border-light shadow-sm">
            <div class="stat-icon mb-2 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-secondary bg-opacity-10 text-secondary" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-user"></i>
            </div>
            <div class="fw-bold text-dark text-truncate fs-6">{{ tenNguoiDung || '—' }}</div>
            <div class="text-muted small fw-medium">Tài khoản</div>
          </div>
        </div>
      </div>
    </div> -->

    <div class="resources-body px-4 py-3 flex-grow-1 overflow-auto">
      <!-- <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-warehouse me-2 text-primary"></i>Tồn kho (tham khảo)</h6>
      <div v-if="dangTaiTonKho" class="text-center py-3">
        <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
      </div>
      <div v-else class="row g-2 mb-4">
        <div v-for="dong in tonKhoTheoLoai" :key="dong.slug_tai_nguyen" class="col-6 col-md-3">
          <div class="border rounded-4 p-3 bg-white shadow-sm h-100">
            <div class="small text-muted">{{ dong.ten_hien_thi }}</div>
            <div class="fw-bold fs-5 text-dark">{{ dong.tong_so_luong }}</div>
          </div>
        </div>
      </div> -->

      <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list me-2 text-danger"></i>Tài nguyên hiện có của đội</h6>
      <div v-if="dangTaiTaiNguyen" class="text-center py-5">
        <div class="spinner-border text-danger" role="status"></div>
        <p class="mt-2 text-muted">Đang tải...</p>
      </div>
      <div v-else-if="danhSachTaiNguyen.length === 0" class="text-center py-5">
        <div class="mb-3">
          <i class="fa-solid fa-toolbox text-secondary opacity-25" style="font-size: 64px;"></i>
        </div>
        <h6 class="text-secondary fw-bold">Chưa có tài nguyên được cấp</h6>
        <p class="text-muted small">Gửi yêu cầu để admin duyệt và cấp phát từ kho</p>
      </div>
      <div v-else class="row g-3">
        <div v-for="item in danhSachTaiNguyen" :key="item.id_tai_nguyen" class="col-12 col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 hover-card">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center">
                  <div class="resource-icon me-3 d-flex align-items-center justify-content-center rounded-3 text-white" :class="layLopBieuTuong(item.slug_tai_nguyen)" style="width: 48px; height: 48px;">
                    <i :class="layIconTheoSlug(item.slug_tai_nguyen)"></i>
                  </div>
                  <div>
                    <h6 class="fw-bold text-dark mb-1">{{ item.ten_tai_nguyen }}</h6>
                    <!-- <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1 small">{{ item.slug_tai_nguyen }}</span> -->
                  </div>
                </div>
                <span class="badge rounded-pill px-3 py-2 fw-bold" :class="layLopTrangThai(item.trang_thai)">
                  {{ layChuoiTrangThai(item.trang_thai) }}
                </span>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <span class="text-muted small">Số lượng còn lại:</span>
                  <span class="fw-bold text-dark ms-2">{{ item.so_luong }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" :class="{ show: hienModalYeuCau }" :style="{ display: hienModalYeuCau ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">Yêu cầu cấp phát tài nguyên</h5>
            <button type="button" class="btn-close" @click="dongModalYeuCau"></button>
          </div>
          <div class="modal-body">
            <div class="rounded-3 bg-light p-3 mb-3 small">
              <div><span class="text-muted">Đội cứu hộ:</span> <strong>{{ tenDoiCuuHo || '—' }}</strong></div>
              <div><span class="text-muted">Người gửi:</span> <strong>{{ tenNguoiDung || '—' }}</strong></div>
              <div><span class="text-muted">Thời gian gửi:</span> <strong>{{ thoiGianGuiHienThi }}</strong></div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Loại tài nguyên</label>
              <select v-model="formYeuCau.slug_tai_nguyen" class="form-select rounded-3">
                <option value="">Chọn loại</option>
                <option v-for="k in tonKhoTheoLoai" :key="k.slug_tai_nguyen" :value="k.slug_tai_nguyen">
                  {{ k.ten_hien_thi }} (kho: {{ k.tong_so_luong }})
                </option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Số lượng yêu cầu</label>
              <input v-model.number="formYeuCau.so_luong_yeu_cau" type="number" min="1" class="form-control rounded-3" placeholder="Nhập số lượng">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Ghi chú <span class="text-muted fw-normal">(tuỳ chọn)</span></label>
              <textarea v-model="formYeuCau.ghi_chu" class="form-control rounded-3" rows="2" placeholder="Ghi chú cho admin..."></textarea>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary rounded-pill" @click="dongModalYeuCau">Huỷ</button>
            <button type="button" class="btn btn-danger rounded-pill fw-bold" :disabled="dangGuiYeuCau || !hopLeFormYeuCau" @click="xuLyGuiYeuCau">
              {{ dangGuiYeuCau ? 'Đang gửi...' : 'Gửi yêu cầu' }}
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
  name: "TaiNguyen",
  data() {
    return {
      dangTaiTaiNguyen: false,
      dangTaiTonKho: false,
      dangGuiYeuCau: false,
      hienModalYeuCau: false,
      danhSachTaiNguyen: [],
      tonKhoTheoLoai: [],
      formYeuCau: {
        slug_tai_nguyen: "",
        so_luong_yeu_cau: 1,
        ghi_chu: "",
      },
    };
  },
  computed: {
    idDoiCuuHo() {
      const teamStr = localStorage.getItem("rescuer_team");
      if (teamStr) {
        try {
          const team = JSON.parse(teamStr);
          return team.id_doi_cuu_ho || team.id || null;
        } catch {
          return null;
        }
      }
      return null;
    },
    tenDoiCuuHo() {
      const teamStr = localStorage.getItem("rescuer_team");
      if (teamStr) {
        try {
          const team = JSON.parse(teamStr);
          return team.ten_doi || null;
        } catch {
          return null;
        }
      }
      return null;
    },
    tenNguoiDung() {
      return localStorage.getItem("rescuer_name") || null;
    },
    tongSoTaiNguyenDoi() {
      return this.danhSachTaiNguyen.reduce((s, x) => s + (parseInt(x.so_luong, 10) || 0), 0);
    },
    tongTonKho() {
      return this.tonKhoTheoLoai.reduce((s, x) => s + (parseInt(x.tong_so_luong, 10) || 0), 0);
    },
    thoiGianGuiHienThi() {
      return new Date().toLocaleString("vi-VN");
    },
    hopLeFormYeuCau() {
      return (
        !!this.formYeuCau.slug_tai_nguyen &&
        Number(this.formYeuCau.so_luong_yeu_cau) >= 1
      );
    },
  },
  async mounted() {
    await Promise.all([this.taiDanhSachTaiNguyen(), this.taiTonKhoHeThong()]);
  },
  methods: {
    tachMangTuPhanTrang(payload) {
      if (!payload) return [];
      if (Array.isArray(payload)) return payload;
      if (Array.isArray(payload.data)) return payload.data;
      return [];
    },
    async taiDanhSachTaiNguyen() {
      this.dangTaiTaiNguyen = true;
      try {
        if (!this.idDoiCuuHo) {
          this.danhSachTaiNguyen = [];
          return;
        }
        const res = await rescuerAPI.getTeamResources(this.idDoiCuuHo);
        const raw = res.data?.data ?? res.data ?? [];
        this.danhSachTaiNguyen = this.tachMangTuPhanTrang(raw);
      } catch (e) {
        console.error("taiDanhSachTaiNguyen", e);
        toaster.error("Không tải được tài nguyên đội");
      } finally {
        this.dangTaiTaiNguyen = false;
      }
    },
    async taiTonKhoHeThong() {
      this.dangTaiTonKho = true;
      try {
        const res = await rescuerAPI.xemTonKhoTaiNguyen();
        this.tonKhoTheoLoai = res.data?.data ?? [];
      } catch (e) {
        console.error("taiTonKhoHeThong", e);
        toaster.error("Không tải được dữ liệu kho");
      } finally {
        this.dangTaiTonKho = false;
      }
    },
    moModalYeuCau() {
      this.formYeuCau = {
        slug_tai_nguyen: "",
        so_luong_yeu_cau: 1,
        ghi_chu: "",
      };
      this.hienModalYeuCau = true;
    },
    dongModalYeuCau() {
      this.hienModalYeuCau = false;
    },
    async xuLyGuiYeuCau() {
      if (!this.hopLeFormYeuCau) {
        toaster.warning("Vui lòng chọn loại và số lượng hợp lệ");
        return;
      }
      this.dangGuiYeuCau = true;
      try {
        await rescuerAPI.guiYeuCauCapPhatTaiNguyen({
          slug_tai_nguyen: this.formYeuCau.slug_tai_nguyen,
          so_luong_yeu_cau: this.formYeuCau.so_luong_yeu_cau,
          ghi_chu: this.formYeuCau.ghi_chu || undefined,
        });
        toaster.success("Đã gửi yêu cầu cấp phát");
        this.dongModalYeuCau();
        await this.taiTonKhoHeThong();
      } catch (e) {
        const msg =
          e.response?.data?.message ||
          e.response?.data?.errors?.slug_tai_nguyen?.[0] ||
          "Không gửi được yêu cầu";
        toaster.error(msg);
      } finally {
        this.dangGuiYeuCau = false;
      }
    },
    layIconTheoSlug(slug) {
      const map = {
        xe_cuu_ho: "fa-solid fa-truck-medical",
        nhu_yeu_pham: "fa-solid fa-bottle-water",
        vat_tu_y_te: "fa-solid fa-kit-medical",
        dung_cu_thi_cong: "fa-solid fa-screwdriver-wrench",
        Vehicle: "fa-solid fa-truck-medical",
        Supply: "fa-solid fa-bottle-water",
        Medical: "fa-solid fa-kit-medical",
        Equipment: "fa-solid fa-screwdriver-wrench",
      };
      return map[slug] || "fa-solid fa-box";
    },
    layLopBieuTuong(slug) {
      const map = {
        xe_cuu_ho: "bg-danger",
        nhu_yeu_pham: "bg-success",
        vat_tu_y_te: "bg-primary",
        dung_cu_thi_cong: "bg-warning",
        Vehicle: "bg-danger",
        Supply: "bg-success",
        Medical: "bg-primary",
        Equipment: "bg-warning",
      };
      return map[slug] || "bg-secondary";
    },
    layLopTrangThai(trangThai) {
      return trangThai === 1 ? "bg-success bg-opacity-10 text-success" : "bg-danger bg-opacity-10 text-danger";
    },
    layChuoiTrangThai(trangThai) {
      return trangThai === 1 ? "Sẵn sàng" : "Tạm ngưng / Hỏng";
    },
  },
};
</script>

<style scoped>
.resources-wrapper {
  margin: -1.5rem -1.5rem -2rem;
  height: calc(100vh - 72px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #f8f9fa;
}
.resources-header { flex-shrink: 0; }
.stats-row { flex-shrink: 0; }
.resources-body { flex: 1; overflow-y: auto; }
.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
}
.modal.show { background: rgba(0,0,0,0.5); }
</style>
