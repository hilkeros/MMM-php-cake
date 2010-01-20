<?php
vendor('api/lastfmapi');
class LfmsupdateController extends AppController {

	var $name = 'Lfms';
	var $uses = array('Lfm','Lfmalbum','Lfmtrack','Lfmlistener');
	var $helpers = array('Html', 'Error');
	var $etime = NULL;

	/* Name: index
	 * Desc: update last.fm User profile
	 update lfm_music , lfm_listeners , lfm_top_album , lfm_top_tracks tables
	 */

	function index()
	{


		$program_start_time = microtime(true);
		$this->etime = time();

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

		$result = $this->Lfm->findAll();
		if($result)
		{
			foreach($result as $key => $lfmdata)
			{
				$ptime = $lfmdata['Lfm']['executetime'];

		  if(date('Ymd',$ptime)!=date('Ymd'))
		  {
		  	if($lfmdata['Lfm']['music_group'])
		  	{

		  		$methodVars = array(
				'artist' => $lfmdata['Lfm']['music_group']
		  		); // if($lfmdata['Lfm']['music_group'])


		  		if ( $artist = $artistClass->getInfo($methodVars) )
		  		{
		  			// Update lfm_music table
		  			$record['Lfm']['lfm_m_id'] =  $lfmdata['Lfm']['lfm_m_id'];
		  			$record['Lfm']['url'] =  $artist['url'];
		  			$record['Lfm']['executetime'] =  $this->etime;
		  			$this->Lfm->save($record);
		  			$record = NULL;

		  			// Insert latest listeners & time into lfmlisteners table
		  			$record['Lfmlistener']['lfm_m_id'] =  $lfmdata['Lfm']['lfm_m_id'];
		  			$record['Lfmlistener']['listeners'] = $artist['stats']['listeners'];
		  			$record['Lfmlistener']['etime'] = $this->etime;
		  			$this->Lfmlistener->create();
		  			$this->Lfmlistener->save($record);
		  			$record = NULL;
		  			//} // if($record)

		  		} // 	if ( $artist = $artistClass->getInfo($methodVars) )

		  		if ( $albums = $artistClass->getTopAlbums($methodVars) ) {

		  			foreach($albums as $key => $val)
		  			{
					 	
						$methodAlbumVars = array(
									'artist' => $lfmdata['Lfm']['music_group'] ,
									'album' => $val['name'],
									'mbid' => $val['mbid']
								   );
						
						if($topAlbum = $albumClass->getinfo($methodAlbumVars))
						{
							// Insert values into lfm_top_album table
							$record['Lfmalbum']['lfm_m_id']=$lfmdata['Lfm']['lfm_m_id'];
							$record['Lfmalbum']['rank']=$val['rank'];
							$record['Lfmalbum']['name']=$val['name'];
							$record['Lfmalbum']['playcount']=$topAlbum['playcount'];
							$record['Lfmalbum']['etime']=$this->etime;
							$this->Lfmalbum->create();
							$this->Lfmalbum->save($record);
							$record = NULL;
						}
		  			} // foreach($albums as $key => $val)
		  		} // 			if ( $albums = $artistClass->getTopAlbums($methodVars) ) {

		  		if ( $tracks = $artistClass->getTopTracks($methodVars) ) {

		  			foreach($tracks as $key => $val)
		  			{
						 $methodTrackVars = array(
									'artist' => $lfmdata['Lfm']['music_group'] ,
									'track' => $val['name']
								   );
						
						if($toptrack = $trackClass->getinfo($methodTrackVars))
						{
						  // Insert values into lfm_top_tracks
						  $record['Lfmtrack']['lfm_m_id']=$lfmdata['Lfm']['lfm_m_id'];
						  $record['Lfmtrack']['name']=$val['name'];
						  $record['Lfmtrack']['rank']=$val['rank'];
						  $record['Lfmtrack']['playcount']=$toptrack['playcount'];
						  $record['Lfmtrack']['etime']=$this->etime;
						  $this->Lfmtrack->create();
						  $this->Lfmtrack->save($record);
						  $record = NULL;
						}

		  			} // foreach($tracks as $key => $val)
		  		} // if ( $tracks = $artistClass->getTopTracks($methodVars) ) {

		  			
		  	} // $lfmdata['Lfm']['music_group'
		  } // if(date('Ymd',$ptime)!=date('Ymd'))
			} // foreach($result as $key => $lfmdata)
		} // if($result)
		echo "Total execution time : ".(microtime(true)-$program_start_time)." seconds<br />";
		exit;

	} // 	function index()
}

?>
