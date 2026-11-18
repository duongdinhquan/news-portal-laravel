@extends('layouts.workspace')
@section('title', 'Bài viết của tôi')
@section('content')
<div class="workspace-top"><div><h1>Bài viết của tôi</h1><p>Danh sách chỉ bao gồm bài viết do tài khoản hiện tại tạo.</p></div><a class="btn btn-primary" href="{{ route('editor.articles.create') }}">＋ Viết bài mới</a></div>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Bài viết</th><th>Chuyên mục</th><th>Trạng thái</th><th>Ngày cập nhật</th><th>Thao tác</th></tr></thead><tbody>
@forelse($articles as $article)
    @php($status = $article->workingRevision?->status ?? ($article->currentPublishedRevision ? 'PUBLISHED' : 'DRAFT'))
    <tr><td><div class="table-title">{{ $article->workingRevision?->title ?? $article->currentPublishedRevision?->title }}</div><div class="table-sub">{{ $article->slug }}</div></td><td>{{ $article->category?->name }}</td><td>@include('components.status-pill', ['status' => $status])</td><td>{{ $article->updated_at->format('d/m/Y H:i') }}</td><td><a class="btn btn-light btn-sm" href="{{ route('editor.articles.edit', $article) }}">Chỉnh sửa</a></td></tr>
@empty<tr><td colspan="5"><div class="empty-state"><h3>Chưa có bài viết</h3><p>Tạo bài viết đầu tiên của bạn.</p></div></td></tr>@endforelse
</tbody></table></div><div style="margin-top:16px">{{ $articles->links() }}</div></section>
@endsection
