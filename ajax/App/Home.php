<?php

namespace Lagdo\PolrAdmin\Ajax\App;

use Lagdo\PolrAdmin\Package;

use Jaxon\CallableClass;

class Home extends CallableClass
{
    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    public function reload($selected)
    {
        $this->response->assign('polr-dashboard-container', 'outerHTML', $this->package->getHtml($selected));
        $this->response->script($this->package->getReadyScript());

        return $this->response;
    }
}
