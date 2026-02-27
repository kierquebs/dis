<?php
/**
 * AUTOMATED PROCESS
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MX_Controller {
    private $API_KEY = 'b0f733fd80a844c7b142527a6bc34aab'; // This is a 32-character random alphanumeric string (hexadecimal format), which is pretty standard and strong enough for secure usage.
    // private $API_KEY = 'f3a9d2b7c1e84672b5f8a4d9e3c72a5f' ; UAT KEY
	public function	__construct(){
		parent::__construct();	
        $this->load->model('Sys_model');
	}


    public function getMerchantID($pa_id){

        // GET MERCHANT_ID
        $this->db->select('MERCHANT_ID'); 
        $this->db->from('pa_header');
        $this->db->where('PA_ID', $pa_id); 
        $query = $this->db->get();
        $merchant_id = $query->row()->MERCHANT_ID; 

        return $merchant_id;

    }

    public function getSI($pa_id){
        // GET MARKETING FEE AND VAT
        $this->db->select('SUM(MARKETING_FEE) as marketing_fee, SUM(VAT) as vat');
        $this->db->from('pa_detail');
        $this->db->where('PA_ID', $pa_id);
        $query = $this->db->get();
        $result = $query->row(); 

        return $result;
    }
	
	public function getReimDate($pa_id){
        $this->db->select('REIMBURSEMENT_DATE');
        $this->db->from('pa_header');
        $this->db->where('PA_ID', $pa_id);
        $query = $this->db->get();
        $result = $query->row(); 
		
		return $result;
    }
    
    public function getSiDetails() {
    $api_key = $this->input->get_request_header('X-API-KEY');

    if ($api_key !== $this->API_KEY) {
        $response = [
            'status' => false,
            'message' => 'Unauthorized: Invalid API key'
        ];
        $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        return;
    }
	

    $pa_id = $this->input->get('pa_id');

    if (!$pa_id) {
        $response = [
            'status' => false,
            'message' => 'pa_id is required'
        ];
        $this->output
             ->set_status_header(400)
             ->set_content_type('application/json')
             ->set_output(json_encode($response));
        return;
    }

    // Check if pa_id is numeric
    if (!is_numeric($pa_id)) {
        $response = [
            'status' => false,
            'message' => 'pa_id must be numeric'
        ];
        $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        return;
    }

    // Check if pa_id exists in pa_header
    $this->db->where('PA_ID', $pa_id);
    $query = $this->db->get('pa_header', 1);
    $row = $query->row();

    if (!$row) {
        $response = [
            'status' => false,
            'message' => 'pa_id not found'
        ];
        $this->output
            ->set_status_header(404)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        return;
    }

    $merchant_id = $row->MERCHANT_ID;

    $result = $this->getSI($pa_id);

    // Get CP_ID
    $this->db->select('CP_ID');
    $this->db->from('branches');
    $this->db->where('MERCHANT_ID', $merchant_id);
    $query = $this->db->get();
    $cp_id = $query->row() ? $query->row()->CP_ID : null;

    // Get MERCHANT DETAILS
    $this->db->select('LegalName, PayeeName, BankAccountNumber, MeanofPayment, Address, TIN');
    $this->db->from('cp_merchant');
    $this->db->where('CP_ID', $cp_id);
    $query = $this->db->get();
    $merchant = $query->row();
	
	$pa_header = $this->getReimDate($pa_id);

    $data = [
        'PA_ID' => $pa_id,
        'MERCHANT_ID' => $merchant_id,
        'CP_ID' => $cp_id,
        'MERCHANT' => $merchant,
        'SI_DETAILS' => [
            'MARKETING_FEE' => $result->marketing_fee,
            'VAT' => $result->vat,
			'REIMBURSEMENT_DATE' => $pa_header->REIMBURSEMENT_DATE,
			'SERVICE' => 'Pluxee Credits'
        ]
    ];

    $response = [
        'status' => true,
        'message' => 'SI details fetched successfully',
        'data' => $data
    ];

    $this->output
         ->set_content_type('application/json')
         ->set_output(json_encode($response));
}

	
}
