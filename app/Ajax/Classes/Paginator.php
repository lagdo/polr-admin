<?php

namespace Jaxon\App;

use App\Models\Link as LinkModel;
use App\Models\User as UserModel;

use Datatables;
use Jaxon\Sentry\Armada as JaxonClass;

class Paginator extends JaxonClass
{
    use \Jaxon\Helpers\Session;

    protected function datatableParameters($parameters)
    {
        // The boolean parameters sent by Guzzle in a HTTP request re not recognized
        // by Datatables. So we need to convert them to strings "true" or "false".
        foreach($parameters['columns'] as &$column)
        {
            $column['searchable'] = ($column['searchable']) ? 'true' : 'false';
            $column['orderable'] = ($column['orderable']) ? 'true' : 'false';
            $column['search']['regex'] = ($column['search']['regex']) ? 'true' : 'false';
        }
        $parameters['key'] = $this->apiKey;
        return $parameters;
    }

    public function paginateAdminUsers($parameters)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        // Write the input parameters back into the Datatables HTTP Request object.
        // The Datatables class needs to have them there.
        $this->dtRequest->merge($parameters);

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

        $this->response->datatables->show($datatables->content());

        return $this->response;
    }

    public function paginateAdminLinks($parameters)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        // Fetch the users from the Polr instance
        $apiResponse = $this->apiClient->get('links', [
            'query' => $this->datatableParameters($parameters)
        ]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $links = collect($jsonResponse->result->data);

        // Write the input parameters back into the Datatables HTTP Request object.
        // The Datatables class needs to have them there.
        /*$this->dtRequest->merge($parameters);

        $links = LinkModel::select(['id', 'short_url', 'long_url', 'clicks',
            'created_at', 'creator', 'is_disabled']);*/

        $datatables = Datatables::of($links)
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

        $this->response->datatables->show($datatables,
            $jsonResponse->result->recordsTotal, $jsonResponse->result->recordsFiltered);

        return $this->response;
    }

    public function _paginateUserLinks($parameters)
    {
        if(!$this->isLoggedIn())
        {
            $this->notify->error('User is not logged in.', 'Error');
            return $this->response;
        }

        // Write the input parameters back into the Datatables HTTP Request object.
        // The Datatables class needs to have them there.
        $this->dtRequest->merge($parameters);
        $username = session('username');
        $links = LinkModel::where('creator', $username)
            ->select(['id', 'short_url', 'long_url', 'clicks', 'created_at']);

        $datatables = Datatables::of($links)
            ->setRowAttr([
                'data-id' => '{{$id}}',
                'data-ending' => '{{$short_url}}',
            ])
            ->editColumn('clicks', [$this->dtRenderer, 'renderClicksCell'])
            ->editColumn('long_url', [$this->dtRenderer, 'renderLongUrlCell'])
            ->escapeColumns(['short_url'])
            ->make(true);

        $this->response->datatables->show($datatables);

        return $this->response;
    }

    public function paginateUserLinks($parameters)
    {
        if(!$this->isLoggedIn())
        {
            $this->notify->error('User is not logged in.', 'Error');
            return $this->response;
        }

        // Fetch the users from the Polr instance
        $apiResponse = $this->apiClient->get('user/links', [
            'query' => $this->datatableParameters($parameters)
        ]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $links = collect($jsonResponse->result->data);

        $datatables = Datatables::of($links)
            ->setRowAttr([
                'data-id' => '{{$id}}',
                'data-ending' => '{{$short_url}}',
            ])
            ->editColumn('clicks', [$this->dtRenderer, 'renderClicksCell'])
            ->editColumn('long_url', [$this->dtRenderer, 'renderLongUrlCell'])
            ->escapeColumns(['short_url'])
            ->make(true);

        $this->response->datatables->show($datatables,
            $jsonResponse->result->recordsTotal, $jsonResponse->result->recordsFiltered);

        return $this->response;
    }
}
