<?php
namespace Lagdo\Polr\Api\Helpers;

use App\Models\User;

class UserHelper
{
    /**
     * The name of the user 
     * @var string
     */
    public static $username = '';

    /**
     * The user roles
     * @var array
     */
    public static $USER_ROLES = [
        'admin'    => 'admin',
        'default'  => '',
    ];

    /**
     * Check if the user is admin
     *
     * @param User $user
     *
     * @return boolean
     */
    public static function userIsAdmin($user)
    {
        return ($user->role == self::$USER_ROLES['admin']);
    }

    /**
     * Check if the user is anonymous
     *
     * @param User $user
     *
     * @return boolean
     */
    public static function userIsAnonymous($user)
    {
        return ($user->anonymous);
    }

    /**
     * Get a user by id
     *
     * @param integer $user_id      The user id
     *
     * @return User
     */
    public static function getUserById($user_id)
    {
        return User::select(['username', 'email', 'created_at', 'active',
            'api_key', 'api_active', 'api_quota', 'role', 'id'])
            ->where('id', $user_id)->first();
    }
}
