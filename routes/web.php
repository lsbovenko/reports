<?php

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

/**
 * only auth users
 */
Route::group(['middleware' => ['jwt']], function () {
    Route::group(['middleware' => ['auth', 'check_user', 'refresh_jwt']], function () {
        Route::get('/', 'IndexController@index')->name('main');

        Route::get('/view', 'BrowseIdeasController@priorityBoard')->name('view');
        Route::post('/view/add-time/{date}', 'IndexController@createIdea');

        Route::get('/view-statistics', 'BrowseIdeasController@priorityBoard')->name('view-statistics');

        //superadmin or admin
        Route::group(['middleware' => ['role:admin|superadmin']], function() {
            Route::post('/pin-priority/{id}', 'ReviewIdeaController@pinToPriority')->where('id', '[0-9]+')->name('pin-priority');

        });

        //superadmin
        Route::group(['middleware' => ['role:superadmin']], function() {
            Route::get('/edit-idea/{id}', 'EditIdeaController@edit')->where('id', '[0-9]+')->name('edit-idea');
            Route::post('/edit-idea/{id}', 'EditIdeaController@postEdit')->where('id', '[0-9]+');
            Route::post('/review-idea/{id}', 'ReviewIdeaController@approve')->where('id', '[0-9]+');
            Route::get('/pending-review', 'BrowseIdeasController@pendingReview')->name('pending-review');
            Route::get('/declined', 'BrowseIdeasController@declined')->name('declined');

            Route::group([
                'as' => 'users.',
                'prefix' => 'users'
            ], function () {
                Route::get('/', 'UsersController@index')->name('index');
            });
        });
    });
});