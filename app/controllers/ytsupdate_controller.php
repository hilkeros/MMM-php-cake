<?php
vendor('Zend/Loader');
class YtsupdateController extends AppController {
	var $user;
	var $record;
	var $id;
	var $time= NULL;
	var $name = 'Yts';
	var $uses = array('Yt','Ytstat','Ytcommstat');
	var $program_start_time ;
	var $helpers = array('Html', 'Error');
	var $developerKey , $applicationId , $clientId , $username ;
	/**
	 * Name: beforeFilter
	 * Desc: Performs necessary steps and function calls prior to executing
	 *       any view function calls.
	 */
	function beforeFilter() {
		$this->program_start_time= microtime(true);
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

		// Retrieve the user's friends and pass them to the view.
		$result= $this->Yt->findAll();
		if($result)
		{
			foreach($result as $key => $ytval)
			{
				
				$this->time = time() ;
				$executetime = $this->time ;
				$tdate=	date('d-m-Y',$ytval['Yt']['etime']);
				$fdate = date('d-m-Y',$executetime);
				if($tdate!=$fdate)
				{
	
				$this->Session->write('sessionToken',$ytval['Yt']['sessionToken']);
				$this->username = $ytval['Yt']['user_id'];
				$this->id = $ytval['Yt']['yt_id'];
				$mmm_id = $ytval['Yt']['mmm_id'] ;	

				
				$this->getAuthSubRequestUrl();
					
					
					$httpClient=$this->getAuthSubHttpClient($ytval['Yt']['mmm_id'],$this->username);
					
					if($httpClient)
					{
				
						$yt = new Zend_Gdata_YouTube($httpClient, $this->applicationId, $this->clientId, $this->developerKey);
						
						try
						{
						
							/* Getting User Profile Data */
							$userProfile	=   $this->getAndPrintUserProfile($this->username,$yt,$mmm_id);
							echo "getAndPrintUserProfile <br>";
							
							$this->record['Yt']['yt_id'] = $this->id;
							$this->record['Yt']['etime'] = $this->time ;
							$this->Yt->save($this->record);
							
							echo "------ > Yt save record.";
							
							$this->record['Ytstat']['yt_id'] = $this->id;
							$this->record['Ytstat']['views'] = $userProfile['channelView'] ;
							$this->record['Ytstat']['subscriber'] = $userProfile['subscriberCount'] ;
	
							/* Getting Contacts / Friends List */
							$contactsFeed = $this->getAndPrintContactsFeed($this->username,$yt);
							$frds = count($contactsFeed)-1;
							$this->record['Ytstat']['friends'] =  $frds ;
							$this->record['Ytstat']['etime'] = $this->time;
							$this->Ytstat->create();
							$this->Ytstat->save($this->record);
							$this->record = NULL;
							
							/* Getting Video Comments */
							sleep(5);
							$vid=$this->ReturnVideoId($yt->getuserUploads($this->username));
							
							if($vid)
							{
								echo "------ > video section ";
									
								foreach($vid as $key=>$val)
								{
							
									$entry = $yt->getVideoEntry($key);
									
							
									$comments = $yt->getVideoCommentFeed($entry->videoId);
									$commentsFeed = $this->printCommentFeed($comments);
									$videosEntry = $this->printVideoEntry($entry);
									
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
									$record['Ytcommstat']['etime']= $this->time;
	
									$this->Ytcommstat->create();
									$this->Ytcommstat->save($record);
									$record = NULL ;
									//								} // if($record)
	
								} // foreach($vid as $key=>$val)
								
							} // if($vid)
						}
						catch (Exception $e)
						{
							continue;
						}
					} // if($httpClient)
				} // if($tdate!=$fdate)
			} // foreach($result as $key => $ytval)
		}  // if($result)
		echo "Total execution time : ".(microtime(true)-$this->program_start_time)." seconds<br />";
		exit ;
			
	} // 	function index()


	///////////////////////////////////////////////////////////////////////////////////////////////////////////

	function getAuthSubRequestUrl()
	{
		//   $next ='http://mmm.zeropoint.it/index.php/yts/index/';
		$next ="http://".$_SERVER['SERVER_NAME'].$this->base."/yts/index/";
		$scope = 'http://gdata.youtube.com';
		$secure = false;
		$session = true;
		return Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
	}



	function getAuthSubHttpClient($mmm_id,$username)
	{
		$httpClient = Zend_Gdata_AuthSub::getHttpClient($_SESSION['sessionToken']);
		return $httpClient;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////



	////////////////////////////////////  User Profile  /////////////////////////////////////////////////////////////////////////

	function getAndPrintUserProfile($userName,$yt,$mmm_id)
	{

		$userProfileEntry = $yt->getUserProfile($userName);
		$displayTitle = $userProfileEntry->title->text;
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

		$vEntry['title'] 	= $videoEntry->getVideoTitle() ;
		$vEntry['id'] 		= $videoEntry->getVideoId() ;
		$vEntry['duration'] 	= $videoEntry->getVideoDuration() ;
		$vEntry['viewCount'] 	= $videoEntry->getVideoViewCount() ;
		return $vEntry;
	
	}  // function printVideoEntry($videoEntry)



	function ReturnVideoId($videoFeed)
	{
	
		$ary = NULL;
		foreach ($videoFeed as $videoEntry) {
			
			/*
			if($videoEntry->isVideoPrivate())
			{
				continue;
			}
			*/
			
			try{
				$ary[$videoEntry->getVideoId()]=$videoEntry->getVideoTitle();
			} catch (Zend_Gdata_App_Exception $e) {
				echo $e->getMessage() . "\n";
				continue;
			}
		} // foreach ($videoFeed as $videoEntry) 
		
		if(!empty($ary))
		{
			return $ary;
		}
		else
		{
			return false;
		}
	} // function ReturnVideoId($videoFeed)


	//////////////////////////////////End Videos///////////////////////////////////////////////////////////////////


} // class YtsController extends AppController {


?>
