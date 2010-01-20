<?php
//vendor('facebook/facebook'); 
/*

Developer : Babar Ali
Date	  : Nov 3rd , 2008

*/

/*
 uses('i18n');
 uses('l10n');
*/

class AppController extends Controller {
//	var $facebook;
	var $helpers = array('Javascript', 'Ajax');
	var $uses = array('Cms','Band');
	

//	var $__fbApiKey = 'f83446549e7fc01a7240acb7d6e8b938';
//	var $__fbSecret = 'de4d3704ad7db145f66589e649498688';


	function __construct() {
		parent::__construct();

		// Prevent the 'Undefined index: facebook_config' notice from being thrown.
//		$GLOBALS['facebook_config']['debug'] = NULL;

		// Create a Facebook client API object.
//		$this->facebook = new Facebook($this->__fbApiKey, $this->__fbSecret);
	}

	
	
	
	function beforeRender() {
		
		if($this->Session->check('id'))
		{
			$mmm_id = $this->Session->read('id');
			$band = $this->Band->findAll(array('mmm_id'=>$mmm_id,'status'=>'1'));
			$this->set('band',$band);
		}
		
		$results = $this->Cms->findAll(array('status' => '1'),array('id' , 'title'));
		$this->set('cms',$results);
		
	}
	

	function auth()
	{
//	$this->redirect("/users/service_unavailable/");
//	exit;
	if (!$this->Session->check('id'))
		{
	
			if(isset($_COOKIE['id']))
			{
	
				$id=$this->Cookie->read('id');
				$id = $id['id'];

				$results = $this->User->find(array('id'=>$id));

				// set session value
				$this->Session->write('email', $results['User']['email']);
				$this->Session->write('id', $results['User']['id']);
				$this->Session->write('user', $results['User']['username']);
				$this->Session->write('usertype', $results['User']['usertype']);
				// set "last_login" session equal to users last login time
				$this->Session->write('login', $results['User']['last_login']);
				$this->Session->write('ids', $results['User']['session_id']);
				$results['User']['last_login'] = date("Y-m-d H:i:s");
				// save last_login date
				$this->User->save($results);
			} // 		if(isset($_COOKIE['email']) and isset($_COOKIE['password']))
			else
			{
	
				$this->Session->setFlash('You are not authorized to access , Please login');
				$this->redirect('/users/index/');
				exit;
			} // if(isset($_COOKIE['email']) and isset($_COOKIE['password']))

		}	//if($this->Session->check('email'))
	
	} // function auth()
} // class AppController extends Controller


?>
