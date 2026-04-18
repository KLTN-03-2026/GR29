const fs = require('fs');
const path = 'c:/xampp/htdocs/DATN/SOS/GR29/FE_SOS/src/components/Client/DangXuLy/index.vue';
let text = fs.readFileSync(path, 'utf8');

const replacements = {
  'Theo DĂµi Tiáº¿n Ä á»™': 'Theo Dõi Tiến Độ',
  'CĂ C YĂŠU Cáº¦U Ä ANG TRONG QUĂ  TRĂŒNH Xá»¬ LĂ ': 'CÁC YÊU CẦU ĐANG TRONG QUÁ TRÌNH XỬ LÝ',
  'Thanh tĂ¬m kiáº¿m': 'Thanh tìm kiếm',
  'TĂ¬m theo ID, loáº¡i sá»± cá»‘...': 'Tìm theo ID, loại sự cố...',
  'Ä ang táº£i...': 'Đang tải...',
  'Ä ang Ä‘á»“ng bá»™ dá»¯ liá»‡u...': 'Đang đồng bộ dữ liệu...',
  'Vui lĂ²ng Ä‘á»£i trong giĂ¢y lĂ¡t': 'Vui lòng đợi trong giây lát',
  'KhĂ´ng cĂ³ yĂªu cáº§u Ä‘ang xá»­ lĂ½': 'Không có yêu cầu đang xử lý',
  'Báº¡n Ä‘ang an toĂ\x80n!': 'Bạn đang an toàn!',
  'Báº¡n Ä‘ang an toĂ n!': 'Bạn đang an toàn!',
  'Báº¡n Ä‘ang an toĂ n!': 'Bạn đang an toàn!',
  'Hiá»‡n táº¡i báº¡n khĂ´ng cĂ³ yĂªu cáº§u cá»©u há»™ nĂ\x80o Ä‘ang chá»  hoáº·c Ä‘ang Ä‘Æ°á»£c xá»­ lĂ½.': 'Hiện tại bạn không có yêu cầu cứu hộ nào đang chờ hoặc đang được xử lý.',
  'Táº¡o YĂªu Cáº§u Má»›i': 'Tạo Yêu Cầu Mới',
  'Danh sĂ¡ch yĂªu cáº§u Ä‘ang xá»­ lĂ½': 'Danh sách yêu cầu đang xử lý',
  'Dáº£i mĂ\x80u tráº¡ng thĂ¡i bĂªn trĂ¡i': 'Dải màu trạng thái bên trái',
  'Dáº£i mĂ u tráº¡ng thĂ¡i bĂªn trĂ¡i': 'Dải màu trạng thái bên trái',
  'Cá»™t TrĂ¡i: áº¢nh & Icons': 'Cột Trái: Ảnh & Icons',
  'áº¢nh hiá»‡n trÆ°á» ng': 'Ảnh hiện trường',
  'Cá»™t Pháº£i: Ná»™i Dung & Tiáº¿n Ä á»™': 'Cột Phải: Nội Dung & Tiến Độ',
  'Thanh Tiáº¿n Ä á»™': 'Thanh Tiến Độ',
  'Ä Ă£ tiáº¿p nháº\xADn ': 'Đã tiếp nhận ',
  'Ä Ă£ tiáº¿p nháº­n ': 'Đã tiếp nhận ',
  'Ä á»™i cá»©u há»™ Ä‘Ă£ tá»›i': 'Đội cứu hộ đã tới',
  'Váº¥n Ä‘á»  Ä‘ang Ä‘Æ°á»£c giáº£i quyáº¿t': 'Vấn đề đang được giải quyết',
  'Chi Tiáº¿t': 'Chi Tiết',
  'LiĂªn há»‡ Ä á»™i Cá»©u Há»™': 'Liên hệ Đội Cứu Hộ',
  'Modal Chi tiáº¿t': 'Modal Chi tiết',
  'Chi Tiáº¿t YĂªu Cáº§u Kháº©n Cáº¥p': 'Chi Tiết Yêu Cầu Khẩn Cấp',
  'MĂ£ YĂªu Cáº§u': 'Mã Yêu Cầu',
  'Tráº¡ng thĂ¡i hiá»‡n táº¡i': 'Trạng thái hiện tại',
  'Gá»\xADi lĂºc': 'Gửi lúc',
  'Gá»­i lĂºc': 'Gửi lúc',
  'Vá»‹ TrĂ\xAD Cáº§n Cá»©u Há»™': 'Vị Trí Cần Cứu Hộ',
  'MĂ´ Táº£ Sá»± Cá»‘': 'Mô Tả Sự Cố',
  'KhĂ´ng cĂ³ mĂ´ táº£ chi tiáº¿t.': 'Không có mô tả chi tiết.',
  'Ä Ă³ng Láº¡i': 'Đóng Lại'
};

for (const bad in replacements) {
    text = text.split(bad).join(replacements[bad]);
}
fs.writeFileSync(path, text, 'utf8');
console.log('Fixed using script file');
