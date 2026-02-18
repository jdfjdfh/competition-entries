<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class QuickSwitchRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Этот метод должен быть доступен только в локальной среде
        return app()->environment('local') && $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'role' => 'required|in:participant,jury,admin',
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Роль обязательна для выбора',
            'role.in' => 'Выбрана недопустимая роль',
        ];
    }
}
