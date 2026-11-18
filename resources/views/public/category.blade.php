@extends('layouts.public')
@section('title', $category->name.' – Bản Tin')
@section('content')
<div class="page-shell"><div class="site-width">
    <div class="section-head" style="margin-bottom:22px"><div><div class="section-eyebrow">Chuyên mục</div><h1 class="page-title">{{ $category->name }}</h1><p class="page-subtitle" style="margin:6px 0 0">{{ $category->description ?: 'Những tin tức và góc nhìn mới nhất trong chuyên mục '.$category->name.'.' }}</p></div></div>
    @if($articles->isNotEmpty())
        <div class="article-grid">@foreach($articles as $article)@include('components.article-card', ['article' => $article])@endforeach</div>
        <div style="margin-top:25px">{{ $articles->links() }}</div>
    @else
        <div class="empty-state"><h3>Chưa có bài viết trong chuyên mục này</h3><p>Các bài viết đã xuất bản sẽ xuất hiện tại đây.</p></div>
    @endif
</div></div>
@endsection
