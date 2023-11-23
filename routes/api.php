<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('test',function(){
    p("Working");
});

Route::post('user/store','App\Http\Controllers\Api\UserController@store');
Route::get('user/get/{flag}',[UserController::class,'flagindex']);
Route::get('user/get/',[UserController::class,'index']);
Route::get('user/{id}',[UserController::class,'show']);
Route::delete('user/delete/{id}',[UserController::class,'destroy']);
Route::put('user/update/{id}',[UserController::class,'update']);
Route::patch('user/change-password/{id}',[UserController::class,'changePassword']);