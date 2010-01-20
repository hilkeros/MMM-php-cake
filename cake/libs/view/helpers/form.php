<?php
/* SVN FILE: $Id: form.php 6305 2008-01-02 02:33:56Z phpnut $ */
/**
 * Automatic generation of HTML FORMs from given data.
 *
 * Used for scaffolding.
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
 * Tag template for a div with a class attribute.
 */
	define('TAG_DIV', '<div class="%s">%s</div>');
/**
 * Tag template for a paragraph with a class attribute.
 */
	define('TAG_P_CLASS', '<p class="%s">%s</p>');
/**
 * Tag template for a label with a for attribute.
 */
	define('TAG_LABEL', '<label for="%s">%s</label>');
/**
 * Tag template for a fieldset with a legend tag inside.
 */
	define('TAG_FIELDSET', '<fieldset><legend>%s</legend>%s</label>');
/**
 * Form helper library.
 *
 * Automatic generation of HTML FORMs from given data.
 *
 * @package		cake
 * @subpackage	cake.cake.libs.view.helpers
 */
class FormHelper extends Helper{
/**
 * Included helpers.
 *
 * @var array
 * @access public
 */
	var $helpers = array('Html');
/**
 * Returns a formatted error message for given FORM field, NULL if no errors.
 *
 * @param string $field This should be "Modelname/fieldname"
 * @return bool If there are errors this method returns true, else false.
 * @access public
 */
	function isFieldError($field) {
		$error=1;
		$this->Html->setFormTag($field);

		if ($error == $this->Html->tagIsInvalid($this->Html->model, $this->Html->field)) {
			return true;
		} else {
			return false;
		}
	}
/**
 * Returns a formatted LABEL element for HTML FORMs.
 *
 * @param string $tagName This should be "Modelname/fieldname"
 * @param string $text Text that will appear in the label field.
 * @return string The formatted LABEL element
 * @access public
 */
	function labelTag($tagName, $text) {
		return sprintf(TAG_LABEL, Inflector::camelize(str_replace('/', '_', $tagName)), $text);
	}
/**
 * Returns a formatted DIV tag for HTML FORMs.
 *
 * @param string $class CSS class name of the div element.
 * @param string $text String content that will appear inside the div element.
 * @return string The formatted DIV element
 * @access public
 */
	function divTag($class, $text) {
		return sprintf(TAG_DIV, $class, $text);
	}
/**
 * Returns a formatted P tag with class for HTML FORMs.
 *
 * @param string $class CSS class name of the p element.
 * @param string $text Text that will appear inside the p element.
 * @return string The formatted P element
 * @access public
 */
	function pTag($class, $text) {
		return sprintf(TAG_P_CLASS, $class, $text);
	}
/**
 * Returns a formatted INPUT tag for HTML FORMs.
 *
 * @param string $tagName	This should be "Modelname/fieldname"
 * @param string $prompt Text that will appear in the label field.
 * @param bool $required True if this field is a required field.
 * @param string $errorMsg	Text that will appear if an error has occurred.
 * @param int $size Size attribute for INPUT element
 * @param array $htmlOptions	HTML options array.
 * @return string The formatted INPUT element, with a label and wrapped in a div.
 * @access public
 */
	function generateInputDiv($tagName, $prompt, $required = false, $errorMsg = null, $size = 20, $htmlOptions = null) {
		$htmlAttributes = $htmlOptions;
		$htmlAttributes['size'] = $size;
		$str = $this->Html->input($tagName, $htmlAttributes);
		$strLabel = $this->labelTag($tagName, $prompt);
		$divClass = "optional";
		if ($required) {
			$divClass = "required";
		}
		$strError = "";

		if ($this->isFieldError($tagName)) {
			$strError = $this->pTag('error', $errorMsg);
			$divClass = sprintf("%s error", $divClass);
		}
		$divTagInside = sprintf("%s %s %s", $strError, $strLabel, $str);
		return $this->divTag($divClass, $divTagInside);
	}
/**
 * Returns a formatted CHECKBOX tag inside a DIV for HTML FORMs.
 *
 * @param string $tagName This should be "Modelname/fieldname"
 * @param string $prompt Text that will appear in the label field.
 * @param bool $required True if this field is a required field.
 * @param string $errorMsg Text that will appear if an error has occurred.
 * @param array $htmlOptions	HTML options array.
 * @return string The formatted checkbox div
 * @access public
 */
	function generateCheckboxDiv($tagName, $prompt, $required = false, $errorMsg = null, $htmlOptions = null) {
		$htmlOptions['class'] = "inputCheckbox";
		$str = $this->Html->checkbox($tagName, null, $htmlOptions);
		$strLabel = $this->labelTag($tagName, $prompt);
		$divClass = "optional";
		if ($required) {
			$divClass = "required";
		}
		$strError = "";

		if ($this->isFieldError($tagName)) {
			$strError = $this->pTag('error', $errorMsg);
			$divClass = sprintf("%s error", $divClass);
		}
		$divTagInside = sprintf("%s %s %s", $strError, $strLabel, $str);
		return $this->divTag($divClass, $divTagInside);
	}
/**
 * Returns a formatted date option element for HTML FORMs.
 *
 * @param string $tagName	This should be "Modelname/fieldname"
 * @param string $prompt Text that will appear in the label field.
 * @param bool $required True if this field is a required field.
 * @param string $errorMsg	Text that will appear if an error has occurred.
 * @param int $size Not used.
 * @param array $htmlOptions HTML options array
 * @return string Date option wrapped in a div.
 * @todo  Remove the $size parameter from this method.
 * @access public
 */
	function generateDate($tagName, $prompt, $required = false, $errorMsg = null, $size = 20, $htmlOptions = null, $selected = null) {
		$str = $this->Html->dateTimeOptionTag($tagName, 'MDY', 'NONE', $selected, $htmlOptions);
		$strLabel = $this->labelTag($tagName, $prompt);
		$divClass = "optional";
		if ($required) {
			$divClass = "required";
		}
		$strError = "";

		if ($this->isFieldError($tagName)) {
			$strError = $this->pTag('error', $errorMsg);
			$divClass = sprintf("%s error", $divClass);
		}
		$divTagInside = sprintf("%s %s %s", $strError, $strLabel, $str);
		$requiredDiv = $this->divTag($divClass, $divTagInside);
		return $this->divTag("date", $requiredDiv);
	}
/**
 * Returns a formatted date option element for HTML FORMs.
 *
 * @param string $tagName	This should be "Modelname/fieldname"
 * @param string $prompt Text that will appear in the label field.
 * @param bool $required True if this field is a required field.
 * @param string $errorMsg	Text that will appear if an error has occurred.
 * @param int $size Not used.
 * @param array $htmlOptions HTML options array
 * @return string Date option wrapped in a div.
 * @todo  Remove the $size parameter from this method.
 * @access public
 */
	function generateTime($tagName, $prompt, $required = false, $errorMsg = null, $size = 20, $htmlOptions = null, $selected = null) {
		$str = $this->Html->dateTimeOptionTag($tagName, 'NONE', '24', $selected, $htmlOptions);
		$strLabel = $this->labelTag($tagName, $prompt);
		$divClass = "optional";
		if ($required) {
			$divClass = "required";
		}
		$strError = "";

		if ($this->isFieldError($tagName)) {
			$strError = $this->pTag('error', $errorMsg);
			$divClass = sprintf("%s error", $divClass);
		}
		$divTagInside = sprintf("%s %s %s", $strError, $strLabel, $str);
		$requiredDiv = $this->divTag($divClass, $divTagInside);
		return $this->divTag("time", $requiredDiv);
	}
/**
 * Returns a formatted year option element for HTML FORMs.
 *
 * @param string $tagName	This should be "Modelname/fieldname"
 * @param string $prompt Text that will appear in the label field.
 * @param bool $required True if this field is a required field.
 * @param string $errorMsg	Text that will appear if an error has occurred.
 * @param int $size Not used.
 * @param array $htmlOptions HTML options array
 * @return string Date option wrapped in a div.
 * @todo  Remove the $size parameter from this method.
 * @access public
 */
	function generateYear($tagName, $prompt, $required = false, $errorMsg = null, $size = 20, $htmlOptions = null, $selected = null) {
		$str = $this->Html->dateTimeOptionTag($tagName, 'Y', 'NONE', $selected, $htmlOptions);
		$strLabel = $this->labelTag($tagName, $prompt);
		$divClass = "optional";
		if ($required) {
			$divClass = "required";
		}
		$strError = "";

		if ($this->isFieldError($tagName)) {
			$strError = $this->pTag('error', $errorMsg);
			$divClass = sprintf("%s error", $divClass);
		}
		$divTagInside = sprintf("%s %s %s", $strError, $strLabel, $str);
		$requiredDiv = $this->divTag($divClass, $divTagInside);
		return $this->divTag("year", $requiredDiv);
	}
/**
 * Returns a formatted datetime option element for HTML FORMs.
 *
 * @param string $tagName This should be "Modelname/fieldname"
 * @param string $prompt Text that will appear in the label field.
 * @param bool $required True if this field is required.
 * @param string $errorMsg Text that will appear if an error has occurred.
 * @param int $size Not used.
 * @param array $htmlOptions  HTML options array
 * @param array $selected Selected index in the dateTimeOption tag.
 * @return string The formatted datetime option element wrapped in a div.
 * @todo  Remove the $size parameter from this method.
 * @access public
 */
	function generateDateTime($tagName, $prompt, $required = false, $errorMsg = null, $size = 20, $htmlOptions = null, $selected = null) {
		$str = $this->Html->dateTimeOptionTag($tagName, 'MDY', '12', $selected, $htmlOptions);
		$strLabel = $this->labelTag($tagName, $prompt);
		$divClass = "optional";
		if ($required) {
			$divClass = "required";
		}
		$strError = "";

		if ($this->isFieldError($tagName)) {
			$strError = $this->pTag('error', $errorMsg);
			$divClass = sprintf("%s error", $divClass);
		}
		$divTagInside = sprintf("%s %s %s", $strError, $strLabel, $str);
		$requiredDiv = $this->divTag($divClass, $divTagInside);
		return $this->divTag("date", $requiredDiv);
	}
/**
 * Returns a formatted TEXTAREA inside a DIV for use with HTML forms.
 *
 * @param string $tagName	This should be "Modelname/fieldname"
 * @param string $prompt	Text that will appear in the label field.
 * @param boolean $required	True if this field is required.
 * @param string $errorMsg	ext that will appear if an error has occurred.
 * @param integer $cols		Number of columns.
 * @param integer $rows		Number of rows.
 * @param array $htmlOptions	HTML options array.
 * @return string The formatted TEXTAREA element, wrapped in a div.
 * @access public
 */
	function generateAreaDiv($tagName, $prompt, $required = false, $errorMsg = null, $cols = 60, $rows = 10, $htmlOptions = null) {
		$htmlAttributes = $htmlOptions;
		$htmlAttributes['cols'] = $cols;
		$htmlAttributes['rows'] = $rows;
		$str = $this->Html->textarea($tagName, $htmlAttributes);
		$strLabel = $this->labelTag($tagName, $prompt);
		$divClass = "optional";

		if ($required) {
			$divClass="required";
		}
		$strError = "";

		if ($this->isFieldError($tagName)) {
			$strError = $this->pTag('error', $errorMsg);
			$divClass = sprintf("%s error", $divClass);
		}
		$divTagInside = sprintf("%s %s %s", $strError, $strLabel, $str);
		return $this->divTag($divClass, $divTagInside);
	}
/**
 * Returns a formatted SELECT tag for HTML FORMs.
 *
 * @param string $tagName This should be "Modelname/fieldname"
 * @param string $prompt Text that will appear in the label field
 * @param array $options Options to be contained in SELECT element
 * @param string $selected Currently selected item
 * @param array $selectAttr Array of HTML attributes for the SELECT element
 * @param array $optionAttr Array of HTML attributes for the OPTION elements
 * @param bool $required True if this field is required
 * @param string $errorMsg Text that will appear if an error has occurred
 * @return string The formatted INPUT element, wrapped in a div
 * @access public
 */
	function generateSelectDiv($tagName, $prompt, $options, $selected = null, $selectAttr = null, $optionAttr = null, $required = false, $errorMsg = null) {
		$str = $this->Html->selectTag($tagName, $options, $selected, $selectAttr, $optionAttr);
		$strLabel = $this->labelTag($tagName, $prompt);
		$divClass = "optional";

		if ($required) {
			$divClass = "required";
		}
		$strError = "";

		if ($this->isFieldError($tagName)) {
			$strError=$this->pTag('error', $errorMsg);
			$divClass=sprintf("%s error", $divClass);
		}
		$divTagInside = sprintf("%s %s %s", $strError, $strLabel, $str);
		return $this->divTag($divClass, $divTagInside);
	}
/**
 * Returns a formatted submit widget for HTML FORMs.
 *
 * @param string $displayText Text that will appear on the widget
 * @param array $htmlOptions HTML options array
 * @return string The formatted submit widget
 * @access public
 */
	function generateSubmitDiv($displayText, $htmlOptions = null) {
		return $this->divTag('submit', $this->Html->submit($displayText, $htmlOptions));
	}
/**
 * Generates a form to go onto a HtmlHelper object.
 *
 * @param array $fields An array of form field definitions
 * @param boolean $readOnly True if the form should be rendered as READONLY
 * @return string The completed form specified by the $fields parameter
 * @access public
 */
	function generateFields($fields, $readOnly = false) {
		$strFormFields = '';

		foreach ($fields as $field) {
			if (isset($field['type'])) {

				if (!isset($field['required'])) {
					$field['required'] = false;
				}

				if (!isset($field['errorMsg'])) {
					$field['errorMsg'] = null;
				}

				if (!isset($field['htmlOptions'])) {
					$field['htmlOptions'] = array();
				}

				if ($readOnly) {
					$field['htmlOptions']['READONLY'] = "readonly";
				}

				switch($field['type']) {
					case "input":
						if (!isset($field['size'])) {
							$field['size'] = 40;
						}
						$strFormFields = $strFormFields . $this->generateInputDiv($field['tagName'], $field['prompt'],
																		$field['required'], $field['errorMsg'], $field['size'], $field['htmlOptions']);
					break;
					case "checkbox":
						$strFormFields = $strFormFields . $this->generateCheckboxDiv($field['tagName'], $field['prompt'],
																		$field['required'], $field['errorMsg'], $field['htmlOptions']);
					break;
					case "select":
					case "selectMultiple":
						if ("selectMultiple" == $field['type']) {
							$field['selectAttr']['multiple'] = 'multiple';
							$field['selectAttr']['class'] = 'selectMultiple';
						}

						if (!isset($field['selected'])) {
							$field['selected'] = null;
						}

						if (!isset($field['selectAttr'])) {
							$field['selectAttr'] = null;
						}

						if (!isset($field['optionsAttr'])) {
							$field['optionsAttr'] = null;
						}

						if ($readOnly) {
							$field['selectAttr']['DISABLED'] = true;
						}

						if (!isset($field['options'])) {
							$field['options'] = null;
						}
						$strFormFields = $strFormFields . $this->generateSelectDiv($field['tagName'], $field['prompt'], $field['options'],
																		$field['selected'], $field['selectAttr'], $field['optionsAttr'], $field['required'], $field['errorMsg']);
					break;
					case "area":
						if (!isset($field['rows'])) {
							$field['rows'] = 10;
						}

						if (!isset($field['cols'])) {
							$field['cols'] = 60;
						}
						$strFormFields = $strFormFields . $this->generateAreaDiv($field['tagName'], $field['prompt'],
																		$field['required'], $field['errorMsg'], $field['cols'], $field['rows'], $field['htmlOptions']);
					break;
					case "fieldset":
						$strFieldsetFields = $this->generateFields($field['fields']);
						$strFieldSet = sprintf(' <fieldset><legend>%s</legend><div class="notes"><h4>%s</h4><p class="last">%s</p></div>%s</fieldset>',
														$field['legend'], $field['noteHeading'], $field['note'], $strFieldsetFields);
						$strFormFields = $strFormFields . $strFieldSet;
					break;
					case "hidden":
						if (!isset($field['value'])) {
							$field['value'] = null;
						}
						$strFormFields = $strFormFields . $this->Html->hidden($field['tagName'], $field['value']);
					break;
					case "date":
						if (!isset($field['selected'])) {
							$field['selected'] = null;
						}
						$strFormFields = $strFormFields . $this->generateDate($field['tagName'], $field['prompt'], $field['required'], $field['errorMsg'], null, $field['htmlOptions'], $field['selected']);
					break;
					case "datetime":
						if (!isset($field['selected'])) {
							$field['selected'] = null;
						}
						$strFormFields = $strFormFields . $this->generateDateTime($field['tagName'], $field['prompt'], $field['required'], $field['errorMsg'], null, $field['htmlOptions'], $field['selected']);
					break;
					case "time":
						if (!isset($field['selected'])) {
							$field['selected'] = null;
						}
						$strFormFields = $strFormFields . $this->generateTime($field['tagName'], $field['prompt'], $field['required'], $field['errorMsg'], null, $field['htmlOptions'], $field['selected']);
					break;
					case "year":
						if (!isset($field['selected'])) {
							$field['selected'] = null;
						}
						$strFormFields = $strFormFields . $this->generateYear($field['tagName'], $field['prompt'], $field['required'], $field['errorMsg'], null, $field['htmlOptions'], $field['selected']);
					break;
					default:
					break;
				}
			}
		}
		return $strFormFields;
	}
}
?>
