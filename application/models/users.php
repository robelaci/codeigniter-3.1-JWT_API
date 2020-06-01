<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users extends CI_Model{
	
	public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }
    
    public function login($userid, $password) {
	    $userid = mssql_escape($userid);
	    $password = mssql_escape($password);
	    $sql = "select * from UserManager where UserId ='$userid' and UserLevel='Level1'";
        $query = $this->db->query($sql);

        if ($query && !empty($result = $query->result_array())) {
            $row = $result[0];
            if(passdecode($row['Password']) == $password) {
                unset($row['Password']);
                return $row;
            }
        }
        return [];
    }
}