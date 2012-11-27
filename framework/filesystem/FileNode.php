<?php

/**
 * A parent of File and Folder, abstracts the 'common' functionality between both
 * to a separate parent class
 *
 * @author <marcus@silverstripe.com.au>
 * @license BSD License http://www.silverstripe.org/bsd-license
 */
class FileNode extends DataObject {
	public static $db = array(
		"Name" => "Varchar(255)",
		"Title" => "Varchar(255)",
	);
	
	static $has_one = array(
		"Parent" => "FileNode",
		"Owner" => "Member"
	);
}
