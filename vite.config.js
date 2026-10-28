
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                'resources/css/auth.css',
                'resources/js/auth.js',

                'resources/css/portal.css',
                'resources/css/pages/admin/umkms.css',
                'resources/js/portal.js',

                'resources/css/landing.css',
                'resources/js/landing.js',

                'resources/css/public/base.css',
                'resources/css/public/home.css',
                'resources/js/public/home.js',

                // Login: aset khusus halaman
                'resources/css/auth/login.css',
                'resources/js/auth/login.js',

                // Aset register
                'resources/css/auth/register.css',
                'resources/js/auth/register.js',

                // Aset Transitions
                'resources/css/auth/transitions.css',
                'resources/js/auth/transitions.js',

                // Aset Product Show
                'resources/css/public/product-show.css',
                'resources/js/public/product-show.js',

                'resources/css/buyer/base.css',
                'resources/js/buyer/buyer.js',

                'resources/css/pages/pembeli/dashboard.css',

                'resources/css/pages/pembeli/cart.css',

                'resources/css/pages/pembeli/checkout.css',
                'resources/css/pages/pembeli/checkout-success.css',

                'resources/css/pages/pembeli/order-history.css',

                'resources/css/pages/pembeli/payment-detail.css',
                'resources/js/pages/pembeli/payment-detail.js',

                'resources/css/pages/umkm/dashboard.css',
                'resources/js/pages/umkm/dashboard.js',

                'resources/css/pages/umkm/store-create.css',
                'resources/js/pages/umkm/store-create.js',

                'resources/css/pages/umkm/products-index.css',
                'resources/js/pages/umkm/products-index.js',

                'resources/css/pages/umkm/products-index.css',
                'resources/js/pages/umkm/products-index.js',
                
                'resources/css/pages/umkm/product-edit.css',
                'resources/js/pages/umkm/product-edit.js',

                'resources/css/public/base.css',
                'resources/css/public/home.css',
                'resources/js/public/home.js',
            ],
            refresh: true,
        }),
    ],
});
