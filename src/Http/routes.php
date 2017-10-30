<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

/* REST endpoints */

$this->app->group(['prefix' => 'api/v2', 'namespace' => 'Lagdo\Polr\Api\Http\Controllers',
		'middleware' => 'rest_api'], function($app) {
	// Get a paginated list of links
    $app->get('links', 'LinkController@getAdminLinks');
	// Get a paginated list of links
    $app->get('user/links', 'LinkController@getUserLinks');
    // Create a new link
	$app->post('links', 'LinkController@shortenLink');
    // Get the link with a given ending
    $app->get('links/{ending}', 'LinkController@lookupLink');
    // Update the link with a given ending
    $app->put('links/{ending}', 'LinkController@updateLink');
    // Delete the link with a given ending
    $app->delete('links/{ending}', 'LinkController@deleteLink');
    // Get stats of a given type
    $app->get('stats', 'StatsController@getStats');
    // Get stats of a given type, for the link with a given ending
    $app->get('links/{ending}/stats', 'StatsController@getLinkStats');
});
