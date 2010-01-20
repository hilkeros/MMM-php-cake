<?php
/*
Developed by : Babar Ali
Project	     : MMM
*/

vendor('twitter/twitterOAuth');
       
class TwitterupdateController extends AppController
{
	var $uses = array('Twitter','Twtuser','Twtstats','Twtdm','Twtmentions','Twtstatus');
	var $helpers = array('Html', 'Error', 'Javascript', 'Ajax', 'Pagination');
	var $components = array('Cookie'); //  use component email
	var $consumer_key = '6gd1IvNlyO9YQPox4GLeg';
	var $consumer_secret = 'HjTW5UVDCtwetGX93JVNGjkPN882UvLWwEACCpAaA';
	
	/*
	 name : index
	 description : twitter daily fetcher
	*/
	function index()
	{
		
		$this->program_start_time= microtime(true);
		$result = $this->Twitter->findAll();
		if($result)
		{
			foreach($result as $tkey => $tval)
			{
				
				$tdate=	date('d-m-Y',$tval['Twitter']['etime']);
				$fdate = date('d-m-Y',time());
				$time = time();
				
				if($tdate==$fdate)
				{
					echo $tval['Twitter']['user_id']."<br>";
					continue;
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
				$to = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->Session->read('oauth_access_token'), $this->Session->read('oauth_access_token_secret'));
		 
				/* Run request on twitter API as user. */
				
				//	$content .= $to->OAuthRequest('https://twitter.com/statuses/followers.xml?user_id=60862714', array(), 'GET');
				//	$xcontent = $to->OAuthRequest('https://twitter.com/statuses/update.xml', array('status' => '3nd post .. Busy in work....'), 'POST');
				//	$content .= $to->OAuthRequest('http://twitter.com/account/rate_limit_status.xml', array(), 'GET');
			
				
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
						$record['Twitter']['tweets']		=  $text;
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
								$dm_result['Twtdm']['sender_screen_name']	=	$sender_screen_name;
								$dm_result['Twtdm']['message']			=	$text;
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
								$mentions_result['Twtmentions']['screen_name']			=	$screen_name;
								$mentions_result['Twtmentions']['message']			=	$text;
								$mentions_result['Twtmentions']['reply_id']			=	$us_id;
								$mentions_result['Twtmentions']['reply_name']			=	$us_name;
								$mentions_result['Twtmentions']['reply_screen_name']		=	$us_screen_name;
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
								$status_result['Twtstatus']['text']			=	$text;
								$status_result['Twtstatus']['source']			=	$source;
								$status_result['Twtstatus']['truncated']		=	$truncated;
								$status_result['Twtstatus']['in_reply_status']		=	$reply_status_id;
								$status_result['Twtstatus']['created_at']		=	strtotime($created_at);
								$status_result['Twtstatus']['etime']			=	$time;
								$status_result['Twtstatus']['in_reply_user']		=	$reply_user_id;
								$status_result['Twtstatus']['favorited']		=	$favorited;
								$status_result['Twtstatus']['in_reply_screen_name']	=	$reply_screen_name;
								$status_result['Twtstatus']['twt_user_id']		=	$us_id;
								$status_result['Twtstatus']['twt_name']			=	$us_name;
								$status_result['Twtstatus']['twt_screen_name']		=	$us_screen_name;
								$this->Twtstatus->create();
								$this->Twtstatus->save($status_result);
								
							}
							
						}
					}
				} //if($statuses_tweets)
						
				 
			} 

		} // if($result)
			echo "Total execution time : ".(microtime(true)-$this->program_start_time)." seconds<br />";
			exit ;
	} // function index()
}
?>
