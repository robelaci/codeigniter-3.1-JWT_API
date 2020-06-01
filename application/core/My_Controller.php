<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $userId = null;
    protected $userLevel = '';
    function __construct()
    {
        parent::__construct();
        $this->load->model('jwt/JWT');
        $this->verifyToken();

    }

    private function verifyToken()
    {
        if($this->isExceptAuthentication()) {
            return true;
        }
        $privateKey = $this->config->item('app_private_key');
        $token = $this->getBearerToken();
        if($token == null) {
            showErrorResponse("Authorization token Required",UNAUTHORIZED_CODE);
            die();
        }
        try {
            $payload = JWT::decode($token,$privateKey,['HS256']);
            // set user info
            $this->userId = $payload->userId;
            $this->userLevel = $payload->userLevel;
        } catch (\Exception $ex) {
           showErrorResponse($ex->getMessage(),UNAUTHORIZED_CODE);
            die();
        }
        return $payload;
    }

    public function generateToken($user) {
        $payload = [
            'iat' => time(),
            'iss' =>'localhost',
            'exp' => time() + (24*60*60),
            'userId' => isset($user['UserId']) ? $user['UserId'] : '',
            'userLevel' => isset($user['UserLevel']) ? $user['UserLevel'] : '',
        ];
        try {
            $privateKey = $this->config->item('app_private_key');
            $token = JWT::encode($payload,$privateKey);
            return $token;
        } catch (\Exception $ex) {
            $token = false;
        }
        return $token;

    }

    /**
     * Get header Authorization
     * */
    public function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
     * get access token from header
     * */
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            // Replacing Multiple space by one space
            $headers = preg_replace('/\s+/',  ' ', $headers);
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    private function isExceptAuthentication()
    {
        $class = $this->router->fetch_class();
        $method = $this->router->fetch_method();
        $currentMethod = $class.'/'.$method;
        $exceptAuthenticationMethod = is_array($this->config->item('except_authentication_method')) ? $this->config->item('except_authentication_method') : [];
        if(in_array($currentMethod,$exceptAuthenticationMethod)) {
            return true;
        }
        return false;
    }

}
