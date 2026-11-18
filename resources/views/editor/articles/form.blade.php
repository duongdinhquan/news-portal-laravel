@extends('layouts.workspace')
@section('title', $article ? 'Chỉnh sửa bài viết' : 'Tạo bài viết mới')
@php
    $currentStatus = $revision?->status ?? 'DRAFT';
    $locked = $currentStatus === 'PENDING_REVIEW';
    $coverUrl = $revision?->coverMedia?->url;
@endphp
@section('content')
<div class="workspace-top"><div><h1>{{ $article ? 'Chỉnh sửa bài viết' : 'Tạo bài viết mới' }}</h1><p>{{ $article ? 'Cập nhật bản nháp hoặc revision của bạn.' : 'Soạn thảo nội dung và lưu thành bản nháp trước khi gửi duyệt.' }}</p></div><a class="btn btn-light" href="{{ route('editor.articles.index') }}">← Danh sách</a></div>
@if($revision?->rejection_reason)<div class="alert alert-error"><strong>Lý do từ chối:</strong> {{ $revision->rejection_reason }}</div>@endif
@if($locked)<div class="alert alert-success">Bài viết đang chờ Admin duyệt. Bạn chưa thể chỉnh sửa revision này.</div>@endif
<form method="post" action="{{ $article ? route('editor.articles.update', $article) : route('editor.articles.store') }}">
    @csrf @if($article)@method('put')@endif
    <input type="hidden" name="content_html" value="{{ old('content_html', $revision?->content_html ?? '<p></p>') }}">
    <input type="hidden" name="content_json" value="{{ old('content_json', json_encode($revision?->content_json ?? [], JSON_UNESCAPED_UNICODE)) }}">
    <input type="hidden" name="cover_media_id" value="{{ old('cover_media_id', $revision?->cover_media_id) }}">
    <div class="two-col">
        <div class="panel">
            <div class="form-group"><label class="form-label" for="title">Tiêu đề bài viết *</label><input class="form-control" id="title" name="title" maxlength="255" value="{{ old('title', $revision?->title) }}" required @disabled($locked)></div>
            <div class="form-group"><label class="form-label" for="excerpt">Sapo / Mô tả ngắn</label><textarea class="form-control" id="excerpt" name="excerpt" rows="3" maxlength="500" @disabled($locked)>{{ old('excerpt', $revision?->excerpt) }}</textarea><div class="form-hint">Tối đa 500 ký tự.</div></div>
            <div class="form-group"><label class="form-label" for="category_id">Chuyên mục *</label><select class="form-control" id="category_id" name="category_id" required @disabled($locked)>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $article?->category_id) === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Nội dung bài viết *</label>
                <div class="editor-toolbar" data-editor-toolbar>
                    <button type="button" data-command="bold"><b>B</b></button><button type="button" data-command="italic"><i>I</i></button><button type="button" data-command="underline"><u>U</u></button><button type="button" data-command="strike"><s>S</s></button>
                    <button type="button" data-command="h2">H2</button><button type="button" data-command="h3">H3</button><button type="button" data-command="bullet">• Danh sách</button><button type="button" data-command="ordered">1. Danh sách</button><button type="button" data-command="quote">Trích dẫn</button><button type="button" data-command="link">Liên kết</button><button type="button" data-command="hr">Đường kẻ</button><button type="button" data-command="undo">↶</button><button type="button" data-command="redo">↷</button>
                </div>
                <div data-rich-editor data-initial-html="{{ base64_encode(old('content_html', $revision?->content_html ?? '<p></p>')) }}"></div>
            </div>
            <div class="form-group"><label class="form-label">Chèn ảnh vào nội dung</label><div class="upload-row"><input type="file" accept="image/jpeg,image/png,image/webp" data-editor-image-upload data-upload-url="{{ route('editor.media.store') }}" data-article-id="{{ $article?->id }}" @disabled($locked)><span class="form-hint">JPEG, PNG, WebP · tối đa 5 MB. Lưu bản nháp trước khi tải ảnh.</span></div></div>
            @unless($locked)<button class="btn btn-primary" type="submit">{{ $article ? 'Lưu thay đổi' : 'Lưu bản nháp' }}</button>@endunless
        </div>
        <aside>
            <section class="panel"><h2 style="font-size:14px;margin:0 0 14px">Ảnh bìa</h2>
                @if($coverUrl)<img class="cover-preview" data-cover-preview src="{{ $coverUrl }}" alt="Ảnh bìa hiện tại">@else<img class="cover-preview" data-cover-preview src="{{ asset('images/covers/default.svg') }}" alt="Ảnh minh họa">@endif
                <div class="form-group" style="margin-top:12px"><label class="form-label">Tải ảnh bìa</label><input type="file" accept="image/jpeg,image/png,image/webp" data-cover-upload data-upload-url="{{ route('editor.media.store') }}" data-article-id="{{ $article?->id }}" @disabled($locked)><div class="form-hint">Ảnh sẽ được xác minh ở server. Tối đa 5 MB.</div></div>
            </section>
            <section class="panel"><h2 style="font-size:14px;margin:0 0 12px">Quy trình xuất bản</h2>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><span class="form-label" style="margin:0">Trạng thái hiện tại</span>@include('components.status-pill', ['status' => $currentStatus])</div>
                <p class="form-hint">Sau khi gửi duyệt, chỉ Admin mới có thể xuất bản hoặc từ chối bài viết. Nếu bài đã xuất bản, nội dung công khai cũ vẫn được giữ nguyên trong lúc chờ duyệt revision mới.</p>
                @if($article && in_array($currentStatus, ['DRAFT', 'REJECTED'], true))
                    <button class="btn btn-primary" type="submit" form="submit-article-form" style="width:100%;margin-top:15px" data-confirm="Gửi bài viết này đến Admin để duyệt?">Gửi duyệt bài viết</button>
                @elseif($locked)
                    <p class="form-hint" style="color:#95600a;font-weight:800">Đang chờ phản hồi từ Admin.</p>
                @elseif($currentStatus === 'PUBLISHED')
                    <p class="form-hint">Bài viết hiện đang được công khai. Hãy lưu thay đổi để tạo revision mới trước khi gửi duyệt.</p>
                @endif
            </section>
            @if($article)<section class="panel"><h2 style="font-size:14px;margin:0 0 10px">Thông tin bài viết</h2><div class="form-hint">Mã: {{ $article->id }}</div><div class="form-hint">Tạo lúc: {{ $article->created_at->format('d/m/Y H:i') }}</div><div class="form-hint">Cập nhật: {{ $article->updated_at->format('d/m/Y H:i') }}</div></section>@endif
        </aside>
    </div>
</form>
@if($article && in_array($currentStatus, ['DRAFT', 'REJECTED'], true))
    <form id="submit-article-form" method="post" action="{{ route('editor.articles.submit', $article) }}">@csrf</form>
@endif
@endsection
@push('scripts')@vite('resources/js/editor.js')@endpush
