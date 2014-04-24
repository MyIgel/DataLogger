<?php
/*
* Data Logging API
* 
* TODO:
* Header Auth, header err respons, requests (GET,POST,DELETE...) ...
*/

define('_API', 1);

include_once('../include/config.php');
include_once('../include/functions.php');

$return = array('status'=>'err');
$url = @explode( "/", $_GET['url'] );

if( isset($url['0']) && $url['0'] == 'show' ){
	$_GET['apikey'] = $api_key;
}

if( isset($_GET['apikey']) && $_GET['apikey'] == $api_key && isset($url['0']) ){
	
	$log = new logger( $database, $_GET['apikey'] );
	
	
	if( $url['0'] == 'log' ){  // http://log.server.com/v1/log/temp/SenSorNo/10.4&apikey=R4nd0MsE3dT8beChANgeD
		
		if( isset($url['1']) && isset($url['2']) && isset($url['3']) ){
			
			if( $log->data( $url['1'], $url['2'], $url['3'] ) ){
			  
				$return['status'] = 'ok';

			}
			
		}
		
	} else	if( $url['0'] == 'show' ){  // http://log.server.com/v1/show/temp/SenSorNo/[from/to]
		
		if( isset($url['1']) && isset($url['2']) ){
			
			if( isset($url['3']) ){
				if( isset($url['4']) ){
					$data = $log->get( $url['1'], $url['2'], $url['3'], $url['4'] );
				} else {
					$data = $log->get( $url['1'], $url['2'], $url['3'] );
				}
			} else {
				$data = $log->get( $url['1'], $url['2'] );
			}
			
			if( $data ){
			  
				$return['status'] = 'ok';
				$return['data'] = $data;
    
			}
			
		}
		
	}
	
}

if ($return['status']!='ok' ){
	header('HTTP/1.0 404 Not Found');
}


echo json_encode($return, true);