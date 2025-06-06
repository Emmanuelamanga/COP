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
        Schema::create('case_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->onDelete('cascade');
            $table->string('from_status');
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_status_histories');
    }
};
