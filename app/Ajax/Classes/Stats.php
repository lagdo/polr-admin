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

        $this->link = LinkHelper::getLinkByShortUrl($short_url);
        // Return 404 if link not found
        if ($this->link == null)
        {
            $this->notify->error('Cannot show stats for nonexistent link.', 'Error');
            return false;
        }

        if((session('username') != $this->link->creator) && !$this->currIsAdmin() )
        {
            $this->notify->error('You do not have permission to view stats for this link.', 'Error');
            return false;
        }

        try
        {
            // Initialize StatHelper
            $this->stats = new StatsHelper($this->link->id, $this->left_bound, $this->right_bound);
        }
        catch (\Exception $e)
        {
            $this->notify->error('Invalid date bounds. The right date bound must be more recent than the left bound.', 'Error');
            return false;
        }

        return true;
    }

    private function showLinkStatsContent()
    {
        $day_stats = $this->stats->getDayStats();
        $country_stats = $this->stats->getCountryStats();
        $referer_stats = $this->stats->getRefererStats();

        $content = view('stats.link.content', [
            'link' => $this->link,
            'referer_stats' => $referer_stats,
        ]);

        // Set the stats content
        $this->response->html('stats-content', $content);
        // Set the datepickers, the table and the graphs
        /*$this->response->script("polr.stats.initData(" . json_encode($day_stats) . "," .
            json_encode($referer_stats) . "," . json_encode($country_stats) . ",'" .
            $this->left_bound . "','" . $this->right_bound . "')");*/
        // Il faut convertir les dates en string pour ne pas envoyer des objets vers
        // le navigateur, car la fonction polr.stats.initData() attend des string.
        $this->response->call("polr.stats.initData", $day_stats, $referer_stats,
             $country_stats, (string)$this->left_bound, (string)$this->right_bound);
        $this->response->script("polr.stats.initCharts()");
    }

    public function refreshLinkStats(array $dates, $short_url)
    {
        if(!$this->checkInputs($dates, $short_url))
        {
            return $this->response;
        }

        // Set the table and the graphs
        $this->showLinkStatsContent();

        return $this->response;
    }

    public function showLinkStats($short_url)
    {
        if(!$this->checkInputs([], $short_url))
        {
            return $this->response;
        }

        // Set the stats header
        $header = view('stats.link.header', [
            'link' => $this->link,
        ]);
        $this->response->html('stats-header', $header);

        // Set the click handler on the refresh button
        $this->jq('#stats-dates .btn-refresh-stats')->click(
            $this->rq()->refreshLinkStats(rq()->form('stats-dates'), $this->link->short_url)
        );

        // Show the stats tab
        $this->jq('.admin-nav .stats a')->tab('show');

        // Set the table and the graphs
        $this->showLinkStatsContent();

        // Set the datepickers
        $this->response->script("polr.stats.initDatePickers()");

        return $this->response;
    }
}
