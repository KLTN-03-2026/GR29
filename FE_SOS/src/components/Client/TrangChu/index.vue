<template>
    <div class="sos-page">
        <!-- ====== HEADER ====== -->
        <div class="sos-header">
            <div class="sos-header__badge">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div class="sos-header__text">
                <h1 class="sos-header__title">Yêu Cầu Cứu Hộ </h1>
                <p class="sos-header__subtitle">Thông tin chính xác giúp lực lượng phản ứng tiếp cận nhanh nhất.</p>
            </div>
        </div>

        <!-- ====== MAIN LAYOUT ====== -->
        <div class="sos-layout">
            <!-- LEFT PANEL: Form -->
            <aside class="sos-form-panel" role="form" aria-label="Form yeu cau cuu ho">
                <!-- Section: Loai su co -->
                <section class="sos-section" aria-labelledby="section-type">
                    <div class="sos-section__header">
                        <span class="sos-section__step">1</span>
                        <h2 class="sos-section__title" id="section-type"> Vấn Đề</h2>
                    </div>

                    <!-- Loading state -->
                    <div v-if="loadingIncidentTypes" class="sos-skeleton-grid">
                        <div v-for="n in 6" :key="n" class="sos-skeleton-card"></div>
                    </div>

                    <!-- Error state -->
                    <div v-else-if="incidentTypes.length === 0" class="sos-alert sos-alert--danger" role="alert">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        <span>{{ incidentTypeError || "Chua co du lieu loai su co tu he thong." }}</span>
                    </div>

                    <!-- Incident type grid -->
                    <div v-else class="sos-type-grid" role="group" aria-label="Chon loai su co">
                        <button
                            v-for="type in incidentTypes"
                            :key="type.id"
                            type="button"
                            :class="['sos-type-card', { 'sos-type-card--active': selectedType === type.id }]"
                            :aria-pressed="selectedType === type.id"
                            :aria-label="type.label"
                            @click="chonLoaiSuCo(type.id)"
                        >
                            <div class="sos-type-card__icon">
                                <i :class="['fa-solid', type.icon]" aria-hidden="true"></i>
                            </div>
                            <span class="sos-type-card__label">{{ type.label }}</span>
                        </button>
                    </div>
                </section>

                <!-- Section: Chi tiet -->
                <transition name="sos-slide">
                    <section v-if="selectedType" class="sos-section" aria-labelledby="section-detail">
                        <div class="sos-section__header">
                            <span class="sos-section__step">2</span>
                            <h2 class="sos-section__title" id="section-detail">Bạn cần giúp gì?</h2>
                        </div>

                        <div v-if="loadingDetails" class="sos-loading-row">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            <span>Đang tải chi tiết...</span>
                        </div>

                        <div v-else class="sos-chip-group" role="group" aria-label="Chon chi tiet su co">
                            <button
                                v-for="detail in incidentDetails"
                                :key="detail.id"
                                type="button"
                                :class="['sos-chip', { 'sos-chip--active': selectedDetailIds.includes(detail.id) }]"
                                :aria-pressed="selectedDetailIds.includes(detail.id)"
                                @click="chuyenDoiChiTiet(detail.id)"
                            >
                                {{ detail.label }}
                            </button>
                        </div>

                        <p v-if="!loadingDetails && incidentDetails.length === 0" class="sos-empty-hint">
                            Không có chi tiết cho loại vấn đề này.
                        </p>
                    </section>
                </transition>

                <!-- Section: Vi tri -->
                <section class="sos-section sos-location-section" aria-labelledby="section-location">
                    <div class="sos-section__header">
                        <span class="sos-section__step">3</span>
                        <h2 class="sos-section__title" id="section-location">Vị Trí Cứu Hộ</h2>
                        <button
                            type="button"
                            class="sos-gps-btn"
                            :disabled="locating"
                            @click="layGps"
                            aria-label="Lay vi tri GPS hien tai"
                        >
                            <svg v-if="!locating" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v4M12 18v4M2 12h4M18 12h4"/></svg>
                            <i v-else class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                            <span>{{ locating ? 'Dang xác định...' : 'GPS' }}</span>
                        </button>
                    </div>

                    <!-- Search input -->
                    <div class="sos-search-wrapper">
                        <div class="sos-search-input-wrap">
                            <svg class="sos-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input
                                v-model="addressSearch"
                                type="text"
                                class="sos-search-input"
                                placeholder="Nhập địa chỉ, tên đường, khu vực..."
                                autocomplete="off"
                                @input="timDiaChi"
                                aria-label="Tìm kiếm địa chỉ"
                            />
                            <button
                                v-if="addressSearch"
                                type="button"
                                class="sos-search-clear"
                                @click="addressSearch = ''; addressSuggestions = []"
                                aria-label="Xoa tim kiem"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <transition name="sos-fade">
                            <ul
                                v-if="addressSuggestions.length > 0"
                                class="sos-suggestions"
                                role="listbox"
                                aria-label="Goi y dia chi"
                            >
                                <li
                                    v-for="(suggestion, index) in addressSuggestions"
                                    :key="index"
                                    class="sos-suggestion-item"
                                    role="option"
                                    @click="chonDiaChi(suggestion)"
                                >
                                    <div class="sos-suggestion-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </div>
                                    <div class="sos-suggestion-text">
                                        <span class="sos-suggestion-main">{{ suggestion.display_name }}</span>
                                        <span class="sos-suggestion-sub">Xác nhận bằng Tìm kiếm</span>
                                    </div>
                                </li>
                            </ul>
                        </transition>
                    </div>

                    <!-- Selected address display -->
                    <div :class="['sos-address-card', { 'sos-address-card--filled': address }]">
                        <div class="sos-address-card__icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div class="sos-address-card__content">
                            <label class="sos-address-card__label">Địa chỉ hiện tại</label>
                            <p class="sos-address-card__value" v-if="address">{{ address }}</p>
                            <p class="sos-address-card__placeholder" v-else>Chưa xác định vị trí...</p>
                        </div>
                    </div>

                    <!-- Coordinates info -->
                    <div v-if="coordsText" class="sos-coords-bar">
                        <span class="sos-coords-source">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                            Nguồn: {{ coordsSource }}
                        </span>
                        <span class="sos-coords-value">{{ coordsText.replace('GPS: ', '') }}</span>
                    </div>

                    <p class="sos-hint-text">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Hoặc click trực tiếp lên bản đồ bên phải
                    </p>
                </section>

                <!-- Section: Mo ta -->
                <section class="sos-section" aria-labelledby="section-desc">
                    <div class="sos-section__header">
                        <span class="sos-section__step">4</span>
                        <h2 class="sos-section__title" id="section-desc">Mô tả tình huống</h2>
                    </div>
                    <textarea
                        v-model="description"
                        class="sos-textarea"
                        rows="3"
                        placeholder="Số người bị nạn, tình trạng hiện tại, thông tin thêm..."
                        aria-label="Mô tả tình huống"
                    ></textarea>
                </section>

                <!-- Section: Hinh anh -->
                <section class="sos-section" aria-labelledby="section-image">
                    <div class="sos-section__header">
                        <span class="sos-section__step">5</span>
                        <h2 class="sos-section__title" id="section-image">Ảnh Hiện Trường</h2>
                    </div>
                    <label class="sos-upload-zone">
                        <input
                            type="file"
                            class="d-none"
                            accept="image/*,video/*"
                            @change="handleFileSelect"
                            aria-label="Tai len anh hoac video"
                        />
                        <div class="sos-upload-zone__inner">
                            <div class="sos-upload-zone__icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            </div>
                            <p class="sos-upload-zone__text">Tải lên ảnh/video</p>
                            <p class="sos-upload-zone__hint">Click hoặc kéo thả file vào đây</p>
                        </div>
                    </label>
                    <p v-if="selectedImageName" class="sos-file-name">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                        {{ selectedImageName }}
                    </p>
                </section>

                <!-- Section: Lien he (guest only) -->
                <transition name="sos-slide">
                    <section v-if="!isUserLoggedIn" class="sos-section sos-guest-section" aria-labelledby="section-contact">
                        <div class="sos-guest-badge">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Ban chua dang nhap</span>
                        </div>
                        <div class="sos-field">
                            <label class="sos-field__label" for="guest-phone">Số điện thoại liên lạc (bắt buộc)</label>
                            <input
                                id="guest-phone"
                                v-model="guestPhone"
                                type="tel"
                                class="sos-input"
                                placeholder="0xxxxxxxxx"
                                autocomplete="tel"
                                @input="validateGuestPhone"
                            />
                            <p v-if="guestPhoneError" class="sos-field__error" role="alert">{{ guestPhoneError }}</p>
                        </div>
                        <p class="sos-hint-text">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            Sau khi đăng ký tài khoản, các yêu cầu sẽ được theo dõi ngay.
                        </p>
                    </section>
                </transition>

                <!-- Submit CTA -->
                <button
                    type="button"
                    class="sos-submit-btn"
                    :disabled="submitting"
                    @click="guiYeuCau"
                    aria-label="Gui yeu cau cuu ho"
                >
                    <span v-if="!submitting" class="sos-submit-btn__content">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.08 6.08l1.87-1.87a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.5 16a2 2 0 0 1 .5.92z"/></svg>
                        Gửi Cứu Hộ Ngay
                    </span>
                    <span v-else class="sos-submit-btn__content">
                        <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                        Dang gui...
                    </span>
                </button>
            </aside>

            <!-- RIGHT PANEL: Map -->
            <div class="sos-map-panel" role="complementary" aria-label="Ban do vi tri">
                <div class="sos-map-container">
                    <MapboxMap
                        ref="mapRef"
                        class="sos-map"
                        :enableClick="true"
                        @mapClick="xuLyClickMap"
                    />

                    <!-- Units overlay -->
                    <div class="sos-units-card" aria-label="Lực lượng cứu hộ">
                        <h3 class="sos-units-card__title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Lực Lượng Cứu Hộ
                        </h3>
                        <ul class="sos-units-list">
                            <li v-for="u in units" :key="u.name" class="sos-units-item">
                                <div :class="['sos-units-item__icon', u.c]">
                                    <i :class="['fa-solid', u.i]" aria-hidden="true"></i>
                                </div>
                                <div class="sos-units-item__info">
                                    <span class="sos-units-item__name">{{ u.name }}</span>
                                    <span class="sos-units-item__dist">{{ u.d }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <Transition name="sos-rl-fade">
            <div
                v-if="rateLimitModalOpen"
                class="sos-rl-overlay"
                role="dialog"
                aria-modal="true"
                aria-labelledby="sos-rl-title"
            >
                <div class="sos-rl-backdrop" @click="closeRateLimitModal"></div>
                <div class="sos-rl-dialog">
                    <div class="sos-rl-icon-wrap" aria-hidden="true">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <h2 id="sos-rl-title" class="sos-rl-title">Đã đạt giới hạn gửi yêu cầu</h2>
                    <p class="sos-rl-desc">{{ rateLimitModalMessage }}</p>
                    <div class="sos-rl-countdown" aria-live="polite">
                        <span class="sos-rl-countdown__label">Thời gian còn lại</span>
                        <span class="sos-rl-countdown__value">{{ rateLimitCountdownLabel }}</span>
                    </div>
                    <p v-if="rateLimitSecondsRemaining <= 0" class="sos-rl-ready">Bạn có thể thử gửi lại.</p>
                    <button type="button" class="sos-rl-btn" @click="closeRateLimitModal">Đóng</button>
                </div>
            </div>
        </Transition>
    </Teleport>

    <Teleport to="body">
        <Transition name="sos-gs-fade">
            <div
                v-if="guestPostSuccessModalOpen"
                class="sos-gs-overlay"
                role="dialog"
                aria-modal="true"
                aria-labelledby="sos-gs-title"
            >
                <div class="sos-gs-backdrop" @click="closeGuestPostSuccessModal"></div>
                <div class="sos-gs-dialog">
                    <div class="sos-gs-icon" aria-hidden="true">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h2 id="sos-gs-title" class="sos-gs-title">Yêu cầu đã được gửi</h2>
                    <p v-if="guestPostSuccessRequestId" class="sos-gs-lead">
                        Mã yêu cầu: <strong>#{{ guestPostSuccessRequestId }}</strong>
                    </p>
                    <p class="sos-gs-text">Hệ thống đã tiếp nhận yêu cầu cứu hộ của bạn.</p>
                    <p class="sos-gs-highlight">
                        Nếu bạn muốn xem trạng thái yêu cầu vừa gửi, vui lòng
                        <strong>đăng ký tài khoản</strong> dùng
                        <strong>cùng số điện thoại</strong> bạn vừa nhập
                        (<strong>{{ guestPostSuccessModalPhone }}</strong>) trên
                        <strong>thiết bị này</strong>. Sau khi đăng ký, các yêu cầu đã gửi có thể được
                        liên kết với tài khoản của bạn.
                    </p>
                    <div class="sos-gs-actions">
                        <button type="button" class="sos-gs-btn sos-gs-btn--ghost" @click="closeGuestPostSuccessModal">
                            Đóng
                        </button>
                        <button type="button" class="sos-gs-btn sos-gs-btn--primary" @click="goDangKySauGuiKhach">
                            Đăng ký
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";
import { incidentTypeAPI, rescueRequestAPI, guestAPI } from "../../../services/api";

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

/** Phản hồi giới hạn gửi yêu cầu (429 hoặc message từ backend). */
function laPhanHoiGioiHanGuiYeuCau(error) {
    const status = Number(error?.response?.status);
    const data = error?.response?.data;
    const msg = typeof data?.message === "string" ? data.message : "";
    const needle = "chỉ được gửi tối đa 3 yêu cầu cứu hộ trong 15 phút";
    if (status === 429) return true;
    if (msg.includes(needle)) return true;
    return false;
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
            diemUuTien: null,
            trangThai: null,
            searchTimeout: null,
            guestPhone: "",
            guestPhoneError: "",
            deviceId: "",
            guestSessionId: null,
            units: [
                { name: "Cảnh sát", d: "1.2 km - 5p", i: "fa-shield-halved", c: "bg-primary", t: "text-primary" },
                { name: "BV Đa khoa", d: "0.8 km - 3p", i: "fa-hospital", c: "bg-danger", t: "text-danger" }
            ],
            rateLimitModalOpen: false,
            rateLimitModalMessage: "",
            rateLimitSecondsRemaining: 0,
            rateLimitTimerId: null,
            guestPostSuccessModalOpen: false,
            guestPostSuccessModalPhone: "",
            guestPostSuccessRequestId: "",
        };
    },
    computed: {
        isUserLoggedIn() {
            return !!(localStorage.getItem("token") || localStorage.getItem("user") || localStorage.getItem("client"));
        },
        rateLimitCountdownLabel() {
            const s = Math.max(0, Math.floor(Number(this.rateLimitSecondsRemaining) || 0));
            const m = Math.floor(s / 60);
            const r = s % 60;
            return `${String(m).padStart(2, "0")}:${String(r).padStart(2, "0")}`;
        },
    },
    mounted() {
        window.addEventListener('storage', this.handleStorageChange);
    },
    beforeUnmount() {
        window.removeEventListener('storage', this.handleStorageChange);
        this.closeRateLimitModal();
        this.closeGuestPostSuccessModal();
    },
    async created() {
        await this.taiDanhSachLoaiSuCo();
        this.khoiTaoDeviceId();
    },
    methods: {
        handleStorageChange() {
            this.$forceUpdate();
        },
        khoiTaoDeviceId() {
            let stored = localStorage.getItem('guest_device_id');
            if (!stored) {
                stored = 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('guest_device_id', stored);
            }
            this.deviceId = stored;
        },
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
        closeRateLimitModal() {
            if (this.rateLimitTimerId != null) {
                clearInterval(this.rateLimitTimerId);
                this.rateLimitTimerId = null;
            }
            this.rateLimitModalOpen = false;
            this.rateLimitModalMessage = "";
            this.rateLimitSecondsRemaining = 0;
        },
        closeGuestPostSuccessModal() {
            this.guestPostSuccessModalOpen = false;
            this.guestPostSuccessModalPhone = "";
            this.guestPostSuccessRequestId = "";
        },
        goDangKySauGuiKhach() {
            const phone = this.guestPostSuccessModalPhone || "";
            this.closeGuestPostSuccessModal();
            this.$router.push({
                path: "/client/register",
                query: phone ? { phone } : {},
            });
        },
        openRateLimitModal(retryAfterSeconds, message) {
            this.closeRateLimitModal();
            const sec = Math.max(0, Math.ceil(Number(retryAfterSeconds) || 0));
            this.rateLimitModalMessage =
                message ||
                "Mỗi tài khoản hoặc thiết bị chỉ được gửi tối đa 3 yêu cầu cứu hộ trong 15 phút.";
            this.rateLimitSecondsRemaining = sec;
            this.rateLimitModalOpen = true;
            if (sec <= 0) {
                return;
            }
            this.rateLimitTimerId = setInterval(() => {
                if (this.rateLimitSecondsRemaining <= 1) {
                    this.rateLimitSecondsRemaining = 0;
                    if (this.rateLimitTimerId != null) {
                        clearInterval(this.rateLimitTimerId);
                        this.rateLimitTimerId = null;
                    }
                } else {
                    this.rateLimitSecondsRemaining -= 1;
                }
            }, 1000);
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
                    this.incidentTypeError = "Backend chưa trả về dữ liệu loại vấn đề.";
                }
            } catch (error) {
                console.error("Không tải được loại vấn đề:", error);
                this.incidentTypes = [];
                this.incidentDetails = [];
                this.selectedType = null;
                this.selectedDetailIds = [];
                this.incidentTypeError = "Không tải được loại vấn đề từ backend.";
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
                console.error("Không tải được chi tiết loại vấn đề:", error);
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
                }
            } catch (e) {
                this.coordsText = "Không lấy được vị trí (cấp quyền trình duyệt hoặc dùng HTTPS).";
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
                console.error("Không lấy được địa chỉ từ tọa độ:", e);
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
        handleFileSelect(event) {
            const file = event.target.files?.[0];
            if (!file) {
                this.selectedImageName = "";
                this.selectedImageFile = null;
                return;
            }
            this.selectedImageName = file.name;
            this.selectedImageFile = file;
        },
        layIdNguoiDungHienTai() {
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
        validateGuestPhone() {
            const phone = this.guestPhone.trim();
            const phoneRegex = /^0\d{9,10}$/;
            if (!phone) {
                this.guestPhoneError = "Bạn chưa nhập số điện thoại.";
                return false;
            }
            if (!phoneRegex.test(phone)) {
                this.guestPhoneError = "Số điện thoại không hợp lệ.";
                return false;
            }
            this.guestPhoneError = "";
            return true;
        },
        async createGuestSession() {
            const response = await guestAPI.createOrUpdateSession({
                so_dien_thoai: this.guestPhone.trim(),
                device_id: this.deviceId,
            });
            this.guestSessionId = response.data.data.id;
        },
        async guiYeuCau() {
            const loggedIn = this.isUserLoggedIn;
            if (!this.selectedType) {
                this.hienToast("warning", "Vui lòng chọn loại vấn đề.");
                return;
            }

            if (!this.selectedDetailIds || this.selectedDetailIds.length === 0) {
                this.hienToast("warning", "Vui lòng cho chúng tôi biết bạn cần giúp gì.");
                return;
            }

            if (!this.selectedCoords?.lat || !this.selectedCoords?.lng) {
                this.hienToast("warning", "Vui lòng chọn vị trí trên bản đồ hoặc tìm kiếm địa chỉ.");
                return;
            }

            if (loggedIn) {
                const userId = this.layIdNguoiDungHienTai();
                if (!userId) {
                    this.hienToast("error", "Không xác định được tài khoản hiện tại. Vui lòng đăng nhập lại.");
                    return;
                }
            } else {
                if (!this.validateGuestPhone()) {
                    this.hienToast("warning", "Vui lòng nhập số điện thoại để gửi yêu cầu cứu hộ.");
                    return;
                }
            }

            this.submitting = true;
            try {
                const payload = new FormData();
                if (loggedIn) {
                    payload.append('id_nguoi_dung', this.layIdNguoiDungHienTai());
                } else {
                    await this.createGuestSession();
                    payload.append('guest_session_id', this.guestSessionId);
                    payload.append('device_id', this.deviceId);
                }
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
                if (this.diemUuTien !== null) {
                    payload.append('diem_uu_tien', Number(this.diemUuTien));
                }
                if (this.trangThai) {
                    payload.append('trang_thai', this.trangThai);
                }

                const response = await rescueRequestAPI.create(payload);
                const newId = response?.data?.data?.id_yeu_cau || response?.data?.id_yeu_cau || "";
                const phoneDaGui = loggedIn ? "" : this.guestPhone.trim();

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
                this.diemUuTien = null;
                this.trangThai = null;
                this.selectedDetailIds = [];
                this.selectedType = null;
                this.incidentDetails = [];
                if (loggedIn) {
                    this.hienToast(
                        "success",
                        `Gui yeu cau cuu ho thanh cong${newId ? `. Ma yeu cau: ${newId}` : "."}`
                    );
                    this.$router.push("/client/requests");
                } else {
                    this.guestPostSuccessRequestId = newId ? String(newId) : "";
                    this.guestPostSuccessModalPhone = phoneDaGui;
                    this.guestPhone = "";
                    this.guestPhoneError = "";
                    this.guestPostSuccessModalOpen = true;
                }
            } catch (error) {
                const hinhAnhLoi = error?.response?.data?.errors?.hinh_anh?.[0] || "";
                let message;
                if (laPhanHoiGioiHanGuiYeuCau(error)) {
                    const data = error?.response?.data;
                    const retryAfter = Number(data?.retry_after);
                    const msg =
                        (typeof data?.message === "string" && data.message) ||
                        "Mỗi tài khoản hoặc thiết bị chỉ được gửi tối đa 3 yêu cầu cứu hộ trong 15 phút.";
                    this.openRateLimitModal(Number.isFinite(retryAfter) ? retryAfter : 0, msg);
                    console.error("Giới hạn gửi yêu cầu:", error?.response?.status, data);
                    return;
                } else if (hinhAnhLoi && (hinhAnhLoi.toLowerCase().includes("xác thực") || hinhAnhLoi.toLowerCase().includes("authentication") || hinhAnhLoi.toLowerCase().includes("xac thuc"))) {
                    message = "Bạn chưa gửi ảnh.";
                } else if (hinhAnhLoi) {
                    message = hinhAnhLoi;
                } else {
                    const errs = error?.response?.data?.errors;
                    const firstValidation =
                        errs && typeof errs === "object"
                            ? Object.values(errs).flat().find(Boolean)
                            : null;
                    message =
                        error?.response?.data?.message ||
                        firstValidation ||
                        error?.response?.data?.errors?.id_loai_su_co?.[0] ||
                        error?.response?.data?.errors?.vi_tri_lat?.[0] ||
                        error?.response?.data?.errors?.vi_tri_lng?.[0] ||
                        "Không thể gửi yêu cầu cứu hộ. Vui lòng kiểm tra lại thông tin và thử lại.";
                }
                console.error("Gửi yêu cầu cứu hộ thất bại:", error?.response?.status, error?.response?.data);
                this.hienToast("error", message);
            } finally {
                this.submitting = false;
            }
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
/* ============================================================
   DESIGN SYSTEM: SOS Emergency Rescue
   Style: Accessible & Ethical (WCAG AAA)
   Colors: Cyan primary + Green CTA + Light backgrounds
   ============================================================ */

/* --- ROOT TOKENS --- */
.sos-page {
    --sos-primary: #0891B2;
    --sos-primary-dark: #0E7490;
    --sos-primary-light: #22D3EE;
    --sos-primary-bg: #ECFEFF;
    --sos-cta: #059669;
    --sos-cta-hover: #047857;
    --sos-danger: #DC2626;
    --sos-danger-bg: #FEF2F2;
    --sos-text: #164E63;
    --sos-text-secondary: #64748B;
    --sos-text-muted: #94A3B8;
    --sos-border: #E2E8F0;
    --sos-surface: #FFFFFF;
    --sos-surface-alt: #F8FAFC;
    --sos-radius-sm: 8px;
    --sos-radius: 12px;
    --sos-radius-lg: 16px;
    --sos-radius-xl: 20px;
    --sos-shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
    --sos-shadow: 0 4px 12px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.04);
    --sos-shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.08), 0 4px 8px rgba(0, 0, 0, 0.04);
    --sos-transition: 0.2s ease;
    --sos-font: 'Noto Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-family: var(--sos-font);
    color: var(--sos-text);
    background: var(--sos-primary-bg);
    min-height: 100vh;
    padding: 0;
}

/* --- HEADER --- */
.sos-header {
    background: linear-gradient(135deg, var(--sos-primary) 0%, var(--sos-primary-dark) 100%);
    padding: 12px 32px;
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
    overflow: hidden;
}

.sos-header::after {
    content: '';
    position: absolute;
    top: -40px;
    right: -40px;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.06);
}

.sos-header__badge {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.sos-header__title {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 4px;
    line-height: 1.2;
    letter-spacing: -0.3px;
}

.sos-header__subtitle {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.85);
    margin: 0;
    line-height: 1.4;
}

/* --- LAYOUT --- */
.sos-layout {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 104px);
}

@media (min-width: 992px) {
    .sos-layout {
        flex-direction: row;
        height: calc(100vh - 104px);
    }
}

/* --- FORM PANEL --- */
.sos-form-panel {
    flex: 1;
    overflow-y: auto;
    padding: 20px 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-width: 100%;
}

@media (min-width: 992px) {
    .sos-form-panel {
        width: 420px;
        min-width: 380px;
        max-width: 460px;
        padding: 24px 20px;
        border-right: 1px solid var(--sos-border);
        background: var(--sos-surface);
        overflow-y: auto;
    }
}

/* --- SECTIONS --- */
.sos-section {
    background: var(--sos-surface);
    border-radius: var(--sos-radius-lg);
    padding: 18px 20px;
    border: 1px solid var(--sos-border);
    box-shadow: var(--sos-shadow-sm);
}

.sos-section__header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}

.sos-section__step {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--sos-primary);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sos-section__title {
    font-size: 15px;
    font-weight: 700;
    color: var(--sos-text);
    margin: 0;
    letter-spacing: -0.2px;
}

/* --- INCIDENT TYPE GRID --- */
.sos-type-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.sos-type-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 14px 8px 12px;
    border-radius: var(--sos-radius);
    border: 2px solid var(--sos-border);
    background: var(--sos-surface-alt);
    cursor: pointer;
    transition: all var(--sos-transition);
    min-height: 80px;
}

.sos-type-card:hover {
    border-color: var(--sos-primary);
    background: var(--sos-primary-bg);
    transform: translateY(-1px);
    box-shadow: var(--sos-shadow-sm);
}

.sos-type-card:focus-visible {
    outline: 3px solid var(--sos-primary);
    outline-offset: 2px;
}

.sos-type-card--active {
    border-color: var(--sos-danger);
    background: var(--sos-danger-bg);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.12);
}

.sos-type-card--active .sos-type-card__icon {
    color: var(--sos-danger);
}

.sos-type-card__icon {
    font-size: 24px;
    color: var(--sos-primary);
    transition: color var(--sos-transition);
}

.sos-type-card__label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: var(--sos-text-secondary);
    text-align: center;
    line-height: 1.2;
}

/* --- SKELETON LOADING --- */
.sos-skeleton-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.sos-skeleton-card {
    height: 80px;
    border-radius: var(--sos-radius);
    background: linear-gradient(90deg, #f0f4f8 25%, #e2e8f0 50%, #f0f4f8 75%);
    background-size: 200% 100%;
    animation: sos-skeleton 1.5s ease-in-out infinite;
}

@keyframes sos-skeleton {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* --- CHIPS --- */
.sos-chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.sos-chip {
    padding: 7px 16px;
    border-radius: 20px;
    border: 2px solid var(--sos-border);
    background: var(--sos-surface-alt);
    font-size: 13px;
    font-weight: 500;
    color: var(--sos-text-secondary);
    cursor: pointer;
    transition: all var(--sos-transition);
}

.sos-chip:hover {
    border-color: var(--sos-primary);
    color: var(--sos-primary);
    background: var(--sos-primary-bg);
}

.sos-chip:focus-visible {
    outline: 3px solid var(--sos-primary);
    outline-offset: 2px;
}

.sos-chip--active {
    border-color: var(--sos-danger);
    background: var(--sos-danger);
    color: #fff;
    font-weight: 600;
}

/* --- LOCATION SECTION --- */
.sos-location-section .sos-section__header {
    flex-wrap: wrap;
}

.sos-gps-btn {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 20px;
    background: var(--sos-primary);
    color: #fff;
    border: none;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--sos-transition);
    box-shadow: 0 2px 8px rgba(8, 145, 178, 0.3);
}

.sos-gps-btn:hover:not(:disabled) {
    background: var(--sos-primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 145, 178, 0.35);
}

.sos-gps-btn:focus-visible {
    outline: 3px solid var(--sos-primary);
    outline-offset: 2px;
}

.sos-gps-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Search input */
.sos-search-wrapper {
    position: relative;
    margin-bottom: 12px;
}

.sos-search-input-wrap {
    display: flex;
    align-items: center;
    background: var(--sos-surface-alt);
    border: 2px solid var(--sos-border);
    border-radius: var(--sos-radius);
    padding: 0 12px;
    gap: 10px;
    transition: all var(--sos-transition);
}

.sos-search-input-wrap:focus-within {
    border-color: var(--sos-primary);
    background: var(--sos-surface);
    box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12);
}

.sos-search-icon {
    color: var(--sos-text-muted);
    flex-shrink: 0;
}

.sos-search-input {
    flex: 1;
    height: 44px;
    border: none;
    background: transparent;
    font-size: 14px;
    color: var(--sos-text);
    outline: none;
}

.sos-search-input::placeholder {
    color: var(--sos-text-muted);
}

.sos-search-clear {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: none;
    background: var(--sos-border);
    border-radius: 50%;
    color: var(--sos-text-secondary);
    cursor: pointer;
    transition: all var(--sos-transition);
    flex-shrink: 0;
}

.sos-search-clear:hover {
    background: var(--sos-danger);
    color: #fff;
}

/* Suggestions dropdown */
.sos-suggestions {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: var(--sos-surface);
    border: 1px solid var(--sos-border);
    border-radius: var(--sos-radius);
    box-shadow: var(--sos-shadow-lg);
    list-style: none;
    margin: 0;
    padding: 6px;
    z-index: 200;
    max-height: 260px;
    overflow-y: auto;
}

.sos-suggestion-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    border-radius: var(--sos-radius-sm);
    cursor: pointer;
    transition: background var(--sos-transition);
}

.sos-suggestion-item:hover {
    background: var(--sos-primary-bg);
}

.sos-suggestion-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--sos-danger-bg);
    color: var(--sos-danger);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

.sos-suggestion-text {
    flex: 1;
    min-width: 0;
}

.sos-suggestion-main {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--sos-text);
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sos-suggestion-sub {
    display: block;
    font-size: 11px;
    color: var(--sos-text-muted);
    margin-top: 2px;
}

/* Address card */
.sos-address-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    border-radius: var(--sos-radius);
    border: 2px dashed var(--sos-border);
    background: var(--sos-surface-alt);
    transition: all var(--sos-transition);
}

.sos-address-card--filled {
    border-color: var(--sos-primary);
    border-style: solid;
    background: var(--sos-primary-bg);
}

.sos-address-card__icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--sos-danger-bg);
    color: var(--sos-danger);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sos-address-card__content {
    flex: 1;
    min-width: 0;
}

.sos-address-card__label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--sos-text-muted);
    margin-bottom: 3px;
}

.sos-address-card__value {
    font-size: 13px;
    font-weight: 600;
    color: var(--sos-text);
    line-height: 1.4;
    margin: 0;
    word-break: break-word;
}

.sos-address-card__placeholder {
    font-size: 13px;
    font-style: italic;
    color: var(--sos-text-muted);
    margin: 0;
}

.sos-coords-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 8px;
    padding: 0 2px;
}

.sos-coords-source {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 600;
    color: var(--sos-cta);
    background: rgba(5, 150, 105, 0.08);
    padding: 3px 10px;
    border-radius: 20px;
}

.sos-coords-value {
    font-size: 11px;
    font-family: 'Consolas', 'Monaco', monospace;
    color: var(--sos-text-muted);
    letter-spacing: 0.3px;
}

/* --- TEXTAREA --- */
.sos-textarea {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid var(--sos-border);
    border-radius: var(--sos-radius);
    background: var(--sos-surface-alt);
    font-size: 14px;
    font-family: var(--sos-font);
    color: var(--sos-text);
    resize: vertical;
    transition: all var(--sos-transition);
    line-height: 1.5;
    min-height: 80px;
}

.sos-textarea:focus {
    outline: none;
    border-color: var(--sos-primary);
    background: var(--sos-surface);
    box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12);
}

.sos-textarea::placeholder {
    color: var(--sos-text-muted);
}

/* --- UPLOAD ZONE --- */
.sos-upload-zone {
    display: block;
    cursor: pointer;
    border-radius: var(--sos-radius);
}

.sos-upload-zone__inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    border: 2px dashed var(--sos-border);
    border-radius: var(--sos-radius);
    background: var(--sos-surface-alt);
    transition: all var(--sos-transition);
    text-align: center;
}

.sos-upload-zone:hover .sos-upload-zone__inner {
    border-color: var(--sos-primary);
    background: var(--sos-primary-bg);
}

.sos-upload-zone__icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: var(--sos-primary-bg);
    color: var(--sos-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
}

.sos-upload-zone__text {
    font-size: 14px;
    font-weight: 600;
    color: var(--sos-text);
    margin: 0 0 4px;
}

.sos-upload-zone__hint {
    font-size: 12px;
    color: var(--sos-text-muted);
    margin: 0;
}

.sos-file-name {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 10px;
    font-size: 13px;
    color: var(--sos-cta);
    font-weight: 500;
}

/* --- GUEST SECTION --- */
.sos-guest-section {
    background: var(--sos-surface-alt);
    border-color: var(--sos-border);
}

.sos-guest-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--sos-text-secondary);
    background: var(--sos-surface);
    border: 1px solid var(--sos-border);
    padding: 5px 12px;
    border-radius: 20px;
    margin-bottom: 12px;
}

.sos-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sos-field__label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: var(--sos-text-secondary);
}

.sos-input {
    padding: 12px 14px;
    border: 2px solid var(--sos-border);
    border-radius: var(--sos-radius);
    background: var(--sos-surface);
    font-size: 14px;
    font-family: var(--sos-font);
    color: var(--sos-text);
    transition: all var(--sos-transition);
}

.sos-input:focus {
    outline: none;
    border-color: var(--sos-primary);
    box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12);
}

.sos-input::placeholder {
    color: var(--sos-text-muted);
}

.sos-field__error {
    font-size: 12px;
    color: var(--sos-danger);
    font-weight: 500;
    margin: 0;
}

/* --- HINT TEXT --- */
.sos-hint-text {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--sos-text-muted);
    margin: 8px 0 0;
}

/* --- ALERT --- */
.sos-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 14px 16px;
    border-radius: var(--sos-radius);
    font-size: 13px;
    font-weight: 500;
}

.sos-alert--danger {
    background: var(--sos-danger-bg);
    color: var(--sos-danger);
    border: 1px solid rgba(220, 38, 38, 0.2);
}

/* --- LOADING ROW --- */
.sos-loading-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--sos-text-muted);
    padding: 8px 0;
}

/* --- EMPTY HINT --- */
.sos-empty-hint {
    font-size: 13px;
    color: var(--sos-text-muted);
    font-style: italic;
    padding: 8px 0;
    margin: 0;
}

/* --- SUBMIT BUTTON --- */
.sos-submit-btn {
    width: 100%;
    padding: 16px 24px;
    border-radius: var(--sos-radius);
    background: var(--sos-danger);
    color: #fff;
    border: none;
    font-size: 16px;
    font-weight: 700;
    font-family: var(--sos-font);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
    letter-spacing: 0.2px;
}

.sos-submit-btn:hover:not(:disabled) {
    background: #B91C1C;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4);
}

.sos-submit-btn:focus-visible {
    outline: 3px solid var(--sos-danger);
    outline-offset: 3px;
}

.sos-submit-btn:active:not(:disabled) {
    transform: translateY(0);
}

.sos-submit-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}

.sos-submit-btn__content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

/* --- MAP PANEL --- */
.sos-map-panel {
    flex: 1;
    position: relative;
    min-height: 360px;
}

@media (min-width: 992px) {
    .sos-map-panel {
        min-height: 0;
    }
}

.sos-map-container {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 360px;
}

.sos-map {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 0;
}

@media (min-width: 992px) {
    .sos-map {
        border-radius: 0;
    }
}

/* --- UNITS CARD --- */
.sos-units-card {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: var(--sos-radius-lg);
    padding: 16px;
    width: 240px;
    box-shadow: var(--sos-shadow-lg);
    z-index: 10;
    display: none;
}

@media (min-width: 768px) {
    .sos-units-card {
        display: block;
    }
}

.sos-units-card__title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--sos-text-secondary);
    margin: 0 0 14px;
}

.sos-units-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sos-units-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sos-units-item__icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
    font-size: 14px;
}

.bg-primary {
    background: var(--sos-primary) !important;
}

.bg-danger {
    background: var(--sos-danger) !important;
}

.sos-units-item__info {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.sos-units-item__name {
    font-size: 13px;
    font-weight: 600;
    color: var(--sos-text);
}

.sos-units-item__dist {
    font-size: 11px;
    color: var(--sos-text-muted);
}

/* --- TRANSITIONS --- */
.sos-fade-enter-active,
.sos-fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.sos-fade-enter-from,
.sos-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

.sos-slide-enter-active,
.sos-slide-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}

.sos-slide-enter-from,
.sos-slide-leave-to {
    opacity: 0;
    transform: translateY(-8px);
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
    margin-top: 0;
    margin-bottom: 0;
}

.sos-slide-enter-to,
.sos-slide-leave-from {
    max-height: 500px;
}

/* --- RESPONSIVE --- */
@media (max-width: 575.98px) {
    .sos-header {
        padding: 18px 16px;
    }

    .sos-header__title {
        font-size: 18px;
    }

    .sos-form-panel {
        padding: 12px 12px 20px;
    }

    .sos-section {
        padding: 14px 16px;
    }

    .sos-type-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .sos-type-card {
        min-height: 70px;
        padding: 10px 6px 8px;
    }

    .sos-type-card__icon {
        font-size: 20px;
    }

    .sos-type-card__label {
        font-size: 9px;
    }

    .sos-submit-btn {
        padding: 14px 20px;
        font-size: 15px;
    }
}
</style>

<style>
/* Modal giới hạn: Teleport ra body — không dùng scoped */
.sos-rl-overlay {
    position: fixed;
    inset: 0;
    z-index: 21000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.sos-rl-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(3px);
}

.sos-rl-dialog {
    position: relative;
    width: 100%;
    max-width: 400px;
    background: #fff;
    border-radius: 18px;
    padding: 1.5rem 1.35rem 1.25rem;
    text-align: center;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.18);
}

.sos-rl-icon-wrap {
    width: 56px;
    height: 56px;
    margin: 0 auto 0.85rem;
    border-radius: 14px;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #b45309;
    font-size: 1.5rem;
}

.sos-rl-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.6rem;
    line-height: 1.35;
}

.sos-rl-desc {
    font-size: 0.88rem;
    color: #64748b;
    line-height: 1.5;
    margin: 0 0 1rem;
}

.sos-rl-countdown {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    padding: 0.85rem 0.5rem;
    margin-bottom: 0.75rem;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.sos-rl-countdown__label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #94a3b8;
}

.sos-rl-countdown__value {
    font-family: ui-monospace, "Cascadia Code", monospace;
    font-size: 2rem;
    font-weight: 700;
    color: #0e7490;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.04em;
}

.sos-rl-ready {
    font-size: 0.88rem;
    color: #059669;
    font-weight: 600;
    margin: 0 0 0.75rem;
}

.sos-rl-btn {
    width: 100%;
    border: none;
    border-radius: 12px;
    padding: 0.65rem 1rem;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    background: linear-gradient(90deg, #0891b2 0%, #0e7490 100%);
    color: #fff;
}

.sos-rl-btn:hover {
    filter: brightness(1.05);
}

.sos-rl-fade-enter-active,
.sos-rl-fade-leave-active {
    transition: opacity 0.2s ease;
}

.sos-rl-fade-enter-active .sos-rl-dialog,
.sos-rl-fade-leave-active .sos-rl-dialog {
    transition: transform 0.22s ease, opacity 0.22s ease;
}

.sos-rl-fade-enter-from,
.sos-rl-fade-leave-to {
    opacity: 0;
}

.sos-rl-fade-enter-from .sos-rl-dialog,
.sos-rl-fade-leave-to .sos-rl-dialog {
    transform: translateY(10px) scale(0.98);
    opacity: 0;
}

/* Modal: khách gửi yêu cầu thành công (Teleport body — global) */
.sos-gs-overlay {
    position: fixed;
    inset: 0;
    z-index: 20950;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.sos-gs-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(3px);
}

.sos-gs-dialog {
    position: relative;
    width: 100%;
    max-width: 440px;
    background: #fff;
    border-radius: 18px;
    padding: 1.5rem 1.35rem 1.25rem;
    text-align: left;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.16);
}

.sos-gs-icon {
    width: 52px;
    height: 52px;
    margin: 0 auto 0.75rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #059669;
    font-size: 1.45rem;
}

.sos-gs-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.5rem;
    text-align: center;
}

.sos-gs-lead {
    font-size: 0.9rem;
    color: #0e7490;
    text-align: center;
    margin: 0 0 0.75rem;
}

.sos-gs-text {
    font-size: 0.9rem;
    color: #475569;
    line-height: 1.55;
    margin: 0 0 0.75rem;
}

.sos-gs-highlight {
    font-size: 0.88rem;
    color: #334155;
    line-height: 1.55;
    margin: 0 0 1.1rem;
    padding: 0.75rem 0.85rem;
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 12px;
}

.sos-gs-actions {
    display: flex;
    flex-direction: column-reverse;
    gap: 0.5rem;
}

@media (min-width: 400px) {
    .sos-gs-actions {
        flex-direction: row;
        justify-content: flex-end;
    }
}

.sos-gs-btn {
    flex: 1;
    min-height: 44px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.92rem;
    cursor: pointer;
    border: none;
    padding: 0.5rem 1rem;
}

.sos-gs-btn--ghost {
    background: #f1f5f9;
    color: #475569;
}

.sos-gs-btn--ghost:hover {
    background: #e2e8f0;
}

.sos-gs-btn--primary {
    background: linear-gradient(90deg, #059669 0%, #047857 100%);
    color: #fff;
}

.sos-gs-btn--primary:hover {
    filter: brightness(1.05);
}

.sos-gs-fade-enter-active,
.sos-gs-fade-leave-active {
    transition: opacity 0.2s ease;
}

.sos-gs-fade-enter-active .sos-gs-dialog,
.sos-gs-fade-leave-active .sos-gs-dialog {
    transition: transform 0.22s ease, opacity 0.22s ease;
}

.sos-gs-fade-enter-from,
.sos-gs-fade-leave-to {
    opacity: 0;
}

.sos-gs-fade-enter-from .sos-gs-dialog,
.sos-gs-fade-leave-to .sos-gs-dialog {
    transform: translateY(10px) scale(0.98);
    opacity: 0;
}
</style>
