<?php

Object::add_extension('Member', 'GoogleIdentifier');

Authenticator::register_authenticator('GoogleAuthenticator');
