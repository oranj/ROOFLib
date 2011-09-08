<?php
/**
 * ROOFLib
 * Version 0.7
 * MIT License
 * Ray Minge
 * the@rayminge.com
 *
 * @package ROOFLib 0.7
 */

require_once('class.phone.php');

class FI_Number extends FI_Phone {


/**
 * Creates a new FI_Number
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct ($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'lower_limit' => NULL,
			'upper_limit' => NULL,
		);

		$this->merge($options, $defaultValues);
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Number";
 */
	public static function getType() {
		return "Number";
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Number $lower_limit The minimum value to allow; Default:NULL
 * @param Number $upper_limit The maximum value to allow; Default:NULL
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'lower_limit'=>self::DE('number', 'The minimum value to allow', 'NULL'),
			'upper_limit'=>self::DE('number', 'The maximum value to allow', 'NULL'),
		);
	}

/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {

		$html = '';
		$this->form->js_files []= 'number.js';

		$html .= '<script type="text/javascript">$(function () {new NumberManager("'.$this->name().'", '.(! is_null($this->upper_limit)?$this->upper_limit:'null').', '.(! is_null($this->lower_limit)?$this->lower_limit:'null').');})</script>';
		$html .= parent::printForm();
		return $html;
	}

}