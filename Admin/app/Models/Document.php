<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = [
        'borrower_id',
        'loan_id',
        'document_type',
        'file_name',
        'file_url',
        'file_size',
        'mime_type',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}

