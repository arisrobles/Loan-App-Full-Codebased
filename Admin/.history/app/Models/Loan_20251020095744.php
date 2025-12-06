<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'loans';

    protected $fillable = [
        'reference',
        'borrower_id',
        'borrower_name',
        'principal_amount',
        'interest_rate',
        'application_date',
        'maturity_date',
        'release_date',
        'status',
        'total_disbursed',
        'total_paid',
        'total_penalties',
        'penalty_grace_days',
        'penalty_daily_rate',
        'is_active',
        'remarks',
        'disbursement_account_id', // add this if you later add FK to bank_accounts
    ];

    protected $casts = [
        'application_date'   => 'date',
        'maturity_date'      => 'date',
        'release_date'       => 'date',
        'is_active'          => 'boolean',
        'principal_amount'   => 'decimal:2',
        'interest_rate'      => 'decimal:4',
        'total_disbursed'    => 'decimal:2',
        'total_paid'         => 'decimal:2',
        'total_penalties'    => 'decimal:2',
        'penalty_daily_rate' => 'decimal:6',
    ];

    // -----------------------------------------------------
    // Status constants (keep in sync with database enum)
    // -----------------------------------------------------
    public const ST_NEW          = 'new_application';
    public const ST_REVIEW       = 'under_review';
    public const ST_APPROVED     = 'approved';
    public const ST_FOR_RELEASE  = 'for_release';
    public const ST_DISBURSED    = 'disbursed';
    public const ST_CLOSED       = 'closed';
    public const ST_REJECTED     = 'rejected';
    public const ST_RESTRUCTURED = 'restructured';

    // -----------------------------------------------------
    // Relationships
    // -----------------------------------------------------
    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function repayments()
    {
        return $this->hasMany(Repayment::class, 'loan_id');
    }

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class, 'loan_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'disbursement_account_id');
    }

    // -----------------------------------------------------
    // Query Scopes
    // -----------------------------------------------------

    /** Filter by high-level status (includes virtual "active") */
    public function scopeStatus($query, $status = null)
    {
        if (!$status) return $query;

        if ($status === 'active') {
            return $query->where('is_active', 1)
                         ->whereNotIn('status', [self::ST_CLOSED, self::ST_REJECTED]);
        }

        return $query->where('status', $status);
    }

    /** Quick search by reference or borrower name */
    public function scopeSearch($query, $term = null)
    {
        if (!$term) return $query;

        return $query->where(function ($qq) use ($term) {
            $qq->where('reference', 'like', "%{$term}%")
               ->orWhere('borrower_name', 'like', "%{$term}%");
        });
    }

    // -----------------------------------------------------
    // Accessors
    // -----------------------------------------------------

    /**
     * Accessor: outstanding principal (formatted string)
     * Uses BCMath if available for precision.
     */
    public function getOutstandingPrincipalAttribute(): string
    {
        $a = (string) ($this->total_disbursed ?? '0.00');
        $b = (string) ($this->total_paid ?? '0.00');

        if (function_exists('bcsub')) {
            $res = bcsub($a, $b, 2);
            // clamp negatives to 0.00
            return bccomp($res, '0', 2) === -1 ? '0.00' : $res;
        }

        $val = max(0.0, (float)$a - (float)$b);
        return number_format($val, 2, '.', '');
    }

    /** Dynamic label for status (UI helper) */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::ST_NEW          => 'New Application',
            self::ST_REVIEW       => 'Under Review',
            self::ST_APPROVED     => 'Approved',
            self::ST_FOR_RELEASE  => 'For Release',
            self::ST_DISBURSED    => 'Disbursed',
            self::ST_CLOSED       => 'Closed',
            self::ST_REJECTED     => 'Rejected',
            self::ST_RESTRUCTURED => 'Restructured',
            default               => ucfirst($this->status),
        };
    }
}
