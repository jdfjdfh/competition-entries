<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'contest_id',
        'user_id',
        'title',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_NEEDS_FIX = 'needs_fix';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_NEEDS_FIX,
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
        ];
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function comments()
    {
        return $this->hasMany(SubmissionComment::class);
    }

    public function hasScannedAttachments()
    {
        return $this->attachments()->where('status', 'scanned')->exists();
    }

    public function isEditable()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_NEEDS_FIX]);
    }

    /**
     * Получить название статуса на русском
     */
    public function getStatusNameAttribute()
    {
        return match($this->status) {
            'draft' => 'Черновик',
            'submitted' => 'На проверке',
            'needs_fix' => 'Требует доработки',
            'accepted' => 'Принято',
            'rejected' => 'Отклонено',
            default => $this->status,
        };
    }

    /**
     * Получить цветовой класс для статуса
     */
    public function getStatusColorClassAttribute()
    {
        return match($this->status) {
            'accepted' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'submitted' => 'bg-blue-100 text-blue-800',
            'needs_fix' => 'bg-yellow-100 text-yellow-800',
            'draft' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Получить допустимые следующие статусы для текущего статуса
     */
    public function getAllowedNextStatuses(): array
    {
        $transitions = [
            'draft' => ['submitted'], // Черновик можно только отправить
            'submitted' => ['needs_fix', 'accepted', 'rejected'], // На проверке - три варианта
            'needs_fix' => ['submitted', 'rejected'], // На доработке - можно отправить или отклонить
            'accepted' => [], // Принято - ничего нельзя менять
            'rejected' => [], // Отклонено - ничего нельзя менять
        ];

        return $transitions[$this->status] ?? [];
    }

    /**
     * Проверить, может ли жюри установить указанный статус
     */
    public function canJurySetStatus(string $newStatus): bool
    {
        return in_array($newStatus, $this->getAllowedNextStatuses());
    }

    /**
     * Получить человекочитаемое название статуса
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            'draft' => 'Черновик',
            'submitted' => 'На проверке',
            'needs_fix' => 'Требует доработки',
            'accepted' => 'Принято',
            'rejected' => 'Отклонено',
            default => $this->status,
        };
    }
}
