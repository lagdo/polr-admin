<?php

namespace App\Http\Controllers;

use Auth;
use Datatables;
use Jaxon\Laravel\Jaxon;
use Jaxon\Laravel\Http\Controllers\JaxonController;

class AjaxController extends JaxonController
{
    /**
     * @var Renderer          The Datatables row renderer
     */
    protected $dtRenderer;

    /**
     * The constructor.
     * 
     * The parameters are automatically populated by Laravel, thanks to its service container.
     * 
     * @param Jaxon             $jaxon                  The Laravel Jaxon plugin
     */
    public function __construct(Jaxon $jaxon)
    {
        $this->dtRenderer = new \Jaxon\Ext\Datatables\Renderer();
        parent::__construct($jaxon);
    }

    /**
     * Callback for initializing a Jaxon class instance.
     * 
     * This function is called anytime a Jaxon class is instanciated.
     *
     * @param object            $instance               The Jaxon class instance
     *
     * @return void
     */
    public function initInstance($instance)
    {
        // Dialogs and notifications are implemented by the Dialogs plugin
        $instance->dialog = $this->jaxon->ajaxResponse()->dialog;
        $instance->notify = $this->jaxon->ajaxResponse()->dialog;
        // The Datatables HTTP request
        $instance->dtRequest = Datatables::getRequest();
        // The Datatables row renderer
        $instance->dtRenderer = $this->dtRenderer;
    }

    /**
     * Callback before processing a Jaxon request.
     *
     * @param object            $instance               The Jaxon class instance to call
     * @param string            $method                 The Jaxon class method to call
     * @param boolean           $bEndRequest            Whether to end the request or not
     *
     * @return void
     */
    public function beforeRequest($instance, $method, &$bEndRequest)
    {
        if(Auth::guest())
        {
            // Access to these classes is allowed to guest users
            $guestAllowedClasses = [];
            if(in_array(get_class($instance), $guestAllowedClasses))
            {
                return;
            }
            // End Jaxon request, and redirect to login page
            $bEndRequest = true;
            $instance->response->redirect(route('showLogin'));
        }
    }

    /**
     * Callback after processing a Jaxon request.
     *
     * @param object            $instance               The Jaxon class instance called
     * @param string            $method                 The Jaxon class method called
     *
     * @return void
     */
    /*public function afterRequest($instance, $method)
    {
    }*/
}
