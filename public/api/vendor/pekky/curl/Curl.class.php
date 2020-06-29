<?php
	
	/*
	* For handling curl requests
	*/
	
	class Curl{
	 
    /**
     * the curl holder
     * @var object
     */
    private $curl = '';

		 
    /**
     * return transfer
     * @var int
     */
//	private $return_transfer = 1;
		
    /**
     * return transfer
     * @var int
     */
	private $return_transfer = 1;
	
    /**
     * the url
     * @var string
     */	
	public $url = '';
		
	/**
     * result
     * @var mixed
     */	
	public $result = false;
			
	/**
     * error
     * @var string
     */	
	public $error = '';
				
	/**
     * error number
     * @var int
     */	
	protected $error_no = 0;
				
	/**
     * error
     * @var string
     */	
	public $last_error = '';
				
	/**
     * error number
     * @var int
     */	
	protected $last_error_no = 0;
			
		
	public function __construct(){
		//initialise
		$this->curl = curl_init();
	}
	/**
     * Sends a request to the specified url.
     *
     * @param string $url url to use.
     * @return bool - if there is an error executing the request, true - if the request executed without error and CURLOPT_RETURNTRANSFER is set to false, the result - if the request executed without error and CURLOPT_RETURNTRANSFER is set to true
     */
	public function get_auth($url){
		$this->url = $url;
		// do your thing, curl ;)
		curl_setopt_array($this->curl,array(
		CURLOPT_RETURNTRANSFER => $this->return_transfer,
		CURLOPT_URL => $this->url
		));
		$this->result = curl_exec($this->curl);
		$l = strtolower($this->result);
		$this->clear_error();
		if(!$this->result || strpos($l, 'found') != false){
			$this->error = curl_error($this->curl);
			$this->error_no = curl_errno($this->curl);			
		}
		//curl_close($this->curl);
		return $this->result;
	}
		
	/**
     * Get an error if there's an error
     *
     * @return string|bool  false if no error is found, or returns the error message.
     */		
	public function get_error(){
		if( !empty($this->error)){
			return $this->error;
		}
		else{
			return false;
		}
	}

	/**
     * Get an error number if there's an error
     *
     * @return int if no error is found, or returns the error number.
     */		
	public function get_errno(){
		return $this->error_no;
	}

	/**
	 * Clears the error and adds it to the last error
	 */
	protected function clear_error(){
		$this->last_error = $this->error;
		$this->last_error_no = $this->error_no;
		$this->error = '';
		$this->error_no = 0;
		
	}
		
}
	
	