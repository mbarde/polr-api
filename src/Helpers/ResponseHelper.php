<?php

namespace Lagdo\Polr\Api\Helpers;

class ResponseHelper
{
    /**
     * Create a JSON response for API calls
     *
     * @param string $result        The result data of the API call
     * @param string $message       The result message of the API call
     * @param number $code          The HTTP code returned by the API call
     *
     * @return Response
     */
	public static function make($result = null, $message = 'OK', $code = 200)
	{
	    $response = compact('message');
	    if($code == 200)
	    {
    	    $response['settings'] = [
    	        'analytics' => env('SETTING_ADV_ANALYTICS'),
    	        'username' => UserHelper::$username,
    	        'roles' => UserHelper::$USER_ROLES,
    	    ];
	    }
	    if($result !== null && $result !== false)
	    {
	        $response["result"] = $result;
	    }
	
	    return response()->json($response, $code)
    	    ->header('Content-Type', 'application/json')
    	    ->header('Access-Control-Allow-Origin', '*');
	}
}