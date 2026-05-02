<template>
    <div class="report-wrapper">
        <!-- Header -->
        <div
            class="report-header px-4 py-3 d-flex align-items-center justify-content-between border-bottom bg-white shadow-sm">
            <div class="d-flex align-items-center">
                <div class="header-icon me-3 d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-white"
                    style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-chart-pie fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Báo Cáo & Thống Kê</h5>
                    <span class="text-muted small">Tổng quan hệ thống SOS</span>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <!-- Date range filter -->
                <div class="date-filter-group" role="group" aria-label="Bộ lọc thời gian">
                    <button v-for="opt in dateRangeOptions" :key="opt.value" type="button" class="date-filter-btn"
                        :class="{ active: dateRange === opt.value }" @click="setDateRange(opt.value)">
                        {{ opt.label }}
                    </button>
                </div>
                <button class="btn btn-light border rounded-pill px-3 fw-semibold shadow-sm" @click="refreshData">
                    <i class="fa-solid fa-rotate me-1" :class="{ 'fa-spin': loading }"></i> Làm mới
                </button>
                <button class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" @click="exportReport">
                    <i class="fa-solid fa-download me-2"></i> Xuất báo cáo
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="report-body px-4 py-3 flex-grow-1 overflow-auto">

            <!-- Loading -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-danger" role="status"></div>
                <p class="mt-2 text-muted">Đang tải dữ liệu...</p>
            </div>

            <template v-else>
                <!-- Stats Overview -->
                <div class="stats-grid mb-4">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div
                                class="stat-card bg-white rounded-4 p-4 text-center border border-light shadow-sm hover-lift">
                                <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-white"
                                    style="width: 56px; height: 56px;">
                                    <i class="fa-solid fa-clipboard-list fs-4"></i>
                                </div>
                                <div class="fw-bold text-dark fs-2 mb-1">{{ stats.total_requests || 0 }}</div>
                                <div class="text-muted small fw-medium">Tổng yêu cầu</div>
                                <div class="text-primary small mt-1">Hôm nay: {{ stats.total_requests_today || 0 }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div
                                class="stat-card bg-white rounded-4 p-4 text-center border border-light shadow-sm hover-lift">
                                <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning"
                                    style="width: 56px; height: 56px;">
                                    <i class="fa-solid fa-clock fs-4"></i>
                                </div>
                                <div class="fw-bold text-dark fs-2 mb-1">{{ stats.cho_xu_ly || 0 }}</div>
                                <div class="text-muted small fw-medium">Chờ xử lý</div>
                                <div class="text-warning small mt-1">TB chờ: {{ stats.avg_wait_minutes || 0 }} ph</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div
                                class="stat-card bg-white rounded-4 p-4 text-center border border-light shadow-sm hover-lift">
                                <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-white"
                                    style="width: 56px; height: 56px;">
                                    <i class="fa-solid fa-check-circle fs-4"></i>
                                </div>
                                <div class="fw-bold text-dark fs-2 mb-1">{{ stats.hoan_thanh || 0 }}</div>
                                <div class="text-muted small fw-medium">Hoàn thành hôm nay</div>
                                <div class="text-success small mt-1">Tỷ lệ: {{ completionRate }}%</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div
                                class="stat-card bg-white rounded-4 p-4 text-center border border-light shadow-sm hover-lift">
                                <div class="stat-icon mb-3 mx-auto d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-white"
                                    style="width: 56px; height: 56px;">
                                    <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                                </div>
                                <div class="fw-bold text-dark fs-2 mb-1">{{ stats.critical_count || 0 }}</div>
                                <div class="text-muted small fw-medium">Ưu tiên cao chờ</div>
                                <div class="text-danger small mt-1">Cần điều phối</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="row g-3 mb-4">
                    <!-- Bar Chart: Yêu cầu theo tháng -->
                    <div class="col-md-8">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <h6 class="fw-bold mb-0">Yêu cầu cứu hộ theo tháng</h6>
                                <small class="text-muted">Số liệu trong năm</small>
                            </div>
                            <div class="admin-card-body">
                                <div id="chart-monthly" style="height: 240px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Doughnut: Trạng thái xử lý -->
                    <div class="col-md-4">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <h6 class="fw-bold mb-0">Trạng thái xử lý</h6>
                            </div>
                            <div class="admin-card-body">
                                <div id="chart-by-status" style="height: 240px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="row g-3 mb-4">
                    <!-- Bar Chart: Yêu cầu theo loại sự cố -->
                    <div class="col-md-6">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <h6 class="fw-bold mb-0">Theo loại sự cố</h6>
                                <span class="badge-count">{{ byTypeData.length }} loại</span>
                            </div>
                            <div class="admin-card-body">
                                <div id="chart-by-type" style="height: 260px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bar Chart: Theo mức độ khẩn cấp -->
                    <div class="col-md-6">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <h6 class="fw-bold mb-0">Theo mức độ khẩn cấp</h6>
                            </div>
                            <div class="admin-card-body">
                                <div id="chart-by-urgency" style="height: 260px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 3: Hiệu suất đội cứu hộ -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h6 class="fw-bold mb-0">Hiệu suất đội cứu hộ</h6>
                            </div>
                            <div class="admin-card-body">
                                <div id="chart-team-performance" style="height: 280px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Requests Table -->
                <div class="admin-card">
                    <div class="admin-card-header border-bottom-0">
                        <h6 class="fw-bold mb-0">Yêu cầu gần đây</h6>
                    </div>
                    <div class="admin-card-body p-0">
                        <div class="table-responsive rounded-3 overflow-hidden">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4 py-3 fw-semibold">ID</th>
                                        <th class="py-3 fw-semibold">Người yêu cầu</th>
                                        <th class="py-3 fw-semibold">Loại sự cố</th>
                                        <th class="py-3 fw-semibold">Mức độ</th>
                                        <th class="py-3 fw-semibold">Trạng thái</th>
                                        <th class="py-3 fw-semibold">Ngày tạo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in recentRequests" :key="item.id_yeu_cau">
                                        <td class="ps-4">
                                            <span class="fw-bold">#{{ item.id_yeu_cau }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ item.nguoi_dung?.ho_ten || item.ho_ten || 'N/A' }}
                                            </div>
                                            <div class="small" style="color: #9ca3af">{{ item.so_dien_thoai || item.sdt
                                                || '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ item.loai_su_co?.ten_danh_muc ||
                                                item.loai_su_co?.ten_loai_su_co || '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge-count-pill"
                                                :class="getUrgencyClass(item.muc_do_khan_cap)">
                                                {{ getUrgencyText(item.muc_do_khan_cap) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-status-pill" :class="getStatusClass(item.trang_thai)">
                                                {{ getStatusText(item.trang_thai) }}
                                            </span>
                                        </td>
                                        <td style="color: #6b7280">
                                            {{ formatDate(item.created_at) }}
                                        </td>
                                        
                                    </tr>
                                    <tr v-if="recentRequests.length === 0">
                                        <td colspan="7" class="text-center py-4" style="color: #9ca3af">
                                            Chưa có dữ liệu yêu cầu
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { analyticsAPI, rescueRequestAPI } from "../../../services/api.js";

export default {
    name: "AdminReports",
    data() {
        return {
            loading: false,
            dateRange: '30',
            dateRangeOptions: [
                { label: "7 ngày", value: "7" },
                { label: "30 ngày", value: "30" },
                { label: "90 ngày", value: "90" },
                { label: "Tất cả", value: "all" },
            ],
            stats: {
                total_requests: 0,
                total_requests_today: 0,
                cho_xu_ly: 0,
                dang_xu_ly: 0,
                hoan_thanh: 0,
                huy_bo: 0,
                critical_count: 0,
                avg_wait_minutes: 0,
            },
            processingStatus: {},
            byTypeData: [],
            byUrgencyData: [],
            teamPerformanceData: [],
            recentRequests: [],
            chartInstances: {},
            monthlyBars: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        };
    },
    computed: {
        completionRate() {
            const total = this.stats.total_requests;
            if (total === 0) return 0;
            return Math.round((this.stats.hoan_thanh / total) * 100);
        },
    },

    watch: {
        recentRequests: {
            handler(val) {
                if (val && val.length > 0) {
                    const currentYear = new Date().getFullYear();
                    const counts = new Array(12).fill(0);
                    val.forEach(r => {
                        if (r.created_at) {
                            const d = new Date(r.created_at);
                            if (d.getFullYear() === currentYear) {
                                counts[d.getMonth()]++;
                            }
                        }
                    });
                    const max = Math.max(...counts, 1);
                    this.monthlyBars = counts.map(c => Math.round((c / max) * 100));
                }
            },
            immediate: false,
        },
    },
    async mounted() {
        await this.fetchAllData();
        this.$nextTick(() => {
            this.renderAllCharts();
        });
    },
    beforeUnmount() {
        Object.values(this.chartInstances).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
    },
    methods: {
        getDateRangeParams() {
            if (this.dateRange === 'all') return {};
            const days = parseInt(this.dateRange);
            const to = new Date();
            const from = new Date();
            from.setDate(from.getDate() - days);
            return {
                from_date: from.toISOString().split('T')[0],
                to_date: to.toISOString().split('T')[0],
            };
        },

        async setDateRange(value) {
            this.dateRange = value;
            await this.refreshData();
        },

        async fetchAllData() {
            this.loading = true;
            try {
                await Promise.all([
                    this.fetchDashboardStats(),
                    this.fetchProcessingStatus(),
                    this.fetchByType(),
                    this.fetchByUrgency(),
                    this.fetchTeamPerformance(),
                    this.fetchRecentRequests(),
                ]);
            } catch (e) {
                console.error("Lỗi tải dữ liệu báo cáo:", e);
            } finally {
                this.loading = false;
            }
        },

        async refreshData() {
            Object.values(this.chartInstances).forEach(chart => {
                if (chart && typeof chart.destroy === 'function') {
                    chart.destroy();
                }
            });
            this.chartInstances = {};
            await this.fetchAllData();
            this.$nextTick(() => {
                this.renderAllCharts();
            });
        },

        async fetchDashboardStats() {
            try {
                const res = await analyticsAPI.getDashboard({ params: this.getDateRangeParams() });
                if (res.data?.success && res.data?.data) {
                    const d = res.data.data;
                    this.stats = {
                        total_requests: d.total_requests || 0,
                        total_requests_today: d.total_requests_today || 0,
                        cho_xu_ly: d.cho_xu_ly || 0,
                        dang_xu_ly: d.dang_xu_ly || 0,
                        hoan_thanh: d.hoan_thanh || 0,
                        huy_bo: d.huy_bo || 0,
                        critical_count: d.critical_count || 0,
                        avg_wait_minutes: d.avg_wait_minutes || 0,
                    };
                }
            } catch (e) {
                console.error("Lỗi dashboard stats:", e);
            }
        },

        async fetchProcessingStatus() {
            try {
                const params = this.getDateRangeParams();
                const res = await analyticsAPI.getProcessingStatus(params);
                if (res.data?.success && res.data?.data) {
                    this.processingStatus = res.data.data;
                }
            } catch (e) {
                console.error("Lỗi processing status:", e);
            }
        },

        async fetchByType() {
            try {
                const params = this.getDateRangeParams();
                const res = await analyticsAPI.getRequestsByType(params);
                if (res.data?.success) {
                    this.byTypeData = res.data.data || [];
                }
            } catch (e) {
                console.error("Lỗi by type:", e);
            }
        },

        async fetchByUrgency() {
            try {
                const params = this.getDateRangeParams();
                const res = await analyticsAPI.getRequestsByPriority(params);
                if (res.data?.success) {
                    this.byUrgencyData = res.data.data || [];
                }
            } catch (e) {
                console.error("Lỗi by urgency:", e);
            }
        },

        async fetchTeamPerformance() {
            try {
                const params = this.getDateRangeParams();
                const res = await analyticsAPI.getTeamPerformance(params);
                if (res.data?.success) {
                    this.teamPerformanceData = res.data.data || [];
                }
            } catch (e) {
                console.error("Lỗi team performance:", e);
            }
        },

        async fetchRecentRequests() {
            try {
                const params = { ...this.getDateRangeParams(), per_page: 100 };
                const res = await rescueRequestAPI.getList(params);
                if (res.data?.data?.data) {
                    this.recentRequests = res.data.data.data.slice(0, 20);
                } else if (res.data?.data) {
                    this.recentRequests = Array.isArray(res.data.data)
                        ? res.data.data.slice(0, 20)
                        : (res.data.data.slice ? res.data.data.slice(0, 20) : []);
                } else if (Array.isArray(res.data)) {
                    this.recentRequests = res.data.slice(0, 20);
                }
            } catch (e) {
                console.error("Lỗi recent requests:", e);
            }
        },

        renderAllCharts() {
            if (!window.ApexCharts) return;
            this.renderChartByStatus();
            this.renderChartByType();
            this.renderChartByUrgency();
            this.renderChartTeamPerformance();
            this.renderChartMonthly();
        },

        renderChartMonthly() {
            const container = document.getElementById("chart-monthly");
            if (!container || !window.ApexCharts) return;

            if (this.chartInstances.monthly) {
                this.chartInstances.monthly.destroy();
            }

            const labels = ["T1", "T2", "T3", "T4", "T5", "T6", "T7", "T8", "T9", "T10", "T11", "T12"];

            this.chartInstances.monthly = new window.ApexCharts(container, {
                series: [{
                    name: "Yêu cầu",
                    data: this.monthlyBars,
                }],
                chart: {
                    type: "bar",
                    height: 240,
                    toolbar: { show: false },
                    fontFamily: "inherit",
                },
                colors: ["#f97316"],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: "50%",
                        borderRadius: 6,
                    },
                },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ["transparent"] },
                xaxis: {
                    categories: labels,
                    labels: { style: { colors: "#6b7280", fontSize: "12px" } },
                },
                yaxis: {
                    labels: { style: { colors: "#6b7280", fontSize: "12px" } },
                },
                fill: { opacity: 1 },
                tooltip: {
                    theme: "light",
                    y: { formatter: (val) => `${val} yêu cầu` },
                },
                legend: { show: false },
                grid: { borderColor: "#f3f4f6" },
            });
            this.chartInstances.monthly.render();
        },

        renderChartByStatus() {
            const container = document.getElementById("chart-by-status");
            if (!container || !window.ApexCharts) return;

            if (this.chartInstances.byStatus) {
                this.chartInstances.byStatus.destroy();
            }

            const ps = this.processingStatus;
            const labels = [];
            const series = [];
            const colors = [];

            if (ps && (ps.total > 0 || ps.new !== undefined)) {
                if (ps.new > 0) { labels.push("Chờ xử lý"); series.push(ps.new); colors.push("#f59e0b"); }
                if (ps.processing > 0) { labels.push("Đang xử lý"); series.push(ps.processing); colors.push("#3b82f6"); }
                if (ps.completed > 0) { labels.push("Hoàn thành"); series.push(ps.completed); colors.push("#10b981"); }
                if (ps.cancelled > 0) { labels.push("Hủy bỏ"); series.push(ps.cancelled); colors.push("#6b7280"); }
            }

            if (series.length === 0) {
                labels.push("Chờ xử lý", "Đang xử lý", "Hoàn thành", "Hủy bỏ");
                series.push(
                    this.stats.cho_xu_ly || 0,
                    this.stats.dang_xu_ly || 0,
                    this.stats.hoan_thanh || 0,
                    this.stats.huy_bo || 0
                );
                colors.push("#f59e0b", "#3b82f6", "#10b981", "#6b7280");
            }

            this.chartInstances.byStatus = new window.ApexCharts(container, {
                series: series,
                chart: {
                    type: "donut",
                    height: 240,
                    fontFamily: "inherit",
                },
                colors: colors,
                labels: labels,
                plotOptions: {
                    pie: {
                        donut: {
                            size: "65%",
                            labels: {
                                show: true,
                                name: { show: true, fontSize: "13px", fontWeight: 600 },
                                value: {
                                    show: true,
                                    fontSize: "16px",
                                    fontWeight: 700,
                                    formatter: (val) => val,
                                },
                                total: {
                                    show: true,
                                    label: "Tổng",
                                    fontSize: "13px",
                                    fontWeight: 600,
                                    color: "#374151",
                                    formatter: () => series.reduce((a, b) => a + b, 0),
                                },
                            },
                        },
                    },
                },
                dataLabels: { enabled: false },
                legend: {
                    position: "bottom",
                    fontSize: "12px",
                    fontWeight: 500,
                    labels: { colors: "#6b7280" },
                },
                stroke: { width: 0 },
                tooltip: { theme: "light" },
            });
            this.chartInstances.byStatus.render();
        },

        renderChartByType() {
            const container = document.getElementById("chart-by-type");
            if (!container || !window.ApexCharts || this.byTypeData.length === 0) return;

            if (this.chartInstances.byType) {
                this.chartInstances.byType.destroy();
            }

            const labels = this.byTypeData.map(d => d.type_name || d.ten_danh_muc || d.ten_loai_su_co || 'Khác');
            const series = this.byTypeData.map(d => d.count);

            this.chartInstances.byType = new window.ApexCharts(container, {
                series: [{ name: "Yêu cầu", data: series }],
                chart: {
                    type: "bar",
                    height: 260,
                    toolbar: { show: false },
                    fontFamily: "inherit",
                },
                colors: ["#3b82f6", "#f97316", "#10b981", "#8b5cf6", "#ef4444", "#06b6d4", "#f59e0b", "#ec4899"],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 6,
                        barHeight: "60%",
                        distributed: true,
                    },
                },
                dataLabels: { enabled: true, style: { fontSize: "12px", fontWeight: 700, colors: ["#374151"] } },
                xaxis: {
                    categories: labels,
                    labels: { style: { colors: "#6b7280", fontSize: "12px" } },
                },
                yaxis: {
                    labels: { style: { colors: "#6b7280", fontSize: "12px" } },
                },
                fill: { opacity: 1 },
                tooltip: {
                    theme: "light",
                    y: { formatter: (val) => `${val} yêu cầu` },
                },
                legend: { show: false },
                grid: { borderColor: "#f3f4f6" },
            });
            this.chartInstances.byType.render();
        },

        renderChartByUrgency() {
            const container = document.getElementById("chart-by-urgency");
            if (!container || !window.ApexCharts) return;

            if (this.chartInstances.byUrgency) {
                this.chartInstances.byUrgency.destroy();
            }

            const labels = [];
            const series = [];
            const colors = [];

            this.byUrgencyData.forEach(item => {
                const level = item.muc_do_khan_cap || item.urgency || '';
                labels.push(this.getUrgencyText(level));
                series.push(item.count);
                colors.push(this.getUrgencyColor(level));
            });

            if (series.length === 0) {
                labels.push("Không có dữ liệu");
                series.push(0);
                colors.push("#e5e7eb");
            }

            this.chartInstances.byUrgency = new window.ApexCharts(container, {
                series: [{ name: "Số yêu cầu", data: series }],
                chart: {
                    type: "bar",
                    height: 260,
                    toolbar: { show: false },
                    fontFamily: "inherit",
                },
                colors: colors,
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: "50%",
                        borderRadius: 6,
                        distributed: true,
                    },
                },
                dataLabels: { enabled: true, style: { fontSize: "12px", fontWeight: 700, colors: ["#374151"] } },
                xaxis: {
                    categories: labels,
                    labels: { style: { colors: "#6b7280", fontSize: "12px" }, rotate: -20 },
                },
                yaxis: {
                    labels: { style: { colors: "#6b7280", fontSize: "12px" } },
                },
                fill: { opacity: 1 },
                tooltip: {
                    theme: "light",
                    y: { formatter: (val) => `${val} yêu cầu` },
                },
                legend: { show: false },
                grid: { borderColor: "#f3f4f6" },
            });
            this.chartInstances.byUrgency.render();
        },

        renderChartTeamPerformance() {
            const container = document.getElementById("chart-team-performance");
            if (!container || !window.ApexCharts) return;

            if (this.chartInstances.teamPerf) {
                this.chartInstances.teamPerf.destroy();
            }

            const labels = [];
            const missionSeries = [];
            const rateSeries = [];

            this.teamPerformanceData.slice(0, 8).forEach(team => {
                labels.push(team.ten_doi || team.name || 'Đội');
                missionSeries.push(team.so_nhiem_vu_dang_xy_ly || team.active_missions || 0);
                rateSeries.push(team.ty_le_hoan_thanh || team.completion_rate || 0);
            });

            if (labels.length === 0) {
                labels.push("Không có dữ liệu");
                missionSeries.push(0);
                rateSeries.push(0);
            }

            this.chartInstances.teamPerf = new window.ApexCharts(container, {
                series: [
                    { name: "Nhiệm vụ đang xử lý", data: missionSeries },
                    { name: "Tỷ lệ hoàn thành (%)", data: rateSeries },
                ],
                chart: {
                    type: "bar",
                    height: 280,
                    toolbar: { show: false },
                    fontFamily: "inherit",
                },
                colors: ["#f97316", "#10b981"],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: "45%",
                        borderRadius: 6,
                    },
                },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ["transparent"] },
                xaxis: {
                    categories: labels,
                    labels: {
                        style: { colors: "#6b7280", fontSize: "11px" },
                        rotate: -20,
                    },
                },
                yaxis: [
                    { labels: { style: { colors: "#6b7280", fontSize: "12px" } } },
                    {
                        max: 100,
                        labels: {
                            style: { colors: "#6b7280", fontSize: "12px" },
                            formatter: (val) => val + "%",
                        },
                    },
                ],
                fill: { opacity: [1, 1] },
                tooltip: {
                    theme: "light",
                    y: {
                        formatter: (val, { seriesIndex }) => {
                            return seriesIndex === 0 ? `${val} nhiệm vụ` : `${val}%`;
                        },
                    },
                },
                legend: {
                    position: "bottom",
                    fontSize: "12px",
                    fontWeight: 500,
                    labels: { colors: "#6b7280" },
                },
                grid: { borderColor: "#f3f4f6" },
            });
            this.chartInstances.teamPerf.render();
        },

        exportReport() {
            const csvContent = this.generateCSV();
            const blob = new Blob(["\ufeff" + csvContent], { type: "text/csv;charset=utf-8;" });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = `bao-cao-sos-${new Date().toISOString().split("T")[0]}.csv`;
            link.click();
        },

        generateCSV() {
            const headers = ["ID", "Người yêu cầu", "Loại sự cố", "Mức độ", "Trạng thái", "Ngày tạo"];
            const rows = this.recentRequests.map((r) => [
                r.id_yeu_cau,
                r.nguoi_dung?.ho_ten || r.ho_ten || "",
                r.loai_su_co?.ten_danh_muc || r.loai_su_co?.ten_loai_su_co || "",
                this.getUrgencyText(r.muc_do_khan_cap),
                this.getStatusText(r.trang_thai),
                r.created_at,
            ]);
            return [headers, ...rows].map((row) => row.join(",")).join("\n");
        },

        viewDetail(item) {
            console.log("View detail:", item);
        },

        getUrgencyClass(level) {
            const l = (level || "").toUpperCase();
            if (l === "KHA_CAP" || l === "CRITICAL" || l === "CAO") return "bg-danger bg-opacity-10 text-white border-danger border-opacity-25";
            if (l === "TRUNG_BINH" || l === "MEDIUM") return "bg-warning bg-opacity-10 text-white border-warning border-opacity-25";
            return "bg-info bg-opacity-10 text-info border-info border-opacity-25";
        },

        getUrgencyText(level) {
            const l = (level || "").toUpperCase();
            if (l === "KHA_CAP" || l === "CRITICAL" || l === "CAO") return "KHẨN CẤP";
            if (l === "TRUNG_BINH" || l === "MEDIUM") return "TRUNG BÌNH";
            if (l === "THAP" || l === "THUONG" || l === "LOW" || l === "THẤP") return "THẤP";
            return level || "N/A";
        },

        getUrgencyColor(level) {
            const l = (level || "").toUpperCase();
            if (l === "KHA_CAP" || l === "CRITICAL" || l === "CAO") return "#ef4444";
            if (l === "TRUNG_BINH" || l === "MEDIUM") return "#f59e0b";
            return "#3b82f6";
        },

        getStatusClass(status) {
            if (status === "HOAN_THANH") return "bg-success";
            if (status === "DANG_XU_LY") return "bg-primary";
            if (status === "CHO_XU_LY") return "bg-warning";
            if (status === "HUY_BO" || status === "DA_HUY") return "bg-secondary";
            return "bg-dark";
        },

        getStatusText(status) {
            if (status === "HOAN_THANH") return "Hoàn thành";
            if (status === "DANG_XU_LY") return "Đang xử lý";
            if (status === "CHO_XU_LY") return "Chờ xử lý";
            if (status === "HUY_BO" || status === "DA_HUY") return "Hủy bỏ";
            return status || "N/A";
        },

        formatDate(dateString) {
            if (!dateString) return "-";
            const date = new Date(dateString);
            return date.toLocaleDateString("vi-VN", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },
    },
};
</script>

<style scoped>
.report-wrapper {
    margin: -1.5rem -1.5rem -2rem;
    height: calc(100vh - 72px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: #f4f5f7;
}

.report-header {
    flex-shrink: 0;
    z-index: 10;
}

.report-body {
    flex: 1;
    overflow-y: auto;
}

.stats-grid {
    margin-bottom: 1.5rem;
}

/* Date filter */
.date-filter-group {
    display: flex;
    gap: 0;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.date-filter-btn {
    padding: 6px 14px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    background: transparent;
    border: none;
    border-right: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.date-filter-btn:last-child {
    border-right: none;
}

.date-filter-btn:hover {
    background: #f1f5f9;
    color: #334155;
}

.date-filter-btn.active {
    background: #ef4444;
    color: #fff;
}

/* Admin card — consistent rounded corners, clean border */
.admin-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
    overflow: hidden;
}

.admin-card-header {
    padding: 16px 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.admin-card-header h6 {
    font-size: 0.9rem;
    color: #1e293b;
}

.admin-card-header small {
    font-size: 0.75rem;
}

.admin-card-body {
    padding: 12px 16px 16px;
}

/* Badge pill — mức độ */
.badge-count-pill {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    line-height: 1.4;
}

.badge-count-pill.badge-critical {
    background: #fee2e2;
    color: #dc2626;
}

.badge-count-pill.badge-medium {
    background: #fef3c7;
    color: #d97706;
}

.badge-count-pill.badge-low {
    background: #dbeafe;
    color: #2563eb;
}

/* Badge pill — trạng thái */
.badge-status-pill {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.4;
}

.badge-status-pill.bg-success {
    background: #10b981;
}

.badge-status-pill.bg-primary {
    background: #3b82f6;
}

.badge-status-pill.bg-warning {
    background: #f59e0b;
}

.badge-status-pill.bg-secondary {
    background: #94a3b8;
}

.badge-status-pill.bg-dark {
    background: #475569;
}

/* Badge count (header) */
.badge-count {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 600;
    background: #f1f5f9;
    color: #64748b;
}

/* Stat card */
.stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px 16px;
    text-align: center;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

/* Table */
.table {
    border-collapse: separate;
    border-spacing: 0;
}

.table thead.table-light th {
    background: #f8fafc;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    border-bottom: 1px solid #e2e8f0;
}

.table tbody tr:hover td {
    background: #fafbfc;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
}

/* Action button */
.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-action:hover {
    background: #fef2f2;
    color: #ef4444;
    border-color: #fecaca;
}

@media (max-width: 768px) {
    .stats-grid .col-6 {
        margin-bottom: 1rem;
    }
}
</style>
