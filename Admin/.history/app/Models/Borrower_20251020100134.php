<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Borrower extends Model
{
    use HasFactory, SoftDeletes;

    /** Enum-like statuses */
    public const STATUSES = [
        'active',
        'inactive',
        'delinquent',
        'closed',
        'blacklisted',
    ];

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'email',
        'phone',
        'address',
        'sex',
        'occupation',
        'birthday',
        'monthly_income',
        'civil_status',
        'reference_no',
        'status',
        'is_archived',
        'archived_at',
    ];

    protected $casts = [
        'birthday'       => 'date',
        'monthly_income' => 'decimal:2',
        'is_archived'    => 'boolean',
        'archived_at'    => 'datetime',
    ];

    // --------------------------------------------------
    // Relationships
    // --------------------------------------------------

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class, 'borrower_id');
    }

    // --------------------------------------------------
    // Accessors
    // --------------------------------------------------

    public function getNameAttribute(): string
    {
        return $this->full_name ?: trim("{$this->first_name} {$this->last_name}");
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status ?? 'Unknown');
    }

    public function getArchivedLabelAttribute(): string
    {
        return $this->is_archived ? 'Archived' : 'Active';
    }

    // --------------------------------------------------
    // Scopes
    // --------------------------------------------------

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
        // Keyword search
        $q->when($filters['q'] ?? null, function (Builder $q, $term) {
            $q->where(function (Builder $qq) use ($term) {
                $qq->where('full_name', 'like', "%{$term}%")
                   ->orWhere('email', 'like', "%{$term}%")
                   ->orWhere('phone', 'like', "%{$term}%")
                   ->orWhere('reference_no', 'like', "%{$term}%")
                   ->orWhere('address', 'like', "%{$term}%");
            });
        });

        // Status filter
        $q->when($filters['status'] ?? null, function (Builder $q, $status) {
            $statuses = is_array($status) ? $status : explode(',', $status);
            $valid = array_intersect($statuses, self::STATUSES);
            if ($valid) $q->whereIn('status', $valid);
        });

        // Archived
        if (array_key_exists('archived', $filters)) {
            $arch = filter_var($filters['archived'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if ($arch === true)  $q->where('is_archived', true);
            if ($arch === false) $q->where('is_archived', false);
        }

        // Income range
        $q->when($filters['min_income'] ?? null, fn($q, $v) => $q->where('monthly_income', '>=', $v));
        $q->when($filters['max_income'] ?? null, fn($q, $v) => $q->where('monthly_income', '<=', $v));

        // Date range
        $q->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v));
        $q->when($filters['date_to'] ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v));

        // Sorting
        $sortBy  = $filters['sort_by']  ?? 'created_at';
        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowed = ['created_at', 'full_name', 'status', 'monthly_income'];

        return $q->orderBy(in_array($sortBy, $allowed) ? $sortBy : 'created_at', $sortDir);
    }

    // --------------------------------------------------
    // Utility
    // --------------------------------------------------

    /** Export-ready CSV headers */
    public static function exportableHeaders(): array
    {
        return [
            'Reference No',
            'Full Name',
            'Email',
            'Phone',
            'Address',
            'Status',
            'Monthly Income',
            'Archived',
        ];
    }
}
