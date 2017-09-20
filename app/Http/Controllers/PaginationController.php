<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Link;
use App\Models\User;
use App\Helpers\UserHelper;

use Datatables;
use Jaxon\Laravel\Jaxon;

class PaginationController extends Controller
{
    /**
     * Process AJAX Datatables pagination queries from the admin panel.
     *
     * @return Response
     */

    /* Cell rendering functions */

    public function renderLongUrlCell($link)
    {
        return '<a target="_blank" title="' . e($link->long_url) . '" href="'. $link->long_url .'">' . str_limit($link->long_url, 50) . '</a>
            <a class="btn btn-primary btn-xs edit-long-link-btn"><i class="fa fa-edit edit-link-icon"></i></a>';
    }

    public function renderClicksCell($link)
    {
        if (env('SETTING_ADV_ANALYTICS')) {
            return $link->clicks . ' <a target="_blank" class="stats-icon" href="/admin/stats/' . e($link->short_url) . '">
                <i class="fa fa-area-chart" aria-hidden="true"></i>
            </a>';
        }
        else {
            return $link->clicks;
        }
    }

    public function renderDeleteUserCell($user)
    {
        // Add "Delete" action button
        $btn_class = '';
        if (session('username') === $user->username)
        {
            $btn_class = 'disabled';
        }
        else
        {
            $btn_class = 'btn-delete-user';
        }
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
        if (session('username') === $user->username)
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
        $btn_class = '';
        if (session('username') === $user->username)
        {
            $btn_class = ' disabled';
        }
        else
        {
            $btn_class = ' btn-toggle-user-active';
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

        return '<a class="btn btn-sm status-display' . $btn_color_class . $btn_class . '">' . $active_text . '</a>';
    }

    public function renderChangeUserRoleCell($user)
    {
        // Add "change role" select box
        // <select> field does not use Angular bindings
        // because of an issue affecting fields with duplicate names.

        if (session('username') === $user->username)
        {
            // Do not allow user to change own role
            $select_role = '<select class="form-control" disabled>';
        }
        else
        {
            $select_role = '<select class="form-control change-user-role">';
        }

        foreach (UserHelper::$USER_ROLES as $role_text => $role_val)
        {
            // Iterate over each available role and output option
            $select_role .= '<option value="' . e($role_val) . '"';

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
            $btn_class = 'btn-danger';
            $btn_text = 'Enable';
        }
        else
        {
            $btn_class = 'btn-success';
            $btn_text = 'Disable';
        }

        return '<a class="btn btn-sm btn-toggle-link ' . $btn_class . '">' . $btn_text . '</a>';
    }

    /* DataTables bindings */

    public function paginateAdminUsers(Request $request)
    {
        self::ensureAdmin();

        $admin_users = User::select(['username', 'email', 'created_at', 'active', 'api_key', 'api_active', 'api_quota', 'role', 'id']);
        return Datatables::of($admin_users)
            ->setRowAttr([
                'data-id' => '{{$id}}',
                'data-name' => '{{$username}}',
            ])
            ->addColumn('api_action', [$this, 'renderAdminApiActionCell'])
            ->addColumn('toggle_active', [$this, 'renderToggleUserActiveCell'])
            ->addColumn('change_role', [$this, 'renderChangeUserRoleCell'])
            ->addColumn('delete', [$this, 'renderDeleteUserCell'])
            ->escapeColumns(['username', 'email'])
            ->make(true);
    }

    public function paginateAdminLinks(Request $request)
    {
        self::ensureAdmin();

        $admin_links = Link::select(['id', 'short_url', 'long_url', 'clicks', 'created_at', 'creator', 'is_disabled']);
        return Datatables::of($admin_links)
            ->setRowAttr([
                'data-id' => '{{$id}}',
                'data-ending' => '{{$short_url}}',
            ])
            ->addColumn('disable', [$this, 'renderToggleLinkActiveCell'])
            ->addColumn('delete', [$this, 'renderDeleteLinkCell'])
            ->editColumn('clicks', [$this, 'renderClicksCell'])
            ->editColumn('long_url', [$this, 'renderLongUrlCell'])
            ->escapeColumns(['short_url', 'creator'])
            ->make(true);
    }

    public function paginateUserLinks(Request $request)
    {
        self::ensureLoggedIn();

        $username = session('username');
        $user_links = Link::where('creator', $username)
            ->select(['id', 'short_url', 'long_url', 'clicks', 'created_at']);

        return Datatables::of($user_links)
            ->setRowAttr([
                'data-id' => '{{$id}}',
                'data-ending' => '{{$short_url}}',
            ])
            ->editColumn('clicks', [$this, 'renderClicksCell'])
            ->editColumn('long_url', [$this, 'renderLongUrlCell'])
            ->escapeColumns(['short_url'])
            ->make(true);
    }
}
