<template>
  <div class="container py-4 py-lg-5">
    <div class="row g-4">
      <div class="col-12 col-lg-5">
        <div class="p-4 p-lg-5 rounded-4 shadow-sm border bg-white">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center"
              style="width: 44px; height: 44px; background: rgba(255,152,0,.12); color: #ff9800;">
              <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
              <h3 class="fw-bold mb-0">Thông tin an toàn</h3>
              <div class="text-muted small">Một vài hướng dẫn nhanh khi gặp sự cố</div>
            </div>
          </div>

          <div class="alert alert-warning border-0 rounded-3 mb-0" style="background: rgba(255,152,0,.12);">
            <div class="d-flex gap-2">
              <i class="fa-solid fa-triangle-exclamation mt-1" style="color: #ff9800;"></i>
              <div class="small">
                Nếu tình huống nguy hiểm đến tính mạng, hãy <b>gọi 113/114/115</b> ngay và rời khỏi khu vực rủi ro.
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-7">
        <div class="rounded-4 shadow-sm border bg-white overflow-hidden">
          <div class="p-4 border-bottom">
            <div class="fw-semibold">Checklist nhanh</div>
            <div class="text-muted small">Các bước ưu tiên giúp lực lượng phản ứng xử lý nhanh hơn</div>
          </div>

          <div class="p-4">
            <div class="row g-3">
              <div class="col-12 col-md-6" v-for="item in items" :key="item.title">
                <div class="p-3 rounded-4 border h-100" style="background: rgba(26,42,64,.03);">
                  <div class="d-flex gap-3">
                    <div class="rounded-3 d-inline-flex align-items-center justify-content-center flex-shrink-0"
                      style="width: 42px; height: 42px; background: rgba(26,42,64,.08); color: #1a2a40;">
                      <i :class="item.icon"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">{{ item.title }}</div>
                      <div class="text-muted small">{{ item.desc }}</div>
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
</template>

<script>
export default {
  name: "SafetyInfoClient",
  data() {
    return {
      items: [
        {
          title: "Chia sẻ vị trí chính xác",
          desc: "Bật GPS, mô tả mốc gần nhất và cập nhật nếu di chuyển.",
          icon: "fa-solid fa-location-crosshairs",
        },
        {
          title: "Giữ liên lạc",
          desc: "Mở chuông, sạc pin, trả lời cuộc gọi từ điều phối/đội cứu hộ.",
          icon: "fa-solid fa-phone-volume",
        },
        {
          title: "Chụp ảnh hiện trường",
          desc: "Ảnh/video giúp nhận diện rủi ro và chuẩn bị phương án phù hợp.",
          icon: "fa-solid fa-camera",
        },
        {
          title: "Đảm bảo an toàn cá nhân",
          desc: "Tránh tụ tập, tránh vật dễ rơi, di chuyển đến nơi an toàn khi cần.",
          icon: "fa-solid fa-person-shelter",
        },
      ],
    };
  },
};
</script>

<style scoped>
.container {
  max-width: 1120px;
}
</style>

