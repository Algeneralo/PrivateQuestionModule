<?php

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

use Modules\PrivateQuestions\Http\Controllers\UserPrivateQuestionsController;
use Modules\PrivateQuestions\Http\Controllers\SpecialistPrivateQuestionsController;

Route::prefix('v2')->group(function () {
    Route::group(['prefix' => '/users/{user}/private-questions', 'middleware' => ['auth:sanctum']], function () {
        Route::get('/', [UserPrivateQuestionsController::class, 'index']);
        Route::post('/', [UserPrivateQuestionsController::class, 'store']);
        Route::get('/notifications', [UserPrivateQuestionsController::class, 'notifications']);
        Route::get('/{privateQuestion}', [UserPrivateQuestionsController::class, 'show']);
        Route::put('/{privateQuestion}/read', [UserPrivateQuestionsController::class, 'read'])->whereNumber('privateQuestion');
    });

    Route::group(['prefix' => '/specialists/{specialist}/private-questions', 'middleware' => ['auth:api-specialist']], function () {
        Route::get('/', [SpecialistPrivateQuestionsController::class, 'index']);
        Route::put('/{privateQuestion}/acquire', [SpecialistPrivateQuestionsController::class, 'acquire']);
        Route::put('/{privateQuestion}/answer', [SpecialistPrivateQuestionsController::class, 'answer']);
        Route::get('/notifications', [SpecialistPrivateQuestionsController::class, 'notifications']);
        Route::get('/{privateQuestion}', [SpecialistPrivateQuestionsController::class, 'show']);
    });
});
