@extends('layouts.workspace')
@section('title', 'Thư viện media')
@section('content')
<div class="workspace-top"><div><h1>Thư viện media</h1><p>Ảnh do tài khoản của bạn tải lên.</p></div></div>
<section class="panel"><h2 style="font-size:14px;margin:0 0 14px">Tải ảnh lên cho bài viết</h2>
<form method="post" action="{{ route('editor.media.store') }}" enctype="multipart/form-data" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end">@csrf
    <div class="form-group" style="flex:1;min-width:220px;margin:0"><label class="form-label">Bài viết sở hữu ảnh</label><select class="form-control" name="article_id" required><option value="">Chọn bài viết</option>@foreach($articles as $article)<option value="{{ $article->id }}">{{ $article->slug }}</option>@endforeach</select></div>
    <div class="form-group" style="flex:1;min-width:220px;margin:0"><label class="form-label">Tệp ảnh</label><input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp" required></div>
    <button class="btn btn-primary" type="submit">Tải lên</button>
</form></section>
<section class="panel"><div class="panel-head"><h2>Ảnh đã tải lên</h2><span class="meta">{{ $items->total() }} tệp</span></div>
@if($items->isNotEmpty())<div class="article-grid">@foreach($items as $item)<div class="article-card"><img src="{{ $item->url }}" alt="{{ $item->original_name }}"><div class="article-card-body"><div class="table-title">{{ $item->original_name }}</div><div class="table-sub">{{ $item->mime_type }} · {{ number_format($item->size_bytes / 1024, 0) }} KB</div><form method="post" action="{{ route('editor.media.destroy', $item) }}" style="margin-top:10px">@csrf @method('delete')<button class="btn btn-danger btn-sm" data-confirm="Xóa ảnh này?">Xóa</button></form></div></div>@endforeach</div><div style="margin-top:18px">{{ $items->links() }}</div>@else<div class="empty-state"><h3>Chưa có ảnh nào</h3><p>Ảnh bạn tải lên sẽ xuất hiện tại đây.</p></div>@endif
</section>
@endsection
