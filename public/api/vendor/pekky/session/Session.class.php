<?php
	//namespace pp\mail;
/*
* Session stuffETC
* 
*/
 class Session{
	 
	 /**
     * determines if smpt is to be used or not.
     * @var bool
	 * @default true;
     */
	 private $_session_id = '';
	 
    /**
     */
	 
	 public function __construct(){
		 
		 
	 }
	 
	 /*
	  * Checks session basically
	  * @param string, the session to check for
	  *
	  * @return boolean, returns true if the session is valid, false otherwise.
	  */
    public function check_session_basically($session_id){
		$this->_session_id = $session_id;
		
		if(isset($this->_session_id) && !empty($this->_session_id)){
			return true;
		}
		else{
			return false;
		}
    }
    
	 }
 