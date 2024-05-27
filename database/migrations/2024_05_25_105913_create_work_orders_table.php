<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable();
            $table->float('discount_percentage')->nullable();
            $table->float('discount_amount')->nullable();
            $table->float('subtotal');
            $table->float('tax');
            $table->float('total');
            $table->boolean('review')->default(false);
            $table->foreignId('executive_id')->constrained('users');
            $table->foreignId('mechanic_id')->constrained('users');
            $table->foreignId('client_id')->constrained();
            $table->foreignId('vehicle_id')->constrained();
            $table->integer('entry_mileage');
            $table->integer('exit_mileage')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->foreignId('billed_by')->nullable()->constrained('users');
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->enum('status', ['Abierto', 'Comenzó', 'Incidencias Reportadas', 'Incidencias Aprobadas', 'Completado', 'Facturado', 'Cerrado']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_orders');
    }
}
