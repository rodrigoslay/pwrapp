<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToProductWorkOrderTable extends Migration
{
    public function up()
    {
        Schema::table('product_work_order', function (Blueprint $table) {
            $table->string('status')->default('pendiente');
        });
    }

    public function down()
    {
        Schema::table('product_work_order', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}

