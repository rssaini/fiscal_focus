<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\EntityManagementController;

// Auth::routes();

Route::get('/', function () {
    return redirect()->route('ledgers.index');
});

Route::resource('ledgers', LedgerController::class)->except(['edit', 'update', 'destroy']);
Route::get('ledgers/{ledger}/export', [LedgerController::class, 'export'])->name('ledgers.export');
Route::get('ledgers/{ledger}/export-pdf', [LedgerController::class, 'exportPdf'])->name('ledgers.export-pdf');



Route::resource('chart-of-accounts', ChartOfAccountController::class)->names([
    'index' => 'chart-of-accounts.index',
    'create' => 'chart-of-accounts.create',
    'store' => 'chart-of-accounts.store',
    'show' => 'chart-of-accounts.show',
    'edit' => 'chart-of-accounts.edit',
    'update' => 'chart-of-accounts.update',
    'destroy' => 'chart-of-accounts.destroy'
]);
Route::get('api/accounts/type/{type}', [ChartOfAccountController::class, 'getAccountsByType'])
    ->name('accounts.by-type');

Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('/create', [CustomerController::class, 'create'])->name('create');
    Route::post('/', [CustomerController::class, 'store'])->name('store');
    Route::get('/export', [CustomerController::class, 'export'])->name('export');
    Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
    Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
    Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
    Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    Route::get('/{customer}/statement', [CustomerController::class, 'statement'])->name('statement');
    Route::post('/{customer}/upload-document', [CustomerController::class, 'uploadDocument'])->name('upload-document');
    Route::delete('/{customer}/documents/{document}', [CustomerController::class, 'deleteDocument'])->name('delete-document');
});

Route::prefix('vouchers')->name('vouchers.')->group(function () {
    Route::get('/', [VoucherController::class, 'index'])->name('index');
    Route::get('/create', [VoucherController::class, 'create'])->name('create');
    Route::post('/', [VoucherController::class, 'store'])->name('store');
    Route::get('/{voucher}', [VoucherController::class, 'show'])->name('show');
    Route::get('/{voucher}/edit', [VoucherController::class, 'edit'])->name('edit');
    Route::put('/{voucher}', [VoucherController::class, 'update'])->name('update');
    Route::post('/{voucher}/post', [VoucherController::class, 'post'])->name('post');
    Route::post('/{voucher}/cancel', [VoucherController::class, 'cancel'])->name('cancel');
    Route::get('/{voucher}/duplicate', [VoucherController::class, 'duplicate'])->name('duplicate');
    Route::get('/{voucher}/print', [VoucherController::class, 'print'])->name('print');
});


Route::resource('parties', PartyController::class);

Route::prefix('entity-management')->name('entity-management.')->group(function () {
    Route::get('/', [EntityManagementController::class, 'index'])->name('index');
    Route::get('/create', [EntityManagementController::class, 'create'])->name('create');
    Route::post('/', [EntityManagementController::class, 'store'])->name('store');
    Route::get('/{entityManagement}', [EntityManagementController::class, 'show'])->name('show');
    Route::get('/{entityManagement}/edit', [EntityManagementController::class, 'edit'])->name('edit');
    Route::put('/{entityManagement}', [EntityManagementController::class, 'update'])->name('update');
    Route::delete('/{entityManagement}', [EntityManagementController::class, 'destroy'])->name('destroy');

    // Utility routes
    Route::post('/create-defaults', [EntityManagementController::class, 'createDefaults'])->name('create-defaults');
});

// API routes for getting entity management records
Route::prefix('api/entity-management')->group(function () {
    Route::get('/entity-creation-head', [EntityManagementController::class, 'getEntityCreationHead'])->name('api.entity-creation-head');
    Route::get('/voucher-ledger', [EntityManagementController::class, 'getVoucherLedger'])->name('api.voucher-ledger');
});
