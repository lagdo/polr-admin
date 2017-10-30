<?php

namespace Jaxon\App;

use Validator;
use App\Helpers\LinkHelper;
use App\Factories\LinkFactory;

use Jaxon\Sentry\Armada as JaxonClass;

class Link extends JaxonClass
{
    use \Jaxon\Helpers\Session;

    public function editLongUrl($ending)
    {
        // Fetch the link from the Polr instance
        $ending = trim($ending);
        $apiResponse = $this->apiClient->get('links/' . $ending,
            ['query' => ['key' => $this->apiKey]]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $link = $jsonResponse->result;

        /*$link = LinkHelper::getLinkByShortUrl($ending);
        if(!$link)
        {
            $this->notify->error('Link not found.', 'Error');
            return $this->response;
        }*/

        $title = 'Long URL';
        $content = view('snippets.edit_long_url', ['link' => $link]);
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
    
    public function saveLongUrl($ending, array $formValues)
    {
        /**
         * If user is an admin, allow the user to edit the value of any link's long URL.
         * Otherwise, only allow the user to edit their own links.
         */
    
        /*$link = LinkHelper::getLinkByShortUrl($ending);
        if(!$link)
        {
            $this->notify->error('Link not found.', 'Error');
            return $this->response;
        }*/
        
        // Validate the new URL
        $formValues['ending'] = trim($ending);
        $rules = array(
            'ending' => 'alpha_dash',
            'long_url' => 'required|url',
        );
        $validator = Validator::make($formValues, $rules);
        if($validator->fails())
        {
            $this->notify->error('Link not valid.', 'Error');
            return $this->response;
        }

        /*if ($link->creator !== session('username') && !$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        $link->long_url = $formValues['long_url'];
        $link->save();*/

        // Update the link in the Polr instance
        $apiResponse = $this->apiClient->put('links/' . $formValues['ending'],
            ['query' => ['key' => $this->apiKey, 'url' => $formValues['long_url']]]);
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
        /*if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }*/

        // Validate the new URL
        $values = [
            'ending' => trim($ending),
            'status' => trim($status),
        ];
        $rules = array(
            'ending' => 'alpha_dash',
            'status' => 'required|in:0,1',
        );
        $validator = Validator::make($values, $rules);
        if($validator->fails())
        {
            $this->notify->error('Status not valid.', 'Error');
            return $this->response;
        }

        /*$link = LinkHelper::getLinkByShortUrl($ending);
        if(!$link)
        {
            $this->notify->error('Link not found.', 'Error');
            return $this->response;
        }

        $new_status = ($status) ? 0 : 1;
        $link->is_disabled = $new_status;
        $link->save();*/

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
        /*if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }*/

        // Validate the input
        $values = [
            'ending' => trim($ending),
        ];
        $rules = array(
            'ending' => 'alpha_dash',
        );
        $validator = Validator::make($values, $rules);
        if($validator->fails())
        {
            $this->notify->error('Ending not valid.', 'Error');
            return $this->response;
        }

        /*$link = LinkHelper::getLinkByShortUrl($ending);
        if(!$link)
        {
            $this->notify->error('Link not found.', 'Error');
            return $this->response;
        }

        $link->delete();*/

        // Delete the link in the Polr instance
        $apiResponse = $this->apiClient->delete('links/' . $values['ending'],
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
        $ending_conforms = LinkHelper::validateEnding($ending);

        if (!$ending_conforms)
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

        /*if (LinkHelper::linkExists($ending))
        {
            // if ending already exists
            $this->response->html('link-availability-status',
                '<span style="color:red"><i class="fa fa-ban"></i> Already in use</span>');
            return $this->response;
        }

        $this->response->html('link-availability-status',
            '<span style="color:green"><i class="fa fa-check"></i> Available</span>');*/

        return $this->response;
    }

    public function shorten(array $formValues)
    {
        /*if (!env('SETTING_SHORTEN_PERMISSION', false))
        {
            $this->notify->error('You are not allowed to shorten links.', 'Error');
            return $this->response;
        }*/

        // Validate URL form data
        $validator = \Validator::make($formValues, [
            'link-url' => 'required|url',
            'custom-ending' => 'alpha_dash'
        ]);
        if ($validator->fails())
        {
            $this->notify->error('Invalid URL or custom ending.', 'Error');
            return $this->response;
        }

        /*$long_url = $formValues['link-url'];
        $custom_ending = $formValues['custom-ending'];
        $is_secret = ($formValues['options'] == "s" ? true : false);
        $creator = session('username');
        $link_ip = $this->httpRequest->ip();

        try
        {
            $short_url = LinkFactory::createLink($long_url, $is_secret, $custom_ending, $link_ip, $creator);
        }
        catch (\Exception $e)
        {
            $this->notify->error($e->getMessage(), 'Error');
            return $this->response;
        }*/

        // API request parameters
        $parameters = [
            'key' => $this->apiKey,
            'url' => $formValues['link-url'],
            'secret' => ($formValues['options'] == "s" ? 'true' : 'false'),
            'ip' => $this->httpRequest->ip(),
        ];
        if($formValues['custom-ending'] != '')
        {
            $parameters['ending'] = $formValues['custom-ending'];
        }

        // Update the link in the Polr instance
        $apiResponse = $this->apiClient->post('links', ['query' => $parameters]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $short_url = $jsonResponse->result;

        $title = 'Shortened URL';
        $content = view('shorten.result', ['short_url' => $short_url]);
        $buttons = [];
        $this->dialog->show($title, $content, $buttons);
        $this->jq('.result-box')->focus()->select();

        return $this->response;
    }
}
