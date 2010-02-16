<?php

	//please register at http://developer.myspace.com for a CONSUMERK_KEY
	define('CONSUMER_KEY', 'c87b1a3237324b5eaed23749e9ebff37');
//	define('CONSUMER_KEY', 'http://www.myspace.com/499532349');
	//please register at http://developer.myspace.com for a CONSUMER_SECRET
	define('CONSUMER_SECRET', '46aed6a612744b80aab0c19cbb488140ee2bea3a7add45f982ec3765bc48c76e');
//	define('CONSUMER_SECRET', 'ab913d3ca03b4da2ba17e1815bf960b6156241e1a07f4a66a5a1ed665a02c87e');

	/**
     * This is where the example will store its OpenID information.
     * You should change this path if you want the example store to be
     * created elsewhere.  After you're done playing with the example
     * script, you'll have to remove this directory manually.
     */
	define('TEMP_STORE_PATH', "tmp/_php_consumer_test");

	/**
	 * map the following CONST to a proper file for your opperatin system/ enviroment
	 *
	 * "source/Auth/OpenID/CryptUtil.php"
	 *
	 * define('Auth_OpenID_RAND_SOURCE', 'C:\_net_capture\001.pcap');
	 */
?>