<template>
  <div class="auth-container team-theme">
    <!-- Action/Emergency background lines -->
    <div class="diagonal-bg"></div>

    <!-- Glowing orbs for emergency theme -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="auth-card">
      <div class="brand">
        <div class="sos-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
        <h1 class="logo">now<span>SOS</span></h1>
        <p class="subtitle">Cổng Thông Tin Đội Cứu Hộ</p>
      </div>

      <div class="alert-banner">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <span>Sẵn sàng ứng phó 24/7</span>
      </div>

      <form @submit.prevent="handleTeamLogin" class="auth-form">
        <div class="input-group">
          <label for="team-id">MÃ ĐỘI / CÁ NHÂN</label>
          <div class="input-wrapper">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <input type="text" id="team-id" placeholder="VD: RESCUE-01" required>
          </div>
        </div>

        <div class="input-group">
          <label for="password">MẬT KHẨU TỔ CHỨC</label>
          <div class="input-wrapper">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <input type="password" id="password" placeholder="Nhập mật khẩu" required>
          </div>
        </div>

        <button type="submit" class="btn-primary">Đăng Nhập Khẩn Cấp</button>
      </form>

      <div class="contact-support">
        Trợ giúp? <a href="#">Liên hệ Trung tâm</a>
      </div>
    </div>
  </div>
</template>

<script setup>
const handleTeamLogin = () => {
  // Team SOS Login logic here
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700;800&family=Oswald:wght@500;600;700&display=swap');

* {
  box-sizing: border-box;
  font-family: 'Quicksand', sans-serif;
}

.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #fff1f2; /* Rose 50 */
  position: relative;
  overflow: hidden;
  padding: 20px;
}

/* Team SOS Theme Colors - Red/Orange vibrant gradients */
.team-theme {
  background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
}

.diagonal-bg {
  position: absolute;
  top: -50%; left: -50%; width: 200%; height: 200%;
  background: repeating-linear-gradient(
    45deg,
    rgba(225, 29, 72, 0.03),
    rgba(225, 29, 72, 0.03) 10px,
    transparent 10px,
    transparent 20px
  );
  z-index: 0;
}

.team-theme .orb-1 {
  background: radial-gradient(circle, rgba(244,63,94,0.6) 0%, rgba(244,63,94,0) 70%);
  width: 500px;
  height: 500px;
  top: -100px;
  right: -100px;
  animation: pulse 4s ease-in-out infinite alternate;
}

.team-theme .orb-2 {
  background: radial-gradient(circle, rgba(249,115,22,0.5) 0%, rgba(249,115,22,0) 70%);
  width: 450px;
  height: 450px;
  bottom: -150px;
  left: -50px;
}

.team-theme .orb-3 {
  background: radial-gradient(circle, rgba(239,68,68,0.4) 0%, rgba(239,68,68,0) 70%);
  width: 300px;
  height: 300px;
  top: 40%;
  left: 20%;
  animation: float 6s ease-in-out infinite;
}

.orb {
  position: absolute;
  border-radius: 50%;
  z-index: 0;
  filter: blur(40px);
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 0.8; }
  100% { transform: scale(1.1); opacity: 1; }
}

@keyframes float {
  0% { transform: translate(0, 0); }
  50% { transform: translate(30px, -20px); }
  100% { transform: translate(0, 0); }
}

.auth-card {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(244, 63, 94, 0.3);
  border-radius: 20px;
  padding: 40px;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 20px 40px rgba(225, 29, 72, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.5) inset;
  z-index: 1;
  position: relative;
  border-top: 5px solid #e11d48;
}

.brand {
  text-align: center;
  margin-bottom: 25px;
}

.sos-icon {
  margin: 0 auto 15px auto;
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #f43f5e, #e11d48);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 8px 16px rgba(225, 29, 72, 0.3);
}

.sos-icon svg {
  width: 28px;
  height: 28px;
}

.logo {
  font-family: 'Oswald', sans-serif;
  font-size: 32px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.logo span {
  color: #e11d48; /* SOS Red */
}

.subtitle {
  color: #64748b;
  font-size: 15px;
  margin-top: 5px;
  font-weight: 600;
}

.alert-banner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: rgba(244, 63, 94, 0.1);
  color: #e11d48;
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 25px;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.alert-banner svg {
  width: 16px;
  height: 16px;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.input-group label {
  font-size: 12px;
  font-weight: 800;
  color: #475569;
  letter-spacing: 0.5px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 16px;
  width: 18px;
  height: 18px;
  color: #94a3b8;
  transition: color 0.3s ease;
}

.input-wrapper input {
  width: 100%;
  padding: 14px 16px 14px 44px;
  border-radius: 12px;
  border: 1px solid rgba(225, 29, 72, 0.2);
  background: rgba(255, 255, 255, 0.9);
  font-size: 15px;
  color: #1e293b;
  transition: all 0.3s ease;
  outline: none;
  font-weight: 600;
}

.input-wrapper input::placeholder {
  color: #94a3b8;
  font-weight: 500;
}

.input-wrapper input:focus {
  background: #ffffff;
  border-color: #f43f5e;
  box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.15);
}

.input-wrapper input:focus + .input-icon,
.input-wrapper input:focus ~ .input-icon {
  color: #e11d48;
}

.btn-primary {
  background: linear-gradient(to right, #f43f5e, #e11d48);
  color: white;
  border: none;
  border-radius: 12px;
  padding: 15px;
  font-size: 16px;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 8px 20px rgba(225, 29, 72, 0.35);
  margin-top: 15px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 25px rgba(225, 29, 72, 0.45);
  background: linear-gradient(to right, #fb7185, #f43f5e);
}

.btn-primary:active {
  transform: translateY(0);
}

.contact-support {
  text-align: center;
  margin-top: 25px;
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
}

.contact-support a {
  color: #e11d48;
  text-decoration: none;
  font-weight: 700;
  transition: color 0.2s;
}

.contact-support a:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .auth-card {
    padding: 30px 20px;
  }
}
</style>