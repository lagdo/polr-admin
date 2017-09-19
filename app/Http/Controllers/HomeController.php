<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

use Log;
use App\Models\Link;
use App\Models\User;
use App\Helpers\UserHelper;
use Jaxon\Laravel\Jaxon;

class HomeController extends Controller
{
    public function index(Request $request, Jaxon $jaxon)
    {
        // Register Jaxon classes
        $jaxon->register();

        $username = session('username');
        $role = session('role');

        $user = UserHelper::getUserByUsername($username);

        if (!$user) {
            return redirect(route('showLogin'))->with('error', 'Invalid or disabled account.');
        }

        return view('home', [
            'role' => $role,
            'admin_role' => UserHelper::$USER_ROLES['admin'],
            'user_roles' => UserHelper::$USER_ROLES,
            'api_key' => $user->api_key,
            'api_active' => $user->api_active,
            'api_quota' => $user->api_quota,
            'user_id' => $user->id,
            'jaxonCss' => $jaxon->css(),
            'jaxonJs' => $jaxon->js(),
            'jaxonScript' => $jaxon->script(),
            'jaxonUser' => $jaxon->request(\Jaxon\App\User::class), // Jaxon request to the \Jaxon\App\User class
        ]);
    }
}
