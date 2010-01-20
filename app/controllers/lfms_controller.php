<?php
vendor('api/lastfmapi');
class LfmsController extends AppController {

	var $name = 'Lfms';
	var $etime = NULL;
	var $uses = array('User','Lfm','Lfmtrack','Lfmalbum','Lfmlistener');
	var $helpers = array('Html', 'Error', 'Javascript' , 'FlashChart');
	var $components = array('Cookie'); //  use component email
	var $developerKey , $applicationId , $clientId , $username ;


	/**
	 * Name: beforeFilter
	 * Desc: Performs necessary steps and function calls prior to executing
	 *       any view function calls.
	 */
	function beforeFilter() {
		$this->auth();

	} // function beforeFilter() {

	/* Name: index
	 * Desc: Display the index page.
	 */

	function index()
	{

	} // 	function index()
	
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
			$this->Session->write('lfm_m_id',$this->params['url']['id']);
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
			if($this->Session->check('lfm_m_id'))
			{
			
				$lastdate = $this->params['url']['date'];
				$opt = $this->params['url']['opt'];
				$id=trim($this->Session->read('lfm_m_id'));
				
				
				
				$this->getGraphData($id,$lastdate,$opt,'listeners','','listeners'); // Get & set listeners data for graphs , params( id , date , option , field, track or album name , type)
				if($this->params['url']['type']=='listener')
				{
					$this->Session->write('type','listener');
				}
				
				if($this->Session->check('top_album'))
				{
				   $this->Session->write('top_album',$this->Session->read('top_album'));
				}
				
				if($this->Session->check('tracks'))
				{
				   $this->Session->write('top_tracks',$this->Session->read('top_tracks'));
				}
				/* if album trend chart required
				 if($this->params['url']['type']=='albumtrend')
				{
					$name = $this->params['url']['name'];
					$this->getGraphData($id,$lastdate,$opt,'playcount',$name,'albumtrend'); // Get & set listeners data for graphs , params( id , date , option , field, track or album name , type)
					$this->Session->write('type','albumtrend');
				}
				*/
				
													
				$mmm_id = $this->Session->read('id');
				if(@$this->params['url']['ids'])
				{
					$lfm_id = $this->params['url']['ids'];
					$titles = $this->params['url']['title'];
					$color = $this->params['url']['color'];
					
					
				}
				else
				{
					$lfm_id ='0';
				}
				
				$this->set('lfm_id',$lfm_id);
				$track_lfm = $this->Session->read('track_lfm');
				$this->set('track_lfm',$track_lfm);
				$this->set('id',$id);
				
				$totalplays 	= NULL;
				$lfmlistener 	= NULL;
				
				foreach($track_lfm as $key => $val)
				{
					$lfm_ids 	= $val['l']['toptrack_id'];
					$title		= $val['l']['name'];
					
					if($key < 5)
					{
						if($lfm_id=='all') 
						{
							$_SESSION[$title]	="1";
						}
						
						
						$lfmplays[$title] 	= $this->getGraphData($id,$lastdate,$opt,'playcount',addslashes($title),'plays');		
														
						foreach($lfmplays[$title] as $mskey => $msval)
						{	
							@$totalplays[$mskey]+= $msval;	
						}
					}
					else
					{
						$lfmtotalplays 	= $this->getGraphData($id,$lastdate,$opt,'playcount',addslashes($title),'totalplays');
																		
						// get & set total plays statistics
						foreach($lfmtotalplays as $mskey => $msval)
						{	
							@$totalplays[$mskey]+= $msval;	
						} // foreach($lfmtotalplays as $mskey => $msval)
							
					}
				}
				
				$lfmplays['tplay'] = $totalplays;
				$this->set('lfmplays',$lfmplays);
				$this->Session->write('lfmplays',$lfmplays);
				
						if($lfm_id=='all')
						{
							if($this->params['url']['type']=='plays')
							{
								$this->Session->write('type','plays');
								$this->Session->write('tplay',"1");
							}
							
						}
						elseif($lfm_id=='none')
						{
							
														
							foreach($track_lfm as $key => $val)	// All plays toggle off
							{
								
								$title= $val['l']['name'];
								if($key <  5 )
								{
									if($this->params['url']['type']=='plays')
									{
									$_SESSION[$title]="0";
									}
								}
							}
									
									
							$this->Session->write('lastdate',$lastdate);
							$this->Session->write('opt',$opt);
													
															
							if($this->params['url']['type']=='plays')
							{
								$totalplays 	= NULL;
								$lfmplays 	= NULL;
								// video play views
								$this->Session->write('tplay',"0");  // Total plays toggle off
								$this->set('lfmplays',$lfmplays);
								$this->Session->write('lfmplays',$lfmplays);
								$this->Session->write('type','plays');
								
							}
							
						}
						elseif($lfm_id=='tplay')
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
								} // if($this->params['url']['type']=='views')
								
						}
						elseif($lfm_id=="0")
						{
								
							if($this->params['url']['type']=='plays')
							{
								$this->Session->write('type','plays');
							}
						}
						elseif(!empty($lfm_id))
						{
							
							
							if($this->params['url']['type']=='plays')
							{
								
								$this->Session->write('type','plays');
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
								
						}
					
			} // if($this->Session->check('mssid'))
			
		} // if(!empty($this->params['url']['date']) and !empty($this->params['url']['opt']))
		else   
		{
			$this->set('flag',0);
			if($this->Session->check('lfm_m_id'))
			{
				$mmm_id = $this->Session->read('id');
				$lfm_m_id = $this->Session->read('lfm_m_id');
				$results = $this->Lfm->find(array('mmm_id'=>$mmm_id,'lfm_m_id'=>$lfm_m_id)); //
				
				if($results)
				{
					
					$id = $results['Lfm']['lfm_m_id'];
					$this->Session->write('lfm_m_id',$id); // to get myspace home page
					
								
					$lfmlistener= $this->getGraphData($id,'w','d','listeners','','listeners'); // Get & set listeners data for graphs , params( id , date , option , field, track or album name , type)
					$this->Session->write('type','listener');   // by default channel views tab selected
					
				
					// Top Album
					$qry = "select a.name , a.playcount  from lfm_top_album a , lfm_music m
						where a.lfm_m_id=$id and a.etime=m.executetime and a.lfm_m_id=m.lfm_m_id and m.mmm_id='$mmm_id'
						and a.rank < 6";
	
					$top_album = $this->Lfmalbum->findBySql($qry);
					if($top_album)
					{
						$this->Session->write('top_album',$top_album);
						$this->set('top_album',$top_album);
					} // if($top_album)
	
	
					// Top Tracks
					$qry = "select t.name , t.playcount  from lfm_top_tracks t , lfm_music m
						where t.lfm_m_id=$id and t.etime=m.executetime and t.lfm_m_id=m.lfm_m_id and m.mmm_id='$mmm_id'
						and t.rank < 6";
	
					$top_tracks = $this->Lfmtrack->findBySql($qry);
					if($top_tracks)
					{
						$this->Session->write('top_tracks',$top_tracks);
						$this->set('top_tracks',$top_tracks);
					} // if($top_album)
					
	
				} // if($results)
				else
				{
					if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
						
					$this->Session->setFlash('No Last.fm data found.');
					$this->set('flag',1);   // if no data found
					
					
				} // if($results)
				

				
				$this->set('lfm_id','all');
								
				$qry = "select l.toptrack_id , l.name from lfm_top_tracks l , lfm_music m 
								where
								l.lfm_m_id=$id and l.etime = m.executetime and l.lfm_m_id = m.lfm_m_id and m.mmm_id = '$mmm_id' 
								group by l.name , l.toptrack_id
								order by sum(l.playcount) desc
								";
				$track_lfm = $this->Lfmtrack->findBySql($qry);
				
				if($track_lfm)
				{
					$this->Session->write('track_lfm',$track_lfm);
					$this->set('track_lfm',$track_lfm);
					
					$this->Session->write('color','#FF6600');
					$this->set('id',$id);
					$lfmlistener = NULL;
					
					$this->Session->write('tplay',"1");  //  total plays toggle on
					
					foreach($track_lfm as $key => $val)
					{
						
						$title= $val['l']['name'];
						if($key<5)
						{
							$lfmplays[$title] 	= $this->getGraphData($id,'w','d','playcount',addslashes($title),'plays');
							$_SESSION[$title]="1"; // All plays toggle on
							//$this->Session->write("$title","1");   // All plays toggle on
												
							// get & set total plays statistics
							foreach($lfmplays[$title] as $mskey => $msval)
							{	
								@$totalplays[$mskey]+= $msval;	
							} // foreach($msplays[$title] as $mskey => $msval)
						}
						else
						{
							$lfmtotalplays 	= $this->getGraphData($id,'w','d','playcount',addslashes($title),'totalplays');
																		
							// get & set total plays statistics
							foreach($lfmtotalplays as $mskey => $msval)
							{	
								@$totalplays[$mskey]+= $msval;	
							} // foreach($msplays[$title] as $mskey => $msval)
						}
				
						
						
					} // foreach($track_lfm as $key => $val)
						
						$lfmplays['tplay'] = $totalplays ;
						$this->set('lfmplays',$lfmplays);
						$this->Session->write('lfmplays',$lfmplays);
						
												
						
				} // if($track_lfm)
				else
				{
					if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
					$this->Session->setFlash('No Last.fm data found.');
					$this->set('flag',1);
					
					// if no data found
				} // if($track_lfm)
			} //if($this->Session->check('band_id'))
			else
			{
					if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
					$this->Session->setFlash('Session expired.');
					$this->set('flag',1);  // session expired
			} //if($this->Session->check('band_id'))
			
				
		}
			
		$this->layout="stats";
	} // function chart()



	/* Name: updategroup
	 Desc: update & active last.fm group
	 Retrun : void
	*/
	function updategroup()
	{
		if($this->data)
		{
			$mmm_id = $this->Session->read('id') ;
			if($this->Cookie->valid('bandid'))
			{
			
				$id = $this->data['Lfm']['group'];
				
					$band_id=$this->Cookie->read('bandid');
					$band_id = $band_id['bandid'];
					
					$qry = " update lfm_music
						 set status=0
						 where mmm_id='$mmm_id'
						 and band_id  = $band_id ";
						
					$this->Lfm->query($qry);
					
					$qry;
					if($id!="none")
					{
					$qry = " update lfm_music
						 set status=1
						 where mmm_id='$mmm_id'
						 and band_id  = $band_id
						 and lfm_m_id=$id";
					
					$this->Lfm->query($qry);
					}
					
					$this->Session->setFlash('Last.fm data updated succesfully.');
					$this->redirect('/band/manage/');
					
				
				
				
			} // if($this->Cookie->valid('bandid'))
			else
			{
				$this->Session->setFlash('Please select a band.');
				$this->redirect('/band/index/');
			} // if($this->Cookie->valid('bandid'))
		} // if($this->data)
		else
		{
				$this->Session->setFlash('Invalid data.');
				$this->redirect('/band/manage/');
					
		} // if($this->data)
		
	}

	/* Name: addgroup
	 Desc: Adding groups for last.fm
	 Retrun : void
	 set : get and insert all last.fm statistics with getMusic function
	 */
	function addgroup()
	{
		if($this->data)
		{
			if($this->Cookie->valid('bandid'))
			{
				$band  =$this->Cookie->read('flag'); // myspace called from wizard or setting manage
				$flag= $band["flag"];
				
				if(!empty($this->data['Lfm']['music_group']))
				{
					$this->data['Lfm']['status']='1';
					$this->data['Lfm']['mmm_id']=	$this->Session->read('id');
					
					$band  =$this->Cookie->read('bandid'); 
					$band_id = $band['bandid']; // get band_id value from cookie
		
					$record = $this->Lfm->find(array('mmm_id' => $this->Session->read('id') , 'music_group' => $this->data['Lfm']['music_group'] ,'band_id'=>$band_id));
		
					if(!$record)
					{
						$mmm_id = $this->Session->read('id');
						// set all user music group status = 0
						$qry = " update lfm_music
							set status=0
							where mmm_id='$mmm_id'
							and band_id  = $band_id ";
						$this->Lfm->query($qry);
						
						$this->data['Lfm']['band_id']=	$band_id;
						if($this->Lfm->save($this->data))
						{
							$id=$this->Lfm->getLastInsertId();
							$getMs = $this->getMusic($this->data['Lfm']['music_group'],$id,$band_id);
							
								if($flag=='b')
								{
									$this->Session->setFlash('Last.fm information has been processed correctly.');
									$this->redirect('/band/twitter/');
								}	
								else
								{	
									$this->redirect('/band/manage/');
								}
							
							
						} // 	if($this->Lfm->save($this->data))
					} // 	if(!$record)
					else
					{
						$this->Session->setFlash('The data about this artist are already present in our database.');
							if($flag=='b')
							{
								$this->redirect('/band/lastfm/');
							}	
							else
							{	
								$this->redirect('/band/manage/');
							}
					} // 	if(!$record)
				}
				else
				{
						$this->Session->setFlash('Artist name required.');
							if($flag=='b')
							{
								$this->redirect('/band/lastfm/');
							}	
							else
							{	
								$this->redirect('/band/manage/');
							}
					
				}
			} //if($this->Cookie->valid('bandid'))
			else
			{
				$this->Session->setFlash('Please select a band.');
				$this->redirect('/band/index/');
			}//if($this->Cookie->valid('bandid'))

		} // if($this->data)
	} // function addgroup()

	/* Name: grouplist
	 Desc: Display last.fm user submitted groups
	 Retrun : void
	 set : set lfm_stat and show graphs for groups & listerners data
	 */
	function grouplist()
	{

		$results = $this->Lfm->findAll(array('mmm_id'=>$this->Session->read('id'),'status'=>1)); //
		if($results)
		{

			$this->set('results',$results);
		} // if($results)
		else
		{
			$this->Session->setFlash('Please Add Music Group');
			$this->redirect('/lfms/index/');

		} // if($results)

	} // function grouplist()

	
	
	
	/* Name: Charts
	 Desc: Weekly cumulative chart
	 Display Charts for Music Group & Listernes
	 called from statistics view
	 Retrun : void
	 set : Result value for view and cumulativechart session value set in statistics function
	 */
	function charts()
	{

		$this->set('lfmlisteners',$this->Session->read('lfmlisteners'));
		$this->set('dt',$this->Session->read('dt'));
		
	} // function charts()

	/* Name: dailycharts
	 Desc: Weekly daily chart
	 Display dailycharts for Music Group & Listernes
	 called from statistics view
	 Retrun : void
	 set : Result value for view and dailychart session value set in statistics function
	 */
	function dailycharts()
	{

		$results= $this->Session->read('dailychart');
		$diff= $this->Session->read('diff');   // for weekly 'w' , monthly 'm' & yearly 'y'
		$this->set('results',$results);
		$this->set('diff',$diff);
			
	} // function charts()


	 /*
	 * Name: getGraphData
	 * Desc : To get graph data for Listeners , Total plays , top songs , top album
	 */
	function getGraphData($id,$lastdate,$opt,$field,$name,$type)
	{
		if(!empty($id))
		{			
			$mmm_id = $this->Session->read('id') ;
			
			if($lastdate=='m')
			{
				if($type=='plays' || $type=='totalplays')
				{
					if($opt=='d')
					{
						$qry_yt = "select l.$field , l.etime from lfm_top_tracks l  where l.lfm_m_id = $id and l.name='$name'  and FROM_UNIXTIME(l.etime) between DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 DAY) AND CURDATE( ) order by etime";
						
					}
					else
					{
						$qry_yt = "select l.$field , l.etime from lfm_top_tracks l  where l.lfm_m_id = $id and l.name='$name'   and  FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";					
					}	
				} // if($type=='track')
				elseif($type=='listeners')
				{
					if($opt=='d')
					{
						$qry_yt = "select l.$field , l.etime from lfm_listeners l  where l.lfm_m_id = $id  and FROM_UNIXTIME(l.etime) between DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 DAY) AND CURDATE( ) order by etime";
						
					}
					else
					{
						$qry_yt = "select l.$field , l.etime from lfm_listeners l   where l.lfm_m_id = $id  and  FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";					
					}
				} // if($type=='track')
				elseif($type=='albumtrend')
				{
					if($opt=='d')
					{
						$qry_yt = "select l.$field , l.etime from lfm_top_album l  where l.lfm_m_id = $id and l.name='$name'  and FROM_UNIXTIME(l.etime) between DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 DAY) AND CURDATE( ) order by etime";
						
					}
					else
					{
						$qry_yt = "select l.$field , l.etime from lfm_top_album l  where l.lfm_m_id = $id  and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";					
					}
				}
			
				$lfm = $this->Lfmlistener->findBySql($qry_yt);


				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE() curr";
				$tfdate = $this->Lfmlistener->findBySql($qry);
	
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
				
				if($type=='plays' || $type=='totalplays')
				{
					$qry_yt = "select l.$field , l.etime from lfm_top_tracks l  where l.lfm_m_id = $id and l.name='$name'  and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";	
				} // if($type=='track')
				elseif($type=='listeners')
				{
					$qry_yt = "select l.$field , l.etime from lfm_listeners l  where l.lfm_m_id = $id  and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";
				} //  // if($type=='track')
				elseif($type=='albumtrend')
				{
					$qry_yt = "select l.$field , l.etime from lfm_top_album l  where l.lfm_m_id = $id and l.name='$name' and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";
				}
				
				
				$lfm = $this->Lfmlistener->findBySql($qry_yt);
				
				$lfm = $this->getyear($lfm,'l',$field);

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, CURDATE() curr";
				$tfdate = $this->Lfmlistener->findBySql($qry);

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
				if($type=='plays' || $type=='totalplays')
				{
					if($opt=='d')
					{	
						$qry_yt = "select l.$field , l.etime from lfm_top_tracks l  where l.lfm_m_id = $id and l.name='$name'  and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";
					}
					else
					{
						$qry_yt = "select l.$field , l.etime from lfm_top_tracks l  where l.lfm_m_id = $id and l.name='$name' and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";
					}	
				} // if($type=='track')
				elseif($type=='listeners')
				{
					if($opt=='d')
					{	
						$qry_yt = "select l.$field , l.etime from lfm_listeners l  where l.lfm_m_id = $id  and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";
					}
					else
					{
						$qry_yt = "select l.$field , l.etime from lfm_listeners l  where l.lfm_m_id = $id  and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";
					}
				} // if($type=='track')
				elseif($type=='albumtrend')
				{
					if($opt=='d')
					{
						$qry_yt = "select l.$field , l.etime from lfm_top_album l  where l.lfm_m_id = $id and l.name='$name' and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";
						
					}
					else
					{
						$qry_yt = "select l.$field , l.etime from lfm_top_album l  where l.lfm_m_id = $id and l.name='$name'  and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";
					}
				}
				
				$lfm = $this->Lfmlistener->findBySql($qry_yt);

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, CURDATE() curr";
				$tfdate = $this->Lfmlistener->findBySql($qry);
	
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


			if(empty($lfm))
			{	
				$lfmcount=0;
			} // if(empty($mss))
			else
			{
				$lfmcount = count($lfm);		
			} // if(empty($mss))

			$lfmlistenercount=0;
			$cnt=0;
			$predate=0;
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				if($lastdate=='y')
				{

					
						for($i=0 ; $i < $lfmcount ; $i++)  // set Myspace views according to main loop date
						{
							if($opt=='c')
							{
								if($lfm[$i]['l']['etime']==$val)
								{
									
									$lfmlistener[$cnt]= $lfm[$i]['l'][$field] ;
									$lfmlistenercount+=$lfmlistener[$cnt];
							
									$flg=1;
									$cnt++;
									break;
								} //	if($mss_data[$i]['s']['etime'])==$val)
							}
							elseif($opt=='d')
							{
								if($lfm[$i]['l']['etime']==$val)
								{
									if($i!=0)
									{
										$lfmlistener[$cnt]= $lfm[$i]['l'][$field]-$lfm[$i-1]['l'][$field];
										$lfmlistenercount += $lfmlistener[$cnt] ;
									}
									else
									{
										$lfmlistener[$cnt]= $lfm[$i]['l'][$field];
										
									}
										$flg=1;
										$cnt++;
										break;
								}
							}
							
											
						} // for($i=0 ; $i < $mscount-1 ; $i++)

						if($flg==0)
						{
							$lfmlistener[$cnt]=0;
							$cnt++;

						}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $lfmcount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$lfm[$i]['l']['etime'])==$val)
						{
							
							if($opt=='c')
							{
								if(date('Y-m-d',$lfm[$i]['l']['etime'])!=$predate)
								{
									
									$predate = date('Y-m-d',$lfm[$i]['l']['etime']);
	
										$lfmlistener[$cnt]= $lfm[$i]['l'][$field];
										$lfmlistenercount += $lfmlistener[$cnt] ;
									
									$flg=1;
									$cnt++;
								}
								break;
							}
							elseif($opt=='d')
							{
								if($i!=0)
								{
									$lfmlistener[$cnt]= $lfm[$i]['l'][$field]-$lfm[$i-1]['l'][$field];
									$lfmlistenercount += $lfmlistener[$cnt] ;
								}
								else
								{
									$lfmlistener[$cnt] = 0;
								}
								$flg=1;
								$cnt++;
								break;
							}

						} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $mscount-1 ; $i++)

					if($flg==0)
					{
						$lfmlistener[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
				
				if($lfmlistener[$cnt-1]==0)
				{
					$percentage = 0;
					$wpercentage = 0;
				}
				else
				{
					$percentage = round((($lfmlistener[$cnt-1]-$lfmlistener[$cnt-2])/$lfmlistener[$cnt-1])*100,2);
					$wpercentage = round((($lfmlistener[$cnt-1]-$lfmlistener[$cnt-7])/$lfmlistener[$cnt-1])*100,2);
				}
				
				$diff = $lfmlistener[$cnt-1]-$lfmlistener[$cnt-2];
				$wdiff = $lfmlistener[$cnt-1]-$lfmlistener[$cnt-7];

				if($type=='listeners')
				{
					
					$this->Session->write('lfmlistener',$lfmlistener);
					$this->Session->write('dt',$dt);
					$this->set('lpercentage',$percentage);
					$this->set('ldiff',$diff);
					$this->set('lwpercentage',$wpercentage);
					$this->set('lwdiff',$wdiff);
					$this->set('ltotal',$lfmlistener[$cnt-1]);
					
					
				}
				
				elseif($type=='plays')
				{
					$this->Session->write('lfmplays',$lfmlistener);
					$this->Session->write('dt',$dt);
					$this->set('ppercentage',$percentage);
					$this->set('pdiff',$diff);
					$this->set('pwpercentage',$wpercentage);
					$this->set('pwdiff',$wdiff);
					$this->set('ptotal',$lfmlistener[$cnt-1]);
					
					
				}
				
				elseif($type=='albumtrend')
				{
					
					$this->Session->write('lfmalbumtrend',$lfmlistener);
					$this->Session->write('dt',$dt);
					$this->set('apercentage',$percentage);
					$this->set('adiff',$diff);
					$this->set('awpercentage',$wpercentage);
					$this->set('awdiff',$wdiff);
					$this->set('atotal',$lfmlistener[$cnt-1]);
					
					
				}
				
				
				
					$this->Session->write('lastdate',$lastdate);
					$this->Session->write('opt',$opt);
					$this->set('lastdate',$lastdate);
					return $lfmlistener;
					
		} // if(!empty($this->params['url']['id']) and !empty($this->params['url']['title']))
	} // function getGraphData($id,$lastdate,$opt,$field,$name,$type)


	function listenerschart()
	{
		
		$this->set('lfmlistener',$this->Session->read('lfmlistener'));
		$this->set('dt',$this->Session->read('dt'));
		
	}
	
	function playschart()
	{
		
		$this->set('lfmplays',$this->Session->read('lfmplays'));
		$this->set('dt',$this->Session->read('dt'));
		
	}
	
	/* Name: albumchart
	 Desc: Display Top Album for Music Group
	 called from topalbum view
	 Retrun : void
	 set : Result value for view
	 */
	function albumchart()
	{
		
		$top_album = $this->Session->read('top_album');
		$this->set('top_album', $top_album);

	} // function albumchart()
	
	/* Name: trackchart
	 Desc: Display Top Album for Music Group
	 called from toptrack view
	 Retrun : void
	 set : Result value for view
	 */
	function trackchart()
	{
		$top_tracks = $this->Session->read('top_tracks');
		$this->set('top_tracks', $top_tracks);

	} // function albumchart()
	
	/* Name: statistics
	 Desc: Display Listeners and groups
	 Retrun : void
	 set : set groups & listerners data
	 */
	function statistics()
	{
		
		if(empty($this->data['analytic']['date']))
		{
		$lastdate = 'w';
		} // if(empty($this->params['url']['lastdate']))
		else
		{
		$lastdate = $this->data['analytic']['date'];						
		} // if(empty($this->params['url']['lastdate']))

		if(!empty($this->params['url']['etime']))
		{
			$etime= $this->params['url']['etime'];
			$this->Session->write('etime',$etime);
			
		}
		
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'] ;
			$mmm_id = $this->Session->read('id') ;
			
			if($lastdate=='m')
			{
			$qry_lfm = "select l.listeners , l.etime from lfm_listeners l , lfm_music m where l.lfm_m_id = $id and m.mmm_id='$mmm_id' and m.lfm_m_id = l.lfm_m_id and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";
			
			$lfm = $this->Lfmlistener->findBySql($qry_lfm);


			$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE() curr";
			$tfdate = $this->Lfmlistener->findBySql($qry);

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
			$qry_lfm = "select l.listeners , l.etime from lfm_listeners l , lfm_music m where l.lfm_m_id = $id and m.mmm_id='$mmm_id' and m.lfm_m_id = l.lfm_m_id and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";


				$lfm = $this->Lfmlistener->findBySql($qry_lfm);
				$lfm = $this->getyear($lfm,'l','listeners');

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, CURDATE() curr";
				$tfdate = $this->Lfmlistener->findBySql($qry);

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
			$qry_lfm = "select l.listeners , l.etime from lfm_listeners l , lfm_music m where l.lfm_m_id = $id and m.mmm_id='$mmm_id' and m.lfm_m_id = l.lfm_m_id and FROM_UNIXTIME(l.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";
			
			$lfm = $this->Lfmlistener->findBySql($qry_lfm);


			$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, CURDATE() curr";
			$tfdate = $this->Lfmlistener->findBySql($qry);

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


			if(empty($lfm))
			{	
				$lfmcount=0;
			} // if(empty($mss))
			else
			{
				$lfmcount = count($lfm);		
			} // if(empty($mss))

			$lfmlistenerscount=0;
			$cnt=0;
			$predate=0;
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				if($lastdate=='y')
				{

					
						for($i=0 ; $i < $lfmcount ; $i++)  // set Myspace views according to main loop date
						{
							if($lfm[$i]['l']['etime']==$val)
							{
								
								$lfmlisteners[$cnt]= $lfm[$i]['l']['listeners'] ;
								$lfmlistenerscount+=$lfmlisteners[$cnt];
						
								$flg=1;
								$cnt++;
								break;
							} //	if($mss_data[$i]['s']['etime'])==$val)
						} // for($i=0 ; $i < $mscount-1 ; $i++)

						if($flg==0)
						{
							$lfmlisteners[$cnt]=0;
							$cnt++;

						}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $lfmcount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$lfm[$i]['l']['etime'])==$val)
						{
							if(date('Y-m-d',$lfm[$i]['l']['etime'])!=$predate)
							{
								
								$predate = date('Y-m-d',$lfm[$i]['l']['etime']);

									$lfmlisteners[$cnt]= $lfm[$i]['l']['listeners'];
									$lfmlistenerscount += $lfmlisteners[$cnt] ;
								
								$flg=1;
								$cnt++;
							}
							break;

						} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $mscount-1 ; $i++)

					if($flg==0)
					{
						$lfmlisteners[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
				
				
				$this->Session->write('lfmlisteners',$lfmlisteners);
				$this->Session->write('dt',$dt);
				$this->Session->write('lastdate',$lastdate);
				$this->set('lastdate',$lastdate);
				$this->set('id',$id);
				$etime= $this->Session->read('etime');
				$this->set('etime',$etime);
			
			
			
		} // if(!empty($this->params['url']['id']) and !empty($this->params['url']['title']))
		else
		{
			$this->Session->setFlash('Invalid parameter.');
			$this->redirect('/lfms/index/');

		} // if($results)
	


	} // function statistics()

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
	} // function getviewyear()

	

	
	


	/* Name: toptracks
	 Desc: Display tracks rank , name and playcount
	 Retrun : void
	 set : set tracks rank , name & playcount
	 */
	function toptracks()
	{
		$id=$this->params['url']['id'];
		$etime=$this->params['url']['etime'];
		$mmm_id= $this->Session->read('id') ;

		$qry = "select t.rank , t.name , t.playcount from lfm_top_tracks t , lfm_music m  where t.lfm_m_id=$id and t.etime=$etime and t.lfm_m_id=m.lfm_m_id and m.mmm_id='$mmm_id'";
		$results = $this->Lfm->findBySql($qry);

		if($results)
		{
			$this->set('results',$results);
			$this->set('id',$id);
			$this->set('etime',$etime);

		} // if($results)
		else
		{
			$this->Session->setFlash('No stats available.');
			$this->redirect('/lfms/grouplist/');
		} // if($results)
	} // function topalbum()

	/* Name: toptracktrend
	 Desc: Display top album trend name and playcount
	 called from trackchart view
	 Retrun : void
	 */
	function toptracktrend()
	{
		
		if(empty($this->data['analytic']['date']))
		{
		$lastdate = 'w';
		} // if(empty($this->params['url']['lastdate']))
		else
		{
		$lastdate = $this->data['analytic']['date'];						
		} // if(empty($this->params['url']['lastdate']))

		$value=$this->params['url']['id'];
		$id=substr($value,strrpos($value,"-")+1,strlen($value));
		$name=addslashes(substr($value,0,strrpos($value,"-")));
		
		if(!empty($id) and !empty($name))
		{
			$mmm_id = $this->Session->read('id') ;
			
			if($lastdate=='m')
			{
			$qry_lfm = "select a.playcount , a.etime  from lfm_top_tracks a , lfm_music m where a.lfm_m_id=$id and name='$name' and a.lfm_m_id=m.lfm_m_id and m.mmm_id='$mmm_id' and FROM_UNIXTIME(a.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";
			
			$lfm = $this->Lfmtrack->findBySql($qry_lfm);


			$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE() curr";
			$tfdate = $this->Lfmtrack->findBySql($qry);

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
			$qry_lfm = "select a.playcount , a.etime  from lfm_top_tracks a , lfm_music m where a.lfm_m_id=$id and name='$name' and a.lfm_m_id=m.lfm_m_id and m.mmm_id='$mmm_id' and FROM_UNIXTIME(a.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";


				$lfm = $this->Lfmtrack->findBySql($qry_lfm);
				$lfm = $this->getyear($lfm,'a','playcount');

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, CURDATE() curr";
				$tfdate = $this->Lfmtrack->findBySql($qry);

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
			$qry_lfm = "select a.toptrack_id , a.rank , a.name , a.playcount , a.etime  from lfm_top_tracks a , lfm_music m where a.lfm_m_id=$id and name='$name' and a.lfm_m_id=m.lfm_m_id and m.mmm_id='$mmm_id' and FROM_UNIXTIME(a.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";
			
			$lfm = $this->Lfmtrack->findBySql($qry_lfm);


			$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, CURDATE() curr";
			$tfdate = $this->Lfmtrack->findBySql($qry);

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


			if(empty($lfm))
			{	
				$lfmcount=0;
			} // if(empty($mss))
			else
			{
				$lfmcount = count($lfm);		
			} // if(empty($mss))

			$lfmlistenerscount=0;
			$cnt=0;
			$predate=0;
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				if($lastdate=='y')
				{

					
						for($i=0 ; $i < $lfmcount ; $i++)  // set Myspace views according to main loop date
						{
							if($lfm[$i]['a']['etime']==$val)
							{
								
								$lfmlisteners[$cnt]= $lfm[$i]['a']['playcount'] ;
								$lfmlistenerscount+=$lfmlisteners[$cnt];
						
								$flg=1;
								$cnt++;
								break;
							} //	if($mss_data[$i]['s']['etime'])==$val)
						} // for($i=0 ; $i < $mscount-1 ; $i++)

						if($flg==0)
						{
							$lfmlisteners[$cnt]=0;
							$cnt++;

						}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $lfmcount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$lfm[$i]['a']['etime'])==$val)
						{
							if(date('Y-m-d',$lfm[$i]['a']['etime'])!=$predate)
							{
								
								$predate = date('Y-m-d',$lfm[$i]['a']['etime']);

									$lfmlisteners[$cnt]= $lfm[$i]['a']['playcount'];
									$lfmlistenerscount += $lfmlisteners[$cnt] ;
								
								$flg=1;
								$cnt++;
							}
							break;

						} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $mscount-1 ; $i++)

					if($flg==0)
					{
						$lfmlisteners[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
				
				
				$this->Session->write('lfmlisteners',$lfmlisteners);
				$this->Session->write('dt',$dt);
				$this->Session->write('lastdate',$lastdate);
				$this->set('lastdate',$lastdate);
				$this->set('id',$id);
				$this->set('name',$name);
				
			
			
			
		} // if(!empty($this->params['url']['id']) and !empty($this->params['url']['title']))
	

	} // function topalbum()

	/* Name: trackcharttrend
	 Desc: Display tend graph for playcount & date
	 called from toptracktrend
	 Retrun : void
	 */
	function trackcharttrend()
	{
		$this->set('lfmlisteners',$this->Session->read('lfmlisteners'));
		$this->set('dt',$this->Session->read('dt'));
	} //  albumcharttrend()


	/* Name: albumcharttrend
	 Desc: Display tend graph for playcount & date
	 called from topalbumtrend
	 Retrun : void
	 */
	function albumcharttrend()
	{
		$albumtrend = $this->Session->read('lfmalbumtrend');
		$dt = $this->Session->read('dt');
		$this->set('albumtrend',$albumtrend);
		$this->set('dt',$dt);

	} //  albumcharttrend()


	/* Name: topalbumtrend
	 Desc: Display top album trend name and playcount
	 Retrun : void
	 */
	function topalbumtrend()
	{

	
		if(empty($this->data['analytic']['date']))
		{
		$lastdate = 'w';
		} // if(empty($this->params['url']['lastdate']))
		else
		{
		$lastdate = $this->data['analytic']['date'];						
		} // if(empty($this->params['url']['lastdate']))

		$value=$this->params['url']['id'];
		$id=substr($value,strrpos($value,"-")+1,strlen($value));
		$name=addslashes(substr($value,0,strrpos($value,"-")));
		
		if(!empty($id) and !empty($name))
		{
			$mmm_id = $this->Session->read('id') ;
			
			if($lastdate=='m')
			{
			$qry_lfm = "select a.playcount , a.etime  from lfm_top_album a , lfm_music m where a.lfm_m_id=$id and name='$name' and a.lfm_m_id=m.lfm_m_id and m.mmm_id='$mmm_id' and FROM_UNIXTIME(a.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";
			
			$lfm = $this->Lfmalbum->findBySql($qry_lfm);


			$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE() curr";
			$tfdate = $this->Lfmalbum->findBySql($qry);

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
			$qry_lfm = "select a.playcount , a.etime  from lfm_top_album a , lfm_music m where a.lfm_m_id=$id and name='$name' and a.lfm_m_id=m.lfm_m_id and m.mmm_id='$mmm_id' and FROM_UNIXTIME(a.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";


				$lfm = $this->Lfmalbum->findBySql($qry_lfm);
				$lfm = $this->getyear($lfm,'a','playcount');

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, CURDATE() curr";
				$tfdate = $this->Lfmalbum->findBySql($qry);

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
			$qry_lfm = "select a.playcount , a.etime  from lfm_top_album a , lfm_music m where a.lfm_m_id=$id and name='$name' and a.lfm_m_id=m.lfm_m_id and m.mmm_id='$mmm_id' and FROM_UNIXTIME(a.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";
			
			$lfm = $this->Lfmalbum->findBySql($qry_lfm);


			$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, CURDATE() curr";
			$tfdate = $this->Lfmalbum->findBySql($qry);

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


			if(empty($lfm))
			{	
				$lfmcount=0;
			} // if(empty($mss))
			else
			{
				$lfmcount = count($lfm);		
			} // if(empty($mss))

			$lfmlistenerscount=0;
			$cnt=0;
			$predate=0;
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				if($lastdate=='y')
				{

					
						for($i=0 ; $i < $lfmcount ; $i++)  // set Myspace views according to main loop date
						{
							if($lfm[$i]['a']['etime']==$val)
							{
								
								$lfmlisteners[$cnt]= $lfm[$i]['a']['playcount'] ;
								$lfmlistenerscount+=$lfmlisteners[$cnt];
						
								$flg=1;
								$cnt++;
								break;
							} //	if($mss_data[$i]['s']['etime'])==$val)
						} // for($i=0 ; $i < $mscount-1 ; $i++)

						if($flg==0)
						{
							$lfmlisteners[$cnt]=0;
							$cnt++;

						}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $lfmcount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$lfm[$i]['a']['etime'])==$val)
						{
							if(date('Y-m-d',$lfm[$i]['a']['etime'])!=$predate)
							{
								
								$predate = date('Y-m-d',$lfm[$i]['a']['etime']);

									$lfmlisteners[$cnt]= $lfm[$i]['a']['playcount'];
									$lfmlistenerscount += $lfmlisteners[$cnt] ;
								
								$flg=1;
								$cnt++;
							}
							break;

						} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $mscount-1 ; $i++)

					if($flg==0)
					{
						$lfmlisteners[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
				
				
				$this->Session->write('lfmlisteners',$lfmlisteners);
				$this->Session->write('dt',$dt);
				$this->Session->write('lastdate',$lastdate);
				$this->set('lastdate',$lastdate);
				$this->set('id',$id);
				$this->set('name',$name);
				
			
			
			
		} // if(!empty($this->params['url']['id']) and !empty($this->params['url']['title']))
	

			


	} // 	function topalbumtrend()



	/* Name: getMusic
	 Desc: get & insert complete last.fm statistic on database
	 Retrun : void
	 set : insert complete last.fm statistic on database
	 */
	function getMusic($music , $id , $band_id )
	{
			
				
			$authVars = array(
				'apiKey' => '13846fb92c539b877ff2abc79ede2718',
				'secret' => 'ce4450255eb766372b130f0784c15087',
				'username' => 'hilkeros',
				'sessionKey' => '671bbefd4f15d3ed2fdf524dea62212b',
				'subscriber' => 0
			);
			$auth = new lastfmApiAuth('setsession', $authVars);
	
			$apiClass = new lastfmApi();
			$artistClass = $apiClass->getPackage($auth, 'artist');
			$trackClass = $apiClass->getPackage($auth, 'track');
			$albumClass = $apiClass->getPackage($auth, 'album');
	
			if($music)
			{
	
				$methodVars = array(
					'artist' => $music
				);
	
				$this->etime = time();
				if ( $artist = $artistClass->getInfo($methodVars) )
				{
	
					$stat['Lfm']['url'] = $artist['url'];
					$stat['Lfm']['lfm_m_id']=$id;
					$stat['Lfm']['executetime'] = $this->etime;
					$stat['Lfm']['band_id'] = $band_id;

					$this->Lfm->save($stat);
					$stat = NULL;
					$stat['Lfmlistener']['lfm_m_id']=$id;
					$stat['Lfmlistener']['listeners']=$artist['stats']['listeners'];
					$stat['Lfmlistener']['etime'] = $this->etime;
				
					$this->Lfmlistener->create();
					$this->Lfmlistener->save($stat);
	
				}
	
				if ( $albums = $artistClass->getTopAlbums($methodVars) ) {
	
					foreach($albums as $key => $val)
					{
	
						$methodAlbumVars = array(
									'artist' => $music ,
									'album' => $val['name'],
									'mbid' => $val['mbid']
								   );
						
						if($topAlbum = $albumClass->getinfo($methodAlbumVars))
						{
							$alb['Lfmalbum']['rank']=$val['rank'];
							$alb['Lfmalbum']['name']=$val['name'];
							$alb['Lfmalbum']['playcount']=$topAlbum['playcount'];
							$alb['Lfmalbum']['lfm_m_id']= $id;
							$alb['Lfmalbum']['etime']= $this->etime;
							$this->Lfmalbum->create();
							$this->Lfmalbum->save($alb);
						}
						/*					$qry = "insert into lfm_top_album(stat_id , rank , name , playcount) values ($stat_id,$rank,'$name',$playcount)";
						 $result = mysql_query($qry);
						 */
						//      $TopAlbumCount ++ ;
					}
				}
	
				if ( $tracks = $artistClass->getTopTracks($methodVars) ) {
						
					foreach($tracks as $key => $val)
					{
						$methodTrackVars = array(
									'artist' => $music ,
									'track' => $val['name']
								   );
						
						if($toptrack = $trackClass->getinfo($methodTrackVars))
						{
							
								$trk['Lfmtrack']['lfm_m_id']= $id;
								$trk['Lfmtrack']['rank']=$val['rank'];
								$trk['Lfmtrack']['name']=$val['name'];
								$trk['Lfmtrack']['playcount']=$toptrack['playcount'];
								$trk['Lfmtrack']['etime']=$this->etime;
								$this->Lfmtrack->create();
								$this->Lfmtrack->save($trk);
						}
							
						/*
						 $qry = "insert into lfm_top_tracks(stat_id , rank , name , playcount) values ($stat_id,$rank,'$name',$playcount)";
						 $result = mysql_query($qry);
						 */
	
					} // foreach($tracks as $key => $val)
				}
			return true;
	
			} // if($music)
	
		
	} // function getMusic()*/


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
