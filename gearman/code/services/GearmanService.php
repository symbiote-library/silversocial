<?php


/**
 *
 * Used to interface with the gearman service
 * 
 * @author marcus@symbiote.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class GearmanService {
	
	public $host = 'localhost';
	public $port = '4730';
	
	public $name = null;
	
	public function __call($method, $args) {
		$name = $this->name ? $this->name : preg_replace("/[^\w_]/","",Director::baseFolder() .'_handle');
		$val = get_include_path();
		require_once 'Net/Gearman/Client.php';
		
		// @TODO Make this an injected, configured property....
		$client = new Net_Gearman_Client($this->host . ':' . $this->port);
		$set = new Net_Gearman_Set;

		array_unshift($args, $method);
		$task = new Net_Gearman_Task($name, $args, null, Net_Gearman_Task::JOB_BACKGROUND);
		$set->addTask($task);
		$client->runSet($set);
	}
	
	public function handleCall($args) {
		if (!count($args)) {
			return;
		}
		$workerImpl = ClassInfo::implementorsOf('GearmanHandler');
		$workers = array();
		
		$method = array_shift($args);
		
		foreach ($workerImpl as $type) {
			$obj = Injector::inst()->get($type);
			if ($obj->getName() == $method) {
				call_user_func_array(array($obj, $method), $args);
			}
		}
	}
}
