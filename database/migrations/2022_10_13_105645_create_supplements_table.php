<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplements', function (Blueprint $table) {
            $table->id();
            $table->string('eurocode', 10)->unique()->nullable();
            $table->string('full_name')->nullable();
            $table->text('description')->nullable();
            $table->text('harm')->nullable();
            $table->text('benefit')->nullable();
            $table->tinyInteger('level')->default(0);
            $table->dateTime('updated_at');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplements');
    }
}
