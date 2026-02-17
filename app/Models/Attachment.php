<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'user_id',
        'original_name',
        'mime',
        'size',
        'storage_key',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'size' => 'integer',
        'status' => 'string',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SCANNED = 'scanned';
    const STATUS_REJECTED = 'rejected';

    public static function getAllowedMimeTypes()
    {
        return [
            'application/pdf',
            'application/zip',
            'image/png',
            'image/jpeg',
            'image/jpg',
        ];
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isScanned()
    {
        return $this->status === self::STATUS_SCANNED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
