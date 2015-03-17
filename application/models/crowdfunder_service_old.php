<?php
 class Crowdfunder_service extends CI_Model {
 
        private static $_badwords = array('CIC', 'foundation', 'charity','social enterprise', 'cooperative',
'co-op', 'co-operative', 'ltd', 'association', 'company', 'business', 'trust');

        function __construct()
        {
                parent::__construct();
                $this->load->library('simple_html_dom');
        }
    
        public function getGroupDetails($groupurl){
		$html = curl_retrieve($groupurl);
		$values = array();
		
		$dom = new simple_html_dom();
		$dom->load($html);
		$h1s = $dom->find('h1',0);
		//print_r($h1s);
                $as = $h1s->find('a');
                //foreach($as as $a)
                //print_r($a->href);
                if (count($as) == 0){
                        echo "Invalid URL"; 
                        return null;
                } //else return count($as);
		$a = $as[0];
		//echo $a;
		$values["NAME"] = $a->innertext;
		$values["URL"] = $groupurl;
		$values["FUNDEDDATE"] = '0000-00-00 00:00:00';
		$values["DESCRIPTION"] = "";
		//print_r($values);
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
		         	$values["FUNDEDDATE"] = '0000-00-00 00:00:00';
		        }
		        $ps = $dom->find('div.project-body p');
		        //echo count($ps);		
		        foreach ($ps as $p){
			        $values["DESCRIPTION"] .= $p->plaintext;
		        }
		        $nodevalue = $dom->find('span.sofar',0);
		        $values["RAISED"] = filter_var($nodevalue, FILTER_SANITIZE_NUMBER_INT);
		        $nodevalue = $dom->find('span.target',0);
		        $values["TARGET"] = filter_var($nodevalue, FILTER_SANITIZE_NUMBER_INT);
		        $nodevalue = $dom->find('span.backers',0);
		        $values["BACKERS"] = filter_var($nodevalue, FILTER_SANITIZE_NUMBER_INT);
		        $nodevalue = $dom->find('span.days-left',0);
		        $values["DAYS"] = filter_var($nodevalue, FILTER_SANITIZE_NUMBER_INT);
		        
		        
		        
		        return $values;
		} else {
		        return null;
		}
	}
	
	public function getGroupBackers($url,$owner){
	
	        
 		
	        
	        for ($i= 'a'; $i != 'z'; $i++){
	        
		        $html = curl_retrieve($url."backers/".$i."/");		
		        $dom = new simple_html_dom();
		        $dom->load($html);
		
		        //echo $html;
		
		        $values = array();
		        $backers = array();
		
		
		        $as = $dom->find('div.who-pledged a');
		        //echo "=====".count($as)."===";
		        foreach($as as $a){
			        if($owner["NAME"] !=  str_replace(">","",$a->title)){
				        $values["NAME"] = $a->title;
				        $values["URL"]  = $a->href;
				        $backers[] = $values;
				        }
			
         		}
         		
         		//echo "\n============\n";		    
	                //print_r($backers);
	                //echo "\n============\n";
	                $dom->clear();
 		}
		return $backers;
	}
	
	public function getGroupOWner($groupurl){
		$html = curl_retrieve($groupurl);		
		$dom = new simple_html_dom();
		$dom->load($html);
		
		$values = array();
		
		$a = $dom->find('a.fname',0);
		$values["NAME"] = str_replace(">","",$a->title);
		$url = str_replace( "http://www.crowdfunder.co.uk","",$a->title);
		$values["URL"] = str_replace(">","",$url);
		
		
		
		return $values;
	}
	
	public function getBackerGroups($backerurl){
	        $id = self::getBackerId($backerurl);
	        if ($id){
	                $html = self::loadAllBackerGroups($id);
	                $dom = new simple_html_dom();
		        $dom->load($html);
		        $values = array();
		        $groups = array();
		
		        $as = $dom->find('a.project-thumb');
		        echo count($as);
		        foreach ($as as $a){
                                $values["NAME"] = str_replace(">","",$a->title);
		                $url = $a->href;
		                $values["URL"] = str_replace(">","",$url);
		                //print_r("<br>".$backerurl."/----------/");
		                //print_r($values);
		                $groups[] = $values; 
                        }
		        return $groups;
		} else {
		        return null;
		}
	}
	public function loadAllBackerGroups($id){
		$url = "http://www.crowdfunder.co.uk/user/get_events/";
		$data = array('from' => '0', 'count' => '100', 'iduser' => $id);

		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data),
			),
		);
		$context  = stream_context_create($options);
		
		//$result = file_get_contents($url, false, $context);
		
		$result = curl_post_retrieve($url, $id);
		//print_r($result);
		return $result;
	}
	public function getBackerId($backerUrl){
	        $html = curl_retrieve("http://crowdfunder.co.uk".$backerUrl);		
		$dom = new simple_html_dom();
		$dom->load($html);
		
		//echo "=================================";
		$a = $dom->find('a.older-posts',0);
		//print_r($a);
		if ($a){
		        $onclick = $a->onclick;
		        $exp1 = explode("(",$onclick);
		        $exp2 = explode(")",$exp1[1]);
		        $userID = $exp2[0];
		} else {
		        return null;
		}
		return $userID;
	}
	public function checkValidGroup($group,$filter, $newBadWords){
	//echo ".";
		$valid = true;
		if ($group["TARGET"] > $group["RAISED"]){
			$valid = false;
		}
		date_default_timezone_set("Europe/London");
		$now = new DateTime();
		$currentyear = $now->format("Y");
		$fundeddate = new DateTime($group["FUNDEDDATE"]);
		$fundedyear = $fundeddate->format("Y");
		$fundedyear += 2;
		if ($fundedyear < $currentyear){
			$valid = false;
		}
		/*echo $group["NAME"];
		foreach(self::$_badwords as $badword){
			if (strpos($group["DESCRIPTION"],$badword) !== false){
				$valid = false;
				echo "\n ".$badword." found in ".$group["NAME"]."\n";
			}
		}*/
		if ($filter){
		        //echo $newBadWords; 
		        //$tempArray = explode("/",$newBadWords);
		        //$badwords = array_merge(self::$_badwords,$tempArray);
		        foreach(self::$_badwords as $badword){
			        if (preg_match('/'.$badword.'/i', $group["DESCRIPTION"], $matches)){
				        $valid = false;
				        //echo "\n ".$badword." found in ".$group["NAME"]."\n";
			        }
		        }
		}
		return $valid;
	}
}
