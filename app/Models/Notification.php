<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Маппинг статусов на русский язык
    const STATUS_NAMES = [
        'draft' => 'Черновик',
        'submitted' => 'На проверке',
        'needs_fix' => 'Требует доработки',
        'accepted' => 'Принята',
        'rejected' => 'Отклонена',
    ];

    // Цвета для статусов
    const STATUS_COLORS = [
        'draft' => 'bg-gray-100 text-gray-800',
        'submitted' => 'bg-blue-100 text-blue-800',
        'needs_fix' => 'bg-yellow-100 text-yellow-800',
        'accepted' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Получить ссылку на связанный объект
     */
    public function getLinkAttribute()
    {
        return match($this->type) {
            'status_changed' => route('submissions.show', $this->data['submission_id'] ?? 0),
            'new_comment' => route('submissions.show', $this->data['submission_id'] ?? 0),
            'new_submission' => route('submissions.show', $this->data['submission_id'] ?? 0),
            'deadline_reminder' => route('contests.show', $this->data['contest_id'] ?? 0),
            default => '#',
        };
    }

    /**
     * Получить иконку уведомления
     */
    public function getIconAttribute()
    {
        return match($this->type) {
            'status_changed' => '🔄',
            'new_comment' => '💬',
            'new_submission' => '📝',
            'deadline_reminder' => '⏰',
            default => '📢',
        };
    }

    /**
     * Получить цвет фона
     */
    public function getBgColorAttribute()
    {
        return match($this->type) {
            'status_changed' => 'bg-blue-50',
            'new_comment' => 'bg-green-50',
            'new_submission' => 'bg-purple-50',
            'deadline_reminder' => 'bg-yellow-50',
            default => 'bg-gray-50',
        };
    }

    /**
     * Получить текст уведомления
     */
    public function getMessageAttribute()
    {
        $data = $this->data;

        return match($this->type) {
            'status_changed' => $this->getStatusChangedMessage($data),
            'new_comment' => $this->getNewCommentMessage($data),
            'new_submission' => $this->getNewSubmissionMessage($data),
            'deadline_reminder' => $this->getDeadlineReminderMessage($data),
            default => 'Новое уведомление',
        };
    }

    private function getStatusChangedMessage($data)
    {
        $newStatus = $data['new_status'] ?? '';
        $statusText = self::STATUS_NAMES[$newStatus] ?? $newStatus;

        return "Статус работы \"{$data['submission_title']}\" изменен на \"{$statusText}\"";
    }

    private function getNewCommentMessage($data)
    {
        return "{$data['comment_author']} оставил(а) комментарий к работе \"{$data['submission_title']}\"";
    }

    private function getNewSubmissionMessage($data)
    {
        return "Новая работа \"{$data['submission_title']}\" от {$data['author_name']} ожидает проверки";
    }

    private function getDeadlineReminderMessage($data)
    {
        $days = $data['days_left'] ?? 0;
        $dayText = $this->pluralForm($days, ['день', 'дня', 'дней']);

        return "До окончания конкурса \"{$data['contest_title']}\" осталось {$days} {$dayText}";
    }

    /**
     * Получить название старого статуса на русском
     */
    public function getOldStatusNameAttribute()
    {
        $oldStatus = $this->data['old_status'] ?? null;
        return $oldStatus ? (self::STATUS_NAMES[$oldStatus] ?? $oldStatus) : null;
    }

    /**
     * Получить название нового статуса на русском
     */
    public function getNewStatusNameAttribute()
    {
        $newStatus = $this->data['new_status'] ?? null;
        return $newStatus ? (self::STATUS_NAMES[$newStatus] ?? $newStatus) : null;
    }

    /**
     * Получить цвет для старого статуса
     */
    public function getOldStatusColorAttribute()
    {
        $oldStatus = $this->data['old_status'] ?? null;
        return $oldStatus ? (self::STATUS_COLORS[$oldStatus] ?? 'bg-gray-100 text-gray-800') : 'bg-gray-100 text-gray-800';
    }

    /**
     * Получить цвет для нового статуса
     */
    public function getNewStatusColorAttribute()
    {
        $newStatus = $this->data['new_status'] ?? null;
        return $newStatus ? (self::STATUS_COLORS[$newStatus] ?? 'bg-gray-100 text-gray-800') : 'bg-gray-100 text-gray-800';
    }

    private function pluralForm($n, $forms)
    {
        return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] :
            ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
    }
}
