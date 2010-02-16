<?php
// Copyright 2007 Facebook Corp.  All Rights Reserved. 
// 
// Application: mmm
// File: 'index.php' 
//   This is a sample skeleton for your application. 
// 

require_once 'facebook.php';

$appapikey = '44ff3356af58e933da2adb962bd431e0';
$appsecret = '991dd74f42c7c81244a3b673581de188';
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
$data['first_name']=$user_details[0]['first_name']; 
$data['last_name']=$user_details[0]['last_name']; 

echo "&nbsp;&nbsp;$count.&nbsp;".$data['first_name']."&nbsp;".$data['last_name']."<br>";
$count=$count+1;
//  echo "<br>$friend";
}

echo "</p>";


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
