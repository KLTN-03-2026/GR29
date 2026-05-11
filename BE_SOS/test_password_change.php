<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\NguoiDungController;
use App\Models\NguoiDung;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

// Simulate a request
$request = new Request();
$request->merge([
    'current_password' => '123456',
    'new_password' => 'newpassword123',
    'new_password_confirmation' => 'newpassword123',
    'ho_ten' => 'Test User',
    'email' => 'test@example.com',
    'so_dien_thoai' => '0123456789'
]);

// Create a test user if not exists
$user = NguoiDung::where('email', 'test@example.com')->first();
if (!$user) {
    $user = NguoiDung::create([
        'ho_ten' => 'Test User',
        'email' => 'test@example.com',
        'so_dien_thoai' => '0123456789',
        'mat_khau' => Hash::make('123456'),
        'trang_thai' => 1
    ]);
}

// Simulate authentication
Auth::guard('nguoi-dung')->login($user);

$controller = new NguoiDungController();
$response = $controller->updateProfile($request);

echo "Response: " . $response->getContent() . "\n";

// Check if password was updated
$user->refresh();
if (Hash::check('newpassword123', $user->mat_khau)) {
    echo "Password updated successfully!\n";
} else {
    echo "Password not updated!\n";
}
