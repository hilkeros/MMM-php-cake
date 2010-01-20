<?php
class FacebookController extends AppController {
	var $facebook;

	/**
	 * Name: beforeFilter
	 * Desc: Performs necessary steps and function calls prior to executing
	 *       any view function calls.
	 */
	function beforeFilter() {
		
		$this->user = $this->facebook->require_login();
	}

	/**
	 * Name: index
	 * Desc: Display the friends index page.
	 */
	function index() {
		// Retrieve the user's friends and pass them to the view.

	$fields = array('name','pic_small','type','website','founder','company_overview','mission','product','location','fan_count','has_added_app');
	$fields = array('page_id','name','pic_small','type','company_overview','mission','fan_count','location','website');
	$pages = $facebook->api_client->pages_getInfo(null,$fields,$this->user,null);

		$this->set('user_id',$this->user);

		if($pages)
		{
			$this->set('pages',$pages);
		} // if($pages)
		
		$grp=$facebook->api_client->groups_get($this->user, $gids=null);

		if($grp)
		{
			$this->set('grp',$grp);
		} // if($grp)

			


	} // 	function index() {
}
?>
