<?php
 class Cic_model extends CI_Model {

        function __construct()
        {
                parent::__construct();
        }
    
        protected static $table = 'cic';

	
	public function addBacker($values){
		$this->insert($table,$values);
		
	}
	
	public function findCIC($name){
	        //SELECT * FROM `charity` WHERE `NAME`LIKE `%s%`
		$query = $this->db->query("SELECT * FROM `".self::$table."` WHERE NAME LIKE '%".$name."%'");	
	
		$resultSet = $query->result();

		if($query->num_rows() < 1){
			return null;
		}
		
		return $resultSet;	

	}
	
	public function getAll(){
	        $query = $this->db->query("SELECT * FROM `cic` LIMIT 1");
		$resultSet = $query->result();
		return count($resultSet);
	}
	
	public function deleteAll(){
		$this->db->empty_table($table); 
	}
}