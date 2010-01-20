<?php
/* SVN FILE: $Id: javascript.php 6305 2008-01-02 02:33:56Z phpnut $ */
/**
 * Javascript Helper class file.
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
 * @subpackage		cake.cake.libs.view.helpers
 * @since			CakePHP(tm) v 0.10.0.1076
 * @version			$Revision: 6305 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-01 21:33:56 -0500 (Tue, 01 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Javascript Helper class for easy use of JavaScript.
 *
 * JavascriptHelper encloses all methods needed while working with JavaScript.
 *
 * @package		cake
 * @subpackage	cake.cake.libs.view.helpers
 */
class JavascriptHelper extends Helper{
	var $_cachedEvents = array();
	var $_cacheEvents = false;
	var $_cacheToFile = false;
	var $_cacheAll = false;
	var $_rules = array();
/**
 * Returns a JavaScript script tag.
 *
 * @param  string $script The JavaScript to be wrapped in SCRIPT tags.
 * @param  boolean $allowCache Allows the script to be cached if non-event caching is active
 * @return string The full SCRIPT element, with the JavaScript inside it.
 * @access public
 */
	function codeBlock($script, $allowCache = true) {
		if ($this->_cacheEvents && $this->_cacheAll && $allowCache) {
			$this->_cachedEvents[] = $script;
		} else {
			return sprintf($this->tags['javascriptblock'], $script);
		}
	}
/**
 * Returns a JavaScript include tag (SCRIPT element)
 *
 * @param  string $url URL to JavaScript file.
 * @return string
 * @access public
 */
	function link($url) {
		if (strpos($url, '.js') === false) {
			$url .= ".js";
		}
		return sprintf($this->tags['javascriptlink'], $this->webroot . $this->themeWeb . JS_URL . $url);
	}
/**
 * Returns a JavaScript include tag for an externally-hosted script
 *
 * @param  string $url URL to JavaScript file.
 * @return string
 * @access public
 */
	function linkOut($url) {
		if (strpos($url, '.js') === false && strpos($url, '?') === false) {
			$url .= '.js';
		}
		return sprintf($this->tags['javascriptlink'], $url);
	}
/**
 * Escape carriage returns and single and double quotes for JavaScript segments.
 *
 * @param string $script string that might have javascript elements
 * @return string escaped string
 * @access public
 */
	function escapeScript($script) {
		$script = r(array("\r\n", "\n", "\r"), '\n', $script);
		$script = r(array('"', "'"), array('\"', "\\'"), $script);
		return $script;
	}
/**
 * Escape a string to be JavaScript friendly.
 *
 * List of escaped ellements:
 *	+ "\r\n" => '\n'
 *	+ "\r" => '\n'
 *	+ "\n" => '\n'
 *	+ '"' => '\"'
 *	+ "'" => "\\'"
 *
 * @param  string $script String that needs to get escaped.
 * @return string Escaped string.
 * @access public
 */
	function escapeString($string) {
		$escape = array("\r\n" => '\n', "\r" => '\n', "\n" => '\n', '"' => '\"', "'" => "\\'");
		return r(array_keys($escape), array_values($escape), $string);
	}
/**
 * Attach an event to an element. Used with the Prototype library.
 *
 * @param string $object Object to be observed
 * @param string $event event to observe
 * @param string $observer function to call
 * @param boolean $useCapture default true
 * @return boolean true on success
 * @access public
 */
	function event($object, $event, $observer = null, $useCapture = false) {

		if ($useCapture == true) {
			$useCapture = "true";
		} else {
			$useCapture = "false";
		}

		if ($object == 'window' || strpos($object, '$(') !== false || strpos($object, '"') !== false || strpos($object, '\'') !== false) {
			$b = "Event.observe($object, '$event', function(event) { $observer }, $useCapture);";
		} else {
			$chars = array('#', ' ', ', ', '.', ':');
			$found = false;
			foreach ($chars as $char) {
				if (strpos($object, $char) !== false) {
					$found = true;
					break;
				}
			}
			if ($found) {
				$this->_rules[$object] = $event;
			} else {
				$b = "Event.observe(\$('$object'), '$event', function(event) { $observer }, $useCapture);";
			}
		}

		if (isset($b) && !empty($b)) {
			if ($this->_cacheEvents === true) {
				$this->_cachedEvents[] = $b;
				return;
			} else {
				return $this->codeBlock($b);
			}
		}
	}
/**
 * Cache JavaScript events created with event()
 *
 * @param boolean $file If true, code will be written to a file
 * @param boolean $all If true, all code written with JavascriptHelper will be sent to a file
 * @return void
 * @access public
 */
	function cacheEvents($file = false, $all = false) {
		$this->_cacheEvents = true;
		$this->_cacheToFile = $file;
		$this->_cacheAll = $all;
	}
/**
 * Write cached JavaScript events
 *
 * @return string
 * @access public
 */
	function writeEvents() {

		$rules = array();
		if (!empty($this->_rules)) {
			foreach ($this->_rules as $sel => $event) {
				$rules[] = "\t'{$sel}': function(element, event) {\n\t\t{$event}\n\t}";
			}
			$this->_cacheEvents = true;
		}

		if ($this->_cacheEvents) {

			$this->_cacheEvents = false;
			$events = $this->_cachedEvents;
			$data = implode("\n", $events);
			$this->_cachedEvents = array();

			if (!empty($rules)) {
				$data .= "\n\nvar SelectorRules = {\n" . implode(",\n\n", $rules) . "\n}\n";
				$data .= "\nEventSelectors.start(SelectorRules);\n";
			}

			if (!empty($events) || !empty($rules)) {
				if ($this->_cacheToFile) {
					$filename = md5($data);
					if (!file_exists(JS . $filename . '.js')) {
						cache(r(WWW_ROOT, '', JS) . $filename . '.js', $data, '+999 days', 'public');
					}
					return $this->link($filename);
				} else {
					return $this->codeBlock("\n" . $data . "\n");
				}
			}
		}
	}
/**
 * Includes the Prototype Javascript library (and anything else) inside a single script tag.
 *
 * Note: The recommended approach is to copy the contents of
 * javascripts into your application's
 * public/javascripts/ directory, and use @see javascriptIncludeTag() to
 * create remote script links.
 *
 * @param string $script name of script to include
 * @return string script with all javascript in/javascripts folder
 * @access public
 */
	function includeScript($script = "") {
		if ($script == "") {
			$dh = opendir(JS);
			while (false !== ($filename = readdir($dh))) {
                $files[] = $filename;
            }
            sort($files);
			$javascript = '';
			foreach ($files as $file) {
				if (substr($file, -3) == '.js') {
					$javascript .= file_get_contents(JS . "{$file}") . "\n\n";
				}
			}
		} else {
			$javascript = file_get_contents(JS . "$script.js") . "\n\n";
		}
		return $this->codeBlock("\n\n" . $javascript);
	}
/**
 * Generates a JavaScript object in JavaScript Object Notation (JSON)
 * from an array
 *
 * @param array $data Data to be converted
 * @param boolean $block Wraps return value in a <script/> block if true
 * @param string $prefix Prepends the string to the returned data
 * @param string $postfix Appends the string to the returned data
 * @param array $stringKeys A list of array keys to be treated as a string
 * @param boolean $quoteKeys If false, treats $stringKey as a list of keys *not* to be quoted
 * @param string $q The type of quote to use
 * @return string A JSON code block
 * @access public
 */
	function object($data = array(), $block = false, $prefix = '', $postfix = '', $stringKeys = array(), $quoteKeys = true, $q = "\"") {
		if (is_object($data)) {
			$data = get_object_vars($data);
		}

		$out = array();
		$key = array();

		if (is_array($data)) {
			$keys = array_keys($data);
		}

		$numeric = true;
		if (!empty($keys)) {
			$numeric = (array_values($keys) === array_keys(array_values($keys)));
		}

		foreach ($data as $key => $val) {
			if (is_array($val) || is_object($val)) {
				$val = $this->object($val, false, '', '', $stringKeys, $quoteKeys, $q);
			} else {
				if ((!count($stringKeys) && !is_numeric($val) && !is_bool($val)) || ($quoteKeys && in_array($key, $stringKeys, true)) || (!$quoteKeys && !in_array($key, $stringKeys, true))) {
					$val = $q . $this->escapeString($val) . $q;
				}
				if ($val === null) {
					$val = 'null';
				}
				if (is_bool($val)) {
					$val = ife($val, 'true', 'false');
				}
			}

			if (!$numeric) {
				$val = $q . $key . $q . ':' . $val;
			}

			$out[] = $val;
		}

		if (!$numeric) {
			$rt = '{' . join(', ', $out) . '}';
		} else {
			$rt = '[' . join(', ', $out) . ']';
		}
		$rt = $prefix . $rt . $postfix;

		if ($block) {
			$rt = $this->codeBlock($rt);
		}

		return $rt;
	}
/**
 * AfterRender callback.  Writes any cached events to the view, or to a temp file.
 *
 * @return void
 * @access public
 */
	function afterRender() {
		echo $this->writeEvents();
	}
}
?>
