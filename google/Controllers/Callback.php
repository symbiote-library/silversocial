<?php

class GoogleCallback extends ThirdPartyAuthController {
	
	public static $allowed_actions = array(
		'Connect',
		'Login',
		'GoogleConnect',
		'FinishGoogle',
		'RemoveGoogle',
	);
	
	public function FinishGoogle($request) {
		$token = SecurityToken::inst();
		if(!$token->checkRequest($request)) return $this->httpError(400);
		if($this->CurrentMember()->GoogleIdentity) {
			return '<script type="text/javascript">//<![CDATA[
			opener.GoogleResponse(' . Convert::raw2json(array(
				'name' => $this->CurrentMember()->GoogleName,
				'removeLink' => $token->addToUrl($this->Link('RemoveGoogle')),
			)) . ');
			window.close();
			//]]></script>';
		} else {
			return '<script type="text/javascript">window.close();</script>';
		}
	}
	
	public function GoogleConnect() {
		return $this->connectUser($this->Link('FinishGoogle'));
	}
	
	public function RemoveGoogle($request) {
		$token = SecurityToken::inst();
		if(!$token->checkRequest($request)) return $this->httpError(400);
		$m = $this->CurrentMember();
		$m->GoogleIdentity = $m->GoogleName = null;
		$m->write();
	}
	
	protected function getDiscoveryUrl($backURL) {
		$url = 'https://www.google.com/accounts/o8/id';
		
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array( 'Accept: application/xrds+xml')
		));
		
		$xml = curl_exec($ch);
		
		$xml = simplexml_load_string($xml);
		$discovery = "" . $xml->XRD->Service->URI;
		
		curl_close($ch);
		
		$params = array(
			'openid.mode' => 'checkid_setup',
			'openid.ns' => 'http://specs.openid.net/auth/2.0',
			'openid.return_to' => $backURL,
			'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
			'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
			'openid.ns.ax' => 'http://openid.net/srv/ax/1.0',
			'openid.ax.mode' => 'fetch_request',
			'openid.ax.required' => 'email,firstname,lastname',
			'openid.ax.type.email' => 'http://axschema.org/contact/email',
			'openid.ax.type.firstname' => 'http://axschema.org/namePerson/first',
			'openid.ax.type.lastname' => 'http://axschema.org/namePerson/last',
			'openid.realm' => Director::absoluteBaseURL(),
		);
		
		$serialised = array();
		
		foreach ($params as $param => $val) {
			$serialised[] = "$param=$val";
		}
		
		return $discovery . '?' . implode('&', $serialised);
	}
	
	protected function validateCallback($params) {
		$params2 = array();
		$signed = explode(',', $params['openid_signed']);
		
		foreach ($signed as $key) {
			$val = $params['openid_' . str_replace('.', '_', $key)];
		    $params2['openid.' . $key] = $val;
		}
		$params2['openid.mode'] = 'check_authentication';
		$params2['openid.sig'] = $params['openid_sig'];
		$params2['openid.signed'] = $params['openid_signed'];
		
		if(strpos($params['openid_op_endpoint'], 'https://www.google.com/accounts') === 0) {
			$ch = curl_init($params['openid_op_endpoint']);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params2);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			$response = (curl_exec($ch));
			$valid = strpos($response, 'is_valid:true') !== false;
		} else {
			$valid = false;
		}
		return $valid;
	}
	
	public function connectUser($returnTo = '') {
		$token = SecurityToken::inst();
		if($returnTo) {
			$returnTo = $token->addToUrl($returnTo); 
			$returnTo = urlencode($returnTo);
		}
		$callback = $this->AbsoluteLink('Connect?ret=' . $returnTo);
		$callback = $token->addToUrl($callback);
		return self::curr()->redirect($this->getDiscoveryUrl(urlencode($callback)));
	}
	
	public function loginUser() {
		$token = SecurityToken::inst();
		$callback = $this->AbsoluteLink('Login');
		$callback = $token->addToUrl($callback);
		return self::curr()->redirect($this->getDiscoveryUrl(urlencode($callback)));
	}
	
	public function index() {
		$this->httpError(403);
	}
	
	public function Login(SS_HTTPRequest $req) {
		$token = SecurityToken::inst();
		if(!$token->checkRequest($req)) return $this->httpError(400);
		if($req->requestVar('openid_mode') == 'cancel') {
			Session::set('FormInfo.GoogleLoginForm_LoginForm.formError.message', 'Login cancelled.');
			Session::set('FormInfo.GoogleLoginForm_LoginForm.formError.type', 'error');
			return $this->redirect('Security/login#GoogleLoginForm_LoginForm_tab');
		}
		if(!$this->validateCallback($req->requestVars())) {
			Session::set('FormInfo.GoogleLoginForm_LoginForm.formError.message', 'Response validation failed.');
			Session::set('FormInfo.GoogleLoginForm_LoginForm.formError.type', 'error');
			return $this->redirect('Security/login#GoogleLoginForm_LoginForm_tab');
		}
		$id = $req->requestVar('openid_identity');
		$name = $req->requestVar('openid_ext1_value_firstname') . ' ' . $req->requestVar('openid_ext1_value_lastname');
		if(!trim($name)) {
			$name = $req->requestVar('openid_ext1_value_email');
		}
		$email = $req->requestVar('openid_ext1_value_email');
		$u = DataObject::get_one('Member', '"GoogleIdentity" = \'' . Convert::raw2sql($id) . '\'');
		if(!$u || !$u->exists()) {
			$u = DataObject::get_one('Member', '"Email" = \'' . Convert::raw2sql($email) . '\'');
		}
		if(!$u || !$u->exists()) {
			if (self::$create_new_user) {
				$u = $this->newUser(
					$req->requestVar('openid_ext1_value_firstname'),
					$req->requestVar('openid_ext1_value_lastname'),
					$req->requestVar('openid_ext1_value_email')
				);
			} else {
				Session::set('FormInfo.GoogleLoginForm_LoginForm.formError.message', 'No one found for Google user ' . $name . '.');
				Session::set('FormInfo.GoogleLoginForm_LoginForm.formError.type', 'error');
				return $this->redirect('Security/login#GoogleLoginForm_LoginForm_tab');
			}
		}
		if($u->GoogleName != $name) {
			singleton('TransactionManager')->run(function () use ($u, $name, $id) {
				$u->GoogleName = $name;
				$u->GoogleIdentity = $id;
				$u->write();
			}, $u);
		}

		$u->login(Session::get('SessionForms.GoogleLoginForm.Remember'));
		Session::clear('SessionForms.GoogleLoginForm.Remember');
		$backURL = Session::get('BackURL');
		Session::clear('BackURL');
		return $this->redirect($backURL);
	}
	
	public function Connect(SS_HTTPRequest $req) {
		$token = SecurityToken::inst();
		if(!$token->checkRequest($req)) return $this->httpError(400);
		if($req->requestVar('openid_mode') == 'id_res' && $this->validateCallback($req->requestVars())) {
			$id = $req->requestVar('openid_identity');
			$name = $req->requestVar('openid_ext1_value_firstname') . ' ' . $req->requestVar('openid_ext1_value_lastname');
			if(!trim($name)) {
				$name = $req->requestVar('openid_ext1_value_email');
			}
			if($m = $this->CurrentMember()) {
				$m->GoogleIdentity = $id;
				$m->GoogleName = $name;
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
		return self::join_links('GoogleCallback', $action);
	}
}
