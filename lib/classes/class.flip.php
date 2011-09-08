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

require_once('class.select.php');

class FI_Flip extends FI_Select {

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
			'show_default'=>false,
			'inc_text'=>'[+]',
			'dec_text'=>'[-]',
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
		return "Flip";
	}


/**
 * Prints the Javascript necessary to update the select into the flip
 *
 * @return String "Text";
 */
	public function print_js() {
		$this->form->js_files []= 'flip.js';
		$js = '<script>$(new FlipController("'.$this->name().'", "'.$this->inc_text.'", "'.$this->dec_text.'"));</script>';
		return $js;
	}

/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		$html = parent::printForm();
		$html .= $this->print_js();
		return $html;
	}
}