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

    public static function getUserById($user_id, $inactive = false)
    {
        $user = User::select(['username', 'email', 'created_at', 'active',
            'api_key', 'api_active', 'api_quota', 'role', 'id'])
            ->where('id', $user_id);
        if (!$inactive)
        {
            $user = $user->where('active', 1);
        }
        return $user->first();
    }
}
