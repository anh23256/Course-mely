<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleHasEmployees
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
    
        // Kiểm tra nếu người dùng là admin hoặc super_admin
        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            // Admin có thể truy cập tất cả các route
            if ($user->email_verified_at !== null && $user->status === 'active') {
                return $next($request);
            }
    
            // Nếu chưa xác thực email, chuyển hướng đến trang email
            return redirect('email');
        }
    
        // Kiểm tra nếu người dùng là employee
        if ($user->hasRole('employee')) {
            // Employee chỉ có quyền truy cập vào banners và posts
            if ($request->is('admin/banners/*') || $request->is('admin/posts/*')) {
                // Kiểm tra email đã xác thực và tài khoản đang hoạt động
                if ($user->email_verified_at !== null && $user->status === 'active') {
                    return $next($request);
                }
    
                // Nếu chưa xác thực email, chuyển hướng đến trang email
                return redirect('email');
            }
    
            // Nếu employee truy cập vào những route khác, từ chối truy cập
            return abort(403, 'Bạn không có quyền truy cập vào hệ thống này.');
        }
    
        // Nếu không phải admin hoặc employee
        return abort(403, 'Bạn không có quyền truy cập vào hệ thống.');
    }
    
}
