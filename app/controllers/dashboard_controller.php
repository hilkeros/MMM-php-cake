<?php
class DashboardController extends AppController {
        var $name = 'Dashboard';
	var $time = NULL;
	var $uses = array('User','Band','Mss','Msslogin','Fb','Fbpage','Fbgroup','Lfm','Lfmlistener','Yt','Ytstat','Lfmtrack','Cms','Twtuser','Twtstats','Twtdm','Twtmentions','Twitter','Feedback','Dashboard');
	var $helpers = array('Html', 'Error', 'Javascript', 'FlashChart');
	var $components = array('Email','Cookie'); //  use component email
	var $developerKey , $applicationId , $clientId , $username ;

	/**
	 * Name: beforeFilter
	 * Desc: Performs necessary steps and function calls prior to executing
	 *       any view function calls.
	 */
	function beforeFilter() {
		$this->auth();
		if($this->Session->check('id'))
		{
			$mmm_id = $this->Session->read('id');
			$band = $this->Band->findAll(array('mmm_id'=>$mmm_id,'status'=>'1'));
			$this->set('band',$band);
		}
		
		$results = $this->Cms->findAll(array('status' => '1'),array('id' , 'title'));
		$this->set('cms',$results);
	} // function beforeFilter() {

	/* Name: index
	 * Desc: MMM Band Settings
	*/

	function index()
	{
		
		$mmm_id = $this->Session->read('id');
		
		if(!empty($this->params['url']['bandid']) && ($this->params['url']['bandid']!=-1))
		{
			$band_id = $this->params['url']['bandid'];
			$this->Session->write('band_id',$band_id);
			$this->set('bandid',$band_id);
			
			$band = $this->Band->find(array('mmm_id'=>$mmm_id,'status'=>'1','band_id'=>$band_id));
			
			if (empty($band))
			{	
				$this->Session->setFlash('Invalid band id.');
				$this->redirect("/band/index/");
				
			}
			
			
		} // if(@$this->params['url']['bandid'])
		else
		{
			$band = $this->Band->find(array('mmm_id'=>$mmm_id,'status'=>'1'));
			if($band)
			{
				$band_id = $band['Band']['band_id'];
				$this->set('bandid',$band_id);
			}
			else
			{
				$band_id = -1;
				$this->set('bandid',$band_id);
			}
			
			$this->Session->write('band_id', $band_id);

		} // if(@$this->params['url']['bandid'])
		
		
		// feedback form return value
		
		
		// get and st dashboard page setting expand / collapse    'E' for expand and 'C' for collapse
		// get and set status block twitter , facebook profile and facebook page state  '1' = checked and '0' = uncheck
		
		
		$dresult = $this->Dashboard->findAll(array('user_id'=>$mmm_id));
		if($dresult)
		{
			$dsetting 	= 'C';
			$fbs_profile 	= '1';
			$fbs_page 	= '1';
			$twt_state 	= '1';
			$mss_status     = '1';
			
			foreach($dresult as $key => $val)
			{
				switch($val['Dashboard']['type'])
				{
					case 'expand' : 	  	$dsetting=$val['Dashboard']['status']; 		break;
					case 'facebook_profile' : 	$fbs_profile= $val['Dashboard']['status']; 	break;
					case 'facebook_page' : 		$fbs_page= $val['Dashboard']['status']; 	break;
					case 'twitter' : 		$twt_state=$val['Dashboard']['status']; 	break;
					case 'mss' : 			$mss_status=$val['Dashboard']['status']; 	break;
					default: break;
				}
				
			}
			
			$this->set('dsetting',$dsetting);
			$this->set('fbs_profile',$fbs_profile);
			$this->set('fbs_page',$fbs_page);
			$this->set('twt_state',$twt_state);
			$this->set('mss_status',$mss_status);
			
		}
		else{
			
			$this->set('dsetting','C');
			$this->set('fbs_profile','1');
			$this->set('fbs_page','1');
			$this->set('twt_state','1');
			$this->set('mss_status','1');
												
		}	
			
		$flag=0;
		
		/*
		 * 	MySpace Summary Page Data
		 *	statistics(id) also used to get data for MySpace Summary Page
		*/
			$mssViews = 0;
			$mssFriends = 0;
			$mssComments = 0;
			$mssPlays = 0;
			
			$mssresults = $this->Msslogin->find(array('mmm_id'=>$mmm_id,'status'=>'1','band_id'=>$band_id)); //
			if($mssresults)
			{
				$this->set('mss',1);
				$flag=1;
				$id = $mssresults['Msslogin']['mss_id'];
				
				$user_id = $mssresults['Msslogin']['user_id'];
				$this->Session->write('mssuserid',$user_id); // to get myspace home page
				
				/*  get and set link for MySpace login */
				$this->Session->write('type','views');
				$url="http://profile.myspace.com/index.cfm?fuseaction=user.viewprofile&friendid=".$mssresults['Msslogin']['user_id'];
				$this->set('id',$id);

				$mssUrl = "http://www.myspace.com/".$mssresults['Msslogin']['user_id'];
				$this->set('mssUrl',$mssUrl);
				
					/* Get total views , friends , comments , plays data & percentage on basis of weekly data */
					$qry = " select m.views , m.friends , m.comments , m.plays from mss_stat m where m.mss_id=$id and FROM_UNIXTIME(m.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND DATE_ADD(CURDATE( ),INTERVAL 1 DAY) order by etime";
					$mssResult = $this->Mss->findBySql($qry);
					
						if($mssResult)
						{
							
							$msstotalviews=0;
							$msstotalfriends=0;
							$msstotalcomments=0;
							$msstotalplays=0;
							
							$mssCount = count($mssResult);
							
							$mssViews = $mssResult[$mssCount-1]['m']['views'] ;
							$mssFriends = $mssResult[$mssCount-1]['m']['friends'];
							$mssComments = $mssResult[$mssCount-1]['m']['comments'] ;
							$mssPlays = $mssResult[$mssCount-1]['m']['plays'] ;
							
							$this->set('mssviews' 	, 	$mssViews);
							$this->set('mssfriends'	, 	$mssFriends);
							$this->set('msscomments',	$mssComments);
							$this->set('mssplays'	,	$mssPlays);
					
					
							/* Get percentage on basis of weekly data*/
							
							
							/* for myspace views */
							if(($mssCount > 6) && ($mssViews != 0))
							{
								$lastWeekViews 		= $mssResult[$mssCount-7]['m']['views'] ;
								$mssViewsPercentage 	= round((($mssViews - $lastWeekViews)/$mssViews)*100);
							} //if(($mssCount > 6) && ($mssViews != 0))
							else
							{
								$mssViewsPercentage = 0;
						
							} // if(($mssCount > 6) && ($mssViews != 0))
							$this->set('mssViewsPercentage',$mssViewsPercentage);
							
							
							
							/* for myspace friends */
							if(($mssCount > 6) && ($mssFriends != 0))
							{
								$lastWeekFriends 	= $mssResult[$mssCount-7]['m']['friends'] ;
								$mssFriendsPercentage 	= round((($mssFriends - $lastWeekFriends)/$mssFriends)*100);
							} // if(($mssCount > 6) && ($mssFriends != 0))
							else
							{
								$mssFriendsPercentage=0;
							} // if(($mssCount > 6) && ($mssFriends != 0))
							
							$this->set('mssFriendsPercentage',$mssFriendsPercentage);
							
							/* for myspace comments */
							if(($mssCount > 6) && ($mssComments != 0))
							{
								$lastWeekComments 	= $mssResult[$mssCount-7]['m']['comments'] ;
								$mssCommentsPercentage 	= round((($mssComments - $lastWeekComments)/$mssComments)*100);
							} // if(($mssCount > 6) && ($mssComments != 0))
							else
							{
								$mssCommentsPercentage=0;
							} //if(($mssCount > 6) && ($mssComments != 0))
							$this->set('mssCommentsPercentage',$mssCommentsPercentage);
							
							/* for myspace plays */
							if(($mssCount > 6) && ($mssPlays != 0))
							{
								$lastWeekPlays 		= $mssResult[$mssCount-7]['m']['plays'] ;
								$mssPlaysPercentage 	= round((($mssPlays - $lastWeekPlays)/$mssPlays)*100);
							} // if(($mssCount > 6) && ($mssPlays != 0))
							else
							{
								$mssPlaysPercentage=0;
							} // if(($mssCount > 6) && ($mssPlays != 0))
							$this->set('mssPlaysPercentage',$mssPlaysPercentage);
							
							
						} // if($mssResult)
						else
						{
							
							$this->set('mssviews' 	, 	0);
							$this->set('mssfriends'	, 	0);
							$this->set('msscomments',	0);
							$this->set('mssplays'	,	0);
					
							$this->set('mssViewsPercentage'		,0);
							$this->set('mssFriendsPercentage'	,0);
							$this->set('mssCommentsPercentage'	,0);
							$this->set('mssPlaysPercentage'		,0);
							
							
						} // if($mssResult)
			} // if($mssresults)
			else
			{
				$this->set('mss',0);
			} // if($mssresults)
			
		/*
		 * 	End MySpace Summary Page Data
		 *	
		*/	
		
		
		/*
		 * 	Last.fm Summary Page Data
		 *	
		*/
				
				$lfmResult	= 	$this->Lfm->find(array('mmm_id'=>$mmm_id , 'band_id'=>$band_id,'status'=>1));
				if($lfmResult)
				{
					$flag=1;
					$this->set('lfm',1);
					$lfm_m_id = $lfmResult['Lfm']['lfm_m_id'];
					
					$lfmsUrl = $lfmResult['Lfm']['url'];
					$this->set('lfmsUrl',$lfmsUrl);
					/* listeners percentage on basis of weekly data*/
					
					$qry = " select l.listeners from lfm_listeners l where l.lfm_m_id=$lfm_m_id and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND DATE_ADD(CURDATE( ),INTERVAL 1 DAY) order by etime";
					$listenerResult = $this->Lfmlistener->findBySql($qry);
						if($listenerResult)
						{
							$lfmtotallisteners=0;
							$lfmCount = count($listenerResult);
							$totalListener = $listenerResult[$lfmCount-1]['l']['listeners'];
							$this->set('listener',$totalListener);
							
							/* for last.fmn listeners*/
							if(($lfmCount > 6) && ($totalListener != 0))
							{
								$lastWeeklistener 	= $listenerResult[$lfmCount-7]['l']['listeners'] ;
								$lfmPercentage 		= round((($totalListener - $lastWeeklistener)/$totalListener)*100);
							} //if(($lfmCount > 1) && ($lfmlistenerstoatl != 0))
							else
							{
								$lfmPercentage = 0;
							} // if(($lfmCount > 6) && ($totalListener != 0))
							$this->set('lfmPercentage',$lfmPercentage);
							

						} // if($listenerResult)
						else
						{
							$this->set('lfmPercentage',0);
							$this->set('listener',0);
							
						} // if($listenerResult)
						
				
			
			
					$qry = "select sum(t.playcount) playcount  from lfm_top_tracks t , lfm_music m where t.lfm_m_id = m.lfm_m_id and m.lfm_m_id= $lfm_m_id and FROM_UNIXTIME(t.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND DATE_ADD(CURDATE( ),INTERVAL 1 DAY) group by t.etime";
					$tracksResult = $this->Lfmtrack->findBySql($qry);
					$tracks = 0;
					$lfmTotalPlays = 0;
					
						if($tracksResult)
						{
							$playsCount = count($tracksResult);
							$totalPlays = $tracksResult[$playsCount-1][0]['playcount'];
							
							$this->set('plays',$totalPlays);
							
							
							/* for last.fmn tracks plays*/
							if(($playsCount > 6) && ($totalPlays != 0))
							{
								$lastWeekPlays 		= $tracksResult[$playsCount-7][0]['playcount'] ;
								$lfmPlaysPercentage 	= round((($totalPlays - $lastWeekPlays)/$totalPlays)*100);
							} //if(($playsCount > 6) && ($totalPlays != 0))
							else
							{
								$lfmPlaysPercentage = 0;
							} // iif(($playsCount > 6) && ($totalPlays != 0))
							$this->set('lfmPlaysPercentage',$lfmPlaysPercentage);
							
						} // if($tracksResult)
						else
						{
							$this->set('plays',0);
							$this->set('lfmPlaysPercentage',0);
						} // if($tracksResult)
					
					$this->set('lfm_m_id',$lfm_m_id);
			
				} // if($lfmResult)
				else
				{
					$this->set('lfm',0);
				}// if($lfmResult)	
		/*
		 * 	End Last.fm Summary Page Data
		 *	
		*/
		
		
		/*
		 * 	Youtube Summary Page Data
		 *	
		*/
		
				$ytViews = 0;
				$ytFriends = 0;
				$ytSubscribers = 0;
				
				$ytResult	= 	$this->Yt->find(array('mmm_id'=>$mmm_id , 'band_id'=>$band_id,'status'=>1));
				if($ytResult)
				{
					$flag=1;
					$this->set('yt',1);
					$yt_id = $ytResult['Yt']['yt_id'];
					$this->set('yt_id',$yt_id);
					
					$ytsUrl = "http://www.youtube.com/".$ytResult['Yt']['user_id'];
					$this->set('ytsUrl',$ytsUrl);
					
					$qry = " select y.views , y.subscriber , y.friends from yt_stat y where y.yt_id = $yt_id and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND DATE_ADD(CURDATE( ),INTERVAL 1 DAY) order by etime";
					$youtubeResult = $this->Ytstat->findBySql($qry);
						if($youtubeResult)
						{
						/* Youtube views percentage*/
							$yttotalviews=0;
							$yttotalfriends=0;
							$yttotalsubscribers=0;
							
							$ytCount = count($youtubeResult);
							
							$ytViews = 		$youtubeResult[$ytCount-1]['y']['views'];
							$ytFriends = 		$youtubeResult[$ytCount-1]['y']['friends'];
							$ytSubscribers = 	$youtubeResult[$ytCount-1]['y']['subscriber'];
							
							$this->set('ytViews',$ytViews);
							$this->set('ytFriends',$ytFriends);
							$this->set('ytSubscribers',$ytSubscribers);
							
										
							/* for youtube views*/
							if(($ytCount > 6) && ($ytViews != 0))
							{
								$lastWeekViews 		= $youtubeResult[$ytCount-7]['y']['views'] ;
								$ytViewsPercentage 	= round((($ytViews - $lastWeekViews)/$ytViews)*100);
							} //if(($ytCount > 6) && ($ytViews != 0))
							else
							{
								$ytViewsPercentage = 0;
							} // if(($ytCount > 6) && ($ytViews != 0))
							$this->set('ytViewsPercentage',$ytViewsPercentage);
						
						
							/* for youtube friends*/
							if(($ytCount > 6) && ($ytFriends != 0))
							{
								$lastWeekFriends  	= $youtubeResult[$ytCount-7]['y']['friends'] ;
								$ytFriendsPercentage 	= round((($ytFriends - $lastWeekFriends)/$ytFriends)*100);
							} //if(($ytCount > 6) && ($ytFriends != 0))
							else
							{
								$ytFriendsPercentage = 0;
							} // if(($ytCount > 6) && ($ytFriends != 0))
							$this->set('ytFriendsPercentage',$ytFriendsPercentage);
							
							/* for youtube subscribers*/
							if(($ytCount > 6) && ($ytSubscribers != 0))
							{
								$lastWeekSubscribers  		= $youtubeResult[$ytCount-7]['y']['subscriber'] ;
								$ytSubscribersPercentage 	= round((($ytSubscribers - $lastWeekSubscribers)/$ytSubscribers)*100);
							} //if(($ytCount > 6) && ($ytSubscribers != 0))
							else
							{
								$ytSubscribersPercentage = 0;
							} // if(($ytCount > 6) && ($ytSubscribers != 0))
							$this->set('ytSubscribersPercentage',$ytSubscribersPercentage);
							
	
						} // if($youtubeResult)
						else
						{
							
							$this->set('ytViews',0);
							$this->set('ytFriends',0);
							$this->set('ytSubscribers',0);
							
							$this->set('ytViewsPercentage',0);
							$this->set('ytFriendsPercentage',0);
							$this->set('ytSubscribersPercentage',0);
						
							
						} // if($youtubeResult)
						
						
					$qry = "select sum(y.views) plays  from yt_comments_stat y , yt_login l where y.yt_id = l.yt_id and l.yt_id= $yt_id and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND DATE_ADD(CURDATE( ),INTERVAL 1 DAY) group by y.etime";
					$ytPlaysResult = $this->Ytstat->findBySql($qry);
					$plays = 0;
					$ytTotalPlays = 0;
					
						if($ytPlaysResult)
						{
							$playsCount = count($ytPlaysResult);
							$totalPlays = $ytPlaysResult[$playsCount-1][0]['plays'];
							
							$this->set('ytPlays',$totalPlays);
							
							
							/* for last.fmn tracks plays*/
							if(($playsCount > 6) && ($totalPlays != 0))
							{
								$lastWeekPlays 		= $ytPlaysResult[$playsCount-7][0]['plays'] ;
								$ytPlaysPercentage 	= round((($totalPlays - $lastWeekPlays)/$totalPlays)*100);
							} //if(($playsCount > 6) && ($totalPlays != 0))
							else
							{
								$ytPlaysPercentage = 0;
							} // iif(($playsCount > 6) && ($totalPlays != 0))
							$this->set('ytPlaysPercentage',$ytPlaysPercentage);
							
						} // if($ytPlaysResult)
						else
						{
							$this->set('ytPlays',0);
							$this->set('ytPlaysPercentage',0);
						} // if($ytPlaysResult)
			
				} // if($ytResult)
				else
				{
					$this->set('yt',0);
				} // if($ytResult)
			
		/*
		 * 	End Youtube Summary Page Data
		 *	
		*/
		
		/*
		 * 	Facebook Summary Page & group Data
		 *	
		*/
			$fancount 	=0;
			$membercount	=0;
			$fbsResult	= 	$this->Fb->find(array('mmm_id'=>$mmm_id , 'band_id'=>$band_id,'status'=>1));
			
			if($fbsResult)
			{
				$flag=1;
				$this->set('fbs',1);
				
				$login_id 	= $fbsResult['Fb']['login_id'];
				$pname 		= addslashes($fbsResult['Fb']['page']);
				$gname		= addslashes($fbsResult['Fb']['group']);
				
				$fbsUrl ="http://www.facebook.com/".$pname;
				$this->set('fbsUrl',$fbsUrl);
				
				
				if(!empty($pname))
				{
					
					
					$qry = " select p.fan_count , page_url  from fb_pages p where p.login_id = $login_id and p.name='$pname' and FROM_UNIXTIME(p.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND DATE_ADD(CURDATE( ),INTERVAL 1 DAY) order by etime";
					$fbsPageResult = $this->Fbpage->findBySql($qry);
					
					if($fbsPageResult)
					{
						$fbstotalfans=0;
						$fbspageCount = count($fbsPageResult);
						
						$page_url = $fbsPageResult[$fbspageCount-1]['p']['page_url'] ;
						$this->set('fbsUrl',$page_url);
																			
						$fbstotalfans = $fbsPageResult[$fbspageCount-1]['p']['fan_count'] ;
						$this->set('fancount',$fbstotalfans);
						
						/* for Facebook pages*/
						if(($fbspageCount > 6) && ($fbstotalfans != 0))
						{
							$lastWeekFans  = $fbsPageResult[$fbspageCount-7]['p']['fan_count'] ;
							$fbFansPercentage = round((($fbstotalfans - $lastWeekFans)/$fbstotalfans)*100);
						} //if(($fbspageCount > 6) && ($fbstotalfans != 0))
						else
						{
							$fbFansPercentage = 0;
						} // if(($fbspageCount > 6) && ($fbstotalfans != 0))

						$this->set('fbFansPercentage',$fbFansPercentage);
					
					} // if($fbsPageResult)
					else
					{
						$this->set('fancount',0);
						$this->set('fbFansPercentage',0);
						$this->set('fbsUrl','');
					
					} // if($fbsPageResult)
							
						
					
				} // if(!empty($pname))
				else
				{
					$this->set('fancount','none');
					$this->set('fbFansPercentage',0);
					$this->set('fbsUrl','');
							
				} // if(!empty($pname))
				
			
				if(!empty($gname))
				{
					$qry = " select g.member from fb_group g where g.login_id = $login_id and g.name='$gname' and FROM_UNIXTIME(g.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND DATE_ADD(CURDATE( ),INTERVAL 1 DAY) order by etime";
					$fbsGroupResult = $this->Fbgroup->findBySql($qry);
					if($fbsGroupResult)
					{
						$fbstotalmembers=0;
						$fbsgroupCount = count($fbsGroupResult);
						
						$fbstotalmembers = $fbsGroupResult[$fbsgroupCount-1]['g']['member'];
						$this->set('member',$fbstotalmembers);
														
						/* for Facebook Group*/
						if(($fbsgroupCount > 6) && ($fbstotalmembers != 0))
						{
							$lastWeekMembers  = $fbsGroupResult[$fbsgroupCount-7]['g']['member'] ;
							$fbMembersPercentage = round((($fbstotalmembers - $lastWeekMembers)/$fbstotalmembers)*100);
						} //if(($fbsgroupCount > 6) && ($fbstotalmembers != 0))
						else
						{
							$fbMembersPercentage = 0;
						} // if(($fbsgroupCount > 6) && ($fbstotalmembers != 0))

						$this->set('fbMembersPercentage',$fbMembersPercentage);
					 
					} // if($fbsGroupResult)
					else
					{
						$this->set('member',0);
						$this->set('fbMembersPercentage',0);
					
					} // if($fbsGroupResult)
				} // if(!empty($gname))
				else
				{
					$this->set('member','none');
					$this->set('fbMembersPercentage',0);
				} // if(!empty($gname))
				
				$this->set('login_id',$login_id);
				
			} // if($fbsResult)
			else
			{
				$this->set('fbs',0);
			} // if($fbsResult)
			
		/* end facebook */
		
		
		/*
		 * 	Twitter Summary Page Data
		 *	
		*/
		
				$twtFollower = 0;
				$twtFollowing = 0;
				$twtTweets = 0;
				$twtFavorites = 0;
				$twtTime = null;
				
				
				// $this->Twtuser->find(array('mmm_id'=>$mmm_id , 'band_id'=>$band_id,'status'=>1));
				$qry	=  "select t.user_id , t.screen_name from twt_login t , twt_user u where u.user_id = t.user_id and u.mmm_id=$mmm_id and u.band_id=$band_id and u.status=1";
				$twtResult = $this->Twtuser->query($qry);
				
				if($twtResult)
				{
					
					
					$flag=1;
					$this->set('twt',1);
					$twt_user_id = $twtResult[0]['t']['user_id'];
					$this->set('twt_user_id',$twt_user_id);
					
					$screen_name = $twtResult[0]['t']['screen_name'];
					$twtUrl = "http://twitter.com/$screen_name";
					$this->set('twtUrl',$twtUrl);
					
					$qry = " select follower , following , tweets , favorites from twt_stats where user_id = $twt_user_id and FROM_UNIXTIME(etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND DATE_ADD(CURDATE( ),INTERVAL 1 DAY) order by etime";
					$twtStatResult = $this->Twtstats->findBySql($qry);

						if($twtStatResult)
						{
						/* Youtube views percentage*/
							$twttotalfollower=0;
							$twttotalfollowing=0;
							$twttotaltweets=0;
							$twttotalfavorites=0;
							
							
							$twtCount = count($twtStatResult);
							
							$twtFollower 	= 	$twtStatResult[$twtCount-1]['twt_stats']['follower'];
							$twtFollowing	= 	$twtStatResult[$twtCount-1]['twt_stats']['following'];
							$twtTweets 	= 	$twtStatResult[$twtCount-1]['twt_stats']['tweets'];
							$twtFavorites	=	$twtStatResult[$twtCount-1]['twt_stats']['favorites'];
							
							$this->set('twtFollower',$twtFollower);
							$this->set('twtFollowing',$twtFollowing);
							$this->set('twtTweets',$twtTweets);
							$this->set('twtFavorites',$twtFavorites);
							
										
							/* for twitter followers*/
							if(($twtCount > 6) && ($twtFollower != 0))
							{
								$lastWeekFollower 		= $twtStatResult[$twtCount-7]['twt_stats']['follower'] ;
								$twtFollowerPercentage 		= round((($twtFollower - $lastWeekFollower)/$twtFollower)*100);
							} //if(($ytCount > 6) && ($ytViews != 0))
							else
							{
								$twtFollowerPercentage = 0;
							} // if(($ytCount > 6) && ($ytViews != 0))
							$this->set('twtFollowerPercentage',$twtFollowerPercentage);
							
							
							/* for twitter following*/
							if(($twtCount > 6) && ($twtFollowing != 0))
							{
								$lastWeekFollowing 		= $twtStatResult[$twtCount-7]['twt_stats']['following'] ;
								$twtFollowingPercentage		= round((($twtFollowing - $lastWeekFollowing)/$twtFollowing)*100);
							} //if(($ytCount > 6) && ($ytViews != 0))
							else
							{
								$twtFollowingPercentage = 0;
							} // if(($ytCount > 6) && ($ytViews != 0))
							$this->set('twtFollowingPercentage',$twtFollowingPercentage);
							
							/* for twitter tweets*/
							if(($twtCount > 6) && ($twtTweets != 0))
							{
								$lastWeekTweets			= $twtStatResult[$twtCount-7]['twt_stats']['tweets'] ;
								$twtTweetsPercentage		= round((($twtTweets - $lastWeekTweets)/$twtTweets)*100);
							} //if(($ytCount > 6) && ($ytViews != 0))
							else
							{
								$twtTweetsPercentage = 0;
							} // if(($ytCount > 6) && ($ytViews != 0))
							$this->set('twtTweetsPercentage',$twtTweetsPercentage);
							
							
							/* for twitter favorites*/
							if(($twtCount > 6) && ($twtFavorites != 0))
							{
								$lastWeekFavorites			= $twtStatResult[$twtCount-7]['twt_stats']['favorites'] ;
								$twtFavoritesPercentage		= round((($twtFavorites - $lastWeekFavorites)/$twtFavorites)*100);
							} //if(($ytCount > 6) && ($ytViews != 0))
							else
							{
								$twtFavoritesPercentage = 0;
							} // if(($ytCount > 6) && ($ytViews != 0))
							$this->set('twtFavoritesPercentage',$twtFavoritesPercentage);
						
						} // if($youtubeResult)
						else
						{
							
							$this->set('twtFollower',0);
							$this->set('twtFollowing',0);
							$this->set('twtTweets',0);
							$this->set('twtFavorites',0);
							
							$this->set('twtFollowerPercentage',0);
							$this->set('twtFollowingPercentage',0);
							$this->set('twtTweetsPercentage',0);
							$this->set('twtFavoritesPercentage',0);
						
							
						} // if($youtubeResult)
	
					
					
					
				} // if($twtResult)
				else
				{
					$this->set('twt',0);
					$this->set('twt_user_id',-1);
				} // if($twtResult)
			
		/*
		 * 	End Twitter Summary Page Data
		 *	
		*/
		
		
			$useragent = $_SERVER['HTTP_USER_AGENT'];
			$col = 49;
			$size = 46;
			$number_size = 2;
			
			if (preg_match('|MSIE ([0-9].[0-9]{1,2})|',$useragent,$matched)) {
				$col = 51;
				$size = 71;
				
			} elseif (preg_match( '|Opera ([0-9].[0-9]{1,2})|',$useragent,$matched)) {
				$col = 49;
				$size = 46;
			} elseif(preg_match('|Firefox/([0-9\.]+)|',$useragent,$matched)) {
				$col = 49;
				$size = 46;
			} elseif(preg_match('|Safari/([0-9\.]+)|',$useragent,$matched)) {
				if($matched[1]=='525.28.1')
				{
					$col = 51;
					$size = 48;
					$number_size = 3;
				}
				else
				{
					$col = 72;
					$size = 71;
					
				}
				
			}
			
			$this->set('size',$size);  //  shortren input box size.
			$this->set('col',$col);  // update status textarea cols.
			$this->set('number_size',$number_size);  // 140 counter box size
			
	} // function index()
	
	
	/* Name: statistics
	 Desc: Show MySpace Statistics
	 Retrun : void
	 */
	function statistics($id)
	{
		//$this->layout='stats';
		//$mmm_id = $this->Session->read('email') ;
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
			//		$results = $this->Mss->find(array('mmm_id'=>$this->Session->read('email'),'mss_id'=>$this->params['url']['id'])); //
			if($results)
			{
				return $results;
			} // if($results)
			
		
		} // if(!empty($this->params['url']['id']) and !empty($this->params['url']['etime']))
	
	} // 	function statistics()
	
	
	/*
	 name : feeback
	 description : send feedback mail
	*/
	function feedback()
	{
		
		if(!empty($this->data['Users']['feedback']))
		{
					
			$band_id = $this->Session->read('band_id');
			
			
			if($this->Session->check('user'))
			{
				$name = $this->Session->read('user');
				$this->set('user',$this->Session->read('user'));
			}
			else
			{
				$this->set('user','User');
				$name = 'User';
			}
			 
			
			$os 	= $this->data['Users']['os'];
			$brws	= $this->data['Users']['browser'];
			
			$data = "Operating system : $os <br>";
			$data .= "Browser : $brws <br>";
			$data .= $this->data['Users']['feedback'] ;
			
			$mmm_id = $this->Session->read('id');
			$user = $this->User->find(array('id'=>$mmm_id));
			$from_mail = $user['User']['email'];
			
			$feedback['Feedback']['name'] = 	$name;
			$feedback['Feedback']['os'] = 		$os;
			$feedback['Feedback']['browser'] = 	$brws;
			$feedback['Feedback']['feedback'] =	$data;
			$feedback['Feedback']['tdate'] =	time();
			$feedback['Feedback']['email'] =	$from_mail;
			$feedback['Feedback']['mmm_id'] =	$mmm_id ;
			
			$data .= "<br> $from_mail";
			
			$this->set('data', $data);
			$this->Email->to = 'hilkeros@gmail.com';
			$this->Email->subject = 'Feedback';
			$result = $this->Email->send('feedback','feedback@mmmotion.com');
			$this->Feedback->save($feedback); // save feedback in database.
			$this->Session->setFlash('Thank you very much for your feedback. We will read it with the greatest care.');
			header("Location:".$_SERVER['HTTP_REFERER']);
			exit;
		
		} // if(!empty($this->data['Users']['feedback']))
		
		$bandid= $this->data['Users']['bandid'] ;
		$this->set('bandid',$bandid);
		$this->layout="stats";
			
		$browser["Firefox"]		="Firefox";
		$browser["Safari"]		="Safari";
		$browser["Google Chrome"]	="Google Chrome";
		$browser["Opera"]		="Opera";
		$browser["Internet Explorer"]	="Internet Explorer";
		$browser["Netscape"]		="Netscape";
		$browser["Other"]		="Other";
		
		$this->set('browser',$browser);
	
	}
	
	/*
	 name : search
	 description : google search page
	*/
	function search()
	{
		$this->set('dashboard',true);
		if($this->Session->check('band_id'))
		{
			$this->set('bandid',$this->Session->read('band_id'));
		}
		elseif ($this->Cookie->valid('bandid'))
		{
			$bandid=$this->Cookie->read('bandid');
			
			$this->set('bandid',$bandid['bandid']);
		}
		
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
	 * name : updateExpand
	 * description : remember expand / collapse status
	 */
	function updateExpand() {
		
		if(!empty($this->params['url']['id']))
		{	
			$mmm_id = $this->Session->read('id');
			$flag = "Collapse";
			$flag = $this->params['url']['id'] ;
			
			$result = $this->Dashboard->find(array('user_id'=>$mmm_id ,'type'=>'expand'));
			if($result)
			{
				if($flag=='Expand')
				{
					$result['Dashboard']['status'] = 'E' ;
				} // if($flag=='Expand')
				else
				{
					$result['Dashboard']['status'] = 'C' ;
				} // if($flag=='Expand')
			} // if($result)
			else
			{
					$result['Dashboard']['type'] = 'expand' ;
					$result['Dashboard']['status'] = 'E' ;
					$result['Dashboard']['user_id'] = $mmm_id ;
			} // if($result)
			$this->Dashboard->save($result);
			exit;
		} // if(!empty($this->params['url']['id']))
		exit;
	} // function updateExpand()
	
	/*
	 * name : updateTicket
	 * description : remember twitter , facebook checkbox status
	 */
	function updateTicket() {
		
			
		if(!empty($this->params['url']['id']) and !empty($this->params['url']['name']))
		{
			$flag = $this->params['url']['id'] ;
			$name = $this->params['url']['name'] ;
			$mmm_id = $this->Session->read('id');
						
			
			$result = $this->Dashboard->find(array('user_id'=>$mmm_id ,'type'=>$name));
			if($result)
			{
				if($flag=='true')
				{
					$result['Dashboard']['status'] = '1' ;
				} // if($flag=='true')
				else
				{
					$result['Dashboard']['status'] = '0' ;
				} // if($flag=='true')
			} // if($result)
			else
			{
					$result['Dashboard']['type'] = $name ;
					$result['Dashboard']['status'] = '0' ;
					$result['Dashboard']['user_id'] = $mmm_id ;
			} // if($result)
			
			$this->Dashboard->save($result);
			exit;
		} // if(!empty($this->params['url']['id']) and !empty($this->params['url']['name']))
		exit;
	} // function updateTicket()
	
} // class DashboardController extends AppController {
?>
