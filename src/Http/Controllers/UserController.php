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
    /**
     * @api {get} /users Get Users
     * @apiDescription Fetch a paginated list of users. The input parameters are those of the Datatables library.
     * @apiName GetUsers
     * @apiGroup Users
     *
     * @apiParam {Integer} [draw]           The draw option.
     * @apiParam {Object} [columns]         The table columns.
     * @apiParam {Object} [order]           The data ordering.
     * @apiParam {Integer} [start]          The data offset.
     * @apiParam {Integer} [length]         The data count.
     * @apiParam {Object} [search]          The search options.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Object} result          The user list.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to list users.
     */
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

    /**
     * @api {get} /users/:id Get a User
     * @apiDescription Get the user with the given id
     * @apiName GetUser
     * @apiGroup Users
     *
     * @apiParam {String} key               The user API key.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Object} result          The user data.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to get users.
     * @apiError (Error 404) {Object} NotFound               Unable to find a user with the given id.
     * @apiError (Error 400) {Object} MissingParameters      There is a missing or invalid parameter.
     */
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

        $user = UserHelper::getUserById($user_id);
        if (!$user)
        {
            return ResponseHelper::make('NOT_FOUND', 'User not found.', 404);
        }

        return ResponseHelper::make($user);
    }

    /**
     * @api {put} /users/:id Update a user
     * @apiDescription Update the user with the given id.
     * @apiName UpdateUser
     * @apiGroup Users
     *
     * @apiParam {String} key               The user API key.
     * @apiParam {String} [role]            The new role.
     * @apiParam {String} [status]          The user status change: enable, disable or toggle.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Object} result          The updated user data.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to edit the user.
     * @apiError (Error 404) {Object} NotFound               Unable to find a user with the given id.
     * @apiError (Error 400) {Object} MissingParameters      There is a missing or invalid parameter.
     */
    public function updateUser(Request $request, $user_id)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to edit users.', 401);
        }

        // At least one of the user properties must be present in the input data
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

        $user = UserHelper::getUserById($user_id);
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

    /**
     * @api {put} /users/:id/api Change API Settings
     * @apiDescription Change the API Settings of the user with the given id.
     * @apiName ChangeAPI
     * @apiGroup Users
     *
     * @apiParam {String} key               The user API key.
     * @apiParam {String} [quota]           The new API quota.
     * @apiParam {String} [status]          The API access change: enable, disable or toggle.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Object} result          The updated user data.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to edit the user.
     * @apiError (Error 404) {Object} NotFound               Unable to find a user with the given id.
     * @apiError (Error 400) {Object} MissingParameters      There is a missing or invalid parameter.
     */
    public function updateApi(Request $request, $user_id)
    {
        if(!UserHelper::userIsAdmin($request->user))
        {
            return ResponseHelper::make('ACCESS_DENIED', 'You do not have permission to edit users.', 401);
        }

        // At least one of the user properties must be present in the input data
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

        $user = UserHelper::getUserById($user_id);
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

    /**
     * @api {post} /users/:id/api Generate Key
     * @apiDescription Generate a new API access key for the user with the given id.
     * @apiName GenerateKey
     * @apiGroup Users
     *
     * @apiParam {String} key               The user API key.
     *
     * @apiSuccess {String} message         The response message.
     * @apiSuccess {Object} settings        The Polr instance config options.
     * @apiSuccess {Mixed} result           The updated user data.
     *
     * @apiError (Error 401) {Object} AccessDenied           The user does not have permission to edit the user.
     * @apiError (Error 404) {Object} NotFound               Unable to find a user with the given id.
     * @apiError (Error 400) {Object} MissingParameters      There is a missing or invalid parameter.
     */
    public function generateNewKey(Request $request, $user_id)
    {
        $validator = \Validator::make(['id' => $user_id], [
            'id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return ResponseHelper::make('MISSING_PARAMETERS', 'Invalid or missing parameters.', 400);
        }

        $user = UserHelper::getUserById($user_id);
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

        $user = UserHelper::getUserById($user_id);
        if (!$user)
        {
            return ResponseHelper::make('NOT_FOUND', 'User not found.', 404);
        }

        $user->delete();

        return ResponseHelper::make();
    }*/
}
