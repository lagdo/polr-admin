<?php

namespace Jaxon\Helpers;

/**
 * Session.php - Session helpers for Jaxon classes
 *
 * This trait provides common session related functions for Jaxon classes.
 */

trait Session
{
    /**
     * Check if there is a logged in user
     *
     * @return boolean
     */
    protected function isLoggedIn() {
        $username = session('username');
        return (isset($username));
    }

    /**
     * Check if the current user is admin
     *
     * @return boolean
     */
    protected function currIsAdmin()
    {
        $role = session('role');
        return ($role == 'admin');
    }
}