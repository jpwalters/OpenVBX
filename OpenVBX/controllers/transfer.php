<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/

 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 **/
 
 require_once(APPPATH.'libraries/twilio.php');
 require_once(APPPATH.'config/config.php');
 
 class TransferException extends Exception {}
 
 /* This controller handles transfering or modifing live calls
*/
class Transfer extends MY_Controller {

	protected $response;
	
	protected $say_params;
	
	// This is an API response controller, suppress warnings & notices
	// to avoid breakage in operation
	protected $suppress_warnings_notices = true;

	public function __construct()
	{
		// this is an API controller, suppress warning & notice output to avoid XML breakage
		ini_set('display_errors', 'Off');
		
		parent::__construct();

		$this->load->helper('cookie');

		$this->load->library('applet');
		$this->load->library('TwimlResponse');

		$this->load->model('vbx_flow');
		$this->load->model('vbx_rest_access');
		$this->load->model('vbx_user');
		$this->load->model('vbx_message');

		$this->say_params = array(
			'voice' => $this->vbx_settings->get('voice', $this->tenant->id),
			'language' => $this->vbx_settings->get('voice_language', $this->tenant->id)
		);

		$this->flow_id = get_cookie('flow_id');
		$this->response = new TwimlResponse;
	}

	function index()
	{
		redirect('');
	}
	
	function call($call_sid, $transfer_to)
	{
		//validate_rest_request();
		
		log_message("info", "Calling Transfer Start $call_sid to $transfer_to");
		
		$account = OpenVBX::getAccount();
		
		$call = $account->calls->get($call_sid);
	
		$parent_call = $account->calls->get($call->parent_call_sid);
		
		$config =& get_config();
		
		try {
			
			$parent_call->update(array(
				"Url" => $config['base_url']."twiml/transfer/".$transfer_to,
				"Method" => "POST"
			));
		}
		catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}
}