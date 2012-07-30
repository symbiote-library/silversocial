<?php

Director::addRules(100, array(
	'Security/$Action' => 'SilvertwitSecurity',
));

ThirdPartyAuthController::$default_group = 'Members';