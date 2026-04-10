<template>
  <nav class="navbar navbar-expand-lg navbar-dark py-2 px-3 shadow-sm" style="background-color: #1a2a40;">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center fw-bold fs-4" href="#">
        nowSOS <span class="text-danger ms-1">RESCUER</span>
      </a>

      <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#sentinelNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="sentinelNav">
        <div class="navbar-nav ms-auto align-items-center gap-3 gap-lg-4 pt-3 pt-lg-0">
          
          <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-1 border border-white border-opacity-25">
            <span class="text-white-50 small fw-bold me-2">TRẠNG THÁI TRỰC:</span>
            <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
              <input class="form-check-input shadow-none cursor-pointer" type="checkbox" checked role="switch" id="statusSwitch">
              <label class="form-check-label text-white small fw-bold ms-2 cursor-pointer" for="statusSwitch">ONLINE</label>
            </div>
          </div>

          <div class="text-end d-none d-md-block">
            <div class="text-white-50 fw-bold" style="font-size: 10px;">MÃ ĐỘI VIÊN</div>
            <div class="text-white fw-bold">UNIT-702</div>
          </div>

          <div class="d-flex align-items-center gap-3">
            <button class="btn btn-link text-white p-0 position-relative shadow-none">
              <i class="bi bi-bell-fill fs-5"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5rem;">1</span>
            </button>
            <button class="btn btn-link p-0 border-0 shadow-none">
              <img src="https://ui-avatars.com/api/?name=Sentinel&background=random" class="rounded-circle border border-white border-2" width="38" height="38" alt="Avatar">
            </button>
          </div>

        </div>
      </div>
    </div>
  </nav>
</template>

<script>
export default { name: 'TheSentinelHeader' }
</script>