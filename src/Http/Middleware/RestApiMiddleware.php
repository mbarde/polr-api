<?php

namespace Lagdo\Polr\Api\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\ApiHelper;
use App\Exceptions\Api\ApiException;
use Lagdo\Polr\Api\Helpers\ResponseHelper;
use Lagdo\Polr\Api\Helpers\UserHelper;

class RestApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        $api_key = $request->input('key');
        if(!$api_key)
        {
            // no API key provided; check whether anonymous API is enabled
            if(env('SETTING_ANON_API'))
            {
                $username = 'ANONIP-' . $request->ip();
            }
            else
            {
                return ResponseHelper::make('AUTH_ERROR', 'Authentication token required.', 401);
            }
            $user = (object) ['username' => $username, 'anonymous' => true];
        }
        else
        {
            $user = User::where('active', 1)->where('api_key', $api_key)->where('api_active', 1)->first();
            if(!$user)
            {
                return ResponseHelper::make('AUTH_ERROR', 'Authentication token invalid.', 401);
            }
            $username = $user->username;
            $user->anonymous = false;
        }

        $api_limit_reached = ApiHelper::checkUserApiQuota($username);
        if($api_limit_reached)
        {
            return ResponseHelper::make('QUOTA_EXCEEDED', 'Quota exceeded.', 429);
        }
        $request->user = $user;
        UserHelper::$username = $user->username;

        return $next($request);
    }
}
