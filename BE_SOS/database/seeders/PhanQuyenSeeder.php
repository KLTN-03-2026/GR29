<?php

namespace Database\Seeders;

use App\Models\ChucNang;
use App\Models\ChucVu;
use App\Models\PhanQuyen;
use Illuminate\Database\Seeder;

class PhanQuyenSeeder extends Seeder
{
    /**
     * Role structure (defined in ChucVuSeeder):
     *
     *  [Admin panel - auth:admin]
     *  - admin     (slug: admin)     => Quản trị viên    - Full system access
     *  - operator  (slug: operator)   => Điều phối       - Dispatch, queue, assignment, reports
     *
     *  [Rescuer panel - auth:thanh-vien-doi]
     *  - manager     (slug: manager)     => Quản lý       - Full rescuer access (all teams)
     *  - team_leader (slug: team_leader) => Trưởng đội    - Full rescuer access (own team)
     *  - member      (slug: member)      => Thành viên    - Limited personal access
     */
    public function run(): void
    {
        $this->command->info('Bat dau phan quyen...');

        $admin    = ChucVu::where('slug_chuc_vu', 'admin')->first();
        $operator = ChucVu::where('slug_chuc_vu', 'operator')->first();
        $manager  = ChucVu::where('slug_chuc_vu', 'manager')->first();
        $teamLead = ChucVu::where('slug_chuc_vu', 'team_leader')->first();
        $member   = ChucVu::where('slug_chuc_vu', 'member')->first();

        if (!$admin) {
            $this->command->error('Chua co chuc vu admin. Vui long chay ChucVuSeeder truoc.');
            return;
        }

        $cn = fn(string $code): ?ChucNang => ChucNang::where('ma_chuc_nang', $code)->first();

        $assign = function (ChucVu $role, array $permissions) use ($cn) {
            $count = 0;
            foreach ($permissions as $code) {
                $chucNang = $cn($code);
                if (!$chucNang) {
                    $this->command->warn("  Khong tim thay chuc nang: {$code}");
                    continue;
                }
                PhanQuyen::firstOrCreate([
                    'id_chuc_vu'  => $role->id_chuc_vu,
                    'id_chuc_nang' => $chucNang->id_chuc_nang,
                ]);
                $count++;
            }
            $this->command->info("  {$role->ten_chuc_vu}: {$count} quyen");
        };

        // =====================================================================
        // ADMIN — Quản trị viên (slug: admin)
        // Full access to everything in the system.
        // =====================================================================
        if ($admin) {
            $this->command->info('Phan quyen cho Admin:');
            $assign($admin, [
                // Request
                'request.create', 'request.view', 'request.show', 'request.update', 'request.delete',
                'request.update_status', 'request.view_by_status', 'request.view_by_urgency',
                'request.search', 'request.track', 'request.track_list', 'request.track_updates',
                // Queue
                'queue.view', 'queue.view_by_status',
                // Assignment
                'assignment.create', 'assignment.view', 'assignment.show', 'assignment.update',
                'assignment.delete', 'assignment.update_status', 'assignment.accept',
                'assignment.view_by_request', 'assignment.view_by_team', 'assignment.view_by_status',
                'assignment.check_active',
                // Team
                'team.view', 'team.show', 'team.create', 'team.update', 'team.delete',
                'team.view_by_status', 'team.view_by_area', 'team.search',
                // Member
                'member.view', 'member.create', 'member.update', 'member.delete', 'member.change_status',
                // Resource
                'resource.view', 'resource.create', 'resource.update',
                // Location
                'location.view', 'location.create',
                // Capability
                'capability.view', 'capability.update',
                // Incident type
                'incident.view', 'incident.show', 'incident.create', 'incident.update',
                'incident.delete', 'incident.update_status', 'incident.search',
                // Team incident type
                'team_incident.view', 'team_incident.create',
                // Result
                'result.view', 'result.create', 'result.update', 'result.view_by_assignment',
                // Rating
                'rating.view', 'rating.view_by_request',
                // Report / Statistics
                'report.view', 'report.show', 'report.view_by_request', 'report.view_by_team',
                'report.dashboard', 'report.total_requests', 'report.by_incident_type',
                'report.by_urgency', 'report.processing_status', 'report.team_efficiency',
                'report.available_teams',
                // Map
                'map.heatmap.view',
                // User management
                'admin.manage', 'client.manage',
                // Role / Permission management
                'role.manage', 'permission.manage',
                // AI
                'ai_classification.view', 'ai_classification.create',
                // Team finding
                'team.find_nearest',
            ]);
        }

        // =====================================================================
        // OPERATOR — Điều phối (slug: operator)
        // Lives in admin panel. Dispatch, queue, assignment, reports.
        // Cannot manage users, teams CRUD, or system config.
        // =====================================================================
        if ($operator) {
            $this->command->info('Phan quyen cho Operator:');
            $assign($operator, [
                // Request
                'request.view', 'request.show', 'request.update', 'request.update_status',
                'request.view_by_status', 'request.view_by_urgency', 'request.search',
                'request.track', 'request.track_list', 'request.track_updates',
                // Queue
                'queue.view', 'queue.view_by_status',
                // Assignment — full dispatch authority
                'assignment.create', 'assignment.view', 'assignment.show', 'assignment.update',
                'assignment.delete', 'assignment.update_status',
                'assignment.view_by_request', 'assignment.view_by_team', 'assignment.view_by_status',
                'assignment.check_active',
                // Team — view only
                'team.view', 'team.show', 'team.view_by_status', 'team.view_by_area', 'team.search',
                // Member — view only
                'member.view',
                // Resource — view only
                'resource.view',
                // Location — view only
                'location.view',
                // Capability — view only
                'capability.view',
                // Incident type — view + search
                'incident.view', 'incident.show', 'incident.search',
                // Team incident type
                'team_incident.view',
                // Result
                'result.view', 'result.view_by_assignment',
                // Rating
                'rating.view', 'rating.view_by_request',
                // Report / Statistics — full access
                'report.view', 'report.show', 'report.view_by_request', 'report.view_by_team',
                'report.dashboard', 'report.total_requests', 'report.by_incident_type',
                'report.by_urgency', 'report.processing_status', 'report.team_efficiency',
                'report.available_teams',
                // Map
                'map.heatmap.view',
                // AI
                'ai_classification.view', 'ai_classification.create',
                // Team finding
                'team.find_nearest',
            ]);
        }

        // =====================================================================
        // MANAGER — Quản lý (slug: manager)
        // Full rescuer access across ALL teams.
        // =====================================================================
        if ($manager) {
            $this->command->info('Phan quyen cho Manager:');
            $assign($manager, [
                // Dashboard
                'report.dashboard',
                // Request — full access
                'request.view', 'request.show', 'request.update',
                'request.view_by_status', 'request.view_by_urgency', 'request.search',
                'request.track', 'request.track_list', 'request.track_updates',
                // Queue
                'queue.view', 'queue.view_by_status',
                // Assignment — full management
                'assignment.create', 'assignment.view', 'assignment.show',
                'assignment.update', 'assignment.delete', 'assignment.update_status',
                'assignment.accept', 'assignment.view_by_request', 'assignment.view_by_team',
                'assignment.view_by_status', 'assignment.check_active',
                // Team — full CRUD
                'team.view', 'team.show', 'team.create', 'team.update', 'team.delete',
                'team.view_by_status', 'team.view_by_area', 'team.search',
                // Member — full CRUD
                'member.view', 'member.create', 'member.update', 'member.delete', 'member.change_status',
                // Resource — full CRUD
                'resource.view', 'resource.create', 'resource.update',
                // Location — full
                'location.view', 'location.create',
                // Capability — full
                'capability.view', 'capability.update',
                // Incident type — full CRUD
                'incident.view', 'incident.show', 'incident.create', 'incident.update',
                'incident.delete', 'incident.update_status', 'incident.search',
                // Team incident type
                'team_incident.view', 'team_incident.create',
                // Result
                'result.view', 'result.create', 'result.update', 'result.view_by_assignment',
                // Rating
                'rating.view', 'rating.view_by_request',
                // Report — full
                'report.view', 'report.show', 'report.view_by_request', 'report.view_by_team',
                'report.total_requests', 'report.by_incident_type',
                'report.by_urgency', 'report.processing_status',
                'report.team_efficiency', 'report.available_teams',
                // Map
                'map.heatmap.view',
                // AI
                'ai_classification.view', 'ai_classification.create',
                // Team finding
                'team.find_nearest',
            ]);
        }

        // =====================================================================
        // TEAM LEADER — Trưởng đội (slug: team_leader)
        // Full rescuer access scoped to own team.
        // =====================================================================
        if ($teamLead) {
            $this->command->info('Phan quyen cho Team Leader:');
            $assign($teamLead, [
                // Dashboard
                'report.dashboard',
                // Request — view + track
                'request.view', 'request.show',
                'request.track', 'request.track_list', 'request.track_updates',
                // Queue
                'queue.view', 'queue.view_by_status',
                // Assignment — full management for own team
                'assignment.view', 'assignment.show',
                'assignment.update_status', 'assignment.accept',
                'assignment.view_by_team', 'assignment.view_by_status',
                'assignment.check_active',
                'assignment.create',
                // Team — view all teams
                'team.view', 'team.show',
                'team.view_by_status', 'team.view_by_area', 'team.search',
                // Member — view all + manage own team
                'member.view', 'member.update', 'member.change_status',
                // Resource — full access
                'resource.view', 'resource.create', 'resource.update',
                // Location — full access
                'location.view', 'location.create',
                // Capability — full access
                'capability.view', 'capability.update',
                // Incident type
                'incident.view', 'incident.show', 'incident.search',
                // Team incident type
                'team_incident.view', 'team_incident.create',
                // Result — create + update
                'result.view', 'result.create', 'result.update', 'result.view_by_assignment',
                // Rating
                'rating.view', 'rating.view_by_request',
                // Report — full
                'report.view', 'report.show', 'report.view_by_request', 'report.view_by_team',
                'report.team_efficiency', 'report.available_teams',
                // Map
                'map.heatmap.view',
                // AI
                'ai_classification.view', 'ai_classification.create',
                // Team finding
                'team.find_nearest',
            ]);
        }

        // =====================================================================
        // MEMBER — Thành viên (slug: member)
        // Limited personal access.
        // =====================================================================
        if ($member) {
            $this->command->info('Phan quyen cho Member:');
            $assign($member, [
                // Dashboard
                'report.dashboard',
                // Request
                'request.view', 'request.show',
                'request.track', 'request.track_list', 'request.track_updates',
                // Queue
                'queue.view',
                // Assignment — view + accept + update status
                'assignment.view', 'assignment.show', 'assignment.update_status',
                'assignment.accept', 'assignment.view_by_team', 'assignment.view_by_status',
                'assignment.check_active',
                // Team — view own team
                'team.view', 'team.show',
                // Member — view own team members
                'member.view',
                // Resource — view own team
                'resource.view',
                // Location — view own team
                'location.view',
                // Capability — view own team
                'capability.view',
                // Team incident type
                'team_incident.view',
                // Result — create for own assignment
                'result.view', 'result.create', 'result.view_by_assignment',
                // Team finding
                'team.find_nearest',
            ]);
        }

        $this->command->info('');
        $this->command->info('PhanQuyenSeeder: Hoan tat phan quyen.');
        $this->command->info('');
        $this->command->info('Cau truc phan quyen:');
        $this->command->info('  [Admin panel]  admin     = Quản trị viên  — full quyền hệ thống');
        $this->command->info('  [Admin panel]  operator  = Điều phối     — dispatch, báo cáo');
        $this->command->info('  [Rescuer panel] manager   = Quản lý       — full quyền rescuer');
        $this->command->info('  [Rescuer panel] team_leader = Trưởng đội — full quyền rescuer');
        $this->command->info('  [Rescuer panel] member    = Thành viên    — truy cập hạn chế');
    }
}
