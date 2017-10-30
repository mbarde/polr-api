<?php
namespace Lagdo\Polr\Api\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Helpers\LinkHelper;
use Lagdo\Polr\Api\Helpers\UserHelper;
use Lagdo\Polr\Api\Helpers\ResponseHelper;
use Lagdo\Polr\Api\Helpers\StatsHelper;

class StatsController extends Controller
{
    protected function fetchStats(Request $request, $link)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required|in:day,country,referer',
            'left_bound' => 'required|date',
            'right_bound' => 'required|date'
        ]);
        if($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $left_bound = $request->input('left_bound');
        $right_bound = $request->input('right_bound');
        try
        {
            $stats = new StatsHelper(($link) ? $link->id : -1, $left_bound, $right_bound);
        }
        catch (\Exception $e)
        {
            return ResponseHelper::make('ANALYTICS_ERROR', $e->getMessage(), 400);
        }

        $stats_type = $request->input('type');
        if($stats_type == 'day')
        {
            $fetched_stats = $stats->getDayStats();
        }
        else if($stats_type == 'country')
        {
            $fetched_stats = $stats->getCountryStats();
        }
        else if($stats_type == 'referer')
        {
            $fetched_stats = $stats->getRefererStats();
        }
        else
        {
            return ResponseHelper::make('INVALID_ANALYTICS_TYPE', 'Invalid analytics type requested.', 400);
        }

        return ResponseHelper::make(($fetched_stats) ? : []);
    }

    public function getStats(Request $request)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to view stats for all links.', 401);
        }

    	// Validate the stats type
        $validator = \Validator::make($request->all(), ['type' => 'required:in:day,country,referer']);
        if($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $link = null;
    	return $this->fetchStats($request, $link);
    }

    public function getLinkStats(Request $request, $ending)
    {
        // Validate the stats type
        $validator = \Validator::make($request->all(), ['type' => 'required:in:day,country,referer']);
        if($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }
        // Validate the link ending
        $validator = \Validator::make(['ending' => $ending], ['ending' => 'alpha_dash']);
        if($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

    	$link = LinkHelper::linkExists($ending);
        if($link === false)
        {
            return ResponseHelper::make('NOT_FOUND', 'Link not found.', 404);
        }

        if($request->user->username != $link->creator && !UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to view stats for this link.', 401);
        }

        return $this->fetchStats($request, $link);
    }
}
