<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\Settings\PasswordController;
use App\Http\Controllers\Api\Settings\ProfileController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication, No Rate Limiting)
|--------------------------------------------------------------------------
*/

Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{slug}', [ProjectController::class, 'show']);

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/paginated', [CategoryController::class, 'indexPaginated']);
Route::get('/categories/projects', [CategoryController::class, 'projectCategories']);
Route::get('/categories/articles', [CategoryController::class, 'articleCategories']);

Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/paginated', [TagController::class, 'indexPaginated']);

Route::get('/skills', [SkillController::class, 'index']);
Route::get('/skills/paginated', [SkillController::class, 'indexPaginated']);

/*
|--------------------------------------------------------------------------
| Auth Routes (Rate Limited - 5 requests per minute)
|--------------------------------------------------------------------------
*/

Route::middleware('throttle:auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // User Profile & Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all-devices', [AuthController::class, 'logoutAllDevices']);

    // Settings
    Route::prefix('settings')->group(function () {
        Route::put('/password', [PasswordController::class, 'update']);
        Route::put('/profile', [ProfileController::class, 'update']);
    });

    /*
    |--------------------------------------------------------------------------
    | Write Operations (Rate Limited - 100 requests per minute)
    |--------------------------------------------------------------------------
    */

    Route::middleware('throttle:writes')->group(function () {
        
        // Articles - Admin & Author
        Route::middleware('role:admin,author')->group(function () {
            Route::post('/articles', [ArticleController::class, 'store']);
            Route::put('/articles/{article}', [ArticleController::class, 'update']);
            Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
            Route::get('/articles/{article}/edit', [ArticleController::class, 'edit']);
        });

        // Projects, Categories, Tags, Skills - Admin Only
        Route::middleware('role:admin')->group(function () {
            
            // Projects
            Route::post('/projects', [ProjectController::class, 'store']);
            Route::put('/projects/{project}', [ProjectController::class, 'update']);
            Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
            Route::get('/projects/{project}/edit', [ProjectController::class, 'edit']);

            // Categories
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{category}', [CategoryController::class, 'update']);
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
            Route::get('/categories/{category}/edit', [CategoryController::class, 'edit']);

            // Tags
            Route::post('/tags', [TagController::class, 'store']);
            Route::put('/tags/{tag}', [TagController::class, 'update']);
            Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
            Route::get('/tags/{tag}/edit', [TagController::class, 'edit']);

            // Skills
            Route::post('/skills', [SkillController::class, 'store']);
            Route::put('/skills/{skill}', [SkillController::class, 'update']);
            Route::delete('/skills/{skill}', [SkillController::class, 'destroy']);
            Route::get('/skills/{skill}/edit', [SkillController::class, 'edit']);
        });
    });
});