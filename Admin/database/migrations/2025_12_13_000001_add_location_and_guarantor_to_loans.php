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
        // Add location fields to loans table
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('application_latitude', 10, 8)->nullable()->after('remarks');
            $table->decimal('application_longitude', 11, 8)->nullable()->after('application_latitude');
            $table->string('application_location_address', 255)->nullable()->after('application_longitude');
        });

        // Create guarantors table
        Schema::create('guarantors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id')->unique();
            $table->string('full_name', 255);
            $table->string('address', 255);
            $table->string('civil_status', 64)->nullable();
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guarantors');

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['application_latitude', 'application_longitude', 'application_location_address']);
        });
    }
};

