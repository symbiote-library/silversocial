<?php

/**
 * Permissions for syncrotron usage
 *
 * @author marcus@symbiote.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class SyncrotronPermissions implements PermissionDefiner {
	
	public function definePermissions() {
		return array(
			'Syncro'
		);
	}
}
