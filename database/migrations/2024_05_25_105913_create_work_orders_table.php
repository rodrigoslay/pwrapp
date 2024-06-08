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
            $table->boolean('review')->default(0)->after('status');
            $table->foreignId('executive_id')->constrained('users');
            $table->foreignId('client_id')->constrained();
            $table->foreignId('vehicle_id')->constrained();
            $table->integer('entry_mileage')->default(0);
            $table->integer('exit_mileage')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->foreignId('billed_by')->nullable()->constrained('users');
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->boolean('revisiones')->default(false); // Campo revisiones
            $table->timestamps();
            $table->enum('status', ['Abierto', 'Comenzó', 'Incidencias Reportadas', 'Incidencias Aprobadas', 'Completado', 'Facturado', 'Cerrado']);
        });


        Schema::create('work_order_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->float('price');
            $table->timestamps();
        });

        Schema::create('work_order_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->float('price');
            $table->timestamps();
        });

        Schema::create('work_order_incident', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('incident_id')->constrained()->onDelete('cascade');
            $table->boolean('approved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_order_incident');
        Schema::dropIfExists('work_order_product');
        Schema::dropIfExists('work_order_service');
        Schema::dropIfExists('work_orders');
    }
}
