<template>
  <div class="history-page">
    <div class="history-container">

      <!-- ===== PAGE HEADER ===== -->
      <header class="page-header">
        <div class="page-header-text">
          <h1 class="page-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="page-title-icon">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Lịch sử yêu cầu cứu hộ
          </h1>
          <p class="page-subtitle">Theo dõi các tín hiệu khẩn cấp đã hoàn thành</p>
        </div>

        <!-- Stats Pills -->
        <div v-if="!loading && danhsach.length > 0" class="stats-row">
          <div class="stat-pill">
            <span class="stat-number">{{ danhsach.length }}</span>
            <span class="stat-label">Yêu cầu</span>
          </div>
          <div class="stat-pill">
            <span class="stat-number">{{ ratedCount }}</span>
            <span class="stat-label">Đã đánh giá</span>
          </div>
          <div class="stat-pill">
            <span class="stat-number">{{ unratedCount }}</span>
            <span class="stat-label">Chưa đánh giá</span>
          </div>
        </div>
      </header>

      <!-- ===== FILTER BAR ===== -->
      <div class="filter-bar">
        <!-- Search -->
        <div class="filter-search">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="search-icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            class="search-input"
            placeholder="Tìm ID, loại sự cố, địa chỉ..."
            @input="onSearchInput"
          />
          <button v-if="searchQuery" class="search-clear" @click="clearSearch" aria-label="Xóa tìm kiếm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Type Filter -->
        <div class="filter-select-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="select-icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v7.5m0 0l-3-3m3 3l3-3m3 7.5V3" />
          </svg>
          <select v-model="selectedType" class="filter-select" @change="onFilterChange">
            <option value="">Tất cả loại</option>
            <option v-for="type in uniqueTypes" :key="type" :value="type">{{ type }}</option>
          </select>
        </div>

        <!-- Rating Filter -->
        <div class="filter-select-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="select-icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
          </svg>
          <select v-model="selectedRating" class="filter-select" @change="onFilterChange">
            <option value="">Tất cả đánh giá</option>
            <option value="5">5 sao</option>
            <option value="4">4 sao</option>
            <option value="3">3 sao</option>
            <option value="2">2 sao</option>
            <option value="1">1 sao</option>
            <option value="0">Chưa đánh giá</option>
          </select>
        </div>

        <!-- Clear All -->
        <button
          v-if="hasActiveFilters"
          class="btn-clear-all"
          @click="clearAllFilters"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Xóa lọc
        </button>
      </div>

      <!-- ===== RESULT COUNT ===== -->
      <Transition name="fade-slide">
        <div v-if="hasActiveFilters" class="result-count">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Tìm thấy <strong>{{ filteredList.length }}</strong> yêu cầu
          <span v-if="filteredList.length !== danhsach.length"> trong {{ danhsach.length }} yêu cầu</span>
        </div>
      </Transition>

      <!-- ===== LOADING SKELETON ===== -->
      <div v-if="loading" class="cards-grid">
        <div v-for="n in 4" :key="n" class="skeleton-card">
          <div class="skeleton-image"></div>
          <div class="skeleton-body">
            <div class="skeleton-line w-60"></div>
            <div class="skeleton-line w-80"></div>
            <div class="skeleton-line w-40"></div>
            <div class="skeleton-line w-90"></div>
            <div class="skeleton-line w-70"></div>
            <div class="skeleton-actions">
              <div class="skeleton-btn"></div>
              <div class="skeleton-btn"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== REQUEST CARDS ===== -->
      <div v-else-if="filteredList.length > 0" class="cards-grid">
        <article
          v-for="item in filteredList"
          :key="item.id"
          class="request-card"
          :class="{ 'card-has-rating': item.danh_gia }"
        >
          <!-- Card Image -->
          <div class="card-image-wrap">
            <img
              v-if="item.anh_hien_truong"
              :src="item.anh_hien_truong"
              :alt="`Hiện trường sự cố ${item.loai}`"
              class="card-image"
              loading="lazy"
            />
            <div v-else class="card-image-placeholder">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="['placeholder-icon', item.iconColor]">
                <path stroke-linecap="round" stroke-linejoin="round" :d="getIconPath(item.icon)" />
              </svg>
            </div>

            <!-- Status Badge -->
            <div class="card-status-badge">
              <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" viewBox="0 0 8 8">
                <path d="M4 0l1.5 3 3.5.5-2.5 2.5.5 3.5L4 7.5 1 9l.5-3.5L-1 3.5 2.5 3z"/>
              </svg>
              Hoàn thành
            </div>

            <!-- Rating overlay (if rated) -->
            <div v-if="item.danh_gia" class="card-rating-overlay">
              <span class="overlay-stars">
                <svg v-for="n in 5" :key="n" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" :fill="n <= item.danh_gia ? '#f59e0b' : 'none'" stroke="#f59e0b" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
              </span>
              <span class="overlay-label">{{ item.danh_gia }}/5</span>
            </div>
          </div>

          <!-- Card Body -->
          <div class="card-body">
            <!-- ID + Type Row -->
            <div class="card-meta-row">
              <span class="request-id">SOS-{{ String(item.id).padStart(4, "0") }}</span>
              <span class="incident-type-badge" :style="{ color: getTypeColor(item.iconColor) }">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="getIconPath(item.icon)" />
                </svg>
                {{ item.loai }}
              </span>
            </div>

            <!-- Time -->
            <div class="card-time">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ item.time }}
            </div>

            <!-- Address -->
            <div class="card-address">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="address-icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
              </svg>
              <span>{{ item.address }}</span>
            </div>

            <!-- Description -->
            <p class="card-description">{{ item.moTa }}</p>

            <!-- Inline Rating Display -->
            <div v-if="item.danh_gia" class="card-inline-rating">
              <svg v-for="n in 5" :key="n" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" :fill="n <= item.danh_gia ? '#f59e0b' : 'none'" stroke="#f59e0b" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
              </svg>
              <span class="inline-rating-text">{{ item.danh_gia }}/5</span>
            </div>

            <!-- Action Buttons -->
            <div class="card-actions">
              <button
                class="btn-action btn-detail"
                @click="showDetailModal(item)"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Chi tiết
              </button>
              <button
                class="btn-action btn-review"
                :class="{ 'btn-reviewed': item.danh_gia }"
                @click="openRatingModal(item)"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                {{ item.danh_gia ? "Xem đánh giá" : "Đánh giá" }}
              </button>
            </div>
          </div>
        </article>
      </div>

      <!-- ===== NOT AUTHENTICATED ===== -->
      <div v-else-if="error && error.includes('đăng nhập')" class="state-container">
        <div class="state-icon-wrap state-icon-warning">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>
        </div>
        <h3 class="state-title">Vui lòng đăng nhập</h3>
        <p class="state-description">Bạn cần đăng nhập để xem lịch sử yêu cầu cứu hộ</p>
        <button class="btn-primary-action" @click="$router.push('/dang-nhap')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
          </svg>
          Đăng nhập ngay
        </button>
      </div>

      <!-- ===== ERROR STATE ===== -->
      <div v-else-if="error" class="state-container">
        <div class="state-icon-wrap state-icon-error">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
          </svg>
        </div>
        <h3 class="state-title">{{ error }}</h3>
        <p class="state-description">Đã xảy ra lỗi khi tải dữ liệu</p>
        <button class="btn-secondary-action" @click="loadHistory">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Thử lại
        </button>
      </div>

      <!-- ===== EMPTY STATE ===== -->
      <div v-else-if="danhsach.length === 0" class="state-container">
        <div class="state-icon-wrap state-icon-empty">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
          </svg>
        </div>
        <h3 class="state-title">Chưa có yêu cầu hoàn thành</h3>
        <p class="state-description">Bạn chưa có yêu cầu cứu hộ nào được hoàn thành</p>
        <button class="btn-primary-action" @click="$router.push('/')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Gửi yêu cầu cứu hộ
        </button>
      </div>

      <!-- ===== NO FILTER RESULTS ===== -->
      <div v-else-if="filteredList.length === 0 && danhsach.length > 0" class="state-container">
        <div class="state-icon-wrap state-icon-empty">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
        </div>
        <h3 class="state-title">Không tìm thấy kết quả</h3>
        <p class="state-description">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
        <button class="btn-secondary-action" @click="clearAllFilters">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Xóa bộ lọc
        </button>
      </div>

    </div>

    <!-- ===== DETAIL MODAL ===== -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal" role="dialog" aria-modal="true" :aria-label="`Chi tiết yêu cầu SOS-${selectedItem?.id}`">
          <div class="detail-modal">
            <!-- Modal Header -->
            <div class="detail-modal-header">
              <div class="detail-header-left">
                <span class="detail-request-id">SOS-{{ String(selectedItem?.id).padStart(4, "0") }}</span>
                <div class="detail-status-badge">
                  <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 8 8">
                    <path d="M4 0l1.5 3 3.5.5-2.5 2.5.5 3.5L4 7.5 1 9l.5-3.5L-1 3.5 2.5 3z"/>
                  </svg>
                  Hoàn thành
                </div>
              </div>
              <button class="modal-close-btn" @click="closeModal" aria-label="Đóng">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Modal Image -->
            <div v-if="selectedItem?.anh_hien_truong" class="detail-modal-image">
              <img :src="selectedItem.anh_hien_truong" :alt="`Hiện trường ${selectedItem?.loai}`" class="detail-image" />
            </div>

            <!-- Modal Body -->
            <div class="detail-modal-body">
              <!-- Incident Type -->
              <div class="detail-section">
                <div class="detail-label-row">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                  </svg>
                  <span class="detail-label">Loại sự cố</span>
                </div>
                <p class="detail-value detail-type-value">{{ selectedItem?.loai }}</p>
              </div>

              <!-- Address -->
              <div class="detail-section">
                <div class="detail-label-row">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="detail-icon-danger">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                  </svg>
                  <span class="detail-label">Địa chỉ</span>
                </div>
                <p class="detail-value">{{ selectedItem?.address }}</p>
              </div>

              <!-- Completion Time -->
              <div class="detail-section">
                <div class="detail-label-row">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="detail-icon-success">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="detail-label">Thời gian hoàn thành</span>
                </div>
                <p class="detail-value">{{ selectedItem?.time }}</p>
              </div>

              <!-- Description -->
              <div class="detail-section">
                <div class="detail-label-row">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                  </svg>
                  <span class="detail-label">Mô tả</span>
                </div>
                <div class="detail-desc-box">
                  <p class="detail-value">{{ selectedItem?.moTa || "Không có mô tả" }}</p>
                </div>
              </div>

              <!-- Rescue Result -->
              <div v-if="selectedItem?.ketQua" class="detail-section">
                <div class="detail-label-row">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="detail-icon-success">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="detail-label">Kết quả cứu hộ</span>
                </div>
                <div class="detail-result-box">
                  <p class="detail-value">{{ selectedItem.ketQua }}</p>
                </div>
              </div>

              <!-- Rating -->
              <div v-if="selectedItem?.danh_gia" class="detail-section">
                <div class="detail-label-row">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="detail-icon-warning">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                  </svg>
                  <span class="detail-label">Đánh giá của bạn</span>
                </div>
                <div class="detail-rating-display">
                  <div class="detail-stars">
                    <svg v-for="n in 5" :key="n" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" :fill="n <= selectedItem.danh_gia ? '#f59e0b' : 'none'" stroke="#f59e0b" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                  </div>
                  <span class="detail-rating-label">{{ selectedItem.danh_gia }}/5</span>
                </div>
                <p v-if="selectedItem?.nhan_xet" class="detail-comment">{{ selectedItem.nhan_xet }}</p>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="detail-modal-footer">
              <button class="btn-secondary-action" @click="closeModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Đóng
              </button>
              <button class="btn-primary-action" @click="goToReview">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                {{ selectedItem?.danh_gia ? "Chỉnh sửa đánh giá" : "Đánh giá ngay" }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>



    <!-- ===== RATING MODAL ===== -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isRatingModalOpen" class="rating-overlay" @click.self="closeRatingModal">
          <div class="rating-modal" role="dialog" aria-modal="true">
            <!-- Header -->
            <div class="rating-header">
              <div class="rating-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
              </div>
              <div>
                <h3 class="rating-title">Đánh giá dịch vụ cứu hộ</h3>
                <p class="rating-subtitle">SOS-{{ ratingItem?.id }} · {{ ratingItem?.loai }}</p>
              </div>
              <button class="rating-close-btn" @click="closeRatingModal" aria-label="Đóng">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Stars -->
            <div class="rating-stars-section">
              <p class="rating-label">Bạn hài lòng với dịch vụ cứu hộ không?</p>
              <div class="stars-row">
                <button
                  v-for="n in 5" :key="n" type="button"
                  class="star-btn"
                  :class="{ active: n <= ratingHoveredStar, selected: n <= ratingSelected }"
                  @mouseenter="ratingHoveredStar = n"
                  @mouseleave="ratingHoveredStar = 0"
                  @click="setRating(n)"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" :fill="n <= ratingSelected || n <= ratingHoveredStar ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                  </svg>
                </button>
              </div>
              <Transition name="label-fade">
                <p v-if="ratingSelected > 0" class="rating-feedback-text" :class="`rating-${ratingSelected}`">
                  {{ ratingFeedbackLabel }}
                </p>
              </Transition>
            </div>

            <!-- Tags -->
            <div class="rating-tags-section">
              <p class="rating-label">Chia sẻ trải nghiệm của bạn</p>
              <div class="tags-grid">
                <button
                  v-for="tag in allRatingTags" :key="tag.label"
                  type="button"
                  class="tag-chip"
                  :class="{ selected: ratingSelectedTags.includes(tag.label) }"
                  @click="toggleRatingTag(tag.label)"
                >
                  <span>{{ tag.icon }}</span>
                  {{ tag.label }}
                </button>
              </div>
            </div>

            <!-- Comment -->
            <div class="rating-comment-section">
              <label class="rating-label">Nhận xét thêm <span class="optional-label">(tùy chọn)</span></label>
              <textarea
                v-model="ratingComment"
                class="rating-textarea"
                :class="{ 'has-content': ratingComment.length > 0 }"
                placeholder="Mô tả chi tiết trải nghiệm của bạn..."
                rows="3" maxlength="500"
                :disabled="ratingSubmitting"
              ></textarea>
              <div class="char-counter" :class="{ warning: ratingComment.length > 450 }">
                {{ ratingComment.length }}/500
              </div>
            </div>

            <!-- Actions -->
            <div class="rating-actions">
              <button type="button" class="btn-cancel" @click="closeRatingModal" :disabled="ratingSubmitting">Hủy</button>
              <button
                type="button"
                class="btn-submit"
                :disabled="ratingSelected === 0 || ratingSubmitting"
                @click="submitRating"
              >
                <span v-if="ratingSubmitting" class="btn-spinner"></span>
                <span v-else>
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                  </svg>
                  Gửi đánh giá
                </span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script>
import { rescueRequestAPI } from "../../../services/api";

const BASE_URL = "http://localhost:8000";

const TYPE_ICON = {
  "y tế":        { icon: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z", color: "text-danger" },
  "cháy nổ":    { icon: "M15.362 5.214a8.252 8.252 0 011.115 2.003c.475.133.956.259 1.441.418.972.318 1.97.566 2.986.832.63.167 1.295.308 1.99.416a6 6 0 003.356 0c.695-.108 1.36-.25 1.99-.416.971-.27 1.964-.52 2.982-.838.485-.16.97-.286 1.444-.42.485-.134.98-.265 1.46-.422M13 3.227V9.323c.95.19 1.898.35 2.85.507a1 1 0 011.09.942c.09.41.146.835.164 1.268v4.25a6 6 0 01-3.104 5.274 6 6 0 01-4.892 0 6 6 0 01-3.104-5.274V13.73c-.014-3.223.14-6.434.492-9.573M7 14a4 4 0 004 4h2a4 4 0 004-4 4 4 0 00-4-4H7z", color: "text-danger" },
  "cháy":       { icon: "M15.362 5.214a8.252 8.252 0 011.115 2.003c.475.133.956.259 1.441.418.972.318 1.97.566 2.986.832.63.167 1.295.308 1.99.416a6 6 0 003.356 0c.695-.108 1.36-.25 1.99-.416.971-.27 1.964-.52 2.982-.838.485-.16.97-.286 1.444-.42.485-.134.98-.265 1.46-.422M13 3.227V9.323c.95.19 1.898.35 2.85.507a1 1 0 011.09.942c.09.41.146.835.164 1.268v4.25a6 6 0 01-3.104 5.274 6 6 0 01-4.892 0 6 6 0 01-3.104-5.274V13.73c-.014-3.223.14-6.434.492-9.573M7 14a4 4 0 004 4h2a4 4 0 004-4 4 4 0 00-4-4H7z", color: "text-danger" },
  "lũ":         { icon: "M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z", color: "text-info" },
  "lũ lụt":     { icon: "M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z", color: "text-info" },
  "sóng thần": { icon: "M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z", color: "text-info" },
  "hạn hán":   { icon: "M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z", color: "text-warning" },
  "tai nạn":   { icon: "M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z", color: "text-warning" },
  "giao thông":{ icon: "M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12", color: "text-secondary" },
  "động đất": { icon: "M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z", color: "text-danger" },
};

const TYPE_COLORS = {
  "y tế":        "#dc2626",
  "cháy nổ":    "#dc2626",
  "cháy":       "#dc2626",
  "lũ":         "#0284c7",
  "lũ lụt":     "#0284c7",
  "sóng thần": "#0284c7",
  "hạn hán":   "#d97706",
  "tai nạn":   "#d97706",
  "giao thông":"#64748b",
  "động đất":  "#dc2626",
};

const DEFAULT_ICON = "M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z";

function normalizeValue(value, fallback = "") {
  if (value === null || value === undefined) return fallback;
  if (typeof value === "object") {
    return normalizeValue(
      value.ten_danh_muc || value.ten_loai_su_co || value.ten || value.name || fallback,
      fallback
    );
  }
  return String(value);
}

function extractUserId(parsed) {
  if (!parsed || typeof parsed !== "object") return null;
  const keys = ["id_nguoi_dung", "id", "user_id", "ma_nguoi_dung", "nguoi_dung_id"];
  for (const key of keys) {
    const value = parsed[key];
    if (value !== undefined && value !== null && value !== "") {
      return Number(value);
    }
  }
  return null;
}

function getCurrentUserId() {
  const sources = ["user_token", "user", "client"];
  for (const key of sources) {
    const raw = localStorage.getItem(key);
    if (!raw) continue;
    try {
      const parsed = JSON.parse(raw);
      const id = extractUserId(parsed);
      if (id) return id;
    } catch (_) {}
  }
  return null;
}

function formatTime(value) {
  if (!value) return "";
  const time = new Date(value);
  if (Number.isNaN(time.getTime())) return normalizeValue(value);
  return time.toLocaleString("vi-VN", {
    hour: "2-digit", minute: "2-digit",
    day: "2-digit", month: "2-digit", year: "numeric",
  });
}

function parseTypeIcon(rawType) {
  const type = normalizeValue(rawType).toLowerCase();
  for (const key of Object.keys(TYPE_ICON)) {
    if (type.includes(key)) return TYPE_ICON[key];
  }
  return { icon: DEFAULT_ICON, color: "text-secondary" };
}

function getImageUrl(image) {
  if (!image) return null;
  const raw = String(image);
  if (/^(https?:|data:)/i.test(raw)) return raw;
  if (raw.startsWith("uploads/") || raw.startsWith("/uploads/")) {
    return BASE_URL + (raw.startsWith("/") ? "" : "/") + raw;
  }
  return null;
}

export default {
  name: "LichSuYeuCau",
  data() {
    return {
      danhsach: [],
      loading: false,
      error: "",
      searchQuery: "",
      selectedType: "",
      selectedRating: "",
      isModalOpen: false,
      selectedItem: null,
      isRatingModalOpen: false,
      ratingItem: null,
      ratingSelected: 0,
      ratingHoveredStar: 0,
      ratingSelectedTags: [],
      ratingComment: "",
      ratingSubmitting: false,
      allRatingTags: [
        { label: "Phản hồi nhanh", icon: "⚡" },
        { label: "Thái độ tốt", icon: "😊" },
        { label: "Chuyên nghiệp", icon: "🏅" },
        { label: "Trang thiết bị đầy đủ", icon: "🔧" },
        { label: "Xử lý hiệu quả", icon: "✅" },
        { label: "Thông tin rõ ràng", icon: "📋" },
        { label: "Hỗ trợ tận tâm", icon: "🤝" },
        { label: "An toàn", icon: "🛡" },
      ],
    };
  },
  computed: {
    uniqueTypes() {
      const types = this.danhsach.map((item) => item.loai);
      return [...new Set(types)].filter((type) => type && type !== "Không rõ");
    },
    filteredList() {
      let result = [...this.danhsach];

      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase().trim();
        result = result.filter((item) => {
          const loai = (item.loai || "").toLowerCase();
          const address = (item.address || "").toLowerCase();
          const moTa = (item.moTa || "").toLowerCase();
          const id = String(item.id || "").toLowerCase();
          return loai.includes(query) || address.includes(query) || moTa.includes(query) || id.includes(query);
        });
      }

      if (this.selectedType) {
        result = result.filter((item) => item.loai === this.selectedType);
      }

      if (this.selectedRating) {
        const rating = parseInt(this.selectedRating);
        if (rating === 0) {
          result = result.filter((item) => !item.danh_gia || item.danh_gia === 0);
        } else {
          result = result.filter((item) => item.danh_gia === rating);
        }
      }

      return result;
    },
    ratedCount() {
      return this.danhsach.filter((item) => item.danh_gia && item.danh_gia > 0).length;
    },
    unratedCount() {
      return this.danhsach.filter((item) => !item.danh_gia || item.danh_gia === 0).length;
    },
    hasActiveFilters() {
      return !!(this.searchQuery || this.selectedType || this.selectedRating);
    },
    ratingFeedbackLabel() {
      const labels = { 1: "Rất không hài lòng", 2: "Không hài lòng", 3: "Bình thường", 4: "Hài lòng", 5: "Rất hài lòng" };
      return labels[this.ratingSelected] || "";
    },
  },
  async created() {
    await this.loadHistory();
  },
  methods: {
    onSearchInput() {},
    onFilterChange() {},

    clearSearch() {
      this.searchQuery = "";
    },

    clearAllFilters() {
      this.searchQuery = "";
      this.selectedType = "";
      this.selectedRating = "";
    },

    getIconPath(icon) {
      return icon || DEFAULT_ICON;
    },

    getTypeColor(colorClass) {
      const map = {
        "text-danger":    "#dc2626",
        "text-info":      "#0284c7",
        "text-warning":   "#d97706",
        "text-secondary": "#64748b",
      };
      return map[colorClass] || "#64748b";
    },

    hienToast(type, message) {
      if (this.$toast?.[type]) {
        this.$toast[type](message, { position: "top-right", duration: 3500 });
        return;
      }
      alert(message);
    },

    normalizeResults(items) {
      if (!Array.isArray(items)) return [];
      return items.map((item) => {
        const id = item.id_yeu_cau || item.id || item.ma_ket_qua || item.result_id || "";

        let typeLabel = "Không rõ";
        if (item.loaiSuCo) {
          typeLabel = normalizeValue(
            item.loaiSuCo.ten_danh_muc || item.loaiSuCo.ten_loai_su_co || item.loaiSuCo.ten || "Không rõ"
          );
        } else {
          typeLabel = normalizeValue(
            item.ten_loai_su_co || item.loai_su_co || item.loai || item.chi_tiet || item.chi_tiet_su_co || "Không rõ"
          );
        }

        const typeMeta = parseTypeIcon(typeLabel);

        let address = "";
        if (item.vi_tri_dia_chi) {
          address = item.vi_tri_dia_chi;
        } else if (item.dia_chi || item.address) {
          address = item.dia_chi || item.address;
        } else if (item.vi_tri_lat && item.vi_tri_lng) {
          address = `${item.vi_tri_lat}, ${item.vi_tri_lng}`;
        }

        const time = formatTime(
          item.thoi_gian_hoan_thanh || item.ngay_hoan_thanh || item.updated_at ||
          item.created_at || item.thoi_gian || item.time
        );

        let danhGia = null;
        let nhanXet = "";
        if (item.danhGias && item.danhGias.length > 0) {
          danhGia = item.danhGias[0].diem_danh_gia || item.danhGias[0].so_sao || item.danhGias[0].danh_gia || null;
          nhanXet = item.danhGias[0].noi_dung_danh_gia || item.danhGias[0].nhan_xet || item.danhGias[0].feedback || "";
        }
        danhGia = danhGia || item.diem_danh_gia || item.danh_gia || item.danh_gia_sao || item.rating || null;

        let anhHienTruong = null;
        if (item.hinh_anh) anhHienTruong = getImageUrl(item.hinh_anh);
        else if (item.anh_hien_truong) anhHienTruong = getImageUrl(item.anh_hien_truong);
        else if (item.anh) anhHienTruong = getImageUrl(item.anh);
        else if (item.image) anhHienTruong = getImageUrl(item.image);

        return {
          id,
          loai: typeLabel,
          moTa: normalizeValue(item.mo_ta || item.moTa || item.mota || item.description || ""),
          time: time || "Không xác định",
          address: address || "Chưa có địa chỉ",
          danh_gia: danhGia,
          nhan_xet: nhanXet || item.nhan_xet || item.feedback || "",
          icon: typeMeta.icon,
          iconColor: typeMeta.color,
          iconBg: typeMeta.bg,
          anh_hien_truong: anhHienTruong,
          trang_thai: item.trang_thai,
          ketQua: item.ket_qua || item.ketQua || item.ket_qua_cuu_ho || "",
          raw: item,
        };
      });
    },

    async loadHistory() {
      this.loading = true;
      this.error = "";
      try {
        const currentUserId = getCurrentUserId();
        if (!currentUserId) {
          this.danhsach = [];
          this.error = "Vui lòng đăng nhập để xem lịch sử yêu cầu.";
          return;
        }

        let response;
        try {
          response = await rescueRequestAPI.getByUser(currentUserId);
        } catch (apiError) {
          response = await rescueRequestAPI.getList();
        }

        const rawData = response?.data;
        let items = Array.isArray(rawData)
          ? rawData
          : Array.isArray(rawData?.data)
          ? rawData.data
          : Array.isArray(rawData?.data?.data)
          ? rawData.data.data
          : [];

        const completedItems = items.filter((item) => {
          const trangThai = (item.trang_thai || "").toUpperCase();
          const isCompleted = trangThai === "HOAN_THANH" || trangThai === "DA_HOAN_THANH";
          const itemUserId = extractUserId(item) || extractUserId(item?.nguoi_dung);
          return isCompleted && itemUserId === currentUserId;
        });

        this.danhsach = this.normalizeResults(completedItems);
      } catch (error) {
        console.error("Không tải được lịch sử yêu cầu:", error);
        this.error = "Không tải được yêu cầu. Vui lòng thử lại.";
        this.hienToast("error", this.error);
      } finally {
        this.loading = false;
      }
    },

    showDetailModal(item) {
      this.selectedItem = item;
      this.isModalOpen = true;
      document.body.style.overflow = "hidden";
    },

    closeModal() {
      this.isModalOpen = false;
      this.selectedItem = null;
      document.body.style.overflow = "";
    },

    openRatingModal(item) {
      this.ratingItem = item;
      this.ratingSelected = item?.danh_gia || 0;
      this.ratingHoveredStar = 0;
      this.ratingSelectedTags = [];
      this.ratingComment = item?.nhan_xet || "";
      this.ratingSubmitting = false;
      this.isRatingModalOpen = true;
      document.body.style.overflow = "hidden";
    },

    closeRatingModal() {
      this.isRatingModalOpen = false;
      this.ratingItem = null;
      document.body.style.overflow = "";
    },

    setRating(n) {
      this.ratingSelected = n;
    },

    toggleRatingTag(label) {
      const idx = this.ratingSelectedTags.indexOf(label);
      if (idx > -1) {
        this.ratingSelectedTags.splice(idx, 1);
      } else {
        this.ratingSelectedTags.push(label);
      }
    },

    goToReview() {
      this.closeModal();
      this.$nextTick(() => {
        this.ratingItem = this.selectedItem;
        this.openRatingModal(this.ratingItem);
      });
    },

    async submitRating() {
      if (this.ratingSelected === 0 || this.ratingSubmitting) return;
      this.ratingSubmitting = true;
      try {
        await rescueRequestAPI.submitRating(this.ratingItem.id, {
          diem_danh_gia: this.ratingSelected,
          noi_dung: this.ratingComment,
          tags: this.ratingSelectedTags.join(", "),
        });

        const payload = { id: this.ratingItem.id, danh_gia: this.ratingSelected, nhan_xet: this.ratingComment };
        const idx = this.danhsach.findIndex((item) => item.id === payload.id);
        if (idx > -1) {
          this.danhsach[idx].danh_gia = payload.danh_gia;
          this.danhsach[idx].nhan_xet = payload.nhan_xet;
        }
        if (this.selectedItem && this.selectedItem.id === payload.id) {
          this.selectedItem.danh_gia = payload.danh_gia;
          this.selectedItem.nhan_xet = payload.nhan_xet;
        }

        this.closeRatingModal();
        if (this.$toast?.success) {
          this.$toast.success("Cảm ơn bạn! Đánh giá đã được gửi.", { position: "top-right", duration: 3500 });
        }
      } catch (error) {
        console.error("Lỗi gửi đánh giá:", error);
        if (this.$toast?.error) {
          this.$toast.error(error?.response?.data?.message || "Gửi đánh giá thất bại. Vui lòng thử lại.", { position: "top-right", duration: 3500 });
        } else {
          alert("Gửi đánh giá thất bại. Vui lòng thử lại.");
        }
      } finally {
        this.ratingSubmitting = false;
      }
    },
  },
};
</script>

<style scoped>
/* ===== PAGE LAYOUT ===== */
.history-page {
  background: #f8fafc;
  min-height: 100vh;
  padding: 2rem 1.5rem;
}

.history-container {
  max-width: 1280px;
  margin: 0 auto;
}

/* ===== TRANSITIONS ===== */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-from .detail-modal,
.modal-fade-leave-to .detail-modal {
  transform: scale(0.96) translateY(8px);
}

/* ===== PAGE HEADER ===== */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 4px;
  display: flex;
  align-items: center;
  gap: 10px;
  letter-spacing: -0.02em;
}

.page-title-icon {
  color: #0369a1;
  flex-shrink: 0;
}

.page-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 600;
}

/* Stats */
.stats-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.stat-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.stat-number {
  font-size: 0.875rem;
  font-weight: 800;
  color: #0f172a;
}

.stat-label {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 500;
}

/* ===== FILTER BAR ===== */
.filter-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.filter-search {
  position: relative;
  flex: 1;
  min-width: 200px;
  max-width: 340px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 9px 36px 9px 36px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.875rem;
  font-family: inherit;
  color: #0f172a;
  background: #ffffff;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  box-sizing: border-box;
}

.search-input::placeholder {
  color: #94a3b8;
}

.search-input:focus {
  outline: none;
  border-color: #0369a1;
  box-shadow: 0 0 0 3px rgba(3, 105, 161, 0.1);
}

.search-clear {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  transition: color 0.15s ease, background 0.15s ease;
}

.search-clear:hover {
  color: #475569;
  background: #f1f5f9;
}

.filter-select-wrap {
  position: relative;
  min-width: 160px;
}

.select-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  pointer-events: none;
  z-index: 1;
}

.filter-select {
  width: 100%;
  padding: 9px 10px 9px 32px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.875rem;
  font-family: inherit;
  color: #475569;
  background: #ffffff;
  appearance: none;
  cursor: pointer;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  box-sizing: border-box;
}

.filter-select:focus {
  outline: none;
  border-color: #0369a1;
  box-shadow: 0 0 0 3px rgba(3, 105, 161, 0.1);
}

.btn-clear-all {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 9px 14px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  background: #ffffff;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.btn-clear-all:hover {
  border-color: #dc2626;
  color: #dc2626;
  background: #fef2f2;
}

/* ===== RESULT COUNT ===== */
.result-count {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  color: #64748b;
  margin-bottom: 1rem;
}

.result-count svg {
  color: #0369a1;
}

.result-count strong {
  color: #0f172a;
  font-weight: 700;
}

/* ===== SKELETON LOADING ===== */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
  gap: 1.25rem;
}

.skeleton-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #f1f5f9;
  overflow: hidden;
  display: flex;
}

.skeleton-image {
  width: 180px;
  min-height: 200px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  flex-shrink: 0;
}

.skeleton-body {
  flex: 1;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.skeleton-line {
  height: 12px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-actions {
  display: flex;
  gap: 8px;
  margin-top: auto;
}

.skeleton-btn {
  height: 36px;
  width: 90px;
  border-radius: 20px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ===== REQUEST CARDS ===== */
.request-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #f1f5f9;
  overflow: hidden;
  display: flex;
  transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
  cursor: default;
}

.request-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  border-color: #e2e8f0;
  transform: translateY(-2px);
}

.card-image-wrap {
  width: 180px;
  min-height: 200px;
  position: relative;
  flex-shrink: 0;
  overflow: hidden;
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.request-card:hover .card-image {
  transform: scale(1.04);
}

.card-image-placeholder {
  width: 100%;
  height: 100%;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
}

.placeholder-icon.text-danger { color: #dc2626; }
.placeholder-icon.text-info { color: #0284c7; }
.placeholder-icon.text-warning { color: #d97706; }
.placeholder-icon.text-secondary { color: #64748b; }

.card-status-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  font-size: 0.65rem;
  font-weight: 700;
  color: #ffffff;
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
}

.card-rating-overlay {
  position: absolute;
  bottom: 10px;
  left: 10px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: rgba(255, 255, 255, 0.92);
  border-radius: 8px;
  backdrop-filter: blur(4px);
}

.overlay-stars {
  display: flex;
  gap: 1px;
}

.overlay-label {
  font-size: 0.65rem;
  font-weight: 700;
  color: #0f172a;
  margin-left: 2px;
}

.card-body {
  flex: 1;
  padding: 1.125rem 1.25rem;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.card-meta-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.request-id {
  font-size: 0.7rem;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.incident-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  background: currentColor;
  background: rgba(0, 0, 0, 0.04);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.card-time {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.8rem;
  color: #64748b;
  margin-bottom: 5px;
}

.card-address {
  display: flex;
  align-items: flex-start;
  gap: 5px;
  font-size: 0.8rem;
  color: #64748b;
  margin-bottom: 10px;
  min-width: 0;
}

.address-icon {
  color: #dc2626;
  flex-shrink: 0;
  margin-top: 1px;
}

.card-address span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.card-description {
  font-size: 0.875rem;
  color: #475569;
  line-height: 1.5;
  margin: 0 0 10px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  flex-grow: 0;
}

.card-inline-rating {
  display: flex;
  align-items: center;
  gap: 2px;
  margin-bottom: 10px;
}

.inline-rating-text {
  font-size: 0.75rem;
  font-weight: 600;
  color: #f59e0b;
  margin-left: 4px;
}

.card-actions {
  display: flex;
  gap: 8px;
  margin-top: auto;
}

.btn-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 7px 14px;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.15s ease;
  border: 1.5px solid transparent;
}

.btn-detail {
  background: #0369a1;
  color: #ffffff;
  flex: 1;
}

.btn-detail:hover {
  background: #025190;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(3, 105, 161, 0.25);
}

.btn-detail:active {
  transform: translateY(0);
}

.btn-detail:focus-visible {
  outline: 2px solid #0369a1;
  outline-offset: 2px;
}

.btn-review {
  background: #fffbeb;
  color: #b45309;
  border-color: #fde68a;
  flex: 1;
}

.btn-review:hover {
  background: #fef3c7;
  border-color: #f59e0b;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

.btn-review:active {
  transform: translateY(0);
}

.btn-review:focus-visible {
  outline: 2px solid #f59e0b;
  outline-offset: 2px;
}

.btn-reviewed {
  background: #ecfdf5;
  color: #065f46;
  border-color: #a7f3d0;
}

.btn-reviewed:hover {
  background: #d1fae5;
  border-color: #10b981;
}

/* ===== STATE CONTAINERS ===== */
.state-container {
  text-align: center;
  padding: 4rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.state-icon-wrap {
  width: 72px;
  height: 72px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
}

.state-icon-warning { background: #fffbeb; color: #d97706; }
.state-icon-error   { background: #fef2f2; color: #dc2626; }
.state-icon-empty   { background: #f0fdf4; color: #10b981; }

.state-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.state-description {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0;
  max-width: 360px;
}

.btn-primary-action {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 10px 20px;
  background: #0369a1;
  color: #ffffff;
  border: none;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 0.5rem;
  box-shadow: 0 4px 14px rgba(3, 105, 161, 0.25);
}

.btn-primary-action:hover {
  background: #025190;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(3, 105, 161, 0.35);
}

.btn-primary-action:active {
  transform: translateY(0);
}

.btn-primary-action:focus-visible {
  outline: 2px solid #0369a1;
  outline-offset: 2px;
}

.btn-secondary-action {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 10px 20px;
  background: #ffffff;
  color: #475569;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.15s ease;
  margin-top: 0.5rem;
}

.btn-secondary-action:hover {
  border-color: #94a3b8;
  color: #0f172a;
  background: #f8fafc;
}

.btn-secondary-action:focus-visible {
  outline: 2px solid #0369a1;
  outline-offset: 2px;
}

/* ===== DETAIL MODAL ===== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.detail-modal {
  background: #ffffff;
  width: 100%;
  max-width: 580px;
  max-height: 90vh;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.18);
  display: flex;
  flex-direction: column;
}

.detail-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 20px 16px;
  border-bottom: 1px solid #f1f5f9;
  flex-shrink: 0;
}

.detail-header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.detail-request-id {
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.detail-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  font-size: 0.7rem;
  font-weight: 700;
  color: #ffffff;
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.modal-close-btn {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: none;
  background: #f1f5f9;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
  flex-shrink: 0;
}

.modal-close-btn:hover {
  background: #e2e8f0;
  color: #0f172a;
}

.modal-close-btn:focus-visible {
  outline: 2px solid #0369a1;
  outline-offset: 2px;
}

.detail-modal-image {
  max-height: 220px;
  overflow: hidden;
  flex-shrink: 0;
}

.detail-image {
  width: 100%;
  height: 220px;
  object-fit: cover;
}

.detail-modal-body {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}

.detail-section {
  margin-bottom: 16px;
}

.detail-section:last-child {
  margin-bottom: 0;
}

.detail-label-row {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 5px;
}

.detail-icon-danger  { color: #dc2626; }
.detail-icon-success { color: #10b981; }
.detail-icon-warning { color: #f59e0b; }

.detail-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.07em;
}

.detail-value {
  font-size: 0.95rem;
  color: #0f172a;
  margin: 0;
  line-height: 1.5;
  font-weight: 500;
}

.detail-type-value {
  color: #0369a1;
  font-weight: 700;
}

.detail-desc-box,
.detail-result-box {
  padding: 10px 14px;
  border-radius: 10px;
  margin-top: 4px;
}

.detail-desc-box {
  background: #f8fafc;
  border: 1px solid #f1f5f9;
}

.detail-result-box {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
}

.detail-rating-display {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
}

.detail-stars {
  display: flex;
  gap: 2px;
}

.detail-rating-label {
  font-size: 0.875rem;
  font-weight: 700;
  color: #f59e0b;
}

.detail-comment {
  font-size: 0.875rem;
  color: #475569;
  margin: 6px 0 0;
  font-style: italic;
  line-height: 1.5;
}

.detail-modal-footer {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 20px;
  border-top: 1px solid #f1f5f9;
  flex-shrink: 0;
}

.detail-modal-footer .btn-secondary-action,
.detail-modal-footer .btn-primary-action {
  flex: 1;
  margin-top: 0;
  justify-content: center;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .history-page {
    padding: 1.25rem 1rem;
  }

  .page-title {
    font-size: 1.35rem;
  }

  .stats-row {
    width: 100%;
    justify-content: flex-start;
  }

  .filter-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-search {
    max-width: 100%;
  }

  .filter-select-wrap {
    min-width: 0;
  }

  .cards-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .request-card {
    flex-direction: column;
  }

  .card-image-wrap {
    width: 100%;
    height: 180px;
    min-height: unset;
  }

  .card-body {
    padding: 1rem;
  }

  .card-meta-row {
    flex-direction: row;
    align-items: center;
  }

  .request-id {
    order: -1;
    width: 100%;
    margin-bottom: 4px;
  }

  .detail-modal {
    max-height: 85vh;
    border-radius: 20px 20px 0 0;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    max-width: 100%;
    align-self: flex-end;
  }

  .modal-overlay {
    align-items: flex-end;
    padding: 0;
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* ===== RATING MODAL ===== */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.25s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-from .rating-modal, .modal-fade-leave-to .rating-modal { transform: scale(0.95) translateY(8px); }
.label-fade-enter-active, .label-fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.label-fade-enter-from, .label-fade-leave-to { opacity: 0; transform: translateY(-4px); }

.rating-overlay {
  position: fixed; inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999; padding: 1rem;
}

.rating-modal {
  background: #ffffff; border-radius: 20px;
  width: 100%; max-width: 520px; max-height: 90vh;
  overflow-y: auto; box-shadow: 0 24px 64px rgba(0,0,0,0.18);
  overscroll-behavior: contain;
}

.rating-header {
  display: flex; align-items: center; gap: 12px;
  padding: 22px 22px 18px; border-bottom: 1px solid #f1f5f9;
}

.rating-header-icon {
  width: 44px; height: 44px; min-width: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0369a1, #0ea5e9);
  display: flex; align-items: center; justify-content: center;
  color: #ffffff; box-shadow: 0 4px 14px rgba(3,105,161,0.35); flex-shrink: 0;
}

.rating-title { font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0; }
.rating-subtitle { font-size: 0.78rem; color: #64748b; margin: 2px 0 0; }

.rating-close-btn {
  margin-left: auto; background: #f1f5f9; border: none;
  border-radius: 10px; width: 34px; height: 34px;
  display: flex; align-items: center; justify-content: center;
  color: #64748b; cursor: pointer; transition: all 0.2s ease; flex-shrink: 0;
}
.rating-close-btn:hover { background: #e2e8f0; color: #0f172a; }
.rating-close-btn:focus-visible { outline: 2px solid #0369a1; outline-offset: 2px; }

.rating-stars-section, .rating-tags-section, .rating-comment-section { padding: 18px 22px 0; }

.rating-label {
  font-size: 0.78rem; font-weight: 600; color: #475569;
  text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 10px;
}
.optional-label { font-weight: 400; text-transform: none; letter-spacing: 0; color: #94a3b8; }

.stars-row { display: flex; align-items: center; gap: 4px; margin-bottom: 8px; }

.star-btn {
  background: none; border: none; padding: 4px;
  cursor: pointer; color: #e2e8f0;
  transition: all 0.15s ease; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
}
.star-btn:hover, .star-btn.active { color: #f59e0b; transform: scale(1.12); }
.star-btn.selected { color: #f59e0b; }
.star-btn:focus-visible { outline: 2px solid #0369a1; outline-offset: 2px; }
.star-btn:active { transform: scale(0.9); }

.rating-feedback-text { font-size: 0.875rem; font-weight: 600; margin: 0 0 2px; }
.rating-1 { color: #dc2626; }
.rating-2 { color: #f97316; }
.rating-3 { color: #eab308; }
.rating-4 { color: #22c55e; }
.rating-5 { color: #10b981; }

.tags-grid { display: flex; flex-wrap: wrap; gap: 7px; }

.tag-chip {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 5px 11px; border-radius: 20px;
  border: 1.5px solid #e2e8f0; background: #f8fafc;
  font-size: 0.78rem; font-weight: 500; color: #475569;
  cursor: pointer; transition: all 0.15s ease;
}
.tag-chip:hover { border-color: #0369a1; background: #eff6ff; color: #0369a1; }
.tag-chip.selected { border-color: #0369a1; background: #0369a1; color: #ffffff; }
.tag-chip:focus-visible { outline: 2px solid #0369a1; outline-offset: 2px; }

.rating-textarea {
  width: 100%; border: 1.5px solid #e2e8f0;
  border-radius: 12px; padding: 10px 12px;
  font-size: 0.875rem; font-family: inherit; color: #0f172a;
  resize: vertical; min-height: 76px; background: #f8fafc;
  transition: border-color 0.2s ease, box-shadow 0.2s ease; box-sizing: border-box;
}
.rating-textarea::placeholder { color: #94a3b8; }
.rating-textarea:focus { outline: none; border-color: #0369a1; box-shadow: 0 0 0 3px rgba(3,105,161,0.1); background: #fff; }
.rating-textarea:disabled { opacity: 0.6; cursor: not-allowed; }

.char-counter { text-align: right; font-size: 0.73rem; color: #94a3b8; margin-top: 3px; transition: color 0.2s ease; }
.char-counter.warning { color: #f59e0b; }

.rating-actions { display: flex; align-items: center; gap: 10px; padding: 18px 22px 22px; }

.btn-cancel {
  flex: 0 0 auto; padding: 9px 18px; border-radius: 12px;
  border: 1.5px solid #e2e8f0; background: #fff;
  font-size: 0.875rem; font-weight: 600; color: #475569;
  cursor: pointer; transition: all 0.2s ease;
}
.btn-cancel:hover:not(:disabled) { border-color: #94a3b8; color: #0f172a; background: #f8fafc; }
.btn-cancel:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-submit {
  flex: 1; padding: 10px 18px; border-radius: 12px; border: none;
  background: linear-gradient(135deg, #0369a1, #0ea5e9);
  color: #fff; font-size: 0.875rem; font-weight: 700;
  cursor: pointer; transition: all 0.2s ease;
  display: flex; align-items: center; justify-content: center; gap: 6px;
  box-shadow: 0 4px 14px rgba(3,105,161,0.3);
}
.btn-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(3,105,161,0.4); }
.btn-submit:active:not(:disabled) { transform: translateY(0); }
.btn-submit:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }

.btn-spinner {
  width: 17px; height: 17px;
  border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
  border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block;
}

@keyframes spin { to { transform: rotate(360deg); } }

.rating-modal::-webkit-scrollbar { width: 4px; }
.rating-modal::-webkit-scrollbar-track { background: transparent; }
.rating-modal::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

@media (max-width: 480px) {
  .rating-modal { max-height: 85vh; border-radius: 20px 20px 0 0; position: fixed; bottom: 0; left: 0; right: 0; max-width: 100%; }
  .rating-overlay { align-items: flex-end; padding: 0; }
  .rating-header, .rating-stars-section, .rating-tags-section, .rating-comment-section, .rating-actions { padding-left: 16px; padding-right: 16px; }
}
</style>
