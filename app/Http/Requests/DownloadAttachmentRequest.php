<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DownloadAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');
        $attachment = $this->route('attachment');
        $user = $this->user();

        if (!$user) {
            return false;
        }

        // Проверяем, принадлежит ли файл этой заявке
        if ($attachment->submission_id !== $submission->id) {
            return false;
        }

        // Проверка доступа
        if ($user->isParticipant() && $attachment->submission->user_id !== $user->id) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException('Доступ к файлу запрещен');
    }
}
