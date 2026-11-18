<?php

namespace App\Services;

use App\Models\RefreshSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    public function register(array $data): array
    {
        // Check jwt thuat toan , secret , ttl , refresh_ttl , issuer , audience
        $this->assertJwtConfiguration();

        // Create the account and its first refresh session atomically. A JWT configuration
        // error must not leave behind an account that appears registered but cannot log in.
        return DB::transaction(function () use ($data): array {
            $user = User::create([
                'email' => mb_strtolower(trim($data['email'])),
                'password' => $data['password'],
                'full_name' => trim($data['full_name']),
                'display_name' => trim($data['full_name']),
                'role' => 'USER',
                'status' => 'ACTIVE',
            ]);
            
            return ['user' => $user, 'tokens' => $this->issuePair($user)];
        });
    }

    public function login(string $email, string $password): array
    {
        $user = User::where('email', mb_strtolower(trim($email)))->first();
        if (!$user || !Hash::check($password, $user->password) || $user->status !== 'ACTIVE') {
            throw ValidationException::withMessages(['email' => 'Thông tin đăng nhập không chính xác hoặc tài khoản không hoạt động.']);
        }
        $this->assertJwtConfiguration();
        return DB::transaction(function () use ($user): array {
            $user->forceFill(['last_login_at' => now()])->save();
            return ['user' => $user, 'tokens' => $this->issuePair($user)];
        });
    }

    public function issuePair(User $user): array
    {
        $this->assertJwtConfiguration();

        $familyId = (string) Str::uuid();
        $sessionId = (string) Str::uuid();
        $access = $this->createToken($user, 'access', (int) config('jwt.ttl'), $sessionId);
        $refresh = $this->createToken($user, 'refresh', (int) config('jwt.refresh_ttl'), $sessionId);
        $payload = JWTAuth::setToken($refresh)->getPayload();
        $jti = (string) $payload->get('jti');
        RefreshSession::create([
            'id' => $sessionId,
            'user_id' => $user->id,
            'jti' => $jti,
            'token_hash' => hash('sha256', $refresh),
            'family_id' => $familyId,
            'expires_at' => now()->addMinutes((int) config('jwt.refresh_ttl')),
        ]);

        return ['access_token' => $access, 'refresh_token' => $refresh];
    }

    public function rotate(string $refreshToken): array
    {
        try {
            JWTAuth::setToken($refreshToken);
            $payload = JWTAuth::getPayload();
            if ($payload->get('typ') !== 'refresh'
                || $payload->get('iss') !== config('jwt.issuer')
                || $payload->get('aud') !== config('jwt.audience')) {
                throw new RuntimeException('Refresh token không hợp lệ.');
            }
            $user = JWTAuth::authenticate();
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages(['token' => 'Phiên đăng nhập đã hết hạn hoặc không hợp lệ.']);
        }

        if (!$user || $user->status !== 'ACTIVE') {
            throw ValidationException::withMessages(['token' => 'Tài khoản không hoạt động.']);
        }

        $sid = (string) $payload->get('sid');
        $jti = (string) $payload->get('jti');
        $result = DB::transaction(function () use ($refreshToken, $sid, $jti, $user) {
            $session = RefreshSession::query()->lockForUpdate()->whereKey($sid)->first();
            if (!$session || $session->user_id !== $user->id || $session->jti !== $jti
                || !hash_equals($session->token_hash, hash('sha256', $refreshToken))) {
                throw ValidationException::withMessages(['token' => 'Refresh token không hợp lệ.']);
            }
            if ($session->revoked_at || $session->expires_at->isPast()) {
                // Return a marker so the family revocation commits before the request is rejected.
                RefreshSession::where('family_id', $session->family_id)->whereNull('revoked_at')->update(['revoked_at' => now()]);
                return ['reuse_detected' => true];
            }

            $session->update(['revoked_at' => now()]);
            $tokens = $this->issuePair($user);
            $newPayload = JWTAuth::setToken($tokens['refresh_token'])->getPayload();
            $newSession = RefreshSession::where('jti', (string) $newPayload->get('jti'))->firstOrFail();
            $newSession->update(['family_id' => $session->family_id]);
            $session->update(['replaced_by_session_id' => $newSession->id]);
            return ['user' => $user, 'tokens' => $tokens];
        });

        if (($result['reuse_detected'] ?? false) === true) {
            throw ValidationException::withMessages(['token' => 'Phát hiện refresh token đã được sử dụng. Vui lòng đăng nhập lại.']);
        }

        return $result;
    }

    public function revokeCurrent(?string $refreshToken): void
    {
        if (!$refreshToken) return;
        try {
            JWTAuth::setToken($refreshToken);
            $payload = JWTAuth::getPayload();
            if ($payload->get('typ') === 'refresh') {
                RefreshSession::whereKey((string) $payload->get('sid'))->whereNull('revoked_at')->update(['revoked_at' => now()]);
            }
        } catch (JWTException) {
            // Expired or invalid refresh cookies are still cleared by the controller.
        }
    }

    public function revokeAccess(?string $accessToken): void
    {
        if (!$accessToken) return;
        try {
            JWTAuth::setToken($accessToken);
            $payload = JWTAuth::getPayload();
            if ($payload->get('typ') === 'access') {
                RefreshSession::whereKey((string) $payload->get('sid'))->whereNull('revoked_at')->update(['revoked_at' => now()]);
            }
        } catch (\Throwable) {
            // Expired or invalid access cookies are cleared by the controller.
        }
    }

    public function revokeAll(User $user): void
    {
        RefreshSession::where('user_id', $user->id)->whereNull('revoked_at')->update(['revoked_at' => now()]);
    }

    private function assertJwtConfiguration(): void
    {
        $secret = (string) config('jwt.secret', '');
        $algorithm = (string) config('jwt.algo', '');

        if (strlen($secret) < 32) {
            throw new RuntimeException(
                'JWT_SECRET chưa được cấu hình hợp lệ. Hãy để ứng dụng tạo secret bằng `php artisan jwt:secret` hoặc khởi động lại Docker để entrypoint tự tạo.'
            );
        }

        if (!in_array($algorithm, ['HS256', 'HS384', 'HS512'], true)) {
            throw new RuntimeException('JWT_ALGO của dự án hiện cần là HS256, HS384 hoặc HS512 để dùng JWT_SECRET dạng HMAC.');
        }

        if (trim((string) config('jwt.issuer', '')) === '' || trim((string) config('jwt.audience', '')) === '') {
            throw new RuntimeException('JWT_ISSUER và JWT_AUDIENCE không được để trống.');
        }

        if ((int) config('jwt.ttl', 0) < 1 || (int) config('jwt.refresh_ttl', 0) < 1) {
            throw new RuntimeException('JWT_TTL và JWT_REFRESH_TTL phải là số phút lớn hơn 0.');
        }
    }

    private function createToken(User $user, string $type, int $ttl, ?string $sessionId): string
    {
        if ($ttl < 1) {
            throw new RuntimeException('JWT_TTL và JWT_REFRESH_TTL phải là số phút lớn hơn 0.');
        }

        JWTAuth::factory()->setTTL($ttl);
        $claims = ['typ' => $type];
        if ($sessionId) $claims['sid'] = $sessionId;
        return JWTAuth::customClaims($claims)->fromUser($user);
    }
}
