<?php

require_once FACEBOOK_PATH . '/lib/facebook.php';

class FacebookCallback extends ThirdPartyAuthController {
	
	protected static $facebook_secret = null;
	protected static $facebook_id = null;
	private static $email_fallback = false;
	
	public static function set_facebook_secret($secret) {
		self::$facebook_secret = $secret;
	}
	
	public static function set_facebook_id($id) {
		self::$facebook_id = $id;
	}
	
	public static function get_email_fallback() {
		return self::$email_fallback;
	}
	
	public static function set_email_fallback($val) {
		self::$email_fallback = (bool)$val;
	}
	
	public static function get_current_user() {
		if(self::$facebook_secret == null || self::$facebook_id == null) {
			user_error('Cannot instigate a FacebookCallback object without an application secret and id', E_USER_ERROR);
		}
		$facebook = new Facebook(array(
			'appId' => self::$facebook_id,
			'secret' => self::$facebook_secret
		));
		$user = $facebook->getUser();
		if($user) {
			try {
				$user_profile = $facebook->api('/me');
				if(isset($user_profile->error)) {
					$user = null;
				}
			} catch(FacebookApiException $e) {
				$user = null;
			}
		}
		return $user ? $user_profile : null;
	}
	
	public static $allowed_actions = array(
		'Connect',
		'Login',
		'FinishFacebook',
		'FacebookConnect',
		'RemoveFacebook',
	);
	
	public function __construct() {
		if(self::$facebook_secret == null || self::$facebook_id == null) {
			user_error('Cannot instigate a FacebookCallback object without an application secret and id', E_USER_ERROR);
		}
		parent::__construct();
	}
	
	public function FinishFacebook($request) {
		$token = SecurityToken::inst();
		if(!$token->checkRequest($request)) return $this->httpError(400);
		if($this->CurrentMember()->FacebookID) {
			return '<script type="text/javascript">//<![CDATA[
			opener.FacebookResponse(' . Convert::raw2json(array(
				'name' => $this->CurrentMember()->FacebookName,
				'removeLink' => $token->addToUrl($this->Link('RemoveFacebook')),
			)) . ');
			window.close();
			//]]></script>';
		} else {
			return '<script type="text/javascript">window.close();</script>';
		}
	}
	
	public function FacebookConnect() {
		return $this->connectUser($this->Link('FinishFacebook'));
	}
	
	public function RemoveFacebook($request) {
		$token = SecurityToken::inst();
		if(!$token->checkRequest($request)) return $this->httpError(400);
		$m = $this->CurrentMember();
		$m->FacebookID = $m->FacebookName = null;
		$m->write();
	}
	
	public function connectUser($returnTo = '', Array $extra = array()) {
		$facebook = new Facebook(array(
			'appId' => self::$facebook_id,
			'secret' => self::$facebook_secret
		));
		$user = $facebook->getUser();
		if($user) {
			try {
				$user_profile = $facebook->api('/me');
				if(isset($user_profile->error)) {
					$user = null;
				}
			} catch(FacebookApiException $e) {
				$user = null;
			}
		}
		$token = SecurityToken::inst();
		if($returnTo) {
			$returnTo = $token->addToUrl($returnTo); 
			$returnTo = urlencode($returnTo);
		}
		$callback = $this->AbsoluteLink('Connect?ret=' . $returnTo);
		$callback = $token->addToUrl($callback);
		
		if($user && empty($extra)) {
			return self::curr()->redirect($callback);
		} else {
			return self::curr()->redirect($facebook->getLoginUrl(array(
				'redirect_uri' => $callback,
			) + $extra));
		}
	}
	
	public function loginUser(Array $extra = array(), $return = false) {
		$facebook = new Facebook(array(
			'appId' => self::$facebook_id,
			'secret' => self::$facebook_secret
		));
		$user = $facebook->getUser();
		if($user) {
			try {
				$user_profile = $facebook->api('/me');
				if(isset($user_profile->error)) {
					$user = null;
				}
			} catch(FacebookApiException $e) {
				$user = null;
			}
		}
		$token = SecurityToken::inst();
		if($return) {
			$return = $token->addToUrl($return);
			$return = urlencode($return);
		}
		$callback = $this->AbsoluteLink('Login' . ($return ? '?ret=' . $return : ''));
		$callback = $token->addToUrl($callback);
		if(self::$email_fallback) {
			if(!$user || !isset($user_profile->email)) {
				$scope = empty($extra['scope']) ? '' : $extra['scope'];
				if(strpos($scope, 'email') === false) {
					if($scope) $scope .= ',';
					$scope .= 'email';
				}
				$extra['scope'] = $scope;
			}
		}
		if($user && empty($extra)) {
			return self::curr()->redirect($callback);
		} else {
			return self::curr()->redirect($facebook->getLoginUrl(array(
				'redirect_uri' => $callback,
			) + $extra));
		}
	}
	
	public function index() {
		$this->httpError(403);
	}
	
	public function Login(SS_HTTPRequest $req) {
		$token = SecurityToken::inst();
		if(!$token->checkRequest($req)) return $this->httpError(400);
		if($req->getVar('ret')) {
			$facebook = new Facebook(array(
				'appId' => self::$facebook_id,
				'secret' => self::$facebook_secret
			));
			$user = $facebook->getUser();
			return $this->redirect($req->getVar('ret'));
		}
		if($req->getVar('denied') || $req->getVar('error_reason') == 'user_denied') {
			Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.message', 'Login cancelled.');
			Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.type', 'error');
			return $this->redirect('Security/login#FacebookLoginForm_LoginForm_tab');
		}
		if(!(Member::currentUser() && Member::logged_in_session_exists())) {
			$facebook = new Facebook(array(
				'appId' => self::$facebook_id,
				'secret' => self::$facebook_secret
			));
			$user = $facebook->getUser();
			if($user) {
				try {
					$data = $facebook->api('/me');
					if(isset($data->error)) {
						Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.message', 'Login error: ' . $data->error->message);
						Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.type', 'error');
						return $this->redirect('Security/login#FacebookLoginForm_LoginForm_tab');
					}
				} catch(FacebookApiException $e) {
					Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.message', 'Login error: ' . $e->message);
					Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.type', 'error');
					return $this->redirect('Security/login#FacebookLoginForm_LoginForm_tab');
				}
			}
			if(!$user) {
				Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.message', 'Login cancelled.');
				Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.type', 'error');
				return $this->redirect('Security/login#FacebookLoginForm_LoginForm_tab');
			}
			if(!is_numeric($user)) {
				Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.message', 'Invalid user id received from Facebook.');
				Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.type', 'error');
				return $this->redirect('Security/login#FacebookLoginForm_LoginForm_tab');
			}
			$u = DataObject::get_one('Member', '"FacebookID" = \'' . Convert::raw2sql($user) . '\'');
			if((!$u || !$u->exists()) && !isset($data->email)) {
				Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.message', 'No one found for Facebook user ' . $data->name . '.');
				Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.type', 'error');
				return $this->redirect('Security/login#FacebookLoginForm_LoginForm_tab');
			} elseif(!$u || !$u->exists()) {
				$e = Convert::raw2sql($data->email);
				$u = DataObject::get_one('Member', '"Email" = \'' . $e . '\'');
				if(!$u || !$u->exists()) {
					if (self::$create_new_user) {
						$u = $this->newUser($data->first_name, $data->last_name, $data->email);
					} else {
						Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.message', 'No one found for Facebook user ' . $data->name . '.');
						Session::set('FormInfo.FacebookLoginForm_LoginForm.formError.type', 'error');
						return $this->redirect('Security/login#FacebookLoginForm_LoginForm_tab');
					}
				}
			}
			
			if($u->FacebookName != $data->name) {
				$u->FacebookName = $data->name;
				$u->FacebookID = $user;
				$u->write();
			}
			$u->login(Session::get('SessionForms.FacebookLoginForm.Remember'));
		}
		Session::clear('SessionForms.FacebookLoginForm.Remember');
		$backURL = Session::get('BackURL');
		Session::clear('BackURL');
		return $this->redirect($backURL);
	}
	
	public function Connect(SS_HTTPRequest $req) {
		$token = SecurityToken::inst();
		if(!$token->checkRequest($req)) return $this->httpError(400);
		if($req->getVars() && !$req->getVar('error')) {
			$facebook = new Facebook(array(
				'appId' => self::$facebook_id,
				'secret' => self::$facebook_secret
			));
			$user = $facebook->getUser();
			if($user) {
				try {
					$data = $facebook->api('/me');
					if(isset($data->error)) {
						$user = null;
					}
				} catch(FacebookApiException $e) {
					$user = null;
				}
			}
			if($user && $m = $this->CurrentMember()) {
				$m->FacebookID = $data->id;
				$m->FacebookName = $data->name;
				$m->write();
			}
		}
		$ret = $req->getVar('ret');
		if($ret) {
			return $this->redirect($ret);
		} else {
			return $this->redirect(Director::baseURL());
		}
	}
	
	public function AbsoluteLink($action = null) {
		return Director::absoluteURL($this->Link($action));
	}
	
	public function Link($action = null) {
		return self::join_links('FacebookCallback', $action);
	}
}
