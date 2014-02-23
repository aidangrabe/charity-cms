<?php

class PostController extends BaseController {

    public function __construct() {
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->beforeFilter('auth', array(
            'only' => array(
                'getCreate',
                'postCreate'
        )));
    }

    private function charityNotFound() {
        return Redirect::to('/')
            ->with('message_error', Lang::get('charity.charity_not_found'));
    }

    private function pageNotFound() {
        return Redirect::to('/')
            ->with('message_error', Lang::get('page.page_not_found'));
    }

    private function permissionDenied() {
        return Redirect::to('/')
            ->with('message_error', Lang::get('strings.permission_denied'));
    }

    private function postNotFound() {
        return Redirect::to('/')
            ->with('message_error', Lang::get('post.post_not_found'));
    }

    private function viewNotFound() {
        return Redirect::to('/')
            ->with('message_error', Lang::get('strings.view_not_found'));
    }

    public function getCreate($page_id, $view_id = 0) {
        $page = Page::find($page_id);

        if ($page == null) return $this->pageNotFound();
        if (!Auth::user()->canPostTo($page)) return $this->permissionDenied();

        $view_id = $view_id == 0 ? $page->default_view_id : $view_id;
        $view = PostView::find($view_id);

        if ($view == null) return $this->viewNotFound();

        $formView = View::make("postViews.form", array(
            'page' => $page,
            'view' => $view
        ));

        $layout = View::make('layout._single_column');    
        $layout->content = $formView;
        return $layout;
    }

    public function getSingle($charity_name, $post_id) {
        $charity = Charity::where('name', '=', $charity_name)->first();

        // check if charity exists
        if ($charity == null) return $this->charityNotFound();

        // check if post exists
        $post = Post::find($post_id);
        if ($post == null) return $this->postNotFound();

        $pages = Page::where('charity_id', '=', $charity->charity_id)->get();

        return View::make('layout.charity._two_column', array(
            'charity' => $charity,
            'pages' => $pages,
            'post' => $post,
            'content' => View::make('charity.single_post', array(
                'post' => $post,
            ))
        ));
    }

    public function postCreate($page_id) {
        $page = Page::find($page_id);

        if ($page == null) return $this->pageNotFound();
        if (!Auth::user()->canPostTo($page)) return $this->permissionDenied();

        $view_id = Input::get('view_id');
        $view = PostView::find($view_id);

        if ($view == null) return $this->viewNotFound();

        // set the page to redirect to
        $charity = Charity::where('charity_id', '=', $page->charity_id)->first();
        $redirect = $charity == null
            ? "posts/create/{$page_id}"
            : "c/charity/{$charity->name}/{$page->page_id}";

        $postValidator = $view->makePostValidator();
        $validator = $postValidator->validate();
        if ($validator->passes()) {
            $data = $postValidator->onSuccess();

            Post::makeAndSave(Auth::user(), $page, $view, Input::get('title'), $data);

            return Redirect::to($redirect)
                ->with('message_success', Lang::get('postViews.success'));
        } else {
            return Redirect::to("posts/create/{$page_id}")
                ->with('message_error', Lang::get('postViews.error'))
                ->withErrors($validator)
                ->withInput();
        }
    }

}
