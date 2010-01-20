<?php
/*
Developed by : Babar Ali
Project	     : MMM
User Model Class used for User Registration with validation
*/

class User extends AppModel
{

var $name = 'User';
var $useTable= 'users';
var $validate = array(
	'username' => VALID_NOT_EMPTY,
	'username' => '/[a-z0-9\_\-]{3,}$/i',
	'Agreement' => '/[1]$/i',
	'email' => VALID_NOT_EMPTY,
	'email' => VALID_EMAIL,	
	'firstname' => VALID_NOT_EMPTY,
	'lastname' => VALID_NOT_EMPTY,
	'password' => VALID_NOT_EMPTY
     ); 
}
?>
