<?php

namespace Lagdo\Polr\Admin;

use Carbon\Carbon;
use GuzzleHttp\Client as HttpClient;

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
     * The Polr config options, read from config file
     *
     * @var Jaxon\Utils\Config\Config
     */
    protected $config;

    /**
     * A function to call in order to reload the dashboard
     *
     * @var Closure
     */
    protected static $reloadCallback = null;

    public function __construct($dtRenderer)
    {
        // Set the class initializer
        $this->apiKey = null;
        $this->apiClient = null;
        // Set the Datatables renderer
        $this->dtRenderer = $dtRenderer;
        // Set the input validator
        $this->validator = new Helpers\Validator();
    }

    protected function init()
    {
        // Polr API Client
        if($this->apiClient == null)
        {
            $armada = jaxon()->armada();
            // Get Polr endpoints from the config
            if(!($current = $armada->session()->get('polr.endpoint')))
            {
                // $current = config('polradmin.default', '');
                $current = $this->config->getOption('default', '');
                $armada->session()->set('polr.endpoint', $current);
            }
            $cfgKey = 'endpoints.' . $current;
            $this->apiKey = $this->config->getOption($cfgKey . '.key');
            $uri = rtrim($this->config->getOption($cfgKey . '.url'), '/') . '/' .
                trim($this->config->getOption($cfgKey . '.api'), '/') . '/';
            $this->apiClient = new HttpClient(['base_uri' => $uri]);
        }

        if($this->tabs == null)
        {
            $jaxon = jaxon();
            // Get Polr endpoints from the config
            $armada = $jaxon->armada();
            if(!($current = $armada->session()->get('polr.endpoint')))
            {
                $current = $this->config->getOption('default', '');
                $armada->session()->set('polr.endpoint', $current);
            }
            $this->endpoints = [
                'current' => (object)$this->config->getOption('endpoints.' . $current, null),
                'names' => [],
            ];
            if($this->endpoints['current'] != null)
            {
                $this->endpoints['current']->id = $current;
            }
            foreach($this->config->getOption('endpoints') as $id => $endpoint)
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
                $tab->view = $armada->view()->render('polr_admin::tabs.' . $id, [
                    'endpoint' => $this->endpoints['current'],
                    'endpoints' => $this->endpoints['names'],
                ]);
            }
        }
    }

    public function config($sConfigFile)
    {
        // Read the config file
        $this->config = jaxon()->readConfigFile($sConfigFile, 'lib', '');
        $this->init();
    }

    public function tabs()
    {
        return $this->tabs;
    }

    public function endpoint()
    {
        if(count($this->endpoints) == 0)
        {
            return null;
        }
        return $this->endpoints['current'];
    }

    public function css()
    {
        $armada = jaxon()->armada();
        $template = $this->config->getOption('templates.css', 'polr_admin::css');
        return $armada->view()->render($template);
    }

    public function js()
    {
        $armada = jaxon()->armada();
        $template = $this->config->getOption('templates.js', 'polr_admin::js');
        $js = $armada->view()->render($template);
        return $armada->view()->render('polr_admin::snippets.js', [
            'js' => $js,
            'user' => $armada->request(User::class), // Ajax request to the Jaxon User class
            'link' => $armada->request(Link::class), // Ajax request to the Jaxon Link class
            'stats' => $armada->request(Stats::class), // Ajax request to the Jaxon Stats class
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
        $armada = jaxon()->armada();
        $template = $this->config->getOption('templates.html', 'polr_admin::default');
        return $armada->view()->render($template)->with('tabs', $this->tabs());
    }

    public function initInstance($instance)
    {
        // Save the HTTP REST client
        $instance->apiKey = $this->apiKey;
        $instance->apiClient = $this->apiClient;

        // Dialogs and notifications are implemented by the Dialogs plugin
        $sentry = jaxon()->sentry();
        $response = $sentry->ajaxResponse();
        $instance->dialog = $response->dialog;
        $instance->notify = $response->dialog;

        // The client IP address
        $instance->remoteAddress = $_SERVER['REMOTE_ADDR'];

        // Save the Datatables renderer in the class instance
        $instance->dtRenderer = $this->dtRenderer;

        // Polr plugin instance
        $instance->polr = $this;

        // The input validator
        $instance->validator = $this->validator;
    }

    public function setReloadCallback(\Closure $callback)
    {
        self::$reloadCallback = $callback;
    }

    public function onReload(\Closure $callback)
    {
        self::$reloadCallback = $callback;
    }
    
    public function reload(Response $response)
    {
        if(self::$reloadCallback == null)
        {
            $url = \Jaxon\Utils\URI::detect(); // Reload the page by redirecting to the current URL
            $response->redirect($url);
        }
        else
        {
            $callback = self::$reloadCallback; // Custom callback set by the user
            $callback($response);
        }
    }
}
