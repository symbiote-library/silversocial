<?php

Director::addRules(100, array(
	'Security/$Action/$ID' => 'SilvertwitSecurity',
));

ThirdPartyAuthController::$default_group = 'Members';