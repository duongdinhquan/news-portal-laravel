@php
    $labels = ['DRAFT' => 'Bản nháp', 'PENDING_REVIEW' => 'Chờ duyệt', 'PUBLISHED' => 'Đã xuất bản', 'REJECTED' => 'Bị từ chối', 'SUPERSEDED' => 'Phiên bản cũ', 'PENDING' => 'Chờ duyệt', 'APPROVED' => 'Đã duyệt', 'HIDDEN' => 'Đã ẩn', 'DISABLED' => 'Đã khóa', 'ACTIVE' => 'Hoạt động'];
@endphp
<span class="status-pill status-{{ strtolower($status) }}">{{ $labels[$status] ?? $status }}</span>
