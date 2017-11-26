<?php

namespace Lagdo\Polr\Admin\App;

use Carbon\Carbon;

use Jaxon\Sentry\Armada as JaxonClass;

class Stats extends JaxonClass
{
    const DAYS_TO_FETCH = 30;

    private function checkInputs(array $dates, $short_url)
    {
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

        $this->short_url = trim($short_url);
        $this->link = null;
        if($this->short_url !== '')
        {
            // Fetch the link from the Polr instance
            $apiResponse = $this->apiClient->get('links/' . $this->short_url,
                ['query' => ['key' => $this->apiKey]]);
            $jsonResponse = json_decode($apiResponse->getBody()->getContents());
            $this->link = $jsonResponse->result;
            if ($this->link == null)
            {
                $this->notify->error('Cannot show stats for nonexistent link.', 'Error');
                return false;
            }
            $this->short_url = $short_url;
        }

        return true;
    }

    private function showStatsContent()
    {
        $path = ($this->short_url === '' ? 'stats' : 'links/' . $this->short_url . '/stats');
        $parameters = [
            'key' => $this->apiKey,
            'left_bound' => (string)$this->left_bound,
            'right_bound' => (string)$this->right_bound,
        ];

        // Fetch the stats from the Polr instance
        $parameters['type'] = 'day';
        $apiResponse = $this->apiClient->get($path, ['query' => $parameters]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $day_stats = $jsonResponse->result;

        // Fetch the stats from the Polr instance
        $parameters['type'] = 'country';
        $apiResponse = $this->apiClient->get($path, ['query' => $parameters]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $country_stats = $jsonResponse->result;

        // Fetch the stats from the Polr instance
        $parameters['type'] = 'referer';
        $apiResponse = $this->apiClient->get($path, ['query' => $parameters]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $referer_stats = $jsonResponse->result;

        $clicks = 0;
        foreach($referer_stats as $stats)
        {
            $clicks += $stats->clicks;
        }
        $content = $this->view()->render('polr_admin::stats.content', [
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
            $header = $this->view()->render('polr_admin::stats.link.header', [
                'link' => $this->link,
                'endpoint' => $this->polr->endpoint(),
            ]);
            $this->response->html('stats-filter', $header);

            // Show the stats tab and clear button
            $this->jq('.admin-nav .stats a')->tab('show');
            $this->jq('#stats-buttons .clear-stats')->show();
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
