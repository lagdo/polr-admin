<?php

/**
 * Plugin.php - Datatables plugin for Jaxon.
 */

namespace Lagdo\Polr\Admin\Ext\Datatables;

class Plugin extends \Jaxon\Plugin\Response
{
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
        return '0.0.1';
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
     * Show the Datatables.
     *
     * @return void
     */
    public function show($datatables, $total = 0, $filtered = 0)
    {
        $content = json_decode($datatables->content());
        if($total > 0)
        {
            $content->recordsTotal = $total;
        }
        if($filtered > 0)
        {
            $content->recordsFiltered = $filtered;
        }
        $this->addCommand(array('cmd' => 'datatables'), $content);
    }
}