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

class FI_Checkbox extends FormItem {

	protected $options;
	protected $selected;


/**
 * Creates a new FI_Checkbox
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);
		$defaultValues = Array(
			'options' => Array()
		);
		$this->merge($options, $defaultValues);
		$this->_update_selected_values();
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param String $options The list of available options; Default: Array();
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'options'=>self::DE('Array', 'The list of available options', 'Array()')
		);
	}


/**
 * Sets the internal data representation to match the values in the $_POST
 */
	protected function _update_selected_values() {
		$this->selected = Array();
		foreach ($this->options as $value => $label) {
			if (isset($_POST[$this->name().'_'.$value])) {
				$this->selected [$this->name().'_'.$value] = true;
			}
		}
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Checkbox";
 */
	public static function getType() {
		return "Checkbox";
	}


/**
 * Gets or Sets the value of the FormItem
 *
 * @param mixed $input A hash from the name to a boolean;
 *
 * @return Array The user's input- A hash from the name to a boolean integer
 */
	public function value($input = NULL){
		if ($input !== NULL) {
			if (is_array($input)) {
				foreach ($input as $name => $bool) {
					$this->selected[$this->name().'_'.$name] = (bool)$bool;
				}
			}
		} else {
			$this->_update_selected_values();
			$out = Array();
			foreach ($this->options as $value => $label) {
				$out[$value] = (int)(isset($this->selected[$this->name().'_'.$value]));
			}
			return $out;
		}
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		$html  = '';
		foreach ($this->options as $value => $label) {
			$id = $this->name().'_'.$value;
			$html .= '<div><input type="checkbox" id="'.$id.'" name="'.$id.'" value="'.$value.'" '.((isset($this->selected[$id]) && $this->selected[$id])?' checked':'').'/><label for="'.$id.'">'.$label.'</label></div>'."\n";
		}
		return $html;
	}

/**
 * Adds the form info to the DatabaseForm object()
 *
 * @param DatabaseForm $dbForm The DatabaseForm to add fields to
 */

	public function addToDB(&$dbForm) {
		$values = $this->value();
		foreach ($this->options as $value => $label) {
			$dbForm->addItem($dbForm->dbName($label), (isset($values[$this->name().'_'.$value])?'X':''));
		}
	}


/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail () {
		$html = '';
		$selected_values = $this->value();
		foreach ($this->options as $value => $label) {
			$id = $this->name().'_'.$value;
			if (isset($selected_values[$id])) {
				$html .= '<div>'.$label.'</div>';
			}
		}
		if (! $html) {
			$html .= '<em>None</em>';
		}
		return $html;
	}

}


?>