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
        Schema::create('cashflows', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 20);
            $table->foreignId('user_id')->restrictOnDelete();
            $table->foreignId('group_id')->restrictOnDelete();
            $table->foreignId('category_id')->restrictOnDelete();
            $table->timestamp('transaction_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashflows');
    }
};
