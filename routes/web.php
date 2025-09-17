<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// $router->post('/v1/send-email/{user_id}', 'EmailController@sendOTP');

$router->post('/v1/auth/register', 'UsersController@registerNew');
$router->post('/v1/auth/check-account', 'UsersController@checkAccount');
$router->post('/v1/auth/check-account-password', 'UsersController@checkAccountPassword');
$router->post('/v1/auth/forget-password', 'UsersController@forgetPassword');
// $router->post('/v1/auth/change-password', 'UsersController@changepassword');

// $router->post('/v1/auth/register-otp', 'UsersController@registerWithOtp');
// $router->post('/v1/auth/register-with-whatsapp', 'UsersController@registerWithWhatsapp');
// $router->post('/v1/otp/{user_id}/request', 'OtpController@requestOTP');
// $router->post('/v1/otp-verify/{user_id}', 'OtpController@verifyOtp');
$router->post('/v1/otp-verify', 'OtpController@verifyOtpWithContact');
// $router->post('/v1/otp-verify-reset-passwrod/{user_id}', 'OtpController@verifyOtpResetPassword');

// $router->get('/v1/auth/verifikasi-email', 'UsersController@verifikasi');
$router->post('/v1/auth/login', 'UsersController@login');
$router->post('/v1/auth/login/admin', 'AdminsController@login');
$router->post('/v1/auth/login-google', 'UsersController@loginWithGoogle');
$router->get('/v1/auth/google', 'UsersController@redirectToGoogle');
$router->get('/v1/auth/google/callback', 'UsersController@handleGoogleCallback');

// $router->get('/v1/auth/me', 'UsersController@getMe');

// $router->post('/transaction/callback', 'TransactionController@callback');

//auth new
// $router->get('/v1/auth/google-zukses', 'AuthController@redirectToGoogle');
// $router->get('/v1/auth/google/callback', 'AuthController@handleGoogleCallback');
$router->get('/v1/product', 'ProductController@index');
$router->get('/v1/product/show_product/{id}', 'ProductController@show');
$router->get('/v1/province', 'MasterProvinceController@list');
$router->post('/v1/otp-verify/{user_id}', 'OtpController@verifyOtp');
$router->post('/v1/send-email/{user_id}', 'EmailController@sendOTP');
$router->post('/v1/auth/login-otp', 'AuthController@login');
$router->post('/v1/auth/login-otp-admin', 'AuthController@loginAdmin');
// $router->post('/v1/auth/register', 'AuthController@register');
// $router->post('/v1/otp/{user_id}/request', 'OtpController@requestOTP');
// $router->post('/v1/otp/{user_id}/request-email', 'EmailController@sendOTPEmail');
$router->get('/v1/category/list-array', 'ProductCategoryController@showListArray');
$router->get('/v1/category/list', 'ProductCategoryController@showList');
$router->get('/v1/banners', 'BannerController@index');
$router->get('/list', 'ProductCategoryController@list');

$router->get('v1/messages', 'MessageController@index'); // Mengambil riwayat pesan
$router->post('v1/messages', 'MessageController@store'); // Menyimpan pesan baru
$router->post('/midtrans-callback', 'OrderController@midtransCallback');


$router->group(['prefix' => 'v1/master'], function () use ($router) {
    $router->group(['prefix' => 'province'], function () use ($router) {
        $router->get('/', 'MasterProvinceController@index');
    });

    $router->group(['prefix' => 'city'], function () use ($router) {
        $router->get('/', 'MasterCityController@index');
    });

    $router->group(['prefix' => 'subdistrict'], function () use ($router) {
        $router->get('/', 'MasterSubdistrictController@index');
    });

    $router->group(['prefix' => 'postal_code'], function () use ($router) {
        $router->get('/', 'MasterPostalCodeController@index');
    });

    $router->group(['prefix' => 'polygon'], function () use ($router) {
        $router->get('/check_coordinate', 'MasterSubdistrictPolygonController@check_coordinate');
    });

    $router->group(['prefix' => 'status'], function () use ($router) {
        $router->get('/', 'MasterStatusController@index');
    });
});

$router->get('v1/fees', 'ServiceFeeController@getSettings');


$router->group(['prefix' => 'v1', 'middleware' => 'check-token'], function () use ($router) {

    $router->group(['prefix' => 'product'], function () use ($router) {
        // $router->get('/', 'ProductController@index');
        $router->get('/show', 'ProductController@list');
        $router->get('/show-seller', 'ProductController@listDetailSeller');
        $router->get('/performa-product', 'ProductController@performaProduct');
        $router->get('/{id}', 'ProductController@detail');
        $router->post('/', 'ProductController@store');
        $router->post('/{id}', 'ProductController@update');
        $router->delete('/{id}', 'ProductController@delete');
        $router->delete('/{id}/variant', 'ProductController@deleteVariantPrice');
    });

    $router->group(['prefix' => 'transaction'], function () use ($router) {
        $router->get('/', 'TransactionController@index');
        $router->post('/', 'TransactionController@create');
        // $router->get('/', 'ProductController@index');
        // $router->get('/{id}', 'ProductController@detail');
        // $router->delete('/{id}', 'ProductController@delete');
    });



    //user role
    $router->get('/users-role', 'UsersRoleController@index');
    $router->get('/users-role/list/{role}', 'UsersRoleController@list');
    $router->post('/users-role/add', 'UsersRoleController@create');
    $router->post('/users-role/{id}/edit', 'UsersRoleController@update');
    $router->delete('/users-role/{id}/delete', 'UsersRoleController@destroy');

    //menu
    $router->get('/menus/list', 'MenuController@list');
    $router->get('/menus', 'MenuController@index');
    $router->post('/menus/ordinal', 'MenuController@ordinal');
    $router->get('/menus/parent', 'MenuController@parent');
    $router->get('/menus/tree/{parent_name}', 'MenuController@tree');
    $router->get('/menus/tree/access/{id_role}', 'MenuController@treeRole');
    $router->get('/menus/{id}', 'MenuController@show');
    $router->post('/menus/add', 'MenuController@create');
    $router->post('/menus/{id}/edit', 'MenuController@update');
    $router->delete('/menus/{id}/delete', 'MenuController@destroy');

    //User Access Menu
    $router->get('/users-access-menu/{id_role}', 'UsersAccessMenuController@index');
    $router->get('/users-access-menu/list/{role}', 'UsersAccessMenuController@list'); //access menu
    $router->post('/users-access-menu/add', 'UsersAccessMenuController@create');
    $router->post('/users-access-menu/{id}/edit', 'UsersAccessMenuController@update');
    $router->delete('/usersn-access-menu/{role}/{menu}/delete', 'UsersAccessMenuController@destroy');


    //alamat lengkap
    $router->get('full-address', 'FullAddressController@listAddress');
    $router->get('/alamat-sekitar', 'FullAddressController@getNearbyPlaces');

    //user address
    $router->post('user-address/create/{user_id}', 'UserAddressController@create');
    $router->get('user-address/{user_id}', 'UserAddressController@index');
    $router->post('user-address/{id}/edit', 'UserAddressController@update');
    $router->post('user-address/{id}/edit-status', 'UserAddressController@isPrimary');
    $router->delete('user-address/{id}/delete', 'UserAddressController@destroy');

    $router->get('user-profile/{user_id}', 'UserProfileController@show');
    $router->post('user-profile/{user_id}/create', 'UserProfileController@create');
    $router->post('user-profile/{user_id}/update', 'UserProfileController@update');
    $router->post('user-profile/{user_id}/delete', 'UserProfileController@destroy');

    $router->post('otp-verify-contact/{user_id}', 'OtpController@verifyOtpContact');
    $router->post('otp/{user_id}/request-contact', 'OtpController@requestOTPContact');

    $router->group(['prefix' => 'banks'], function () use ($router) {
        // GET /api/banners - Get all banners
        $router->get('/', 'BankController@index');

        // POST /api/banners - Create a new banner
        // Note: For file uploads in Lumen, PUT/PATCH might not work out of the box.
        // It's often easier to use POST for updates involving files.
        $router->post('/', 'BankController@store');

        // GET /api/banners/{id} - Get a single banner
        $router->get('/{id}', 'BankController@show');

        // POST /api/banners/{id} - Update a banner (using POST for file upload compatibility)
        $router->post('/{id}', 'BankController@update');

        // DELETE /api/banners/{id} - Delete a banner
        $router->delete('/{id}', 'BankController@destroy');
    });

    $router->group(['prefix' => 'bank-accounts'], function () use ($router) {
        $router->get('/{user_id}/show', 'BankAccountController@index');
        $router->post('/{user_id}', 'BankAccountController@store');
        $router->post('/{id}/edit', 'BankAccountController@update');
        $router->delete('/{id}', 'BankAccountController@destroy');
        $router->post('/{id}/edit-status', 'BankAccountController@isPrimary');
    });

    // $router->get('shop/profile', 'ShopProfileController@show');
    // $router->post('shop/profile', 'ShopProfileController@store');

    $router->group(['prefix' => 'shop'], function () use ($router) {
        $router->group(['prefix' => 'requerment'], function () use ($router) {
            $router->get('/', 'ShopProfileController@requerment');
            $router->post('create/{seller_id}/{user_id}', 'RequermentController@store');
            $router->post('update-product/{product_id}/{user_id}', 'RequermentController@updateProduct');
        });
        $router->group(['prefix' => 'address'], function () use ($router) {
            $router->post('create/{seller_id}', 'ShopAddressController@create');
            $router->get('{seller_id}', 'ShopAddressController@index');
            $router->post('{id}/edit', 'ShopAddressController@update');
            $router->post('{id}/edit-status', 'ShopAddressController@isPrimary');
            $router->delete('{id}/delete', 'ShopAddressController@destroy');
        });

        $router->group(['prefix' => 'bank-accounts'], function () use ($router) {
            $router->get('/{seller_id}/show', 'ShopBankAccountController@index');
            $router->post('/{seller_id}', 'ShopBankAccountController@store');
            $router->post('/{id}/edit', 'ShopBankAccountController@update');
            $router->delete('/{id}', 'ShopBankAccountController@destroy');
            $router->post('/{id}/edit-status', 'ShopBankAccountController@isPrimary');
        });
        $router->group(['prefix' => 'shipping-settings'], function () use ($router) {
            $router->get('/', 'StoreShippingSettingController@getSettings');
            $router->post('/', 'StoreShippingSettingController@updateSettings');
        });
    });
    $router->group(['prefix' => 'courier-service'], function () use ($router) {
        $router->get('/', 'CourierController@index');
        $router->get('/list', 'CourierController@list');
        $router->get('/list/{seller_id}', 'CourierController@listSeller');
    });
    $router->group(['prefix' => 'cart'], function () use ($router) {
        $router->post('/', 'CartController@store');
        $router->get('/', 'CartController@index');
        $router->post('/update-variant', 'CartController@updateVariant');
        $router->post('/update-qty', 'CartController@updateQty');
        $router->delete('/delete/{id_product}/{id_variant}', 'CartController@delete');
        $router->get('/header-cart', 'CartController@headerCart');
        $router->delete('/delete-multiple', 'CartController@deleteMultiple');
    });

    $router->get('shop-profile', 'ShopProfileController@show');
    $router->post('shop-profile/{user_id}/update', 'ShopProfileController@create');
    $router->delete('shop-profile/{id}/delete', 'ShopProfileController@destroy');

    $router->get('/checkout', 'CheckoutController@index');

    $router->post('/checkout-payment', 'OrderController@checkout');
    $router->post('/pay-va', 'OrderController@payVa');

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->post('/', 'UsersController@register');
        $router->post('/{id}', 'UsersController@edit');
        $router->post('/{id}/update-status', 'UsersController@updateStatus');
        $router->get('/', 'UsersController@index');
        $router->delete('/{id}', 'UsersController@destroy');
    });
    $router->get('customer', 'CustomerController@getCustomers');

    $router->get('service-fee', 'ServiceFeeController@index');
});


$router->group(['prefix' => 'v1', 'middleware' => 'check-admin'], function () use ($router) {
    $router->group(['prefix' => 'master'], function () use ($router) {
        $router->group(['prefix' => 'province'], function () use ($router) {
            // $router->get('/', 'MasterProvinceController@index');
            $router->post('/', 'MasterProvinceController@create');
            $router->post('/{id}', 'MasterProvinceController@update');
            $router->delete('/{id}', 'MasterProvinceController@delete');
        });

        $router->group(['prefix' => 'city'], function () use ($router) {
            // $router->get('/', 'MasterCityController@index');
            $router->post('/', 'MasterCityController@create');
            $router->post('/{id}', 'MasterCityController@update');
            $router->delete('/{id}', 'MasterCityController@delete');
        });

        $router->group(['prefix' => 'subdistrict'], function () use ($router) {
            // $router->get('/', 'MasterSubdistrictController@index');
            $router->post('/', 'MasterSubdistrictController@create');
            $router->post('/{id}', 'MasterSubdistrictController@update');
            $router->delete('/{id}', 'MasterSubdistrictController@delete');
        });

        $router->group(['prefix' => 'postal_code'], function () use ($router) {
            // $router->get('/', 'MasterPostalCodeController@index');
            $router->post('/', 'MasterPostalCodeController@create');
            $router->post('/{id}', 'MasterPostalCodeController@update');
            $router->delete('/{id}', 'MasterPostalCodeController@delete');
        });

        $router->group(['prefix' => 'polygon'], function () use ($router) {
            // $router->get('/check_coordinate', 'MasterSubdistrictPolygonController@check_coordinate');
            $router->post('/validate_coordinate', 'MasterSubdistrictPolygonController@validate_coordinate');
        });

        $router->group(['prefix' => 'status'], function () use ($router) {
            // $router->get('/', 'MasterStatusController@index');
            $router->post('/', 'MasterStatusController@create');
            $router->post('/{id}', 'MasterStatusController@update');
            $router->delete('/{id}', 'MasterStatusController@delete');
        });
    });

    $router->group(['prefix' => 'category'], function () use ($router) {
        $router->get('/', 'ProductCategoryController@index');
        $router->get('/show', 'ProductCategoryController@show');
        $router->post('/', 'ProductCategoryController@create');
        $router->post('/{id}', 'ProductCategoryController@update');
        $router->delete('/{id}', 'ProductCategoryController@delete');
        $router->get('/list', 'ProductCategoryController@list');
    });

    $router->group(['prefix' => 'banners'], function () use ($router) {
        // GET /api/banners - Get all banners
        // POST /api/banners - Create a new banner
        // Note: For file uploads in Lumen, PUT/PATCH might not work out of the box.
        // It's often easier to use POST for updates involving files.
        $router->post('/', 'BannerController@store');
        $router->get('/list', 'BannerController@list');

        // GET /api/banners/{id} - Get a single banner
        $router->get('/{id}', 'BannerController@show');

        // POST /api/banners/{id} - Update a banner (using POST for file upload compatibility)
        $router->post('/{id}', 'BannerController@update');
        $router->post('/{id}/active', 'BannerController@isActive');

        // DELETE /api/banners/{id} - Delete a banner
        $router->delete('/{id}', 'BannerController@destroy');
    });

    $router->group(['prefix' => 'admin'], function () use ($router) {
        $router->post('/', 'AdminsController@register');
        $router->post('/{id}', 'AdminsController@edit');
        $router->post('/{id}/update-status', 'AdminsController@updateStatus');
        $router->get('/', 'AdminsController@index');
        $router->delete('/{id}', 'AdminsController@destroy');
    });

    $router->post('/fees/update', 'ServiceFeeController@updateSettings');
    $router->get('orders/by-seller', 'OrderController@showGroupedBySeller');
    $router->get('courier-service', 'CourierController@listCourier');
    $router->get('orders/by-seller/{courier_id}', 'OrderController@showGroupedByCourier');


});
$router->get('orders/by-seller', 'OrderController@showGroupedBySeller');
$router->get('orders/by-seller/{courier_id}', 'OrderController@showGroupedByCourier');
$router->get('courier-service', 'CourierController@listCourier');

// Contoh untuk Lumen di routes/web.php
$router->get('orders-items/by-seller/{order_id}/{seller_id}', 'OrderController@showProductBySeller');

// Contoh untuk Laravel di routes/api.php
// Route::get('/orders/by-seller/{orderIdentifier}', [App\Http\Controllers\OrderController::class, 'showGroupedBySeller']);

// Midtrans Account Verification Routes
$router->group(['prefix' => 'v1/midtrans'], function () use ($router) {
    $router->post('/check-account', 'MidtransAccountController@checkAccount');
    $router->get('/supported-banks', 'MidtransAccountController@getSupportedBanks');
});

// Command execution routes (admin only)
$router->group(['prefix' => 'commands'], function () use ($router) {
    $router->get('/', 'CommandController@commands');
    $router->post('/execute', 'CommandController@execute');
});

$router->options('/{any:.*}', function () {
    return response()->json([], 200);
});
