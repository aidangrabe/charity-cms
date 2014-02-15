<?php

use \Cms\App\Sanitiser;

class UserController extends BaseController {

    protected $layout = 'layout._single_column';

    public function __construct() {
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->beforeFilter('auth', array(
            'only' => array(
                'getDashboard'
            )
        ));
    }

    public function getAll() {
        $users = User::limit(25)->get();
        $this->layout->content = View::make('users.all');
        $this->layout->content->users = $users;
        $this->layout->content->oauth = OAuth::get();
    }

    public function getDashboard() {
        $this->layout->content = View::make('users.dashboard');
        $charities = Auth::user()->getCharities();
        $this->layout->content->myCharities = $charities;
    }

    public function getLogin() {
        // no need to show a login page if already logged
        if (Auth::check()) {
            return Redirect::to('users/dashboard');
        }

        $this->layout->content = View::make('users.login');
    }

    public function getLogout() {
        Auth::logout();
        return Redirect::to('users/login')
            ->with('message_success', 'You were successfully logged out');
    }

    public function getRegister() {
        if (Auth::check()) {
            return Redirect::to('users/dashboard')
                ->with('message_error', 'You already have an account!');
        }

        $this->layout->content = View::make('users.register');
    }

    public function postCreate() {
        $sanitiser = Sanitiser::make(Input::all())
            ->guard(array('password', 'password_confirmation'))
            ->sanitise();
        Input::merge($sanitiser->getAll());

        $validator = User::validate(Input::all());

        if ($validator->passes()) {
            // make a new user from the input received
            $user = User::make(Input::all());
            $user->save();

            return Redirect::to('users/login')
                ->with('message_success', Lang::get('forms.register_success'));
        } else {
            return Redirect::to('users/register')
                ->with('message_error', Lang::get('forms.errors_occurred'))
                ->withErrors($validator)
                ->withInput();
        }
    }

    public function postSignin() {
        if (Auth::attempt(array(
                'email' => Input::get('email'),
                'password' => Input::get('password')
                ))) {
            return Redirect::to('users/dashboard')
                ->with('message_success', Lang::get('strings.login_successful'));
        } else {
            return Redirect::to('users/login')
                ->with('message_error', Lang::get('strings.login_failed'))
                ->withInput();
        }
    }

}
