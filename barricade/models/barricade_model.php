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
		$this->EE->db->update('members', array('group_id' => 2));
		
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
	public function update_cerberus($data)
	{
		$response = FALSE;
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
