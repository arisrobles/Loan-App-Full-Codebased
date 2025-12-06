<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Repayment extends Model
{
    use HasFactory;

    protected $table = 'repayments';

    protected $fillable = [
        'loan_id',
        'due_date',
        'amount_due',
        'amount_paid',
        'paid_at',
        'penalty_applied',
        'note',          // DB column
        // if you later rename the column to remarks, add 'remarks' here instead
    ];

    protected $casts = [
        'due_date'        => 'date',
        'amount_due'      => 'decimal:2',
        'amount_paid'     => 'decimal:2',
        'penalty_applied' => 'decimal:2',
        'paid_at'         => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'repayment_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Outstanding = amount_due - amount_paid, clamped to >= 0.00 (string, 2dp)
     */
    public function getOutstandingAttribute(): string
    {
        $due  = (string) ($this->amount_due ?? '0.00');
        $paid = (string) ($this->amount_paid ?? '0.00');

        if (function_exists('bcsub')) {
            $res = bcsub($due, $paid, 2);
            return bccomp($res, '0', 2) === -1 ? '0.00' : $res;
        }

        $val = max(0.0, (float) $due - (float) $paid);
        return number_format($val, 2, '.', '');
    }

    /**
     * Days overdue (int) relative to today in Asia/Manila, clamped to >= 0.
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }

        $today   = Carbon::now('Asia/Manila')->startOfDay();
        $dueDate = $this->due_date->copy()->startOfDay();

        return max(0, $dueDate->diffInDays($today, false));
    }

    /**
     * Compute suggested penalty (string, 2dp)
     * Formula: max( days_overdue - grace, 0 ) * daily_rate * outstanding
     *  - daily_rate from Loan.penalty_daily_rate (e.g. 0.01 for 1%/day)
     *  - grace from Loan.penalty_grace_days
     */
    public function computePenalty(?float $overrideDailyRate = null, ?int $overrideGraceDays = null): string
    {
        $loan = $this->loan;

        // if no loan attached, no penalty
        if (!$loan) {
            return '0.00';
        }

        $grace      = $overrideGraceDays ?? (int) ($loan->penalty_grace_days ?? 0);
        $dailyRateF = $overrideDailyRate ?? (float) ($loan->penalty_daily_rate ?? 0.0);

        $days = max(0, $this->days_overdue - $grace);
        if ($days === 0 || $dailyRateF <= 0) {
            return '0.00';
        }

        $out = $this->outstanding; // decimal string

        if (function_exists('bcmul')) {
            // penalty = outstanding * dailyRate * days
            $rateStr = rtrim(rtrim(number_format($dailyRateF, 6, '.', ''), '0'), '.') ?: '0';

            $p1 = bcmul($out, $rateStr, 6);
            $p2 = bcmul($p1, (string) $days, 6);

            // round to 2dp
            return bcadd($p2, '0', 2);
        }

        $penalty = ((float) $out) * $dailyRateF * $days;
        return number_format($penalty, 2, '.', '');
    }
}