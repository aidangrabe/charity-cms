<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
});


App::after(function($request, $response)
{

});

/*
|--------------------------------------------------------------------------
| 404 Errors
|--------------------------------------------------------------------------
|
| Handle the 404 errors
|
*/
App::missing(function($exception) {
    return Response::view('errors.missing', array('url' => Request::url()), 404);
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest()) {
        $msg = Cms\App\Messages\FlashMessageFactory::makeWarningMessage(
            Lang::get('strings.login_required'), 'msg');
        return Redirect::guest('users/login')
            ->with('message', $msg);
    }
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function() {

	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});

/*
|--------------------------------------------------------------------------
| Check if PHP ini upload_max_filesize is exceeded
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/
function upload_max_redirect() {
    $postMax = ini_get('post_max_size');
    $uploadMax = ini_get('upload_max_filesize');
    $redirect = Redirect::back()
        ->withInput()
        ->with('message_error', "Uploads must be smaller than {$uploadMax}");
}

Route::filter('upload.max', function() {
    // try to catch error where the upload file size is greater than PHP ini
    // setting
    $size_errors = array(
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE
    );
    foreach ($_FILES as $file) {
        if (in_array($file['error'], $size_errors)) {
            return upload_max_redirect();
        }
    }

    if (empty($_POST) && empty($_FILES) && isset($_SERVER['REQUEST_METHOD'])
            && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {

        return upload_max_redirect();
    }

});
