<?php

namespace Lagdo\Polr\Admin\App;

use Valitron\Validator;

use Jaxon\Sentry\Armada as JaxonClass;

class Link extends JaxonClass
{
    public function editLongUrl($ending)
    {
        $ending = trim($ending);
        // Validate the input
        if(!$this->validator->validateLinkEnding($ending))
        {
            $this->notify->error('Ending not valid.', 'Error');
            return $this->response;
        }

        // Fetch the link from the Polr instance
        $apiResponse = $this->apiClient->get('links/' . $ending,
            ['query' => ['key' => $this->apiKey]]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $link = $jsonResponse->result;

        $title = 'Long URL';
        $content = $this->view()->render('polr_admin::snippets.edit_long_url', ['link' => $link]);
        $buttons = [
            [
                'title' => 'Save link',
                'class' => 'btn btn-primary btn-sm',
                'click' => $this->rq()->saveLongUrl($link->short_url, rq()->form('edit-long-url')),
            ],
            [
                'title' => 'Cancel',
                'class' => 'btn btn-danger btn-sm',
                'click' => 'close',
            ]
        ];
        $this->dialog->show($title, $content, $buttons);

        return $this->response;
    }

    public function saveLongUrl($ending, array $values)
    {
        // Validate the new URL
        $values['ending'] = trim($ending);
        if(!$this->validator->validateLinkUrl($values, true))
        {
            $this->notify->error('Link not valid.', 'Error');
            return $this->response;
        }

        // Update the link in the Polr instance
        $apiResponse = $this->apiClient->put('links/' . $values['ending'],
            ['query' => ['key' => $this->apiKey, 'url' => $values['url']]]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());

        // Reload the datatable
        $this->response->script("polr.home.reloadLinkTables()");
        // Show a confirmation message
        $this->notify->info("Long URL successfully changed.", 'Success');
        // Hide the dialog
        $this->dialog->hide();

        return $this->response;
    }

    public function setLinkStatus($ending, $status)
    {
        // Validate the new URL
        $values = [
            'ending' => trim($ending),
            'status' => trim($status),
        ];
        if(!$this->validator->validateLinkStatus($values))
        {
            $this->notify->error('Status not valid.', 'Error');
            return $this->response;
        }

        // Update the link in the Polr instance
        $update = ($values['status']) ? 'enable' : 'disable';
        $apiResponse = $this->apiClient->put('links/' . $values['ending'],
            ['query' => ['key' => $this->apiKey, 'status' => $update]]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());

        // Reload the datatable
        $this->response->script("polr.home.reloadLinkTables()");
        // Show a confirmation message
        $this->notify->info("Link successfully {$update}d.", 'Success');

        return $this->response;
    }

    public function deleteLink($ending)
    {
        $ending = trim($ending);
        // Validate the input
        if(!$this->validator->validateLinkEnding($ending))
        {
            $this->notify->error('Ending not valid.', 'Error');
            return $this->response;
        }

        // Delete the link in the Polr instance
        $apiResponse = $this->apiClient->delete('links/' . $ending,
            ['query' => ['key' => $this->apiKey]]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());

        // Reload the datatable
        $this->response->script("polr.home.reloadLinkTables()");
        // Show a confirmation message
        $this->notify->info("Link successfully deleted.", 'Success');


        return $this->response;
    }

    public function checkAvailability($ending)
    {
        $ending = trim($ending);
        // Validate the input
        if(!$this->validator->validateLinkEnding($ending))
        {
            $this->response->html('link-availability-status',
                '<span style="color:orange"><i class="fa fa-exclamation-triangle"></i> Invalid Custom URL Ending</span>');
            return $this->response;
        }

        // Fetch the link from the Polr instance
        try
        {
            $apiResponse = $this->apiClient->get('links/' . $ending,
                ['query' => ['key' => $this->apiKey]]);
            $jsonResponse = json_decode($apiResponse->getBody()->getContents());

            // if ending already exists
            $this->response->html('link-availability-status',
                '<span style="color:red"><i class="fa fa-ban"></i> Already in use</span>');
        }
        catch(\Exception $e)
        {
            $this->response->html('link-availability-status',
                '<span style="color:green"><i class="fa fa-check"></i> Available</span>');
        }

        return $this->response;
    }

    public function shorten(array $values)
    {
        // Validate URL form data
        if(!$this->validator->validateLinkUrl($values, false))
        {
            $this->notify->error('Invalid URL or custom ending.', 'Error');
            return $this->response;
        }

        // API request parameters
        $parameters = [
            'key' => $this->apiKey,
            'url' => $values['url'],
            'secret' => ($values['options'] == "s" ? 'true' : 'false'),
            'ip' => $this->remoteAddress,
        ];
        if($values['ending'] != '')
        {
            $parameters['ending'] = $values['ending'];
        }

        // Update the link in the Polr instance
        $apiResponse = $this->apiClient->post('links', ['query' => $parameters]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $short_url = $jsonResponse->result;

        $title = 'Shortened URL';
        $content = $this->view()->render('polr_admin::shorten.result', ['short_url' => $short_url]);
        $buttons = [];
        $this->dialog->show($title, $content, $buttons);
        $this->jq('.result-box')->focus()->select();

        return $this->response;
    }

    protected function datatableParameters($parameters)
    {
        // The boolean parameters sent by Guzzle in a HTTP request are not recognized
        // by Datatables. So we need to convert them to strings "true" or "false".
        foreach($parameters['columns'] as &$column)
        {
            $column['searchable'] = ($column['searchable']) ? 'true' : 'false';
            $column['orderable'] = ($column['orderable']) ? 'true' : 'false';
            $column['search']['regex'] = ($column['search']['regex']) ? 'true' : 'false';
        }
        // Set the "key" parameter
        $parameters['key'] = $this->apiKey;
        return $parameters;
    }

    public function getAdminLinks($parameters)
    {
        // Fetch the links from the Polr instance
        $apiResponse = $this->apiClient->get('links', [
            'query' => $this->datatableParameters($parameters)
        ]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $this->dtRenderer->settings = $jsonResponse->settings;

        $this->response->datatables->make($jsonResponse->result->data,
            $jsonResponse->result->recordsTotal, $jsonResponse->result->draw)
            ->add('disable', [$this->dtRenderer, 'renderToggleLinkActiveCell'])
            ->add('delete', [$this->dtRenderer, 'renderDeleteLinkCell'])
            ->edit('clicks', [$this->dtRenderer, 'renderClicksCell'])
            ->edit('long_url', [$this->dtRenderer, 'renderLongUrlCell'])
            ->escape(['short_url', 'creator'])
            ->attr([
                'data-id' => 'id',
                'data-ending' => 'short_url',
            ]);

        return $this->response;
    }

    public function getUserLinks($parameters)
    {
        // Fetch the links from the Polr instance
        $apiResponse = $this->apiClient->get('users/me/links', [
            'query' => $this->datatableParameters($parameters)
        ]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $this->dtRenderer->settings = $jsonResponse->settings;

        $this->response->datatables->make($jsonResponse->result->data,
            $jsonResponse->result->recordsTotal, $jsonResponse->result->draw)
            ->edit('clicks', [$this->dtRenderer, 'renderClicksCell'])
            ->edit('long_url', [$this->dtRenderer, 'renderLongUrlCell'])
            ->escape(['short_url'])
            ->attr([
                'data-id' => 'id',
                'data-ending' => 'short_url',
            ]);

        return $this->response;
    }
}
