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
    /**
     * @api {get} /links Get Admin Links
     * @apiDescription Fetch a paginated list of links. The input parameters are those of the Datatables library.
     * @apiName GetAdminLinks
     * @apiGroup Links
     *
     * @apiParam {Integer} [draw]           The draw option.
     * @apiParam {Object} [columns]         The table columns.
     * @apiParam {Object} [order]           The data ordering.
     * @apiParam {Integer} [start]          The data offset.
     * @apiParam {Integer} [length]         The data count.
     * @apiParam {Object} [search]          The search options.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Object} result          The link list.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to list links.
     */
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

    /**
     * @api {get} /users/me/links Get User Links
     * @apiDescription Fetch a paginated list of links. The input parameters are those of the Datatables library.
     * @apiName GetUserLinks
     * @apiGroup Links
     *
     * @apiParam {Integer} [draw]           The draw option.
     * @apiParam {Object} [columns]         The table columns.
     * @apiParam {Object} [order]           The data ordering.
     * @apiParam {Integer} [start]          The data offset.
     * @apiParam {Integer} [length]         The data count.
     * @apiParam {Object} [search]          The search options.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Mixed} result           The link list.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to list links.
     */
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

    /**
     * @api {post} /links Shorten a link
     * @apiDescription Create a shortened URL for a given link
     * @apiName ShortenLink
     * @apiGroup Links
     *
     * @apiParam {String} key               The user API key.
     * @apiParam {String} url               The link to shorten.
     * @apiParam {String} [ending]          A custom ending for the link.
     * @apiParam {String} [secret]          Create a secret link or not.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Mixed} result           The shortened URL.
     *
     * @apiError (Error 400) {Object} CreationError          An error occurs while shortening the link.
     * @apiError (Error 400) {Object} MissingParameters      There is a missing or invalid parameter.
     */
    public function shortenLink(Request $request)
    {
        // Validate parameters
        // Encode spaces as %20 to avoid validator conflicts
        $validator = \Validator::make(array_merge([
            'url' => str_replace(' ', '%20', $request->input('url'))
        ], $request->except('url')), [
            'url' => 'required|url',
        ]);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }
        $validator = \Validator::make($request->all(), [
            'ending' => 'alpha_dash',
            'secret' => 'in:true,false',
        ]);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $long_url = $request->input('url'); // * required
        $is_secret = ($request->input('secret') == 'true' ? true : false);
        $link_ip = $request->ip();
        $custom_ending = $request->input('ending');
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

    /**
     * @api {post} /links/batch Shorten a list of links
     * @apiDescription Create a shortened URL for a given list of links
     * @apiName ShortenLinks
     * @apiGroup Links
     *
     * @apiParam {String} key               The user API key.
     * @apiParam {String} urls              The list of links to shorten.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Mixed} result           The shortened URLs.
     */
    public function shortenLinks(Request $request)
    {
        $user = $request->user;
        $link_ip = $request->ip();
        $username = $user->username;

        $formatted_links = [];
        $links_array = json_decode($request->input('urls'));

        foreach($links_array as $link)
        {
            $validator = \Validator::make($link, [
                'url' => 'required|url'
            ]);
            if($validator->fails())
            {
                continue;
            }

            $is_secret = array_get($link, 'is_secret') == 'true' ? true : false;
            $custom_ending = array_get($link, 'custom_ending', null);
            $formatted_link = LinkFactory::createLink($link['url'], $is_secret,
                $custom_ending, $link_ip, $username, false, true);
            $formatted_link = [
                'long_url' => $link['url'],
                'short_url' => $formatted_link
            ];
            // Extra data added by the caller to entries
            if(key_exists('ref', $link))
            {
                $formatted_link['ref'] = $link['ref'];
            }

            $formatted_links[] = $formatted_link;
        }

        return ResponseHelper::make($formatted_links);
    }

    /**
     * @api {get} /links/:ending Lookup Link
     * @apiDescription Returns
     * @apiName LookupLink
     * @apiGroup Links
     *
     * @apiParam {String} key               The user API key.
     * @apiParam {String} [secret]          The link secret.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Object} result          The link data.
     *
     * @apiError (Error 404) {Object} NotFound               Unable to find a link with the given ending.
     * @apiError (Error 401) {Object} AccessDenied           Invalid URL code given for a secret URL.
     * @apiError (Error 400) {Object} MissingParameters      There is a missing or invalid parameter.
     */
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
        $secret = $request->input('secret');
        if($link['secret_key'] && $secret != $link['secret_key'])
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

    /**
     * @api {put} /links/:ending Update a link
     * @apiDescription Update the link with the given ending.
     * @apiName UpdateLink
     * @apiGroup Links
     *
     * @apiParam {String} key               The user API key.
     * @apiParam {String} [url]             The new URL.
     * @apiParam {String} [status]          The status change: enable, disable or toggle.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to edit the link.
     * @apiError (Error 404) {Object} NotFound               Unable to find a link with the given ending.
     * @apiError (Error 400) {Object} MissingParameters      There is a missing or invalid parameter.
     */
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
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to edit the link.', 401);
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
                $link->is_disabled = 0;
                break;
            case 'disable':
                $link->is_disabled = 1;
                break;
            case 'toggle':
            default:
                $link->is_disabled = ($link->is_disabled ? 1 : 0);
                break;
            }
        }

        $link->save();

        return ResponseHelper::make();
    }

    /**
     * @api {delete} /links/:ending Delete a link
     * @apiDescription Delete the link with the given ending.
     * @apiName DeleteLink
     * @apiGroup Links
     *
     * @apiParam {String} key               The user API key.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to delete links.
     * @apiError (Error 404) {Object} NotFound               Unable to find a link with the given ending.
     */
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
