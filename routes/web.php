<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Editor\ArticleController as EditorArticleController;
use App\Http\Controllers\Editor\DashboardController as EditorDashboardController;
use App\Http\Controllers\Editor\MediaController as EditorMediaController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/chuyen-muc/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tin/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/tim-kiem', SearchController::class)->name('search');

{
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
}

Route::post('/auth/refresh', [AuthController::class, 'refresh'])->middleware('throttle:10,1')->name('auth.refresh');

Route::middleware(['jwt.cookie', 'user.active'])->group(function () {
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
    Route::get('/tai-khoan', [AuthController::class, 'account'])->name('account.profile');
    Route::post('/tai-khoan/doi-mat-khau', [AuthController::class, 'changePassword'])->name('account.password');
    Route::post('/auth/dang-xuat-tat-ca', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
    Route::post('/tin/{article}/binh-luan', [CommentController::class, 'store'])->middleware(['role:USER', 'throttle:10,1'])->name('comments.store');
    Route::delete('/tai-khoan/binh-luan/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::prefix('editor')->name('editor.')->middleware('role:EDITOR,ADMIN')->group(function () {
        Route::get('/', EditorDashboardController::class)->name('dashboard');
        Route::get('/bai-viet', [EditorArticleController::class, 'index'])->name('articles.index');
        Route::get('/bai-viet/tao-moi', [EditorArticleController::class, 'create'])->name('articles.create');
        Route::post('/bai-viet', [EditorArticleController::class, 'store'])->name('articles.store');
        Route::get('/bai-viet/{article}/sua', [EditorArticleController::class, 'edit'])->name('articles.edit');
        Route::put('/bai-viet/{article}', [EditorArticleController::class, 'update'])->name('articles.update');
        Route::post('/bai-viet/{article}/gui-duyet', [EditorArticleController::class, 'submit'])->name('articles.submit');
        Route::get('/media', [EditorMediaController::class, 'index'])->name('media.index');
        Route::post('/media', [EditorMediaController::class, 'store'])->middleware('throttle:20,1')->name('media.store');
        Route::delete('/media/{media}', [EditorMediaController::class, 'destroy'])->name('media.destroy');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:ADMIN')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/nguoi-dung', [UserController::class, 'index'])->name('users.index');
        Route::post('/nguoi-dung/editor', [UserController::class, 'storeEditor'])->name('users.editor.store');
        Route::patch('/nguoi-dung/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::patch('/nguoi-dung/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::get('/bai-viet', [AdminArticleController::class, 'index'])->name('articles.index');
        Route::get('/cho-duyet', [AdminArticleController::class, 'queue'])->name('articles.queue');
        Route::post('/bai-viet/{article}/publish', [AdminArticleController::class, 'publish'])->name('articles.publish');
        Route::post('/bai-viet/{article}/reject', [AdminArticleController::class, 'reject'])->name('articles.reject');
        Route::post('/bai-viet/{article}/archive', [AdminArticleController::class, 'archive'])->name('articles.archive');
        Route::get('/binh-luan', [AdminCommentController::class, 'index'])->name('comments.index');
        Route::patch('/binh-luan/{comment}/moderation', [AdminCommentController::class, 'moderate'])->name('comments.moderate');
        Route::get('/chuyen-muc', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/chuyen-muc', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::patch('/chuyen-muc/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/chuyen-muc/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('/media', [AdminMediaController::class, 'index'])->name('media.index');
        Route::post('/media', [AdminMediaController::class, 'store'])->middleware('throttle:20,1')->name('media.store');
        Route::delete('/media/{media}', [AdminMediaController::class, 'destroy'])->name('media.destroy');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    });
});
