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
    /**
     * Fetch stats from the database
     *
     * @param Request $request          The Laravel Request object
     * @param App\Model\Link $link      The link to get stats of, or null
     *
     * @return Response
     */
    protected function fetchStats(Request $request, $link = null)
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
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid analytics type requested.', 400);
        }

        return ResponseHelper::make(($fetched_stats) ? : []);
    }

    /**
     * @api {get} /stats Get Stats
     * @apiDescription Fetch stats of a given type.
     * @apiName GetStats
     * @apiGroup Stats
     * 
     * @apiParam {String} key               The user API key.
     * @apiParam {String} type              The type of stats to fetch.
     * @apiParam {String} left_bound        The start date.
     * @apiParam {String} right_bound       The end date.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Mixed} result           The stats data.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to view stats.
     * @apiError (Error 400) {Object} AnalyticsError         An error occurs while fetching stats from the database.
     * @apiError (Error 400) {Object} MissingParameters      There is a missing or invalid parameter.
     */
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

    	return $this->fetchStats($request);
    }

    /**
     * @api {get} /links/:ending/stats Get Link Stats
     * @apiDescription Fetch stats of a given type for a single link.
     * @apiName GetLinkStats
     * @apiGroup Stats
     * 
     * @apiParam {String} key               The user API key.
     * @apiParam {String} type              The type of stats to fetch.
     * @apiParam {String} ending            The short URL id of the link.
     * @apiParam {String} left_bound        The start date.
     * @apiParam {String} right_bound       The end date.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Mixed} result           The stats data.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to view stats.
     * @apiError (Error 400) {Object} AnalyticsError         An error occurs while fetching stats from the database.
     * @apiError (Error 400) {Object} MissingParameters      There is a missing or invalid parameter.
     */
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
