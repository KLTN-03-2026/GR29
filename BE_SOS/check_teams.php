<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$teams = \App\Models\DoiCuuHo::select('id_doi_cuu_ho', 'ten_doi', 'vi_tri_lat', 'vi_tri_lng')->get();
foreach($teams as $team) {
    echo $team->id_doi_cuu_ho . ': ' . $team->vi_tri_lat . ', ' . $team->vi_tri_lng . PHP_EOL;
}
?>