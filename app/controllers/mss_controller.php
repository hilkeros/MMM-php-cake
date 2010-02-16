<?php
vendor('myspace/myspace.class');
vendor('myspace/source/MySpaceID/myspace');
vendor('myspace/config/config.MySpace');
define('LIB_PATH', "vendors/myspace/source/");
define('CONFIG_PATH',	"vendors/myspace/config/");
define('LOCAL', false);

class MssController extends AppController {

	var $name = 'Mss';
	var $time = NULL;
	var $uses = array('User','Mss','Mssstat','Msscomm','Msslogin','Band');
	var $helpers = array('Html', 'Error', 'Javascript', 'FlashChart');
	var $components = array('Cookie'); //  use component email
	var $developerKey , $applicationId , $clientId , $username ;
	var $consumer_key ="c87b1a3237324b5eaed23749e9ebff37";
	var $consumer_secret = "46aed6a612744b80aab0c19cbb488140ee2bea3a7add45f982ec3765bc48c76e";
	
	/**
	 * Name: beforeFilter
	 * Desc: Performs necessary steps and function calls prior to executing
	 *       any view function calls.
	 */
	function beforeFilter() {
		$this->auth();
	} // function beforeFilter() {

	/* Name: index
	 * Desc: Myspace profile selection box & Profile Summary.
	 * set: get & set Profile Summary & Views
	*/
	function index()
	{
		$this->redirect('/dashboard/index/');
	} // 	function index()

	
	/*
	 * name : updateStatus
	 * description : update MySpace profile status .
	 * called : called from dashboard
	 */
	function updateStatus()
	{
		
		if($this->data)
		{
			if(!empty($this->data['Dashboard']['mss']))
			{
				$mmm_id = $this->Session->read('id');
				$band_id = $this->data['Dashboard']['bandid'];
			
				$result= $this->Msslogin->find(array('mmm_id'=>$mmm_id,'band_id'=>$band_id,'status'=>1,'link'=>'1',array('not'=>array('access_token'=>null)),array('not'=>array('access_secret'=>null))));
				if($result)
				{
			
					$ms_key = CONSUMER_KEY;			//we get this from config.MySpace.php
					$ms_secret = CONSUMER_SECRET;
				
					$token = $result['Msslogin']['access_token'];
					$secret = $result['Msslogin']['access_secret'];
					
					
					//$oauth_verifier = $result['Msslogin']['oauth_verifier'];
					/*
					$ms = new MySpace($ms_key, $ms_secret, $token, $secret, true, $oauth_verifier);
					$tok = $ms->getAccessToken();
		
					if (!is_string($tok->key) || !is_string($tok->secret))
					{
						error_log("Bad token from MySpace::getAccessToken(): ".var_export($tok, TRUE));
						echo "Invalid response.";
						exit;
					}
					*/
					$text = $this->data['Dashboard']['status'];
					$ms = new MySpace($ms_key, $ms_secret,  $token, $secret);
					$userid = $ms->getCurrentUserIds();
					$ms->updateStatus($userid, $text);
					echo "MySpace status updated succesfully";
					exit;
				}
				else
				{
					echo "We need your permission to publish a status update to your MySpace profile. Go to the settings page and click Add new MySpace profile link.";
					exit;
				}
			}
			
		}
		exit;
		
	}
	
	/*
	 * name : login
	 * description : redirect to get login screen & allow permission
	 */
	 
	function login()
	{
		$this->redirect('/mss/connect/?f=start');
		exit;
		
	}
	
	
	/*
	 * name : connect
	 * description : connect with MySpace Application and get authentication
	 *
	 */
	 
	function connect()
	{
		$ms_key = CONSUMER_KEY;			//we get this from config.MySpace.php
		$ms_secret = CONSUMER_SECRET;

		if(!empty($this->params['url']['f']))
		{
			if ($this->params['url']['f']== 'start')
			{
				// get a request token + secret from MySpace and redirect to the authorization page
				$ms = new MySpace($ms_key, $ms_secret);
				$host = $_SERVER['HTTP_HOST'].$this->base;
				$tok = $ms->getRequestToken("http://$host/mss/connect/?f=callback");
		
				if (!isset($tok['oauth_token'])
				    || !is_string($tok['oauth_token'])
				    || !isset($tok['oauth_token_secret'])
				    || !is_string($tok['oauth_token_secret']))
				{
					$this->Session->setFlash('Invalid response.');
					$this->redirect('/band/manage/');
					exit;
				}
				
				$this->Session->write('auth_state','start');
				$this->Session->write('request_token',$tok['oauth_token']);
				$this->Session->write('request_secret',$tok['oauth_token_secret']);
				$this->Session->write('callback_confirmed',$tok['oauth_callback_confirmed']);
	
				header("Location: ".$ms->getAuthorizeURL($tok['oauth_token']));
				exit;
			}
			else if (@$_GET['f'] == 'callback')
			{
				
				// the user has authorized us at MySpace, so now we can pick up our access token + secret
				if($this->Session->check('auth_state'))
				{
					if($this->Session->read('auth_state')!='start')
					{
						$this->Session->setFlash('Invalid call.');
						$this->redirect('/band/manage/');
						exit;
						
					}
				} // if($this->Session->check('auth_state'))
				
				// the user has authorized us at MySpace, so now we can pick up our access token + secret
				if(!empty($this->params['url']['oauth_problem']))
				{
					if($this->params['url']['oauth_problem']=='user_refused')
					{
						$this->redirect('/band/manage/');
						exit;
					}
					else{
						$this->Session->setFlash('Authorization problem.');
						$this->redirect('/band/manage/');
						exit;
					}
				} // if($this->Session->check('auth_state'))
				
				/*
				Get Band Id from Session or Cookie
			       */
				if($this->Session->check('band_id'))
				{
					$band_id = $this->Session->read('band_id') ;
				}
				elseif($this->Cookie->valid('bandid'))
				{
				
					
					$band  =$this->Cookie->read('bandid'); 
					$band_id = $band['bandid']; // band_id value get from cookie
				}
				else
				{
					$this->Session->setFlash('Please select a band.');
					$this->redirect('/band/index/');
					exit;
				}
			 
				$oauth_verifier = $this->params['url']['oauth_verifier'];
		  		if(!isset($oauth_verifier))
				{
					$this->Session->setFlash('Invalid response.');
					$this->redirect('/band/manage/');
					exit;
				}
		  
				$ms = new MySpace($ms_key, $ms_secret, $this->Session->read('request_token'), $this->Session->read('request_secret'), true, $oauth_verifier);
				$tok = $ms->getAccessToken();
	
				if (!is_string($tok->key) || !is_string($tok->secret))
				{
					error_log("Bad token from MySpace::getAccessToken(): ".var_export($tok, TRUE));
					$this->Session->setFlash('Invalid response.');
					$this->redirect('/band/manage/');
					exit;
				}
	
				$ms = new MySpace($ms_key, $ms_secret,  $tok->key, $tok->secret);
				$webUri = $ms->getCurrentUserId();
				// get user id from webUri
				$user_id = substr($webUri , strrpos($webUri,"/")+1,strlen($webUri));
				
				$result = $this->Msslogin->find(array('user_id'=>$user_id,'band_id'=>$band_id));
				if($result)
				{
					$result['Msslogin']['access_token']= $tok->key;
					$result['Msslogin']['access_secret']=$tok->secret;
					$result['Msslogin']['oauth_token']=$this->Session->read('request_token');
					$result['Msslogin']['oauth_token_secret']=$this->Session->read('request_secret');
					$result['Msslogin']['link']= '1';
					
					$this->Msslogin->save($result);
					$this->Session->setFlash('MySpace update status permission allowed.');
					
					$band  =$this->Cookie->read('flag'); // myspace called from wizard or setting manage
					$flag= $band["flag"];
					
					if($flag=='b')
					{
						$this->redirect('/band/myspace/');
					}
					else
					{
						$this->redirect('/band/manage/');
					}
					exit;
				
					
				}
				else
				{
					// return to band manager or band wizard page
					$this->addprofile($user_id , $tok->key , $tok->secret,$band_id) ;
				}
			}
		} // if(!empty($this->params['url']['f'])
		else
		{
			$this->Session->setFlash('Invalid response.');
			$this->redirect('/band/manage/');
			exit;	
		}
	
		$this->Session->setFlash('Invalid response.');
		$this->redirect('/band/manage/');
		exit;
	}
	
	/* Name: updateprofile
	 Desc: active given profile
	 Return : void
	 
	 */
	function updateprofile()
	{
	
		if($this->data)
		{
					
			$mmm_id = $this->Session->read('id') ;
			/*
				Get Band Id from Session or Cookie
			       */
			       if($this->Session->check('band_id'))
			       {
				       $band_id = $this->Session->read('band_id') ;
			       }
			       elseif($this->Cookie->valid('bandid'))
			       {
			       
				       
				       $band  =$this->Cookie->read('bandid'); 
				       $band_id = $band['bandid']; // band_id value get from cookie
			       }
			       else
			       {
				       $this->Session->setFlash('Please select a band.');
				       $this->redirect('/band/index/');
				       exit;
			       }
			       
			
			
				$id = $this->data['Msslogin']['profile'];
				
				$band_id=$this->Cookie->read('bandid');
				$band_id = $band_id['bandid'];
				
				$qry = " update mss_login
					 set status=0
					 where mmm_id='$mmm_id'
					 and band_id  = $band_id ";
				$this->Msslogin->query($qry);
				
								
				if($id!="none")
				{
				$qry = " update mss_login
					 set status=1
					 where mmm_id='$mmm_id'
					 and band_id  = $band_id
					 and mss_id=$id";
					 $this->Msslogin->query($qry);
				}
				
				$this->Session->setFlash('Profile updated successfully.');
				$this->redirect('/band/manage/');
				exit;
			
		} // if($this->data)
		else
		{
				$this->Session->setFlash('Invalid data.');
				$this->redirect('/band/manage/');
				exit;
				
		} // if($this->data)
		
	}
	
	/* Name: addprofile
	 Desc: Get Myspace Profile Statistics
	 Return : void
	 set : get and insert all MySpace user statistics with getStat function
	 */
	function addprofile($user_id , $key , $secret , $band_id)
	{

		$band  =$this->Cookie->read('flag'); // myspace called from wizard or setting manage
		$flag= $band["flag"];

			
		if(!empty($user_id))
		{
			$mmm_id = $this->Session->read('id');
				
			$record['Msslogin']['status']	='1';
			$record['Msslogin']['link']	='1';
			$record['Msslogin']['active']	='1';
			$record['Msslogin']['mmm_id']	=$mmm_id ;
			$record['Msslogin']['user_id']	=$user_id ;
			$record['Msslogin']['band_id']	=$band_id ;
			$record['Msslogin']['access_token']= $key;
			$record['Msslogin']['access_secret']=$secret;
			
			/*		
				$pos=strrpos($this->data['Msslogin']['user_id'],"=");
				if(empty($pos))
				{
					$pos=strrpos($this->data['Msslogin']['user_id'],"/");
					
					if(empty($pos))
					{
						
							$profileid = $this->data['Msslogin']['user_id'] ;	
						
					} // if($empty($pos))
					else
					{
						$profileid = substr($this->data['Msslogin']['user_id'],$pos+1,strlen($this->data['Msslogin']['user_id']));
					} // if($empty($pos))
				}
				else
				{
					$profileid = substr($this->data['Msslogin']['user_id'],strrpos($this->data['Msslogin']['user_id'],"=")+1,strlen($this->data['Msslogin']['user_id']));
				}
				*/
			
			$result = $this->Msslogin->find(array('mmm_id' => $mmm_id , 'user_id' => $user_id,'band_id'=>$band_id));
			if(!$result)
			{
					
					// all myspace user status set to 0
					$qry = " update mss_login
							set status=0
							where mmm_id='$mmm_id'
							and band_id  = $band_id ";
					 
					$this->Msslogin->query($qry);
										
					$MssResult= $this->getStat($record , $band_id);
					if($MssResult)
					{
						$this->Session->setFlash('Myspace information has been processed correctly.');
						if($flag=='b')
						{
							
							$this->redirect('/band/facebook/');
							
						}	
						else
						{	
							$this->redirect('/band/manage/');
							
						}
						
					} // if($this->Cms->save($this->data))
					else
					{
						$this->Session->setFlash('Invalid url.');
						if($flag=='b')
						{
							
							$this->redirect('/band/myspace/');
						}	
						else
						{	
							$this->redirect('/band/manage/');
						}
						
					}
					exit;
	
			} // 	if(!$record)
			else
			{
					
					if($result['Msslogin']['active']==0)
					{
						$record['Msslogin']['active'] = 1;
						$this->Msslogin->save($record);
						$this->Session->setFlash('Myspace information has been processed correctly.');
					}
					else
					{
						$this->Session->setFlash('The data about this artist are already present in our database.');
					}
					
					if($flag=='b')
					{
						$this->redirect('/band/myspace/');
					}
					else
					{
						$this->redirect('/band/manage/');
					}
					exit;
			} // 	if(!$record)
			
			

		} // if($this->data)
		else
		{
			$this->Session->setFlash('Invalid parameters.');
			if($flag=='b')
			{
				$this->redirect('/band/myspace/');
			}
			else
			{
				$this->redirect('/band/manage/');
			}
			exit;
			
		}// if($this->data)
	} // 	function addprofile()
	
	
	/*
	 * name : uninstall
	 * description : when user remove application from his profile then link status set = 0
	 *
	 */
	function uninstall()
	{
		if(!empty($this->params['url']['opensocial_owner_id']))
		{
			$user_id = $this->params['url']['opensocial_owner_id'];
			$result = $this->Msslogin->find(array('user_id'=>$user_id));
			if($result)
			{
				$result['Msslogin']['link']=0;
				$this->Msslogin->save($result);
				echo "Successfully unlink";
			}
		}
		exit;
	}
	
	/*
	 name : chart
	 set : get and set data for profile views , friends , comments & plays
	 */
	function chart()
	{
		//	get and set light box & graph width & height

		if(!empty($this->params['url']['width']) and !empty($this->params['url']['height']))
		{
			$width 	= $this->params['url']['width'] ;
			$height = $this->params['url']['height'] ;
		}
		else
		{
			$width 	= 600;
			$height	= 400;
		}
		
		$this->set('cwidth',$width);
		$this->set('cheight',$height);
		
		$this->set('width',round($width/1.25));
		$this->set('height',round($height/1.5));
		
		//	End  get and set light box & graph width & height  
	
	
		
		if(!empty($this->params['url']['id']))
		{
			$this->Session->write('mssid',$this->params['url']['id']);
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
			if($this->Session->check('mssid'))
			{
				
				$lastdate = $this->params['url']['date'];
				$opt = $this->params['url']['opt'];
				$id=trim($this->Session->read('mssid'));
				
				
				/*
					get graph data for views
				*/
				$this->getGraphData($id,$lastdate,$opt,'views'); // Get & set profile views data for graphs , params( id , date , option , field)
				if($this->params['url']['type']=='views')
				{
					$this->Session->write('type','views');
				}
				
				
				/*
					get graph data for friends
				*/
				
				$this->getGraphData($id,$lastdate,$opt,'friends'); // Get & set friends data for graphs , params( id , date , option , field)
				if($this->params['url']['type']=='friends')
				{
					$this->Session->write('type','friends');
				}
				
				/*
					get graph data for comments
				*/
					
				$clastdate=$this->getGraphData($id,$lastdate,$opt,'comments'); // Get & set comments data for graphs , params( id , date , option , field)
				if($this->params['url']['type']=='comments')
				{
					$this->Session->write('type','comments');
				}
				
				/*
				 *	get graph data for plays
				 */
					
				$mmm_id = $this->Session->read('id');
				if(@$this->params['url']['ids'])
				{
					$mss_plids = $this->params['url']['ids'];
					$titles = $this->params['url']['title'];
					$color = $this->params['url']['color'];
					
				}
				else
				{
					$mss_plids = "0";
				}
					
				$this->set('mss_plids',$mss_plids);
						
				$plays_mss = $this->Session->read('plays_mss');
				$this->set('plays_mss',$plays_mss);
				$this->set('id',$id);
				
				$totalplays 	= NULL;
				$msplays 	= NULL;
				
				$totaldownloads = NULL;
				$msdownloads 	= NULL;
				
				
				foreach($plays_mss as $key => $val)
				{
					$mss_plid= $val['s']['mss_plid'];
					$title= $val['s']['title'];
					
					if($mss_plids=='all')
					{	
						$_SESSION[$title]="1";
					}
					elseif($mss_plids=='dall')
					{
						$_SESSION["d".$title]="1";
					}

						
					$msplays[$title] = $this->msplays($id,$lastdate,$opt,'plays',addslashes($title));
					$msdownloads[$title] = $this->msplays($id,$lastdate,$opt,'downloads',addslashes($title));
					
					foreach($msplays[$title] as $mskey => $msval)
					{	
						@$totalplays[$mskey]+= $msval;	
					}
					
					foreach($msdownloads[$title] as $mskey => $msval)
					{	
						@$totaldownloads[$mskey]+= $msval;	
					}
				}
				
				
				
				$msplays['tplay'] = $totalplays ;
				$this->set('msplays',$msplays);
				$this->Session->write('msplays',$msplays);
				
				
				
				$msdownloads['tdownloads'] = $totaldownloads ;
				$this->set('msdownloads',$msdownloads);
				$this->Session->write('msdownloads',$msdownloads);
								
						
				if($mss_plids=='all' || $mss_plids=='dall')
				{
					
					if($this->params['url']['type']=='plays')
					{
						$this->Session->write('type','plays');
						$this->Session->write('tplay',"1");
					} // if($this->params['url']['type']=='plays')
					elseif($this->params['url']['type']=='downloads')
					{
						$this->Session->write('type','downloads');
						$this->Session->write('dtdownloads',"1");
					} // if($this->params['url']['type']=='plays')
											
				}
				elseif($mss_plids=='none'  || $mss_plids=='dnone')
				{
					
					$totalplays 	= NULL;
					$msplays 	= NULL;
				
					$totaldownloads = NULL;
					$msdownloads 	= NULL;
																		
					foreach($plays_mss as $key => $val)	// All plays toggle off
					{
						
						$title= $val['s']['title'];
						if($this->params['url']['type']=='plays')
						{
						$_SESSION[$title]	="0";
						}
						elseif($this->params['url']['type']=='downloads')
						{
						$_SESSION["d".$title]	="0";
						}
						

					} // foreach($plays_mss as $key => $val)
					
					
					$this->Session->write('lastdate',$lastdate);
					$this->Session->write('opt',$opt);
				
										
					if($this->params['url']['type']=='plays')
					{
						$this->set('msplays',$msplays);
						$this->Session->write('msplays',$msplays);
						$this->Session->write('tplay',"0");  // Total plays toggle off
						$this->Session->write('type','plays');
					} // if($this->params['url']['type']=='plays')
					elseif($this->params['url']['type']=='downloads')
					{
						$this->set('msdownloads',$msdownloads);
						$this->Session->write('msdownloads',$msdownloads);
						$this->Session->write('dtdownloads',"0");  // Total downloads toggle off
						$this->Session->write('type','downloads');
					} // if($this->params['url']['type']=='plays')
					
				}
				elseif($mss_plids=='tplay' || $mss_plids=='tdownloads')
				{
								
					if($this->params['url']['type']=='plays')
					{
						$this->Session->write('type','plays');
						if($this->Session->read('tplay')=='1')
						{
							$this->Session->write('tplay','0');
						}
						else
						{
							$this->Session->write('tplay','1');
						}
					} // if($this->params['url']['type']=='plays')
					elseif($this->params['url']['type']=='downloads')
					{
						
						$this->Session->write('type','downloads');
						if($this->Session->read('dtdownloads')=='1')
						{
							$this->Session->write('dtdownloads','0');
						}
						else
						{
							$this->Session->write('dtdownloads','1');
						}
					} // if($this->params['url']['type']=='plays')
					
					
				}
				elseif($mss_plids=="0")
				{
					
					if($this->params['url']['type']=='plays')
					{
						$this->Session->write('type','plays');
					}
					elseif($this->params['url']['type']=='downloads')
					{
						$this->Session->write('type','downloads');
					}
							
					
				}
				elseif(!empty($mss_plids))
				{
					
					if($this->params['url']['type']=='plays')
					{
						$this->Session->write('type','plays');
						$this->Session->write('color',$color);
						
						if($_SESSION[$titles]=='1')
						{
							$_SESSION[$titles]='0';
						}
						else
						{
							$_SESSION[$titles]='1';
						}
					} // if($this->params['url']['type']=='plays')
					elseif($this->params['url']['type']=='downloads')
					{
						$this->Session->write('type','downloads');
						$this->Session->write('color',$color);
						
						if($_SESSION["d".$titles]=='1')
						{
							$_SESSION["d".$titles]='0';
						}
						else
						{
							$_SESSION["d".$titles]='1';
						}
					} // if($this->params['url']['type']=='plays')
					
					
				}
			} // if($this->Session->check('mssid'))
			
		} // if(!empty($this->params['url']['date']) and !empty($this->params['url']['opt']))
		else   
		{
			$this->set('flag',0);
			if($this->Session->check('mssid'))
			{
				$mmm_id = $this->Session->read('id');
				$mss_id = $this->Session->read('mssid');
				
				$results = $this->Msslogin->find(array('mmm_id'=>$mmm_id,'mss_id'=>$mss_id)); //
				if($results)
				{
					
					$id = $results['Msslogin']['mss_id'];
					$user_id = $results['Msslogin']['user_id'];
					$this->Session->write('mssuserid',$user_id); // to get myspace home page
					
								
					$lastdate=$this->getGraphData($id,'w','d','views'); // Get & set profile views data for graphs , params( id , date , option , field)
					$flastdate=$this->getGraphData($id,'w','d','friends'); // Get & set friends data for graphs , params( id , date , option , field)
					$clastdate=$this->getGraphData($id,'w','d','comments'); // Get & set comments data for graphs , params( id , date , option , field)
				
					$this->Session->write('type','views');
					
	
				} // if($results)
				else
				{
					if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
					$this->Session->setFlash('No MySpace data found.');
					$this->set('flag',1);
					
				} // if($results)
				
				
				
				
				$this->set('mss_plids','all');
				
				$qry = "select s.mss_plid , s.title from mss_play_stat s , mss_login l
								where
								s.mss_id = $id and s.etime = l.etime and s.mss_id = l.mss_id and l.mmm_id='$mmm_id'
								order by s.plays desc
								limit 5
								";
			
				$plays_mss = $this->Mssstat->findBySql($qry);

				
				if($plays_mss)
				{
					$this->Session->write('plays_mss',$plays_mss);
					$this->set('plays_mss',$plays_mss);
					
					$this->Session->write('color','#FF6600');
					$this->set('id',$id);
					
					$msplays 	= NULL;
					$msdownloads 	= NULL;
					
					$this->Session->write('tplay',"1");  //  total plays toggle on
					$this->Session->write('dtdownloads',"1");  //  total downloads toggle on
					
					foreach($plays_mss as $key => $val)
					{
						$mss_plid= $val['s']['mss_plid'];
						$title= $val['s']['title'];
						
											
						$msplays[$title] = $this->msplays($id,'w','d','plays',addslashes($title));
						$msdownloads[$title] = $this->msplays($id,'w','d','downloads',addslashes($title));
						
						
						$_SESSION[$title]	="1"; // All plays toggle on
						$_SESSION["d".$title]	="1"; // All downloads toggle on
						
						
						// get & set total plays statistics
						foreach($msplays[$title] as $mskey => $msval)
						{	
							@$totalplays[$mskey]+= $msval;	
						} // foreach($msplays[$title] as $mskey => $msval)
						
						// get & set total plays statistics
						foreach($msdownloads[$title] as $mskey => $msval)
						{	
							@$totaldownloads[$mskey]+= $msval;	
						} // foreach($msplays[$title] as $mskey => $msval)
						
					} // foreach($plays_mss as $key => $val)
						
						
						
						$msplays['tplay'] = $totalplays ;
						$this->set('msplays',$msplays);
						$this->Session->write('msplays',$msplays);
						
						
						$msdownloads['tdownloads'] = $totaldownloads ;
						$this->set('msdownloads',$msdownloads);
						$this->Session->write('msdownloads',$msdownloads);
				} // if($plays_mss)
			//	else
			//	{
			//		if($this->Session->check('band_id'))
			//			$this->set('bandid',$this->Session->read('band_id'));
			//		$this->Session->setFlash('No MySpace data found.');
			//		$this->set('flag',1);
			//	} // if($plays_mss)
			} //if($this->Session->check('band_id'))
			else
			{
				if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
					$this->Session->setFlash('Session expired.');
					$this->set('flag',1);
			} //if($this->Session->check('band_id'))
			
				
		} // if(!empty($this->params['url']['date']) and !empty($this->params['url']['opt']))
		
		$this->layout="stats";
	} // function chart()
	
	/*	 Name: getGraphData
	*	Desc: Get Myspace Graph data for Views , friends & comments
	* 	set : session set for weekly , monthly & yearly data & dates
	*/
	function getGraphData($id,$lastdate,$opt,$field)
	{
		if(!empty($id))
		{
			$mmm_id = $this->Session->read('id') ;
			
			if($lastdate=='m')
			{
				
				if($opt=='d')
				{
					$qry_mss = "select s.$field , s.etime from mss_stat s where s.mss_id=$id and FROM_UNIXTIME(s.etime) between DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 DAY) AND DATE_ADD(CURDATE( ), INTERVAL 1 DAY) order by etime";
					$qry= "select DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 Day) last, CURDATE( ) curr";
					
					
				}
				else
				{
					$qry_mss = "select s.$field , s.etime from mss_stat s where s.mss_id=$id and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";
					$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE( ) curr";
					
				}
				
				
				$mss = $this->Mssstat->findBySql($qry_mss);
				$tfdate = $this->Mssstat->findBySql($qry);
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
				
			        $this->Session->write('mm',count($dt));
				$c=0;
							

			} // if($lastdate=='m')
			elseif($lastdate=='y')
			{
				$qry_mss = "select s.$field , s.etime from mss_stat s  where s.mss_id=$id and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND DATE_ADD(CURDATE( ), INTERVAL 1 DAY) order by etime";
				$mss = $this->Mssstat->findBySql($qry_mss);
				$mss = $this->getyear($mss,'s',$field);

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, DATE_ADD(CURDATE( ), INTERVAL 1 DAY) curr";
				$tfdate = $this->Mssstat->findBySql($qry);

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
						
				if($opt=='d')
				{
					$qry_mss = "select s.$field , s.etime from mss_stat s where s.mss_id=$id  and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 8 DAY) AND DATE_ADD(CURDATE( ), INTERVAL 1 DAY) order by etime";
				
					
				}
				else
				{
					$qry_mss = "select s.$field , s.etime from mss_stat s where s.mss_id=$id  and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";
					
					
				
				}
				
				$mss = $this->Mssstat->findBySql($qry_mss); // to get myspace stats
				
				$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, DATE_ADD(CURDATE( ), INTERVAL 1 DAY) curr";
				$tfdate = $this->Mssstat->findBySql($qry); // to get date
	
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


			
			
			if(empty($mss))
			{	
				$mscount=0;
			} // if(empty($mss))
			else
			{
				$mscount = count($mss);		
			} // if(empty($mss))

			$msplayscount=0;
			$cnt=0;
			$predate=0;
			
			
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;
				
				if($lastdate=='y')
				{
					for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
					{
						if($opt=='c')
						{
							if($mss[$i]['s']['etime']==$val)
							{
								$msplays[$cnt]= $mss[$i]['s'][$field] ;
								$msplayscount+=$msplays[$cnt];
						
								$flg=1;
								$cnt++;
								break;
							} //	if($mss_data[$i]['s']['etime'])==$val)
						}
						elseif($opt=='d')
							{
								if($mss[$i]['s']['etime']==$val)
								{
									
									if($i!=0)
									{
										$msplays[$cnt]= $mss[$i]['s'][$field]-$mss[$i-1]['s'][$field];
										$msplayscount += $msplays[$cnt] ;
									}
									else
									{
										$msplays[$cnt]= $mss[$i]['s'][$field];
										
									}
										$flg=1;
										$cnt++;
										break;
								}
								
							}
							
					} // for($i=0 ; $i < $mscount-1 ; $i++)
					if($flg==0)
					{
						$msplays[$cnt]=0;
						$cnt++;
					}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
						{
							
							if($opt=='c')
							{
								
								if(date('Y-m-d',$mss[$i]['s']['etime'])!=$predate)
								{
									$predate = date('Y-m-d',$mss[$i]['s']['etime']);
									
																			
										$msplays[$cnt]= $mss[$i]['s'][$field];
										
										$msplayscount += $msplays[$cnt] ;
									
									$flg=1;
									$cnt++;
								}
								break;
							}
							elseif($opt=='d')
							{
								if($i!=0)
								{
									$msplays[$cnt]= $mss[$i]['s'][$field]-$mss[$i-1]['s'][$field];
									$msplayscount += $msplays[$cnt] ;
								}
								else
								{
									$msplays[$cnt] = 0;
								}
								$flg=1;
								$cnt++;
								break;
							}

						} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $mscount-1 ; $i++)

					if($flg==0)
					{
						$msplays[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
			
				if($msplays[$cnt-1]==0)
				{
					$percentage = 0;
					$wpercentage = 0;
				}
				else
				{
					$percentage = round((($msplays[$cnt-1]-$msplays[$cnt-2])/$msplays[$cnt-1])*100,2);
					$wpercentage = round((($msplays[$cnt-1]-$msplays[$cnt-7])/$msplays[$cnt-1])*100,2);
				}
				
				$diff = $msplays[$cnt-1]-$msplays[$cnt-2];
				$wdiff = $msplays[$cnt-1]-$msplays[$cnt-7];
			
			
			
				if($field=='views')
				{
					
					$this->Session->write('msviews',$msplays);
					$this->set('vpercentage',$percentage);
					$this->set('vdiff',$diff);
					$this->set('vwpercentage',$wpercentage);
					$this->set('vwdiff',$wdiff);
					$this->set('vtotal',$msplays[$cnt-1]);
										
					
				}
				elseif($field=='friends')
				{
					$this->Session->write('msfriends',$msplays);
					$this->set('fpercentage',$percentage);
					$this->set('fdiff',$diff);
					$this->set('fwpercentage',$wpercentage);
					$this->set('fwdiff',$wdiff);
					$this->set('ftotal',$msplays[$cnt-1]);
					
				}
				elseif($field=='comments')
				{
					$this->Session->write('mscomments',$msplays);
					$this->set('cpercentage',$percentage);
					$this->set('cdiff',$diff);
					$this->set('cwpercentage',$wpercentage);
					$this->set('cwdiff',$wdiff);
					$this->set('ctotal',$msplays[$cnt-1]);
					
				}
			
				if($this->Session->check('dt'))
				{
					$this->Session->delete('dt');
				}
				
				$this->Session->write('dt',$dt);
				$this->Session->write('lastdate',$lastdate);
				$this->Session->write('opt',$opt);
				
				return $lastdate;
		}
		
	}
	
	/* 	name : msPlays
	* 	description : get myspace plays data
	* 	set : session set for weekly , monthly & yearly data & dates
	*/
	function msPlays($id,$lastdate,$opt,$field,$title)
	{
		if(!empty($id))
		{
			$mmm_id = $this->Session->read('id') ;
			
			if($lastdate=='m')
			{
				if($opt=='d')
				{
					$qry_mss = "select s.$field , s.etime from mss_play_stat s where s.mss_id=$id and s.title='$title' and FROM_UNIXTIME(s.etime) between DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 DAY) AND DATE_ADD(CURDATE( ), INTERVAL 1 DAY) order by etime";
					$qry= "select DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 Day) last, CURDATE( ) curr";
					
					
				}
				else
				{
					$qry_mss = "select s.$field , s.etime from mss_play_stat s  where s.mss_id=$id and s.title='$title' and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";
					$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE( ) curr";
					
					
				}
				
				$mss = $this->Mssstat->findBySql($qry_mss);
				$tfdate = $this->Mssstat->findBySql($qry);

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
				$qry_mss = "select s.$field , s.etime from mss_play_stat s  where s.mss_id=$id and s.title='$title'  and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND DATE_ADD(CURDATE( ), INTERVAL 1 DAY) order by etime";
				$mss = $this->Mssstat->findBySql($qry_mss);
				$mss = $this->getyear($mss,'s',$field);

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, DATE_ADD(CURDATE( ), INTERVAL 1 DAY) curr";
				$tfdate = $this->Mssstat->findBySql($qry);

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
				if($opt=='d')
				{
					$qry_mss = "select s.$field , s.etime from mss_play_stat s  where s.mss_id=$id and s.title='$title' and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 8 DAY) AND DATE_ADD(CURDATE( ), INTERVAL 1 DAY) order by etime";
					
					
				
				}
				else
				{
					$qry_mss = "select s.$field , s.etime from mss_play_stat s  where s.mss_id=$id and s.title='$title' and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";
					
					
					
				}
				$mss = $this->Mssstat->findBySql($qry_mss);
				$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, DATE_ADD(CURDATE( ), INTERVAL 1 DAY) curr";

	
				$tfdate = $this->Mssstat->findBySql($qry);
	
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


			if(empty($mss))
			{	
				$mscount=0;
			} // if(empty($mss))
			else
			{
				$mscount = count($mss);		
			} // if(empty($mss))

			$msplayscount=0;
			$cnt=0;
			$predate=0;
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				if($lastdate=='y')
				{
					for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
					{
						if($opt=='c')
						{
							if($mss[$i]['s']['etime']==$val)
							{
							$msplays[$cnt]= $mss[$i]['s'][$field] ;
							$msplayscount+=$msplays[$cnt];
					
							$flg=1;
							$cnt++;
							break;
							} //	if($mss_data[$i]['s']['etime'])==$val)
						}
						elseif($opt=='d')
						{
							if($mss[$i]['s']['etime']==$val)
							{
								if($i!=0)
								{
									$msplays[$cnt]= $mss[$i]['s'][$field]-$mss[$i-1]['s'][$field];
									$msplayscount += $msplays[$cnt] ;
								}
								else
								{
									$msplays[$cnt]= $mss[$i]['s'][$field];
									
								}
									$flg=1;
									$cnt++;
									break;
							}
							
						}
					} // for($i=0 ; $i < $mscount-1 ; $i++)
					if($flg==0)
					{
						$msplays[$cnt]=0;
						$cnt++;
					}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
						{
							if($opt=='c')
							{
							
								if(date('Y-m-d',$mss[$i]['s']['etime'])!=$predate)
								{
									$predate = date('Y-m-d',$mss[$i]['s']['etime']);
	
										$msplays[$cnt]= $mss[$i]['s'][$field];
										$msplayscount += $msplays[$cnt] ;
									
									$flg=1;
									$cnt++;
								}
								break;
							}
							elseif($opt=='d')
							{
								if($i!=0)
								{
									$msplays[$cnt]= $mss[$i]['s'][$field]-$mss[$i-1]['s'][$field];
									$msplayscount += $msplays[$cnt] ;
								}
								else
								{
									$msplays[$cnt] = 0;
								}
								$flg=1;
								$cnt++;
								break;
							}

						} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $mscount-1 ; $i++)

					if($flg==0)
					{
						$msplays[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
			
					
			$this->Session->write('dt',$dt);
			$this->Session->write('lastdate',$lastdate);
			$this->Session->write('opt',$opt);
			
			return $msplays;
		}
	}
	
 
	/* Name: showprofile
	 Desc: Display profile link
	 Retrun : void
	 set : show profile links
	 */

	function showprofile()
	{
		$results = $this->Msslogin->findAll(array('mmm_id'=>$this->Session->read('id'),'status'=>'1')); //
		if($results)
		{
			$this->set('results',$results);
		} // if($results)
		else
		{
			$this->Session->setFlash('Please Add Profile');
			$this->redirect('/Mss/addprofile/');

		} // if($results)
	}

	/* Name: barstat
	 Desc: Show MySpace Statistics ( Friends , views , comments etc .. on  Bar Graph )
	 Return : void
	 */
	function barstat()
	{
		$this->set('results',$this->Session->read('barstat'));

	}

	/* Name: barmusicstat
	 Desc: Show MySpace Statistics ( Song Title , plays , downloads etc .. on Bar Graphs)
	 Return : void
	 */

	function barmusicstat()
	{
		$this->set('results',$this->Session->read('musicstat'));

	}

	/* Name: statistics
	 Desc: Show MySpace Statistics
	 Return : void
	 */
	function statistics($id)
	{
		//$this->layout='stats';
		$mmm_id = $this->Session->read('id') ;
		//if(!empty($this->params['url']['id']))
		if(!empty($id))
		{
			//$id= $this->params['url']['id'];
			
			$qry = "select s.mss_id , s.friends , s.views , s.lastlogin , s.plays , s.todayplays , s.downloads , s.tdownloads , s.comments from mss_stat s
				where s.mss_stat_id=(SELECT max( s.mss_stat_id )
							FROM mss_stat s, mss_login l
							where
							s.mss_id = $id and l.mss_id = s.mss_id and l.mmm_id='$mmm_id')";
			
			
			$results = $this->Mss->findBySql($qry);
			//		$results = $this->Mss->find(array('mmm_id'=>$this->Session->read('id'),'mss_id'=>$this->params['url']['id'])); //
			if($results)
			{
				return $results;
			} // if($results)
			
		
		} // if(!empty($this->params['url']['id']) and !empty($this->params['url']['etime']))
	
	} // 	function statistics()


	function viewschart()
	{
		$this->set('msviews',$this->Session->read('msviews'));
		$this->set('dt',$this->Session->read('dt'));
	}


	function friendschart()
	{
		$this->set('msfriends',$this->Session->read('msfriends'));
		$this->set('dt',$this->Session->read('dt'));
	}


	function commentschart()
	{
		$this->set('mscomments',$this->Session->read('mscomments'));
		$this->set('dt',$this->Session->read('dt'));
	}
	
	function playschart()
	{
		
		$this->set('msplays',$this->Session->read('msplays'));
		$this->set('dt',$this->Session->read('dt'));
	}
	
	function downloadschart()
	{
		$this->set('msdownloads',$this->Session->read('msdownloads'));
		$this->set('dt',$this->Session->read('dt'));
	}


	
	
	/* Name: plays
	   Desc: Analytic graphs for plays
	*/
	function plays()
	{
		
		if(empty($this->data['analytic']['date']))
		{
		$lastdate = 'w';
		} // if(empty($this->params['url']['lastdate']))
		else
		{
		$lastdate = $this->data['analytic']['date'];						
		} // if(empty($this->params['url']['lastdate']))

		
		if(!empty($this->params['url']['id']) and !empty($this->params['url']['title']))
		{
			$id = $this->params['url']['id'] ;
			$title = $this->params['url']['title'];
			
			$mmm_id = $this->Session->read('id') ;
			
			if($lastdate=='m')
			{
			$qry_mss = "select s.plays , s.etime from mss_play_stat s , mss_login l where s.mss_id=$id and s.title='$title' and s.mss_id = l.mss_id and l.mmm_id = '$mmm_id' and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";
			
			$mss = $this->Mssstat->findBySql($qry_mss);


			$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE() curr";
			$tfdate = $this->Mssstat->findBySql($qry);

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
			$qry_mss = "select s.plays , s.etime from mss_play_stat s , mss_login l where s.mss_id=$id and s.title='$title' and s.mss_id = l.mss_id and l.mmm_id = '$mmm_id' and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";


				$mss = $this->Mssstat->findBySql($qry_mss);
				$mss = $this->getyear($mss,'s','plays');

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, CURDATE() curr";
				$tfdate = $this->Mssstat->findBySql($qry);

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
			$qry_mss = "select s.plays , s.etime from mss_play_stat s , mss_login l where s.mss_id=$id and s.title='$title' and s.mss_id = l.mss_id and l.mmm_id = '$mmm_id' and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";
			
			$mss = $this->Mssstat->findBySql($qry_mss);


			$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, CURDATE() curr";
			$tfdate = $this->Mssstat->findBySql($qry);

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


			if(empty($mss))
			{	
				$mscount=0;
			} // if(empty($mss))
			else
			{
				$mscount = count($mss);		
			} // if(empty($mss))

			$msplayscount=0;
			$cnt=0;
			$predate=0;
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				if($lastdate=='y')
				{

					
						for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
						{
							if($mss[$i]['s']['etime']==$val)
							{
								$msplays[$cnt]= $mss[$i]['s']['plays'] ;
								$msplayscount+=$msplays[$cnt];
						
								$flg=1;
								$cnt++;
								break;
							} //	if($mss_data[$i]['s']['etime'])==$val)
						} // for($i=0 ; $i < $mscount-1 ; $i++)

						if($flg==0)
						{
							$msplays[$cnt]=0;
							$cnt++;

						}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
						{
							if(date('Y-m-d',$mss[$i]['s']['etime'])!=$predate)
							{
								$predate = date('Y-m-d',$mss[$i]['s']['etime']);

									$msplays[$cnt]= $mss[$i]['s']['plays'];
									$msplayscount += $msplays[$cnt] ;
								
								$flg=1;
								$cnt++;
							}
							break;

						} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $mscount-1 ; $i++)

					if($flg==0)
					{
						$msplays[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
				
				
				$this->Session->write('msplays',$msplays);
				$this->Session->write('dt',$dt);
				$this->Session->write('lastdate',$lastdate);
				$this->set('lastdate',$lastdate);
			
		} // if(!empty($this->params['url']['id']) and !empty($this->params['url']['title']))
			
	} 	// function plays()
	
	

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
			} // if($count==1)
		
			return $views_data;
		}
	} // function getviewyear()

	
	function gethomepage()
	{
		
		if($this->Session->check('mssuserid'))
		{
			$user_id = $this->Session->read('mssuserid');
			//$url="http://profile.myspace.com/index.cfm?fuseaction=user.viewprofile&friendid=".$user_id;
			$url="http://www.myspace.com";
			
			
				$content="";
				$ch = curl_init();
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
				$content = curl_exec ($ch);
				curl_close ($ch);

			$this->layout = "stats";
			$this->set('myspace',$content);
		}
		else
		{
			$this->redirect('/mss/index/');
		}
		
	}
	/* Name: getstatistics
	 Desc: Get MySpace Player Statistics and called from statistics view
	 Retrun : array of Player Statistics to statistics view
	 */
	function getstatistics()
	{
		$stats = $this->Mssstat->findAll(array('mss_id'=>$this->params['id'],'etime'=>$this->params['etime'])); //
		if($stats)
		{
			$this->Session->write('musicstat', $stats); // to show results in graphs
			return $stats;
		}
		return false;
	} //function getstatistics()


	/* Name: getStat
	 Desc: Get and insert all user statistics into database
	 Retrun : true if successfuly submitted
	 */

	function getStat($data,$band_id)
	{
					
			//$url="http://profile.myspace.com/index.cfm?fuseaction=user.viewprofile&friendid=".$data['Msslogin']['user_id'];
			$id = str_replace(" ","",$data['Msslogin']['user_id']);
			$url="http://www.myspace.com/".$id;
			
		
			$myspace = new myspace_profile($url);
			
			
			if($myspace->data)
			{	
				// Getting Total Number of Friends
				$frds = $myspace->get_friends();
	
				// Getting Profile Views
				@$view = $myspace->get_profile_view();
				@$vw = explode("<br>",@$view);
				@$pfview = explode(":",@$vw[5]);
				@$lastlg = explode(":",@$vw[9]);
				@$views = str_replace("&nbsp;","",trim($pfview[1]));
				@$lastlogin = date("Y-m-d",strtotime(str_replace("&nbsp;","",(string)trim($lastlg[1]))));
			
				if(true)
				{
						
						$name=$myspace->get_name();
						$name = str_replace("<br />"," ",$name);
												
						$this->time = time();
						$data['Msslogin']['name']= $name;
						$data['Msslogin']['etime']= $this->time;
						$this->Msslogin->create();
						$this->Msslogin->save($data);
						$id=$this->Msslogin->getLastInsertId();
		
						$tmp = explode("<u:break>", $myspace->get_comments());
				
						for($i=0;$i<count($tmp);$i++)
						{
							$text[$i][] = $myspace->strip($tmp[$i], "<u:name>", "</u:name>");
							$text[$i][]= $myspace->strip($tmp[$i], "<c:date>", "</c:date>");
							$text[$i][]= $myspace->strip($tmp[$i], "<c:comment>", "</c:comment>");
						} // for($i=0;$i<count($tmp);$i++)
		
		
						// Total Comments Count
						$no_comments = $myspace->get_no_comments();
		
						if(empty($no_comments))
						{
							$no_comments=count($text);
						} // if(empty($no_comments))
		
		
						if($artid and $plid and $profid)
						{
					
							$url = "http://musicservices.myspace.com/Modules/MusicServices/Services/MusicPlayerService.ashx?artistId$artid&playlistId$plid&artistUserId$profid&action=getArtistPlaylist";
			
							
							$objDOM = new DOMDocument();
							if (($file=@fopen($url, "r")))
							{
								$obj= $objDOM->load($url);
								$stats = $objDOM->getElementsByTagName("stats")->item(0);
			
								$att=$stats->getAttributeNode('plays');
								$tplays=$att->value;
			
								$att=$stats->getAttributeNode('playsToday');
								$ttplays=$att->value;
			
								$att=$stats->getAttributeNode('downloads');
								$downloads=$att->value;
			
								$att=$stats->getAttributeNode('downloadsToday');
								$tdownloads=$att->value;
			
								$data = NULL;
			
								$data['Mss']['mss_id']=$id;
								$data['Mss']['status']=1;
								$data['Mss']['friends']=$frds;
								$data['Mss']['views']=$views;
								$data['Mss']['lastlogin']=$lastlogin;
								$data['Mss']['plays']=$tplays;
								$data['Mss']['todayplays']=$ttplays;
								$data['Mss']['downloads']=$downloads;
								$data['Mss']['tdownloads']=$tdownloads;
								$data['Mss']['comments']=$no_comments;
								$data['Mss']['etime']= $this->time;
			
								if($this->Mss->save($data))
								{
									$data = NULL;
									$note = $objDOM->getElementsByTagName("track");
			
			
									foreach( $note as $key => $value )
									{
			
										$names = $value->getElementsByTagName("title");
										$title= $names->item(0)->nodeValue;
										$song = $value->getElementsByTagName("song");
										foreach($song as $skey => $sval)
										{
											$status = $sval->getElementsByTagName("stats")->item(0);
											$att=$status->getAttributeNode('plays');
											$play= $att->value;
			
											/*
											 $att=$status->getAttributeNode('playsToday');
											$tplay= $att->value;
											*/
											$tplay= 0;
											/*
											$att=$status->getAttributeNode('downloads');
											$download= $att->value;
											*/
											$download= 0;
											/*
											$att=$status->getAttributeNode('downloadsToday');
											$tdownload= $att->value;
											*/
											$tdownload = 0;
			
										} // foreach($song as $skey => $sval)
			
										$stat['Mssstat']['mss_id'] = $id ;
										$stat['Mssstat']['title'] = $title ;
										$stat['Mssstat']['plays'] = $play ;
										$stat['Mssstat']['todayplays'] = $tplay ;
										$stat['Mssstat']['downloads'] = $download ;
										$stat['Mssstat']['tdownloads'] = $tdownload ;
										$stat['Mssstat']['etime'] = $this->time ;
										$this->Mssstat->create();
										$this->Mssstat->save($stat);
			
									} // foreach( $note as $key => $value )
			
			
								} // 	if (($file=@fopen($url, "r")))
	
							} // if($artid and $plid and $profid)
							else
							{
								return false;
							} //  // if($artid and $plid and $profid)
						} // if($artid and $plid and $profid)
						else
						{
						
							$data = NULL;
			
							$data['Mss']['mss_id']=$id;
							$data['Mss']['status']=1;
							$data['Mss']['friends']=$frds;
							$data['Mss']['views']=$views;
							$data['Mss']['lastlogin']=$lastlogin;
							$data['Mss']['plays']=0;
							$data['Mss']['todayplays']=0;
							$data['Mss']['downloads']=0;
							$data['Mss']['tdownloads']=0;
							$data['Mss']['comments']=$no_comments;
							$data['Mss']['etime']= $this->time;
		
							$this->Mss->save($data);
								
						} //if($artid and $plid and $profid)
						
						$name = NULL;
						$cdate = NULL;
						$comments = NULL;
						// Comments contents
						foreach ($text as $key => $val)
						{
							foreach ($val as $key1 => $cval)
							{
								if($key1==0)
								{
									if($cval)
									{
										$name=$cval;
									}
								}
	
								elseif($key1==1)
								{
									if($cval)
									{
										$cdate= date('Y-m-d H:m:s',strtotime($cval));
									}
								}
								else
								{
									if($cval)
									{ $comments.=$cval; }
	
								}
							} // foreach ($val as $key1 => $cval)
							if($comments)
							{
								$comm['Msscomm']['mss_id'] = $id ;
								$comm['Msscomm']['name'] = $name ;
								$comm['Msscomm']['cdate'] = $cdate ;
								$comm['Msscomm']['comments'] = $comments ;
								$comm['Msscomm']['etime'] = $this->time ;
								$this->Msscomm->create();
								$this->Msscomm->save($comm);
								$comm = NULL;
								$comments = NULL;
							}
						} // foreach ($text as $key => $val)
						return true;
				}
				else
				{
					return false;	
				}
			} // if(myspace)
		
	}// function getStat()*/

} // class LfmsController extends AppController {







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
