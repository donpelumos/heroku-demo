 <?php
/**
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          https://developers.google.com/recaptcha/docs/php
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * @copyright Copyright (c) 2014, Google Inc.
 * @link      http://www.google.com/recaptcha
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
/**
 * A ReCaptchaResponse is returned from checkAnswer().
 */
class ReCaptchaResponse
{
    public $success;
    public $errorCodes;
}
class ReCaptcha
{
    private static $_signupUrl = "https://www.google.com/recaptcha/admin";
    private static $_siteVerifyUrl =
        "https://www.google.com/recaptcha/api/siteverify?";
    private $_secret;
    private static $_version = "php_1.0";
	protected $recaptchaResponse;
    /**
     * Constructor.
     *
     * @param string $secret shared secret between site and ReCAPTCHA server.
     */
    function ReCaptcha($secret)
    {
        if ($secret == null || $secret == "") {
            die("To use reCAPTCHA you must get an API key from <a href='"
                . self::$_signupUrl . "'>" . self::$_signupUrl . "</a>");
        }
        $this->_secret=$secret;
    }
    /**
     * Encodes the given data into a query string format.
     *
     * @param array $data array of string elements to be encoded.
     *
     * @return string - encoded request.
     */
    private function _encodeQS($data)
    {
        $req = "";
        foreach ($data as $key => $value) {
            $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
        }
        // Cut the last '&'
        $req=substr($req, 0, strlen($req)-1);
        return $req;
    }
    /**
     * Submits an HTTP GET to a reCAPTCHA server.
     *
     * @param string $path url path to recaptcha server.
     * @param array  $data array of parameters to be sent.
     *
     * @return array response
     */
    private function _submitHTTPGet($path, $data)
    {
        $req = $this->_encodeQS($data);
        $response = file_get_contents($path . $req);
        return $response;
    }
    /**
     * Calls the reCAPTCHA siteverify API to verify whether the user passes
     * CAPTCHA test.
     *
     * @param string $remoteIp   IP address of end user.
     * @param string $response   response string from recaptcha verification.
     *
     * @return ReCaptchaResponse
     */
    public function verifyResponse($remoteIp, $response)
    {
        // Discard empty solution submissions
        if ($response == null || strlen($response) == 0) {
            $recaptchaResponse = new ReCaptchaResponse();
            $recaptchaResponse->success = false;
            $recaptchaResponse->errorCodes = 'missing-input';
            return $recaptchaResponse;
        }
        $getResponse = $this->_submitHttpGet(
            self::$_siteVerifyUrl,
            array (
                'secret' => $this->_secret,
                'remoteip' => $remoteIp,
                'v' => self::$_version,
                'response' => $response
            )
        );
        $answers = json_decode($getResponse, true);
        $recaptchaResponse = new ReCaptchaResponse();
        if (trim($answers ['success']) == true) {
            $recaptchaResponse->success = true;
        } else {
            $recaptchaResponse->success = false;
            $recaptchaResponse->errorCodes = $answers [error-codes];
        }
		$this->recaptchaResponse = $recaptchaResponse;
        return $recaptchaResponse;
    }
}

########################################################################

	//namespace pp\mail;
/*
* Handles the racaptcha for google's api
* More @ https://developers.google.com/recaptcha/docs/verify
* @author Precious omonze
*/
 class PekkyRecaptcha /*extends ReCaptcha*/{
	 
	 
	 /**
     * the secret key
     * @var string
     */
	 
	 private $secret_key = '';
	 
    /**
     * the response key
     * @var string
     */
    private $response = '';

    /**
     * Holding the response data
     * @var json_decode
     */
	 private $response_data;
	 
    /**
     * Holding the remote ip address
     * @var string
     */
	 protected $remote_ip = '';
	 
	 //holds the recaptcha object of the other class
	 protected $recap_object;
    /**
     * Initialise the recaptcha,chai, i find it hard to spell this recaptcha thing
     * @param string $secret_key
     */
	 
	 public function __construct($secret_key,$response,$remote_ip){
		 
		 $this->secret_key = $secret_key;
		 $this->response = trim($response);
		 $this->remote_ip = $remote_ip;
//		 if(isset($this->secret_key) && isset($response)){
			 $this->recap_object = new ReCAPTCHA($this->secret_key);
		 //get from the parent class
		$this->response_data = $this->recap_object->verifyResponse($this->remote_ip, $this->response);
		  //get verify response data
		  /*old
      //  $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$this->secret_key.'&response='.$this->response);
        //$this->response_data = json_decode($verify_response);
		*/
		
//		 }
		 
	 }
	 
    /**
     * To confirm if there's an error or not
     * 
	 * @returns boolean
     */
	 /*old ones, gave me tough time *
	 public function error(){
		 
		 if($this->response_data->success){//if($this->response_data->success == true){
			 //show nothing
			 echo "Captcha was successful";
			 return false;
		 }
		 else{
			 echo "Captcha was not successful at all";
			 return true; //error
		 }
		 
	 }
	 */
	 
    /**
     * To display an error message, if any
     * @param string $msg = '' (optional)
	 * @returns string
     */
	 /*
	 public function error_msg($msg = 'failed'){
		 $error_msg = '';
		 if($this->response_data->success == true){
			 //show nothing
		 }
		 else{
			 $error_msg = $msg;
			 echo "<h1>".$error_msg."</h1>";
		 }
		 return $this->response_data->success;//$this->recap_object->verifyResponse($this->remote_ip, $this->response)->success;//$this->response_data->success;
	 }
	 
	 */
	 
	  
    /**
     * To confirm if there's an error or not
     * 
	 * @returns boolean
     */
	 
	 public function error(){
		 
		 if($this->response != ""){//if($this->response_data->success == true){
			 //show nothing
			 //echo "Captcha was successful";
			 return false;
		 }
		 else{
			// echo "Captcha was not successful at all";
			 return true; //error
		 }
		 
	 }
	 
	 
    /**
     * To display an error message, if any
     * @param string $msg = '' (optional)
	 * @returns string
     */
	 
	 public function error_msg($msg = ''){
		 $error_msg = '';
		 if($this->response != ""){
			 //show nothing
		 }
		 else{
			 $error_msg = $msg;
			
		 }
		 return $error_msg;//$this->recap_object->verifyResponse($this->remote_ip, $this->response)->success;//$this->response_data->success;
	 }
	 
	 
	 
	 
    /**
     * To display an error message, if any and kill the page
     * @param string $msg = '' (optional)
	 * 
     */
	 
	 public function terminate($msg = ''){
		 $error_msg = '';
		 if($this->response_data->success){
			 //show nothing
		 }
		 else{
			 echo $error_msg = '';
			 exit();
		 }
		 
	 }
	 
	 
    /**
     * To get the remote ip address
     * 
	 * @returns string
     */
	 
	 public function get_ip(){
		 return $this->remote_ip;
	 }
	 
 }
 