@extends('layouts.workspace')
@section('title', 'Quản lý bài viết')
@section('content')
<div class="workspace-top"><div><h1>Quản lý bài viết</h1><p>Danh sách toàn bộ bài viết và revision trong hệ thống.</p></div><a class="btn btn-light" href="{{ route('admin.articles.queue') }}">Hàng đợi duyệt</a></div>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Tiêu đề</th><th>Tác giả</th><th>Chuyên mục</th><th>Trạng thái</th><th>Cập nhật</th><th>Thao tác</th></tr></thead><tbody>
@forelse($articles as $article)
    @php($status = $article->workingRevision?->status ?? ($article->currentPublishedRevision ? 'PUBLISHED' : 'DRAFT'))
    <tr><td><div class="table-title">{{ $article->workingRevision?->title ?? $article->currentPublishedRevision?->title }}</div><div class="table-sub">{{ $article->slug }}</div></td><td>{{ $article->author?->full_name }}</td><td>{{ $article->category?->name }}</td><td>@include('components.status-pill', ['status' => $status])</td><td>{{ $article->updated_at->format('d/m/Y') }}</td><td>@if($article->current_published_revision_id && !$article->archived_at)<form method="post" action="{{ route('admin.articles.archive', $article) }}">@csrf<button class="btn btn-danger btn-sm" data-confirm="Lưu trữ bài viết này?">Lưu trữ</button></form>@elseif($article->archived_at)<span class="status-pill">Đã lưu trữ</span>@else<span class="table-sub">Chưa xuất bản</span>@endif</td></tr>
@empty<tr><td colspan="6">Chưa có bài viết.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:16px">{{ $articles->links() }}</div></section>
@endsection
