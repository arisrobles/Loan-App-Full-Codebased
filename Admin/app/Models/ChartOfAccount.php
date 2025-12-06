<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'code',
        'name',
        'description',
        'report',
        'group_account',
        'normal_balance',
        'debit_effect',
        'credit_effect',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    // ENUM-LIKE CONSTANTS
    public const REPORTS = [
        'Balance Sheets',
        'Profit and Losses',
    ];

    public const GROUPS = [
        'Assets',
        'Liabilities',
        'Equity',
        'Revenue (Income)',
        'Expense (COGS)',
        'Expenses',
    ];

    public const NORMAL_BALANCES = [
        'Debit',
        'Credit',
    ];

    public const EFFECTS = [
        'Increase',
        'Decrease',
    ];

    /* Scopes */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function (Builder $q, $qValue) {
                $q->where(function (Builder $inner) use ($qValue) {
                    $inner->where('code', 'like', "%{$qValue}%")
                          ->orWhere('name', 'like', "%{$qValue}%");
                });
            })
            ->when($filters['report'] ?? null, fn (Builder $q, $report) =>
                $q->where('report', $report)
            )
            ->when($filters['group_account'] ?? null, fn (Builder $q, $group) =>
                $q->where('group_account', $group)
            )
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $q) use ($filters) {
                $q->where('is_active', (bool) $filters['is_active']);
            });
    }
}