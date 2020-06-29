<?php

	/*
	* HANDLE NAMES, FOR SPLITTING NAMES INTO FIRST AND SECOND
	*/
	
	class Names{
		private $name = '';
		//delimeter
		private $delimeter = " ";
			function __construct($name){
				$this->name = $name;
			}
		public function show_name(){
			echo $this->name;
		}
		public function show_f_name(){
			//$this->name = $name;
			//split
			$split = explode($this->delimeter,$this->name);
			
			return $split[0];
			
		}
		public function show_l_name(){
		//	$this->name = $name;
				//split
			$split = explode($this->delimeter,$this->name);
			
			return $split[1];
				
		}
		
		/*
		 * show_initial
		 *
		 * for showing initial of a name.
		 * 
		 * @param names, the name being passed.
		 * @param initial_sign, either the dot, or whatever, default is "."
		 * @return string, the output
		 */
		public function show_initial($name,$initial_sign = ".")
		{
			//break the name and print the first letter only
			$name_array = str_split($name,1);
			$result = $name_array[0].$initial_sign;
			return $result;
		}
	}
	