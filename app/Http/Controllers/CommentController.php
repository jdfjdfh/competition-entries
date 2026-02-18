<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\SubmissionService;
use App\Http\Requests\StoreCommentRequest;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $submissionService;

    public function __construct(SubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    public function store(StoreCommentRequest $request, Submission $submission)
    {
        // Проверяем права на комментарий
        $user = auth()->user();
        if ($user->isParticipant() && $user->id !== $submission->user_id) {
            abort(403);
        }

        $comment = $this->submissionService->addComment(
            $submission,
            $user,
            $request->body
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Комментарий добавлен',
                'comment' => $comment->load('user')
            ]);
        }

        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', 'Комментарий добавлен');
    }
}
