<?php defined('_API') or die();

class logger {

  private $db;
  private $user = '';
  var $err = false;
  
  function __construct( $database, $user ) {
    
    $this->db = @new mysqli( $database['host'], $database['user'], $database['password'], $database['database']);
    if (mysqli_connect_errno()) {
      $this->err = 'The database hates me :( Error: '.mysqli_connect_errno().' : '.mysqli_connect_error();
      die('{"status":"err","err":"'.$this->err.'"}');
    }
		
    $this->user = $user;
  }
	 
  function data( $type, $sensor, $data ){
    
    if( $this->db->query( "INSERT INTO datalog (client, type, data, sensor) VALUES ('".$this->db->real_escape_string($this->user)."', '".$this->db->real_escape_string($type)."', '".$this->db->real_escape_string($data)."', '".$this->db->real_escape_string($sensor)."');" ) ){
      return true;
    }
		
    return false;
  }
	 
  function get( $type, $sensor ){
    
    $result = $this->db->query("SELECT (UNIX_TIMESTAMP(time)*1000) as time,data FROM `datalog` where type = '".$this->db->real_escape_string($type)."' and sensor = '".$this->db->real_escape_string($sensor)."'");
     if ( $result ) {
        $data = array();
        
        while($row = $result->fetch_array(MYSQL_ASSOC)) {
          $data[$row['time']] = $row['data'];
        }
        return $data;     
    }
    return false;
  }
	 
  function stats( $type ){
    
    if( $type == "total" ) {
		$result = $this->db->query("SELECT count(id) AS total FROM `datalog`");
		if ( $result ) {
			$data = $result->fetch_array(MYSQL_ASSOC);
			return $data['total'];
		}
    }
    return false;
  }

}