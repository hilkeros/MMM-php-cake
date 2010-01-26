<h1>Add Post</h1>
<?php
	echo "<form name=posts method=post>";
	echo "User Name";		
	echo $html->input('Post/name');
	echo $html->tagErrorMsg('Post/name', 'Name is required.');
	echo "Password";	
	echo $html->password('Post/password','size=20');
	echo $html->tagErrorMsg('Post/password', 'Password is required.');
	echo "Email address";	
	echo $html->input('Post/title','size=50');
	echo $html->tagErrorMsg('Post/title', 'Invalid Email Address.');
	echo "Body";	
	echo $html->textarea('Post/body',array('value'=>'Text Area','row'=>'20','col'=>'4'));
	echo $html->tagErrorMsg('Post/body', 'Only Numaric character allowed');
	echo $html->submit('GO');
	echo "</form>";
?>
