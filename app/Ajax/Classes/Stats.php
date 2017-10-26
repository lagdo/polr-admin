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
    use \Jaxon\Helpers\Session;

    const DAYS_TO_FETCH = 30;

    private function checkInputs(array $dates, $short_url)
    {
        if(!env('SETTING_ADV_ANALYTICS'))
        {
            $this->notify->error('Please enable advanced analytics to view link stats.', 'Error');
            return false;
        }

        $validator = \Validator::make($dates, [
            'left_bound' => 'date',
            'right_bound' => 'date'
        ]);
        if ($validator->fails())
        {
            $this->notify->error('Invalid date bounds.', 'Error');
            return false;
        }
        $user_left_bound = array_key_exists('left_bound', $dates) ? $dates['left_bound'] : '';
        $user_right_bound = array_key_exists('right_bound', $dates) ? $dates['right_bound'] : '';

        // Carbon bounds for StatHelper
        $this->left_bound = $user_left_bound ?: Carbon::now()->subDays(self::DAYS_TO_FETCH);
        $this->right_bound = $user_right_bound ?: Carbon::now();

        if(Carbon::parse($this->right_bound)->gt(Carbon::now()))
        {
            // Right bound must not be greater than current time
            // i.e cannot be in the future
            $this->notify->error('Invalid date bounds. The right date bound cannot be in the future.', 'Error');
            return false;
        }

        $link_id = 0;
        $this->link = null;
        if(($short_url))
        {
            $this->link = LinkHelper::getLinkByShortUrl($short_url);
            if ($this->link == null)
            {
                $this->notify->error('Cannot show stats for nonexistent link.', 'Error');
                return false;
            }
            $link_id = $this->link->id;
        }

        if(!$this->currIsAdmin() && (!$this->link || session('username') != $this->link->creator))
        {
            $this->notify->error('You do not have permission to view stats for this link.', 'Error');
            return false;
        }

        try
        {
            // Initialize StatHelper
            $this->stats = new StatsHelper($link_id, $this->left_bound, $this->right_bound);
        }
        catch (\Exception $e)
        {
            $this->notify->error('Invalid date bounds. The right date bound must be more recent than the left bound.', 'Error');
            return false;
        }

        return true;
    }

    private function showStatsContent()
    {
        $day_stats = $this->stats->getDayStats();
        $country_stats = $this->stats->getCountryStats();
        $referer_stats = $this->stats->getRefererStats();

        $clicks = 0;
        foreach($referer_stats as $stats)
        {
            $clicks += $stats->clicks;
        }
        $content = view('stats.content', [
            'clicks' => $clicks,
            'referer_stats' => $referer_stats,
        ]);

        // Set the stats content
        $this->response->html('stats-content', $content);
        // Set the datepickers, the table and the graphs
        // The dates must explicitely be converted to strings, or else they will be sent as JSON objects.
        // The polr.stats.initData() function takes strings as date parameters.
        $this->response->call("polr.stats.initData", $day_stats, $referer_stats,
             $country_stats, (string)$this->left_bound, (string)$this->right_bound);
        $this->response->script("polr.stats.initCharts()");
    }

    public function refreshStats(array $dates, $short_url)
    {
        if(!$this->checkInputs($dates, $short_url))
        {
            return $this->response;
        }

        // Set the table and the graphs
        $this->showStatsContent();

        return $this->response;
    }

    public function showStats($short_url)
    {
        if(!$this->checkInputs([], $short_url))
        {
            return $this->response;
        }

        // Set the stats header
        if(($this->link))
        {
            $header = view('stats.link.header', [
                'link' => $this->link,
            ]);
            $this->response->html('stats-filter', $header);

            // Show the stats tab
            $this->jq('.admin-nav .stats a')->tab('show');
        }

        // Set the click handler on the refresh button
        $this->response->script("polr.stats.short_url='$short_url'");

        // Set the table and the graphs
        $this->showStatsContent();

        // Set the datepickers
        $this->response->script("polr.stats.initDatePickers()");

        return $this->response;
    }
}
