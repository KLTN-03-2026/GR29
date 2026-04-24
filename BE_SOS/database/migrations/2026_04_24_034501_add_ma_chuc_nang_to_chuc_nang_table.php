<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add column as nullable (safe for existing data)
        Schema::table('chuc_nang', function (Blueprint $table) {
            $table->string('ma_chuc_nang', 100)->nullable()->after('ten_chuc_nang');
        });

        // Step 2: Map ALL existing ten_chuc_nang entries to their ma_chuc_nang values
        // (matches every entry in ChucNangSeeder)
        $mapping = [
            // === REQUEST (Yêu cầu cứu hộ) ===
            'Tạo yêu cầu cứu hộ'           => 'request.create',
            'Xem danh sách yêu cầu'         => 'request.view',
            'Xem chi tiết yêu cầu'          => 'request.show',
            'Cập nhật yêu cầu cứu hộ'      => 'request.update',
            'Xóa yêu cầu cứu hộ'           => 'request.delete',
            'Cập nhật trạng thái yêu cầu'  => 'request.update_status',
            'Xem yêu cầu theo trạng thái'  => 'request.view_by_status',
            'Xem yêu cầu theo mức độ khẩn cấp' => 'request.view_by_urgency',
            'Tìm kiếm yêu cầu cứu hộ'      => 'request.search',
            'Theo dõi yêu cầu cứu hộ'      => 'request.track',
            'Xem danh sách theo dõi'        => 'request.track_list',
            'Nhận cập nhật theo dõi thời gian thực' => 'request.track_updates',

            // === QUEUE (Hàng đợi xử lý) ===
            'Xem hàng đợi xử lý'           => 'queue.view',
            'Xem hàng đợi theo trạng thái' => 'queue.view_by_status',

            // === ASSIGNMENT (Phân công cứu hộ) ===
            'Tạo phân công cứu hộ'          => 'assignment.create',
            'Xem danh sách phân công'       => 'assignment.view',
            'Xem chi tiết phân công'        => 'assignment.show',
            'Cập nhật phân công cứu hộ'    => 'assignment.update',
            'Xóa phân công cứu hộ'          => 'assignment.delete',
            'Cập nhật trạng thái nhiệm vụ' => 'assignment.update_status',
            'Tiếp nhận nhiệm vụ cứu hộ'    => 'assignment.accept',
            'Xem phân công theo yêu cầu'   => 'assignment.view_by_request',
            'Xem phân công theo đội'        => 'assignment.view_by_team',
            'Xem phân công theo trạng thái' => 'assignment.view_by_status',
            'Kiểm tra nhiệm vụ đang hoạt động' => 'assignment.check_active',

            // === TEAM (Đội cứu hộ) ===
            'Xem danh sách đội cứu hộ'      => 'team.view',
            'Xem chi tiết đội cứu hộ'       => 'team.show',
            'Tạo đội cứu hộ'               => 'team.create',
            'Cập nhật đội cứu hộ'           => 'team.update',
            'Xóa đội cứu hộ'               => 'team.delete',
            'Xem đội theo trạng thái'       => 'team.view_by_status',
            'Xem đội theo khu vực'          => 'team.view_by_area',
            'Tìm kiếm đội cứu hộ'           => 'team.search',

            // === MEMBER (Thành viên đội) ===
            'Xem danh sách thành viên'      => 'member.view',
            'Tạo thành viên đội'            => 'member.create',
            'Cập nhật thành viên đội'       => 'member.update',
            'Xóa thành viên đội'            => 'member.delete',
            'Thay đổi trạng thái thành viên' => 'member.change_status',

            // === RESOURCE (Tài nguyên đội) ===
            'Xem tài nguyên đội'            => 'resource.view',
            'Thêm tài nguyên đội'           => 'resource.create',
            'Cập nhật tài nguyên đội'       => 'resource.update',

            // === LOCATION (Vị trí đội) ===
            'Xem vị trí đội'                => 'location.view',
            'Thêm vị trí đội'               => 'location.create',

            // === CAPABILITY (Năng lực đội) ===
            'Xem năng lực đội'              => 'capability.view',
            'Cập nhật năng lực đội'         => 'capability.update',

            // === INCIDENT TYPE (Loại sự cố) ===
            'Xem danh sách loại sự cố'      => 'incident.view',
            'Xem chi tiết loại sự cố'       => 'incident.show',
            'Tạo loại sự cố'                => 'incident.create',
            'Cập nhật loại sự cố'            => 'incident.update',
            'Xóa loại sự cố'                => 'incident.delete',
            'Cập nhật trạng thái loại sự cố' => 'incident.update_status',
            'Tìm kiếm loại sự cố'           => 'incident.search',

            // === RESULT (Kết quả cứu hộ) ===
            'Xem kết quả cứu hộ'             => 'result.view',
            'Tạo kết quả cứu hộ'            => 'result.create',
            'Cập nhật kết quả cứu hộ'       => 'result.update',
            'Xem kết quả theo phân công'    => 'result.view_by_assignment',

            // === RATING (Đánh giá cứu hộ) ===
            'Tạo đánh giá cứu hộ'           => 'rating.create',
            'Xem đánh giá cứu hộ'           => 'rating.view',
            'Xem đánh giá theo yêu cầu'     => 'rating.view_by_request',

            // === REPORT (Báo cáo / Thống kê) ===
            'Xem báo cáo cứu hộ'            => 'report.view',
            'Xem chi tiết báo cáo'           => 'report.show',
            'Xem báo cáo theo yêu cầu'      => 'report.view_by_request',
            'Xem báo cáo theo đội'          => 'report.view_by_team',
            'Xem thống kê dashboard'        => 'report.dashboard',
            'Xem tổng số yêu cầu'           => 'report.total_requests',
            'Xem thống kê theo loại sự cố'  => 'report.by_incident_type',
            'Xem thống kê theo mức độ khẩn cấp' => 'report.by_urgency',
            'Xem thống kê trạng thái xử lý' => 'report.processing_status',
            'Xem hiệu suất đội cứu hộ'      => 'report.team_efficiency',
            'Xem danh sách đội sẵn sàng'    => 'report.available_teams',

            // === MAP / HEATMAP ===
            'Xem bản đồ nhiệt'              => 'map.heatmap.view',

            // === USER MANAGEMENT ===
            'Quản lý tài khoản quản trị'    => 'admin.manage',
            'Quản lý tài khoản người dùng'  => 'client.manage',

            // === ROLE / PERMISSION MANAGEMENT ===
            'Quản lý chức vụ'               => 'role.manage',
            'Quản lý quyền hạn'             => 'permission.manage',

            // === AI CLASSIFICATION ===
            'Xem phân loại AI'              => 'ai_classification.view',
            'Tạo phân loại AI'              => 'ai_classification.create',

            // === TEAM INCIDENT TYPE ===
            'Xem loại sự cố đội xử lý'      => 'team_incident.view',
            'Thêm loại sự cố cho đội'        => 'team_incident.create',

            // === TEAM FINDING ===
            'Tìm đội cứu hộ gần nhất'       => 'team.find_nearest',
        ];

        foreach ($mapping as $ten => $ma) {
            DB::table('chuc_nang')
                ->where('ten_chuc_nang', $ten)
                ->whereNull('ma_chuc_nang')
                ->update(['ma_chuc_nang' => $ma]);
        }

        // Step 3: Change to NOT NULL and add unique constraint
        Schema::table('chuc_nang', function (Blueprint $table) {
            $table->string('ma_chuc_nang', 100)->nullable(false)->change();
            $table->unique('ma_chuc_nang', 'chuc_nang_ma_chuc_nang_unique');
            $table->index('ma_chuc_nang', 'chuc_nang_ma_chuc_nang_index');
        });
    }

    public function down(): void
    {
        Schema::table('chuc_nang', function (Blueprint $table) {
            $table->dropUnique('chuc_nang_ma_chuc_nang_unique');
            $table->dropIndex('chuc_nang_ma_chuc_nang_index');
            $table->dropColumn('ma_chuc_nang');
        });
    }
};
