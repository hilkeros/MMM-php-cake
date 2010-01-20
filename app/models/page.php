<?php
/*
Developed by : Babar Ali
Project	     : MMM
User Model Class used for User Registration with validation
*/

class Page extends AppModel
{
	var $name = 'Page';
	var $useTable= 'pages';
	var $primaryKey  = 'id';

var $validate = array(
	'title' => VALID_NOT_EMPTY,
	'content' => VALID_NOT_EMPTY,
     ); 
}
?>
