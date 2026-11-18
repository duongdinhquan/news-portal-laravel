<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Tài khoản') · Bản Tin</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<div class="topline"><div class="site-width topline-inner"><span>Không gian tài khoản Bản Tin</span><a href="{{ route('home') }}">← Về trang chủ</a></div></div>
<main class="auth-page"><section class="auth-card"><a class="brand" href="{{ route('home') }}" style="margin-bottom:25px"><span class="brand-mark">B</span><span><span class="brand-name">Bản Tin</span><span class="brand-tag">Đọc để hiểu</span></span></a>@include('components.flash')@yield('content')</section></main>
</body></html>
