<?php

/**
 * Describes a relationship between users 
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class Friendship extends DataObject {
	
	public static $db = array(
		'Status'			=> "Enum('Approved,Pending')",
	);
	
	public static $has_one = array(
		'Initiator'			=> 'Member',
		'Other'				=> 'Member',
	);
}
