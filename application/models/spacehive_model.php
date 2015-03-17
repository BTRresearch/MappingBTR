<?php
 class Spacehive_model extends CI_Model {

        function __construct()
        {
                parent::__construct();
        }
    
        protected static $table = 'spacehive';

	
	public function addProject($values){
		$this->db->insert(self::$table,$values);
		
	}
	
	public function getProjectById($id){
		$query = $this->db->get_where(self::$table, array('ID' => $id));	
	
		$resultSet = $query->result();

		if($query->num_rows() < 1){
			return null;
		}
		
		return $resultSet[0];	

	}
	
	public function getProjectByName($name){
		$query = $this->db->get_where(self::$table, array('NAME' => $name));	
	
		$resultSet = $query->result();

		if($query->num_rows() < 1){
			return null;
		}
		
		return $resultSet[0];
	}
	
	public function getProjectByUrl($name){
		$query = $this->db->get_where(self::$table, array('URL' => $name));	
	
		$resultSet = $query->result();

		if($query->num_rows() < 1){
			return null;
		}
		
		return $resultSet[0];
	}
	
	
	public function getAll(){
	        $query = $this->db->get(self::$table);
		$resultSet = $query->result();
		return $resultSet;
	}
	
	public function deleteAll(){
		$this->db->empty_table(self::$table); 
	}
}
