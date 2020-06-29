<?php
	
	/*
	* TO DISPLAY A NOT FOUND PAGE
	*/
	
	class _404Message{
		
		public function display(){
			 echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL ".$_SERVER["PHP_SELF"]." was not found on this server.</p>
</body></html>";
		}
		
	}
	
	