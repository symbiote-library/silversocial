<?php

/**
 * Helper for running bits and pieces of code in transactions
 * 
 * Especially useful for running code as different user
 * 
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class TransactionManager {
	public function __construct() {
		
	}
	
	public function runAsAdmin($closure) {
		$admin = Security::findAnAdministrator();
		return $this->run($closure, $admin);
	}
	
	public function run($closure, $as=null) {
		DB::getConn()->transactionStart();
		$args = func_get_args();
		array_shift($args);array_shift($args);
		$current = singleton('SecurityContext')->getMember();
		if ($as) {
			singleton('SecurityContext')->setMember($as);
		}
		
		$return = null;
		if (is_array($closure)) {
			$return = call_user_func_array($closure, $args);
		} else {
			$return = $closure();
		}

		if ($as) {
			singleton('SecurityContext')->setMember($current);
		}
		DB::getConn()->transactionEnd();
		
		return $return;
	}
}