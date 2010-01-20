<?php
/* SVN FILE: $Id: component.php 6305 2008-01-02 02:33:56Z phpnut $ */
/**
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
 * @subpackage		cake.cake.libs.controller
 * @since			CakePHP(tm) v TBD
 * @version			$Revision: 6305 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-01 21:33:56 -0500 (Tue, 01 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Component
 *
 * Used to create instances of applications components
 *
 * @package		cake
 * @subpackage	cake.cake.libs.controller
 */
class Component extends Object {
/**
 * Instance Controller
 *
 * @var object
 * @access private
 */
	var $__controller = null;
/**
 * Constructor
 */
	function __construct() {
	}
/**
 * Used to initialize the components for current controller
 *
 * @param object $controller
 * @access public
 */
	function init(&$controller) {
		$this->__controller =& $controller;

		if ($this->__controller->components !== false) {
			$loaded = array();
			$this->__controller->components = array_merge(array('Session'), $this->__controller->components);
			$loaded = $this->__loadComponents($loaded, $this->__controller->components);

			foreach (array_keys($loaded)as $component) {
				$tempComponent =& $loaded[$component];

				if (isset($tempComponent->components) && is_array($tempComponent->components)) {
					foreach ($tempComponent->components as $subComponent) {
						$this->__controller->{$component}->{$subComponent} =& $loaded[$subComponent];
					}
				}
				if (is_callable(array($tempComponent, 'initialize'))) {
					$tempComponent->initialize($controller);
				}
			}
		}
	}

/**
 * Enter description here...
 *
 * @param array $loaded
 * @param array $components
 * @return loaded components
 * @access private
 */
	function &__loadComponents(&$loaded, $components) {
		foreach ($components as $component) {
			$parts = preg_split('/\/|\./', $component);

			if (count($parts) === 1) {
				$plugin = $this->__controller->plugin;
			} else {
				$plugin = Inflector::underscore($parts['0']);
				$component = $parts[count($parts) - 1];
			}

			$componentCn = $component . 'Component';

			if (in_array($component, array_keys($loaded)) !== true) {

				if (!class_exists($componentCn)) {

					if (is_null($plugin) || !loadPluginComponent($plugin, $component)) {

						if (!loadComponent($component)) {
							$this->cakeError('missingComponentFile', array(array(
													'className' => $this->__controller->name,
													'component' => $component,
													'file' => Inflector::underscore($component) . '.php',
													'base' => $this->__controller->base)));
							exit();
						}
					}

					if (!class_exists($componentCn)) {
						$this->cakeError('missingComponentClass', array(array(
												'className' => $this->__controller->name,
												'component' => $component,
												'file' => Inflector::underscore($component) . '.php',
												'base' => $this->__controller->base)));
						exit();
					}
				}

				if ($componentCn == 'SessionComponent') {
					$param = strip_plugin($this->__controller->base, $this->__controller->plugin) . '/';
				} else {
					$param = null;
				}
				$this->__controller->{$component} =& new $componentCn($param);
				$loaded[$component] =& $this->__controller->{$component};

				if (isset($this->__controller->{$component}->components) && is_array($this->__controller->{$component}->components)) {
					$loaded =& $this->__loadComponents($loaded, $this->__controller->{$component}->components);
				}
			}
		}
		return $loaded;
	}
}
?>
