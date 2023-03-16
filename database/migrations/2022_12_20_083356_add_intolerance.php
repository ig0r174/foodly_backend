<?php

use App\Http\Resources\Allergens\Berries;
use App\Http\Resources\Allergens\Fruits;
use App\Http\Resources\Allergens\Gluten;
use App\Http\Resources\Allergens\Lactose;
use App\Http\Resources\Allergens\Nuts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIntolerance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intolerances', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('alias');
            $table->text('composition')->nullable()->default(null);
            $table->text('names')->nullable()->default(null);
        });

        foreach ([Gluten::class, Lactose::class, Fruits::class, Nuts::class, Berries::class] as $class) {
            $allergenClass = (new $class);
            DB::table('intolerances')->insert([
                "name" => $allergenClass->getName() == "lactose" ? "Непереносимость лактозы" : "Аллергия на " . mb_strtolower($allergenClass->getRussianName()),
                "alias" => $allergenClass->getName()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('intolerances');
    }
}
