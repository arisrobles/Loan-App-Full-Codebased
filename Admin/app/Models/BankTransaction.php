<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BankTransaction extends Model
{
    protected $table = 'bank_transactions';

    protected $fillable = [
        'bank_account_id','account_id', 'ref_code','kind','tx_date','contact_display','description',
        'spent','received','reconcile_status','ledger_contact','account_name',
        'remarks','tx_class','source',
        'status','posted_at','is_transfer','bank_text','match_id',
    ];

    protected $casts = [
        'tx_date'     => 'date',
        'spent'       => 'decimal:2',
        'received'    => 'decimal:2',
        'posted_at'   => 'datetime',
        'is_transfer' => 'boolean',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function account()    // link to COA
    {
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'account_id');
    }

    // Convenience scopes (work even if columns don’t exist, as long as you only call when present)
    public function scopeForAccount(Builder $q, int $accountId): Builder
    {
        return $q->where('bank_account_id', $accountId);
    }

    public function scopeDateRange(Builder $q, ?string $from, ?string $to): Builder
    {
        if ($from) $q->whereDate('tx_date', '>=', $from);
        if ($to)   $q->whereDate('tx_date', '<=', $to);
        return $q;
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        $term = trim($term);
        return $q->where(function($qq) use ($term) {
            $qq->where('contact_display', 'like', "%$term%")
               ->orWhere('description', 'like', "%$term%")
               ->orWhere('ledger_contact', 'like', "%$term%")
               ->orWhere('account_name', 'like', "%$term%")
               ->orWhere('remarks', 'like', "%$term%")
               ->orWhere('tx_class', 'like', "%$term%")
               ->orWhere('ref_code', 'like', "%$term%")
               ->orWhereRaw('CAST(spent AS CHAR) LIKE ?', ["%$term%"])
               ->orWhereRaw('CAST(received AS CHAR) LIKE ?', ["%$term%"]);
        });
    }
}
