<?php

namespace Lagdo\Polr\Admin\App;

use Valitron\Validator;

use Jaxon\Sentry\Armada as JaxonClass;

class User extends JaxonClass
{
    public function selectEndpoint($endpoint)
    {
        $endpoint = trim($endpoint);
        // Validate the new endpoint
        if(!$this->validator->validateEndpoint($endpoint))
        {
            $this->notify->error('The endpoint id is not valid.', 'Error');
            return $this->response;
        }

        $this->session()->set('polr.endpoint', $endpoint);
        $this->polr->reload($this->response);

        return $this->response;
    }

    public function generateNewKey($userId, $fromDev)
    {
        if(!$this->validator->validateId($userId))
        {
            $this->notify->error('Invalid or missing parameters.', 'Error');
            return $this->response;
        }

        // Generate the new key on the Polr instance
        $apiResponse = $this->apiClient->post('users/' . $userId . '/api',
            ['query' => ['key' => $this->apiKey]]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $user = $jsonResponse->result;

        if(($fromDev))
        {
            // Show the new API key in the form
            $this->response->assign('api-key-value', 'value', $user->api_key);

            // Show a confirmation message
            $this->notify->info("New API key successfully generated.", 'Success');
        }
        else
        {
            // Update the dialog with the new status
            $this->jq('#edit-user-api-key .status-display')->html($user->api_key);

            // Show a confirmation message
            $this->notify->info("New API key for user {$user->username} successfully generated.", 'Success');
        }

        return $this->response;
    }

    public function showAPIInfo($userId)
    {
        if(!$this->validator->validateId($userId))
        {
            $this->notify->error('Invalid or missing parameters.', 'Error');
            return $this->response;
        }

        // Get the user on the Polr instance
        $apiResponse = $this->apiClient->get('users/' . $userId,
            ['query' => ['key' => $this->apiKey]]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $user = $jsonResponse->result;
        if(!$user->active)
        {
            $this->notify->error('User not active.', 'Error');
            return $this->response;
        }

        $title = 'Edit User API Settings';
        $content = $this->view()->render('polr_admin::snippets.edit_user_api_info', ['user' => $user]);
        $buttons = [
            [
                'title' => 'Close',
                'class' => 'btn btn-danger btn-sm',
                'click' => 'close',
            ]
        ];

        $this->dialog->show($title, $content, $buttons);
        // Set event handlers on buttons
        $this->jq('#edit-user-api-active a.btn')->click($this->rq()->toggleAPIActive($user->id));
        $this->jq('#edit-user-api-key a.btn')->click($this->rq()->generateNewKey($user->id, false));
        $this->jq('#edit-user-api-quota a.btn')->click($this->rq()->editAPIQuota($user->id,
            jq('#edit-user-api-quota input.api-quota')->val()));

        return $this->response;
    }
    
    public function toggleAPIActive($userId)
    {
        if(!$this->validator->validateId($userId))
        {
            $this->notify->error('Invalid or missing parameters.', 'Error');
            return $this->response;
        }

        // Toogle the user API status on the Polr instance
        $apiResponse = $this->apiClient->put('users/' . $userId . '/api',
            ['query' => ['key' => $this->apiKey, 'status' => 'toggle']]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $user = $jsonResponse->result;

        // Update the dialog with the new status
        $this->jq('#edit-user-api-active .status-display')->html(($user->api_active == 1) ? 'True' : 'False');
        // Show a confirmation message
        $status = ($user->api_active == 1) ? 'active' : 'inactive';
        $this->notify->info("API status of user {$user->username} successfully changed to $status.", 'Success');

        return $this->response;
    }

    public function editAPIQuota($userId, $newQuota)
    {
        $values = ['id' => $userId, 'quota' => $newQuota];
        if(!$this->validator->validateUserQuota($values))
        {
            $this->notify->error('Invalid or missing parameters.', 'Error');
            return $this->response;
        }

        // Change the user API quota on the Polr instance
        $apiResponse = $this->apiClient->put('users/' . $userId . '/api',
            ['query' => ['key' => $this->apiKey, 'quota' => $newQuota]]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $user = $jsonResponse->result;

        // Show a confirmation message
        $this->notify->info("Quota of user {$user->username} successfully changed to {$user->api_quota}.", 'Success');

        return $this->response;
    }

    public function setUserStatus($userId, $status)
    {
        $values = ['id' => $userId, 'status' => $status];
        if(!$this->validator->validateUserStatus($values))
        {
            $this->notify->error('Invalid or missing parameters.', 'Error');
            return $this->response;
        }

        // Change the user status on the Polr instance
        $status = ($status == 1) ? 'enable' : 'disable';
        $apiResponse = $this->apiClient->put('users/' . $userId,
            ['query' => ['key' => $this->apiKey, 'status' => $status]]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $user = $jsonResponse->result;

        // Reload the datatable
        $status = ($user->active == 1) ? 'active' : 'inactive';
        $this->response->script("polr.home.reloadUserTables()");
        // Show a confirmation message
        $this->notify->info("Status of user {$user->username} successfully changed to $status.", 'Success');

        return $this->response;
    }

    public function changeUserRole($userId, $role)
    {
        $values = ['id' => $userId, 'role' => $role];
        if(!$this->validator->validateUserRole($values))
        {
            $this->notify->error('Invalid or missing parameters.', 'Error');
            return $this->response;
        }

        // Change the user role on the Polr instance
        $apiResponse = $this->apiClient->put('users/' . $userId,
            ['query' => ['key' => $this->apiKey, 'role' => $role]]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $user = $jsonResponse->result;

        // Reload the datatable
        $this->response->script("polr.home.reloadUserTables()");
        // Show a confirmation message
        $role = ($user->role) ? : 'default';
        $this->notify->info("Role of user {$user->username} successfully changed to $role.", 'Success');

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

    public function getUsers($parameters)
    {
        // Fetch the users from the Polr instance
        $apiResponse = $this->apiClient->get('users', [
            'query' => $this->datatableParameters($parameters)
        ]);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        $this->dtRenderer->settings = $jsonResponse->settings;

        // Fill user roles dropdown
        $this->response->html('user-roles', $this->view()->render('polr_admin::snippets.select-roles',
            ['roles' => $jsonResponse->settings->roles]));

        $this->response->datatables->make($jsonResponse->result->data,
            $jsonResponse->result->recordsTotal, $jsonResponse->result->draw)
            ->add('api_action', [$this->dtRenderer, 'renderAdminApiActionCell'])
            ->add('toggle_active', [$this->dtRenderer, 'renderToggleUserActiveCell'])
            ->add('change_role', [$this->dtRenderer, 'renderChangeUserRoleCell'])
            ->add('delete', [$this->dtRenderer, 'renderDeleteUserCell'])
            ->escape(['username', 'email'])
            ->attr([
                'data-id' => 'id',
                'data-name' => 'username',
            ]);

        return $this->response;
    }
}
