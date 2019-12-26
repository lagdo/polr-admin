<?php

namespace Lagdo\PolrAdmin\Ajax\App;

use Lagdo\PolrAdmin\Client;
use Lagdo\PolrAdmin\Helpers\Validator;

use Carbon\Carbon;
use Jaxon\CallableClass;

class Stats extends CallableClass
{
    const DAYS_TO_FETCH = 30;

    public function __construct(Client $client, Validator $validator)
    {
        $this->client = $client;
        $this->validator = $validator;
    }

    private function checkInputs($server, $ending, array $dates = [])
    {
        if(!$this->validator->validateStatsDate($dates))
        {
            $this->response->dialog->error('Invalid date bounds.', 'Error');
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
            $this->response->dialog->error('Invalid date bounds. The right date bound cannot be in the future.', 'Error');
            return false;
        }

        if($ending !== '')
        {
            // Fetch the link from the Polr instance
            if($this->client->checkAvailability($server, $ending))
            {
                $this->response->dialog->error('Cannot show stats for nonexistent link.', 'Error');
                return false;
            }
        }

        return true;
    }

    private function showStatsContent($server, $ending)
    {
        $stats = $this->client->getStats($server, $ending, $this->left_bound, $this->right_bound);
        $clicks = 0;
        foreach($stats['referer'] as $refererStats)
        {
            $clicks += $refererStats->clicks;
        }
        $content = $this->view()->render('polr_admin::stats.content', [
            'clicks' => $clicks,
            'referer_stats' => $stats['referer'],
        ]);

        // Set the stats content
        $this->response->html('stats-content', $content);
        // Set the datepickers, the table and the graphs
        // The dates must explicitely be converted to strings, or else they will be sent as JSON objects.
        // The polr.stats.initData() function takes strings as date parameters.
        $this->response->call("polr.stats.initData", $stats['day'], $stats['referer'],
             $stats['country'], (string)$this->left_bound, (string)$this->right_bound);
        $this->response->script("polr.stats.initCharts()");
    }

    public function refreshStats($server, $ending, array $dates)
    {
        $server = trim($server);
        $ending = trim($ending);
        if(!$this->checkInputs($server, $ending, $dates))
        {
            return $this->response;
        }

        // Set the table and the graphs
        $this->showStatsContent($server, $ending);

        return $this->response;
    }

    public function showStats($server, $ending)
    {
        $server = trim($server);
        $ending = trim($ending);
        if(!$this->checkInputs($server, $ending))
        {
            return $this->response;
        }

        // Set the stats header
        if(($ending))
        {
            $header = $this->view()->render('polr_admin::stats.link.header', [
                'link' => $this->client->getShortUrl($server, $ending),
                'server' => $this->client->getServer($server),
            ]);
            $this->response->html('stats-filter', $header);

            // Show the stats tab and clear button
            $this->jq('.admin-nav .stats a')->tab('show');
            $this->jq('#stats-buttons .clear-stats')->show();
        }

        // Set the click handler on the refresh button
        $this->response->script("polr.stats.ending='$ending'");

        // Set the table and the graphs
        $this->showStatsContent($server, $ending);

        // Set the datepickers
        $this->response->script("polr.stats.initDatePickers()");

        return $this->response;
    }
}
