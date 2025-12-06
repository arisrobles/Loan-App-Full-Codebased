<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'loan_id',
        'borrower_id',
        'repayment_id',
        'receipt_document_id',
        'amount',
        'penalty_amount',
        'status',
        'remarks',
        'rejection_reason',
        'approved_by_user_id',
        'paid_at',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function repayment()
    {
        return $this->belongsTo(Repayment::class);
    }

    public function receiptDocument()
    {
        return $this->belongsTo(Document::class, 'receipt_document_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}

