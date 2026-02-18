<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            // Здесь будут правила для настроек
            // Пример:
            // 'site_name' => 'required|string|max:255',
            // 'maintenance_mode' => 'boolean',
        ];
    }
}
