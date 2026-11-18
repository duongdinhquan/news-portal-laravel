@extends('layouts.auth')
@section('title', 'Đăng nhập')
@section('content')
<h1>Chào mừng trở lại</h1><p>Đăng nhập để tiếp tục trải nghiệm Bản Tin.</p>
<form method="post" action="{{ route('login.store') }}">@csrf
    <div class="form-group"><label class="form-label" for="email">Địa chỉ email</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="ban@example.com" autocomplete="username" required></div>
    <div class="form-group"><label class="form-label" for="password">Mật khẩu</label><input class="form-control" id="password" name="password" type="password" placeholder="Nhập mật khẩu" autocomplete="current-password" required></div>
    <button class="btn btn-primary" type="submit" style="width:100%;padding:12px">Đăng nhập</button>
</form>
<div style="margin-top:19px;padding:13px;background:#f8f9fb;border-radius:8px"><strong style="font-size:11px">Tài khoản demo để kiểm thử</strong><div class="form-hint" style="margin-top:6px">Admin: admin@news.local · Admin@12345<br>Editor: editor1@news.local · Editor@12345<br>Độc giả: reader1@news.local · User@12345</div></div>
<div class="auth-footer">Chưa có tài khoản? <a href="{{ route('register') }}" style="color:var(--brand);font-weight:800">Đăng ký độc giả</a></div>
@endsection
