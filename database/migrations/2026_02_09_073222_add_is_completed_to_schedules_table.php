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
       Schema::table('schedules', function (Blueprint $table) {
        $table->boolean('is_completed')->default(false); // 完了フラグ（初期値は未完了）
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('schedules', function (Blueprint $table) {
        $table->dropColumn('is_completed');
    });

    }
};
