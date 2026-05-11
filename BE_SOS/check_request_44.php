<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\YeuCauCuuHo;
use App\Models\LoaiSuCo;
use App\Models\Doi;

// Get request #44
$request = YeuCauCuuHo::find(44);
if ($request) {
    echo "Request #44:\n";
    echo "- ID: " . $request->id . "\n";
    echo "- Loai Su Co ID: " . $request->loai_su_co_id . "\n";
    
    // Get incident type
    $loaiSuCo = LoaiSuCo::find($request->loai_su_co_id);
    if ($loaiSuCo) {
        echo "- Loai Su Co Name: " . $loaiSuCo->ten_loai_su_co . "\n";
        echo "- Loai Su Co Slug: " . $loaiSuCo->slug . "\n";
    }
    
    // Get assigned team
    if ($request->doi_id) {
        $doi = Doi::find($request->doi_id);
        if ($doi) {
            echo "- Assigned Team ID: " . $doi->id . "\n";
            echo "- Assigned Team Name: " . $doi->ten_doi . "\n";
            echo "- Team Type: " . $doi->loai_doi . "\n";
        }
    } else {
        echo "- No team assigned\n";
    }
} else {
    echo "Request #44 not found\n";
}
