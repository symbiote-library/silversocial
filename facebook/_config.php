<?php

define('FACEBOOK_PATH', dirname(__FILE__));

Object::add_extension('Member', 'FacebookIdentifier');

Authenticator::register_authenticator('FacebookAuthenticator');
