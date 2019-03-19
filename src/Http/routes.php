<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

/* REST endpoints */

$this->app->group(['prefix' => 'api/v2', 'namespace' => 'Lagdo\Polr\Api\Http\Controllers',
        'middleware' => 'rest_api'], function($app) {
    /*
     * Links routes
     */
    // Get a paginated list of links
    $app->get('links', 'LinkController@getAdminLinks');
    // Get a paginated list of user links
    $app->get('users/me/links', 'LinkController@getUserLinks');
    // Create a new link
    $app->post('links', 'LinkController@shortenLink');
    // Create a list of link
    $app->post('links/batch', 'LinkController@shortenLinks');
    // Get the link with a given ending
    $app->get('links/{ending}', 'LinkController@lookupLink');
    // Update the link with a given ending
    $app->put('links/{ending}', 'LinkController@updateLink');
    // Delete the link with a given ending
    $app->delete('links/{ending}', 'LinkController@deleteLink');

    /*
     * Stats routes
     */
    // Get stats of a given type
    $app->get('stats', 'StatsController@getStats');
    // Get stats of a given type, for the link with a given ending
    $app->get('links/{ending}/stats', 'StatsController@getLinkStats');

    /*
     * Users routes
     */
    // Get a paginated list of users
    $app->get('users', 'UserController@getUsers');
    // Create a new user
    // $app->post('users', 'UserController@createUser');
    // Get the user with a given id
    $app->get('users/{id}', 'UserController@getUser');
    // Update the user with a given id
    $app->put('users/{id}', 'UserController@updateUser');
    // Generate new API key for the user
    $app->post('users/{id}/api', 'UserController@generateNewKey');
    // Update API settings for the user
    $app->put('users/{id}/api', 'UserController@updateApi');
    // Delete the user with a given id
    // $app->delete('users/{id}', 'UserController@deleteUser');
});
