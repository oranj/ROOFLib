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

require_once('class.formitem.php');

class FI_Script extends FormItem {

	protected $value;
	protected $script;


/**
 * Creates a new FI_Script
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);
		$defaultValues = Array(
			'value' => '',
			'script' => 'alert("Hello World"); return 12;'
		);
		$this->merge($options, $defaultValues);
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param String $script A sequence of Javascript commands which should return the desired value; Default: 'alert("Hello World"); return 12;'),
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'script'=>self::DE('string', 'A sequence of Javascript commands which should return the desired value.', 'alert("Hello World"); return 12;'),
		);
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Script";
 */
	public static function getType() {
		return "Script";
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
			return trim($this->value);
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
 * Prints the form item including label and input.
 *
 * @return String The HTML string.
 */
	public function printRow() {
		$id = 'scr_id_'.$this->name();
		$fun = 'scr_fn_'.$this->name();
		$html .= '<input type="hidden" id="'.$id.'" name="'.$this->name().'" value="" />'."\n";
		$html .= '<script type="text/javascript"> function '.$fun.'() { '.$this->script.'} $("#'.$id.'").attr("value", '.$fun.'()); </script>';

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