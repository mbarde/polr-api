<?php
namespace Lagdo\Polr\Api\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Factories\LinkFactory;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Lagdo\Polr\Api\Helpers\UserHelper;
use Lagdo\Polr\Api\Helpers\ResponseHelper;

use Yajra\Datatables\Facades\Datatables;

class LinkController extends Controller
{
    public function getAdminLinks(Request $request)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to get links.', 401);
        }

    	$links = Link::select(['id', 'short_url', 'long_url', 'clicks', 'created_at', 'creator', 'is_disabled']);
        $datatables = Datatables::of($links)->make(true);

        return ResponseHelper::make(json_decode($datatables->content()));
    }

    public function getUserLinks(Request $request)
    {
        if(UserHelper::userIsAnonymous($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to get links.', 401);
        }

        $username = $request->user->username;
        $links = Link::where('creator', $username)
            ->select(['id', 'short_url', 'long_url', 'clicks', 'created_at']);

        $datatables = Datatables::of($links)->make(true);

        return ResponseHelper::make(json_decode($datatables->content()));
    }

    public function shortenLink(Request $request)
    {
        // Validate parameters
        // Encode spaces as %20 to avoid validator conflicts
        $validator = \Validator::make(array_merge([
            'url' => str_replace(' ', '%20', $request->input('url'))
        ], $request->except('url')), [
            'url' => 'required|url'
        ]);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $long_url = $request->input('url'); // * required
        $is_secret = ($request->input('is_secret') == 'true' ? true : false);
        $link_ip = $request->ip();
        $custom_ending = $request->input('custom_ending');
        try
        {
            $formatted_link = LinkFactory::createLink($long_url, $is_secret,
                $custom_ending, $link_ip, $request->user->username, false, true);
        }
        catch (\Exception $e)
        {
            return ResponseHelper::make('CREATION_ERROR', $e->getMessage(), 400);
        }

        return ResponseHelper::make($formatted_link);
    }

    public function lookupLink(Request $request, $ending)
    {
        // Validate URL form data
        $validator = \Validator::make(['ending' => $ending], ['ending' => 'required|alpha_dash']);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $link = LinkHelper::linkExists($ending);
        if(!$link)
        {
            return ResponseHelper::make('NOT_FOUND', 'Link not found.', 404);
        }

        // "secret" key required for lookups on secret URLs
        $url_key = $request->input('url_key');
        if($link['secret_key'] && $url_key != $link['secret_key'])
        {
            return ResponseHelper::make('ACCESS_DENIED', 'Invalid URL code for secret URL.', 401);
        }

        $response = null;
        if(!$request->has('check'))
        {
            $response = [
                'short_url' => $ending,
                'long_url' => $link['long_url'],
                'created_at' => $link['created_at'],
                'clicks' => $link['clicks'],
                'updated_at' => $link['updated_at'],
                'created_at' => $link['created_at']
            ];
        }
        return ResponseHelper::make($response);
    }

    public function updateLink(Request $request, $ending)
    {
    	// At least one of the link properties must be present in the input data
        $request->merge(['ending' => $ending]);
        $validator = \Validator::make($request->all(), [
            'ending' => 'required|alpha_dash',
        	'url' => 'required_without_all:status|url',
            'status' => 'required_without_all:url|in:enable,disable,toggle',
        ]);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        /**
         * If user is an admin, allow the user to edit the value of any link's long URL.
         * Otherwise, only allow the user to edit their own links.
         */
        $link = LinkHelper::linkExists($ending);
        if (!$link)
        {
            return ResponseHelper::make('NOT_FOUND', 'Link not found.', 404);
        }

        if($request->user->username != $link->creator && !UserHelper::userIsAdmin($user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to edit this link.', 401);
        }

        if($request->has('url'))
        {
            $link->long_url = $request->input('url');
        }

        if($request->has('status'))
        {
            $status = $request->input('status');
            switch($status)
            {
            case 'enable':
                // if currently disabled, then enable
                $link->is_disabled = 0;
                break;
            case 'disable':
                // if currently disabled, then enable
                $link->is_disabled = 1;
                break;
            case 'toggle':
            default:
                // if currently disabled, then enable
                $link->is_disabled = ($link->is_disabled ? 1 : 0);
                break;
            }
        }

        $link->save();

        return ResponseHelper::make();
    }

    public function deleteLink(Request $request, $ending)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to delete links.', 401);
        }

        $link = LinkHelper::linkExists($ending);
        if(!$link)
        {
            return ResponseHelper::make('NOT_FOUND', 'Link not found.', 404);
        }

        $link->delete();

        return ResponseHelper::make();
    }
}
