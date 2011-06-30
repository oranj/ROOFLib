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

class FI_Hidden extends FormItem {

	protected $value;

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
			'value' => ''
		);
		$this->merge($options, $defaultValues);
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Hidden";
 */
	public static function getType() {
		return "Hidden";
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param String $text The value that will be submitted to the form; Default:''
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'value'=>self::DE('text', 'The value that will be submitted to the form', '\'\''),
		);
	}


/**
 * Gets or Sets the value of the FormItem
 *
 * @param String $input Providing an input indicates that the FormItem should be printed with that default.
 *
 * @return String If using this function as a Getter, gets the value of the item.
 */
	public function value($input = NULL) {
		if ($input !== NULL) {
			$this->value = $input;
		} else {
			if ($_POST[$this->name()]) {
				return trim($_POST[$this->name()]);
			}
			return trim($this->value);
		}
	}


/**
 * Adds the form info to the DatabaseForm object()
 *
 * @param DatabaseForm $dbForm The DatabaseForm to add fields to
 */
	public function addToDB(&$dbForm) {
		$dbForm->addItem($dbForm->dbName($this->label), $this->value());
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
 * @return String The HTML string.
 */
	public function printRow() {
		return '<input type="hidden" name="'.$this->name().'" value="'.htmlentities($this->value()).'" />'."\n";
	}


/**
 * Prints the Form Item for the Form
 *
 * @return String the HTML formatted string.
 */
	public function printForm() {
		return '';
	}


/**
 * Prints the Form Item for Email
 *
 * @return String the HTML formatted string.
 */
	public function printEmail() {
		return $this->value();
	}

}