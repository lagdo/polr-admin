<?php

namespace Lagdo\Polr\Admin;

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
    protected $endpoints = [];

    public function __construct(Jaxon $jaxon)
    {
        $this->tabs = [
            (object)[
                'id' => 'home',
                'view' => null,
                'title' => 'Home',
                'class' => '',
                'active' => true,
            ],
            (object)[
                'id' => 'settings',
                'view' => null,
                'title' => 'Settings',
                'class' => '',
                'active' => false,
            ],
            (object)[
                'id' => 'user-links',
                'view' => null,
                'title' => 'User Links',
                'class' => '',
                'active' => false,
            ],
            (object)[
                'id' => 'admin-links',
                'view' => null,
                'title' => 'Admin Links',
                'class' => '',
                'active' => false,
            ],
            (object)[
                'id' => 'users',
                'view' => null,
                'title' => 'Users',
                'class' => '',
                'active' => false,
            ],
            (object)[
                'id' => 'stats',
                'view' => null,
                'title' => 'Stats',
                'class' => 'stats',
                'active' => false,
            ],
        ];

        $this->jaxon = $jaxon;
    }

    protected function init()
    {
        // Get Polr endpoints from the config
        if(count($this->endpoints) == 0)
        {
            if(!session()->has('polr.endpoint'))
            {
                $current = config('polr.default', '');
                session()->set('polr.endpoint', $current);
            }
            else
            {
                $current = session()->get('polr.endpoint');
            }
            $this->endpoints = [
                'current' => [
                    'id' => $current,
                    'url' => config('polr.endpoints.' . $current . '.url'),
                    'name' => config('polr.endpoints.' . $current . '.name'),
                ],
                'names' => [],
            ];
            foreach(config('polr.endpoints') as $id => $endpoint)
            {
                $this->endpoints['names'][$id] = $endpoint['name'];
            }
        }
        // Set the tabs content
        if($this->tabs[0]->view == null)
        {
            foreach($this->tabs as &$tab)
            {
                $tab->view = view('polr_admin::tabs.' . $tab->id, ['endpoints' => $this->endpoints]);
            }
        }
    }

    public function tabs()
    {
        $this->init();
        return $this->tabs;
    }

    public function endpoint()
    {
        $this->init();
        if(count($this->endpoints) == 0)
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
