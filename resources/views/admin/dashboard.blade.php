@extends('layouts.workspace')
@section('title', 'Quản trị hệ thống')
@section('content')
<div class="workspace-top"><div><h1>Tổng quan hệ thống</h1><p>Xin chào {{ request()->user()->full_name }}. Đây là tình hình hoạt động của tòa soạn.</p></div><a href="{{ route('admin.articles.queue') }}" class="btn btn-primary">Xem hàng đợi duyệt →</a></div>
<div class="stat-grid">
    <div class="stat-card"><span>Tổng tài khoản</span><strong>{{ $stats['users'] }}</strong></div>
    <div class="stat-card"><span>Editor</span><strong>{{ $stats['editors'] }}</strong></div>
    <div class="stat-card"><span>Tổng bài viết</span><strong>{{ $stats['articles'] }}</strong></div>
    <div class="stat-card"><span>Bài chờ duyệt</span><strong>{{ $stats['pending_articles'] }}</strong></div>
</div>
<div class="stat-grid" style="grid-template-columns:repeat(2,minmax(0,1fr));max-width:510px"><div class="stat-card"><span>Bình luận chờ duyệt</span><strong>{{ $stats['pending_comments'] }}</strong></div><div class="stat-card"><span>Chuyên mục</span><strong>{{ $stats['categories'] }}</strong></div></div>
<section class="panel"><div class="panel-head"><h2>Bài viết chờ duyệt</h2><a href="{{ route('admin.articles.queue') }}" class="section-more">Mở hàng đợi →</a></div>
@if($pending->isNotEmpty())<div class="table-wrap"><table><thead><tr><th>Bài viết</th><th>Tác giả</th><th>Chuyên mục</th><th>Gửi lúc</th><th></th></tr></thead><tbody>@foreach($pending as $article)<tr><td><div class="table-title">{{ $article->workingRevision?->title }}</div><div class="table-sub">{{ $article->workingRevision?->excerpt }}</div></td><td>{{ $article->author?->full_name }}</td><td>{{ $article->category?->name }}</td><td>{{ $article->workingRevision?->submitted_at?->format('d/m H:i') }}</td><td><a class="btn btn-light btn-sm" href="{{ route('admin.articles.queue') }}">Duyệt</a></td></tr>@endforeach</tbody></table></div>
@else<div class="empty-state"><h3>Không có bài đang chờ duyệt</h3><p>Các bài được Editor gửi lên sẽ xuất hiện tại đây.</p></div>@endif
</section>
@endsection
