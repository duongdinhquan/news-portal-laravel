<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('k', 32)),
            'jwt.secret' => str_repeat('local-test-secret-', 5),
            'jwt.algo' => 'HS256',
        ]);
    }

    public function test_public_registration_always_creates_user_role_even_if_role_is_supplied(): void
    {
        $response = $this->post('/dang-ky', [
            'full_name' => 'Người đọc thử nghiệm',
            'email' => 'Reader@Example.Local ',
            'password' => '123',
            'password_confirmation' => '123',
            'role' => 'ADMIN',
        ])->assertRedirect('/');

        $cookieNames = array_map(
            static fn ($cookie) => $cookie->getName(),
            $response->headers->getCookies(),
        );
        $this->assertContains('access_token', $cookieNames);
        $this->assertContains('refresh_token', $cookieNames);

        $this->assertDatabaseHas('users', [
            'email' => 'reader@example.local',
            'role' => 'USER',
            'status' => 'ACTIVE',
        ]);

        $user = \App\Models\User::where('email', 'reader@example.local')->firstOrFail();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('123', $user->password));
    }
    public function test_user_can_log_in_with_a_simple_password(): void
    {
        $this->post('/dang-ky', [
            'full_name' => 'Độc giả đăng nhập',
            'email' => 'simple-reader@example.local',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertRedirect('/');

        $response = $this->post('/dang-nhap', [
            'email' => 'SIMPLE-READER@example.local ',
            'password' => '123',
        ])->assertRedirect('/');

        $cookieNames = array_map(
            static fn ($cookie) => $cookie->getName(),
            $response->headers->getCookies(),
        );
        $this->assertContains('access_token', $cookieNames);
        $this->assertContains('refresh_token', $cookieNames);
    }

}
