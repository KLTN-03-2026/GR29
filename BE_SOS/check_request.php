<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$request = \App\Models\YeuCauCuuHo::find(1);
if ($request) {
    echo 'Request 1: ' . $request->vi_tri_lat . ', ' . $request->vi_tri_lng . PHP_EOL;
} else {
    echo 'Request 1 not found' . PHP_EOL;
}
?>