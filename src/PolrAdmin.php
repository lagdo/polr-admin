<?php

namespace Lagdo\Polr\Admin;

use Illuminate\Contracts\Auth\Guard;
use Carbon\Carbon;

use Jaxon\Laravel\Jaxon;

use Lagdo\Polr\Admin\App\Link;
use Lagdo\Polr\Admin\App\Stats;
use Lagdo\Polr\Admin\App\User;

class PolrAdmin
{
    /**
     * The Polr endpoints, read from the config
     *
     * @var array
     */
    protected $endpoints;

    public function __construct(Jaxon $jaxon, Guard $auth)
    {
        $this->tabs = [
            (object)[
                'id' => 'home',
                'view' => null,
                'title' => 'Home',
                'active' => true,
            ],
            (object)[
                'id' => 'settings',
                'title' => 'Settings',
                'active' => false,
            ],
            (object)[
                'id' => 'user-links',
                'title' => 'User Links',
                'active' => false,
            ],
            (object)[
                'id' => 'admin-links',
                'title' => 'Admin Links',
                'active' => false,
            ],
            /*(object)[
                'id' => 'users',
                'title' => 'User',
                'active' => false,
            ],*/
            (object)[
                'id' => 'stats',
                'title' => 'Stats',
                'active' => false,
            ],
        ];

        $this->jaxon = $jaxon;

        $this->auth = $auth;
    }

    public function tabs()
    {
        $this->init();
        return $this->tabs;
    }

    protected function init()
    {
        if($this->tabs[0]->view == null)
        {
            // Get the Polr endpoints from the config
            if($this->auth->check())
            {
                // Get Polr endpoints from config
                if(!session()->has('polr.endpoint'))
                {
                    $current = config('polr.default', '');
                    session()->set('polr.endpoint', $current);
                }
                else
                {
                    $current = session()->get('polr.endpoint');
                }
                $endpoints = [
                    'current' => [
                        'id' => $current,
                        'url' => config('polr.endpoints.' . $current . '.url'),
                        'name' => config('polr.endpoints.' . $current . '.name'),
                    ],
                    'names' => [],
                ];
                foreach(config('polr.endpoints') as $id => $endpoint)
                {
                    $endpoints['names'][$id] = $endpoint['name'];
                }
                foreach($this->tabs as &$tab)
                {
                    $tab->view = view('polr_admin::tabs.' . $tab->id, ['endpoints' => $endpoints]);
                }
            }
            else
            {
                $endpoints = null;
            }
        }
    }

    public function endpoint($endpoint = null)
    {
        $this->init();
        if(!is_array($this->endpoints))
        {
            return '';
        }
        return $this->endpoints['current']['name'];
    }

    public function css()
    {
        return view('polr_admin::css');
    }

    public function js()
    {
        return view('polr_admin::js', [
            'jaxonUser' => $this->jaxon->request(User::class), // Ajax request to the Jaxon User class
            'jaxonLink' => $this->jaxon->request(Link::class), // Ajax request to the Jaxon Link class
            'jaxonStats' => $this->jaxon->request(Stats::class), // Ajax request to the Jaxon Stats class
            'datePickerLeftBound' => Carbon::now()->subDays(Stats::DAYS_TO_FETCH),
            'datePickerRightBound' => Carbon::now(),
        ]);
    }
}
