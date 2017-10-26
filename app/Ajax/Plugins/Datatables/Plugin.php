<?php

/**
 * Plugin.php - Javascript charts for Jaxon with the Flot library.
 *
 * @package jaxon-flot
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-flot
 */

namespace Jaxon\Ext\Datatables;

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
    args.request.dt.callback(args.data);
});
';
    }

    /**
     * Draw a Plot in a given HTML element.
     *
     * @return void
     */
    public function show($datatables)
    {
        $this->addCommand(array('cmd' => 'datatables'), $datatables);
    }
}
