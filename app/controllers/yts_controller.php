<?php
vendor('Zend/Loader');
class YtsController extends AppController {
	var $user;
	var $record;
	var $id;
	var $etime= NULL;
	var $name = 'Yts';
	var $uses = array('User','Yt','Ytstat','Ytcommstat','Cms');
	var $helpers = array('Html', 'Error', 'Javascript','FlashChart');
	var $components = array('Cookie'); //  use component email
	var $developerKey , $applicationId , $clientId , $username ;

	/**
	 * Name: beforeFilter
	 * Desc: Performs necessary steps and function calls prior to executing
	 *       any view function calls.
	 */
	function beforeFilter() {

		$this->auth();

		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_YouTube');
		Zend_Loader::loadClass('Zend_Gdata_AuthSub');
		$this->developerKey = 'AI39si6A9KK-TPm6AZG6nOP-EPLjAhKlQM3QZdQhYyEbM-4FF2TwFdBg_Pw-G3tYdMNwrVUCvqL433R1RPKonK2iYDSGD0kQ7w';
		$this->applicationId = 'Yourtri.be';
		// $this->clientId = 'ytapi-BabarAli-musicmotion-mlg50gjd-0';

	
	} // function beforeFilter() {

	/* Name: index
	 * Desc: Display the friends index page.
	 */

	function index()
	{
		$this->redirect('/band/manage/');
//		$this->set('url',$this->getAuthSubRequestUrl());
//		$url=$this->getAuthSubRequestUrl();
//		header("Location : $url");
	

	} // 	function index()
	
	
	/*
	 *	name : ytswelcome
	 *	description : youtube authentication welcome screen
	 */
	
	function ytswelcome()
	{
		$this->layout = "wizard";
		$band  =$this->Cookie->read('flag'); // called from wizard or setting manage
		$flag= $band["flag"];
		$this->set('flag',$flag);
		
		$results = $this->Cms->findAll(array('status' => '1'),array('id' , 'title'));
		$this->set('cms',$results);
	}
	
	/*
	 * 	name : calllogin
	 *	description : youtube login authentication page call
	 */
	function calllogin()
	{
		$url=$this->getAuthSubRequestUrl();
		$url= urldecode($url);

		header("Location: $url");
		exit;
	} // 	function calllogin()

	/*
	 * Name:	process
	 * Desc:	Get and insert youtube statistical data
	 */
	function process()
	{
		if($this->Cookie->valid('bandid'))
		{
			$band_id=$this->Cookie->read('bandid');
			$band_id = $band_id['bandid'];
			
			$band  =$this->Cookie->read('flag'); // myspace called from wizard or setting manage
			$flag= $band["flag"];
			
		
			$mmm_id = $this->Session->read('id');
			$this->getAuthSubRequestUrl();
			$this->username = 'default';
			$httpClient	=	$this->getAuthSubHttpClient($mmm_id,$this->username);
	
			if($httpClient)
			{
				$yt = new Zend_Gdata_YouTube($httpClient, $this->applicationId, $this->clientId, $this->developerKey);
	
				/* Getting User Profile Data */
				$userProfile	=   $this->getAndPrintUserProfile($this->username,$yt,$mmm_id);
				if($this->record)
				{
					
					$user_id= $userProfile['name'];
					$result= $this->Yt->find(array('mmm_id'=>$mmm_id ,'user_id'=>$user_id ,'band_id'=>$band_id));
					
					 if($result)
					{
						        $stoken = $this->record['Yt']['sessionToken'];
							$qry="update yt_login
							       set sessionToken='$stoken'
							       where user_id='$user_id'";
							$this->Yt->query($qry);       
							
						$this->Session->setFlash('This YouTube user is already registered.');
						if($flag=='b')
						{
							$this->redirect('/band/youtube/');
						}
						else
						{
							$this->redirect('/band/manage/');
						}
						exit;
					} //if($result)
					
					
					// set status = 0 for all 'specific mmm user & band'
					$qry = " update yt_login
							set status=0
							where mmm_id='$mmm_id'
							and band_id  = $band_id";
					$this->Yt->query($qry);
					
					$stoken = $this->record['Yt']['sessionToken'];
					$qry="update yt_login
					       set sessionToken='$stoken'
					       where user_id='$user_id'";
					
					$this->Yt->query($qry);
					
					/*
					Below given values for Yt set from  '$this->getAuthSubHttpClient' function 
					$this->record['Yt']['mmm_id'] , $this->record['Yt']['sessionToken'] ,
					$this->record['Yt']['status'] ,	$this->record['Yt']['etime']
					*/

					$this->record['Yt']['user_id'] = $user_id ;
					$this->record['Yt']['band_id'] = $band_id ;
					
					
					$this->Yt->create();
					$this->Yt->save($this->record);
					$this->id = $this->Yt->getLastInsertId();
					
					
	
					$stat['Ytstat']['yt_id'] = $this->id ;
					$stat['Ytstat']['views'] = $userProfile['channelView'] ;
					$stat['Ytstat']['subscriber'] = $userProfile['subscriberCount'] ;
	
					/* Getting Contacts / Friends List */
					$contactsFeed = $this->getAndPrintContactsFeed($this->username,$yt);
					$frds = count($contactsFeed)-1;
					$stat['Ytstat']['friends'] =  $frds ;
					$stat['Ytstat']['etime']= $this->time;
					
					$this->Ytstat->create();
					$this->Ytstat->save($stat);
					$this->record = NULL;
	
	
					/* Getting Video Comments */
					$vid=$this->ReturnVideoId($yt->getuserUploads($this->username));
					if($vid)
					{
						foreach($vid as $key=>$val)
						{
							$entry = $yt->getVideoEntry($key);
							$comments = $yt->getVideoCommentFeed($entry->videoId);
							$commentsFeed = $this->printCommentFeed($comments);
							$videosEntry = $this->printVideoEntry($entry);
							$record = NULL ;
							$record['Ytcommstat']['yt_id']= $this->id;
							$record['Ytcommstat']['title']= $videosEntry['title'];
							$record['Ytcommstat']['duration']= $videosEntry['duration'];
							$record['Ytcommstat']['videoid']= $videosEntry['id'];
							if(empty($videosEntry['viewCount']))
							{
								$record['Ytcommstat']['views']= 0;	
							}
							else{
								$record['Ytcommstat']['views']= $videosEntry['viewCount'];	
							}
							
							$record['Ytcommstat']['total_comments']= count($commentsFeed);
							$record['Ytcommstat']['etime']= $this->time ;
							
							
							$this->Ytcommstat->create();
							$this->Ytcommstat->save($record);
							$record = NULL ;
						}
	
					} // if($vid)
					
										
				} // if($this->record)
	
	
	
				//getAndPrintSubscriptionFeed($this->username,$yt);
				$this->Session->delete("sessionToken");
							
				$this->Session->setFlash('YouTube information has been processed correctly.');
				if($flag=='b')
				{
					$this->redirect('/band/lastfm/');
				}
				else
				{
					$this->redirect('/band/manage/');
				}
	
			} // 		if($httpClient)
			else
			{
				$this->Session->setFlash('To register another YouTube account, you first need to log out in YouTube.');
				if($flag=='b')
				{
					$this->redirect('/band/youtube/');
				}
				else
				{
					$this->redirect('/band/manage/');
				}
			}
		} //
		else
		{
			$this->Session->setFlash('Please select a band.');
			$this->redirect('/band/index/');
		}

	} // function process()


	/*
	 name : updatechannel
	 description :	update / activate specific channel .
	 called :	called from band/manage 
	*/
	function updatechannel()
	{
		
		if($this->data)
		{
			$mmm_id = $this->Session->read('id') ;
			
			if($this->Session->check('band_id'))
			{
				$band_id = $this->Session->read('band_id');
			}
			elseif($this->Cookie->valid('bandid'))
			{
			
				$band_id=$this->Cookie->read('bandid');
				$band_id = $band_id['bandid'];
			}
			else
			{
				$this->Session->setFlash('Please select a band.');
				$this->redirect('/band/index/');
			}
				
				$id = $this->data['Yt']['channel'];
				
				$qry = " update yt_login
					 set status=0
					 where mmm_id='$mmm_id'
					 and band_id  = $band_id ";
				$this->Yt->query($qry);
				
								
				if($id!="none")
				{
				$qry = " update yt_login
					 set status=1
					 where mmm_id='$mmm_id'
					 and band_id  = $band_id
					 and yt_id=$id";
				
				$this->Yt->query($qry);
				}
				$this->Session->setFlash('YouTube channel updated succesfully.');
				$this->redirect('/band/manage/');
				
				
			
		} // if($this->data)
		else
		{
				$this->Session->setFlash('Invalid data.');
				$this->redirect('/band/manage/');
				
		} // if($this->data)	
	}
	/*
	 name : chart
	 set : get and set data for profile views , friends , comments  ,  plays & subscribers
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
			$this->Session->write('yt_id',$this->params['url']['id']);
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
			if($this->Session->check('yt_id'))
			{
			
				$lastdate = $this->params['url']['date'];
				$opt = $this->params['url']['opt'];
				$id=trim($this->Session->read('yt_id'));
				
				
				
				$this->getGraphData($id,$lastdate,$opt,'friends'); // Get & set friends data for graphs , params( id , date , option , field)
				if($this->params['url']['type']=='friends')
				{
					$this->Session->write('type','friends');
				}
				
				$this->getGraphData($id,$lastdate,$opt,'views'); // Get & set channel views data for graphs , params( id , date , option , field)
				if($this->params['url']['type']=='channel')
				{
					$this->Session->write('type','channel');
				}
				
				$this->getGraphData($id,$lastdate,$opt,'subscriber'); // Get & set subscribers data for graphs , params( id , date , option , field)
				if($this->params['url']['type']=='subscribers')
				{
					$this->Session->write('type','subscribers');
				}



									
				$mmm_id = $this->Session->read('id');
				if(@$this->params['url']['ids'])
				{
					$yt_c_id = $this->params['url']['ids'];
					$titles = $this->params['url']['title'];
					$color = $this->params['url']['color'];
					
					
				}
				else
				{
					$yt_c_id ='0';
				}
				
				$this->set('yt_c_id',$yt_c_id);
				
				$video_yt = $this->Session->read('video_yt');
				$this->set('video_yt',$video_yt);
				$this->set('id',$id);
				
				$totalplays 	= NULL;
				$ytviews 	= NULL;
							
				$totalcomments 	= NULL;
				$ytcomments 	= NULL;					
				
				
				foreach($video_yt as $key => $val)
				{
					$yt_c_ids 	= $val['y']['yt_c_id'];
					$title		= $val['y']['title'];
					
					if($yt_c_id=='all') 
					{
						$_SESSION[$title]	="1";
					}
					elseif($yt_c_id=='call')
					{
						$_SESSION["c".$title]	="1";
					} // if($yt_c_id=='all' || $yt_c_id=='call')
					
					$ytviews[$title] 	= $this->getGraphDataVideo($id,$lastdate,$opt,'views',addslashes($title));
					$ytcomments[$title] 	= $this->getGraphDataVideo($id,$lastdate,$opt,'total_comments',addslashes($title));
					
													
					foreach($ytviews[$title] as $mskey => $msval)
					{	
						@$totalplays[$mskey]+= $msval;	
					}
					
					foreach($ytcomments[$title] as $mskey => $msval)
					{	
						@$totalcomments[$mskey]+= $msval;	
					}
					
				}
				
				$ytviews['tplay'] = $totalplays;
				$this->set('ytviews',$ytviews);
				$this->Session->write('ytviews',$ytviews);
				
			
				$ytcomments['tcomments'] = $totalcomments;
				$this->set('ytcomments',$ytcomments);
				$this->Session->write('ytcomments',$ytcomments);
					
						if($yt_c_id=='all' || $yt_c_id=='call')
						{
							if($this->params['url']['type']=='views')
							{
								$this->Session->write('type','views');
								$this->Session->write('tplay',"1");
							}
							elseif($this->params['url']['type']=='total_comments')
							{
								$this->Session->write('type','comments');
								$this->Session->write('ctcomments',"1");
							}
													
						}
						elseif($yt_c_id=='none' || $yt_c_id=='cnone')
						{
							
														
							foreach($video_yt as $key => $val)	// All plays toggle off
							{
								
								$title= $val['y']['title'];
								if($this->params['url']['type']=='views')
								{
								$_SESSION[$title]="0";
								}
								elseif($this->params['url']['type']=='total_comments')
								{
								$_SESSION["c".$title]="0";
								}
																
							}
									
									
							$this->Session->write('lastdate',$lastdate);
							$this->Session->write('opt',$opt);
													
															
							if($this->params['url']['type']=='views')
							{
								$totalplays 	= NULL;
								$ytviews 	= NULL;
								// video play views
								$this->Session->write('tplay',"0");  // Total plays toggle off
								$this->set('ytviews',$ytviews);
								$this->Session->write('ytviews',$ytviews);
								$this->Session->write('type','views');
								
							}
							elseif($this->params['url']['type']=='total_comments')
							{
								$totalcomments 	= NULL;
								$ytcomments 	= NULL;	
							
								// Video Comments
								$this->Session->write('ctcomments',"0");  // Total plays toggle off
								$this->set('ytcomments',$ytcomments);
								$this->Session->write('ytcomments',$ytcomments);
								$this->Session->write('type','comments');
							}
							
						}
						elseif($yt_c_id=='tplay' || $yt_c_id=='tcomments')
						{
							
								
								
								if($this->params['url']['type']=='views')
								{
									$this->Session->write('type','views');
									if($this->Session->read('tplay')=='1')
									{	
										$this->Session->write('tplay','0');
									}
									else
									{
									$this->Session->write('tplay','1');
									}
								} // if($this->params['url']['type']=='views')
								elseif($this->params['url']['type']=='total_comments')
								{
									$this->Session->write('type','comments');
									if($this->Session->read('ctcomments')=='1')
									{
										$this->Session->write('ctcomments','0');
									} // if($this->Session->read('ctcomments')=='1')
									else
									{
										$this->Session->write('ctcomments','1');
									} // if($this->Session->read('ctcomments')=='1')
								} // if($this->params['url']['type']=='views')
							
							
							
						}
						elseif($yt_c_id=="0")
						{
								
								if($this->params['url']['type']=='views')
								{
									$this->Session->write('type','views');
								}
								elseif($this->params['url']['type']=='total_comments')
								{
									$this->Session->write('type','comments');
								}
							
							
						}
						elseif(!empty($yt_c_id))
						{
							
							
								if($this->params['url']['type']=='views')
								{
									
									$this->Session->write('type','views');
									$this->Session->write('color',$color);
									if($this->Session->read($titles)=='1')
									{
										$_SESSION[$titles]='0';
									} // if($this->Session->read($title)=='1')
									else
									{
										$_SESSION[$titles]='1';
									} // if($this->Session->read($title)=='1')
								} // if($this->params['url']['type']=='views')
								elseif($this->params['url']['type']=='total_comments')
								{
									$this->Session->write('type','comments');
									$this->Session->write('color',$color);
									if($this->Session->read("c".$titles)=='1')
									{
										$_SESSION["c".$titles]='0';
									} // if($this->Session->read("c".$title)=='1')
									else
									{
										$_SESSION["c".$titles]='1';
									} // if($this->Session->read("c".$title)=='1')
							
								} // if($this->params['url']['type']=='views')
							
							
						}
					
					
					
				
				
			} // if($this->Session->check('mssid'))
			
		} // if(!empty($this->params['url']['date']) and !empty($this->params['url']['opt']))
		else   
		{
			$this->set('flag',0);
			if($this->Session->check('yt_id'))
			{
				$mmm_id = $this->Session->read('id');
				$yt_id = $this->Session->read('yt_id');
				$results = $this->Yt->find(array('mmm_id'=>$mmm_id,'yt_id'=>$yt_id)); //
	
				if($results)
				{
					
					$id = $results['Yt']['yt_id'];
					$user_id = $results['Yt']['user_id'];
					$this->Session->write('ytuserid',$user_id); // to get myspace home page
					$this->Session->write('yt_id',$id); // to get myspace home page
					
								
					$this->getGraphData($id,'w','d','views'); // Get & set channel views data for graphs , params( id , date , option , field)
					$this->getGraphData($id,'w','d','friends'); // Get & set friends data for graphs , params( id , date , option , field)
					$this->getGraphData($id,'w','d','subscriber'); // Get & set subscribers data for graphs , params( id , date , option , field)
					
					$this->Session->write('type','channel');   // by default channel views tab selected
					
	
				} // if($results)
				else
				{
					if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
							$this->Session->setFlash('No YouTube data found.');
							$this->set('flag',1);
					
				} // if($results)
				
				
				
				
				$this->set('yt_c_id','all');
				
				$this->Session->write('totalplay',0);				
				
				$qry = "select y.yt_c_id , y.title from yt_comments_stat y , yt_login l
								where
								y.yt_id= $id and y.etime = l.etime and y.yt_id = l.yt_id and l.mmm_id='$mmm_id'
								group by y.yt_c_id , y.title , y.views
								order by sum(y.views) desc
								limit 5";
								
				
				$video_yt = $this->Ytcommstat->findBySql($qry);
				
				
				if($video_yt)
				{
					$this->Session->write('video_yt',$video_yt);
					$this->set('video_yt',$video_yt);
					
					$this->Session->write('color','#FF6600');
					$this->set('id',$id);
					$ytviews = NULL;
					$ytcomments = NULL;
					$this->Session->write('tplay',"1");  //  total plays toggle on
					$this->Session->write('ctcomments',"1");  //  total plays toggle on
					foreach($video_yt as $key => $val)
					{
						$yt_c_id= $val['y']['yt_c_id'];
						$title= $val['y']['title'];
						
						
						
						$ytviews[$title] 	= $this->getGraphDataVideo($id,'w','d','views',addslashes($title));
						$ytcomments[$title] 	= $this->getGraphDataVideo($id,'w','d','total_comments',addslashes($title));
						
						$_SESSION[$title]="1"; // All plays toggle on
						$_SESSION["c".$title]="1"; // All comments toggle on
						//$this->Session->write("$title","1");   // All plays toggle on
											
						
						// get & set total plays statistics
						foreach($ytviews[$title] as $mskey => $msval)
						{	
							@$totalplays[$mskey]+= $msval;	
						} // foreach($msplays[$title] as $mskey => $msval)
						
						// get & set total plays statistics
						foreach($ytcomments[$title] as $mskey => $msval)
						{	
							@$totalcomments[$mskey]+= $msval;	
						} // foreach($msplays[$title] as $mskey => $msval)
						
					} // foreach($video_yt as $key => $val)
						
						$ytviews['tplay'] = $totalplays ;
						$this->set('ytviews',$ytviews);
						$this->Session->write('ytviews',$ytviews);
						
						$ytcomments['tcomments'] = $totalcomments ;
						$this->set('ytcomments',$ytcomments);
						$this->Session->write('ytcomments',$ytcomments);
						
						
						
						
				} // if($video_yt)
				else
				{
					if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
							$this->Session->setFlash('No YouTube data found.');
							$this->set('flag',1);
				} // if($video_yt)
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
	 * Name: channel
	 * Desc: Show channel list and link to open channel statistics
	 */
	function channel()
	{
		$mmm_id = $this->Session->read('id');
		$result= $this->Yt->findAll(array('mmm_id'=>$mmm_id , 'status'=>'1'));
		if($result)
		{
			$this->set('result',$result);

		} // if($result)
		else
		{
			$this->Session->setFlash('First you should use register your YouTube account.');
			$this->redirect('/yts/index/');
		}
	} //function data()

	/*
	 * Name: statistics
	 * Desc: Show Channel Statistics and call Stat function to get Video Statistics
	 */
	function statistics()
	{

		$id=	$this->params['url']['id'];
		$etime=	$this->params['url']['etime'];
		$mmm_id = $this->Session->read('id');
		$qry = "select s.views,s.subscriber,s.friends from yt_stat s , yt_login y where s.yt_id=$id and s.etime=$etime and y.yt_id=s.yt_id and y.mmm_id='$mmm_id'";
		$record= $this->Ytstat->findBySql($qry);
		if($record)
		{
			$this->set('result',$record);
			$this->set('id',$id);
			$this->set('etime',$etime);
		} // if($record)
		else
		{
			$this->Session->setFlash('Invalid parameter.');
			$this->redirect('/yts/channel/');
		}
			
	} // function statistics()

	/*
	 * Name: stat
	 * Desc : Get Video Statistics
	 */
	function stat()
	{
		$ytid=$this->params['id'];
		$etime=$this->params['etime'];
		$record = $this->Ytcommstat->findAll(array('yt_id'=>$ytid , 'etime'=>$etime));
		return $record;
	}


	/*
	 * Name: getGraphData
	 * Desc : To get graph data for views , friends & subscribers
	 */
	function getGraphData($ytid,$lastdate,$opt,$field)
	{
		if(!empty($ytid))
		{			
			$mmm_id = $this->Session->read('id') ;
			
			if($lastdate=='m')
			{
				if($opt=='d')
				{
					$qry_yt = "select y.$field , y.etime from yt_stat y where y.yt_id=$ytid  and FROM_UNIXTIME(y.etime) between DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 DAY) AND CURDATE( ) order by etime";
				}
				else
				{
					$qry_yt = "select y.$field , y.etime from yt_stat y where y.yt_id=$ytid  and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";					
				}
			
			
				$yt = $this->Ytstat->findBySql($qry_yt);


				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE() curr";
				$tfdate = $this->Ytstat->findBySql($qry);
	
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
				$qry_yt = "select y.$field , y.etime from yt_stat y  where y.yt_id=$ytid  and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";
				$yt = $this->Ytstat->findBySql($qry_yt);
				$yt = $this->getyear($yt,'y',$field);

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, CURDATE() curr";
				$tfdate = $this->Ytstat->findBySql($qry);

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
					$qry_yt = "select y.$field , y.etime from yt_stat y  where y.yt_id=$ytid  and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";
				}
				else
				{
					$qry_yt = "select y.$field , y.etime from yt_stat y  where y.yt_id=$ytid  and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";	
				}
			
				$yt = $this->Ytstat->findBySql($qry_yt);


				$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, CURDATE() curr";
				$tfdate = $this->Ytstat->findBySql($qry);
	
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


			if(empty($yt))
			{	
				$ytcount=0;
			} // if(empty($mss))
			else
			{
				$ytcount = count($yt);		
			} // if(empty($mss))

			$ytviewscount=0;
			$cnt=0;
			$predate=0;
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				if($lastdate=='y')
				{

					
						for($i=0 ; $i < $ytcount ; $i++)  // set Myspace views according to main loop date
						{
							if($opt=='c')
							{							
								if($yt[$i]['y']['etime']==$val)
								{
							
									$ytviews[$cnt]= $yt[$i]['y'][$field] ;
									$ytviewscount+=$ytviews[$cnt];
					
									$flg=1;
									$cnt++;
									break;
								} //	if($mss_data[$i]['s']['etime'])==$val
							}
							elseif($opt=='d')
							{
								if($yt[$i]['y']['etime']==$val)
								{
									if($i!=0)
									{
										$ytviews[$cnt]= $yt[$i]['y'][$field]-$yt[$i-1]['y'][$field];
										$ytviewscount += $ytviews[$cnt] ;
									}
									else
									{
										$ytviews[$cnt]= $yt[$i]['y'][$field];
										
									}
										$flg=1;
										$cnt++;
										break;
								}
							}
						} // for($i=0 ; $i < $mscount-1 ; $i++)

						if($flg==0)
						{
							$ytviews[$cnt]=0;
							$cnt++;

						}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $ytcount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)
						{
							
							if($opt=='c')
							{
								if(date('Y-m-d',$yt[$i]['y']['etime'])!=$predate)
								{
									
									$predate = date('Y-m-d',$yt[$i]['y']['etime']);
	
										$ytviews[$cnt]= $yt[$i]['y'][$field];
										$ytviewscount += $ytviews[$cnt] ;
									
									$flg=1;
									$cnt++;
								}
								break;
							}
							elseif($opt=='d')
							{
								if($i!=0)
								{
									$ytviews[$cnt]= $yt[$i]['y'][$field]-$yt[$i-1]['y'][$field];
									$ytviewscount += $ytviews[$cnt] ;
								}
								else
								{
									$ytviews[$cnt] = 0;
								}
								$flg=1;
								$cnt++;
								break;
							}

						} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $mscount-1 ; $i++)

					if($flg==0)
					{
						$ytviews[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
				
				if($ytviews[$cnt-1]==0)
				{
					$percentage = 0;
					$wpercentage = 0;
				}
				else
				{
					$percentage = round((($ytviews[$cnt-1]-$ytviews[$cnt-2])/$ytviews[$cnt-1])*100,2);
					$wpercentage = round((($ytviews[$cnt-1]-$ytviews[$cnt-7])/$ytviews[$cnt-1])*100,2);
				}
				
				$diff = $ytviews[$cnt-1]-$ytviews[$cnt-2];
				$wdiff = $ytviews[$cnt-1]-$ytviews[$cnt-7];

				if($field=='views')
				{
					$this->Session->write('ytchannel',$ytviews);
					$this->Session->write('dt',$dt);
					$this->set('vpercentage',$percentage);
					$this->set('vdiff',$diff);
					$this->set('vwpercentage',$wpercentage);
					$this->set('vwdiff',$wdiff);
					$this->set('vtotal',$ytviews[$cnt-1]);
					
					
				}
				elseif($field=='friends')
				{
					$this->Session->write('ytfriends',$ytviews);
					$this->Session->write('fdt',$dt);
					$this->set('fpercentage',$percentage);
					$this->set('fdiff',$diff);
					$this->set('fwpercentage',$wpercentage);
					$this->set('fwdiff',$wdiff);
					$this->set('ftotal',$ytviews[$cnt-1]);
					
				}
				elseif($field=='subscriber')
				{
					$this->Session->write('ytsubscriber',$ytviews);
					$this->Session->write('sdt',$dt);
					$this->set('spercentage',$percentage);
					$this->set('sdiff',$diff);
					$this->set('swpercentage',$wpercentage);
					$this->set('swdiff',$wdiff);
					$this->set('stotal',$ytviews[$cnt-1]);
					
				}
				
					$this->Session->write('lastdate',$lastdate);
					$this->Session->write('opt',$opt);
					$this->set('lastdate',$lastdate);
					
		} // if(!empty($this->params['url']['id']) and !empty($this->params['url']['title']))
	} // function getGraphData($id,$lastdate,$opt,$field)


	/*
	 * Name: getGraphDataVideo
	 * Desc : To get graph data for plays , downloads
	 */
	function getGraphDataVideo($ytid,$lastdate,$opt,$field,$title)
	{
		if(!empty($ytid))
		{			
			$mmm_id = $this->Session->read('id') ;
			
			if($lastdate=='m')
			{
				if($opt=='d')
				{
					$qry_yt = "select y.$field , y.etime from yt_comments_stat y  where y.yt_id=$ytid and y.title='$title' and FROM_UNIXTIME(y.etime) between DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 DAY) AND CURDATE( ) order by etime";
				}
				else
				{
					$qry_yt = "select y.$field , y.etime from yt_comments_stat y  where y.yt_id=$ytid and y.title='$title' and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";					
				}
			
			
				$yt = $this->Ytcommstat->findBySql($qry_yt);


				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE() curr";
				$tfdate = $this->Ytcommstat->findBySql($qry);
	
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
				$qry_yt = "select y.$field , y.etime from yt_comments_stat y  where y.yt_id=$ytid and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";
				$yt = $this->Ytcommstat->findBySql($qry_yt);
				$yt = $this->getyear($yt,'y',$field);

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, CURDATE() curr";
				$tfdate = $this->Ytcommstat->findBySql($qry);

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
					$qry_yt = "select y.$field , y.etime from yt_comments_stat y  where y.yt_id=$ytid and y.title='$title'  and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";
				}
				else
				{
					$qry_yt = "select y.$field , y.etime from yt_comments_stat y  where y.yt_id=$ytid and y.title='$title' and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";	
				}
			
				$yt = $this->Ytcommstat->findBySql($qry_yt);
				
				$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, CURDATE() curr";
				$tfdate = $this->Ytcommstat->findBySql($qry);
	
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


			if(empty($yt))
			{	
				$ytcount=0;
			} // if(empty($mss))
			else
			{
				$ytcount = count($yt);		
			} // if(empty($mss))

			$ytviewscount=0;
			$cnt=0;
			$predate=0;
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				if($lastdate=='y')
				{

					
						for($i=0 ; $i < $ytcount ; $i++)  // set Myspace views according to main loop date
						{
							if($opt=='c')
							{
								if($yt[$i]['y']['etime']==$val)
								{
								
									$ytviews[$cnt]= $yt[$i]['y'][$field] ;
									$ytviewscount+=$ytviews[$cnt];
						
									$flg=1;
									$cnt++;
									break;
								} //	if($mss_data[$i]['s']['etime'])==$val)
							}
							elseif($opt=='d')
							{
								if($yt[$i]['y']['etime']==$val)
								{
									if($i!=0)
									{
										$ytviews[$cnt]= $yt[$i]['y'][$field]-$yt[$i-1]['y'][$field];
										$ytviewscount += $ytviews[$cnt] ;
									}
									else
									{
										$ytviews[$cnt]= $yt[$i]['y'][$field];
									
									}
										$flg=1;
										$cnt++;
										break;
								}
							}
						} // for($i=0 ; $i < $mscount-1 ; $i++)

						if($flg==0)
						{
							$ytviews[$cnt]=0;
							$cnt++;

						}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $ytcount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)
						{
							
							if($opt=='c')
							{
								if(date('Y-m-d',$yt[$i]['y']['etime'])!=$predate)
								{
									
									$predate = date('Y-m-d',$yt[$i]['y']['etime']);
	
										$ytviews[$cnt]= $yt[$i]['y'][$field];
										$ytviewscount += $ytviews[$cnt] ;
									
									$flg=1;
									$cnt++;
								}
								break;
							}
							elseif($opt=='d')
							{
								if($i!=0)
								{
									$ytviews[$cnt]= $yt[$i]['y'][$field]-$yt[$i-1]['y'][$field];
									$ytviewscount += $ytviews[$cnt] ;
								}
								else
								{
									$ytviews[$cnt] = 0;
								}
								$flg=1;
								$cnt++;
								break;
							}

						} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $mscount-1 ; $i++)

					if($flg==0)
					{
						$ytviews[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
			
				
				
				if($ytviews[$cnt-1]==0)
				{
				$percentage = 0;
				$wpercentage = 0;
				}
				else
				{
				$percentage = round((($ytviews[$cnt-1]-$ytviews[$cnt-2])/$ytviews[$cnt-1])*100,2);
				$wpercentage = round((($ytviews[$cnt-1]-$ytviews[$cnt-7])/$ytviews[$cnt-1])*100,2);
				}
				$diff = $ytviews[$cnt-1]-$ytviews[$cnt-2];
				$wdiff = $ytviews[$cnt-1]-$ytviews[$cnt-7];


				if($opt=='views')
				{
					$this->Session->write('ytviews',$ytviews);
												
				}
				elseif($opt=='total_comments')
				{
					$this->Session->write('ytcomments',$ytviews);
					
				}
				
				$this->Session->write('dt',$dt);	
							
				$this->Session->write('lastdate',$lastdate);
				$this->set('lastdate',$lastdate);
				
				$this->set('percentage',$percentage);
				$this->set('diff',$diff);
				$this->set('wpercentage',$wpercentage);
				$this->set('wdiff',$wdiff);
				$this->set('total',$ytviews[$cnt-1]);
				return $ytviews;
			
		} // if(!empty($this->params['url']['id']) and !empty($this->params['url']['title']))
	

	}

	

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
		
			if($count==1)
			{
				$vdata[$j]=$vms[0][$field];
				$ctd=date('M',strtotime(date("Y-m-d", $vms[0]['time'])));
				$views_data[][$type] = array($field=>$vdata[$j] , 'etime'=>$ctd);
				
			} // if(count==1)
			else
			{	
				if($vms)
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
			
				
					return $views_data;
				}
			}
	} // function getviewyear()

	
	
	function viewschart()
	{
		
		$this->set('ytchannel',$this->Session->read('ytchannel'));
		$this->set('dt',$this->Session->read('dt'));
		
	}
	
	function friendschart()
	{
		$this->set('ytfriends',$this->Session->read('ytfriends'));
		$this->set('dt',$this->Session->read('dt'));
	}

	function subscriberchart()
	{
		
		$this->set('ytsubscriber',$this->Session->read('ytsubscriber'));
		$this->set('dt',$this->Session->read('dt'));
		
	}

	function commentschart()
	{
		$this->set('ytcomments',$this->Session->read('ytcomments'));
		$this->set('dt',$this->Session->read('dt'));
		
	}
	
	function playschart()
	{
		
		$this->set('ytviews',$this->Session->read('ytviews'));
		$this->set('dt',$this->Session->read('dt'));
		
		
	}
	
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////

	function getAuthSubRequestUrl()
	{
		$next ="http://".$_SERVER['SERVER_NAME'].$this->base."/yts/process/";
		$scope = 'http://gdata.youtube.com';
		$secure = false;
		$session = true;
		return Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
	}



	function getAuthSubHttpClient($mmm_id,$username)
	{
		if (!isset($_SESSION['sessionToken']) && !isset($_GET['token']) ){
			$this->redirect('/yts/index/');  exit;
			return false;
		} else if (!isset($_SESSION['sessionToken']) && isset($_GET['token'])) {
			$_SESSION['sessionToken'] = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
			$sessionToken	=	$_SESSION['sessionToken'] ;
			$this->time = time();
			$this->record['Yt']['mmm_id'] = $mmm_id ;
			$this->record['Yt']['sessionToken'] = $sessionToken ;
			$this->record['Yt']['status'] = '1' ;
			$this->record['Yt']['etime'] = $this->time;

			/*$qry="insert into yt_login(mmm_id , user_id ,sessionToken , status) values
			 ('$mmm_id', '$username' , '$sessionToken', '1')";
			 $result = mysql_query($qry);
			 */
		}
		
		$httpClient = Zend_Gdata_AuthSub::getHttpClient($_SESSION['sessionToken']);
		
		return $httpClient;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////



	////////////////////////////////////  User Profile  /////////////////////////////////////////////////////////////////////////

	function getAndPrintUserProfile($userName,$yt,$mmm_id)
	{

		$userProfileEntry 	= $yt->getUserProfile($userName);
		$displayTitle 		= $userProfileEntry->title->text;

		$name 		= $userProfileEntry->getUsername() ;
		$data['name'] 	= $name ;
		/*
		 $data['title'] = $displayTitle ;
		 $data['name'] = $name ;
		 */
		$stat = $userProfileEntry->getStatistics();
		foreach($stat as $key => $val)
		{
			$data['channelView']=$val->getViewCount();
			$data['subscriberCount']=$val->getSubscriberCount();
		} // foreach($stat as $key => $val)

		return $data;

	} // 		function getAndPrintUserProfile($userName,$yt,$mmm_id)

	///////////////////////////////// End User Profile ///////////////////////////////////////////////////////////////////


	//////////////////////////////// Friends / Contacts //////////////////////////////////////////////////////////////////

	function getAndPrintContactsFeed($userName,$yt)
	{
		$contactsFeed = $yt->getContactFeed($userName);
		$Feed = $this->printContactsFeed($contactsFeed);
		return $Feed;
	}

	function printContactsFeed($contactsFeed)
	{
		$count = 0 ;
		$displayTitle = $contactsFeed->title->text;
		$feed[$count] = $displayTitle ;

		foreach ($contactsFeed as $contactsEntry)
		{
			$count ++;
			$feed[$count]=$this->printContactsEntry($contactsEntry);
		} // foreach ($contactsFeed as $contactsEntry)

		return $feed;
	} // 		function printContactsFeed($contactsFeed)

	function printContactsEntry($contactsEntry)
	{
		return $contactsEntry->title->text;
			
	}

	//////////////////////////////// Comments  /////////////////////////////////////////////////////////////////

	function printCommentFeed($commentFeed)
	{
		$commentsFeed = NULL;
		foreach ($commentFeed as $commentEntry)
		{
			$commentsFeed[]=$this->printCommentEntry($commentEntry);
		} // foreach ($commentFeed as $commentEntry)
		return $commentsFeed ;
	} // function printCommentFeed($commentFeed)


	function printCommentEntry($commentEntry)
	{
		$comment['Comment']= $commentEntry->title->text;
		$comment['text']= $commentEntry->content->text;
		$comment['Author']= $commentEntry->author[0]->name->text;
		return $comment;

	}

	//////////////////////////////////End Comments///////////////////////////////////////////////////////////////////



	////////////////////////////////// Videos///////////////////////////////////////////////////////////////////


	function printVideoEntry($videoEntry)
	{

		$vEntry['title'] = $videoEntry->getVideoTitle() ;
		$vEntry['id'] = $videoEntry->getVideoId() ;
		$vEntry['duration'] = $videoEntry->getVideoDuration() ;
		$vEntry['viewCount'] = $videoEntry->getVideoViewCount() ;
		return $vEntry;
	}  // function printVideoEntry($videoEntry)


	function ReturnVideoId($videoFeed)
	{
		$ary = NULL;
		foreach ($videoFeed as $videoEntry) {
			
			if($videoEntry->getVideoState() or $videoEntry->isVideoPrivate())
			{
				continue;
			}
			
			$ary[$videoEntry->getVideoId()]=$videoEntry->getVideoTitle();

		}
		return $ary;
	} // function ReturnVideoId($videoFeed)


	//////////////////////////////////End Videos///////////////////////////////////////////////////////////////////


} // class YtsController extends AppController {




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

function open_flash_chart_object( $width, $height, $url, $use_swfobject=true, $base='flash' )
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
