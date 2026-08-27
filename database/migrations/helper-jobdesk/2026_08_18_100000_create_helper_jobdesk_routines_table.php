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
        Schema::create('helper_jobdesk_routines', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_helper_jobdesk_routines', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('helper_jobdesk_routines');
        Schema::dropIfExists('_history_helper_jobdesk_routines');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {

        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('day', 'helper_jobdesk_routines_day_idx');
        }

        $table->string('day');
        $table->string('activity_name');
        $table->text('note')->nullable();
        $table->integer('order')->default(0);

        $table->bigInteger('created_by')->unsigned()->nullable();
        $table->bigInteger('updated_by')->unsigned()->nullable();
        $table->bigInteger('deleted_by')->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
