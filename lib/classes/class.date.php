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

class FI_Date extends FI_Text {

/**
 * Creates a new FI_Date
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		$defaultValues = Array(
			'src'=>DATE_TIME_PICKER_SRC
		);
		parent::__construct($name, $label, $options);
		$this->merge($options, $defaultValues);
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Date";
 */
	public static function getType() {
		return "Date";
	}



/**
 * Formats and prints the internal timestamp
 *
 * @return String Formatted Date String.
 */
	public function printDate() {
		if (is_numeric($this->_value) && (int)$this->_value > 0) {
			return date('m/d/Y h:i a', (int)$this->_value);
		} else {
			return date('m/d/Y h:i a'); // show now by default;
		}
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Text $src The Source of the jQueryUI Datepicker script; Default:DATE_TIME_PICKER_SRC
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description() {
		$description = Array(
			'src'=>self::DE('text', 'The Source of the jQueryUI Datepicker script', 'DATE_TIME_PICKER_SRC')
		);
		return $description;
	}


/**
 * Gets or Sets the value of the FormItem
 *
 * @param Bool $input Providing an input indicates that the FormItem should be printed with that default.
 *
 * @return Bool If using this function as a Getter, gets the value of the item.
 */
	public function value($input = NULL) {
		if ($input === NULL) { // Get
			return $this->_value;
		} else { // SET
			if (is_numeric($input)) { //- assume a timestamp
				$this->_value = (int)$input;
			} else {
				$this->_value = strtotime($input);
			}
		}
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		global $dt_picker_included;
		$using_ie6 = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.') !== FALSE);
		$html = '';
		if (! $dt_picker_included) {
			if (! $using_ie6) {
				$html .= '<script type="text/javascript" src="'.$this->src.'"></script>';
			}
			$dt_picker_included = true;
		}
		$html .= $this->printPre().'<input id="'.$this->name().'_in" type="text" name="'.$this->name().'"'.($this->required()?' required':'').' value="'.htmlentities($this->printDate($this->value())).'" />'.$this->printPost();

		if ($this->description) {
			$html .= '<div class="descr">'.$this->description.'</div>';
		}
		if (! $using_ie6) {
			$html .= '<script type="text/javascript">$("#'.$this->name().'_in").datetimepicker({ampm: true});</script>';
		}
		return $html;
	}

}