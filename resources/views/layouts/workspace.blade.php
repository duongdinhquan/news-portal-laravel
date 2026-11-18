<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Bảng điều khiển') · Bản Tin</title>@vite(['resources/css/app.css','resources/js/app.js'])@stack('head')</head>
<body>
<div class="topline"><div class="site-width topline-inner"><span>HỆ THỐNG QUẢN TRỊ BẢN TIN</span><span>{{ request()->user()?->full_name }} · {{ request()->user()?->role }}</span></div></div>
<header class="site-header"><div class="site-width brand-row" style="padding:13px 0"><a class="brand" href="{{ route('home') }}"><span class="brand-mark">B</span><span><span class="brand-name">Bản Tin</span><span class="brand-tag">Newsroom workspace</span></span></a><div class="header-tools"><a class="text-link" href="{{ route('home') }}">Xem website</a><form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-light btn-sm" type="submit">Đăng xuất</button></form></div></div></header>
@include('components.flash')
<div class="workspace"><div class="site-width workspace-grid">
    <aside class="workspace-sidebar">
        <div class="workspace-label">{{ request()->user()?->role === 'ADMIN' ? 'Administration' : 'Editorial desk' }}</div>
        <nav class="workspace-nav">
            @if(request()->user()?->role === 'ADMIN')
                <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="nav-icon">▦</span>Tổng quan</a>
                <a class="{{ request()->routeIs('admin.articles.queue') ? 'active' : '' }}" href="{{ route('admin.articles.queue') }}"><span class="nav-icon">◷</span>Chờ duyệt</a>
                <a class="{{ request()->routeIs('admin.articles.*') && !request()->routeIs('admin.articles.queue') ? 'active' : '' }}" href="{{ route('admin.articles.index') }}"><span class="nav-icon">▤</span>Bài viết</a>
                <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><span class="nav-icon">♙</span>Người dùng</a>
                <a class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><span class="nav-icon">▧</span>Chuyên mục</a>
                <a class="{{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="{{ route('admin.comments.index') }}"><span class="nav-icon">▱</span>Bình luận</a>
                <a class="{{ request()->routeIs('admin.media.*') ? 'active' : '' }}" href="{{ route('admin.media.index') }}"><span class="nav-icon">▣</span>Media</a>
                <a class="{{ request()->routeIs('admin.audit.*') ? 'active' : '' }}" href="{{ route('admin.audit.index') }}"><span class="nav-icon">◉</span>Audit log</a>
            @elseif(request()->user()?->role === 'EDITOR')
                <a class="{{ request()->routeIs('editor.dashboard') ? 'active' : '' }}" href="{{ route('editor.dashboard') }}"><span class="nav-icon">▦</span>Tổng quan</a>
                <a class="{{ request()->routeIs('editor.articles.*') ? 'active' : '' }}" href="{{ route('editor.articles.index') }}"><span class="nav-icon">▤</span>Bài viết của tôi</a>
                <a href="{{ route('editor.articles.create') }}"><span class="nav-icon">＋</span>Viết bài mới</a>
                <a class="{{ request()->routeIs('editor.media.*') ? 'active' : '' }}" href="{{ route('editor.media.index') }}"><span class="nav-icon">▣</span>Thư viện media</a>
            @else
                <a href="{{ route('home') }}"><span class="nav-icon">⌂</span>Trang chủ</a>
            @endif
            <a href="{{ route('account.profile') }}"><span class="nav-icon">⚙</span>Tài khoản</a>
        </nav>
    </aside>
    <main class="workspace-main">@yield('content')</main>
</div></div>
@stack('scripts')
</body></html>
