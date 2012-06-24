<?php

/**
 * A set of utility functions commonly used in all sites
 *
 * @license http://silverstripe.org/bsd-license
 * @author Marcus Nyeholt <marcus@silverstripe.com.au> 
 */
class SiteUtils {
	public function __construct() {}

	function log($message, $level=null) {
		if (!$level) {
			$level = SS_Log::NOTICE;
		}
		$message = array(
			'errno' => '',
			'errstr' => $message,
			'errfile' => dirname(__FILE__),
			'errline' => '',
			'errcontext' => ''
		);

		SS_Log::log($message, $level);
	}

	public function ajaxResponse($message, $status) {
		return Convert::raw2json(array(
			'message' => $message,
			'status' => $status,
		));
	}
}
