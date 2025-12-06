<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankStatement extends Model
{
    use HasFactory;

    protected $table = 'bank_statements';
    protected $fillable = [
        'bank_account_id',
        'statement_end_date',
        'ending_balance',
        'source_name',
    ];

    protected $casts = [
        'statement_end_date' => 'date',
    ];

    /** Relationships **/
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
}
