<?php
namespace Lagdo\Polr\Api\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Factories\UserFactory;
use App\Models\User;
use App\Helpers\CryptoHelper;
use Lagdo\Polr\Api\Helpers\UserHelper;
use Lagdo\Polr\Api\Helpers\ResponseHelper;

use Yajra\Datatables\Facades\Datatables;

class UserController extends Controller
{
    public function getUsers(Request $request)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to get users.', 401);
        }

        $users = User::select(['username', 'email', 'created_at', 'active',
            'api_key', 'api_active', 'api_quota', 'role', 'id']);
        $datatables = Datatables::of($users)->make(true);
        
        return ResponseHelper::make(json_decode($datatables->content()));
    }

    public function getUser(Request $request, $user_id)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to get users.', 401);
        }

        $validator = \Validator::make(['id' => $user_id], [
            'id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $user = UserHelper::getUserById($user_id, true);
        if (!$user)
        {
            return ResponseHelper::make('NOT_FOUND', 'User not found.', 404);
        }
        
        return ResponseHelper::make($user);
    }

    public function updateUser(Request $request, $user_id)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to edit users.', 401);
        }

        // At least one of the link properties must be present in the input data
        $request->merge(['id' => $user_id]);
        $validator = \Validator::make($request->all(), [
            'id' => 'required|numeric',
        	'role' => 'required_without_all:status|between:1,16|alpha_num',
            'status' => 'required_without_all:role|in:enable,disable,toggle',
        ]);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $user = UserHelper::getUserById($user_id, true);
        if (!$user)
        {
            return ResponseHelper::make('NOT_FOUND', 'User not found.', 404);
        }

        if($request->has('role'))
        {
            $role = trim($request->input('role'));
            if($role == 'default')
            {
                $role = '';
            }
            $user->role = $role;
        }
        if($request->has('status'))
        {
            $status = $request->input('status');
            switch($status)
            {
            case 'enable':
                $user->active = 1;
                break;
            case 'disable':
                $user->active = 0;
                break;
            case 'toggle':
            default:
                $user->active = ($user->active ? 0 : 1);
                break;
            }
        }

        $user->save();

        return ResponseHelper::make($user);
    }

    public function updateApi(Request $request, $user_id)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to edit users.', 401);
        }

        // At least one of the link properties must be present in the input data
        $request->merge(['id' => $user_id]);
        $validator = \Validator::make($request->all(), [
            'id' => 'required|numeric',
        	'quota' => 'required_without_all:status|numeric',
            'status' => 'required_without_all:quota|in:enable,disable,toggle',
        ]);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $user = UserHelper::getUserById($user_id, true);
        if (!$user)
        {
            return ResponseHelper::make('NOT_FOUND', 'User not found.', 404);
        }

        if($request->has('quota'))
        {
            $user->api_quota = $request->input('quota');
        }
        if($request->has('status'))
        {
            $status = $request->input('status');
            switch($status)
            {
                case 'enable':
                    $user->api_active = 1;
                    break;
                case 'disable':
                    $user->api_active = 0;
                    break;
                case 'toggle':
                default:
                    $user->api_active = ($user->api_active ? 0 : 1);
                    break;
            }
        }

        $user->save();

        return ResponseHelper::make($user);
    }

    public function generateNewKey(Request $request, $user_id)
    {
        /**
         * If user is an admin, allow resetting of any API key
         *
         * If user is not an admin, allow resetting of own key only, and only if
         * API is enabled for the account.
         * @return string; new API key
         */

        $validator = \Validator::make(['id' => $user_id], [
            'id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $user = UserHelper::getUserById($user_id, true);
        if (!$user)
        {
            return ResponseHelper::make('NOT_FOUND', 'User not found.', 404);
        }

        if(!UserHelper::userIsAdmin($request->user))
        {
            // If user is attempting to reset another user's API key, ensure they are an admin
            if($user->username != $request->user->username)
            {
                return ResponseHelper::make('ACCESS_DENIED',
                    'You do not have permission to generate API key for another user.', 401);
            }
            // User is attempting to reset own key, ensure that user is permitted to access the API
            if(!$user->api_active)
            {
                return ResponseHelper::make('ACCESS_DENIED',
                    'You do not have permission generate API key without access to the API.', 401);
            }
        }

        $new_api_key = CryptoHelper::generateRandomHex(env('_API_KEY_LENGTH'));
        $user->api_key = $new_api_key;
        $user->save();

        return ResponseHelper::make($user);
    }

    /*public function addNewUser(Request $request)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to create users.', 401);
        }

        $ip = $request->input('ip');
        $username = $request->input('username');
        $user_password = $request->input('user_password');
        $user_email = $request->input('user_email');
        $user_role = $request->input('user_role');

        UserFactory::createUser($username, $user_email, $user_password, 1, $ip, false, 0, $user_role);

        return ResponseHelper::make();
    }

    public function deleteUser(Request $request, $user_id)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to delete users.', 401);
        }

        $validator = \Validator::make(['id' => $user_id], [
            'id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $user = UserHelper::getUserById($user_id, true);
        if (!$user)
        {
            return ResponseHelper::make('NOT_FOUND', 'User not found.', 404);
        }

        $user->delete();

        return ResponseHelper::make();
    }*/
}
