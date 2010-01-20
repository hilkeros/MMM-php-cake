<?php
/* SVN FILE: $Id: validators.php 6305 2008-01-02 02:33:56Z phpnut $ */
/**
 * Tort Validators
 *
 * Used to validate data in Models.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *			1785 E. Sahara Avenue, Suite 490-204
 *			Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake.libs
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 6305 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-01 21:33:56 -0500 (Tue, 01 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Not empty.
 */
	define('VALID_NOT_EMPTY', '/.+/');
/**
 * Numbers [0-9] only.
 */
	define('VALID_NUMBER', '/^[-+]?\\b[0-9]*\\.?[0-9]+\\b$/');
/**
 * A valid email address.
 */
	define('VALID_EMAIL', '/\\A(?:^([a-z0-9][a-z0-9_\\-\\.\\+]*)@([a-z0-9][a-z0-9\\.\\-]{0,63}\\.(com|org|net|biz|info|name|net|pro|aero|coop|museum|[a-z]{2,4}))$)\\z/i');
/**
 * A valid year (1000-2999).
 */
	define('VALID_YEAR', '/^[12][0-9]{3}$/');
?>
