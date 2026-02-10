<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReason extends Model
{
    use HasFactory;

    protected $table = 'student_reasons';

    protected $fillable = [
        'type',
    ];

    public $timestamps = false;

    public const LEAVE_OF_ABSENCE = 'leave_of_absence';
    public const TRANSFER = 'transfer';
    public const WITHDRAWAL = 'withdrawal';
    public const GRADUATION = 'graduation';
    public const DELINQUENCY = 'delinquency';
    public const ADMINISTRATIVE = 'administrative';
    public const ADMINISTRATIVE_CORRECTION = 'administrative_correction';
    public const RETURNED_FROM_LEAVE = 'returned_from_leave';
    public const OTHER = 'other';

    public static function types(): array
    {
        return [
            self::LEAVE_OF_ABSENCE,
            self::TRANSFER,
            self::WITHDRAWAL,
            self::GRADUATION,
            self::DELINQUENCY,
            self::ADMINISTRATIVE,
            self::ADMINISTRATIVE_CORRECTION,
            self::RETURNED_FROM_LEAVE,
            self::OTHER,
        ];
    }
}
