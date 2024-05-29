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
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WarehouseRequestController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('permissions', PermissionsController::class);
Route::resource('roles', RolesController::class);
Route::resource('clients', ClientController::class);
Route::resource('client-groups', ClientGroupController::class);
Route::resource('vehicles', VehicleController::class);
Route::resource('brands', BrandController::class);
Route::resource('products', ProductController::class);
Route::resource('services', ServiceController::class);
Route::resource('incidents', IncidentController::class);

// Rutas de órdenes de trabajo
Route::get('work-orders/list', [WorkOrderController::class, 'list'])->name('work-orders.list');
Route::resource('work-orders', WorkOrderController::class)->except(['show']); // Excluimos la ruta 'show'

// FORMSTEP
Route::get('work-orders/create-step-one', [WorkOrderController::class, 'createStepOne'])->name('work-orders.create-step-one');
Route::post('work-orders/search-vehicle', [WorkOrderController::class, 'searchVehicle'])->name('work-orders.search-vehicle');
Route::post('work-orders/select-vehicle', [WorkOrderController::class, 'selectVehicle'])->name('work-orders.select-vehicle');
Route::get('work-orders/create-step-three/{vehicle_id}', [WorkOrderController::class, 'createStepThree'])->name('work-orders.create-step-three');
Route::post('work-orders/store-step-three', [WorkOrderController::class, 'storeStepThree'])->name('work-orders.store-step-three');
Route::get('work-orders/create-step-four', [WorkOrderController::class, 'createStepFour'])->name('work-orders.create-step-four');
Route::post('work-orders/store-step-four', [WorkOrderController::class, 'storeStepFour'])->name('work-orders.store-step-four');
Route::get('work-orders/create-step-five', [WorkOrderController::class, 'createStepFive'])->name('work-orders.create-step-five');
Route::post('work-orders/store-step-five', [WorkOrderController::class, 'storeStepFive'])->name('work-orders.store-step-five');
Route::get('work-orders/create-step-six', [WorkOrderController::class, 'createStepSix'])->name('work-orders.create-step-six');
Route::post('work-orders/store-step-six', [WorkOrderController::class, 'storeStepSix'])->name('work-orders.store-step-six');
Route::get('work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
Route::post('work-orders/{workOrder}/update-service-status/{service}', [WorkOrderController::class, 'updateServiceStatus'])->name('work-orders.update-service-status');
Route::post('work-orders/{workOrder}/add-incident', [WorkOrderController::class, 'addIncident'])->name('work-orders.add-incident');

// Rutas para mecánicos
Route::get('mechanic-work-orders', [WorkOrderController::class, 'mechanicWorkOrders'])->name('mechanic-work-orders');
Route::get('mechanic-work-orders/{workOrder}', [WorkOrderController::class, 'mechanicShowWorkOrder'])->name('mechanic-work-orders.show');
Route::put('mechanic-work-orders/{workOrder}/update-status', [WorkOrderController::class, 'updateMechanicWorkOrderStatus'])->name('mechanic-work-orders.update-status');

// Rutas para bodeguero
Route::get('warehouse-work-orders', [WorkOrderController::class, 'warehouseWorkOrders'])->name('warehouse-work-orders.index');
Route::get('warehouse-work-orders/list', [WorkOrderController::class, 'warehouseWorkOrdersList'])->name('warehouse-work-orders.list');
Route::get('warehouse-work-orders/{workOrder}', [WorkOrderController::class, 'showWarehouseWorkOrder'])->name('warehouse-work-orders.show');
Route::post('warehouse-work-orders/{workOrder}/update-product-status/{product}', [WorkOrderController::class, 'updateProductStatus'])->name('warehouse-work-orders.update-product-status');

// Rutas para ejecutivos
Route::get('executive-work-orders', [WorkOrderController::class, 'executiveWorkOrders'])->name('executive-work-orders');
Route::get('executive-work-orders/{workOrder}', [WorkOrderController::class, 'executiveShowWorkOrder'])->name('executive-work-orders.show');
Route::post('work-orders/{workOrder}/update-incident-status/{incident}', [WorkOrderController::class, 'updateIncidentStatus'])->name('work-orders.update-incident-status');
// Rutas para agregar servicios y productos
Route::post('work-orders/{workOrder}/add-service', [WorkOrderController::class, 'addService'])->name('work-orders.add-service');
Route::post('work-orders/{workOrder}/add-product', [WorkOrderController::class, 'addProduct'])->name('work-orders.add-product');

// Ruta para imprimir
Route::get('executive-work-orders/{workOrder}/print', [WorkOrderController::class, 'printWorkOrder'])->name('executive-work-orders.print');



Route::resource('settings', SettingController::class);
Route::resource('reports', ReportController::class);
Route::resource('warehouse-requests', WarehouseRequestController::class);

Route::middleware(['auth'])->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
    Route::resource('permissions', PermissionsController::class);
    Route::resource('roles', RolesController::class);
});
Route::resource('users', UsersController::class);
