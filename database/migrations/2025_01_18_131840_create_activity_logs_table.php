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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment("e.g., 'form_submission', 'cv_download'.");
            $table->string('user_ip')->nullable();
            $table->text('metadata')->nullable()->comment("Optional additional details.");
            $table->string('user_agent')->nullable()->comment("Browser or device info.");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
