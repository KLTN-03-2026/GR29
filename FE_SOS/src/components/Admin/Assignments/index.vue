<template>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0 fw-semibold">Phân công đội cứu hộ cho yêu cầu</h5>
        <small class="text-muted">Bố cục UI theo style dashboard, sẽ gắn logic sau</small>
      </div>
    </div>
    <div class="card-body">
      <div class="alert alert-info small mb-0">
        Trang này sẽ cho phép:
        <ul class="mb-0">
          <li>Chọn yêu cầu từ hàng đợi.</li>
          <li>Chọn đội cứu hộ phù hợp (gần nhất, rảnh, đủ năng lực).</li>
          <li>Gửi lệnh phân công và theo dõi trạng thái.</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminAssignments",
};
</script>

