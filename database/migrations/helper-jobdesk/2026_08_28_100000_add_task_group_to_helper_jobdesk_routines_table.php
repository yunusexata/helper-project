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
        Schema::table('helper_jobdesk_routines', function (Blueprint $table) {
            $table->string('task_group')->nullable()->after('day');
        });

        Schema::table('_history_helper_jobdesk_routines', function (Blueprint $table) {
            $table->string('task_group')->nullable()->after('day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helper_jobdesk_routines', function (Blueprint $table) {
            $table->dropColumn('task_group');
        });

        Schema::table('_history_helper_jobdesk_routines', function (Blueprint $table) {
            $table->dropColumn('task_group');
        });
    }
};
