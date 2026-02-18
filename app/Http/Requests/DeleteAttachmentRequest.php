<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Attachment;

class DeleteAttachmentRequest extends FormRequest
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

        // Проверка прав: владелец работы или админ
        if ($submission->user_id !== $user->id && !$user->isAdmin()) {
            return false;
        }

        // Проверка статуса работы
        if (!$submission->isEditable()) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            // Нет правил валидации для удаления
        ];
    }

    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException('У вас нет прав для удаления этого файла');
    }
}
