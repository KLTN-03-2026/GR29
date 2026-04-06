<template>
    <div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-semibold">Lịch sử sự cố</h4>
                <p class="text-muted mb-0">Hiển thị các yêu cầu cứu hộ đã hoàn thành.</p>
            </div>
            <button class="btn btn-outline-secondary" @click="taiYeuCauHoanThanh" :disabled="loading">
                <i class="bi bi-arrow-clockwise me-2"></i>
                Làm mới
            </button>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-12 col-md-4">
                        <input v-model.trim="query" class="form-control" placeholder="Tìm theo mã sự cố" />
                    </div>

                    <div class="col-12 col-md-3">
                        <select v-model="loaiSuCo" class="form-select" >
                            <option value="">Tất cả loại sự cố</option>
                            <option v-for="type in loaiSuCoList" :key="type.id" :value="type.id">
                                {{ type.name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <input v-model="ngayLoc" type="date" class="form-control" />
                    </div>

                    <div class="col-12 col-md-2">
                        <button class="btn btn-primary w-100" @click="taiYeuCauHoanThanh">Lọc</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="small text-muted mt-2">Đang tải danh sách hoàn thành...</div>
        </div>

        <div v-if="!loading && danhSachLoc.length === 0" class="alert alert-warning">
            Không tìm thấy yêu cầu hoàn thành nào phù hợp.
        </div>

        <div class="row g-4" v-if="!loading && danhSachLoc.length > 0">
            <div v-for="request in danhSachLoc" :key="request.key" class="col-12 col-xl-6">
                <div class="card h-100 border-0 shadow-sm overflow-hidden">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="mb-1">SOS-{{ request.id }}</h5>
                            <span class="badge rounded-pill" :class="request.statusClass">{{ request.statusLabel
                                }}</span>
                        </div>
                        <p class="small text-muted mb-1">Loại: {{ request.type }}</p>
                        <p class="small text-muted mb-2">Địa chỉ: {{ request.address }}</p>
                        <p class="small text-muted mb-2">Thời gian tạo: {{ request.time }}</p>
                        <p class="small text-muted mb-2">Ưu tiên: {{ request.priorityLabel }}</p>
                        <p class="mb-2">{{ request.description }}</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-success">Hoàn thành</span>
                            <span class="badge bg-info text-dark">Đã cứu hộ/Đang đợi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { rescueRequestAPI, incidentTypeAPI } from "../../../services/api";

const STATUS_META = {
    CHO_XU_LY: { label: "Chờ xử lý", badge: "bg-info text-dark" },
    DANG_XU_LY: { label: "Đang xử lý", badge: "bg-warning text-dark" },
    HOAN_THANH: { label: "Hoàn thành", badge: "bg-success text-white" },
    DONE: { label: "Hoàn thành", badge: "bg-success text-white" },
    HUY_BO: { label: "Đã hủy", badge: "bg-danger text-white" },
};

function chuanHoaChuoi(value, fallback = "") {
    if (value === null || value === undefined) return fallback;
    if (typeof value === "object") {
        return chuanHoaChuoi(
            value.ten_danh_muc || value.ten_loai_su_co || value.title || value.name || fallback,
            fallback
        );
    }
    const normalized = String(value).trim();
    return normalized || fallback;
}

function dinhDangThoiGian(value) {
    if (!value) return "Không xác định";
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) return chuanHoaChuoi(value, "Không xác định");
    return parsed.toLocaleString("vi-VN", {
        hour: "2-digit",
        minute: "2-digit",
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

function chuanHoaTrangThai(status) {
    if (!status) return "CHO_XU_LY";
    return chuanHoaChuoi(status, "CHO_XU_LY").toUpperCase().replace(/\s+/g, "_");
}

function layTrangThaiMeta(status) {
    const key = chuanHoaTrangThai(status);
    return (
        STATUS_META[key] || {
            label: chuanHoaChuoi(status, "Không rõ"),
            badge: "bg-secondary text-white",
        }
    );
}

function phanTichYeuCau(payload) {
    const data = payload;
    const items = Array.isArray(data)
        ? data
        : Array.isArray(data?.data)
            ? data.data
            : Array.isArray(data?.data?.data)
                ? data.data.data
                : [];

    return items.map((item, index) => {
        const id = item.id_yeu_cau || item.id || item.request_id || "-";
        const statusMeta = layTrangThaiMeta(item.trang_thai || item.status);
        const thoiGianRaw = item.thoi_gian_gui || item.created_at || item.updated_at || item.thoi_gian || item.time;

        return {
            key: `${id}-${index}`,
            id,
            incidentTypeId: item.loai_su_co?.id_loai_su_co || item.loai_su_co?.id || "",
            type: chuanHoaChuoi(
                item.loai_su_co?.ten_danh_muc ||
                item.loai_su_co?.ten_loai ||
                item.loai ||
                item.chi_tiet ||
                item.chi_tiet_su_co,
                "Không rõ"
            ),
            description: chuanHoaChuoi(item.mo_ta || item.description || item.noi_dung, "Không có mô tả"),
            address: chuanHoaChuoi(item.vi_tri_dia_chi || item.dia_chi, "Không có địa chỉ"),
            time: dinhDangThoiGian(thoiGianRaw),
            ngaySoSanh: thoiGianRaw ? new Date(thoiGianRaw).toISOString().split('T')[0] : null,
            priorityLabel: chuanHoaChuoi(item.muc_do_khan_cap || item.diem_uu_tien, "Không xác định"),
            status: chuanHoaTrangThai(item.trang_thai || item.status),
            statusLabel: statusMeta.label,
            statusClass: statusMeta.badge,
            raw: item,
        };
    });
}

export default {
    name: "AdminLichSu",
    data() {
        return {
            requests: [],
            loaiSuCoList: [],
            loading: false,
            loadingTypes: false,
            error: "",
            query: "",
            loaiSuCo: "",
            ngayLoc: "", // Thay thế ngayTu và ngayDen
        };
    },
    computed: {
        danhSachLoc() {
            const q = this.query.toLowerCase();
            const filterDate = this.ngayLoc; // Định dạng YYYY-MM-DD

            return this.requests.filter((r) => {
                const text = `${r.id} ${r.type} ${r.description}`.toLowerCase();
                if (q && !text.includes(q)) return false;

                if (this.loaiSuCo && r.incidentTypeId !== this.loaiSuCo) return false;

                if (filterDate && r.ngaySoSanh !== filterDate) return false;

                return true;
            });
        },
    },
    async created() {
        await this.taiLoaiSuCo();
        await this.taiYeuCauHoanThanh();
    },
    methods: {
        datLaiLoc() {
            this.query = "";
            this.loaiSuCo = "";
            this.ngayLoc = "";
        },
        async taiLoaiSuCo() {
            this.loadingTypes = true;
            try {
                const response = await incidentTypeAPI.getList();
                const payload = response?.data || response;
                const items = Array.isArray(payload) ? payload : Array.isArray(payload?.data) ? payload.data : [];
                this.loaiSuCoList = items.map(item => ({
                    id: item.id_loai_su_co || item.id,
                    name: chuanHoaChuoi(item.ten_danh_muc || item.ten_loai_su_co || item.name, "Không rõ")
                }));
            } catch (err) {
                console.error("Lỗi tải loại sự cố", err);
            } finally {
                this.loadingTypes = false;
            }
        },
        async taiYeuCauHoanThanh() {
            this.loading = true;
            this.error = "";

            try {
                let response = null;
                try {
                    response = await rescueRequestAPI.getByStatus("HOAN_THANH");
                } catch (err) {
                    console.warn("Không lấy được theo trạng thái, thử tất cả rồi lọc", err);
                    response = await rescueRequestAPI.getList();
                }

                const payload = response?.data || response;
                const allRequests = phanTichYeuCau(payload);
                this.requests = allRequests.filter((r) => ["HOAN_THANH", "DONE"].includes(r.status));
            } catch (err) {
                console.error("Lấy danh sách lịch sử thất bại", err);
                this.error = "Không tải được lịch sử. Vui lòng thử lại.";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.card-body {
    min-height: auto;
}
</style>