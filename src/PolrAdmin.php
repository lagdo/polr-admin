<?php

namespace Lagdo\Polr\Admin;

use Carbon\Carbon;
use GuzzleHttp\Client as RestClient;
use Datatables;

use Jaxon\Response\Response;
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

    /**
     * A function to call in order to reload the dashboard
     *
     * @var Closure
     */
    protected static $reloadCallback = null;

    public function __construct(Jaxon $jaxon)
    {
        $this->jaxon = $jaxon;
        // Set the class initializer
        $this->apiKey = null;
        $this->apiClient = null;
    }

    protected function init()
    {
        if($this->tabs == null)
        {
            // Get Polr endpoints from the config
            if(!($current = session()->get('polr.endpoint')))
            {
                $current = config('polradmin.default', '');
                session()->set('polr.endpoint', $current);
            }
            $this->endpoints = [
                'current' => (object)config('polradmin.endpoints.' . $current, null),
                'names' => [],
            ];
            if($this->endpoints['current'] != null)
            {
                $this->endpoints['current']->id = $current;
            }
            foreach(config('polradmin.endpoints') as $id => $endpoint)
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
        $template = config('polradmin.templates.css', 'polr_admin::css');
        return view($template);
    }

    public function js()
    {
        $template = config('polradmin.templates.js', 'polr_admin::js');
        $js = view($template);
        return view('polr_admin::snippets.js', [
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
        return 'polr.home.init();polr.stats.initDatePickers();polr.home.setHandlers();';
    }

    public function html()
    {
        $template = config('polradmin.templates.html', 'polr_admin::default');
        return view($template)->with('tabs', $this->tabs());
    }

    public function initInstance($instance)
    {
        // Polr API Client
        if($this->apiClient == null)
        {
            // Get Polr endpoints from the config
            if(!($current = session()->get('polr.endpoint')))
            {
                $current = config('polradmin.default', '');
                session()->set('polr.endpoint', $current);
            }
            $cfgKey = 'polradmin.endpoints.' . $current;
            $this->apiKey = config($cfgKey . '.key');
            $uri = rtrim(config($cfgKey . '.url'), '/') . '/' .
                trim(config($cfgKey . '.api'), '/') . '/';
            $this->apiClient = new RestClient(['base_uri' => $uri]);
        }

        // Save the HTTP REST client
        $instance->apiKey = $this->apiKey;
        $instance->apiClient = $this->apiClient;

        // Dialogs and notifications are implemented by the Dialogs plugin
        $sentry = jaxon()->sentry();
        $response = $sentry->ajaxResponse();
        $instance->dialog = $response->dialog;
        $instance->notify = $response->dialog;

        // The HTTP Request
        $instance->httpRequest = app()->make('request');
        
        // Save the Datatables renderer and request in the class instance
        $instance->dtRequest = Datatables::getRequest();
        $instance->dtRenderer = app()->make('jaxon.dt.renderer');

        // Polr plugin instance
        $instance->polr = $this;
    }

    public function setReloadCallback(\Closure $callback)
    {
        self::$reloadCallback = $callback;
    }

    public function reload(Response $response)
    {
        if(self::$reloadCallback == null)
        {
            $url = url(); // Reload the page by redirecting to the current URL
            $response->redirect($url);
        }
        else
        {
            $callback = self::$reloadCallback; // Custom callback set by the user
            $callback($response);
        }
    }
}
