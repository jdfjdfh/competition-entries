<?php

namespace App\Jobs;

use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanAttachmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $attachment;

    public function __construct(Attachment $attachment)
    {
        $this->attachment = $attachment;
    }

    public function handle(AttachmentService $attachmentService)
    {
        try {
            // Проверяем имя файла
            if (!$this->validateFileName($this->attachment->original_name)) {
                $attachmentService->reject($this->attachment, 'Имя файла содержит недопустимые символы');
                return;
            }

            // Проверяем MIME тип
            if (!in_array($this->attachment->mime, Attachment::getAllowedMimeTypes())) {
                $attachmentService->reject($this->attachment, 'Недопустимый тип файла');
                return;
            }

            // Проверяем размер
            if ($this->attachment->size > 10 * 1024 * 1024) { // 10MB
                $attachmentService->reject($this->attachment, 'Размер файла превышает 10MB');
                return;
            }

            // Все проверки пройдены
            $attachmentService->markScanned($this->attachment);

            Log::info('Attachment scanned successfully', ['attachment_id' => $this->attachment->id]);

        } catch (\Exception $e) {
            Log::error('Error scanning attachment', [
                'attachment_id' => $this->attachment->id,
                'error' => $e->getMessage()
            ]);

            $attachmentService->reject($this->attachment, 'Ошибка при сканировании файла');
        }
    }

    protected function validateFileName(string $fileName)
    {
        // Проверяем, что имя файла содержит только допустимые символы
        return preg_match('/^[a-zA-Z0-9\s._-]+$/', $fileName);
    }
}
