<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use App\Models\RefreshSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateWithJwt
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie(config('jwt.access_cookie', 'access_token'));
        if (!$token && preg_match('/^Bearer\s+(.+)$/i', (string) $request->header('Authorization'), $matches)) {
            $token = trim($matches[1]);
        }

        $rotatedTokens = null;
        try {
            if (!$token) throw new \RuntimeException('Missing access token');
            JWTAuth::setToken($token);
            $payload = JWTAuth::getPayload();
            $audience = $payload->get('aud');
            if ($payload->get('typ') !== 'access'
                || $payload->get('iss') !== config('jwt.issuer')
                || !in_array(config('jwt.audience'), (array) $audience, true)) {
                throw new \RuntimeException('Invalid access token claims');
            }
            $user = JWTAuth::authenticate();
            $session = RefreshSession::query()->whereKey((string) $payload->get('sid'))
                ->whereNull('revoked_at')->where('expires_at', '>', now())->first();
            if (!$session || $session->user_id !== $user?->id) throw new \RuntimeException('Revoked access session');
        } catch (\Throwable) {
            // Blade pages can transparently rotate an expired access token once, using the HttpOnly refresh cookie.
            $refresh = $request->cookie(config('jwt.refresh_cookie', 'refresh_token'));
            if (!$refresh) {
                if ($request->expectsJson()) abort(401, 'Vui lòng đăng nhập.');
                return redirect()->guest(route('login'))->with('error', 'Vui lòng đăng nhập để tiếp tục.');
            }
            try {
                $result = app(AuthService::class)->rotate($refresh);
                $user = $result['user'];
                $rotatedTokens = $result['tokens'];
            } catch (\Throwable) {
                if ($request->expectsJson()) abort(401, 'Phiên đăng nhập đã hết hạn.');
                return redirect()->guest(route('login'))->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
            }
        }

        if (!$user || $user->status !== 'ACTIVE') abort(403, 'Tài khoản đã bị khóa hoặc không còn hoạt động.');
        // Request::user() and Gate/Policy must resolve the same JWT-authenticated user.
        $request->setUserResolver(fn () => $user);
        Auth::setUser($user);

        $response = $next($request);

        if ($rotatedTokens) {
            $secure = (bool) config('jwt.cookie_secure', false);
            $sameSite = config('jwt.cookie_same_site', 'lax');
            $response->withCookie(cookie(config('jwt.access_cookie', 'access_token'), $rotatedTokens['access_token'], (int) config('jwt.ttl'), '/', null, $secure, true, false, $sameSite));
            $response->withCookie(cookie(config('jwt.refresh_cookie', 'refresh_token'), $rotatedTokens['refresh_token'], (int) config('jwt.refresh_ttl'), '/', null, $secure, true, false, $sameSite));
        }

        return $response;
    }
}
