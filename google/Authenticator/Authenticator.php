<?php

class GoogleAuthenticator extends Authenticator {
	public static function get_name() {
		return 'Google';
	}
	
	public static function get_login_form(Controller $controller) {
		$form = new GoogleLoginForm($controller, 'LoginForm');
		singleton('GoogleAuthenticator')->extend('updateLoginForm', $form);
		return $form;
	}
	
	public static function authenticate($data, Form $form = null) {
		singleton('GoogleAuthenticator')->extend('onAuthenticate', $data, $form);
		return singleton('GoogleCallback')->loginUser();
	}
}
