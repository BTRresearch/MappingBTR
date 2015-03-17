<?php
if (!defined('BASEPATH'))
  exit('No direct script access allowed');

class Download extends CI_Controller {
	
  function __construct()
  {
		parent::__construct();
		$this->load->helper(array('form', 'url'));
	}
  
  //index, just load the main page
	public function index() {


		//load the view/download.php
		$this->load->view('download');
		
	}
		//IF download/plaintext,
	public function plaintext($fileName) {
		//load the download helper
		//$fileName = $this->input->get('f');
		$this->load->helper('download');
		//set the textfile's content 
		//$data = 'Hello world! Codeigniter rocks!';
		//set the textfile's name
		//$temp_array = explode('/',$fileName);
		$name = file_get_contents("application/data/".$fileName);
		//use this function to force the session/browser to download the created file
		force_download($fileName, $name);
	}
		//IF download/upload,
	

}
