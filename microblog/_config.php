<?php

Object::add_extension('Member', 'MicroBlogMember');

Object::add_extension('SiteConfig', 'Restrictable');

Object::add_extension('Image', 'MaximumSizeImageExtension');

DashboardController::set_allowed_dashlets(array(
	'TimelineDashlet',
	'UserProfileDashlet',
	'FriendsDashlet'
));

DashboardUser::$default_dashlets = array(array('TimelineDashlet'), array('UserProfileDashlet'));
