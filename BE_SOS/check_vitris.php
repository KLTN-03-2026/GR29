<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$teams = \App\Models\DoiCuuHo::with('viTris')->get();
foreach($teams as $team) {
    echo 'Team ' . $team->id_doi_cuu_ho . ': HQ ' . $team->vi_tri_lat . ', ' . $team->vi_tri_lng . PHP_EOL;
    foreach($team->viTris as $vt) {
        echo '  ViTri ' . $vt->id_vi_tri . ': ' . $vt->vi_tri_lat . ', ' . $vt->vi_tri_lng . PHP_EOL;
    }
}
?>