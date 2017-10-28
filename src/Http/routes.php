<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

/* REST endpoints */

$app->group(['prefix' => 'api/v2','namespace' => 'Lagdo\Polr\Api\Http\Controllers'], function($app)
{
	// Get a paginated list of links
    $app->get('links', 'LinkController@getLinks');
    // Create a new link
	$app->post('links', 'LinkController@shortenLink');
    // Get the link with a given ending
    $app->get('links/{ending}', 'LinkController@lookupLink');
    // Update the link with a given ending
    $app->put('links/{ending}', 'LinkController@updateLink');
    // Delete the link with a given ending
    $app->delete('links/{ending}', 'LinkController@deleteLink');
    // Get stats for all links
    $app->get('links/stats/{period}', 'StatsController@getStats');
    // Get stats for the link with a given ending
    $app->get('links/{ending}/stats/{period}', 'StatsController@getLinkStats');
});
