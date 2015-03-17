<?php
 class Crowdfunder_service extends CI_Model {
 
	private static $_badwords = array('Community Interest company', 'CIC', 'Foundation', 'Charity', 'Social enterprise', 'Cooperative', 'Co-op', 'Co-operative');

	function __construct()
	{
			parent::__construct();
			$this->load->library('simple_html_dom');
	}


	
	private function updateUniqueID($values){
		$this->db->set('UNIQUEID', 'ID', FALSE);
		$this->db->where(array('NAME' => $values["NAME"]));
		$this->db->update('backer'); 
	}
	
	private function createRelationsWithBacker($backer){
		if (self::valueFound('backer','NAME',$backer["NAME"])){
			$temp_array = self::getValuesWhere(array('URL' => $backer["URL"]),"backer");
			foreach ($temp_array as $element){
				self::insertRelationBetween($backer["SEED"], $element->SEED, $backer);
			}
		}
	}
	
	private function insertRelationBetween($source, $destination, $backer){
		if ($source != NULL && $destination != NULL){
			if ($source != $destination){
				if (self::valuesFound('edges',array('SOURCE' => $source, 'TARGET' => $destination))){
					self::addBackerToList('edges', array('SOURCE' => $source, 'TARGET' => $destination), 'WEIGHT', $backer);
				} else if (self::valuesFound('edges',array('SOURCE' => $destination, 'TARGET' => $source))){
					self::addBackerToList('edges', array('SOURCE' => $destination, 'TARGET' => $source), 'WEIGHT', $backer);
				} else {
					self::insertValuesIntoTable(array('SOURCE' => $source, 'TARGET' => $destination, 'TYPE' => 'Undirected', 'LABEL' => $backer["NAME"], 'WEIGHT' => 1),'edges');
				}
			}
		}
	}
	
	private function addBackerToList($table, $clause, $field, $backer){
		self::logThis("Adding ".$backer["NAME"]." for seed: ".$backer["SEED"]);
		self::logThis("the relation ".$clause["SOURCE"]." destination: ".$clause["TARGET"]);
		$this->db->set($field, "$field+1", FALSE);
		$this->db->set('LABEL',"CONCAT_WS(',',LABEL,'".$backer["NAME"]."')", FALSE);
		$this->db->where($clause);
		$this->db->update($table); 

	}
	
	private function incrementRecordInTable($table, $clause, $field){
		$this->db->set($field, "$field+1", FALSE);
		$this->db->where($clause);
		$this->db->update($table); 
	}
	
	private function insertValuesIntoTable($values, $table){
		$flag = true;
		foreach ($values as $element){
			if ($element === NULL){
				$flag = false;
			}
		}
		if ($flag)
		$this->db->insert($table, $values);
	}
	
	private function incrementProjectCount($query, $nBacker){
		//$b = ','.$nBacker;
		$this->db->set('COUNTED', 'COUNTED+1', FALSE);
		$this->db->set('BACKERLIST',"CONCAT_WS(',',BACKERLIST,'".$nBacker."')", FALSE);
		$this->db->where($query);
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
		//echo "<hr>".$groupurl;
		$div = $dom->find('div.hidden-sm',0);
		//echo "|".$div->innertext."|<br>".strlen($div->innertext)."<br>";;
		if (strlen(trim($div->innertext)) == 0){
			$div = $dom->find('div.hidden-sm',1);
		}
		if ($div){
			$a = $div->first_child()->last_child();
			$values["OWNER"] = $a->plaintext;
			//echo "<br>".$values["OWNER"]." is the owner of ".$groupurl."<br>";
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
							   'SEED'            => $result->SEED,
							   'BACKERLIST'      => $result->BACKERLIST
						);
							   
		return $temp;					   
	}
	
	private function deleteFromTable($table, $field, $value, $message){
		//echo "<hr>".$table." ".$field." ".$value." because ".$message;
		$this->db->delete($table, array($field => $value)); 
	}
	
	private function deleteFromTableWithQuery($table, $query){
		//echo "<hr>".$table." ".$field." ".$value." because ".$message;
		$this->db->delete($table, $query); 
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
		
	private function updateRecordInTable($table, $clause, $values){
		$this->db->where($clause);
		$this->db->update($table, $values); 
	}
	
	private function getNRows($table,$values){
		$query = $this->db->get_where($table, $values);
		$resultSet = $query->num_rows();
		return $resultSet;
	}
	
	private function getValuesWhere($values, $table){
		$query = $this->db->get_where($table, $values);
		$resultSet = $query->result();
		return $resultSet;
	}
	
	private function getFirstValuesWhere($values, $table){
		$query = $this->db->get_where($table, $values);
		$resultSet = $query->result();
		if (isset($resultSet[0])){
			return $resultSet[0];
		} else {
			self::logThis("ERROR FETCHING SOMETHING: ".$values["NODE"]);
			return (object)(array('ID' => 'DUD'));
		}
	}
	//retrieves the backer id needed to load all projects with ajax
	private function getBackerId($backerUrl){
			
	        $html = curl_retrieve("http://crowdfunder.co.uk".$backerUrl);		
			$dom = new simple_html_dom();
			$dom->load($html);
			$div = $dom->find('div.public_profile',0);
			if ($div != null){
				$userID = $div->getAttribute('data-id');
			} else {
				self::logThis("ERROR GETTING USER ID FOR:".$backerUrl);
				$userID = 0;
			}
			//echo "=".$userID;
			//if ($div){
				//if(isset($div->data-id)) 
				//$userID = $div->data-id;
			//} else {
					//return null;
			//}
			$dom->clear();
			unset($dom);
			unset($html);
			return $userID;
		}

	private function updateBackerID($values){
		$this->db->set('BACKERID', 'ID', FALSE);
		$this->db->where(array('BACKERNAME' => $values["BACKERNAME"]));
		$this->db->update('crowdfunderstats'); 
	}
	
	public function loadFinancialData(){
		$this->db->empty_table('crowdfunderstats');
		$temp_array = self::getAllValuesFromTable('results');
		foreach ($temp_array as $element){
			self::getAllBackerPledges($element);
		}
		$datestring = "%Y%m%d%h%i%a";
		$time = time();
		$cstat = mdate($datestring, $time)."_cstat.csv";
		$this->db->query("SELECT 'PROJECTID','PROJECTNAME','BACKERID','BACKERNAME','PLEDGED' UNION SELECT PROJECTID, PROJECTNAME, BACKERID, BACKERNAME, PLEDGED INTO OUTFILE '".absolute_path()."application/data/".$cstat."' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' FROM crowdfunderstats");
		return $cstat;
	
	}
	
	public function createFinancialFile(){
		$datestring = "%Y%m%d%h%i%a";
		$time = time();
		$cstat = mdate($datestring, $time)."_cstat.csv";
		$this->db->query("SELECT 'PROJECTID','PROJECTNAME','BACKERID','BACKERNAME','PLEDGED' UNION SELECT PROJECTID, PROJECTNAME, BACKERID, BACKERNAME, PLEDGED INTO OUTFILE '".absolute_path()."application/data/".$cstat."' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' FROM crowdfunderstats");
		return $cstat;
	}
	
	public function getAllBackerPledges($project){
		$a = range('A','Z');
		foreach($a as $i){
			$url = $project->URL.'/backers/'.$i.'/';
			$html = curl_retrieve($url);		
			$dom = new simple_html_dom();
			$dom->load($html);
			$divs = $dom->find('div.who-pledged');
			foreach ($divs as $div){
				$inside = str_replace("&pound;",'/',$div->plaintext);
				//echo "<br>".$inside;
				//inner text has leading spaces
				$inside = trim($inside);
				$temp_array = explode(" ", $inside);
				if ($temp_array[0] != "anonymous"){
					$values["PROJECTID"] = $project->ID;
					$values["PROJECTNAME"] = $project->NAME;
					//$values["BACKERID"] = "ID";
					$values["BACKERNAME"] = $temp_array[0];
					$temp_array = explode("/", $inside);
					if (isset($temp_array[1])){
						$values["PLEDGED"] = $temp_array[1];
						$temp_var = self::getValuesWhere(array('BACKERNAME' => $temp_array[0]),'crowdfunderstats'); 
						if (isset($temp_var[0])){
							$values["BACKERID"] = $temp_var[0]->BACKERID;
							self::insertValuesIntoTable($values, "crowdfunderstats");
						} else {
							$values["BACKERID"] = "ID";
							self::insertValuesIntoTable($values, "crowdfunderstats");
							self::updateBackerID($values);
						}
					}
				}	else {
					$values["PROJECTID"] = $project->ID;
					$values["PROJECTNAME"] = $project->NAME;
					//$values["BACKERID"] = "ID";
					$values["BACKERNAME"] = $temp_array[0];
					$temp_array = explode("/", $inside);
					if (isset($temp_array[1])){
						$values["PLEDGED"] = $temp_array[1];
					}
					$values["BACKERID"] = 0;
					self::insertValuesIntoTable($values, "crowdfunderstats");
				}
			}
		}
		$dom->clear();
		unset($dom);
		unset($html);
	}
	
	public function getBackersFromUrl($base, $relations){
		self::logThis("Loading backers from URL: ".$base);
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
						if ($relations){
							self::createRelationsWithBacker($values);
						}
						self::insertValuesIntoTable($values, "backer");
						self::updateUniqueID($values);
					} else {
						$sibling = $div->prev_sibling()->first_child()->href;
						$temp_var = self::getValuesWhere(array('NAME' => $temp_array[0]),'backer'); 
						$values["UNIQUEID"] = $temp_var[0]->ID;
						$values["URL"] = $sibling;//"/user/".$temp_array[0];
						$values["NAME"] = $temp_array[0];
						$values["SEED"] = $base;
						if ($relations){
							self::createRelationsWithBacker($values);
						}
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
	
	public function retrieveAllProjects($seed){
		$temp_array = self::getValuesWhere(array('SEED' => $seed),"backer");
		foreach ($temp_array as $value){
			self::logThis("fetching all projects by ".$value->NAME."/".$seed);
			self::getProjectsByBacker($value);
		}
	}
	
	public function getProjectsByBacker($backer){
		$url = "http://www.crowdfunder.co.uk/user/get_events/"; 
		$id = self::getBackerId($backer->URL);
		self::logThis("Obtaining backer ID: ".$backer->NAME." and ID ".$id);
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
					$values["BACKERLIST"] = $backer->NAME;
					
					self::insertValuesIntoTable($values, "projects"); 
				} else {
					self::incrementProjectCount(array('URL' => $url, 'SEED' => $backer->SEED), $backer->NAME);
				}
			}
		$dom->clear();
		unset($dom);
		unset($html);
		} else {
			self::logThis( $backer->NAME);
		}
					
	}

	public function removeSeedFromResults($url){
		self::deleteFromTable('results','URL',$url,'');
		$temp = self::getFirstValuesWhere(array('NODE' => $url),'nodes');
		if ($temp != null){
			self::deleteFromTable('edges',"TARGET",$temp->ID,'');
			self::deleteFromTable('edges',"SOURCE",$temp->ID,'');
		}
		self::deleteFromTable('edges',"TARGET",$url,'');
		self::deleteFromTable('edges',"SOURCE",$url,'');
		self::deleteFromTable('nodes','NODE',$url,'');
	}

	public function myDate(){
		$datestring = "%Y%m%d-%h%i";
		$time = time();
		$cstat = mdate($datestring, $time);
		return $cstat;
	}

	public function logThis($info){
		$temp = self::myDate();
		if ($this->db->table_exists('logs') ){
		  // table exists
		  self::insertValuesIntoTable(array('log' => $info, 'dtm' => "$temp"),'logs');
		}
		
	}
	
	public function generalColink($seeds, $filter, $badwords, $tablesToDelete, $relations){
		foreach ($seeds as $seed){
			
			self::logThis("General Colink function, running script for seed: ".$seed->URL);
			self::emptyTables($tablesToDelete);
			//echo "tables emptied<br>";
			self::getBackersFromUrl($seed->URL, $relations);
			//echo "backers loaded<br>";
			self::retrieveAllProjects($seed->URL);
			//todo add input 
			self::firstColink($seed->URL, $filter, $badwords);
		}
		
	}
	
	public function emptyTables($tables){
		self::logThis("deleted some tables");
		if ($tables[0]){
			$this->db->empty_table('backer');
			$this->db->empty_table('edges');
			$this->db->empty_table('nodes');
			$this->db->empty_table('crowdfunderstats');
		}
		if ($tables[1])
			$this->db->empty_table('projects');
		if ($tables[2])
			$this->db->empty_table('results');
		
	}
	
	public function generateNetworkEdgesNodes($url){
		//echo 'generate network edges <br>';
		$temp_array = self::getAllValuesFromTable('results');
		
		foreach($temp_array as $element){
			if (!self::valueFound('results', 'SEED', $element->URL) && !self::valueFound('projects', 'SEED', $element->URL)){
				self::getBackersFromUrl($element->URL, true);
			}
		}
		
		self::removeSeedFromResults($url);
		
		foreach($temp_array as $element){
			$nLinks = self::getNRows('edges',array('SOURCE' => $element->URL)) + self::getNRows('edges',array('TARGET' => $element->URL));
			self::insertValuesIntoTable(array('NODE' => $element->URL, 'LABEL' => $element->NAME, 'NLINKS' => $nLinks),'nodes');
		}
		
		$temp_array = self::getAllValuesFromTable('edges');
		foreach($temp_array as $element){
			$source = self::getFirstValuesWhere(array('NODE' => $element->SOURCE),'nodes');
			$target = self::getFirstValuesWhere(array('NODE' => $element->TARGET),'nodes');
			/*
			$backerlist = self::getFirstValuesWhere(array('URL' => $element->TARGET, 'SEED' => $element->SOURCE),'projects');
			if ($backerlist->ID == 'DUD'){
				$backerlist = self::getFirstValuesWhere(array('URL' => $element->SOURCE, 'SEED' => $element->TARGET),'projects');
			}
			if ($backerlist->ID == 'DUD'){
				self::deleteFromTableWithQuery('edges',array('SOURCE' => $element->SOURCE, 'TARGET' => $element->TARGET));
			} else {*/
				self::updateRecordInTable('edges', array('ID' => $element->ID), array('LABEL' => $element->WEIGHT." common backers: ".$element->LABEL,'SOURCE' => $source->ID, 'TARGET' => $target->ID));
			//}
		}
		
		
		
		$datestring = "%Y%m%d%h%i%a";
		$time = time();
		$edgesFile = mdate($datestring, $time)."_edges.csv";
		$nodesFile = mdate($datestring, $time)."_nodes.csv";
		$this->db->query("SELECT 'Source','Target','Type','Label','Weight' UNION SELECT Source,Target,Type,Label,Weight INTO OUTFILE '".absolute_path()."application/data/".$edgesFile."' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' FROM edges");
		$this->db->query("SELECT 'Id','Node','Label','pCategory','nLinks' UNION SELECT Id,Node,Label,pCategory,nLinks INTO OUTFILE '".absolute_path()."application/data/".$nodesFile."' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' FROM nodes");
		self::insertValuesIntoTable(array('EDGES' => $edgesFile, 'NODES' => $nodesFile),'files');
		return array('edges' => $edgesFile,'nodes' => $nodesFile);
	}	

	public function firstColink($seed, $filter, $badwords){
		$this->db->where('COUNTED >',1);
		$this->db->where('SEED',$seed);
		$query = $this->db->get('projects');
		$resultSet = $query->result();
		////echo $value."  ".count($resultSet)."<hr>";
		foreach ($resultSet as $res){
			if (!self::valueFound('results','URL',$res->URL)){
				$temp = self::copyInfoToResult(self::getProjectDetails($res->URL),$res);
				if ($temp->OWNER == null)
				$temp->OWNER = "Default";
				self::insertValuesIntoTable($temp, 'results');
			}
		}
		self::applyFilters($filter, $badwords);
	}

	public function networkTest(){
		$datestring = "%Y%m%d%h%i%a";
		$time = time();
		$this->db->query("SELECT 'Source','Target','Type','Label','Weight' UNION SELECT Source,Target,Type,Label,Weight INTO OUTFILE '".absolute_path()."application/data/".mdate($datestring, $time)."_edges.csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' FROM edges");
	}
	
	public function getSecondGenSeeds(){
	
		return self::getAllValuesFromTable('results');
	}
	
	public function getAllValuesFromTable($table){
		$query = $this->db->get($table);
		$resultSet = $query->result();
		return $resultSet;
	}
	
}