<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Spacehive extends CI_Controller {

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
	
	public function safe($inp) {
		if(is_array($inp))
			return array_map(__METHOD__, $inp);

		if(!empty($inp) && is_string($inp)) {
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
		}

		return $inp;
	}
	
	
	public function importCSVFiles(){
	        $this->load->helper('form');
		$this->load->helper('url');
		$this->load->view('dash');
		$this->load->model('spacehive_service', 'spaceService', TRUE);
                $shs = $this->spaceService;
                
                $this->load->model('cic_model', 'cicModel', TRUE);
	        $cicM = $this->cicModel;
	        $this->load->model('charity_model', 'charityModel', TRUE);
	        $charityM = $this->charityModel;
                
                $result = $cicM->getAll();
                if ($result == 0){
                        $shs->csvImport("cics.csv","cic");
                }
                $result = $charityM->getAll();
                if ($result == 0){
                        $shs->csvImport("charities.csv","charity");
                }
                
		//$shs->csvImport("charities.csv","charity");
		//$shs->csvImport("cics.csv","cic");
		$this->load->view('display_data',array("Loaded"));
	}
	
	public function loadData(){
		//$filter = $this->input->post('filters') == 'true';
		$filter = true;
		$newBadWords = $this->input->post('keywords');
		if ($newBadWords !=""){
			$badwords = explode('/',$newBadWords);
		} else {
			$badwords = array('CIC', 'foundation', 'charity','social enterprise', 'cooperative',
			'co-op', 'co-operative', 'ltd', 'association', 'company', 'business', 'trust');
		}
		//$newBadWords = explode('/', $this->input->post('keywords'));
		//echo $filter;
		$this->load->model('cic_model', 'cicModel', TRUE);
		$cicM = $this->cicModel;
		$this->load->model('charity_model', 'charityModel', TRUE);
		$charityM = $this->charityModel;
		
		$this->load->helper('form');
		$this->load->view("dash");
		$this->load->helper('url');
		$this->load->helper('my_path_helper');
		$this->load->helper('date');
		$this->load->model('spacehive_service', 'spaceService', TRUE);
		$shs = $this->spaceService;
		$this->load->model('spacehive_model', 'spaceModel', TRUE);
		$spaceModel = $this->spaceModel;
		$spaceModel->deleteAll();
		$finance = $this->input->post('finance') == 'true';
		
		$result = $cicM->getAll();
		//print_r($result);
		if (!$result){
				
				$shs->csvImport("cics.csv","cic");
		}
		$result = $charityM->getAll();
		if (!$result){
				$shs->csvImport("charities.csv","charity");
		}
				
				
				
				$pages = array();
		$data = $shs->retrieveFundedProjects(0);
		$number =  $data["currentProject"];
		unset($data["currentProject"]);
		$pages = array_merge($pages,$data);
		//self::printData($data);
		//echo $number;
		$p = eval('return '.$number.';');
		
		$i = 0;
		while ($p < 1){
			//echo "\n".$i."\n";
			$i = $i+1;
			$data = $shs->retrieveFundedProjects($i);
			$number =  $data["currentProject"];
			unset($data["currentProject"]);
			$pages = array_merge($pages,$data);
			//self::printData($data);
			$p = eval('return '.$number.';');
			//echo "<hr>".$p."<hr>";
		}
		$i = 0;
		//echo "<hr>".$data["NAME"].count($pages)."<hr>";
		foreach ($pages as $key => $page){
				$gresult = $spaceModel->getProjectByUrl($page);
			if($gresult == null){
					$data = $shs->getProjectDataFromUrl($page);
					if ($filter){
							$check = (bool) $shs->validGroup($data,$badwords);
							$test = (bool) $shs->checkForCC(self::safe($data["PROMOTER"]));
					}
					else{
							$check = true;
							$test = false;
					}
					
					if (!$check || $test){
						$i = $i +1;
						unset($pages[$key]);
					} else {
				
							$groupid = $spaceModel->addProject($data);
							//echo $groupid;
						}
			}
		}
		//echo "\n ".$i." deleted items\n";
		$data["qresult"] = $spaceModel->getAll();
		if ($finance){
			$data['links'] = $shs->loadFinancialData();
			$this->load->view('finance_dash',$data);
		}
		$this->load->view("display_data",$data);
		
		
		
	}
	
	public function loadFinancialData(){
		$this->load->helper('form');
		$this->load->view("dash");
		$this->load->helper('url');
		$this->load->helper('my_path_helper');
		$this->load->helper('date');
		$this->load->model('spacehive_service', 'spaceService', TRUE);
		$shs = $this->spaceService;
		$data['links'] = $shs->loadFinancialData();
		$this->load->view('finance_dash',$data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
