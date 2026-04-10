<template>
  <div class="d-flex flex-column flex-grow-1 overflow-hidden bg-light">
    <div class="container-fluid p-0 d-flex flex-column flex-lg-row flex-grow-1 overflow-hidden" style="height: calc(100vh - 140px);">
      
      <div class="col-lg-4 col-xl-3 bg-white border-end d-flex flex-column shadow-sm">
        <div class="p-3 border-bottom bg-light">
          <h6 class="fw-bold mb-0 text-uppercase small text-secondary">
            <i class="bi bi-asterisk text-danger me-2"></i>Danh sách yêu cầu cứu hộ
          </h6>
        </div>
        
        <div class="flex-grow-1 overflow-y-auto p-3 d-flex flex-column gap-3">
          <div v-for="task in missions" :key="task.id" class="card border-0 shadow-sm rounded-4 hover-shadow transition-all">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between mb-2">
                <span :class="['badge rounded-pill px-2 py-1', task.priorityBg]">{{ task.priorityText }}</span>
                <span class="text-muted small fw-bold">{{ task.distance }}</span>
              </div>
              <h6 class="fw-bold mb-1">{{ task.type }}</h6>
              <p class="text-muted mb-3" style="font-size: 11px;"><i class="bi bi-clock me-1"></i>{{ task.time }}</p>
              <button class="btn btn-dark w-100 fw-bold btn-sm rounded-3 py-2">TIẾP NHẬN</button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-8 col-xl-9 position-relative bg-secondary bg-opacity-10 p-0">
        <div class="w-100 h-100 bg-white position-relative">
          <MapboxMap
            class="w-100 h-100"
            :center="[105.8342, 21.0285]"
            :zoom="14"
          />
        </div>

        <div class="position-absolute top-0 end-0 m-3 col-11 col-md-5 col-lg-4">
          <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-body p-3">
              <div class="d-flex align-items-center mb-3">
                <div class="bg-danger bg-opacity-10 p-2 rounded-3 me-3 text-danger">
                  <i class="bi bi-file-earmark-text-fill fs-5"></i>
                </div>
                <div>
                  <div class="fw-bold small text-muted text-uppercase" style="font-size: 9px;">Nhiệm vụ hiện tại</div>
                  <div class="text-success fw-bold small"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> ĐANG XỬ LÝ</div>
                </div>
              </div>

              <div class="bg-light rounded-3 p-3 mb-3">
                <div class="text-muted x-small fw-bold">NẠN NHÂN</div>
                <div class="fw-bold mb-2">Nguyễn Văn A</div>
                <div class="text-muted x-small fw-bold">SỐ ĐIỆN THOẠI</div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-danger">0901234567</span>
                  <a href="tel:0901234567" class="btn btn-white btn-sm shadow-sm border rounded-circle"><i class="bi bi-telephone-fill text-muted"></i></a>
                </div>
              </div>

              <button class="btn btn-danger w-100 py-3 fw-bold rounded-3 shadow-sm border-0">
                <i class="bi bi-send-fill me-2"></i> CHỈ ĐƯỜNG ĐẾN HIỆN TRƯỜNG
              </button>
            </div>
          </div>
        </div>

        <div class="position-absolute bottom-0 end-0 m-3 d-flex flex-column gap-2 shadow-sm">
          <button class="btn btn-white bg-white p-2 border-0 rounded-top shadow-none"><i class="bi bi-plus-lg"></i></button>
          <button class="btn btn-white bg-white p-2 border-0 rounded-0 border-top border-bottom shadow-none"><i class="bi bi-dash-lg"></i></button>
          <button class="btn btn-white bg-white p-2 border-0 rounded-bottom shadow-none text-danger"><i class="bi bi-crosshair"></i></button>
        </div>
      </div>
    </div>

    <div class="bg-white border-top py-3 px-4 shadow-lg mt-auto">
      <div class="container-fluid">
        <div class="row g-3 justify-content-center">
          <div class="col-12 col-md-4 col-lg-3">
            <button class="btn btn-danger w-100 py-3 fw-bold rounded-3 shadow border-0">
              <i class="bi bi-geo-alt-fill me-2"></i> ĐÃ ĐẾN HIỆN TRƯỜNG
            </button>
          </div>
          <div class="col-12 col-md-4 col-lg-3">
            <button class="btn btn-warning w-100 py-3 fw-bold rounded-3 shadow border-0 text-dark">
              <i class="bi bi-megaphone-fill me-2"></i> YÊU CẦU HỖ TRỢ
            </button>
          </div>
          <div class="col-12 col-md-4 col-lg-3">
            <button class="btn btn-success w-100 py-3 fw-bold rounded-3 shadow border-0">
              <i class="bi bi-check-circle-fill me-2"></i> HOÀN THÀNH NHIỆM VỤ
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MapboxMap from "../../common/MapboxMap.vue";

export default {
  components: { MapboxMap },
  data() {
    return {
      missions: [
        { id: 1, type: 'Tai nạn giao thông', priorityText: 'CAO', priorityBg: 'bg-danger bg-opacity-10 text-danger', distance: '2.5km', time: '2 phút trước' },
        { id: 2, type: 'Hỏa hoạn nhà dân', priorityText: 'CAO', priorityBg: 'bg-danger bg-opacity-10 text-danger', distance: '4.1km', time: '5 phút trước' },
        { id: 3, type: 'Hỗ trợ y tế', priorityText: 'TRUNG BÌNH', priorityBg: 'bg-warning bg-opacity-10 text-warning', distance: '1.2km', time: '12 phút trước' },
      ]
    }
  }
}
</script>

<style scoped>
.x-small { font-size: 10px; }
.hover-shadow:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
.transition-all { transition: all 0.2s ease-in-out; }
.cursor-pointer { cursor: pointer; }
</style>
