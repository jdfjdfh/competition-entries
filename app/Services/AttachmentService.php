<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Submission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('s3');
    }

    public function upload(Submission $submission, UploadedFile $file, int $userId)
    {
        // Генерируем уникальный ключ для S3
        $key = 'submissions/' . $submission->id . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Загружаем файл в S3
        $path = $this->disk->putFileAs('', $file, $key, 'public');

        // Создаем запись в БД
        $attachment = Attachment::create([
            'submission_id' => $submission->id,
            'user_id' => $userId,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'storage_key' => $key,
            'status' => Attachment::STATUS_PENDING,
        ]);

        // Запускаем задачу на сканирование
        \App\Jobs\ScanAttachmentJob::dispatch($attachment);

        return $attachment;
    }

    public function markScanned(Attachment $attachment)
    {
        $attachment->update([
            'status' => Attachment::STATUS_SCANNED,
            'rejection_reason' => null,
        ]);

        return $attachment;
    }

    public function reject(Attachment $attachment, string $reason)
    {
        $attachment->update([
            'status' => Attachment::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        return $attachment;
    }

    public function getSignedUrl(Attachment $attachment)
    {
        // Создаем временную ссылку на 5 минут
        return $this->disk->temporaryUrl($attachment->storage_key, now()->addMinutes(5));
    }

    public function delete(Attachment $attachment)
    {
        // Удаляем файл из S3
        $this->disk->delete($attachment->storage_key);

        // Удаляем запись из БД
        return $attachment->delete();
    }
}
