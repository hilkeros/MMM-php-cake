<?php
class PostsController extends AppController {
var $name = 'Posts';


	function index() {
	 $this->set('posts', $this->Post->findall(''));
	//$this->Post->search();
	}

	function view($id) {
	$this->Post->id = $id;
	$this->set('posts', $this->Post->read());
	}

	function add() {
		if (!empty($this->data)) 
		{
			
			if ($this->Post->save($this->data)) 
		        {
			$this->flash('Your post has been saved.', '/posts');
			}
			 
			
		}
	}
}
?>

