<?php

namespace Jaxon\App;

use Hash;
use App\Helpers\UserHelper;
use App\Helpers\CryptoHelper;
use App\Factories\UserFactory;
use Jaxon\Sentry\Armada as JaxonClass;

class User extends JaxonClass
{
    protected function currIsAdmin()
    {
        $role = session('role');
        return ($role == 'admin');
    }

    public function generateNewAPIKey($user_id, $fromDev)
    {
        /**
         * If user is an admin, allow resetting of any API key
         *
         * If user is not an admin, allow resetting of own key only, and only if
         * API is enabled for the account.
         * @return string; new API key
         */
        $user = UserHelper::getUserById($user_id);

        $username_user_requesting = session('username');
        $user_requesting = UserHelper::getUserByUsername($username_user_requesting);

        if(!$user)
        {
            $this->notify->error('User not found.', 'Error');
            return $this->response;
        }

        if($user != $user_requesting)
        {
            // if user is attempting to reset another user's API key,
            // ensure they are an admin
            if(!$this->currIsAdmin())
            {
                $this->notify->error('User is not admin.', 'Error');
                return $this->response;
            }
        }
        else
        {
            // user is attempting to reset own key
            // ensure that user is permitted to access the API
            $user_api_enabled = $user->api_active;
            if(!$user_api_enabled)
            {
                // if the user does not have API access toggled on,
                // allow only if user is an admin
                if(!$this->currIsAdmin())
                {
                    $this->notify->error('User is not admin.', 'Error');
                    return $this->response;
                }
            }
        }

        $new_api_key = CryptoHelper::generateRandomHex(env('_API_KEY_LENGTH'));
        $user->api_key = $new_api_key;
        $user->save();

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
            $this->jq('#edit-user-api-key .status-display')->html($new_api_key);

            // Show a confirmation message
            $this->notify->info("New API key for user {$user->username} successfully generated.", 'Success');
        }

        return $this->response;
    }

    public function changePassword(array $formValues)
    {
        $username = session('username');
        $old_password = $formValues['current_password'];
        $new_password = $formValues['new_password'];

        if(UserHelper::checkCredentials($username, $old_password) == false)
        {
            // Invalid credentials
            $this->notify->error('Current password is invalid. Try again.', 'Error');
            return $this->response;
        }

        // Credentials are correct
        $user = UserHelper::getUserByUsername($username);
        $user->password = Hash::make($new_password);
        $user->save();

        // Clear the form
        $this->jq('#change-password-form .password-box')->val('');

        // Show a confirmation message
        $this->notify->info("Password successfully changed.", 'Success');

        return $this->response;
    }

    public function addNewUser(array $formValues)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        $ip = $this->httpRequest->ip();
        $username = $formValues['username'];
        $user_password = $formValues['user_password'];
        $user_email = $formValues['user_email'];
        $user_role = $formValues['user_role'];

        UserFactory::createUser($username, $user_email, $user_password, 1, $ip, false, 0, $user_role);

        // Clear and hide the form
        $this->jq('#new-user-form input.form-control')->val('');
        $this->jq('.new-user-fields')->hide();
        // Reload the datatable
        $this->response->script("polr.home.reloadUserTables()");
        // Show a confirmation message
        $this->notify->info("User successfully created.", 'Success');

        return $this->response;
    }

    public function deleteUser($user_id)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        $user = UserHelper::getUserById($user_id, true);
        if(!$user)
        {
            $this->notify->error('User not found.', 'Error');
            return $this->response;
        }

        $user->delete();

        // Reload the datatable
        $this->response->script("polr.home.reloadUserTables()");
        // Show a confirmation message
        $this->notify->info("User successfully deleted.", 'Success');

        return $this->response;
    }

    public function showAPIInfo($user_id)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        $user = UserHelper::getUserById($user_id, true);
        if(!$user)
        {
            $this->notify->error('User not found.', 'Error');
            return $this->response;
        }
        if(!$user->active)
        {
            $this->notify->error('User not active.', 'Error');
            return $this->response;
        }

        $title = 'Edit User API Settings';
        $content = view('snippets.edit_user_api_info', ['user' => $user]);
        $buttons = [
            [
                'title' => 'Close',
                'class' => 'btn btn-danger btn-xs',
                'click' => 'close',
            ]
        ];

        $this->dialog->show($title, $content, $buttons);
        // Set event handlers on buttons
        $this->jq('#edit-user-api-active a.btn')->click($this->rq()->toggleAPIActive($user->id));
        $this->jq('#edit-user-api-key a.btn')->click($this->rq()->generateNewAPIKey($user->id, false));
        $this->jq('#edit-user-api-quota a.btn')->click($this->rq()->editAPIQuota($user->id,
            jq('#edit-user-api-quota input.api-quota')->val()));

        return $this->response;
    }
    
    public function toggleAPIActive($user_id)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        $user = UserHelper::getUserById($user_id);
        if(!$user)
        {
            $this->notify->error('User not found.', 'Error');
            return $this->response;
        }

        $current_status = $user->api_active;
        $new_status = ($current_status == 1) ? 0 : 1;

        $user->api_active = $new_status;
        $user->save();

        // Update the dialog with the new status
        $this->jq('#edit-user-api-active .status-display')->html(($new_status == 1) ? 'True' : 'False');
        // Show a confirmation message
        $status = ($new_status == 1) ? 'active' : 'inactive';
        $this->notify->info("API status of user {$user->username} successfully changed to $status.", 'Success');

        return $this->response;
    }

    public function editAPIQuota($user_id, $new_quota)
    {
        /**
         * If user is an admin, allow the user to edit the per minute API quota of
         * any user.
         */
    
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }
    
        $user = UserHelper::getUserById($user_id);
        if(!$user)
        {
            $this->notify->error('User not found.', 'Error');
            return $this->response;
        }
        
        $user->api_quota = $new_quota;
        $user->save();

        // Show a confirmation message
        $this->notify->info("Quota of user {$user->username} successfully changed to $new_quota.", 'Success');

        return $this->response;
    }

    public function setUserStatus($user_id, $status)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        $user = UserHelper::getUserById($user_id, true);
        if(!$user)
        {
            $this->notify->error('User not found.', 'Error');
            return $this->response;
        }

        $new_status = !($status) ? 0 : 1;

        $user->active = $new_status;
        $user->save();

        // Reload the datatable
        $this->response->script("polr.home.reloadUserTables()");
        // Show a confirmation message
        $status = ($new_status == 1) ? 'active' : 'inactive';
        $this->notify->info("Status of user {$user->username} successfully changed to $status.", 'Success');

        return $this->response;
    }

    public function changeUserRole($user_id, $role)
    {
        if(!$this->currIsAdmin())
        {
            $this->notify->error('User is not admin.', 'Error');
            return $this->response;
        }

        $user = UserHelper::getUserById($user_id, true);
        if(!$user)
        {
            $this->notify->error('User not found.', 'Error');
            return $this->response;
        }

        $user->role = $role;
        $user->save();

        // Reload the datatable
        $this->response->script("polr.home.reloadUserTables()");
        // Show a confirmation message
        if(!$role)
        {
            $role = 'default';
        }
        $this->notify->info("Role of user {$user->username} successfully changed to $role.", 'Success');

        return $this->response;
    }
}
