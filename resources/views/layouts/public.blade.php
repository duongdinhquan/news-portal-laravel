<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Bản Tin - cập nhật tin tức, góc nhìn và những câu chuyện đáng chú ý mỗi ngày.">
    <title>@yield('title', 'Bản Tin – Tin tức mỗi ngày')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
    <div class="topline"><div class="site-width topline-inner"><span>{{ now()->locale('vi')->translatedFormat('l, d/m/Y') }} <span style="opacity:.45">·</span> Tin tức chọn lọc mỗi ngày</span><span><a href="{{ request()->cookie(config('jwt.access_cookie', 'access_token')) ? route('account.profile') : route('login') }}">{{ request()->cookie(config('jwt.access_cookie', 'access_token')) ? 'Tài khoản' : 'Khu vực độc giả' }}</a></span></div></div>
    <header class="site-header">
        <div class="site-width brand-row">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark">B</span>
                <span><span class="brand-name">Bản Tin</span><span class="brand-tag">Đọc để hiểu · Biết để kết nối</span></span>
            </a>
            <form class="header-search" action="{{ route('search') }}" method="get">
                <input name="q" type="search" placeholder="Tìm kiếm tin tức, chủ đề..." value="{{ request('q') }}" aria-label="Từ khóa tìm kiếm">
                <button type="submit">Tìm kiếm</button>
            </form>
            <div class="header-tools">
                @if(request()->cookie(config('jwt.access_cookie', 'access_token')))
                    <a class="text-link" href="{{ route('account.profile') }}">Tài khoản</a>
                    <form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-light btn-sm" type="submit">Đăng xuất</button></form>
                @else
                    <a class="text-link" href="{{ route('login') }}">Đăng nhập</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Đăng ký</a>
                @endif
            </div>
        </div>
        <nav class="category-nav"><div class="site-width nav-inner">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">TRANG CHỦ</a>
            @foreach(($navCategories ?? collect()) as $navCategory)
                <a href="{{ route('categories.show', $navCategory->slug) }}" class="{{ request()->route('slug') === $navCategory->slug ? 'active' : '' }}">{{ mb_strtoupper($navCategory->name) }}</a>
            @endforeach
            <a class="nav-hot" href="{{ route('search', ['q' => 'công nghệ']) }}">GÓC NHÌN</a>
        </div></nav>
    </header>
    @include('components.flash')
    <main>@yield('content')</main>
    <footer class="site-footer">
        <div class="site-width">
            <div class="footer-grid">
                <div><h3>Bản Tin</h3><p>Một không gian đọc tin tức tiếng Việt, nơi thông tin được trình bày rõ ràng, dễ tiếp cận và khuyến khích góc nhìn đa chiều.</p><p style="color:#93a7c0">Sản phẩm mẫu phục vụ học tập và kiểm thử chức năng.</p></div>
                <div><h3>Chuyên mục</h3><div class="footer-links">@foreach(($navCategories ?? collect())->take(6) as $item)<a href="{{ route('categories.show', $item->slug) }}">{{ $item->name }}</a>@endforeach</div></div>
                <div><h3>Tòa soạn</h3><div class="footer-links"><a href="{{ route('login') }}">Đăng nhập hệ thống</a><a href="{{ route('register') }}">Tạo tài khoản độc giả</a><a href="{{ route('search') }}">Tìm kiếm bài viết</a></div></div>
            </div>
            <div class="footer-bottom">© {{ date('Y') }} Bản Tin. Nội dung minh họa được tạo để kiểm thử ứng dụng.</div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
