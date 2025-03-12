<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use LoggableTrait;

    public function login()
    {
        try {
            if (Auth::check()) {
                return redirect()->route('admin.dashboard');
            }

            $title = 'Đăng nhập hệ thống quản trị';

            return view('auth.login', compact([
                'title'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function signup(){
        try {
            $title = 'Đăng kí tài khoản quản trị';
            return view('auth.signup', compact([
                'title'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function forgotPassword(){
        try {
            $title = 'Quên mật khẩu';
            return view('emails.auth.forgot-password', compact([
                'title'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function handleLogin(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {

                $user =  Auth::user();

                if ($user->hasRole('admin') || $user->hasRole('employee')) {
                    return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công');
                } else {
                    Auth::logout();

                    Abort(403, 'Bạn không có quyền truy cập');
                }
            }

            return redirect()->back()->with('error', 'Email hoặc mật khẩu không đúng');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        try {
            Auth::logout();

            session()->flush();

            return redirect()->route('admin.login');
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
