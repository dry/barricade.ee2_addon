<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @package		Barricade
 * @author		Greg Salt <drylouvre> <greg@purple-dogfish.co.uk>
 * @copyright	Copyright (c) 2010 Purple Dogfish Ltd
 * @license		http://www.purple-dogfish.co.uk/licence/free
 * @link		http://www.purple-dogfish.co.uk/free-stuff/barricade
 * @since		Version 0.1
 * 
 */

/**
 * Changelog
 * ---------------------------------------------------------
 * Version 0.9 20101023
 * Initial public release
 * * ---------------------------------------------------------
 */
class Barricade_ext {

	public $name = 'Barricade';
	public $version = '0.9';
	public $description = 'Query member registrations against the spamforumspam.com database';
	public $settings_exist = 'y';
	public $docs_url = 'http://www.purple-dogfish.co.uk/free-stuff/barricade';
		
	public $settings = array();
	
	private $EE;
	
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->EE->load->add_package_path(PATH_THIRD.'barricade/');	
		$this->EE->load->model('barricade_model', 'barricade');
		$this->settings = $settings;
		$this->setup_donation_button();
	}
	
	/**
	 * Settings Form
	 *
	 * Set up the extension settings
	 *
	 * @access	public
	 * @return	string
	 */
	public function settings_form($current)
	{
		$site_id = $this->EE->config->item('site_id');

		if (isset($current[$site_id]))
		{
			$settings = $current[$site_id];
		}
		else
		{
			$settings = array();
		}

		$vars = array();
		$vars['form_url'] = 'C=addons_extensions';
		$vars['form_url'] .= AMP.'M=save_extension_settings';
		$vars['form_url'] .= AMP.'file=barricade';

		if (isset($settings['enabled']))
		{
			$vars['enabled'] = $settings['enabled'];
		}
		else
		{
			$vars['enabled'] = '0';
		}

		$vars['enabled_options'] = array(lang('no'), lang('yes'));

		if (isset($settings['access_key']))
		{
			$vars['access_key'] = $settings['access_key'];
		}
		else
		{
			$vars['access_key'] = '';
		}

		return $this->EE->load->view('settings', $vars, TRUE);
	}

	/**
	 * Save Settings
	 *
	 * @access	public
	 * @return	void
	 */
	public function save_settings()
	{
		if (empty($_POST))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->EE->barricade->save_settings($_POST);
	}

	/**
	 * Check Registration
	 *
	 * @access	public
	 * @param	array	$data	Member data
	 * @param	int		$member_id	Member ID
	 * @return	void
	 */
	public function check_registration($data, $member_id)
	{
		$response = FALSE;
		$quarantined = FALSE;
		$response = $this->get_response($data);
		
		if ($response)
		{
			$quarantined = $this->EE->barricade->quarantine_member($member_id);
			$updated = $this->EE->barricade->update_cerberus($data);
		}

		if ($quarantined)
		{
			$this->EE->barricade->log($data, $member_id);
		}

		if ($updated)
		{
			$this->EE->barricade->log($data, $member_id);
		}
	}
	
	/**
	 * Get Response
	 *
	 * Query the SFS API and then send the response to be parsed
	 *
	 * @access	private
	 * @param	array	Member data to be checked
	 * @return	bool	TRUE if spammer in SFS
	 */
	private function get_response($data)
	{
		foreach($data AS $key => $value)
		{
			$$key = $value;
		}

		try
		{
			$request_url = sprintf(
				'http://stopforumspam.com/api?username=%s&ip=%s&email=&%s&f=json',
				$username,
				$ip_address,
				$email
			);
			$xml = file_get_contents($request_url);

			return $this->parse_response($xml);
		}
		catch(Exception $e)
		{
			$this->EE->load->model('barricade_model', 'barricade');
			$this->EE->barricade->log($e->getMessage());
		}
	}

	private function parse_response($xml)
	{
		$spammer = FALSE;
		
		$response = json_decode($xml);
	
		if ($response->success)
		{
			$email = $response->email->appears;
			$ip = $response->ip->appears;
			$username = $response->username->appears;
			
			if ($email OR ($ip AND $username))
			{
				$spammer = TRUE;
			}
		}

		return $spammer;
	}
	
	private function setup_donation_button()
	{
		$this->name .=<<<DONATE
&nbsp;&nbsp;<form style="display: inline;" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="PX3U6RMHL84JY">
<input style="vertical-align: middle;" height="18" type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
DONATE;
	}

	/**
	 * Activate Extension
	 *
	 * Insert the hook details into the database
	 *
	 * @access	public
	 * @return	void
	 */
	public function activate_extension()
	{
		$data = array(
			'class' => __CLASS__,
			'method' => 'check_registration',
			'hook' => 'member_member_register',
			'settings' => serialize($this->settings),
			'priority' => 10,
			'version' => $this->version,
			'enabled' => 'y'
		);

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
