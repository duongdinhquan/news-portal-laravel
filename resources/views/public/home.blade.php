@extends('layouts.public')
@section('title', 'Bản Tin – Tin tức, góc nhìn và cuộc sống')
@section('content')
<div class="page-shell"><div class="site-width">
    @php($lead = $featured->first())
    @if($lead)
        @php($leadRevision = $lead->currentPublishedRevision)
        <div class="headline-grid">
            <a class="headline-main" href="{{ route('articles.show', $lead->slug) }}">
                <img class="cover" src="{{ $leadRevision?->coverMedia?->url ?? asset('images/covers/'.($lead->category?->slug ?? 'default').'.svg') }}" alt="{{ $leadRevision?->title }}">
                <div class="headline-overlay"></div>
                <div class="headline-content">
                    <span class="category-chip">{{ $lead->category?->name ?? 'Nổi bật' }}</span>
                    <h1>{{ $leadRevision?->title }}</h1>
                    <p>{{ $leadRevision?->excerpt }}</p>
                    <div class="meta"><span>{{ $lead->author?->display_name ?? $lead->author?->full_name }}</span><span>·</span><span>{{ $lead->published_at?->format('d/m/Y') }}</span></div>
                </div>
            </a>
            <div class="headline-side">
                @foreach($featured->skip(1)->take(3) as $story)
                    @php($revision = $story->currentPublishedRevision)
                    <a class="side-story" href="{{ route('articles.show', $story->slug) }}">
                        <img src="{{ $revision?->coverMedia?->url ?? asset('images/covers/'.($story->category?->slug ?? 'default').'.svg') }}" alt="{{ $revision?->title }}">
                        <div><span class="section-eyebrow">{{ $story->category?->name }}</span><h3>{{ $revision?->title }}</h3><p>{{ $story->published_at?->format('d/m/Y') }} · {{ $story->author?->display_name ?? $story->author?->full_name }}</p></div>
                    </a>
                @endforeach
                @if($featured->count() < 2)<div class="empty-state" style="margin:15px">Các tin nổi bật khác sẽ xuất hiện tại đây.</div>@endif
            </div>
        </div>
    @else
        <div class="empty-state"><h3>Chào mừng đến với Bản Tin</h3><p>Dữ liệu bài viết chưa được nạp. Hãy chạy lệnh migrate --seed theo README.</p></div>
    @endif

    <div class="content-layout">
        <section>
            <div class="section-head"><div><div class="section-eyebrow">Cập nhật liên tục</div><h2 class="section-title">Tin mới nhất</h2></div><a class="section-more" href="{{ route('search') }}">Khám phá tất cả →</a></div>
            @if($latest->isNotEmpty())
                <div class="article-grid">@foreach($latest->skip($lead ? 1 : 0)->take(9) as $article)@include('components.article-card', ['article' => $article])@endforeach</div>
            @else
                <div class="empty-state"><h3>Chưa có bài viết</h3><p>Hãy đăng nhập bằng tài khoản Editor để tạo bài viết đầu tiên.</p></div>
            @endif
        </section>
        <aside>
            <div class="sidebar-box"><h3 class="sidebar-title">ĐỌC NHIỀU</h3>
                @foreach($latest->take(5) as $index => $story)
                    <a class="trending-item" href="{{ route('articles.show', $story->slug) }}"><span class="trend-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span><div><h4>{{ $story->currentPublishedRevision?->title }}</h4><div class="meta">{{ $story->category?->name }} · {{ $story->published_at?->format('d/m') }}</div></div></a>
                @endforeach
            </div>
            <div class="sidebar-box"><h3 class="sidebar-title">BẢN TIN MỖI NGÀY</h3><p style="font-size:12px;color:var(--muted);margin-top:0">Tạo tài khoản độc giả để tham gia thảo luận và theo dõi các bài viết bạn quan tâm.</p><a class="btn btn-primary" style="width:100%" href="{{ route('register') }}">Tạo tài khoản miễn phí</a></div>
        </aside>
    </div>

    <div class="category-strip">
        @foreach($categoryBlocks->take(6) as $category)
            @if($category->articles->isNotEmpty())
                <section class="category-panel">
                    <div class="category-panel-head"><h3>{{ $category->name }}</h3><a class="section-more" href="{{ route('categories.show', $category->slug) }}">Xem thêm →</a></div>
                    @foreach($category->articles->take(3) as $story)
                        @php($revision = $story->currentPublishedRevision)
                        <a class="mini-story" href="{{ route('articles.show', $story->slug) }}"><img src="{{ $revision?->coverMedia?->url ?? asset('images/covers/'.$category->slug.'.svg') }}" alt="{{ $revision?->title }}"><div><h4>{{ $revision?->title }}</h4><span>{{ $story->published_at?->format('d/m/Y') }}</span></div></a>
                    @endforeach
                </section>
            @endif
        @endforeach
    </div>
</div></div>
@endsection
