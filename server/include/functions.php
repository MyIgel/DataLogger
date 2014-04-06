<?php defined('_API') or die();

include_once('logger.class.php');

function getData( $sensor, $from=0, $to="NOW" ){
	global $log;
	
	$data = $log->get( 'temp', $sensor, $from, $to );
	
	if( is_array($data) ){
		return $data;
	}
	return false;
}

function arrToVar( $data ){

	if( is_array($data) ){
		$data = json_encode($data, true);
		$data = str_replace( '","', "],[", $data);
		$data = str_replace( '{"', '[', $data);
		$data = str_replace( '"}', ']', $data);
		$data = str_replace( '":"', ',', $data);
		return $data;
	}
	return false;
}