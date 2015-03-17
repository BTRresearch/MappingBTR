<?php
 class Crowdfundernet_service extends CI_Model {

	function __construct(){
			parent::__construct();
			$this->load->library('simple_html_dom');
	}

	public function getBackersFromUrl($base){
		$a = range('A','Z');
		foreach($a as $i){
			$url = $base.'/backers/'.$i.'/';
			$html = curl_retrieve($url);		
			$dom = new simple_html_dom();
			$dom->load($html);
			$divs = $dom->find('div.backing');
			foreach ($divs as $div){
				$inside = $div->plaintext;
				//inner text has leading spaces
				$inside = trim($inside);
				$temp_array = explode(" ", $inside);
				if ($temp_array[0] != "anonymous"){
					if (!self::valueFound('backer','NAME',$temp_array[0])){
						$sibling = $div->prev_sibling()->first_child()->href;
						$values["URL"] = $sibling;//"/user/".$temp_array[0];
						$values["NAME"] = $temp_array[0];
						$values["SEED"] = $base;
						self::insertValuesIntoTable($values, "backer");
					}
				}
			}
			
			
			$dom->clear();
			unset($dom);
			//$html->clear();
			unset($html);
			////echo $url."<br>";
		}
	}
	
	private function insertValuesIntoTable($values, $table){
		$this->db->insert($table, $values);
	}
	
	public function retrieveAllProjects(){
		$temp_array = self::getAllValuesFromTable("backer");
		foreach ($temp_array as $value){
			////echo "<br>fetching all projects by ".$value->NAME."<br>";
			self::getProjectsByBacker($value);
		}
	}
	
	public function getProjectsByBacker($backer){
		$url = "http://www.crowdfunder.co.uk/user/get_events/"; 
		$id = self::getBackerId($backer->URL);

		if ($id){
			$html =  curl_post_retrieve($url, $id);		
			
			$dom = new simple_html_dom();
			$dom->load($html);
			
			$values = array();
			$projects = array();
			
			$as = $dom->find('a[title="Backed a project"]');
			//////echo count($as);
			foreach ($as as $a){
				$url = "http://www.crowdfunder.co.uk".$a->href;
				if ($backer->SEED != $url)
				if (!self::valuesFound('projects', array('URL' => $url, 'SEED' => $backer->SEED))){
					//$values["NAME"] = str_replace(">","",$a->title);
					
					$values["URL"] = $url;
					$values["SEED"] = $backer->SEED;
					$values["COUNTED"] = 1;
					
					self::insertValuesIntoTable($values, "projects"); 
				} else {
					self::incrementProjectCount(array('URL' => $url, 'SEED' => $backer->SEED));
				}
			}
			$dom->clear();
			unset($dom);
			unset($html);
		} else {
			////echo $backer->NAME;
		}
					
	}
	
	private function incrementProjectCount($values){
		$this->db->set('COUNTED', 'COUNTED+1', FALSE);
		$this->db->where($values);
		$this->db->update('projects'); 
	}
	
	private function valueFound($table, $field, $value){
		$query = $this->db->get_where($table, array($field => $value));
		$resultSet = $query->result();
		//////echo $value."  ".count($resultSet)."<hr>";
		if ( count($resultSet) > 0){
			return true;
		} else {
			return false;
		}
	}
	
	private function valuesFound($table, $values){
		$query = $this->db->get_where($table, $values);
		$resultSet = $query->result();
		//print_r($values); echo $table."  ".count($resultSet)."<hr>";
		if ( count($resultSet) > 0){
			return true;
		} else {
			return false;
		}
	}
	
	private function getProjectDetails($groupurl){
		$html = curl_retrieve($groupurl);		
		$dom = new simple_html_dom();
		$dom->load($html);
		
		$values = array();
		////echo "<hr>".$groupurl;
		$div = $dom->find('div.hidden-sm',0);
		//echo "|".$div->innertext."|<br>".strlen($div->innertext)."<br>";;
		if (strlen(trim($div->innertext)) == 0){
			$div = $dom->find('div.hidden-sm',1);
		}
		if ($div){
			$a = $div->first_child()->last_child();
			$values["OWNER"] = $a->plaintext;
			////echo "<br>".$values["OWNER"]." is the owner of ".$groupurl."<br>";
		} else {
			$values["OWNER"] = 'NA';
		}
		$a = $dom->find('h1 a',0);
		if ($a){
			$values["NAME"] = $a->innertext;
			$values["FUNDEDDATE"] = '0000-00-00 00:00:00';
			$values["DESCRIPTION"] = "";
			
			$div = $dom->find('div.status-funded',0);
	
			if (!$div){
				$div = $dom->find('div.status-overfunded',0);        
			} 
			if ($div){
				if(preg_match('/(\w{3}\s\d{2},\s\d{4})/', $div->innertext, $datematch)){
					$timezone = new DateTimeZone('Europe/Berlin');
					$date = new DateTime($datematch[1],$timezone);
					$values["FUNDEDDATE"] = $date->format('Y-m-d H:i:s');
				}else{
					if(preg_match('/(\w{3}\s\d{1},\s\d{4})/', $div->innertext, $datematch)){
						$timezone = new DateTimeZone('Europe/Berlin');
						$date = new DateTime($datematch[1],$timezone);
						$values["FUNDEDDATE"] = $date->format('Y-m-d H:i:s');
					} else {
						 $values["FUNDEDDATE"] = '0000-00-00 00:00:00';
					}
				}
				////echo "<br>".$values["FUNDEDDATE"];
				$ps = $dom->find('div.project-body p');
				//////echo count($ps);		
				foreach ($ps as $p){
					$values["DESCRIPTION"] .= $p->plaintext;
				}
				//////echo "<br>".$values["DESCRIPTION"];
				$nodevalue = $dom->find('span.sofar',0);
				$values["RAISED"] = filter_var($nodevalue, FILTER_SANITIZE_NUMBER_INT);
				////echo "<br>".$values["RAISED"];
				$nodevalue = $dom->find('span.target',0);
				$values["GOAL"] = filter_var($nodevalue, FILTER_SANITIZE_NUMBER_INT);
				////echo "<br>".$values["GOAL"];
				$nodevalue = $dom->find('span.backers',0);
				$values["BACKERS"] = filter_var($nodevalue, FILTER_SANITIZE_NUMBER_INT);
				////echo "<br>".$values["BACKERS"];
				$nodevalue = $dom->find('span.days-left',0);
				$values["DAYS"] = filter_var($nodevalue, FILTER_SANITIZE_NUMBER_INT);
				////echo "<br>".$values["DAYS"];
		} else {
			$dom->clear();
			unset($dom);
			unset($html);
			return null;
		}

		$dom->clear();
		unset($dom);
		unset($html);
		return $values;
		}
	}
	
	private function copyInfoToResult($info, $result){
		$temp = (object) array('NAME'            => $info["NAME"],
							   'URL'             => $result->URL,
							   'OWNER'           => $info["OWNER"],
							   'COUNTED'         => $result->COUNTED,
							   'GOAL'            => $info["GOAL"],
							   'RAISED'          => $info["RAISED"],
							   'FUNDEDDATE'      => $info["FUNDEDDATE"],
							   'BACKERS'         => $info["BACKERS"],
							   'DESCRIPTION'     => $info["DESCRIPTION"],
							   'SEED'            => $result->SEED
						);
							   
		return $temp;					   
	}
	
	public function buildResultsFromProjects($filter, $badwords){
		//$this->db->where('COUNTED >',1);
		$query = $this->db->get('projects');
		$resultSet = $query->result();
		////echo $value."  ".count($resultSet)."<hr>";
		foreach ($resultSet as $res){
			if (!self::valuesFound('results', array('URL' => $res->URL, 'SEED' => $res->SEED))){
				//$temp = self::copyInfoToResult(self::getProjectDetails($res->URL),$res);
				//if ($temp->OWNER != null)
				self::insertValuesIntoTable($res, 'results');
			}
		}
		self::applyFilters($filter, $badwords);
	}
	
	private function deleteFromTable($table, $field, $value, $message){
		//echo "<hr>".$table." ".$field." ".$value." because ".$message;
		$this->db->delete($table, array($field => $value)); 
	}
	
	private function applyFilters($keywordFilter, $badwords){
		$query = $this->db->get('results');
		$resultSet = $query->result();
		foreach ($resultSet as $res){
			if ($res->GOAL > $res->RAISED){
				self::deleteFromTable('results', 'URL', $res->URL, " not enough money");
			} else {
				date_default_timezone_set("Europe/London");
				$now = new DateTime();
				$currentyear = $now->format("Y");
				$fundeddate = new DateTime($res->FUNDEDDATE);
				$fundedyear = $fundeddate->format("Y");
				$fundedyear += 2;
				if ($fundedyear < $currentyear){
					self::deleteFromTable('results', 'URL', $res->URL," too old");
				}	
			}
			
			if ($keywordFilter){
				foreach($badwords as $badword){
			        if (preg_match('/'.$badword.'/i', $res->DESCRIPTION, $matches)){
				        self::deleteFromTable('results', 'URL', $res->URL, $badword);
			        }
		        }
			}
		}
	}
	
	public function buildNetwork(){
		//make sure the backer table is not emptied with each run
		//select each of the result and for them select all backers
		//for each backer select from backer and count how many occurrences we have
		//for each occurrence add an entry in the edges table or increment if it exists
		//the form should be |SOURCE|WEIGHT|TARGET|
		//the results table is the nodes source
		//-----------OR
		//when we add the backer we can also create the relation
		//in which case we have redundant info.
		
	}
	
	public function emptyTables($tables){
		if ($tables[0])
		$this->db->empty_table('backer');
		if ($tables[1])
		$this->db->empty_table('projects');
		if ($tables[2])
		$this->db->empty_table('results');
		
	}
	
	public function getSecondGenNetworkSeeds(){
		return self::getAllValuesFromTable('projects');
	}
	
	public function getValuesFromTableWhere($table, $field, $value){
		$this->db->where($field, $value);
		$query = $this->db->get($table);
		$resultSet = $query->result();
		return $resultSet;
	}
	
	public function getAllValuesFromTable($table){
		$query = $this->db->get($table);
		$resultSet = $query->result();
		return $resultSet;
	}
	//retrieves the backer id needed to load all projects with ajax
	private function getBackerId($backerUrl){

	        $html = curl_retrieve("http://crowdfunder.co.uk".$backerUrl);		
			$dom = new simple_html_dom();
			$dom->load($html);
			$a = $dom->find('a.older-posts',0);
			if ($a){
					$onclick = $a->onclick;
					$exp1 = explode("(",$onclick);
					$exp2 = explode(")",$exp1[1]);
					$userID = $exp2[0];
			} else {
					return null;
			}
			$dom->clear();
			unset($dom);
			unset($html);
			return $userID;
		}

			
	public function createCsvFiles(){
		
	}
}