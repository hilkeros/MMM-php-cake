<?php
/*
Developed by : Babar Ali
Project	     : MMM
Login Model Class used for User Login with email and password validation
*/

class Forget extends AppModel
{
var $name = 'Forget';
var $useTable= 'users';
var $validate = array(
	'email' => VALID_EMAIL
      ); 
}
?>
