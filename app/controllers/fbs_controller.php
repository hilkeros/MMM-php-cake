<?php
vendor('facebook/facebook'); 
class FbsController extends AppController {
	var $user;
	var $key;
	var $lg_id;
	var $time = NULL;
	var $flg = true ;
	var $id = NULL;
	var $name = 'Fbs';
	var $uses = array('User','Fb','Fbpage','Fbgroup','Cms');
	var $helpers = array('Html', 'Error','FlashChart','Javascript');
	var $components = array('Cookie'); //  use component email
	var $facebook;
	var $__fbApiKey = 'f83446549e7fc01a7240acb7d6e8b938';
	var $__fbSecret = 'de4d3704ad7db145f66589e649498688';
	
	/**
	 * Name: beforeFilter
	 * Desc: Performs necessary steps and function calls prior to executing
	 *       any view function calls.
	 */
	function beforeFilter() {
		
		$this->auth();
		// Prevent the 'Undefined index: facebook_config' notice from being thrown.
		$GLOBALS['facebook_config']['debug'] = NULL;
		// Create a Facebook client API object.
		$this->facebook = new Facebook($this->__fbApiKey, $this->__fbSecret);
		
		$mmm_id = $this->Session->read('id');
		
		$results = $this->Cms->findAll(array('status' => '1'),array('id' , 'title'));
		$this->set('cms',$results);
	}

	/**
	 * Name: request
	 * return : true if user is admin of page
	 */
	function request()
	{
		$page_id=$this->params['id'];
		$isAdmin=$this->facebook->api_client->pages_isAdmin($page_id);
		return $isAdmin;
	} // 	function request()

	/**
	 * Name: groupmember
	 * return : true total groups member array
	 */

	function groupmember()
	{
		$grp_id=$this->params['id'];
		$member=$this->facebook->api_client->groups_getMembers($grp_id);
		return $member;
	} // 	function groupmember()

	
	/**
	 * Name: updateStatus
	 * descrition : update facebook user and pages status .
	 * called from : dashboard
	 * return : status message to dashbaord.
	 */
	function updateStatus(){
		
		if($this->data)
		{
			$mmm_id = $this->Session->read('id');
			$band_id = $this->data['Dashboard']['bandid'];
		
			$result= $this->Fb->find(array('mmm_id'=>$mmm_id,'band_id'=>$band_id,'status'=>1));
			if($result)
			{
			
				
				$this->facebook->set_user($result['Fb']['user_id'], $result['Fb']['session_key']);
				if(!empty($this->data['Dashboard']['fbs_page']) or !empty($this->data['Dashboard']['fbs_profile']))
				{
					if($this->facebook->api_client->users_isAppUser($result['Fb']['user_id'])==0)  // if not application user & revoke extended permission and delete application
					{
						echo "Your Facebook account is not yet linked to Motion Music Manager. Go to the settings page and add your Facebook profile.";
						exit;
					}
				}
					
				$this->facebook->set_user($result['Fb']['user_id'], $result['Fb']['session_key']);
									
				$uid = $result['Fb']['user_id'];
				$login_id = $result['Fb']['login_id'];
				$text = $this->data['Dashboard']['status'];
				$message = NULL;
				
				// if user has active page.
				if(!empty($this->data['Dashboard']['fbs_page']))
				{
					
					$page = addslashes($result['Fb']['page']);
					$pResult = $this->Fbpage->find(array('name'=>$page,array('not'=>array('p_id'=>null))));
					if($pResult)
					{
						$pid = $pResult['Fbpage']['p_id'];
						// update facebook page status.
						$id= $this->facebook->api_client->users_setStatus($text,$pid);
						//$id = $this->facebook->api_client->stream_publish($text,null,null,$pid);
						
						if($id==1)
						{
							$message =  "Facebook page status updated succesfully<br>";	
						}
						else
						{
							$message =  "We need your permission to publish a status update to your Facebook page. Go to the settings page and edit your Facebook publish permissions.<br>";	
						}
					}
				} // if(!empty($this->data['Dashboard']['fbs_page']))
				
				// Update Facebook user status.
				if(!empty($this->data['Dashboard']['fbs_profile']))
				{
					$id= $this->facebook->api_client->users_setStatus($text,$uid);
					if($id==1)
					{
						$message .= " Facebook status updated succesfully";	
					}
					else
					{
						$message .= "We need your permission to publish a status update to your Facebook personal profile. Go to the settings page and edit your Facebook publish permissions.";	
					}
				} // if(!empty($this->data['Dashboard']['fbs_profile']))
				
				echo $message;
				
			} //if($result)
				
		} // if($this->data)
		
		exit;
	} // function updateStatus(){
	
	
	/**
	 * Name: index
	 * desc : insert pages and groups data and display on view
	  
	 */
	function index() {

			$this->user = $this->facebook->require_login();
			$key = $this->facebook->api_client->session_key;
			$fkey=$key;
			
			
			$band  =$this->Cookie->read('flag'); // myspace called from wizard or setting manage
			$flag= $band["flag"];
			$base=$this->base;
			
			if(strlen($key)<46)
			{
			
			$this->Session->write('fbs_key',$key);
			$this->Cookie->write('fbs_key',array('fbs_key'=>$key));
			
			$band_id=$this->Cookie->read('bandid');
			$band_id = $band_id['bandid'];
			$this->set('bandid',$band_id);
			
			$mmm_id = $this->Session->read('id');
			$result= $this->Fb->find(array('mmm_id'=>$mmm_id  , 'user_id'=>$this->user,'band_id'=>$band_id));
				if($result)  // if same user already exist
				{
												
						$result['Fb']['session_key'] = $key;
						$result['Fb']['active'] = "1";
						$this->Fb->save($result);
						
						$userid= $result['Fb']['user_id'];
						
						$qry = " update fb_login
							set session_key='$key'
							where user_id = '$userid'";
						$this->Fb->query($qry);
						
						$this->Session->write('fbs_login_id',$result['Fb']['login_id']);
						$this->Session->write('fbs_name',$result['Fb']['name']);
						$hasPermission =  $this->facebook->api_client->call_method("facebook.users.hasAppPermission",array(
							'uid'            => $this->user,
							'ext_perm'        => 'offline_access'
						));
						if($hasPermission==1)
						{
							$this->redirect("/fbs/settings/");
							exit;
						}
						else
						{
							$base=$this->base;
							$host = $_SERVER['HTTP_HOST'];
							$url = "http://$host$base/users/logoutfbs/";
							
							$this->set('url',$url);
							$this->layout="wizard";	
							
							
							$rurl = "http://www.facebook.com/logout.php?app_key=f83446549e7fc01a7240acb7d6e8b938&session_key=$fkey&next=$url";
							$purl="http://www.facebook.com/connect/prompt_permissions.php?api_key=f83446549e7fc01a7240acb7d6e8b938&v=1.0&next=http://$host$base/fbs/permission_success/&display=popup&ext_perm=offline_access,publish_stream";
							$this->set('purl',$purl); // prompt authorization offline acess and publish stream.
							$this->set('rurl',$rurl);
							$this->set('inprocess',true);	
						}
						
						
					
				} //if($result)
				else
				{
					$userid = $this->user;
					$us=$this->facebook->api_client->users_getInfo($userid, array('name'));
					
					$nm = addslashes($us['0']['name']);
					if (empty($nm))
					{
						$this->set('name','user');
						$this->Session->write('fbs_name','user');
					}
					else
					{
						$this->set('name',$nm);
						$this->Session->write('fbs_name',$us['0']['name']);
					}
									
					if($band_id)
					{
						// set status = 0 for all 'specific mmm user & band'
						$qry = " update fb_login
							set status=0
							where mmm_id='$mmm_id'
							and band_id  = $band_id
							and status=1";
						$this->Fb->query($qry);
						
						
						
						
						$this->time = time();
						$record['Fb']['name']=$nm;
						$record['Fb']['mmm_id']=$mmm_id;
						$record['Fb']['user_id']=$this->user ;
						$record['Fb']['session_key']=$key;
						$record['Fb']['status']='1';
						$record['Fb']['etime']= $this->time ;
						$record['Fb']['band_id']= $band_id;
						$this->Fb->create();
						$this->Fb->save($record);
						
						$this->lg_id = $this->Fb->getLastInsertId();
						$this->Session->write('fbs_login_id',$this->lg_id);
						$this->flg = false;
						
						$uid= $this->user;
						
						$qry = "update fb_login
							set session_key='$key'
							where user_id = '$uid'";
						$this->Fb->query($qry);
						
						
						$query = "SELECT page_id , name , type , fan_count , website , page_url FROM page WHERE page_id IN (SELECT page_id FROM page_fan WHERE uid = $uid)";
						$pages = $this->facebook->api_client->fql_query($query);
				
						if(!empty($pages))
						{
							foreach($pages as $key => $val)
							{
								$isAdmin=$this->facebook->api_client->pages_isAdmin($val['page_id']);
								if($isAdmin)
								{
									$record['Fbpage']['login_id']=$this->lg_id ;
									$record['Fbpage']['name']=$val['name'];
									$record['Fbpage']['type']=$val['type'];
									$record['Fbpage']['fan_count']=$val['fan_count'] ;
									$record['Fbpage']['website']=$val['website'];
									$record['Fbpage']['page_url']=$val['page_url'];
									$record['Fbpage']['p_id']=$val['page_id'];
									$record['Fbpage']['is_admin']=$isAdmin;
									$record['Fbpage']['etime']=$this->time;
									$this->Fbpage->create();
									$this->Fbpage->save($record);
									$record = NULL ;
								}
							
							} //foreach(pages as $key => $val)
						} // 		if($pages)
	
					//	$grp=$this->facebook->api_client->groups_get($this->user, $gids=null);
					$query = "SELECT gid , name , group_type , group_subtype , creator , update_time , website FROM group WHERE gid IN (SELECT gid FROM group_member WHERE uid = $uid)";
					$grp = $this->facebook->api_client->fql_query($query);
						if(!empty($grp))
						{
	
							foreach ($grp as $key => $val)
							{
								$isCreator = 'No';
								if($val['creator']==$this->user)
								{
									$isCreator = 'Yes';
									$cmember=NULL;
										$grp_id = $val['gid'] ;
										$member=$this->facebook->api_client->groups_getMembers($grp_id);
										$cmember = count($member['members']);
										
	
										if(empty($cmember))
										{ $cmember=0; }
	
								$record['Fbgroup']['login_id']=$this->lg_id ;
								$record['Fbgroup']['gid']=$grp_id;
								$record['Fbgroup']['name']=$val['name'];
								$record['Fbgroup']['type']=$val['group_type'];
								$record['Fbgroup']['sub_type']=$val['group_subtype'] ;
								$record['Fbgroup']['update_time']=$val['update_time'] ;
								$record['Fbgroup']['website']=$val['website'];
								$record['Fbgroup']['member']=$cmember;
								$record['Fbgroup']['isCreator']=$isCreator ;
								$record['Fbgroup']['etime']=$this->time;
	
								$this->Fbgroup->create();
								$this->Fbgroup->save($record);
								} // if($val['creator']==$this->user)
	
							} // foreach ($grp as $key => $val)
	
						} // If ($grp)
						
						
						$hasPermission =  $this->facebook->api_client->call_method("facebook.users.hasAppPermission",array(
							'uid'            => $this->user,
							'ext_perm'        => 'offline_access'
						));
						if($hasPermission==1)
						{
						$this->Session->setFlash('Facebook information has been processed correctly.');
						$this->redirect("/fbs/settings/");
						exit;
						}
						else
						{
							$base=$this->base;
							$host = $_SERVER['HTTP_HOST'];
							$url = "http://$host$base/users/logoutfbs/";
							
							$this->set('url',$url);
							$this->layout="wizard";	
							
							
							$rurl = "http://www.facebook.com/logout.php?app_key=f83446549e7fc01a7240acb7d6e8b938&session_key=$fkey&next=$url";
							$purl="http://www.facebook.com/connect/prompt_permissions.php?api_key=f83446549e7fc01a7240acb7d6e8b938&v=1.0&next=http://$host$base/fbs/permission_success/&display=popup&ext_perm=offline_access,publish_stream";
							$this->set('purl',$purl); // prompt authorization offline acess and publish stream.
							$this->set('rurl',$rurl);
							$this->set('inprocess',true);
						}
						
						
					} // if($band_id)
					else
					{
						$this->Session->setFlash('Please select a band.');
						$this->redirect('/band/index/');
						exit;
					} // if($band_id)
				} // if($result)  // if same user already exist
			} // if(strlen($key)<40)
			else
			{
				$base=$this->base;
				$host = $_SERVER['HTTP_HOST'];
				$url = "http://$host$base/users/logoutfbs/";
				
				$this->set('url',$url);
				$this->layout="wizard";	
				
				
				$rurl = "http://www.facebook.com/logout.php?app_key=f83446549e7fc01a7240acb7d6e8b938&session_key=$fkey&next=$url";
				$purl="http://www.facebook.com/connect/prompt_permissions.php?api_key=f83446549e7fc01a7240acb7d6e8b938&v=1.0&next=http://$host$base/fbs/permission_success/&display=popup&ext_perm=offline_access,publish_stream";
				$this->set('purl',$purl); // prompt authorization offline acess and publish stream.
				$this->set('rurl',$rurl);
				$this->set('inprocess',true);
			} // if(strlen($key)<40)


	} // 	function index() {

	/*
	 * name : login_success
	 * description : when user click edit permission and logged on with facebook on succesful loggon this page call and redirect towards allow publishing permission page.
	*/
	function login_success()
	{
		if($this->Session->check('band_id'))
		{
			$band_id = $this->Session->read('band_id');
		}
		else
		{
			$band_id=$this->Cookie->read('bandid');
			$band_id = $band_id['bandid'];
			
		}
		
		$mmm_id = $this->Session->read('id');
		
		$qry = "select distinct p.p_id , f.user_id , f.session_key from fb_pages p , fb_login f where f.mmm_id=$mmm_id and f.band_id=$band_id and f.login_id = p.login_id and f.page=p.name and p.p_id is not null and f.status=1";
		$result = $this->Fbgroup->findBySql($qry);
		if($result)
		{
			
			$host = $_SERVER['HTTP_HOST'].$this->base;
			$uid = $result['0']['f']['user_id'];
			$key = $result['0']['f']['session_key'];
			$p_id = $result['0']['p']['p_id'];
			$fburl = "http://www.facebook.com/connect/prompt_permissions.php?api_key=f83446549e7fc01a7240acb7d6e8b938&v=1.0&next=http://$host/fbs/permission_success/&display=popup&ext_perm=read_stream,publish_stream&enable_profile_selector=1&profile_selector_ids=$p_id";
			$this->redirect($fburl);
			exit;
		}
		
	} // function login_success()
	
	/*
	 *  name :  permission
	 *  description : when user click edit permission from setting page .. open facebook login screen on success redirected towards login_success 
	 *  
	*/
	function permission()
	{
		if($this->Session->check('band_id'))
		{
			$band_id = $this->Session->read('band_id');
		}
		else
		{
			$band_id=$this->Cookie->read('bandid');
			$band_id = $band_id['bandid'];
			
		}
		
		$mmm_id = $this->Session->read('id');
		
		$qry = "select distinct p.p_id , f.user_id , f.session_key from fb_pages p , fb_login f where f.mmm_id=$mmm_id and f.band_id=$band_id and f.login_id = p.login_id and f.page=p.name and p.p_id is not null and f.status=1";
		$result = $this->Fbgroup->findBySql($qry);
		if($result)
		{
			$host = $_SERVER['HTTP_HOST'].$this->base;
			$uid = $result['0']['f']['user_id'];
			$key = $result['0']['f']['session_key'];
			$p_id = $result['0']['p']['p_id'];
			$url = "http://www.facebook.com/login.php?api_key=f83446549e7fc01a7240acb7d6e8b938&connect_display=popup&v=1.0&next=http://$host/fbs/login_success/&cancel_url=http://www.facebook.com/connect/login_failure.html&fbconnect=true&return_session=true";
		
			$this->layout="wizard";
			$this->set('bandid',$band_id);
			$this->set('url',$url);
		}
		else
		{
			$this->Session->setFlash('Facebook user has no active page.');
			$this->redirect("/band/manage/?bandid=$band_id");
			exit;
			
		}
		
		
	} // function permission()
	
	
	/*
	 * name : permission_success
	 * description : when user click allow publishing and Don't allow page redirected to permission_success page where we showed thanks message.
	*/
	function permission_success()
	{
		
		$this->layout = "stats";
	} // function permission_success()
	
	
	/*
	 * name : settings
	 * description : pages & groups data settings
	 *
	 */
	function settings(){
		
		if($this->Session->check('fbs_login_id'))
		{
			$this->layout="wizard";
			if(!empty($this->data))  // if facebook profile has page then allow publishing permission screen else process=2 means finish or Add new screen
			{
				
					if($this->Session->check('band_id'))
					{
						$band_id = $this->Session->read('band_id');
					}
					else
					{
						$band_id=$this->Cookie->read('bandid');
						$band_id = $band_id['bandid'];
					
					}
					$mmm_id = $this->Session->read('id');
						
					// update Facebook page & group data.
					$login_id = $this->Session->read('fbs_login_id');
					$this->data['Fb']['login_id'] = $login_id ;
					$this->Fb->save($this->data);
					
					
					$qry = "select distinct p.p_id , f.user_id , f.session_key from fb_pages p , fb_login f where f.mmm_id=$mmm_id and f.band_id=$band_id and f.login_id = p.login_id and f.page=p.name and p.p_id is not null and f.status=1";
					$result = $this->Fbgroup->findBySql($qry);
					if($result)  // if page found
					{
						$uid = $result['0']['f']['user_id'];
						$key = $result['0']['f']['session_key'];
						
						
						$this->facebook->set_user($uid, $key);
						//print_r($this->facebook->api_client->users_getLoggedInUser());
						
						$host = $_SERVER['HTTP_HOST'].$this->base;
						$p_id = $result['0']['p']['p_id'];
						$url = "http://www.facebook.com/connect/prompt_permissions.php?api_key=f83446549e7fc01a7240acb7d6e8b938&v=1.0&next=http://$host/fbs/permission_success/&display=popup&ext_perm=read_stream,publish_stream&enable_profile_selector=1&profile_selector_ids=$p_id";
						$this->layout="wizard";
						$this->set('bandid',$band_id);
						$this->set('url',$url);
						$this->set('inprocess',1);
					}
					else // process = 2 call finish or Add new screen
					{
						$this->redirect('/fbs/settings/?type=finish'); // recall setting page and go to finish step
						exit;
					}
			}
			else
			{
				
				if(!empty($this->params['url']['type'])=='finish')
				{
						// if session expire then get from cookie
						if($this->Session->check('fbs_key'))
						{
							$fkey =	$this->Session->read('fbs_key');
						}
						else
						{
							$key=$this->Cookie->read('fbs_key');
							$fkey  = $key['fbs_key'];	
						}
										     
						$this->set('name',$this->Session->read('fbs_name'));
						
						$base=$this->base;
						$host = $_SERVER['HTTP_HOST'];
						
						$url = "http://$host$base/users/logoutfbs/";
						$furl = "http://www.facebook.com/logout.php?app_key=f83446549e7fc01a7240acb7d6e8b938&session_key=$fkey&next=$url";
						$this->set('url',$furl);
						
						$addurl="http://$host$base/fbs/process/";
						$addnewurl = "http://www.facebook.com/logout.php?app_key=f83446549e7fc01a7240acb7d6e8b938&session_key=$fkey&next=$addurl";
						$this->set('addnewurl',$addnewurl);
											
						$band  =$this->Cookie->read('flag'); // myspace called from wizard or setting manage
						$flag= $band["flag"];
						
						$this->set('flag',$flag);
						$this->set('inprocess',2);
					
				} //if(!empty($this->params['url']['type'])=="finish")  
				else
				{
					 // update facebook user page & group 
					if($this->Session->check('fbs_name'))
					{
						$this->set('name',$this->Session->read('fbs_name'));
					}
					else
					{
						$this->set('name','user');
					}
					$id = $this->Session->read('fbs_login_id');
						
						
					$qry ="select distinct name from fb_group where login_id=$id and isCreator='Yes'";
					$result = $this->Fbgroup->findBySql($qry);
					
					if($result)
					{
						foreach($result as $key => $groupVal)
						{
							$name =	stripslashes($groupVal['fb_group']['name']);
							
							$group[$name]= $name;
						}
							$group[' '] = 'None';
					}
					else
					{
							$group[' '] = 'None';
					}
						
						
					$this->set('group',$group);
					
					$qry ="select distinct name from fb_pages where login_id=$id and is_admin=1";
					$result = $this->Fbgroup->findBySql($qry);
					
					if($result)
					{
						foreach($result as $key => $pageVal)
						{
							$name =	stripslashes($pageVal['fb_pages']['name']);
							
							$page[$name]= $name;
						}
							$page[' '] = 'None';
					}
					else
					{
							$page[' '] = 'None';
					}
					$this->set('page',$page);
					$this->set('inprocess',0);
				} // if(!empty($this->params['url']['type'])=="finish")
		
			}
		
		}
	 	else
		{
			// if session expire then get from cookie
			if($this->Session->check('fbs_key'))
			{
				$fkey =	$this->Session->read('fbs_key');
			}
			elseif($this->Cookie->valid('fbs_key'))
			{
				$key=$this->Cookie->read('fbs_key');
				$fkey  = $key['fbs_key'];	
			}
			else
			{
				$band  =$this->Cookie->read('flag'); // myspace called from wizard or setting manage
				$flag= $band["flag"];
				if($flag=='b')
				{
					$this->redirect('/band/facebook/');
				}
				else
				{
					$this->redirect('/band/manage/');
				}
				exit;
			}
			
			$base=$this->base;
			$host = $_SERVER['HTTP_HOST'];
			
			$url = "http://$host$base/users/logoutfbs/";
			$furl = "http://www.facebook.com/logout.php?app_key=f83446549e7fc01a7240acb7d6e8b938&session_key=$fkey&next=$url";
			$this->redirect($furl);
			exit;
		}
		
	}
	
	/* name :  process
	  description : logout & delete cookies before facebook parser process
	  called : when user click process & close button during facebook registration
	*/
	function process(){
		
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
			
		$this->redirect('/fbs/index/');
		exit;

	}
	
	function fbswelcome(){
		$this->layout = "wizard";
		$band  =$this->Cookie->read('flag'); // called from wizard or setting manage
		$flag= $band["flag"];
		$this->set('flag',$flag);
	}
	
	/*
	 name : updateuser
	 description :	update / activate specific facebook user .
	 called :	called from band/manage 
	*/
	function updateuser()
	{
		if($this->data)
		{
			
			$mmm_id = $this->Session->read('id') ;
			if($this->Cookie->valid('bandid'))
			{
			
				$id = $this->data['Fb']['user'];
				if($id!="none")
				{
					$page = addslashes($this->data['Fb']['page']);
					$group = addslashes($this->data['Fb']['group']);
				}
			
				$band_id=$this->Cookie->read('bandid');
				$band_id = $band_id['bandid'];
				
				$qry = " update fb_login
					 set status=0 
					 where mmm_id='$mmm_id'
					 and band_id  = $band_id
					 and status=1";
				
				
				$this->Fb->query($qry);
				
								
				if($id!=="none")
				{
				$qry = " update fb_login f
					 set f.status=1 ,
					     f.page = '$page' ,
					     f.group = '$group' 
					 where f.mmm_id='$mmm_id'
					 and f.band_id  = $band_id
					 and f.login_id=$id";
				
				$this->Fb->query($qry);
				}
				
				
				$this->Session->setFlash('Facebook user, page and group updated successfully.');
				$this->redirect('/band/manage/');
				exit();
				
			} // if($this->Cookie->valid('bandid'))
			else
			{
				$this->Session->setFlash('Please select a band.');
				$this->redirect('/band/index/');
				exit;
				
			} // if($this->Cookie->valid('bandid'))
		} // if($this->data)
		else
		{
				$this->Session->setFlash('Invalid data.');
				$this->redirect('/band/manage/');
				exit;
				
		} // if($this->data)	
	}
	
	/*
	 name : chart
	 set : get and set data for facebook pages & group
	 */
	function chart()
	{
		/*
			get and set light box & graph width & height
		*/
		if(!empty($this->params['url']['width']) and !empty($this->params['url']['height']))
		{
			$width 	= $this->params['url']['width'] ;
			$height = $this->params['url']['height'] ;
		}
		else
		{
			$width = 600;
			$height=400;
		}
		
		$this->set('cwidth',$width);
		$this->set('cheight',$height);
		
		$this->set('width',round($width/1.25));
		$this->set('height',round($height/1.5));
		
		// 	End  get and set light box & graph width & height
	
	
		
		if(!empty($this->params['url']['id']))
		{
			$this->Session->write('login_id',$this->params['url']['id']);
		}
		
		if(!empty($this->params['url']['bandid']))
		{
			$this->Session->write('band_id',$this->params['url']['bandid']);
			
		}
		/*
			if in case chart call within the chart
			else in case of chart first time call without dates then default date & option applied 
		*/
		if(!empty($this->params['url']['date']) and !empty($this->params['url']['opt']))
		{
			$this->set('flag',0);
			if($this->Session->check('login_id'))
			{
			
				$lastdate = $this->params['url']['date'];
				$opt = $this->params['url']['opt'];
				$id=trim($this->Session->read('login_id'));
				
				
				
				if($this->Session->check('pname'))
				{
					$pname = $this->Session->read('pname');
					if($pname)
					{
						$this->Session->write('pname',$pname);
						$this->set('pageflag',1);
						
						$this->getGraphData($id , $lastdate , $opt , 'fan_count' , addslashes($pname) , 'page' ); // Get & set fbs pages data for graphs , params( id , date , option , field , name , type)
						
						if($this->params['url']['type']=='page')
						{
							$this->Session->write('type','page');
							$this->set('type','page');
						}
					}
				}
				else
				{
					$this->set('pageflag',0);
					$fbpage = NULL;
					$this->Session->write('fbpage',$fbpage);
				}
				
				
				if($this->Session->check('gname'))
				{
					$gname = $this->Session->read('gname');
					
					if($gname)
					{
						$this->Session->write('gname',$gname);
						$this->set('groupflag',1);
						$this->getGraphData($id,$lastdate,$opt,'member',addslashes($gname),'group'); // Get & set fbs group data for graphs , params( id , date , option , field , name , type)
						if($this->params['url']['type']=='group')
						{
							$this->Session->write('type','group');
							$this->set('type','group');
						}
					}
				}
				else
				{
					$this->set('groupflag',0);
					$fbgroup = NULL;
					$this->Session->write('fbgroup',$fbgroup);
					
				}
			
				
				
			} // if($this->Session->check('login_id'))
			
		} // if(!empty($this->params['url']['date']) and !empty($this->params['url']['opt']))
		else   
		{
			$this->set('flag',0);
			if($this->Session->check('login_id'))
			{
				$mmm_id = $this->Session->read('id');
				$login_id = $this->Session->read('login_id');
				$results = $this->Fb->find(array('mmm_id'=>$mmm_id,'login_id'=>$login_id)); //
				if($results)
				{
					
					$id = $results['Fb']['login_id'];
					$user_id = $results['Fb']['user_id'];
					$pname= $results['Fb']['page'];
					$gname= $results['Fb']['group'];
					$this->Session->write('fbuserid',$user_id); // to get myspace home page
					$this->Session->write('login_id',$id); // to get myspace home page
					
					if($pname)
					{
						$this->Session->write('pname',$pname);
						$this->set('pageflag',1);
						$this->getGraphData($id,'w','d','fan_count',addslashes($pname),'page'); // Get & set fbs pages data for graphs , params( id , date , option , field , name , type)
					}
					else
					{
						$this->set('pageflag',0);
					}
					
					if($gname)
					{
						$this->set('groupflag',1);
						$this->Session->write('gname',$gname);
						$this->getGraphData($id,'w','d','member',addslashes($gname),'group'); // Get & set fbs group data for graphs , params( id , date , option , field , name , type)
					}
					else
					{
						$this->set('groupflag',0);
					}
					
					
					$this->Session->write('type','page');   // by default channel views tab selected
					
					if(!$pname && !$gname)
					{
						if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
							$this->Session->setFlash('No Facebook data found.');
							$this->set('flag',1);
					}
	
				} // if($results)
				else
				{
					if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
							$this->Session->setFlash('No Facebook data found.');
							$this->set('flag',1);
					
				} // if($results)
				
			} //if($this->Session->check('band_id'))
			else
			{
				if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
							$this->Session->setFlash('Session expired.');
							$this->set('flag',1);
			} //if($this->Session->check('band_id'))
			
				
		}
			
		$this->layout="stats";
	} // function chart()


	/*
	 * Name: getGraphData
	 * Desc : To get Facebook pages and groups data 
	 */
	function getGraphData($id , $lastdate , $opt , $field , $name, $type)
	{
	
		if(!empty($id))
		{
			$mmm_id = $this->Session->read('id') ;
			
			if($lastdate=='m')
			{
				if($type=='page')
				{
					if($opt=='d')
					{	
						$qry_fb = "select p.$field , p.etime from fb_pages p where p.login_id=$id and p.name='$name' and FROM_UNIXTIME(p.etime) between DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 DAY) AND CURDATE( ) order by etime";
					}
					else
					{
						$qry_fb = "select p.$field , p.etime from fb_pages p  where p.login_id=$id and p.name='$name' and FROM_UNIXTIME(p.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";
					}
				}
				

				if($type=='group')
				{
					if($opt=='d')
					{
						$qry_fb = "select p.$field , p.etime from fb_group p  where p.login_id=$id and p.name='$name'  and FROM_UNIXTIME(p.etime) between DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 DAY) AND CURDATE( ) order by etime";
					}
					else
					{
						$qry_fb = "select p.$field , p.etime from fb_group p where p.login_id=$id and p.name='$name' and FROM_UNIXTIME(p.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";		
					}
				}

			
			$fb = $this->Fbpage->findBySql($qry_fb);


			$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE() curr";
			$tfdate = $this->Fbpage->findBySql($qry);

			$dt =NULL;
			$date = NULL;

			
			$c=0;
			$cdate= $tfdate['0']['0']['curr'];
			$curdate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($cdate)) . " -1 day"));
			$d=$tfdate['0']['0']['last'];
			$dt[$c]=date('Y-m-d',strtotime(date("Y-m-d", strtotime($d))));

				while($curdate!=$date)
				{
					$date = date('Y-m-d',strtotime(date("Y-m-d", strtotime($dt[$c])) . " +1 day"));
					$c ++;
					$dt[$c]= $date;

				} // while($tfdate['0']['0']['curr']!=$date)


			} // if($lastdate=='m')
			elseif($lastdate=='y')
			{
			
				if($type=='page')
				{			
					$qry_fb = "select p.$field, p.etime from fb_pages p  where p.login_id=$id and p.name='$name' and FROM_UNIXTIME(p.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";
					
				}
				

				if($type=='group')
				{			
					$qry_fb = "select p.$field , p.etime from fb_group p  where p.login_id=$id and p.name='$name' and FROM_UNIXTIME(p.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";
				}

				$fb = $this->Fbpage->findBySql($qry_fb);
				$fb = $this->getyear($fb,'p',$field);

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, CURDATE() curr";
				$tfdate = $this->Fbpage->findBySql($qry);

				$dt =NULL;
				$date = NULL;
				$c=0;
				$td= $tfdate['0']['0']['curr'];
				$td=date('M',strtotime(date("Y-m-d", strtotime($td))));


				$d=$tfdate['0']['0']['last'];
				$edate=date('Y-m-d',strtotime(date("Y-m-d", strtotime($d)). " +1 Month"));
				$dt[$c]=date('M',strtotime(date("Y-m-d", strtotime($d)). " +1 Month"));

				while($td!=$date)
				{
					$date = date('M',strtotime(date("Y-m-d", strtotime($edate)) . " +1 Month"));

					$c ++;
					$dt[$c]= $date;
					$edate=date('Y-m-d',strtotime(date("Y-m-d", strtotime($edate)). " +1 Month"));
					if($c>12)
					{
					 break;
					} // 			if($c>12)
				} // while($tfdate['0']['0']['curr']!=$date)

			} // if($lastdate=='m')
			else   // if($lastdate=='w') or no date selected
			{

				if($type=='page')
				{			
					if($opt=='d')
					{		
						$qry_fb = "select p.$field , p.etime from fb_pages p  where p.login_id=$id and p.name='$name' and FROM_UNIXTIME(p.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";
					}
					else
					{
						$qry_fb = "select p.$field , p.etime from fb_pages p  where p.login_id=$id and p.name='$name' and FROM_UNIXTIME(p.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";	
					}
				}
				

				if($type=='group')
				{
					if($opt=='d')
					{
						$qry_fb = "select p.$field , p.etime from fb_group p  where p.login_id=$id and p.name='$name' and FROM_UNIXTIME(p.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";
					}
					else
					{
						$qry_fb = "select p.$field , p.etime from fb_group p where p.login_id=$id and p.name='$name' and FROM_UNIXTIME(p.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";	
					}
				}

			$fb = $this->Fbpage->findBySql($qry_fb);
			$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, CURDATE() curr";
			$tfdate = $this->Fbpage->findBySql($qry);

			$dt =NULL;
			$date = NULL;
			$c=0;
			$dt[$c]=$tfdate['0']['0']['last'];

				while($tfdate['0']['0']['curr']!=$date)
				{
					
					$date = date('Y-m-d',strtotime(date("Y-m-d", strtotime($dt[$c])) . " +1 day"));
					$c ++;
					if($c==7)	 // i.e 0-6 last 7 days
					{
						break;
					}
					$dt[$c]= $date;

				} // while($tfdate['0']['0']['curr']!=$date)
			} // if($lastdate=='m')


			if(empty($fb))
			{	
				$fbcount=0;
			} // if(empty($mss))
			else
			{
				$fbcount = count($fb);		
			} // if(empty($mss))

			$fbhitscount=0;
			$cnt=0;
			$predate=0;
			
				
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				if($lastdate=='y')
				{
						for($i=0 ; $i < $fbcount ; $i++)  // set Myspace views according to main loop date
						{
							if($opt=='c')
							{
								if($fb[$i]['p']['etime']==$val)
								{
									
									$fbhits[$cnt]= $fb[$i]['p'][$field] ;
									$fbhitscount+=$fbhits[$cnt];
							
									$flg=1;
									$cnt++;
									break;
								} //	if($mss_data[$i]['s']['etime'])==$val)
							}
							elseif($opt=='d')
							{
								if($fb[$i]['p']['etime']==$val)
								{
									if($i!=0)
									{
										$fbhits[$cnt]= $fb[$i]['p'][$field]-$fb[$i-1]['p'][$field] ;
										$fbhitscount += $fbhits[$cnt] ;
									}
									else
									{
										$fbhits[$cnt]= $fb[$i]['p'][$field];
										
									}
										$flg=1;
										$cnt++;
										break;
								}
								
							}
						} // for($i=0 ; $i < $mscount-1 ; $i++)

						if($flg==0)
						{
							$fbhits[$cnt]=0;
							$cnt++;

						}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $fbcount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$fb[$i]['p']['etime'])==$val)
						{
							if($opt=='c')
							{
								if(date('Y-m-d',$fb[$i]['p']['etime'])!=$predate)
								{
									
									$predate = date('Y-m-d',$fb[$i]['p'][$field]);
	
										$fbhits[$cnt]= $fb[$i]['p'][$field];
										$fbhitscount += $fbhits[$cnt] ;
									
									$flg=1;
									$cnt++;
								}
								break;
							}
							elseif($opt=='d')
							{
								if($i!=0)
								{
									$fbhits[$cnt]= $fb[$i]['p'][$field]-$fb[$i-1]['p'][$field] ;
									$fbhitscount += $fbhits[$cnt] ;
								}
								else
								{
									$fbhits[$cnt]=0;
								}
								$flg=1;
								$cnt++;
								break;
							}

						} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $mscount-1 ; $i++)

					if($flg==0)
					{
						$fbhits[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
				
				if($fbhits[$cnt-1]==0)
				{
					$percentage = 0;
					$wpercentage = 0;
				}
				else
				{
					$percentage = round((($fbhits[$cnt-1]-$fbhits[$cnt-2])/$fbhits[$cnt-1])*100,2);
					$wpercentage = round((($fbhits[$cnt-1]-$fbhits[$cnt-7])/$fbhits[$cnt-1])*100,2);
				}
				$diff = $fbhits[$cnt-1]-$fbhits[$cnt-2];
				$wdiff = $fbhits[$cnt-1]-$fbhits[$cnt-7];
				
				
				if($type=='page')
				{
					$this->Session->write('fbpage',$fbhits);
					$this->Session->write('dt',$dt);
					
					$this->set('ppercentage',$percentage);
					$this->set('pdiff',$diff);
	
					$this->set('pwpercentage',$wpercentage);
					$this->set('pwdiff',$wdiff);
					$this->set('ptotal',$fbhits[$cnt-1]);
				}
				
				elseif($type=='group')
				{
					$this->Session->write('fbgroup',$fbhits);
					$this->Session->write('dt',$dt);
					
					$this->set('gpercentage',$percentage);
					$this->set('gdiff',$diff);
	
					$this->set('gwpercentage',$wpercentage);
					$this->set('gwdiff',$wdiff);
					$this->set('gtotal',$fbhits[$cnt-1]);
					
				}
		
				$this->Session->write('lastdate',$lastdate);
				$this->Session->write('opt',$opt);
				$this->Session->write('type',$type);
				$this->set('lastdate',$lastdate);
			
		} // if(!empty($id))
	

	} // function hits($id , $lastdate , $opt , $field , $name, $type)


	/*
	 * name : pagechart
	 * 	  get and set chart data for pages
	 */
	function pagechart()
	{
		
		$this->set('fbpage',$this->Session->read('fbpage'));
		$this->set('dt',$this->Session->read('dt'));
	}
	
	/*
	 * name : groupchart
	 * 	  get and set chart data for group
	 */
	function groupchart()
	{
		
		$this->set('fbgroup',$this->Session->read('fbgroup'));
		$this->set('dt',$this->Session->read('dt'));
	}
	
	
	/*
	 * name : getyear
	 * 	  convert days & data into months to make complete year 
	 */
	function getyear($mss,$type,$field)
	{
		$vms=NULL;
		for($i=0 ; $i<count($mss);$i++)
		{
			$vms[] = array($field=>$mss[$i][$type][$field],'time'=>$mss[$i][$type]['etime']);
					
		} //	for($i=0 ; $i<count($mss);$i)
			
			
		$j=0;
		$vdata[$j]=0;
		$views_data = NULL;
		$count = count($vms);
		$flag =0;
		
		
		if($vms)
		{
			if($count==1)
			{
				$vdata[$j]=$vms[0][$field];
				$ctd=date('M',strtotime(date("Y-m-d", $vms[0]['time'])));
				$views_data[][$type] = array($field=>$vdata[$j] , 'etime'=>$ctd);
				
			} // if(count==1)
			else
			{
				foreach($vms as $key => $val)
				{
		
		
					if($count-1!=$key)
					{
					 
						$ctd=date('M',strtotime(date("Y-m-d", $val['time'])));
						$ntd=date('M',strtotime(date("Y-m-d", $vms[$key+1]['time'])));
					}
					else
					{
						
						$vdata[$j]=$val[$field];
						break;
					}
		
					if($ctd==$ntd)
					{
		
						$vdata[$j]=$val[$field];
						
							
					} // if($ctd==$ntd)
					else
					{
		
						$vdata[$j]=$val[$field];
						
						$views_data[][$type] = array($field=>$vdata[$j] , 'etime'=>$ctd);
						$j++;
						$flag=1;
					}
		
				} // foreach($vms as $key => $val)
		
				
				
		
				if($flag==1)  // if more than 1 month data found
				{
					$views_data[][$type] = array($field=>$vdata[$j] , 'etime'=>$ctd);
		
				}
		
				if(empty($views_data)) // if only one month data found
				{
					$views_data[][$type] = array($field=>$vdata[$j] , 'etime'=>$ctd);
				} // if(empty($views_data))
			} //if(count==1)
	
			
			return $views_data;
		}
	} // function getviewyear()

}


/*
 Open Flash Chart functions to display flash chart on page
 */

function open_flash_chart_object_str( $width, $height, $url, $use_swfobject=true, $base='' )
{
	//
	// return the HTML as a string
	//
	return _ofc( $width, $height, $url, $use_swfobject, $base );
}

function open_flash_chart_object( $width, $height, $url, $use_swfobject=true, $base='' )
{
	//
	// stream the HTML into the page
	//
	echo _ofc( $width, $height, $url, $use_swfobject, $base );
}

function _ofc( $width, $height, $url, $use_swfobject, $base )
{
	//
	// I think we may use swfobject for all browsers,
	// not JUST for IE...
	//
	//$ie = strstr(getenv('HTTP_USER_AGENT'), 'MSIE');

	//
	// escape the & and stuff:
	//
	$url = urlencode($url);

	//
	// output buffer
	//
	$out = array();

	//
	// check for http or https:
	//
	if (isset ($_SERVER['HTTPS']))
	{
		if (strtoupper ($_SERVER['HTTPS']) == 'ON')
		{
			$protocol = 'https';
		}
		else
		{
			$protocol = 'http';
		}
	}
	else
	{
		$protocol = 'http';
	}

	//
	// if there are more than one charts on the
	// page, give each a different ID
	//
	global $open_flash_chart_seqno;
	$obj_id = 'chart';
	$div_name = 'flashcontent';

	//$out[] = '<script type="text/javascript" src="'. $base .'js/ofc.js"></script>';

	if( !isset( $open_flash_chart_seqno ) )
	{
		$open_flash_chart_seqno = 1;
		$out[] = '<script type="text/javascript" src="'. $base .'js/swfobject.js"></script>';
	}
	else
	{
		$open_flash_chart_seqno++;
		$obj_id .= '_'. $open_flash_chart_seqno;
		$div_name .= '_'. $open_flash_chart_seqno;
	}

	if( $use_swfobject )
	{
		// Using library for auto-enabling Flash object on IE, disabled-Javascript proof
		$out[] = '<div id="'. $div_name .'"></div>';
		$out[] = '<script type="text/javascript">';
		$out[] = 'var so = new SWFObject("'. $base .'open-flash-chart.swf", "'. $obj_id .'", "'. $width . '", "' . $height . '", "9", "#FFFFFF");';
		//$out[] = 'so.addVariable("width", "' . $width . '");';
		//$out[] = 'so.addVariable("height", "' . $height . '");';
		$out[] = 'so.addVariable("data", "'. $url . '");';
		$out[] = 'so.addParam("allowScriptAccess", "sameDomain");';
		$out[] = 'so.write("'. $div_name .'");';
		$out[] = '</script>';
		$out[] = '<noscript>';
	}

	$out[] = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="' . $protocol . '://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" ';
	$out[] = 'width="' . $width . '" height="' . $height . '" id="ie_'. $obj_id .'" align="middle">';
	$out[] = '<param name="allowScriptAccess" value="sameDomain" />';
	$out[] = '<param name="movie" value="'. $base .'open-flash-chart.swf?width='. $width .'&height='. $height . '&data='. $url .'" />';
	$out[] = '<param name="quality" value="high" />';
	$out[] = '<param name="bgcolor" value="#FFFFFF" />';
	$out[] = '<embed src="'. $base .'open-flash-chart.swf?data=' . $url .'" quality="high" bgcolor="#FFFFFF" width="'. $width .'" height="'. $height .'" name="'. $obj_id .'" align="middle" allowScriptAccess="sameDomain" ';
	$out[] = 'type="application/x-shockwave-flash" pluginspage="' . $protocol . '://www.macromedia.com/go/getflashplayer" id="'. $obj_id .'"/>';
	$out[] = '</object>';

	if ( $use_swfobject ) {
		$out[] = '</noscript>';
	}

	return implode("\n",$out);
}
?>
