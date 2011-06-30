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

class FI_Select extends FormItem {

	protected $options;
	protected $selected;



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
			'options' => Array(),
			'size'=>1,
			'default'=>'-1',
			'default_string'=>' - Please Choose - ',
			'show_default'=>true,
		);

		$this->merge($options, $defaultValues);

		$this->_update_selected();
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Array $options The list of available options; Default:Array()
 * @param String $default The index to be counted as the default value. In required select boxes, this value would not pass validation; Default:-1
 * @param String $default_string The prompt to display when no option is selected; Default:' - Please Choose - '
 * @param Bool $show_default Whether or not to display a select prompt; Default:true;
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'options'=>self::DE('array', 'The list of available options', 'Array()'),
			'default'=>self::DE('string', 'The index to be counted as the default value. In required select boxes, this value would not pass validation', '-1'),
			'default_string'=>self::DE('string', 'The prompt to display when no option is selected', '\' - Please Choose - \''),
			'show_default'=>self::DE('bool', 'Whether or not to display a select prompt', 'true'),
		);
	}


/**
 * Updates the internal representation of the value according to the $_POST values.
 */
	protected function _update_selected() {
		if (! $this->selected) {
			$this->selected = ($this->show_default?$this->default:key($this->options));
		}
		if (isset($_POST[$this->name()])) {
			$this->selected = $_POST[$this->name()];
		}
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Select";
 */
	public static function getType() {
		return "Select";
	}


/**
 * Gets or Sets the value of the FormItem
 *
 * @param String $input Providing an input indicates that the FormItem should be printed with that default.
 *
 * @return String If using this function as a Getter, gets the value of the item.
 */
	public function value($input = NULL){
		if ($input !== NULL) {
			$this->selected = $input;
		} else {
			$this->_update_selected();
			return $this->selected;
		}
	}


/**
 * Performs native validation within the FormItem.
 *
 * @param Array $errors An array in which to place errors
 * @param Array $warnings An array in which to place warnings
 * @param Bool $continue A Bool to indicate whether or not the containing FI_Group or Form should break upon completion
 */
	public function check(&$errors, &$warnings, &$continue) {
		$value = $this->value();
		if ($this->default == $value && $this->required) {
			$errors []= 'Please enter a value for field: <em>'.$this->label().'</em>';
		}
		parent::check($errors, $warnings, $continue);
	}


/**
 * Adds the form item to the database.
 *
 * @param DatabaseForm $form The DatabaseForm
 */
	public function addToDB(&$dbForm) {
		$dbForm->addItem($dbForm->dbName($this->label), $this->options[$this->value()]);
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		$html  = '';
		$selected_value = $this->value();
		$html .= $this->printPre().'<select name="'.$this->name().'" size="'.$this->size.'">'."\n";
		if ($this->show_default) {
			$id = $this->name().'_'.$this->default;
			$html .= "\t".'<option '.(((string)$selected_value === (string)$this->default)?' selected="yes"':'').' value="'.$this->default.'">'.$this->default_string.'</option>'."\n";
		}
		foreach ($this->options as $value => $label) {
			$id = $this->name().'_'.$value;
			$html .= "\t".'<option '.(((string)$selected_value === (string)$value)?' selected="yes"':'').' value="'.$value.'">'.$label.'</option>'."\n";
		}
		$html .= '</select>'.$this->printPost();
		if ($this->description) {
			$html .= '<div class="descr">'.$this->description.'</div>';
		}

		return $html;
	}

/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail() {
		$html = $this->options[$this->value()];
		return $html;
	}


}


?>