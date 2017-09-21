<?php

namespace Jaxon\App;

use Carbon\Carbon;

use App\Models\Clicks;
use App\Helpers\LinkHelper;
use App\Helpers\StatsHelper;
use Illuminate\Support\Facades\DB;

use Jaxon\Sentry\Armada as JaxonClass;

class Stats extends JaxonClass
{
    const DAYS_TO_FETCH = 30;

    protected function currIsAdmin()
    {
        $role = session('role');
        return ($role == 'admin');
    }

    public function showLinkStats(array $dates, $short_url)
    {
        if(!env('SETTING_ADV_ANALYTICS'))
        {
            $this->notify->error('Please enable advanced analytics to view link stats.', 'Error');
            return $this->response;
        }

        $validator = \Validator::make($dates, [
            'left_bound' => 'date',
            'right_bound' => 'date'
        ]);

        if ($validator->fails())
        {
            $this->notify->error('Invalid date bounds.', 'Error');
            return $this->response;
        }

        $user_left_bound = array_key_exists('left_bound', $dates) ? $dates['left_bound'] : '';
        $user_right_bound = array_key_exists('right_bound', $dates) ? $dates['right_bound'] : '';

        // Carbon bounds for StatHelper
        $left_bound = $user_left_bound ?: Carbon::now()->subDays(self::DAYS_TO_FETCH);
        $right_bound = $user_right_bound ?: Carbon::now();

        if(Carbon::parse($right_bound)->gt(Carbon::now()))
        {
            // Right bound must not be greater than current time
            // i.e cannot be in the future
            $this->notify->error('Invalid date bounds. The right date bound cannot be in the future.', 'Error');
            return $this->response;
        }

        $link = LinkHelper::getLinkByShortUrl($short_url);
        // Return 404 if link not found
        if ($link == null)
        {
            $this->notify->error('Cannot show stats for nonexistent link.', 'Error');
            return $this->response;
        }
        $link_id = $link->id;

        if((session('username') != $link->creator) && !self::currIsAdmin() )
        {
            $this->notify->error('You do not have permission to view stats for this link.', 'Error');
            return $this->response;
        }

        try
        {
            // Initialize StatHelper
            $stats = new StatsHelper($link_id, $left_bound, $right_bound);
        }
        catch (\Exception $e)
        {
            $this->notify->error('Invalid date bounds. The right date bound must be more recent than the left bound.', 'Error');
            return $this->response;
        }

        $day_stats = $stats->getDayStats();
        $country_stats = $stats->getCountryStats();
        $referer_stats = $stats->getRefererStats();

        $html = view('stats.link', [
            'link' => $link,
            'referer_stats' => $referer_stats,
        ]);

        // Show the stats tab
        $this->jq('.admin-nav .stats a')->tab('show');
        // Set the stats content
        $this->response->html('stats', $html);
        $this->response->script("dayData = JSON.parse('" . json_encode($day_stats) . "')");
        $this->response->script("refererData = JSON.parse('" . json_encode($referer_stats) . "')");
        $this->response->script("countryData = JSON.parse('" . json_encode($country_stats) . "')");
        $this->response->script("datePickerLeftBound = '" . $left_bound . "'");
        $this->response->script("datePickerRightBound = '" . $right_bound . "'");
        $this->response->script("polr.stats.init()");
        // Set the click handler on the refresh button
        $this->jq('#stats-dates .btn-refresh-stats')->click(
            $this->rq()->showLinkStats(rq()->form('stats-dates'), $link->short_url)
        );

        return $this->response;
    }
}
