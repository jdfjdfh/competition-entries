<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Публичные маршруты
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Маршруты аутентификации
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Маршруты для авторизованных пользователей
Route::middleware('auth')->group(function () {
    // Выход
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Дашборд
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Уведомления
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear/all', [NotificationController::class, 'clearAll'])->name('clear-all');

        // API маршруты для AJAX
        Route::get('/unread/count', [NotificationController::class, 'unreadCount'])->name('unread.count');
        Route::get('/latest', [NotificationController::class, 'latest'])->name('latest');
    });

    // Конкурсы (доступны всем)
    Route::get('/contests', [ContestController::class, 'index'])->name('contests.index');
    Route::get('/contests/{contest}', [ContestController::class, 'show'])->name('contests.show');

    // Работы
    Route::resource('submissions', SubmissionController::class)->except(['destroy']);
    Route::post('/submissions/{submission}/submit', [SubmissionController::class, 'submit'])->name('submissions.submit');
    Route::patch('/submissions/{submission}/status', [SubmissionController::class, 'changeStatus'])->name('submissions.change-status');

    // Файлы
    Route::prefix('submissions/{submission}')->name('attachments.')->group(function () {
        Route::post('/attachments', [AttachmentController::class, 'upload'])->name('upload');
        Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('download');
        Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('destroy');
    });

    // Комментарии
    Route::post('/submissions/{submission}/comments', [CommentController::class, 'store'])->name('comments.store');

    // Админ-панель
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard'); // админская главная
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/contests', [AdminController::class, 'contests'])->name('contests');
        Route::get('/contests/create', [AdminController::class, 'createContest'])->name('contests.create');
        Route::post('/contests', [AdminController::class, 'storeContest'])->name('contests.store');
        Route::get('/contests/{contest}/edit', [AdminController::class, 'editContest'])->name('contests.edit');
        Route::put('/contests/{contest}', [AdminController::class, 'updateContest'])->name('contests.update');
        Route::delete('/contests/{contest}', [AdminController::class, 'deleteContest'])->name('contests.delete');
        Route::get('/submissions', [AdminController::class, 'submissions'])->name('submissions');
        Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('/clear-cache', [AdminController::class, 'clearCache'])->name('clear-cache');
    });
});
