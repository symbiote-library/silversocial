<?php

class FacebookAuthenticator extends Authenticator {
	public static function get_name() {
		return 'Facebook';
	}
	
	public static function get_login_form(Controller $controller) {
		$form = new FacebookLoginForm($controller, 'LoginForm');
		singleton('FacebookAuthenticator')->extend('updateLoginForm', $form);
		return $form;
	}
	
	public static function authenticate($data, Form $form = null) {
		singleton('FacebookAuthenticator')->extend('onAuthenticate', $data, $form);
		return singleton('FacebookCallback')->loginUser();
	}
}
