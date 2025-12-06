<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'borrower_id',
        'loan_id',
        'type',
        'title',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Notification types
    public const TYPE_INFO = 'info';
    public const TYPE_REMINDER = 'reminder';
    public const TYPE_APPROVAL = 'approval';
    public const TYPE_PAYMENT_RECEIVED = 'payment_received';
    public const TYPE_PAYMENT_DUE = 'payment_due';
    public const TYPE_LOAN_STATUS_CHANGE = 'loan_status_change';

    // Relationships
    public function borrower()
    {
        return $this->belongsTo(Borrower::class, 'borrower_id');
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    // Helper method to create notification
    public static function createForBorrower(
        int $borrowerId,
        string $type,
        string $title,
        string $message,
        ?int $loanId = null
    ): self {
        return self::create([
            'borrower_id' => $borrowerId,
            'loan_id' => $loanId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }
}

