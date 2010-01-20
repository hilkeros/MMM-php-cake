<?php
// Copyright 2007 Facebook Corp.  All Rights Reserved. 
// 
// Application: mmm
// File: 'index.php' 
//   This is a sample skeleton for your application. 
// 

require_once 'facebook.php';
include("connection.php");
@$tkn = $_REQUEST['auth_token'];
@$username = $_REQUEST['username'];

if($username)
{
	$qry="select username from facebook where username='$username'";
	$result = mysql_query($qry);
	if($result)
	{	
		$row=mysql_fetch_row($result);
		$qry="update facebook set token='$tkn' where username='$username')";
		$result = mysql_query($qry);
	}
	else
	{
		$qry="insert into facebook(username,token,status)values('$username','$tkn','1')";
		$result = mysql_query($qry);
	} // if($result)
	

$appapikey = 'f83446549e7fc01a7240acb7d6e8b938';
$appsecret = 'de4d3704ad7db145f66589e649498688';
$facebook = new Facebook($appapikey, $appsecret);

$user_id = $facebook->require_login();


	
// Greet the currently logged-in user!
// echo "<p>Hello, <fb:name uid=\"$user_id\" useyou=\"false\" />!</p>";

// Print out at most 25 of the logged-in user's friends,
// using the friends.get API method

//$facebook_users = $facebook->api_client->fql_query("SELECT first_name , last_name , name , sex FROM user WHERE uid=' . $user_id . '");
$user_details=$facebook->api_client->users_getInfo($user_id, array('last_name','first_name')); 
$data['first_name']=$user_details[0]['first_name']; 
$data['last_name']=$user_details[0]['last_name']; 

echo "<br<br><h1> Facebook Friends List </h1>";

echo "Himself: &nbsp;".$data['first_name']."&nbsp;".$data['last_name']."<br>";

//header("location: http://www.facebook.com/profile.php?id=".$user_id."");
//exit;

echo "<p>Friends: <br>";

$count=1;
$friends = $facebook->api_client->friends_get();
$friends = array_slice($friends, 0, 25);
foreach ($friends as $friend) {

$user_details=$facebook->api_client->users_getInfo($friend, array('last_name','first_name')); 
$data['first_name']=	$user_details[0]['first_name']; 
$data['last_name'] =	$user_details[0]['last_name']; 

echo "&nbsp;&nbsp;$count.&nbsp;".$data['first_name']."&nbsp;".$data['last_name']."<br>";
$count=$count+1;
//  echo "<br>$friend";
}

echo "</p>";

} //if($username)
/*
if ($fbuid) {
    try {
        if ($facebook->api_client->users_isAppAdded()) {
            // The user has added our app
        } else {
            // The user has not added our app
        }
   
    } catch (Exception $ex) {
        //this will clear cookies for your app and redirect them to a login prompt
        $facebook->set_user(null, null);
        $facebook->redirect($_SERVER['SCRIPT_URI']);
        exit;
    }
} else {
    // The user has never used our app
}

*/
?>
