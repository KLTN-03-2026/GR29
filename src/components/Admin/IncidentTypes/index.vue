<template>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0 fw-semibold">Quản lý loại sự cố</h5>
        <small class="text-muted">Thêm / sửa / xóa loại sự cố</small>
      </div>
      <button class="btn btn-sm btn-primary">
        <i class="fa-solid fa-plus me-1"></i>Thêm loại sự cố
      </button>
    </div>
    <div class="card-body">
      <div class="alert alert-info small mb-0">
        Sẽ có:
        <ul>
          <li>Bảng danh sách loại sự cố (tên, mã, mô tả, icon, màu).</li>
          <li>Form popup/slide-over để thêm/sửa.</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminIncidentTypes",
};
</script>

