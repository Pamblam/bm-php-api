<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'BrowserMirror.php';

BrowserMirror::setBMPath('/path/to/bm-server/bm');
BrowserMirror::setNodePath('/path/to/node');

$return = array(
	"response" => "Success",
	"success" => true,
	"data" => array()
);

checkParams(array("action"));
switch($_REQUEST['action']){
	
	case "get_status":
		try{
			$return['data'] = array('running'=>BrowserMirror::isRunning());
		}catch(Exception $e){
			oops($e->getMessage());
		}
		output();
		break;
	
	case "get_logs":
		echo "test"; exit;
		try{
			$return['data'] = array('logs'=>BrowserMirror::getLogs());
		}catch(Exception $e){
			oops($e->getMessage());
		}
		output();
		break;
	
	case "start_server":
		try{
			$port = !empty($_REQUEST['port']) && is_integer($_REQUEST['port']) ? $port : 1337;
			$return['data'] = array('started'=>BrowserMirror::start(), 'port'=>$port);
		}catch(Exception $e){
			oops($e->getMessage());
		}
		output();
		break;
	
	case "stop_server":
		try{
			$return['data'] = array('stopped'=>BrowserMirror::stop());
		}catch(Exception $e){
			oops($e->getMessage());
		}
		output();
		break;
	
	default: oops("Error: invalid action parameter");
}

function checkParams($reqd){
	foreach($reqd as $param)
		if(!isset($_REQUEST[$param])) 
			oops("Error: Missing $param parameter.");
}

function oops($oopsie){
	$GLOBALS['return']['response'] = $oopsie;
	$GLOBALS['return']['success'] = false;
	$GLOBALS['return']['data'] = array();;
	output();
}

function output(){
	header("Content-Type: application/json");
	echo json_encode($GLOBALS['return']);
	exit;
}
