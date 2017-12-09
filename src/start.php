<?php

/**
 * start.php -
 *
 * This file is automatically loaded by the Composer autoloader
 *
 * The package is initialized here.
 */

function polr_admin_init()
{
    // Read config file
    $jaxon = jaxon();
    $sentry = $jaxon->sentry();
    $sConfigFile = __DIR__ . '/../config/jaxon.php';
    $xAppConfig = $jaxon->readConfigFile($sConfigFile, 'lib', 'app');
    $sentry->addClassOptions($xAppConfig);
    $sentry->addClassNamespaces($xAppConfig);
    $sentry->addViewNamespaces($xAppConfig);

    // Set the class initializer
    $sentry->addClassInitializer('Lagdo\Polr\Admin\App', function($instance) {
        $polr = jaxon()->sentry()->getPackage('polr.admin');
        // Init the Jaxon class instance
        $polr->initInstance($instance);
    });

    // Register the Datatables row renderer
    $sentry->registerPackage('dt.renderer', function() {
        return new \Lagdo\Polr\Admin\Ext\Datatables\Renderer();
    });
    // Register the Polr Admin
    $sentry->registerPackage('polr.admin', function() {
        $dtRenderer = jaxon()->sentry()->getPackage('dt.renderer');
        return new \Lagdo\Polr\Admin\PolrAdmin($dtRenderer);
    });
}

// Register an instance of the Datatables plugin
jaxon_register_plugin(new \Lagdo\Polr\Admin\Ext\Datatables\Plugin());

// Initialize the package
polr_admin_init();
