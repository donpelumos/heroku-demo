<?php

	/*
	 * HANDLES letter stuff, like maybe transforming,etc
	 *
	 * @author Precious Omonzejele
	*/
	
	class Letters{
		
		/*
		 * @var string
		 */
		private $word = '';
		
		/*
		 *@var string
		 */
		private $delimeter = '';
		
			function __construct(){
			}
		
		/*
		 * Transform words backwards
		 *
		 *@param $word, the word to work with
		 *@param $delimeter (optional)
		 *
		 *@return string, returns the reverse word
		 */
		public function backwards($word,$delimeter = ''){
			$this->word = $word;
			/*
			$this->delimeter = $delimeter;
			//split
			$split = explode($this->delimeter,$this->word);
			*/
			$split = str_split($this->word,1);
			$reverse_word = '';
			//now take it backwards
			for($i = (count($split) - 1); $i >= 0; $i--){
				$reverse_word .= $split[$i];
			} 
			return $reverse_word;
			
		}
	}
	