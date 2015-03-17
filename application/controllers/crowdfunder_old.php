<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Crowdfunder_old extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->view('dash');
	}
	
	public function test(){
	        $this->load->helper('form');
		$this->load->helper('url');
	        $url = $this->input->post('seed');
	        $text = $this->input->post('keywords');
	        //echo $url;
	        $this->load->view('display_data',array("message" => $text));
	}
	
	public function logData($data){
	        echo "<hr><hr>";
   		print_r($data);
   		echo "<hr><hr>";
	}
	
	public function analize(){
	        
	        $this->load->helper('form');
		$this->load->helper('url');
		$this->load->view('dash');
	        $url = $this->input->post('seed');
	        $newBadWords = "";//$this->input->post('keywords');
	        ///$url = "http://www.crowdfunder.co.uk/urban-harvest/";
	        //$url = "http://www.crowdfunder.co.uk/growafuture/";
	        $filter = $this->input->post('filters') == 'true';
	        //echo $filter;
	        $this->load->model('crowdfunder_service', 'cs', TRUE);
	        $this->load->model('group_model', 'gm', TRUE);
	        $this->load->model('backer_model', 'bm', TRUE);
	        $this->load->model('backergroup_model', 'bgm', TRUE);
	        //$this->load->model('group_model', 'gm', TRUE);
	        
	        
	        $cs = $this->cs;
   		$group = $cs->getGroupDetails($url);
   		$owner = $cs->getGroupOwner($url);
   		$gm = $this->gm;
   		$bm = $this->bm;
   		$bgm = $this->bgm;
   		
   		//reset db
   		$gm->deleteAll();
   		$bm->deleteAll();
   		$bgm->deleteAll();
   		
   		$gresult = $gm->getGroupByUrl($group["URL"]);
   		
   		if($gresult == null){
   			$groupid = $gm->addGroup($group);
   		}else{
   			$groupid = $gresult->ID;
   		}
   		
   		$backers = $cs->getGroupBackers($url,$owner);
   		
   		foreach($backers as $backer){
   			
   			$bresult = $bm->getBackerByName($backer["NAME"]);
   			if($bresult == null){
   				$backerid = $bm->addBacker($backer);
   			}else{
   				$backerid = $bresult->ID;
   			}
   			
   			$backeridgroupid = array("BACKERID" => $backerid, "GROUPID" => $groupid);
   			$bgm->addBackerGroup($backeridgroupid);
   			
   			$backergroups = $cs->getBackerGroups($backer["URL"]);
   			//print_r( $backergroups);
			//echo $backer["URL"];	
			if ($backergroups)		
   			foreach($backergroups as $backergroup){
   				$group = $cs->getGroupDetails($backergroup["URL"]);
   				if($group != null){
           				//check group restrictionss
           				if($filter){
					
           					$isvalid = $cs->checkValidGroup($group,true,$newBadWords);
					        //echo "\n".$group["NAME"]."==============================================".$isvalid;
           				}else{
					
           					$isvalid = $cs->checkValidGroup($group,false,$newBadWords);
					        //echo "\n".$group["NAME"]."\n======================#NOFILTER===============".$isvalid;
           				}
           				if ($isvalid){
           					//check that group doesn't exist
           					$gresult = $gm->getGroupByUrl($backergroup["URL"]);
           					if ($gresult == null){
           						//we add the new group to the db
           						$ngroupid = $gm->addGroup($group);
           					}else{
           						$ngroupid = $gresult->ID;
           						//self::logData($ngroupid);
           					}
           					//chect if that relation exists
           					$gbresult = $bgm->getRelationID($ngroupid,$backerid);
           					if ($gbresult == null) {
           						//we add the new relation to the db
           						$backeridgroupid = array("BACKERID" => $backerid, "GROUPID" => $ngroupid);
           						$bgm->addBackerGroup($backeridgroupid);
           					}
           					//if it exists we don't do anything.
           				}
   				}
   			}
   		}
		//==== Co link ====//
		$result = array();
		//Gets all groups ID's different from the seed's one from the table 'group'.
   		$ngroups = $gm->getNonEqual($groupid);
   		$links = false;
   		//for each of these group ID's
   		foreach($ngroups as $ng){
   		// Gets the number of rows in which that ID appears in the table backergroup
   			$commonbacker = $bgm->getBackersByGroupId($ng->ID);
   			//If this number is greater than 1, there exist co-link between the new group and the seed.
   			//self::logData($commonbacker);
   			//echo "<br><br>".count($commonbacker)."<br><br>";
   			if (count($commonbacker) > 1){
   				$links = true;
   				$str = $ng->NAME;
   				//echo $str;
   				$result[] = $str;
   			}
   		}
   		if (!$links) {
   		        $str = "\n We couldn't find any links \n";
   		        $result[] = $str;
   			//echo "\n We couldn't find any links \n";
   		}
		$data["qresult"] = $result;
		$this->load->view("display_data",$data);
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
