<?php

namespace Database\Seeders;

use App\Models\ChucNang;
use Illuminate\Database\Seeder;

class ChucNangSeeder extends Seeder
{
    public function run(): void
    {
        $chucNangs = [
            // ========== MODULE: REQUEST (Yêu cầu cứu hộ) ==========
            // Client creates a request; stored in HangDoiXuLy automatically on store()
            ['ma_chuc_nang' => 'request.create',           'ten_chuc_nang' => 'Tạo yêu cầu cứu hộ'],
            // All roles view the request list (paginated, with filters)
            ['ma_chuc_nang' => 'request.view',             'ten_chuc_nang' => 'Xem danh sách yêu cầu'],
            // View single request detail (full relationships loaded)
            ['ma_chuc_nang' => 'request.show',             'ten_chuc_nang' => 'Xem chi tiết yêu cầu'],
            // Admin-only: full update (location, description, priority, type)
            ['ma_chuc_nang' => 'request.update',           'ten_chuc_nang' => 'Cập nhật yêu cầu cứu hộ'],
            // Admin-only: hard delete (blocked if DANG_XU_LY/DA_PHAN_CONG/DA_DEN_HIEN_TRUONG)
            ['ma_chuc_nang' => 'request.delete',           'ten_chuc_nang' => 'Xóa yêu cầu cứu hộ'],
            // Admin updates status (CHO_XU_LY / DA_PHAN_CONG / DANG_XU_LY / DA_DEN_HIEN_TRUONG / HOAN_THANH / THAT_BAI / HUY_BO)
            ['ma_chuc_nang' => 'request.update_status',    'ten_chuc_nang' => 'Cập nhật trạng thái yêu cầu'],
            // Filter requests by status (CHO_XU_LY, DA_PHAN_CONG, DANG_XU_LY, DA_DEN_HIEN_TRUONG, HOAN_THANH, THAT_BAI, HUY_BO)
            ['ma_chuc_nang' => 'request.view_by_status',  'ten_chuc_nang' => 'Xem yêu cầu theo trạng thái'],
            // Filter by urgency (LOW, MEDIUM, HIGH, CRITICAL)
            ['ma_chuc_nang' => 'request.view_by_urgency', 'ten_chuc_nang' => 'Xem yêu cầu theo mức độ khẩn cấp'],
            // Multi-filter search: keyword, status, urgency, type, user, date range, priority range, affected people
            ['ma_chuc_nang' => 'request.search',          'ten_chuc_nang' => 'Tìm kiếm yêu cầu cứu hộ'],
            // Real-time tracking: full request + teams + timeline + current step
            ['ma_chuc_nang' => 'request.track',            'ten_chuc_nang' => 'Theo dõi yêu cầu cứu hộ'],
            // List all requests in active states (DA_PHAN_CONG, DANG_XU_LY, DA_DEN_HIEN_TRUONG)
            ['ma_chuc_nang' => 'request.track_list',       'ten_chuc_nang' => 'Xem danh sách theo dõi'],
            // Delta updates for real-time sync (with removed_ids detection)
            ['ma_chuc_nang' => 'request.track_updates',    'ten_chuc_nang' => 'Nhận cập nhật theo dõi thời gian thực'],

            // ========== MODULE: QUEUE (Hàng đợi xử lý) ==========
            // Paginated queue with priority sorting
            ['ma_chuc_nang' => 'queue.view',              'ten_chuc_nang' => 'Xem hàng đợi xử lý'],
            // Filter queue by status (WAITING, PROCESSING, DONE)
            ['ma_chuc_nang' => 'queue.view_by_status',    'ten_chuc_nang' => 'Xem hàng đợi theo trạng thái'],

            // ========== MODULE: ASSIGNMENT (Phân công cứu hộ) ==========
            // Create new assignment (maps request to team, default status MOI)
            ['ma_chuc_nang' => 'assignment.create',       'ten_chuc_nang' => 'Tạo phân công cứu hộ'],
            // View assignment list (paginated)
            ['ma_chuc_nang' => 'assignment.view',          'ten_chuc_nang' => 'Xem danh sách phân công'],
            // View single assignment detail
            ['ma_chuc_nang' => 'assignment.show',        'ten_chuc_nang' => 'Xem chi tiết phân công'],
            // Admin updates assignment (reassign team, change details)
            ['ma_chuc_nang' => 'assignment.update',       'ten_chuc_nang' => 'Cập nhật phân công cứu hộ'],
            // Admin deletes assignment
            ['ma_chuc_nang' => 'assignment.delete',       'ten_chuc_nang' => 'Xóa phân công cứu hộ'],
            // Rescuer/Admin: update task status (MOI → DANG_XU_LY → DA_DEN_HIEN_TRUONG → HOAN_THANH/THAT_BAI/HUY_BO)
            ['ma_chuc_nang' => 'assignment.update_status', 'ten_chuc_nang' => 'Cập nhật trạng thái nhiệm vụ'],
            // Rescuer accepts assignment (MOI → DANG_XU_LY), blocking second active assignment
            ['ma_chuc_nang' => 'assignment.accept',       'ten_chuc_nang' => 'Tiếp nhận nhiệm vụ cứu hộ'],
            // Get assignments for a specific request
            ['ma_chuc_nang' => 'assignment.view_by_request', 'ten_chuc_nang' => 'Xem phân công theo yêu cầu'],
            // Get assignments for a specific team (rescuer views their own)
            ['ma_chuc_nang' => 'assignment.view_by_team',  'ten_chuc_nang' => 'Xem phân công theo đội'],
            // Filter assignments by task status
            ['ma_chuc_nang' => 'assignment.view_by_status', 'ten_chuc_nang' => 'Xem phân công theo trạng thái'],
            // Check if team already has an active assignment (prevents double-accept)
            ['ma_chuc_nang' => 'assignment.check_active', 'ten_chuc_nang' => 'Kiểm tra nhiệm vụ đang hoạt động'],

            // ========== MODULE: TEAM (Đội cứu hộ) ==========
            // View team list (with capacity fields)
            ['ma_chuc_nang' => 'team.view',               'ten_chuc_nang' => 'Xem danh sách đội cứu hộ'],
            // View single team detail
            ['ma_chuc_nang' => 'team.show',               'ten_chuc_nang' => 'Xem chi tiết đội cứu hộ'],
            // Admin creates team
            ['ma_chuc_nang' => 'team.create',             'ten_chuc_nang' => 'Tạo đội cứu hộ'],
            // Admin updates team
            ['ma_chuc_nang' => 'team.update',             'ten_chuc_nang' => 'Cập nhật đội cứu hộ'],
            // Admin deletes team
            ['ma_chuc_nang' => 'team.delete',             'ten_chuc_nang' => 'Xóa đội cứu hộ'],
            // Filter teams by status (SAN_SANG, DANG_CUU_HO, etc.)
            ['ma_chuc_nang' => 'team.view_by_status',    'ten_chuc_nang' => 'Xem đội theo trạng thái'],
            // Filter teams by area
            ['ma_chuc_nang' => 'team.view_by_area',      'ten_chuc_nang' => 'Xem đội theo khu vực'],
            // Search teams by name or area
            ['ma_chuc_nang' => 'team.search',             'ten_chuc_nang' => 'Tìm kiếm đội cứu hộ'],

            // ========== MODULE: TEAM MEMBER (Thành viên đội) ==========
            // View team members
            ['ma_chuc_nang' => 'member.view',             'ten_chuc_nang' => 'Xem danh sách thành viên'],
            // Admin creates member
            ['ma_chuc_nang' => 'member.create',           'ten_chuc_nang' => 'Tạo thành viên đội'],
            // Admin updates member
            ['ma_chuc_nang' => 'member.update',           'ten_chuc_nang' => 'Cập nhật thành viên đội'],
            // Admin deletes member
            ['ma_chuc_nang' => 'member.delete',           'ten_chuc_nang' => 'Xóa thành viên đội'],
            // Admin toggles member active/inactive
            ['ma_chuc_nang' => 'member.change_status',    'ten_chuc_nang' => 'Thay đổi trạng thái thành viên'],

            // ========== MODULE: RESOURCE (Tài nguyên đội) ==========
            // View team resources
            ['ma_chuc_nang' => 'resource.view',           'ten_chuc_nang' => 'Xem tài nguyên đội'],
            // Add resource to team
            ['ma_chuc_nang' => 'resource.create',         'ten_chuc_nang' => 'Thêm tài nguyên đội'],
            // Update resource
            ['ma_chuc_nang' => 'resource.update',         'ten_chuc_nang' => 'Cập nhật tài nguyên đội'],

            // ========== MODULE: TEAM LOCATION (Vị trí đội) ==========
            // View team location history
            ['ma_chuc_nang' => 'location.view',            'ten_chuc_nang' => 'Xem vị trí đội'],
            // Add location point (tracking movement)
            ['ma_chuc_nang' => 'location.create',          'ten_chuc_nang' => 'Thêm vị trí đội'],

            // ========== MODULE: TEAM CAPABILITY (Năng lực đội) ==========
            // View team capability metrics
            ['ma_chuc_nang' => 'capability.view',         'ten_chuc_nang' => 'Xem năng lực đội'],
            // Update capability (so_viec_dang_xu_ly, so_viec_toi_da, ty_le_hoan_thanh, thoi_gian_xu_ly_tb)
            ['ma_chuc_nang' => 'capability.update',       'ten_chuc_nang' => 'Cập nhật năng lực đội'],

            // ========== MODULE: INCIDENT TYPE (Loại sự cố) ==========
            // View incident types
            ['ma_chuc_nang' => 'incident.view',            'ten_chuc_nang' => 'Xem danh sách loại sự cố'],
            // View single incident type with details
            ['ma_chuc_nang' => 'incident.show',            'ten_chuc_nang' => 'Xem chi tiết loại sự cố'],
            // Admin creates incident type
            ['ma_chuc_nang' => 'incident.create',          'ten_chuc_nang' => 'Tạo loại sự cố'],
            // Admin updates incident type
            ['ma_chuc_nang' => 'incident.update',          'ten_chuc_nang' => 'Cập nhật loại sự cố'],
            // Admin deletes incident type
            ['ma_chuc_nang' => 'incident.delete',          'ten_chuc_nang' => 'Xóa loại sự cố'],
            // Filter by status
            ['ma_chuc_nang' => 'incident.update_status',   'ten_chuc_nang' => 'Cập nhật trạng thái loại sự cố'],
            // Search incident types
            ['ma_chuc_nang' => 'incident.search',          'ten_chuc_nang' => 'Tìm kiếm loại sự cố'],

            // ========== MODULE: RESULT (Kết quả cứu hộ) ==========
            // View rescue results
            ['ma_chuc_nang' => 'result.view',             'ten_chuc_nang' => 'Xem kết quả cứu hộ'],
            // Rescuer creates result for their assignment
            ['ma_chuc_nang' => 'result.create',           'ten_chuc_nang' => 'Tạo kết quả cứu hộ'],
            // Admin updates result
            ['ma_chuc_nang' => 'result.update',           'ten_chuc_nang' => 'Cập nhật kết quả cứu hộ'],
            // Get result for a specific assignment
            ['ma_chuc_nang' => 'result.view_by_assignment', 'ten_chuc_nang' => 'Xem kết quả theo phân công'],

            // ========== MODULE: RATING (Đánh giá cứu hộ) ==========
            // Client rates a completed rescue
            ['ma_chuc_nang' => 'rating.create',           'ten_chuc_nang' => 'Tạo đánh giá cứu hộ'],
            // View ratings
            ['ma_chuc_nang' => 'rating.view',             'ten_chuc_nang' => 'Xem đánh giá cứu hộ'],
            // View ratings for a specific request
            ['ma_chuc_nang' => 'rating.view_by_request',  'ten_chuc_nang' => 'Xem đánh giá theo yêu cầu'],

            // ========== MODULE: REPORT (Báo cáo / Thống kê) ==========
            // Admin/Operator views rescue reports from teams (field reports with images)
            ['ma_chuc_nang' => 'report.view',             'ten_chuc_nang' => 'Xem báo cáo cứu hộ'],
            // View single report
            ['ma_chuc_nang' => 'report.show',             'ten_chuc_nang' => 'Xem chi tiết báo cáo'],
            // View reports for a specific request
            ['ma_chuc_nang' => 'report.view_by_request',  'ten_chuc_nang' => 'Xem báo cáo theo yêu cầu'],
            // View reports for a specific team
            ['ma_chuc_nang' => 'report.view_by_team',     'ten_chuc_nang' => 'Xem báo cáo theo đội'],
            // Dashboard: total, new, processing, completed, cancelled, critical count, avg wait
            ['ma_chuc_nang' => 'report.dashboard',        'ten_chuc_nang' => 'Xem thống kê dashboard'],
            // Total requests with breakdown by status
            ['ma_chuc_nang' => 'report.total_requests',   'ten_chuc_nang' => 'Xem tổng số yêu cầu'],
            // Requests grouped by incident type
            ['ma_chuc_nang' => 'report.by_incident_type', 'ten_chuc_nang' => 'Xem thống kê theo loại sự cố'],
            // Requests grouped by urgency level
            ['ma_chuc_nang' => 'report.by_urgency',       'ten_chuc_nang' => 'Xem thống kê theo mức độ khẩn cấp'],
            // Processing status with completion rate, avg urgency, avg affected people
            ['ma_chuc_nang' => 'report.processing_status','ten_chuc_nang' => 'Xem thống kê trạng thái xử lý'],
            // Team efficiency: members, resources, active tasks, completion rate, avg time
            ['ma_chuc_nang' => 'report.team_efficiency',  'ten_chuc_nang' => 'Xem hiệu suất đội cứu hộ'],
            // Teams ready for assignment (capacity-aware)
            ['ma_chuc_nang' => 'report.available_teams',  'ten_chuc_nang' => 'Xem danh sách đội sẵn sàng'],

            // ========== MODULE: MAP / HEATMAP ==========
            // View heatmap with incident density, urgency intensity, status
            ['ma_chuc_nang' => 'map.heatmap.view',        'ten_chuc_nang' => 'Xem bản đồ nhiệt'],

            // ========== MODULE: USER MANAGEMENT ==========
            // Admin CRUD on admin users
            ['ma_chuc_nang' => 'admin.manage',            'ten_chuc_nang' => 'Quản lý tài khoản quản trị'],
            // Admin CRUD on client users
            ['ma_chuc_nang' => 'client.manage',            'ten_chuc_nang' => 'Quản lý tài khoản người dùng'],

            // ========== MODULE: ROLE MANAGEMENT ==========
            // CRUD on ChucVu (admin, operator, team_leader, manager, member)
            ['ma_chuc_nang' => 'role.manage',             'ten_chuc_nang' => 'Quản lý chức vụ'],

            // ========== MODULE: POSITION MANAGEMENT ==========
            ['ma_chuc_nang' => 'permission.manage',       'ten_chuc_nang' => 'Quản lý quyền hạn'],

            // ========== MODULE: AI CLASSIFICATION ==========
            ['ma_chuc_nang' => 'ai_classification.view',  'ten_chuc_nang' => 'Xem phân loại AI'],
            ['ma_chuc_nang' => 'ai_classification.create', 'ten_chuc_nang' => 'Tạo phân loại AI'],

            // ========== MODULE: TEAM INCIDENT TYPE ==========
            ['ma_chuc_nang' => 'team_incident.view',      'ten_chuc_nang' => 'Xem loại sự cố đội xử lý'],
            ['ma_chuc_nang' => 'team_incident.create',     'ten_chuc_nang' => 'Thêm loại sự cố cho đội'],

            // ========== MODULE: TEAM FINDING ==========
            ['ma_chuc_nang' => 'team.find_nearest',       'ten_chuc_nang' => 'Tìm đội cứu hộ gần nhất'],
        ];

        foreach ($chucNangs as $item) {
            ChucNang::firstOrCreate(
                ['ma_chuc_nang' => $item['ma_chuc_nang']],
                ['ten_chuc_nang' => $item['ten_chuc_nang']]
            );
        }

        echo "ChucNangSeeder: da tao " . ChucNang::count() . " chuc nang\n";
    }
}
