<?php

use App\Http\Controllers\CashierController;
use App\Http\Controllers\CmsBranchController;
use App\Http\Controllers\CmsFaqController;
use App\Http\Controllers\CmsFeatureController;
use App\Http\Controllers\CmsGalleryController;
use App\Http\Controllers\CmsSettingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

// ---------- PUBLIK ----------
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/cek-pesanan', [PublicController::class, 'track'])->middleware('throttle:20,1')->name('track');
Route::post('/pertanyaan', [PublicController::class, 'storeInquiry'])
    ->middleware('throttle:5,1')->name('inquiry.store');

// ---------- WAJIB LOGIN ----------
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CUSTOMER
    Route::middleware('role:customer')->group(function () {
        Route::get('/akun-saya', [PortalController::class, 'index'])->name('portal.index');
        Route::get('/akun-saya/pesanan', [PortalController::class, 'orders'])->name('portal.orders');

        Route::get('/akun-saya/pesan', [PortalController::class, 'create'])->name('portal.order.create');
        Route::post('/akun-saya/pesan', [PortalController::class, 'store'])->name('portal.order.store');

        Route::get('/akun-saya/pesanan/{order}', [PortalController::class, 'show'])
            ->whereNumber('order')->name('portal.show');
        Route::patch('/akun-saya/pesanan/{order}/batal', [PortalController::class, 'cancel'])
            ->whereNumber('order')->name('portal.cancel');
    });

    // OWNER
    Route::middleware('role:owner')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->whereNumber('expense')->name('expenses.destroy');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // OWNER + ADMIN
    Route::middleware('role:owner,admin')->group(function () {
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
Route::put('/staff/{user}', [StaffController::class, 'update'])->whereNumber('user')->name('staff.update');
Route::patch('/staff/{user}', [StaffController::class, 'toggle'])->whereNumber('user')->name('staff.toggle');
Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->whereNumber('user')->name('staff.destroy');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

        Route::prefix('cms')->name('cms.')->group(function () {
            Route::get('/', [CmsSettingController::class, 'edit'])->name('settings');
            Route::put('/', [CmsSettingController::class, 'update'])->name('settings.update');

            Route::resource('branches', CmsBranchController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('features', CmsFeatureController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('gallery', CmsGalleryController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('faqs', CmsFaqController::class)->only(['index', 'store', 'update', 'destroy']);
        });
    });

    // ADMIN
    Route::middleware('role:admin')->group(function () {
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->whereNumber('customer')->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->whereNumber('customer')->name('customers.update');

        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->whereNumber('service')->name('services.update');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->whereNumber('service')->name('services.destroy');

        // PENTING: /orders/create harus sebelum /orders/{order}
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->whereNumber('order')->name('orders.status');

        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::patch('/inquiries/{inquiry}', [InquiryController::class, 'update'])->whereNumber('inquiry')->name('inquiries.update');
    });

    // ADMIN + KASIR: terima pembayaran
    Route::middleware('role:admin,kasir')->group(function () {
        Route::post('/orders/{order}/payments', [PaymentController::class, 'store'])->whereNumber('order')->name('payments.store');
    });

    // OWNER + ADMIN + KASIR: rekap pembayaran
    Route::middleware('role:owner,admin,kasir')->group(function () {
        Route::get('/rekap-kasir', [CashierController::class, 'recap'])->name('cashier.recap');
    });

    // OWNER + ADMIN + KASIR: lihat pesanan
    Route::middleware('role:owner,admin,kasir')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    });
});

require __DIR__ . '/auth.php';