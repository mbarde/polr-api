<?php

namespace Lagdo\Polr\Api\Helpers;

class ResponseHelper
{
	public static function make($result = null, $message = 'OK', $code = 200)
	{
	    $response = ["message" => $message];
	    if($result !== null && $result !== false)
	    {
	        $response["result"] = $result;
	    }
	
	    return response()->json($response, $code)
    	    ->header('Content-Type', 'application/json')
    	    ->header('Access-Control-Allow-Origin', '*');
	}
}