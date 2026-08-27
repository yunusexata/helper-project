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
        Schema::create('helper_jobdesk_requests', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_helper_jobdesk_requests', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('helper_jobdesk_requests');
        Schema::dropIfExists('_history_helper_jobdesk_requests');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {

        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('day', 'helper_jobdesk_requests_day_idx');
            $table->index('employee_whitelists_id', 'helper_jobdesk_requests_employee_whitelists_id_idx');
        }

        $table->string('day');
        $table->string('activity_name');
        $table->text('note')->nullable();
        $table->unsignedBigInteger('employee_whitelists_id');
        $table->string('employee_whitelists_name');

        $table->bigInteger('created_by')->unsigned()->nullable();
        $table->bigInteger('updated_by')->unsigned()->nullable();
        $table->bigInteger('deleted_by')->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
