<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Borrower extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['active','inactive','delinquent','closed','blacklisted'];

    protected $fillable = [
        'full_name','email','password','phone','address',
        'sex','occupation','birthday','monthly_income','civil_status','reference_no',
        'status','is_archived','archived_at',
    ];

    protected $casts = [
        'birthday'       => 'date',
        'monthly_income' => 'decimal:2',
        'is_archived'    => 'boolean',
        'archived_at'    => 'datetime',
    ];

    public function loans() { return $this->hasMany(Loan::class); }
public function transactions() { return $this->hasMany(BankTransaction::class); }


    /* ---------- Scopes ---------- */
    public function scopeArchived(Builder $q): Builder
    {
        return $q->where('is_archived', true);
    }

    public function scopeNotArchived(Builder $q): Builder
    {
        return $q->where('is_archived', false);
    }

    public function scopeFilter(Builder $q, array $filters): Builder
    {
        $q->when($filters['q'] ?? null, function (Builder $q, $qstr) {
            $q->where(function (Builder $qq) use ($qstr) {
                $qq->where('full_name', 'like', "%{$qstr}%")
                   ->orWhere('email', 'like', "%{$qstr}%")
                   ->orWhere('phone', 'like', "%{$qstr}%")
                   ->orWhere('reference_no', 'like', "%{$qstr}%")
                   ->orWhere('address', 'like', "%{$qstr}%");
            });
        });

        $q->when($filters['status'] ?? null, function (Builder $q, $status) {
            $statuses = is_array($status) ? $status : explode(',', $status);
            $q->whereIn('status', array_intersect($statuses, self::STATUSES));
        });

        // archived = 1|0
        if (array_key_exists('archived', $filters)) {
            $arch = filter_var($filters['archived'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if ($arch === true)   $q->where('is_archived', true);
            if ($arch === false)  $q->where('is_archived', false);
        }

        $q->when($filters['min_income'] ?? null, fn($q,$v) => $q->where('monthly_income','>=',$v));
        $q->when($filters['max_income'] ?? null, fn($q,$v) => $q->where('monthly_income','<=',$v));

        $q->when($filters['date_from'] ?? null, fn($q,$v) => $q->whereDate('created_at','>=',$v));
        $q->when($filters['date_to'] ?? null, fn($q,$v) => $q->whereDate('created_at','<=',$v));

        // Sorting
        $sortBy  = $filters['sort_by']  ?? 'created_at';
        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowed = ['created_at','full_name','status','monthly_income'];
        if (!in_array($sortBy, $allowed, true)) $sortBy = 'created_at';

        return $q->orderBy($sortBy, $sortDir);
    }
}
