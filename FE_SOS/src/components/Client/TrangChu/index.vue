<template>
    <div class="container-fluid p-0 d-flex flex-column flex-lg-row flex-grow-1 overflow-hidden h-100">
        <div class="col-lg-5 col-xl-4 bg-white p-4 p-xl-5 overflow-y-auto" style="max-height: 100%;">
            <h2 class="fw-bold mb-1">Yêu Cầu Cứu Hộ Mới</h2>
            <p class="text-muted small mb-4">Cung cấp thông tin chính xác để lực lượng phản ứng tiếp cận nhanh nhất.</p>

            <label class="fw-bold small text-uppercase mb-2 d-block text-secondary text-opacity-75">Loại sự cố</label>
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
                        selectedType === type.id
                            ? 'btn-outline-danger active'
                            : 'btn-outline-light text-dark border-light-subtle bg-light'
                    ]">
                        <i :class="['fa-solid fs-4 mb-1 mt-2', type.icon]"></i>
                        <span style="font-size: 10px;" class="fw-bold text-uppercase">{{ type.label }}</span>
                    </button>
                </div>
            </div>

            <div v-if="selectedType" class="mb-4">
                <label class="fw-bold small text-uppercase mb-2 d-block text-secondary text-opacity-75">Bạn cần giúp gì?</label>
                <div v-if="loadingDetails" class="small text-muted text-center py-2 bg-light rounded-3">
                    <i class="fas fa-spinner fa-spin me-2"></i> Dang tai chi tiet...
                </div>
                <div v-else class="d-flex flex-wrap gap-2">
                    <button v-for="detail in incidentDetails" :key="detail.id" type="button" :class="[
                        'btn btn-sm rounded-pill px-3 border-opacity-25 shadow-sm',
                        selectedDetailIds.includes(detail.id)
                            ? 'btn-danger'
                            : 'btn-outline-secondary'
                    ]" @click="chuyenDoiChiTiet(detail.id)">
                        {{ detail.label }}
                    </button>
                    <div v-if="incidentDetails.length === 0" class="small text-muted py-2 px-3 bg-light rounded-3">
                        Khong co chi tiet cho loai su co nay.
                    </div>
                </div>
            </div>

            <!-- <div
                class="bg-light rounded-3 p-3 mb-3 border border-secondary border-opacity-10 shadow-sm position-relative">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="fw-bold small text-uppercase mb-0 text-secondary">Vị Trí của bạn</label>
                    <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold shadow-sm"
                        style="font-size: 10px;" :disabled="locating" @click="layGps">
                        <i class="fa-solid fa-location-crosshairs me-1"></i>
                        {{ locating ? 'Dang lay...' : 'Xác định GPS' }}
                    </button>
                </div>
                <div class="input-group bg-white rounded-2 overflow-hidden shadow-sm">
                    <span class="input-group-text bg-white border-0 py-1">
                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                    </span>
                    <input v-model="addressSearch" type="text" class="form-control border-0 py-1 shadow-none small"
                        placeholder="Tim dia chi..." @input="timDiaChi">
                    <button class="btn btn-secondary border-0 rounded-0 py-1" type="button" @click="timDiaChi"
                        :disabled="!addressSearch.trim()">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
                <div v-if="addressSuggestions.length > 0" class="list-group shadow-sm mt-1 rounded-2 overflow-hidden"
                    style="position: absolute; left: 12px; right: 12px; z-index: 1050; max-height: 120px; overflow-y: auto;">
                    <button v-for="(suggestion, index) in addressSuggestions" :key="index" type="button"
                        class="list-group-item list-group-item-action py-1 px-2 small text-start"
                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                        @click="chonDiaChi(suggestion)">
                        <i class="fa-solid fa-location-dot text-danger me-1"></i>
                        {{ suggestion.display_name }}
                    </button>
                </div>
                <div class="input-group bg-white rounded-2 overflow-hidden shadow-sm mt-2">
                    <span class="input-group-text bg-white border-0 py-1">
                        <i class="fa-solid fa-location-dot text-danger"></i>
                    </span>
                    <input v-model="address" type="text" class="form-control border-0 py-1 shadow-none small bg-light"
                        readonly placeholder="Dia chi se hien thi sau khi chon vi tri...">
                </div>
                <div v-if="coordsText" class="small text-muted mt-1 mb-0 d-flex align-items-center">
                    <i class="fa-solid fa-check-circle text-success me-1"></i>
                    {{ coordsText }}
                    <span v-if="coordsSource" class="ms-2 badge bg-success-subtle text-success" style="font-size: 9px;">
                        {{ coordsSource }}
                    </span>
                </div>
                <div v-if="!selectedCoords" class="small text-muted mt-1 mb-0 fst-italic">
                    <i class="fa-solid fa-hand-pointer me-1"></i>
                    Click len ban do hoac tim dia chi
                </div>
            </div> -->

            <div class="ns-location-picker p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="ns-icon-wrapper me-3">
                            <i class="fa-solid fa-map-location-dot fs-5 text-ns-primary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Vị Trí Cần Cứu Hộ</h5>
                            <p class="text-muted small mb-0" style="font-size: 11px;">
                                <i class="fa-solid fa-circle-check text-success me-1"></i>
                                Chính xác để lực lượng phản ứng nhanh nhất
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-ns-gps btn-sm rounded-pill px-4 fw-bold transition-all"
                        :disabled="locating" @click="layGps">
                        <i :class="['fa-solid me-1', locating ? 'fa-spinner fa-spin' : 'fa-location-crosshairs']"></i>
                        {{ locating ? 'Đang xác định...' : 'Sử dụng GPS' }}
                    </button>
                </div>

                <div class="position-relative mb-3">
                    <div class="ns-search-input-group d-flex align-items-center">
                        <i class="fa-solid fa-magnifying-glass text-muted ms-3 fs-6"></i>
                        <input v-model="addressSearch" type="text"
                            class="form-control ns-search-input border-0 shadow-none ps-3 pe-5"
                            placeholder="Nhập địa chỉ, tên đường hoặc khu vực..."
                            @input="timDiaChi"
                            @keydown.enter.prevent="chonDiaChiDauTien">
                        <button v-if="addressSearch" @click="addressSearch = ''; addressSuggestions = []"
                            class="btn-ns-clear btn btn-link text-muted position-absolute end-0 me-3" type="button">
                            <i class="fa-solid fa-xmark fs-6"></i>
                        </button>
                    </div>

                    <transition name="ns-fade">
                        <div v-if="addressSuggestions.length > 0"
                            class="list-group ns-suggestions-dropdown shadow-lg mt-2 rounded-4 overflow-hidden position-absolute w-100"
                            style="z-index: 1060; max-height: 250px; overflow-y: auto;">
                            <button v-for="(suggestion, index) in addressSuggestions" :key="index" type="button"
                                class="list-group-item list-group-item-action py-3 px-3 border-0 d-flex align-items-start"
                                @click="chonDiaChi(suggestion)">
                                <i class="fa-solid fa-location-dot text-danger mt-1 me-3"></i>
                                <div>
                                    <span class="d-block text-dark fw-medium text-truncate" style="font-size: 13px;">{{
                                        suggestion.display_name }}</span>
                                    <span class="d-block text-muted" style="font-size: 11px;">Xác nhận bằng Tìm
                                        kiếm</span>
                                </div>
                            </button>
                        </div>
                    </transition>
                </div>

                <div class="ns-address-box rounded-4 p-3 d-flex align-items-start border border-dashed">
                    <div class="ns-location-icon-wrapper me-3">
                        <i class="fa-solid fa-map-pin fs-3 text-danger"></i>
                    </div>
                    <div class="flex-grow-1">
                        <label class="text-uppercase fw-bold text-muted small mb-1"
                            style="font-size: 10px; letter-spacing: 0.5px;">Địa chỉ hiện tại</label>
                        <p class="mb-0 fw-bold text-dark ns-selected-address" v-if="address">{{ address }}</p>
                        <p class="mb-0 text-muted fst-italic" v-else>Chưa xác định...</p>
                    </div>
                </div>

                <div class="mt-3 d-flex align-items-center justify-content-between px-2">
                    <div class="small text-muted fst-italic" style="font-size: 10px;">
                        <i class="fa-solid fa-circle-info me-1"></i> Hoặc click trực tiếp lên bản đồ
                    </div>
                    <transition name="ns-fade">
                        <div v-if="coordsText" class="d-flex align-items-center">
                            <span class="badge bg-ns-success text-success rounded-pill px-3 py-1 fw-bold"
                                style="font-size: 10px;">
                                Nguồn: {{ coordsSource }}
                            </span>
                            <span class="ms-3 text-muted fw-mono ns-coords"
                                style="font-size: 10px; letter-spacing: 0.2px;">{{ coordsText.replace('GPS: ', '')
                                }}</span>
                        </div>
                    </transition>
                </div>
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
                <MapboxMap ref="mapRef" class="position-absolute top-0 start-0 w-100 h-100" :enableClick="true"
                    @mapClick="xuLyClickMap" />
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

export default {
    components: { MapboxMap },
    data() {
        return {
            selectedType: null,
            selectedDetailIds: [],
            incidentTypes: [],
            incidentDetails: [],
            loadingIncidentTypes: false,
            loadingDetails: false,
            incidentTypeError: "",
            address: "",
            addressSearch: "",
            addressSuggestions: [],
            coordsSource: "",
            description: "",
            coordsText: "",
            locating: false,
            submitting: false,
            selectedCoords: null,
            selectedImageName: "",
            selectedImageFile: null,
            soNguoiBiAnhHuong: 1,
            mucDoKhanCap: "HIGH",
            diemUuTien: null,
            trangThai: null,
            searchTimeout: null,
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
                this.selectedType = null;
                this.selectedDetailIds = [];

                if (this.incidentTypes.length === 0) {
                    this.incidentTypeError = "Backend chua tra ve du lieu loai su co.";
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
        async taiChiTietLoaiSuCo() {
            if (!this.selectedType) {
                this.incidentDetails = [];
                this.selectedDetailIds = [];
                return;
            }

            this.loadingDetails = true;
            try {
                const response = await incidentTypeAPI.getDetail(this.selectedType);
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
            const id = Number(typeId);

            if (this.selectedType === id) {
                this.selectedType = null;
                this.incidentDetails = [];
                this.selectedDetailIds = [];
            } else {
                this.selectedType = id;
                await this.taiChiTietLoaiSuCo();
            }
        },
        chuyenDoiChiTiet(detailId) {
            const id = Number(detailId);
            const idx = this.selectedDetailIds.indexOf(id);
            if (idx > -1) {
                this.selectedDetailIds.splice(idx, 1);
            } else {
                this.selectedDetailIds.push(id);
            }
        },
        layTenChiTietDangChon() {
            return this.incidentDetails
                .filter((detail) => this.selectedDetailIds.includes(detail.id))
                .map((d) => d.label)
                .join(", ");
        },
        async layGps() {
            this.locating = true;
            this.coordsText = "";
            try {
                const map = this.$refs.mapRef;
                if (map?.locateUser) {
                    const { lng, lat } = await map.locateUser();
                    this.selectedCoords = { lng, lat };
                    this.coordsText = `GPS: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    this.coordsSource = "GPS";
                    await this.diaChiTuCoords(lat, lng);
                }
            } catch (e) {
                this.coordsText = "Khong lay duoc vi tri (cap quyen trinh duyet hoac dung HTTPS).";
            } finally {
                this.locating = false;
            }
        },
        xuLyClickMap({ lng, lat }) {
            this.selectedCoords = { lng, lat };
            this.coordsText = `GPS: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            this.coordsSource = "Ban do";
            this.addressSuggestions = [];
            this.diaChiTuCoords(lat, lng);
        },
        async diaChiTuCoords(lat, lng) {
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=vi`
                );
                const data = await response.json();
                if (data && data.display_name) {
                    this.address = data.display_name;
                }
            } catch (e) {
                console.error("Khong lay duoc dia chi tu toa do:", e);
            }
        },
        async timDiaChi() {
            if (!this.addressSearch.trim()) {
                this.addressSuggestions = [];
                return;
            }

            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            this.searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.addressSearch)}&limit=5&accept-language=vi`
                    );
                    const results = await response.json();
                    this.addressSuggestions = results;
                } catch (e) {
                    console.error("Loi tim dia chi:", e);
                    this.addressSuggestions = [];
                }
            }, 300);
        },
        chonDiaChi(suggestion) {
            const lat = parseFloat(suggestion.lat);
            const lng = parseFloat(suggestion.lon);

            this.selectedCoords = { lng, lat };
            this.coordsText = `GPS: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            this.coordsSource = "Tim kiem";
            this.address = suggestion.display_name;
            this.addressSuggestions = [];
            this.addressSearch = "";

            const map = this.$refs.mapRef;
            if (map?.flyTo) {
                map.flyTo(lng, lat, 16);
            }
        },
        chonDiaChiDauTien() {
            if (this.addressSuggestions.length > 0) {
                this.chonDiaChi(this.addressSuggestions[0]);
            } else if (this.addressSearch.trim()) {
                this.timDiaChi();
            }
        },         handleFileSelect(event) {
            const file = event.target.files?.[0];
            if (!file) {
                this.selectedImageName = "";
                this.selectedImageFile = null;
                return;
            }
            this.selectedImageName = file.name;
            this.selectedImageFile = file;
        }, layIdNguoiDungHienTai() {
            const sources = ["user", "client"];
            for (const key of sources) {
                const raw = localStorage.getItem(key);
                if (!raw) continue;
                try {
                    const parsed = JSON.parse(raw);
                    const id = layGiaTriDauTien(parsed, ["id_nguoi_dung", "id", "user_id"]);
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
            const isLoggedIn = !!localStorage.getItem("token") || !!localStorage.getItem("user") || !!localStorage.getItem("client");
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
                this.hienToast("warning", "Vui long chon it nhat mot loai su co.");
                return;
            }

            if (!this.selectedCoords?.lat || !this.selectedCoords?.lng) {
                this.hienToast("warning", "Vui long chon vi tri tren ban do hoac tim kiem dia chi.");
                return;
            }

            this.submitting = true;
            try {
                const payload = new FormData();
                payload.append('id_nguoi_dung', userId);
                payload.append('id_loai_su_co', JSON.stringify([this.selectedType]));
                payload.append('vi_tri_lat', this.selectedCoords.lat);
                payload.append('vi_tri_lng', this.selectedCoords.lng);
                payload.append('vi_tri_dia_chi', this.address.trim());
                payload.append('chi_tiet', this.layTenChiTietDangChon());
                payload.append('mo_ta', this.description.trim());
                if (this.selectedImageFile) {
                    payload.append('hinh_anh', this.selectedImageFile);
                }
                payload.append('so_nguoi_bi_anh_huong', Number(this.soNguoiBiAnhHuong) || 1);
                payload.append('muc_do_khan_cap', this.mucDoKhanCap || "HIGH");
                if (this.diemUuTien !== null) {
                    payload.append('diem_uu_tien', Number(this.diemUuTien));
                }
                if (this.trangThai) {
                    payload.append('trang_thai', this.trangThai);
                }

                const response = await rescueRequestAPI.create(payload);
                const newId = response?.data?.data?.id_yeu_cau || response?.data?.id_yeu_cau || "";

                this.hienToast(
                    "success",
                    `Gui yeu cau cuu ho thanh cong${newId ? `. Ma yeu cau: ${newId}` : "."}`
                );
                this.description = "";
                this.address = "";
                this.addressSearch = "";
                this.addressSuggestions = [];
                this.coordsText = "";
                this.coordsSource = "";
                this.selectedCoords = null;
                this.selectedImageName = "";
                this.selectedImageFile = null;
                this.soNguoiBiAnhHuong = 1;
                this.mucDoKhanCap = "HIGH";
                this.diemUuTien = null;
                this.trangThai = null;
                this.selectedDetailIds = [];
                this.selectedType = null;
                this.incidentDetails = [];
                this.$router.push("/client/requests");
            } catch (error) {
                const message =
                    error?.response?.data?.message ||
                    error?.response?.data?.errors?.id_nguoi_dung?.[0] ||
                    error?.response?.data?.errors?.id_loai_su_co?.[0] ||
                    error?.response?.data?.errors?.vi_tri_lat?.[0] ||
                    error?.response?.data?.errors?.vi_tri_lng?.[0] ||
                    error?.response?.data?.errors?.hinh_anh?.[0] ||
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

<style scoped>
/* -- CẤU HÌNH CSS CHO PHONG CÁCH MỚI -- */
.text-ns-primary {
    color: #007bff;
}

/* Màu xanh đặc trưng SOS */
.text-ns-success {
    color: #28a745;
}

.bg-ns-success {
    background-color: rgba(40, 167, 69, 0.1);
}

.ns-location-picker {
    background: #f8f9fa;
    /* Màu nền nhẹ, đồng nhất */
    border-radius: 20px;
    box-shadow: 10px 10px 30px rgba(0, 0, 0, 0.05), -10px -10px 30px rgba(255, 255, 255, 0.8);
    /* Neumorphic shadow */
}

/* Nút GPS */
.btn-ns-gps {
    background-color: #007bff;
    color: white;
    border: none;
    font-size: 12px;
    letter-spacing: 0.3px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 6px rgba(0, 123, 255, 0.2);
}

.btn-ns-gps:hover:not(:disabled) {
    background-color: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 123, 255, 0.3);
}

.btn-ns-gps:disabled {
    background-color: #a0cfff;
    opacity: 0.8;
}

/* Ô tìm kiếm */
.ns-search-input-group {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.05), inset -2px -2px 5px rgba(255, 255, 255, 1);
    /* Khối chìm vào */
    overflow: hidden;
}

.ns-search-input {
    height: 45px;
    font-size: 14px;
    background: transparent;
}

.btn-ns-clear {
    padding: 0;
    transition: color 0.2s ease;
}

.btn-ns-clear:hover {
    color: #ff4757;
}

/* Danh sách gợi ý */
.ns-suggestions-dropdown {
    border: none;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    transform-origin: top;
}

.list-group-item-action {
    transition: background-color 0.2s ease;
}

.list-group-item-action:hover {
    background-color: #f1f8ff;
}

/* Box hiển thị địa chỉ */
.ns-address-box {
    background: #ffffff;
    border: 2px dashed rgba(0, 123, 255, 0.2);
    box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.03);
}

.ns-location-icon-wrapper {
    background: rgba(255, 71, 87, 0.1);
    padding: 10px;
    border-radius: 12px;
}

.ns-selected-address {
    font-size: 14px;
    line-height: 1.4;
}

/* Tọa độ */
.ns-coords {
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    /* Font mono cho tọa độ */
    font-size: 11px;
}

/* Hiệu ứng Fade cho Transition */
.ns-fade-enter-active,
.ns-fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.ns-fade-enter-from,
.ns-fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
