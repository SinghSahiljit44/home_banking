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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['transfer_in', 'transfer_out', 'deposit', 'withdrawal']);
            $table->text('description')->nullable();
            $table->string('reference_code', 50)->unique();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
