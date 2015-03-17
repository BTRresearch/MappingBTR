<?php
 class Spacehive_service extends CI_Model {
 
        //private static $_badwords = array('CIC', 'foundation', 'charity','social enterprise', 'cooperative',
//'co-op', 'co-operative', 'ltd', 'association', 'company', 'business', 'trust');

        function __construct()
        {
                parent::__construct();
                $this->load->library('simple_html_dom');
        }
		
		public function safe($inp) {
		if(is_array($inp))
			return array_map(__METHOD__, $inp);

		if(!empty($inp) && is_string($inp)) {
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
		}

		return $inp;
	}
  
        public function getProjectDataFromUrl($url){
                //echo $url."<br>";
                $html = curl_retrieve($url);
		$values = array();
		
		$dom = new simple_html_dom();
		$dom->load($html);
		
		$class = $dom->find('h1.project',0);
		//echo $class;
		if (!$class){
		        return null;
		} else {
		       //echo $values["NAME"];
		       $values["NAME"] = $class->plaintext;
		       $values["URL"] = $url;
		}
		
		$values["DESCRIPTION"] = "";
		$divs = $dom->find('div.copy p');
		if ($divs){
		        foreach ($divs as $p){
		                $values["DESCRIPTION"] .= $p->plaintext;
		        }
		}
		
		$divs = $dom->find('div.promoter a',1);
		if ($divs){
	                $values["PROMOTER"] = $divs->plaintext;
		}
		
		$uls = $dom->find('ul.project-nav a',1); 
		if ($uls){
		        $pLink = $uls->href;       
		}
		
		$uls = $dom->find('ul.project-nav a',3); 
		if ($uls){
		        $nLink = $uls->href;       
		}
		$values["PDESCRIPTION"] = '';
		$html = curl_retrieve("http://spacehive.com".$pLink);
		//echo "<hr>".$pLink."<hr>";
		$dom->load($html);
		$p0 = $dom->find('p',0)->plaintext;
		if ($dom->find('p',1)){
		        $p1 = $dom->find('p',1)->plaintext;
		        $values["PDESCRIPTION"] = $p0.$p1;
		} else
		$values["PDESCRIPTION"] = $p0;
		
		$html = curl_retrieve("http://spacehive.com".$nLink);
		//echo "<hr>".$nLink."<hr>";
		$dom->load($html);
		$spans = $dom->find('h3[id=therm-pledged] span', 0);
		if ($spans){
		        $span = str_replace(',', '', $spans->plaintext);
		        $span = str_replace('&#163;', '', $span);
			$values["RAISED"] = filter_var($span, FILTER_SANITIZE_NUMBER_INT);
			$tempStr = $dom->find('h3[id=therm-pledged]', 0)->plaintext;
			//echo "<hr>"."<hr>".$tempStr."<hr>"."<hr>";
			$tempStr = str_replace("pledged","*",$tempStr);
			//echo "<hr>"."<hr>".$tempStr."<hr>"."<hr>";
			$tempArr = explode("*",$tempStr);
			$values["BACKERS"] = filter_var($tempArr[1], FILTER_SANITIZE_NUMBER_INT);
		}
		
		$spans = $dom->find('span', 0);
		$span = str_replace('&#163;', '', $spans->plaintext);
		$span = str_replace(',', '', $span);
		$values["GOAL"] = filter_var($span, FILTER_SANITIZE_NUMBER_INT);
		
		$values["FDATE"] = "placeholder for date\n";//$abbr->getAttribute('title');
		//print_r($values);
		return $values;
        }
        
        public function validGroup($data, $badwords){
		$valid = true;
		foreach($badwords as $badword){
			if (preg_match('/'.$badword.'/i', $data["DESCRIPTION"], $matches)){
				$valid = false;
				//echo "\n ".$badword." found in ".$data["NAME"]." DESCRIPTION\n";
			}
		}
		foreach($badwords as $badword){
			if (preg_match('/'.$badword.'/i', $data["PDESCRIPTION"], $matches)){
				$valid = false;
				//echo "\n ".$badword." found in ".$data["NAME"]." PDESCRIPTION\n";
			}
		}
		return $valid;
	}
	
	public function retrieveFundedProjects($page){
		$url = 'http://spacehive.com/ProjectSearch';
		$data = array('Complete' => 'true', 'page' => $page);

		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data),
			),
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		
		$result = "<div id='projects-list'>".$result."</div>";
		
		//$html = curl_retrieve($groupurl);
		$values = array();
		
		$dom = new simple_html_dom();
		$dom->load($result);
		
		
		//$divs = $dom->find('div.pagination strong', 1)->plaintext;
		$currentProject = 0;
		$result = array();
		$currentProject = $dom->find('div.pagination strong', 1)->plaintext."/".$dom->find('div.pagination strong', 2)->plaintext;
				
		$as = $dom->find('div.image-container a');
		
		$i = 0;
		foreach ($as as $a){
                        $result[$i] = "http://spacehive.com".$a->href;
                        $i = $i + 1;
                }
                $result["currentProject"] = $currentProject;
		
		return $result;

	}
	
	public function csvImport($fileName, $tableName){
		$csvfile = __DIR__ . '/../data/' . $fileName;
		//$csvfile = __DIR__ . '\..\..\data\\' . $fileName;
		//echo $csvfile;
		try {
			$result = $this->db->query("LOAD DATA LOCAL INFILE ? INTO TABLE ".$tableName." FIELDS TERMINATED BY ',' ENCLOSED BY '" . '"' . "' LINES TERMINATED BY '\n' IGNORE 1 LINES",
                    array($csvfile));
			
		} catch (PDOException $e) {
			print "Error1!: " . $e->getMessage() . "<br/>";
		}
		//echo "<a href='/../dash'>Back to the dashboard</a>";
	}
	
	public function checkForCC($data){
	        $this->load->model('cic_model', 'cicModel', TRUE);
	        $cicM = $this->cicModel;
	        $this->load->model('charity_model', 'charityModel', TRUE);
	        $charityM = $this->charityModel;
	        
		$sresult =  $charityM->findCharity($data);
		$dresult =  $cicM->findCIC($data);
		if($sresult == null && $dresult == null){
   			$found = false;
   		}else{
			//echo "*found*";
   			$found = true;
   		}
		return $found;
	}
	
	public function getAllBackerPledges($project){
		$url = $project->URL.'#Funders';
		$html = curl_retrieve($url);	
		//$html = str_replace('expand hide','expand_hide',$html);
		$pos = strpos($html, '/ProjectView/Donations/');
		$epos = strpos($html, '?', $pos);
		$test = substr($html, $pos, $epos-$pos);
		$int = filter_var($test, FILTER_SANITIZE_NUMBER_INT);
		//echo $int;
		unset($html);
		//$url = str_replace('https://spacehive.com/','https://spacehive.com/ProjectView/ShowMoreCash/',$project)."/".$int."?page=0";
		$url = 'https://spacehive.com/ProjectView/ShowMoreCash/'.$int."?page=";
		$dom = new simple_html_dom();	
		//$html = curl_retrieve($url."0");
		$page = 0;
		//echo $html;
		//$test =  strpos($html,'CashDonationsPage next');
		//echo $test;
		//echo "<br>".$url.$page."<br>";
		do{
			$html = curl_retrieve($url.$page);
			$dom->load($html);
			$lis = $dom->find('h3');
			$ps = $dom->find('p');
			foreach ($lis as $index => $value){
				self::insertValuesIntoTable(array('PROJECTID' => $project->ID,
												  'PROJECTNAME' => $project->NAME,
												  'BACKERID' => '0',
												  'BACKERNAME' => self::safe($lis[$index]->plaintext),
												  'PLEDGED' =>str_replace(',','',str_replace('&#163;','',$ps[$index]->plaintext))),
												  'spacehivestats');
				
			}
			$page++;
			//echo "<br>".$url.$page."<br>";
		} while (strpos($html,'CashDonationsPage next') > 0);
		$dom->clear();
		unset($dom);
		unset($html);
		//return $result;
	}
	
	public function loadFinancialData(){
		$this->db->empty_table('spacehivestats');
		$temp_array = self::getAllValuesFromTable('spacehive');
		foreach ($temp_array as $element){
			self::getAllBackerPledges($element);
		}
		$datestring = "%Y%m%d%h%i%a";
		$time = time();
		$cstat = mdate($datestring, $time)."_spstat.csv";
		$this->db->query("SELECT 'PROJECTID','PROJECTNAME','BACKERID','BACKERNAME','PLEDGED' UNION SELECT PROJECTID, PROJECTNAME, BACKERID, BACKERNAME, PLEDGED INTO OUTFILE '".absolute_path()."application/data/".$cstat."' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' FROM spacehivestats");
		return $cstat;
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
	
    public function getAllValuesFromTable($table){
		$query = $this->db->get($table);
		$resultSet = $query->result();
		return $resultSet;
	}
 }
