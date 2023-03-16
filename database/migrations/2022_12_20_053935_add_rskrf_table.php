<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRskrfTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rskrf', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('link');
            $table->float('rating');
            $table->boolean('is_intruder')->default(false);
            $table->bigInteger('barcode')->unsigned();
            $table->timestamp('research_date')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::create('rskrf_research', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('rskrf_id')->unsigned();
            $table->text('name');
            $table->float('value');

            $table->foreign('rskrf_id')->references('id')->on('rskrf');
        });

        Schema::create('rskrf_info', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('rskrf_id')->unsigned();
            $table->text('type');
            $table->text('value');

            $table->foreign('rskrf_id')->references('id')->on('rskrf');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rskrf');
        Schema::dropIfExists('rskrf_research');
        Schema::dropIfExists('rskrf_info');
    }
}
