<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Barricade_model extends CI_Model {
	
	private $EE;
	
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	public function quarantine_member($member_id)
	{
		$updated = FALSE;
		
		$this->EE->where('member_id', $member_id);
		$this->EE->db->update('members', array('group_id' => 2));
		
		if ($this->EE->db->affected_rows() == 1)
		{
			$updated = TRUE;
		}
		
		return $updated;
	}
	
	public function log($data, $member_id)
	{
		$this->EE->lang->loadfile('barricade');
		$this->EE->load->library('logger');
		$this->EE->logger->log_action(sprintf(lang('quarantined')), $data['username'], $member_id);
	}
}
/* End of file barricade_model.php */
/* Location: ./system/expressionengine/third_party/barricade/models/barricade_model.php */