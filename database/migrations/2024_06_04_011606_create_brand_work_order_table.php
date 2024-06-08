<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('brand_work_order', function (Blueprint $table) {
        $table->id();
        $table->foreignId('work_order_id')->constrained('work_orders');
        $table->foreignId('brand_id')->constrained('brands');
        $table->foreignId('car_model_id')->constrained('car_models');
        $table->year('year');
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('brand_work_order');
}
};
