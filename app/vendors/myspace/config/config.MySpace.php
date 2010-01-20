<?php

	//please register at http://developer.myspace.com for a CONSUMERK_KEY
	define('CONSUMER_KEY', '59f0f7ec119649698d1c5710afc29824');
//	define('CONSUMER_KEY', 'http://www.myspace.com/499532349');
	//please register at http://developer.myspace.com for a CONSUMER_SECRET
	define('CONSUMER_SECRET', '2cb0a56532434ae7b34b9f95253e3082ccd08b5079354d208378a13cc1a44c5f');
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
