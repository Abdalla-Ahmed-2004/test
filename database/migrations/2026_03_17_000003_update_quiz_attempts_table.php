<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Make score nullable so it can be set on submit, not at start
            $table->integer('score')->nullable()->change();
            $table->timestamp('started_at')->nullable()->after('score');
            $table->timestamp('completed_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->integer('score')->nullable(false)->change();
            $table->dropColumn(['started_at', 'completed_at']);
        });
    }
};
