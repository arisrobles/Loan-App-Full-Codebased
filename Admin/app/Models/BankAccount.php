<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'bank_accounts';
    protected $fillable = ['code', 'name', 'timezone'];
    public $timestamps = true;

    /** Relationships **/
    public function transactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function statements()
    {
        return $this->hasMany(BankStatement::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'disbursement_account_id');
    }
}
