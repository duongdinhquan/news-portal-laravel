<?php

return [
    'secret' => env('JWT_SECRET'),
    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],
    'algo' => env('JWT_ALGO', 'HS256'),
    'issuer' => env('JWT_ISSUER', 'news-portal'),
    'audience' => env('JWT_AUDIENCE', 'news-portal-browser'),
    'access_cookie' => env('JWT_ACCESS_COOKIE', 'access_token'),
    'refresh_cookie' => env('JWT_REFRESH_COOKIE', 'refresh_token'),
    'cookie_secure' => filter_var(env('JWT_COOKIE_SECURE', false), FILTER_VALIDATE_BOOLEAN),
    'cookie_same_site' => env('JWT_COOKIE_SAME_SITE', 'lax'),
    'ttl' => (int) env('JWT_TTL', 15),
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 10080),
    'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti'],
    'persistent_claims' => [],
    'lock_subject' => true,
    'leeway' => (int) env('JWT_LEEWAY', 0),
    'blacklist_enabled' => filter_var(env('JWT_BLACKLIST_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'blacklist_grace_period' => (int) env('JWT_BLACKLIST_GRACE_PERIOD', 0),
    'decrypt_cookies' => filter_var(env('JWT_DECRYPT_COOKIES', false), FILTER_VALIDATE_BOOLEAN),
    'providers' => [
        'jwt' => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,
        'auth' => Tymon\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,
    ],
];
