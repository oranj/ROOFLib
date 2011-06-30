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
		return $this->printPre().'<input autocomplete="off" type="tel" '.($this->required()?'required ':'') .'name="'.$this->name().'" value="'.$this->value().'"/>'.$this->printPost().$this->printDescription();
	}
}