<?php
vendor('facebook/facebook'); 
class FbsupdateController extends AppController {
	var $user;
	var $lg_id;
	var $time;
	var $flg = true ;
	var $id = NULL;
	var $name = 'Fbs';
	var $uses = array('Fb','Fbpage','Fbgroup');
	var $helpers = array('Html', 'Error');
	var $facebook;
	var $__fbApiKey = '44ff3356af58e933da2adb962bd431e0';
	var $__fbSecret = '991dd74f42c7c81244a3b673581de188';

	/**
	 * Name: index
	 * Desc: get and insert facebook statistics
	 */


	function index() {
		$program_start_time = microtime(true);
		$GLOBALS['facebook_config']['debug'] = NULL;
		// Create a Facebook client API object.
		$this->facebook = new Facebook($this->__fbApiKey, $this->__fbSecret);
		
		$result= $this->Fb->findAll();
		if($result)
		{
			
			foreach ($result as $key => $fbval)
			{
				
				$this->time = time();
				$executetime =  $this->time ;
				$tdate=	date('d-m-Y',$fbval['Fb']['etime']);
				$fdate = date('d-m-Y',$executetime);
								
				if($tdate!=$fdate)
				{
					sleep(3);
					$this->facebook->set_user($fbval['Fb']['user_id'], $fbval['Fb']['session_key']);	
					if($this->facebook->api_client->users_isAppUser($fbval['Fb']['user_id'])==0)  // if not application user & revoke extended permission and delete application
					{
						continue;
					}
				
				$this->facebook->set_user($fbval['Fb']['user_id'], $fbval['Fb']['session_key']);
								
				$this->user = $fbval['Fb']['user_id'];
				$this->lg_id = $fbval['Fb']['login_id'];
				$this->flg = true;
				$this->id = $fbval['Fb']['login_id'];

			
				$uid= $this->user;
				
				$pages=$this->facebook->api_client->pages_getInfo(null, array('page_id','name','type','fan_count','website','page_url'), $uid, null);
				
				if(!empty($pages))
				{
					
					if (array_key_exists('error_code', $pages))  // 102 means session key expire or no longer exist
					{
						continue;	
					}
				}
				
				
				$fbresult['Fb']['login_id'] = $fbval['Fb']['login_id'];
				$fbresult['Fb']['etime']=$this->time;
				$this->Fb->save($fbresult);
				
				/*
				$query = "SELECT page_id , name , type , fan_count , website FROM page WHERE page_id IN (SELECT page_id FROM page_fan WHERE uid = $uid)";
				
				$pages = $this->facebook->api_client->fql_query($query);
				*/

				if(!empty($pages))
				{
					foreach($pages as $key => $val)
					{
						$isAdmin=$this->facebook->api_client->pages_isAdmin($val['page_id']);
						if($isAdmin)
						{
						$record['Fbpage']['login_id']=$this->lg_id ;
						$record['Fbpage']['name']=$val['name'];
						$record['Fbpage']['type']=$val['type'];
						$record['Fbpage']['fan_count']=$val['fan_count'] ;
						$record['Fbpage']['website']=$val['website'];
						$record['Fbpage']['page_url']=$val['page_url'];
						$record['Fbpage']['p_id']=$val['page_id'];
						$record['Fbpage']['is_admin']=$isAdmin;
						$record['Fbpage']['etime']=$this->time;
						$this->Fbpage->create();
						$this->Fbpage->save($record);
						$record = NULL ;
						}
					} //foreach(pages as $key => $val)
				} // 		if($pages)

				//$grp=$this->facebook->api_client->groups_get($this->user, $gids=null);
				$query = "SELECT gid , name , group_type , group_subtype , creator , update_time , website FROM group WHERE gid IN (SELECT gid FROM group_member WHERE uid = $uid)";
				$grp = $this->facebook->api_client->fql_query($query);
				if(!empty($grp))
				{

					foreach ($grp as $key => $val)
					{
						$isCreator = 'No';
						if($val['creator']==$this->user)
						{
								$isCreator = 'Yes';
								$cmember=NULL;
									$grp_id = $val['gid'] ;
									$member=$this->facebook->api_client->groups_getMembers($grp_id);
									$cmember = count($member['members']);

									if(empty($cmember))
									{ $cmember=0; }

						$record['Fbgroup']['login_id']=$this->lg_id ;
						$record['Fbgroup']['gid']=$grp_id;
						$record['Fbgroup']['name']=$val['name'];
						$record['Fbgroup']['type']=$val['group_type'];
						$record['Fbgroup']['subtype']=$val['group_subtype'] ;
						$record['Fbgroup']['update_time']=$val['update_time'] ;
						$record['Fbgroup']['website']=$val['website'];
						$record['Fbgroup']['member']=$cmember;
						$record['Fbgroup']['isCreator']=$isCreator ;
						$record['Fbgroup']['etime']=$this->time; ;

						$this->Fbgroup->create();
						$this->Fbgroup->save($record);
						$record = NULL;
						}
					} // foreach ($grp as $key => $val)

				} // If ($grp)
			    } // if($tdate!=$fdate)
			
			} //  foreach ($result as $key => $fbval)

		} // 		if($result)
		
		echo "Total execution time : ".(microtime(true)-$program_start_time)." seconds<br />";
		exit;
	} // 	function index() {

	function groupmember()
	{
		$grp_id=$this->params['id'];
		$member=$this->facebook->api_client->groups_getMembers($grp_id);
		return $member;
	}


}
?>
