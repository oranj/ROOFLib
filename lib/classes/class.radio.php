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

class FI_Radio extends FormItem {

	protected $options;
	protected $selected;


/**
 * Creates a new FI_Radio
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'options' => Array(),
			'label_left' => false,
			'other' => false,
			'other_label' => 'Other',
			'other_name' => 'rf_other',
		);

		$this->merge($options, $defaultValues);
		$this->_update_selected();
	}


/**
 * Updates the internal representation of the value according to the $_POST values.
 */
	protected function _update_selected() {
		if (isset($_POST[$this->name()])) {
			$this->selected = $this->options[$_POST[$this->name()]];
		} else {
			$this->selected = reset($this->options);
		}
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Array $options The list of available options; Default:Array()
 * @param Bool $label_left Make the labels appear to the left of the radio; Default:false
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'options'=>self::DE('Array', 'The list of available options', 'Array()'),
			'other'=>self::DE('bool', 'Whether or not to display an "other" field', 'false'),
			'other_label'=>self::DE('Array', 'The default "Other" label', '"Other"'),
			'label_left'=>self::DE('bool', 'Make the labels appear to the left of the radio', 'false'),
		);
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Radio";
 */
	public static function getType() {
		return "Radio";
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
			$this->selected = $this->options[$input];
		} else {
			if ($_POST) {
				if ($this->other && ($_POST[$this->name] == $this->other_name)) {
					$this->selected = "[".$this->other_label."] ".$_POST[$this->name().'_other'];
				} else {
					$this->selected = $_POST[$this->name()];
				}
			}
			return $this->selected;
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
		$html  = '';
		$selected_value = $this->value();
		$html .= $this->printPre();
		foreach ($this->options as $value => $label) {
			$id = $this->name().'_'.$value;
			$label = '<label for="'.$id.'">'.$label.'</label>';
			$input = '<input type="radio" id="'.$id.'"  '.(($selected_value == $value)?(' checked="checked" '):('')).'name="'.$this->name.'" value="'.$value.'"/>';
			$html .= '<div class="radio">'.($this->label_left?($label.$input):($input.$label)).'</div>';
		}
		if ($this->other) {
			$id = $this->name().'_other';
			$label = '<label for="'.$id.'">'.$this->other_label.' <input name="'.$id.'" value="'.$vaue.'" /></label>';
			$input = '<input type="radio" id="'.$id.'"  '.(($selected_value == $value)?(' checked="checked" '):('')).'name="'.$this->name.'" value="'.htmlentities($this->other_name).'"/>';
			$html .= '<div class="radio">'.($this->label_left?($label.$input):($input.$label)).'</div>';
		}
		$html .= $this->printPost();
		$html .= $this->printDescription();
		return $html;
	}


/**
 * Performs native validation within the FormItem.
 *
 * @param Array $errors An array in which to place errors
 * @param Array $warnings An array in which to place warnings
 * @param Bool $continue A Bool to indicate whether or not the containing FI_Group or Form should break upon completion
 */
	public function check(&$errors, &$warnings, &$continue) {
		return parent::check($errors, $warnings, $continue);
	}


/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail() {
		$html = $this->value();
		return $html;
	}
}

?>