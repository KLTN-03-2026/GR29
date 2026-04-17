<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRescuerOrAdmin
{
    /**
     * Handle an incoming request.
     * Allows: Admin or ThanhVienDoi authenticated users
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check admin guard
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            return $next($request);
        }

        // Check thanh-vien-doi guard (rescuer team member)
        $rescuer = Auth::guard('thanh-vien-doi')->user();
        if ($rescuer) {
            return $next($request);
        }

        // Check doi-cuu-ho guard (rescue team itself)
        $doi = Auth::guard('sanctum')->user();
        if ($doi) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized - Quyen truy cap bi tu choi'], 401);
    }
}
