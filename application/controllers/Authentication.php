<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');     

class Authentication extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Users');
    }

    public function login() {
        $validationResponse = $this->validateLogin();
        if($validationResponse !==true) {
            return showErrorResponse(PRECONDITION_FAILED,PRECONDITION_FAILED_CODE,$validationResponse);
        }

        $userId 		= $this->input->get_post('userId', true);
        $password 		= $this->input->get_post('password', true);
        
//        $user = $this->Users->login($userId, $password);
		$user = ['id'=>'testId'];
        $data = [];
        if(!empty($user)) {
            $token = $this->generateToken($user);
            $data['token'] = [
                'access_token' => $token,
                'token_type'=>'Bearer',
            ];
            $data['user'] = $user;
            return showSuccessResponse($data,OK_CODE,LOGIN_SUCCESSFUL);
        }
        return showErrorResponse(LOGIN_FAIL,UNAUTHORIZED_CODE);
    }

    public function addUserToken() {
        $validationResponse = $this->validateUserAddToken();
        if($validationResponse !==true) {
            return showErrorResponse(PRECONDITION_FAILED,PRECONDITION_FAILED_CODE,$validationResponse);
        }
        $data = [
            'UserId'=> trim($this->input->post('userId')),
            'Tocket'=> trim($this->input->post('token')),
            'EntryDate'=> date('Y-m-d H:i:s'),
            ];
        $result = $this->db->insert('UserTocken',$data);
        if($result) {
            return showSuccessResponse([],OK_CODE,SUCCESSFUL);
        }
        return showErrorResponse(SOMETHING_WENT_WRONG,UNAUTHORIZED_CODE);
    }

    private function validateLogin()
    {
        $this->form_validation->set_rules('userId', 'User Id', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if($this->form_validation->run()==FALSE){
            return validation_error_to_array(validation_errors());
        }else{
            return true;
        }
    }

    private function validateUserAddToken()
    {
        $this->form_validation->set_rules('userId', 'User Id', 'required');
        $this->form_validation->set_rules('token', 'Token', 'required');

        if($this->form_validation->run()==FALSE){
            return validation_error_to_array(validation_errors());
        }else{
            return true;
        }
    }

}
