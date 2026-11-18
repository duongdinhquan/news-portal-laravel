@php
    $revision = $article->currentPublishedRevision;
    $cover = $revision?->coverMedia?->url ?? asset('images/covers/'.($article->category?->slug ?? 'default').'.svg');
@endphp
<article class="article-card">
    <a href="{{ route('articles.show', $article->slug) }}"><img src="{{ $cover }}" alt="Ảnh minh họa: {{ $revision?->title }}" loading="lazy"></a>
    <div class="article-card-body">
        @if($article->category)<a class="category-chip" href="{{ route('categories.show', $article->category->slug) }}">{{ $article->category->name }}</a>@endif
        <h3><a href="{{ route('articles.show', $article->slug) }}">{{ $revision?->title }}</a></h3>
        <p>{{ $revision?->excerpt }}</p>
        <div class="meta"><span>{{ $article->published_at?->format('d/m/Y') }}</span><span>·</span><span>{{ $article->author?->display_name ?? $article->author?->full_name }}</span></div>
    </div>
</article>
