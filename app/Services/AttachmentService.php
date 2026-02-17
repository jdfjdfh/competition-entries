<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\ScanAttachmentJob;
use Illuminate\Support\Facades\Log;

class AttachmentService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('s3');
    }

    /**
     * Загрузка файла
     */
    public function upload(Submission $submission, User $user, UploadedFile $file): Attachment
    {
        Log::info('Начало загрузки в сервисе', [
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'file_name' => $file->getClientOriginalName()
        ]);

        try {
            // Генерируем уникальный ключ для S3
            $extension = $file->getClientOriginalExtension();
            $key = 'submissions/' . $submission->id . '/' . Str::uuid() . '.' . $extension;

            Log::info('Сгенерирован ключ для S3', ['key' => $key]);

            // Загружаем файл в S3
            $path = $this->disk->putFileAs('', $file, $key, 'public');

            if (!$path) {
                throw new \Exception('Не удалось загрузить файл в S3');
            }

            Log::info('Файл загружен в S3', ['path' => $path]);

            // Создаем запись в БД
            $attachment = Attachment::create([
                'submission_id' => $submission->id,
                'user_id' => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'storage_key' => $key,
                'status' => 'pending',
            ]);

            Log::info('Запись создана в БД', ['attachment_id' => $attachment->id]);

            // Запускаем задачу на сканирование
            ScanAttachmentJob::dispatch($attachment);
            Log::info('Задача на сканирование поставлена в очередь', ['attachment_id' => $attachment->id]);

            return $attachment;

        } catch (\Exception $e) {
            Log::error('Ошибка в AttachmentService::upload: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Проверить существование файла
     */
    public function exists(Attachment $attachment): bool
    {
        try {
            return $this->disk->exists($attachment->storage_key);
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке существования файла: ' . $e->getMessage(), [
                'attachment_id' => $attachment->id,
                'storage_key' => $attachment->storage_key
            ]);
            return false;
        }
    }

    /**
     * Отметить файл как проверенный
     */
    public function markScanned(Attachment $attachment): Attachment
    {
        Log::info('Отметка файла как проверенного', ['attachment_id' => $attachment->id]);

        $attachment->update([
            'status' => 'scanned',
            'rejection_reason' => null,
        ]);

        return $attachment;
    }

    /**
     * Отклонить файл
     */
    public function reject(Attachment $attachment, string $reason): Attachment
    {
        Log::info('Отклонение файла', ['attachment_id' => $attachment->id, 'reason' => $reason]);

        $attachment->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $attachment;
    }

    /**
     * Получить временную ссылку для скачивания
     */
    public function getSignedUrl(Attachment $attachment): string
    {
        try {
            // Создаем временную ссылку на 5 минут
            return $this->disk->temporaryUrl($attachment->storage_key, now()->addMinutes(5));
        } catch (\Exception $e) {
            Log::error('Ошибка при создании signed URL: ' . $e->getMessage(), [
                'attachment_id' => $attachment->id,
                'storage_key' => $attachment->storage_key
            ]);
            throw $e;
        }
    }

    /**
     * Удалить файл
     */
    public function delete(Attachment $attachment): bool
    {
        Log::info('Удаление файла', ['attachment_id' => $attachment->id]);

        try {
            // Удаляем файл из S3
            if ($this->disk->exists($attachment->storage_key)) {
                $this->disk->delete($attachment->storage_key);
                Log::info('Файл удален из S3');
            }

            // Удаляем запись из БД
            return $attachment->delete();

        } catch (\Exception $e) {
            Log::error('Ошибка при удалении файла: ' . $e->getMessage(), [
                'attachment_id' => $attachment->id
            ]);
            throw $e;
        }
    }
}
