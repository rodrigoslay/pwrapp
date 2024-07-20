<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    DashboardController,
    PermissionsController,
    RolesController,
    UsersController,
    ClientController,
    ClientGroupController,
    VehicleController,
    BrandController,
    CarModelController,
    ProductController,
    ServiceController,
    IncidentController,
    WorkOrderController,
    SettingController,
    ReportController,
    ProfileController,
    DarkModeController,
    RevisionController,
    ManagerWorkOrdersController,
    ChatController
};


// Página de bienvenida
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Rutas públicas para el estado de las órdenes de trabajo
Route::get('/work-order-status', [WorkOrderController::class, 'publicWorkOrderStatus'])->name('public.work-order-status');
Route::get('/api/work-orders', [WorkOrderController::class, 'getWorkOrders'])->name('api.work-orders');


// Rutas de autenticación
Auth::routes();

// Ruta del dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

// Rutas de gestión de clientes y grupos de clientes
Route::middleware(['auth', 'role:Administrador|Ejecutivo|Líder'])->group(function () {
    Route::resource('clients', ClientController::class);
    Route::resource('client-groups', ClientGroupController::class);
});

// Rutas de gestión de vehículos y marcas
Route::middleware(['auth', 'role:Administrador|Ejecutivo|Líder'])->group(function () {
    Route::resource('vehicles', VehicleController::class)->except(['update']);
    Route::resource('brands', BrandController::class)->except(['store']);
    Route::resource('car-models', CarModelController::class);
    Route::get('brands/{brand}/models', [CarModelController::class, 'getModelsByBrand'])->name('brands.models');
});

// Rutas de gestión de productos y servicios
Route::middleware(['auth', 'role:Administrador|Ejecutivo|Líder|Bodeguero'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::resource('services', ServiceController::class);
});

// Rutas de gestión de incidentes
Route::middleware(['auth', 'role:Administrador|Ejecutivo|Líder|Mecánico'])->group(function () {
    Route::resource('incidents', IncidentController::class);
});
//chat
Route::middleware(['auth'])->group(function () {
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('chat', [ChatController::class, 'store'])->name('chat.store');
});

// rutas manageer

Route::middleware(['auth', 'role:Manager'])->prefix('manager-work-orders')->name('manager-work-orders.')->group(function () {
    Route::get('/', [ManagerWorkOrdersController::class, 'index'])->name('index');
    Route::get('/list', [ManagerWorkOrdersController::class, 'list'])->name('list'); // Esta es la ruta que falta
    Route::get('/stats', [ManagerWorkOrdersController::class, 'stats'])->name('stats');
    Route::get('/summary', [ManagerWorkOrdersController::class, 'summary'])->name('summary');
    Route::get('/{id}', [ManagerWorkOrdersController::class, 'show'])->name('show');
    Route::post('/{id}/close', [ManagerWorkOrdersController::class, 'closeWorkOrder'])->name('close');
});


// Rutas de gestión de órdenes de trabajo
Route::middleware(['auth', 'role:Administrador|Ejecutivo|Líder|Mecánico'])->group(function () {
    Route::resource('work-orders', WorkOrderController::class)->except(['show']);
    Route::get('work-orders/list', [WorkOrderController::class, 'list'])->name('work-orders.list');
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
    Route::put('work-orders/{workOrder}/update-status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
});

// Lideres

Route::middleware(['auth', 'role:Líder'])->group(function () {
    Route::get('leader-work-orders', [WorkOrderController::class, 'leaderWorkOrders'])->name('leader-work-orders.index');
    Route::get('leader-work-orders/list', [WorkOrderController::class, 'leaderWorkOrdersList'])->name('leader-work-orders.list');
    Route::get('leader-work-orders/{workOrder}', [WorkOrderController::class, 'leaderShowWorkOrder'])->name('leader-work-orders.show');
    Route::post('leader-work-orders/{workOrder}/assign-mechanic', [WorkOrderController::class, 'assignMechanic'])->name('leader-work-orders.assign-mechanic');
    Route::put('leader-work-orders/{workOrder}/change-mechanic/{service}', [WorkOrderController::class, 'changeMechanic'])->name('leader-work-orders.change-mechanic');
    Route::put('leader-work-orders/{workOrder}/update-status/{service}', [WorkOrderController::class, 'updateServiceStatusByLeader'])->name('leader-work-orders.update-status');
    Route::put('leader-work-orders/{workOrder}/update-fault-status/{revision}/{fault}', [WorkOrderController::class, 'updateFaultStatusByLeader'])->name('leader-work-orders.update-fault-status');
    Route::post('leader-work-orders/{workOrder}/add-incident', [WorkOrderController::class, 'addIncidentByLeader'])->name('leader-work-orders.add-incident');
});


// Rutas para obtener datos específicos
Route::middleware(['auth', 'role:Administrador|Ejecutivo|Líder'])->group(function () {
    Route::get('vehicles/list', [VehicleController::class, 'list'])->name('vehicles.list');
    Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::get('/vehicles/check-license-plate', [VehicleController::class, 'checkLicensePlate'])->name('vehicles.check-license-plate');
});

Route::middleware(['auth', 'role:Administrador|Ejecutivo|Líder'])->group(function () {
    Route::get('brands/list', [BrandController::class, 'list'])->name('brands.list');
    Route::post('brands/store', [BrandController::class, 'store'])->name('brands.store');
    Route::get('brands/{brand}/models', [BrandController::class, 'getModels'])->name('brands.models');
});

Route::middleware(['auth', 'role:Administrador|Ejecutivo|Líder'])->group(function () {
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('clients/list', [ClientController::class, 'list'])->name('clients.list');
    Route::get('clients/check-rut', [ClientController::class, 'checkRUT'])->name('clients.check-rut');
});

// Rutas para gestionar mecánicos
Route::middleware(['auth', 'role:Mecánico'])->group(function () {
    Route::get('mechanic-work-orders', [WorkOrderController::class, 'mechanicWorkOrders'])->name('mechanic-work-orders.index');
    Route::get('mechanic-work-orders/list', [WorkOrderController::class, 'mechanicWorkOrdersList'])->name('mechanic-work-orders.list');
    Route::get('mechanic-work-orders/{workOrder}', [WorkOrderController::class, 'mechanicShowWorkOrder'])->name('mechanic-work-orders.show');
    Route::put('mechanic-work-orders/{workOrder}/update-status/{serviceId}', [WorkOrderController::class, 'updateMechanicWorkOrderStatus'])->name('mechanic-work-orders.update-status');
    Route::post('mechanic-work-orders/{workOrder}/add-incident', [WorkOrderController::class, 'addIncident'])->name('mechanic-work-orders.add-incident');
    Route::put('mechanic-work-orders/{workOrder}/update-fault-status/{revisionId}/{faultId}', [WorkOrderController::class, 'updateFaultStatus'])->name('mechanic-work-orders.update-fault-status');
    Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
});

// Rutas para gestionar bodegas
Route::prefix('warehouse-work-orders')->name('warehouse-work-orders.')->middleware(['auth', 'role:Bodeguero'])->group(function () {
    Route::get('/', [WorkOrderController::class, 'warehouseWorkOrders'])->name('index');
    Route::get('/list', [WorkOrderController::class, 'warehouseWorkOrdersList'])->name('list');
    Route::get('/{id}', [WorkOrderController::class, 'showWarehouseWorkOrder'])->name('show');
    Route::put('/update-product-status/{workOrder}/{product}', [WorkOrderController::class, 'updateProductStatus'])->name('update-product-status');
});

// Rutas para gestionar ejecutivos Lider Manager
Route::middleware(['auth', 'role:Ejecutivo|Líder|Manager'])->group(function () {
    Route::get('executive-work-orders', [WorkOrderController::class, 'executiveWorkOrders'])->name('executive-work-orders.index');
    Route::get('executive-work-orders/list', [WorkOrderController::class, 'executiveWorkOrdersList'])->name('executive-work-orders.list');
    Route::get('executive-work-orders/{workOrder}', [WorkOrderController::class, 'executiveShowWorkOrder'])->name('executive-work-orders.show');
    Route::post('work-orders/{workOrder}/update-incident-status/{incident}', [WorkOrderController::class, 'updateIncidentStatus'])->name('work-orders.update-incident-status');
    Route::post('work-orders/{workOrder}/facturar', [WorkOrderController::class, 'facturar'])->name('work-orders.facturar');
    Route::post('work-orders/{workOrder}/no-realizado', [WorkOrderController::class, 'noRealizado'])->name('work-orders.no-realizado');
    Route::post('/work-orders/{workOrderId}/start', [WorkOrderController::class, 'start'])->name('work-orders.start');
    Route::get('/work-orders/quotations', [WorkOrderController::class, 'executiveQuotations'])->name('work-orders.quotations');
    Route::get('/work-orders/quotations/list', [WorkOrderController::class, 'executiveQuotationsList'])->name('work-orders.quotations-list');
    Route::get('/work-orders/scheduled', [WorkOrderController::class, 'executiveScheduled'])->name('work-orders.scheduled');
    Route::post('work-orders/{workOrderId}/update-incident-status/{incidentId}', 'WorkOrderController@updateIncidentStatus');
    Route::delete('work-orders/{workOrderId}/remove-revision/{revisionId}', 'WorkOrderController@removeRevision')->name('work-orders.remove-revision');
    Route::delete('work-orders/{workOrder}/remove-service/{service}', [WorkOrderController::class, 'removeService'])->name('work-orders.remove-service');
    Route::delete('work-orders/{workOrder}/remove-product/{product}', [WorkOrderController::class, 'removeProduct'])->name('work-orders.remove-product');
    Route::delete('work-orders/{workOrder}/remove-fault/{revision}/{fault}', [WorkOrderController::class, 'removeFault'])->name('work-orders.remove-fault');
});

// Rutas para agregar servicios, productos y revisiones a las órdenes de trabajo
Route::middleware(['auth', 'role:Administrador|Ejecutivo|Líder'])->group(function () {
    Route::post('work-orders/{workOrder}/add-service', [WorkOrderController::class, 'addService'])->name('work-orders.add-service');
    Route::post('work-orders/{workOrder}/add-product', [WorkOrderController::class, 'addProduct'])->name('work-orders.add-product');
    Route::post('work-orders/{workOrder}/add-revision', [WorkOrderController::class, 'addRevision'])->name('work-orders.add-revision');
    Route::get('executive-work-orders/{id}/print', [WorkOrderController::class, 'showPrintView'])->name('executive-work-orders.print');
    Route::get('executive-work-orders/{id}/download-pdf', [WorkOrderController::class, 'downloadWorkOrderPDF'])->name('executive-work-orders.download-pdf');
});

// Rutas de configuración, reportes y solicitudes de bodega
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::resource('settings', SettingController::class);
    Route::resource('reports', ReportController::class);
});

// Rutas para revisiones
Route::middleware(['auth', 'role:Administrador|Ejecutivo|Líder'])->group(function () {
    Route::post('revisions/store', [RevisionController::class, 'store'])->name('revisions.store');
    Route::get('revisions/list', [WorkOrderController::class, 'getRevisions'])->name('revisions.list');
});

// Ruta para el perfil del usuario
Route::middleware(['auth'])->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

});
Route::group(['middleware' => ['auth', 'apply-dark-mode']], function () {
    // tus rutas protegidas aquí
    Route::post('/toggle-dark-mode', [DarkModeController::class, 'toggleDarkMode'])->name('toggle-dark-mode');
});

// Agrupar rutas para usuarios, roles y permisos
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        Route::resource('permissions', PermissionsController::class);
        Route::resource('roles', RolesController::class);
    });
    Route::resource('users', UsersController::class);
});
