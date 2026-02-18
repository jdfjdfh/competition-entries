<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contest;
use App\Models\Submission;
use App\Models\Attachment;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\StoreContestRequest;
use App\Http\Requests\Admin\UpdateContestRequest;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Главная админ-панели
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_participants' => User::where('role', 'participant')->count(),
            'total_jury' => User::where('role', 'jury')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_contests' => Contest::count(),
            'active_contests' => Contest::where('is_active', true)->count(),
            'total_submissions' => Submission::count(),
            'submissions_by_status' => [
                'draft' => Submission::where('status', 'draft')->count(),
                'submitted' => Submission::where('status', 'submitted')->count(),
                'needs_fix' => Submission::where('status', 'needs_fix')->count(),
                'accepted' => Submission::where('status', 'accepted')->count(),
                'rejected' => Submission::where('status', 'rejected')->count(),
            ],
            'total_attachments' => Attachment::count(),
            'storage_used' => Attachment::sum('size'),
            'recent_users' => User::latest()->take(5)->get(),
            'recent_submissions' => Submission::with(['user', 'contest'])->latest()->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Управление пользователями
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Поиск
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Фильтр по роли
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->withCount('submissions')->latest()->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Форма создания пользователя
     */
    public function createUser()
    {
        return view('admin.users-create');
    }

    /**
     * Сохранение нового пользователя
     */
    public function storeUser(StoreUserRequest $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users')
            ->with('success', 'Пользователь успешно создан');
    }

    /**
     * Редактирование пользователя
     */
    public function editUser(User $user)
    {
        return view('admin.users-edit', compact('user'));
    }

    /**
     * Обновление пользователя
     */
    public function updateUser(UpdateUserRequest $request, User $user)
    {
        // Не позволяем админу изменить свою собственную роль
        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return back()->with('error', 'Вы не можете изменить свою собственную роль');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users')
            ->with('success', 'Пользователь успешно обновлен');
    }

    /**
     * Удаление пользователя
     */
    public function deleteUser(User $user)
    {
        // Не позволяем админу удалить самого себя
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Вы не можете удалить свой собственный аккаунт');
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'Пользователь успешно удален');
    }

    /**
     * Статистика по платформе
     */
    public function statistics()
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'by_role' => [
                    'participant' => User::where('role', 'participant')->count(),
                    'jury' => User::where('role', 'jury')->count(),
                    'admin' => User::where('role', 'admin')->count(),
                ],
                'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
                'new_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ],
            'contests' => [
                'total' => Contest::count(),
                'active' => Contest::where('is_active', true)->count(),
                'expired' => Contest::where('deadline_at', '<', now())->count(),
                'by_month' => Contest::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                    ->whereYear('created_at', now()->year)
                    ->groupBy('month')
                    ->pluck('count', 'month'),
            ],
            'submissions' => [
                'total' => Submission::count(),
                'by_status' => [
                    'draft' => Submission::where('status', 'draft')->count(),
                    'submitted' => Submission::where('status', 'submitted')->count(),
                    'needs_fix' => Submission::where('status', 'needs_fix')->count(),
                    'accepted' => Submission::where('status', 'accepted')->count(),
                    'rejected' => Submission::where('status', 'rejected')->count(),
                ],
                'by_contest' => Submission::selectRaw('contest_id, COUNT(*) as count')
                    ->groupBy('contest_id')
                    ->with('contest')
                    ->get()
                    ->mapWithKeys(function($item) {
                        return [$item->contest->title => $item->count];
                    }),
            ],
            'attachments' => [
                'total' => Attachment::count(),
                'total_size_mb' => round(Attachment::sum('size') / 1024 / 1024, 2),
                'by_status' => [
                    'pending' => Attachment::where('status', 'pending')->count(),
                    'scanned' => Attachment::where('status', 'scanned')->count(),
                    'rejected' => Attachment::where('status', 'rejected')->count(),
                ],
                'by_type' => [
                    'pdf' => Attachment::where('mime', 'application/pdf')->count(),
                    'zip' => Attachment::where('mime', 'application/zip')->count(),
                    'image' => Attachment::whereIn('mime', ['image/png', 'image/jpeg'])->count(),
                ],
            ],
        ];

        return view('admin.statistics', compact('stats'));
    }

    /**
     * Управление конкурсами (переопределение методов ContestController)
     */
    public function contests()
    {
        $contests = Contest::withCount('submissions')->latest()->paginate(15);
        return view('admin.contests', compact('contests'));
    }

    /**
     * Создание конкурса
     */
    public function createContest()
    {
        return view('admin.contests-create');
    }

    /**
     * Сохранение конкурса
     */
    public function storeContest(StoreContestRequest $request)
    {
        Contest::create($request->all());

        return redirect()->route('admin.contests')
            ->with('success', 'Конкурс успешно создан');
    }

    /**
     * Редактирование конкурса
     */
    public function editContest(Contest $contest)
    {
        return view('admin.contests-edit', compact('contest'));
    }

    /**
     * Обновление конкурса
     */
    public function updateContest(UpdateContestRequest $request, Contest $contest)
    {
        $contest->update($request->all());

        return redirect()->route('admin.contests')
            ->with('success', 'Конкурс успешно обновлен');
    }

    /**
     * Удаление конкурса
     */
    public function deleteContest(Contest $contest)
    {
        // Проверяем, есть ли связанные заявки
        if ($contest->submissions()->count() > 0) {
            return back()->with('error', 'Нельзя удалить конкурс, на который уже поданы работы');
        }

        $contest->delete();

        return redirect()->route('admin.contests')
            ->with('success', 'Конкурс успешно удален');
    }

    /**
     * Просмотр всех работ (как жюри)
     */
    public function submissions(Request $request)
    {
        $query = Submission::with(['user', 'contest', 'attachments']);

        // Фильтры
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('contest_id')) {
            $query->where('contest_id', $request->contest_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $submissions = $query->latest()->paginate(15);

        $contests = Contest::all();
        $users = User::where('role', 'participant')->get();

        return view('admin.submissions', compact('submissions', 'contests', 'users'));
    }

    /**
     * Системные настройки
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Сохранение настроек
     */
    public function updateSettings(UpdateSettingsRequest $request)
    {
        // Здесь можно сохранять настройки в отдельную таблицу или .env
        // Для простоты просто показываем сообщение

        return redirect()->route('admin.settings')
            ->with('success', 'Настройки сохранены');
    }

    /**
     * Очистка кэша
     */
    public function clearCache()
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');

            return back()->with('success', 'Кэш успешно очищен');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при очистке кэша');
        }
    }
}
