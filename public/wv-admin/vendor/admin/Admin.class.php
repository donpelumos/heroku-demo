<?php
/**
 * Admin class
 *
 * This handles majority of the admin processes, Please edit only if you know what you're doing :-|
 * I don't mind anyone improving on this :)
 *
 * @author Precious Omonzejele <me@codexplorer.ninja>
 */
class Admin{
    /**
     * Code/Site build state
     *
     * if the value is not 'production', it tries to show immediate code debugs :)
     * @var string
     */
    private $build_state = 'production';
    /**
     * Api base
     *
     * @var string
     */
    private $api_base = 'https://waveustransit.com/_wv/jpk/';
    /**
     * @var Curl obj
     */
    private $curl = null;
    /**
     * curl url
     *
     * @var string
     */
    private $c_url = '';
    /**
     * Response from the curl
     *
     * @var string
     */
    private $raw_response = '';
    /**
     * Response from the curl converted to array
     *
     * @var array
     */
    private $raw_response_array = array();
    /**
     * Response error from the curl
     *
     * @var string
     */
    private $response_error = '';
    /**
     * Store any error gotten here
     *
     * This is what we'll most likely use to display readable errors to the users
     * @var string
     */
    private $error = '';
    /**
     * success state
     *
     * @var bool
     */
    private $success_state = null;
    /**
     * Error reason gotten from the api
     *
     * @var array
     */
    public $err_reason = array('code'=>'','txt'=>'');
    /**
     * Stores the data of the response
     *
     * @var array
     */
    private $data = array();
    /**
     * Stores the data size response
     *
     * gives proper value for responses that are enclosed in a key value :)
     *
     * @var int
     */
    private $data_size = 0;
    /**
     * To store mail_sent key values
     *
     * @var bool
     */
    private $mail_sent = null;
    /**
     * Stores the msg to display when mail sent is false
     *
     * @var string
     */
    private $mail_sent_error = '';
    /**
     * Id's seperator when submitted to api in bulk
     */
    private $ids_sep = ',';
    /**
     * Stores the current menu
     *
     * @var string
     */
    protected $current_menu = '';
    /**
     * Stores admin_type
     *
     * so as not to keep involking the curl and make load time faster
     * @var int
     */
    protected $current_admin_type = 0;

    /**
     * Condstrucdor :)
     */
    public function __construct(){
        /** Order types
         * You are adviced to use these constants anywhere you want to signify the order codes
         */
        define('ORDER_CANCEL_CODE',-1);
        define('ORDER_HOLD_CODE',1);
        define('ORDER_PROCESS_CODE',2);
        define('ORDER_COMPLETE_CODE',3);

        $this->curl = new Curl();
        if($_SERVER['SERVER_NAME'] != 'waveustransit.com'){//not in production environment
            $this->api_base = 'http://waveustransit.com/_wv/jpk/';
            //turn on bug report
            $this->build_state = 'test';
        }
        //for dev purposes
        $this->curl->get_auth($this->api_base);
        if($this->curl->get_error())
            $this->api_base = 'http://localhost/waveus/api/';
    }
    /**
     * get curl url
     *
     * @return string
     */
    public function get_c_url(){
        return $this->c_url;
    }
    /**
     * Call api endpoint
     *
     * @param string $endpoint ignore first /
     * @param string $namespace e.g. general
     * @return bool
     */
    public function get_endpoint($endpoint,$namespace = 'admin'){
        $endpoint = ltrim($endpoint,'/');//trim any forward slash from the left
        $endpoint_a = explode('.php?',$endpoint);//split into 2 so we can add the url encode
        //encode the param part :), then convert '=' and '&' back to their values to avoid issues, badass
        $endpoint_a[1] = str_ireplace('%3D','=',urlencode($endpoint_a[1]));
        $endpoint_a[1] = str_ireplace('%26','&',$endpoint_a[1]);
        //join back
        $endpoint = join('.php?',$endpoint_a);
        $url = $this->sort_namespace($namespace).$endpoint;
        $r = $this->curl->get_auth($url);
        if($this->sort_response($r))
            return true;
        else
            return false;
    }
    /**
     * sorts the namespace for curl
     *
     * @param string $namespace
     * @return string
     */
    private function sort_namespace($namespace){
        $result = '';
        if(strpos($namespace,'admi') !== false)
            $result = '_admin';
        else if(strpos($namespace,'dispa') !== false)
            $result = '_dispatcher';
        else if(strpos($namespace,'use') !== false)
            $result = '_user';
        else
            $result = '_general';
        $result = $this->api_base.$result.'/';
        return $result;
    }
    /**
     * Sorts the response gotten from curl
     *
     * @param json|string $response
     * @return bool
     */
    protected function sort_response($response){
        $this->reset();
        $response = (string)$response;
        $this->raw_response = $response;
        if($this->curl->get_error()){
            $this->response_error = $this->curl->get_error();
            if($this->get_build_state() != 'production')
                $this->error = $this->get_raw_response();
            else
                $this->error = 'Sorry, an internal error occured on API request call, try again later '.$this->contact_dev_msg();//display to the user too
            return false;
        }
        //go ahead
        $response_a = json_decode($response,true);//decode to array
        if(json_last_error() != 0){
            $this->response_error = json_last_error_msg();
            if($this->get_build_state() != 'production')
                $this->error = $this->get_raw_response();
            else
                $this->error = 'Sorry, an internal API error occured, try again later '.$this->contact_dev_msg();//display to the user too
            return false;
        }
        $this->raw_response_array = $response_a;
        //since all passed , get success state
        if($response_a['success'] == "true")
            $this->success_state = true;
        else{
            $this->success_state = false;
            $this->err_reason['code'] = $response_a['reason_code'];
            $this->err_reason['txt'] = $response_a['reason_txt'];
        }
        //unset success state and store the rest in data
        unset($response_a['success']);
        //check if there's mail_sent ish
        if(isset($response_a['mail_sent'])){
            if($response_a['mail_sent'] == 'true')
                $this->mail_sent = true;
            else{
                $this->mail_sent = false;
                $this->mail_sent_error = 'the mail could not be sent';
            }
        }
        //now check if there are extra key names, chai, what you get from not following standard :(
        if(array_key_exists('order_list',$response_a))
            $this->data = $response_a['order_list'];
        else if(array_key_exists('data',$response_a))
            $this->data = $response_a['data'];
        else
            $this->data = $response_a;
        //check for size
        if($this->success_state)
            $this->data_size = (array_key_exists('size',$response_a)) ? (int)$response_a['size']: count($this->data);
        return true;
    }
    /**
     * Clear error values
     */
    private function clear_error(){
        $this->response_error = '';
        $this->success_state = null;
        $this->error = '';
    }
    /**
     * Reset values for a new round
     */
    private function reset(){
        $this->clear_error();
        $this->err_reason = array('code'=>'','reason'=>'');//just changed :)
        $this->data = array();
        $this->data_size = 0;
        $this->raw_response = '';
        $this->raw_response_array = array();
        $this->mail_sent = null;
        $this->mail_sent_error = '';
    }

    /**
     * get response error property
     *
     * @return mixed
     */
    public function get_response_error(){
        return $this->response_error;
    }

    /**
     * Gets the error msg
     *
     * @return string
     */
    public function get_error(){
        return $this->error;
    }
    /**
     * Gets err_reason data
     *
     * @return array|bool , false if empty
     */
    public function get_reason(){
        $r = $this->err_reason;
        if(empty($r['code']) && empty($r['txt']))
            return false;
        return $r;
    }
    /**
     * Returns a contact dev custom msg
     *
     * @return string
     */
    public function contact_dev_msg(){
        if($this->get_build_state() != 'production')
            return $this->get_reason();
        return 'or contact the developer if this error persists';
    }
    /**
     * Returns the build_state
     *
     * @return string
     */
    public function get_build_state(){
        return strtolower(trim($this->build_state));
    }
    /**
     * Sorts the reason out for proper outputing
     *
     * @param string $user_error (optional) what to display when error is caused by user, if empty, displays msg from api result
     * @return mixed|bool, returns false if the reasons are empty
     */
    public function sort_reason($user_error = ''){
        $r_c = $this->get_reason();
        if($r_c == false)
            return false;
        switch($r_c['code']){
            case -1:
            case 0://internal server error,give an easy message to the user
                if($this->get_build_state() != 'production')
                    $this->error = $r_c['txt'];
                else
                    $this->error = 'Sorry an internal error occured, Please try again later '.$this->contact_dev_msg();
                break;
            default:
                $this->error = (empty(trim($user_error))) ? $r_c['txt'] : $user_error;
        }
    }
    /**
     * gets raw response from url
     *
     * @return json|string
     */
    public function get_raw_response(){
        return $this->raw_response;
    }
    /**
     * gets raw response array from url
     *
     * @return array
     */
    public function get_raw_response_array(){
        return $this->raw_response_array;
    }
    /**
     * gets success state
     *
     * @return bool
     */
    public function get_success_state(){
        return $this->success_state;
    }
    /**
     * Gets the mail sent state, should only be called when the endpoint returns a mail_sent param
     *
     * @return bool|null , null if the endpoint doesnt have the mail_sent key
     */
    public function get_mail_sent(){
        return $this->mail_sent;
    }
    /**
     * Gets the mail sent error text
     *
     * @param string $msg(optional), the message you want to show instead of the default
     * @return string
     */
    public function get_mail_sent_error($msg = ''){
        if(empty($this->mail_sent_error))//no need to display
            return '';
        $msg = isset($msg) ? trim($msg) : '';
        if(!empty($msg))
            $this->mail_sent_error = $msg;
        return $this->mail_sent_error;
    }
    /**
     * gets data
     *
     * @return array
     */
    public function get_data(){
        return $this->data;
    }
    /**
     * gets data size
     *
     * @return array
     */
    public function get_data_size(){
        return $this->data_size;
    }
    /**
     * The waveus logo url
     *
     * @return string
     */
    public function logo_url(){
        return 'images/icon/dashboard-logo.png';
    }
    /**
     * Filter order status to text
     *
     *  3 -- completed
     *  2 -- processing,en-route,enroute,en route
     *  1 -- on hold,held,on-hold
     *  -1 -- cancelled,canceled
     *
     * @param int $status
     * @param string $output (optional) the output format you want, num,past,continuous,present
     * @return string
     */
    public function filter_order_status($status,$output = 'present'){
        $output = isset($output) ? trim($output) : "";
        $result = false;
        $result_array = array();
        /**
         * keeps list of order statuses
         * id => array(phrases,word_type)
         * word_type follows 'present,past,continuous' respectively
         * @var array
         */
        $order_statuses = array(
            ORDER_COMPLETE_CODE => array('completed','complete,completed,completing'),
            ORDER_PROCESS_CODE => array('processing,en-route,enroute,en route','process,processed,processing'),
            ORDER_HOLD_CODE => array('on-hold,held,on hold','on hold,held,on hold'),
            ORDER_CANCEL_CODE => array('cancelled,canceled','cancel,canceled,canceling'),
        );

        foreach($order_statuses as $int => $values){
            if($status == $int || strpos($values[0],$status) !== false){
                $result_array = array($int,$values);
                break;
            }
        }
        if(!empty($result_array)){//means its an actual value
            $word_type = explode(',',$result_array[1][1]);
            switch($output){
                case 'present':
                    $result = $word_type[0];
                    break;
                case 'past':
                    $result = $word_type[1];
                    break;
                case 'continuous':
                    $result = $word_type[2];
                    break;
                default:
                    $result = $result_array[0];
            }
        }
        return $result;
    }
    /**
     * Get the corresponding user type number or text
     *
     * @param string $value
     * @param bool $return_txt(optional),default is false, if set to true, respective text will be returned
     * @return mixed
     */
    public function get_user_type($value,$return_txt = false){
        $result = 0;
        $result_txt = '';
        if(strpos($value,'use') !== false){
            $result = 1;
            $result_txt = 'user';
        }
        else if(strpos($value,'dispa') !== false){
            $result = 2;
            $result_txt = 'dispatcher';
        }
        else if(strpos($value,'admi') !== false){
            $result = 3;
            $result_txt = 'admin';
        }
        return ($return_txt == true) ? $result_txt : $result;
    }
    /**
     * Checks if user is logged in
     *
     * @return bool
     */
    public function is_logged_in(){
        if(isset($_SESSION['user']) && !empty($_SESSION['user']) )
            return true;
        else
            return false;
    }
    /**
     * handles the user validation and redirects to the $redirect page if false
     *
     * @param string $redirect(optional) url to redirect if the user isn't authorised
     */
    public function authorise($redirect = 'login.php'){
        if(!$this->is_logged_in()){
            //retrieve the page it was coming from, so in login, we can redirect back
            $current_page = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
            header("location:".$redirect."?red={$current_page}");
            exit();
        }
    }
    /**
     * Get the logged in user session
     *
     * @return array
     */
    public function get_session(){
        return $_SESSION['user'];
    }
    /**
     * Sets the user session
     *
     * @param string $value
     */
    private function set_session($value){
        $_SESSION['user'] = $value;
    }
    /**
     * Gets current user info
     *
     * @param string $id
     * @param int $user_type (optional)
     * @return array|bool
     */
    public function get_user_data($id,$user_type = 3){
        $url = 'get_user_details.php?user='.$id.'&user_type='.$user_type;
        if(!$this->get_endpoint($url,'general'))
            return false;
        if(!$this->get_success_state()){
            return false;
        }
        return $this->data;
    }
    /**
     * Gets current user info
     *
     * @param string $id
     * @return array|bool
     */
    public function get_order_data($id){
        $url = 'get_order_details.php?user_id='.$this->get_session().'&order_id='.$id.'&user_type=3';
        if(!$this->get_endpoint($url,'general'))
            return false;
        if(!$this->get_success_state()){
            return false;
        }
        return $this->data;
    }
    /**
     * Displays amount
     *
     * @param mixed $value
     * @return string
     */
    public function amount($value){
        $value = (float)$value;
        return '₦'.number_format($value,2,'.',',');
    }
    /**
     * for displaying the date_time properly
     *
     * @param string $value, the date to be processed
     * @param string $format, the format to convert it to, takes in any correct date format, default is "".
     * @return string
     */
    public function date_time_display($value,$format = ''){
        $value = trim($value);
        $value = strtotime($value);//strtr($value, '/', '-');
        $format = trim($format);
        //START WORK
        $event = $value;//DAY OF EVENT
        $now = strtotime("now");
        $today = strtotime("today");
        $yesterday =  strtotime("yesterday");
        //FIND OUT IF IT'S YESTERDAY, OR TODAY, TO DISPLAY IT WELL
        //CALCULATE THE STUFF WELL, SINCE IT'S IN SECONDS
        $_24 = 24*60*60;//24 hours in seconds
        $_48 = $_24 * 2;//48 HOURS IN SECONDS
        $current = $now - $event;
        $today_event = $today + $_24;
        $yesterday_event = $yesterday + $_48;
        $date = '';
        if(!empty($format)){
            $date = date($format,$value);
        }
        else{
            if(($event >= $today) && ($event <= $today_event)){//THE EVENT IS STILL WITHIN 24 HOURS
                $date = "Today @ ".date('h:i A', $value);//.$dt->format("h:m A");
            }
            else if(($event >= $yesterday) && ($event <= $yesterday_event)){//THE EVENT IS STILL WITHIN 48 HOURS BUT GREATER THAN 24 HOURS
                $date = "Yesterday @ ".date('h:i A', $value);//.$dt->format("h:m A");
            }
            else{
                //$date = $dt->format("d M, Y")." @ ".$dt->format("h:m A");
                $date = date('d M, Y', $value). " @ ".date('h:i A', $value);
            }
        }
        return $date;
    }

    /**
     * Handles login aspect of a user
     *
     * @param string email
     * @param string password
     * @return bool
     */
    public function login($email,$password){
        $e = isset($email) ? trim(strtolower($email)) : '';
        $p = $password;
        if(empty($e)){
            $this->error = 'Email is empty';
            return false;
        }
        if(empty($p)){
            $this->error = 'Password is empty';
            return false;
        }
        //so lets continue our something
        $url = 'login.php?email='.$e.'&password='.$p;
        if(!$this->get_endpoint($url)){//seems it didnt go well,
            // return false because the get_endpoint processes it all
            return false;
        }
        //get status
        if($this->success_state){
            $this->set_session($this->data['id']);
            $location = '';
            if(isset($_GET['red']))
                $location = $_GET['red'];
            header("location:".$location);
            return true;
        }
        //continue, its false sort reason ish out
        $this->sort_reason('Incorrect login details');
        return false;
    }
    /**
     * Logs the user out
     */
    public function logout(){
        unset($_SESSION['user']);
        $this->current_admin_type = 0;
        header("location:login.php");
    }
    /**
     * Gets the current admin type
     *
     * @return int
     */
    public function get_current_admin_type(){
        $type = $this->current_admin_type;
        if($type == 0){
            $user_data = $this->get_user_data($this->get_session(),3);
            if(!$user_data){
                $this->current_admin_type = 0;
                return 0;
            }
            //set the admin_type value so you dont need to recall api everytime
            $this->current_admin_type = (int)$user_data['admin_type'];
            $type = $this->current_admin_type;
        }
        return $type;
    }
    /**
     * Get the corresponding admin type number or text
     *
     * @param string $value
     * @param bool $return_txt(optional),default is false, if set to true, respective text will be returned
     * @return mixed
     */
    public function get_admin_type($value,$return_txt = false){
        $result = 0;
        $result_txt = '';
        if(strpos($value,'super') !== false || strpos($value,'main') !== false || $value == 1){
            $result = 1;
            $result_txt = 'super admin';
        }
        else if(strpos($value,'sub') !== false || $value == 2){
            $result = 2;
            $result_txt = 'sub admin';
        }//keep adding if you have more options
        return ($return_txt == true) ? $result_txt : $result;
    }
    /**
     * To check if an admin meets the list of admin_types that can do something
     *
     * useful when you want only a particular admin type to have access to something
     * @param array $admin_types(optional) the list of admin types you want to allow, default is only 1 in the list
     * @return bool
     */
    public function can_admin_access($admin_types = array(1)){
        $_type = 0;
        if($this->get_current_admin_type() != 0){
            $_type = $this->get_current_admin_type();
        }
        else{
            $user_data = $this->get_user_data($this->get_session(),3);
            if(!$user_data)
                return false;
            //set the admin_type value so you dont need to recall api everytime
            $this->current_admin_type = (int)$user_data['admin_type'];
            $_type = $this->get_current_admin_type();
        }
        //check if the admin_type is equal to the current admin logged in
        if( !in_array($_type,$admin_types ) )//deny
            return false;
        return true;
    }
    /**
     * Handles access of page contents or contents based on an admin type
     *
     * useful when you want only a particular admin type to have access to something, if admin is not of the authorised type, it displays a message to them
     * @param string $msg_info(optional)
     * @param array $admin_types(optional) the list of admin types you want to allow, default is only 1 in the list
     * @param bool $terminate(optional) if set to true, the script will terminate
     * @return void
     */
    public function admin_access_content($msg_info = '',$admin_types = array(1),$terminate = true){
        $msg_info = isset($msg_info) ? $msg_info : '';
        if(!$this->can_admin_access($admin_types)){
            ?>
            <div class="col-md-offset-1 col-md-8">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Unathorised access!</strong>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger" role="alert">
                            <?php
                            echo (!empty(trim($msg_info)) ? $msg_info : 'Sorry, you\'re not allowed to access this page.');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $this->footer();
            exit();
        }
    }
    /**
     * Gets the users
     *
     * @param int $user_type(optional) default is 1, 1 is user, 2 is dispatcher,3 is admin
     * @param string $status(optional) default is all,can either be active or inactive, to filter the user data
     * @return array|bool
     */
    public function get_users($user_type = 1,$status = 'all'){
        $user_type = isset($user_type) ? $user_type : 1;
        if(strpos($status,'inacti') !== false || strpos($status,'suspend') !== false)
            $status = 'inactive';
        else if(strpos($status,'acti') !== false)
            $status = 'active';
        else
            $status = 'all';
        $url = 'view_users.php?&user_id='.$this->get_session().'&user_type='.$user_type;
        if(!$this->get_endpoint($url)){
            $this->error = 'Sorry an internal error occured, try again later';
            return false;
        }
        //continue
        if(!$this->success_state){//succes state is false
            $this->sort_reason();//sort out the reason to display proper error
            return false;
        }//if continue, went well, return data.
        if($status == 'all')
            return $this->data;
        //filter to only active or inactive
        $new_data = array();
        $status_n = 0;
        if($status == 'active')
            $status_n = 1;
        foreach($this->data as $d){
            if((int)$d['active'] == $status_n)
                $new_data[] = $d;
        }
        $this->data_size = count($new_data);
        return $new_data;
    }
    /**
     * Just counts the users
     *
     * @param int $user_type(optional) default is 1
     * @param string $status(optional) default is all
     * @return int
     */
    public function count_users($user_type = 1,$status = 'all'){
        $this->get_users($user_type,$status);
        return $this->get_data_size();
    }

    /**
     * Gets the orders
     *
     * @param int $status(optional) same value as in doc
     * @return array|bool
     */
    public function get_orders($status = 'all'){
        $status = isset($status) ? $status : 'all';
        $url = 'get_orders_by_status.php?&user_id='.$this->get_session().'&user_type=3&status='.$status;
        if(!$this->get_endpoint($url,'general')){
            $this->error = 'Sorry an internal error occured, try again later';
            return false;
        }
        //continue
        if(!$this->success_state){//succes state is false
            $this->sort_reason();//sort out the reason to display proper error
            return false;
        }//if continue, went well, return data.
        return $this->data;
    }
    /**
     * Just counts the orders
     *
     * @param int $status(optional) same value as in doc
     * @return int
     */
    public function count_orders($status = 'all'){
        $this->get_orders($status);
        return $this->get_data_size();
    }
    /**
     * Gets total price of order
     *
     * @param string $status, same value as in the doc
     * @return int
     */
    public function get_order_total_price($status = 'all'){
        $url = 'get_order_total_price.php?user_id='.$this->get_session().'&status='.$status;
        if(!$this->get_endpoint($url)){
            return 0;
        }
        //continue
        if(!$this->success_state){//succes state is false
            $this->sort_reason();//sort out the reason to display proper error
            return 0;
        }//if continue, went well, return data.
        return $this->data['total'];
    }
    /**
     * Gets a user's total orders and order amount
     *
     * @param string $user_id
     * @param int $user_type
     * @param string $status, same value as in the doc
     * @return array|bool
     */
    public function get_user_order_total_count($user_id,$user_type = 1,$status = 'complete'){
        $url = 'get_user_order_total_count.php?user_id='.$user_id.'&user_type='.$user_type.'&status='.$status;
        if(!$this->get_endpoint($url,'general')){
            return false;
        }
        //continue
        if(!$this->success_state){//succes state is false
            $this->sort_reason();//sort out the reason to display proper error
            return false;
        }//if continue, went well, return data.
        return $this->data;
    }
    /**
     * Adds an extra data to array, for customization
     *
     * Note it allows namespace, but it must tally with the data key name, like [key],
     * e.g. $data[0=>['id'=>23,'name'=>'prec'],1=>['id'=>46,'name'=>'pelumi']]
     * in $extra,you can have, ['data where id is [id] persons name is [name]','testing [id]']
     * this function will return
     * $data[
     *       0=>['id'=>23,'name'=>'prec',0=>'data where id is 23 persons name is prec',1=>'testing 23'],
     *        1=>['id'=>46,'name'=>'pelumi',0=>'data where id is 46 persons name is pelumi',1=>'testing 46']]
     *      ]
     * sweet ba? i know, so you can also parse assoc array to $extra and it'll return it properly :)
     * that way, you can easily integrate it with the minimal and cool checkbox table generator function with the respective column key
     * @param array $data, the data you want to add stuff to
     * @param array $extras, the extra data you're adding
     * @return array
     */
    public function add_to_data($data,$extras){
        if(empty($data) || empty($extras))
            return $data;
        $new_data = array();
        $extras_b = $extras;
        foreach($data as $d){
            $inner_count = 1;
            $inner_total = count($d);
            foreach($d as $key=>$val){
                if(!is_array($val))//incase we encounter an array, prevent
                    $extras = preg_replace('/\['.$key.'\]/',$val,$extras);
                if($inner_count == $inner_total){//last value, so merge,
                    $new_data[] = array_merge($d,$extras);
                    $extras = $extras_b;//that way, we reset the value for the enext round
                    continue;
                }
                $inner_count++;
            }
        }
        return $new_data;
    }
    /**
     * filters the order for proper display,table display use actually
     *
     * @param array $data, the array data of the orders
     * @param int $limit(optional), in case you want to set a limit cause loading everything takes more time,if set to 0,returns every row
     * @return array
     */
    public function filter_orders($data,$limit = 0){
        $new_data = array();
        $count = 1;
        foreach($data as $d){
            foreach($d as $key=>$val){
                $val = (is_null($val) || (!is_array($val) && empty($val)) ) ? 'N/A' : $val;
                $d[$key] = $val;//to update
                switch($key){
                    case 'id':
                        $d[$key] = $val;
                        break;
                    case 'date_time':
                        $d[$key] = $this->date_time_display($val);
                        break;
                    case 'price':
                        $d[$key] = $this->amount($val);
                        break;
                    case 'payment_type':
                        $d[$key] = $val;
                        break;
                    case 'status':
                        $output = $this->filter_order_status($val,'continuous');
                        if($val != 2 )
                            $output = str_replace('ing','ed',$output);
                        $d[$key] = '<span class="status--'.(str_replace(' ','-',$this->filter_order_status($val))).'">'.$output.'</span>';
                        break;
                    case ($key == 'user_id' || $key == 'dispatcher_id'):
                        $u_type = ($key == 'dispatcher_id') ? 2 : 1;
                        $user = $this->get_user_data($val,$u_type);
                        if(!$user){
                            $d[$key] = $val;
                        }
                        else{
                            $u_phone = !empty($user['phone']) ? '<a href="tel:'.$user['phone'].'" title="Call '.$user['fullname'].'"><i class="fas fa-phone"></i></a>' : '';
                            $u_email = !empty($user['email']) ? '<a href="mailto:'.$user['email'].'" title="Send a mail to '.$user['fullname'].'"><i class="fas fa-envelope"></i></a>' : '';
                            $d[$key] = '<a href="user.php?id='.$user['id'].'&type='.$key.'" title="view profile"><span class="block-email">'.$user['fullname'].'</span></a>';
                            $d[$key] .= '<span class="call-to-action">'.$u_phone.' '.$u_email.'</span>';
                        }
                        break;
                }
            }
            $new_data[] = $d;
            if(!($limit < 1) && $count == $limit)
                break;
            $count++;
        }
        return $new_data;
    }
    /**
     * filters the users for proper display, table display use actually
     *
     * @param array $data, the array data of the users
     * @param int $limit(optional), in case you want to set a limit cause loading everything takes more time,if set to 0,returns every row
     * @return array
     */
    public function filter_users($data,$limit = 0){
        $new_data = array();
        $count = 1;
        foreach($data as $d){
            foreach($d as $key=>$val){
                $val = (is_null($val)) ? 'N/A' : $val;
                $d[$key] = $val;//to update
                switch($key){
                    case 'date_time':
                        $d[$key] = $this->date_time_display($val);
                        break;
                    case 'email':
                        $d[$key] = '<a href="mailto:'.$val.'" title="send a mail"><span class="block-email">'.$val.'</span></a>';
                        break;
                    case 'phone':
                        $d[$key] = (!empty(trim($val))) ? '<a href="tel:'.$val.'"><span class="block-email">Click to call</span></a>' : 'N/A';
                        break;
                    case 'active':
                        $d[$key] = ($val) == 1 ? '<span class="block active">active</span>' : '<span class="block inactive">suspended</span>';
                        break;
                    case 'admin_type':
                        $d[$key] = '<span class="block-email">'.$this->get_admin_type($val,true).'</span>';
                        break;
                }
            }
            $new_data[] = $d;
            if(!($limit < 1) && $count == $limit)
                break;
            $count++;
        }
        return $new_data;
    }
    /**
     * Gets the site options detail fro api
     *
     * @param array $key_args(optional), single array, add the only keys you want to return here, if not, leave empty
     * @return array|bool
     */
    public function get_site_options($key_args = array()){
        $new_array = array();
        $url = 'get_site_option_details.php?user_id='.$this->get_session();
        if(!$this->get_endpoint($url))
            return false;
        if(!$this->get_success_state()){
            $this->sort_reason();
            return false;
        }//works now check if the key args empty

        if(empty($key_args))//return all
            return $this->get_data();

        foreach($this->get_data() as $data){
            foreach($key_args as $key){
                if($key == $data['option_name']){
                    $new_array = $data;
                    break;
                }
            }
        }
        return $new_array;
    }
    /**
     * Helps submit the bulk action to the respective api
     *
     * Should be called after the form has been submitted, form must be POST type, if you like, go and do your own :)
     *
     * @param string $type(optional), can be 'order','dispatcher','user', default is order
     * @param string $success_msg(optional), what to show on success, default is ''
     * @return void
     */
    public function submit_bulk($type = 'order',$success_msg = ''){
        $success_msg = isset($success_msg) ? trim($success_msg) : '';
        $t = '';
        $value_a = isset($_POST['values']) ? $_POST['values'] : '';
        $action_type = isset($_POST['action_type']) ? trim(strtolower($_POST['action_type'])) : '';
        if(empty($value_a)){
            $this->error = 'Select at least one row';
            ?>
            <script type="text/javascript">
                alert("<?php echo $this->error; ?>");
            </script>
            <?php
            return;
        }
        if(empty($action_type)){
            $this->error = 'Please select an action';
            ?>
            <script type="text/javascript">
                alert("<?php echo $this->error; ?>");
            </script>
            <?php
            return;
        }
        if(strpos($type,'use') !== false){
            $t = 'user';
        }
        else if(strpos($type,'dispa') !== false ){
            $t = 'dispatcher';
        }
        else if(strpos($type,'admi') !== false ){
            $t = 'admin';
        }
        else if(strpos($type,'order') !== false){
            $t = 'order';
        }
        if(empty($t)){
            $this->error = 'type is missing';
            ?>
            <script type="text/javascript">
                alert("<?php echo $this->error; ?>");
            </script>
            <?php
            return;
        }

        //initialise some stuff
        $url = '';
        $values = join($this->ids_sep,$value_a);
        $key = 'deleted';//key value for the returned array response
        switch($t){
            case ($t == 'user' || $t == 'dispatcher' || $t == 'admin'):
                if($action_type == 'delete')
                    $url = 'delete_users.php?user_id='.$this->get_session().'&';
                else if($action_type == 'activate'){
                    $url = 'change_users_status.php?user_id='.$this->get_session().'&status=active&';
                    $key = 'changed';
                }
                else if($action_type == 'suspend'){
                    $url = 'change_users_status.php?user_id='.$this->get_session().'&status=inactive&';
                    $key = 'changed';
                }

                $type_number = $this->get_user_type($t);
                $url .='user_type='.$type_number.'&ids='.$values;
                break;
            case 'order'://default is order
                if($action_type == 'delete')
                    $url = 'delete_orders.php?user_id='.$this->get_session().'&ids='.$values;
                else if(strpos($action_type,'status_change') !== false){//for now, only cancel is allowed :), na pelumi talk am
                    $url = 'change_orders_status.php?user_id='.$this->get_session().'&status=cancel&ids='.$values;
                    $key = 'status_updated';
                }
                break;
        }
        if(!$this->get_endpoint($url)){
            ?>
            <script type="text/javascript">
                alert("<?php echo $this->error; ?>");
            </script>
            <?php
            return;
        }
        if(!$this->get_success_state()){
            $this->sort_reason();
            ?>
            <script type="text/javascript">
                alert("<?php echo $this->error ?>");
            </script>
            <?php
            return;
        }
        //everything went well.
        $data = $this->get_raw_response_array();
        $msg = $data[$key].' out of '.$data['total'].' row(s) '.str_replace('_',' ',$key);
        if(!empty($success_msg))
            $msg = $success_msg;
        echo '<script type="text/javascript">
        alert("'.$msg.'");
        </script>';
    }
    /**
     * Adds a new user
     *
     * @param array $args, an assoc array,key must be same as param being passed to endpoint
     * ['fullname'=>'value','email'=>'value','phone'=>'value']etc
     * @param int $user_type(optional) 2 or 3, default is 2.
     * @return bool
     */
    public function add_user($args,$user_type){
        $user_type = isset($user_type) ? $user_type : 2;
        if(empty($args)){
            $this->error = 'Sorry, an internal error occurred, try later.';
            $this->response_error = 'empty array :-|';
            return false;
        }
        switch($user_type){
            case 3:
                $user_type = 3;
                break;
            case 1:
                $user_type = 1;
                break;
            default:
                $user_type = 2;
        }
        $url = 'add_user.php?user_id='.$this->get_session().'&user_type='.$user_type;
        foreach($args as $key=>$val){
            $key = strtolower($key);
            if(empty(trim($val)) && $key != 'phone'){//ignore empty phone
                $this->response_error = 'Error caused by user, check Admin::$error';
                $this->error = $key.' value is empty.';
                return false;
            }
            $url .= '&'.$key.'='.$val;
        }
        if(!$this->get_endpoint($url))
            return false;
        //check for success
        if(!$this->get_success_state()){
            $this->sort_reason();//sort error message for user end
            return false;
        }
        if($this->get_mail_sent())
            return true;
        else{
            $this->error = 'Account created, but the email details could not be sent, please make sure the email provided is a valid one.';
            return false;
        }
    }
    /**
     * Updates user details, works like a charm :)
     *
     * @param array $args, an assoc array,key must be same as param being passed to endpoint
     * ['user_id'=>'value','fullname'=>'value','email'=>'value','phone'=>'value']etc
     * if you also want to update the password, add ['password'=>'value','new_password'=>'value','confirm_new_password'=>'value'] to the args
     * @param int $user_type(optional) 2 or 3, default is 2.
     * @return bool
     */
    public function update_user_details($args,$user_type){
        $user_type = isset($user_type) ? $user_type : 2;
        if(empty($args)){
            $this->error = 'Sorry, an internal error occurred, try later.';
            $this->response_error = 'empty array :-|';
            return false;
        }
        // Set namespace and url, cause its different for usertype 1:)
        $namespace = 'general';
        $endpoint = 'update_user_details.php';
        switch($user_type){
            case 3:
                $user_type = 3;
                break;
            case 1:
                $user_type = 1;
                $namespace = 'user';
                $endpoint = 'update_details.php';
                break;
            default:
                $user_type = 2;
        }
        $password_array = array();
        $url = $endpoint.'?user_type='.$user_type;
        foreach($args as $key=>$val){
            $key = strtolower($key);
            if(strpos($key,'confirm_new_pass') !== false)
                $password_array['confirm_new_password'] = $val;
            else if(strpos($key,'new_pass') !== false)
                $password_array['new_password'] = $val;
            else if(strpos($key,'pass') !== false)
                $password_array['old_password'] = $val;
            //sorted this way to avoid confusion :),mini AI is just in my body shaa
            else//normal once ,add to url
                $url .= '&'.$key.'='.$val;
        }
        if(!$this->get_endpoint($url,$namespace))
            return false;
        //check for success
        if(!$this->get_success_state()){
            $this->sort_reason();//sort error message for user end
            return false;
        }//continue
        if(empty($password_array))//no need to update password, end
            return true;
        //continue to part 2 :)
        return $this->update_user_password($args['user_id'],$password_array,$user_type);
    }
    /**
     * Updates the user password, works like a charm :)
     *
     * @param string $user_id
     * @param array $p_args, the password arguments,key must be same as param being passed to endpoint
     * ['old_password'=>'value','new_password'=>'value','confirm_new_password'=>'value']
     * @param int $user_type(optional), default is 3
     * @param bool $check_old_password(optional), if true, checks for the old password,default is true
     * @return bool
     */
    public function update_user_password($user_id,$p_args,$user_type = 3,$check_old_password = true){
        $user_type = isset($user_type) ? $user_type : 3;
        $user_id = isset($user_id) ? trim($user_id) : '';
        if(empty($p_args)){
            $this->error = 'Sorry, an internal error occurred, try later.';
            $this->response_error = 'empty password array, nawa for you oh :-|';
            return false;
        }
        if(empty($user_id)){
            $this->error = 'Sorry, an internal error occurred, try later.';
            $this->response_error = 'empty user_id, smh :-|';
            return false;
        }
        switch($user_type){
            case 2:
                $user_type = 2;
                break;
            case 1:
                $user_type = 1;
                break;
            default:
                $user_type = 3;
        }//check for password ish so as not to waste curl time
        if($check_old_password == true){//check for old password
            if(empty($p_args['old_password'])){
                $this->error = 'Current password is empty';
                return false;
            }
            //verify if the old password is correct
            $url = 'verify_current_password.php?user_id='.$user_id.'&user_type='.$user_type.'&password='.$p_args['old_password'];
            if(!$this->get_endpoint($url,'general'))
                return false;
            //check if success is true
            if(!$this->get_success_state()){
                $this->sort_reason('Your current password is incorrect');
                return false;
            }
        }
        if(empty($p_args['new_password'])){
            $this->error = 'New password is empty';
            return false;
        }
        if($p_args['new_password'] != $p_args['confirm_new_password']){
            $this->error = 'Password combination mismatch, try again.';
            return false;
        }
        if(strlen($p_args['new_password']) < 6){
            $this->error = 'Password should be at least 6 characters long, try combining numbers and letters too.';
            return false;
        }
        //worked now update
        $url = 'update_user_password.php?user_id='.$user_id.'&user_type='.$user_type.'&password='.$p_args['new_password'];
        if(!$this->get_endpoint($url,'general'))
            return false;
        //check success
        if(!$this->get_success_state()){
            $this->sort_reason();
            return false;
        }//went well, end like a boss,las las , the truth shall be returned :)
        return true;
    }

    /**
     * Helps check and send a password reset link via the respective endpoint
     *
     * @param string $email
     * @param int $user_type(optional), default is 3
     * @return bool
     */
    public function set_forgot_password($email,$user_type){
        $user_type = isset($user_type) ? $user_type : 3;
        $email = isset($email) ? trim($email) : '';
        if(empty($email)){
            $this->error = 'Please provide an email address';
            //$this->response_error = 'empty user_id, smh :-|';
            return false;
        }
        switch($user_type){
            case 3:
                $user_type = 3;
                break;
            case 2:
                $user_type = 2;
            case 1:
                $user_type = 1;
            default:
                $this->error = 'invalid user type'.$this->contact_dev_msg();
                return false;
        }
        //verify if the old password is correct
        $url = 'password_reset_link.php?email='.$email.'&user_type='.$user_type;
        if(!$this->get_endpoint($url,'general'))
            return false;
        //check success
        if(!$this->get_success_state()){
            $this->sort_reason();
            return false;
        }
        if(!$this->get_mail_sent())//mail couldnt send
            return false;
        return true;
    }

    /**
     * handles the confirmation of the link and mail
     *
     * Checks and makes sure the confirmation link and mail are correct
     *
     * @param string $email
     * @param string $link
     * @param int $user_type
     * @return array|bool
     */
    public function confirm_activation_link($email,$link,$user_type){
        $email = isset($email) ? trim($email) : '';
        $link = isset($link) ? trim($link) : '';
        $user_type = isset($user_type) ? $user_type : 3;

        if(empty($email) || empty($link))
            return false;
        $url = 'confirm_activation_link.php?email='.$email.'&link='.$link.'&user_type='.$user_type;
        if(!$this->get_endpoint($url,'general'))
            return false;
        if(!$this->get_success_state())
            return false;
        //check if the user_type returned is same as passed in this page url,cause it has to be the same, its highly impossible
        // the endpoint returns true and the user_type is different, but oh well, i like to explore 8-)
        if($this->get_data()['user_type'] != $user_type)
            return false;
        return $this->get_data();
    }

    /**
     * Updates the user email
     *
     * @param string $user_id (can also be email)
     * @param string $new_email
     * @param int $user_type internally, default is most times 3.
     * @return array|bool
     */
    public function update_user_email($user_id,$new_email,$user_type){
        $user_id = isset($user_id) ? trim($user_id) : '';
        $new_email = isset($new_email) ? trim($new_email) : '';
        $user_type = isset($user_type) ? $user_type : 3;

        if( empty( $user_id ) || empty( $new_email ) )
            return false;

        $url = 'update_user_email.php?user_id='.$user_id.'&new_email='.$new_email.'&user_type='.$user_type;
        if( !$this->get_endpoint( $url, 'general' ) )
            return false;

        if( !$this->get_success_state() )
            return false;

        return $this->get_data();
    }

    /**
     * Displays the minimalist simple table
     *
     * @param array $columns, the number of columns with their names, assoc array,e.g['name'=>'Name','email'=>'E-mail']
     * @param array $values, the array data to display, key names must match $columns key name
     * @param int $limit(optional), set the number of rows to display,if set to zero, echos all rows,
     * @return void
     */
    public function minimal_table($columns,$values,$limit = 0){
        if(empty($columns)){
            return;
        }
        $table = '<div class="table-responsive m-b-40"><table class="table table-borderless table-data3">';
        $thead = '<thead><tr>';
        foreach($columns as $key => $val){
            $thead .= '<th>'.trim($val).'</th>';
        }
        $thead .= '</tr></thead>';
        $tbody = '<tbody>';
        $insert = '';
        //for the body
        $count = 1;
        foreach($values as $value){
            $tbody .= '<tr>';
            foreach($columns as $key=>$val){
                $prefix = '';
                if($key == 'id')
                    $prefix = '<strong>#</strong>';
                $col_key = (isset($columns[$key])) ? array_search($columns[$key],$columns) : false;
                $insert = (isset($value[$col_key])) ? '<td>'.$prefix.$value[$col_key].'</td>' : '';
                $tbody .= $insert;
            }
            $tbody .='</tr>';
            if(!($limit < 1) && $count == $limit){
                break;
            }
            $count++;
        }
        $tbody .= '</tbody>';
        $table .= $thead.$tbody.'</table></div>';
        echo $table;
    }

    /**
     * Displays the table with checkbox feature
     *
     * @param array $columns, the number of columns with their names, must be assoc array,e.g['name'=>'Name','email'=>'E-mail']
     * @param array $values, the array data to display, key names must match $columns key name
     * @return void
     */
    public function cool_checkbox_table($columns,$values){
        if(empty($columns)){
            return;
        }
        //add the checkbox;
        $c_b = array('checks'=>'<label class="au-checkbox">
        <input type="checkbox" id="bulk-checkbox" title="select/unselect all">
        <span class="au-checkmark"></span>
        </label>');
        if($this->can_admin_access())//admin has access to bulk edit, so add the checkbox
            $columns = array_merge($c_b,$columns);

        $table = '<div class="table-responsive table-responsive-data2"><table class="table table-data2">';
        $thead = '<thead><tr>';
        foreach($columns as $key => $val){
            $thead .= '<th>'.trim($val).'</th>';
        }
        $thead .= '</tr></thead>';
        $tbody = '<tbody>';
        $insert = '';
        //for the body
        foreach($values as $value){
            $tbody .= '<tr class="tr-shadow">';
            //add checkbox to it
            $c_b = array('checks'=>'<label class="au-checkbox">
            <input type="checkbox" class="inner-checkbox" name="values[]" value = "'.$value['id'].'">
            <span class="au-checkmark"></span>
            </label>');

            if($this->can_admin_access())//admin has access to bulk edit, so add the checks
                $value = array_merge($c_b,$value);

            foreach($columns as $key=>$val){
                $prefix = '';
                $adjust_class = '';
                if($key == 'id')
                    $prefix = '<strong>#</strong>';
                if($key == 'checks')
                    $adjust_class = 'adjust-middle';

                $col_key = (isset($columns[$key])) ? array_search($columns[$key],$columns) : false;
                $insert = (isset($value[$col_key])) ? '<td class="'.$adjust_class.'">'.$prefix.$value[$col_key].'</td>' : '';
                $tbody .= $insert;
            }
            $tbody .='</tr><tr class="spacer"></tr>';
        }
        $tbody .= '</tbody>';
        $table .= $thead.$tbody.'</table></div>
        <script type="text/javascript"> 
            $("#bulk-checkbox").change(function(){
                var checked = false;
                if($(this).is(":checked"))
                    checked = true;
                $(".inner-checkbox").prop("checked",checked);
            });
        </script>
        ';
        echo $table;
    }

    /**
     * Displays the menu and header
     *
     * @param string $page_title(optional) , the current page you're on
     * @param string $parent_folder(optional), the parent folder, in case you refer this method from a different folder
     */
public function header($page_title = '',$parent_folder = ''){
    $page_title .=' - Wave Us';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <!-- Required meta tags-->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="robots" content="noindex, nofollow" />
        <!-- Title Page-->
        <title><?php echo $page_title; ?></title>
        <link rel="shortcut icon" href="<?php echo $parent_folder; ?>images/icon/favicon.ico">
        <!-- Fontfaces CSS-->
        <link href="<?php echo $parent_folder; ?>css/font-face.css" rel="stylesheet" media="all">
        <link href="<?php echo $parent_folder; ?>assets/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
        <link href="<?php echo $parent_folder; ?>assets/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
        <link href="<?php echo $parent_folder; ?>assets/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
        <!-- Bootstrap CSS-->
        <link href="<?php echo $parent_folder; ?>assets/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">
        <!-- Vendor CSS-->
        <link href="<?php echo $parent_folder; ?>assets/animsition/animsition.min.css" rel="stylesheet" media="all">
        <link href="<?php echo $parent_folder; ?>assets/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet" media="all">
        <link href="<?php echo $parent_folder; ?>assets/wow/animate.css" rel="stylesheet" media="all">
        <link href="<?php echo $parent_folder; ?>assets/css-hamburgers/hamburgers.min.css" rel="stylesheet" media="all">
        <link href="<?php echo $parent_folder; ?>assets/slick/slick.css" rel="stylesheet" media="all">
        <link href="<?php echo $parent_folder; ?>assets/select2/select2.min.css" rel="stylesheet" media="all">
        <link href="<?php echo $parent_folder; ?>assets/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" media="all">
        <!--<link href="assets/vector-map/jqvmap.min.css" rel="stylesheet" media="all">-->
        <!-- Main CSS-->
        <link href="<?php echo $parent_folder; ?>css/theme.css" rel="stylesheet" media="all">
        <link href="<?php echo $parent_folder; ?>css/custom.css" rel="stylesheet" media="all">
        <!-- Jquery JS-->
        <script src="<?php echo $parent_folder; ?>assets/jquery-3.2.1.min.js"></script>
        <style type="text/css">
            .wv-table-actions button{
                background-color:#666;
                margin-bottom:8px;
            }
            .wv-table-actions a:hover button{
                background-color:#4272d7;
            }
            .wv-table-actions .active button{
                background-color:#4272d7;
            }
        </style>
    </head>
    <?php
    }
    /**
     * Menu
     *
     * @param string $active_page
     */
    public function menu($active_page){
    $this->current_menu = strtolower(trim($active_page));
    ?>
    <body class="animsition">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
        <header class="header-mobile d-block d-lg-none">
            <div class="header-mobile__bar">
                <div class="container-fluid">
                    <div class="header-mobile-inner">
                        <a class="logo" href="index.php">
                            <img src="<?php echo $this->logo_url(); ?>" alt="WaveUs" />
                        </a>
                        <button class="hamburger hamburger--slider" type="button">
                            <span class="hamburger-box">
                                <span class="hamburger-inner"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            <nav class="navbar-mobile">
                <div class="container-fluid">
                    <ul class="navbar-mobile__list list-unstyled">
                        <li class="dashboard">
                            <a class="js-arrow" href="index.php">
                                <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        <li class="orders">
                            <a href="orders.php">
                                <i class="fas fa-calendar-alt"></i>Orders
                                <?php $o_p_count = $this->count_orders('process');
                                echo ($o_p_count > 0) ? '<span title="'.$o_p_count.' orders Processing" class="inbox-num">'.$o_p_count.'</span>' : '' ; ?>
                            </a>
                        </li>
                        <li class="dispatchers">
                            <a href="dispatchers.php">
                                <i class="fas fa-users"></i>Dispatchers</a>
                        </li>
                        <li class="admins">
                            <a href="admins.php">
                                <i class="fas fa-users"></i>Admins</a>
                        </li>
                        <li class="users">
                            <a href="users.php">
                                <i class="fas fa-table"></i>Users</a>
                        </li>
                        <li class="account">
                            <a href="account.php">
                                <i class="zmdi zmdi-account"></i>Account</a>
                        </li>
                        <li>
                            <a href="logout.php">
                                <i class="fas fa-power-off"></i>logout</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <!-- END HEADER MOBILE-->
        <!-- MENU SIDEBAR-->
        <aside class="menu-sidebar d-none d-lg-block">
            <div class="logo">
                <a href="index.php">
                    <img src="<?php echo $this->logo_url(); ?>" alt="Wave Us" />
                </a>
            </div>
            <div class="menu-sidebar__content js-scrollbar1">
                <nav class="navbar-sidebar">
                    <ul class="list-unstyled navbar__list">
                        <li class="dashboard">
                            <a class="js-arrow" href="index.php">
                                <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        <li class="orders">
                            <a href="orders.php">
                                <i class="fas fa-calendar-alt"></i>Orders
                                <?php $o_p_count = $this->count_orders('process');
                                echo ($o_p_count > 0) ? '<span title="'.$o_p_count.' orders Processing" class="inbox-num">'.$o_p_count.'</span>' : '' ; ?>
                            </a>
                        </li>
                        <li class="admins">
                            <a href="admins.php">
                                <i class="fas fa-users"></i>Admins</a>
                        </li>
                        <li class="dispatchers">
                            <a href="dispatchers.php">
                                <i class="fas fa-users"></i>Dispatchers</a>
                        </li>
                        <li class="users">
                            <a href="users.php">
                                <i class="fas fa-users"></i>Users</a>
                        </li>
                        <li class="account">
                            <a href="account.php">
                                <i class="zmdi zmdi-account"></i>Account</a>
                        </li>
                        <li>
                            <a href="logout.php">
                                <i class="fas fa-power-off"></i>logout</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        <!-- END MENU SIDEBAR-->
        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <!-- HEADER DESKTOP-->
            <header class="header-desktop">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="header-wrap">
                            <div class="header-button">
                                <?php $user_data = $this->get_user_data($this->get_session()); ?>
                                <div class="account-wrap" style="float:right;">
                                    <div class="account-item clearfix js-item-menu">
                                        <div class="image">
                                            <img src="images/icon/default.jpg" />
                                        </div>
                                        <div class="content">
                                            <a class="js-acc-btn" href="#"><?php echo $user_data['fullname']; ?></a>
                                        </div>
                                        <div class="account-dropdown js-dropdown">
                                            <div class="info clearfix">
                                                <div class="image">
                                                    <a href="account.php">
                                                        <img src="images/icon/default.jpg" />
                                                    </a>
                                                </div>
                                                <div class="content">
                                                    <h5 class="name">
                                                        <a href="account.php"><?php echo $user_data['fullname']; ?></a>
                                                    </h5>
                                                    <span class="email"><?php echo $user_data['email']; ?></span>
                                                </div>
                                            </div><!--
                                            <div class="account-dropdown__body">
                                            </div>-->
                                            <div class="account-dropdown__footer">
                                                <a href="logout.php">
                                                    <i class="zmdi zmdi-power"></i>Logout</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- HEADER DESKTOP-->
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <?php
                        }
                        /**
                         * Footer
                         */
                        public function footer(){
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="copyright">
                                    <p> <?php echo date('Y'); ?> Wave Us. Template by <a href="https://colorlib.com">Colorlib</a>.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>
    </div>
    <!-- Bootstrap JS-->
    <script src="assets/bootstrap-4.1/popper.min.js"></script>
    <script src="assets/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- assets JS       -->
    <script src="assets/slick/slick.min.js">
    </script>
    <script src="assets/wow/wow.min.js"></script>
    <script src="assets/animsition/animsition.min.js"></script>
    <script src="assets/bootstrap-progressbar/bootstrap-progressbar.min.js">
    </script>
    <script src="assets/counter-up/jquery.waypoints.min.js"></script>
    <script src="assets/counter-up/jquery.counterup.min.js">
    </script>
    <script src="assets/circle-progress/circle-progress.min.js"></script>
    <script src="assets/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/chartjs/Chart.bundle.min.js"></script>
    <script src="assets/select2/select2.min.js">
    </script>
    <!-- Main JS-->
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $('.<?php echo $this->current_menu; ?>').addClass('active');
    </script>
    </body>
    </html>
    <?php
}
}