<?php

class BrowserMirror{
	
	private static $path;
	private static $nodePath;
	
	private static function getBMPath(){
		if(!empty(self::$path)) return self::$path;
		exec("which bm", $out);
		return !empty($out) && !empty($out[0]) && file_exists($out[0]) && is_executable($out[0]) ? $out[0] : false;
	}
	
	private static function getNodePath(){
		if(!empty(self::$nodePath)) return self::$nodePath;
		exec("which node", $out);
		return !empty($out) && !empty($out[0]) && file_exists($out[0]) && is_executable($out[0]) ? $out[0] : false;
	}
	
	private static function getBasePath(){
		$dirs = explode("/", self::getBMPath());
		array_pop($dirs);
		return implode("/",$dirs);
	}
	
	private static function run($cmd){
		$return = array("output"=>"", "error"=>"", "exit_status"=>0, "command"=>$cmd);
		exec($cmd, $out, $ret);
		$ret = intval($ret);
		$return['exit_status'] = $ret;
		$return['output'] = $out;
		switch($ret){
			case 0: $return['error'] = "None"; break;
			case 1: $return['error'] = "General Error"; break;
			case 126: $return['error'] = "Permission problem or command is not an executable"; break;
			case 127: $return['error'] = "Command not found or Permission problem with \$PATH"; break;
			case 128: $return['error'] = "Invalid exit argument"; break;
			case 130: $return['error'] = "Command manually terminated"; break;
			case 255: $return['error'] = "Exit status out of range"; break;
		}
		return $return;
	}
	
	private static function checkPort($port){
		$cmd = "lsof -i -P | grep ':$port'";
		$command = self::run($cmd);
		if(!empty($command['output']) && !empty($command['exit_status'])) throw new Exception($command['error'].": ".implode("\n",$command['output']));
		return $command['exit_status'] === 1 && empty($command['output']);
	}
	
	public static function setNodePath($path){
		if(file_exists($path) && is_executable($path)) self::$nodePath = $path;
		else throw new Exception("$path is not the correct path to the bm program");
	}
	
	public static function setBMPath($path){
		if(file_exists($path) && is_executable($path)) self::$path = $path;
		else throw new Exception("$path is not the correct path to the bm program");
	}
	
	public static function isRunning(){
		$base_path = self::getBasePath();
		$command = self::run('sudo pgrep -x "bm-server" 2>&1');
		if(!empty($command['output']) && !empty($command['exit_status'])) throw new Exception($command['error'].": ".implode("\n",$command['output']));
		return !($command['exit_status'] === 1 && empty($command['output']));
	}
	
	public static function stop(){
		if(!self::isRunning()) return true;
		$command = self::run("sudo killall bm-server 2>&1 &");
		if(!empty($command['exit_status'])) throw new Exception($command['error'].": ".implode("\n",$command['output']));
		return !self::isRunning();
	}
	
	public static function start($port=1337){
		if(self::isRunning()) return true;
		if(!is_integer($port)) throw new Exception("Invalid port number");
		if(!self::checkPort($port)) throw new Exception("Port $port is in use.");
		$HERE = self::getBasePath();
		$NODE =self::getNodePath();
		$cmd = "sudo $NODE $HERE/bm-server $port > \"$HERE/logs.txt\" 2>&1 &";
		$command = self::run($cmd);
		if(!empty($command['exit_status'])) throw new Exception($command['error'].": ".implode("\n",$command['output']));
		sleep(1);
		return self::isRunning();
	}
	
	public static function getLogs(){
		$HERE = self::getBasePath();
		$command = self::run("sudo cat $HERE/logs.txt"); echo "<Pre>"; var_dump($command); exit;
		if(!empty($command['exit_status'])) throw new Exception($command['error'].": ".implode("\n",$command['output']));
		$logs = array_reverse($command['output']);
		$filtered = array();
		foreach($logs as $log){
			$log = trim($log);
			if(empty($log)) continue;
			preg_match('/\[([0-9]{4}-[0-9]{1,2}-[0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})\]/', $log, $match);
			$date = !empty($match) && !empty($match[1]) ? $match[1] : false;
			preg_match('/\[[0-9]{4}-[0-9]{1,2}-[0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}\] - ([\s\S]*)/', $log, $match);
			$message = !empty($match) && !empty($match[1]) ? $match[1] : false;
			if(empty($date) && empty($message)) $message = $log;
			$filtered[] = array("date"=>$date, "message"=>$message);
		}
		return $filtered;
	}
	
}
