<?php

Object::add_extension('Member', 'MicroBlogMember');

Object::add_extension('SiteConfig', 'Restrictable');

Object::add_extension('Image', 'MaximumSizeImageExtension');

DashboardUser::$default_dashlets = array(array('TimelineDashlet'), array('UserProfileDashlet'));