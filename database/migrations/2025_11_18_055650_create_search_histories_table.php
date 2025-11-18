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
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
    
            $table->string('query');
            $table->unsignedInteger('results_count')->nullable();
            $table->string('provider')->default('google');
    
            // Store raw JSON response (or subset) from provider
            $table->json('results_raw')->nullable();
    
            // Later you can add: user_id, ip, etc.
            // $table->foreignId('user_id')->nullable()->constrained();
    
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
