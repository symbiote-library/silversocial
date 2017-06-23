<?php

/**
 * @author marcus@symbiote.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroPostVote extends DataObject {
	public static $db = array(
		'Direction'		=> 'Int',
	);
	
	public static $has_one = array(
		'User'		=> 'Member',
		'Post'		=> 'MicroPost',
	);
}
