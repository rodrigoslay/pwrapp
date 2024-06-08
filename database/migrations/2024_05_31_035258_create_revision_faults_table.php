<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevisionFaultsTable extends Migration
{
    public function up()
    {
        Schema::create('revision_faults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_id')->constrained('revisions')->onDelete('cascade');
            $table->string('fallo');
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('revision_faults');
    }
};
