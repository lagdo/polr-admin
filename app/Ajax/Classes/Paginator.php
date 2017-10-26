<?php

namespace Jaxon\App;

use App\Models\Link as LinkModel;
use App\Models\User as UserModel;

use Datatables;
use Jaxon\Sentry\Armada as JaxonClass;

class Paginator extends JaxonClass
{
    /**
     * Process AJAX Datatables pagination queries from the admin panel.
     *
     * @return Response
     */

    protected function currIsAdmin()
    {
        $role = session('role');
        return ($role == 'admin');
    }

    /* DataTables bindings */

    public function paginateAdminUsers($parameters)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        // Write the input parameters back into the Laravel HTTP Request object.
        // The Datatables class needs to have them there.
        $this->httpRequest->merge($parameters);

        $admin_users = UserModel::select(['username', 'email', 'created_at', 'active',
            'api_key', 'api_active', 'api_quota', 'role', 'id']);
        $datatables = Datatables::of($admin_users)
            ->setRowAttr([
                'data-id' => '{{$id}}',
                'data-name' => '{{$username}}',
            ])
            ->addColumn('api_action', [$this->dtRenderer, 'renderAdminApiActionCell'])
            ->addColumn('toggle_active', [$this->dtRenderer, 'renderToggleUserActiveCell'])
            ->addColumn('change_role', [$this->dtRenderer, 'renderChangeUserRoleCell'])
            ->addColumn('delete', [$this->dtRenderer, 'renderDeleteUserCell'])
            ->escapeColumns(['username', 'email'])
            ->make(true);

        $this->response->datatables->show(json_decode($datatables->content()));

        return $this->response;
    }

    public function paginateAdminLinks($parameters)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        // Write the input parameters back into the Laravel HTTP Request object.
        // The Datatables class needs to have them there.
        $this->httpRequest->merge($parameters);

        $admin_links = LinkModel::select(['id', 'short_url', 'long_url', 'clicks',
            'created_at', 'creator', 'is_disabled']);
        $datatables = Datatables::of($admin_links)
            ->setRowAttr([
                'data-id' => '{{$id}}',
                'data-ending' => '{{$short_url}}',
            ])
            ->addColumn('disable', [$this->dtRenderer, 'renderToggleLinkActiveCell'])
            ->addColumn('delete', [$this->dtRenderer, 'renderDeleteLinkCell'])
            ->editColumn('clicks', [$this->dtRenderer, 'renderClicksCell'])
            ->editColumn('long_url', [$this->dtRenderer, 'renderLongUrlCell'])
            ->escapeColumns(['short_url', 'creator'])
            ->make(true);

        $this->response->datatables->show(json_decode($datatables->content()));

        return $this->response;
    }

    public function paginateUserLinks($parameters)
    {
        // self::ensureLoggedIn();

        // Write the input parameters back into the Laravel HTTP Request object.
        // The Datatables class needs to have them there.
        $this->httpRequest->merge($parameters);

        $username = session('username');
        $user_links = LinkModel::where('creator', $username)
            ->select(['id', 'short_url', 'long_url', 'clicks', 'created_at']);

        $datatables = Datatables::of($user_links)
            ->setRowAttr([
                'data-id' => '{{$id}}',
                'data-ending' => '{{$short_url}}',
            ])
            ->editColumn('clicks', [$this->dtRenderer, 'renderClicksCell'])
            ->editColumn('long_url', [$this->dtRenderer, 'renderLongUrlCell'])
            ->escapeColumns(['short_url'])
            ->make(true);

        $this->response->datatables->show(json_decode($datatables->content()));

        return $this->response;
    }
}
