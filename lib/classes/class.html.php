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

class FI_HTML extends FormItem {


/**
 * Creates a new FormItem
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);
		$defaultValues = Array(
			'html' => ''
		);
		$this->merge($options, $defaultValues);
	}


/**
 * Description for function getType()
 *
 * @return String "HTML"
 */
	public static function getType() {
		return "HTML";
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param String $html The text to display; Default:''
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'html'=>self::DE('text', 'The text to display.', '\'\''),
		);
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
 * Prints the form item including label and input.
 *
 * @param Bool $email Indicates if this is being printed for email purposes
 * @param Bool $nameAbove Indicates if this is being printed in div layout.
 *
 * @return String The HTML string.
 */
	public function printRow($email = false, $nameAbove = false) {
		if ($email && ! $this->email) {
			return '';
		}
		if ($nameAbove) {
			return '<div '.$this->attrString().'>'.$this->html.'</div>'."\n";
		} else {
			return '<tr '.$this->attrString().'><td colspan="2">'.$this->html.'</td></tr>'."\n";
		}
	}
}