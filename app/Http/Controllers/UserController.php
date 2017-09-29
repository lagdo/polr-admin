<?php

namespace App\Http\Controllers;

use Log;
use Mail;
use Hash;
use Auth;
use App\Models\User;
use Illuminate\Http\Request;

use App\Helpers\CryptoHelper;
use App\Helpers\UserHelper;

use App\Factories\UserFactory;

class UserController extends Controller
{
    /**
     * Show pages related to the user control panel.
     *
     * @return Response
     */
    public function showLogin(Request $request)
    {
        return view('user.login');
    }

    public function displaySignupPage(Request $request)
    {
        return view('user.signup');
    }

    public function showLostPassword(Request $request)
    {
        return view('user.password.lost');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->forget('username');
        return redirect()->route('showLogin');
    }

    public function postLogin(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $credentials_valid = UserHelper::checkCredentials($username, $password);

        if($credentials_valid != false)
        {
            Auth::login(UserHelper::getUserByUsername($username), false);
            // log user in
            $role = $credentials_valid['role'];
            $request->session()->put('username', $username);
            $request->session()->put('role', $role);

            return redirect()->route('index');
        }
        else
        {
            return redirect()->route('showLogin')->with('error', 'Invalid password or inactivated account. Try again.');
        }
    }

    public function postSignup(Request $request)
    {
        if(env('POLR_ALLOW_ACCT_CREATION') == false)
        {
            return redirect(route('index'))->with('error', 'Sorry, but registration is disabled.');
        }

        if(env('POLR_ACCT_CREATION_RECAPTCHA'))
        {
            // Verify reCAPTCHA if setting is enabled
            $gRecaptchaResponse = $request->input('g-recaptcha-response');

            $recaptcha = new \ReCaptcha\ReCaptcha(env('POLR_RECAPTCHA_SECRET_KEY'));
            $recaptcha_resp = $recaptcha->verify($gRecaptchaResponse, $request->ip());

            if(!$recaptcha_resp->isSuccess())
            {
                return redirect(route('showSignup'))->with('error', 'You must complete the reCAPTCHA to register.');
            }
        }

        // Validate signup form data
        $this->validate($request, [
            'username' => 'required|alpha_dash',
            'password' => 'required',
            'email' => 'required|email'
        ]);

        $username = $request->input('username');
        $password = $request->input('password');
        $email = $request->input('email');

        if(env('SETTING_RESTRICT_EMAIL_DOMAIN'))
        {
            $email_domain = explode('@', $email)[1];
            $permitted_email_domains = explode(',', env('SETTING_ALLOWED_EMAIL_DOMAINS'));

            if(!in_array($email_domain, $permitted_email_domains))
            {
                return redirect(route('showSignup'))->with('error', 'Sorry, your email\'s domain is not permitted to create new accounts.');
            }
        }

        $ip = $request->ip();

        $user_exists = UserHelper::userExists($username);
        $email_exists = UserHelper::emailExists($email);

        if($user_exists || $email_exists)
        {
            // if user or email email
            return redirect(route('showSignup'))->with('error', 'Sorry, your email or username already exists. Try again.');
        }

        $acct_activation_needed = env('POLR_ACCT_ACTIVATION');

        if($acct_activation_needed == false)
        {
            // if no activation is necessary
            $active = 1;
            $response = redirect(route('showLogin'))->with('success', 'Thanks for signing up! You may now log in.');
        }
        else
        {
            // email activation is necessary
            $response = redirect(route('showLogin'))->with('success', 'Thanks for signing up! Please confirm your email to continue.');
            $active = 0;
        }

        $api_active = false;
        $api_key = null;

        if(env('SETTING_AUTO_API'))
        {
            // if automatic API key assignment is on
            $api_active = 1;
            $api_key = CryptoHelper::generateRandomHex(env('_API_KEY_LENGTH'));
        }

        $user = UserFactory::createUser($username, $email, $password, $active, $ip, $api_key, $api_active);

        if($acct_activation_needed)
        {
            Mail::send('emails.activation', [
                'username' => $username, 'recoveryKey' => $user->recoveryKey, 'ip' => $ip
            ], function ($m) use ($user) {
                $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));

                $m->to($user->email, $user->username)->subject(env('APP_NAME') . ' account activation');
            });
        }

        return $response;
    }

    public function postLostPassword(Request $request)
    {
        if(!env('SETTING_PASSWORD_RECOV'))
        {
            return redirect(route('index'))->with('error', 'Password recovery is disabled.');
        }

        $email = $request->input('email');
        $ip = $request->ip();
        $user = UserHelper::getUserByEmail($email);

        if(!$user)
        {
            return redirect(route('lost_password'))->with('error', 'Email is not associated with a user.');
        }

        $recoveryKey = UserHelper::resetRecoveryKey($user->username);

        Mail::send('emails.lost_password', [
            'username' => $user->username, 'recoveryKey' => $recoveryKey, 'ip' => $ip
        ], function ($m) use ($user) {
            $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));

            $m->to($user->email, $user->username)->subject(env('APP_NAME') . ' Password Reset');
        });

        return redirect(route('index'))->with('success', 'Password reset email sent. Check your inbox for details.');
    }

    public function performActivation(Request $request, $username, $recoveryKey)
    {
        $user = UserHelper::getUserByUsername($username, true);

        if(UserHelper::userResetKeyCorrect($username, $recoveryKey, true))
        {
            // Key is correct
            // Activate account and reset recovery key
            $user->active = 1;
            $user->save();

            UserHelper::resetRecoveryKey($username);
            return redirect(route('showLogin'))->with('success', 'Account activated. You may now login.');
        }
        else
        {
            return redirect(route('index'))->with('error', 'Username or activation key incorrect.');
        }
    }

    public function resetPassword(Request $request, $username, $recoveryKey)
    {
        $new_password = $request->input('new_password');
        $user = UserHelper::getUserByUsername($username);

        if(UserHelper::userResetKeyCorrect($username, $recoveryKey))
        {
            if(!$new_password)
            {
                return view('user.password.reset');
            }

            // Key is correct
            // Reset password
            $user->password = Hash::make($new_password);
            $user->save();

            UserHelper::resetRecoveryKey($username);
            return redirect(route('showLogin'))->with('success', 'Password reset. You may now login.');
        }
        else
        {
            return redirect(route('index'))->with('error', 'Username or reset key incorrect.');
        }
    }
}
