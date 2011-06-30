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

class FI_Password extends FI_Text {

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