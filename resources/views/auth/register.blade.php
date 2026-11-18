@extends('layouts.auth')
@section('title', 'Tạo tài khoản độc giả')
@section('content')
<h1>Tạo tài khoản</h1><p>Tham gia cộng đồng độc giả của Bản Tin.</p>
<form method="post" action="{{ route('register.store') }}">@csrf
    <div class="form-group"><label class="form-label" for="full_name">Họ và tên</label><input class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" autocomplete="name" required></div>
    <div class="form-group"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></div>
    <div class="form-group"><label class="form-label" for="password">Mật khẩu</label><input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required><div class="form-hint">Không bắt buộc kết hợp chữ hoa, chữ số hoặc ký tự đặc biệt.</div></div>
    <div class="form-group"><label class="form-label" for="password_confirmation">Nhập lại mật khẩu</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
    <button class="btn btn-primary" type="submit" style="width:100%;padding:12px">Tạo tài khoản độc giả</button>
</form>
<div class="auth-footer">Đã có tài khoản? <a href="{{ route('login') }}" style="color:var(--brand);font-weight:800">Đăng nhập</a></div>
@endsection
