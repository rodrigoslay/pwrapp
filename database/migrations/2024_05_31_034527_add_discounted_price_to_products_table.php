<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{


// En la migración generada:
public function up()
{
    Schema::table('products', function (Blueprint $table) {
        $table->double('discounted_price', 8, 2)->nullable()->after('price');
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn('discounted_price');
    });
}
};
