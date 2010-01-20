<?php
vendor('myspace/myspace.class');
class MssupdateController extends AppController {

	var $name = 'Mssupdate';
	var $time = NULL;

	var $uses = array('Mss','Mssstat','Msscomm','Msslogin');
	var $helpers = array('Html', 'Error',);

	/* Name: index
	 * Desc: Display the friends index page.
	 */

	function index()
	{
		$results = $this->Msslogin->findAll();

		foreach($results as $key => $value)
		{
			$this->time = time();
			$profileid = $value['Msslogin']['user_id'];
			$mss_id = $value['Msslogin']['mss_id'];
			$executetime =   $this->time;
			$tdate=	date('d-m-Y',$value['Msslogin']['etime']);
			$fdate = date('d-m-Y',$executetime);
				if($tdate!=$fdate)
				{	
				$result =$this->updateStat($profileid , $mss_id , $executetime);
				}
				
		} // foreach($results as $key => $value)
		exit;
	}


	// Update Daily MySpace Statistics

	function updateStat($pid , $id , $executetime)
	{

		$url="http://www.myspace.com/".$pid;

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
			@$views = str_replace("&nbsp;","",trim($pfview[1]));						$data['Mss']['status']=1;
			@$lastlogin = date("Y-m-d",strtotime(str_replace("&nbsp;","",(string)trim($lastlg[1]))));
			if(!empty($view))
			{

			// Getting Player Statistics
			$play = $myspace->get_player();
			$artid = $play['artid'];
			$plid = $play['plid'];
			$profid = $play['profid'];

			
				// Update Mss_login execution time
				$record['Msslogin']['mss_id']= $id;
				$record['Msslogin']['etime']= $executetime ;
				$this->Msslogin->save($record);



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
			
								// Updating daily statistics
			
								$this->data['Mss']['mss_id'] = $id ;
								$this->data['Mss']['friends'] = $frds ;
								$this->data['Mss']['views'] = $views ;
								$this->data['Mss']['lastlogin'] = $lastlogin ;
								$this->data['Mss']['plays'] = $tplays ;
								$this->data['Mss']['todayplays'] = $ttplays ;
								$this->data['Mss']['downloads'] = $downloads ;
								$this->data['Mss']['tdownloads'] = $tdownloads ;
								$this->data['Mss']['comments'] = $no_comments ;
								$this->data['Mss']['etime'] = $executetime ;
			
								$this->Mss->create();
								$this->Mss->save($this->data);
									
			
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
										$tplay=0;
										
										/*
										$att=$status->getAttributeNode('downloads');
										$download= $att->value;
										*/
										$download=0;
										
										/*
										$att=$status->getAttributeNode('downloadsToday');
										$tdownload= $att->value;
										*/
										$tdownload= 0 ;
									} // foreach($song as $skey => $sval)
									// Updating Daily Music Player Stats
			
									$result['Mssstat']['mss_id'] = $id ;
									$result['Mssstat']['title'] = $title ;
									$result['Mssstat']['plays'] = $play ;
									$result['Mssstat']['todayplays'] = $tplay ;
									$result['Mssstat']['downloads'] = $download ;
									$result['Mssstat']['tdownloads'] = $tdownload ;
									$result['Mssstat']['etime'] = $executetime ;
									$this->Mssstat->create();
									$this->Mssstat->save($result);
										
			
								} // foreach( $note as $key => $value )
			
							} // 	if (($file=@fopen($url, "r")))
				} // if($artid and $plid and $profid)
				else
				{
						
									$data = NULL;
					
									$result['Mss']['mss_id']=$id;
									$result['Mss']['status']=1;
									$result['Mss']['friends']=$frds;
									$result['Mss']['views']=$views;
									$result['Mss']['lastlogin']=$lastlogin;
									$result['Mss']['plays']=0;
									$result['Mss']['todayplays']=0;
									$result['Mss']['downloads']=0;
									$result['Mss']['tdownloads']=0;
									$result['Mss']['comments']=$no_comments;
									$result['Mss']['etime']= $this->time;
				
									$this->Mss->save($data);
								
				} //if($artid and $plid and $profid)


				$name = NULL;
				$cdate = NULL;
				$comments = NULL;

				$qry = "select max(cdate) from mss_comments where mss_id=$id";
				$result = mysql_query($qry);
				$row = mysql_fetch_row($result);
					
				// Comments contents
				foreach ($text as $key => $val)
				{
					$flg=1;
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
									
								if($executetime > $cdate )
								{
									$flg=0;
									break;
								}

							}
						}
						else
						{
							if($cval)
							{ $comments.=$cval; }

						}
					} // foreach ($val as $key1 => $cval)
					if($flg and !empty($name) and !empty($comments))
					{
						$comm['Msscomm']['mss_id'] = $id ;
						$comm['Msscomm']['name'] = $name ;
						$comm['Msscomm']['cdate'] = $cdate ;
						$comm['Msscomm']['comments'] = $comments ;
						$comm['Msscomm']['etime'] = $executetime;
						$this->Msscomm->create();
						$this->Msscomm->save($comm);
						/*					$qry = "insert into mss_comments (mss_id , name , cdate , comments)
						 values($id , '$name','$cdate','$comments')";

						 echo $qry."<br>";
						 $result = mysql_query($qry);
						 */
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
		
	} // function updateStat()*/

} // class LfmsController extends AppController {

?>
