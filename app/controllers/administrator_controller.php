<?php
/*

Developed by : Babar Ali
Project	     : MMM

*/
class AdministratorController extends AppController {
	var $name = 'Administrator';
	var $uses = array('Admin','Cms','User','Country','Band','Page','Invite','Feedback');
	var $helpers = array('Html', 'Error', 'Javascript', 'Ajax', 'Pagination');
	var $components = array('Pagination','Email','Cookie'); //  use component email
	var $pagenate= array('limit' => 2, 'page' => 1);

	function beforefilter(){
		$this->auth();
		if($this->Session->read('usertype')<>'A')
		{
			$this->redirect('/users/index');
		}

	}	// 	 function beforefilter(){


	function index() {
		$this->redirect('/users/index');
	} // function index()

	/*
	 * name : band
	 * description : display band information
	 * call : edit band 
	 */
	function band(){
		
		$this->layout='admin';
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];
			$result = $this->Band->findAll(array('mmm_id'=>$id));
			if($result)
			{
				$this->Session->write('editbandid',$id);
				$this->set('results',$result);
				
			}
			else
			{
				$this->Session->setFlash('No band defined for this user');
				$this->redirect('/administrator/userlist/');
			}
			
			
		} // if(!empty($this->params['url']['id']))
		else
		{
				$this->Session->setFlash('Please select user band');
				$this->redirect('/administrator/userlist/');
		}
	}
	
	function editband()
	{
		$this->layout='admin';
			
			if(!empty($this->data))
			{
				if($this->Band->save($this->data))
				{
					$id= $this->Session->read('editbandid');
					$this->Session->delete('editbandid');
					$this->Session->setFlash('Band successfully updated');
					$this->redirect('/administrator/band/?id='.$id);
					
				}
			
			}
			elseif(!empty($this->params['url']['id']))
			{
				$id = $this->params['url']['id'];
				$result = $this->Band->find(array('band_id'=>$id));
				if($result)
				{
					
					$this->set('results',$result);
					
				}
				else
				{
					$this->Session->setFlash('Invalid band');
					$this->redirect('/administrator/userlist/');
				}
			}
			else
			{
				$this->Session->setFlash('Please select user band');
				$this->redirect('/administrator/userlist/');	
			}
		
	}
	
	function userlist()
	{
		$this->layout='admin';
		if($this->data)
		{
			if(!empty($this->data['Search']['username']))
			{
				// findAll([$ conditions=null], [$ fields=null], [$ order=null], [$ limit=null], [$ page=1], [$ recursive=null])
				$settings = array ('sortBy'=>'username','show' => 10);
				list($order,$limit,$page) = $this->Pagination->init(array('username'=>$this->data['Search']['username']),NULL,$settings);
				$field = array('Admin.username','Admin.id','Admin.email','Admin.registration_time','Admin.status','Admin.usertype');	
				$results = $this->Admin->findAll(array('username'=>$this->data['Search']['username']), $field, $order, $limit, $page); // Extra parameters added

		 	//			$results = $this->Admin->findAll(array('username'=>$this->data['Search']['username']),null,'username ASC');
		 	$this->set('results', $results);
			} // if(!empty($this->data['Search']['username']))
			else
			{
					
				if($this->data['Admin']['c'])
				{
					foreach ($this->data as $key => $data)
					{
						for($i=0;$i<$data['c'];$i++)
						{
							if($data["selected$i"]==1)
							{
									
								$qry=NULL;
									
								if(($data["chkstatus"]==1) && ($data["chkuserstatus"]==1))
								{
									$qry = "status='".$data["status"]."', usertype='".$data["usertype"]."'";
								}
								elseif($data["chkstatus"]==1)
								{
									$qry= "status='".$data["status"]."'";
								} // 							if($data["chkstatus$i"]==1)

								elseif($data["chkuserstatus"]==1)
								{
									$qry="usertype='".$data["usertype"]."'";
								} // 							if($data['chkuserstatus==1'])
									
								if($qry)
								{
									$query = "UPDATE " . $this->Admin->useTable . " SET ".$qry." WHERE id=".$data["id$i"];
									$this->Admin->query($query);
								}
							} // if($data["selected$i"]==1)
						} // for($i=0;$i<$data['count'];$i++)

					} //  foreach ($results as $key => $data)
						
				} 	// if($this->data['Admin']['c'])

		  	$settings = array ('sortBy'=>'username','show' => 10);
			list($order,$limit,$page) = $this->Pagination->init(array("Admin.username !=''"),NULL,$settings);
			$field = array('Admin.username','Admin.id','Admin.email','Admin.registration_time','Admin.status','Admin.usertype');
		 	$results = $this->Admin->findAll(array("Admin.username !=''"), $field, $order, $limit, $page); // Extra parameters added
		 	$this->set('results', $results);

			}//if(!empty($this->data['Search']['username']))
		} // if($this->data)
		else
		{
			$settings = array ('sortBy'=>'username','show' => 10);
			list($order,$limit,$page) = $this->Pagination->init(array("Admin.username !=''"),NULL,$settings);
			$field = array('Admin.username','Admin.id','Admin.email','Admin.registration_time','Admin.status','Admin.usertype');
			$results = $this->Admin->findAll(array("Admin.username !=''"),$field, $order , $limit, $page); // Extra parameters added
			$this->set('results', $results);

		} // if($this->data)

	} // 	function userlist()


	function user()
	{
		$this->layout='admin';
		
		if(!$this->data)
		{
			$results = $this->Admin->find(array('id'=>$this->params['url']['id']));
			if($results)
			{
				$this->data['Admin']['id']=$results['Admin']['id'];
				$this->data['Admin']['username']=$results['Admin']['username'];
				$this->data['Admin']['email']=$results['Admin']['email'];
				$this->data['Admin']['firstname']=$results['Admin']['firstname'];
				$this->data['Admin']['lastname']=$results['Admin']['lastname'];
				$this->data['Admin']['country']=$results['Admin']['country'];
				$this->data['Admin']['city']=$results['Admin']['city'];
				$this->data['Admin']['postalcode']=$results['Admin']['postalcode'];
				$this->data['Admin']['street']=$results['Admin']['street'];
				$this->data['Admin']['streetnumber']=$results['Admin']['streetnumber'];
				$this->data['Admin']['phonenumber']=$results['Admin']['phonenumber'];
				
				$crecord = $this->Country->findAll();
				foreach($crecord as $key => $cval)
				{
					$country[$cval['Country']['country_code']]=$cval['Country']['country'];
				}
			/*end get list of all country code*/
			$this->set('country',$country);
			} //		if($results)
		} // if(!$this->data)
		else
		{
				
			if($this->data['Admin']['password']!=NULL)
			{
				if($this->data['Admin']['password']!=$this->data['Admin']['cpassword'])
				{
					$this->Session->setFlash('Password mismatch');
					$this->redirect("/administrator/user/?id=".$this->data['Admin']['id']);
				} //if($this->data['Admin']['password']!=$this->data['Admin']['cpassword'])
				else
				{
					$query = "UPDATE " . $this->Admin->useTable . " SET username='".$this->data['Admin']['username']."',
																email='".$this->data['Admin']['email']."',
																firstname='".$this->data['Admin']['firstname']."',
																lastname='".$this->data['Admin']['lastname']."',
																country='".$this->data['Admin']['country']."',
																city='".$this->data['Admin']['city']."',
																postalcode='".$this->data['Admin']['postalcode']."',
																street='".$this->data['Admin']['street']."',
																streetnumber='".$this->data['Admin']['streetnumber']."',
																phonenumber='".$this->data['Admin']['phonenumber']."',
																password='".md5($this->data['Admin']['password'])."' 
							 WHERE id=".$this->data['Admin']['id'];
				}
			} // if($this->data['Admin']['password']!=NULL)
			else
			{
				$query = "UPDATE " . $this->Admin->useTable . " SET username='".$this->data['Admin']['username']."',
																email='".$this->data['Admin']['email']."',
																firstname='".$this->data['Admin']['firstname']."',
																lastname='".$this->data['Admin']['lastname']."',
																country='".$this->data['Admin']['country']."',
																city='".$this->data['Admin']['city']."',
																postalcode='".$this->data['Admin']['postalcode']."',
																street='".$this->data['Admin']['street']."',
																streetnumber='".$this->data['Admin']['streetnumber']."',
																phonenumber='".$this->data['Admin']['phonenumber']."' 
							
			 				WHERE id=".$this->data['Admin']['id'];


			} // 			if($this->data['Admin']['password']!=NULL)

			$this->Admin->query($query);
			$this->Session->setFlash('User\'ve successfully Updated ');
			$this->redirect('/administrator/userlist/');
				
				
		}
	} // 	function user()


	function cms()
	{
		$this->layout='admin';
		if($this->data)
		{
			if($this->Cms->save($this->data))
			{
				//	Write Cookies
				$this->Session->setFlash('CMS Page has been written');
				$this->redirect('/administrator/cmspanel/');
			} // 			if($this->Cms->save($this->data))
		} // if($this->data)


	} // function cms()



	function cmspanel()
	{
		$this->layout='admin';
		$results = $this->Cms->findAll();
		$this->set('results',$results);
		
		$pageResults = $this->Page->findAll();
		$this->set('pageResults',$pageResults);
		
		
	} // function cmspanel()



	function cmsedit()
	{
		$this->layout='admin';
		if($this->data)
		{
			$query = "UPDATE " . $this->Cms->useTable . " SET title='".addslashes($this->data['Cms']['title'])."',
																description='".addslashes($this->data['Cms']['description'])."',
																status='".$this->data['Cms']['status']."'
							
			 					WHERE id=".$this->data['Cms']['id'];
			$this->Cms->query($query);
			$this->Session->setFlash('CMS Page has been Updated');
			$this->redirect('/administrator/cmspanel/');
		} // if($this->data)
		else
		{
			if(!empty($this->params['url']['id']))
			{
				$results = $this->Cms->find(array('id'=>$this->params['url']['id']));
				if($results)
				{
					$this->data['Cms']['id'] = $results['Cms']['id'];
					$this->data['Cms']['title'] = $results['Cms']['title'];
					$this->data['Cms']['description'] = $results['Cms']['description'];
					$this->data['Cms']['status'] = $results['Cms']['status'];
				} // 			if($results)
				else
				{
					$this->Session->setFlash('Invalid reference Id');
					$this->redirect('/administrator/cmspanel/');
				} // 			if($results)
			}
			else
			{
				$this->redirect('/administrator/cmspanel/');
			} //			if(@$this->params['url']['id']))
		} // if($this->data)
	} // 	function edit()
	
	function pages()
	{
		$this->layout='admin';
		if($this->data)
		{
			
			$query = "UPDATE " . $this->Page->useTable . " SET title='".addslashes($this->data['Page']['title'])."',
																description='".addslashes($this->data['Page']['description'])."',
																status='1'
							
			 					WHERE id='".$this->data['Page']['id']."'";
			$this->Page->query($query);
			$this->Session->setFlash('CMS Page has been Updated');
			$this->redirect('/administrator/cmspanel/');
		} // if($this->data)
		else
		{
			if(!empty($this->params['url']['id']))
			{
				$results = $this->Page->find(array('id'=>$this->params['url']['id']));
				if($results)
				{
					$this->data['Page']['id'] 		= $results['Page']['id'];
					$this->data['Page']['title'] 		= $results['Page']['title'];
					$this->data['Page']['description'] 	= $results['Page']['description'];
					$this->data['Page']['status'] 		= $results['Page']['status'];
				} // 			if($results)
				else
				{
					$this->Session->setFlash('Invalid reference Id');
					$this->redirect('/administrator/cmspanel/');
				} // 			if($results)
			}
			else
			{
				$this->redirect('/administrator/cmspanel/');
			} //			if(@$this->params['url']['id']))
		} // if($this->data)
	} // 	function edit()
	

	function cmsdelete()
	{
		if(@$this->params['url']['id'])
		{
			$query = "delete from " . $this->Cms->useTable . "
			 					WHERE id=".$this->params['url']['id'];
			if($this->Cms->query($query))
			{
				$this->Session->setFlash('CMS Page has been deleted');
				$this->redirect('/administrator/cmspanel/');
			} // 				if($this->Cms->query($query))
			else
			{
				$this->Session->setFlash('Invalid reference Id');
				$this->redirect('/administrator/cmspanel/');
			} // 				if($this->Cms->query($query))


		} // if(@$this->params['url']['id'])
		else
		{
			$this->redirect('/administrator/cmspanel/');
		} // if(@$this->params['url']['id'])
			
	} // 		function cmsdelete()


	/*
	 *	name : login
	 *	description : user login by administrator
	 *	
	 */
	function login()
	{
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'] ;
			$results = $this->Admin->find(array('id'=> $id));
			if($results)
			{
				if($results['Admin']['usertype']=='A')
				{
					$this->Session->write('usertype',$results['Admin']['usertype']);
				} // if($resutls['Login']['usertype']=='A')
	
				// Write Cookies
				$this->Cookie->write('id',array('id'=>$results['Admin']['id']));
				
				// Write Sessions
				$this->Session->write('id', $results['Admin']['id']);
				$this->Session->write('user', $results['Admin']['username']);
				// set "last_login" session equal to users last login time
				$this->Session->write('login', $results['Admin']['last_login']);
				$this->Session->write('ids', $results['Admin']['session_id']);
				$this->redirect('/dashboard/index/');
				exit;
							
			} else {
			// login data is wrong, redirect to login page
				$this->Session->setFlash('Invalid reference Id.');
				$this->redirect('/administrator/userlist/');
				exit;
				
			}
		}
		else
		{
			$this->Session->setFlash('Invalid reference Id');
			$this->redirect('/administrator/userlist/');
			exit;
		}
	}
	
	/*
	 * name : invitation
	 * description : invitation code generation and send email address.
	 */
	function invitation()
	{
		$this->layout='admin';
		if(!empty($this->data['Invite']))
		{
		      if(!empty($this->data['Invite']['description']))
		      {

				
				
				$data = $this->data['Invite']['invite'];
				$email = explode("\r\n",$data);
				$result = NULL;
				$count = 0;
				foreach($email as $mail)
				{
					 if($mail)
					 {
						 $code = $this->generateCode();
						 $this->set('code',$code);
						 $description = $this->data['Invite']['description'];
						 $invite_code= $this->data['Invite']['code'];
						 $invite_code .= ": <a href=http://".$_SERVER['HTTP_HOST'].$this->base."/users/register/?code=$code target=_blank>$code</a>";
						 $end_description= $this->data['Invite']['bottom'];
						 $unsubscribe= $this->data['Invite']['unsubscribe'];
						 $unsubscribe.= " <a href=http://".$_SERVER['HTTP_HOST'].$this->base."/users/unsubscribe/?code=$code target=_blank>uitschrijven</a>.";
						 
						
						$description.= "<br>".$invite_code;
						$description.= "<br>".$end_description;
						$description.= "<br>".$unsubscribe;
									
						 $this->set('description',$description);				
						 $this->sendmail($mail,'invite','MMM Invitation','register@yourtri.be');
					       
						 $result['Invite']['email'] = $mail;
						 $result['Invite']['code'] = $code;
						 $result['Invite']['fdate'] = time();
						 $result['Invite']['mailed'] = "Yes";
						 $this->Invite->create();
						 $this->Invite->save($result);
						 $count ++;
					 }
				       
				}
				$this->Session->setFlash("Email successfully send to $count people.");
				$this->redirect('/administrator/invitation/');
				exit;
		      }
		      else
		      {
				$this->Session->setFlash("Please enter email text.");
		      }
		}
		elseif(!empty($this->data['Admin']))
		{
		    
		       $count=0;
		       $result = $this->Invite->findAll(array('fdate'=>0 ,'mailed'=>'Yes'));
		       
		       if($result)
		       {
			       foreach($result as $key => $val)
			       {
		       
				       $code = $this->generateCode();
				       $this->set('code',$code);
				       $this->sendmail($val['Invite']['email'],'invitation','MMM Invitation','register@yourtri.be');
				       
				       $record['Invite']['email'] = $val['Invite']['email'];
				       $record['Invite']['code'] = $code;
				       $record['Invite']['fdate'] = time();
				       
				       $this->Invite->save($record);
				       $count++;
							       
			       }
			       
			        $this->Session->setFlash("Email successfully send to $count people.");
			       	$this->redirect('/administrator/invitation/');
				exit;
			       
		       } // if($result)
		       else
		       {
			        $this->Session->setFlash("Email already send to all people.");
			       	$this->redirect('/administrator/invitation/');
				exit;
		       }
		}
		
	}
	
	/*
	 * name : feedback
	 * description : list of feedback include time , text & sender email address.
	 */
	function feedback()
	{
		$this->layout='admin';
		$feedbackResult = $this->Feedback->findAll(null,null,'tdate desc',null,null);
		$this->set('feedbackResult',$feedbackResult);
		
	}
	/*
	 * name : mailreport
	 * description : list of email address to whom mail sended
	 */
	function mailreport()
	{
		$this->layout = 'admin';
		$total_email = 0;
		$total_respond = 0;
		$total_unsubscribe = 0;
		$total_return=0;
		
		$result = $this->Invite->findAll(array('fdate'=>'!= 0'),array('email','from_unixtime(fdate) fdate','from_unixtime(tdate) tdate','code','status','subscriber'));
		foreach($result as $key => $val)
		{
				if($val['Invite']['status']==1)
				{
					$total_respond ++ ;
				}
				elseif($val['Invite']['status']==2)
				{
					$total_return ++ ;
				}
				
				if($val['Invite']['subscriber']==0)
				{
					$total_unsubscribe ++;
				}
		}
		
		$total_email = count($result);
		
		$this->set('total_email',$total_email);
		$this->set('total_respond',$total_respond);
		$this->set('total_unsubscribe',$total_unsubscribe);
		$this->set('total_return',$total_return);
		$this->set('reportResult',$result);
		
		$user = $this->User->find(null , array('count(*) as count'));
		$this->set('total_user',$user[0]['count']);
		
			
	}
	
	/*
	 *	name : generatePassword
	 *	description : 16 digits length unique code generate
	 *	return : 16 digits code
	 *
	 */
	function generateCode ($length = 16)
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
	
} //class UsersController extends AppController
?>
