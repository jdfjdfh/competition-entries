<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Contest;
use App\Models\User;
use App\Services\SubmissionService;
use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    protected SubmissionService $submissionService;
    protected AttachmentService $attachmentService;

    public function __construct(SubmissionService $submissionService, AttachmentService $attachmentService)
    {
        $this->submissionService = $submissionService;
        $this->attachmentService = $attachmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isJury() || $user->isAdmin()) {
            // Для жюри и админа - все работы
            $query = Submission::with(['user', 'contest']);

            // Фильтр по статусу
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Фильтр по конкурсу
            if ($request->filled('contest_id')) {
                $query->where('contest_id', $request->contest_id);
            }

            // Фильтр по пользователю
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Поиск по названию
            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            $submissions = $query->latest()->paginate(15)->withQueryString();

            // Данные для фильтров
            $contests = Contest::all();
            $users = User::where('role', 'participant')->get();
            $statuses = [
                'draft' => 'Черновик',
                'submitted' => 'На проверке',
                'needs_fix' => 'Требуется доработка',
                'accepted' => 'Принято',
                'rejected' => 'Отклонено'
            ];

            return view('submissions.index', compact('submissions', 'contests', 'users', 'statuses'));

        } else {
            // Для участника - только свои работы
            $submissions = $user->submissions()
                ->with('contest')
                ->latest()
                ->paginate(15);

            return view('submissions.index', compact('submissions'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $contestId = $request->get('contest_id');

        if ($contestId) {
            $contest = Contest::findOrFail($contestId);

            if (!$contest->is_active || $contest->deadline_at < now()) {
                return redirect()->route('contests.show', $contest)
                    ->with('error', 'Этот конкурс недоступен для подачи работ');
            }

            return view('submissions.create', compact('contest'));
        }

        // Если не указан конкретный конкурс, показываем список доступных
        $contests = Contest::where('is_active', true)
            ->where('deadline_at', '>', now())
            ->get();

        if ($contests->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Нет активных конкурсов для подачи работ');
        }

        return view('submissions.choose-contest', compact('contests'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contest_id' => 'required|exists:contests,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        try {
            $submission = $this->submissionService->create($request->all(), Auth::user());

            return redirect()->route('submissions.show', $submission)
                ->with('success', 'Черновик работы успешно создан');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Submission $submission)
    {
        $user = Auth::user();

        // Проверка доступа
        if ($user->isParticipant() && $submission->user_id !== $user->id) {
            abort(403, 'У вас нет доступа к этой работе');
        }

        // Загружаем связи
        $submission->load(['user', 'contest', 'attachments', 'comments.user']);

        // Статистика файлов
        $attachments_stats = [
            'total' => $submission->attachments->count(),
            'scanned' => $submission->attachments->where('status', 'scanned')->count(),
            'pending' => $submission->attachments->where('status', 'pending')->count(),
            'rejected' => $submission->attachments->where('status', 'rejected')->count(),
        ];

        // Можно ли редактировать
        $can_edit = $submission->isEditable() && $submission->user_id === $user->id;

        // Можно ли отправлять
        $can_submit = $submission->status === 'draft' &&
            $attachments_stats['scanned'] > 0 &&
            $submission->user_id === $user->id;

        // Можно ли загружать файлы
        $can_upload = $submission->user_id === $user->id &&
            $submission->isEditable() &&
            $attachments_stats['total'] < 3;

        // Доступные статусы для жюри/админа
        $available_statuses = [];
        $statusNames = [
            'draft' => 'Черновик',
            'submitted' => 'На проверке',
            'needs_fix' => 'Требуется доработка',
            'accepted' => 'Принято',
            'rejected' => 'Отклонено'
        ];

        if ($user->isJury() || $user->isAdmin()) {
            $available_statuses = $this->getAllowedStatusTransitions()[$submission->status] ?? [];
        }

        return view('submissions.show', compact(
            'submission',
            'attachments_stats',
            'can_edit',
            'can_submit',
            'can_upload',
            'available_statuses',
            'statusNames'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Submission $submission)
    {
        $user = Auth::user();

        if ($submission->user_id !== $user->id) {
            abort(403);
        }

        if (!$submission->isEditable()) {
            return redirect()->route('submissions.show', $submission)
                ->with('error', 'Редактирование недоступно в текущем статусе');
        }

        return view('submissions.edit', compact('submission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Submission $submission)
    {
        $user = Auth::user();

        if ($submission->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        try {
            $this->submissionService->update($submission, $request->all());

            return redirect()->route('submissions.show', $submission)
                ->with('success', 'Работа успешно обновлена');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Submit submission for review
     */
    public function submit(Submission $submission)
    {
        $user = Auth::user();

        if ($submission->user_id !== $user->id) {
            abort(403);
        }

        try {
            $this->submissionService->submit($submission);

            return redirect()->route('submissions.show', $submission)
                ->with('success', 'Работа отправлена на проверку');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Изменение статуса работы (для жюри)
     */
    public function changeStatus(Request $request, Submission $submission)
    {
        $user = Auth::user();

        // Только жюри и админ могут менять статус
        if (!$user->isJury() && !$user->isAdmin()) {
            return redirect()->back()->with('error', 'У вас нет прав для изменения статуса');
        }

        // Валидация
        $request->validate([
            'status' => 'required|in:submitted,needs_fix,accepted,rejected',
            'comment' => 'required_if:status,needs_fix|nullable|string|max:1000',
        ]);

        // Проверяем, допустим ли такой переход
        if (!$submission->canJurySetStatus($request->status)) {
            return redirect()->back()->with('error', 'Недопустимый переход статуса');
        }

        try {
            // Меняем статус
            $this->submissionService->changeStatus($submission, $request->status, $user);

            // Если есть комментарий, добавляем его
            if ($request->filled('comment')) {
                $this->submissionService->addComment($submission, $user, $request->comment);
            }

            // Разные сообщения для разных статусов
            $messages = [
                'accepted' => 'Работа принята. Поздравляем участника!',
                'rejected' => 'Работа отклонена',
                'needs_fix' => 'Запрошена доработка. Комментарий отправлен участнику.',
            ];

            $successMessage = $messages[$request->status] ?? 'Статус работы изменен';

            return redirect()->route('submissions.show', $submission)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Get allowed status transitions
     */
    private function getAllowedStatusTransitions()
    {
        return [
            'draft' => ['submitted'],
            'submitted' => ['needs_fix', 'accepted', 'rejected'],
            'needs_fix' => ['submitted', 'rejected'],
            'accepted' => [],
            'rejected' => [],
        ];
    }
}
