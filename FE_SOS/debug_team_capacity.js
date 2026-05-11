import axios from 'axios';

async function debugTeamCapacity() {
  try {
    const response = await axios.get('http://localhost:8000/api/doi-cuu-ho?get_all=true', {
      headers: {
        'Authorization': 'Bearer 1|abc123',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    const teams = response.data.data || [];
    const sonTraTeam = teams.find(team => 
      team.ten_doi && team.ten_doi.toLowerCase().includes('sơn trà')
    );
    
    if (sonTraTeam) {
      console.log('Đội Sơn Trà:');
      console.log('ID:', sonTraTeam.id_doi_cuu_ho || sonTraTeam.id);
      console.log('Tên:', sonTraTeam.ten_doi);
      console.log('Active count:', sonTraTeam.active_count);
      console.log('Pending count:', sonTraTeam.pending_count);
      console.log('Thành viên:', sonTraTeam.thanh_viens ? sonTraTeam.thanh_viens.length : 0);
      console.log('Tổng nhiệm vụ:', (sonTraTeam.active_count || 0) + (sonTraTeam.pending_count || 0));
      console.log('Capacity:', sonTraTeam.thanh_viens ? sonTraTeam.thanh_viens.length * 1 : 0);
      
      console.log('\nPhân công chi tiết:');
      if (sonTraTeam.phan_congs && sonTraTeam.phan_congs.length > 0) {
        sonTraTeam.phan_congs.forEach((pc, index) => {
          console.log(`${index + 1}. Trạng thái: ${pc.trang_thai || pc.trang_thai_nhiem_vu}`);
        });
      } else {
        console.log('Không có dữ liệu phân công');
      }
    } else {
      console.log('Không tìm thấy đội Sơn Trà');
      console.log('Các đội có sẵn:');
      teams.forEach(team => {
        console.log(`- ${team.ten_doi} (ID: ${team.id_doi_cuu_ho || team.id})`);
      });
    }
  } catch (error) {
    console.error('Lỗi:', error.message);
    if (error.response) {
      console.error('Response status:', error.response.status);
      console.error('Response data:', error.response.data);
    }
  }
}

debugTeamCapacity();
