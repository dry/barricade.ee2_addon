<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @package		Barricade
 * @author		Greg Salt <drylouvre> <greg@purple-dogfish.co.uk>
 * @copyright	Copyright (c) 2010 Purple Dogfish Ltd
 * @license		http://www.purple-dogfish.co.uk/licence/free
 * @link		http://www.purple-dogfish.co.uk/free-stuff/barricade
 * @since		Version 2.1.
 * 
 */

/**
 * Changelog
 * ---------------------------------------------------------
 * Version 0.1 201010
 * Initial public release
 * * ---------------------------------------------------------
 */
class Barricade_ext {

	public $name				= 'Barricade';
	public $version			= '0.9';
	public $description		= 'Query member registrations against the SpamForumSpam database';
	public $settings_exist	= 'n';
	public $docs_url			= 'http://www.purple-dogfish.co.uk/free-stuff/barricade';
		
	public $settings = array();
	
	private $EE;
	
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
    	$this->settings = $settings;
	}
	
	public function check_registration($data, $member_id)
	{
		$quarantined = FALSE;
		$response = $this->get_response($data);
		
		if ($response)
		{
			$this->EE->load->model('barricade_model', 'barricade');
			$quarantined = $this->EE->barricade->quarantine_member($member_id);
		}
		
		if ($quarantined)
		{
			$this->EE->barricade->log($data, $member_id);
		}
	}
	
	private function get_response($data)
	{
		foreach($data AS $key => $value)
		{
			$$key = $value;
		}

		$xml = file_get_contents('http://stopforumspam.com/api?username='.$username.'&ip='.$ip_address.'&email='.urlencode($email).'&f=json');

		return $this->parse_response($xml);
	}

	private function parse_response($xml)
	{
		$spammer = FALSE;
		
		$response = json_decode($xml);
		
		if ($response->success)
		{
			$email = $response->email['appears'];
			$ip = $response->ip['appears'];
			$username = $response->username['appears'];
			
			if ($email OR ($ip AND $username))
			{
				$spammer = TRUE;
			}
		}
		
		return $spammer;
	}

	public function activate_extension()
	{
		$data = array();

		$data['class']			= __CLASS__;
        $data['method']			= "check_registration";
        $data['hook']     	    = "member_member_register";
        $data['settings']	    = "";
		$data['priority']	    = 10;
		$data['version']		= $this->version;
		$data['enabled']		= "y";
		
    	$this->EE->db->insert('extensions', $data);

	}

	public function update_extension($current = '')
	{
		$status = FALSE;
		
		if ($this->version != $current)
		{
			$data = array();
			$data['version'] = $this->version;
			$this->EE->db->update('extensions', $data, 'version = '.$current);
			
			if($this->EE->db->affected_rows() == 1)
			{
				$status = TRUE;
			}
		}
		
		return $status;
	}

	public function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->delete('extensions');
	}
	

}
/* End of file ext.barricade.php */
/* Location: ./system/expressionengine/third_party/barricade/ext.barricade.php */