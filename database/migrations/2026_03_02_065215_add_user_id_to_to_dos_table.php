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
        Schema::table('to_dos', function (Blueprint $table) {
        // 既存のデータがある場合のエラーを防ぐため、一旦 nullable で追加します
        $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
	});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('to_dos', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
	});
    }
};
