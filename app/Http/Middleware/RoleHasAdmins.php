<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleHasAdmins
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role = null): Response
    {
        if (
            Auth::check() && (
                Auth::user()->hasRole('admin') || Auth::user()->hasRole('employee')
            ) &&
            Auth::user()->email_verified_at !== null &&
            Auth::user()->status === 'active'
        ) {
            return $next($request);
        }
        // Kiểm tra quyền cho employee
        // if ( Auth::check() && Auth::user()->hasRole('employee')) {
        //     // Kiểm tra nếu yêu cầu truy cập vào banners hoặc posts
        //     if ($request->is('admin/banners/*') || $request->is('admin/posts/*') || $request->is('admin/dashboard')) {
        //         return $next($request);
        //     }

        //     // Nếu không phải banners hoặc posts, từ chối quyền truy cập
        //     return abort(403, 'Bạn không có quyền truy cập vào hệ thống này.');
        // }

        return abort(403, 'Bạn không có quyền truy cập vào hệ thống.');
    }
}
