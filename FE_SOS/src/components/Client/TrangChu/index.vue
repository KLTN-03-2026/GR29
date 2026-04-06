<template>
    <div class="container-fluid p-0 d-flex flex-column flex-lg-row flex-grow-1 overflow-hidden h-100">
        <div class="col-lg-5 col-xl-4 bg-white p-4 p-xl-5 overflow-y-auto" style="max-height: 100%;">
            <h2 class="fw-bold mb-1">Yeu cau cuu ho moi</h2>
            <p class="text-muted small mb-4">Cung cap thong tin chinh xac de luc luong phan ung tiep can nhanh nhat.</p>

            <label class="fw-bold small text-uppercase mb-2 d-block text-secondary text-opacity-75">Loai su co khan
                cap</label>
            <div class="row g-2 mb-4 mt-2">
                <div v-if="loadingIncidentTypes" class="col-12">
                    <div class="small text-muted bg-light rounded-3 px-3 py-2">Dang tai loai su co...</div>
                </div>
                <div v-else-if="incidentTypes.length === 0" class="col-12">
                    <div class="small text-danger bg-light rounded-3 px-3 py-2">
                        {{ incidentTypeError || "Chua co du lieu loai su co tu he thong." }}
                    </div>
                </div>
                <div v-else class="col-3" v-for="type in incidentTypes" :key="type.id">
                    <button @click="chonLoaiSuCo(type.id)" type="button" :class="[
                        'btn w-100 h-100 py-3 border-2 shadow-sm d-flex flex-column align-items-center justify-content-center',
                        selectedType === type.id ? 'btn-outline-danger active' : 'btn-outline-light text-dark border-light-subtle bg-light'
                    ]">
                        <i :class="['fa-solid fs-4 mb-1 mt-2', type.icon]"></i>
                        <span style="font-size: 10px;" class="fw-bold text-uppercase">{{ type.label }}</span>
                    </button>
                </div>
            </div>

            <div v-if="selectedType" class="mb-4">
                <label class="fw-bold small text-uppercase mb-2 d-block text-secondary text-opacity-75">Chi tiet su
                    co</label>
                <div v-if="loadingDetails" class="small text-muted text-center py-2 bg-light rounded-3">
                    <i class="fas fa-spinner fa-spin me-2"></i> Dang tai chi tiet...
                </div>
                <div v-else class="d-flex flex-wrap gap-2">
                    <div v-for="detail in incidentDetails" :key="detail.id">
                        <input type="checkbox" class="btn-check" :id="'detail-' + detail.id" :value="detail.id"
                            v-model="selectedDetailIds" autocomplete="off">
                        <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 border-opacity-25 shadow-sm"
                            :for="'detail-' + detail.id">
                            {{ detail.label }}
                        </label>
                    </div>
                    <div v-if="incidentDetails.length === 0" class="small text-muted py-2 px-3 bg-light rounded-3">
                        Khong co chi tiet cho loai su co nay.
                    </div>
                </div>
                <div v-if="selectedDetailIds.length > 0" class="small text-muted mt-2">
                    Da chon {{ selectedDetailIds.length }} muc chi tiet.
                </div>
            </div>

            <div class="bg-light rounded-3 p-3 mb-4 border border-secondary border-opacity-10 shadow-sm">
                <label class="fw-bold small text-uppercase mb-2 d-block text-secondary">Cach xac dinh vi tri</label>
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold"
                        :class="cheDoViTri === CHE_DO_VI_TRI.GPS ? 'btn-dark' : 'btn-outline-secondary'"
                        @click="chonCheDoViTri(CHE_DO_VI_TRI.GPS)">
                        Toi can cuu ho
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold"
                        :class="cheDoViTri === CHE_DO_VI_TRI.NHAP_TAY ? 'btn-dark' : 'btn-outline-secondary'"
                        @click="chonCheDoViTri(CHE_DO_VI_TRI.NHAP_TAY)">
                        Toi goi giup nguoi khac
                    </button>
                </div>

                <div v-if="cheDoViTri === CHE_DO_VI_TRI.GPS">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="fw-bold small text-uppercase mb-0 text-secondary">Vi tri cua ban</label>
                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold shadow-sm"
                            style="font-size: 10px;" :disabled="locating" @click="layGps">
                            <i class="fa-solid fa-location-crosshairs me-1"></i>
                            {{ locating ? 'Dang lay...' : 'Xac dinh GPS' }}
                        </button>
                    </div>
                    <div class="small text-muted bg-white rounded-3 px-3 py-2 border border-secondary border-opacity-10">
                        Su dung GPS hien tai de gui yeu cau cuu ho cho chinh ban.
                    </div>
                </div>

                <div v-else>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="fw-bold small text-uppercase mb-0 text-secondary">Dia chi can cuu ho</label>
                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold shadow-sm"
                            style="font-size: 10px;" :disabled="dangTimDiaChi" @click="xacNhanDiaChiNhap">
                            <i class="fa-solid fa-paper-plane me-1"></i>
                            {{ dangTimDiaChi ? 'Dang xu ly...' : 'Dung dia chi nay' }}
                        </button>
                    </div>
                    <div class="input-group bg-white rounded-2 overflow-hidden shadow-sm">
                        <span class="input-group-text bg-white border-0"><i
                                class="fa-solid fa-location-dot text-danger"></i></span>
                        <input v-model="address" type="text" class="form-control border-0 py-2 shadow-none small"
                            placeholder="Nhap dia chi chi tiet...">
                    </div>
                    <div class="small text-muted mt-2">
                        Nhap dia chi cua nguoi can giup, sau do bam <span class="fw-semibold">Dung dia chi nay</span>.
                    </div>
                </div>

                <div v-if="coordsText" class="small text-muted mt-2 mb-0">{{ coordsText }}</div>
            </div>

            <div class="mb-4">
                <label class="fw-bold small text-uppercase mb-2 d-block text-secondary">Mo ta tinh huong</label>
                <textarea v-model="description" class="form-control border-0 bg-light shadow-sm p-3" rows="3"
                    placeholder="So nguoi bi nan, tinh trang hien tai..."></textarea>
            </div>

            <div class="mb-4">
                <label class="fw-bold small text-uppercase mb-2 d-block text-secondary">Hinh anh hien truong</label>
                <label
                    class="w-100 border border-2 border-secondary border-opacity-25 border-dashed rounded-3 p-4 text-center bg-light text-muted btn btn-light border-0 shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-up fs-2"></i>
                    <p class="small mt-2 mb-0 fw-bold">Tai len anh/video</p>
                    <input type="file" class="d-none" accept="image/*,video/*" @change="handleFileSelect">
                </label>
                <div v-if="selectedImageName" class="small text-muted mt-2">
                    <span class="fw-semibold">File đã chọn:</span> {{ selectedImageName }}
                </div>
                <div v-if="imageProcessing" class="mt-3 text-center">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Đang xử lý ảnh...
                </div>
                <div v-else-if="selectedImagePreview" class="mt-3">
                    <img :src="selectedImagePreview" alt="Preview" class="img-fluid rounded border" style="max-height: 200px;">
                </div>
            </div>

            <button type="button" class="btn btn-danger w-100 py-3 fw-bold fs-5 shadow-lg rounded-3 border-0"
                :disabled="submitting" @click="guiYeuCau">
                {{ submitting ? "DANG GUI..." : "GUI CUU HO NGAY" }}
            </button>

            <div class="modal fade" id="loginRequireModal" tabindex="-1" aria-hidden="true" ref="loginModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold text-danger"><i
                                    class="fa-solid fa-triangle-exclamation me-2"></i>Yeu cau dang nhap</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <p class="mb-4">Ban can dang nhap hoac dang ky tai khoan de gui yeu cau cuu ho. Viec nay
                                giup doi cuu ho xac minh va lien he voi ban nhanh hon.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <button @click="goToLogin" data-bs-dismiss="modal"
                                    class="btn btn-outline-danger px-4 rounded-pill">Dang nhap</button>
                                <button @click="goToRegister" data-bs-dismiss="modal"
                                    class="btn btn-danger px-4 rounded-pill">Dang ky moi</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 col-xl-8 bg-light p-4 position-relative border-start d-flex flex-column"
            style="min-height: 320px;">
            <div class="flex-grow-1 rounded-4 overflow-hidden border border-white border-4 shadow-lg position-relative bg-white"
                style="min-height: 320px;">
                <MapboxMap ref="mapRef" class="position-absolute top-0 start-0 w-100 h-100" />
                <div class="position-absolute bottom-0 start-0 m-3 d-none d-md-block">
                    <div class="card border-0 shadow-lg p-3 rounded-4" style="width: 260px;">
                        <h6 class="fw-bold small text-uppercase mb-3 text-secondary">Luc luong lan can</h6>
                        <div class="d-flex align-items-center mb-2" v-for="u in units" :key="u.name">
                            <div :class="['rounded-circle p-2 me-2 bg-opacity-10', u.c]">
                                <i :class="['fa-solid small', u.i, u.t]"></i>
                            </div>
                            <div style="font-size: 11px;"><b class="d-block text-dark">{{ u.name }}</b><span
                                    class="text-muted">{{ u.d }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";
import { incidentTypeAPI, rescueRequestAPI } from "../../../services/api";

const INCIDENT_ICON_FALLBACK = "fa-triangle-exclamation";
const CHE_DO_VI_TRI = {
    GPS: "gps",
    NHAP_TAY: "nhap_tay",
};

const INCIDENT_ICON_RULES = [
    { keywords: ["chay", "hoa hoan", "hoan", "lua", "lua chay", "no"], icon: "fa-fire" },
    { keywords: ["tai nan", "va cham", "giao thong", "xe", "duong bo"], icon: "fa-car-burst" },
    { keywords: ["y te", "cap cuu", "benh", "thuong tich", "suc khoe"], icon: "fa-truck-medical" },
    { keywords: ["lu", "lut", "ngap", "nuoc", "song", "suoi"], icon: "fa-water" },
    { keywords: ["bao", "giong", "loc", "thien tai"], icon: "fa-cloud-bolt" },
    { keywords: ["sat lo", "dat da", "nui"], icon: "fa-mountain" },
    { keywords: ["dong dat", "rung chan"], icon: "fa-house-crack" },
    { keywords: ["song than"], icon: "fa-house-flood-water" },
    { keywords: ["cuop", "an ninh", "bao luc", "danh nhau"], icon: "fa-user-shield" },
    { keywords: ["mat tich", "tim kiem"], icon: "fa-magnifying-glass" },
    { keywords: ["dien", "dien giat", "ro ri"], icon: "fa-bolt" },
];

function layGiaTriDauTien(source, keys) {
    for (const key of keys) {
        const value = source?.[key];
        if (value !== undefined && value !== null && value !== "") {
            return value;
        }
    }
    return null;
}

function chuanHoaDanhSach(payload) {
    if (Array.isArray(payload)) return payload;
    if (Array.isArray(payload?.data)) return payload.data;
    if (Array.isArray(payload?.data?.data)) return payload.data.data;
    if (Array.isArray(payload?.result)) return payload.result;
    return [];
}

function chuanHoaTuKhoa(value) {
    return String(value || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();
}

function xacDinhIconSuCo(label, backendIcon) {
    if (backendIcon && String(backendIcon).startsWith("fa-")) {
        return backendIcon;
    }

    const normalizedLabel = chuanHoaTuKhoa(label);
    const matchedRule = INCIDENT_ICON_RULES.find((rule) =>
        rule.keywords.some((keyword) => normalizedLabel.includes(chuanHoaTuKhoa(keyword)))
    );

    return matchedRule?.icon || INCIDENT_ICON_FALLBACK;
}

function anhXaLoaiSuCo(item, index) {
    const label =
        layGiaTriDauTien(item, ["ten_danh_muc", "ten_loai_su_co", "ten_loai", "loai_su_co", "ten", "name"]) ||
        `Loai su co ${index + 1}`;

    const icon = layGiaTriDauTien(item, ["icon", "bieu_tuong", "icon_class"]);

    return {
        id: layGiaTriDauTien(item, ["id_loai_su_co", "id", "ma_loai_su_co", "ma_loai", "value"]) ?? index + 1,
        label,
        icon: xacDinhIconSuCo(label, icon),
        raw: item,
    };
}

function anhXaChiTietSuCo(item, index) {
    return {
        id: layGiaTriDauTien(item, ["id_chi_tiet", "id", "ma_chi_tiet", "value"]) ?? index + 1,
        label: layGiaTriDauTien(item, ["ten_chi_tiet", "ten", "name", "chi_tiet"]) || `Chi tiet ${index + 1}`,
        raw: item,
    };
}

async function timToaDoTheoDiaChi(diaChi) {
    const apiKey = import.meta.env.VITE_OPENMAP_API_KEY;
    if (!apiKey) {
        throw new Error("Chua cau hinh VITE_OPENMAP_API_KEY de tim dia chi.");
    }

    const query = new URLSearchParams({
        address: diaChi,
        apikey: apiKey,
    });

    const response = await fetch(`https://mapapis.openmap.vn/v1/geocode/forward?${query.toString()}`);
    const data = await response.json();
    const ketQuaDauTien = data?.results?.[0];
    const viTri = ketQuaDauTien?.geometry?.location;

    if (!response.ok || !viTri?.lat || !viTri?.lng) {
        throw new Error(data?.message || "Khong tim thay vi tri phu hop voi dia chi da nhap.");
    }

    return {
        lat: Number(viTri.lat),
        lng: Number(viTri.lng),
        diaChiDayDu: ketQuaDauTien?.formatted_address || ketQuaDauTien?.address || diaChi,
    };
}

export default {
    components: { MapboxMap },
    data() {
        return {
            CHE_DO_VI_TRI,
            selectedType: null,
            selectedDetailIds: [],
            incidentTypes: [],
            incidentDetails: [],
            loadingIncidentTypes: false,
            loadingDetails: false,
            incidentTypeError: "",
            address: "",
            description: "",
            coordsText: "",
            cheDoViTri: CHE_DO_VI_TRI.GPS,
            locating: false,
            dangTimDiaChi: false,
            submitting: false,
            selectedCoords: null,
            selectedImageName: "",
            selectedImageData: null,
            selectedImagePreview: null,
            imageProcessing: false,
            soNguoiBiAnhHuong: 1,
            mucDoKhanCap: "HIGH",
            diemUuTien: null,
            trangThai: null,
            units: [
                { name: "Canh sat", d: "1.2 km - 5p", i: "fa-shield-halved", c: "bg-primary", t: "text-primary" },
                { name: "BV Da khoa", d: "0.8 km - 3p", i: "fa-hospital", c: "bg-danger", t: "text-danger" }
            ]
        };
    },
    async created() {
        await this.taiDanhSachLoaiSuCo();
    },
    methods: {
        hienToast(type, message) {
            if (this.$toast?.[type]) {
                this.$toast[type](message, {
                    position: "top-right",
                    duration: 3500,
                });
                return;
            }

            alert(message);
        },
        chuanHoaChiTietSuCo(payload) {
            const detailSources = [
                payload?.chiTiets,
                payload?.chi_tiets,
                payload?.data?.chiTiets,
                payload?.data?.chi_tiets,
                payload?.data?.data?.chiTiets,
                payload?.data?.data?.chi_tiets,
            ];

            const foundArray = detailSources.find((item) => Array.isArray(item));
            const items = foundArray || chuanHoaDanhSach(payload?.data) || chuanHoaDanhSach(payload);

            return items.map(anhXaChiTietSuCo);
        },
        async taiDanhSachLoaiSuCo() {
            this.loadingIncidentTypes = true;
            this.incidentTypeError = "";

            try {
                const response = await incidentTypeAPI.getList();
                const items = chuanHoaDanhSach(response?.data);
                this.incidentTypes = items.map(anhXaLoaiSuCo);
                this.selectedType = this.incidentTypes[0]?.id ?? null;
                this.selectedDetailIds = [];

                if (this.incidentTypes.length === 0) {
                    this.incidentTypeError = "Backend chua tra ve du lieu loai su co.";
                    this.incidentDetails = [];
                } else {
                    await this.taiChiTietLoaiSuCo(this.selectedType);
                }
            } catch (error) {
                console.error("Khong tai duoc loai su co:", error);
                this.incidentTypes = [];
                this.incidentDetails = [];
                this.selectedType = null;
                this.selectedDetailIds = [];
                this.incidentTypeError = "Khong tai duoc loai su co tu backend.";
            } finally {
                this.loadingIncidentTypes = false;
            }
        },
        async taiChiTietLoaiSuCo(typeId) {
            if (!typeId) {
                this.incidentDetails = [];
                this.selectedDetailIds = [];
                return;
            }

            this.loadingDetails = true;
            try {
                const response = await incidentTypeAPI.getDetail(typeId);
                this.incidentDetails = this.chuanHoaChiTietSuCo(response?.data);
                this.selectedDetailIds = [];
            } catch (error) {
                console.error("Khong tai duoc chi tiet loai su co:", error);
                this.incidentDetails = [];
                this.selectedDetailIds = [];
            } finally {
                this.loadingDetails = false;
            }
        },
        async chonLoaiSuCo(typeId) {
            if (Number(this.selectedType) === Number(typeId)) {
                return;
            }

            this.selectedType = typeId;
            this.selectedDetailIds = [];
            await this.taiChiTietLoaiSuCo(typeId);
        },
        layDanhSachChiTietDaChon() {
            const selectedSet = new Set(this.selectedDetailIds.map((id) => Number(id)));

            return this.incidentDetails
                .filter((detail) => selectedSet.has(Number(detail.id)))
                .map((detail) => detail.label);
        },
        taoChuoiChiTietSuCo() {
            return this.layDanhSachChiTietDaChon().join(", ");
        },
        chonCheDoViTri(cheDo) {
            if (this.cheDoViTri === cheDo) {
                return;
            }

            this.cheDoViTri = cheDo;
            this.coordsText = "";
            this.selectedCoords = null;
        },
        async layGps() {
            this.cheDoViTri = CHE_DO_VI_TRI.GPS;
            this.locating = true;
            this.coordsText = "";
            try {
                const map = this.$refs.mapRef;
                if (map?.locateUser) {
                    const { lng, lat } = await map.locateUser();
                    this.selectedCoords = { lng, lat };
                    this.coordsText = `GPS: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                }
            } catch (e) {
                this.coordsText = "Khong lay duoc vi tri (cap quyen trinh duyet hoac dung HTTPS).";
            } finally {
                this.locating = false;
            }
        },
        async xacNhanDiaChiNhap() {
            if (!this.address.trim()) {
                this.hienToast("warning", "Vui long nhap dia chi can cuu ho.");
                return;
            }

            this.dangTimDiaChi = true;
            this.coordsText = "";

            try {
                const ketQua = await timToaDoTheoDiaChi(this.address.trim());
                this.address = ketQua.diaChiDayDu;
                this.selectedCoords = { lat: ketQua.lat, lng: ketQua.lng };
                this.coordsText = `Dia chi da xac nhan: ${ketQua.lat.toFixed(5)}, ${ketQua.lng.toFixed(5)}`;
                this.$refs.mapRef?.flyTo?.(ketQua.lng, ketQua.lat, 15);
                this.hienToast("success", "Da xac nhan vi tri tu dia chi vua nhap.");
            } catch (error) {
                this.selectedCoords = null;
                this.hienToast("error", error.message || "Khong tim thay vi tri tu dia chi da nhap.");
            } finally {
                this.dangTimDiaChi = false;
            }
        },
        handleFileSelect(event) {
            const file = event.target.files?.[0];
            if (!file) {
                this.selectedImageName = "";
                this.selectedImageData = null;
                this.selectedImagePreview = null;
                return;
            }
            this.selectedImageName = file.name;
            this.imageProcessing = true;
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    // Resize ảnh xuống 800x600 hoặc giữ tỷ lệ
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const maxWidth = 800;
                    const maxHeight = 600;
                    let { width, height } = img;

                    if (width > height) {
                        if (width > maxWidth) {
                            height = (height * maxWidth) / width;
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width = (width * maxHeight) / height;
                            height = maxHeight;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);
                    const resizedDataUrl = canvas.toDataURL('image/jpeg', 0.8); // JPEG với quality 0.8
                    this.selectedImagePreview = resizedDataUrl;
                    this.selectedImageData = resizedDataUrl.split(',')[1];
                    this.imageProcessing = false;
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        layIdNguoiDungHienTai() {
            const sources = ["user", "client"];
            for (const key of sources) {
                const raw = localStorage.getItem(key);
                if (!raw) continue;
                try {
                    const parsed = JSON.parse(raw);
                    const id =
                        layGiaTriDauTien(parsed, ["id_nguoi_dung", "id", "user_id"]) ??
                        layGiaTriDauTien(parsed?.data, ["id_nguoi_dung", "id", "user_id"]) ??
                        layGiaTriDauTien(parsed?.user, ["id_nguoi_dung", "id", "user_id"]);
                    if (id !== null && id !== undefined && id !== "") {
                        return Number(id);
                    }
                } catch (error) {
                    console.error(`Khong doc duoc localStorage ${key}:`, error);
                }
            }
            return null;
        },
        async guiYeuCau() {
            const isLoggedIn =
                !!localStorage.getItem("user_token") ||
                !!localStorage.getItem("token") ||
                !!localStorage.getItem("user") ||
                !!localStorage.getItem("client");
            if (!isLoggedIn) {
                if (window.bootstrap) {
                    const modal = new window.bootstrap.Modal(this.$refs.loginModal);
                    modal.show();
                } else {
                    this.hienToast("warning", "Vui long dang nhap hoac dang ky tai khoan de gui yeu cau cuu ho.");
                    this.$router.push("/client/login");
                }
                return;
            }

            const userId = this.layIdNguoiDungHienTai();
            if (!userId) {
                this.hienToast("error", "Khong xac dinh duoc tai khoan hien tai. Vui long dang nhap lai.");
                return;
            }

            if (!this.selectedType) {
                this.hienToast("warning", "Vui long chon loai su co phu hop.");
                return;
            }

            if (this.cheDoViTri === CHE_DO_VI_TRI.NHAP_TAY && !this.address.trim()) {
                this.hienToast("warning", "Vui long nhap dia chi xay ra su co.");
                return;
            }

            if (!this.selectedCoords?.lat || !this.selectedCoords?.lng) {
                this.hienToast(
                    "warning",
                    this.cheDoViTri === CHE_DO_VI_TRI.GPS
                        ? "Vui long bam Xac dinh GPS truoc khi gui yeu cau."
                        : "Vui long bam Dung dia chi nay de xac nhan vi tri truoc khi gui yeu cau."
                );
                return;
            }

            this.submitting = true;
            try {
                const payload = {
                    id_nguoi_dung: userId,
                    id_loai_su_co: Number(this.selectedType),
                    vi_tri_lat: this.selectedCoords.lat,
                    vi_tri_lng: this.selectedCoords.lng,
                    vi_tri_dia_chi: this.address.trim() || this.coordsText,
                    chi_tiet: this.taoChuoiChiTietSuCo(),
                    mo_ta: this.description.trim(),
                    hinh_anh: null, // Tạm thời không gửi ảnh để tránh lỗi database
                    so_nguoi_bi_anh_huong: Number(this.soNguoiBiAnhHuong) || 1,
                    muc_do_khan_cap: this.mucDoKhanCap || "HIGH",
                    diem_uu_tien: this.diemUuTien !== null ? Number(this.diemUuTien) : null,
                    trang_thai: this.trangThai,
                };

                const response = await rescueRequestAPI.create(payload);
                const newId = response?.data?.data?.id_yeu_cau || response?.data?.id_yeu_cau || "";

                this.hienToast(
                    "success",
                    `Gui yeu cau cuu ho thanh cong${newId ? `. Ma yeu cau: ${newId}` : "."}`
                );
                this.description = "";
                this.address = "";
                this.coordsText = "";
                this.cheDoViTri = CHE_DO_VI_TRI.GPS;
                this.selectedCoords = null;
                this.selectedImageName = "";
                this.selectedImageData = null;
                this.selectedImagePreview = null;
                this.imageProcessing = false;
                this.soNguoiBiAnhHuong = 1;
                this.mucDoKhanCap = "HIGH";
                this.diemUuTien = null;
                this.trangThai = null;
                this.selectedType = this.incidentTypes[0]?.id ?? null;
                this.selectedDetailIds = [];
                await this.taiChiTietLoaiSuCo(this.selectedType);
                this.$router.push("/client/requests");
            } catch (error) {
                const message =
                    error?.response?.data?.message ||
                    error?.response?.data?.error ||
                    error?.response?.data?.errors?.id_nguoi_dung?.[0] ||
                    error?.response?.data?.errors?.id_loai_su_co?.[0] ||
                    error?.response?.data?.errors?.vi_tri_lat?.[0] ||
                    error?.response?.data?.errors?.vi_tri_lng?.[0] ||
                    error?.response?.data?.errors?.vi_tri_dia_chi?.[0] ||
                    error?.response?.data?.errors?.chi_tiet?.[0] ||
                    error?.response?.data?.errors?.mo_ta?.[0] ||
                    error?.response?.data?.errors?.muc_do_khan_cap?.[0] ||
                    "Khong the gui yeu cau cuu ho. Vui long kiem tra lai thong tin va thu lai.";
                console.error("Gui yeu cau cuu ho that bai:", error);
                this.hienToast("error", message);
            } finally {
                this.submitting = false;
            }
        },
        goToLogin() {
            document.body.classList.remove("modal-open");
            const backdrops = document.querySelectorAll(".modal-backdrop");
            backdrops.forEach((b) => b.remove());
            this.$router.push("/client/login");
        },
        goToRegister() {
            document.body.classList.remove("modal-open");
            const backdrops = document.querySelectorAll(".modal-backdrop");
            backdrops.forEach((b) => b.remove());
            this.$router.push("/client/register");
        }
    },
};
</script>

<style scoped></style>
