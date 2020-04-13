<?php

namespace Lagdo\PolrAdmin;

use Jaxon\Plugin\Package as JaxonPackage;
use Lagdo\PolrAdmin\Ajax\App\Link;
use Lagdo\PolrAdmin\Ajax\App\Stats;
use Lagdo\PolrAdmin\Ajax\App\Home;

use Carbon\Carbon;

class Package extends JaxonPackage
{
    /**
     * The home page tabs
     *
     * @var array
     */
    protected $tabs = [
        'home' => [
            'title' => 'Home',
            'class' => '',
            'active' => true,
        ],
        'user-links' => [
            'title' => 'User Links',
            'class' => '',
            'active' => false,
        ],
        'admin-links' => [
            'title' => 'Admin Links',
            'class' => '',
            'active' => false,
        ],
        'stats' => [
            'title' => 'Stats',
            'class' => 'stats',
            'active' => false,
        ],
    ];

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
            'home' => jaxon()->request(Home::class), // Ajax request to the Jaxon Home class
            'datePickerLeftBound' => Carbon::now()->subDays(Stats::DAYS_TO_FETCH),
            'datePickerRightBound' => Carbon::now(),
        ]);
    }

    public function getHtml($selected = '')
    {
        // Get Polr servers from the config
        $config = $this->getConfig();
        $servers = $config->getOption('servers', []);
        if(!\is_array($servers) || \count($servers) == 0)
        {
            return $this->view()->render('polr_admin::snippets.empty');
        }

        // Get the current server from the configuration
        if($selected == '')
        {
            $selected = $config->getOption('default', '');
        }
        // Set the selected server
        if($selected == '' || !\key_exists($selected, $servers))
        {
            // Set the first server as selected
            foreach($servers as $key => &$server)
            {
                $selected = $key;
                break;
            }
        }

        return $this->view()->render('polr_admin::home', [
            'tabs' => $this->tabs,
            'servers' => $servers,
            'selected' => $selected,
        ]);
    }
}
