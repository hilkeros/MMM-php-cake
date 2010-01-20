<?php
/*
Developed by : Babar Ali
Project	     : MMM
Login Model Class used for User Login with email and password validation
*/

class Newpass extends AppModel
{
var $name = 'Newpass';
var $useTable= 'users';
var $validate = array(
	'password' => VALID_NOT_EMPTY,
	'npassword' => VALID_NOT_EMPTY,
	'cpassword' => VALID_NOT_EMPTY
     ); 
}
?>
