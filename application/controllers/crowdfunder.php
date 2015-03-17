<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Crowdfunder extends CI_Controller {

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
		$this->load->helper('my_path_helper');
	}
	
	public function scraper(){
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->helper('my_path_helper');
		$this->load->helper('date');
		
		$url = $this->input->post('seed');
		$newBadWords = $this->input->post('keywords');
		$filter = $this->input->post('filters') == 'true';
		//$colink = $this->input->post('colink') == 'true';
		$network = $this->input->post('network') == 'true';
		$finance = $this->input->post('finance') == 'true';
		
		$this->load->model('crowdfunder_service', 'cs', TRUE);
		//$this->load->model('crowdfundernet_service', 'cns', TRUE);
		//$cns = $this->cns;
		
		//todo add input 
		if ($newBadWords !=""){
			$badwords = explode('/',$newBadWords);
		} else {
			$badwords = array('CIC', 'foundation', 'charity','social enterprise', 'cooperative',
			'co-op', 'co-operative', 'ltd', 'association', 'company', 'business', 'trust');
		}
		$initialSeed = array((object) array('URL' => $url));
		$this->load->view('dash');
		
		//Colink
			$this->cs->generalColink($initialSeed, $filter, $badwords, array(true, true, true), false);
			$temp = $this->cs->getSecondGenSeeds();
			$this->cs->removeSeedFromResults($initialSeed[0]->URL);
			$this->cs->generalColink($temp, $filter, $badwords, array(false, false, false), true);
			$this->cs->removeSeedFromResults($initialSeed[0]->URL);
			//$this->cs->removeSeedFromResults($url);
			if ($network){
				$links = $this->cs->generateNetworkEdgesNodes($url);
				$data['links'] = $links;
				$this->load->view('network_dash',$data);
			}
			//TODO: Make the data CSV FILE TO DOWNLOAD
			if ($finance){				
				$data['links'] = $this->cs->loadFinancialData();//array('Data loaded into tables.','Success!');
				$this->load->view('finance_dash',$data);
			}
			//$this->load->view('dash');
			$data["qresult"] = $this->cs->getAllValuesFromTable('results');
			$this->load->view('display_data',$data);
		//End COlink
	}
	
	public function network(){
		$this->load->helper('form');
		$this->load->helper('my_path_helper');
		$this->load->helper('date');
		
		$this->load->helper('url');
		$this->load->model('crowdfunder_service', 'cs', TRUE);
		$cns = $this->cs;
		
		$cns->networkTest();
		
		//$temp = $cns->getSecondGenNetworkSeeds();
		//$cns->buildNetwork($temp, $filter, $badwords, false);
		$this->load->view('dash');
		//$data["qresult"] = $cns->generateFiles();
		//$this->load->view('display_data',$data);
	}
	
	
	public function financial(){
		$this->load->helper('form');
		$this->load->helper('my_path_helper');
		$this->load->helper('url');
		$this->load->helper('date');
		$this->load->view('dash');
		$this->load->model('crowdfunder_service', 'cs', TRUE);
		$cns = $this->cs;
		$data['links'] = $cns->createFinancialFile();
		//$data['qresult'] = array('Data loaded into tables.','Success!');
		$this->load->view('finance_dash',$data);
		
	}
	
	public function test(){
		$user = array("URL" => "/user/pgeraghty", "SEED" => "/urban-harvest");
		$test = (object) $user;
		$this->load->model('crowdfunder_service', 'cs', TRUE);
		$this->cs->emptyTables();
		//$this->cs->getProjectsByBacker($test);
	}
}

/* End of file Crowdfunder.php */
/* Location: ./application/controllers/welcome.php */
