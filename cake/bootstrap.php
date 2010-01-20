<?php
/* SVN FILE: $Id: bootstrap.php 6305 2008-01-02 02:33:56Z phpnut $ */
/**
 * Basic Cake functionality.
 *
 * Core functions for including other source files, loading models and so forth.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 6305 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-01 21:33:56 -0500 (Tue, 01 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Configuration, directory layout and standard libraries
 */
	if (!isset($bootstrap)) {
		require CORE_PATH . 'cake' . DS . 'basics.php';
		require APP_PATH . 'config' . DS . 'core.php';
		require CORE_PATH . 'cake' . DS . 'config' . DS . 'paths.php';
	}
	$TIME_START = getMicrotime();
	require LIBS . 'object.php';
	require LIBS . 'session.php';
	require LIBS . 'security.php';
	require LIBS . 'inflector.php';
	require LIBS . 'configure.php';
	$paths = Configure::getInstance();
/**
 * Enter description here...
 */
	if (empty($uri) && defined('BASE_URL')) {
		$uri = setUri();

		if ($uri === '/' || $uri === '/index.php' || $uri === '/'.APP_DIR.'/') {
			$_GET['url'] = '/';
			$url = '/';
		} else {
			if (strpos($uri, 'index.php') !== false) {
				$uri = r('?', '', $uri);
				$elements=explode('/index.php', $uri);
			} else {
				$elements = explode('/?', $uri);
			}

			if (!empty($elements[1])) {
				$_GET['url'] = $elements[1];
				$url = $elements[1];
			} else {
				$_GET['url'] = '/';
				$url = '/';
			}
		}
	} else {
		if (empty($_GET['url'])) {
			$url = null;
		} else {
			$url = $_GET['url'];
		}
	}

	if (strpos($url, 'ccss/') === 0) {
		include WWW_ROOT . DS . 'css.php';
		die();
	}

	Configure::write('debug', DEBUG);

	require CAKE . 'dispatcher.php';

	if (defined('CACHE_CHECK') && CACHE_CHECK === true) {
		if (empty($uri)) {
			$uri = setUri();
		}
		$filename=CACHE . 'views' . DS . convertSlash($uri) . '.php';

		if (file_exists($filename)) {
			uses(DS . 'controller' . DS . 'component', DS . 'view' . DS . 'view');
			$v = null;
			$view = new View($v);
			$view->renderCache($filename, $TIME_START);
		} elseif (file_exists(CACHE . 'views' . DS . convertSlash($uri) . '_index.php')) {
			uses(DS . 'controller' . DS . 'component', DS . 'view' . DS . 'view');
			$v = null;
			$view = new View($v);
			$view->renderCache(CACHE . 'views' . DS . convertSlash($uri) . '_index.php', $TIME_START);
		}
	}
?>
