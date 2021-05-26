<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UserController;
use App\Models\Request as ModelsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('workers', [UserController::class, 'store'])->name('workers.store');
Route::delete('workers', [UserController::class, 'destroy'])->name('workers.destroy');
Route::post('managers', [UserController::class, 'store'])->name('managers.store');
Route::delete('managers', [UserController::class, 'destroy'])->name('managers.destroy');


// {worker_id} can be replaced if implementing API Token Authentication module
Route::prefix('workers/{worker_id}')->where(['worker_id' => '[0-9]+'])->group(function () {

    Route::prefix('request')->group(function () {
        Route::get('status/approved', [RequestController::class, 'showByStatusApproved'])->name('workers.request.status.approved');
        Route::get('status/rejected', [RequestController::class, 'showByStatusRejected'])->name('workers.request.status.rejected');
        Route::get('status/pending', [RequestController::class, 'showByStatusPending'])->name('workers.request.status.pending');
        Route::get('remaining_days', [RequestController::class, 'showRemainingDays'])->name('workers.request.remaining_days');
    });

    Route::apiResource('request', RequestController::class);
});

// Can be improved by setting User Role to users
Route::prefix('managers')->group(function () {
    Route::prefix('request')->group(function () {
        Route::get('status/approved', [AdminController::class, 'showByStatusApproved'])->name('managers.request.status.approved');
        Route::get('status/pending', [AdminController::class, 'showByStatusPending'])->name('managers.request.status.pending');
        Route::get('worker/{worker_id}', [AdminController::class, 'index'])->where(['worker_id' => '[0-9]+'])->name('managers.request.worker.show');
        Route::get('/overlapping', [AdminController::class, 'overlappingRequests'])->name('managers.request.overlapping');
        Route::get('/{request_id}', [AdminController::class, 'showRequest'])->where(['request_id' => '[0-9]+'])->name('managers.request.show');
        Route::post('/{request_id}/approve', [AdminController::class, 'approveRequest'])->where(['request_id' => '[0-9]+'])->name('managers.request.approve');
        Route::post('/{request_id}/reject', [AdminController::class, 'rejectRequest'])->where(['request_id' => '[0-9]+'])->name('managers.request.reject');
        Route::get('/', [AdminController::class, 'all'])->name('managers.request.all');
    });
});

Route::get('help', function() {
    $data = Storage::disk('local')->get('public/api_help.md');
    return json_encode($data);
});

Route::any('/', function() {
    $data = [
        'code' => 200,
        '_method' => $_SERVER['REQUEST_METHOD'],
        'message' => "Welcome to the API!"
    ];

    return $data;
});