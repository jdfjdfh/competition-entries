<?php

namespace App\Jobs;

use App\Models\Submission;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyStatusChangedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $submission;
    protected $oldStatus;

    public function __construct(Submission $submission, ?string $oldStatus = null)
    {
        $this->submission = $submission;
        $this->oldStatus = $oldStatus;
    }

    public function handle()
    {
        try {
            $user = $this->submission->user;
            $contest = $this->submission->contest;

            $data = [
                'submission_id' => $this->submission->id,
                'submission_title' => $this->submission->title,
                'contest_title' => $contest->title,
                'old_status' => $this->oldStatus,
                'new_status' => $this->submission->status,
                'changed_at' => now()->toDateTimeString(),
            ];

            // 1. Сохраняем уведомление в БД
            Notification::create([
                'user_id' => $user->id,
                'type' => 'status_changed',
                'data' => $data,
            ]);

            // 2. Отправляем email (если настроено)
            try {
                Mail::send('emails.status-changed', ['data' => $data], function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Статус вашей работы изменен');
                });
            } catch (\Exception $e) {
                Log::warning('Failed to send email notification', ['error' => $e->getMessage()]);
            }

            // 3. Логируем для отладки
            Log::info('Status changed notification sent', [
                'user_id' => $user->id,
                'submission_id' => $this->submission->id,
                'old_status' => $this->oldStatus,
                'new_status' => $this->submission->status,
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending status change notification', [
                'submission_id' => $this->submission->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
