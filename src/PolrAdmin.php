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
     * The Polr page tabs
     *
     * @var array
     */
    protected $tabs = null;

    /**
     * The Polr endpoints, read from the config
     *
     * @var array
     */
    protected $endpoints = [];

    public function __construct(Jaxon $jaxon)
    {
        $this->jaxon = $jaxon;
    }

    protected function init()
    {
        if($this->tabs == null)
        {
            // Get Polr endpoints from the config
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
                'current' => (object)config('polr.endpoints.' . $current, null),
                'names' => [],
            ];
            if($this->endpoints['current'] != null)
            {
                $this->endpoints['current']->id = $current;
            }
            foreach(config('polr.endpoints') as $id => $endpoint)
            {
                $this->endpoints['names'][$id] = $endpoint['name'];
            }

            // Set the tabs content
            $this->tabs = [
                'home' => (object)[
                    'view' => null,
                    'title' => 'Home',
                    'class' => '',
                    'active' => true,
                ],
                'settings' => (object)[
                    'view' => null,
                    'title' => 'Settings',
                    'class' => '',
                    'active' => false,
                ],
                'user-links' => (object)[
                    'view' => null,
                    'title' => 'User Links',
                    'class' => '',
                    'active' => false,
                ],
                'admin-links' => (object)[
                    'view' => null,
                    'title' => 'Admin Links',
                    'class' => '',
                    'active' => false,
                ],
                'users' => (object)[
                    'view' => null,
                    'title' => 'Users',
                    'class' => '',
                    'active' => false,
                ],
                'stats' => (object)[
                    'view' => null,
                    'title' => 'Stats',
                    'class' => 'stats',
                    'active' => false,
                ],
            ];

            foreach($this->tabs as $id => &$tab)
            {
                $tab->view = view('polr_admin::tabs.' . $id, [
                    'endpoint' => $this->endpoints['current'],
                    'endpoints' => $this->endpoints['names'],
                ]);
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
            return null;
        }
        return $this->endpoints['current'];
    }

    public function css()
    {
        $template = config('polr.templates.css', 'polr_admin::css');
        return view($template);
    }

    public function js()
    {
        $template = config('polr.templates.js', 'polr_admin::js');
        $js = view($template);
        return view('polr_admin::code', [
            'js' => $js,
            'user' => $this->jaxon->request(User::class), // Ajax request to the Jaxon User class
            'link' => $this->jaxon->request(Link::class), // Ajax request to the Jaxon Link class
            'stats' => $this->jaxon->request(Stats::class), // Ajax request to the Jaxon Stats class
            'datePickerLeftBound' => Carbon::now()->subDays(Stats::DAYS_TO_FETCH),
            'datePickerRightBound' => Carbon::now(),
        ]);
    }

    public function ready()
    {
        return 'polr.home.setHandlers();polr.home.init();polr.stats.initDatePickers();';
    }

    public function html()
    {
        $template = config('polr.templates.html', 'polr_admin::default');
        return view($template)->with('tabs', $this->tabs());
    }
}
