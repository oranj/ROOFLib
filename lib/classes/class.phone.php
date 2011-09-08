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
		return $this->printPre().'<input type="tel" '.($this->required() && ($this->required_attr || $this->form->required_attr)?'required ':'') .'name="'.$this->name().'" value="'.$this->value().'"/>'.$this->printPost().$this->printDescription();
	}
}