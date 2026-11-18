@extends('layouts.workspace')
@section('title', 'Bảng điều khiển Editor')
@section('content')
<div class="workspace-top"><div><h1>Chào {{ request()->user()->display_name ?? request()->user()->full_name }}</h1><p>Quản lý nội dung và theo dõi trạng thái bài viết của bạn.</p></div><a class="btn btn-primary" href="{{ route('editor.articles.create') }}">＋ Viết bài mới</a></div>
<div class="stat-grid">
    <div class="stat-card"><span>Bản nháp</span><strong>{{ $counts['DRAFT'] }}</strong></div>
    <div class="stat-card"><span>Chờ duyệt</span><strong>{{ $counts['PENDING_REVIEW'] }}</strong></div>
    <div class="stat-card"><span>Đã xuất bản</span><strong>{{ $counts['PUBLISHED'] }}</strong></div>
    <div class="stat-card"><span>Bị từ chối</span><strong>{{ $counts['REJECTED'] }}</strong></div>
</div>
<section class="panel"><div class="panel-head"><h2>Bài viết gần đây</h2><a href="{{ route('editor.articles.index') }}" class="section-more">Tất cả bài viết →</a></div>
    @if($articles->isNotEmpty())<div class="table-wrap"><table><thead><tr><th>Tiêu đề</th><th>Chuyên mục</th><th>Trạng thái</th><th>Cập nhật</th><th></th></tr></thead><tbody>
        @foreach($articles as $article)
            @php($status = $article->workingRevision?->status ?? ($article->currentPublishedRevision ? 'PUBLISHED' : 'DRAFT'))
            <tr><td><div class="table-title">{{ $article->workingRevision?->title ?? $article->currentPublishedRevision?->title }}</div><div class="table-sub">{{ $article->slug }}</div></td><td>{{ $article->category?->name }}</td><td>@include('components.status-pill', ['status' => $status])</td><td>{{ $article->updated_at->format('d/m/Y H:i') }}</td><td><a class="btn btn-light btn-sm" href="{{ route('editor.articles.edit', $article) }}">Mở</a></td></tr>
        @endforeach
    </tbody></table></div>@else<div class="empty-state"><h3>Chưa có bài viết nào</h3><p>Bắt đầu bằng cách tạo một bản nháp mới.</p><a class="btn btn-primary" href="{{ route('editor.articles.create') }}">Tạo bài viết</a></div>@endif
</section>
@endsection
