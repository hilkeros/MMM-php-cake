<?php

/*

Developed by : Babar Ali
Project	     : MMM

*/
class UsersController extends AppController {
	var $name = 'Users';
	var $uses = array('User','Login','Forget','Newpass','Admin','Cms','Country','Band','Page','Tip','Invite');
	var $helpers = array('Html', 'Error', 'Javascript', 'Ajax');
	var $components = array('Email','Cookie'); //  use component email

	/* 	name : beforefilter
	  *	description : set session values using cookie if session expire
	  *
	*/
	
	function beforefilter(){
		if (!$this->Session->check('ids'))
		{
			if(isset($_COOKIE['id']))
			{
				$id=$this->Cookie->read('id');
				$id = $id['id'];
				
				$results = $this->User->find(array('id'=>$id));
				// set session value
				$this->Session->write('id', $results['User']['id']);
				$this->Session->write('user', $results['User']['username']);
				$this->Session->write('usertype', $results['User']['usertype']);
				// set "last_login" session equal to users last login time
				$this->Session->write('login', $results['User']['last_login']);
				$this->Session->write('ids', $results['User']['session_id']);
				$results['User']['last_login'] = date("Y-m-d H:i:s");
				// save last_login date
				$this->User->save($results);
			} // 		if(isset($_COOKIE['id']))
		}	//if($this->Session->check('email'))
		
		if($this->Session->check('id'))
		{
			$this->layout = "default";
		}
		else
		{
			$this->layout = "home";
		}
	}	// 	 function beforefilter(){



	/*
	 *	name : index
	 *	descrption : application home page
	 *	redirect : dashboard home page if user loggedin
	 */
	function index() {
			
			if($this->Session->check('user'))
			{
				$this->redirect('/dashboard/index/');
				exit;
			}
			
			$result= $this->Page->findAll(array('id'=>array('home','register'),'status'=>'1'));
			if($result)
			{
				$home = $result[0]['Page']['description'];
				$register = $result[1]['Page']['description'];
				
				$this->set('home',$home);
				$this->set('register',$register);
				
			}
			else
			{
				$this->set('home',' ');
				$this->set('register',' ');
			}
			$this->set('home',true);
				
	} // function index()


	/*
	 *	name : agreement
	 *	description : popup lightbox window for agreement details
	 */
	function agreement() {
		$this->layout = "stats";
		$result= $this->Page->find(array('id'=>'agreement','status'=>'1'));
			if($result)
			{
				$agreement= $result['Page']['description'];
				$this->set('agreement',$agreement);
			}
			else
			{
				$this->set('agreement',' ');

			}
		$this->set('Users');

	} // function agreement() {
	
	/*
	 *	name : privacy
	 *	description : popup lightbox window for privacy policy details
	 */
	function privacy() {
		$this->layout = "stats";
		$result= $this->Page->find(array('id'=>'policy','status'=>'1'));
			if($result)
			{
				$privacy= $result['Page']['description'];
				$this->set('privacy',$privacy);
			}
			else
			{
				$this->set('privacy',' ');

			}
		$this->set('Users');

	} // function agreement() {
	
	/*
	  * 	name : login
	  *	description : login application set session values
	  *	redirect :  /dashboard/index/   on successfull login
	*/
	
	function login() {
		
			if($this->Session->check('user'))
			{
				$this->redirect('/dashboard/index/');
				exit;
			}
			
			if ($this->data) {
					
				$results = $this->Login->find(array('email' => $this->data['Login']['email']));
				if($results && $results['Login']['status']!=1)
				{
					$this->Session->setFlash(' Please check your email and confirm your identity.');
					$this->redirect('/users/index/');
					exit;
				} // 	if($results['Login']['registration_time']==NULL)
				else
				{
					if ($results && $results['Login']['password'] == md5($this->data['Login']['password'])) {
	
						// User type set
						if($results['Login']['usertype']=='A')
						{
							$this->Session->write('usertype',$results['Login']['usertype']);
						} // if($resutls['Login']['usertype']=='A')
	
						// Write Cookies
						$this->Cookie->write('id',array('id'=>$results['Login']['id']));
						
							
						// Write Sessions
							
						$this->Session->write('id', $results['Login']['id']);
						$this->Session->write('user', $results['Login']['username']);
						// set "last_login" session equal to users last login time
						$this->Session->write('login', $results['Login']['last_login']);
						$this->Session->write('ids', $results['Login']['session_id']);
						$results['Login']['last_login'] = date("Y-m-d H:i:s");
							
						// save last_login date
						$this->User->save($results);
						$this->redirect('/dashboard/index/');
							
					} else {
						// login data is wrong, redirect to login page
						$this->Session->setFlash('Wrong username or password. Please try again.');
						$this->set('error', false);
						$this->redirect('/users/index/');
						exit;
					} // if ($results && $results['User']['password'] == md5($this->data['User']['password']))
				} //	if($results['Login']['registration_time']==NULL)
	
	
			} // if ($this->data)]
			else
			{
				$this->redirect('/users/index/');
				exit;
			}

	} //function login()

	
	/*
	 *	name : logout
	 *	description : logout from application and destroy session & cookies
	 */
	function logout()
	{
		//Destroy session
		$this->Session->destroy();
		//Delete Cookies Value
		$this->Cookie->delete('id');
		$base = $this->base;
		
		

		setcookie('f83446549e7fc01a7240acb7d6e8b938_user', '', time()-1, "$base/fbs/index");
		setcookie('f83446549e7fc01a7240acb7d6e8b938_session_key', '', time()-1, "$base/fbs/index");
		setcookie('f83446549e7fc01a7240acb7d6e8b938_expires', '', time()-1, "$base/fbs/index");
		setcookie('f83446549e7fc01a7240acb7d6e8b938', '', time()-1, "$base/fbs/index");

		$this->Session->setFlash(' You have successfully logged out.');
		$this->redirect('/users/index/');
	}


	/*
	 *	name : logoutfbs
	 *	description : logout from facebook on completion of facebook wizard.
	 *
	 */
	function logoutfbs()
	{
		$base = $this->base;
		$band  =$this->Cookie->read('flag'); // myspace called from wizard or setting manage
		$flag= $band["flag"];
		
		 $useragent = $_SERVER['HTTP_USER_AGENT'];
		 if(preg_match('|Firefox/([0-9\.]+)|',$useragent,$matched))
		 {
			setcookie('f83446549e7fc01a7240acb7d6e8b938_user', '', time()-1, "$base/fbs/index/");
			setcookie('f83446549e7fc01a7240acb7d6e8b938_session_key', '', time()-1, "$base/fbs/index/");
			setcookie('f83446549e7fc01a7240acb7d6e8b938_expires', '', time()-1, "$base/fbs/index/");
			setcookie('f83446549e7fc01a7240acb7d6e8b938', '', time()-1, "$base/fbs/index/");	
		 }
		 else
		 {
			setcookie('f83446549e7fc01a7240acb7d6e8b938_user', '', time()-1, "$base/fbs/index");
			setcookie('f83446549e7fc01a7240acb7d6e8b938_session_key', '', time()-1, "$base/fbs/index");
			setcookie('f83446549e7fc01a7240acb7d6e8b938_expires', '', time()-1, "$base/fbs/index");
			setcookie('f83446549e7fc01a7240acb7d6e8b938', '', time()-1, "$base/fbs/index");	
		 }
			
			
		

		if($flag=='b')
		{
			if($this->Session->check('fbsgenereatekey'))
			{
				if($this->Session->read('fbsgenereatekey')=='false')
				{
					$this->redirect('/band/facebook/');
				} // if($this->Session->read('fbsgenereatekey')=='false')
				else
				{
					$this->redirect('/band/youtube/');
				}
			} // if($this->Session->check('fbsgenereatekey'))
			else
			{
				$this->redirect('/band/youtube/');
			}
		}
		else
		{
			$this->redirect('/band/manage/');
		}

	}


	/*
	 *	name : registration
	 *	
	 *
	 */
	function registration()
	{
		$this->set('Users');
	}


	/*
	 *	name : add
	 *	description : add user for MMM application & generate email on success
	 *
	 */
	function add() {
		  
		if($this->Session->check('user'))
		{
		
			$this->redirect('/dashboard/index/');
			exit;
		}
		elseif(!empty($this->data['User']))
		{
			if(empty($this->data['User']['email']))
			{
				$this->User->save($this->data); // return email required messsage to view
			} //if(empty($this->data['User']['email']))
			else
			{
				$results = $this->User->findByEmail($this->data['User']['email']);
				if($results)
				{
					$this->Session->setFlash('This email address already exists. Please try again with another email address.');
					
				} // if($results)
				else
				{
					if($this->data['User']['password']!=$this->data['Admin']['cpassword'])
					{
		
						$this->Session->setFlash('Password mismatch.');
					} // 	 if($this->data['User']['password']!=$this->data['Admin']['cpassword'])						if($this->data['User']['password']!=$this->data['Admin']['cpassword'])
					else
					{
		
						session_regenerate_id();
						$session_id = session_id();
		
		
						$this->data['User']['password']=md5($this->data['User']['password']);
						$this->data['User']['session_id']=$session_id;
		
						if($this->User->save($this->data))
						{
							$id = $this->User->getLastInsertId();
							$this->set('id', $id);
							$this->set('email', $this->data['User']['email']);
							$this->set('user', $this->data['User']['username']);
							$this->set('ids',$session_id);
							$this->set('base',$this->base);
							
							/* Dynamic Tips :	enable tips for specific user */
							$tip['Tip']['id'] = $id;
							$tip['Tip']['tip'] = 'user-setting';
							$this->Tip->Save($tip);
							/*end Dynamic Tips */
		
							
							$email = $this->data['User']['email'] ;
							$this->Session->write('email', $email );
							
							
							$this->send_confirmation_mail($this->data['User']['email']);
		
							$this->Session->write('id', $id);
							$this->Session->write('user', $this->data['User']['username']);
							$this->Cookie->write('id',array('id'=>$id));
							
							$this->data = NULL;
							$this->Session->write('session_id', $session_id);
									
							$this->redirect('/band/welcome/');
						} // if ($this->User->save($this->data))
						else
						{
							$this->data['User']['password']=NULL;
							$this->data['User']['session_id']=NULL;
						} //  if ($this->User->save($this->data))
					} // if($this->data['User']['password']!=$this->data['Admin']['cpassword'])
				} //if($results)
			} //if(empty($this->data['User']['email']))
			
		}
		elseif(!empty($this->params['url']['code']) and ($this->Session->check('invite')))  // if redirect from email invitation
		{
		
			/*
			 *  100 user limit 
				Disabled for now
			$user = $this->User->findAll();
			if(count($user) >= 130)
			{
				$qry = "update invite_people set status='2' where code='".$this->params['url']['code']."'";
				$this->Invite->query($qry);
				
				$this->Session->setFlash('Our private beta is currently closed for new users. When everything is stable, we will invite you to try again.');
				$this->redirect('/users/index/');
				exit;	
			}
			*/
			
		}
		elseif(!empty($this->data['Login']))
		{
			
			$result = $this->Invite->find(array('code'=>$this->data['Login']['invitation']));
			if($result)
			{
				$this->Session->write('invite',$this->data['Login']['invitation']);
				
				$record['Invite']['email'] = $result['Invite']['email'];
				$record['Invite']['tdate'] = time();
				$record['Invite']['status'] = "1";
				$this->Invite->save($record);
				
			}
			else
			{
				$this->Session->setFlash('Invalid code. Check your e-mail.');
				$this->redirect('/users/index/');
				exit;
			}
			
		}
		else
		{
		
			$this->Session->setFlash('Invalid invite code. We are currently in private beta. If you want to receive an invite code, mail to hilke@mmmotion.com ');
			$this->redirect('/users/index/');
			exit;			
		}
		
			/* to get list of all country with country code */
			$crecord = $this->Country->findAll();
			
			foreach($crecord as $key => $cval)
			{
				$country[$cval['Country']['country_code']]=$cval['Country']['country'];
			}
			
			/*end get list of all country code*/
			
			$this->set('country',$country);
	} // 	function add() 

	
	/*
	 *	name : edituser
	 *	description : Edit user personal information
	 *	called : called from /band/index/
	 *	return : true when updated
	 */
	function edituser() {
				
		
		$this->set('editband',true);
		
		if($this->Session->check('band_id'))
		{
			$this->set('bandid',$this->Session->read('band_id'));
		}
		elseif($this->Cookie->read('bandid'))
		{
			$band_id =$this->Cookie->read('bandid');
			$this->set('bandid',$band_id['bandid']);
		} // if($this->data)
		
		if (!empty($this->data))
		{
			
			if($this->Admin->save($this->data))
			{
					$this->Session->setFlash('Personal settings updated.');
					$this->redirect('/band/index/');
			} //							if($this->data['User']['password']!=$this->data['Admin']['cpassword'])
			else
			{
					$this->Session->setFlash('Sorry, we were unable to save your data.');
					$this->redirect('/band/index/');
					
			}
		} // 		if (!empty($this->data))
		else
		{
			/* to get list of all country with country code */
			
			$crecord = $this->Country->findAll();
			foreach($crecord as $key => $cval)
			{
				$country[$cval['Country']['country_code']]=$cval['Country']['country'];
			}
			/*end get list of all country code*/
			$this->set('country',$country);
			
			$mmm_id = $this->Session->read('id');
			if($mmm_id)
			{
				$results = $this->Admin->find(array('id'=>$mmm_id , 'status'=>'1'));
				if($results)
				{
					$this->set('results',$results);
					
				}
				else
				{
					$this->redirect('/band/index/');
				}
			}
			else
			{
				$this->redirect('/band/index/');
			}
		}// 		if (!empty($this->data))

	} // 	function add() {

	
	/*
	 *	name : send
	 *	description : send mail to user for your account registration
	 *
	 */
	function send_confirmation_mail($email)
	{
		$this->Email->template = 'email/confirm';
		$this->Email->to = $email;
		$this->Email->subject = 'your new account';
		$result = $this->Email->send('email', 'register@mmmotion.com');


	}  //     function send() {

	
	/*
	 *	name :	authentiation
	 *	description : user account email authentication
	 *
	 */
	function authentication()
	{
		if(!empty($this->params['url']['sid']) and !empty($this->params['url']['email']))
		{
			$record = $this->User->find(array('session_id' => $this->params['url']['sid'] , 'email' => $this->params['url']['email']));
				
			if($record)
			{
				$results['Login']['last_login'] = date("Y-m-d H:i:s");
				$this->Session->write('id', $record['User']['id']);
				$this->Session->write('user', $record['User']['username']);
				$this->Session->write('login', $record['User']['last_login']);
				$this->Cookie->write('id',array('id'=>$record['User']['id']));
					
				if($record['User']['registration_time']==NULL)
				{
					$record['User']['registration_time']=time();
					$record['User']['status']=1;
					$this->User->save($record);
				}
					
					
					$result = $this->Band->find(array('mmm_id'=>$record['User']['id']));
					if($result)
					{
						$this->Session->setFlash('Thank you for confirming your email adress');
						$this->redirect('/users/index/');
					}
					else
					{
						$this->Session->write('session_id', $record['User']['session_id']);
						$this->redirect('/band/welcome/');
					}
				
			} // if($record)
			else
			{
				$this->redirect('/users/add/');
			} // if($record)
		} // if(@$this->params['url']['sid'] and @$this->params['url']['email'])
		else
		{
			$this->redirect('/users/add/');

		} // if(@$this->params['url']['sid'] and @$this->params['url']['email'])
	} // function authentication()


	/*
	 *	name : sendmail
	 *	description : send mail to user
	 *
	 */
	function sendmail($email,$temp,$subject,$from)
	{

		$this->Email->to = $email;
		$this->Email->subject = $subject;
		$result = $this->Email->send($temp,$from);

	}  //     function send() {


	/*
	 *	name : forgetpassword
	 *	description : email generate with new password 
	 *
	 */
	 
	function forgetpassword()
	{


		if($this->Session->check('user'))
		{
			$this->redirect('/dashboard/index/');
			exit;
		}
		
		if ($this->data) {
			
			// check submitted email address against database
			if(!empty($this->data['Forget']['email']))
			{
				$results = $this->Forget->findByEmail($this->data['Forget']['email']);
				if($results)
				{
					$password = $this->generatePassword();
					$results['Forget']['password'] = md5($password);
					$this->Forget->save($results);
					$this->set('password',$password);
					$this->set('email',$this->data['Forget']['email']);
					$this->sendmail($this->data['Forget']['email'],'forget','your new password','register@mmmotion.com');
					$this->Session->setFlash('A new password has been sent to your mailbox.');
					$this->redirect('/users/index/');
				} //
				else
				{
					$this->Session->setFlash('Invalid email address.');
					$this->redirect('/users/forgetpassword/');
				} // if($results)
			}
			else
			{
					$this->Session->setFlash('email address is required field.');
			}
		} // if ($this->data) {

	} // function forgetpassword()


	/*
	 *	name : generatePassword
	 *	description : 8 digits length unique password generate
	 *	return : 8 digits password
	 *
	 */
	
	function generatePassword ($length = 8)
	{
		// start with a blank password
		$password = "";

		// define possible characters
		$possible = "0123456789bcdfghjkmnpqrstvwxyz";

		// set up a counter
		$i = 0;

		// add random characters to $password until $length is reached
		while ($i < $length) {

			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

			// we don't want this character if it's already in the password
			if (!strstr($password, $char)) {
		  $password .= $char;
		  $i++;
			} // 		if (!strstr($password, $char))

		} // 	  while ($i < $length)

		// done!
		return $password;


	} //function generatePassword ($length = 8)

	/*
	 *	name : changepass
	 *	description : to change mmm user password
	 *
	 */
	function changepass()
	{
		if($this->data)
		{
			if($this->data['Newpass']['npassword']==$this->data['Newpass']['cpassword'])
			{
				//$email=	$this->Session->read('email');
				$id	=	$this->Session->read('id');
				//$record = $this->Newpass->find(array('email' => $email, 'password' => md5($this->data['Newpass']['password'])));
				$record = $this->Newpass->find(array('id' => $id, 'password' => md5($this->data['Newpass']['password'])));
				if($record)
				{
					$password =md5($this->data['Newpass']['npassword']);
					$record['Newpass']['password'] = $password;
					//$this->Cookie->write('password',array('password',$password));
					$this->Newpass->save($record);
					$this->Session->setFlash('Your password has been changed.');
					$this->redirect('/band/index/');
				} // if($record)
				else
				{
					$this->Session->setFlash('Wrong password. Please try again.');
					$this->redirect('/band/index/');
				}
			} // if($this->data['Newpass']['npassword']==$this->data['Newpass']['cpassword'])
			else
			{
				$this->Session->setFlash('Password mismatch.');
				$this->redirect('/band/index/');
			}
		} //if($this->data)
	} // 	function changepass()

	
	/*
	 *	name : contactus
	 *	description : contact us form & generate email
	 *
	 */
	function contactus()
	{
		if($this->data)
		{
			$this->set('topic',$this->data['User']['topic']);
			$this->set('message',$this->data['User']['message']);
			$subject = "Contact Us  ".$this->data['User']['type'];
			$this->sendmail(ADMIN_EMAIL,'contactus',$subject,'register@mmmotion.com');
			$this->redirect('/users/index/');

		} // 		if($this->data)
		else
		{
			$record = $this->User->find(array('id' => $this->Session->read('id')));
			$this->set('fname',$record['User']['firstname']);
			$this->set('lname',$record['User']['lastname']);
		} // 		if($this->data)
	} // function contactus()

	/*
	 *	name : confirmation
	 *	description
	 */
	function confirmation() {
	}
	 
	 /*	
	  *	name : showcms
	  *	descirption : cms panel
	  *
	  *
	  */
	function showcms()
	{
		if($this->Session->check('id'))
		{
			
			
			$this->set('editband',true);
			if($this->Session->check('band_id'))
			{
				$this->set('bandid',$this->Session->read('band_id'));	
			}
			elseif($this->Cookie->valid('bandid'))
			{
				$band_id =$this->Cookie->read('bandid');
				$id = $band_id['bandid'];
				$this->set('bandid',$id);
			} // if($this->data)
		}
		$results = $this->Cms->find(array('id' => $this->params['url']['id']));
		$this->set('results',$results);
		$this->set('cmsid',$this->params['url']['id']);
	}
	
	
	/*
	  *	name :  search
	  *	description : google search 
	  *
	*/
	function search()
	{
		$this->layout="google";
		if(empty($this->params['url']['q']))
		{
			if(!empty($_SERVER['HTTP_REFERER']))
			{
				$this->redirect($_SERVER['HTTP_REFERER']);
			}
			else
			{
				$this->redirect('/users/index/');
			}

		}
	}
	
	/*
	 * name : register
	 * description : email confirmation for register
	 */
	function register()
	{
		if(!empty($this->params['url']['code']))
		{
			
			$result = $this->Invite->find(array('code'=>$this->params['url']['code']));
			if($result)
			{
				$this->Session->write('invite',$this->params['url']['code']);
				
				$record['Invite']['email'] = $result['Invite']['email'];
				$record['Invite']['tdate'] = time();
				$record['Invite']['status'] = "1";
				$this->Invite->save($record);
				
				$this->redirect("/users/add/?code=".$this->params['url']['code']);
				exit;
			}
			else
			{
				$this->Session->setFlash('Invalid code. Check your e-mail.');
				$this->redirect('/users/index/');
				exit;
			}
		}
		else
		{
			$this->Session->setFlash('Invalid code. Check your e-mail.');
			$this->redirect('/users/index/');
			exit;
		}
	}
	
	/*
	 * name : unsubscribe
	 * description : unsubscriber from mailing list
	 */
	function unsubscribe()
	{
		if(!empty($this->params['url']['code']))
		{
			
			$result = $this->Invite->find(array('code'=>$this->params['url']['code']));
			if($result)
			{
				$record['Invite']['email'] = $result['Invite']['email'];
				$record['Invite']['tdate'] = time();
				$record['Invite']['status'] = "1";
				$record['Invite']['subscriber'] = 0;
				$this->Invite->save($record);
				
				$this->Session->setFlash('Successfully unsubscribe from mailing list.');
				$this->redirect("/users/index/");
				exit;
			}
			else
			{
				$this->Session->setFlash('Invalid code. Check your e-mail.');
				$this->redirect('/users/index/');
				exit;
			}
		}
		else
		{
			$this->Session->setFlash('Invalid code. Check your e-mail.');
			$this->redirect('/users/index/');
			exit;
		}
	}
	
	function service_unavailable()
	{
		$this->layout = "home";
	}
} //class UsersController extends AppController
?>
