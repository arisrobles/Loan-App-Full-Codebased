<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

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
        'sort_order'
    ];

    public $timestamps = true;

    /** Relationships **/
    public function transactions()
    {
        return $this->hasMany(BankTransaction::class, 'account_id');
    }
}
