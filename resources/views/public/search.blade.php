@extends('layouts.public')
@section('title', 'Tìm kiếm'.($term ? ': '.$term : '').' – Bản Tin')
@section('content')
<div class="page-shell"><div class="site-width">
    <div class="section-eyebrow">Khám phá thông tin</div><h1 class="page-title">Tìm kiếm</h1><p class="page-subtitle">Chỉ hiển thị bài viết đã được Admin xuất bản.</p>
    <form action="{{ route('search') }}" method="get" class="panel" style="display:grid;grid-template-columns:1fr 220px auto;gap:10px;align-items:end;margin-bottom:25px">
        <div class="form-group" style="margin:0"><label class="form-label" for="q">Từ khóa</label><input id="q" class="form-control" name="q" value="{{ $term }}" placeholder="Nhập từ khóa cần tìm..."></div>
        <div class="form-group" style="margin:0"><label class="form-label" for="category">Chuyên mục</label><select id="category" class="form-control" name="category"><option value="">Tất cả chuyên mục</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(($data['category'] ?? '') === $category->slug)>{{ $category->name }}</option>@endforeach</select></div>
        <button class="btn btn-primary" type="submit">Tìm bài viết</button>
    </form>
    <div class="section-head"><h2 class="section-title" style="font-size:22px">{{ $term ? 'Kết quả cho “'.$term.'”' : 'Bài viết mới nhất' }}</h2><span class="meta">{{ $articles->total() }} kết quả</span></div>
    @if($articles->isNotEmpty())<div class="article-grid">@foreach($articles as $article)@include('components.article-card', ['article' => $article])@endforeach</div><div style="margin-top:25px">{{ $articles->links() }}</div>
    @else<div class="empty-state"><h3>Không tìm thấy bài viết phù hợp</h3><p>Thử từ khóa khác hoặc chọn một chuyên mục khác.</p></div>@endif
</div></div>
@endsection
