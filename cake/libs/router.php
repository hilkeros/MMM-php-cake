<?php
/* SVN FILE: $Id: router.php 6305 2008-01-02 02:33:56Z phpnut $ */
/**
 * Parses the request URL into controller, action, and parameters.
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
 * @subpackage		cake.cake.libs
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 6305 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-01 21:33:56 -0500 (Tue, 01 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Included libraries.
 *
 */
	if (!class_exists('Object')) {
		 uses ('object');
	}
/**
 * Parses the request URL into controller, action, and parameters.
 *
 * @package		cake
 * @subpackage	cake.cake.libs
 */
class Router extends Object {
/**
 * Array of routes
 *
 * @var array
 * @access public
 */
	 var $routes = array();
/**
 * CAKE_ADMIN route
 *
 * @var array
 * @access private
 */
	 var $__admin = null;
/**
 * Constructor
 *
 * @access public
 */
	function __construct() {
		if (defined('CAKE_ADMIN')) {
			$admin = CAKE_ADMIN;
			if (!empty($admin)) {
				$this->__admin = array('/:' . $admin . '/:controller/:action/* (default)',
										'/^(?:\/(?:(' . $admin . ')(?:\\/([a-zA-Z0-9_\\-\\.\\;\\:]+)(?:\\/([a-zA-Z0-9_\\-\\.\\;\\:]+)(?:[\\/\\?](.*))?)?)?))[\/]*$/',
										array($admin, 'controller', 'action'), array());
			}
		}
	}
/**
 * Returns this object's routes array. Returns false if there are no routes available.
 *
 * @param string $route	An empty string, or a route string "/"
 * @param array $default NULL or an array describing the default route
 * @return array Array of routes
 */
	function connect($route, $default = null) {
		$parsed = $names = array();

		if (defined('CAKE_ADMIN') && $default == null) {
			if ($route == CAKE_ADMIN) {
				$this->routes[] = $this->__admin;
				$this->__admin = null;
			}
		}
		$r = null;
		if (($route == '') || ($route == '/')) {
			$regexp='/^[\/]*$/';
			$this->routes[] = array($route, $regexp, array(), $default);
		} else {
			$elements = array();

			foreach (explode('/', $route)as $element) {
				if (trim($element))
				$elements[] = $element;
			}

			if (!count($elements)) {
				return false;
			}

			foreach ($elements as $element) {

				if (preg_match('/^:(.+)$/', $element, $r)) {
					$parsed[]='(?:\/([^\/]+))?';
					$names[] =$r[1];
				} elseif (preg_match('/^\*$/', $element, $r)) {
					$parsed[] = '(?:\/(.*))?';
				} else {
					$parsed[] = '/' . $element;
				}
			}

			$regexp='#^' . join('', $parsed) . '[\/]*$#';
			$this->routes[] = array($route, $regexp, $names, $default);
		}
		return $this->routes;
	}
/**
 * Parses given URL and returns an array of controllers, action and parameters
 * taken from that URL.
 *
 * @param string $url URL to be parsed
 * @return array
 * @access public
 */
	function parse($url) {
		if (ini_get('magic_quotes_gpc') == 1) {
			$url = stripslashes_deep($url);
		}

		if ($url && ('/' != $url[0])) {
			if (!defined('SERVER_IIS')) {
				$url = '/' . $url;
			}
		}
		$out = array('pass'=>array());
		$r = null;
		$default_route = array('/:controller/:action/* (default)',
								'/^(?:\/(?:([a-zA-Z0-9_\\-\\.\\;\\:]+)(?:\\/([a-zA-Z0-9_\\-\\.\\;\\:]+)(?:[\\/\\?](.*))?)?))[\\/]*$/',
								array('controller', 'action'), array());

		if (defined('CAKE_ADMIN') && $this->__admin != null) {
			$this->routes[]=$this->__admin;
			$this->__admin =null;
		}
		$this->connect('/bare/:controller/:action/*', array('bare' => '1'));
		$this->connect('/ajax/:controller/:action/*', array('bare' => '1'));

		if (defined('WEBSERVICES') && WEBSERVICES == 'on') {
			$this->connect('/rest/:controller/:action/*', array('webservices' => 'Rest'));
			$this->connect('/rss/:controller/:action/*', array('webservices' => 'Rss'));
			$this->connect('/soap/:controller/:action/*', array('webservices' => 'Soap'));
			$this->connect('/xml/:controller/:action/*', array('webservices' => 'Xml'));
			$this->connect('/xmlrpc/:controller/:action/*', array('webservices' => 'XmlRpc'));
		}
		$this->routes[] = $default_route;

		if (strpos($url, '?') !== false) {
			$url = substr($url, 0, strpos($url, '?'));
		}

		foreach ($this->routes as $route) {
			list($route, $regexp, $names, $defaults) = $route;

			if (preg_match($regexp, $url, $r)) {
				// remove the first element, which is the url
				array_shift ($r);
				// hack, pre-fill the default route names
				foreach ($names as $name) {
					$out[$name] = null;
				}
				$ii=0;

				if (is_array($defaults)) {
					foreach ($defaults as $name => $value) {
						if (preg_match('#[a-zA-Z_\-]#i', $name)) {
							$out[$name] =  $this->stripEscape($value);
						} else {
							$out['pass'][] =  $this->stripEscape($value);
						}
					}
				}

				foreach ($r as $found) {
					// if $found is a named url element (i.e. ':action')
					if (isset($names[$ii])) {
						$out[$names[$ii]] = $found;
					} else {
						// unnamed elements go in as 'pass'
						$found = explode('/', $found);
						$pass = array();
						foreach ($found as $key => $value) {
							if ($value == "0") {
								$pass[$key] =  $this->stripEscape($value);
							} elseif ($value) {
								$pass[$key] =  $this->stripEscape($value);
							}
						}
						$out['pass'] = am($out['pass'], $pass);
					}
					$ii++;
				}
				break;
			}
		}
		return $out;
	}
	function stripEscape($param) {
		if (!is_array($param) || empty($param)) {
			if (is_bool($param)) {
				return $param;
			}

			$return = preg_replace('/^[\\t ]*(?:-!)+/', '', $param);
			return $return;
		}
		foreach ($param as $key => $value) {
			if (is_string($value)) {
				$return[$key] = preg_replace('/^[\\t ]*(?:-!)+/', '', $value);
			} else {
				foreach ($value as $array => $string) {
					$return[$key][$array] = $this->stripEscape($string);
				}
			}
		}
		return $return;
	}
}
?>
