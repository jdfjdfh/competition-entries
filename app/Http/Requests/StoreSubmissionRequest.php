<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->isParticipant();
    }

    public function rules()
    {
        return [
            'contest_id' => 'required|exists:contests,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ];
    }
}
