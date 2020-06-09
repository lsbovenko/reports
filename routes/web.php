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
Route::get('lang/{lang}', 'IndexController@switchLanguage')->name('lang');

/**
 * only auth users
 */
Route::group(['middleware' => ['jwt']], function () {
    Route::group(['middleware' => ['auth', 'check_user', 'refresh_jwt']], function () {

        Route::get('/', 'Reports@create')->name('main');
        Route::get('/projects/search', 'Projects@search')->name('projects.search');

        /* Reports resource */
        Route::get('/reports/create', 'Reports@create')->name('reports.create');
        Route::post('/reports/store', 'Reports@store')->name('reports.store');
        Route::delete('reports/{report}', 'Reports@destroy')->name('reports.delete');
        Route::get('/reports/month-stats', 'Reports@getMonthStats')->name('reports.month-stats');
        Route::put('/reports/{reportId}/update', 'Reports@update')->name('reports.update');
        Route::put('/reports/update-dates', 'Reports@updateDates')->name('reports.update-dates');

        /* Statistics */
        Route::get('/statistics', 'Statistics@index')->name('statistics.index');
        Route::get('/my-stats', 'Statistics@index')->name('my-stats');
        Route::get('/statistics/filter', 'Statistics@filter')->name('statistics.filter');
        Route::get('/statistics/chart-data', 'Statistics@chartData')->name('statistics.chart-data');
        Route::get('/statistics/logged-minutes', 'Statistics@loggedTime')->name('statistics.logged-minutes');
        Route::get('/statistics/time-all-period', 'Statistics@timeAllPeriod')->name('statistics.time-all-period');

        /* Admin */

        //superadmin or admin
        Route::group(['middleware' => ['role:admin|superadmin']], function() {
            Route::group([
                'as' => 'projects.',
                'prefix' => 'projects'
            ], function () {
                Route::get('/', 'Projects@index')->name('index');
                Route::get('/create', 'Projects@create')->name('create');
                Route::post('/create', 'Projects@save')->name('save');
                Route::get('/edit/{id}', 'Projects@edit')->where('id', '[0-9]+')->name('edit');
                Route::post('/edit/{id}', 'Projects@update')->where('id', '[0-9]+')->name('update');
            });

            Route::get('/hours', 'Hours@index')->name('hours.index');
            Route::get('/hours/filter', 'Hours@filter')->name('hours.filter');

            Route::get('/revenues', 'Revenues@index')->name('revenues.index');
            Route::get('/revenues/filter', 'Revenues@filter')->name('revenues.filter');

            Route::get('/planned-hours', 'PlannedHours@index')->name('planned-hours.index');
            Route::get('/planned-hours/edit/{year}', 'PlannedHours@edit')->where('year', '[0-9]+')->name('planned-hours.edit')
                ->middleware('check_year');
            Route::post('/planned-hours/edit/{year}', 'PlannedHours@update')->where('year', '[0-9]+')->name('planned-hours.update')
                ->middleware('check_year');
            Route::get('/pm', 'ProjectManager@index')->name('pm.index');
            Route::get('/pm/filter', 'ProjectManager@filter')->name('pm.filter');
        });
    });
});