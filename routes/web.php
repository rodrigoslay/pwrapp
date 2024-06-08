<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientGroupController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CarModelController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WarehouseRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RevisionController;

// Página de bienvenida
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Rutas de autenticación
Auth::routes();

// Ruta del dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Rutas de gestión de clientes y grupos de clientes
Route::resource('clients', ClientController::class);
Route::resource('client-groups', ClientGroupController::class);

// Rutas de gestión de vehículos y marcas
Route::resource('vehicles', VehicleController::class);
Route::resource('brands', BrandController::class)->except(['store']);
Route::resource('car-models', CarModelController::class);
Route::get('brands/{brand}/models', [CarModelController::class, 'getModelsByBrand'])->name('brands.models');

// Rutas de gestión de productos y servicios
Route::resource('products', ProductController::class);
Route::resource('services', ServiceController::class);

// Rutas de gestión de incidentes
Route::resource('incidents', IncidentController::class);

// Rutas de gestión de órdenes de trabajo
Route::resource('work-orders', WorkOrderController::class)->except(['show']);
Route::get('work-orders/list', [WorkOrderController::class, 'list'])->name('work-orders.list');

// Rutas paso a paso para creación de órdenes de trabajo
Route::get('work-orders/create-step-one', [WorkOrderController::class, 'createStepOne'])->name('work-orders.create-step-one');
Route::post('work-orders/search-vehicle', [WorkOrderController::class, 'searchVehicle'])->name('work-orders.search-vehicle');
Route::get('work-orders/create-step-two', [WorkOrderController::class, 'createStepTwo'])->name('work-orders.create-step-two');
Route::post('work-orders/store-step-two', [WorkOrderController::class, 'storeStepTwo'])->name('work-orders.store-step-two');
Route::post('work-orders/store-service', [WorkOrderController::class, 'storeService'])->name('work-orders.store-service');
Route::post('work-orders/store-revision', [WorkOrderController::class, 'storeRevision'])->name('work-orders.store-revision');
Route::post('work-orders/store-revision-fault', [WorkOrderController::class, 'storeRevisionFault'])->name('work-orders.store-revision-fault');
Route::get('work-orders/create-step-three', [WorkOrderController::class, 'createStepThree'])->name('work-orders.create-step-three');
Route::post('work-orders/store-step-three', [WorkOrderController::class, 'storeStepThree'])->name('work-orders.store-step-three');
Route::get('work-orders/create-step-four', [WorkOrderController::class, 'createStepFour'])->name('work-orders.create-step-four');
Route::post('work-orders/store-step-four', [WorkOrderController::class, 'storeStepFour'])->name('work-orders.store-step-four');
Route::get('work-orders/create-step-five', [WorkOrderController::class, 'createStepFive'])->name('work-orders.create-step-five');
Route::post('work-orders/store-step-five', [WorkOrderController::class, 'storeStepFive'])->name('work-orders.store-step-five');

Route::get('clients/check-rut', [ClientController::class, 'checkRUT'])->name('clients.check-rut');

// Ruta para obtener datos de un vehículo
Route::get('vehicles/list', [VehicleController::class, 'list'])->name('vehicles.list');
Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
Route::get('/vehicles/check-license-plate', [VehicleController::class, 'checkLicensePlate'])->name('vehicles.check-license-plate');

// Ruta para obtener datos de una marca
Route::get('brands/list', [BrandController::class, 'list'])->name('brands.list');
Route::post('brands/store', [BrandController::class, 'store'])->name('brands.store');
Route::get('brands/{brand}/models', [BrandController::class, 'getModels'])->name('brands.models');

// Ruta para obtener datos de un cliente
Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
Route::get('clients/list', [ClientController::class, 'list'])->name('clients.list');

// Rutas para gestionar mecánicos
Route::get('mechanic-work-orders', [WorkOrderController::class, 'mechanicWorkOrders'])->name('mechanic-work-orders.index');
Route::get('mechanic-work-orders/list', [WorkOrderController::class, 'mechanicWorkOrdersList'])->name('mechanic-work-orders.list');
Route::get('mechanic-work-orders/{workOrder}', [WorkOrderController::class, 'mechanicShowWorkOrder'])->name('mechanic-work-orders.show');
Route::put('mechanic-work-orders/{workOrder}/update-status/{serviceId}', [WorkOrderController::class, 'updateMechanicWorkOrderStatus'])->name('mechanic-work-orders.update-status');
Route::post('mechanic-work-orders/{workOrder}/add-incident', [WorkOrderController::class, 'addIncident'])->name('mechanic-work-orders.add-incident');
Route::put('mechanic-work-orders/{workOrder}/update-fault-status/{revisionId}/{faultId}', [WorkOrderController::class, 'updateFaultStatus'])->name('mechanic-work-orders.update-fault-status');

// Rutas para gestionar bodegas
Route::prefix('warehouse-work-orders')->name('warehouse-work-orders.')->group(function () {
    Route::get('/', [WorkOrderController::class, 'warehouseWorkOrders'])->name('index');
    Route::get('/list', [WorkOrderController::class, 'warehouseWorkOrdersList'])->name('list');
    Route::get('/{id}', [WorkOrderController::class, 'showWarehouseWorkOrder'])->name('show');
    Route::put('/update-product-status/{workOrder}/{product}', [WorkOrderController::class, 'updateProductStatus'])->name('update-product-status');
});

// Rutas para gestionar ejecutivos
Route::get('executive-work-orders', [WorkOrderController::class, 'executiveWorkOrders'])->name('executive-work-orders.index');
Route::get('executive-work-orders/list', [WorkOrderController::class, 'executiveWorkOrdersList'])->name('executive-work-orders.list');
Route::get('executive-work-orders/{workOrder}', [WorkOrderController::class, 'executiveShowWorkOrder'])->name('executive-work-orders.show');
Route::post('work-orders/{workOrder}/update-incident-status/{incident}', [WorkOrderController::class, 'updateIncidentStatus'])->name('work-orders.update-incident-status');
Route::post('work-orders/{workOrder}/facturar', [WorkOrderController::class, 'facturar'])->name('work-orders.facturar');

// Rutas para agregar servicios, productos y revisiones a las órdenes de trabajo
Route::post('work-orders/{workOrder}/add-service', [WorkOrderController::class, 'addService'])->name('work-orders.add-service');
Route::post('work-orders/{workOrder}/add-product', [WorkOrderController::class, 'addProduct'])->name('work-orders.add-product');
Route::post('work-orders/{workOrder}/add-revision', [WorkOrderController::class, 'addRevision'])->name('work-orders.add-revision');

// Ruta para imprimir órdenes de trabajo
Route::get('executive-work-orders/{workOrder}/print', [WorkOrderController::class, 'printWorkOrder'])->name('executive-work-orders.print');

// Rutas de configuración, reportes y solicitudes de bodega
Route::resource('settings', SettingController::class);
Route::resource('reports', ReportController::class);
Route::resource('warehouse-requests', WarehouseRequestController::class);

// Rutas para revisiones
Route::post('revisions/store', [RevisionController::class, 'store'])->name('revisions.store');
Route::get('revisions/list', [WorkOrderController::class, 'getRevisions'])->name('revisions.list');

// Ruta para el perfil del usuario
Route::middleware(['auth'])->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Agrupar rutas para usuarios, roles y permisos
Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
    Route::resource('permissions', PermissionsController::class);
    Route::resource('roles', RolesController::class);
});
Route::resource('users', UsersController::class);
