<?php
/**
 * ROOFLib
 * Version 0.4
 * Copyright 2011, Ecreativeworks
 * Raymond Minge
 * rminge@ecreativeworks.com
 *
 * @package ROOFLib 0.4
 */

require_once('class.formitem.php');

class FI_Bool extends FormItem {


/**
 * Creates a new FI_Bool
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'value'=>false
		);

		$this->merge($options, $defaultValues);
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Bool";
 */
	public static function getType() {
		return "Bool";
	}

/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Bool $value The default true or false data; Default:false
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'value'=>self::DE('bool', 'The default true or false data', false)
		);
	}

/**
 * Gets or Sets the value of the FormItem
 *
 * @param Bool $input Providing an input indicates that the FormItem should be printed with that default.
 *
 * @return Bool If using this function as a Getter, gets the value of the item.
 */
	public function value($input = NULL){
		if ($input !== NULL) {
			$this->value = (bool)$input;
		} else {
			return (bool)$this->value;
		}
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		$html  = '';
		$checked = $this->value();
		$html = '<input type="checkbox" name="'.$this->name().'" value="true"'.($checked?' checked="checked"':'').' />';
		$html .= $this->printDescription();
		return $html;
	}



/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail() {
		$html = $this->value()?'Yes':'No';
		return $html;
	}
}

?>