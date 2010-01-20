<?php
class Post extends AppModel    {
var $name = 'Post';

var $validate = array(
        'name'  => VALID_NOT_EMPTY,
        'password' => VALID_NOT_EMPTY,
        'title'   => VALID_EMAIL,
	'id'   => VALID_NUMBER
   );



	
	function search()
		{

			$qry="select id , title from posts";
			$result = mysql_query($qry);
			while($row=mysql_fetch_row($result))
			{
				echo $row[0]."----".$row[1]."<br>";
			}
		}
}
?>

