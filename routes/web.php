<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PermissionCOntroller;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestDetailsController;
use App\Http\Controllers\PurchaseRequestsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // permission
    route::resource('/permissions', PermissionCOntroller::class);
    // roles
    route::resource('/roles', RoleController::class);

    // company
    route::resource('/companies', CompanyController::class);
    
    // company
    route::resource('/departments', DepartmentController::class);
    
    // vendor
    route::resource('/vendor', VendorController::class);
    
    // user
    route::resource('/users', UserController::class);
   
    // PR
    route::resource('purchase_requests', PurchaseRequestsController::class);
   
    // po
    Route::resource('purchase_orders', PurchaseOrderController::class); // <-- Plural
    Route::get('purchase-orders/data', [PurchaseOrderController::class, 'data'])->name('purchase_orders.data');

    Route::get('purchase_orders/create-from-pr/{pr_id}', [PurchaseOrderController::class, 'createFromPR'])->name('purchase_orders.createFromPR');

    // ROUTE 2: Untuk AJAX DataTables (Memuat JSON)
    // Route::get('purchase-order/data', [PurchaseOrderController::class, 'data'])->name('purchase_order.data');

    // RUTE KHUSUS UNTUK APPROVAL FLOW

    // 1. Daftar PR yang perlu disetujui oleh user yang sedang login
    // Route::get('purchase_requests/approval/list', [PurchaseRequestController::class, 'indexApproval'])
    //     ->name('purchase_requests.indexApproval');

    Route::post('purchase_requests/{pr}/approve', [PurchaseRequestsController::class, 'processApproval'])
    ->name('purchase_requests.processApproval');

    Route::get('purchase_requests/{pr}/printPr', [PurchaseRequestsController::class, 'printPr'])
    ->name('purchase_requests.print');

    // 2. Memproses Aksi Persetujuan/Penolakan
    // Route::post('purchase_requests/{pr}/process-approval', [PurchaseRequestController::class, 'processApproval'])
    //     ->name('purchase_requests.processApproval');
   


});

require __DIR__.'/auth.php';
