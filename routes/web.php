<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/test', 'Test@test')->name('test');
Route::get('/test', 'Test@test')->name('test');

Route::post('/webhook/receive', 'Webhook@receive')->name('webhook.receive');

/**
 * only auth users
 */
Route::group(['middleware' => ['jwt']], function () {
    Route::group(['middleware' => ['auth', 'check_user', 'refresh_jwt']], function () {

        Route::get('/', 'Reports@create')->name('main');

        /* Reports resource */
        Route::get('/reports/create', 'Reports@create')->name('reports.create');
        Route::post('/reports/store', 'Reports@store')->name('reports.store');
        Route::delete('reports/{report}', 'Reports@destroy')->name('reports.delete');

        /* Statistics */
        Route::get('/statistics', 'Statistics@index')->name('statistics.index');
        Route::get('/statistics/filter', 'Statistics@filter')->name('statistics.filter');
        Route::get('/statistics/chart-data', 'Statistics@chartData')->name('statistics.chart-data');

        /* Admin */

        //superadmin or admin
        Route::group(['middleware' => ['role:admin|superadmin']], function() {

        });

    });
});