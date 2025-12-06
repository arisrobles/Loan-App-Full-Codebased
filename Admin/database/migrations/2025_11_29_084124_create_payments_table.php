<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->unsignedBigInteger('borrower_id');
            $table->unsignedBigInteger('repayment_id')->nullable();
            $table->unsignedBigInteger('receipt_document_id')->nullable(); // Link to documents table
            $table->decimal('amount', 14, 2);
            $table->decimal('penalty_amount', 14, 2)->default(0.00);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('remarks')->nullable(); // Admin notes
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('paid_at')->nullable(); // When payment was made (user submission)
            $table->timestamp('approved_at')->nullable(); // When admin approved
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('borrower_id')->references('id')->on('borrowers')->onDelete('cascade');
            $table->foreign('repayment_id')->references('id')->on('repayments')->onDelete('set null');
            $table->foreign('receipt_document_id')->references('id')->on('documents')->onDelete('set null');
            $table->foreign('approved_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['loan_id', 'status']);
            $table->index(['borrower_id', 'status']);
            $table->index('status');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
