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
     * Show the Datatables.
     *
     * @return void
     */
    /*public function show($datatables, $total = 0, $filtered = 0)
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
    }*/

    /**
     * Create a datatable object, and add the corresponding command into the response
     * @param array $data
     * @param integer $total
     * @param integer $draw
     * @return \Lagdo\Polr\Admin\Ext\Datatables\Datatables
     */
    public function make(array $data, $total, $draw = 0)
    {
        $datatables = new Datatables($data, $total, $draw);
        $this->addCommand(array('cmd' => 'datatables'), $datatables);

        return $datatables;
    }
}
