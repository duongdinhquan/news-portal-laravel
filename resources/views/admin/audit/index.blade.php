@extends('layouts.workspace')
@section('title', 'Audit log')
@section('content')
<div class="workspace-top"><div><h1>Audit log</h1><p>Lịch sử các thao tác quản trị và nghiệp vụ quan trọng.</p></div></div>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Thời điểm</th><th>Người thực hiện</th><th>Hành động</th><th>Đối tượng</th><th>Metadata</th></tr></thead><tbody>
@forelse($logs as $log)<tr><td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td><td>{{ $log->actor?->full_name ?? 'System' }}</td><td><span class="status-pill">{{ $log->action }}</span></td><td>{{ $log->entity_type }}<div class="table-sub">{{ $log->entity_id }}</div></td><td style="max-width:260px;white-space:normal"><code>{{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE) }}</code></td></tr>@empty<tr><td colspan="5">Chưa có bản ghi audit.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:16px">{{ $logs->links() }}</div></section>
@endsection
