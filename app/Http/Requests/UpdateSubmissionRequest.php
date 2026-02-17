<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Submission;

class UpdateSubmissionRequest extends FormRequest
{
    public function authorize()
    {
        $submission = $this->route('submission');
        return $this->user() &&
            $this->user()->id === $submission->user_id &&
            $submission->isEditable();
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ];
    }
}
