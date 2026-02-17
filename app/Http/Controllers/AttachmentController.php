<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Attachment;
use App\Services\AttachmentService;
use App\Http\Requests\UploadAttachmentRequest;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    protected $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function upload(UploadAttachmentRequest $request, Submission $submission)
    {
        try {
            $attachment = $this->attachmentService->upload(
                $submission,
                $request->file('file'),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Файл успешно загружен',
                'attachment' => $attachment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке файла: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download(Submission $submission, Attachment $attachment)
    {
        // Проверяем, принадлежит ли файл этой заявке
        if ($attachment->submission_id !== $submission->id) {
            abort(404);
        }

        // Проверяем права доступа
        $user = auth()->user();
        if ($user->isParticipant() && $user->id !== $submission->user_id) {
            abort(403);
        }

        try {
            $url = $this->attachmentService->getSignedUrl($attachment);
            return redirect()->away($url);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ошибка при получении файла');
        }
    }

    public function delete(Submission $submission, Attachment $attachment)
    {
        // Проверяем, принадлежит ли файл этой заявке
        if ($attachment->submission_id !== $submission->id) {
            abort(404);
        }

        // Проверяем права доступа
        $user = auth()->user();
        if ($user->id !== $submission->user_id || !$submission->isEditable()) {
            abort(403);
        }

        try {
            $this->attachmentService->delete($attachment);

            return response()->json([
                'success' => true,
                'message' => 'Файл успешно удален'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении файла'
            ], 500);
        }
    }
}
