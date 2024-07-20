<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Crear tablas específicas del proyecto
        Schema::create('client_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('discount_percentage', 8, 2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rut');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('client_group_id')->constrained('client_groups')->onDelete('cascade');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('created_by')->constrained('users')->notNullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->string('model');
            $table->year('year');
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate')->unique();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->string('model');
            $table->string('color');
            $table->string('chassis')->nullable();
            $table->string('photo')->nullable(); // Campo photo agregado aquí
            $table->integer('kilometers')->default(0);
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->notNullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('client_vehicle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('work_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_number')->nullable();
            $table->double('discount_percentage', 8, 2)->nullable();
            $table->double('discount_amount', 8, 2)->nullable();
            $table->double('subtotal', 8, 2)->notNullable();
            $table->double('tax', 8, 2)->notNullable();
            $table->double('total', 8, 2)->notNullable();
            $table->tinyInteger('review')->default(0);
            $table->foreignId('executive_id')->constrained('users')->notNullable();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->integer('entry_mileage')->default(0);
            $table->integer('exit_mileage')->nullable();
            $table->foreignId('created_by')->constrained('users')->notNullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->foreignId('billed_by')->nullable()->constrained('users');
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->enum('status', [
                'Iniciado', 'En Proceso', 'Incidencias', 'Completado', 'Facturado', 'Cerrado', 'Aprobado', 'Parcial', 'Rechazado', 'Cotización', 'Agendado'
            ])->notNullable();
            $table->dateTime('scheduling')->nullable(); // Cambio aquí
            $table->timestamps();
        });


        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description');
            $table->integer('price');
            $table->tinyInteger('discount_applicable')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('revision_faults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_id')->constrained('revisions')->onDelete('cascade');
            $table->string('fallo');
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->double('price', 8, 2);
            $table->double('discounted_price', 8, 2)->nullable();
            $table->integer('inventory')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('status')->default(1);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('revision_work_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->foreignId('revision_id')->constrained('revisions')->onDelete('cascade');
            $table->foreignId('fault_id')->constrained('revision_faults')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('service_work_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('mechanic_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['iniciado', 'pendiente', 'completado'])->default('pendiente');
            $table->timestamps();
        });

        Schema::create('product_work_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->string('status')->default('pendiente');
            $table->timestamps();
        });

        Schema::create('incident_work_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->boolean('approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('observation')->nullable();
            $table->timestamps();
        });

        // Crear tabla de configuración
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('value');
            $table->timestamps();
        });

        // Crear tabla de reportes
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down()
    {
        // Eliminar tablas en el orden inverso
        Schema::dropIfExists('reports');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('incident_work_order');
        Schema::dropIfExists('product_work_order');
        Schema::dropIfExists('service_work_order');
        Schema::dropIfExists('revision_work_order');
        Schema::dropIfExists('revision_faults');
        Schema::dropIfExists('revisions');
        Schema::dropIfExists('services');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('products');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('client_vehicle');
        Schema::dropIfExists('car_models');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('client_groups');
    }
};
