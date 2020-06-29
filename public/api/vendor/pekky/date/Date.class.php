<?php
		class Dater{
		private $value;
		
		/*
		 * CONVERTING TO DATE
		 *@$value: what to be converted
		*/
		
		function to_date($value){
			$this->value = $value;
			//START WORK
			$dt = new DateTime();
			//$tz = new DateTimeZone();
			$dt->setTimestamp($this->value);//SET THE UNIX VALUE AND TRY TO CONVERT.
			//$dt->setTimezone();
			//SHOW DATE WELL,
				$event = $this->value;//DAY OF EVENT
		
			$now = $this->to_unix("now");
			$today = $this->to_unix("today");
			$yesterday =  $this->to_unix("yesterday");
			//FIND OUT IF IT'S YESTERDAY, OR TODAY, TO DISPLAY IT WELL
			//CALCULATE THE STUFF WELL, SINCE IT'S IN SECONDS
			$_24 = 24*60*60;//24 hours in seconds
			$_48 = $_24 * 2;//48 HOURS IN SECONDS
			$current = $now - $event;
			if(($current) <= $_24){//THE EVENT IS STILL WITHIN 24 HOURS
				$date = "Today <i class=\"icon fa-clock-o\"></i> ".$dt->format("h:m A");
			}
			else if((($current) > $_24) && (($current) <= $_48)){//THE EVENT IS STILL WITHIN 48 HOURS BUT GREATER THAN 24 HOURS
				$date = "Yesterday <i class=\"icon fa-clock-o\"></i> ".$dt->format("h:m A");
			}
			else{
				$date = $dt->format("d M, Y")." <i class=\"icon fa-clock-o\"></i> ".$dt->format("h:m A");
			}
			return $date;
		}
		
		/*
		 * CONVERTING DATE TO UNIX TIME
		 *@$value: what to be converted
		*/
		function to_unix($value){
			$this->value = $value;
			return strtotime($this->value);
		}
	}
