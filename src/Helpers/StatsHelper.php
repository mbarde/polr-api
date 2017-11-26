<?php
namespace Lagdo\Polr\Api\Helpers;

use App\Models\Click;
use App\Models\Link;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsHelper
{
    /**
     * The constructor
     *
     * @param unknown $link_id          The link id
     * @param unknown $left_bound       The start date
     * @param unknown $right_bound      The end date
     *
     * @throws \Exception
     */
    public function __construct($link_id, $left_bound, $right_bound)
    {
        $this->link_id = $link_id;
        $this->left_bound_parsed = Carbon::parse($left_bound);
        $this->right_bound_parsed = Carbon::parse($right_bound);

        if (!$this->left_bound_parsed->lte($this->right_bound_parsed))
        {
            // If left bound is not less than or equal to right bound
            throw new \Exception('Invalid bounds.');
        }

        $days_diff = $this->left_bound_parsed->diffInDays($this->right_bound_parsed);
        $max_days_diff = env('_ANALYTICS_MAX_DAYS_DIFF') ?: 365;

        if ($days_diff > $max_days_diff)
        {
            throw new \Exception('Bounds too broad.');
        }
    }

    /**
     * Fetches base rows given left date bound, right date bound, and link id
     *
     * @return DB rows
     */
    public function getBaseRows()
    {
        $rows = DB::table('clicks')
            // ->where('link_id', $this->link_id)
            ->where('created_at', '>=', $this->left_bound_parsed)
            ->where('created_at', '<=', $this->right_bound_parsed);
        // Filter on link id only if it is greater than or equals to 0
        if($this->link_id >= 0)
        {
            $rows->where('link_id', $this->link_id);
        }
        return $rows;
    }

    /**
     * Fetches day stats
     *
     * @return DB rows
     */
    public function getDayStats()
    {
        // Return stats by day from the last 30 days
        // date => x
        // clicks => y

        // Run a different SQL query depending on database driver
        $db_driver = DB::connection()->getDriverName();
        if ($db_driver == 'pgsql') {
            $created_at = "to_char(created_at, 'yyyy-mm-dd')";
        }
        else {
            $created_at = "DATE_FORMAT(created_at, '%Y-%m-%d')";
        }

        $stats = $this->getBaseRows()
            ->select(DB::raw("$created_at AS x, count(*) AS y"))
            ->groupBy(DB::raw($created_at))
            ->orderBy('x', 'asc')
            ->get();

        return $stats;
    }

    /**
     * Fetches country stats
     *
     * @return DB rows
     */
    public function getCountryStats()
    {
        $stats = $this->getBaseRows()
            ->select(DB::raw("country AS label, count(*) AS clicks"))
            ->groupBy('country')
            ->orderBy('clicks', 'desc')
            ->get();

        return $stats;
    }

    /**
     * Fetches referer stats
     *
     * @return DB rows
     */
    public function getRefererStats()
    {
        $stats = $this->getBaseRows()
            ->select(DB::raw("COALESCE(referer_host, 'Direct') as label, count(*) as clicks"))
            ->groupBy('referer_host')
            ->orderBy('clicks', 'desc')
            ->get();

        return $stats;
    }
}
