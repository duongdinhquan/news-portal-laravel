@extends('layouts.workspace')
@section('title', 'Hàng đợi duyệt bài')
@section('content')
<div class="workspace-top"><div><h1>Hàng đợi duyệt</h1><p>Xem nội dung revision đang chờ và quyết định xuất bản hoặc từ chối.</p></div></div>
@forelse($articles as $article)
    @php($revision = $article->workingRevision)
    <section class="panel">
        <div class="panel-head"><div><span class="section-eyebrow">{{ $article->category?->name }} · {{ $revision?->submitted_at?->format('d/m/Y H:i') }}</span><h2 style="font-family:Georgia,serif;font-size:21px;margin:4px 0">{{ $revision?->title }}</h2><div class="form-hint">Tác giả: {{ $article->author?->full_name }} · Revision {{ $revision?->revision_number }}</div></div>@include('components.status-pill', ['status' => $revision?->status ?? 'PENDING_REVIEW'])</div>
        <p style="font-size:13px;color:#475467">{{ $revision?->excerpt }}</p>
        <details style="margin:12px 0"><summary style="font-size:12px;color:var(--brand);font-weight:800;cursor:pointer">Xem nội dung đã sanitize</summary><div class="article-body" style="font-size:15px">{!! $revision?->content_html !!}</div></details>
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;border-top:1px solid var(--line);padding-top:15px">
            <form method="post" action="{{ route('admin.articles.publish', $article) }}">@csrf<button class="btn btn-primary" type="submit" data-confirm="Duyệt và xuất bản revision này?">✓ Duyệt & xuất bản</button></form>
            <form method="post" action="{{ route('admin.articles.reject', $article) }}" style="display:flex;gap:7px;align-items:end;flex:1;min-width:260px">@csrf<div style="flex:1"><label class="form-label">Lý do từ chối (bắt buộc)</label><input class="form-control" name="rejection_reason" minlength="5" maxlength="2000" placeholder="Nêu rõ nội dung cần chỉnh sửa..." required></div><button class="btn btn-danger" type="submit">Từ chối</button></form>
        </div>
    </section>
@empty
    <div class="empty-state"><h3>Hàng đợi đang trống</h3><p>Chưa có bài viết nào được gửi để duyệt.</p></div>
@endforelse
<div style="margin-top:18px">{{ $articles->links() }}</div>
@endsection
