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
			'others' => Array(),
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
			$this->selected = NULL;//reset($this->options);
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
   			'others'=>self::DE('Array', 'List of options with string compoments', 'Array()'),
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
				foreach ($this->others as $name => $value) {
					$other_name = $this->name().'_'.$name;
					if (isset($_POST[$other_name])) {
						$this->selected = Array('name'=>$name, 'label'=>$value, 'value'=>$_POST[$other_name]);
					}
				}

				if (! $this->selected) {
					$this->selected = isset($_POST[$this->name()])?$_POST[$this->name()]:NULL;
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
		$value = $this->value();
		if (is_array($value)) {
			$value = $value['label'].':: '.$value['value'];
		}
		$dbForm->addItem($dbForm->dbName($this->label), $value);
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
		foreach ($this->others as $key => $label) {
			$id = $this->name().'_'.$key;
			if (is_array($selected_value) && $selected_value['name'] == $key) {
				$label = '<label for="'.$id.'">'.$label.' </label><input onclick="document.getElementById(\''.$id.'\').checked = true;" name="'.htmlentities($id).'" value="'.htmlentities($selected_value['value']).'" />';
				$input = '<input type="radio" id="'.$id.'" checked="checked" name="'.$this->name.'" value="'.htmlentities($id).'"/>';
			} else {
				$label = '<label for="'.$id.'">'.$label.' </label><input onclick="document.getElementById(\''.$id.'\').checked = true;" name="'.htmlentities($id).'" value="" />';
				$input = '<input type="radio" id="'.$id.'" name="'.$this->name.'" value="'.htmlentities($id).'"/>';
			}
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
		if (is_array($html)) {
			$html = '<em>'.$html['label'].':</em> '.$html['value'];
		}
		return $html;
	}
}

?>