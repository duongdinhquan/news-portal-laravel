@extends('layouts.workspace')
@section('title', 'Quản lý người dùng')
@section('content')
<div class="workspace-top"><div><h1>Người dùng</h1><p>Quản lý tài khoản, vai trò và trạng thái truy cập.</p></div></div>
<section class="panel"><h2 style="font-size:14px;margin:0 0 15px">Tạo tài khoản Editor</h2>
<form method="post" action="{{ route('admin.users.editor.store') }}" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:9px;align-items:end">@csrf
    <div class="form-group" style="margin:0"><label class="form-label">Họ tên</label><input class="form-control" name="full_name" required minlength="2"></div>
    <div class="form-group" style="margin:0"><label class="form-label">Email</label><input class="form-control" name="email" type="email" required></div>
    <div class="form-group" style="margin:0"><label class="form-label">Mật khẩu</label><input class="form-control" name="password" type="password" required minlength="10"></div>
    <button class="btn btn-primary" type="submit">Tạo Editor</button>
</form></section>
<section class="panel"><div class="panel-head"><h2>Danh sách tài khoản</h2><span class="meta">{{ $users->total() }} tài khoản</span></div><div class="table-wrap"><table><thead><tr><th>Người dùng</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th>Quản lý vai trò</th><th>Trạng thái tài khoản</th></tr></thead><tbody>
@foreach($users as $user)
<tr><td><div class="table-title">{{ $user->full_name }}</div><div class="table-sub">{{ $user->email }}</div></td><td><span class="status-pill">{{ $user->role }}</span></td><td>@include('components.status-pill', ['status' => $user->status])</td><td>{{ $user->created_at->format('d/m/Y') }}</td>
<td>@if($user->id !== request()->user()->id)<form class="inline-form" method="post" action="{{ route('admin.users.role', $user) }}">@csrf @method('patch')<select class="form-control" name="role" style="padding:6px;min-width:100px"><option value="USER" @selected($user->role==='USER')>USER</option><option value="EDITOR" @selected($user->role==='EDITOR')>EDITOR</option><option value="ADMIN" @selected($user->role==='ADMIN')>ADMIN</option></select><button class="btn btn-light btn-sm">Lưu</button></form>@else<span class="table-sub">Tài khoản hiện tại</span>@endif</td>
<td>@if($user->id !== request()->user()->id)<form method="post" action="{{ route('admin.users.status', $user) }}">@csrf @method('patch')<input type="hidden" name="status" value="{{ $user->status==='ACTIVE' ? 'DISABLED' : 'ACTIVE' }}"><button class="btn {{ $user->status==='ACTIVE' ? 'btn-danger' : 'btn-light' }} btn-sm" data-confirm="Thay đổi trạng thái tài khoản này?">{{ $user->status==='ACTIVE' ? 'Khóa tài khoản' : 'Mở khóa' }}</button></form>@else<span class="table-sub">Không thể tự khóa</span>@endif</td></tr>
@endforeach
</tbody></table></div><div style="margin-top:16px">{{ $users->links() }}</div></section>
@endsection
