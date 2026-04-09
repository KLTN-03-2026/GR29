<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_get_own_profile(): void
    {
        $user = NguoiDung::create([
            'ho_ten' => 'Nguyen Van A',
            'so_dien_thoai' => '0912345678',
            'email' => 'client@example.com',
            'mat_khau' => Hash::make('secret123'),
            'trang_thai' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/client/profile/data');

        $response
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.email', 'client@example.com')
            ->assertJsonMissingPath('data.mat_khau');
    }

    public function test_admin_cannot_access_client_profile_routes(): void
    {
        $admin = Admin::create([
            'ho_ten' => 'Admin User',
            'email' => 'admin@example.com',
            'mat_khau' => Hash::make('secret123'),
            'id_chuc_vu' => null,
            'trang_thai' => 1,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/client/profile/data')
            ->assertForbidden();
    }

    public function test_client_can_update_profile_and_password(): void
    {
        $user = NguoiDung::create([
            'ho_ten' => 'Nguyen Van A',
            'so_dien_thoai' => '0912345678',
            'email' => 'client@example.com',
            'mat_khau' => Hash::make('secret123'),
            'trang_thai' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/client/profile/update', [
            'ho_ten' => 'Nguyen Van B',
            'so_dien_thoai' => '0987654321',
            'email' => 'updated@example.com',
            'mat_khau' => 'newpass123',
            'mat_khau_confirmation' => 'newpass123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.ho_ten', 'Nguyen Van B')
            ->assertJsonPath('data.email', 'updated@example.com')
            ->assertJsonPath('data.so_dien_thoai', '0987654321');

        $user->refresh();

        $this->assertTrue(Hash::check('newpass123', $user->mat_khau));
    }

    public function test_client_can_change_password_from_client_form_payload(): void
    {
        $user = NguoiDung::create([
            'ho_ten' => 'Nguyen Van A',
            'so_dien_thoai' => '0912345678',
            'email' => 'client@example.com',
            'mat_khau' => Hash::make('secret123'),
            'trang_thai' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/client/profile/change-password', [
            'currentPassword' => 'secret123',
            'newPassword' => 'newsecret456',
            'confirmPassword' => 'newsecret456',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Doi mat khau thanh cong');

        $user->refresh();

        $this->assertTrue(Hash::check('newsecret456', $user->mat_khau));
    }

    public function test_client_cannot_change_password_with_wrong_current_password(): void
    {
        $user = NguoiDung::create([
            'ho_ten' => 'Nguyen Van A',
            'so_dien_thoai' => '0912345678',
            'email' => 'client@example.com',
            'mat_khau' => Hash::make('secret123'),
            'trang_thai' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/client/profile/change-password', [
            'current_password' => 'wrong-password',
            'new_password' => 'newsecret456',
            'new_password_confirmation' => 'newsecret456',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'Mat khau hien tai khong dung');

        $user->refresh();

        $this->assertTrue(Hash::check('secret123', $user->mat_khau));
    }
}
