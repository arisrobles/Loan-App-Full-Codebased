<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'code', 'name', 'description',
        'report',        // 'Balance Sheet' | 'Profit and Losses'
        'group_account', // 'Assets' | 'Liabilities' | 'Equity' | 'Revenue (Income)' | 'Expense (COGS)' | 'Expenses'
        'normal_balance',// 'Debit' | 'Credit' | NULL
        'debit_effect',  // 'Increase' | 'Decrease' | NULL
        'credit_effect', // 'Increase' | 'Decrease' | NULL
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];
}
