<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'loans';

    /*
    |--------------------------------------------------------------------------
    | Status constants
    |--------------------------------------------------------------------------
    */

    public const ST_NEW          = 'new_application';
    public const ST_REVIEW       = 'under_review';
    public const ST_APPROVED     = 'approved';
    public const ST_FOR_RELEASE  = 'for_release';
    public const ST_DISBURSED    = 'disbursed';
    public const ST_CLOSED       = 'closed';
    public const ST_REJECTED     = 'rejected';
    public const ST_CANCELLED    = 'cancelled';
    public const ST_RESTRUCTURED = 'restructured';

    /*
    |--------------------------------------------------------------------------
    | Mass assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'reference',
        'borrower_id',
        'disbursement_account_id',
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
    ];

    protected $casts = [
        'principal_amount'   => 'decimal:2',
        'interest_rate'      => 'decimal:4',
        'application_date'   => 'date',
        'maturity_date'      => 'date',
        'release_date'       => 'date',
        'total_disbursed'    => 'decimal:2',
        'total_paid'         => 'decimal:2',
        'total_penalties'    => 'decimal:2',
        'penalty_grace_days' => 'integer',
        'penalty_daily_rate' => 'decimal:6',
        'is_active'          => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function borrower()
    {
        return $this->belongsTo(Borrower::class, 'borrower_id');
    }

    public function repayments()
    {
        return $this->hasMany(Repayment::class, 'loan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'loan_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Filter by status (or all if null / empty).
     */
    public function scopeStatus($query, ?string $status)
    {
        if (!$status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Simple search on reference / borrower_name / remarks.
     */
    public function scopeSearch($query, ?string $q)
    {
        if (!$q) {
            return $query;
        }

        return $query->where(function ($qry) use ($q) {
            $qry->where('reference', 'like', "%{$q}%")
                ->orWhere('borrower_name', 'like', "%{$q}%")
                ->orWhere('remarks', 'like', "%{$q}%");
        });
    }
}