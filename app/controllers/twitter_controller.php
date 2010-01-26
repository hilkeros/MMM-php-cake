<?php
/*
Developed by : Babar Ali
Project	     : MMM
*/

vendor('twitter/twitterOAuth');
       
class TwitterController extends AppController
{
	var $uses = array('Cms','Twitter','Twtuser','Twtstats','Twtdm','Twtmentions','User','Twtstatus','Dashboard');
	var $helpers = array('Html', 'Error', 'Javascript', 'Ajax','FlashChart');
	var $components = array('Cookie'); //  use component email
	var $consumer_key = '6gd1IvNlyO9YQPox4GLeg';
	var $consumer_secret = 'HjTW5UVDCtwetGX93JVNGjkPN882UvLWwEACCpAaA';
	
	function beforefilter()
	{
		
		$this->auth();
		
			
	}	// 	 function beforefilter(){

	function index()
	{
		
		
		$content = NULL;
		/* Set state if previous session */
					
		    /* Create TwitterOAuth object with app key/secret */
		    $to = new TwitterOAuth($this->consumer_key,$this->consumer_secret);
		    /* Request tokens from twitter */
		    $tok = $to->getRequestToken();
		    
		    /* Save tokens for later */
		    $token = $tok['oauth_token'];
		    $this->Session->write('oauth_request_token',$token );
		    $this->Session->write('oauth_request_token_secret',$tok['oauth_token_secret']);
		    $this->Session->write('oauth_state',"start");
			    
		    /* Build the authorization URL */
		    $request_link = $to->getAuthorizeURL($token);
		    $this->redirect($request_link);
		    exit;
		    /* Build link that gets user to twitter to authorize the app */
		   // $content = 'Click on the link to go to twitter to authorize your account.';
		   // $content .= '<br /><a href="'.$request_link.'">'.$request_link.'</a>';
	}
	
	/*
	 *	name : ytswelcome
	 *	description : youtube authentication welcome screen
	 */
	
	function welcome()
	{
		$this->layout = "wizard";
		$this->set('band_id',$this->Session->read('band_id'));
		$band  =$this->Cookie->read('flag'); // called from wizard or setting manage
		$flag= $band["flag"];
		$this->set('flag',$flag);
		$results = $this->Cms->findAll(array('status' => '1'),array('id' , 'title'));
		$this->set('cms',$results);	
	}
	
	/*
	 name : update
	 description :	update / activate specific user .
	 called :	called from band/manage 
	*/
	function update()
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
				$this->redirect('/band/manage/');
				exit;
			}
			
						
				$user_id = $this->data['twitter']['user_id'];
						
				$qry = " update twt_user
					 set status=0
					 where mmm_id='$mmm_id'
					 and band_id  = $band_id ";
				$this->Twtuser->query($qry);
				
								
				if($user_id!="none")
				{
				$qry = " update twt_user
					 set status=1
					 where mmm_id ='$mmm_id'
					 and band_id  = $band_id
					 and user_id  = $user_id";
				
				$this->Twtuser->query($qry);
				}
				$this->Session->setFlash('Twitter user updated succesfully.');
				$this->redirect('/band/manage/');
				exit;
				
				
			
			
		} // if($this->data)
		else
		{
				$this->Session->setFlash('Invalid data.');
				$this->redirect('/band/manage/');
				
		} // if($this->data)	
	}
	
	/*
	 * name : getDms
	 * description : return user direct message data to dashboard
	 */
	function getDms()
	{
		if(!empty($this->params['url']['id']) &&  !empty($this->params['url']['bandid']))
		{
			
			$user_id = $this->params['url']['id'];
			$mmm_id = $this->Session->read('id');
			$band_id = $this->params['url']['bandid'];
			if(!empty($this->params['url']['count']))
			{
				
				$twtdmscount = $this->params['url']['count'];
				$this->Session->write('twtdmscount',$twtdmscount);
			}
			else
			{
				if($this->Session->check('twtdmscount'))
				{
					$twtdmscount = $this->Session->read('twtdmscount')+10;
					$this->Session->write('twtdmscount',$twtdmscount);
					
				}
				else
				{
					$twtdmscount = 10;
				}
			}
			
			$flag=1;
			$newDmsCount = 0;
			
			
			
			$result = $this->Twtuser->find(array('user_id'=>$user_id , 'band_id'=>$band_id,'mmm_id'=>$mmm_id,'active'=>1));
			if($result)
			{
				$twitterResult = $this->Twitter->find(array('user_id'=> $user_id));
				if($twitterResult)
				{
					$dmsTime = $twitterResult['Twitter']['refresh_time'];
				}
				
				//$dmsResult = $this->Twtdm->findAll(array('user_id'=>$user_id), null , 'created_at desc' , $twtdmscount , null);
				$dmsResult = $this->Twtdm->findAll(array('user_id'=>$user_id), null , 'created_at desc' , 100 , null);
				foreach($dmsResult as $dmskey => $dmsval)
				{
					$cdate	= $dmsval['Twtdm']['created_at'];
					if($cdate > $dmsTime)
					{
						$newDmsCount ++;
					}
				}
				$dmsCount = $this->Twtdm->find(array('user_id'=>$user_id),array('count(*) as count'));
				if(($twtdmscount>=$dmsCount[0]['count']) or ($twtdmscount>=100))
				{
					$flag=0;
				}
				
				if($dmsResult)
				{
						
					
						$data ="[";
						
						foreach($dmsResult as $dmskey => $dmsval)
						{
							if($dmskey < 	$twtdmscount )
							{
								$message= stripslashes($dmsval['Twtdm']['message']);
								$message = ereg_replace ( "(http://[^' ']+)", "<a class=graylink href=\\1 target=_blank> \\1</a>", $message);
								$message = ereg_replace ( "(@[^' ']+)", "<a class=graylink href=http://twitter.com/\\1 target=_blank> \\1</a>", $message);
								$message = ereg_replace ( "(#[^' ']+)", "<a class=graylink href=http://twitter.com/search?q=\\1 target=_blank> \\1</a>", $message);
								$message= str_replace(array("http://twitter.com/@","http://twitter.com/search?q=#"),array("http://twitter.com/","http://twitter.com/search?q="),$message);
								$message= trim(str_replace(array("\n","\r","'"),array("","",""),$message));
								
								$name = trim(stripslashes($dmsval['Twtdm']['sender_screen_name']));
								//$name	= str_replace(array("\"","'"),array("",""),$dmsval['Twtdm']['sender_screen_name']);
								$cdate	= $dmsval['Twtdm']['created_at'];
								$create_at = date('g:i a F jS',$cdate);
								
								$create_at = stripslashes($create_at);
								
								
								$data.="{ optionText:'$message' , optionName:'$name' , optionDate:'$create_at', optionFlag: $flag , optionCount : $newDmsCount },";
							}
						}
						$data= substr($data,0,strlen($data)-1);
						$data.="]";
						echo $data;
						
					
				}
				else
				{
					$data="[{optionText:'no data available' , optionName:'' , optionDate:'' , optionFlag:0}]";
					echo $data;
				}
			}
			else
			{
				$data="[{optionText:'no data available' , optionName:'' , optionDate:'' , optionFlag:0}]";
				echo $data;
			}
			exit;
		}
	}
	
	
	/*
	 * name : getMentions
	 * description : return user direct message data to dashboard
	 */
	function getMentions()
	{
		if(!empty($this->params['url']['id']) &&  !empty($this->params['url']['bandid']))
		{
			
			$user_id = $this->params['url']['id'];
			$mmm_id = $this->Session->read('id');
			$band_id = $this->params['url']['bandid'];
			if(!empty($this->params['url']['count']))
			{
				
				$twtmentioncount = $this->params['url']['count'];
				$this->Session->write('twtmentioncount',$twtmentioncount);
				
			}
			else
			{
				if($this->Session->check('twtmentioncount'))
				{
					$twtmentioncount = $this->Session->read('twtmentioncount')+10;
					$this->Session->write('twtmentioncount',$twtmentioncount);
				}
				else
				{
					$twtmentioncount = 10;
				}
			
			}
			
			$flag=1;
			$newMentionsCount =0;
			$result = $this->Twtuser->find(array('user_id'=>$user_id , 'band_id'=>$band_id,'mmm_id'=>$mmm_id,'active'=>1));
			if($result)
			{
				$twitterResult = $this->Twitter->find(array('user_id'=> $user_id));
				if($twitterResult)
				{
					$MentionsTime = $twitterResult['Twitter']['refresh_time'];
				}
				
				//$mentionsResult = $this->Twtmentions->findAll(array('user_id'=>$user_id), null , 'created_at desc' , $twtmentioncount , null);
				$mentionsResult = $this->Twtmentions->findAll(array('user_id'=>$user_id), null , 'created_at desc' , 100 , null);
				foreach($mentionsResult as $mkey => $mval)
				{
					$cdate	= $mval['Twtmentions']['created_at'];
					if($cdate > $MentionsTime)
					{
						$newMentionsCount ++ ;
					}
				}
				
				$mentionsCount = $this->Twtmentions->find(array('user_id'=>$user_id),array('count(*) as count'));
				
				if(($twtmentioncount>=$mentionsCount[0]['count']) or ($twtmentioncount >=100) )
				{
					$flag=0;
					
				}
				
				if($mentionsResult)
				{
					
						$data ="[";
						
						foreach($mentionsResult as $mkey => $mval)
						{
							if($mkey < $twtmentioncount )
							{
								//$message= str_replace(array("\"","'"),array("",""),$mval['Twtmentions']['message']);
								$message = stripslashes($mval['Twtmentions']['message']);
								$message = ereg_replace ( "(http://[^' ']+)", "<a class=graylink href=\\1 target=_blank>\\1</a>", $message);
								$message = ereg_replace ( "(@[^' ']+)", "<a class=graylink href=http://twitter.com/\\1 target=_blank>\\1</a>", $message);
								$message = ereg_replace ( "(#[^' ']+)", "<a class=graylink href=http://twitter.com/search?q=\\1 target=_blank> \\1</a>", $message);
								$message= str_replace(array("http://twitter.com/@","http://twitter.com/search?q=#"),array("http://twitter.com/","http://twitter.com/search?q="),$message);
								$message= trim(str_replace(array("\n","\r","'"),array("","",""),$message));
								
														
								$name	= trim(stripslashes($mval['Twtmentions']['reply_screen_name']));
								$cdate	= $mval['Twtmentions']['created_at'];
								
								$create_at = date('g:i a F jS',$cdate);
								
								$create_at =stripslashes($create_at);
								
								
								$data.="{ optionText:'$message' , optionName:'$name' , optionDate:'$create_at', optionFlag: $flag , optionCount : $newMentionsCount},";
							}
						}
						$data= substr($data,0,strlen($data)-1);
						$data.="]";
						echo $data;
						
					
				}
				else
				{
					$data="[{optionText:'no data available' , optionName:'' , optionDate:'' , optionFlag:0}]";
					echo $data;
				}
			}
			else
			{
				$data="[{optionText:'no data available' , optionName:'' , optionDate:'' , optionFlag:0}]";
				echo $data;
			}
			exit;
		}
	}
	
	
	/*
	 * name : getTweets
	 * description : return user tweets data to dashboard
	 */
	function getTweets()
	{
		if(!empty($this->params['url']['id']) &&  !empty($this->params['url']['bandid']))
		{
			
			$user_id = $this->params['url']['id'];
			$mmm_id = $this->Session->read('id');
			$band_id = $this->params['url']['bandid'];
			if(!empty($this->params['url']['count']))
			{
				
				$twttweetscount = $this->params['url']['count'];
				$this->Session->write('twttweetscount',$twttweetscount);
			}
			else
			{
				if($this->Session->check('twttweetscount'))
				{
					$twttweetscount = $this->Session->read('twttweetscount')+10;
					$this->Session->write('twttweetscount',$twttweetscount);
					
				}
				else
				{
					$twttweetscount = 10;
				}
			}
			
			$flag=1;
			$newTweetsCount =0;
			
			$result = $this->Twtuser->find(array('user_id'=>$user_id , 'band_id'=>$band_id,'mmm_id'=>$mmm_id,'active'=>1));
			if($result)
			{
				$twitterResult = $this->Twitter->find(array('user_id'=> $user_id));
				if($twitterResult)
				{
					$TweetsTime = $twitterResult['Twitter']['refresh_time'];
				}
				
				$tweetsResultCount = $this->Twtstatus->findAll(array('user_id'=>$user_id), null , 'created_at desc' , 100 , null );
				foreach($tweetsResultCount as $twtkey => $twtval)
				{
					$cdate	= $twtval['Twtstatus']['created_at'];
					if($cdate > $TweetsTime)
					{
						$newTweetsCount ++ ;
					}
				}
				
			//	$twitterResult = $this->Twtstatus->findAll(array('user_id'=>$user_id), null , 'created_at desc' , $twttweetscount , null );
				$tweetsCount = $this->Twtstatus->find(array('user_id'=>$user_id),array('count(*) as count'));
				
				if(($twttweetscount>=$tweetsCount[0]['count']) or ($twttweetscount>=100))
				{
					$flag=0;
				}
				
				if($twitterResult)
				{
					$count=0;
						$data ="[";
						foreach($tweetsResultCount as $tkey => $tval)
						{
							if($tkey < $twttweetscount)
							{
								
								$message = stripslashes($tval['Twtstatus']['text']);
								$message = ereg_replace ( "(http://[^' ']+)", "<a class=graylink href=\\1 target=_blank> \\1</a>", $message);
								$message = ereg_replace ( "(@[^' ']+)", "<a class=graylink href=http://twitter.com/\\1 target=_blank> \\1</a>",$message );
								$message = ereg_replace ( "(#[^' ']+)", "<a class=graylink href=http://twitter.com/search?q=\\1 target=_blank> \\1</a>", $message);
								$message= str_replace(array("http://twitter.com/@","http://twitter.com/search?q=#"),array("http://twitter.com/","http://twitter.com/search?q="),$message);
								$message= trim(str_replace(array("\n","\r","'"),array("","",""),$message));
								
								
								$name	= trim(stripslashes($tval['Twtstatus']['twt_screen_name']));
								// if($name)
								// {
								// $name = "<a href=http://twitter.com/$name target=_blank> $name </a>";
								// }
								
								$cdate	= $tval['Twtstatus']['created_at'];
								
								$create_at = date('g:i a F jS',$cdate);
								
								$create_at = stripslashes($create_at);
														
								$data.="{ optionText:'$message' ,  optionDate:'$create_at', optionName : '$name' , optionFlag: $flag , optionCount : $newTweetsCount },";
								$count ++;
								
								
							}
					
						}
						$data= substr($data,0,strlen($data)-1);
						$data.="]";
						echo $data;
					
				}
				else
				{
					$data="[{optionText:'no data available' , optionDate:'' , optionFlag:0}]";
					echo $data;
				}
			}
			else
			{
				$data="[{optionText:'no data available' , optionDate:'' , optionFlag:0}]";
				echo $data;
			}
			exit;
		}
	}
	
	/*
	 * name : getStatuses
	 * description : return user tweets message data to dashboard
	 */
	function getStatuses()
	{
		if(!empty($this->params['url']['id']) &&  !empty($this->params['url']['bandid']))
		{
			
			$user_id = $this->params['url']['id'];
			$mmm_id = $this->Session->read('id');
			$band_id = $this->params['url']['bandid'];
			
			
			
			$result = $this->Twtuser->find(array('user_id'=>$user_id , 'band_id'=>$band_id,'mmm_id'=>$mmm_id,'active'=>1));
			if($result)
			{
				
				
				$tweetsResult = $this->Twitter->find(array('user_id'=>$user_id));
				if($tweetsResult)
				{
						$message = stripslashes($tweetsResult['Twitter']['tweets']);
						$message = ereg_replace ( "(@[^' ']+)", "<a class=graylink href=http://twitter.com/\\1 target=_blank> \\1</a>",$message);
						$message= str_replace(array("http://twitter.com/@"),array("http://twitter.com/"),$message);
						$message= str_replace(array("\n","\r","'"),array("","",""),$message);
						
						$cdate	= $tweetsResult['Twitter']['tweet_created_at'];
						
						$create_at = date('g:i a F jS',$cdate);
						
						$create_at = stripslashes($create_at);
						$data ="[{ optionText:'$message' ,  optionDate:'$create_at'}]";
						echo $data;
					
				}
				else
				{
					$data="[{optionText:'no data available' , optionDate:''}]";
					echo $data;
				}
			}
			else
			{
				$data="[{optionText:'no data available' , optionDate:''}]";
				echo $data;
			}
			exit;
		}
	}
	
	
	
	/*
	 *  name : process
	 *  description : twitter processed & get data using API
	 *
	 */
	function process()
	{
		if($_REQUEST['oauth_token'])
		{
			$time = time();
			
			
			if($this->Session->check('band_id'))
			{
				$band_id = $this->Session->read('band_id');
			}
			else{
				$band_id=$this->Cookie->read('bandid');
				$band_id = $band_id['bandid'];	
			}
				
			if(empty($band_id))
			{
				$this->Session->setFlash('Please select a band.');
				if($flag=='b')
				{
					$this->redirect('/band/twitter/');
				}
				else
				{
					$this->redirect('/band/manage/');
				}
				exit;
			}
			
			if($this->Cookie->valid('flag'))
			{
				$band  =$this->Cookie->read('flag'); // called from wizard or setting manage
				$flag= $band["flag"];					
			}
			
			
			
			$mmm_id = $this->Session->read('id');
			
				
				
			if($this->Session->check('oauth_request_token'))
			{
				/* Checks if oauth_token is set from returning from twitter */
				$session_token = $this->Session->read('oauth_request_token') ;
				
			}
			else
			{
				// $this->Session->setFlash('oauth request token missed.');
				$this->redirect('/twitter/welcome/');
				exit;	
			}
			
			
			if(!empty($_REQUEST['section']))
			{
				/* Set section var */
				$section = $_REQUEST['section'];
			}
			
			/* Clear PHP sessions */
			if(!empty($_REQUEST['test']))
			{
				if ($_REQUEST['test'] === 'clear') {/*{{{*/
				  $this->Session->destroy();
				}/*}}}*/
			}

			/* If oauth_token is missing get it 
			if (@$_REQUEST['oauth_token'] != NULL && @$_SESSION['oauth_state'] === 'start') {
			  $_SESSION['oauth_state'] = $state = 'returned';
			} */
			  
	
			if ($this->Session->read('oauth_access_token') === NULL && $this->Session->read('oauth_access_token_secret') === NULL)
			{
		
			/* Create TwitterOAuth object with app key/secret and token key/secret from default phase */
				$to = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $_SESSION['oauth_request_token'], $_SESSION['oauth_request_token_secret']);
			/* Request access tokens from twitter */
				$tok = $to->getAccessToken();
			/* Save the access tokens. Normally these would be saved in a database for future use. */
				$this->Session->write('oauth_access_token' , $tok['oauth_token'] );
				$this->Session->write('oauth_access_token_secret' , $tok['oauth_token_secret']);
			}
			
		        /* Create TwitterOAuth with app key/secret and user access key/secret */
			$to = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->Session->read('oauth_access_token'), $this->Session->read('oauth_access_token_secret'));
		 
		       /* Run request on twitter API as user. */
			$content = $to->OAuthRequest('https://twitter.com/account/verify_credentials.xml', array(), 'GET');
			$dm = $to->OAuthRequest('http://twitter.com/direct_messages.xml', array(), 'GET');
			$mentions = $to->OAuthRequest('http://twitter.com/statuses/mentions.xml', array(), 'GET');
			$statuses_tweets = $to->OAuthRequest('http://twitter.com/statuses/friends_timeline.xml', array(), 'GET');
		//	$content .= $to->OAuthRequest('http://twitter.com/account/rate_limit_status.xml', array(), 'GET');
			
				
			$doc = new DOMDocument();
			$doc->loadXML( $content );
			
			$users = $doc->getElementsByTagName( "user" );
			
			foreach( $users as $user )
			{
				$id = $user->getElementsByTagName( "id" );
				$id = $id->item(0)->nodeValue;
				
				$name = $user->getElementsByTagName( "name" );
				$name = $name->item(0)->nodeValue;
				
				$screen_name= $user->getElementsByTagName( "screen_name" );
				$screen_name= $screen_name->item(0)->nodeValue;
				
				$location= $user->getElementsByTagName( "location" );
				$location= $location->item(0)->nodeValue;
				
				$description= $user->getElementsByTagName( "description" );
				$description= $description->item(0)->nodeValue;
				
				$profile_image_url= $user->getElementsByTagName( "profile_image_url" );
				$profile_image_url= $profile_image_url->item(0)->nodeValue;
				
				$url= $user->getElementsByTagName( "url" );
				$url= $url->item(0)->nodeValue;
				
				$protected= $user->getElementsByTagName( "protected" );
				$protected= $protected->item(0)->nodeValue;
				
				$created_at= $user->getElementsByTagName( "created_at" );
				$created_at= $created_at->item(0)->nodeValue;
				
				$followers_count= $user->getElementsByTagName( "followers_count" );
				$followers_count= $followers_count->item(0)->nodeValue;
				
				$friends_count= $user->getElementsByTagName( "friends_count" );
				$following = $friends_count->item(0)->nodeValue;
				
				$statuses_count= $user->getElementsByTagName( "statuses_count" );
				$tweets = $statuses_count->item(0)->nodeValue;
				
				$favourites_count= $user->getElementsByTagName( "favourites_count" );
				$favourites_count = $favourites_count->item(0)->nodeValue;
				
				$status = $user->getElementsByTagName( "status" );
				foreach($status as $stat)
				{
					$text = $stat->getElementsByTagName( "text" );
					$text = $text->item(0)->nodeValue;
					
					$tweet_created_at = $stat->getElementsByTagName( "created_at" );
					$tweet_created_at = $tweet_created_at->item(0)->nodeValue;
					
					
				}
				
				
				$record = $this->Twitter->find(array('user_id'=>$id));
				if($record)
				{
					$record['Twitter']['etime']		= $time;
					$record['Twitter']['request_token']	= $this->Session->read('oauth_request_token');
					$record['Twitter']['token_secret']	= $this->Session->read('oauth_request_token_secret');
					$record['Twitter']['access_token']	= $this->Session->read('oauth_access_token');
					$record['Twitter']['access_secret']	= $this->Session->read('oauth_access_token_secret');
					$record['Twitter']['tweets']		= $text;
					$record['Twitter']['tweet_created_at']	= strtotime($tweet_created_at);
					$this->Twitter->save($record);
					
				}
				else
				{
					$record['Twitter']['user_id']		= $id;
					$record['Twitter']['name']		= addslashes($name);
					$record['Twitter']['screen_name']	= addslashes($screen_name);
					$record['Twitter']['location']		= addslashes($location);
					$record['Twitter']['description']	= addslashes($description);
					$record['Twitter']['profile_image']	= addslashes($profile_image_url);
					$record['Twitter']['url']		= addslashes($url);
					$record['Twitter']['protected']		= $protected;
					$record['Twitter']['created_at']	= strtotime($created_at);
					$record['Twitter']['etime']		= $time;
					$record['Twitter']['request_token']	= $this->Session->read('oauth_request_token');
					$record['Twitter']['token_secret']	= $this->Session->read('oauth_request_token_secret');
					$record['Twitter']['access_token']	= $this->Session->read('oauth_access_token');
					$record['Twitter']['access_secret']	= $this->Session->read('oauth_access_token_secret');
					$record['Twitter']['tweets']		= $text;
					$record['Twitter']['tweet_created_at']	= strtotime($tweet_created_at);
					
					
					$this->Twitter->save($record);
					
				}
				
			
				
					
				$result = $this->Twtuser->find(array('user_id'=>$id , 'mmm_id'=>$mmm_id , 'band_id'=>$band_id));
				if($result)
				{
					$result['Twtuser']['active']	= '1' ;
					$result['Twtuser']['status']	= '1' ;
					
					
					
				}
				else
				{
					$result['Twtuser']['user_id']	= $id ;
					$result['Twtuser']['mmm_id']	= $mmm_id;
					$result['Twtuser']['band_id']	= $band_id;
				}
				
				$qry = "update twt_user set status=0 where mmm_id=$mmm_id and band_id=$band_id";
				$this->Twtuser->query($qry);
					
				$this->Twtuser->save($result);
				
				$date = date('Y-m-d',time());
				$stats = $this->Twtstats->find(array('user_id'=>$id,'from_unixtime(etime)'=>"LIKE %".$date."%"));
				if($stats)
				{
					$stats['Twtstats']['follower'] 	= $followers_count ;
					$stats['Twtstats']['following']	= $following ;
					$stats['Twtstats']['tweets']	= $tweets ;
					$stats['Twtstats']['favorites']	= $favourites_count;
					$stats['Twtstats']['etime']	= $time;
					$this->Twtstats->save($stats);
					
				}
				else
				{
					$stats['Twtstats']['user_id']	= $id;
					$stats['Twtstats']['follower'] 	= $followers_count ;
					$stats['Twtstats']['following']	= $following ;
					$stats['Twtstats']['tweets']	= $tweets ;
					$stats['Twtstats']['favorites']	= $favourites_count;
					$stats['Twtstats']['etime']	= $time;
					$this->Twtstats->create();
					$this->Twtstats->save($stats);
					
					
				}
				
					
			
			} // foreach( $users as $user )
			
			
			$doc->loadXML( $dm );
			
			$directmsg = $doc->getElementsByTagName( "direct-messages" );
			
			foreach( $directmsg as $directms )
			{
				$dmsg = $directms->getElementsByTagName( "direct_message" );
				
				foreach($dmsg as $dms)
				{
					$dm_id = $dms->getElementsByTagName( "id" );
					$dm_id = $dm_id->item(0)->nodeValue;
					
					$sender_id = $dms->getElementsByTagName( "sender_id" );
					$sender_id = $sender_id->item(0)->nodeValue;
					
					$text = $dms->getElementsByTagName( "text" );
					$text = $text->item(0)->nodeValue;
					
					$created_at = $dms->getElementsByTagName( "created_at" );
					$created_at = $created_at->item(0)->nodeValue;
					
					$sender_screen_name = $dms->getElementsByTagName( "sender_screen_name" );
					$sender_screen_name = $sender_screen_name->item(0)->nodeValue;
					
					$dm_result = $this->Twtdm->find(array('user_id'=>$id , 'created_at'=>strtotime($created_at)));
					if(empty($dm_result))
					{
						$dm_result['Twtdm']['user_id']			=	$id;
						$dm_result['Twtdm']['dm_id']			=	$dm_id;
						$dm_result['Twtdm']['sender_id']		=	$sender_id;
						$dm_result['Twtdm']['sender_screen_name']	=	addslashes($sender_screen_name);
						$dm_result['Twtdm']['message']			=	$text;
						$dm_result['Twtdm']['created_at']		=	strtotime($created_at);
						$dm_result['Twtdm']['etime']			=	$time;
						$this->Twtdm->create();
						$this->Twtdm->save($dm_result);
						
					}
					
				}
			}
			
			
			$doc->loadXML( $mentions );
			
			$statuses = $doc->getElementsByTagName( "statuses" );
			
			foreach( $statuses as $statuse )
			{
				$status = $statuse->getElementsByTagName( "status" );
				
				foreach($status as $stat)
				{
					$mention_id = $stat->getElementsByTagName( "id" );
					$mention_id = $mention_id->item(0)->nodeValue;
					
					$truncated = $stat->getElementsByTagName( "truncated" );
					$truncated = $truncated->item(0)->nodeValue;
					
					$text = $stat->getElementsByTagName( "text" );
					$text = $text->item(0)->nodeValue;
					
					$created_at = $stat->getElementsByTagName( "created_at" );
					$created_at = $created_at->item(0)->nodeValue;
					
					$in_reply_to_screen_name = $stat->getElementsByTagName( "in_reply_to_screen_name" );
					$screen_name = $in_reply_to_screen_name->item(0)->nodeValue;
					
					$user = $stat->getElementsByTagName( "user" );
					
					foreach($user as $us)
					{
						$us_id = $us->getElementsByTagName( "id" );
						$us_id = $us_id->item(0)->nodeValue;
						
						$us_name = $us->getElementsByTagName( "name" );
						$us_name = $us_name->item(0)->nodeValue;
						
						$us_screen_name = $us->getElementsByTagName( "screen_name" );
						$us_screen_name = $us_screen_name->item(0)->nodeValue;
					}
					
					
					
					
					$mentions_result = $this->Twtmentions->find(array('user_id'=>$id , 'created_at'=>strtotime($created_at)));
					if(empty($mentions_result))
					{
						$mentions_result['Twtmentions']['user_id']			=	$id;
						$mentions_result['Twtmentions']['mention_id']			=	$mention_id;
						$mentions_result['Twtmentions']['truncated']			=	$truncated;
						$mentions_result['Twtmentions']['screen_name']			=	addslashes($screen_name);
						$mentions_result['Twtmentions']['message']			=	addslashes($text);
						$mentions_result['Twtmentions']['reply_id']			=	$us_id;
						$mentions_result['Twtmentions']['reply_name']			=	addslashes($us_name);
						$mentions_result['Twtmentions']['reply_screen_name']		=	addslashes($us_screen_name);
						$mentions_result['Twtmentions']['created_at']			=	strtotime($created_at);
						$mentions_result['Twtmentions']['etime']			=	$time;
						$this->Twtmentions->create();
						$this->Twtmentions->save($mentions_result);
						
					}
					
				}
			}
			
			$doc->loadXML( $statuses_tweets );
			
			$statusesmsg= $doc->getElementsByTagName( "statuses" );
			
			foreach( $statusesmsg as $statuses )
			{
				$statusmsg = $statuses->getElementsByTagName( "status" );
				
				foreach($statusmsg as $status)
				{
					$status_id = $status->getElementsByTagName( "id" );
					$status_id = $status_id->item(0)->nodeValue;
					
					$text = $status->getElementsByTagName( "text" );
					$text = $text->item(0)->nodeValue;
					
					$created_at = $status->getElementsByTagName( "created_at" );
					$created_at = $created_at->item(0)->nodeValue;
					
					$source = $status->getElementsByTagName( "source" );
					$source = $source->item(0)->nodeValue;
					
					$truncated = $status->getElementsByTagName( "truncated" );
					$truncated = $truncated->item(0)->nodeValue;
					
					$favorited = $status->getElementsByTagName( "favorited" );
					$favorited = $favorited->item(0)->nodeValue;
					
					$reply_status_id = $status->getElementsByTagName( "in_reply_to_status_id" );
					$reply_status_id = $reply_status_id->item(0)->nodeValue;
					
					$reply_user_id = $status->getElementsByTagName( "in_reply_to_user_id" );
					$reply_user_id = $reply_user_id->item(0)->nodeValue;
					
					$reply_screen_name = $status->getElementsByTagName( "in_reply_to_screen_name" );
					$reply_screen_name = $reply_screen_name->item(0)->nodeValue;
					
					$user = $status->getElementsByTagName( "user" );
							
							foreach($user as $us)
							{
								$us_id = $us->getElementsByTagName( "id" );
								$us_id = $us_id->item(0)->nodeValue;
								
								$us_name = $us->getElementsByTagName( "name" );
								$us_name = $us_name->item(0)->nodeValue;
								
								$us_screen_name = $us->getElementsByTagName( "screen_name" );
								$us_screen_name = $us_screen_name->item(0)->nodeValue;
							}
					
					$status_result = $this->Twtstatus->find(array('user_id'=>$id , 'created_at'=>strtotime($created_at)));
					if(empty($status_result))
					{
						$status_result['Twtstatus']['user_id']			=	$id;
						$status_result['Twtstatus']['id']			=	$status_id;
						$status_result['Twtstatus']['text']			=	addslashes($text);
						$status_result['Twtstatus']['source']			=	addslashes($source);
						$status_result['Twtstatus']['truncated']		=	$truncated;
						$status_result['Twtstatus']['in_reply_status']		=	$reply_status_id;
						$status_result['Twtstatus']['created_at']		=	strtotime($created_at);
						$status_result['Twtstatus']['etime']			=	$time;
						$status_result['Twtstatus']['in_reply_user']		=	$reply_user_id;
						$status_result['Twtstatus']['favorited']		=	$favorited;
						$status_result['Twtstatus']['in_reply_screen_name']	=	addslashes($reply_screen_name);
						$status_result['Twtstatus']['twt_user_id']		=	$us_id;
						$status_result['Twtstatus']['twt_name']			=	addslashes($us_name);
						$status_result['Twtstatus']['twt_screen_name']		=	addslashes($us_screen_name);
						
						$this->Twtstatus->create();
						$this->Twtstatus->save($status_result);
						
					}
					
				}
			}
			
			$this->Session->del('oauth_request_token');
			$this->Session->del('oauth_request_token_secret');
			$this->Session->del('oauth_access_token');
			$this->Session->del('oauth_access_token_secret');
					
			$this->Session->write('oauth_state','start');
					
			$this->Session->setFlash('Twitter information has been processed correctly.');
			
			if($flag=='b')
			{
				$this->redirect('/band/thanks/');
			}
			else
			{
				$this->redirect('/band/manage/');
			}
			exit;
				
		} // if($this->Session->check('oauth_state'))
		else
		{
			$this->Session->setFlash('Some problem with Twitter procedure. Please try again');
			
			if($flag=='b')
			{
				$this->redirect('/band/twitter/');
			}
			else
			{
				$this->redirect('/band/manage/');
			}
			exit;
		}
	}
	
	/* name :  updateTwitter
	*  description  : update twitter data for each user
	*/
	
	function updateTwitter()
	{
		if(!empty($this->params['url']['bandid']))
		{
			$mmm_id =  $this->Session->read('id');
			$band_id = $this->params['url']['bandid'];
			$twtResult = $this->Twtuser->find(array('mmm_id'=>$mmm_id , 'band_id'=>$band_id ,'status'=>1));
			
			if($twtResult)
			{
				$result = $this->Twitter->findAll(array('user_id'=>$twtResult['Twtuser']['user_id']));
			
				if($result)
				{
					foreach($result as $tkey => $tval)
					{
						$tdate = date('d-m-Y H:i',strtotime(date('d-m-Y H:i',$tval['Twitter']['etime'])." + 1 hour"));
						$fdate = date('d-m-Y H:i',time());
						$time = time();
						
					//	if($tdate > $fdate)
					//	{
					//		continue;
					//	}
						if($this->Session->check('oauth_request_token'))
								{
									$this->Session->del('oauth_request_token');
								}
								
								if($this->Session->check('oauth_request_token_secret'))
								{
								   $this->Session->del('oauth_request_token_secret');
								}
								
								if($this->Session->check('oauth_access_token'))
								{
									$this->Session->del('oauth_access_token');
								}
								
								if($this->Session->check('oauth_access_token_secret'))
								{
									$this->Session->del('oauth_access_token_secret');	
								}
				
								
								
								
								
						$request_token = $tval['Twitter']['request_token'];
						$token_secret = $tval['Twitter']['token_secret'];
						$access_token = $tval['Twitter']['access_token'];
						$access_secret = $tval['Twitter']['access_secret'];
				
						$this->Session->write('oauth_request_token', $request_token);
						$this->Session->write('oauth_request_token_secret',$token_secret);
						$this->Session->write('oauth_state','returned');
						$this->Session->write('oauth_access_token',$access_token);
						$this->Session->write('oauth_access_token_secret',$access_secret);
						
						
						 /* Create TwitterOAuth with app key/secret and user access key/secret */
						$to = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $access_token , $access_secret);
				 
						
						/* Run request on twitter API as user. */
						 $content = $to->OAuthRequest('https://twitter.com/account/verify_credentials.xml', array(), 'GET');
						 $dm = $to->OAuthRequest('http://twitter.com/direct_messages.xml?count=200', array(), 'GET');
						 $mentions = $to->OAuthRequest('http://twitter.com/statuses/mentions.xml', array(), 'GET');
						 $statuses_tweets = $to->OAuthRequest('http://twitter.com/statuses/friends_timeline.xml', array(), 'GET');
						 
									
					$doc = new DOMDocument();
					$doc->loadXML( $content );
					
					$users = $doc->getElementsByTagName( "user" );
					
					foreach( $users as $user )
					{
						$id = $user->getElementsByTagName( "id" );
						$id = $id->item(0)->nodeValue;
						
						$name = $user->getElementsByTagName( "name" );
						$name = $name->item(0)->nodeValue;
						
						$screen_name= $user->getElementsByTagName( "screen_name" );
						$screen_name= $screen_name->item(0)->nodeValue;
						
						$location= $user->getElementsByTagName( "location" );
						$location= $location->item(0)->nodeValue;
						
						$description= $user->getElementsByTagName( "description" );
						$description= $description->item(0)->nodeValue;
						
						$profile_image_url= $user->getElementsByTagName( "profile_image_url" );
						$profile_image_url= $profile_image_url->item(0)->nodeValue;
						
						$url= $user->getElementsByTagName( "url" );
						$url= $url->item(0)->nodeValue;
						
						$protected= $user->getElementsByTagName( "protected" );
						$protected= $protected->item(0)->nodeValue;
						
						$created_at= $user->getElementsByTagName( "created_at" );
						$created_at= $created_at->item(0)->nodeValue;
						
						$followers_count= $user->getElementsByTagName( "followers_count" );
						$followers_count= $followers_count->item(0)->nodeValue;
						
						$friends_count= $user->getElementsByTagName( "friends_count" );
						$following = $friends_count->item(0)->nodeValue;
						
						$statuses_count= $user->getElementsByTagName( "statuses_count" );
						$tweets = $statuses_count->item(0)->nodeValue;
						
						$favourites_count= $user->getElementsByTagName( "favourites_count" );
						$favourites_count = $favourites_count->item(0)->nodeValue;
						
						$status = $user->getElementsByTagName( "status" );
						foreach($status as $stat)
						{
							$text = $stat->getElementsByTagName( "text" );
							$text = $text->item(0)->nodeValue;
							
							$twt_created_at = $stat->getElementsByTagName( "created_at" );
							$twt_created_at = $twt_created_at->item(0)->nodeValue;
						}
						
						
							$record['Twitter']['refresh_time']	=  $tval['Twitter']['etime'];
							$record['Twitter']['user_id']		=  $tval['Twitter']['user_id'];
							$record['Twitter']['tweets']		=  addslashes($text);
							$record['Twitter']['etime']		=  $time;
							$record['Twitter']['tweet_created_at']  = strtotime($twt_created_at);
						
							
							$this->Twitter->save($record);
							
					
						
						$date   = date('Y-m-d',time());
						$id	= $tval['Twitter']['user_id'];
						
						$stats = $this->Twtstats->find(array('user_id'=>$id,'from_unixtime(etime)'=>"LIKE %".$date."%"));
						if($stats)
						{
							$stats['Twtstats']['follower'] 	= $followers_count ;
							$stats['Twtstats']['following']	= $following ;
							$stats['Twtstats']['tweets']	= $tweets ;
							$stats['Twtstats']['favorites']	= $favourites_count;
							$stats['Twtstats']['etime']	= $time;
							$this->Twtstats->save($stats);
							
						}
						else
						{
							$stats['Twtstats']['user_id']	= $id;
							$stats['Twtstats']['follower'] 	= $followers_count ;
							$stats['Twtstats']['following']	= $following ;
							$stats['Twtstats']['tweets']	= $tweets ;
							$stats['Twtstats']['favorites']	= $favourites_count;
							$stats['Twtstats']['etime']	= $time;
							$this->Twtstats->create();
							$this->Twtstats->save($stats);
							
							
						}
						
							
					
					} // foreach( $users as $user )
					
					
					
					if($dm)
					{
						$doc->loadXML( $dm );
						
						$directmsg = $doc->getElementsByTagName( "direct-messages" );
						
						foreach( $directmsg as $directms )
						{
							
							$dmsg = $directms->getElementsByTagName( "direct_message" );
							
							foreach($dmsg as $dms)
							{
								
								$dm_id = $dms->getElementsByTagName( "id" );
								$dm_id = $dm_id->item(0)->nodeValue;
								
								$sender_id = $dms->getElementsByTagName( "sender_id" );
								$sender_id = $sender_id->item(0)->nodeValue;
								
								$text = $dms->getElementsByTagName( "text" );
								$text = $text->item(0)->nodeValue;
								
								$created_at = $dms->getElementsByTagName( "created_at" );
								$created_at = $created_at->item(0)->nodeValue;
								
								$sender_screen_name = $dms->getElementsByTagName( "sender_screen_name" );
								$sender_screen_name = $sender_screen_name->item(0)->nodeValue;
								
								$dm_result = $this->Twtdm->find(array('user_id'=>$id , 'created_at'=>strtotime($created_at)));
								if(empty($dm_result))
								{
									$dm_result['Twtdm']['user_id']			=	$id;
									$dm_result['Twtdm']['dm_id']			=	$dm_id;
									$dm_result['Twtdm']['sender_id']		=	$sender_id;
									$dm_result['Twtdm']['sender_screen_name']	=	addslashes($sender_screen_name);
									$dm_result['Twtdm']['message']			=	addslashes($text);
									$dm_result['Twtdm']['created_at']		=	strtotime($created_at);
									$dm_result['Twtdm']['etime']			=	$time;
									$this->Twtdm->create();
									$this->Twtdm->save($dm_result);
									
								}
								
							}
						}
						
					} // if($dm)
					
					
					if($mentions)
					{
					
						$doc->loadXML( $mentions );
						
						$statuses = $doc->getElementsByTagName( "statuses" );
						
						foreach( $statuses as $statuse )
						{
							$status = $statuse->getElementsByTagName( "status" );
							
							foreach($status as $stat)
							{
								$mention_id = $stat->getElementsByTagName( "id" );
								$mention_id = $mention_id->item(0)->nodeValue;
								
								$truncated = $stat->getElementsByTagName( "truncated" );
								$truncated = $truncated->item(0)->nodeValue;
								
								$text = $stat->getElementsByTagName( "text" );
								$text = $text->item(0)->nodeValue;
								
								$created_at = $stat->getElementsByTagName( "created_at" );
								$created_at = $created_at->item(0)->nodeValue;
								
								$in_reply_to_screen_name = $stat->getElementsByTagName( "in_reply_to_screen_name" );
								$screen_name = $in_reply_to_screen_name->item(0)->nodeValue;
								
								$user = $stat->getElementsByTagName( "user" );
								
								foreach($user as $us)
								{
									$us_id = $us->getElementsByTagName( "id" );
									$us_id = $us_id->item(0)->nodeValue;
									
									$us_name = $us->getElementsByTagName( "name" );
									$us_name = $us_name->item(0)->nodeValue;
									
									$us_screen_name = $us->getElementsByTagName( "screen_name" );
									$us_screen_name = $us_screen_name->item(0)->nodeValue;
								}
								
								
								
								
								$mentions_result = $this->Twtmentions->find(array('user_id'=>$id , 'created_at'=>strtotime($created_at)));
								if(empty($mentions_result))
								{
									$mentions_result['Twtmentions']['user_id']			=	$id;
									$mentions_result['Twtmentions']['mention_id']			=	$mention_id;
									$mentions_result['Twtmentions']['truncated']			=	$truncated;
									$mentions_result['Twtmentions']['screen_name']			=	addslashes($screen_name);
									$mentions_result['Twtmentions']['message']			=	addslashes($text);
									$mentions_result['Twtmentions']['reply_id']			=	$us_id;
									$mentions_result['Twtmentions']['reply_name']			=	addslashes($us_name);
									$mentions_result['Twtmentions']['reply_screen_name']		=	addslashes($us_screen_name);
									$mentions_result['Twtmentions']['created_at']			=	strtotime($created_at);
									$mentions_result['Twtmentions']['etime']			=	$time;
									$this->Twtmentions->create();
									$this->Twtmentions->save($mentions_result);
									
								}
								
							}
						
						
						
						}
					} // if($mentions)
						
					if($statuses_tweets)
					{
						$doc->loadXML( $statuses_tweets );
				
						$statusesmsg= $doc->getElementsByTagName( "statuses" );
						
						foreach( $statusesmsg as $statuses )
						{
							$statusmsg = $statuses->getElementsByTagName( "status" );
							
							foreach($statusmsg as $status)
							{
								$status_id = $status->getElementsByTagName( "id" );
								$status_id = $status_id->item(0)->nodeValue;
								
								$text = $status->getElementsByTagName( "text" );
								$text = $text->item(0)->nodeValue;
								
								$created_at = $status->getElementsByTagName( "created_at" );
								$created_at = $created_at->item(0)->nodeValue;
								
								$source = $status->getElementsByTagName( "source" );
								$source = $source->item(0)->nodeValue;
								
								$truncated = $status->getElementsByTagName( "truncated" );
								$truncated = $truncated->item(0)->nodeValue;
								
								$favorited = $status->getElementsByTagName( "favorited" );
								$favorited = $favorited->item(0)->nodeValue;
								
								$reply_status_id = $status->getElementsByTagName( "in_reply_to_status_id" );
								$reply_status_id = $reply_status_id->item(0)->nodeValue;
								
								$reply_user_id = $status->getElementsByTagName( "in_reply_to_user_id" );
								$reply_user_id = $reply_user_id->item(0)->nodeValue;
								
								$reply_screen_name = $status->getElementsByTagName( "in_reply_to_screen_name" );
								$reply_screen_name = $reply_screen_name->item(0)->nodeValue;
								
								$user = $status->getElementsByTagName( "user" );
								
								foreach($user as $us)
								{
									$us_id = $us->getElementsByTagName( "id" );
									$us_id = $us_id->item(0)->nodeValue;
									
									$us_name = $us->getElementsByTagName( "name" );
									$us_name = $us_name->item(0)->nodeValue;
									
									$us_screen_name = $us->getElementsByTagName( "screen_name" );
									$us_screen_name = $us_screen_name->item(0)->nodeValue;
								}
								
								$status_result = $this->Twtstatus->find(array('user_id'=>$id , 'created_at'=>strtotime($created_at)));
								if(empty($status_result))
								{
									$status_result['Twtstatus']['user_id']			=	$id;
									$status_result['Twtstatus']['id']			=	$status_id;
									$status_result['Twtstatus']['text']			=	addslashes($text);
									$status_result['Twtstatus']['source']			=	addslashes($source);
									$status_result['Twtstatus']['truncated']		=	$truncated;
									$status_result['Twtstatus']['in_reply_status']		=	$reply_status_id;
									$status_result['Twtstatus']['created_at']		=	strtotime($created_at);
									$status_result['Twtstatus']['etime']			=	$time;
									$status_result['Twtstatus']['in_reply_user']		=	$reply_user_id;
									$status_result['Twtstatus']['favorited']		=	$favorited;
									$status_result['Twtstatus']['in_reply_screen_name']	=	addslashes($reply_screen_name);
									$status_result['Twtstatus']['twt_user_id']		=	$us_id;
									$status_result['Twtstatus']['twt_name']			=	addslashes($us_name);
									$status_result['Twtstatus']['twt_screen_name']		=	addslashes($us_screen_name);
									$this->Twtstatus->create();
									$this->Twtstatus->save($status_result);
									
								}
								
							}
						}
					} //if($statuses_tweets)
								
						 
					} 
		
				} // if($result)
			}
		}
	exit;
	} // function updateTwitter
	
	
	/**
	 * Name: updateStatus
	 * descrition : update twitter status .
	 * called from : dashboard
	 * return : status message to dashbaord.
	 */
	function updateStatus()
	{
		if($this->data)
		{
			$mmm_id =  $this->Session->read('id');
			$band_id = $this->data['Dashboard']['bandid'];
			
			if(!empty($this->data['Dashboard']['twt'])=="on")
			{
			
				$twtResult = $this->Twtuser->find(array('mmm_id'=>$mmm_id , 'band_id'=>$band_id ,'status'=>1));
			
						
				if($twtResult)
				{
					$result = $this->Twitter->find(array('user_id'=>$twtResult['Twtuser']['user_id']));
					if($result)
					{
						
							if($this->Session->check('oauth_request_token'))
							{
								$this->Session->del('oauth_request_token');
							}
							
							if($this->Session->check('oauth_request_token_secret'))
							{
							   $this->Session->del('oauth_request_token_secret');
							}
							
							if($this->Session->check('oauth_access_token'))
							{
								$this->Session->del('oauth_access_token');
							}
							
							if($this->Session->check('oauth_access_token_secret'))
							{
								$this->Session->del('oauth_access_token_secret');	
							}
			
							
							$request_token = $result['Twitter']['request_token'];
							$token_secret = $result['Twitter']['token_secret'];
							$access_token = $result['Twitter']['access_token'];
							$access_secret = $result['Twitter']['access_secret'];
					
							$this->Session->write('oauth_request_token', $request_token);
							$this->Session->write('oauth_request_token_secret',$token_secret);
							$this->Session->write('oauth_state','returned');
							$this->Session->write('oauth_access_token',$access_token);
							$this->Session->write('oauth_access_token_secret',$access_secret);
							
							
							// Create TwitterOAuth with app key/secret and user access key/secret 
							$to = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $access_token , $access_secret);
							sleep(2);
							$status = $this->data['Dashboard']['status'];
							$xcontent = $to->OAuthRequest('https://twitter.com/statuses/update.xml', array('status' => $status), 'POST');
							
							sleep(2);
							if($xcontent)
							{
								$result['Twitter']['tweets'] = $status;
								$this->Twitter->save($result);
								echo "Twitter status updated succesfully <br>";
							}else
							{
								echo "Unable to update Twitter status . Try again.";
							}
							//$this->redirect("/dashboard/index/?bandid=".$band_id);
							exit;
					}
					
				}
				
			} // if($this->data['Dashboard']['twt']=="on")

		}
		exit;
	}
	
	/*
	 name : chart
	 set : get and set data for Followers , following , tweets , favorites
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
			$this->Session->write('twt_user_id',$this->params['url']['id']);
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
			if($this->Session->check('twt_user_id'))
			{
				
				$lastdate = $this->params['url']['date'];
				$opt = $this->params['url']['opt'];
				$id=trim($this->Session->read('twt_user_id'));
				
				
				//	get graph data for follower
				
				$this->getGraphData($id,$lastdate,$opt,'follower'); // Get & set follower data for graphs , params( id , date , option , field)
				if($this->params['url']['type']=='follower')
				{
					$this->Session->write('type','follower');
				}
				
				
				//	get graph data for following
				
				$this->getGraphData($id,$lastdate,$opt,'following'); // Get & set following data for graphs , params( id , date , option , field)
				if($this->params['url']['type']=='following')
				{
					$this->Session->write('type','following');
				}
				
				
				//	get graph data for tweets
								
				$clastdate=$this->getGraphData($id,$lastdate,$opt,'tweets'); // Get & set tweets data for graphs , params( id , date , option , field)
				if($this->params['url']['type']=='tweets')
				{
					$this->Session->write('type','tweets');
				}
				
				//	get graph data for favorites
				
				$this->getGraphData($id,$lastdate,$opt,'favorites'); // Get & set favorites data for graphs , params( id , date , option , field)
				if($this->params['url']['type']=='favorites')
				{
					$this->Session->write('type','favorites');
				}
			} // if($this->Session->check('twt_user_id'))
			
		} // if(!empty($this->params['url']['date']) and !empty($this->params['url']['opt']))
		else   
		{
			$this->set('flag',0);
			if($this->Session->check('twt_user_id'))
			{
				$mmm_id = $this->Session->read('id');
				$twt_user_id = $this->Session->read('twt_user_id');
				
				
				$results = $this->Twtuser->find(array('mmm_id'=>$mmm_id,'user_id'=>$twt_user_id)); //
				if($results)
				{
					$lastdate=$this->getGraphData($twt_user_id,'w','d','follower'); // Get & set folowers data for graphs , params( id , date , option , field)
					$flastdate=$this->getGraphData($twt_user_id,'w','d','following'); // Get & set following data for graphs , params( id , date , option , field)
					$clastdate=$this->getGraphData($twt_user_id,'w','d','tweets'); // Get & set tweets data for graphs , params( id , date , option , field)
					$clastdate=$this->getGraphData($twt_user_id,'w','d','favorites'); // Get & set favorites data for graphs , params( id , date , option , field)
					
					
				
					$this->Session->write('type','follower');
					
	
				} // if($results)
				else
				{
					if($this->Session->check('band_id'))
						$this->set('bandid',$this->Session->read('band_id'));
					$this->Session->setFlash("No Twitter data found.$mmm_id - $twt_user_id");
					$this->set('flag',1);
					
				} // if($results)
					
			} // if($this->Session->check('twt_user_id'))
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
					$qry_twt = "select s.$field , s.etime from twt_stats s where s.user_id=$id and FROM_UNIXTIME(s.etime) between DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 DAY) AND DATE_ADD(CURDATE( ), INTERVAL 1 DAY) order by etime";
					$qry= "select DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 Month),INTERVAL -1 Day) last, CURDATE( ) curr";
					
					
				}
				else
				{
					$qry_twt = "select s.$field , s.etime from twt_stats s where s.user_id=$id and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Month) AND CURDATE( ) order by etime";
					$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Month) last, CURDATE( ) curr";
					
				}
				
				
				$twt = $this->Twtstats->findBySql($qry_twt);
				$tfdate = $this->Twtstats->findBySql($qry);
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
				$qry_twt = "select s.$field , s.etime from twt_stats s where s.user_id=$id and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 Year) AND DATE_ADD(CURDATE( ), INTERVAL 1 DAY) order by etime";
				$twt = $this->Twtstats->findBySql($qry_twt);
				$twt = $this->getyear($twt,'s',$field);

				$qry= "select DATE_SUB(CURDATE(), INTERVAL 1 Year) last, DATE_ADD(CURDATE( ), INTERVAL 1 DAY) curr";
				$tfdate = $this->Twtstats->findBySql($qry);

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
					$qry_twt = "select s.$field , s.etime from twt_stats s where s.user_id=$id   and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 8 DAY) AND DATE_ADD(CURDATE( ), INTERVAL 1 DAY) order by etime";
				
					
				}
				else
				{
					$qry_twt = "select s.$field , s.etime from twt_stats s where s.user_id=$id  and FROM_UNIXTIME(s.etime) between DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE( ) order by etime";
					
					
				
				}
				
				$twt = $this->Twtstats->findBySql($qry_twt); // to get myspace stats
				
				$qry= "select DATE_SUB(CURDATE(), INTERVAL 7 DAY) last, DATE_ADD(CURDATE( ), INTERVAL 1 DAY) curr";
				$tfdate = $this->Twtstats->findBySql($qry); // to get date
	
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


			
			
			if(empty($twt))
			{	
				$twtcount=0;
			} // if(empty($twt))
			else
			{
				$twtcount = count($twt);		
			} // if(empty($twt))

			
			$cnt=0;
			$predate=0;
			$twtdatacount = 0;
			
			
			foreach($dt as $key => $val)  // main date loop
			{
				$flg=0;
				
				if($lastdate=='y')
				{
					for($i=0 ; $i < $twtcount ; $i++)  // set Myspace views according to main loop date
					{
						if($opt=='c')
						{
							if($twt[$i]['s']['etime']==$val)
							{
								$twtdata[$cnt]= $twt[$i]['s'][$field] ;
								$twtdatacount+=$twtdata[$cnt];
						
								$flg=1;
								$cnt++;
								break;
							} //	if($twt_data[$i]['s']['etime'])==$val)
						}
						elseif($opt=='d')
							{
								if($twt[$i]['s']['etime']==$val)
								{
									
									if($i!=0)
									{
										$twtdata[$cnt]= $twt[$i]['s'][$field]-$twt[$i-1]['s'][$field];
										$twtdatacount += $twtdata[$cnt] ;
									}
									else
									{
										$twtdata[$cnt]= $twt[$i]['s'][$field];
										
									}
										$flg=1;
										$cnt++;
										break;
								}
								
							}
							
					} // for($i=0 ; $i < $twtcount-1 ; $i++)
					if($flg==0)
					{
						$twtdata[$cnt]=0;
						$cnt++;
					}
			
				}	 // if($lastdate=='y')
				else
				{				
					for($i=0 ; $i < $twtcount ; $i++)  // set Myspace views according to main loop date
					{
						if(date('Y-m-d',$twt[$i]['s']['etime'])==$val)
						{
							
							if($opt=='c')
							{
								
								if(date('Y-m-d',$twt[$i]['s']['etime'])!=$predate)
								{
									$predate = date('Y-m-d',$twt[$i]['s']['etime']);
									
																			
										$twtdata[$cnt]= $twt[$i]['s'][$field];
										
										$twtdatacount += $twtdata[$cnt] ;
									
									$flg=1;
									$cnt++;
								}
								break;
							}
							elseif($opt=='d')
							{
								if($i!=0)
								{
									$twtdata[$cnt]= $twt[$i]['s'][$field]-$twt[$i-1]['s'][$field];
									$twtdatacount += $twtdata[$cnt] ;
								}
								else
								{
									$twtdata[$cnt] = 0;
								}
								$flg=1;
								$cnt++;
								break;
							}

						} //	if(date('Y-m-d',$twt[$i]['s']['etime'])==$val)
					} // for($i=0 ; $i < $twtcount-1 ; $i++)

					if($flg==0)
					{
						$twtdata[$cnt]=0;
						$cnt++;

					}
				}  // if($lastdate=='y')
			} // foreach($dt as $key => $val)  // main date loop
			
				if($twtdata[$cnt-1]==0)
				{
					$percentage = 0;
					$wpercentage = 0;
				}
				else
				{
					$percentage = round((($twtdata[$cnt-1]-$twtdata[$cnt-2])/$twtdata[$cnt-1])*100,2);
					$wpercentage = round((($twtdata[$cnt-1]-$twtdata[$cnt-7])/$twtdata[$cnt-1])*100,2);
				}
				
				$diff = $twtdata[$cnt-1]-$twtdata[$cnt-2];
				$wdiff = $twtdata[$cnt-1]-$twtdata[$cnt-7];
			
			
			
				if($field=='follower')
				{
					
					$this->Session->write('twtfollowers',$twtdata);
					$this->set('frpercentage',$percentage);
					$this->set('frdiff',$diff);
					$this->set('frwpercentage',$wpercentage);
					$this->set('frwdiff',$wdiff);
					$this->set('frtotal',$twtdata[$cnt-1]);
										
					
				}
				elseif($field=='following')
				{
					$this->Session->write('twtFollowing',$twtdata);
					$this->set('fgpercentage',$percentage);
					$this->set('fgdiff',$diff);
					$this->set('fgwpercentage',$wpercentage);
					$this->set('fgwdiff',$wdiff);
					$this->set('fgtotal',$twtdata[$cnt-1]);
					
				}
				elseif($field=='tweets')
				{
					$this->Session->write('twtTweets',$twtdata);
					$this->set('tpercentage',$percentage);
					$this->set('tdiff',$diff);
					$this->set('twpercentage',$wpercentage);
					$this->set('twdiff',$wdiff);
					$this->set('ttotal',$twtdata[$cnt-1]);
					
				}
				elseif($field=='favorites')
				{
					$this->Session->write('twtFavorites',$twtdata);
					$this->set('fpercentage',$percentage);
					$this->set('fdiff',$diff);
					$this->set('fwpercentage',$wpercentage);
					$this->set('fwdiff',$wdiff);
					$this->set('ftotal',$twtdata[$cnt-1]);
					
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
	
	/*
	 * name : followerchart
	 * description : followers chart
	 */
	function followerchart()
	{
		
		$this->set('twtFollower',$this->Session->read('twtfollowers'));
		$this->set('dt',$this->Session->read('dt'));
	}
	
	/*
	 * name : followingchart
	 * description : following chart
	 */
	function followingchart()
	{
		
		$this->set('twtFollowing',$this->Session->read('twtFollowing'));
		$this->set('dt',$this->Session->read('dt'));
	}
	
	/*
	 * name : tweetschart
	 * description : tweets chart
	 */
	function tweetschart()
	{
		
		$this->set('twtTweets',$this->Session->read('twtTweets'));
		$this->set('dt',$this->Session->read('dt'));
	}
	
	/*
	 * name : tweetschart
	 * description : tweets chart
	 */
	function favoriteschart()
	{
		
		$this->set('twtFavorites',$this->Session->read('twtFavorites'));
		$this->set('dt',$this->Session->read('dt'));
	}
	
	/* name : getyear
	  * description : convert days into year and return yearly data.
	  *
	*/
	function getyear($twt,$type,$field)
	{
		$vms=NULL;
		for($i=0 ; $i<count($twt);$i++)
		{
			$vms[] = array($field=>$twt[$i][$type][$field],'time'=>$twt[$i][$type]['etime']);
					
		} //	for($i=0 ; $i<count($twt);$i)
			
			
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
