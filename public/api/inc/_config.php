<?php
/**
 * Stores all necessary global variables and constants
 * 
 * To be used in most files
 * 
 * @author Precious Omonzejele <omonze@peepsipi.com>
 */ 
date_default_timezone_set("Africa/Lagos");//this is deprecated though,https://stackoverflow.com/questions/17535883/php-date-default-timezone-set-resulting-in-incorrect-time-for-africa-lagos

// Environment , production or test :) dont forget to change, to avoid stories that touch! serious oh. :-|
define('ENV','production');

// General ish
define('SITE_DOMAIN','waveustransit.com');
define('SITE_URL','https://waveustransit.com/');
define('SITE_TITLE','Wave Us Transit');

 /* DATABASE */
if($_SERVER['SERVER_NAME'] == SITE_DOMAIN){
	// Check if its production or not
	if( ENV === 'production' ){
		define('DB_NAME', 'waveustransit_com');
		define('DB_USER', 'waveustransit_com');
		define('DB_PASSWORD', 'wxkuu6weQJdAYYd6gzfMLcJa');
	}
	else{ // Test environment details :)
		define('DB_NAME', 'waveustransit_com');
		define('DB_USER', 'waveustransit_com');
		define('DB_PASSWORD', 'wxkuu6weQJdAYYd6gzfMLcJa');
	}
	define('DB_HOST', 'localhost');
	/** Database Charset to use in creating database tables. */
	define('DB_CHARSET', 'utf8');
	
}
else{ //Offline
	define('DB_NAME', 'waveus');
	define('DB_USER', 'waveus');
	define('DB_PASSWORD', 'qwerty_12345');
	define('DB_HOST', 'db4free.net');
	/** Database Charset to use in creating database tables. */
	define('DB_CHARSET', 'utf8');
}
/** Default connection type to the database */
define('CON_TYPE', '');
/** Admin types */
define('SUPER_ADMIN', 1);
define('MINI_ADMIN', 2);

/** Order types 
 * You are adviced to use these constants anywhere you want to signify the order codes 
 */
define('ORDER_CANCEL_CODE',-1);
define('ORDER_HOLD_CODE',1);
define('ORDER_PROCESS_CODE',2);
define('ORDER_COMPLETE_CODE',3);
/** currency */
define('CURRENCY_SYMBOL','&#8358'); //₦;
/**
 * used to try to identify and print out reason for non-success and also success state on the end use
 * 
 * if reason code is 1, then the problem is from the end user,if its -1, its an internal problem
 * then the reason txt tries to store some reasonable message for the end user
 * @var array
 */
$echo_state = array('success' => 'false','reason_code' => 0,'reason_txt' => '');
/**
 * in case you want to add extra array values before calling the print_json_state function
 * 
 * @var array
 */
$add_array = array();
/**
 * meta keys that are only meant for backend, use it to determine which to show to users e.g, hiding it from mail messages :)
 * @var array
 */
$meta_backend = array('delivery_coord','pickup_coord');
/**
 * helps to keep the query filtered to only records that dont have deleted set to 1, only relevent with select
 * statement
 * 
 * @param string $query the string that is to be processed
 * @return string returns the condition "Where deleted = 0" appropriately
 */
function query_live($query){
    $query_c = strtolower(trim($query));
    $extra = '';
    $char_array_val = 'nsssnprecipeepsjdjdskdnddjs';//the default value, this isn't rubbish, leave it, its to prevent a possible match
    $end_array = array("order by","limit");//this contains keywords of sql after where clause
    for($i = 0; $i < count($end_array); $i++){
        if(strpos($query_c,$end_array[$i]) != false){//matches
            $char_array_val = $end_array[$i];
            break;
        }
    }
    //now check if the string already has a where clause only
    if(strpos($query_c,"where") !== false && strpos($query_c,$char_array_val) === false){//has
        $query_c = str_replace("where"," WHERE ( deleted = '0' ) AND ",$query_c);
    }
    else if(strpos($query_c,"where") === false && strpos($query_c,$char_array_val) !== false){//has an order by but no where clause
        $query_c = str_replace($char_array_val," WHERE ( deleted = '0' ) ".$char_array_val,$query_c);
    }
    else if(strpos($query_c,"where") !== false && strpos($query_c,$char_array_val) !== false){//has an order by and a where clause
        $query_c = str_replace("where"," WHERE ( deleted = '0' ) AND ",$query_c);
    }
    else{
        $extra = " WHERE deleted = '0'";
    }
    return $query_c.$extra;
}

/**
 * For filtering single array to be just single
 * 
 * What i mean is, the way the records are gotten after retrieved from the db, is
 * array( 0 => array('column' => 'value')),so when it's in JSON format, it's {'data:'value',0:['column':'value']}
 * so when we change it to array('column'=>'value'), in JSON format, it's {'data:'value','column':'value'}
 * @param array $array the array to be processed
 * @return array
 */
function for_single_array($array = array()){
    if(count($array) == 1 ){//nice , sort out
        $new_array = array();
        //check if array has index[0]
        if(array_key_exists(0,$array)){       
            foreach($array[0] as $key => $value){
                $new_array[$key] = $value;
            }
        }
        else{
            foreach($array as $key => $value){
                $new_array[$key] = $value;
            }
        }
        return $new_array;
    }
    else{
        return $array;
    }
}

/**
 * Helps in setting the echo_state to a success
 * 
 * By removing the reason and setting success to true
 */
function pekky_set_success_state(){
    global $echo_state;
    unset($echo_state['reason_code']);
    unset($echo_state['reason_txt']);
    $echo_state['success'] = "true";
}

/**
 * Helps in setting the echo_state to a failure
 * 
 * If extra array could be displayed, it also helps
 * 
 * @param int $reason_code the reason code to be set
 * @param string $reason_txt (optional) the reason txt to be set, if empty, reason_code and reason_txt will be unset.
 */
function pekky_set_failure_state($reason_code,$reason_txt = ""){
    global $echo_state;
    $reason_txt = isset($reason_txt) ? trim($reason_txt) : "";
    if(empty($reason_txt)){
        unset($echo_state['reason_code']);
        unset($echo_state['reason_txt']);    
    }
    else{
        $echo_state['reason_code'] = $reason_code;
        $echo_state['reason_txt'] = $reason_txt;
    }
    $echo_state['success'] = "false";
}


/**
 * Helps in displaying the json format for the echo_state
 * 
 * If extra array could be displayed, it also helps
 * 
 * @param array $extra_array optional array to merge with the array to be outputed
 * @param mixed $extra_holder(optional) The name of the key that'll hold the extra array,d default is 0, its irrelevant is $assoc_pass is true
 * @param bool $assoc_pass(optional) if true, it doesn't assign $extra_array into a parent array, default is true, most times leave this as true when passing assoc arrays
 * @param bool $echo(optional) if true, it echoes ,else, it returns the value, default is true
 * @return json|void depends on the $echo value
 */
function pekky_print_json_state($extra_array = array(),$extra_holder = 0,$assoc_pass = true,$echo = true){
    global $echo_state,$add_array;
    if($assoc_pass == false)
        $extra_array = array($extra_holder => $extra_array);//to fulfill pelumi's format. :)
    $val = array_merge($echo_state,$extra_array,$add_array);
    if($echo)
		echo json_encode($val);  
    else 
		return json_encode($val);
}

/**
 * Helps to set a value for $add_array,lol not really necessary, you could just use $add_array directly
 * 
 * @param array $array optional array to merge with the array to be outputed
 */
function pekky_add_array_to_print($array = array()){
    global $add_array;
    $add_array = array_merge($add_array,$array);
}

/**
 * for returning the equivalent db_name of the types of users
 * 
 * If extra array could be displayed, it also helps
 * 
 * @param int $id optional id of the user type, 1 is user, 2 is dispatcher, 3 is admin
 * @return string the corresponding text of the user
 */
function user_type($id = 1){
    $user_db = "users";
    switch($id){
          case 2 :
              $user_db = "dispatchers";
          break;
          case 3 :
              $user_db = "admins";
              break;
          default:
              $user_db = "users";
    }
    return $user_db;
  
}
/**
 * For encrypting password
 * @param string $str the string to be encrypted 
 * @return string the encrypted password
 */
function hash_password($str){
    return md5($str);
}

/**
 * for checking if a user exists in the database
 * 
 * @param object $query the query object
 * @param mixed $user_id id of the user,can also be the email
 * @param int $user_type optional id of the user type, 1 is user, 2 is dispatcher, 3 is admin,default is 1
 * @param bool $deleted optional, if you want to fetch a user even if its deleted,default is false
 * @return bool true if user exists, false otherwise
 */
function user_exists($query,$user_id,$user_type = 1,$deleted = false){
    $user_db = user_type($user_type);
    $q_txt = "SELECT id from ".$user_db." WHERE (id = ? OR email = ?)";
    $q = $q_txt;
    if(!$deleted)//doesn't include deleted
        $q = query_live($q_txt);
     if(!$query->get($q,[$user_id,$user_id])){return false;}
     if($query->row_count == 1){return true;}
     else{ return false;}
}

/**
 * for checking if an admin is of a particular admin_type
 * 
 * @param object $query the query object
 * @param mixed $id id of the admin,
 * @param int $type_should_be the admin_type you're checking to make sure the admin is,default is SUPER_ADMIN constant
 * @param bool $deleted optional, if you want to fetch a user even if its deleted,default is false
 * @return bool true if admin has the selected type to be, false otherwise
 */
function is_allowed_admin_type($query,$id,$type_should_be = SUPER_ADMIN,$deleted = false){
    $id = isset($id) ? trim($id) : '';
    if(empty($id))
        return false;
    $q_txt = "SELECT admin_type from admins WHERE (id = ? AND  admin_type = ?)";
    $q = $q_txt;
    if(!$deleted)//doesn't include deleted
        $q = query_live($q_txt);
     $query->get($q,[$id,$type_should_be]);
     if($query->row_count == 1){return true;}
     else{ return false;}
}

/**
 * for getting option values
 * 
 * @param object $query the query object
 * @param string $option the option name, must be a valid one
 * @return string the option value, empty if not found
 */
function get_site_option($query,$option){
    $result = '';
    $query->set_fetch_mode("num");
    $query->get("SELECT option_value FROM site_options WHERE option_name = '{$option}'");
    $result = $query->record[0][0];
    return $result;
}


/**
 * For converting order status codes to the respecitve values
 * 
 * Depending on the output you put, it can accept recognised phrases(reasonable spelling shaa) 
 * @param mixed $status the status code you want to check in,could be int, word or phrases
 * @param string $output (optional) the output format you want, num,past,continuous,present
 * @return mixed depends on the output given, if no match, returns false
 */
function get_order_status($status,$output = "num"){
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
        ORDER_HOLD_CODE => array('on hold,held,on-hold','on hold,on hold,held'),
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
 * for properly handling site_option meta json into the db
 * 
 * @param object $query the query object
 * @param JSON|array $json the JSON objects or array
 * @param string $action any valid sql action, insert,update,delete, default is always insert
 * @return bool true if action is successful, false otherwise
 */
function handle_site_option_meta($query,$json,$action = 'insert'){
    $action = isset($action) ? trim(strtolower($action)) : '';
    $result = false;
    $json_array = (is_array($json) ? $json : json_decode($json,true));
    $q = '';
    $type = '';
    switch($action){
        case 'update':
            $type = 'update';
            $q = "UPDATE site_options SET option_value = ?, date_time_updated = NOW() WHERE option_name = ?";
        break;
        case 'delete':
            $q = "DELETE FROM site_options WHERE option_value = ? AND option_name = ?";
        break;
        default:
            $type = 'insert';//to know when the switch falls into this category
            $q = "INSERT into site_options(option_value,option_name,date_time) VALUES(?,?,NOW())";
    }
   // $query->prepare($q);//prepare the statement down
    foreach($json_array as $key => $value){
       if($type == 'insert'){
          //check if the meta key of the same order already exists,
          $query->get("SELECT id FROM site_options WHERE option_name = ?",[$key]);
          if($query->row_count > 0){//already exists,so just update
            $query->change('site_options',['option_value'=>'?','date_time_updated'=>'NOW()'],'WHERE option_name = ?',[$value,$key]);
            $query->prepare($q);//prepare statement again so that the get text doesnt override the initial statement.
              continue;
          }
          else{$query->prepare($q);//prepare statement again so that the get text doesnt override the initial statement.
          }
        }
        else if($type == 'update'){
           //check if the meta key of the same order already exists,
          $query->get("SELECT id FROM site_options WHERE option_name = ?",[$key]);
          if($query->row_count < 1){//doesnt exist, so insert. 
            $query->add('site_options',['option_value'=>'?','option_name'=>'?','date_time'=>'NOW()'],[$value,$key]);
            $query->prepare($q);//prepare statement again so that the get text doesnt override the initial statement.
              continue;
          } else{$query->prepare($q);//prepare statement again so that the get text doesnt override the initial statement.
          }
        }
      $result = $query->execute([$value,$key]);
      $query->close_statement();
    }
   return $result;
}

/**
 * for properly handling order meta json into the db
 * 
 * @param object $query the query object
 * @param int $order_id the id of the order, this speaks for itself :)
 * @param JSON|array $json the JSON objects or array
 * @param string $action any valid sql action, insert,update,delete, default is always insert
 * @return bool true if action is successful, false otherwise
 */
function handle_order_meta($query,$order_id,$json,$action = 'insert'){
    $action = isset($action) ? trim(strtolower($action)) : '';
    $result = false;
    $json_array = (is_array($json) ? $json : json_decode($json,true));
    $q = '';
    $type = '';
    switch($action){
        case 'update':
            $type = 'update';
            $q = "UPDATE order_meta SET meta_value = ? WHERE meta_key = ? AND order_id = ?";
        break;
        case 'delete':
            $type = 'delete';//just added
            $q = "DELETE FROM order_meta WHERE (meta_value = ? AND meta_key = ?) AND order_id = ?";
        break;
        default:
            $type = 'insert';//to know when the switch falls into this category
            $q = "INSERT into order_meta(meta_value,meta_key,order_id) VALUES(?,?,?)";
    }
   // $query->prepare($q);//prepare the statement down
    foreach($json_array as $key => $value){
        //first check if price was in the json data to put it in the price column in order table
        if(trim(strtolower($key)) == 'price'){//add the price value to the order table
          if( $query->change('orders',['price'=>'?'],'WHERE id = ?',[$value,$order_id]) ){
              $result = true;
          }
          else{$result = false;}
        }//first check if payment_type was in the json data to put it in the price column in order table
        else if(trim(strtolower($key)) == 'payment' || trim(strtolower($key)) == 'payment_type'){//add the payment value to the order table
            if( $query->change('orders',['payment_type'=>'?'],'WHERE id = ?',[$value,$order_id]) ){
                $result = true;
            }
            else{$result = false;}
        }
        else if(trim(strtolower($key)) == 'der_ty' || trim(strtolower($key)) == 'order_type'){//add the payment value to the order table
            if( $query->change('orders',['order_type'=>'?'],'WHERE id = ?',[$value,$order_id]) ){
                $result = true;
            }
            else{$result = false;}
        }
        else{
       if($type == 'insert'){
          //check if the meta key of the same order already exists,
          $query->get("SELECT meta_id FROM order_meta WHERE meta_key = ? AND order_id = ?",[$key,$order_id]);
          if($query->row_count > 0){//already exists,so just update
            $query->change('order_meta',['meta_value'=>'?'],'WHERE meta_key = ? AND order_id = ?',[$value,$key,$order_id]);
            $query->prepare($q);//prepare statement again so that the get text doesnt override the initial statement.
              continue;
          }
          else{$query->prepare($q);//prepare statement again so that the get text doesnt override the initial statement.
          }
        }
        else if($type == 'update'){
           //check if the meta key of the same order already exists,
          $query->get("SELECT meta_id FROM order_meta WHERE meta_key = ? AND order_id = ?",[$key,$order_id]);
          if($query->row_count < 1){//doesnt exist, so insert. 
            $query->add('order_meta',['meta_value'=>'?','meta_key'=>'?','order_id'=>'?'],[$value,$key,$order_id]);
            $query->prepare($q);//prepare statement again so that the get text doesnt override the initial statement.
              continue;
          } else{$query->prepare($q);//prepare statement again so that the get text doesnt override the initial statement.
          }
        }//remains for delete
       
       $result = $query->execute([$value,$key,$order_id]);
    }
    $query->close_statement();
    }
   return $result;
}

/**
 * Gets the meta of an order
 * 
 * @param object $query the query object
 * @param int $order_id
 * @param string $meta_key (optional), if a meta key is put in, returns only the value of the meta key
 * @param string $type (optional) the type of order meta, values(all,hidden,shown)hidden meta start with '_',e.g _geo
 * @return mixed array if no specific key is chosen, false if query error, empty if meta_key doesn't exist
 */
function get_order_meta($query,$order_id,$meta_key = '',$type = 'all'){
    $result = '';
    $meta_key = isset($meta_key) ? trim($meta_key) : '';
    $meta_query = !empty($meta_key) ? ' AND meta_key = ?' : '';
    $q = 'SELECT meta_key,meta_value FROM order_meta WHERE order_id = ?'.$meta_query;
    $binding = array($order_id);
    $query->set_fetch_mode("assoc");
    if(!empty($meta_key)){
        $m_b = array($meta_key);
        $binding = array_merge($binding,$m_b);
    }
    if(!$query->get($q,$binding))
        return false;
    if($query->row_count < 1){
        return $result;
    }
     //now check
    if(empty($meta_key)){
        $array = $query->record;
        $result = array();
        for($i = 0; $i < count($array); $i++){//for the type ish
            switch($type){
                case 'hidden':
                    if(substr($array[$i]['meta_key'],0,1) === '_'){
                        $result[$array[$i]['meta_key']] = $array[$i]['meta_value'];
                    }
                break;
                case 'shown':
                    if(substr($array[$i]['meta_key'],0,1) !== '_'){
                        $result[$array[$i]['meta_key']] = $array[$i]['meta_value'];
                    }
                break;
                default:
                $result[$array[$i]['meta_key']] = $array[$i]['meta_value'];
            }
        }
    }
    else{
        $result = $query->record[0]['meta_value'];
    }
    return $result;
}

/**
 * Checks if an order exists
 * 
 * @param object $query the query object
 * @param int $order_id
 * @return bool true if order exists, false otherwise
 */
function order_exists($query,$order_id){
    $query->get(query_live("SELECT id FROM orders WHERE id = ? "),[$order_id]);
    if($query->row_count == 1)
        return true;
        else
        return false;
}

/**
 * Checks if the user passed in owns the order
 * 
 * @param object $query the query object
 * @param string $user_id
 * @param int $user_type has to be 1 or 2
 * @param int $order_id
 * @return bool true if user owns order, false otherwise
 */
 function user_owns_order($query,$user_id,$user_type,$order_id){
    $column = 'user_id';
    switch($user_type){
        case 2:
        $column = 'dispatcher_id';
        break;
        default:
        $column = 'user_id';
    }
    $q = query_live("SELECT id FROM orders WHERE id = ? AND ".$column." = ?");
    $query->get($q,[$order_id,$user_id]);
    if($query->row_count == 1)
        return true;
    else
        return false;
 }
#####coordinates part
$coord_del = '|';//coordinate delimeter
/**
 * checks correct coordinate value
 * 
 * @param string $data the string to check
 * @return bool true if it's the valid format, false otherwise
 */
function is_valid_coord($data){
    global $coord_del;
    $data = isset($data) ? trim($data) : '';
    $data_a = explode($coord_del,$data);
    if(count($data_a) != 2)
        return false;
    for($i = 0; $i < count($data_a); $i++){
        $the_data = isset($data_a[$i]) ? trim($data_a[$i]) : '';
        if(!(is_double($the_data) || is_numeric($the_data)) ){
            return false;
        }
    }
    return true;
}

/**
 * for constructing coordinates to suite json format
 * 
 * @param string $data the coordinate value
 * @param string (optional) $key_name the coordinate key_name value default is ''
 * @param bool $return array, if set to true, it returns an array format, default is true
 * @return JSON|array returns in the format coord:{"x":23,"y":22}, you know how that'll be if its in array format :)
 */
function json_coord($data,$key_name = '',$return_array = true){
    global $coord_del;
    $key_name = isset($key_name) ? (empty(trim($key_name)) ? 'coord' : trim($key_name) ) : 'coord';
    $result = array();
    $data_a = explode($coord_del,$data);
    if(count($data_a) != 2){
        if($return_array)
           return array();
         else
          return json_encode(array());
    }
    $the_data_1 = isset($data_a[0]) ? (double)trim($data_a[0]) : '';
    $the_data_2 = isset($data_a[1]) ? (double)trim($data_a[1]) : '';
    $result[$key_name]['x'] = number_format($the_data_1,7,'.','');//round up to 6 or 7, as per gps stuff
    $result[$key_name]['y'] = number_format($the_data_2,7,'.','');//round up to 6 or 7, as per gps stuff
    if($return_array)
        return $result;
    else
        return json_encode($result);
}

/**
 * Finds the coordinate key name in a given array
 * @param array $array
 * @param string $type(optional) to know which type to look for, 0 = coord, 1=pickup,2=deliver, default is 0
 * @return string|bool key name if found, else false
 */
function find_coord($array,$type = 0){
    $result = false;
    $found = false;
    if(!empty($array)){
    //loop
    foreach($array as $key=>$value){
        switch($type){
            case 1://pickup
              if(strpos($key,'up_coord') !== false || strpos($key,'upcoord') !== false){//found it
                $result = $key;
                $found = true;//so we know when to break.
              }
            break;
            case 2://delivery
              if(strpos($key,'very_coord') !== false || strpos($key,'verycoord') !== false || strpos($key,'vrycoord') !== false || strpos($key,'vry_coord') !== false){//found it
                $result = $key;
                $found = true;//so we know when to break.
              }
            break;
            default: //normal normal 
              if(strpos($key,'coord') !== false || strpos($key,'cord') !== false){//found it
                $result = $key;
                $found = true;//so we know when to break.
              }
        }
        if($found == true)
            break;
    }
    }
    return $result;
}
/**
 * Displays amount 
 * 
 * @param mixed $value
 * @return string
 */
function amount($value){
    $value = (float)$value;
    return CURRENCY_SYMBOL.number_format($value,2,'.',',');
}

/**
 * processes the file error code
 *
 * https://www.php.net/manual/en/features.file-upload.errors.php
 *
 * @param mixed $code most likely int shaa
 * @return string
 */
 function file_upload_error_msg( $code, $extra_msg ){
	 $file_upload_errors = array(
		0 => 'There is no error, the file uploaded with success',
		1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
		2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		3 => 'The uploaded file was only partially uploaded',
		4 => 'No file was uploaded',
		6 => 'Missing a temporary folder',
		7 => 'Failed to write file to disk.',
		8 => 'A PHP extension stopped the file upload.',
	);
	return $file_upload_errors[$code] . ( !empty( $extra_msg ) ? $extra_msg : '' );
 }
 