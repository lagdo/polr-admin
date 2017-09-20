<?php

namespace Jaxon\App;

use Validator;
use App\Helpers\LinkHelper;

use Jaxon\Sentry\Armada as JaxonClass;

class Link extends JaxonClass
{
    protected function currIsAdmin()
    {
        $role = session('role');
        return ($role == 'admin');
    }

    public function editLongUrl($link_id, $datatable)
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
                'click' => $this->rq()->saveLongUrl($link->id, rq()->form('edit-long-url'), $datatable),
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
    
    public function saveLongUrl($link_id, array $formValues, $datatable)
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
        $this->response->script("$.fn.dataTable.ext.search = [];datatables['{$datatable}_links_table'].draw();");
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
        $this->response->script("$.fn.dataTable.ext.search = [];datatables['admin_links_table'].draw();");
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
        $this->response->script("$.fn.dataTable.ext.search = [];datatables['admin_links_table'].draw();");
        // Show a confirmation message
        $this->notify->info("Link successfully deleted.", 'Success');

        return $this->response;
    }
}
