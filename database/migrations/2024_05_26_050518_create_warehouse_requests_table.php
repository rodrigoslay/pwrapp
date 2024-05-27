<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('warehouse_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('work_order_id');
            $table->integer('quantity');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('work_order_id')->references('id')->on('work_orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_requests');
    }
}
