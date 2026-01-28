<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

//endpoint
Route::get('/x', function(){
    return 'API';
});


//region users
//User kezelés, login, logout
//Mindenki
Route::post('users/login', [UserController::class, 'login']);
Route::post('users/logout', [UserController::class, 'logout']);
Route::post('users', [UserController::class, 'store']);

//Admin: 
//minden user lekérdezése
Route::get('users', [UserController::class, 'index'])
    ->middleware('auth:sanctum', 'ability:admin');
//Egy user lekérése    
Route::get('users/{id}', [UserController::class, 'show'])
    ->middleware('auth:sanctum', 'ability:admin');
//User adatok módosítása      
Route::patch('users/{id}', [UserController::class, 'update'])
->middleware('auth:sanctum', 'ability:admin');
//User törlés
Route::delete('users/{id}', [UserController::class, 'destroy'])
->middleware('auth:sanctum', 'ability:admin');  

//User self (Amit a user önmagával csinálhat) parancsok
Route::delete('usersme', [UserController::class, 'destroySelf'])
->middleware('auth:sanctum', 'ability:usersme:delete');

Route::patch('usersme', [UserController::class, 'updateSelf'])
->middleware('auth:sanctum', 'ability:usersme:patch');

Route::patch('usersmeupdatepassword', [UserController::class, 'updatePassword'])
->middleware('auth:sanctum', 'ability:usersme:updatePassword');

Route::get('usersme', [UserController::class, 'indexSelf'])
    ->middleware('auth:sanctum', 'ability:usersme:get'); 
//endregion

Route::get( 'movies', [MovieController::class, 'index'])->middleware('auth:sanctum', 'ability:movies:get'); 




// roles
 
//minden role lekerdezese
Route::get('roles', [RoleController::class, 'index']);
//egy role lekerdezese
Route::get('roles/{id}', [RoleController::class, 'show']);

Route::post('roles', [RoleController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:roles:post']);
//role adatok modositasa
Route::patch('roles/{id}', [RoleController::class, 'update'])
    ->middleware(['auth:sanctum', 'ability:roles:patch']);
//role torlese
Route::delete('roles/{id}', [RoleController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'ability:roles:delete']);



// tasks
//minden task lekerdezese
Route::get('tasks', [TaskController::class, 'index']);
//egy task lekerdezese
Route::get('tasks/{id}', [TaskController::class, 'show']);

Route::post('tasks', [TaskController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:tasks:post']);
//task adatok modositasa
Route::patch('tasks/{id}', [TaskController::class, 'update'])
    ->middleware(['auth:sanctum', 'ability:tasks:patch']);
//task torlese
Route::delete('tasks/{id}', [TaskController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'ability:tasks:delete']);

//endregion
