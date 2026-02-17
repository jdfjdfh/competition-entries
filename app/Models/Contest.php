<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Contest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'deadline_at',
        'is_active',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Вспомогательная функция для склонения слов (публичная)
     */
    public static function pluralForm($n, $forms)
    {
        return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] :
            ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
    }

    /**
     * Получить отформатированную дату окончания с округлением
     */
    public function getFormattedDeadlineAttribute()
    {
        $now = Carbon::now();
        $deadline = $this->deadline_at;

        if ($deadline->isPast()) {
            return 'Завершен ' . $deadline->diffForHumans();
        }

        $diffInDays = $now->diffInDays($deadline, false);
        $diffInHours = $now->diffInHours($deadline, false);
        $diffInMinutes = $now->diffInMinutes($deadline, false);

        if ($diffInDays > 30) {
            $months = floor($diffInDays / 30);
            return $months . ' ' . self::pluralForm($months, ['месяц', 'месяца', 'месяцев']);
        } elseif ($diffInDays > 7) {
            $weeks = floor($diffInDays / 7);
            return $weeks . ' ' . self::pluralForm($weeks, ['неделя', 'недели', 'недель']);
        } elseif ($diffInDays > 0) {
            return $diffInDays . ' ' . self::pluralForm($diffInDays, ['день', 'дня', 'дней']);
        } elseif ($diffInHours > 0) {
            return $diffInHours . ' ' . self::pluralForm($diffInHours, ['час', 'часа', 'часов']);
        } else {
            return $diffInMinutes . ' ' . self::pluralForm($diffInMinutes, ['минута', 'минуты', 'минут']);
        }
    }

    /**
     * Получить цвет статуса для даты
     */
    public function getDeadlineColorAttribute()
    {
        $now = Carbon::now();
        $deadline = $this->deadline_at;

        if ($deadline->isPast()) {
            return 'text-gray-500';
        }

        $diffInDays = $now->diffInDays($deadline, false);

        if ($diffInDays <= 1) {
            return 'text-red-600 font-bold';
        } elseif ($diffInDays <= 3) {
            return 'text-orange-600';
        } elseif ($diffInDays <= 7) {
            return 'text-yellow-600';
        } else {
            return 'text-green-600';
        }
    }

    /**
     * Получить иконку для даты
     */
    public function getDeadlineIconAttribute()
    {
        $now = Carbon::now();
        $deadline = $this->deadline_at;

        if ($deadline->isPast()) {
            return '✅'; // Завершен
        }

        $diffInDays = $now->diffInDays($deadline, false);

        if ($diffInDays <= 1) {
            return '⚠️'; // Срочно
        } elseif ($diffInDays <= 3) {
            return '⏳'; // Скоро
        } elseif ($diffInDays <= 7) {
            return '📅'; // На этой неделе
        } else {
            return '🗓️'; // Есть время
        }
    }

    /**
     * Получить точную дату в формате d.m.Y
     */
    public function getExactDeadlineAttribute()
    {
        return $this->deadline_at->format('d.m.Y H:i');
    }

    /**
     * Получить статус в виде текста
     */
    public function getDeadlineStatusAttribute()
    {
        $now = Carbon::now();
        $deadline = $this->deadline_at;

        if ($deadline->isPast()) {
            return 'Завершен';
        }

        $diffInDays = $now->diffInDays($deadline, false);

        if ($diffInDays <= 1) {
            return 'Срочно';
        } elseif ($diffInDays <= 3) {
            return 'Скоро';
        } elseif ($diffInDays <= 7) {
            return 'Истекает';
        } else {
            return 'Актуален';
        }
    }

    /**
     * Получить цвет статуса для бейджа
     */
    public function getDeadlineBadgeColorAttribute()
    {
        $now = Carbon::now();
        $deadline = $this->deadline_at;

        if ($deadline->isPast()) {
            return 'bg-gray-100 text-gray-800';
        }

        $diffInDays = $now->diffInDays($deadline, false);

        if ($diffInDays <= 1) {
            return 'bg-red-100 text-red-800';
        } elseif ($diffInDays <= 3) {
            return 'bg-orange-100 text-orange-800';
        } elseif ($diffInDays <= 7) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-green-100 text-green-800';
        }
    }
}
