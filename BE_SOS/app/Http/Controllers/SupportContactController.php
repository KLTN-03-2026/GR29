<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SupportContactController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::guard('nguoi-dung')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $record = [
            'id_nguoi_dung' => $user->id_nguoi_dung,
            'ho_ten' => $validated['name'],
            'so_dien_thoai' => $validated['phone'],
            'chu_de' => $validated['subject'],
            'noi_dung' => $validated['message'],
            'created_at' => now()->toDateTimeString(),
        ];

        $filename = 'support-contacts/' . now()->format('Y-m-d') . '.jsonl';
        Storage::disk('local')->append($filename, json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
        Log::info('Support contact received', $record);

        return response()->json([
            'status' => true,
            'message' => 'Yêu cầu hỗ trợ đã được gửi.',
        ]);
    }

    public function index()
    {
        $supportPath = 'support-contacts';
        if (!Storage::disk('local')->exists($supportPath)) {
            return response()->json([
                'status' => true,
                'data' => [],
            ]);
        }

        $files = Storage::disk('local')->files($supportPath);
        $records = [];

        foreach ($files as $file) {
            if (!str_ends_with($file, '.jsonl')) {
                continue;
            }

            $content = trim(Storage::disk('local')->get($file));
            if ($content === '') {
                continue;
            }

            foreach (explode("\n", $content) as $line) {
                if (trim($line) === '') {
                    continue;
                }

                $data = json_decode($line, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $records[] = $data;
                }
            }
        }

        usort($records, function ($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        return response()->json([
            'status' => true,
            'data' => array_slice($records, 0, 40),
        ]);
    }
}
