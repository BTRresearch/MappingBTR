<?php
 class Charity_model extends CI_Model {

        function __construct()
        {
                parent::__construct();
        }
    
        protected static $table = 'charity';

	
	public function addBacker($values){
		$this->insert($table,$values);
		
	}
	
	public function findCharity($name){
	        //`
		$query = $this->db->query("SELECT * FROM `".self::$table."` WHERE NAME LIKE '%".$name."%'");	
	
		$resultSet = $query->result();

		if($query->num_rows() < 1){
			return null;
		}
		
		return $resultSet;	

	}
	
	public function getAll(){
	        $query = $this->db->query("SELECT * FROM `charity` LIMIT 1");
		$resultSet = $query->result();
		return count($resultSet);
	}
	
	public function deleteAll(){
		$this->db->empty_table($table); 
	}
}
