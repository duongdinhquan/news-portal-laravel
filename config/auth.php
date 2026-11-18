<?php

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],
    'guards' => [
        // khi goi $this->user() o request no dua vao session de lay thong tin user tu session, neu khong co session thi tra ve null
        // co che coookies - session  (Stateful)
        // Server dùng mã cookie này tìm vào kho lưu trữ Session để đọc ra User ID đã được ghi lại từ lúc đăng nhập
        'web' => ['driver' => 'session', 'provider' => 'users'], 
        'api' => ['driver' => 'jwt', 'provider' => 'users'],
    ],
    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => App\Models\User::class], //  dung de Auth::attempt() xac dinh xem query o table nao
    ],
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
