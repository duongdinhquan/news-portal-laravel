@extends('layouts.public')
@php
    $revision = $article->currentPublishedRevision;
    $cover = $revision?->coverMedia?->url ?? asset('images/covers/'.($article->category?->slug ?? 'default').'.svg');
@endphp
@section('title', $revision->title.' – Bản Tin')
@section('content')
<div class="page-shell"><div class="site-width">
    <div class="article-cover-wrap" style="margin-bottom:24px"><img src="{{ $cover }}" alt="{{ $revision->title }}"></div>
    <article class="article-detail">
        <a class="category-chip" href="{{ route('categories.show', $article->category->slug) }}">{{ $article->category->name }}</a>
        <h1>{{ $revision->title }}</h1>
        <p class="dek">{{ $revision->excerpt }}</p>
        <div class="byline"><span class="avatar">{{ mb_substr($article->author?->display_name ?? $article->author?->full_name ?? 'B', 0, 1) }}</span><span><strong style="color:#344054">{{ $article->author?->display_name ?? $article->author?->full_name }}</strong><br>Đăng ngày {{ $article->published_at?->format('d/m/Y · H:i') }}</span></div>
        <div class="article-body">{!! $revision->content_html !!}</div>
        <div style="border-top:1px solid var(--line);padding-top:18px;margin-top:25px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap"><span class="meta">Chuyên mục: {{ $article->category->name }}</span><a class="section-more" href="{{ route('categories.show', $article->category->slug) }}">Đọc thêm trong chuyên mục →</a></div>
    </article>

    <section id="comments" class="comment-box" style="max-width:820px;margin:25px auto 0">
        <div class="panel-head"><h2>Ý kiến độc giả</h2><span class="meta">{{ $comments->total() }} bình luận đã duyệt</span></div>
        @if(request()->cookie(config('jwt.access_cookie', 'access_token')))
            <p class="form-hint">Bình luận mới sẽ được gửi đến Admin để kiểm duyệt trước khi hiển thị. Chức năng này chỉ dành cho tài khoản USER.</p>
        @else
            <p class="form-hint">Bình luận chỉ dành cho tài khoản USER đã đăng nhập. <a href="{{ route('login') }}" class="text-link">Đăng nhập tại đây</a>.</p>
        @endif
        @if($comments->isNotEmpty())
            @foreach($comments as $comment)
                <div class="comment-row"><div style="display:flex;justify-content:space-between;gap:10px"><strong style="font-size:12px">{{ $comment->user?->display_name ?? $comment->user?->full_name }}</strong><span class="meta">{{ $comment->created_at->format('d/m/Y H:i') }}</span></div><p style="font-size:13px;margin:7px 0 0;color:#475467">{{ $comment->content }}</p></div>
            @endforeach
            <div style="margin-top:15px">{{ $comments->links() }}</div>
        @else
            <p class="meta">Chưa có bình luận nào được duyệt. Hãy là người đầu tiên chia sẻ góc nhìn.</p>
        @endif
        @if(request()->cookie(config('jwt.access_cookie', 'access_token')))
            <div style="border-top:1px solid var(--line);padding-top:18px;margin-top:16px">
                <h3 style="font-size:14px;margin:0 0 10px">Gửi bình luận</h3>
                <form method="post" action="{{ route('comments.store', $article->slug) }}">@csrf
                    <textarea class="form-control" name="content" rows="3" placeholder="Chia sẻ ý kiến của bạn..." required minlength="3" maxlength="2000"></textarea>
                    <div style="display:flex;justify-content:flex-end;align-items:center;gap:10px;margin-top:10px"><button class="btn btn-primary" type="submit">Gửi bình luận</button></div>
                </form>
            </div>
        @endif
    </section>

    @if($related->isNotEmpty())<section style="max-width:1100px;margin:42px auto 0"><div class="section-head"><h2 class="section-title">Có thể bạn quan tâm</h2></div><div class="article-grid">@foreach($related as $story)@include('components.article-card', ['article' => $story])@endforeach</div></section>@endif
</div></div>
@endsection
