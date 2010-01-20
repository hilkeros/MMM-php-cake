<?php
/* SVN FILE: $Id: basics.php 7691 2008-10-02 04:59:12Z nate $ */
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
 * @version			$Revision: 7691 $
 * @modifiedby		$LastChangedBy: nate $
 * @lastmodified	$Date: 2008-10-02 00:59:12 -0400 (Thu, 02 Oct 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Basic defines for timing functions.
 */
	define('SECOND', 1);
	define('MINUTE', 60 * SECOND);
	define('HOUR', 60 * MINUTE);
	define('DAY', 24 * HOUR);
	define('WEEK', 7 * DAY);
	define('MONTH', 30 * DAY);
	define('YEAR', 365 * DAY);
/**
 * Patch for PHP < 4.3
 */
	if (!function_exists("ob_get_clean")) {
		function ob_get_clean() {
			$ob_contents = ob_get_contents();
			ob_end_clean();
			return $ob_contents;
		}
	}
/**
 * Patch for PHP < 4.3
 */
	if (version_compare(phpversion(), '5.0') < 0) {
		eval ('
		function clone($object) {
		return $object;
		}');
	}
/**
 * Computes the difference of arrays using keys for comparison
 *
 * @param array
 * @param array
 * @return array
 */
	if (!function_exists('array_diff_key')) {
		function array_diff_key() {
			$valuesDiff = array();

			if (func_num_args() < 2) {
				return false;
			}

			foreach (func_get_args() as $param) {
				if (!is_array($param)) {
					return false;
				}
			}

			$args = func_get_args();
			foreach ($args[0] as $valueKey => $valueData) {
				for ($i = 1; $i < func_num_args(); $i++) {
					if (isset($args[$i][$valueKey])) {
						continue 2;
					}
				}
				$valuesDiff[$valueKey] = $valueData;
			}
			return $valuesDiff;
		}
	}
/**
 * Computes the intersection of arrays using keys for comparison
 *
 * @param array
 * @param array
 * @return array
 */
	if (!function_exists('array_intersect_key')) {
		function array_intersect_key($arr1, $arr2) {
			$res = array();
			foreach ($arr1 as $key=>$value) {
				if (array_key_exists($key, $arr2)) {
					$res[$key] = $arr1[$key];
				}
			}
			return $res;
		}
	}
/**
 * Loads all models.
 */
	function loadModels() {
		if (!class_exists('Model')) {
			require LIBS . 'model' . DS . 'model.php';
		}
		$path = Configure::getInstance();
		if (!class_exists('AppModel')) {
			if (file_exists(APP . 'app_model.php')) {
				require(APP . 'app_model.php');
			} else {
				require(CAKE . 'app_model.php');
			}
			if (phpversion() < 5 && function_exists("overload")) {
				overload('AppModel');
			}
		}

		foreach ($path->modelPaths as $path) {
			foreach (listClasses($path)as $model_fn) {
				list($name) = explode('.', $model_fn);
				$className = Inflector::camelize($name);

				if (!class_exists($className)) {
					require($path . $model_fn);

					if (phpversion() < 5 && function_exists("overload")) {
						overload($className);
					}
				}
			}
		}
	}
/**
 * Loads all plugin models.
 *
 * @param  string  $plugin Name of plugin
 * @return
 */
	function loadPluginModels($plugin) {
		if (!class_exists('AppModel')) {
			loadModel();
		}
		$pluginAppModel = Inflector::camelize($plugin . '_app_model');
		$pluginAppModelFile = APP . 'plugins' . DS . $plugin . DS . $plugin . '_app_model.php';
		if (!class_exists($pluginAppModel)) {
			if (file_exists($pluginAppModelFile)) {
				require($pluginAppModelFile);
			} else {
				die('Plugins must have a class named ' . $pluginAppModel);
			}
			if (phpversion() < 5 && function_exists("overload")) {
				overload($pluginAppModel);
			}
		}

		$pluginModelDir = APP . 'plugins' . DS . $plugin . DS . 'models' . DS;

		foreach (listClasses($pluginModelDir)as $modelFileName) {
			list($name) = explode('.', $modelFileName);
			$className = Inflector::camelize($name);

			if (!class_exists($className)) {
				require($pluginModelDir . $modelFileName);

				if (phpversion() < 5 && function_exists("overload")) {
					overload($className);
				}
			}
		}
	}
/**
 * Loads custom view class.
 *
 */
	function loadView($viewClass) {
		if (!class_exists($viewClass . 'View')) {
			$paths = Configure::getInstance();
			$file = Inflector::underscore($viewClass) . '.php';

			foreach ($paths->viewPaths as $path) {
				if (file_exists($path . $file)) {
					return require($path . $file);
				}
			}

			if ($viewFile = fileExistsInPath(LIBS . 'view' . DS . $file)) {
				if (file_exists($viewFile)) {
					require($viewFile);
					return true;
				} else {
					return false;
				}
			}
		}
	}
/**
 * Loads a model by CamelCase name.
 */
	function loadModel($name = null) {
		if (!class_exists('Model')) {
			require LIBS . 'model' . DS . 'model.php';
		}
		if (!class_exists('AppModel')) {
			if (file_exists(APP . 'app_model.php')) {
				require(APP . 'app_model.php');
			} else {
				require(CAKE . 'app_model.php');
			}
			if (phpversion() < 5 && function_exists("overload")) {
				overload('AppModel');
			}
		}

		if (!is_null($name) && !class_exists($name)) {
			$className = $name;
			$name = Inflector::underscore($name);
			$paths = Configure::getInstance();

			foreach ($paths->modelPaths as $path) {
				if (file_exists($path . $name . '.php')) {
					require($path . $name . '.php');
					if (phpversion() < 5 && function_exists("overload")) {
						overload($className);
					}
					return true;
				}
			}
			return false;
		} else {
			return true;
		}
	}
/**
 * Loads all controllers.
 */
	function loadControllers() {
		$paths = Configure::getInstance();
		if (!class_exists('AppController')) {
			if (file_exists(APP . 'app_controller.php')) {
				require(APP . 'app_controller.php');
			} else {
				require(CAKE . 'app_controller.php');
			}
		}
		$loadedControllers = array();

		foreach ($paths->controllerPaths as $path) {
			foreach (listClasses($path) as $controller) {
				list($name) = explode('.', $controller);
				$className = Inflector::camelize(str_replace('_controller', '', $name));

				if (loadController($className)) {
					$loadedControllers[$controller] = $className;
				}
			}
		}
		return $loadedControllers;
	}
/**
 * Loads a controller and its helper libraries.
 *
 * @param  string  $name Name of controller
 * @return boolean Success
 */
	function loadController($name) {
		$paths = Configure::getInstance();
		if (!class_exists('AppController')) {
			if (file_exists(APP . 'app_controller.php')) {
				require(APP . 'app_controller.php');
			} else {
				require(CAKE . 'app_controller.php');
			}
		}

		if ($name === null) {
			return true;
		}

		if (!class_exists($name . 'Controller')) {
			$name = Inflector::underscore($name);

			foreach ($paths->controllerPaths as $path) {
				if (file_exists($path . $name . '_controller.php')) {
					require($path . $name . '_controller.php');
					return true;
				}
			}

			if ($controller_fn = fileExistsInPath(LIBS . 'controller' . DS . $name . '_controller.php')) {
				if (file_exists($controller_fn)) {
					require($controller_fn);
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}
/**
 * Loads a plugin's controller.
 *
 * @param  string  $plugin Name of plugin
 * @param  string  $controller Name of controller to load
 * @return boolean Success
 */
	function loadPluginController($plugin, $controller) {
		$pluginAppController = Inflector::camelize($plugin . '_app_controller');
		$pluginAppControllerFile = APP . 'plugins' . DS . $plugin . DS . $plugin . '_app_controller.php';
		if (!class_exists($pluginAppController)) {
			if (file_exists($pluginAppControllerFile)) {
				require($pluginAppControllerFile);
			} else {
				return false;
			}
		}

		if (empty($controller)) {
			if (!class_exists($plugin . 'Controller')) {
				if (file_exists(APP . 'plugins' . DS . $plugin . DS . 'controllers' . DS . $plugin . '_controller.php')) {
					require(APP . 'plugins' . DS . $plugin . DS . 'controllers' . DS . $plugin . '_controller.php');
					return true;
				}
			}
		}

		if (!class_exists($controller . 'Controller')) {
			$controller = Inflector::underscore($controller);
			$file = APP . 'plugins' . DS . $plugin . DS . 'controllers' . DS . $controller . '_controller.php';

			if (file_exists($file)) {
				require($file);
				return true;
			}  elseif (!class_exists(Inflector::camelize($plugin . '_controller'))) {
				if (file_exists(APP . 'plugins' . DS . $plugin . DS . 'controllers' . DS . $plugin . '_controller.php')) {
					require(APP . 'plugins' . DS . $plugin . DS . 'controllers' . DS . $plugin . '_controller.php');
					return true;
				} else {
					return false;
				}
			}
		}
		return true;
	}
/**
 * Loads a helper
 *
 * @param  string  $name Name of helper
 * @return boolean Success
 */
	function loadHelper($name) {
		$paths = Configure::getInstance();

		if ($name === null) {
			return true;
		}

		if (!class_exists($name . 'Helper')) {
			$name=Inflector::underscore($name);

			foreach ($paths->helperPaths as $path) {
				if (file_exists($path . $name . '.php')) {
					require($path . $name . '.php');
					return true;
				}
			}

			if ($helper_fn = fileExistsInPath(LIBS . 'view' . DS . 'helpers' . DS . $name . '.php')) {
				if (file_exists($helper_fn)) {
					require($helper_fn);
					return true;
				} else {
					return false;
				}
			}
		} else {
			return true;
		}
	}
/**
 * Loads a plugin's helper
 *
 * @param  string  $plugin Name of plugin
 * @param  string  $helper Name of helper to load
 * @return boolean Success
 */
	function loadPluginHelper($plugin, $helper) {
		if (!class_exists($helper . 'Helper')) {
			$helper = Inflector::underscore($helper);
			$file = APP . 'plugins' . DS . $plugin . DS . 'views' . DS . 'helpers' . DS . $helper . '.php';
			if (file_exists($file)) {
				require($file);
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
/**
 * Loads a component
 *
 * @param  string  $name Name of component
 * @return boolean Success
 */
	function loadComponent($name) {
		$paths = Configure::getInstance();

		if ($name === null) {
			return true;
		}

		if (!class_exists($name . 'Component')) {
			$name=Inflector::underscore($name);

			foreach ($paths->componentPaths as $path) {
				if (file_exists($path . $name . '.php')) {
					require($path . $name . '.php');
					return true;
				}
			}

			if ($component_fn = fileExistsInPath(LIBS . 'controller' . DS . 'components' . DS . $name . '.php')) {
				if (file_exists($component_fn)) {
					require($component_fn);
					return true;
				} else {
					return false;
				}
			}
		} else {
			return true;
		}
	}
/**
 * Loads a plugin's component
 *
 * @param  string  $plugin Name of plugin
 * @param  string  $helper Name of component to load
 * @return boolean Success
 */
	function loadPluginComponent($plugin, $component) {
		if (!class_exists($component . 'Component')) {
			$component = Inflector::underscore($component);
			$file = APP . 'plugins' . DS . $plugin . DS . 'controllers' . DS . 'components' . DS . $component . '.php';
			if (file_exists($file)) {
				require($file);
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
/**
 * Returns an array of filenames of PHP files in given directory.
 *
 * @param  string $path Path to scan for files
 * @return array  List of files in directory
 */
	function listClasses($path) {
		$dir = opendir($path);
		$classes=array();
		while (false !== ($file = readdir($dir))) {
			if ((substr($file, -3, 3) == 'php') && substr($file, 0, 1) != '.') {
				$classes[] = $file;
			}
		}
		closedir($dir);
		return $classes;
	}
/**
 * Loads configuration files
 *
 * @return boolean Success
 */
	function config() {
		$args = func_get_args();
		foreach ($args as $arg) {
			if (('database' == $arg) && file_exists(CONFIGS . $arg . '.php')) {
				include_once(CONFIGS . $arg . '.php');
			} elseif (file_exists(CONFIGS . $arg . '.php')) {
				include_once(CONFIGS . $arg . '.php');

				if (count($args) == 1) {
					return true;
				}
			} else {
				if (count($args) == 1) {
					return false;
				}
			}
		}
		return true;
	}
/**
 * Loads component/components from LIBS.
 *
 * Example:
 * <code>
 * uses('flay', 'time');
 * </code>
 *
 * @uses LIBS
 */
	function uses() {
		$args = func_get_args();
		foreach ($args as $arg) {
			require_once(LIBS . strtolower($arg) . '.php');
		}
	}
/**
 * Require given files in the VENDORS directory. Takes optional number of parameters.
 *
 * @param string $name Filename without the .php part.
 *
 */
	function vendor($name) {
		$args = func_get_args();
		foreach ($args as $arg) {
			if (file_exists(APP . 'vendors' . DS . $arg . '.php')) {
				require_once(APP . 'vendors' . DS . $arg . '.php');
			} else {
				require_once(VENDORS . $arg . '.php');
			}
		}
	}
/**
 * Prints out debug information about given variable.
 *
 * Only runs if DEBUG level is non-zero.
 *
 * @param boolean $var		Variable to show debug information for.
 * @param boolean $show_html	If set to true, the method prints the debug data in a screen-friendly way.
 */
	function debug($var = false, $showHtml = false) {
		if (Configure::read() > 0) {
			print "\n<pre class=\"cake_debug\">\n";
			ob_start();
			print_r($var);
			$var = ob_get_clean();

			if ($showHtml) {
				$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
			}
			print "{$var}\n</pre>\n";
		}
	}
/**
 * Returns microtime for execution time checking
 *
 * @return integer
 */
	if (!function_exists('getMicrotime')) {
		function getMicrotime() {
			list($usec, $sec) = explode(" ", microtime());
			return ((float)$usec + (float)$sec);
		}
	}
/**
 * Sorts given $array by key $sortby.
 *
 * @param  array	$array
 * @param  string  $sortby
 * @param  string  $order  Sort order asc/desc (ascending or descending).
 * @param  integer $type
 * @return mixed
 */
	if (!function_exists('sortByKey')) {
		function sortByKey(&$array, $sortby, $order = 'asc', $type = SORT_NUMERIC) {
			if (!is_array($array)) {
				return null;
			}

			foreach ($array as $key => $val) {
				$sa[$key] = $val[$sortby];
			}

			if ($order == 'asc') {
				asort($sa, $type);
			} else {
				arsort($sa, $type);
			}

			foreach ($sa as $key => $val) {
				$out[] = $array[$key];
			}
			return $out;
		}
	}
/**
 * Combines given identical arrays by using the first array's values as keys,
 * and the second one's values as values. (Implemented for back-compatibility with PHP4)
 *
 * @param  array $a1
 * @param  array $a2
 * @return mixed Outputs either combined array or false.
 */
	if (!function_exists('array_combine')) {
		function array_combine($a1, $a2) {
			$a1 = array_values($a1);
			$a2 = array_values($a2);
			$c1 = count($a1);
			$c2 = count($a2);

			if ($c1 != $c2) {
				return false;
			}
			if ($c1 <= 0) {
				return false;
			}

			$output=array();
			for ($i = 0; $i < $c1; $i++) {
				$output[$a1[$i]] = $a2[$i];
			}
			return $output;
		}
	}
/**
 * Convenience method for htmlspecialchars.
 *
 * @param string $text
 * @return string
 */
	function h($text) {
		if (is_array($text)) {
			return array_map('h', $text);
		}
		return htmlspecialchars($text);
	}
/**
 * Returns an array of all the given parameters.
 *
 * Example:
 * <code>
 * a('a', 'b')
 * </code>
 *
 * Would return:
 * <code>
 * array('a', 'b')
 * </code>
 *
 * @return array
 */
	function a() {
		$args = func_get_args();
		return $args;
	}
/**
 * Constructs associative array from pairs of arguments.
 *
 * Example:
 * <code>
 * aa('a','b')
 * </code>
 *
 * Would return:
 * <code>
 * array('a'=>'b')
 * </code>
 *
 * @return array
 */
	function aa() {
		$args = func_get_args();
		for ($l = 0, $c = count($args); $l < $c; $l++) {
			if ($l + 1 < count($args)) {
				$a[$args[$l]] = $args[$l + 1];
			} else {
				$a[$args[$l]] = null;
			}
			$l++;
		}
		return $a;
	}
/**
 * Convenience method for echo().
 *
 * @param string $text String to echo
 */
	function e($text) {
		echo $text;
	}
/**
 * Convenience method for strtolower().
 *
 * @param string $str String to lowercase
 */
	function low($str) {
		return strtolower($str);
	}
/**
 * Convenience method for strtoupper().
 *
 * @param string $str String to uppercase
 */
	function up($str) {
		return strtoupper($str);
	}
/**
 * Convenience method for str_replace().
 *
 * @param string $search String to be replaced
 * @param string $replace String to insert
 * @param string $subject String to search
 */
	function r($search, $replace, $subject) {
		return str_replace($search, $replace, $subject);
	}
/**
 * Print_r convenience function, which prints out <PRE> tags around
 * the output of given array. Similar to debug().
 *
 * @see	debug()
 * @param array	$var
 */
	function pr($var) {
		if (Configure::read() > 0) {
			echo "<pre>";
			print_r($var);
			echo "</pre>";
		}
	}
/**
 * Display parameter
 *
 * @param  mixed  $p Parameter as string or array
 * @return string
 */
	function params($p) {
		if (!is_array($p) || count($p) == 0) {
			return null;
		} else {
			if (is_array($p[0]) && count($p) == 1) {
				return $p[0];
			} else {
				return $p;
			}
		}
	}
/**
 * Merge a group of arrays
 *
 * @param array First array
 * @param array Second array
 * @param array Third array
 * @param array Etc...
 * @return array All array parameters merged into one
 */
	function am() {
		$r = array();
		foreach (func_get_args()as $a) {
			if (!is_array($a)) {
				$a = array($a);
			}
			$r = array_merge($r, $a);
		}
		return $r;
	}
/**
 * Returns the REQUEST_URI from the server environment, or, failing that,
 * constructs a new one, using the PHP_SELF constant and other variables.
 *
 * @return string URI
 */
	function setUri() {
		if (env('HTTP_X_REWRITE_URL')) {
			$uri = env('HTTP_X_REWRITE_URL');
		} elseif (env('REQUEST_URI')) {
			$uri = env('REQUEST_URI');
		} else {
			if (env('argv')) {
				$uri = env('argv');

				if (defined('SERVER_IIS')) {
					$uri = BASE_URL . $uri[0];
				} else {
					$uri = env('PHP_SELF') . '/' . $uri[0];
				}
			} else {
				$uri = env('PHP_SELF') . '/' . env('QUERY_STRING');
			}
		}
		return $uri;
	}
/**
 * Gets an environment variable from available sources.
 * Used as a backup if $_SERVER/$_ENV are disabled.
 *
 * @param  string $key Environment variable name.
 * @return string Environment variable setting.
 */
	function env($key) {

		if ($key == 'HTTPS') {
			if (isset($_SERVER) && !empty($_SERVER)) {
				return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
			} else {
				return (strpos(env('SCRIPT_URI'), 'https://') === 0);
			}
		}

		if (isset($_SERVER[$key])) {
			return $_SERVER[$key];
		} elseif (isset($_ENV[$key])) {
			return $_ENV[$key];
		} elseif (getenv($key) !== false) {
			return getenv($key);
		}

		if ($key == 'SCRIPT_FILENAME' && defined('SERVER_IIS') && SERVER_IIS === true) {
			return str_replace('\\\\', '\\', env('PATH_TRANSLATED') );
		}

		if ($key == 'DOCUMENT_ROOT') {
			$offset = 0;
			if (!strpos(env('SCRIPT_NAME'), '.php')) {
				$offset = 4;
			}
			return substr(env('SCRIPT_FILENAME'), 0, strlen(env('SCRIPT_FILENAME')) - (strlen(env('SCRIPT_NAME')) + $offset));
		}
		if ($key == 'PHP_SELF') {
			return r(env('DOCUMENT_ROOT'), '', env('SCRIPT_FILENAME'));
		}
		return null;
	}
/**
 * Returns contents of a file as a string.
 *
 * @param  string  $fileName		Name of the file.
 * @param  boolean $useIncludePath Wheter the function should use the include path or not.
 * @return mixed	Boolean false or contents of required file.
 */
	if (!function_exists('file_get_contents')) {
		function file_get_contents($fileName, $useIncludePath = false) {
			$res=fopen($fileName, 'rb', $useIncludePath);

			if ($res === false) {
				trigger_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);
				return false;
			}
			clearstatcache();

			if ($fileSize = @filesize($fileName)) {
				$data = fread($res, $fileSize);
			} else {
				$data = '';

				while (!feof($res)) {
					$data .= fread($res, 8192);
				}
			}
			return "$data\n";
		}
	}
/**
 * Writes data into file.
 *
 * If file exists, it will be overwritten. If data is an array, it will be join()ed with an empty string.
 *
 * @param string $fileName File name.
 * @param mixed  $data String or array.
 */
	if (!function_exists('file_put_contents')) {
		function file_put_contents($fileName, $data) {
			if (is_array($data)) {
				$data = join('', $data);
			}
			$res = @fopen($fileName, 'w+b');
			if ($res) {
				$write = @fwrite($res, $data);
				if ($write === false) {
					return false;
				} else {
					return $write;
				}
			}
		}
	}
/**
 * Reads/writes temporary data to cache files or session.
 *
 * @param  string $path	File path within /tmp to save the file.
 * @param  mixed  $data	The data to save to the temporary file.
 * @param  mixed  $expires A valid strtotime string when the data expires.
 * @param  string $target  The target of the cached data; either 'cache' or 'public'.
 * @return mixed  The contents of the temporary file.
 */
	function cache($path, $data = null, $expires = '+1 day', $target = 'cache') {
		$now = time();
		if (!is_numeric($expires)) {
			$expires = strtotime($expires, $now);
		}

		switch(strtolower($target)) {
			case 'cache':
				$filename = CACHE . $path;
			break;
			case 'public':
				$filename = WWW_ROOT . $path;
			break;
		}

		$timediff = $expires - $now;
		$filetime = false;
		if (file_exists($filename)) {
			$filetime = @filemtime($filename);
		}

		if ($data === null) {
			// Read data from file
			if (file_exists($filename) && $filetime !== false) {
				if ($filetime + $timediff < $now) {
					// File has expired
					@unlink($filename);
				} else {
					$data = file_get_contents($filename);
				}
			}
		} else {
			file_put_contents($filename, $data);
		}
		return $data;
	}
/**
 * Used to delete files in the cache directories, or clear contents of cache directories
 *
 * @param mixed $params As String name to be searched for deletion, if name is a directory all files in directory will be deleted.
 *              If array, names to be searched for deletion.
 *              If clearCache() without params, all files in app/tmp/cache/views will be deleted
 *
 * @param string $type Directory in tmp/cache defaults to view directory
 * @param string $ext The file extension you are deleting
 * @return true if files found and deleted false otherwise
 */
	function clearCache($params = null, $type = 'views', $ext = '.php') {
		if (is_string($params) || $params === null) {
			$params = preg_replace('/\/\//', '/', $params);
			$cache = CACHE . $type . DS . $params;

			if (is_file($cache . $ext)) {
				@unlink($cache . $ext);
				return true;
			} elseif (is_dir($cache)) {
				$files = glob("$cache*");

				if ($files === false) {
					return false;
				}

				foreach ($files as $file) {
					if (is_file($file)) {
						@unlink($file);
					}
				}
				return true;
			} else {
				$cache = CACHE . $type . DS . '*' . $params . $ext;
				$files = glob($cache);

				$cache = CACHE . $type . DS . '*' . $params . '_*' . $ext;
				$files = array_merge($files, glob($cache));

				if ($files === false) {
					return false;
				}

				foreach ($files as $file) {
					if (is_file($file)) {
						@unlink($file);
					}
				}
				return true;
			}
		} elseif (is_array($params)) {
			foreach ($params as $key => $file) {
				clearCache($file, $type, $ext);
			}
			return true;
		}
		return false;
	}
/**
 * Recursively strips slashes from all values in an array
 *
 * @param unknown_type $value
 * @return unknown
 */
	function stripslashes_deep($value) {
		if (is_array($value)) {
			$return = array_map('stripslashes_deep', $value);
			return $return;
		} else {
			$return = stripslashes($value);
			return $return ;
		}
	}
/**
 * Returns a translated string if one is found,
 * or the submitted message if not found.
 *
 * @param  unknown_type $msg
 * @param  unknown_type $return
 * @return unknown
 * @todo Not implemented fully till 2.0
 */
	function __($msg, $return = null) {
		if (is_null($return)) {
			echo($msg);
		} else {
			return $msg;
		}
	}
/**
 * Counts the dimensions of an array
 *
 * @param array $array
 * @return int The number of dimensions in $array
 */
	function countdim($array) {
		if (is_array(reset($array))) {
			$return = countdim(reset($array)) + 1;
		} else {
			$return = 1;
		}
		return $return;
	}
/**
 * Shortcut to Log::write.
 */
	function LogError($message) {
		if (!class_exists('CakeLog')) {
			uses('cake_log');
		}
		$bad = array("\n", "\r", "\t");
		$good = ' ';
		CakeLog::write('error', str_replace($bad, $good, $message));
	}
/**
 * Searches include path for files
 *
 * @param string $file
 * @return Full path to file if exists, otherwise false
 */
	function fileExistsInPath($file) {
		$paths = explode(PATH_SEPARATOR, ini_get('include_path'));
		foreach ($paths as $path) {
			$fullPath = $path . DIRECTORY_SEPARATOR . $file;

			if (file_exists($fullPath)) {
				return $fullPath;
			} elseif (file_exists($file)) {
				return $file;
			}
		}
		return false;
	}
/**
 * Convert forward slashes to underscores and removes first and last underscores in a string
 *
 * @param string
 * @return string with underscore remove from start and end of string
 */
	function convertSlash($string) {
		$string = trim($string,"/");
		$string = preg_replace('/\/\//', '/', $string);
		$string = str_replace('/', '_', $string);
		return $string;
	}

/**
 * chmod recursively on a directory
 *
 * @param string $path
 * @param int $mode
 * @return boolean
 */
	function chmodr($path, $mode = 0755) {
		if (!is_dir($path)) {
			return chmod($path, $mode);
		}
		$dir = opendir($path);

		while ($file = readdir($dir)) {
			if ($file != '.' && $file != '..') {
				$fullpath = $path . '/' . $file;

				if (!is_dir($fullpath)) {
					if (!chmod($fullpath, $mode)) {
						return false;
					}
				} else {
					if (!chmodr($fullpath, $mode)) {
						return false;
					}
				}
			}
		}
		closedir($dir);

		if (chmod($path, $mode)) {
			return true;
		} else {
			return false;
		}
	}
/**
 * removed the plugin name from the base url
 *
 * @param string $base
 * @param string $plugin
 * @return base url with plugin name removed if present
 */
	function strip_plugin($base, $plugin) {
		if ($plugin != null) {
			$base = preg_replace('/' . $plugin . '/', '', $base);
			$base = str_replace('//', '', $base);
			$pos1 = strrpos($base, '/');
			$char = strlen($base) - 1;

			if ($pos1 == $char) {
				$base = substr($base, 0, $char);
			}
		}
		return $base;
	}
/**
 * Wraps ternary operations.  If $condition is a non-empty value, $val1 is returned, otherwise $val2.
 *
 * @param mixed $condition Conditional expression
 * @param mixed $val1
 * @param mixed $val2
 * @return mixed $val1 or $val2, depending on whether $condition evaluates to a non-empty expression.
 */
	function ife($condition, $val1 = null, $val2 = null) {
		if (!empty($condition)) {
			return $val1;
		}
		return $val2;
	}
?>
