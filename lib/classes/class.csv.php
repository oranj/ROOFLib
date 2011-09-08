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

class FI_CSV extends FI_Text {

	protected $wysiwyg;


/**
 * Creates a new FI_CSV
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
		);
	}



/**
 * Gets the type of the FormItem
 *
 * @return String "CSV"
 */
	public static function getType() {
		return "CSV";
	}


/**
 * Gets or Sets the value of the FormItem
 *
 * @param Array $input
 *
 * @return Array The list of Values
 */
	public function value($input = NULL) {
		if (is_null($input)) {
			return preg_split('/[\s\,]+/', $this->_value);
		} else {
			if (is_array($input)) {
				$this->_value = join(', ', $input);
			} else {
				$this->_value = $input;
			}
		}
	}


/**
 * Description for function printForm()
 *
 * @return
 */
	public function printForm() {
		return '<textarea cols="40" rows="4" '.($this->required()?'required ':'') .'name="'.$this->name().'">'.$this->_value.'</textarea>'.$this->printDescription();
	}
}