<?php

namespace Jaxon\App;

use Validator;
use App\Helpers\LinkHelper;
use App\Factories\LinkFactory;

use Jaxon\Sentry\Armada as JaxonClass;

class Link extends JaxonClass
{
    protected function currIsAdmin()
    {
        $role = session('role');
        return ($role == 'admin');
    }

    public function editLongUrl($link_id)
    {
        $link = LinkHelper::getLinkById($link_id);
        if(!$link)
        {
            $this->notify->error('Link not found.', 'Error');
            return $this->response;
        }

        $title = 'Long URL';
        $content = view('snippets.edit_long_url', ['link' => $link]);
        $buttons = [
            [
                'title' => 'Save link',
                'class' => 'btn btn-primary btn-sm',
                'click' => $this->rq()->saveLongUrl($link->id, rq()->form('edit-long-url')),
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
    
    public function saveLongUrl($link_id, array $formValues)
    {
        /**
         * If user is an admin, allow the user to edit the value of any link's long URL.
         * Otherwise, only allow the user to edit their own links.
         */
    
        $link = LinkHelper::getLinkById($link_id);
        if(!$link)
        {
            $this->notify->error('Link not found.', 'Error');
            return $this->response;
        }
        
        // Validate the new URL
        $rules = array(
            'long_url' => 'required|url',
        );
        $validator = Validator::make($formValues, $rules);
        if($validator->fails())
        {
            $this->notify->error('Link not valid.', 'Error');
            return $this->response;
        }

        if ($link->creator !== session('username') && !$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        $link->long_url = $formValues['long_url'];
        $link->save();

        // Reload the datatable
        $this->response->script("polr.home.reloadLinkTables()");
        // Show a confirmation message
        $this->notify->info("Long URL successfully changed.", 'Success');
        // Hide the dialog
        $this->dialog->hide();

        return $this->response;
    }

    public function setLinkStatus($link_id, $status)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        $link = LinkHelper::getLinkById($link_id);
        if(!$link)
        {
            $this->notify->error('Link not found.', 'Error');
            return $this->response;
        }

        $new_status = ($status) ? 0 : 1;
        $link->is_disabled = $new_status;
        $link->save();

        // Reload the datatable
        $this->response->script("polr.home.reloadLinkTables()");
        // Show a confirmation message
        $status = ($new_status == 1) ? 'disabled' : 'enabled';
        $this->notify->info("Link successfully $status.", 'Success');

        return $this->response;
    }

    public function deleteLink($link_id)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        $link = LinkHelper::getLinkById($link_id);
        if(!$link)
        {
            $this->notify->error('Link not found.', 'Error');
            return $this->response;
        }

        $link->delete();

        // Reload the datatable
        $this->response->script("polr.home.reloadLinkTables()");
        // Show a confirmation message
        $this->notify->info("Link successfully deleted.", 'Success');


        return $this->response;
    }

    public function checkAvailability($link_ending)
    {
        $ending_conforms = LinkHelper::validateEnding($link_ending);

        if (!$ending_conforms)
        {
            $this->response->html('link-availability-status',
                '<span style="color:orange"><i class="fa fa-exclamation-triangle"></i> Invalid Custom URL Ending</span>');
            return $this->response;
        }
        else if (LinkHelper::linkExists($link_ending))
        {
            // if ending already exists
            $this->response->html('link-availability-status',
                '<span style="color:red"><i class="fa fa-ban"></i> Already in use</span>');
            return $this->response;
        }

        $this->response->html('link-availability-status',
            '<span style="color:green"><i class="fa fa-check"></i> Available</span>');
        return $this->response;
    }

    public function shorten(array $formvalues)
    {
        if (!env('SETTING_SHORTEN_PERMISSION', false))
        {
            $this->notify->error('You are not allowed to shorten links.', 'Error');
            return $this->response;
        }

        // Validate URL form data
        $validator = \Validator::make($formvalues, [
            'link-url' => 'required|url',
            'custom-ending' => 'alpha_dash'
        ]);
        if ($validator->fails())
        {
            $this->notify->error('Invalid URL or custom ending.', 'Error');
            return $this->response;
        }

        $long_url = $formvalues['link-url'];
        $custom_ending = $formvalues['custom-ending'];
        $is_secret = ($formvalues['options'] == "s" ? true : false);
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
        }

        $title = 'Shortened URL';
        $content = view('shorten.result', ['short_url' => $short_url]);
        $buttons = [];
        $this->dialog->show($title, $content, $buttons);
        $this->jq('.result-box')->focus()->select();

        return $this->response;
    }
}
