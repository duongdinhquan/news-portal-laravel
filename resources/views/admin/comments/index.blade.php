@extends('layouts.workspace')
@section('title', 'Kiểm duyệt bình luận')
@section('content')
<div class="workspace-top"><div><h1>Kiểm duyệt bình luận</h1><p>Chỉ bình luận được duyệt mới hiển thị trên trang bài viết.</p></div></div>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Nội dung</th><th>Độc giả</th><th>Bài viết</th><th>Trạng thái</th><th>Ngày gửi</th><th>Kiểm duyệt</th></tr></thead><tbody>
@forelse($comments as $comment)
<tr><td style="max-width:320px;white-space:normal">{{ $comment->content }}</td><td>{{ $comment->user?->full_name }}</td><td><div class="table-title">{{ $comment->article?->currentPublishedRevision?->title ?? 'Bài viết' }}</div></td><td>@include('components.status-pill', ['status' => $comment->status])</td><td>{{ $comment->created_at->format('d/m/Y H:i') }}</td><td><form method="post" action="{{ route('admin.comments.moderate', $comment) }}" class="inline-form">@csrf @method('patch')<select class="form-control" name="status" style="min-width:105px;padding:6px"><option value="APPROVED">Duyệt</option><option value="REJECTED">Từ chối</option><option value="HIDDEN">Ẩn</option></select><button class="btn btn-light btn-sm">Lưu</button></form></td></tr>
@empty<tr><td colspan="6"><div class="empty-state"><h3>Chưa có bình luận</h3></div></td></tr>@endforelse
</tbody></table></div><div style="margin-top:16px">{{ $comments->links() }}</div></section>
@endsection
