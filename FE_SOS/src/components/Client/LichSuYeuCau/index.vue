<template>
  <div class="history-page">
    <div class="history-container">

      <!-- ===== PAGE HEADER ===== -->
      <header class="page-header">
        <div class="page-header-text">
          <div class="title-row">
            <div class="title-icon-wrap">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h1 class="page-title">Lịch sử yêu cầu cứu hộ</h1>
              <p class="page-subtitle">Theo dõi &amp; quản lý các tín hiệu khẩn cấp của bạn</p>
            </div>
          </div>
        </div>
      </header>

      <!-- ===== STATS CARDS ===== -->
      <div v-if="!loading && danhsach.length > 0" class="stats-grid">
        <div class="stat-card stat-total">
          <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
          </div>
          <div class="stat-content">
            <span class="stat-number">{{ danhsach.length }}</span>
            <span class="stat-label">Tổng yêu cầu</span>
          </div>
        </div>
        <div class="stat-card stat-success">
          <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="stat-content">
            <span class="stat-number">{{ successCount }}</span>
            <span class="stat-label">Hoàn thành</span>
          </div>
        </div>
        <div class="stat-card stat-failed">
          <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="stat-content">
            <span class="stat-number">{{ failedCount }}</span>
            <span class="stat-label">Thất bại</span>
          </div>
        </div>
        <div class="stat-card stat-rated">
          <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
            </svg>
          </div>
          <div class="stat-content">
            <span class="stat-number">{{ ratedCount }}</span>
            <span class="stat-label">Đã đánh giá</span>
          </div>
        </div>
      </div>

      <!-- ===== TABS ===== -->
      <div class="tabs-bar">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          class="tab-btn"
          :class="{ active: activeTab === tab.value }"
          @click="activeTab = tab.value"
        >
          <svg v-if="tab.value === 'all'" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
          </svg>
          <svg v-else-if="tab.value === 'success'" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ tab.label }}
          <span class="tab-count">{{ tab.count }}</span>
        </button>

        <!-- Search -->
        <div class="search-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="search-icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            class="search-input"
            placeholder="Tìm theo loại sự cố, địa chỉ..."
          />
          <button v-if="searchQuery" class="search-clear" @click="searchQuery = ''">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- ===== LOADING ===== -->
      <div v-if="loading" class="cards-list">
        <div v-for="n in 4" :key="n" class="skeleton-card">
          <div class="skeleton-left"></div>
          <div class="skeleton-right">
            <div class="skeleton-line w-50"></div>
            <div class="skeleton-line w-70"></div>
            <div class="skeleton-line w-40"></div>
            <div class="skeleton-line w-80"></div>
          </div>
        </div>
      </div>

      <!-- ===== REQUEST LIST ===== -->
      <div v-else-if="filteredList.length > 0" class="cards-list">
        <article
          v-for="item in filteredList"
          :key="item.id"
          class="request-card"
          :class="{
            'card-success': item.trang_thai_goc !== 'THAT_BAI',
            'card-failed': item.trang_thai_goc === 'THAT_BAI',
            'has-rating': item.danh_gia
          }"
          @click="showDetailModal(item)"
          role="button"
          tabindex="0"
          @keydown.enter="showDetailModal(item)"
        >
          <!-- Left accent bar -->
          <div class="card-accent" :class="item.trang_thai_goc === 'THAT_BAI' ? 'accent-failed' : 'accent-success'"></div>

          <!-- Card image -->
          <div class="card-thumb">
            <img v-if="item.anh_hien_truong" :src="item.anh_hien_truong" :alt="`Hiện trường ${item.loai}`" class="thumb-img" loading="lazy" />
            <div v-else class="thumb-placeholder" :style="{ background: getTypeBg(item.iconColor) }">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :style="{ color: getTypeColor(item.iconColor) }">
                <path stroke-linecap="round" stroke-linejoin="round" :d="getIconPath(item.icon)" />
              </svg>
            </div>
          </div>

          <!-- Card info -->
          <div class="card-info">
            <div class="card-top-row">
              <div class="card-id-loai">
                <span class="request-id">SOS-{{ String(item.id).padStart(4, '0') }}</span>
                <span class="type-chip" :style="{ color: getTypeColor(item.iconColor), background: getTypeBg(item.iconColor) }">
                  <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="getIconPath(item.icon)" />
                  </svg>
                  {{ item.loai }}
                </span>
              </div>
              <div class="status-badge" :class="item.trang_thai_goc === 'THAT_BAI' ? 'badge-failed' : 'badge-success'">
                <svg v-if="item.trang_thai_goc === 'THAT_BAI'" xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                {{ item.trang_thai_goc === 'THAT_BAI' ? 'Thất bại' : 'Hoàn thành' }}
              </div>
              <div v-if="item.danh_gia" class="rated-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                Đã đánh giá
              </div>
            </div>

            <div class="card-address-row">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="address-icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
              </svg>
              <span>{{ item.address }}</span>
            </div>

            <div class="card-time-row">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ item.time }}</span>
              <span v-if="item.ten_doi_cuu_ho" class="team-tag">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                {{ item.ten_doi_cuu_ho }}
              </span>
            </div>

            <!-- Rating row -->
            <div v-if="item.danh_gia" class="card-rating-row">
              <div class="inline-stars">
                <svg v-for="n in 5" :key="n" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" :fill="n <= item.danh_gia ? '#f59e0b' : 'none'" stroke="#f59e0b" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                <span class="rating-text">{{ item.danh_gia }}/5</span>
              </div>
            </div>
          </div>

          <!-- Card actions -->
          <div class="card-actions">
            <button class="btn-detail" @click.stop="showDetailModal(item)" title="Xem chi tiết">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Chi tiết
            </button>
            <button
              class="btn-rate"
              :class="{ 'btn-rated': item.danh_gia }"
              @click.stop="openRatingModal(item)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
              </svg>
              {{ item.danh_gia ? 'Đã đánh giá' : 'Đánh giá' }}
            </button>
          </div>
        </article>
      </div>

      <!-- ===== EMPTY STATE ===== -->
      <div v-else-if="danhsach.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
          </svg>
        </div>
        <h3 class="empty-title">Chưa có yêu cầu cứu hộ nào</h3>
        <p class="empty-desc">Các yêu cầu hoàn thành hoặc thất bại sẽ hiển thị tại đây</p>
        <button class="btn-primary-action" @click="$router.push('/')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Gửi yêu cầu cứu hộ
        </button>
      </div>

      <!-- ===== NO FILTER RESULTS ===== -->
      <div v-else class="empty-state">
        <div class="empty-icon empty-icon-search">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
        </div>
        <h3 class="empty-title">Không tìm thấy kết quả</h3>
        <p class="empty-desc">Thử thay đổi từ khóa tìm kiếm hoặc chọn tab khác</p>
        <button class="btn-secondary-action" @click="searchQuery = ''; activeTab = 'all'">
          Xóa bộ lọc
        </button>
      </div>

    </div>

    <!-- ===== DETAIL MODAL ===== -->
    <Teleport to="body">
      <Transition name="modal-slide">
        <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
          <div class="detail-modal" role="dialog" aria-modal="true">

            <!-- Modal Header -->
            <div class="modal-header">
              <div class="modal-header-left">
                <span class="modal-request-id">SOS-{{ String(selectedItem?.id).padStart(4, '0') }}</span>
                <div class="modal-status-badge" :class="selectedItem?.trang_thai_goc === 'THAT_BAI' ? 'badge-failed' : 'badge-success'">
                  <svg v-if="selectedItem?.trang_thai_goc === 'THAT_BAI'" xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                  </svg>
                  {{ selectedItem?.trang_thai_goc === 'THAT_BAI' ? 'Thất bại' : 'Hoàn thành' }}
                </div>
              </div>
              <button class="modal-close" @click="closeModal" aria-label="Đóng">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Modal Image -->
            <div v-if="selectedItem?.anh_hien_truong" class="modal-image">
              <img :src="selectedItem.anh_hien_truong" :alt="`Hiện trường ${selectedItem?.loai}`" class="modal-img" />
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

              <!-- Section 1: Thông tin yêu cầu -->
              <div class="info-section">
                <div class="section-title">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                  </svg>
                  Thông tin yêu cầu
                </div>
                <div class="info-grid">
                  <div class="info-item">
                    <span class="info-label">Loại sự cố</span>
                    <span class="info-value type-value" :style="{ color: getTypeColor(selectedItem?.iconColor) }">{{ selectedItem?.loai }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Mức độ khẩn cấp</span>
                    <span class="info-value">
                      <span class="priority-badge" :class="`priority-${selectedItem?.muc_do_khan_cap?.toLowerCase() || 'medium'}`">
                        {{ selectedItem?.muc_do_khan_cap || 'Không rõ' }}
                      </span>
                    </span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Thời gian gửi</span>
                    <span class="info-value">{{ selectedItem?.thoi_gian_gui || selectedItem?.time || 'Không rõ' }}</span>
                  </div>
                  <div class="info-item" v-if="selectedItem?.thoi_gian_tiep_nhan">
                    <span class="info-label">Thời gian tiếp nhận</span>
                    <span class="info-value">{{ selectedItem?.thoi_gian_tiep_nhan }}</span>
                  </div>
                  <div class="info-item" v-if="selectedItem?.thoi_gian_ket_thuc">
                    <span class="info-label">{{ selectedItem?.trang_thai_goc === 'THAT_BAI' ? 'Thời gian kết thúc' : 'Thời gian hoàn thành' }}</span>
                    <span class="info-value">{{ selectedItem?.thoi_gian_ket_thuc }}</span>
                  </div>
                  <div class="info-item" v-if="selectedItem?.trang_thai_nhiem_vu">
                    <span class="info-label">Trạng thái nhiệm vụ</span>
                    <span class="info-value">
                      <span class="mission-badge" :class="`mission-${normalizeMissionStatus(selectedItem?.trang_thai_nhiem_vu)}`">
                        {{ formatMissionStatus(selectedItem?.trang_thai_nhiem_vu) }}
                      </span>
                    </span>
                  </div>
                  <div class="info-item full-width">
                    <span class="info-label">Địa chỉ</span>
                    <span class="info-value">{{ selectedItem?.address || 'Không rõ' }}</span>
                  </div>
                  <div class="info-item full-width" v-if="selectedItem?.moTa">
                    <span class="info-label">Mô tả</span>
                    <span class="info-value">{{ selectedItem?.moTa }}</span>
                  </div>
                </div>
              </div>

              <!-- Section 2: Thông tin điều phối -->
              <div class="info-section" v-if="selectedItem?.ten_doi_cuu_ho || selectedItem?.ten_nguoi_tiep_nhan || selectedItem?.sdt_nguoi_tiep_nhan">
                <div class="section-title">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                  </svg>
                  Thông tin điều phối
                </div>
                <div class="info-grid">
                  <div class="info-item" v-if="selectedItem?.ten_doi_cuu_ho">
                    <span class="info-label">Đội cứu hộ</span>
                    <span class="info-value">{{ selectedItem?.ten_doi_cuu_ho }}</span>
                  </div>
                  <div class="info-item" v-if="selectedItem?.sdt_doi_cuu_ho">
                    <span class="info-label">SĐT đội cứu hộ</span>
                    <span class="info-value">
                      <a :href="`tel:${selectedItem?.sdt_doi_cuu_ho}`" class="phone-link">{{ selectedItem?.sdt_doi_cuu_ho }}</a>
                    </span>
                  </div>
                  <div class="info-item" v-if="selectedItem?.ten_nguoi_tiep_nhan">
                    <span class="info-label">Người tiếp nhận</span>
                    <span class="info-value">{{ selectedItem?.ten_nguoi_tiep_nhan }}</span>
                  </div>
                  <div class="info-item" v-if="selectedItem?.sdt_nguoi_tiep_nhan">
                    <span class="info-label">SĐT người tiếp nhận</span>
                    <span class="info-value">
                      <a :href="`tel:${selectedItem?.sdt_nguoi_tiep_nhan}`" class="phone-link">{{ selectedItem?.sdt_nguoi_tiep_nhan }}</a>
                    </span>
                  </div>
                  <div class="info-item" v-if="selectedItem?.vai_tro_nguoi_tiep_nhan">
                    <span class="info-label">Vai trò</span>
                    <span class="info-value">{{ selectedItem?.vai_tro_nguoi_tiep_nhan }}</span>
                  </div>
                </div>
              </div>

              <!-- Section 3: Kết quả cứu hộ -->
              <div class="info-section" v-if="selectedItem?.ketQua || selectedItem?.bao_cao_hien_truong">
                <div class="section-title">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                  </svg>
                  {{ selectedItem?.trang_thai_goc === 'THAT_BAI' ? 'Lý do thất bại' : 'Kết quả cứu hộ' }}
                </div>
                <div class="result-box" :class="selectedItem?.trang_thai_goc === 'THAT_BAI' ? 'result-failed' : 'result-success'">
                  <p>{{ selectedItem?.ketQua || selectedItem?.bao_cao_hien_truong || 'Không có thông tin' }}</p>
                </div>
              </div>

              <!-- Section 4: Đánh giá -->
              <div class="info-section" v-if="selectedItem?.danh_gia">
                <div class="section-title">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                  </svg>
                  Đánh giá của bạn
                </div>
                <div class="rating-display">
                  <div class="rating-stars">
                    <svg v-for="n in 5" :key="n" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" :fill="n <= selectedItem.danh_gia ? '#f59e0b' : 'none'" stroke="#f59e0b" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                    <span class="rating-label">{{ selectedItem.danh_gia }}/5</span>
                  </div>
                  <p v-if="selectedItem?.nhan_xet" class="rating-comment">{{ selectedItem.nhan_xet }}</p>
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
              <button class="btn-close-modal" @click="closeModal">Đóng</button>
              <button
                class="btn-rate-modal"
                @click="goToReview"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                {{ selectedItem?.danh_gia ? 'Chỉnh sửa đánh giá' : 'Đánh giá ngay' }}
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
            <div class="rating-header">
              <div class="rating-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
              </div>
              <div>
                <h3 class="rating-title">Đánh giá dịch vụ cứu hộ</h3>
                <p class="rating-subtitle">SOS-{{ ratingItem?.id }} &middot; {{ ratingItem?.loai }}</p>
              </div>
              <button class="rating-close-btn" @click="closeRatingModal" aria-label="Đóng">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

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
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                  </svg>
                  {{ tag.label }}
                </button>
              </div>
            </div>

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
  "lũ":         { icon: "M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z", color: "text-info" },
  "lũ lụt":     { icon: "M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z", color: "text-info" },
  "sóng thần": { icon: "M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z", color: "text-info" },
  "hạn hán":   { icon: "M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z", color: "text-warning" },
  "tai nạn":   { icon: "M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z", color: "text-warning" },
  "giao thông":{ icon: "M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12", color: "text-secondary" },
  "động đất": { icon: "M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z", color: "text-danger" },
};

const DEFAULT_ICON = "M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z";

const TYPE_COLORS = {
  "y tế":        { color: "#dc2626", bg: "rgba(220,38,38,0.08)" },
  "cháy nổ":    { color: "#dc2626", bg: "rgba(220,38,38,0.08)" },
  "cháy":       { color: "#dc2626", bg: "rgba(220,38,38,0.08)" },
  "lũ":         { color: "#0284c7", bg: "rgba(2,132,199,0.08)" },
  "lũ lụt":     { color: "#0284c7", bg: "rgba(2,132,199,0.08)" },
  "sóng thần": { color: "#0284c7", bg: "rgba(2,132,199,0.08)" },
  "hạn hán":   { color: "#d97706", bg: "rgba(217,119,6,0.08)" },
  "tai nạn":   { color: "#d97706", bg: "rgba(217,119,6,0.08)" },
  "giao thông":{ color: "#64748b", bg: "rgba(100,116,139,0.08)" },
  "động đất":  { color: "#dc2626", bg: "rgba(220,38,38,0.08)" },
};

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
      activeTab: "all",
      isModalOpen: false,
      selectedItem: null,
      isRatingModalOpen: false,
      ratingItem: null,
      ratingSelected: 0,
      ratingHoveredStar: 0,
      ratingSelectedTags: [],
      ratingComment: "",
      ratingSubmitting: false,
      realtimeChannel: null,
      realtimeUserId: null,
      realtimeConnected: false,
      allRatingTags: [
        { label: "Phản hồi nhanh" },
        { label: "Thái độ tốt" },
        { label: "Chuyên nghiệp" },
        { label: "Trang thiết bị đầy đủ" },
        { label: "Xử lý hiệu quả" },
        { label: "Thông tin rõ ràng" },
        { label: "Hỗ trợ tận tâm" },
        { label: "An toàn" },
      ],
    };
  },
  computed: {
    tabs() {
      return [
        { value: "all", label: "Tất cả", count: this.danhsach.length },
        { value: "success", label: "Hoàn thành", count: this.successCount },
        { value: "failed", label: "Thất bại", count: this.failedCount },
      ];
    },
    filteredList() {
      let result = [...this.danhsach];

      if (this.activeTab === "success") {
        result = result.filter((item) => item.trang_thai_goc !== "THAT_BAI");
      } else if (this.activeTab === "failed") {
        result = result.filter((item) => item.trang_thai_goc === "THAT_BAI");
      }

      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase().trim();
        result = result.filter((item) => {
          const loai = (item.loai || "").toLowerCase();
          const address = (item.address || "").toLowerCase();
          const moTa = (item.moTa || "").toLowerCase();
          return loai.includes(query) || address.includes(query) || moTa.includes(query);
        });
      }

      return result;
    },
    successCount() {
      return this.danhsach.filter((item) => item.trang_thai_goc !== "THAT_BAI").length;
    },
    failedCount() {
      return this.danhsach.filter((item) => item.trang_thai_goc === "THAT_BAI").length;
    },
    ratedCount() {
      return this.danhsach.filter((item) => item.danh_gia && item.danh_gia > 0).length;
    },
    ratingFeedbackLabel() {
      const labels = { 1: "Rất không hài lòng", 2: "Không hài lòng", 3: "Bình thường", 4: "Hài lòng", 5: "Rất hài lòng" };
      return labels[this.ratingSelected] || "";
    },
  },
  async created() {
    await this.loadHistory();
    this.subscribeToReverb();
  },
  beforeUnmount() {
    this.unsubscribeFromReverb();
  },
  methods: {
    getIconPath(icon) {
      return icon || DEFAULT_ICON;
    },

    getTypeColor(colorClass) {
      const map = {
        "text-danger": "#dc2626",
        "text-info": "#0284c7",
        "text-warning": "#d97706",
        "text-secondary": "#64748b",
      };
      return map[colorClass] || "#64748b";
    },
    getTypeBg(colorClass) {
      const map = {
        "text-danger": "rgba(220,38,38,0.08)",
        "text-info": "rgba(2,132,199,0.08)",
        "text-warning": "rgba(217,119,6,0.08)",
        "text-secondary": "rgba(100,116,139,0.08)",
      };
      return map[colorClass] || "rgba(100,116,139,0.08)";
    },

    normalizeMissionStatus(status) {
      if (!status) return "default";
      const s = status.toUpperCase().replace(/\s+/g, "_");
      const map = { "MOI": "moi", "DANG_XU_LY": "dang_xu_ly", "DA_DEN_HIEN_TRUONG": "da_den_hien_truong", "HOAN_THANH": "hoan_thanh", "HUY_BO": "huy_bo" };
      return map[s] || "default";
    },

    formatMissionStatus(status) {
      if (!status) return "Không rõ";
      const map = { "MOI": "Mới tiếp nhận", "DANG_XU_LY": "Đang xử lý", "DA_DEN_HIEN_TRUONG": "Đã đến hiện trường", "HOAN_THANH": "Hoàn thành", "HUY_BO": "Hủy bỏ" };
      return map[status.toUpperCase()] || status;
    },

    hienToast(type, message) {
      if (this.$toast?.[type]) {
        this.$toast[type](message, { position: "top-right", duration: 3500 });
        return;
      }
      alert(message);
    },

    // ===== REAL-TIME (Reverb / Laravel Echo) =====
    subscribeToReverb() {
      const userId = getCurrentUserId();
      if (!userId) return;
      this.realtimeUserId = userId;

      // If Echo is already connected, subscribe immediately
      const conn = window.Echo?.connector?.pusher?.connection;
      if (conn?.state === "connected") {
        this.connectReverbChannel(userId);
      } else {
        // Wait for the connection event
        const handler = (e) => {
          if (e.detail.status === "connected") {
            this.connectReverbChannel(this.realtimeUserId);
            window.removeEventListener("realtime-connection-change", handler);
          }
        };
        window.addEventListener("realtime-connection-change", handler);
      }
    },

    connectReverbChannel(userId) {
      if (!userId || this.realtimeChannel) return;
      this.realtimeConnected = true;

      // Listen on the user-specific channel
      this.realtimeChannel = window.Echo.channel(`rescue-requests.${userId}`);
      this.realtimeChannel.listen("RescueRequestUpdated", (payload) => {
        this.handleReverbEvent(payload);
      });
    },

    handleReverbEvent(payload) {
      const data = payload?.data || payload;
      const requestId = data.id || data.id_yeu_cau;
      if (!requestId) return;

      const status = (data.trang_thai || "").toUpperCase();
      const isTerminal = status === "HOAN_THANH" || status === "DA_HOAN_THANH" || status === "THAT_BAI";
      const userId = this.realtimeUserId;

      // Case 1: Yêu cầu của user vừa hoàn thành/thất bại -> thêm vào danh sách
      if (isTerminal && data.id_nguoi_dung === userId) {
        const exists = this.danhsach.some((item) => item.id === requestId);
        if (!exists) {
          this.hienToast("success", `Yêu cầu SOS-${String(requestId).padStart(4, "0")} đã được xử lý.`);
          this.fetchAndPrepend(requestId);
        }
      }

      // Case 2: Yêu cầu đang xử lý trong danh sách bị cập nhật trạng thái hoàn thành/thất bại
      const idx = this.danhsach.findIndex((item) => item.id === requestId);
      if (idx > -1) {
        if (isTerminal) {
          // Refresh to get full data including ket_qua, thanh_vien, etc.
          this.fetchAndRefresh(requestId);
        } else {
          // Update in place (e.g. assigned to team, arrived, etc.)
          const existing = this.danhsach[idx];
          if (data.ten_doi && !existing.ten_doi_cuu_ho) {
            this.danhsach[idx] = {
              ...existing,
              ten_doi_cuu_ho: data.ten_doi,
              trang_thai_goc: status,
            };
          } else {
            this.danhsach[idx] = { ...existing, trang_thai_goc: status };
          }
        }
      }
    },

    async fetchAndPrepend(requestId) {
      try {
        const response = await rescueRequestAPI.getDetail(requestId);
        const raw = response?.data?.data || response?.data || response;
        const userId = this.realtimeUserId;
        if (raw.id_nguoi_dung !== userId && raw.nguoi_dung?.id !== userId) return;
        const normalized = this.normalizeResults([raw]);
        if (normalized.length > 0) {
          this.danhsach.unshift(normalized[0]);
        }
      } catch (e) {
        console.warn("[History] Could not fetch new request:", e);
      }
    },

    async fetchAndRefresh(requestId) {
      try {
        const response = await rescueRequestAPI.getDetail(requestId);
        const raw = response?.data?.data || response?.data || response;
        const normalized = this.normalizeResults([raw]);
        if (normalized.length > 0) {
          const idx = this.danhsach.findIndex((item) => item.id === requestId);
          if (idx > -1) {
            this.danhsach.splice(idx, 1, normalized[0]);
          }
        }
      } catch (e) {
        console.warn("[History] Could not refresh request:", e);
      }
    },

    unsubscribeFromReverb() {
      if (this.realtimeChannel) {
        this.realtimeChannel.stopListening("RescueRequestUpdated");
        if (this.realtimeUserId) {
          window.Echo?.leave(`rescue-requests.${this.realtimeUserId}`);
        }
        this.realtimeChannel = null;
        this.realtimeConnected = false;
      }
    },

    normalizeResults(items) {
      if (!Array.isArray(items)) return [];
      return items.map((item) => {
        const id = item.id_yeu_cau || item.id || item.ma_ket_qua || item.result_id || "";
        const trangThaiGoc = (item.trang_thai || "").toUpperCase();

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
        // Handle both snake_case (Laravel default) and camelCase response formats
        const rawDanhGias = item.danh_gias || item.danhGias || [];
        if (rawDanhGias.length > 0) {
          danhGia = rawDanhGias[0].diem_danh_gia || rawDanhGias[0].so_sao || rawDanhGias[0].danh_gia || null;
          nhanXet = rawDanhGias[0].noi_dung_danh_gia || rawDanhGias[0].nhan_xet || rawDanhGias[0].feedback || "";
        }
        danhGia = danhGia || item.diem_danh_gia || item.danh_gia || item.danh_gia_sao || item.rating || null;

        let anhHienTruong = null;
        if (item.hinh_anh) anhHienTruong = getImageUrl(item.hinh_anh);
        else if (item.anh_hien_truong) anhHienTruong = getImageUrl(item.anh_hien_truong);
        else if (item.anh) anhHienTruong = getImageUrl(item.anh);
        else if (item.image) anhHienTruong = getImageUrl(item.image);
        else if (item.hinhAnhUrl) anhHienTruong = getImageUrl(item.hinhAnhUrl);

        // Lay thong tin phan cong & doi cuu ho
        let tenDoiCuuHo = "";
        let sdtDoiCuuHo = "";
        let tenNguoiTiepNhan = "";
        let sdtNguoiTiepNhan = "";
        let vaiTroNguoiTiepNhan = "";
        let thoiGianTiepNhan = "";
        let trangThaiNhiemVu = "";
        let baoCaoHienTruong = "";
        let thoiGianKetThucFull = "";

        if (item.phanCongs && item.phanCongs.length > 0) {
          const pc = item.phanCongs[0];
          if (pc.doiCuuHo) {
            tenDoiCuuHo = pc.doiCuuHo.ten_doi || pc.doiCuuHo.ten || "";
            sdtDoiCuuHo = pc.doiCuuHo.so_dien_thoai_hotline || pc.doiCuuHo.sdt || "";
          }
          if (pc.thanhVienTiepNhan || pc.thanh_vien_tiep_nhan) {
            const tv = pc.thanhVienTiepNhan || pc.thanh_vien_tiep_nhan;
            tenNguoiTiepNhan = tv.ho_ten || tv.hoTen || tv.ten || "";
            sdtNguoiTiepNhan = tv.so_dien_thoai || tv.sdt || "";
            vaiTroNguoiTiepNhan = tv.vai_tro_trong_doi || tv.vai_tro || "";
          }
          if (pc.ketQua) {
            baoCaoHienTruong = pc.ketQua.bao_cao_hien_truong || "";
            if (pc.ketQua.thoi_gian_ket_thuc) {
              thoiGianKetThucFull = formatTime(pc.ketQua.thoi_gian_ket_thuc);
            }
          }
          if (pc.thoi_gian_tiep_nhan) {
            thoiGianTiepNhan = formatTime(pc.thoi_gian_tiep_nhan);
          }
          trangThaiNhiemVu = pc.trang_thai_nhiem_vu || "";
        }

        // Lay thoi_gian_ket_thuc tu cac nguon khac neu chua co tu ketQua
        if (!thoiGianKetThucFull) {
          thoiGianKetThucFull = formatTime(item.thoi_gian_ket_thuc || item.thoi_gian_hoan_thanh || item.ngay_hoan_thanh);
        }

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
          anh_hien_truong: anhHienTruong,
          trang_thai: item.trang_thai,
          trang_thai_goc: trangThaiGoc,
          ketQua: item.ket_qua || item.ketQua || item.ket_qua_cuu_ho || "",
          muc_do_khan_cap: item.muc_do_khan_cap || "",
          thoi_gian_gui: formatTime(item.thoi_gian_gui || item.created_at || item.thoi_gian),
          thoi_gian_ket_thuc: thoiGianKetThucFull,
          thoi_gian_tiep_nhan: thoiGianTiepNhan,
          trang_thai_nhiem_vu: trangThaiNhiemVu,
          ten_doi_cuu_ho: tenDoiCuuHo,
          sdt_doi_cuu_ho: sdtDoiCuuHo,
          ten_nguoi_tiep_nhan: tenNguoiTiepNhan,
          sdt_nguoi_tiep_nhan: sdtNguoiTiepNhan,
          vai_tro_nguoi_tiep_nhan: vaiTroNguoiTiepNhan,
          bao_cao_hien_truong: baoCaoHienTruong,
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
          const isCompleted = trangThai === "HOAN_THANH" || trangThai === "DA_HOAN_THANH" || trangThai === "THAT_BAI";
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

    async showDetailModal(item) {
      this.selectedItem = item;
      this.isModalOpen = true;
      document.body.style.overflow = "hidden";

      // Fetch fresh detailed data for the modal (includes full phanCongs, ketQua, thanhVien, danhGias)
      try {
        const response = await rescueRequestAPI.getDetail(item.id);
        const raw = response?.data?.data || response?.data || response;
        const normalized = this.normalizeResults([raw]);
        if (normalized.length > 0) {
          // Update both selectedItem and the card in danhsach with fresh data (especially danh_gia)
          const freshData = normalized[0];
          this.selectedItem = { ...this.selectedItem, ...freshData };
          const idx = this.danhsach.findIndex((d) => d.id === item.id);
          if (idx > -1) {
            this.danhsach.splice(idx, 1, { ...this.danhsach[idx], ...freshData });
          }
        }
      } catch (e) {
        console.warn("[History] Could not fetch detail for modal:", e);
      }
    },

    closeModal() {
      this.isModalOpen = false;
      this.selectedItem = null;
      document.body.style.overflow = "";
    },

    openRatingModal(item) {
      this.ratingItem = item;
      this.ratingSelected = 0;
      this.ratingHoveredStar = 0;
      this.ratingSelectedTags = [];
      this.ratingComment = "";
      this.ratingSubmitting = false;

      // Fetch fresh data from API so we have the complete rating info (sao, nhan_xet, tags)
      this.fetchRatingInfoForModal(item.id);
    },

    async fetchRatingInfoForModal(requestId) {
      try {
        const response = await rescueRequestAPI.getDetail(requestId);
        const raw = response?.data?.data || response?.data || response;

        // Extract fresh rating info from API response
        let freshRating = null;
        let freshNhanXet = "";
        let freshTags = "";

        // Handle both snake_case (Laravel) and camelCase response formats
        const rawDanhGias = raw.danh_gias || raw.danhGias || [];
        if (rawDanhGias.length > 0) {
          const dg = rawDanhGias[0];
          freshRating = dg.diem_danh_gia || dg.so_sao || dg.danh_gia || null;
          freshNhanXet = dg.noi_dung_danh_gia || dg.nhan_xet || dg.feedback || "";
          freshTags = dg.tags || dg.the || "";
        }
        freshRating = freshRating || raw.diem_danh_gia || raw.danh_gia || raw.danh_gia_sao || raw.rating || null;

        // Update ratingItem with fresh data
        if (freshRating !== null) {
          this.ratingItem = { ...this.ratingItem, danh_gia: freshRating, nhan_xet: freshNhanXet };
          this.ratingSelected = freshRating;
          this.ratingComment = freshNhanXet;

          // Parse tags from stored string
          if (freshTags) {
            this.ratingSelectedTags = freshTags.split(",").map((t) => t.trim()).filter(Boolean);
          }
        }

        // Also update danhsach item if present
        const idx = this.danhsach.findIndex((d) => d.id === requestId);
        if (idx > -1) {
          this.danhsach[idx].danh_gia = freshRating;
          this.danhsach[idx].nhan_xet = freshNhanXet;
        }
      } catch (e) {
        console.warn("[History] Could not fetch fresh rating info:", e);
      } finally {
        this.isRatingModalOpen = true;
        document.body.style.overflow = "hidden";
      }
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
        const currentUserId = getCurrentUserId();
        if (!currentUserId) {
          if (this.$toast?.error) {
            this.$toast.error("Vui lòng đăng nhập để đánh giá.", { position: "top-right", duration: 3500 });
          }
          this.ratingSubmitting = false;
          return;
        }
        await rescueRequestAPI.submitRating(this.ratingItem.id, {
          id_nguoi_dung: currentUserId,
          diem_danh_gia: this.ratingSelected,
          noi_dung_danh_gia: this.ratingComment,
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
/* ===== PAGE ===== */
.history-page {
  background: #f1f5f9;
  min-height: 100vh;
  padding: 2rem 1.5rem;
}

.history-container {
  max-width: 900px;
  margin: 0 auto;
}

/* ===== TRANSITIONS ===== */
.modal-slide-enter-active,
.modal-slide-leave-active {
  transition: opacity 0.25s ease;
}
.modal-slide-enter-from,
.modal-slide-leave-to {
  opacity: 0;
}
.modal-slide-enter-from .detail-modal {
  transform: translateY(24px);
}
.modal-slide-leave-to .detail-modal {
  transform: translateY(24px);
}
.modal-slide-enter-active .detail-modal,
.modal-slide-leave-active .detail-modal {
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-from .rating-modal,
.modal-fade-leave-to .rating-modal {
  transform: scale(0.95) translateY(8px);
}

.label-fade-enter-active,
.label-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.label-fade-enter-from,
.label-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

/* ===== PAGE HEADER ===== */
.page-header {
  margin-bottom: 1.5rem;
}

.title-row {
  display: flex;
  align-items: center;
  gap: 14px;
}

.title-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: linear-gradient(135deg, #0f172a, #1e293b);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #22c55e;
  flex-shrink: 0;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.15);
}

.page-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 2px;
  letter-spacing: -0.02em;
}

.page-subtitle {
  font-size: 0.8rem;
  color: #64748b;
  margin: 0;
  font-weight: 500;
}

/* ===== STATS GRID ===== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: #ffffff;
  border-radius: 14px;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid #e2e8f0;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  transform: translateY(-1px);
}

.stat-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-total .stat-icon { background: #f0f9ff; color: #0f172a; }
.stat-success .stat-icon { background: #f0fdf4; color: #16a34a; }
.stat-failed .stat-icon { background: #fef2f2; color: #dc2626; }
.stat-rated .stat-icon { background: #fffbeb; color: #d97706; }

.stat-content {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.stat-number {
  font-size: 1.25rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1;
}

.stat-label {
  font-size: 0.7rem;
  color: #64748b;
  font-weight: 500;
}

/* ===== TABS ===== */
.tabs-bar {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: #ffffff;
  font-size: 0.8rem;
  font-weight: 600;
  font-family: inherit;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s ease;
}

.tab-btn:hover {
  border-color: #cbd5e1;
  color: #0f172a;
  background: #f8fafc;
}

.tab-btn.active {
  background: #0f172a;
  border-color: #0f172a;
  color: #ffffff;
}

.tab-btn.active .tab-count {
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
}

.tab-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 5px;
  border-radius: 20px;
  background: #f1f5f9;
  color: #64748b;
  font-size: 0.7rem;
  font-weight: 700;
}

/* ===== SEARCH ===== */
.search-wrap {
  position: relative;
  margin-left: auto;
  flex: 0 0 240px;
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
  padding: 8px 34px 8px 34px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.825rem;
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
  border-color: #0f172a;
  box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.06);
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
  padding: 3px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  transition: color 0.15s ease;
}

.search-clear:hover {
  color: #475569;
}

/* ===== SKELETON ===== */
.cards-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.skeleton-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #f1f5f9;
  padding: 16px;
  display: flex;
  gap: 14px;
}

.skeleton-left {
  width: 72px;
  height: 72px;
  border-radius: 10px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  flex-shrink: 0;
}

.skeleton-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 4px;
}

.skeleton-line {
  height: 11px;
  border-radius: 6px;
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
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 0;
  overflow: hidden;
  cursor: pointer;
  transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
  position: relative;
}

.request-card:hover {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
  transform: translateY(-1px);
  border-color: #cbd5e1;
}

.request-card:focus-visible {
  outline: 2px solid #0f172a;
  outline-offset: 2px;
}

.card-accent {
  width: 4px;
  align-self: stretch;
  flex-shrink: 0;
}

.accent-success { background: linear-gradient(180deg, #22c55e, #16a34a); }
.accent-failed { background: linear-gradient(180deg, #dc2626, #b91c1c); }

.card-thumb {
  width: 72px;
  height: 72px;
  flex-shrink: 0;
  overflow: hidden;
}

.thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumb-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-info {
  flex: 1;
  padding: 14px 16px;
  min-width: 0;
}

.card-top-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 6px;
  flex-wrap: wrap;
}

.card-id-loai {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.request-id {
  font-size: 0.68rem;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.type-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 9px;
  border-radius: 20px;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  flex-shrink: 0;
}

.badge-success {
  background: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
}

.badge-failed {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.card-address-row {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.8rem;
  color: #475569;
  margin-bottom: 4px;
  overflow: hidden;
}

.card-address-row span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.address-icon {
  color: #dc2626;
  flex-shrink: 0;
}

.card-time-row {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.75rem;
  color: #94a3b8;
  flex-wrap: wrap;
}

.team-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 1px 7px;
  background: #f1f5f9;
  border-radius: 20px;
  font-size: 0.68rem;
  font-weight: 600;
  color: #475569;
}

.card-rating-row {
  margin-top: 6px;
}

.inline-stars {
  display: flex;
  align-items: center;
  gap: 1px;
}

.rating-text {
  font-size: 0.72rem;
  font-weight: 600;
  color: #f59e0b;
  margin-left: 4px;
}

.card-actions {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 14px 16px;
  flex-shrink: 0;
}

.btn-detail,
.btn-rate {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 7px 12px;
  border-radius: 8px;
  font-size: 0.78rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
  border: none;
}

.btn-detail {
  background: #0f172a;
  color: #ffffff;
}

.btn-detail:hover {
  background: #1e293b;
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(15, 23, 42, 0.2);
}

.btn-rate {
  background: #fffbeb;
  color: #b45309;
  border: 1px solid #fde68a;
}

.btn-rate:hover {
  background: #fef3c7;
  border-color: #f59e0b;
  transform: translateY(-1px);
}

.btn-rated {
  background: #f0fdf4;
  color: #15803d;
  border-color: #bbf7d0;
}

.btn-rated:hover {
  background: #dcfce7;
  border-color: #22c55e;
}

/* ===== EMPTY STATE ===== */
.empty-state {
  text-align: center;
  padding: 4rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.6rem;
}

.empty-icon {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  background: #f0fdf4;
  color: #22c55e;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
}

.empty-icon-search {
  background: #fffbeb;
  color: #d97706;
}

.empty-title {
  font-size: 1rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.empty-desc {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0;
  max-width: 340px;
}

.btn-primary-action {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 10px 20px;
  background: #0f172a;
  color: #ffffff;
  border: none;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 0.5rem;
}

.btn-primary-action:hover {
  background: #1e293b;
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
}

.btn-secondary-action {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 10px 20px;
  background: #ffffff;
  color: #475569;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.85rem;
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

/* ===== DETAIL MODAL ===== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 9999;
  padding: 0;
}

@media (min-width: 640px) {
  .modal-overlay {
    align-items: center;
    padding: 1rem;
  }
}

.detail-modal {
  background: #ffffff;
  width: 100%;
  max-width: 560px;
  max-height: 92vh;
  border-radius: 20px 20px 0 0;
  overflow: hidden;
  box-shadow: 0 -4px 40px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
}

@media (min-width: 640px) {
  .detail-modal {
    border-radius: 20px;
    max-height: 88vh;
  }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 20px 16px;
  border-bottom: 1px solid #f1f5f9;
  flex-shrink: 0;
}

.modal-header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.modal-request-id {
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.modal-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.modal-status-badge.badge-success {
  background: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
}

.modal-status-badge.badge-failed {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.rated-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 9px;
  border-radius: 20px;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  flex-shrink: 0;
  background: #fffbeb;
  color: #d97706;
  border: 1px solid #fde68a;
}

.modal-close {
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

.modal-close:hover {
  background: #e2e8f0;
  color: #0f172a;
}

.modal-close:focus-visible {
  outline: 2px solid #0f172a;
  outline-offset: 2px;
}

.modal-image {
  max-height: 220px;
  overflow: hidden;
  flex-shrink: 0;
}

.modal-img {
  width: 100%;
  height: 220px;
  object-fit: cover;
}

.modal-body {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 0.72rem;
  font-weight: 700;
  color: #0f172a;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  padding-bottom: 8px;
  border-bottom: 1px solid #f1f5f9;
}

.section-title svg {
  color: #64748b;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.info-item.full-width {
  grid-column: 1 / -1;
}

.info-label {
  font-size: 0.68rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.info-value {
  font-size: 0.875rem;
  color: #0f172a;
  font-weight: 500;
  line-height: 1.4;
}

.type-value {
  font-weight: 700;
}

.phone-link {
  color: #0f172a;
  text-decoration: none;
  font-weight: 600;
  border-bottom: 1px dashed #94a3b8;
  transition: color 0.15s ease, border-color 0.15s ease;
}

.phone-link:hover {
  color: #0284c7;
  border-color: #0284c7;
}

.priority-badge {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.priority-high { background: #fef2f2; color: #dc2626; }
.priority-medium { background: #fffbeb; color: #d97706; }
.priority-low { background: #f0fdf4; color: #16a34a; }

.mission-badge {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.mission-moi { background: #eff6ff; color: #2563eb; }
.mission-dang_xu_ly { background: #fffbeb; color: #d97706; }
.mission-da_den_hien_truong { background: #f0fdf4; color: #16a34a; }
.mission-hoan_thanh { background: #f0fdf4; color: #16a34a; }
.mission-huy_bo { background: #fef2f2; color: #dc2626; }
.mission-default { background: #f1f5f9; color: #64748b; }

.result-box {
  padding: 14px 16px;
  border-radius: 10px;
  font-size: 0.875rem;
  line-height: 1.6;
  color: #0f172a;
}

.result-success {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
}

.result-failed {
  background: #fef2f2;
  border: 1px solid #fecaca;
}

.rating-display {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.rating-stars {
  display: flex;
  align-items: center;
  gap: 2px;
}

.rating-label {
  font-size: 0.875rem;
  font-weight: 700;
  color: #f59e0b;
  margin-left: 6px;
}

.rating-comment {
  font-size: 0.85rem;
  color: #475569;
  font-style: italic;
  margin: 0;
  line-height: 1.5;
  padding: 10px 14px;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #f1f5f9;
}

.modal-footer {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 20px;
  border-top: 1px solid #f1f5f9;
  flex-shrink: 0;
}

.btn-close-modal {
  flex: 1;
  padding: 10px 18px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: #ffffff;
  font-size: 0.85rem;
  font-weight: 600;
  font-family: inherit;
  color: #475569;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-close-modal:hover {
  border-color: #94a3b8;
  color: #0f172a;
  background: #f8fafc;
}

.btn-rate-modal {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  padding: 10px 18px;
  border-radius: 10px;
  border: none;
  background: #0f172a;
  color: #ffffff;
  font-size: 0.85rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
}

.btn-rate-modal:hover {
  background: #1e293b;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(15, 23, 42, 0.3);
}

/* ===== RATING MODAL ===== */
.rating-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 99999;
  padding: 1rem;
}

.rating-modal {
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.18);
  overscroll-behavior: contain;
}

.rating-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 22px 22px 18px;
  border-bottom: 1px solid #f1f5f9;
}

.rating-header-icon {
  width: 44px;
  height: 44px;
  min-width: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0f172a, #1e293b);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #22c55e;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.2);
  flex-shrink: 0;
}

.rating-title { font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0; }
.rating-subtitle { font-size: 0.78rem; color: #64748b; margin: 2px 0 0; }

.rating-close-btn {
  margin-left: auto;
  background: #f1f5f9;
  border: none;
  border-radius: 10px;
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.rating-close-btn:hover { background: #e2e8f0; color: #0f172a; }
.rating-close-btn:focus-visible { outline: 2px solid #0f172a; outline-offset: 2px; }

.rating-stars-section,
.rating-tags-section,
.rating-comment-section {
  padding: 18px 22px 0;
}

.rating-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 10px;
  display: block;
}

.optional-label { font-weight: 400; text-transform: none; letter-spacing: 0; color: #94a3b8; }

.stars-row { display: flex; align-items: center; gap: 4px; margin-bottom: 8px; }

.star-btn {
  background: none;
  border: none;
  padding: 4px;
  cursor: pointer;
  color: #e2e8f0;
  transition: all 0.15s ease;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.star-btn:hover,
.star-btn.active { color: #f59e0b; transform: scale(1.12); }
.star-btn.selected { color: #f59e0b; }
.star-btn:focus-visible { outline: 2px solid #0f172a; outline-offset: 2px; }
.star-btn:active { transform: scale(0.9); }

.rating-feedback-text { font-size: 0.875rem; font-weight: 600; margin: 0 0 2px; }
.rating-1 { color: #dc2626; }
.rating-2 { color: #f97316; }
.rating-3 { color: #eab308; }
.rating-4 { color: #22c55e; }
.rating-5 { color: #10b981; }

.tags-grid { display: flex; flex-wrap: wrap; gap: 7px; }

.tag-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 5px 11px;
  border-radius: 20px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  font-size: 0.78rem;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.15s ease;
  font-family: inherit;
}

.tag-chip:hover { border-color: #0f172a; background: #f1f5f9; color: #0f172a; }
.tag-chip.selected { border-color: #0f172a; background: #0f172a; color: #ffffff; }
.tag-chip:focus-visible { outline: 2px solid #0f172a; outline-offset: 2px; }

.rating-textarea {
  width: 100%;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 10px 12px;
  font-size: 0.875rem;
  font-family: inherit;
  color: #0f172a;
  resize: vertical;
  min-height: 76px;
  background: #f8fafc;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  box-sizing: border-box;
}

.rating-textarea::placeholder { color: #94a3b8; }
.rating-textarea:focus { outline: none; border-color: #0f172a; box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.06); background: #fff; }
.rating-textarea:disabled { opacity: 0.6; cursor: not-allowed; }

.char-counter { text-align: right; font-size: 0.73rem; color: #94a3b8; margin-top: 3px; transition: color 0.2s ease; }
.char-counter.warning { color: #f59e0b; }

.rating-actions { display: flex; align-items: center; gap: 10px; padding: 18px 22px 22px; }

.btn-cancel {
  flex: 0 0 auto;
  padding: 9px 18px;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  background: #fff;
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
}

.btn-cancel:hover:not(:disabled) { border-color: #94a3b8; color: #0f172a; background: #f8fafc; }
.btn-cancel:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-submit {
  flex: 1;
  padding: 10px 18px;
  border-radius: 12px;
  border: none;
  background: #0f172a;
  color: #fff;
  font-size: 0.875rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-family: inherit;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
}

.btn-submit:hover:not(:disabled) { background: #1e293b; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(15, 23, 42, 0.35); }
.btn-submit:active:not(:disabled) { transform: translateY(0); }
.btn-submit:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }

.btn-spinner {
  width: 17px;
  height: 17px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@keyframes spin { to { transform: rotate(360deg); } }

.rating-modal::-webkit-scrollbar { width: 4px; }
.rating-modal::-webkit-scrollbar-track { background: transparent; }
.rating-modal::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

/* ===== RESPONSIVE ===== */
@media (max-width: 640px) {
  .history-page {
    padding: 1.25rem 1rem;
  }

  .page-title {
    font-size: 1.25rem;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .tabs-bar {
    gap: 4px;
  }

  .tab-btn {
    padding: 6px 10px;
    font-size: 0.75rem;
  }

  .search-wrap {
    flex: 0 0 100%;
    width: 100%;
    margin-left: 0;
    order: 10;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .detail-modal {
    max-height: 90vh;
  }
}

@media (max-width: 400px) {
  .card-thumb {
    width: 60px;
    height: 60px;
  }

  .card-actions {
    padding: 10px 12px;
  }

  .btn-detail,
  .btn-rate {
    padding: 6px 10px;
    font-size: 0.72rem;
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

@media (max-width: 480px) {
  .rating-modal {
    max-height: 85vh;
    border-radius: 20px 20px 0 0;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    max-width: 100%;
  }

  .rating-overlay {
    align-items: flex-end;
    padding: 0;
  }

  .rating-header,
  .rating-stars-section,
  .rating-tags-section,
  .rating-comment-section,
  .rating-actions {
    padding-left: 16px;
    padding-right: 16px;
  }
}
</style>
