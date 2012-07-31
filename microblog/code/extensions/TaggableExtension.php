<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class TaggableExtension extends DataExtension {
	public static $many_many = array(
		'Tags'		=> 'Tag'
	);
}

class Tag extends DataObject {
	public static $db = array(
		'Title'		=> 'Varchar(128)'
	);
}
