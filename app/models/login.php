<?php
/*
Developed by : Babar Ali
Project	     : MMM
Login Model Class used for User Login with email and password validation
*/

class Login extends AppModel
{
var $name = 'Login';
var $useTable= 'users';
var $validate = array(
	'email' => VALID_EMAIL,	
        'password' => VALID_NOT_EMPTY

     ); 
}
?>
