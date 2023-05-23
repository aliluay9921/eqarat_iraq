<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ServicesController;
use App\Http\Requests\ServicesRequest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

route::post("login", [AuthController::class, "login"]);
route::post("register", [AuthController::class, "register"]);

Route::middleware(['auth:api', 'active'])->group(function () {
    Route::middleware(['admin'])->group(function () {
        Route::get("get_all_users", [AuthController::class, 'getAllUsers']);
        Route::put("change_active_user", [AuthController::class, 'changeActiveUser']);
        Route::put("edit_service", [ServicesController::class, 'editService']);
    });

    Route::get("get_services", [ServicesController::class, "getServices"]);
    Route::get("get_posts", [PostController::class, 'getPosts']);
    Route::get("get_auth_service", [ServicesController::class, 'getAuthService']);
    Route::get("get_hotels", [HotelController::class, 'getHotels']);
    Route::post("add_service", [ServicesController::class, "addService"]);
    Route::post("add_post", [PostController::class, 'addPost']);
    Route::post("add_hotel", [HotelController::class, 'addHotel']);

    Route::put("update_post", [PostController::class, 'updatePost']);
    Route::put("update_hotel", [HotelController::class, 'updateHotel']);
    Route::delete("delete_post", [PostController::class, 'deletePost']);
    Route::delete("delete_service", [ServicesController::class, 'deleteService']);
    Route::delete("delete_hotel", [HotelController::class, 'deleteHotel']);

    // route::put("reset_password", [AuthController::class, "resetPassword"]);
    route::put("update_profile", [AuthController::class, "updateProfile"]);
});
