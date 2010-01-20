<?php
class AnalyticsController extends AppController {

	var $name = 'Analytics';
	var $etime = NULL;
	var $uses = array('User','Band','Lfm','Lfmtrack','Lfmalbum','Lfmlistener','Mss','Mssstat','Msscomm','Msslogin','Fb','Fbpage','Yt','Ytstat','Ytcommstat');
	var $helpers = array('Html', 'Error', 'Javascript' , 'FlashChart');
	var $components = array('Cookie'); //  use component email



	/**
	 * Name: beforeFilter
	 * Desc: Performs necessary steps and function calls prior to executing
	 *       any view function calls.
	 */
	function beforeFilter() {
		$this->auth();

	} // function beforeFilter() {

	/* Name: index
	 * Desc: set MySpace , Facebook , Last.fm , Youtube ID's by select option box.
	 */

	function index()
	{

		$this->layout = "analytics";	
		if($this->data)
		{
			$this->Session->write('mssvideo', $this->data['data']['mssvideo']);
			$this->Session->write('video', $this->data['data']['video']);
			$this->Session->write('topalbum', $this->data['data']['album']);
			$this->Session->write('toptrack', $this->data['data']['tracks']);

			$this->redirect('/analytics/cmslist/');
			exit;

		} // if($this->data)
		else
		{
			
			
			$mmm_id = $this->Session->read('id');
	
			if(@$this->params['url']['bandid'])
			{
				$band_id = $this->params['url']['bandid'];
			} // if(@$this->params['url']['bandid'])
			else
			{
				$band = $this->Band->find(array('mmm_id'=>$mmm_id,'status'=>'1'));
				if($band)
				{
					$band_id = $band['Band']['band_id'];
					
				}
				else
				{
					$this->Session->setFlash('Please Add Band');
					$this->redirect('/band/index/');	
				}
			} // if(@$this->params['url']['bandid'])
			
			$band = $this->Band->findAll(array('mmm_id'=>$mmm_id,'status'=>'1'));
			if($band)
			{
				
				$this->set('band',$band);
				foreach($band as $bandkey =>$bandval)
				{
					if($bandval['Band']['band_id']==$band_id)
					{
						$bandname = 	$bandval['Band']['name'];
						break;
					}
				}
				
				$this->set('bandname',$bandname);
				$this->Session->write('bandname',$bandname);
			}
			
			
				
			$this->Session->write('band_id',$band_id);
			
			$qry_mss = "select p.title from mss_play_stat p , mss_login m where p.mss_id = m.mss_id and m.mmm_id='$mmm_id' and m.band_id=$band_id";
			$mss_plays = $this->Mssstat->findBySql($qry_mss);
			if($mss_plays)
			{
				$this->set('mss_plays',$mss_plays);
			} // if($results)
			else
			{
				$mss_plays[0]['p']['title'] = 'No data';
				$this->set('mss_plays',$mss_plays);
			}
			
			
			$qry_lfm_album = "select a.name from lfm_top_album a , lfm_music m where a.lfm_m_id = m.lfm_m_id and m.mmm_id='$mmm_id' and m.band_id=$band_id";
			$lfm_album = $this->Lfmalbum->findBySql($qry_lfm_album);
			if($lfm_album)
			{
				$this->set('lfm_album',$lfm_album);
			} // if($results)
			else
			{
				$lfm_album[0]['a']['name'] = 'No data';
				$this->set('lfm_album',$lfm_album);
			}
			
			
			$qry_lfm_track = "select t.name from lfm_top_tracks t , lfm_music m where t.lfm_m_id = m.lfm_m_id and m.mmm_id='$mmm_id' and m.band_id=$band_id";
			$lfm_track = $this->Lfmalbum->findBySql($qry_lfm_track);
			if($lfm_track)
			{
				$this->set('lfm_track',$lfm_track);
			} // if($results)
			else
			{
				$lfm_track[0]['t']['name'] = 'No data';
				$this->set('lfm_track',$lfm_track);
			}
			
			$qry_yt_video= "select y.title from yt_comments_stat y , yt_login l  where y.yt_id = l.yt_id and l.mmm_id='$mmm_id' and l.band_id=$band_id";
			$ytvideo = $this->Ytcommstat->findBySql($qry_yt_video);
			if($ytvideo)
			{
				$this->set('ytvideo',$ytvideo);
			} // if($results)	
			else
			{
				$ytvideo[0]['y']['title'] = 'No data';
				$this->set('ytvideo',$ytvideo);
			}
	
			$fb = $this->Fb->find(array('mmm_id'=>$mmm_id , 'band_id'=>$band_id , 'status'=>1));
			if($fb)
			{
				
				$page = $fb['Fb']['page'];
				$group = $fb['Fb']['group'];
				
				if($page)
				{
					$this->Session->write('fbpages', $page);
				}
				else
				{
					$page = 'No data';
					$this->Session->write('fbpages', $page);
				}
				
				if($group)
				{
					$this->Session->write('fbgroups', $group);
				}
				else
				{
						$group = 'No data';
						$this->Session->write('fbgroups', $group);
					
				}
				
			} // if($results)
			else
			{
				$page  = 'No data';
				$group = 'No data';
				
				$this->Session->write('fbpages', $page);
				$this->Session->write('fbgroups', $group);
				
			}

		} // if($this->data)
			
	} // 	function index()


	/*
	 Name : album
	 Desc :	Ajax Album  to get top album against last.fm Music Group
	 */
	function album()
	{
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];
			$qry="select distinct l.name from lfm_top_album l where l.lfm_m_id = $id";
			$result = $this->Lfmalbum->findBySql($qry);
			$data = "[";
			foreach($result as $key =>$val)
			{
				$nm = str_replace(array("\"","'"),array(" "," "),$val['l']['name']);
				$data.= "{ optionValue:'$nm' , optionDisplay:'$nm'},";
			}
			$data= substr($data,0,strlen($data)-1);
			$data.="]";
			echo $data;
			exit;

		}

	} // function album


	/*
	 Name : tracks
	 Desc :	Ajax Album  to get top tracks against last.fm Music Group
	 */
	function tracks()
	{
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];
			$qry="select distinct l.name from lfm_top_tracks l where l.lfm_m_id = $id";
			$result = $this->Lfmalbum->findBySql($qry);
			$data = "[";
			foreach($result as $key =>$val)
			{
				$nm = str_replace(array("\"","'"),array(" "," "),$val['l']['name']);
				$data.= "{ optionValue:'$nm' , optionDisplay:'$nm'},";
			}
			$data= substr($data,0,strlen($data)-1);
			$data.="]";
			echo $data;
			exit;

		}

	} // function tracks

	/*
	 Name : comments
	 Desc :	Ajax to get youtube video title
	 */
	function comments()
	{
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];
			$qry="select distinct y.title from yt_comments_stat y where y.yt_id = $id";
			$result = $this->Lfmalbum->findBySql($qry);
			$data = "[";
			foreach($result as $key =>$val)
			{
				$nm = str_replace(array("\"","'"),array(" "," "),$val['y']['title']);
				$data.= "{ optionValue:'$nm' , optionDisplay:'$nm'},";
			}
			$data= substr($data,0,strlen($data)-1);
			$data.="]";
			echo $data;
			exit;

		}

	} // function comments()


	/*
	 Name : mssplays
	 Desc :	Ajax to get Myspace title
	 */
	function mssplays()
	{
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];
			$qry="select distinct m.title from mss_play_stat m where m.mss_id = $id";
			$result = $this->Lfmalbum->findBySql($qry);
			$data = "[";
			foreach($result as $key =>$val)
			{
				$nm = str_replace(array("\"","'"),array(" "," "),$val['m']['title']);
				$data.= "{ optionValue:'$nm' , optionDisplay:'$nm'},";
			}
			$data= substr($data,0,strlen($data)-1);
			$data.="]";
			echo $data;
			exit;

		}

	} // function comments()


	/*
	 Name : Facebook Pages
	 Desc :	Ajax to get Fbs pages
	 */
	function fbspages()
	{
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];
			$qry="select distinct f.name from fb_pages f where f.login_id = $id and f.is_admin=1";
			$result = $this->Lfmalbum->findBySql($qry);
			$data = "[";
			foreach($result as $key =>$val)
			{
				$nm = str_replace(array("\"","'"),array(" "," "),$val['f']['name']);
				$data.= "{ optionValue:'$nm' , optionDisplay:'$nm'},";
			}
			$data= substr($data,0,strlen($data)-1);
			$data.="]";
			echo $data;
			exit;

		}

	} // function fbspages()


	/*
	 Name : Facebook Groups
	 Desc :	Ajax to get Fbs Groups
	 */
	function fbsgroups()
	{
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];
			$qry="select distinct f.name from fb_group f where f.login_id = $id and f.isCreator='Yes'";
			$result = $this->Lfmalbum->findBySql($qry);
			$data = "[";
			foreach($result as $key =>$val)
			{
				$nm = str_replace(array("\"","'"),array(" "," "),$val['f']['name']);
				$data.= "{ optionValue:'$nm' , optionDisplay:'$nm'},";
			}
			$data= substr($data,0,strlen($data)-1);
			$data.="]";
			echo $data;
			exit;

		}

	} // function fbspages()

	/*
	 Name 	:	cmslist
	 Desc	:	link hits , view , plays and comments
	 */
	function cmslist()
	{
		
		if($this->Session->check('id') and $this->Session->check('band_id') and $this->Session->check('bandname') and $this->Session->check('toptrack') and $this->Session->check('topalbum') and $this->Session->check('video') and $this->Session->check('mssvideo'))
		{
			$bandname = $this->Session->read('bandname');
			$this->set('bandname',$bandname);
			
			
			if($this->Session->read('mssvideo')!='No data')
			{	$this->Session->write('msh','msh');
				$this->Session->write('msv','msv');
				$this->Session->write('msp','msp');
				$this->Session->write('msc','msc');
			}
	
			if($this->Session->read('video')!='No data')
			{
				$this->Session->write('yth','yth');
				$this->Session->write('ytv','ytv');
				$this->Session->write('ytp','ytp');
				$this->Session->write('ytc','ytc');
			}	
			
			if(($this->Session->read('topalbum')!='No data') and ($this->Session->read('toptrack')!='No data'))
			{
				$this->Session->write('lfmsh','lfmsh');
				$this->Session->write('lfmsv','lfmsv');
				$this->Session->write('lfmsp','lfmsp');
			}
	
			if($this->Session->read('fbpages')!='No data')					
				$this->Session->write('fbspages','fbspages');
				
			if($this->Session->read('fbgroups')!='No data')										
				$this->Session->write('fbsgroups','fbsgroups');
			
			$this->layout = 'analyticcms';
		} // if($this->Session->check('id') and $this->Session->check('band_id') and $this->Session->check('bandname') and $this->Session->check('toptrack') and $this->Session->check('topalbum') and $this->Session->check('video') and $this->Session->check('mssvideo'))
		else
		{
			$this->redirect('/analytics/index');
		} // if($this->Session->check('email') and $this->Session->check('band_id') and $this->Session->check('bandname') and $this->Session->check('toptrack') and $this->Session->check('topalbum') and $this->Session->check('video') and $this->Session->check('mssvideo'))
		
	} // function cmslist()

	/*
	 name : chart
	 set : get and set data for fans , hits , tracks & comments
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
	
	
	
			$mmm_id = $this->Session->read('id');
	
			// get and set band ID
	
			if(!empty($this->params['url']['bandid']))
			{
				$band_id = $this->params['url']['bandid'];
				$this->Session->write('band_id',$band_id);
			} // if(@$this->params['url']['bandid'])
			elseif($this->Session->check('band_id'))
			{
				$band_id = $this->Session->read('band_id');
			}
			else
			{
				$this->set('flag',1);
						
			}//if(@$this->params['url']['bandid'])
			
			$band = $this->Band->find(array('mmm_id'=>$mmm_id,'status'=>'1','band_id'=>$band_id));
			if($band)
			{
				$bandname = $band['Band']['name'];
				$this->set('bandname',$bandname);
				$this->Session->write('bandname',$bandname);
			}
			
			$this->Session->write('band_id',$band_id);
			
			
			// Get and set values for Myspace , youtube , last.fm
			
			
			// Get and set values for MySpace Tracks Tab
			$qry_mss = "select distinct p.title from mss_play_stat p , mss_login m where p.mss_id = m.mss_id and m.mmm_id='$mmm_id' and m.band_id=$band_id and m.status=1";
			
			$mss_plays = $this->Mssstat->findBySql($qry_mss);
			$mss = NULL;
			if($mss_plays)
			{
				if(!empty($this->params['url']['mss'])) // if option select from light box
				{
					$this->Session->write('mssvideo', $this->params['url']['mss']); 
					$this->set('mssval',$this->params['url']['mss']); // to select dropdown list value
				}
				else
				{
					$this->Session->write('mssvideo', $mss_plays[0]['p']['title']);
					$this->set('mssval',$mss_plays[0]['p']['title']);
				}
				
				foreach($mss_plays as $key => $mssval)
				{
					$mss[$mssval['p']['title']] =  $mssval['p']['title'] ;
				}
				$this->set('mss',$mss);
			} // if($results)
			else
			{
				$mss_plays[0]['p']['title'] = 'No data';
				$this->Session->write('mssvideo', $mss_plays[0]['p']['title']);
				$mss['No data'] = 'No data';
				$this->set('mssval','No data');
				$this->set('mss',$mss);
			}
			
			
			// Get and set values for Last.fm Tracks Tab		
			$qry_lfm_track = "select distinct  t.name from lfm_top_tracks t , lfm_music m where t.lfm_m_id = m.lfm_m_id and m.mmm_id='$mmm_id' and m.band_id=$band_id and m.status=1";
			$lfm_track = $this->Lfmalbum->findBySql($qry_lfm_track);
			$lfmt=NULL;
			if($lfm_track)
			{
				if(!empty($this->params['url']['lfmt']))
				{
					$this->Session->write('toptrack', $this->params['url']['lfmt']);
					$this->set('lfmtval',$this->params['url']['lfmt']);
				}
				else
				{
					$this->Session->write('toptrack', $lfm_track[0]['t']['name']);
					$this->set('lfmtval',$lfm_track[0]['t']['name']);
				}
				
				
				foreach($lfm_track as $key => $lfmval)
				{
					$lfmt[$lfmval['t']['name']] =  $lfmval['t']['name'];
				}
				$this->set('lfmt',$lfmt);
			} // if($results)
			else
			{
				$lfm_track[0]['t']['name'] = 'No data';
				$this->Session->write('toptrack', $lfm_track[0]['t']['name']);
				$lfmt['No data'] = 'No data';
				$this->set('lfmt',$lfmt);
				$this->set('lfmtval','No data');
			}
			
			// Get and set values for Youtubne Tracks & comments Tab
			$qry_yt_video= "select distinct y.title from yt_comments_stat y , yt_login l  where y.yt_id = l.yt_id and l.mmm_id='$mmm_id' and l.band_id=$band_id and l.status=1";
			
			
			$ytvideo = $this->Ytcommstat->findBySql($qry_yt_video);
			
			$yt=NULL;
			if($ytvideo)
			{
				if(!empty($this->params['url']['yt']))
				{
					$this->Session->write('video', $this->params['url']['yt']);
					$this->set('ytval',$this->params['url']['yt']);
				}
				else
				{
					$this->Session->write('video', $ytvideo[0]['y']['title']);
					$this->set('ytval',$ytvideo[0]['y']['title']);
				}
				
				
				foreach($ytvideo as $key => $ytval)
				{
					$yt[$ytval['y']['title']] =  $ytval['y']['title'];
				}
				$this->set('yt',$yt);
			} // if($results)	
			else
			{
				$ytvideo[0]['y']['title'] = 'No data';
				$this->Session->write('video', $ytvideo[0]['y']['title']);
				$yt['No data'] = 'No data';
				$this->set('yt',$yt);
				$this->set('ytval','No data');
			}
	
			
			// Get and set facebook pages & group data for Fans Tab
			$fb = $this->Fb->find(array('mmm_id'=>$mmm_id , 'band_id'=>$band_id , 'status'=>1));
			if($fb)
			{
				
				$page = $fb['Fb']['page'];
				$group = $fb['Fb']['group'];
				
				if($page)
				{
					$this->Session->write('fbpages', $page);
				}
				else
				{
					$page = 'No data';
					$this->Session->write('fbpages', $page);
				}
				
				if($group)
				{
					$this->Session->write('fbgroups', $group);
				}
				else
				{
						$group = 'No data';
						$this->Session->write('fbgroups', $group);
					
				}
				
			} // if($results)
			else
			{
				$page  = 'No data';
				$group = 'No data';
				
				$this->Session->write('fbpages', $page);
				$this->Session->write('fbgroups', $group);
				
			}
	
	
		/*
			if in case chart call within the chart
			else in case of chart first time call without dates then default date & option applied 
		*/
		if(!empty($this->params['url']['date']))
		{
				
				$lastdate = $this->params['url']['date'];
				$this->Session->write('lastdate',$lastdate);
				$this->set('lastdate',$lastdate );
				
				
				
				if(!empty($this->params['url']['id']))
				{
					$id = $this->params['url']['id'];
					
					// set On / Off buttons
					switch($id)
					{
						case 'hall' :
								$this->Session->write('msv','msv');// Myspace hits
								$this->Session->write('ytv','ytv');// Youtube hits
								$this->Session->write('lfmsv','lfmsv'); // last.fm hits
								$this->Session->write('hnone',0);
								break;
						case 'hnone' :
								$this->Session->delete('msv');// Myspace hits
								$this->Session->delete('ytv');// Youtube hits
								$this->Session->delete('lfmsv');// last.fm hits
								if($this->Session->read('hnone')==1)
								{
									$this->Session->write('hnone',0);
								}
								else
								{
									$this->Session->write('hnone',1);
								}
								break;
						case 'fall'  :
								$this->Session->write('msh','msh');// Myspace fans
								$this->Session->write('yth','yth');// Youtube fans
								$this->Session->write('lfmsh','lfmsh'); // last.fm fans
								$this->Session->write('fbspages','fbspages'); // facebook page flag
								$this->Session->write('fbsgroups','fbsgroups'); // facebook group flag
								$this->Session->write('fnone',0);
								break;
						case 'fnone' :
								$this->Session->delete('msh');// Myspace fans
								$this->Session->delete('yth');// Youtube fans
								$this->Session->delete('lfmsh'); // last.fm fans
								$this->Session->delete('fbspages'); // facebook page flag
								$this->Session->delete('fbsgroups'); // facebook group flag
								if($this->Session->read('fnone')==1)
								{
									$this->Session->write('fnone',0);
								}
								else
								{
									$this->Session->write('fnone',1);
								}
								
								break;
						case 'tall' :
								$this->Session->write('msp','msp');// Myspace tracks
								$this->Session->write('ytp','ytp');// Youtube tracks
								$this->Session->write('lfmsp','lfmsp'); // last.fm tracks
								$this->Session->write('tnone',0);
								break;
			
						case 'tnone' :
								$this->Session->delete('msp');// Myspace tracks
								$this->Session->delete('ytp');// Youtube tracks
								$this->Session->delete('lfmsp'); // last.fm tracks
								
								if($this->Session->read('tnone')==1)
								{
									$this->Session->write('tnone',0);
								}
								else
								{
									$this->Session->write('tnone',1);
								}
								break;
						
						case 'call' :
								$this->Session->write('msc','msc');// Myspace comments
								$this->Session->write('ytc','ytc');// Youtube comments
								$this->Session->write('cnone',0);
								break;
				
						case 'cnone' :
							$this->Session->delete('msc');// Myspace comments
							$this->Session->delete('ytc');// Youtube comments
							
							if($this->Session->read('cnone')==1)
							{
								$this->Session->write('cnone',0);
							}
							else
							{
								$this->Session->write('cnone',1);
							}
							break;
						default :
						
							$type = $this->params['url']['type'];
							if($type=='fans')
							{
								$this->Session->write('fnone',0);	
							}
							elseif($type=='hits')
							{
								$this->Session->write('hnone',0);	
							}
							elseif($type=='tracks')
							{
								$this->Session->write('tnone',0);	
							}
							elseif($type=='comments')
							{
								$this->Session->write('cnone',0);	
							}
							
							
							if (!$this->Session->check($id))
							{
								$this->Session->write($id,$id);
				
							}
							else
							{
								$this->Session->delete($id);
							}
							break;
							
					}
					$this->set('id',$id);
				} // if(!empty($this->params['url']['id']))
				
				
				$this->sethitschart(); // get fans for facebook pages & groups , youtueb , myspace , last.fm
				if($this->params['url']['type']=='fans')
				{
					$this->Session->write('type','fans');
				}
				
				
				
				$this->setviewschart(); // get hits for youtube , myspace , last.fm
				if($this->params['url']['type']=='hits')
				{
					$this->Session->write('type','hits');
				}
				
				
				$this->setplayschart(); // get tracks for youtube , myspace , last.fm
				if($this->params['url']['type']=='tracks')
				{
					$this->Session->write('type','tracks');
				}
				
				$this->setcommentchart();  // get comments for youtube , myspace				
				if($this->params['url']['type']=='comments')
				{
					$this->Session->write('type','comments');
				}
				
				
			
		} // if(!empty($this->params['url']['date']) and !empty($this->params['url']['opt']))
		else   
		{
			
			
			
			if($this->Session->check('lastdate'))
			{
				$this->Session->delete('lastdate');
			}
			
			$this->Session->write('lastdate','w');
			$this->set('lastdate','w');
					
					
			
				$this->Session->write('msh','msh');// Myspace fans
				$this->Session->write('msv','msv');// Myspace hits
				$this->Session->write('msp','msp');// Myspace tracks
				$this->Session->write('msc','msc');// Myspace comments
			
				$this->Session->write('yth','yth');// Youtube fans
				$this->Session->write('ytv','ytv');// Youtube hits
				$this->Session->write('ytp','ytp');// Youtube tracks
				$this->Session->write('ytc','ytc');// Youtube comments
				
			
				$this->Session->write('lfmsh','lfmsh'); // last.fm fans
				$this->Session->write('lfmsv','lfmsv'); // last.fm hits
				$this->Session->write('lfmsp','lfmsp'); // last.fm tracks
			
	
				$this->Session->write('fbspages','fbspages'); // facebook page flag
				$this->Session->write('fbsgroups','fbsgroups'); // facebook group flag
			
			
			$this->Session->write('type','fans');
			
			$this->setcommentchart();  // get comments for youtube , myspace
			$this->setviewschart(); // get hits for youtube , myspace , last.fm
			$this->setplayschart(); // get tracks for youtube , myspace , last.fm
			$this->sethitschart(); // get fans for facebook , youtube , myspace & last.fm 
		
					
		} // if(!empty($this->params['url']['date']) and !empty($this->params['url']['opt']))
		
		$this->layout="stats";
	} // function chart()


	/*
	 Name	:	commentchart
	 Desc	:	Get graph values from sessions and display graph
	 */

	function commentchart()
	{

		$dt = $this->Session->read('dt');
		$mscomment = $this->Session->read('mscomment');
		$ytcomment = $this->Session->read('ytcomment');

		$this->set('dt',$dt);
		$this->set('mscomment',$mscomment);
		$this->set('ytcomment',$ytcomment);
	} // function setcommentchart()

	/*
	 Name	:	setcommentchart
	 Desc	:	Get graph values and return to comment()
	*/
	function setcommentchart()
	{


		
		if($this->Session->check('id') and $this->Session->check('band_id') and $this->Session->check('video'))
		{
		
		
		$mmm_id  =	$this->Session->read('id');
		$band_id =	$this->Session->read('band_id');
		$video =	$this->Session->read('video');
		
		} 
		else
		{
			//$this->Session->setFlash('Session Expired');
			//$this->redirect('/analytics/index/');
			return ;
		} // 		if($this->Session->check('email') and $this->Session->check('band_id') and $this->Session->check('video'))

		$mss= NULL;
		$yt=NULL;
		$album=NULL;

		if($this->Session->read('lastdate')=='w') // weekly graphs quries
		{

			$qry_mss = "select s.comments , s.etime from mss_stat s , mss_login l where s.mss_id = l.mss_id and l.band_id=$band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";

			$qry_yt = "select y.total_comments comments , y.etime from yt_comments_stat y , yt_login l where title='$video' and y.yt_id = l.yt_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";

			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msc'))
				{
					
					$mss = $this->Mss->findBySql($qry_mss);
				}

				if ($this->Session->check('ytc'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
				}

			} // 		if(!empty($this->params['url']['id']))
			else
			{


				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);


			} //		if(!empty($this->params['url']['id']))


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

		} // if($this->Session->read('lastdate')=='w') // weekly graphs quries
		elseif($this->Session->read('lastdate')=='m') // monthly graphs quries
		{

			$qry_mss = "select s.comments , s.etime from mss_stat s , mss_login l where s.mss_id = l.mss_id and l.band_id=$band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";

			$qry_yt = "select y.total_comments comments , y.etime from yt_comments_stat y , yt_login l where y.title='$video' and y.yt_id = l.yt_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";


			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msc'))
				{
					$mss = $this->Mss->findBySql($qry_mss);
				}

				if ($this->Session->check('ytc'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
				}

			} // 		if(!empty($this->params['url']['id']))
			else
			{
				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
			} //		if(!empty($this->params['url']['id']))

			$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE() curr";
			$tfdate = $this->Ytstat->findBySql($qry);

			$dt =NULL;
			$date = NULL;
			$c=0;
			
			$cdate= $tfdate['0']['0']['curr'];
			$curdate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($cdate)) . " -1 day"));

			$d=$tfdate['0']['0']['last'];
			$dt[$c]=date('Y-m-d',strtotime(date("Y-m-d", strtotime($d)) . " +1 day"));

			while($curdate!=$date)
			{
				$date = date('Y-m-d',strtotime(date("Y-m-d", strtotime($dt[$c])) . " +1 day"));
				$c ++;
				$dt[$c]= $date;

			} // while($tfdate['0']['0']['curr']!=$date)

			$this->Session->write('mm',count($dt));

		} // elseif($this->Session->read('lastdate')=='m') // monthly graphs quries

		elseif($this->Session->read('lastdate')=='y') // yearly graphs quries
		{
			$qry_mss = "select s.comments , s.etime from mss_stat s , mss_login l where  s.mss_id = l.mss_id and l.band_id=$band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";

			$qry_yt = "select y.total_comments comments , y.etime from yt_comments_stat y , yt_login l where  y.title='$video' and y.yt_id = l.yt_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";


			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msc'))
				{
					$mss = $this->Mss->findBySql($qry_mss);
					$mss_data = $this->getyear($mss,'s','comments');					
				}
				if ($this->Session->check('ytc'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
					$yt_data =$this->getyear($yt,'y','comments');
					
				}

			} // 		if(!empty($this->params['url']['id']))
			else
			{
				/*
				 select sum(s.views) , date_format(FROM_UNIXTIME(s.etime),'%m') from mss_stat s group by date_format(FROM_UNIXTIME(s.etime),'%m')
				 */

				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
				
				$mss_data = $this->getyear($mss,'s','comments');
				$yt_data =$this->getyear($yt,'y','comments');


			} //		if(!empty($this->params['url']['id']))

				
			

	
				/*
				Month Wise Yearly date create
				*/

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



		} // elseif($this->Session->read('lastdate')=='y') // yearly graphs quries

		$mscommentcount=NULL;
		$ytcommentcount=NULL;
			
		if($this->Session->read('lastdate')=='y') // set data according to date for yearly graph
		{

			$flg=0;
			if(empty($mss_data))
			{
				$mscount = 0;
			}
			else
			{
				$mscount = count($mss_data);
			}

			if(empty($yt_data))
			{
				$ytcount = 0;
			}
			else
			{
				$ytcount = count($yt_data);
			}
	
			$cnt=0;
			$ycnt=0;
			$lfcnt=0;
		

			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
				{
					if($mss_data[$i]['s']['etime']==$val)
					{
						$mscomment[$cnt]= $mss_data[$i]['s']['comments'] ;
						$mscommentcount	+= $mscomment[$cnt] ;
						$flg=1;
						$cnt++;
						break;
					} //	if($mss_data[$i]['s']['etime'])==$val)
				} // for($i=0 ; $i < $mscount-1 ; $i++)

				if($flg==0)
				{
					$mscomment[$cnt]=0;
					$cnt++;

				}


				$flg=0;
				$count=0;
				for($i=0 ; $i < $ytcount ; $i++) // set Youtube views according to main loop date
				{

					if($yt_data[$i]['y']['etime']==$val)
					{
						$ytcomment[$ycnt]= $yt_data[$i]['y']['comments'] ;
						$ytcommentcount+=$ytcomment[$ycnt] ;
						$flg=1;
						$ycnt ++;
						break;
					} //	if($yt_data[$i]['y']['etime']==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$ytcomment[$ycnt]=0;
					$ycnt++;

				}
					

			
				/*	if($flg==0 and $albumcount!=0)
				 {
					$album_play[]= 0;
					} // if($flg==0)
					*/
			} // foreach($dt as $key => $val)
		} // if($this->Session->read('lastdate')=='y')
		else   // // set data according to date for Weekly & Monthly Graph
		{

			$flg=0;
			$mscount = count($mss);
			$ytcount = count($yt);
			$mscomment = NULL;
			$ytcomment=NULL;
			$flg=0;
			$cnt=0;
			$ycnt=0;
			$lfcnt=0;

			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
				{
					if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					{

						if($i!=0)
						{
							$mscomment[$cnt]= $mss[$i]['s']['comments']-$mss[$i-1]['s']['comments'];
							$mscommentcount += $mscomment[$cnt] ;
						}
						else
						{
							$mscomment[$cnt] = 0;
						}
						$flg=1;
						$cnt++;

						break;

					} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
				} // for($i=0 ; $i < $mscount-1 ; $i++)

				if($flg==0)
				{
					$mscomment[$cnt]=0;
					$cnt++;

				}


				$flg=0;
				$count=0;
				for($i=0 ; $i < $ytcount ; $i++) // set Youtube views according to main loop date
				{

					if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)
					{

						if($i!=0)
						{
							$ytcomment[$ycnt]= $yt[$i]['y']['comments']-$yt[$i-1]['y']['comments'];
							$ytcommentcount = $ytcomment[$ycnt] ;

						}else
						{
							$ytcomment[$ycnt]=0;
						}
						$flg=1;
						$ycnt ++;
						break;
					} //	if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$ytcomment[$ycnt]=0;
					$ycnt++;

				}
					
			} // foreach($dt as $key => $val)
		} // if($this->Session->read('lastdate')=='y')

		/*
		*	Session to set Average hits
		*/
		$this->Session->write('ytcommentcount',$ytcommentcount);
		$this->Session->write('mscommentcount',$mscommentcount);				

		/*
		*	youtube and myspace data
		*/
		$this->Session->write('dt',$dt);
		$this->Session->write('mscomment',$mscomment);
		$this->Session->write('ytcomment',$ytcomment);
		
		$msaverage  = 0;
		$ytaverage  = 0;
		$lfmaverage = 0;


		if($this->Session->read('lastdate')=='w')
		{
			$msaverage = round($mscommentcount/7,2);
			$ytaverage = round($ytcommentcount/7,2);
		}
		elseif($this->Session->read('lastdate')=='m')
		{
			$mm=$this->Session->read('mm');
			$msaverage = round($mscommentcount/$mm,2);
			$ytaverage = round($ytcommentcount/$mm,2);
		}
		elseif($this->Session->read('lastdate')=='y')
		{
			$msaverage = round($mscommentcount/12,2);
			$ytaverage = round($ytcommentcount/12,2);
		}

		$this->Session->write('msaverage',$msaverage);
		$this->Session->write('ytaverage',$ytaverage);
		$this->Session->write('lfmaverage',$lfmaverage);
		
		return true;		

	} // function setcommentchart()


	
	/*
	 Name	:	viewschart
	 Desc	:	Get graph values from Session and display graph
	 */
	function viewschart()
	{

		$dt = $this->Session->read('dt');
		$msview = $this->Session->read('msview');
		$ytview = $this->Session->read('ytview');
		$lfmplay = $this->Session->read('lfmplay');

		$this->set('dt',$dt);
		$this->set('msview',$msview);
		$this->set('ytview',$ytview);
		$this->set('lfmplay',$lfmplay);
	}



	/*
	 Name	:	setviewschart
	 Desc	:	Get graph values and return to views()
	 */
	function setviewschart()
	{
		
		if($this->Session->check('id') and $this->Session->check('band_id'))
		{
		
		$mmm_id  =	$this->Session->read('id');
		$band_id =	$this->Session->read('band_id');
		
		} 
		else
		{
			//$this->Session->setFlash('Session Expired');
			//$this->redirect('/analytics/index/');
			return ;
		} // 		if($this->Session->check('music') and $this->Session->check('profile') and $this->Session->check('channel') and $this->Session->check('email') and $this->Session->check('topalbum'))


		$mss= NULL;
		$yt=NULL;
		$album=NULL;
		
		if($this->Session->read('lastdate')=='w') // weekly graphs quries
		{

			$qry_mss = "select s.views , s.etime from mss_stat s , mss_login l where s.mss_id = l.mss_id and l.band_id=$band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";

			$qry_yt = "select y.views , y.etime from yt_stat y , yt_login l where y.yt_id = l.yt_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";

			$qry_lfm = "select sum(t.playcount) playcount  , t.etime from lfm_top_tracks t , lfm_music l where l.lfm_m_id =t.lfm_m_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and FROM_UNIXTIME(t.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) group by t.etime order by etime";
			
			
		   			
			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msv'))
				{
					$mss = $this->Mss->findBySql($qry_mss);
				}

				if ($this->Session->check('ytv'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
				}

				if ($this->Session->check('lfmsv'))
				{


					$album = $this->Lfmalbum->findBySql($qry_lfm);
				}
			} // 		if(!empty($this->params['url']['id']))
			else
			{
				
				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
				$album = $this->Lfmalbum->findBySql($qry_lfm);

			} //		if(!empty($this->params['url']['id']))
			
			
			
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

		} // if($this->Session->read('lastdate')=='w') // weekly graphs quries
		elseif($this->Session->read('lastdate')=='m') // monthly graphs quries
		{

				$qry_mss = "select s.views , s.etime from mss_stat s , mss_login l where  s.mss_id = l.mss_id and l.band_id=$band_id and l.mmm_id ='$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";

				$qry_yt = "select y.views , y.etime from yt_stat y , yt_login l where  y.yt_id = l.yt_id  and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";

				$qry_lfm = "select sum(t.playcount) playcount  , t.etime from lfm_top_tracks t , lfm_music l where l.lfm_m_id =t.lfm_m_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and FROM_UNIXTIME(t.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) group by t.etime order by etime";
				

			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msv'))
				{
					$mss = $this->Mss->findBySql($qry_mss);
	

				}

				if ($this->Session->check('ytv'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
				}

				if ($this->Session->check('lfmsv'))
				{

					$album = $this->Lfmalbum->findBySql($qry_lfm);
				}
			} // 		if(!empty($this->params['url']['id']))
			else
			{


				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
				$album = $this->Lfmalbum->findBySql($qry_lfm);

			} //		if(!empty($this->params['url']['id']))


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

			$this->Session->write('mm',count($dt));

		} // elseif($this->Session->read('lastdate')=='m') // monthly graphs quries

		elseif($this->Session->read('lastdate')=='y') // yearly graphs quries
		{

			$qry_mss = "select s.views , s.etime from mss_stat s , mss_login l where  s.mss_id = l.mss_id and l.band_id=$band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";

			$qry_yt = "select y.views , y.etime from yt_stat y , yt_login l where  y.yt_id = l.yt_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";

			// special condition applied in getyear function because of sum(t.playcount) reutrn [0][playcount] instead of [t][playcount]
			$qry_lfm = "select sum(t.playcount) playcount  , t.etime etime from lfm_top_tracks t , lfm_music l where l.lfm_m_id =t.lfm_m_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and FROM_UNIXTIME(t.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) group by t.etime order by t.etime"; 

			

			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msv'))
				{
					$mss = $this->Mss->findBySql($qry_mss);
					$mss_data = $this->getyear($mss,'s','views');
					
				}

				if ($this->Session->check('ytv'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
					$yt_data =$this->getyear($yt,'y','views');
					
				}

				if ($this->Session->check('lfmsv'))
				{

					$album = $this->Lfmalbum->findBySql($qry_lfm);
					$album_data = $this->getyear($album,'t','playcount');
					
						
				}
			} // 		if(!empty($this->params['url']['id']))
			else
			{

				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
				$album = $this->Lfmalbum->findBySql($qry_lfm);
				
				
				$mss_data = $this->getyear($mss,'s','views');
				$yt_data =$this->getyear($yt,'y','views');
				$album_data = $this->getyear($album,'t','playcount');
			} //		if(!empty($this->params['url']['id']))

			

			/*
				Month Wise Yearly date create
				*/

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



		} // elseif($this->Session->read('lastdate')=='y') // yearly graphs quries

			$msviewscount = NULL;
			$ytviewscount = NULL;
			$lfmviewscount = NULL;
			
		if($this->Session->read('lastdate')=='y') // set data according to date for yearly graph
		{

			$flg=0;
			if(empty($mss_data))
			{
				$mscount = 0;
			}
			else
			{
				$mscount = count($mss_data);
			}

			if(empty($yt_data))
			{
				$ytcount = 0;
			}
			else
			{
				$ytcount = count($yt_data);
			}
	
			if(empty($album_data))
			{
				$albumcount = 0;
			}
			else
			{
				$albumcount = count($album_data);
			}

			
			
			$flg=0;
			$cnt=0;
			$ycnt=0;
			$lfcnt=0;


			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
				{
					if($mss_data[$i]['s']['etime']==$val)
					{
						$msview[$cnt]= $mss_data[$i]['s']['views'] ;
						$msviewscount+=$msview[$cnt];
						
						$flg=1;
						$cnt++;
						break;
					} //	if($mss_data[$i]['s']['etime'])==$val)
				} // for($i=0 ; $i < $mscount-1 ; $i++)

				if($flg==0)
				{
					$msview[$cnt]=0;
					$cnt++;

				}


				$flg=0;
				$count=0;
				for($i=0 ; $i < $ytcount ; $i++) // set Youtube views according to main loop date
				{

					if($yt_data[$i]['y']['etime']==$val)
					{
						$ytview[$ycnt]= $yt_data[$i]['y']['views'] ;
						$ytviewscount+=$ytview[$ycnt];
						$flg=1;
						$ycnt ++;
						break;
						
					} //	if($yt_data[$i]['y']['etime']==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$ytview[$ycnt]=0;
					$ycnt++;

				}
					

				
				$flg=0;
				for($i=0 ; $i < $albumcount ; $i++)	// set Las.fm top album playcount according to main loop date
				{
					if($album_data[$i]['t']['etime']==$val)
					{

						$lfmplay[$lfcnt]= $album_data[$i]['t']['playcount'];
						$lfmviewscount+=$album_play[$lfcnt];
						$flg=1;
						$lfcnt++;
						break;
					} //	if($album_data[$i]['t']['etime']==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$lfmplay[$lfcnt]=0;
					$lfcnt++;

				}

					
				/*	if($flg==0 and $albumcount!=0)
				 {
					$album_play[]= 0;
					} // if($flg==0)
					*/
			} // foreach($dt as $key => $val)
		} // if($this->Session->read('lastdate')=='y')
		else   // // set data according to date for Weekly & Monthly Graph
		{

			$flg=0;
			$mscount = count($mss);
			$ytcount = count($yt);
			$albumcount = count($album);
		
			
			
			$flg=0;
			$cnt=0;
			$ycnt=0;
			$lfcnt=0;
			
			
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
				{
					if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					{

						if($i!=0)
						{
							$msview[$cnt]= $mss[$i]['s']['views']-$mss[$i-1]['s']['views'];
							$msviewscount+=$msview[$cnt];
						}
						else
						{
							$msview[$cnt] = 0;
						}
						$flg=1;
						$cnt++;

						break;

					} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
				} // for($i=0 ; $i < $mscount-1 ; $i++)

				if($flg==0)
				{
					$msview[$cnt]=0;
					$cnt++;

				}


				$flg=0;
				$count=0;
				for($i=0 ; $i < $ytcount ; $i++) // set Youtube views according to main loop date
				{

					if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)
					{

						if($i!=0)
						{
							$ytview[$ycnt]= $yt[$i]['y']['views']-$yt[$i-1]['y']['views'];
							$ytviewscount+=$ytview[$ycnt];

						}else
						{
							$ytview[$ycnt]=0;
						}
						$flg=1;
						$ycnt ++;
						break;
					} //	if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$ytview[$ycnt]=0;
					$ycnt++;

				}
					


				$flg=0;
				for($i=0 ; $i < $albumcount ; $i++)	// set Las.fm top album playcount according to main loop date
				{
					if(date('Y-m-d',$album[$i]['t']['etime'])==$val)
					{
						if($i!=0)
						{
							$lfmplay[$lfcnt]= $album[$i][0]['playcount']-$album[$i-1][0]['playcount'];
							$lfmviewscount+=$lfmplay[$lfcnt];

						}
						else
						{
							$lfmplay[$lfcnt]=0;
						}
						$flg=1;
						$lfcnt++;
						break;
					} //	if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					
					$lfmplay[$lfcnt]=0;
					$lfcnt++;

				}

				/*	if($flg==0 and $albumcount!=0)
				 {
					$album_play[]= 0;
					} // if($flg==0)
					*/
			} // foreach($dt as $key => $val)
		} // if($this->Session->read('lastdate')=='y')



		/*
		*	Session set to get weekly , monthly & yearly average / Percentage
		*/			
		$this->Session->write('msviewscount',$msviewscount);
		$this->Session->write('ytviewscount',$ytviewscount);
		$this->Session->write('lfmviewscount',$lfmviewscount);


		
	
		/*
		*	Session to set Graph data
		*/
		$this->Session->write('dt',$dt);
		$this->Session->write('msview',$msview);
		$this->Session->write('ytview',$ytview);
		$this->Session->write('lfmplay',$lfmplay);

			$msaverage = 0;
			$ytaverage = 0;
			$lfmaverage = 0;

			if($this->Session->read('lastdate')=='w')
			{
				$msaverage = round($msviewscount/7,2);
				$ytaverage = round($ytviewscount/7,2);
				$lfmaverage = round($lfmviewscount/7,2);
	
			}
			elseif($this->Session->read('lastdate')=='m')
			{
				$mm=$this->Session->read('mm');
				$msaverage = round($msviewscount/$mm,2);
				$ytaverage = round($ytviewscount/$mm,2);
				$lfmaverage = round($lfmviewscount/$mm,2);
	
			}
			elseif($this->Session->read('lastdate')=='y')
			{
				$msaverage = round($msviewscount/12,2);
				$ytaverage = round($ytviewscount/12,2);
				$lfmaverage = round($lfmviewscount/12,2);
	
			}
	
			$this->Session->write('msaverage',$msaverage);
			$this->Session->write('ytaverage',$ytaverage);
			$this->Session->write('lfmaverage',$lfmaverage);
			
		return true;

	} // function viewschart()


	/*
	 Name	:	playschart
	 Desc	:	Get graph values from sessions and display graph
	 */

	function playschart()
	{

		$dt = $this->Session->read('dt');
		$msplays = $this->Session->read('msplays');
		$ytplays = $this->Session->read('ytplays');
		$lfmplays = $this->Session->read('lfmplays');

		
		$this->set('dt',$dt);
		$this->set('msplays',$msplays);
		$this->set('ytplays',$ytplays);
		$this->set('lfmplays',$lfmplays);
				
	} // function playschart()


/*
	 Name	:	setplayschart
	 Desc	:	Get graph values and return to plays()
	*/
	function setplayschart()
	{

		if($this->Session->check('id') and $this->Session->check('band_id') and $this->Session->check('mssvideo') and $this->Session->check('video') and $this->Session->check('toptrack'))
		{
		
		$mmm_id  =	$this->Session->read('id');
		$band_id =	$this->Session->read('band_id');
		
		$mssvideo = 	addslashes($this->Session->read('mssvideo'));
		$video =	addslashes($this->Session->read('video'));
		$lfm_track =	addslashes($this->Session->read('toptrack'));
		
		} 
		else
		{
			//$this->Session->setFlash('Session Expired');
			//$this->redirect('/analytics/index/');
			return ;
		} // 		if($this->Session->check('music') and $this->Session->check('profile') and $this->Session->ch


		$mss= NULL;
		$yt=NULL;
		$album=NULL;

		if($this->Session->read('lastdate')=='w') // weekly graphs quries
		{

			$qry_mss = "select s.plays , s.etime from mss_play_stat s , mss_login l where s.title='$mssvideo' and s.mss_id = l.mss_id and l.band_id=$band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";
			
			$qry_yt = "select y.views plays , y.etime from yt_comments_stat y , yt_login l where title='$video' and y.yt_id = l.yt_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";

			$qry_lfm = "select t.playcount plays , t.etime from lfm_top_tracks t , lfm_music l where t.name='$lfm_track' and l.lfm_m_id =t.lfm_m_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and FROM_UNIXTIME(t.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by	etime";


			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msp'))
				{
					
					$mss = $this->Mss->findBySql($qry_mss);
				}

				if ($this->Session->check('ytp'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
				}

				if ($this->Session->check('lfmsp'))
				{
					$lfm = $this->Ytstat->findBySql($qry_lfm);
				}


			} // 		if(!empty($this->params['url']['id']))
			else
			{


				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
				$lfm = $this->Ytstat->findBySql($qry_lfm);


			} //		if(!empty($this->params['url']['id']))



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

		} // if($this->Session->read('lastdate')=='w') // weekly graphs quries
		elseif($this->Session->read('lastdate')=='m') // monthly graphs quries
		{

			$qry_mss = "select s.plays , s.etime from mss_play_stat s , mss_login l where s.title='$mssvideo' and s.mss_id = l.mss_id and l.band_id=$band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";

			$qry_yt = "select y.views plays , y.etime from yt_comments_stat y , yt_login l where y.title='$video' and y.yt_id = l.yt_id and l.band_id=$band_id  and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";

			$qry_lfm = "select t.playcount plays , t.etime from lfm_top_tracks t , lfm_music l where t.name='$lfm_track' and l.lfm_m_id =t.lfm_m_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and FROM_UNIXTIME(t.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by	etime";


			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msp'))
				{
					$mss = $this->Mss->findBySql($qry_mss);
				}

				if ($this->Session->check('ytp'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
				}

				if ($this->Session->check('lfmsp'))
				{
					$lfm = $this->Ytstat->findBySql($qry_lfm);
				}
			} // 		if(!empty($this->params['url']['id']))
			else
			{
				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
				$lfm = $this->Ytstat->findBySql($qry_lfm);
			} //		if(!empty($this->params['url']['id']))

			$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE() curr";
			$tfdate = $this->Ytstat->findBySql($qry);

			$dt =NULL;
			$date = NULL;
			$c=0;
			
			$cdate= $tfdate['0']['0']['curr'];
			$curdate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($cdate)) . " -1 day"));

			$d=$tfdate['0']['0']['last'];
			$dt[$c]=date('Y-m-d',strtotime(date("Y-m-d", strtotime($d)) . " +1 day"));

			while($curdate!=$date)
			{
				$date = date('Y-m-d',strtotime(date("Y-m-d", strtotime($dt[$c])) . " +1 day"));
				$c ++;
				$dt[$c]= $date;

			} // while($tfdate['0']['0']['curr']!=$date)

			$this->Session->write('mm',count($dt));

		} // elseif($this->Session->read('lastdate')=='m') // monthly graphs quries

		elseif($this->Session->read('lastdate')=='y') // yearly graphs quries
		{
			$qry_mss = "select s.plays , s.etime from mss_play_stat s , mss_login l where s.title='$mssvideo' and s.mss_id = l.mss_id and l.band_id=$band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";

			$qry_yt = "select y.views plays , y.etime from yt_comments_stat y , yt_login l where y.title='$video' and y.yt_id = l.yt_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";

			$qry_lfm = "select t.playcount plays , t.etime from lfm_top_tracks t , lfm_music l where t.name='$lfm_track' and l.lfm_m_id =t.lfm_m_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(t.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by	etime";


			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msp'))
				{
					$mss = $this->Mss->findBySql($qry_mss);
					$mss_data = $this->getyear($mss,'s','plays');					
				}
				if ($this->Session->check('ytp'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
					$yt_data =$this->getyear($yt,'y','plays');
					
				}

				if ($this->Session->check('lfmsp'))
				{
					$lfm = $this->Ytstat->findBySql($qry_lfm);
					$lfm_data =$this->getyear($lfm,'t','plays');
					
				}

			} // 		if(!empty($this->params['url']['id']))
			else
			{
				/*
				 select sum(s.views) , date_format(FROM_UNIXTIME(s.etime),'%m') from mss_stat s group by date_format(FROM_UNIXTIME(s.etime),'%m')
				 */

				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
				$lfm = $this->Ytstat->findBySql($qry_lfm);
				
				$mss_data = $this->getyear($mss,'s','plays');
				$yt_data =$this->getyear($yt,'y','plays');
				$lfm_data =$this->getyear($lfm,'t','plays');


			} //		if(!empty($this->params['url']['id']))

				
			

			/*
				Month Wise Yearly date create
				*/

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



		} // elseif($this->Session->read('lastdate')=='y') // yearly graphs quries

		$msplayscount=0;
		$ytplayscount=0;
		$lfmplayscount=0;
			
		if($this->Session->read('lastdate')=='y') // set data according to date for yearly graph
		{

			$flg=0;
			if(empty($mss_data))
			{
				$mscount = 0;
			}
			else
			{
				$mscount = count($mss_data);
			}

			if(empty($yt_data))
			{
				$ytcount = 0;
			}
			else
			{
				$ytcount = count($yt_data);
			}
	
			if(empty($lfm_data))
			{
				$lfmcount = 0;
			}
			else
			{
				$lfmcount = count($lfm_data);
			}
	
			$cnt=0;
			$ycnt=0;
			$lfcnt=0;
		

			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
				{
					if($mss_data[$i]['s']['etime']==$val)
					{
						$msplays[$cnt]= $mss_data[$i]['s']['plays'] ;
						$msplayscount += $msplays[$cnt] ;
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


				$flg=0;
				$count=0;
				for($i=0 ; $i < $ytcount ; $i++) // set Youtube views according to main loop date
				{

					if($yt_data[$i]['y']['etime']==$val)
					{
						$ytplays[$ycnt]= $yt_data[$i]['y']['plays'] ;
						$ytplayscount+=$ytplays[$ycnt] ;
						$flg=1;
						$ycnt ++;
						break;
					} //	if($yt_data[$i]['y']['etime']==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$ytplays[$ycnt]=0;
					$ycnt++;

				}

				$flg=0;
				$count=0;

				for($i=0 ; $i < $lfmcount ; $i++) // set Youtube views according to main loop date
				{

					if($lfm_data[$i]['t']['etime']==$val)
					{
						$lfmplays[$lfcnt]= $lfm_data[$i]['t']['plays'] ;
						$lfmplayscount+=$lfmplays[$lfcnt] ;
						$flg=1;
						$lfcnt ++;
						break;
					} //	if($yt_data[$i]['y']['etime']==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$lfmplays[$lfcnt]=0;
					$lfcnt++;

				}
					

			
				/*	if($flg==0 and $albumcount!=0)
				 {
					$album_play[]= 0;
					} // if($flg==0)
					*/
			} // foreach($dt as $key => $val)
		} // if($this->Session->read('lastdate')=='y')
		else   // // set data according to date for Weekly & Monthly Graph
		{

			$flg=0;
			if(empty($mss))	
			{
			$mscount=0;
			}
			else
			{
			$mscount = count($mss);
			}

			if(empty($yt))	
			{
			$ytcount=0;
			}
			else
			{
			$ytcount = count($yt);
			}

			if(empty($lfm))	
			{
			$lfmcount=0;
			}
			else
			{
			$lfmcount = count($lfm);
			}
			

			$flg=0;
			$cnt=0;
			$ycnt=0;
			$lfcnt=0;
			
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
				{
					if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					{

						if($i!=0)
						{
							$msplays[$cnt]= $mss[$i]['s']['plays']-$mss[$i-1]['s']['plays'];
							$msplayscount += $msplays[$cnt] ;
						}
						else
						{
							$msplays[$cnt] = 0;
						}
						$flg=1;
						$cnt++;

						break;

					} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
				} // for($i=0 ; $i < $mscount-1 ; $i++)

				if($flg==0)
				{
					$msplays[$cnt]=0;
					$cnt++;

				}


				$flg=0;
				$count=0;
				for($i=0 ; $i < $ytcount ; $i++) // set Youtube views according to main loop date
				{

					if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)
					{

						if($i!=0)
						{
							$ytplays[$ycnt]= $yt[$i]['y']['plays']-$yt[$i-1]['y']['plays'];
							$ytplayscount += $ytplays[$ycnt] ;

						}else
						{
							$ytplays[$ycnt]=0;
						}
						$flg=1;
						$ycnt ++;
						break;
					} //	if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$ytplays[$ycnt]=0;
					$ycnt++;

				}

				$flg=0;
				$count=0;
				
				for($i=0 ; $i < $lfmcount ; $i++) // set Youtube views according to main loop date
				{
					

					if(date('Y-m-d',$lfm[$i]['t']['etime'])==$val)
					{
						
						if($i!=0)
						{
							$lfmplays[$lfcnt]= $lfm[$i]['t']['plays']-$lfm[$i-1]['t']['plays'];
							$lfmplayscount += $lfmplays[$lfcnt] ;
							

						}else
						{
							$lfmplays[$lfcnt]=0;
						}
						$flg=1;
						$lfcnt ++;
						break;
					} //	if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$lfmplays[$lfcnt]=0;
					$lfcnt++;

				}
					
			} // foreach($dt as $key => $val)
		} // if($this->Session->read('lastdate')=='y')

		/*
		*	Session to set Average hits
		*/
		$this->Session->write('ytplayscount',$ytplayscount);
		$this->Session->write('msplayscount',$msplayscount);
		$this->Session->write('lfmplayscount',$lfmplayscount);								

		
		/*
		*	youtube and myspace data
		*/
		$this->Session->write('dt',$dt);
		$this->Session->write('msplays',$msplays);
		$this->Session->write('ytplays',$ytplays);
		$this->Session->write('lfmplays',$lfmplays);
		
		
		
		$msaverage = NULL;
		$ytaverage = NULL;
		$lfmaverage = NULL;

		if($this->Session->read('lastdate')=='w')
		{
			
			$msaverage = round($msplayscount/7,2);
			$ytaverage = round($ytplayscount/7,2);
			$lfmaverage = round($lfmplayscount/7,2);

		}
		elseif($this->Session->read('lastdate')=='m')
		{
			$mm=$this->Session->read('mm');
			$msaverage = round($msplayscount/$mm,2);
			$ytaverage = round($ytplayscount/$mm,2);
			$lfmaverage = round($lfmplayscount/$mm,2);

		}
		elseif($this->Session->read('lastdate')=='y')
		{
			$msaverage = round($msplayscount/12,2);
			$ytaverage = round($ytplayscount/12,2);
			$lfmaverage = round($lfmplayscount/12,2);

		}

		$this->Session->write('msaverage',$msaverage);
		$this->Session->write('ytaverage',$ytaverage);
		$this->Session->write('lfmaverage',$lfmaverage);
		
		return true;		

	} // function setplayschart()



	/*
	 Name	:	hitschart
	 Desc	:	Get graph values from Session and display graph
	 */
	function hitschart()
	{

		$dt = $this->Session->read('dt');
		$pageshits = $this->Session->read('pageshits');
		$groupshits = $this->Session->read('groupshits');
		$mshit = $this->Session->read('mshit');
		$ythit = $this->Session->read('ythit');
		$album_play = $this->Session->read('album_play');

		
		$this->set('dt',$dt);
		$this->set('pageshits',$pageshits);
		$this->set('groupshits',$groupshits);
		$this->set('mshit',$mshit);
		$this->set('ythit',$ythit);
		$this->set('album_play',$album_play);
	}



	/*
	 Name	:	sethitschart
	 Desc	:	Get graph values and return to hit()
	 */
	function sethitschart()
	{

		if($this->Session->check('id') and $this->Session->check('band_id'))
		{
		
		$mmm_id  =	$this->Session->read('id');
		$band_id =	$this->Session->read('band_id');
		
		$fbpages = addslashes($this->Session->read('fbpages'));
		$fbgroups = addslashes($this->Session->read('fbgroups'));


		} 
		else
		{
			//$this->Session->setFlash('Session Expired');
			//$this->redirect('/analytics/index/');
			return ;
		} // 		if($this->Session->check('music') and $this->Session->check('profile') and $this->Session->check('channel') and $this->Session->check('email') and $this->Session->check('topalbum'))


		$mss= 0;
		$yt=0;
		$album=0;
		$pages=0;
		$groups=0;

		if($this->Session->read('lastdate')=='w') // weekly graphs quries
		{

			$qry_mss = "select s.friends fbs , s.etime from mss_stat s , mss_login l where s.mss_id = l.mss_id and l.band_id = $band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";
			
			$qry_yt = "select y.subscriber fbs , y.etime from yt_stat y , yt_login l where y.yt_id = l.yt_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";

			$qry_lfm = "select t.listeners fbs , t.etime from lfm_listeners t , lfm_music l where l.lfm_m_id =t.lfm_m_id and l.band_id=$band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(t.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";

			$qry_fbs_pages = "select s.fan_count fbs , s.etime from fb_pages s , fb_login l where s.name='$fbpages' and s.login_id = l.login_id and l.band_id=$band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";

			$qry_fbs_groups = "select s.member fbs , s.etime from fb_group s , fb_login l where s.name='$fbgroups' and s.login_id = l.login_id and l.band_id=$band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 9 DAY) AND CURDATE( ) order by etime";



			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msh'))
				{
					$mss = $this->Mss->findBySql($qry_mss);
				}

				if ($this->Session->check('yth'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
				}

				if ($this->Session->check('lfmsh'))
				{


					$album = $this->Lfmalbum->findBySql($qry_lfm);
				}

				if ($this->Session->check('fbspages'))
				{
					$pages = $this->Lfmalbum->findBySql($qry_fbs_pages);
				}

				if ($this->Session->check('fbsgroups'))
				{
					$groups = $this->Lfmalbum->findBySql($qry_fbs_groups);
				}

			} // 		if(!empty($this->params['url']['id']))
			else
			{

				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
				$album = $this->Lfmalbum->findBySql($qry_lfm);
				$pages = $this->Lfmalbum->findBySql($qry_fbs_pages);
				$groups = $this->Lfmalbum->findBySql($qry_fbs_groups);

			} //		if(!empty($this->params['url']['id']))

		
		

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

		} // if($this->Session->read('lastdate')=='w') // weekly graphs quries
		elseif($this->Session->read('lastdate')=='m') // monthly graphs quries
		{

			$qry_mss = "select s.friends fbs , s.etime from mss_stat s , mss_login l where  s.mss_id = l.mss_id and l.band_id = $band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";

			$qry_yt = "select y.subscriber fbs , y.etime from yt_stat y , yt_login l where  y.yt_id = l.yt_id and l.band_id = $band_id and l.mmm_id='$mmm_id' and l.status=1 and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";


			$qry_lfm = "select t.listeners fbs , t.etime from lfm_listeners t , lfm_music l where  l.lfm_m_id =t.lfm_m_id and l.band_id = $band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(t.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by	etime";

			$qry_fbs_pages = "select s.fan_count fbs , s.etime  from fb_pages s , fb_login l where  s.name='$fbpages' and s.login_id = l.login_id and  l.band_id = $band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by	etime";

			$qry_fbs_groups = "select s.member fbs , s.etime from fb_group s , fb_login l where  s.name='$fbgroups' and s.login_id = l.login_id and l.band_id = $band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";


				
			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msh'))
				{
					$mss = $this->Mss->findBySql($qry_mss);
	

				}

				if ($this->Session->check('yth'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
				}

				if ($this->Session->check('lfmsh'))
				{

					$album = $this->Lfmalbum->findBySql($qry_lfm);
				}

				if ($this->Session->check('fbspages'))
				{

					$pages = $this->Lfmalbum->findBySql($qry_fbs_pages);
				}

				if ($this->Session->check('fbsgroups'))
				{
					$groups = $this->Lfmalbum->findBySql($qry_fbs_groups);
				}

			} // 		if(!empty($this->params['url']['id']))
			else
			{


				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
				$album = $this->Lfmalbum->findBySql($qry_lfm);
				$pages = $this->Lfmalbum->findBySql($qry_fbs_pages);
				$groups = $this->Lfmalbum->findBySql($qry_fbs_groups);

			} //		if(!empty($this->params['url']['id']))


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

			$this->Session->write('mm',count($dt));

		} // elseif($this->Session->read('lastdate')=='m') // monthly graphs quries

		elseif($this->Session->read('lastdate')=='y') // yearly graphs quries
		{

			$qry_mss = "select s.friends fbs , s.etime from mss_stat s , mss_login l where  s.mss_id = l.mss_id and l.band_id = $band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";

			$qry_yt = "select y.subscriber fbs , y.etime from yt_stat y , yt_login l where  y.yt_id = l.yt_id and l.band_id = $band_id and l.mmm_id='$mmm_id' and l.status=1 and FROM_UNIXTIME(y.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by etime";


			$qry_lfm = "select t.listeners fbs , t.etime from lfm_listeners t , lfm_music l where  l.lfm_m_id =t.lfm_m_id and l.band_id = $band_id and l.mmm_id='$mmm_id' and l.status=1 and 	FROM_UNIXTIME(t.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by	etime";

			$qry_fbs_pages = "select s.fan_count fbs , s.etime from fb_pages s , fb_login l where  s.name='$fbpages' and s.login_id = l.login_id and l.band_id = $band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by	etime";

			$qry_fbs_groups = "select s.member fbs , s.etime from fb_group s , fb_login l where  s.name='$fbgroups' and s.login_id = l.login_id and l.band_id = $band_id and l.mmm_id = '$mmm_id' and l.status=1 and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND CURDATE( ) order by	etime";



			if(!empty($this->params['url']['id']))
			{

				if ($this->Session->check('msh'))
				{
					$mss = $this->Mss->findBySql($qry_mss);
					$mss_data = $this->getyear($mss,'s','fbs');
					
				}

				if ($this->Session->check('yth'))
				{
					$yt = $this->Ytstat->findBySql($qry_yt);
					$yt_data =$this->getyear($yt,'y','fbs');
					
				}

				if ($this->Session->check('lfmsh'))
				{

					$album = $this->Lfmalbum->findBySql($qry_lfm);
					$album_data = $this->getyear($album,'t','fbs');
					
						
				}

				if ($this->Session->check('fbspages'))
				{

					$pages = $this->Lfmalbum->findBySql($qry_fbs_pages);
					$pages_data = $this->getyear($pages,'s','fbs');

				}

				if ($this->Session->check('fbsgroups'))
				{

					$groups = $this->Lfmalbum->findBySql($qry_fbs_groups);
					$groups_data = $this->getyear($groups,'s','fbs');
					
						
				}
			} // 		if(!empty($this->params['url']['id']))
			else
			{

				$mss = $this->Mss->findBySql($qry_mss);
				$yt = $this->Ytstat->findBySql($qry_yt);
				$album = $this->Lfmalbum->findBySql($qry_lfm);
				$pages = $this->Lfmalbum->findBySql($qry_fbs_pages);
				$groups = $this->Lfmalbum->findBySql($qry_fbs_groups);

				$mss_data = $this->getyear($mss,'s','fbs');
				$yt_data =$this->getyear($yt,'y','fbs');
				$album_data = $this->getyear($album,'t','fbs');
				$pages_data = $this->getyear($pages,'s','fbs');
				$groups_data = $this->getyear($groups,'s','fbs');
				

			} //		if(!empty($this->params['url']['id']))

			

			/*
				Month Wise Yearly date create
				*/

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



		} // elseif($this->Session->read('lastdate')=='y') // yearly graphs quries

		$mshitscount = 0;
		$ythitscount = 0;
		$lfmhitscount = 0;
		$groupshitscount=0;
		$pageshitscount=0;
		
			
		if($this->Session->read('lastdate')=='y') // set data according to date for yearly graph
		{

			$flg=0;
			if(empty($mss_data))
			{
				$mscount = 0;
			}
			else
			{
				$mscount = count($mss_data);
			}

			if(empty($yt_data))
			{
				$ytcount = 0;
			}
			else
			{
				$ytcount = count($yt_data);
			}
	
			if(empty($album_data))
			{
				$albumcount = 0;
			}
			else
			{
				$albumcount = count($album_data);
			}

			if(empty($pages_data))
			{
				$pagescount = 0;
			}
			else
			{
				$pagescount = count($pages_data);
			}

			if(empty($groups_data))
			{
				$groupscount = 0;
			}
			else
			{
				$groupscount = count($groups_data);
			}

			
			$flg=0;
			$cnt=0;
			$ycnt=0;
			$lfcnt=0;
			$pcnt=0;
			$gcnt=0;


			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
				{
					if($mss_data[$i]['s']['etime']==$val)
					{
						$mshit[$cnt]= $mss_data[$i]['s']['fbs'] ;
						$mshitscount+=$mshit[$cnt];
						
						$flg=1;
						$cnt++;
						break;
					} //	if($mss_data[$i]['s']['etime'])==$val)
				} // for($i=0 ; $i < $mscount-1 ; $i++)

				if($flg==0)
				{
					$mshit[$cnt]=0;
					$cnt++;

				}


				$flg=0;
				$count=0;
				for($i=0 ; $i < $ytcount ; $i++) // set Youtube views according to main loop date
				{

					if($yt_data[$i]['y']['etime']==$val)
					{
						$ythit[$ycnt]= $yt_data[$i]['y']['fbs'] ;
						$ythitscount+=$ythit[$ycnt];
						$flg=1;
						$ycnt ++;
						break;
						
					} //	if($yt_data[$i]['y']['etime']==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$ythit[$ycnt]=0;
					$ycnt++;

				}
					

				
				$flg=0;
				for($i=0 ; $i < $albumcount ; $i++)	// set Las.fm top album playcount according to main loop date
				{
					if($album_data[$i]['t']['etime']==$val)
					{

						$album_play[$lfcnt]= $album_data[$i]['t']['fbs'];
						$lfmhitscount+=$album_play[$lfcnt];
						$flg=1;
						$lfcnt++;
						break;
					} //	if($album_data[$i]['t']['etime']==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$album_play[$lfcnt]=0;
					$lfcnt++;

				}

				
				$flg=0;
				$count=0;
				for($i=0 ; $i < $pagescount ; $i++) // set Youtube views according to main loop date
				{

					if($pages_data[$i]['s']['etime']==$val)
					{
						
						$pageshits[$pcnt]= $pages_data[$i]['s']['fbs'] ;
						$pageshitscount+=$pageshits[$pcnt];
						$flg=1;
						$pcnt ++;
						break;
						
					} //	if($yt_data[$i]['y']['etime']==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$pageshits[$pcnt]=0;
					$pcnt++;

				}
					

				$flg=0;
				$count=0;
				for($i=0 ; $i < $groupscount ; $i++) // set Youtube views according to main loop date
				{

					if($groups_data[$i]['s']['etime']==$val)
					{
						$groupshits[$gcnt]= $groups_data[$i]['s']['fbs'] ;
						$groupshitscount+=$groupshits[$gcnt];
						$flg=1;
						$pcnt ++;
						break;
						
					} //	if($yt_data[$i]['y']['etime']==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$groupshits[$gcnt]=0;
					$gcnt++;

				}

					
				
			} // foreach($dt as $key => $val)
		} // if($this->Session->read('lastdate')=='y')
		else   // // set data according to date for Weekly & Monthly Graph
		{

			$flg=0;

			if(empty($mss))
			{
				$mscount = 0;
			}
			else
			{
				$mscount = count($mss);
			}

			if(empty($yt))
			{
				$ytcount = 0;
			}
			else
			{
				$ytcount = count($yt);
			}
	
			if(empty($album))
			{
				$albumcount = 0;
			}
			else
			{
				$albumcount = count($album);
			}

			if(empty($pages))
			{
				$pagescount = 0;
			}
			else
			{
				$pagescount = count($pages);
			}

			if(empty($groups))
			{
				$groupscount = 0;
			}
			else
			{
				$groupscount = count($groups);
			}
		
			
			$flg=0;
			$cnt=0;
			$ycnt=0;
			$lfcnt=0;
			$pcnt=0;
			$gcnt=0;

			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;

				for($i=0 ; $i < $mscount ; $i++)  // set Myspace views according to main loop date
				{
					if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
					{

						if($i!=0)
						{
							$mshit[$cnt]= $mss[$i]['s']['fbs']-$mss[$i-1]['s']['fbs'];
							$mshitscount+=$mshit[$cnt];
						}
						else
						{
							$mshit[$cnt] = 0;
						}
						$flg=1;
						$cnt++;

						break;

					} //	if(date('Y-m-d',$mss[$i]['s']['etime'])==$val)
				} // for($i=0 ; $i < $mscount-1 ; $i++)

			
				if($flg==0)
				{
					$mshit[$cnt]=0;
					$cnt++;

				}


				$flg=0;
				$count=0;
				for($i=0 ; $i < $ytcount ; $i++) // set Youtube views according to main loop date
				{

					if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)
					{

						if($i!=0)
						{
							$ythit[$ycnt]= $yt[$i]['y']['fbs']-$yt[$i-1]['y']['fbs'];
							$ythitscount+=$ythit[$ycnt];

						}else
						{
							$ythit[$ycnt]=0;
						}
						$flg=1;
						$ycnt ++;
						break;
					} //	if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$ythit[$ycnt]=0;
					$ycnt++;

				}
					


				$flg=0;
				for($i=0 ; $i < $albumcount ; $i++)	// set Las.fm top album playcount according to main loop date
				{
					if(date('Y-m-d',$album[$i]['t']['etime'])==$val)
					{
						if($i!=0)
						{
							$album_play[$lfcnt]= $album[$i]['t']['fbs']-$album[$i-1]['t']['fbs'];
							$lfmhitscount+=$album_play[$lfcnt];

						}
						else
						{
							$album_play[$lfcnt]=0;
						}
						$flg=1;
						$lfcnt++;
						break;
					} //	if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$album_play[$lfcnt]=0;
					$lfcnt++;

				}

				$flg=0;
				for($i=0 ; $i < $pagescount ; $i++) // set Youtube views according to main loop date
				{

					if(date('Y-m-d',$pages[$i]['s']['etime'])==$val)
					{

						if($i!=0)
						{
							$pageshits[$pcnt]= $pages[$i]['s']['fbs']-$pages[$i-1]['s']['fbs'];
							$pageshitscount+=$pageshits[$pcnt] ;

						}else
						{
							$pageshits[$pcnt]=0;
						}
						$flg=1;
						$pcnt ++;
						break;
					} //	if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$pageshits[$pcnt]=0;
					$pcnt++;

				}			

				$flg=0;
				for($i=0 ; $i < $groupscount ; $i++) // set Youtube views according to main loop date
				{

					if(date('Y-m-d',$groups[$i]['s']['etime'])==$val)
					{

						if($i!=0)
						{
							$groupshits[$gcnt]= $groups[$i]['s']['fbs']-$groups[$i-1]['s']['fbs'];
							$groupshitscount+=$groupshits[$gcnt] ;

						}else
						{
							$groupshits[$gcnt]=0;
						}
						$flg=1;
						$gcnt ++;
						break;
					} //	if(date('Y-m-d',$yt[$i]['y']['etime'])==$val)

				} // for($i=0 ; $i < $ytcount-1 ; $i++)

				if($flg==0)
				{
					$groupshits[$gcnt]=0;
					$gcnt++;

				}			


			} // foreach($dt as $key => $val)
		} // if($this->Session->read('lastdate')=='y')


		
		/*
		*	Session set to get weekly , monthly & yearly average / Percentage
		*/			
		$this->Session->write('mshitscount',$mshitscount);
		$this->Session->write('ythitscount',$ythitscount);
		$this->Session->write('lfmhitscount',$lfmhitscount);
		$this->Session->write('pageshitscount',$pageshitscount);
		$this->Session->write('groupshitscount',$groupshitscount);


		
		/*
		*	Session to set Graph data
		*/
		$this->Session->write('dt',$dt);
		$this->Session->write('mshit',$mshit);
		$this->Session->write('ythit',$ythit);
		$this->Session->write('album_play',$album_play);
		$this->Session->write('pageshits',$pageshits);
		$this->Session->write('groupshits',$groupshits);


		$pagesaverage = 0;
		$groupsaverage = 0;
		$msaverage = 0;
		$ytaverage = 0;
		$lfmaverage = 0;


		if($this->Session->read('lastdate')=='w')
		{
			$pagesaverage = round($pageshitscount/7,2);
			$groupsaverage = round($groupshitscount/7,2);
			$msaverage = round($mshitscount/7,2);
			$ytaverage = round($ythitscount/7,2);
			$lfmaverage = round($lfmhitscount/7,2);

		}
		elseif($this->Session->read('lastdate')=='m')
		{
			$mm=$this->Session->read('mm');
			$pagesaverage = round($pageshitscount/$mm,2);
			$groupsaverage = round($groupshitscount/$mm,2);
			$msaverage = round($mshitscount/$mm,2);
			$ytaverage = round($ythitscount/$mm,2);
			$lfmaverage = round($lfmhitscount/$mm,2);

		}
		elseif($this->Session->read('lastdate')=='y')
		{
			$pagesaverage = round($pageshitscount/12,2);
			$groupsaverage = round($groupshitscount/12,2);
			$msaverage = round($mshitscount/12,2);
			$ytaverage = round($ythitscount/12,2);
			$lfmaverage = round($lfmhitscount/12,2);

		}

		$this->Session->write('pagesaverage',$pagesaverage);
		$this->Session->write('groupsaverage',$groupsaverage);
		$this->Session->write('msaverage',$msaverage);
		$this->Session->write('ytaverage',$ytaverage);
		$this->Session->write('lfmaverage',$lfmaverage);

		return true;

	} // function hitchart()



	function getyear($mss,$type,$field)
	{
		/*
		$views_data = NULL;
		if($mss)
		{
			$vms=NULL;
			
			
			for($i=0 ; $i<count($mss);$i++)
			{
				if($i==0)
				{
					$vms[]= array($field=>0 , 'time'=>$mss[$i][$type]['etime']);
				}
				else
				{
					if($type=='t' and $field=='playcount')
					{
						$vms[] = array($field=>$mss[$i][0][$field]-$mss[$i-1][0][$field],'time'=>$mss[$i][$type]['etime']);
					}
					else
					{
						$vms[] = array($field=>$mss[$i][$type][$field]-$mss[$i-1][$type][$field],'time'=>$mss[$i][$type]['etime']);
					}
				}
						
			} //	for($i=0 ; $i<count($mss);$i)
				
				
			$j=0;
			$vdata[$j]=0;
			$views_data = NULL;
			$count = count($vms);
			$flag =0;
				
			
			foreach($vms as $key => $val)
			{
	
	
				if($count-1!=$key)
				{
				 
					$ctd=date('M',strtotime(date("Y-m-d", $val['time'])));
					$ntd=date('M',strtotime(date("Y-m-d", $vms[$key+1]['time'])));
				}
				else
				{
					
					$vdata[$j]=$vdata[$j]+$val[$field];
					break;
				}
	
				if($ctd==$ntd)
				{
	
					$vdata[$j]=$vdata[$j]+$val[$field];
					
						
				} // if($ctd==$ntd)
				else
				{
	
					$vdata[$j]=$vdata[$j]+$val[$field];
					
					$views_data[][$type] = array($field=>$vdata[$j] , 'etime'=>$ctd);
					$j++;
					$vdata[$j]=0;
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
		}
		

	
		return $views_data;
		*/
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

} //class AnalyticsController extends AppController {

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
