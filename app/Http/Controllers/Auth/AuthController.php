<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function showLogin(): View { return view('auth.login'); }
    public function showRegister(): View { return view('auth.register'); }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $result = $this->authService->register($request->validated());
        return $this->withTokenCookies(redirect()->route('home')->with('success', 'Tạo tài khoản độc giả thành công.'), $result['tokens']);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $result = $this->authService->login($request->validated('email'), $request->validated('password'));
        $target = match ($result['user']->role) {
            'ADMIN' => route('admin.dashboard'),
            'EDITOR' => route('editor.dashboard'),
            default => route('home'),
        };
        return $this->withTokenCookies(redirect()->to($target)->with('success', 'Đăng nhập thành công.'), $result['tokens']);
    }

    public function refresh(Request $request): RedirectResponse
    {
        $refreshToken = $request->cookie(config('jwt.refresh_cookie', 'refresh_token'));
        abort_unless($refreshToken, 401, 'Không tìm thấy refresh token.');
        $result = $this->authService->rotate($refreshToken);
        return $this->withTokenCookies(redirect()->back(), $result['tokens']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->revokeCurrent($request->cookie(config('jwt.refresh_cookie', 'refresh_token')));
        $this->authService->revokeAccess($request->cookie(config('jwt.access_cookie', 'access_token')));
        return $this->clearTokenCookies(redirect()->route('home')->with('success', 'Bạn đã đăng xuất.'));
    }

    public function logoutAll(Request $request): RedirectResponse
    {
        $this->authService->revokeAll($request->user());
        return $this->clearTokenCookies(redirect()->route('login')->with('success', 'Đã đăng xuất khỏi tất cả thiết bị.'));
    }

    public function account(Request $request): View
    {
        return view('auth.account', ['user' => $request->user()]);
    }

    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill(['password' => Hash::make($request->validated('password'))])->save();
        $this->authService->revokeAll($user);
        return $this->clearTokenCookies(redirect()->route('login')->with('success', 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.'));
    }

    private function withTokenCookies(RedirectResponse $response, array $tokens): RedirectResponse
    {
        $secure = (bool) config('jwt.cookie_secure', false);
        $sameSite = config('jwt.cookie_same_site', 'lax');
        $response->withCookie(cookie(config('jwt.access_cookie', 'access_token'), $tokens['access_token'], (int) config('jwt.ttl'), '/', null, $secure, true, false, $sameSite));
        $response->withCookie(cookie(config('jwt.refresh_cookie', 'refresh_token'), $tokens['refresh_token'], (int) config('jwt.refresh_ttl'), '/', null, $secure, true, false, $sameSite));
        return $response;
    }

    private function clearTokenCookies(RedirectResponse $response): RedirectResponse
    {
        foreach ([config('jwt.access_cookie', 'access_token'), config('jwt.refresh_cookie', 'refresh_token')] as $name) {
            $response->withCookie(cookie($name, '', -1, '/', null, (bool) config('jwt.cookie_secure', false), true, false, config('jwt.cookie_same_site', 'lax')));
        }
        return $response;
    }
}
