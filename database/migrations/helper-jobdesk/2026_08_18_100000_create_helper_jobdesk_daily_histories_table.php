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
        Schema::create('helper_jobdesk_daily_histories', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_helper_jobdesk_daily_histories', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('helper_jobdesk_daily_histories');
        Schema::dropIfExists('_history_helper_jobdesk_daily_histories');
    }

    // 132830
    private function scheme(Blueprint $table, $is_history = false)
    {

        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('employee_whitelists_id', 'helper_jdh_employee_whitelists_id_idx');
            $table->index('subject_id', 'helper_jdh_subject_id_idx');
            $table->index('subject_type', 'helper_jdh_subject_type_idx');
        }

        $table->unsignedBigInteger('employee_whitelists_id')->nullable();
        $table->string('employee_whitelists_name')->nullable();
        $table->unsignedBigInteger('subject_id');
        $table->string('subject_type');
        $table->dateTime('start_at')->nullable();
        $table->dateTime('finish_at')->nullable();
        $table->text('note')->nullable();
        $table->double('amount', 20, 2)->nullable();

        $table->bigInteger('created_by')->unsigned()->nullable();
        $table->bigInteger('updated_by')->unsigned()->nullable();
        $table->bigInteger('deleted_by')->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
