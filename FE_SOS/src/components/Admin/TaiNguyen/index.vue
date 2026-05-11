<template>
  <div class="dashboard-container py-4 px-3 px-md-4 h-100">
    <!-- Header -->
    <div class="row align-items-end mb-4 g-3">
      <div class="col-lg-6">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="brand-icon-box bg-white text-primary border border-primary-subtle shadow-sm">
            <i class="fa-solid fa-people-roof fs-4"></i>
          </div>
          <div>
            <h2 class="mb-0 fw-bolder page-title text-dark">Tài Nguyên</h2>
            <p class="text-muted mb-0 page-subtitle fw-medium">Quản lý  kho tài nguyên và cấp phát</p>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="d-flex justify-content-lg-end gap-3 flex-wrap">
          
          <div class="stat-chip bg-white border border-light-subtle shadow-sm">
            <i class="fa-solid fa-box-open text-warning"></i>
            <span class="stat-chip-label">Tài nguyên</span>
            <span class="stat-chip-value">{{ totalResources }}</span>
          </div>
          <div class="stat-chip bg-white border border-light-subtle shadow-sm">
            <i class="fa-solid fa-circle-check text-success"></i>
            <span class="stat-chip-label">Sẵn sàng</span>
            <span class="stat-chip-value">{{ availableTeams }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-nav-wrapper mb-4">
      <div class="d-flex gap-2 flex-wrap">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          class="tab-btn"
          :class="{ active: activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          <i :class="tab.icon"></i>
          {{ tab.label }}
        </button>
      </div>
    </div>

   

    <!-- ========== TAB 2: TAI NGUYEN ========== -->
    <div v-if="activeTab === 'resources'">
      <div class="card panel-card border-0 shadow-sm bg-transparent">
        <div class="card-header bg-white border-0 shadow-sm rounded-4 mb-4 pt-4 pb-3 px-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h5 class="fw-bolder text-dark mb-0">
              <i class="fa-solid fa-boxes-packing text-warning me-2"></i>Tài nguyên theo đội
            </h5>
            <div class="d-flex gap-2 align-items-center flex-wrap">
              <select v-model="filterResourceTeam" class="form-select custom-input" style="min-width: 180px;">
                <option value="">Tất cả đội</option>
                <option v-for="t in teams" :key="t.id_doi_cuu_ho" :value="t.id_doi_cuu_ho">{{ t.ten_doi }}</option>
              </select>
              <div class="search-box">
                <span class="search-icon"><i class="fa-solid fa-search"></i></span>
                <input v-model="searchResources" type="text" class="form-control" placeholder="Tìm tài nguyên...">
              </div>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div v-if="loadingResources" class="text-center py-5 bg-white rounded-4 shadow-sm">
            <div class="spinner"></div>
            <p class="text-muted mt-2 fw-medium">Đang tải tài nguyên...</p>
          </div>
          <div v-else-if="groupedResources.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm">
            <div class="empty-icon-wrap mx-auto mb-3"><i class="fa-solid fa-box-open fs-1"></i></div>
            <h6 class="fw-bold text-dark">Chưa có tài nguyên nào</h6>
            <p class="text-muted small">Tài nguyên sẽ được hiển thị khi có dữ liệu từ các đội</p>
          </div>
          <div v-else class="row g-4">
            <div v-for="group in groupedResources" :key="group.teamId" class="col-md-6 col-xl-4">
              <div class="card h-100 border-0 shadow-sm rounded-4 resource-team-card overflow-hidden transition-all">
                <div class="card-header bg-white border-bottom pt-3 pb-3 px-4 d-flex align-items-center gap-3">
                  <div class="team-icon-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                    <i class="fa-solid fa-users fs-5"></i>
                  </div>
                  <div class="overflow-hidden">
                    <h6 class="fw-bolder text-dark mb-0 text-truncate" :title="group.teamName">{{ group.teamName }}</h6>
                    <small class="text-muted fw-medium">{{ group.resources.length }} tài nguyên</small>
                  </div>
                </div>
                <div class="card-body p-0">
                  <div class="list-group list-group-flush">
                    <div v-for="res in group.resources" :key="res.id_tai_nguyen" class="list-group-item bg-transparent border-bottom-0 px-4 py-3 d-flex justify-content-between align-items-center resource-list-item position-relative">
                      <div class="d-flex align-items-center gap-3 overflow-hidden pe-2">
                        <div class="resource-type-icon d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" :class="getWarehouseIconClass(res.slug_tai_nguyen)" style="width: 45px; height: 45px;">
                          <i :class="getWarehouseIcon(res.slug_tai_nguyen)" class="fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                          <div class="fw-bold text-dark mb-1 text-truncate" :title="res.ten_tai_nguyen">{{ res.ten_tai_nguyen }}</div>
                          <div class="d-flex gap-2 align-items-center flex-wrap">
                            <span class="badge bg-dark-subtle text-dark-emphasis fw-bold rounded-pill px-2">SL: {{ res.so_luong }}</span>
                            <span class="badge rounded-pill fw-medium" :class="res.trang_thai ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'">
                              <i class="fa-solid" :class="res.trang_thai ? 'fa-check' : 'fa-pause'"></i> {{ res.trang_thai ? 'Hoạt động' : 'Tạm ngưng' }}
                            </span>
                          </div>
                        </div>
                      </div>
                      <div class="dropdown ms-1 flex-shrink-0">
                        <button class="btn btn-sm btn-light rounded-circle shadow-none border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px;">
                          <i class="fa-solid fa-ellipsis-vertical text-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                          <li><a class="dropdown-item py-2 fw-medium d-flex align-items-center" href="#" @click.prevent="openEditResourceModal(res)"><i class="fa-solid fa-pen-to-square text-primary me-2" style="width: 16px; text-align: center;"></i>Chỉnh sửa</a></li>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item py-2 fw-medium text-danger d-flex align-items-center" href="#" @click.prevent="confirmDeleteResource(res)"><i class="fa-solid fa-trash me-2" style="width: 16px; text-align: center;"></i>Xóa tài nguyên</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ========== TAB 2: KHO ========== -->
    <div v-if="activeTab === 'warehouse'">
      <!-- Toolbar: Search + Add -->
      <div class="card panel-card border-0 shadow-sm mb-4">
        <div class="card-body py-3 px-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-2">
              <div class="warehouse-summary-pill" :class="totalWarehouseQty > 0 ? 'active' : 'empty'">
                <i class="fa-solid fa-warehouse me-1"></i>
                <span class="fw-bolder">{{ totalWarehouseQty }}</span>
                <span class="text-muted small">tổng tài nguyên trong kho</span>
              </div>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap w-100 w-md-auto">
              <div class="search-box flex-grow-1" style="min-width: 200px; max-width: 320px;">
                <span class="search-icon"><i class="fa-solid fa-search"></i></span>
                <input v-model="searchWarehouse" type="text" class="form-control" placeholder="Tìm tài nguyên trong kho...">
              </div>
              <button class="btn btn-primary fw-bolder d-flex align-items-center gap-2 px-3" @click="openAddResourceModal">
                <i class="fa-solid fa-plus"></i>
                <span class="d-none d-sm-inline">Thêm tài nguyên mới</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Kho cards row -->
      <div class="row g-4 mb-4">
        <div v-for="item in warehouseItems" :key="item.slug_tai_nguyen"
          class="col-xl-3 col-lg-6">
          <div class="warehouse-card card h-100 border-0 shadow-sm">
            <div class="card-body p-4">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="warehouse-icon rounded-3 d-flex align-items-center justify-content-center"
                  :class="getWarehouseIconClass(item.slug_tai_nguyen)">
                  <i :class="getWarehouseIcon(item.slug_tai_nguyen)"></i>
                </div>
                <button class="btn btn-sm btn-outline-primary rounded-pill fw-medium" @click="openUpdateWarehouseModal(item)">
                  <i class="fa-solid fa-pen me-1"></i>Nhập thêm
                </button>
              </div>
              <h6 class="fw-bolder text-dark mb-1">{{ item.ten_hien_thi }}</h6>
              <p class="text-muted small mb-3">{{ getWarehouseDesc(item.slug_tai_nguyen) }}</p>
              <div class="d-flex align-items-end gap-2">
                <span class="warehouse-qty display-6 fw-bolder text-dark lh-1">{{ item.tong_so_luong }}</span>
                <span class="text-muted small mb-1">{{ getWarehouseUnit(item.slug_tai_nguyen) }}</span>
              </div>
              <div class="progress mt-3" style="height: 6px;">
                <div class="progress-bar" :class="getWarehouseProgressClass(item.slug_tai_nguyen)"
                  role="progressbar"
                  :style="{ width: Math.min((item.tong_so_luong / getWarehouseMax(item.slug_tai_nguyen)) * 100, 100) + '%' }">
                </div>
              </div>
              <p class="text-muted small mt-1 mb-0">{{ item.tong_so_luong }} / {{ getWarehouseMax(item.slug_tai_nguyen) }} {{ getWarehouseUnit(item.slug_tai_nguyen) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Lich su nhap kho -->
      <div class="card panel-card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h5 class="fw-bolder text-dark mb-0">
              <i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Lịch sử nhập / xuất kho
            </h5>
            <div class="d-flex gap-2 align-items-center flex-wrap">
              <select v-model="filterWarehouseType" class="form-select custom-input" style="min-width: 140px;">
                <option value="">Tất cả loại</option>
                <option value="Vehicle">Xe cứu hộ</option>
                <option value="Supply">Nhu yếu phẩm</option>
                <option value="Medical">Vật tư y tế</option>
                <option value="Equipment">Dụng cụ thiết bị</option>
              </select>
              <select v-model="filterWarehouseAction" class="form-select custom-input" style="min-width: 140px;">
                <option value="">Tất cả</option>
                <option value="nhap">Nhập</option>
                <option value="xuat">Xuất</option>
              </select>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div v-if="loadingWarehouseHistory" class="text-center py-5">
            <div class="spinner"></div>
            <p class="text-muted mt-2 fw-medium">Đang tải lịch sử...</p>
          </div>
          <div v-else-if="filteredWarehouseHistory.length === 0" class="text-center py-5">
            <div class="empty-icon-wrap mx-auto mb-3"><i class="fa-solid fa-clock-rotate-left fs-1"></i></div>
            <h6 class="fw-bold text-dark">Chưa có lịch sử nhập/xuất nào</h6>
            <p class="text-muted small">Lịch sử sẽ được hiển thị khi có thao tác với kho</p>
          </div>
          <div v-else class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="fw-bolder text-muted text-uppercase small ps-4">#</th>
                  <th class="fw-bolder text-muted text-uppercase small">Thời gian</th>
                  <th class="fw-bolder text-muted text-uppercase small">Loại tài nguyên</th>
                  <th class="fw-bolder text-muted text-uppercase small">Số lượng</th>
                  <th class="fw-bolder text-muted text-uppercase small">Ghi chú</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in filteredWarehouseHistory" :key="item.id ?? index" class="table-row-hover">
                  <td class="ps-4 text-muted small fw-bold">{{ index + 1 }}</td>
                  <td>
                    <div class="fw-medium text-dark">{{ formatDate(item.created_at || item.thoi_gian) }}</div>
                    <div class="small text-muted">{{ formatTime(item.created_at || item.thoi_gian) }}</div>
                  </td>
                  <td>
                    <span class="badge rounded-pill fw-medium" :class="getResourceTypeBadge(item.slug_tai_nguyen)">
                      <i :class="getResourceTypeIcon(item.slug_tai_nguyen)" class="me-1" :style="{ color: getResourceTypeColor(item.slug_tai_nguyen) }"></i>
                      {{ getResourceTypeLabel(item.slug_tai_nguyen) }}
                    </span>
                  </td>
                  <td>
                    <span v-if="item.loai === 'nhap'" class="badge bg-success-subtle text-success-emphasis fw-bold px-2">
                      <i class="fa-solid fa-arrow-up me-1" style="color: #198754;"></i>+{{ item.so_luong }}
                    </span>
                    <span v-else-if="item.loai === 'xuat'" class="badge bg-danger-subtle text-danger-emphasis fw-bold px-2">
                      <i class="fa-solid fa-arrow-down me-1" style="color: #dc3545;"></i>-{{ item.so_luong }}
                    </span>
                    <span v-else class="badge bg-dark-subtle text-dark-emphasis fw-bold px-2">
                      {{ item.so_luong }}
                    </span>
                  </td>
                  <td>
                    <span class="text-muted small">{{ item.ghi_chu || '—' }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ========== TAB 4: CẤP PHÁT (yêu cầu từ rescuer + lịch sử) ========== -->
    <div v-if="activeTab === 'allocation'" class="allocation-tab-root">
      <div class="row g-4 mb-4">
        <div class="col-12">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-2">
            <div>
              <h5 class="fw-bolder text-dark mb-1">
                <i class="fa-solid fa-inbox text-primary me-2"></i>Hàng đợi yêu cầu cấp phát
              </h5>
              <p class="text-muted small mb-0">Cứu hộ viên gửi yêu cầu — admin duyệt và trừ kho. Cập nhật realtime qua Reverb.</p>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-medium" @click="taiLaiToanBoCapPhat">
              <i class="fa-solid fa-rotate me-1"></i>Tải lại
            </button>
          </div>
          <div v-if="dangTaiDanhSachYeuCau" class="text-center py-5 bg-white rounded-4 shadow-sm">
            <div class="spinner mx-auto"></div>
            <p class="text-muted small mt-2 mb-0">Đang tải yêu cầu...</p>
          </div>
          <div v-else-if="danhSachYeuCauCapPhat.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm border border-light">
            <i class="fa-solid fa-clipboard-check text-secondary opacity-25 fs-1 d-block mb-3"></i>
            <p class="text-muted small mb-0">Không có yêu cầu chờ duyệt</p>
          </div>
          <div v-else class="row g-3">
            <div v-for="yc in danhSachYeuCauCapPhat" :key="'yc-' + yc.id" class="col-12 col-xl-6">
              <div
                class="card border-0 shadow-sm rounded-4 h-100 cap-request-card overflow-hidden"
                :class="{ 'border-danger-subtle ring-insufficient': yc.trang_thai === 'CHO_DUYET' && !yc.du_kho }"
              >
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                      <div class="fw-bolder text-dark">{{ yc.ten_doi || '—' }}</div>
                      <div class="small text-muted">
                        <i class="fa-solid fa-user me-1"></i>{{ yc.ten_nguoi_yeu_cau || '—' }}
                        <span class="mx-1">·</span>
                        <i class="fa-regular fa-clock me-1"></i>{{ formatDate(yc.created_at) }} {{ formatTime(yc.created_at) }}
                      </div>
                    </div>
                    <span class="badge rounded-pill fw-medium shrink-0" :class="badgeTrangThaiYeuCau(yc.trang_thai)">
                      {{ chuoiTrangThaiYeuCau(yc.trang_thai) }}
                    </span>
                  </div>
                  <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge fw-medium" :class="getResourceTypeBadge(laySlugHienThi(yc.slug_tai_nguyen))">
                      <i :class="getResourceTypeIcon(laySlugHienThi(yc.slug_tai_nguyen))" class="me-1"></i>
                      {{ layNhanLoaiTaiNguyen(yc.slug_tai_nguyen) }}
                    </span>
                    <span class="badge bg-dark-subtle text-dark-emphasis">YC: {{ yc.so_luong_yeu_cau }}</span>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis">Kho: {{ yc.so_luong_ton_kho }}</span>
                  </div>
                  <div v-if="yc.ghi_chu" class="small text-muted mb-3 fst-italic">“{{ yc.ghi_chu }}”</div>
                  <div v-if="yc.trang_thai === 'CHO_DUYET' && !yc.du_kho" class="alert alert-danger py-2 px-3 small mb-3 mb-md-0">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>Kho không đủ — không thể cấp phát cho đến khi nhập thêm.
                  </div>
                  <div v-if="yc.trang_thai === 'CHO_DUYET'" class="d-flex flex-wrap gap-2 mt-3">
                    <button
                      type="button"
                      class="btn btn-primary btn-sm rounded-pill fw-bold px-3"
                      :disabled="!yc.du_kho || dangXuLyCapPhat === yc.id"
                      @click="xuLyCapPhatTheoYeuCau(yc.id)"
                    >
                      <span v-if="dangXuLyCapPhat === yc.id"><i class="fa-solid fa-spinner fa-spin me-1"></i></span>
                      <i v-else class="fa-solid fa-paper-plane me-1"></i>Cấp phát
                    </button>
                    <button
                      type="button"
                      class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                      :disabled="dangXuLyTuChoi === yc.id || dangXuLyCapPhat === yc.id"
                      @click="xuLyTuChoiYeuCau(yc.id)"
                    >
                      <span v-if="dangXuLyTuChoi === yc.id"><i class="fa-solid fa-spinner fa-spin me-1"></i></span>
                      Từ chối
                    </button>
                  </div>
                </div>
                <div v-if="yc.trang_thai === 'CHO_DUYET' && yc.du_kho" class="px-4 pb-3">
                  <div class="progress rounded-pill" style="height: 8px;">
                    <div
                      class="progress-bar rounded-pill"
                      :class="thanhTienTrinhTonKho(yc)"
                      role="progressbar"
                      :style="{ width: phanTramTienTrinhTon(yc) + '%' }"
                    ></div>
                  </div>
                  <div class="d-flex justify-content-between small text-muted mt-1">
                    <span>Tồn sau cấp (ước tính)</span>
                    <span class="fw-bold text-dark">{{ Math.max(0, (yc.so_luong_ton_kho || 0) - (yc.so_luong_yeu_cau || 0)) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card panel-card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
          <h5 class="fw-bolder text-dark mb-0">
            <i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Lịch sử cấp phát
          </h5>
          <p class="text-muted small mb-0 mt-1">Đội nhận, người yêu cầu, admin xử lý và thời gian</p>
        </div>
        <div class="card-body px-4 pt-3 pb-4">
          <div class="row g-2 align-items-end mb-3">
            <div class="col-md-3">
              <label class="form-label text-muted small fw-bolder text-uppercase mb-1">Đội</label>
              <select v-model="boLocLichSu.id_doi_cuu_ho" class="form-select custom-input form-select-sm" @change="taiLichSuCapPhatCoBoLoc">
                <option value="">Tất cả</option>
                <option v-for="t in teams" :key="'ls-' + t.id_doi_cuu_ho" :value="String(t.id_doi_cuu_ho)">{{ t.ten_doi }}</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label text-muted small fw-bolder text-uppercase mb-1">Từ ngày</label>
              <input v-model="boLocLichSu.tu_ngay" type="date" class="form-control custom-input form-control-sm" @change="taiLichSuCapPhatCoBoLoc">
            </div>
            <div class="col-md-2">
              <label class="form-label text-muted small fw-bolder text-uppercase mb-1">Đến ngày</label>
              <input v-model="boLocLichSu.den_ngay" type="date" class="form-control custom-input form-control-sm" @change="taiLichSuCapPhatCoBoLoc">
            </div>
            <div class="col-md-3">
              <label class="form-label text-muted small fw-bolder text-uppercase mb-1">Trạng thái</label>
              <select v-model="boLocLichSu.trang_thai" class="form-select custom-input form-select-sm" @change="taiLichSuCapPhatCoBoLoc">
                <option value="">Đã cấp + Từ chối</option>
                <option value="DA_CAP_PHAT">Đã cấp phát</option>
                <option value="TU_CHOI">Từ chối</option>
              </select>
            </div>
          </div>
          <div v-if="dangTaiLichSuCapPhat" class="text-center py-4">
            <div class="spinner"></div>
          </div>
          <div v-else-if="lichSuCapPhat.length === 0" class="text-center py-4 text-muted small">Chưa có lịch sử</div>
          <div v-else class="table-responsive rounded-3 border">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="fw-bolder text-muted text-uppercase small ps-3">Đội nhận</th>
                  <th class="fw-bolder text-muted text-uppercase small">Người YC</th>
                  <th class="fw-bolder text-muted text-uppercase small">Tài nguyên</th>
                  <th class="fw-bolder text-muted text-uppercase small">SL</th>
                  <th class="fw-bolder text-muted text-uppercase small">Admin</th>
                  <th class="fw-bolder text-muted text-uppercase small">id duyệt</th>
                  <th class="fw-bolder text-muted text-uppercase small pe-3">Thời gian duyệt</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in lichSuCapPhat" :key="'lsr-' + row.id" class="table-row-hover">
                  <td class="ps-3 fw-medium">{{ row.ten_doi || '—' }}</td>
                  <td>{{ row.ten_nguoi_yeu_cau || '—' }}</td>
                  <td>{{ layNhanLoaiTaiNguyen(row.slug_tai_nguyen) }}</td>
                  <td><span class="badge bg-dark-subtle text-dark-emphasis">{{ row.so_luong_yeu_cau }}</span></td>
                  <td>{{ row.ten_nguoi_duyet || '—' }}</td>
                  <td class="small text-muted">{{ row.id_nguoi_duyet ?? '—' }}</td>
                  <td class="pe-3 small">{{ formatDate(row.thoi_gian_duyet) }} {{ formatTime(row.thoi_gian_duyet) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  

    <!-- ========== MODAL: ADD/EDIT RESOURCE ========== -->
    <div v-if="showResourceModal" class="modal-overlay" @click.self="closeResourceModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-xl">
          <div class="modal-header border-bottom-0 bg-white py-3 px-4">
            <h5 class="modal-title fw-bolder text-dark">
              <i class="fa-solid fa-boxes-packing text-warning me-2"></i>
              {{ editingResource ? 'Sửa tài nguyên' : 'Thêm tài nguyên mới' }}
            </h5>
            <button type="button" class="btn-close" @click="closeResourceModal"></button>
          </div>
          <div class="modal-body p-4 bg-white">
            <div v-if="!editingResource" class="mb-3">
              <label class="form-label text-muted small fw-bolder text-uppercase">Thuộc đội <span class="text-danger">*</span></label>
              <select v-model="resourceForm.id_doi_cuu_ho" class="form-select custom-input">
                <option value="">-- Chọn đội --</option>
                <option v-for="t in teams" :key="t.id_doi_cuu_ho" :value="t.id_doi_cuu_ho">{{ t.ten_doi }}</option>
              </select>
            </div>
            <div v-else class="mb-3">
              <label class="form-label text-muted small fw-bolder text-uppercase">Đội</label>
              <input :value="getTeamName(resourceForm.id_doi_cuu_ho)" type="text" class="form-control custom-input" disabled>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-muted small fw-bolder text-uppercase">Loại tài nguyên <span class="text-danger">*</span></label>
                <select v-model="resourceForm.slug_tai_nguyen" class="form-select custom-input">
                  <option value="Vehicle">Xe cứu hộ</option>
                  <option value="Supply">Nhu yếu phẩm</option>
                  <option value="Medical">Vật tư y tế</option>
                  <option value="Equipment">Dụng cụ thiết bị</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label text-muted small fw-bolder text-uppercase">Số lượng <span class="text-danger">*</span></label>
                <input v-model.number="resourceForm.so_luong" type="number" min="0" class="form-control custom-input">
              </div>
              <div class="col-12">
                <label class="form-label text-muted small fw-bolder text-uppercase">Tên tài nguyên</label>
                <input v-model="resourceForm.ten_tai_nguyen" type="text" class="form-control custom-input"
                  :placeholder="getDefaultResourceName(resourceForm.slug_tai_nguyen)">
              </div>
              <div class="col-12">
                <label class="form-label text-muted small fw-bolder text-uppercase">Trạng thái</label>
                <div class="form-check form-switch">
                  <input v-model="resourceForm.trang_thai" class="form-check-input" type="checkbox" role="switch" id="resStatusSwitch">
                  <label class="form-check-label fw-medium" for="resStatusSwitch">
                    {{ resourceForm.trang_thai ? 'Hoạt động' : 'Tạm ngưng' }}
                  </label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-top-0 bg-light py-3 px-4">
            <button type="button" class="btn btn-light fw-medium px-4" @click="closeResourceModal">Hủy</button>
            <button type="button" class="btn btn-primary fw-bolder px-4" :disabled="!canSubmitResource || submittingResource" @click="submitResource">
              <span v-if="submittingResource"><i class="fa-solid fa-spinner fa-spin me-2"></i>Đang xử lý...</span>
              <span v-else>
                <i :class="editingResource ? 'fa-solid fa-floppy-disk' : 'fa-solid fa-plus'" class="me-2"></i>
                {{ editingResource ? 'Lưu thay đổi' : 'Thêm tài nguyên' }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ========== MODAL: NHAP KHO ========== -->
    <div v-if="showWarehouseModal" class="modal-overlay" @click.self="closeWarehouseModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-xl">
          <div class="modal-header border-bottom-0 bg-white py-3 px-4">
            <h5 class="modal-title fw-bolder text-dark">
              <i class="fa-solid fa-warehouse text-warning me-2"></i>
              Nhập kho: {{ warehouseForm.ten_hien_thi }}
            </h5>
            <button type="button" class="btn-close" @click="closeWarehouseModal"></button>
          </div>
          <div class="modal-body p-4 bg-white">
            <div class="alert alert-purple-light d-flex align-items-center gap-2 mb-3">
              <i class="fa-solid fa-circle-info text-purple"></i>
              <span class="small text-purple-dark">Số lượng nhập thêm sẽ được cộng vào kho tổng. Không ảnh hưởng đến tài nguyên của các đội cứu hộ.</span>
            </div>
            <div class="mb-3">
              <label class="form-label text-muted small fw-bolder text-uppercase">Số lượng hiện tại</label>
              <div class="form-control custom-input bg-light fw-bolder fs-4 text-center">
                {{ warehouseForm.tong_so_luong_hien_tai }} {{ getWarehouseUnit(warehouseForm.slug_tai_nguyen) }}
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label text-muted small fw-bolder text-uppercase">Số lượng nhập thêm <span class="text-danger">*</span></label>
              <input v-model.number="warehouseForm.so_luong_nhap" type="number" min="1" class="form-control custom-input"
                placeholder="Nhập số lượng cần thêm...">
            </div>
            <div class="mb-3">
              <label class="form-label text-muted small fw-bolder text-uppercase">Ghi chú</label>
              <textarea v-model="warehouseForm.ghi_chu" class="form-control custom-input" rows="2"
                placeholder="Ghi chú (tùy chọn)..."></textarea>
            </div>
            <div v-if="warehouseForm.so_luong_nhap > 0" class="result-preview">
              <span class="fw-medium text-muted small">Sau khi nhập:</span>
              <span class="fw-bolder text-dark ms-2">
                {{ warehouseForm.tong_so_luong_hien_tai + warehouseForm.so_luong_nhap }}
                {{ getWarehouseUnit(warehouseForm.slug_tai_nguyen) }}
              </span>
              <span class="badge bg-success-subtle text-success-emphasis ms-2 fw-medium">
                <i class="fa-solid fa-arrow-up me-1"></i>+{{ warehouseForm.so_luong_nhap }}
              </span>
            </div>
          </div>
          <div class="modal-footer border-top-0 bg-light py-3 px-4">
            <button type="button" class="btn btn-light fw-medium px-4" @click="closeWarehouseModal">Hủy</button>
            <button type="button" class="btn btn-primary fw-bolder px-4" :disabled="!warehouseForm.so_luong_nhap || warehouseForm.so_luong_nhap < 1 || submittingWarehouse" @click="submitWarehouse">
              <span v-if="submittingWarehouse"><i class="fa-solid fa-spinner fa-spin me-2"></i>Đang nhập kho...</span>
              <span v-else><i class="fa-solid fa-warehouse me-2"></i>Nhập kho</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ========== CONFIRM DELETE MODAL ========== -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-xl">
          <div class="modal-body p-4 text-center bg-white">
            <div class="mb-3">
              <div class="delete-icon-wrap mx-auto">
                <i class="fa-solid fa-trash text-danger fs-1"></i>
              </div>
            </div>
            <h5 class="fw-bolder text-dark mb-2">Xác nhận xóa?</h5>
            <p class="text-muted mb-0">
              Bạn có chắc muốn xóa <strong>{{ deleteTarget.name }}</strong>?
              <br>Hành động này không thể hoàn tác.
            </p>
          </div>
          <div class="modal-footer border-top-0 bg-light py-3 px-4 justify-content-center gap-2">
            <button type="button" class="btn btn-light fw-medium px-4" @click="showDeleteModal = false">Hủy</button>
            <button type="button" class="btn btn-danger fw-bolder px-4" :disabled="submittingDelete" @click="executeDelete">
              <span v-if="submittingDelete"><i class="fa-solid fa-spinner fa-spin me-2"></i>Đang xóa...</span>
              <span v-else><i class="fa-solid fa-trash me-2"></i>Xóa</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
      <div v-for="toast in toasts" :key="toast.id"
        class="toast show align-items-center text-white border-0 mb-2 shadow-lg"
        :class="toast.type === 'success' ? 'bg-success' : toast.type === 'error' ? 'bg-danger' : 'bg-warning'" role="alert">
        <div class="d-flex align-items-center gap-2 px-3 py-2">
          <i :class="toast.type === 'success' ? 'fa-solid fa-check-circle' : toast.type === 'error' ? 'fa-solid fa-circle-xmark' : 'fa-solid fa-circle-info'"></i>
          <span class="fw-medium">{{ toast.message }}</span>
          <button type="button" class="btn-close btn-close-white ms-2" @click="removeToast(toast.id)"></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { adminResourcesAPI } from "../../../services/api.js";
import { loadOpenMap, createOpenMap, createOpenMapMarker } from "../../../utils/openMap.js";

const TEN_HIEN_THI = {
  Vehicle: 'Xe cứu hộ',
  Supply: 'Nhu yếu phẩm',
  Medical: 'Vật tư y tế',
  Equipment: 'Dụng cụ thiết bị',
};
const TEN_MACC_DINH = {
  Vehicle: 'Xe cứu hộ',
  Supply: 'Nhu yếu phẩm',
  Medical: 'Vật tư y tế',
  Equipment: 'Dụng cụ thiết bị',
};

/** Nhãn loại đồng bộ với bảng kho (slug snake_case) */
const TEN_SLUG_KHO = {
  xe_cuu_ho: 'Xe cứu hộ',
  nhu_yeu_pham: 'Nhu yếu phẩm',
  vat_tu_y_te: 'Vật tư y tế',
  dung_cu_thi_cong: 'Dụng cụ thi công',
};

export default {
  name: "AdminResources",
  data() {
    return {
      activeTab: 'teams',
      tabs: [
        { id: 'resources', label: 'Tài nguyên', icon: 'fa-solid fa-boxes-packing' },
        { id: 'warehouse', label: 'Kho', icon: 'fa-solid fa-warehouse' },
        { id: 'allocation', label: 'Cấp phát', icon: 'fa-solid fa-paper-plane' },
      ],

      // Teams
      teams: [],
      loadingTeams: false,
      searchTeams: '',
      allResources: [],
      loadingResources: false,
      searchResources: '',
      filterResourceTeam: '',

      // Warehouse
      warehouseItems: [],
      loadingWarehouse: false,
      warehouseHistory: [],
      loadingWarehouseHistory: false,
      searchWarehouse: '',
      filterWarehouseType: '',
      filterWarehouseAction: '',

      // TAB Cấp phát — yêu cầu từ rescuer
      danhSachYeuCauCapPhat: [],
      dangTaiDanhSachYeuCau: false,
      lichSuCapPhat: [],
      dangTaiLichSuCapPhat: false,
      dangXuLyCapPhat: null,
      dangXuLyTuChoi: null,
      boLocLichSu: {
        id_doi_cuu_ho: '',
        tu_ngay: '',
        den_ngay: '',
        trang_thai: '',
      },
      kenhEchoYeuCau: null,

      // Team modal
      showTeamModal: false,
      editingTeam: null,
      submittingTeam: false,
      teamForm: {
        ten_doi: '',
        khu_vuc_quan_ly: '',
        so_dien_thoai_hotline: '',
        vi_tri_lat: 16.0544,
        vi_tri_lng: 108.2022,
        trang_thai: 'SAN_SANG',
        mo_ta: '',
        email: '',
        mat_khau: '',
      },
      teamMap: null,
      teamMarker: null,

      // Resource modal
      showResourceModal: false,
      editingResource: null,
      submittingResource: false,
      resourceForm: {
        id_doi_cuu_ho: '',
        slug_tai_nguyen: 'Vehicle',
        ten_tai_nguyen: '',
        so_luong: 0,
        trang_thai: 1,
      },

      // Warehouse modal
      showWarehouseModal: false,
      submittingWarehouse: false,
      warehouseForm: {
        slug_tai_nguyen: '',
        ten_hien_thi: '',
        tong_so_luong_hien_tai: 0,
        so_luong_nhap: null,
        ghi_chu: '',
      },

      // Delete modal
      showDeleteModal: false,
      submittingDelete: false,
      deleteTarget: { type: '', id: null, name: '' },

      // Toasts
      toasts: [],
      toastCounter: 0,
    };
  },
  computed: {
    totalResources() {
      return this.allResources.reduce((sum, r) => sum + (parseInt(r.so_luong) || 0), 0);
    },
    availableTeams() {
      return this.teams.filter(t => t.trang_thai === 'SAN_SANG').length;
    },
    totalWarehouseQty() {
      return this.warehouseItems.reduce((sum, item) => sum + (parseInt(item.tong_so_luong) || 0), 0);
    },
    filteredWarehouseHistory() {
      let list = this.warehouseHistory;
      if (this.filterWarehouseType) {
        list = list.filter(h => h.slug_tai_nguyen === this.filterWarehouseType);
      }
      if (this.filterWarehouseAction) {
        list = list.filter(h => h.loai === this.filterWarehouseAction);
      }
      if (this.searchWarehouse) {
        const q = this.normalizeSearch(this.searchWarehouse);
        list = list.filter(h =>
          this.normalizeSearch(this.getResourceTypeLabel(h.slug_tai_nguyen) || '').includes(q) ||
          this.normalizeSearch(h.ghi_chu || '').includes(q) ||
          this.normalizeSearch(h.slug_tai_nguyen || '').includes(q)
        );
      }
      return list;
    },
    filteredTeams() {
      if (!this.searchTeams) return this.teams;
      const q = this.normalizeSearch(this.searchTeams);
      return this.teams.filter(t =>
        this.normalizeSearch(t.ten_doi || '').includes(q) ||
        this.normalizeSearch(t.khu_vuc_quan_ly || '').includes(q)
      );
    },
    filteredResources() {
      let list = this.allResources;
      if (this.filterResourceTeam) {
        list = list.filter(r => r.id_doi_cuu_ho == this.filterResourceTeam);
      }
      if (this.searchResources) {
        const q = this.normalizeSearch(this.searchResources);
        list = list.filter(r =>
          this.normalizeSearch(r.ten_tai_nguyen || '').includes(q) ||
          this.normalizeSearch(r.slug_tai_nguyen || '').includes(q) ||
          this.normalizeSearch(this.getTeamName(r.id_doi_cuu_ho) || '').includes(q)
        );
      }
      return list;
    },
    groupedResources() {
      const groups = {};
      this.filteredResources.forEach(res => {
        const teamId = res.id_doi_cuu_ho;
        if (!groups[teamId]) {
          groups[teamId] = {
            teamName: this.getTeamName(teamId),
            teamId: teamId,
            resources: []
          };
        }
        groups[teamId].resources.push(res);
      });
      return Object.values(groups);
    },
    canSubmitResource() {
      return this.editingResource
        ? (this.resourceForm.slug_tai_nguyen && this.resourceForm.so_luong >= 0)
        : (this.resourceForm.id_doi_cuu_ho && this.resourceForm.slug_tai_nguyen && this.resourceForm.so_luong >= 0);
    },
  },
  watch: {
    activeTab(tab, tabTruoc) {
      if (tabTruoc === 'allocation' && tab !== 'allocation') {
        this.ngatEchoYeuCauCapPhat();
      }
      if (tab === 'teams') this.loadTeams();
      else if (tab === 'resources') this.loadResources();
      else if (tab === 'warehouse') { this.loadWarehouse(); this.loadWarehouseHistory(); }
      else if (tab === 'allocation') {
        this.taiLaiToanBoCapPhat();
        this.$nextTick(() => this.ketNoiEchoYeuCauCapPhat());
      }
    },
    'teamForm.vi_tri_lat'(val) { this.updateTeamMarker(); },
    'teamForm.vi_tri_lng'(val) { this.updateTeamMarker(); },
  },
  async mounted() {
    await this.loadTeams();
  },
  beforeUnmount() {
    this.ngatEchoYeuCauCapPhat();
    if (this.teamMap) {
      this.teamMap.remove();
      this.teamMap = null;
    }
  },
  methods: {
    removeDiacritics(str) {
      return str.normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, m => m === 'đ' ? 'd' : 'D');
    },
    normalizeSearch(text) {
      return this.removeDiacritics(String(text || '').toLowerCase());
    },    // ============ API ============
    async loadTeams() {
      this.loadingTeams = true;
      try {
        const res = await adminResourcesAPI.getList();
        const data = res.data?.data?.data ?? res.data?.data ?? res.data ?? [];
        this.teams = Array.isArray(data) ? data : [];
      } catch (e) {
        console.error('loadTeams', e);
        this.showToast('Không thể tải danh sách đội', 'error');
      } finally {
        this.loadingTeams = false;
      }
    },
    async loadResources() {
      this.loadingResources = true;
      try {
        const res = await adminResourcesAPI.getList({ per_page: 500 });
        const teamsData = res.data?.data?.data ?? res.data?.data ?? [];
        this.teams = Array.isArray(teamsData) ? teamsData : [];

        let allRes = [];
        for (const team of this.teams) {
          try {
            const r = await adminResourcesAPI.getByDoi(team.id_doi_cuu_ho);
            const resources = r.data?.data ?? [];
            allRes = allRes.concat(resources);
          } catch (_) {}
        }
        this.allResources = allRes;
      } catch (e) {
        console.error('loadResources', e);
        this.showToast('Không thể tải tài nguyên', 'error');
      } finally {
        this.loadingResources = false;
      }
    },
    async loadWarehouse() {
      this.loadingWarehouse = true;
      try {
        const res = await adminResourcesAPI.getKho();
        this.warehouseItems = res.data?.data ?? [];
      } catch (e) {
        console.error('loadWarehouse', e);
        this.showToast('Không thể tải dữ liệu kho', 'error');
      } finally {
        this.loadingWarehouse = false;
      }
    },
    async loadWarehouseHistory() {
      this.loadingWarehouseHistory = true;
      try {
        const res = await adminResourcesAPI.getLichSuKho({ per_page: 100 });
        const data = res.data?.data?.data ?? res.data?.data ?? res.data ?? [];
        this.warehouseHistory = Array.isArray(data) ? data : [];
      } catch (e) {
        console.error('loadWarehouseHistory', e);
      } finally {
        this.loadingWarehouseHistory = false;
      }
    },
    layNhanLoaiTaiNguyen(slug) {
      if (!slug) return '—';
      return TEN_SLUG_KHO[slug] || TEN_HIEN_THI[slug] || slug;
    },
    laySlugHienThi(slug) {
      if (TEN_HIEN_THI[slug]) return slug;
      const rev = Object.keys(TEN_HIEN_THI).find((k) => TEN_SLUG_KHO[slug] === TEN_HIEN_THI[k]);
      return rev || slug;
    },
    badgeTrangThaiYeuCau(tt) {
      const map = {
        CHO_DUYET: 'bg-warning-subtle text-warning-emphasis',
        DA_CAP_PHAT: 'bg-success-subtle text-success-emphasis',
        TU_CHOI: 'bg-danger-subtle text-danger-emphasis',
      };
      return map[tt] || 'bg-secondary-subtle text-secondary-emphasis';
    },
    chuoiTrangThaiYeuCau(tt) {
      const map = {
        CHO_DUYET: 'Chờ duyệt',
        DA_CAP_PHAT: 'Đã cấp phát',
        TU_CHOI: 'Từ chối',
      };
      return map[tt] || tt || '—';
    },
    phanTramTienTrinhTon(yc) {
      const ton = parseInt(yc.so_luong_ton_kho, 10) || 0;
      const xin = parseInt(yc.so_luong_yeu_cau, 10) || 1;
      if (ton <= 0) return 0;
      const p = Math.round((Math.min(xin, ton) / ton) * 100);
      return Math.min(100, Math.max(4, p));
    },
    thanhTienTrinhTonKho(yc) {
      const ton = parseInt(yc.so_luong_ton_kho, 10) || 0;
      const xin = parseInt(yc.so_luong_yeu_cau, 10) || 0;
      if (xin > ton) return 'bg-danger';
      if (xin / ton > 0.5) return 'bg-warning';
      return 'bg-success';
    },
    async taiDanhSachYeuCauCapPhat() {
      this.dangTaiDanhSachYeuCau = true;
      try {
        const res = await adminResourcesAPI.layDanhSachYeuCauCapPhat({
          trang_thai: 'CHO_DUYET',
          per_page: 50,
        });
        const payload = res.data?.data ?? {};
        this.danhSachYeuCauCapPhat = Array.isArray(payload.data) ? payload.data : [];
      } catch (e) {
        console.error('taiDanhSachYeuCauCapPhat', e);
        this.showToast('Không tải được danh sách yêu cầu', 'error');
      } finally {
        this.dangTaiDanhSachYeuCau = false;
      }
    },
    async taiLichSuCapPhatCoBoLoc() {
      this.dangTaiLichSuCapPhat = true;
      try {
        const params = { per_page: 80 };
        if (this.boLocLichSu.id_doi_cuu_ho) params.id_doi_cuu_ho = this.boLocLichSu.id_doi_cuu_ho;
        if (this.boLocLichSu.tu_ngay) params.tu_ngay = this.boLocLichSu.tu_ngay;
        if (this.boLocLichSu.den_ngay) params.den_ngay = this.boLocLichSu.den_ngay;
        if (this.boLocLichSu.trang_thai) params.trang_thai = this.boLocLichSu.trang_thai;
        const res = await adminResourcesAPI.getLichSuCapPhat(params);
        const payload = res.data?.data ?? {};
        this.lichSuCapPhat = Array.isArray(payload.data) ? payload.data : [];
      } catch (e) {
        console.error('taiLichSuCapPhatCoBoLoc', e);
        this.showToast('Không tải được lịch sử cấp phát', 'error');
      } finally {
        this.dangTaiLichSuCapPhat = false;
      }
    },
    async taiLaiToanBoCapPhat() {
      await Promise.all([this.taiDanhSachYeuCauCapPhat(), this.taiLichSuCapPhatCoBoLoc()]);
    },
    async xuLyCapPhatTheoYeuCau(id) {
      this.dangXuLyCapPhat = id;
      try {
        await adminResourcesAPI.capPhatTheoYeuCau(id);
        this.showToast('Cấp phát thành công', 'success');
        await this.taiLaiToanBoCapPhat();
        await this.loadResources();
        await this.loadWarehouse();
      } catch (e) {
        const msg = e.response?.data?.message || 'Không cấp phát được';
        this.showToast(msg, 'error');
      } finally {
        this.dangXuLyCapPhat = null;
      }
    },
    async xuLyTuChoiYeuCau(id) {
      if (!window.confirm('Từ chối yêu cầu này?')) return;
      this.dangXuLyTuChoi = id;
      try {
        await adminResourcesAPI.tuChoiYeuCauCapPhat(id, {});
        this.showToast('Đã từ chối yêu cầu', 'success');
        await this.taiLaiToanBoCapPhat();
      } catch (e) {
        const msg = e.response?.data?.message || 'Thao tác thất bại';
        this.showToast(msg, 'error');
      } finally {
        this.dangXuLyTuChoi = null;
      }
    },
    ketNoiEchoYeuCauCapPhat() {
      if (typeof window === 'undefined' || !window.Echo) return;
      this.ngatEchoYeuCauCapPhat();
      try {
        this.kenhEchoYeuCau = window.Echo.channel('admin-yeu-cau-cap-phat');
        this.kenhEchoYeuCau.listen('.yeu-cau-cap-phat', () => {
          if (this.activeTab === 'allocation') {
            this.taiLaiToanBoCapPhat();
          }
        });
        const conn = window.Echo.connector?.pusher?.connection;
        if (conn && conn.state !== 'connected') {
          conn.bind('connected', () => {
            if (this.activeTab === 'allocation') this.taiLaiToanBoCapPhat();
          });
        }
      } catch (e) {
        console.warn('[TaiNguyen] Echo yeu cau cap phat:', e);
      }
    },
    ngatEchoYeuCauCapPhat() {
      try {
        if (this.kenhEchoYeuCau && window.Echo) {
          window.Echo.leave('admin-yeu-cau-cap-phat');
        }
      } catch (_) { /* noop */ }
      this.kenhEchoYeuCau = null;
    },


    // ============ TEAM MODAL ============
    async openAddTeamModal() {
      this.editingTeam = null;
      this.teamForm = {
        ten_doi: '',
        khu_vuc_quan_ly: '',
        so_dien_thoai_hotline: '',
        vi_tri_lat: 16.0544,
        vi_tri_lng: 108.2022,
        trang_thai: 'SAN_SANG',
        mo_ta: '',
        email: '',
        mat_khau: '',
      };
      this.showTeamModal = true;
      await this.$nextTick();
      this.initTeamMap();
    },
    async openEditTeamModal(team) {
      this.editingTeam = team;
      this.teamForm = {
        id: team.id_doi_cuu_ho,
        ten_doi: team.ten_doi || '',
        khu_vuc_quan_ly: team.khu_vuc_quan_ly || '',
        so_dien_thoai_hotline: team.so_dien_thoai_hotline || '',
        vi_tri_lat: parseFloat(team.vi_tri_lat) || 16.0544,
        vi_tri_lng: parseFloat(team.vi_tri_lng) || 108.2022,
        trang_thai: team.trang_thai || 'SAN_SANG',
        mo_ta: team.mo_ta || '',
        email: team.email || '',
        mat_khau: '',
      };
      this.showTeamModal = true;
      await this.$nextTick();
      this.initTeamMap();
    },
    closeTeamModal() {
      this.showTeamModal = false;
      if (this.teamMap) {
        this.teamMap.remove();
        this.teamMap = null;
        this.teamMarker = null;
      }
    },
    async initTeamMap() {
      await this.$nextTick();
      const container = this.$refs.teamMapContainer;
      if (!container || this.teamMap) return;

      try {
        await loadOpenMap();
        this.teamMap = createOpenMap(container, {
          center: [this.teamForm.vi_tri_lng, this.teamForm.vi_tri_lat],
          zoom: 13,
        });

        this.teamMap.on('load', () => {
          this.updateTeamMarker();
        });

        this.teamMap.on('click', (e) => {
          const { lng, lat } = e.lngLat;
          this.teamForm.vi_tri_lng = parseFloat(lng.toFixed(6));
          this.teamForm.vi_tri_lat = parseFloat(lat.toFixed(6));
        });
      } catch (e) {
        console.error('initTeamMap', e);
      }
    },
    updateTeamMarker() {
      if (!this.teamMap) return;
      if (this.teamMarker) {
        this.teamMarker.setLngLat([this.teamForm.vi_tri_lng, this.teamForm.vi_tri_lat]);
      } else {
        this.teamMarker = createOpenMapMarker({
          type: 'home',
          position: { lng: this.teamForm.vi_tri_lng, lat: this.teamForm.vi_tri_lat },
          title: 'Vị trí đội cứu hộ',
        }).addTo(this.teamMap);
      }
    },
    centerMapOnTeam() {
      if (!this.teamMap || !this.teamForm.vi_tri_lat || !this.teamForm.vi_tri_lng) return;
      this.teamMap.flyTo({
        center: [this.teamForm.vi_tri_lng, this.teamForm.vi_tri_lat],
        zoom: 15,
        duration: 800,
      });
      this.updateTeamMarker();
    },
    onTeamCoordsChange() {
      this.updateTeamMarker();
    },
    async submitTeam() {
      if (!this.teamForm.ten_doi || !this.teamForm.khu_vuc_quan_ly) {
        this.showToast('Vui lòng nhập tên đội và khu vực quản lý', 'error');
        return;
      }
      this.submittingTeam = true;
      try {
        const payload = { ...this.teamForm };
        if (!payload.mat_khau) delete payload.mat_khau;
        if (!this.editingTeam) delete payload.id;

        if (this.editingTeam) {
          await adminResourcesAPI.update(payload);
          this.showToast('Cập nhật đội cứu hộ thành công!', 'success');
        } else {
          await adminResourcesAPI.create(payload);
          this.showToast('Tạo đội cứu hộ mới thành công!', 'success');
        }
        this.closeTeamModal();
        await this.loadTeams();
      } catch (e) {
        console.error('submitTeam', e);
        const msg = e.response?.data?.message || 'Có lỗi xảy ra';
        this.showToast(msg, 'error');
      } finally {
        this.submittingTeam = false;
      }
    },

    // ============ RESOURCE MODAL ============
    openAddResourceModal() {
      this.editingResource = null;
      this.resourceForm = {
        id_doi_cuu_ho: this.filterResourceTeam || '',
        slug_tai_nguyen: 'Vehicle',
        ten_tai_nguyen: '',
        so_luong: 0,
        trang_thai: 1,
      };
      this.showResourceModal = true;
    },
    openEditResourceModal(res) {
      this.editingResource = res;
      this.resourceForm = {
        id: res.id_tai_nguyen,
        id_doi_cuu_ho: res.id_doi_cuu_ho,
        slug_tai_nguyen: res.slug_tai_nguyen || 'Vehicle',
        ten_tai_nguyen: res.ten_tai_nguyen || '',
        so_luong: parseInt(res.so_luong) || 0,
        trang_thai: res.trang_thai ? true : false,
      };
      this.showResourceModal = true;
    },
    closeResourceModal() {
      this.showResourceModal = false;
      this.editingResource = null;
    },
    async submitResource() {
      this.submittingResource = true;
      try {
        const payload = {
          ...this.resourceForm,
          trang_thai: this.resourceForm.trang_thai ? 1 : 0,
        };
        if (this.editingResource) {
          await adminResourcesAPI.updateTaiNguyen(payload);
          this.showToast('Cập nhật tài nguyên thành công!', 'success');
        } else {
          await adminResourcesAPI.createTaiNguyen(payload);
          this.showToast('Thêm tài nguyên mới thành công!', 'success');
        }
        this.closeResourceModal();
        await this.loadResources();
        await this.loadWarehouse();
      } catch (e) {
        console.error('submitResource', e);
        const msg = e.response?.data?.message || 'Có lỗi xảy ra';
        this.showToast(msg, 'error');
      } finally {
        this.submittingResource = false;
      }
    },

    // ============ WAREHOUSE MODAL ============
    openUpdateWarehouseModal(item) {
      this.warehouseForm = {
        slug_tai_nguyen: item.slug_tai_nguyen,
        ten_hien_thi: item.ten_hien_thi,
        tong_so_luong_hien_tai: item.tong_so_luong,
        so_luong_nhap: null,
        ghi_chu: '',
      };
      this.showWarehouseModal = true;
    },
    closeWarehouseModal() {
      this.showWarehouseModal = false;
    },
    async submitWarehouse() {
      if (!this.warehouseForm.so_luong_nhap || this.warehouseForm.so_luong_nhap < 1) {
        this.showToast('Vui lòng nhập số lượng hợp lệ', 'error');
        return;
      }
      this.submittingWarehouse = true;
      try {
        await adminResourcesAPI.nhapKho({
          slug_tai_nguyen: this.warehouseForm.slug_tai_nguyen,
          so_luong: this.warehouseForm.so_luong_nhap,
          ghi_chu: this.warehouseForm.ghi_chu || '',
        });
        this.showToast('Nhập kho thành công!', 'success');
        this.closeWarehouseModal();
        await this.loadWarehouse();
        await this.loadWarehouseHistory();
      } catch (e) {
        console.error('submitWarehouse', e);
        const msg = e.response?.data?.message || 'Có lỗi xảy ra';
        this.showToast(msg, 'error');
      } finally {
        this.submittingWarehouse = false;
      }
    },

    // ============ DELETE ============
    confirmDeleteTeam(team) {
      this.deleteTarget = { type: 'team', id: team.id_doi_cuu_ho, name: team.ten_doi };
      this.showDeleteModal = true;
    },
    confirmDeleteResource(res) {
      this.deleteTarget = { type: 'resource', id: res.id_tai_nguyen, name: res.ten_tai_nguyen };
      this.showDeleteModal = true;
    },
    async executeDelete() {
      if (!this.deleteTarget.id) return;
      this.submittingDelete = true;
      try {
        if (this.deleteTarget.type === 'team') {
          await adminResourcesAPI.delete({ id: this.deleteTarget.id });
          this.showToast('Xóa đội cứu hộ thành công!', 'success');
          await this.loadTeams();
        } else {
          await adminResourcesAPI.deleteTaiNguyen({ id: this.deleteTarget.id });
          this.showToast('Xóa tài nguyên thành công!', 'success');
          await this.loadResources();
          await this.loadWarehouse();
        }
        this.showDeleteModal = false;
      } catch (e) {
        console.error('executeDelete', e);
        const msg = e.response?.data?.message || 'Có lỗi xảy ra';
        this.showToast(msg, 'error');
      } finally {
        this.submittingDelete = false;
      }
    },

    // ============ HELPERS ============
    getTeamName(id) {
      const t = this.teams.find(x => x.id_doi_cuu_ho == id);
      return t ? t.ten_doi : '—';
    },
    getTeamStatusBadge(status) {
      const map = {
        'SAN_SANG': 'bg-success-subtle text-success-emphasis',
        'BAN_CHI_VAO': 'bg-warning-subtle text-warning-emphasis',
        'TAM_NGHI': 'bg-secondary-subtle text-secondary-emphasis',
        'BAN_PHAN_CONG': 'bg-info-subtle text-info-emphasis',
      };
      return map[status] || 'bg-secondary-subtle text-secondary-emphasis';
    },
    getResourceTypeBadge(type) {
      const map = {
        'Vehicle': 'bg-primary-subtle text-primary-emphasis',
        'Supply': 'bg-success-subtle text-success-emphasis',
        'Medical': 'bg-danger-subtle text-danger-emphasis',
        'Equipment': 'bg-warning-subtle text-warning-emphasis',
      };
      return map[type] || 'bg-secondary-subtle text-secondary-emphasis';
    },
    getResourceTypeIcon(type) {
      const map = {
        'Vehicle': 'fa-solid fa-truck-medical',
        'Supply': 'fa-solid fa-bottle-water',
        'Medical': 'fa-solid fa-kit-medical',
        'Equipment': 'fa-solid fa-screwdriver-wrench',
      };
      return map[type] || 'fa-solid fa-box';
    },
    getResourceTypeColor(type) {
      const map = {
        'Vehicle': '#0d6efd',
        'Supply': '#198754',
        'Medical': '#dc3545',
        'Equipment': '#fd7e14',
      };
      return map[type] || '#6c757d';
    },
    getResourceTypeLabel(type) {
      return TEN_HIEN_THI[type] || type || '—';
    },
    getDefaultResourceName(type) {
      return TEN_MACC_DINH[type] || '';
    },
    getWarehouseIcon(type) {
      const map = {
        'Vehicle': 'fa-solid fa-truck-medical',
        'Supply': 'fa-solid fa-bottle-water',
        'Medical': 'fa-solid fa-kit-medical',
        'Equipment': 'fa-solid fa-screwdriver-wrench',
      };
      return map[type] || 'fa-solid fa-box';
    },
    getWarehouseIconClass(type) {
      const map = {
        'Vehicle': 'bg-primary-subtle text-primary',
        'Supply': 'bg-success-subtle text-success',
        'Medical': 'bg-danger-subtle text-danger',
        'Equipment': 'bg-warning-subtle text-warning',
      };
      return map[type] || 'bg-secondary-subtle text-secondary';
    },
    getWarehouseDesc(type) {
      const map = {
        'Vehicle': 'Phương tiện cứu hộ giao thông',
        'Supply': 'Lương thực, nước uống, vật dụng',
        'Medical': 'Băng, thuốc, dụng cụ y tế',
        'Equipment': 'Dụng cụ cứu hộ chuyên dụng',
      };
      return map[type] || '';
    },
    getWarehouseUnit(type) {
      const map = { Vehicle: 'chiếc', Supply: 'bộ', Medical: 'bộ', Equipment: 'bộ' };
      return map[type] || 'bộ';
    },
    getWarehouseMax(type) {
      const map = { Vehicle: 20, Supply: 500, Medical: 500, Equipment: 200 };
      return map[type] || 100;
    },
    getWarehouseProgressClass(type) {
      const map = {
        'Vehicle': 'bg-primary',
        'Supply': 'bg-success',
        'Medical': 'bg-danger',
        'Equipment': 'bg-warning',
      };
      return map[type] || 'bg-secondary';
    },
    formatDate(dateStr) {
      if (!dateStr) return '—';
      try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
      } catch { return dateStr; }
    },
    formatTime(dateStr) {
      if (!dateStr) return '';
      try {
        const d = new Date(dateStr);
        return d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
      } catch { return ''; }
    },
    showToast(message, type = 'success') {
      const id = ++this.toastCounter;
      this.toasts.push({ id, message, type });
      setTimeout(() => this.removeToast(id), 4000);
    },
    removeToast(id) {
      const idx = this.toasts.findIndex(t => t.id === id);
      if (idx > -1) this.toasts.splice(idx, 1);
    },
  },
};
</script>

<style scoped>
/* ===== STAT CHIPS ===== */
.stat-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 12px;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.stat-chip:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.stat-chip i { font-size: 1.1rem; }
.stat-chip-label { font-size: 0.8rem; color: #6c757d; font-weight: 500; }
.stat-chip-value { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; }

/* ===== BRAND ICON BOX ===== */
.brand-icon-box {
  width: 48px; height: 48px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

/* ===== TAB NAVIGATION ===== */
.tab-nav-wrapper {
  background: white;
  padding: 6px;
  border-radius: 14px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  border: 1px solid #f1f3f4;
  display: inline-flex;
  width: 100%;
}
.tab-btn {
  flex: 1;
  border: none;
  background: transparent;
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 600;
  color: #6c757d;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  white-space: nowrap;
}
.tab-btn:hover { background: #f8f9fa; color: #1a1a2e; }
.tab-btn.active {
  background: #0d6efd;
  color: white;
  box-shadow: 0 4px 12px rgba(13,110,253,0.3);
}

/* ===== TABLE STYLES ===== */
.table-row-hover:hover { background-color: #f8faff; }
.table th {
  font-size: 0.72rem;
  padding: 10px 12px;
  border-top: none;
}
.table td { padding: 12px 12px; }
.table { font-size: 0.875rem; }

/* ===== MEMBER AVATARS ===== */
.member-avatars { display: flex; }
.member-avatar {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: #0d6efd;
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid white;
  margin-left: -8px;
  cursor: pointer;
  transition: transform 0.15s ease;
}
.member-avatar:first-child { margin-left: 0; }
.member-avatar:hover { transform: translateY(-2px); z-index: 1; }

/* ===== ACTION BUTTONS ===== */
.action-btn {
  width: 32px; height: 32px;
  padding: 0;
  display: flex; align-items: center; justify-content: center;
  border-radius: 8px;
  transition: all 0.2s ease;
  cursor: pointer;
}
.action-btn:hover { transform: translateY(-1px); }

/* ===== WAREHOUSE CARDS ===== */
.warehouse-card {
  border-radius: 16px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: default;
}
.warehouse-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important; }
.warehouse-icon {
  width: 52px; height: 52px;
  font-size: 1.4rem;
}
.warehouse-qty { font-size: 2.5rem; }

/* ===== WAREHOUSE SUMMARY PILL ===== */
.warehouse-summary-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 50px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}
.warehouse-summary-pill.active {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
}
.warehouse-summary-pill.empty {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  color: #6b7280;
}
.warehouse-summary-pill i { font-size: 0.9rem; }
.warehouse-summary-pill span.fw-bolder { font-size: 1.1rem; }

/* ===== PURPLE ALERT ===== */
.alert-purple-light {
  background-color: #f5f3ff;
  border: 1px solid #e9d5ff;
  border-radius: 10px;
  color: #5b21b6;
  padding: 10px 14px;
  font-size: 0.85rem;
}
.text-purple { color: #7c3aed !important; }
.text-purple-dark { color: #5b21b6 !important; }

/* ===== RESULT PREVIEW ===== */
.result-preview {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 10px;
  padding: 10px 14px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
}

/* ===== EMPTY STATE ===== */
.empty-icon-wrap {
  width: 64px; height: 64px;
  border-radius: 50%;
  background: #f1f5f9;
  color: #94a3b8;
  display: flex; align-items: center; justify-content: center;
}

/* ===== MODAL STYLES ===== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(4px);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.shadow-xl { box-shadow: 0 20px 60px rgba(0,0,0,0.2); }

/* ===== DELETE MODAL ===== */
.delete-icon-wrap {
  width: 80px; height: 80px;
  border-radius: 50%;
  background: #fee2e2;
  display: flex; align-items: center; justify-content: center;
}

/* ===== SEARCH BOX ===== */
.search-box {
  position: relative;
  display: flex;
  align-items: center;
}
.search-box .search-icon {
  position: absolute;
  left: 12px;
  color: #adb5bd;
  font-size: 0.875rem;
  pointer-events: none;
}
.search-box input {
  padding-left: 36px !important;
  border-radius: 10px !important;
  font-size: 0.875rem;
}

/* ===== MINI MAP ===== */
.mini-map-container {
  border: 2px solid #e9ecef;
  transition: border-color 0.2s ease;
}
.mini-map-container:hover { border-color: #0d6efd; }

/* ===== FORM INPUTS ===== */
.custom-input {
  border-radius: 10px !important;
  font-size: 0.875rem;
  border-color: #e9ecef;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.custom-input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 3px rgba(13,110,253,0.12);
}

/* ===== SPINNER ===== */
.spinner {
  width: 40px; height: 40px;
  border: 4px solid #e9ecef;
  border-top-color: #0d6efd;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.7s linear infinite; }

/* ===== TOAST ===== */
.toast { border-radius: 10px; animation: slideUp 0.3s ease; }
@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* ===== PAGE STYLES ===== */
.page-title { font-size: 1.5rem; }
.page-subtitle { font-size: 0.85rem; }
.panel-card { border-radius: 16px; overflow: hidden; }

/* ===== BADGE VARIANTS ===== */
/* Subtle colors are defined globally in badge-utils.css */
/* Only component-specific overrides here */

/* ===== TAB CẤP PHÁT (yêu cầu) ===== */
.allocation-tab-root .cap-request-card.ring-insufficient {
  box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.35);
}
.allocation-tab-root .shrink-0 { flex-shrink: 0; }
</style>
