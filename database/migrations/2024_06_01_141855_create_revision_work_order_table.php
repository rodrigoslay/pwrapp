<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevisionWorkOrderTable extends Migration
{
    public function up()
{
    Schema::create('revision_work_order', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('work_order_id');
        $table->unsignedBigInteger('revision_id');
        $table->unsignedBigInteger('fault_id'); // Add this line
        $table->boolean('status')->default(true);
        $table->timestamps();

        $table->foreign('work_order_id')->references('id')->on('work_orders')->onDelete('cascade');
        $table->foreign('revision_id')->references('id')->on('revisions')->onDelete('cascade');
        $table->foreign('fault_id')->references('id')->on('revision_faults')->onDelete('cascade'); // Add this line
    });
}

    public function down()
    {
        Schema::dropIfExists('revision_work_order');
    }
}
