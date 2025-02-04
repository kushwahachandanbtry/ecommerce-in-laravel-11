<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\AuthAdmin;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');


//Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add_to_cart'])->name('cart.add');
Route::put('/cart/increase-quantity/{rowId}', [CartController::class, 'increase_cart_quantity'])->name('cart.qty.increase');
Route::put('/cart/decrease-quantity/{rowId}', [CartController::class, 'decrease_cart_quantity'])->name('cart.qty.decrease');
Route::delete('/cart/item/remove/{rowId}', [CartController::class, 'remove_item'])->name('cart.item.remove');
Route::delete('/cart/clear', [CartController::class, 'empty_cart'])->name('cart.empty');

//Coupon Routes
Route::post('/cart/apply-coupon', [CartController::class, 'apply_coupon_code'])->name('cart.coupon.apply');
Route::delete('/cart/remove-coupon', [CartController::class, 'remove_coupon_code'])->name('cart.coupon.remove');

//Wishlist Routes
Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist'])->name('wishlist.add');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::delete('/wishlist/item/remove/{rowId}', [WishlistController::class, 'remove_item'])->name('wishlist.item.remove');
Route::delete('/wishlist/clear', [WishlistController::class, 'empty_wishlist'])->name('wishlist.items.clear');
Route::post('/wishlist/move-to-cart/{rowId}',[WishlistController::class, 'move_to_cart'])->name('wishlist.move.to.cart');

//Checkout Routes
Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/place-an-order', [CartController::class, 'place_an_order'])->name('cart.place.an.order');
Route::get('/order-confirmation', [CartController::class, 'order_confirmation'])->name('cart.order.confirmation');

//Contact Form Routes
Route::get('/contact-us', [HomeController::class, 'contact'])->name('home.contact');
Route::post('/contact/store', [HomeController::class, 'contact_store'])->name('home.contact.store');

//About us Routes
Route::get('/home/about-us', [HomeController::class, 'about_us'])->name('home.about.us');

//Global Search Routes
Route::get('/search', [HomeController::class, 'search'])->name('home.search');

//Grouping routes for as a admin or user
Route::middleware(['auth'])->group(function() {
    Route::get('account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/account-order/{order_id}/details', [UserController::class, 'order_details'])->name('user.order.details');
    Route::put('/accound-order/order-cancel', [UserController::class, 'order_cancel'])->name('user.order.cancel');
    // Route::get('/addresses', [UserController::class, 'addresses'])->name('user.addresses');
    // Route::get('/add-address', [UserController::Class, 'add_address'])->name('user.address.add');
    // Route::get('/account-details', [UserController::class, 'account_details'])->name('user.accound.details');
});

Route::middleware(['auth', AuthAdmin::class])->group(function() {
    Route::get('admin', [AdminController::class, 'index'])->name('admin.index');

    //Brand routes
    Route::get('admin/brands', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('admin/brand/add', [AdminController::class, 'add_brand'])->name('admin.brand.add');
    Route::post('admin/brand/store', [AdminController::class, 'brand_store'])->name('admin.brand.store');
    Route::get('admin/brand/edit/{id}', [AdminController::class, 'brand_edit'])->name('admin.brand.edit');
    Route::put('admin/brand/update', [AdminController::class, 'brand_update'])->name('admin.brand.update');
    Route::delete('admin/brand/{id}/delete', [AdminController::class, 'brand_delete'])->name('admin.brand.delete');

    //Category routes
    Route::get('admin.categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('admin/category/add', [AdminController::class, 'category_add'])->name('admin.category.add');
    Route::post('admin/category/store', [AdminController::class, 'category_store'])->name('admin.category.store');
    Route::get('admin/category/edit/{id}', [AdminController::class, 'category_edit'])->name('admin.category.edit');
    Route::put('admin/category/update', [AdminController::class, 'category_update'])->name('admin.category.update');
    Route::delete('admin/category/{id}/delete', [AdminController::class, 'category_delete'])->name('admin.category.delete');

    //Products routes
    Route::get('admin.products', [AdminController::class, 'products'])->name('admin.products');
    Route::get('admin/product/add', [AdminController::class, 'product_add'])->name('admin.product.add');
    Route::post('admin/product/store', [AdminController::class, 'product_store'])->name('admin.product.store');
    Route::get('admin/product/{id}/edit', [AdminController::class, 'product_edit'])->name('admin.product.edit');
    Route::put('admin/product/update', [AdminController::class, 'product_update'])->name('admin.product.update');
    Route::delete('admin/product/{id}/delete', [AdminController::class, 'product_delete'])->name('admin.product.delete');

    //Coupon Routes
    Route::get('/admin/coupons', [AdminController::class, 'coupons'])->name('admin.coupons');
    Route::get('/admin/coupon/add', [AdminController::class, 'coupon_add'])->name('admin.coupon.add');
    Route::post('/admin/coupon/store', [AdminController::class, 'coupon_store'])->name('admin.coupon.store');
    Route::get('/admin/coupon/{id}/edit', [AdminController::class, 'coupon_edit'])->name('admin.coupon.edit');
    Route::put('/admin/coupon/update', [AdminController::class, 'coupon_update'])->name('admin.coupon.update');
    Route::delete('/admin/coupon/{id}/delete', [AdminController::class, 'coupon_delete'])->name('admin.coupon.delete');

    //Order Routes
    Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/admin/order/{order_id}/details', [AdminController::class, 'order_details'])->name('admin.order.details');
    Route::put('admin/order/update-status', [AdminController::class, 'update_order_status'])->name('admin.order.status.update');

    //Slides Routes
    Route::get('admin/slides', [AdminController::class, 'slides'])->name('admin.slides');
    Route::get('admin/slide/add', [AdminController::class, 'slide_add'])->name('admin.slide.add');
    Route::post('admin/slide/store', [AdminController::class, 'slide_store'])->name('admin.slide.store');
    Route::get('admin/slide/{id}/edit', [AdminController::class, 'slide_edit'])->name('admin.slide.edit');
    Route::put('admin/slide/update', [AdminController::class, 'slide_update'])->name('admin.slide.update');
    Route::delete('admin/slide/{id}/delete', [AdminController::class, 'slide_delete'])->name('admin.slide.delete');

    //Contact Routes
    Route::get('admin/contacts', [AdminController::class, 'contacts'])->name('admin.contacts');
    Route::delete('admin/contact/{id}/delete', [AdminController::class, 'contact_delete'])->name('admin.contact.delete');

    //Setting Routes
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings/update', [AdminController::class, 'update_settings'])->name('admin.update.settings');


    //Global product search
    Route::get('/admin/search', [AdminController::class, 'search'])->name('admin.search');
});
