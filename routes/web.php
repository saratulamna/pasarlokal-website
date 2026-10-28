<?php

use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UmkmVerificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Pembeli\OrderHistoryController;
use App\Http\Controllers\Pembeli\PaymentController as PembeliPaymentController;
use App\Http\Controllers\Umkm\ProductController as UmkmProductController;
use App\Http\Controllers\Umkm\StoreController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

/*
|--------------------------------------------------------------------------
| HALAMAN PUBLIK
|--------------------------------------------------------------------------
*/

Route::get('/', [
    HomeController::class,
    'index',
])->name('home');

Route::get('/intro', function () {
    return view('intro');
})->name('intro');

Route::get('/produk/{product:slug}', [
    ProductController::class,
    'show', 
])->name('products.show');


/*
|--------------------------------------------------------------------------
| LOGIN DAN REGISTRASI
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [
        AuthController::class,
        'showLogin',
    ])->name('login');

    Route::post('/login', [
        AuthController::class,
        'login',
    ])
        ->middleware('throttle:10,1')
        ->name('login.store');

    Route::get('/register', [
        AuthController::class,
        'showRegister',
    ])->name('register');

    Route::post('/register', [
        AuthController::class,
        'register',
    ])
        ->middleware('throttle:5,1')
        ->name('register.store');

});


/*
|--------------------------------------------------------------------------
| SEMUA PENGGUNA YANG SUDAH LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    | Logout
    */

    Route::post('/logout', [
        AuthController::class,
        'logout',
    ])->name('logout');


    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {

            /*
            | Dashboard
            */

            Route::get('/dashboard', [
                DashboardController::class,
                'admin',
            ])->name('dashboard');


            /*
            | Manajemen Produk
            */

            Route::get('/products', [
                AdminProductController::class,
                'index',
            ])->name('products.index');


            /*
            | Verifikasi UMKM
            */

            Route::get('/umkms', [
                UmkmVerificationController::class,
                'index',
            ])->name('umkms.index');

            Route::patch('/umkms/{umkm}/approve', [
                UmkmVerificationController::class,
                'approve',
            ])->name('umkms.approve');

            Route::patch('/umkms/{umkm}/reject', [
                UmkmVerificationController::class,
                'reject',
            ])->name('umkms.reject');


            /*
            | Verifikasi Pembayaran
            */

            Route::get('/pembayaran', [
                PaymentVerificationController::class,
                'index',
            ])->name('payments.index');

            Route::get('/pembayaran/{payment}/bukti', [
                PaymentVerificationController::class,
                'proof',
            ])->name('payments.proof');

            Route::patch('/pembayaran/{payment}/approve', [
                PaymentVerificationController::class,
                'approve',
            ])->name('payments.approve');

            Route::patch('/pembayaran/{payment}/reject', [
                PaymentVerificationController::class,
                'reject',
            ])->name('payments.reject');


            /*
            | Manajemen Pengguna
            */

            Route::resource('users', UserController::class)
                ->except(['show']);

            Route::resource('categories', CategoryController::class)
                ->except(['show']);

            Route::get('/orders', [
    AdminOrderController::class,
    'index',
])->name('orders.index');

        Route::get('/orders/{order}', [
    AdminOrderController::class,
    'show',
])->whereNumber('order')->name('orders.show');

        });


    /*
    |--------------------------------------------------------------------------
    | PEMBELI
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:pembeli')->group(function () {

        Route::get('/pembeli/dashboard', [
            DashboardController::class,
            'pembeli',
        ])->name('pembeli.dashboard');


        /*
        | Keranjang Belanja
        */

        Route::get('/keranjang', [
            CartController::class,
            'index',
        ])->name('cart.index');

        Route::post('/keranjang/produk/{product:slug}', [
            CartController::class,
            'store',
        ])->name('cart.store');

        Route::patch('/keranjang/item/{cartItem}', [
            CartController::class,
            'update',
        ])->name('cart.update');

        Route::delete('/keranjang/item/{cartItem}', [
            CartController::class,
            'destroy',
        ])->name('cart.destroy');


        /*
        | Checkout
        */

        Route::get('/checkout', [
            CheckoutController::class,
            'index',
        ])->name('checkout.index');

        Route::post('/checkout', [
            CheckoutController::class,
            'store',
        ])->name('checkout.store');

        Route::get('/checkout/berhasil', [
            CheckoutController::class,
            'success',
        ])->name('checkout.success');


        /*
        | Riwayat Pesanan dan Pembayaran
        */

        Route::get('/pembeli/pesanan', [
            OrderHistoryController::class,
            'index',
        ])->name('pembeli.orders.index');

        Route::get('/pembeli/pesanan/{order}/pembayaran', [
            PembeliPaymentController::class,
            'show',
        ])->name('pembeli.payments.show');

        Route::post('/pembeli/pesanan/{order}/pembayaran', [
            PembeliPaymentController::class,
            'upload',
        ])->name('pembeli.payments.upload');

        /*
|--------------------------------------------------------------------------
| JELAJAHI PRODUK
|--------------------------------------------------------------------------
*/

Route::get('/pembeli/jelajahi', [
    ProductController::class,
    'index',
])->name('pembeli.products.index');

    });


    /*
    |--------------------------------------------------------------------------
    | UMKM / PENJUAL
    |--------------------------------------------------------------------------
    */

    Route::prefix('umkm')
        ->name('umkm.')
        ->middleware('role:umkm')
        ->group(function () {

            Route::get('/dashboard', [
                DashboardController::class,
                'umkm',
            ])->name('dashboard');


            /*
            | Pembuatan Toko
            */

            Route::get('/toko/buat', [
                StoreController::class,
                'create',
            ])->name('toko.create');

            Route::post('/toko', [
                StoreController::class,
                'store',
            ])->name('toko.store');


            /*
            | Pengelolaan Produk UMKM
            */

            Route::resource('produk', UmkmProductController::class)
                ->parameters(['produk' => 'product'])
                ->except(['show'])
                ->names('products');

        });

});