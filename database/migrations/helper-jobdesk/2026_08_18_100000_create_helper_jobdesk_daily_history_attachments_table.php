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
        Schema::create('helper_jobdesk_daily_history_attachments', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_helper_jobdesk_daily_history_attachments', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('helper_jobdesk_daily_history_attachments');
        Schema::dropIfExists('_history_helper_jobdesk_daily_history_attachments');
    }

    // 132830
    private function scheme(Blueprint $table, $is_history = false)
    {

        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('helper_jobdesk_daily_histories', 'helper_jdha_helper_jobdesk_daily_histories_idx');
        }

        $table->unsignedBigInteger('helper_jobdesk_daily_histories')->nullable();

        $table->string('disk');              // local / s3
        $table->text('path');                // storage path
        $table->text('note')->nullable();    // Attachment Note

        $table->bigInteger('created_by')->unsigned()->nullable();
        $table->bigInteger('updated_by')->unsigned()->nullable();
        $table->bigInteger('deleted_by')->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
