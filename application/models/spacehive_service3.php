<?php
 class Spacehive_service extends CI_Model {
 
        private static $_badwords = array('Community Interest company', 'CIC', 'Foundation', 'Charity', 'Social enterprise', 'Cooperative', 'Co-op', 'Co-operative');

        function __construct()
        {
                parent::__construct();
                $this->load->library('simple_html_dom');
        }
  
        public function getProjectDataFromUrl($url){
                $html = curl_retrieve($groupurl);
		$values = array();
		
		$dom = new simple_html_dom();
		$dom->load($html);
		
		$class = $dom->find('h1.project',1);
		if (!$class){
		        return null;
		} else {
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
		$dom->load($html);
		$ps = $dom->find('div.left promotor p');
		if ($ps){
		        foreach ($ps as $p){
				$values["PDESCRIPTION"] .= $p->plaintext;
			}
		}
		$html = curl_retrieve("http://spacehive.com".$nLink);
		$dom->load($html);
		$ps = $dom->find('h3[id=therm-pledged] span');
		if ($ps){
		        foreach ($ps as $p){
				$values["PDESCRIPTION"] .= $p->plaintext;
			}
		}
		
        }
        
 }
