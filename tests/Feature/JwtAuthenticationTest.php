<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class JwtAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('k', 32)),
            'jwt.secret' => str_repeat('jwt-test-secret-', 5),
            'jwt.algo' => 'HS256',
            'jwt.issuer' => 'news-portal-test',
            'jwt.audience' => 'news-portal-test-browser',
        ]);
    }

    public function test_jwt_middleware_sets_both_request_user_and_laravel_auth_guard_user(): void
    {
        $admin = User::create([
            'full_name' => 'Admin Test',
            'display_name' => 'Admin Test',
            'email' => 'admin-test@example.local',
            'password' => '123456',
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
        ]);

        Route::get('/__test/jwt-user-context', function () {
            return response()->json([
                'request_user_id' => request()->user()?->id,
                'auth_user_id' => auth()->user()?->id,
                'policy_allowed' => Gate::allows('manage', User::class),
            ]);
        })->middleware('jwt.cookie');

        $tokens = app(AuthService::class)->issuePair($admin);

        $this->withHeader('Authorization', 'Bearer '.$tokens['access_token'])
            ->getJson('/__test/jwt-user-context')
            ->assertOk()
            ->assertJson([
                'request_user_id' => $admin->id,
                'auth_user_id' => $admin->id,
                'policy_allowed' => true,
            ]);
    }

    public function test_role_middleware_returns_forbidden_for_an_authenticated_user_without_role(): void
    {
        $reader = User::create([
            'full_name' => 'Reader Test',
            'display_name' => 'Reader Test',
            'email' => 'reader-test@example.local',
            'password' => '123456',
            'role' => 'USER',
            'status' => 'ACTIVE',
        ]);

        Route::get('/__test/admin-only', fn () => response()->noContent())
            ->middleware(['jwt.cookie', 'user.active', 'role:ADMIN']);

        $tokens = app(AuthService::class)->issuePair($reader);

        $this->withHeader('Authorization', 'Bearer '.$tokens['access_token'])
            ->getJson('/__test/admin-only')
            ->assertForbidden();
    }

    public function test_jwt_middleware_authenticates_from_the_access_cookie(): void
    {
        $admin = User::create([
            'full_name' => 'Admin Cookie Test',
            'display_name' => 'Admin Cookie Test',
            'email' => 'admin-cookie-test@example.local',
            'password' => '123456',
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
        ]);

        $tokens = app(AuthService::class)->issuePair($admin);

        Route::get('/__test/jwt-cookie-user', fn () => response()->json([
            'user_id' => request()->user()?->id,
        ]))->middleware(['web', 'jwt.cookie']);

        $this->withCredentials()->withCookie('access_token', $tokens['access_token'])
            ->getJson('/__test/jwt-cookie-user')
            ->assertOk()
            ->assertJson(['user_id' => $admin->id]);
    }
}
