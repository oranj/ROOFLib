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

class FI_Phone extends FI_Text {

/**
 * Creates a new FI_Password
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */

	public function __construct($name, $label, $options = Array()) {
		$defaultValues = Array(
			'mask'=>'phone-us',
		);

		parent::__construct($name, $label, $options);
		$this->merge($options, $defaultValues);
	}

/**
 * Gets the type of the FormItem
 *
 * @return String "Phone";
 */
	public static function getType() {
		return "Phone";
	}

/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		$this->form->js_files []= 'jquery.meio.mask.js';

		return $this->printPre().'<input type="text" rel="meio_mask" alt="'.$this->mask.'" '.($this->required() && ($this->required_attr || $this->form->required_attr)?'required ':'') .'name="'.$this->name().'" value="'.$this->value().'"/>'.$this->printPost().$this->printDescription();
	}
}