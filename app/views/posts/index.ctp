<h1>Blog posts</h1>
<?php echo $html->link('Add Post','/posts/add')." --- | ----  "?>
<?php echo $html->link('View Post','/posts/view/1')?>

<table>
<tr>
<th>Id</th>
<th>Title</th>
<th>Created</th>
</tr>
<!-- Here is where we loop through our $posts array, printing out post info -->
<?php 

foreach ($posts as $post): ?>
<tr>
<td><?php echo $post['Post']['id']; ?></td>
<td>
<?php echo $html->link($post['Post']['title'],"/posts/view/".$post['Post']['id']); ?>
</td>
<td><?php echo $post['Post']['created']; ?></td>
</tr>
<?php endforeach; ?>
</table>
