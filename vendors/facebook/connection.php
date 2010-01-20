<?php

/* START: For local server */
	
	$hostName="localhost";		// Machine on which MySQL Database is running
	$userName="root";			// Database User Login
	$password="public";			// Database User Password
	$database ="MMM";	// Database name
/* END: For local server */
	
	$link = mysql_pconnect($hostName, $userName, $password) or die("Could not connect : " . mysql_error()); 
	mysql_select_db($database, $link);

	function close_mysql() 
	{
		global $link;
		mysql_close($link);
	}
?>
