<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/x', function () {
    return 'API';
});

// Auth / registration
Route::post('users/login', [UserController::class, 'login']);
Route::post('users', [UserController::class, 'store']);

// Public read-only catalog (guest mode)
Route::get('movies', [MovieController::class, 'index']);
Route::get('movies/{id}', [MovieController::class, 'show']);
Route::get('person', [PersonController::class, 'index']);
Route::get('person/{id}', [PersonController::class, 'show']);
Route::get('roles', [RoleController::class, 'index']);
Route::get('roles/{id}', [RoleController::class, 'show']);
Route::get('tasks', [TaskController::class, 'index']);
Route::get('tasks/{id}', [TaskController::class, 'show']);
Route::get('reviews', [ReviewController::class, 'index']);
Route::get('reviews/{id}', [ReviewController::class, 'show']);

// Authenticated user endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('users/logout', [UserController::class, 'logout']);

    Route::delete('usersme', [UserController::class, 'destroySelf'])
        ->middleware('ability:usersme:delete');
    Route::patch('usersme', [UserController::class, 'updateSelf'])
        ->middleware('ability:usersme:patch');
    Route::patch('usersmeupdatepassword', [UserController::class, 'updatePassword'])
        ->middleware('ability:usersme:updatePassword');
    Route::get('usersme', [UserController::class, 'indexSelf'])
        ->middleware('ability:usersme:get');

    // Registered users can write/edit/delete their own reviews
    Route::post('reviews', [ReviewController::class, 'store'])
        ->middleware('ability:reviews:post');
    Route::patch('reviews/{id}', [ReviewController::class, 'update'])
        ->middleware('ability:reviews:patch');
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy'])
        ->middleware('ability:reviews:delete');

    // Admin only user management
    Route::get('users', [UserController::class, 'index'])
        ->middleware('ability:*');
    Route::get('users/{id}', [UserController::class, 'show'])
        ->middleware('ability:*');
    Route::patch('users/{id}', [UserController::class, 'update'])
        ->middleware('ability:*');
    Route::delete('users/{id}', [UserController::class, 'destroy'])
        ->middleware('ability:*');

    // Admin only catalog management
    Route::post('movies', [MovieController::class, 'store'])
        ->middleware('ability:*');
    Route::patch('movies/{id}', [MovieController::class, 'update'])
        ->middleware('ability:*');
    Route::delete('movies/{id}', [MovieController::class, 'destroy'])
        ->middleware('ability:*');

    Route::post('person', [PersonController::class, 'store'])
        ->middleware('ability:*');
    Route::patch('person/{id}', [PersonController::class, 'update'])
        ->middleware('ability:*');
    Route::delete('person/{id}', [PersonController::class, 'destroy'])
        ->middleware('ability:*');

    Route::post('roles', [RoleController::class, 'store'])
        ->middleware('ability:*');
    Route::patch('roles/{id}', [RoleController::class, 'update'])
        ->middleware('ability:*');
    Route::delete('roles/{id}', [RoleController::class, 'destroy'])
        ->middleware('ability:*');

    Route::post('tasks', [TaskController::class, 'store'])
        ->middleware('ability:*');
    Route::patch('tasks/{id}', [TaskController::class, 'update'])
        ->middleware('ability:*');
    Route::delete('tasks/{id}', [TaskController::class, 'destroy'])
        ->middleware('ability:*');
});