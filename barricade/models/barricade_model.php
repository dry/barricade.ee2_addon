<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package	Barricade
 * @author	Greg Salt <greg@purple-dogfish.co.uk>
 */
class Barricade_model extends CI_Model {
	
	/**
	 * @var	ExpressionEngine $EE
	 */
	private $EE;
	
	/**
	 * Constructor
	 * 
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	/**
	 * Quarantine Member
	 *
	 * Move a member into banned group
	 *
	 * @access	public
	 * @param	int $member_id Member ID
	 * @return	bool TRUE if the member group is updated
	 */
	public function quarantine_member($member_id)
	{
		$updated = FALSE;
		
		$this->EE->db->where('member_id', $member_id);
		$this->EE->db->update(
			'members',
			array(
				'group_id' => $this->config_get('barricade', 'barricade_banned_group', 2)
			)
		);
		
		if ($this->EE->db->affected_rows() == 1)
		{
			$updated = TRUE;
		}
		
		return $updated;
	}

	/**
	 * Update Cerberus
	 *
	 * @access	public
	 * @param	array	$data	Member data
	 * @return	mixed	$response	Cerberus Updater response or FALSE if not enabled
	 */
	public function update_cerberus($member, $key)
	{
		$response = FALSE;

		// $member contains more data than we need so let's extract
		// the keys we need

		$required = array(
			'username',
			'screen_name',
			'email',
			'ip_address',
			'url' 
		);

		$cerberus_data = $this->extract_array_template($required, $member);

		$config = array(
			'hostname' => parse_url($this->EE->config->item('site_url'), PHP_URL_HOST),
			'updater_access_key' => $key
		);

		$this->EE->load->library('cerberus', $config);

		$data = json_encode(array($cerberus_data));
		$encrypted = $this->EE->cerberus->encrypt($data);
		$response = $this->EE->cerberus->update($encrypted);

		return $response;
	}

	/**
	 * Extract Array Template
	 *
	 * Use an array template to extract keys & values from another array
	 *
	 * @access	public
	 * @param	array	$template	The array template
	 * @param	array	$data		The array with data you want to extract
	 * @return	array
	 */
	function extract_array_template($template, $data)
	{
		foreach($template AS $key)
		{
			$$key = '';
		}

		extract($data, EXTR_IF_EXISTS);

		$extracted = array();

		foreach($template AS $key)
		{
			$extracted[$key] = $$key;
		}

		return $extracted;
	}

	/**
	 * Get
	 *
	 * Retrieve a config item
	 *
	 * @access	public
	 * @param	string	$file		Config file
	 * @param	string	$key		Config item to get
	 * @param	mixed	$default	Default value
	 * return	mxied	$value		Config item or default value
	 */
	public function config_get($file, $item, $default = FALSE)
	{
		$this->EE->load->config($file);

		if ( ! $value = $this->EE->config->item($item))
		{
			$value = $default;
		}

		return $value;
	}
	
	/**
	 * Log
	 *
	 * Save a message into the CP log
	 *
	 * @access	public
	 * @param	string $data Message
	 * @param	int $member_id Member ID
	 * @return	void
	 */
	public function log($data, $member_id)
	{
		$this->EE->lang->loadfile('barricade');
		$this->EE->load->library('logger');
		$this->EE->logger->log_action(sprintf(lang('barricade_quarantined'), $data['username'], $member_id));
	}
}
/* End of file barricade_model.php */
/* Location: ./system/expressionengine/third_party/barricade/models/barricade_model.php */
