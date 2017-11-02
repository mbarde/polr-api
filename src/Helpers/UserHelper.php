<?php
namespace Lagdo\Polr\Api\Helpers;

use App\Models\User;

class UserHelper
{
    public static $username = '';

    public static $USER_ROLES = [
        'admin'    => 'admin',
        'default'  => '',
    ];

    public static function userIsAdmin($user)
    {
        return ($user->role == self::$USER_ROLES['admin']);
    }

    public static function userIsAnonymous($user)
    {
        return ($user->anonymous);
    }
}
