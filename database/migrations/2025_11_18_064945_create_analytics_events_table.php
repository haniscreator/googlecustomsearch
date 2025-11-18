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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();

            $table->string('event_type'); // e.g. "search_performed"
            $table->string('query')->nullable();
            $table->unsignedInteger('results_count')->nullable();
            $table->string('provider')->nullable();

            // extra JSON payload if you want to store more details later
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }

};
