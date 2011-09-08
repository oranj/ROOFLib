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

require_once('class.text.php');

class FI_Password extends FI_Text {

/**
 * Creates a new FI_Password
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */

	public function __construct($name, $label, $options = Array()) {
		$defaultValues = Array(
			'email'=>false,
		);
		parent::__construct($name, $label, $options);
		$this->merge($options, $defaultValues);
	}
/**
 * Gets the type of the FormItem
 *
 * @return String "Password";
 */
	public static function getType() {
		return "Password";
	}


/**
 * Performs native validation within the FormItem.
 *
 * @param Array $errors An array in which to place errors
 * @param Array $warnings An array in which to place warnings
 * @param Bool $continue A Bool to indicate whether or not the containing FI_Group or Form should break upon completion
 */
	public function check(&$errors, &$warnings, &$continue) {
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		return '<input type="password" '.($this->required()?'required ':'') .'name="'.$this->name().'" value="'.$this->value().'"/>';
	}

}