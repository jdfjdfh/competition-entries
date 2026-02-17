<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\Contest;
use App\Models\User;
use App\Models\SubmissionComment;
use Illuminate\Support\Facades\DB;
use App\Jobs\NotifyStatusChangedJob;

class SubmissionService
{
    protected $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function create(array $data, User $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $submission = Submission::create([
                'contest_id' => $data['contest_id'],
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => Submission::STATUS_DRAFT,
            ]);

            return $submission;
        });
    }

    public function update(Submission $submission, array $data)
    {
        if (!$submission->isEditable()) {
            throw new \Exception('Cannot edit submission in current status');
        }

        return DB::transaction(function () use ($submission, $data) {
            $submission->update($data);
            return $submission;
        });
    }

    public function submit(Submission $submission)
    {
        if (!$submission->isEditable()) {
            throw new \Exception('Cannot submit submission in current status');
        }

        if (!$submission->hasScannedAttachments()) {
            throw new \Exception('Submission must have at least one scanned attachment');
        }

        return DB::transaction(function () use ($submission) {
            $submission->update(['status' => Submission::STATUS_SUBMITTED]);

            // Запускаем уведомление
            NotifyStatusChangedJob::dispatch($submission);

            return $submission;
        });
    }

    public function changeStatus(Submission $submission, string $newStatus, ?User $jury = null): Submission
    {
        // Проверяем, допустим ли такой переход
        if (!$submission->canJurySetStatus($newStatus)) {
            throw new \Exception('Недопустимый переход статуса');
        }

        return DB::transaction(function () use ($submission, $newStatus, $jury) {
            $oldStatus = $submission->status;

            $submission->update(['status' => $newStatus]);

            // Отправляем уведомление
            NotifyStatusChangedJob::dispatch($submission, $oldStatus);

            return $submission;
        });
    }

    public function addComment(Submission $submission, string $body, User $user)
    {
        return SubmissionComment::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'body' => $body,
        ]);
    }

    protected function isValidTransition(string $oldStatus, string $newStatus)
    {
        $allowedTransitions = [
            Submission::STATUS_DRAFT => [Submission::STATUS_SUBMITTED],
            Submission::STATUS_SUBMITTED => [Submission::STATUS_NEEDS_FIX, Submission::STATUS_ACCEPTED, Submission::STATUS_REJECTED],
            Submission::STATUS_NEEDS_FIX => [Submission::STATUS_SUBMITTED, Submission::STATUS_REJECTED],
            Submission::STATUS_ACCEPTED => [],
            Submission::STATUS_REJECTED => [],
        ];

        return in_array($newStatus, $allowedTransitions[$oldStatus] ?? []);
    }

    public function getSubmissionsForUser(User $user)
    {
        if ($user->isJury() || $user->isAdmin()) {
            return Submission::with(['user', 'contest', 'attachments'])->latest()->get();
        }

        return $user->submissions()->with(['contest', 'attachments'])->latest()->get();
    }

    public function findSubmissionForUser(User $user, int $id)
    {
        $query = Submission::with(['user', 'contest', 'attachments', 'comments.user']);

        if ($user->isParticipant()) {
            $query->where('user_id', $user->id);
        }

        return $query->findOrFail($id);
    }
}
