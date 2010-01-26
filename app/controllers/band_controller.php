<?php
class BandController extends AppController {

	var $name = 'Band';
	var $time = NULL;
	var $uses = array('User','Band','Mss','Msslogin','Fb','Lfm','Yt','Fbpage','Fbgroup','Page','Cms','Country','Admin','Tip','Twitter','Twtuser','Genre');
	var $helpers = array('Html', 'Error', 'Javascript', 'FlashChart');
	var $components = array('Cookie'); //  use component email
	var $developerKey , $applicationId , $clientId , $username ;

	/**
	 * Name: beforeFilter
	 * Desc: Performs necessary steps and function calls prior to executing
	 *       any view function calls.
	 */
	function beforeFilter() {
		$this->auth();
		$this->layout="wizard";
		$results = $this->Cms->findAll(array('status' => '1'),array('id' , 'title'));
		$this->set('cms',$results);
		
	} // function beforeFilter() {

	/* Name: index
	 * Desc: MMM Band Settings
	*/

	function index()
	{
	
		
		$this->layout="default";
		$this->set('editband',true);
		
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];	
		} 
		elseif($this->Session->check('band_id'))
		{
			$id = $this->Session->read('band_id');
		}
		elseif($this->Cookie->read('bandid'))
		{
			$band_id =$this->Cookie->read('bandid');
			$id = $band_id['bandid'];
		} 

		
		if(!empty($id))
		{
			$this->set('bandid',$id);
		}
		
		$mmm_id = $this->Session->read('id');
		$results = $this->Band->findAll(array('mmm_id'=>$mmm_id,'status'=>'1'));
		if($results)
		{
			
			$this->set('band',$results);
			foreach($results as $key => $bval)
			{
				if(empty($id) && $key==0)
				{
					$this->set('bandid',$bval['Band']['band_id']);
					$this->Session->write('band_id',$bval['Band']['band_id']);
				}
				$brecord[$bval['Band']['band_id']]=$bval['Band']['name'];
			}
		
			$this->set('brecord',$brecord);
		} // if($results)
		else
		{
			$this->Session->setFlash('Please add an artist');
			$this->redirect('/band/add/');
		} // if($results)
		
		
		
		
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
			
			
			if($mmm_id)
			{
				$results = $this->Admin->find(array('id'=>$mmm_id));
				if($results)
				{
					$this->set('results',$results);
					
				}
				else
				{
					$this->Session->setFlash('Sorry, we were unable to find user data.');
					$this->redirect('/dashboard/index/');
				}
			}
			else
			{
				$this->redirect('/dashboard/index/');
			}
		}// 		if (!empty($this->data))
	} // function index()

	/*
	 * Name: add
	 * Desc : add band for MMM user
	 */
	function add()
	{
		
			$this->set('s','2-step');
			if($this->data)
			{
				$eflag=1;
				if(!empty($this->data['Band']['email']))
				{
								
					if(!$this->check_email_address($this->data['Band']['email']))
					{
							$this->Session->setFlash('Invalid email address.');
							$eflag=0;
							
					}
				}
				
				if($eflag)
				{
					
					
					$mmm_id = $this->Session->read('id');
					$this->data['Band']['mmm_id'] = $mmm_id ;
					$this->data['Band']['status'] = 1 ;
					if($this->Session->check('step'))
					{
						if($this->Session->read('step')=='one')
						{
							$bandid	=	$this->Cookie->read('bandid');
							$id =	$bandid['bandid'];
							$this->data['Band']['band_id'] = $id;
							
							if($this->Band->save($this->data))
							{
								if($this->Cookie->valid('bandid'))
								{	
									$this->Cookie->delete('bandid');
								}	
								$this->Cookie->write('bandid',array('bandid'=>$id));
								$this->Session->write('band_id',$id);
								$this->Session->write('step','one');
								$this->Session->setFlash('Successfully saved.');
								$this->redirect('/band/myspace/');
							}
						}
					}
					elseif($this->Band->save($this->data))
					{
							
							$id=$this->Band->getLastInsertId();
							$name = $this->data['Band']['name'];
							if($this->Cookie->valid('bandid'))
							{	
								$this->Cookie->delete('bandid');
							}	
							$this->Cookie->write('bandid',array('bandid'=>$id));
							$this->Session->write('band_id',$id);
							$this->Session->write('step','one');
							$this->Session->setFlash('Successfully saved.');
							$this->redirect('/band/myspace/');
							exit;
					}
					
				}
				
			}
			elseif($this->Session->check('step'))
			{
				if($this->Session->read('step')=='one')
				{
					$bandid=$this->Cookie->read('bandid');
					$bandid = $bandid['bandid'];
					$band = $this->Band->find(array('band_id'=>$bandid));
					if($band)
					{
						$this->data['Band']['name'] = $band['Band']['name'];
						$this->data['Band']['website'] =$band['Band']['website'];
						$this->data['Band']['email'] =$band['Band']['email'];
						$this->set('s',$band['Band']['music_style']);
					}
				}
			}
			
			$this->Cookie->write('flag',array('flag'=>'b')); // 'b' add new call from band wizard
			$result = $this->Genre->findAll(array('status'=>1));
			
			// set genre value
			foreach($result as $key => $val)
			{
				$genre[$val['Genre']['genre']]= $val['Genre']['genre'];
			}
					
			
			$this->set('genre',$genre);
		
	} // 	function add()


	function check_email_address($email) {
		// First, we check that there's one @ symbol, and that the lengths are right
		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
		return false;
		}
			// Split it into sections to make life easier
			$email_array = explode("@", $email);
			$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
				if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
				return false;
				}
			}
			if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
			$domain_array = explode(".", $email_array[1]);
				if (sizeof($domain_array) < 2) {
			       return false; // Not enough parts to domain
				}
				for ($i = 0; $i < sizeof($domain_array); $i++) {
					if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
					return false;
					}
				}
			}
	return true;
	}
	
	/*
	 name :delete
	 description : selected band status disabled
	 reutrn : your band has been delete if true
	*/
	function delete()
	{
		$this->layout="default";
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];
			$mmm_id = $this->Session->read('id');
			$result = $this->Band->find(array('band_id'=>$id , 'mmm_id'=>$mmm_id));
			if($result)
			{
				$result['Band']['status']=0;
			
				if($this->Band->save($result))
				{
					$this->Session->setFlash("Your band has been deleted.");
					if($this->Session->read('band_id')==$id)
					{
						$this->Session->del('band_id');
						$this->Cookie->delete('bandid');
					}
					$this->redirect('/band/index/');
				}
				else
				{
					$this->Session->setFlash("We were unable to delete this band.");
					$this->redirect('/band/index/');
				}
			}
			else
			{
					$this->Session->setFlash("Invalid id.");
					$this->redirect('/band/index/');
			}
		} // if(!empty($this->params['url']['id']))
		else
		{
			$this->Session->setFlash("Invalid band id.");
			$this->redirect('/band/index/');
		} // if(!empty($this->params['url']['id']))
	}
	
	/*
	 name :inactive
	 description : selected profile inactive
	 reutrn : your band has been delete if true
	*/
	function inactive()
	{
		if(!empty($this->params['url']['id']) && !empty($this->params['url']['type']))
		{
			$id = $this->params['url']['id'];
			$type = $this->params['url']['type'];
			$mmm_id = $this->Session->read('id');
			if($this->Session->check('band_id'))
			{
				$band_id = $this->Session->read('band_id');
			}
			elseif($this->Cookie->valid('bandid'))
			{
				$band_id=$this->Cookie->read('bandid');
				$band_id = $band_id['bandid'];
			}			
						
			switch($type)
			{
				case "mss":
					$result = $this->Msslogin->find(array('mss_id'=>$id,'mmm_id'=>$mmm_id));
					if($result)
					{
						$result['Msslogin']['active']= 0;
						if($this->Msslogin->save($result))
						{
						$this->Session->setFlash("Your Myspace profile has been deleted.");
						$this->redirect('/band/manage/');
						}
						else
						{
						$this->Session->setFlash("We were unable to delete this profile.");
						$this->redirect('/band/manage/');
						}
					}
					else
					{
						$this->Session->setFlash("Invalid id.");
						$this->redirect('/band/manage/');
					}
					break;
				
				case "fbs":
					$result = $this->Fb->find(array('login_id'=>$id,'mmm_id'=>$mmm_id));
					if($result)
					{
						$result['Fb']['active']=0;
						if($this->Fb->save($result))
						{
						$this->Session->setFlash("Your Facebook user has been deleted.");
						$this->redirect('/band/manage/');
						}
						else
						{
						$this->Session->setFlash("We were unable to delete this user.");
						$this->redirect('/band/manage/');
						}
					}
					else
					{
						$this->Session->setFlash("Invalid id.");
						$this->redirect('/band/manage/');	
					}
					break;
				
				case "yts":
					$result = $this->Yt->find(array('yt_id'=>$id,'mmm_id'=>$mmm_id));
					if($result)
					{
						$result['Yt']['active']=0;
						if($this->Yt->save($result))
						{
						$this->Session->setFlash("Your Youtube channel has been deleted.");
						$this->redirect('/band/manage/');
						}
						else
						{
						$this->Session->setFlash("We were unable to delete this channel.");
						$this->redirect('/band/manage/');
						}
					}
					else
					{
						$this->Session->setFlash("Invalid id.");
						$this->redirect('/band/manage/');	
					}
					break;
				
				case "lfm":
					$result = $this->Lfm->find(array('lfm_m_id'=>$id,'mmm_id'=>$mmm_id));
					if($result)
					{
						$result['Lfm']['active']=0;
						if($this->Lfm->save($result))
						{
						$this->Session->setFlash("Your Last.fm artist has been deleted.");
						$this->redirect('/band/manage/');
						}
						else
						{
						$this->Session->setFlash("We were unable to delete this artist.");
						$this->redirect('/band/manage/');
						}
					}
					else
					{
						$this->Session->setFlash("Invalid id.");
						$this->redirect('/band/manage/');	
					}
					break;
				case "twt":
					if(!empty($band_id))
					{
						$result = $this->Twtuser->find(array('user_id'=>$id,'mmm_id'=>$mmm_id,'band_id'=>$band_id));
						if($result)
						{
							$result['Twtuser']['active']=0;
							if($this->Twtuser->save($result))
							{
							$this->Session->setFlash("Your Twitter user has been deleted.");
							$this->redirect('/band/manage/');
							}
							else
							{
							$this->Session->setFlash("We were unable to delete this user.");
							$this->redirect('/band/manage/');
							}
						}
						else
						{
							$this->Session->setFlash("Invalid id.");
							$this->redirect('/band/manage/');	
						}
					}
					break;
				default : break;
				
			}
		
		} // if(!empty($this->params['url']['id']))
		else
		{
			$this->Session->setFlash("Invalid band id.");
			$this->redirect('/band/manage/');
		} // if(!empty($this->params['url']['id']))
	}
	/*
	 * Name: myspace
	 * Desc : manage band account & fetch data for myspace
	 * called : called from band/index
	 * 
	 */
	function myspace()
	{
		$id=NULL;
		
		if($this->Session->check('band_id'))
		{
			$band_id = $this->Session->read('band_id');
			$this->set('bandid',$id);
			$mmm_id  = $this->Session->read('id');
			
			//	MySpace
			
			$this->set('mss_flag', false );
			$mss = $this->Msslogin->findAll(array('mmm_id' => $mmm_id , 'band_id' => $band_id ,'active'=>'1'));
			if($mss)
			{
				$mssdata = NULL;
				$mssactive = NULL;
				foreach($mss as $key => $mssval)
				{
					
					$mssdata[$mssval['Msslogin']['mss_id']] = $mssval['Msslogin']['name'];
					if($mssval['Msslogin']['status']==1)
					{
						$mssactive = $mssval['Msslogin']['mss_id'];
				
					}
				}
				
				$this->set('mss_flag', true );
				$this->set('mssdata',$mssdata);
				$this->set('mssactive',$mssactive);
			}
			
			//	End MySpace
			
		} // if($this->Session->check('band_id'))
		else
		{
			$this->Session->setFlash('Please enter the artist\'s name first.');
			$this->redirect('/band/add/');
		} // 	if($this->Session->check('band_id'))
			
			
	} // function myspace()

	/*
	 * Name: facebook
	 * Desc : manage band account & fetch data for facebook
	 * 	  manage pages & group data
	 * called : called from band/index
	 * 
	 */
	function facebook() {
		
		$id=NULL;
		if($this->Session->check('band_id'))
		{
			$band_id =$this->Session->read('band_id');
			$this->set('bandid',$band_id);
			$mmm_id = $this->Session->read('id');
			/*
				Facebook
			*/
			$this->set('fbs_flag',false);
			$this->set('fbsgenereatekey',false);
			
			$fbs = $this->Fb->findAll(array('band_id'=>$band_id,'mmm_id'=>$mmm_id,'active'=>'1'));
			/*
			 if record exist for specified band then hide Facebook account
			*/
			if($fbs)
			{
				
				$this->set('fbs_flag',true);
								
				$fbsdata = NULL;
				$fbsactive = NULL;
				$pgflag=0;
				foreach($fbs as $key => $fbsval)
				{
					
					$fbsdata[$fbsval['Fb']['login_id']] = $fbsval['Fb']['name'];
					if($fbsval['Fb']['status']==1)
					{
						$fbsactive 	= $fbsval['Fb']['login_id'];
						$etime		= $fbsval['Fb']['etime'];
						$setpage	= $fbsval['Fb']['page'];
						$setgroup	= $fbsval['Fb']['group'];
						
						if(empty($setpage) or empty($setgroup))
						{
							$pgflag=1;		
						}
						
					}
				}
								
				$this->set('pgflag',$pgflag); // set if no page or group set before								
				$this->set('fbsdata',$fbsdata);
				$this->set('fbsdata',$fbsdata);
				
					
					if(!empty($fbsactive))
					{
						
						$this->set('fbsactive',$fbsactive);
						$this->Session->write('fbs_login_id',$fbsactive);
						$pagerecord = $this->Fbpage->findAll(array('login_id'=>$fbsactive , 'etime'=>$etime));
							
								if($pagerecord)
								{
									if(empty($setpage))
									{
										$pageactive=' ';
									}
									else
									{
										$pageactive = $setpage;
									}
									
									
									foreach($pagerecord as $key =>$pval)
									{
										
										$page[$pval['Fbpage']['name']] = $pval['Fbpage']['name'];
																			
									} // foreach($pagerecord as $key =>$pval)
									
									
									$this->set('pageactive',$pageactive);
									$page[' ']="None";
									
									$this->set('page',$page);
									
								} // if($pagerecord)
								else
								{
									$this->set('pageactive',' ');
									$page[' ']="None";
									$this->set('page',$page);
								} // if($pagerecord)
					
					
					
					$grouprecord = $this->Fbgroup->findAll(array('login_id'=>$fbsactive , 'etime'=>$etime));
						if($grouprecord)
						{
							if($setgroup)
							{
								$groupactive =' ';
							}
							else
							{
								$groupactive = $setgroup;
							}
							$groupactive = $setgroup;
							foreach($grouprecord as $key =>$gval)
							{
								$group[$gval['Fbgroup']['name']] = $gval['Fbgroup']['name'];
								
							} // foreach($pagerecord as $key =>$pval)
							
							
							$this->set('groupactive',$groupactive);
							$group[' ']="None";		
							$this->set('group',$group);
							
						} // if($grouprecord)
						else
						{
							$this->set('groupactive',' ');
							$group[' ']="None";
							$this->set('group',$group);
						}
						
						$this->set('none',false); // if data found and status =1
					
					}
					else
					{
							
							
							$this->set('fbsactive','none');
							
							$this->set('groupactive',' ');
							$group[' ']="None";
							$this->set('group',$group);
							
							$this->set('pageactive',' ');
							$page[' ']="None";
							$this->set('page',$page);
							$this->set('none',true);
					}
					
					
				
			}  // if($fbs)
			
				if($this->Session->check('fbsgenereatekey'))
				{
					if($this->Session->read('fbsgenereatekey')=='false')
						{
							$this->set('fbsgenereatekey',true);     // if facebook key generation & update requried
						} // if($this->Session->read('fbsgenereatekey')=='false')
				} // if($this->Session->check('fbsgenereatekey'))	
			
			
			/*
				End Facebook
			*/
		}// if($this->Session->check('band_id'))
		else
		{
			$this->Session->setFlash('Please enter the artist\'s name first.');
			$this->redirect('/band/add/');	
		}// if($this->Session->check('bandid'))
		
	} // function facebook()
	
	
	/*
	 * Name: youtube
	 * Desc : manage band account & fetch data for youtube using API
	 * called : called from band/index
	 * 
	 */
	function youtube(){
		$id=NULL;
		
		
		if($this->Session->check('band_id'))
		{
			$band_id =$this->Session->read('band_id');
			$this->set('bandid',$band_id);
			$mmm_id = $this->Session->read('id');
			
			/*
				Youtube
			*/
			$this->set('yt_flag', false );
			$yts = $this->Yt->findAll(array('mmm_id' => $mmm_id , 'band_id' => $band_id,'active'=>'1'));
			if($yts)
			{
				$ytsdata = NULL;
				$ytsactive = NULL;
				
				
				
				foreach($yts as $key => $ytsval)
				{
					
								
					$ytsdata[$ytsval['Yt']['yt_id']] = $ytsval['Yt']['user_id'];
					if($ytsval['Yt']['status']==1)
					{
						$ytsactive = $ytsval['Yt']['yt_id'];		
					}
				}
								
				$this->set('ytsdata',$ytsdata);
				$this->set('ytsactive',$ytsactive);
				$this->set('yt_flag', true );	
			}
			/*
				End Youtube
			*/
		} // if($this->Session->check('bandid'))
		else
		{
			$this->Session->setFlash('Please enter the artist\'s name first.
');
			$this->redirect('/band/index/');
		} // if($this->Session->check('bandid'))
		
	} // function youtube()
	
	
	/*
	 * Name: last.fm
	 * Desc : manage band account & fetch data for  Last.fm 
	 * called : called from band/index
	 * 
	 */
	
	
	/*
	 * Name: twitter
	 * Desc : manage band account & fetch data for  Twitter 
	 * 
	 */
	function twitter() {
		$id=NULL;
		
		
		if($this->Session->check('band_id'))
		{
			$band_id =$this->Session->read('band_id');
			$this->set('bandid',$band_id);
			$mmm_id = $this->Session->read('id');
			/*
				twitter
			*/
			
			$qry= "select l.name , l.user_id , u.status from twt_login l , twt_user u where l.user_id = u.user_id and u.mmm_id = $mmm_id and u.band_id= $band_id and u.active=1";
			$twts = $this->Twitter->query($qry);
			
			if($twts)
			{
				$twtdata = NULL;
				$twtactive = NULL;
				foreach($twts as $key => $twtval)
				{
					
					$twtdata[$twtval['l']['user_id']] = $twtval['l']['name'];
					if($twtval['u']['status']==1)
					{
						$twtactive = $twtval['l']['user_id'];		
					}
				}
								
				$this->set('twtdata',$twtdata);
				$this->set('twtactive',$twtactive);
			}
			/*
				End twitter
			*/
		} // if($this->Session->check('bandid'))
		else
		{
			$this->Session->setFlash('Please enter the artist\'s name first.');
			$this->redirect('/band/index/');
		} // if($this->Session->check('bandid'))
	} // function twitter()
	
	/*
	 * Name: thanks
	 * Desc : thanks page to set-up social network site data for MMM
	 * 	  
	 */
	function thanks(){
		
		
		if($this->Session->check('step'))
		{
			$this->Session->del('step');
		}
		
		if($this->Session->check('band_id'))
		{
			$band_id =$this->Session->read('band_id');
			$this->set('bandid',$band_id);
		} // if($this->Session->check('bandid'))
		
		$pageResults = $this->Page->find(array('id'=>'FshBandSetup'));
		if($pageResults)
		{
			
			$finish = $pageResults['Page']['description'];
			$this->set('finish',$finish);
		}
		else
		{
				$this->set('finish',' ');
		}
	}
	
	/*
	 * Name: Welcome
	 * Desc : welcome page to set-up social network site data for MMM
	 * 	  
	 */
	
	function welcome(){
		
		
		if(!$this->Session->check('session_id'))
		{
			$this->Session->setFlash('This account was already registered.');
			$this->redirect('/users/index/');
		}
		
		if($this->Session->check('email'))
		{
			$this->set('email',$this->Session->read('email'));
		}
		else
		{
			$this->set('email','youremail@.....');
		}
		
		$pageResults = $this->Page->find(array('id'=>'wcBandSetup'));
		if($pageResults)
		{
			$welcome = $pageResults['Page']['description'];
			$this->set('welcome',$welcome);
		}
		else
		{
				$this->set('welcome',' ');
		}
	}
	/*
	 * Name: manage
	 * Desc : manage band account & fetch data for all social network site ' Facebook , Last.fm , Myspace and Youtube'
	 * 	  manage pages & group data for facebook
	 */
	function manage(){
		
		$id=NULL;
		$this->layout="default";
		$this->set('editband',true);
		$this->set('setting',true);
		
		
		$this->Cookie->write('flag',array('flag'=>'m')); // 'm' add new call from band settings manage
		
		if($this->data)
		{
			$id = $this->data['Band']['id'];
		} // if($this->data)
		elseif(!empty($this->params['url']['bandid']))
		{
			$id = $this->params['url']['bandid'];	
		} // if($this->data)
		elseif($this->Session->check('band_id'))
		{
			$id = $this->Session->read('band_id');
		}
		elseif($this->Cookie->read('bandid'))
		{
			$band_id =$this->Cookie->read('bandid');
			$id = $band_id['bandid'];
		} // if($this->data)
		else
		{
			$this->Session->setFlash('Please select a band.');
			$this->redirect('/band/index/');	
		} // if($this->data)
	
		if($id)
		{
			
			$results = $this->Cms->findAll(array('status' => '1'),array('id' , 'title'));
			$this->set('cms',$results);
			
			if($this->Session->check('id'))
			{
				$mmm_id = $this->Session->read('id');
				$band = $this->Band->findAll(array('mmm_id'=>$mmm_id,'status'=>'1'));
				$this->set('band',$band);
				
				$tip = $this->Tip->find(array('id'=>$mmm_id , 'tip'=>'user-setting'));
				if($tip)
				{
					if($tip['Tip']['status']==1)
					{
						$this->set('tip',true);	
					}
					else
					{
						$this->set('tip',false);	
					}
				}
				else
				{
						$this->set('tip',true);	
				}
			} // if($this->Session->check('id'))
			
			$this->Session->write('band_id',$id);
			
			if($this->Cookie->valid('bandid'))
			{
				$this->Cookie->delete('bandid');
			}
			
			
			$this->Cookie->write('bandid',array('bandid'=>$id));
			$this->set('bandid',$id);
			
			if(!empty($band))
			{
				foreach($band as $bkey => $bval)
				{
					if($bval['Band']['band_id']==$id)
					{
						$this->set('bandname',$bval['Band']['name']);
					}
				}	
			} // 	if(!empty($band))
			
			
			$mmm_id = $this->Session->read('id');
			
			/*
				Facebook
			*/
			$this->facebook();
					
			
			/*
				MySpace
			*/
			
			$this->myspace();
			
			
			/*
				Youtube
			*/
			$this->youtube();
			/*
				End Youtube
			*/
			
			/*
			   Twitter
			*/
			$this->twitter();
			
			
			$results = $this->Band->find(array('band_id'=>$id,'mmm_id'=>$mmm_id));
			
			if($results)
			{
				$name = $results['Band']['name'];
				$this->set('name',$name);
			} // if($results)
			else
			{
				$this->redirect('/band/index/');
			} // if($results)

		} // 	if($id)
		else
		{
			$this->Session->setFlash('Please select a band.');
			$this->redirect('/band/index/');
		} // 	if($id)
	} // 	function manage(){
	
	/*
	 * Name: manage
	 * Desc : set pages & group data for facebook
	 */
	
	function setpg() {
		
		if($this->data)
		{
			$login_id =	$this->Session->read('fbs_login_id');
			$this->data['Fb']['login_id'] = $login_id ;
			$this->Fb->save($this->data);
		}
		
		$this->redirect('/band/manage/');
	} // function setpg {
	
	
	function getpage(){
		
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];
			$qry ="select distinct name from fb_pages where login_id=$id and is_admin=1";
			$result = $this->Fbgroup->findBySql($qry);
			
			if($result)
			{
				
						$data ="[";
						
						foreach($result as $key => $pageval)
						{
						$nm = str_replace(array("\"","'"),array("",""),$pageval['fb_pages']['name']);
						$data.="{ optionValue:'$nm' , optionDisplay:'$nm'},";

						}
						$data.="{optionValue:' ' , optionDisplay:'None'}";
						//$data= substr($data,0,strlen($data)-1);
						$data.="]";
						echo $data;
	
			}
			else
			{
				
				$data="[{optionValue:' ' , optionDisplay:'None'}]";
				echo $data;
			}
			exit;
	
		} // 
		
	} // function getpage(){
	
	function getgroup(){
		
		if(!empty($this->params['url']['id']))
		{
			$id = $this->params['url']['id'];
			
			$qry ="select distinct name from fb_group where login_id=$id and isCreator='Yes'";
			
			$result = $this->Fbgroup->findBySql($qry);
			
			if($result)
			{
						$data ="[";
						
						foreach($result as $key => $groupval)
						{
						$nm = str_replace(array("\"","'"),array("",""),$groupval['fb_group']['name']);
						$data.="{ optionValue:'$nm' , optionDisplay:'$nm'},";

						}
						$data.="{optionValue:' ' , optionDisplay:'None'}";
						//$data= substr($data,0,strlen($data)-1);
						$data.="]";
						echo $data;
	
			}
			else
			{
				$data="[{optionValue:' ' , optionDisplay:'None'}]";
				echo $data;
			}
			exit;
	
		} // 
		
	} // function getpage()
	
	/*
	 *	name : updateTip
	 *	description : update user tip status using jquery
	 */
	function updateTip()
	{
		if(!empty($this->params['url']['id']))
		{
			if($this->Session->check('id'))
			{
				$mmm_id = $this->Session->read('id');
				$tip = $this->params['url']['id'] ;
				$qry = "update tip
					set status=0
					where id=$mmm_id
					and tip = '$tip'";
				echo $qry;
				$this->Tip->query($qry);
				
			}
			
		}
		exit;
	}
	
} //class BandController extends AppController {
?>
