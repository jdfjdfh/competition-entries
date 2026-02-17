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

    public function index()
    {
        $submissions = $this->submissionService->getSubmissionsForUser(auth()->user());
        return view('submissions.index', compact('submissions'));
    }

    public function create(Request $request)
    {
        $contestId = $request->get('contest_id');
        $contest = Contest::findOrFail($contestId);

        $this->authorize('create', [Submission::class, $contest]);

        return view('submissions.create', compact('contest'));
    }

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

    public function show(Submission $submission)
    {
        $submission = $this->submissionService->findSubmissionForUser(
            auth()->user(),
            $submission->id
        );

        return view('submissions.show', compact('submission'));
    }

    public function edit(Submission $submission)
    {
        return view('submissions.edit', compact('submission'));
    }

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

    public function submit(Submission $submission)
    {
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
