<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Submission;

class ChangeSubmissionStatusRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->isJury();
    }

    public function rules()
    {
        $allowedTransitions = [
            Submission::STATUS_SUBMITTED => [Submission::STATUS_NEEDS_FIX, Submission::STATUS_ACCEPTED, Submission::STATUS_REJECTED],
            Submission::STATUS_NEEDS_FIX => [Submission::STATUS_SUBMITTED, Submission::STATUS_REJECTED],
        ];

        $submission = $this->route('submission');
        $currentStatus = $submission->status;

        $allowedNextStatuses = $allowedTransitions[$currentStatus] ?? [];

        return [
            'status' => 'required|in:' . implode(',', $allowedNextStatuses),
            'comment' => 'required_if:status,' . Submission::STATUS_NEEDS_FIX . '|nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'comment.required_if' => 'Комментарий обязателен при запросе доработки',
        ];
    }
}
