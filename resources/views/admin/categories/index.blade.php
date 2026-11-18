@extends('layouts.workspace')
@section('title', 'Chuyên mục')
@section('content')
<div class="workspace-top"><div><h1>Chuyên mục</h1><p>Quản lý tên, slug và trạng thái chuyên mục hiển thị trên website.</p></div></div>
<section class="panel"><h2 style="font-size:14px;margin:0 0 15px">Tạo chuyên mục mới</h2><form method="post" action="{{ route('admin.categories.store') }}" style="display:grid;grid-template-columns:1fr 1fr 1.5fr auto;gap:9px;align-items:end">@csrf
<div class="form-group" style="margin:0"><label class="form-label">Tên chuyên mục</label><input class="form-control" name="name" required maxlength="100"></div><div class="form-group" style="margin:0"><label class="form-label">Slug (tùy chọn)</label><input class="form-control" name="slug" placeholder="tu-dong-tao"></div><div class="form-group" style="margin:0"><label class="form-label">Mô tả</label><input class="form-control" name="description" maxlength="1000"></div><button class="btn btn-primary">Thêm</button>
</form></section>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Chuyên mục</th><th>Số bài viết</th><th>Trạng thái</th><th>Cập nhật</th><th>Thao tác</th></tr></thead><tbody>
@foreach($categories as $category)
<tr><td><div class="table-title">{{ $category->name }}</div><div class="table-sub">/{{ $category->slug }}</div></td><td>{{ $category->articles_count }}</td><td><span class="status-pill {{ $category->is_active ? 'status-approved' : 'status-disabled' }}">{{ $category->is_active ? 'Đang hiển thị' : 'Đã ẩn' }}</span></td><td>{{ $category->updated_at->format('d/m/Y') }}</td><td><div style="display:flex;gap:6px;flex-wrap:wrap"><form method="post" action="{{ route('admin.categories.update', $category) }}">@csrf @method('patch')<input type="hidden" name="name" value="{{ $category->name }}"><input type="hidden" name="slug" value="{{ $category->slug }}"><input type="hidden" name="description" value="{{ $category->description }}"><input type="hidden" name="sort_order" value="{{ $category->sort_order }}"><input type="hidden" name="is_active" value="{{ $category->is_active ? 0 : 1 }}"><button class="btn btn-light btn-sm">{{ $category->is_active ? 'Ẩn' : 'Hiện' }}</button></form>@if(!$category->articles_count)<form method="post" action="{{ route('admin.categories.destroy', $category) }}">@csrf @method('delete')<button class="btn btn-danger btn-sm" data-confirm="Xóa chuyên mục này?">Xóa</button></form>@endif</div></td></tr>
@endforeach
</tbody></table></div><div style="margin-top:16px">{{ $categories->links() }}</div></section>
@endsection
