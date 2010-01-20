<?php
/* SVN FILE: $Id: request_handler.php 6305 2008-01-02 02:33:56Z phpnut $ */
/**
 * Request object for handling alternative HTTP requests
 *
 * Alternative HTTP requests can come from wireless units like mobile phones, palmtop computers, and the like.
 * These units have no use for Ajax requests, and this Component can tell how Cake should respond to the different
 * needs of a handheld computer and a desktop machine.
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
 * @subpackage		cake.cake.libs.controller.components
 * @since			CakePHP(tm) v 0.10.4.1076
 * @version			$Revision: 6305 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-01 21:33:56 -0500 (Tue, 01 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
if (!defined('REQUEST_MOBILE_UA')) {
	define('REQUEST_MOBILE_UA',
			'(AvantGo|BlackBerry|DoCoMo|NetFront|Nokia|PalmOS|PalmSource|portalmmm|Plucker|ReqwirelessWeb|SonyEricsson|Symbian|UP\.Browser|Windows CE|Xiino)');
}
/**
 * Request object for handling alternative HTTP requests
 *
 * @package		cake
 * @subpackage	cake.cake.libs.controller.components
 *
 */
class RequestHandlerComponent extends Object{
/**
 * Enter description here...
 *
 * @var object
 * @access public
 */
	var $controller = true;
/**
 * The layout that will be switched to for Ajax requests
 *
 * @var string
 * @access public
 * @see RequestHandler::setAjax()
 */
	var $ajaxLayout = 'ajax';
/**
 * Determines whether or not callbacks will be fired on this component
 *
 * @var boolean
 * @access public
 */
	var $disableStartup = false;
/**
 * Friendly content-type mappings used to set response types and determine
 * request types.  Can be modified with RequestHandler::setContent()
 *
 * @var array
 * @access private
 * @see RequestHandlerComponent::setContent
 */
	var $__requestContent = array(
		'js' => 'text/javascript',
		'css'	=> 'text/css',
		'html'	=> 'text/html',
		'form'	=> 'application/x-www-form-urlencoded',
		'file'	=> 'multipart/form-data',
		'xhtml'	=> array('application/xhtml+xml', 'application/xhtml', 'text/xhtml'),
		'xml' => array('application/xml', 'text/xml'),
		'rss' => 'application/rss+xml',
		'atom' => 'application/atom+xml'
	);
/**
 * Content-types accepted by the client.  If extension parsing is enabled in the
 * Router, and an extension is detected, the corresponding content-type will be
 * used as the overriding primary content-type accepted.
 *
 * @var array
 * @access private
 */
	var $__acceptTypes = array();
/**
 * Constructor.  Parses the accepted content types accepted by the client using
 * HTTP_ACCEPT
 *
 * @access public
 * @return void
 */
	function __construct() {
		$this->__acceptTypes = explode(',', env('HTTP_ACCEPT'));

		foreach ($this->__acceptTypes as $i => $type) {
			if (strpos($type, ';')) {
				$type = explode(';', $type);
				$this->__acceptTypes[$i] = $type[0];
			}
		}
		parent::__construct();
	}
/**
 * Startup
 *
 * @param object A reference to the controller
 * @return null
 * @access public
 */
	function startup(&$controller) {
		if ($this->disableStartup) {
			return;
		}
		$this->setAjax($controller);
	}
/**
 * Sets a controller's layout based on whether or not the current call is Ajax
 *
 * Add UTF-8 header for IE6 on XPsp2 bug if RequestHandlerComponent::isAjax()
 *
 * @param object The controller object
 * @return null
 * @access public
 */
	function setAjax(&$controller) {
		if ($this->isAjax()) {
			$controller->layout = $this->ajaxLayout;
			// Add UTF-8 header for IE6 on XPsp2 bug
			header ('Content-Type: text/html; charset=UTF-8');
		}
	}
/**
 * Returns true if the current call is from Ajax, false otherwise
 *
 * @return bool True if call is Ajax
 * @access public
 */
	function isAjax() {
		if (env('HTTP_X_REQUESTED_WITH') != null) {
			return env('HTTP_X_REQUESTED_WITH') == "XMLHttpRequest";
		} else {
			return false;
		}
	}
/**
 * Returns true if the current call accepts an XML response, false otherwise
 *
 * @return bool True if client accepts an XML response
 * @access public
 */
	function isXml() {
		return $this->accepts('xml');
	}
/**
 * Returns true if the current call accepts an RSS response, false otherwise
 *
 * @return bool True if client accepts an RSS response
 * @access public
 */
	function isRss() {
		return $this->accepts('rss');
	}
/**
 * Returns true if the current call accepts an RSS response, false otherwise
 *
 * @return bool True if client accepts an RSS response
 * @access public
 */
	function isAtom() {
		return $this->accepts('atom');
	}
/**
 * Returns true if the current call a POST request
 *
 * @return bool True if call is a POST
 * @access public
 */
	function isPost() {
		return (strtolower(env('REQUEST_METHOD')) == 'post');
	}
/**
 * Returns true if the current call a PUT request
 *
 * @return bool True if call is a PUT
 * @access public
 */
	function isPut() {
		return (strtolower(env('REQUEST_METHOD')) == 'put');
	}
/**
 * Returns true if the current call a GET request
 *
 * @return bool True if call is a GET
 * @access public
 */
	function isGet() {
		return (strtolower(env('REQUEST_METHOD')) == 'get');
	}
/**
 * Returns true if the current call a DELETE request
 *
 * @return bool True if call is a DELETE
 * @access public
 */
	function isDelete() {
		return (strtolower(env('REQUEST_METHOD')) == 'delete');
	}
/**
 * Gets Prototype version if call is Ajax, otherwise empty string.
 * The Prototype library sets a special "Prototype version" HTTP header.
 *
 * @return string Prototype version of component making Ajax call
 * @access public
 */
	function getAjaxVersion() {
		if (env('HTTP_X_PROTOTYPE_VERSION') != null) {
			return env('HTTP_X_PROTOTYPE_VERSION');
		}
		return false;
	}
/**
 * Adds/sets the Content-type(s) for the given name
 *
 * @param string $name The name of the Content-type, i.e. "html", "xml", "css"
 * @param mixed $type The Content-type or array of Content-types assigned to the name
 * @return void
 * @access public
 */
	function setContent($name, $type) {
		$this->__requestContent[$name] = $type;
	}
/**
 * Gets the server name from which this request was referred
 *
 * @return string Server address
 * @access public
 */
	function getReferrer() {
		if (env('HTTP_HOST') != null) {
			$sess_host = env('HTTP_HOST');
		}

		if (env('HTTP_X_FORWARDED_HOST') != null) {
			$sess_host = env('HTTP_X_FORWARDED_HOST');
		}
		return trim(preg_replace('/:.*/', '', $sess_host));
	}
/**
 * Gets remote client IP
 *
 * @return string Client IP address
 * @access public
 */
	function getClientIP() {
		if (env('HTTP_X_FORWARDED_FOR') != null) {
			$ipaddr = preg_replace('/,.*/', '', env('HTTP_X_FORWARDED_FOR'));
		} else {
			if (env('HTTP_CLIENT_IP') != null) {
				$ipaddr = env('HTTP_CLIENT_IP');
			} else {
				$ipaddr = env('REMOTE_ADDR');
			}
		}

		if (env('HTTP_CLIENTADDRESS') != null) {
			$tmpipaddr = env('HTTP_CLIENTADDRESS');

			if (!empty($tmpipaddr)) {
				$ipaddr = preg_replace('/,.*/', '', $tmpipaddr);
			}
		}
		return trim($ipaddr);
	}
/**
 * Returns true if user agent string matches a mobile web browser
 *
 * @return bool True if user agent is a mobile web browser
 * @access public
 */
	function isMobile() {
		return (preg_match('/' . REQUEST_MOBILE_UA . '/i', env('HTTP_USER_AGENT')) > 0);
	}
/**
 * Strips extra whitespace from output
 *
 * @param string $str
 * @return string
 * @access public
 */
	function stripWhitespace($str) {
		$r = preg_replace('/[\n\r\t]+/', '', $str);
		return preg_replace('/\s{2,}/', ' ', $r);
	}
/**
 * Strips image tags from output
 *
 * @param string $str
 * @return string
 * @access public
 */
	function stripImages($str) {
		$str = preg_replace('/(<a[^>]*>)(<img[^>]+alt=")([^"]*)("[^>]*>)(<\/a>)/i', '$1$3$5<br />', $str);
		$str = preg_replace('/(<img[^>]+alt=")([^"]*)("[^>]*>)/i', '$2<br />', $str);
		$str = preg_replace('/<img[^>]*>/i', '', $str);
		return $str;
	}
/**
 * Strips scripts and stylesheets from output
 *
 * @param string $str
 * @return string
 * @access public
 */
	function stripScripts($str) {
		return preg_replace('/(<link[^>]+rel="[^"]*stylesheet"[^>]*>|<img[^>]*>|style="[^"]*")|<script[^>]*>.*?<\/script>|<style[^>]*>.*?<\/style>|<!--.*?-->/i', '', $str);
	}
/**
 * Strips extra whitespace, images, scripts and stylesheets from output
 *
 * @param string $str
 * @return string
 * @access public
 */
	function stripAll($str) {
		$str = $this->stripWhitespace($str);
		$str = $this->stripImages($str);
		$str = $this->stripScripts($str);
		return $str;
	}
/**
 * Strips the specified tags from output
 *
 * @return string
 * @access public
 */
	function stripTags() {
		$params = params(func_get_args());
		$str = $params[0];

		for ($i = 1; $i < count($params); $i++) {
			$str = preg_replace('/<' . $params[$i] . '[^>]*>/i', '', $str);
			$str = preg_replace('/<\/' . $params[$i] . '[^>]*>/i', '', $str);
		}
		return $str;
	}

/**
 * Determines which content types the client accepts
 *
 * @param mixed $type Can be null (or no parameter), a string type name, or an
 *					array of types
 * @return mixed If null or no parameter is passed, returns an array of content
 *				types the client accepts.  If a string is passed, returns true
 *				if the client accepts it.  If an array is passed, returns true
 *				if the client accepts one or more elements in the array.
 * @access public
 */
	function accepts($type = null) {
		if ($type == null) {
			return $this->__acceptTypes;
		} elseif (is_array($type)) {
			foreach ($type as $t) {
				if ($this->accepts($t) == true) {
					return true;
				}
			}
			return false;
		} elseif (is_string($type)) {
			// If client only accepts */*, then assume default HTML browser
			if ($type == 'html' && $this->__acceptTypes === array('*/*')) {
				return true;
			}

			if (!in_array($type, array_keys($this->__requestContent))) {
				return false;
			}

			$content = $this->__requestContent[$type];

			if (is_array($content)) {
				foreach ($content as $c) {
					if (in_array($c, $this->__acceptTypes)) {
						return true;
					}
				}
			} else {
				if (in_array($content, $this->__acceptTypes)) {
					return true;
				}
			}
		}
	}
/**
 * Determines which content types the client prefers
 *
 * @param mixed $type
 * @returns mixed
 * @access public
 */
	function prefers($type = null) {
		if ($type == null) {
			return $this->accepts(null);
		}
	}
}
?>
