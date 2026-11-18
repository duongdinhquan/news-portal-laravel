<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('articles')->orderBy('sort_order')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name'], '-', 'vi');
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $category = Category::create($data);
        $audit->record($request->user(), 'category.created', $category);
        return back()->with('success', 'Đã tạo chuyên mục.');
    }

    public function update(StoreCategoryRequest $request, Category $category, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name'], '-', 'vi');
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $category->update($data);
        $audit->record($request->user(), 'category.updated', $category);
        return back()->with('success', 'Đã cập nhật chuyên mục.');
    }

    public function destroy(Category $category, AuditLogService $audit): RedirectResponse
    {
        abort_if($category->articles()->exists(), 422, 'Chuyên mục đang có bài viết, hãy ẩn thay vì xóa.');
        $audit->record(request()->user(), 'category.deleted', $category);
        $category->delete();
        return back()->with('success', 'Đã xóa chuyên mục.');
    }
}
