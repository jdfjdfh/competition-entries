<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Submission;
use App\Services\AttachmentService;
use App\Http\Requests\UploadAttachmentRequest;
use App\Http\Requests\DeleteAttachmentRequest;
use App\Http\Requests\DownloadAttachmentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttachmentController extends Controller
{
    protected AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    /**
     * Загрузка файла
     */
    public function upload(UploadAttachmentRequest $request, Submission $submission)
    {
        Log::info('Начало загрузки файла', [
            'submission_id' => $submission->id,
            'user_id' => Auth::id(),
        ]);

        try {
            $user = Auth::user();
            $file = $request->file('file');

            $attachment = $this->attachmentService->upload(
                $submission,
                $user,
                $file
            );

            Log::info('Файл успешно загружен', [
                'attachment_id' => $attachment->id,
            ]);

            return redirect()->back()->with('success', 'Файл успешно загружен и отправлен на проверку');

        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке файла: ' . $e->getMessage(), [
                'submission_id' => $submission->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Ошибка при загрузке файла: ' . $e->getMessage());
        }
    }

    /**
     * Удаление файла
     */
    public function destroy(DeleteAttachmentRequest $request, Submission $submission, Attachment $attachment)
    {
        Log::info('Начало удаления файла', [
            'attachment_id' => $attachment->id,
            'submission_id' => $submission->id
        ]);

        try {
            $this->attachmentService->delete($attachment);

            Log::info('Файл успешно удален');

            return redirect()->back()->with('success', 'Файл успешно удален');

        } catch (\Exception $e) {
            Log::error('Ошибка удаления: ' . $e->getMessage(), [
                'attachment_id' => $attachment->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Ошибка удаления: ' . $e->getMessage());
        }
    }

    /**
     * Скачивание файла
     */
    public function download(DownloadAttachmentRequest $request, Submission $submission, Attachment $attachment)
    {
        try {
            // Проверяем существование файла
            if (!$this->attachmentService->exists($attachment)) {
                abort(404, 'Файл не найден в хранилище');
            }

            $url = $this->attachmentService->getSignedUrl($attachment);

            return redirect()->away($url);

        } catch (\Exception $e) {
            Log::error('Ошибка скачивания: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }
}
