<?php

namespace Lagdo\PolrAdmin;

use Jaxon\Plugin\Package as JaxonPackage;
use Lagdo\PolrAdmin\Ajax\App\Link;
use Lagdo\PolrAdmin\Ajax\App\Stats;
use Lagdo\PolrAdmin\Ajax\App\User;

use Carbon\Carbon;

class Package extends JaxonPackage
{
    /**
     * A function to call in order to reload the dashboard
     *
     * @var Closure
     */
    protected static $reloadCallback = null;

    public function __construct(Helpers\Validator $validator)
    {
        // Set the input validator
        $this->validator = $validator;
    }

    /**
     * Render a template and remove script tags
     *
     * @param string    $template
     * @param array     $vars
     *
     * @return string
     */
    protected function _render($template, array $vars = [])
    {
        return str_replace(['<script>', '</script>'], ['', ''], $this->view()->render($template, $vars));
    }

    /**
     * Get the path to the config file
     *
     * @return string
     */
    public static function getConfigFile()
    {
        return realpath(__DIR__ . '/../config/polr-admin.php');
    }

    public function getCss()
    {
        return $this->view()->render('polr_admin::css');
    }

    public function getJs()
    {
        return $this->view()->render('polr_admin::js');
    }

    public function getScript()
    {
        return $this->_render('polr_admin::script', [
            'link' => jaxon()->request(Link::class), // Ajax request to the Jaxon Link class
            'stats' => jaxon()->request(Stats::class), // Ajax request to the Jaxon Stats class
        ]);
    }

    public function getReadyScript()
    {
        return $this->_render('polr_admin::ready', [
            'link' => jaxon()->request(Link::class), // Ajax request to the Jaxon Link class
            'datePickerLeftBound' => Carbon::now()->subDays(Stats::DAYS_TO_FETCH),
            'datePickerRightBound' => Carbon::now(),
        ]);
    }

    protected function servers()
    {
        // Get Polr servers from the config
        $config = $this->getConfig();
        $_servers = $config->getOption('servers', []);
        if(!\is_array($_servers) || \count($_servers) == 0)
        {
            return null;
        }

        // Get the current server from the configuration
        $current = $config->getOption('default', '');
        // Check if the current server value exists
        if(!$current || !\key_exists($current, $_servers))
        {
            // Set the first server in the configuration as current
            reset($_servers);
            $current = key($_servers);
        }

        $servers = [
            'current' => (object)$_servers[$current],
            'names' => [],
        ];
        if($servers['current'] != null)
        {
            $servers['current']->id = $current;
        }
        foreach($_servers as $id => $server)
        {
            $servers['names'][$id] = $server['name'];
        }
        return $servers;
    }

    public function getHtml()
    {
        $servers = $this->servers();
        if(!$servers)
        {
            return $this->view()->render('polr_admin::snippets.empty');
        }

        // Set the tabs content
        $tabs = [
            'home' => (object)[
                'view' => null,
                'title' => 'Home',
                'class' => '',
                'active' => false,
            ],
            'user-links' => (object)[
                'view' => null,
                'title' => 'User Links',
                'class' => '',
                'active' => true,
            ],
            'admin-links' => (object)[
                'view' => null,
                'title' => 'Admin Links',
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

        foreach($tabs as $id => $tab)
        {
            $tab->view = $this->view()->render('polr_admin::tabs.' . $id);
        }

        return $this->view()->render('polr_admin::default', [
            'tabs' => $tabs,
            'server' => $servers['current'],
            'servers' => $servers['names'],
        ]);
    }
}
