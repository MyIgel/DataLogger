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
	 
	function data( $sensor, $data ){
		
		$query = "INSERT INTO data (sensorID, data)
					VALUES ('".$this->db->real_escape_string($sensor)."',
							'".$this->db->real_escape_string($data)."'
						   )";

		if( $this->db->query( $query ) ){
			return true;
		}
		
		return false;
	}
	 
	function getSensor( $sensor=false, $type=false ){
		
		$query = "SELECT * FROM sensor WHERE user = '".$this->db->real_escape_string($this->user)."'";
		$query = ($sensor) ? $query.' AND sid = "'.$this->db->real_escape_string($sensor).'"' : $query;
		$query = ($type) ? $query.' AND type = "'.$this->db->real_escape_string($type).'"' : $query;

		$result = $this->db->query( $query );
		if ( $result ) {

			$sensors = array();
				
			while($row = $result->fetch_array(MYSQL_ASSOC)) {
				$sensors[$row['id']] = $row;
			}
			return $sensors;
		}
		return false;
	}
	 
	function get( $sensor, $from=0, $to="NOW" ){
		
		$to = ($to=="NOW")?time():$to;

		$query = "SELECT (UNIX_TIMESTAMP(time)*1000) AS time,data FROM `data`
				  WHERE sensorID = '".$this->db->real_escape_string($sensor)."'
				  AND time BETWEEN
					FROM_UNIXTIME(".$this->db->real_escape_string($from).")
					AND FROM_UNIXTIME(".$this->db->real_escape_string($to).")";

		$result = $this->db->query( $query );
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

			$result = $this->db->query("SELECT count(id) AS total FROM `data`");
			if ( $result ) {

				$data = $result->fetch_array(MYSQL_ASSOC);
				return $data['total'];
			}
		}
		return false;
	}

}