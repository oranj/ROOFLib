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

class FI_Text extends FormItem {

	protected $_value;


/**
 * Creates a new FI_Text
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
		);

		$this->merge($options, $defaultValues);
		$this->_update_value();
	}


/**
 * Updates the internal representation of the value according to the $_POST values.
 */
	protected function _update_value() {
		if (isset($_POST[$this->name])) {
			$this->value(stripslashes($_POST[$this->name]));
		}
	}


/**
 * Gets or sets the form item's name
 *
 * @param String $name The form item's name
 *
 * @return String the form item's name.
 */
	public function name($name = false) {
		$out = parent::name($name);
		$this->_update_value();
		return $out;
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Text";
 */
	public static function getType() {
		return "Text";
	}

/**
 * Gets or Sets the value of the FormItem
 *
 * @param String $input Providing an input indicates that the FormItem should be printed with that default.
 *
 * @return Array If using this function as a Getter, gets the value of the item.
 */
	public function value($input = NULL) {
		if ($input !== NULL) {
			$this->_value = $input;
		} else {
			return $this->_value;
		}
	}

/**
 * Adds the form item to the database.
 *
 * @param DatabaseForm $form The DatabaseForm
 */
	public function addToDB(&$dbForm) {
		$dbForm->addItem($dbForm->dbName($this->label), $this->value());
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		$value = $this->value();
		$html = $this->printPre().'<input type="text" name="'.$this->name().'"'.($this->required()?' required':'').' value="'.htmlentities($this->value()).'" />'.$this->printPost().$this->printDescription();
		return $html;
	}

/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail() {
		return $this->value();
	}
}