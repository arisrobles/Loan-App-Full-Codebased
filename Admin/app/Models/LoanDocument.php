<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDocument extends Model
{
    use HasFactory;

    protected $table = 'loan_documents';

    protected $fillable = [
        'borrower_id',
        'loan_id',
        'uploaded_by_user_id',
        'document_type',
        'original_name',
        'file_path',
        'mime_type',
        'remarks',
    ];

    /**
     * Borrower owner of the document.
     */
    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    /**
     * Related loan (optional).
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * User who uploaded the document (admin / officer / viewer).
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
