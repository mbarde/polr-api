<?php
namespace Lagdo\Polr\Api\Http\Controllers;

use Illuminate\Http\Request;

use App\Factories\LinkFactory;
use App\Helpers\LinkHelper;
use App\Exceptions\Api\ApiException;

class LinkController extends Controller
{
    public function getLinks(Request $request)
    {
        // Todo: Validate inputs
        $links = Link::where();
        // Todo: appy query filters
    	if($request->has('admin') && $request->input('admin') == 'true')
    	{
            self::ensureAdmin();
            $links->select(['short_url', 'long_url', 'clicks', 'created_at', 'creator', 'is_disabled']);
    	}
    	else
    	{
    		$username = $request->user->username;
            $links->where('creator', $username)
                ->select(['id', 'short_url', 'long_url', 'clicks', 'created_at']);
    	}

        return response()->json($links);
    }

	public function shortenLink(Request $request)
    {
        $user = $request->user;

        // Validate parameters
        // Encode spaces as %20 to avoid validator conflicts
        $validator = \Validator::make(array_merge([
            'url' => str_replace(' ', '%20', $request->input('url'))
        ], $request->except('url')), [
            'url' => 'required|url'
        ]);

        if ($validator->fails()) {
            throw new ApiException('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $long_url = $request->input('url'); // * required
        $is_secret = ($request->input('is_secret') == 'true' ? true : false);

        $link_ip = $request->ip();
        $custom_ending = $request->input('custom_ending');

        try {
            $formatted_link = LinkFactory::createLink($long_url, $is_secret, $custom_ending, $link_ip, $user->username, false, true);
        }
        catch (\Exception $e) {
            throw new ApiException('CREATION_ERROR', $e->getMessage(), 400);
        }

        return self::encodeResponse($formatted_link, 'shorten');
    }

    public function lookupLink(Request $request, $url_ending)
    {
        $user = $request->user;

        // Validate URL form data
        $validator = \Validator::make($request->all(), [
            'url_ending' => 'required|alpha_dash'
        ]);

        if ($validator->fails()) {
            throw new ApiException('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $link = LinkHelper::linkExists($url_ending);
        if(!$link)
        {
            throw new ApiException('NOT_FOUND', 'Link not found.', 404);
        }

        // "secret" key required for lookups on secret URLs
        $url_key = $request->input('url_key');
        if($link['secret_key'] && $url_key != $link['secret_key'])
        {
            throw new ApiException('ACCESS_DENIED', 'Invalid URL code for secret URL.', 401);
        }

        $response = ($request->has('check') ?
            ['long_url' => $link['long_url']] :
            [
                'long_url' => $link['long_url'],
                'created_at' => $link['created_at'],
                'clicks' => $link['clicks'],
                'updated_at' => $link['updated_at'],
                'created_at' => $link['created_at']
            ]
        );
        return self::encodeResponse($response);
    }

    public function updateLink(Request $request, $url_ending)
    {
    	// Todo: check the input parameters validity
    	// - At least one of the "long_url" and "status" are present
    	// - The "long_url" parameter is a valid URL
    	// - Accepted values for status are 0 (disabled), 1 (enabled), and 2 (toggle).
        /**
         * If user is an admin, allow the user to edit the value of any link's long URL.
         * Otherwise, only allow the user to edit their own links.
         */
        $link = LinkHelper::linkExists($link_ending);
        if (!$link) {
            throw new ApiException('NOT_FOUND', 'Link not found.', 404);
        }

        if($request->has('long_url'))
        {
        	$new_long_url = $request->input('long_url');
            $this->validate($request, [
                'long_url' => 'required|url',
            ]);
            if ($link->creator !== $request->user->username) {
                self::ensureAdmin();
            }
            $link->long_url = $new_long_url;
        }

        if($request->has('status'))
        {
        	$new_status = $request->input('status');
        	if($new_status == 2)
        	{
                // if currently disabled, then enable
        		$new_status = ($link->is_disabled ? 1 : 0);
        	}
        	/*else if($new_status == 0 || $new_status == 1)
        	{
        		// Save the new status as is
        	}*/
            $link->is_disabled = $new_status;
        }

        $link->save();
        return "OK";
    }

    public function deleteLink(Request $request, $url_ending)
    {
        self::ensureAdmin();

        $link = LinkHelper::linkExists($link_ending);

        if (!$link) {
            throw new ApiException('NOT_FOUND', 'Link not found.', 404);
        }

        $link->delete();
        return "OK";
    }
}
