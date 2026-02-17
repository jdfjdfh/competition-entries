<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attachment;
use App\Models\Submission;
use App\Services\AttachmentService;
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
    public function upload(Request $request, Submission $submission)
    {
        Log::info('Начало загрузки файла', [
            'submission_id' => $submission->id,
            'user_id' => Auth::id(),
            'has_file' => $request->hasFile('file'),
            'all_files' => $request->allFiles()
        ]);

        // Валидация
        try {
            $request->validate([
                'file' => 'required|file|max:10240|mimes:pdf,zip,png,jpg,jpeg'
            ]);
            Log::info('Валидация пройдена');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Ошибка валидации', ['errors' => $e->errors()]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $user = Auth::user();

            // Проверка прав
            if ($submission->user_id !== $user->id && !$user->isAdmin()) {
                Log::warning('Попытка загрузки без прав', [
                    'submission_user' => $submission->user_id,
                    'current_user' => $user->id
                ]);
                return redirect()->back()->with('error', 'У вас нет прав для загрузки файлов в эту работу');
            }

            // Проверка статуса работы
            if (!$submission->isEditable()) {
                Log::warning('Попытка загрузки в нередактируемом статусе', [
                    'status' => $submission->status
                ]);
                return redirect()->back()->with('error', 'Нельзя загружать файлы в текущем статусе работы');
            }

            // Проверка количества файлов
            $attachmentsCount = $submission->attachments()->count();
            if ($attachmentsCount >= 3) {
                Log::warning('Превышен лимит файлов', ['count' => $attachmentsCount]);
                return redirect()->back()->with('error', 'Достигнуто максимальное количество файлов (3)');
            }

            $file = $request->file('file');
            Log::info('Файл получен', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'tmp_path' => $file->getPathname()
            ]);

            // Загружаем файл
            $attachment = $this->attachmentService->upload(
                $submission,
                $user,
                $file
            );

            Log::info('Файл успешно загружен', [
                'attachment_id' => $attachment->id,
                'storage_key' => $attachment->storage_key
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
    public function destroy(Submission $submission, Attachment $attachment)
    {
        Log::info('Начало удаления файла', [
            'attachment_id' => $attachment->id,
            'submission_id' => $submission->id
        ]);

        try {
            // Проверяем, принадлежит ли файл этой заявке
            if ($attachment->submission_id !== $submission->id) {
                Log::warning('Файл не принадлежит заявке');
                return redirect()->back()->with('error', 'Файл не найден');
            }

            $user = Auth::user();

            // Проверяем права на удаление
            if ($submission->user_id !== $user->id && !$user->isAdmin()) {
                Log::warning('Попытка удаления без прав');
                return redirect()->back()->with('error', 'Доступ запрещен');
            }

            // Проверяем, можно ли редактировать работу
            if (!$submission->isEditable()) {
                Log::warning('Попытка удаления в нередактируемом статусе');
                return redirect()->back()->with('error', 'Нельзя удалить файл в текущем статусе работы');
            }

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
    public function download(Submission $submission, Attachment $attachment)
    {
        try {
            // Проверяем, принадлежит ли файл этой заявке
            if ($attachment->submission_id !== $submission->id) {
                abort(404, 'Файл не найден');
            }

            $user = Auth::user();

            // Проверка доступа
            if ($user->isParticipant() && $attachment->submission->user_id !== $user->id) {
                abort(403, 'Доступ запрещен');
            }

            // Проверяем существование файла
            if (!$this->attachmentService->exists($attachment)) {
                abort(404, 'Файл не найден в хранилище');
            }

            $url = $this->attachmentService->getSignedUrl($attachment);

            // Редирект на временную ссылку S3
            return redirect()->away($url);

        } catch (\Exception $e) {
            Log::error('Ошибка скачивания: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }
}
