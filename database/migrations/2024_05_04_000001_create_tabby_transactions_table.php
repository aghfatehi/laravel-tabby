<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabby_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tabby_payment_id')->nullable()->index();
            $table->string('tabby_session_id')->nullable()->index();
            $table->decimal('amount', 20, 2)->default(0);
            $table->string('currency', 3)->default('SAR');
            $table->string('status')->nullable()->index();
            $table->string('payment_type')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('error_message')->nullable();
            $table->nullableMorphs('billable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabby_transactions');
    }
};
