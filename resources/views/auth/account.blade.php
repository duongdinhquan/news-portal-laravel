@extends('layouts.workspace')
@section('title', 'Tài khoản')
@section('content')
<div class="workspace-top"><div><h1>Tài khoản của tôi</h1><p>Thông tin hồ sơ và thiết lập bảo mật.</p></div></div>
<div class="two-col">
    <section class="panel"><h2 style="font-size:15px;margin:0 0 16px">Thông tin hồ sơ</h2>
        <div class="form-group"><label class="form-label">Họ và tên</label><div class="form-control" style="background:#f8f9fb">{{ $user->full_name }}</div></div>
        <div class="form-group"><label class="form-label">Email</label><div class="form-control" style="background:#f8f9fb">{{ $user->email }}</div></div>
        <div class="form-group"><label class="form-label">Vai trò</label><div><span class="status-pill">{{ $user->role }}</span></div></div>
        <div class="form-group"><label class="form-label">Trạng thái</label><div>@include('components.status-pill', ['status' => $user->status])</div></div>
        <div class="form-group"><label class="form-label">Đăng nhập gần nhất</label><div class="form-hint">{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Chưa có dữ liệu' }}</div></div>
    </section>
    <section class="panel"><h2 style="font-size:15px;margin:0 0 16px">Đổi mật khẩu</h2>
        <form method="post" action="{{ route('account.password') }}">@csrf
            <div class="form-group"><label class="form-label">Mật khẩu hiện tại</label><input class="form-control" name="current_password" type="password" required autocomplete="current-password"></div>
            <div class="form-group"><label class="form-label">Mật khẩu mới</label><input class="form-control" name="password" type="password" required minlength="10" autocomplete="new-password"></div>
            <div class="form-group"><label class="form-label">Xác nhận mật khẩu mới</label><input class="form-control" name="password_confirmation" type="password" required autocomplete="new-password"></div>
            <button class="btn btn-primary" type="submit">Cập nhật mật khẩu</button>
        </form>
        <hr style="border:0;border-top:1px solid var(--line);margin:22px 0">
        <form method="post" action="{{ route('auth.logout-all') }}">@csrf<button class="btn btn-danger" type="submit" data-confirm="Đăng xuất khỏi tất cả phiên đang hoạt động?">Đăng xuất khỏi tất cả thiết bị</button></form>
    </section>
</div>
@endsection
