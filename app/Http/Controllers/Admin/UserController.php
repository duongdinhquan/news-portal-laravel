<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEditorRequest;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::latest()->paginate(25);
        return view('admin.users.index', compact('users'));
    }

    public function storeEditor(StoreEditorRequest $request, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validated();
        $editor = User::create([
            'full_name' => $data['full_name'],
            'display_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'EDITOR',
            'status' => 'ACTIVE',
        ]);
        $audit->record($request->user(), 'user.editor_created', $editor);
        return back()->with('success', 'Đã tạo tài khoản Editor.');
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user, AuditLogService $audit): RedirectResponse
    {
        abort_if($request->user()->id === $user->id, 422, 'Không thể tự thay đổi role của chính mình.');
        $this->authorize('updateRole', $user);
        DB::transaction(function () use ($user, $request, $audit) {
            $lockedAdmins = User::query()->where('role', 'ADMIN')->orderBy('id')->lockForUpdate()->get(['id', 'status']);
            $target = User::query()->lockForUpdate()->findOrFail($user->id);
            $newRole = $request->validated('role');
            if ($target->role === 'ADMIN' && $target->status === 'ACTIVE' && $newRole !== 'ADMIN') {
                abort_if($lockedAdmins->where('status', 'ACTIVE')->count() <= 1, 422, 'Không thể hạ quyền Admin hoạt động cuối cùng.');
            }
            $target->update(['role' => $newRole]);
            $audit->record($request->user(), 'user.role_changed', $target, ['role' => $target->role]);
        });
        return back()->with('success', 'Đã cập nhật vai trò.');
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user, AuditLogService $audit): RedirectResponse
    {
        abort_if($request->user()->id === $user->id, 422, 'Không thể khóa tài khoản đang đăng nhập.');
        $this->authorize('updateStatus', $user);
        DB::transaction(function () use ($user, $request, $audit) {
            $lockedAdmins = User::query()->where('role', 'ADMIN')->orderBy('id')->lockForUpdate()->get(['id', 'status']);
            $target = User::query()->lockForUpdate()->findOrFail($user->id);
            $newStatus = $request->validated('status');
            if ($target->role === 'ADMIN' && $target->status === 'ACTIVE' && $newStatus === 'DISABLED') {
                abort_if($lockedAdmins->where('status', 'ACTIVE')->count() <= 1, 422, 'Không thể khóa Admin hoạt động cuối cùng.');
            }
            $target->update(['status' => $newStatus]);
            if ($target->status === 'DISABLED') {
                \App\Models\RefreshSession::where('user_id', $target->id)->whereNull('revoked_at')->update(['revoked_at' => now()]);
            }
            $audit->record($request->user(), 'user.status_changed', $target, ['status' => $target->status]);
        });
        return back()->with('success', 'Đã cập nhật trạng thái tài khoản.');
    }

}
