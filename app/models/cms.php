<?php
/*
Developed by : Babar Ali
Project	     : MMM
User Model Class used for User Registration with validation
*/

class Cms extends AppModel
{
var $name = 'Cms';
var $validate = array(
	'title' => VALID_NOT_EMPTY,
	'content' => VALID_NOT_EMPTY,
     ); 
}
?>
