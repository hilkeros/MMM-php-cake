<?php


define('LIB_PATH', 		"../../source/");
define('CONFIG_PATH', 	"../../config/");
define('LOCAL', false);

//gets a base path to the project  the "3rd parent" directory
$path_extra = dirname(dirname(dirname(__FILE__)));

//gets the default include path(s)
$path = ini_get('include_path');

//lets add a few more default paths.
$path = CONFIG_PATH . PATH_SEPARATOR
		.	LIB_PATH . PATH_SEPARATOR
		.	$path_extra . PATH_SEPARATOR
		.	$path;

//sets the new path(s) for php
ini_set('include_path', $path);

//turns on all error reporting
error_reporting(E_ALL);

//loads the configuration file with you're consumer key and secret
// uses the ternary operation for readability
// http://en.wikipedia.org/wiki/Ternary_operation
// BOOL ? (eval if BOOL == true) : (eval if BOOL == false);
require_once LOCAL ? "config.MySpace.local.php" : "config.MySpace.php";

//loads the myspaceid api sdk
require_once "MySpaceID/myspace.php";


//the entry point for the applicaiton, its called at the end of the file to start it.
function main() {

	 $ms_key = CONSUMER_KEY;			//we get this from config.MySpace.php
	 $ms_secret = CONSUMER_SECRET;

	 ob_start();						//starts output buffering
	 session_start();

	if (@$_GET['f'] == 'start') {
  		// get a request token + secret from MySpace and redirect to the authorization page
  		//
  		  $ms = new MySpace($ms_key, $ms_secret);
		  $tok = $ms->getRequestToken("http://localhost/~babar/myspace/myspaceid-sdk/trunk/samples/myspaceid-oauth/index.php?f=callback");
/*
		  if (!isset($tok['oauth_token'])
		      || !is_string($tok['oauth_token'])
		      || !isset($tok['oauth_token_secret'])
		      || !is_string($tok['oauth_token_secret'])) {
		   echo "ERROR! MySpace::getRequestToken() returned an invalid response. Giving up.";
		   exit;
		  }
*/
		  $_SESSION['auth_state'] = "start";
		  $_SESSION['request_token'] = $token = $tok['oauth_token'];
		  $_SESSION['request_secret'] = $tok['oauth_token_secret'];
		  $_SESSION['callback_confirmed'] = $tok['oauth_callback_confirmed'];
		  
		  header("Location: ".$ms->getAuthorizeURL($token));
	} else if (@$_GET['f'] == 'callback') {
  		// the user has authorized us at MySpace, so now we can pick up our access token + secret
  		//
		  if (@$_SESSION['auth_state'] != "start") {
		   echo "Out of sequence.";
		   exit;
		  }
		  
		  $oauth_verifier = @$_GET['oauth_verifier'];
		  
		  if (!isset($oauth_verifier)){
		  	echo("ERROR! MySpace::getAccessToken() returned an invalid response. Giving up.");
		  	exit;
		  }
		  
//		  if ($_GET['oauth_token'] != $_SESSION['request_token']) {
//		   echo "Token mismatch.";
//		   exit;
//		  }

		  $ms = new MySpace($ms_key, $ms_secret, $_SESSION['request_token'], $_SESSION['request_secret'], true, $oauth_verifier);
		  
		  $tok = $ms->getAccessToken();


		  if (!is_string($tok->key) || !is_string($tok->secret)) {
		  	error_log("Bad token from MySpace::getAccessToken(): ".var_export($tok, TRUE));
			echo "ERROR! MySpace::getAccessToken() returned an invalid response. Giving up.";
			exit;
		  }

		  $_SESSION['access_token'] = $tok->key;
		  $_SESSION['access_secret'] = $tok->secret;

		  $_SESSION['auth_state'] = "done";
		  header("Location: ".$_SERVER['SCRIPT_NAME']);
	} else if (@$_SESSION['auth_state'] == 'done') {
	  	// we have our access token + secret, so now we can actually *use* the api
	  	//
		$ms = new MySpace($ms_key, $ms_secret, $_SESSION['access_token'], $_SESSION['access_secret']);

		// First get the user id.

		$userid = $ms->getCurrentUserId();

		// Use the userID (fetched in the previous step) to get user's profile, friends and other info
		$profile_data = $ms->getProfile($userid);
		displayProfileInfo($profile_data);

		$friends_data = $ms->getFriends($userid);
		displayFriendsInfo($friends_data);
		
		$albums = $ms->getAlbums($userid);
		$albumid = $albums->albums[0]->id;
		$albumInfo = $ms->getAlbumInfo($userid, $albumid);
		$album = $ms->getAlbum($userid, $albumid);
		$firstPhoto = $ms->getAlbumPhoto($userid, $albumid, $album->photos[0]->id);
		displayAlbum($albumInfo, $firstPhoto->imageUri);
		
		$friendStatus = $ms->getFriendsStatus($userid);
		displayObject($friendStatus, '$friendStatus');
		
		$indicators = $ms->getIndicators($userid);
		displayObject($indicators, '$indicators');
		
		$statusHistory = $ms->getStatusHistory($userid);
		displayObject($statusHistory, '$statusHistory');
		
		//$ms->updateStatus($userid, 'Updating from php sdk');
		
		// Test put and get app data
		$ms->putAppData($userid, array('location' => 'usa', 'interests' => 'tennis, golf', 'age' => '21'));
		$appData = $ms->getAppData($userid, 'interests;location');
		displayObject($appData, '$appData for interests and location only');
		$appData = $ms->getAppData($userid);
		displayObject($appData, '$appData for all parameters');
		
		// Test clear app data
		$ms->clearAppData($userid, 'location;age');
		sleep(3);
		$appData = $ms->getAppData($userid);
		displayObject($appData, '$appData after clearing');
		
		// Test get friends' app data
		$appData = $ms->getUserFriendsAppData($userid);
		displayObject($appData, '$appData for friends');
		
		// Test Poco get user
		$pocoPerson = $ms->getPersonPoco('movies');
		displayObject($pocoPerson, '$pocoPerson');

		// Test Poco get friends
		$pocoFriends = $ms->getFriendsPoco();
		displayObject($pocoFriends, '$pocoPerson');
	} else {
		// not authenticated yet, so give a link to use to start authentication.
  		?><p><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>?f=start">Click here to authenticate with MySpace</a></p><?php
 	}
}

function displayObject($obj, $name) {
	print '<b>'.$name.' = </b>';
	print_r($obj);
	print '<br/>';
}

function displayAlbum($albumInfo, $firstPhotoUri) {
	$s = "album:";
	$s .= "ID: " . $albumInfo->id;
	$s .= ", Title: " . $albumInfo->title . "<br>";
	$s .= "<img src='" . $firstPhotoUri . "' height='70px'/>";
	echo $s;
}

function displayProfileInfo($profile_data)
{
	$profileInfo =  "<h3>Your MySpace Profile Information</h3>";

	$profileInfo.= "<img src='" . $profile_data->basicprofile->image . "'> <br>";
	$profileInfo.= "Profile URL: " . "<a href='" . $profile_data->basicprofile->webUri . "'>" . $profile_data->basicprofile->webUri . "</a><br>";
	$profileInfo.= "Name: " . $profile_data->basicprofile->name . "<br>";
	$profileInfo.= "Gender: " . $profile_data->gender . "<br>";
	$profileInfo.= "Age: " . $profile_data->age . "<br>";
	$profileInfo.= "Marital Status: " . $profile_data->maritalstatus . "<br>";
	$profileInfo.= "City: " . $profile_data->city . "<br>";
	$profileInfo.= "Postal Code: " . $profile_data->postalcode . "<br>";
	$profileInfo.= "Region: " . $profile_data->region . "<br>";
	$profileInfo.= "Country: " . $profile_data->country . "<br>";

	echo $profileInfo;
}

function displayFriendsInfo($friends_data)
{
	$friendsInfo =  "<h3>Your MySpace Friends</h3>";

	$friendsInfo.= "<div id='friendsgrid'> <ul>";
	foreach ($friends_data->Friends as $friend) {
		$friendsInfo.= "<li>";
		$friendsInfo.= "<div><a href='" . $friend->webUri . "'>" . $friend->name . "</a></div>";
		$friendsInfo.= "<div> <img src='" . $friend->image . "'></div>";
		$friendsInfo.= "</li>";
	}
	$friendsInfo.= "</ul></div>";

	echo $friendsInfo;
}

main();  //call the entry point function to start the app.

?>
