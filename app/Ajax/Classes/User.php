<?php

namespace Jaxon\App;

use Hash;
use App\Helpers\UserHelper;
use App\Helpers\CryptoHelper;
use Jaxon\Sentry\Armada as JaxonClass;

class User extends JaxonClass
{
    protected function currIsAdmin()
    {
        $role = session('role');
        return ($role == 'admin');
    }

    public function generateNewAPIKey($user_id)
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

        if (!$user)
        {
            $this->dialog->error('User not found.', 'Error');
            return $this->response;
        }

        if ($user != $user_requesting)
        {
            // if user is attempting to reset another user's API key,
            // ensure they are an admin
            if (!$this->currIsAdmin())
            {
                $this->dialog->error('User is not admin.', 'Error');
                return $this->response;
            }
        }
        else
        {
            // user is attempting to reset own key
            // ensure that user is permitted to access the API
            $user_api_enabled = $user->api_active;
            if (!$user_api_enabled)
            {
                // if the user does not have API access toggled on,
                // allow only if user is an admin
                if (!$this->currIsAdmin())
                {
                    $this->dialog->error('User is not admin.', 'Error');
                    return $this->response;
                }
            }
        }

        $new_api_key = CryptoHelper::generateRandomHex(env('_API_KEY_LENGTH'));
        $user->api_key = $new_api_key;
        $user->save();

        // Show the new API key in the form
        $this->response->assign('api-key-value', 'value', $user->api_key);

        // Show a confirmation message
        $this->dialog->info("New API key successfully generated.", 'Success');

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
            $this->dialog->error('Current password is invalid. Try again.', 'Error');
            return $this->response;
        }

        // Credentials are correct
        $user = UserHelper::getUserByUsername($username);
        $user->password = Hash::make($new_password);
        $user->save();

        // Clear the form
        $this->jq('#change-password-form .password-box')->val('');

        // Show a confirmation message
        $this->dialog->info("Password changed successfully.", 'Success');

        return $this->response;
    }
}
