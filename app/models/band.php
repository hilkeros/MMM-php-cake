<?php
class Band extends AppModel
{
	var $name = 'Band';
	var $useTable= 'mmm_band';
	var $primaryKey  = 'band_id';
	
	var $validate = array(
				'name' => VALID_NOT_EMPTY,
				
			); 
}
?>
