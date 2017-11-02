<?php

/**
 * Renderer.php - Renderer for the Datatables plugin.
 */

namespace Lagdo\Polr\Admin\Ext\Datatables;

use App\Helpers\UserHelper;

class Renderer
{
    /**
     * Settings received in the response from Polr
     */
    public $settings = null;

    /* Cell rendering functions */

    public function renderLongUrlCell($link)
    {
        return '<a target="_blank" title="' . e($link->long_url) . '" href="'.
            $link->long_url .'">' . str_limit($link->long_url, 50) . '</a>
            <a class="btn btn-primary btn-xs edit-long-link-btn"><i class="fa fa-edit edit-link-icon"></i></a>';
    }

    public function renderClicksCell($link)
    {
        if(($this->settings) && ($this->settings->analytics))
        {
            return $link->clicks . ' <a class="stats-icon show-link-stats" href="javascript:void(0)">' .
                '<i class="fa fa-area-chart" aria-hidden="true"></i></a>';
        }
        else
        {
            return $link->clicks;
        }
    }

    public function renderDeleteUserCell($user)
    {
        // Add "Delete" action button
        /*$btn_class = '';
        if (($this->settings) && $this->settings->username === $user->username)
        {
            $btn_class = 'disabled';
        }
        else
        {
            $btn_class = 'btn-delete-user';
        }*/
        // This feature is disabled
        $btn_class = 'disabled';
        return '<a class="btn btn-sm btn-danger ' . $btn_class . '">Delete</a>';
    }

    public function renderDeleteLinkCell($link)
    {
        // Add "Delete" action button
        return '<a class="btn btn-sm btn-warning btn-delete-link delete-link">Delete</a>';
    }

    public function renderAdminApiActionCell($user)
    {
        // Add "API Info" action button
        if (($this->settings) && $this->settings->username === $user->username)
        {
            $btn_class = 'disabled';
        }
        else
        {
            $btn_class = 'btn-show-api-info';
        }
        return '<a class="' . $btn_class . ' btn btn-sm btn-info">API info</a>';
    }

    public function renderToggleUserActiveCell($user)
    {
        // Add user account active state toggle buttons
        if (($this->settings) && $this->settings->username === $user->username)
        {
            $btn_class = ' disabled';
        }
        else
        {
            $btn_class = ($user->active) ? ' btn-disable-user' : ' btn-enable-user';
        }

        if ($user->active)
        {
            $active_text = 'Active';
            $btn_color_class = ' btn-success';
        }
        else
        {
            $active_text = 'Inactive';
            $btn_color_class = ' btn-danger';
        }

        return '<a class="btn btn-sm status-display' . $btn_color_class .
            $btn_class . '">' . $active_text . '</a>';
    }

    public function renderChangeUserRoleCell($user)
    {
        // Add "change role" select box
        // <select> field does not use Angular bindings
        // because of an issue affecting fields with duplicate names.

        if (($this->settings) && $this->settings->username === $user->username)
        {
            // Do not allow user to change own role
            $select_role = '<select class="form-control" disabled>';
        }
        else
        {
            $select_role = '<select class="form-control change-user-role">';
        }

        $userRoles = ($this->settings) ? $this->settings->roles : [];
        foreach ($userRoles as $role_text => $role_val)
        {
            // Iterate over each available role and output option
            $select_role .= '<option value="' . e(($role_val) ?: 'default') . '"';

            if ($user->role === $role_val)
            {
                $select_role .= ' selected';
            }

            $select_role .= '>' . e($role_text) . '</option>';
        }

        $select_role .= '</select>';
        return $select_role;
    }

    public function renderToggleLinkActiveCell($link)
    {
        // Add "Disable/Enable" action buttons
        if($link->is_disabled)
        {
            $btn_class = 'btn-enable-link btn-danger';
            $btn_text = 'Enable';
        }
        else
        {
            $btn_class = 'btn-disable-link btn-success';
            $btn_text = 'Disable';
        }

        return '<a class="btn btn-sm ' . $btn_class . '">' . $btn_text . '</a>';
    }
}
