<?php

namespace Jaxon\App;

use App\Models\Link as LinkModel;
use App\Models\User as UserModel;

use Datatables;
use Jaxon\Sentry\Armada as JaxonClass;

class Paginator extends JaxonClass
{
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
        // Fetch the links from the Polr instance
        $apiResponse = $this->apiClient->get('links', [
            'query' => $this->datatableParameters($parameters)
        ]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $links = collect($jsonResponse->result->data);

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

    public function paginateUserLinks($parameters)
    {
        // Fetch the links from the Polr instance
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
