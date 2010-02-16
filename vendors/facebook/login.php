<?php
include("connection.php");
$username=$_REQUEST['name'];
?>
<html>
<head>
<title>Music Motion Manager</title>
</head>
<body>
<br>
<h2> Music Motion Manager Facebook Login <h2>
<br>
<h3>	Click the link below to login facebook Application </h3>
<?php
	$qry="select token from facebook where username='$username'";
	$result = mysql_query($qry);
	if($row=mysql_fetch_row($result))
	{
	echo "<a href=http://yourtri.be/~babar/facebook/?auth_token=".$row[0].">Facebook Login </a>";
	// http://users.zeropoint.it/~babar/facebook/?auth_token=6a1dfe7aa38da97a2517d2b0b46df5d9
	}
	else
	{
	echo "<a href=http://www.facebook.com/login.php?api_key=44ff3356af58e933da2adb962bd431e0&v=1.0>Facebook Login </a>";
	}
?>

</body>
</html>
