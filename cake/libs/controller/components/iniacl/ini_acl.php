<?php
/* SVN FILE: $Id: ini_acl.php 7691 2008-10-02 04:59:12Z nate $ */
/**
 * This is core configuration file.
 *
 * Use it to configure core behaviour ofCake.
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
 * @subpackage		cake.cake.libs.controller.componenets.iniacl
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 7691 $
 * @modifiedby		$LastChangedBy: nate $
 * @lastmodified	$Date: 2008-10-02 00:59:12 -0400 (Thu, 02 Oct 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * load AclBase
 */
uses('controller/components/acl_base');
/**
 * In this file you can extend the AclBase.
 *
 * @package		cake
 * @subpackage	cake.cake.libs.controller.componenets.iniacl
 */
class INI_ACL extends AclBase {
/**
 * Array with configuration, parsed from ini file
 *
 * @var array
 */
	var $config = null;
/**
 * Constructor
 *
 */
	function __construct() {
	}

/**
 * Main ACL check function. Checks to see if the ARO (access request object) has access to the ACO (access control object).
 * Looks at the acl.ini.php file for permissions (see instructions in/config/acl.ini.php).
 *
 * @param string $aro
 * @param string $aco
 * @return boolean
 * @access public
 */
	function check($aro, $aco, $acoAction = null) {
		if ($this->config == null) {
			$this->config = $this->readConfigFile(CONFIGS . 'acl.ini.php');
		}
		$aclConfig = $this->config;

		//First, if the user is specifically denied, then DENY
		if (isset($aclConfig[$aro]['deny'])) {
			$userDenies = $this->arrayTrim(explode(",", $aclConfig[$aro]['deny']));

			if (array_search($aco, $userDenies)) {
				//echo "User Denied!";
				return false;
			}
		}

		//Second, if the user is specifically allowed, then ALLOW
		if (isset($aclConfig[$aro]['allow'])) {
			$userAllows = $this->arrayTrim(explode(",", $aclConfig[$aro]['allow']));

			if (array_search($aco, $userAllows)) {
				//echo "User Allowed!";
				return true;
			}
		}

		//Check group permissions
		if (isset($aclConfig[$aro]['groups'])) {
			$userGroups = $this->arrayTrim(explode(",", $aclConfig[$aro]['groups']));

			foreach ($userGroups as $group) {
				//If such a group exists,
				if (array_key_exists($group, $aclConfig)) {
					//If the group is specifically denied, then DENY
					if (isset($aclConfig[$group]['deny'])) {
						$groupDenies=$this->arrayTrim(explode(",", $aclConfig[$group]['deny']));

						if (array_search($aco, $groupDenies)) {
							//echo("Group Denied!");
							return false;
						}
					}

					//If the group is specifically allowed, then ALLOW
					if (isset($aclConfig[$group]['allow'])) {
						$groupAllows = $this->arrayTrim(explode(",", $aclConfig[$group]['allow']));

						if (array_search($aco, $groupAllows)) {
							//echo("Group Allowed!");
							return true;
						}
					}
				}
			}
		}

		//Default, DENY
		//echo("DEFAULT: DENY.");
		return false;
	}

/**
 * Parses an INI file and returns an array that reflects the INI file's section structure. Double-quote friendly.
 *
 * @param string $fileName
 * @return array
 */
	function readConfigFile($fileName) {
		$fileLineArray = file($fileName);

		foreach ($fileLineArray as $fileLine) {
				$dataLine = trim($fileLine);
				$firstChar = substr($dataLine, 0, 1);

				if ($firstChar != ';' && $dataLine != '') {
					if ($firstChar == '[' && substr($dataLine, -1, 1) == ']') {
						$sectionName = preg_replace('/[\[\]]/', '', $dataLine);
					} else {
						$delimiter = strpos($dataLine, '=');

						if ($delimiter > 0) {
							$key = strtolower(trim(substr($dataLine, 0, $delimiter)));
							$value = trim(substr($dataLine, $delimiter + 1));

							if (substr($value, 0, 1) == '"' && substr($value, -1) == '"') {
								$value = substr($value, 1, -1);
							}

							$iniSetting[$sectionName][$key]=stripcslashes($value);
						} else {
							if (!isset($sectionName)) {
								$sectionName = '';
							}

							$iniSetting[$sectionName][strtolower(trim($dataLine))]='';
						}
					}
				} else {
				}
		}

		return $iniSetting;
	}
/**
 * Removes trailing spaces on all array elements (to prepare for searching)
 *
 * @param array $array Array to trim
 * @return array Trimmed array
 * @access public
 */
	function arrayTrim($array) {
		foreach ($array as $key => $value) {
			$array[$key] = trim($value);
		}
		array_unshift($array, "");
		return $array;
	}
}
?>
