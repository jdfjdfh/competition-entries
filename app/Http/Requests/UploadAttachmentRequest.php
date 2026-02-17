<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Attachment;

class UploadAttachmentRequest extends FormRequest
{
    public function authorize()
    {
        $submission = $this->route('submission');
        return $this->user() &&
            $this->user()->id === $submission->user_id &&
            $submission->isEditable() &&
            $submission->attachments()->count() < 3;
    }

    public function rules()
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB in KB
                'mimes:pdf,zip,png,jpg,jpeg',
                'mimetypes:' . implode(',', Attachment::getAllowedMimeTypes()),
            ],
        ];
    }

    public function messages()
    {
        return [
            'file.max' => 'Размер файла не должен превышать 10MB',
            'file.mimes' => 'Разрешены только файлы: pdf, zip, png, jpg',
        ];
    }
}
