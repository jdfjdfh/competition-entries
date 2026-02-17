<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Contest;
use App\Services\SubmissionService;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Requests\UpdateSubmissionRequest;
use App\Http\Requests\ChangeSubmissionStatusRequest;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    protected $submissionService;

    public function __construct(SubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    /**
     * Показать список работ
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Submission::with(['user', 'contest', 'attachments']);

        // Фильтрация по роли
        if ($user->isParticipant()) {
            $query->where('user_id', $user->id);
        }

        // Поиск
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Фильтр по статусу
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Фильтр по конкурсу (для жюри и админов)
        if (($user->isJury() || $user->isAdmin()) && $request->has('contest_id') && !empty($request->contest_id)) {
            $query->where('contest_id', $request->contest_id);
        }

        // Сортировка
        $query->latest();

        // Пагинация
        $submissions = $query->paginate(15)->withQueryString();

        return view('submissions.index', compact('submissions'));
    }

    /**
     * Показать форму создания работы
     */
    public function create(Request $request)
    {
        $contestId = $request->get('contest_id');
        $contest = Contest::findOrFail($contestId);

        return view('submissions.create', compact('contest'));
    }

    /**
     * Сохранить новую работу
     */
    public function store(StoreSubmissionRequest $request)
    {
        $submission = $this->submissionService->create(
            $request->validated(),
            auth()->user()
        );

        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', 'Черновик работы успешно создан');
    }

    /**
     * Показать конкретную работу
     */
    public function show(Submission $submission)
    {
        $submission->load(['user', 'contest', 'attachments', 'comments.user']);

        return view('submissions.show', compact('submission'));
    }

    /**
     * Показать форму редактирования
     */
    public function edit(Submission $submission)
    {
        return view('submissions.edit', compact('submission'));
    }

    /**
     * Обновить работу
     */
    public function update(UpdateSubmissionRequest $request, Submission $submission)
    {
        $submission = $this->submissionService->update(
            $submission,
            $request->validated()
        );

        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', 'Работа успешно обновлена');
    }

    /**
     * Отправить работу на проверку
     */
    public function submit(Submission $submission)
    {
        $this->authorize('submit', $submission);

        try {
            $submission = $this->submissionService->submit($submission);

            return redirect()
                ->route('submissions.show', $submission)
                ->with('success', 'Работа успешно отправлена на конкурс');
        } catch (\Exception $e) {
            return redirect()
                ->route('submissions.show', $submission)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Изменить статус работы (для жюри)
     */
    public function changeStatus(ChangeSubmissionStatusRequest $request, Submission $submission)
    {
        try {
            $submission = $this->submissionService->changeStatus(
                $submission,
                $request->status,
                $request->comment
            );

            return redirect()
                ->route('submissions.show', $submission)
                ->with('success', 'Статус работы успешно изменен');
        } catch (\Exception $e) {
            return redirect()
                ->route('submissions.show', $submission)
                ->with('error', $e->getMessage());
        }
    }
}
