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
        Schema::create('employee_whitelists', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_employee_whitelists', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_whitelists');
        Schema::dropIfExists('_history_employee_whitelists');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {

        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
            $table->string('employee_id');
            $table->string('email');
        } else {
            $table->string('employee_id')->unique();
            $table->string('email')->unique();
        }

        $table->string('name');
        $table->string('division')->nullable();

        $table->bigInteger('created_by')->unsigned()->nullable();
        $table->bigInteger('updated_by')->unsigned()->nullable();
        $table->bigInteger('deleted_by')->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
