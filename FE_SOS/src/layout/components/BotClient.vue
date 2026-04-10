<template>
  <footer class="bot-client-footer bg-white border-top py-3 px-4">
    <div class="container-fluid d-flex flex-column flex-md-row justify-content-between align-items-center">
      <div class="small text-muted mb-2 mb-md-0">
        &copy; 2026 <strong>nowSOS</strong>. Hệ thống hỗ trợ khẩn cấp.
      </div>
      <div class="d-flex gap-3">
        <a href="#" class="text-decoration-none text-secondary fw-bold" style="font-size: 11px;">BẢO MẬT</a>
        <a href="#" class="text-decoration-none text-secondary fw-bold" style="font-size: 11px;">ĐIỀU KHOẢN</a>
        <span class="text-danger fw-bold" style="font-size: 11px;"><i class="bi bi-telephone-outbound-fill me-1"></i> HOTLINE: 11111</span>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'TheFooter'
}
</script>

<style scoped>
.bot-client-footer {
  margin-top: auto;
  position: relative;
  z-index: 1;
}
</style>
