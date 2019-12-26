<?php

namespace Lagdo\PolrAdmin\Ajax\App;

use Lagdo\PolrAdmin\Client;
use Lagdo\PolrAdmin\Helpers\Validator;

use Jaxon\CallableClass;
use Exception;

class Link extends CallableClass
{
    public function __construct(Client $client, Validator $validator)
    {
        $this->client = $client;
        $this->validator = $validator;
    }

    public function editLongUrl($server, $ending)
    {
        if(!$this->user)
        {
            $this->response->dialog->error('You are not allowed to do that!!', 'Error');
            return $this->response;
        }
        try
        {
            // Fetch the link from the Polr instance
            $link = $this->client->getShortUrl($server, $ending);
        }
        catch(Exception $e)
        {
            $this->response->dialog->error($e->getMessage(), 'Error');
            return $this->response;
        }

        $title = 'Long URL';
        $content = $this->view()->render('polr_admin::snippets.edit_long_url', ['link' => $link]);
        $buttons = [
            [
                'title' => 'Save link',
                'class' => 'btn btn-primary btn-sm',
                'click' => $this->rq()->saveLongUrl($server, $ending, pr()->form('edit-long-url')),
            ],
            [
                'title' => 'Cancel',
                'class' => 'btn btn-danger btn-sm',
                'click' => 'close',
            ]
        ];
        $this->response->dialog->show($title, $content, $buttons);

        return $this->response;
    }

    public function saveLongUrl($server, $ending, $values)
    {
        if(!$this->user)
        {
            $this->response->dialog->error('You are not allowed to do that!!', 'Error');
            return $this->response;
        }
        try
        {
            // Update the link in the Polr instance
            $this->client->saveShortUrl($server, $ending, ['url' => $values['url']]);
        }
        catch(Exception $e)
        {
            $this->response->dialog->error($e->getMessage(), 'Error');
            return $this->response;
        }

        // Reload the datatable
        $this->response->script("polr.home.reloadLinkTables()");
        // Show a confirmation message
        $this->response->dialog->info("Long URL successfully changed.", 'Success');
        // Hide the dialog
        $this->response->dialog->hide();

        return $this->response;
    }

    public function setLinkStatus($server, $ending, $status)
    {
        if(!$this->user)
        {
            $this->response->dialog->error('You are not allowed to do that!!', 'Error');
            return $this->response;
        }
        try
        {
            // Update the link in the Polr instance
            $this->client->saveShortUrl($server, $ending, ['status' => $status]);
        }
        catch(Exception $e)
        {
            $this->response->dialog->error($e->getMessage(), 'Error');
            return $this->response;
        }

        // Reload the datatable
        $this->response->script("polr.home.reloadLinkTables()");
        // Show a confirmation message
        $this->response->dialog->info("Link successfully {$status}d.", 'Success');

        return $this->response;
    }

    public function deleteLink($server, $ending)
    {
        $ending = trim($ending);
        try
        {
            $this->client->deleteShortUrl($server, $ending);
        }
        catch(Exception $e)
        {
            $this->response->dialog->error($e->getMessage(), 'Error');
            return $this->response;
        }

        // Reload the datatable
        $this->response->script("polr.home.reloadLinkTables()");
        // Show a confirmation message
        $this->response->dialog->info("Link successfully deleted.", 'Success');

        return $this->response;
    }

    public function checkAvailability($server, $ending)
    {
        $ending = trim($ending);
        try
        {
            $avail = $this->client->checkAvailability($server, $ending);
        }
        catch(Exception $e)
        {
            $this->response->html('link-availability-status',
                '<span style="color:orange"><i class="fa fa-exclamation-triangle"></i> Invalid Custom URL Ending</span>');
            return $this->response;
        }

        if($avail)
        {
            $this->response->html('link-availability-status',
                '<span style="color:green"><i class="fa fa-check"></i> Available</span>');
        }
        else
        {
            $this->response->html('link-availability-status',
                '<span style="color:red"><i class="fa fa-ban"></i> Already in use</span>');
        }
        return $this->response;
    }

    public function shorten($server, array $values)
    {
        try
        {
            $shortUrl = $this->client->createShortUrl($server, $values);
        }
        catch(Exception $e)
        {
            $this->response->dialog->error($e->getMessage(), 'Error');
            return $this->response;
        }

        $title = 'Shortened URL';
        $content = $this->view()->render('polr_admin::shorten.result', ['shortUrl' => $shortUrl]);
        $buttons = [];
        $this->response->dialog->show($title, $content, $buttons);
        $this->jq('.result-box')->focus()->select();

        return $this->response;
    }

    public function getAdminLinks($server, $parameters)
    {
        try
        {
            // Fetch the links from the Polr instance
            $response = $this->client->getAllShortUrls($server, $parameters);
         }
        catch(Exception $e)
        {
            $this->response->dialog->error($e->getMessage(), 'Error');
            return $this->response;
        }

        $this->response->datatables->adminLinks($response->result, $response->settings);
        return $this->response;
    }

    public function getUserLinks($server, $parameters)
    {
        try
        {
            // Fetch the links from the Polr instance
            $response = $this->client->getShortUrls($server, $parameters);
        }
        catch(Exception $e)
        {
            $this->response->dialog->error($e->getMessage(), 'Error');
            return $this->response;
        }

        $this->response->datatables->userLinks($response->result, $response->settings);
        return $this->response;
    }
}
