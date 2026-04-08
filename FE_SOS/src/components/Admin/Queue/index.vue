<template>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0 fw-semibold">Hàng đợi yêu cầu theo mức ưu tiên</h5>
        <small class="text-muted">Danh sách chi tiết sẽ gắn API sau</small>
      </div>
    </div>
    <div class="card-body">
      <div class="alert alert-info small mb-0">
        Đây là trang danh sách để bạn:
        <ul class="mb-0">
          <li>Xem, lọc và sắp xếp các yêu cầu cứu hộ theo mức ưu tiên.</li>
          <li>Thao tác nhanh: gán đội cứu hộ, cập nhật trạng thái, xem chi tiết.</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminQueue",
};
</script>

