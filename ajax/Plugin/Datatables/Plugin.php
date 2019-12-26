<?php

/**
 * Plugin.php - Datatables plugin for Jaxon.
 */

namespace Lagdo\PolrAdmin\Ajax\Plugin\Datatables;

class Plugin extends \Jaxon\Plugin\Response
{
    public function __construct()
    {
        $this->dtRenderer = new Renderer();
    }

    /**
     * Get the plugin name.
     *
     * @return string
     */
    public function getName()
    {
        return 'datatables';
    }

    /**
     * Get the plugin hash.
     *
     * @return string
     */
    public function generateHash()
    {
        // The version number is used as hash
        return '0.1.0';
    }

    /**
     * Get the javascript code.
     *
     * @return string
     */
    public function getScript()
    {
        return '
jaxon.command.handler.register("datatables", function(args) {
    args.request.datatables.callback(args.data);
});
';
    }

    /**
     * Create a datatable object, and add the corresponding command into the response
     * @param array $data
     * @param integer $total
     * @param integer $draw
     * @return \Lagdo\PolrAdmin\Ext\Datatables\Datatables
     */
    public function make(array $data, $total, $draw = 0)
    {
        $datatables = new Datatables($data, $total, $draw);
        $this->addCommand(array('cmd' => 'datatables'), $datatables);

        return $datatables;
    }

    public function users($result, $settings)
    {
        $this->dtRenderer->settings = $settings;
        $this->make($result->data, $result->recordsTotal, $result->draw)
            ->add('api_action', [$this->dtRenderer, 'renderAdminApiActionCell'])
            ->add('toggle_active', [$this->dtRenderer, 'renderToggleUserActiveCell'])
            ->add('change_role', [$this->dtRenderer, 'renderChangeUserRoleCell'])
            ->add('delete', [$this->dtRenderer, 'renderDeleteUserCell'])
            ->escape(['username', 'email'])
            ->attr([
                'data-id' => 'id',
                'data-name' => 'username',
            ]);
    }

    public function adminLinks($result, $settings)
    {
        $this->dtRenderer->settings = $settings;
        $this->make($result->data, $result->recordsTotal, $result->draw)
            ->add('disable', [$this->dtRenderer, 'renderToggleLinkActiveCell'])
            ->add('delete', [$this->dtRenderer, 'renderDeleteLinkCell'])
            ->edit('clicks', [$this->dtRenderer, 'renderClicksCell'])
            ->edit('long_url', [$this->dtRenderer, 'renderLongUrlCell'])
            ->escape(['short_url', 'creator'])
            ->attr([
                'data-id' => 'id',
                'data-ending' => 'short_url',
            ]);
    }

    public function userLinks($result, $settings)
    {
        $this->dtRenderer->settings = $settings;
        $this->make($result->data, $result->recordsTotal, $result->draw)
            ->edit('clicks', [$this->dtRenderer, 'renderClicksCell'])
            ->edit('long_url', [$this->dtRenderer, 'renderLongUrlCell'])
            ->escape(['short_url'])
            ->attr([
                'data-id' => 'id',
                'data-ending' => 'short_url',
            ]);
    }
}
