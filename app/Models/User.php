<?php
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    protected $table = 'users';

    /**
     * Overrides the method to ignore the remember token.
     */
    public function setAttribute($key, $value)
    {
        // $isRememberTokenAttribute = ($key == $this->getRememberTokenName());
        // if (!$isRememberTokenAttribute)
        if($key != $this->getRememberTokenName())
        {
            parent::setAttribute($key, $value);
        }
    }
}
