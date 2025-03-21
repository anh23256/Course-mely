<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::prefix('v1')
    ->group(function () {
//        Route::prefix('ai')
//            ->group(function () {
//                Route::post('generate-text', [\App\Http\Controllers\API\AI\AiController::class, 'generateText']);
//            });
        Route::prefix('cloudflare')
            ->group(function () {
                Route::post('generate-text', [\App\Http\Controllers\API\AI\AiController::class, 'generateTextCloudflare']);
            });
    });
