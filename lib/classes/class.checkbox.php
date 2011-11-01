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
			'options' => Array(),
			'desc_in_label'=>true,
			'label_left' => false,
   			'other' => false,
			'other_label' => 'Other',
			'other_name' => 'other',
			'db_join' => false,
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
			'options'=>self::DE('Array', 'The list of available options', 'Array()'),
   			'other'=>self::DE('Bool', 'Whether or not to display the "Other" field', 'false'),
   			'other_label'=>self::DE('String', 'The default "Other" label', '"Other"'),
   			'other_name'=>self::DE('String', 'The reserved name for the post variable', '"other"'),
			'label_left'=>self::DE('bool', 'Make the labels appear to the left of the radio', 'false'),
			'db_join'=>self::DE('bool', 'When set to true, concatenates values in the database', 'false'),
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
			if ($this->other) {
				$other_name = $this->name().'_'.$this->other_name;
				foreach ($_POST[$other_name] as $val) {
					if ($val != $other_name) {
						$out[$this->other_name] = $val;
					}
				}
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

		if ($this->other) {
			$post = $this->name().'_'.$this->other_name;
			$value = '';
			if (isset($_POST[$post])) {
				foreach ($_POST[$post] as $value) {
					if ($value != $post) {
						break;
					}
				}
			}

			$id = $this->name().'_'.$this->other_name;
			$label = '<label for="'.$id.'">'.$this->other_label.'</label> <input onclick="document.getElementById(\''.$id.'\').checked=\'checked\';" name="'.$id.'[]" value="'.$value.'" />';
			$input = '<input type="checkbox" id="'.$id.'" '.((isset($this->selected[$id]))?(' checked="checked" '):('')).'name="'.$id.'[]" value="'.htmlentities($value).'" />';
			$html .= '<div class="checkbox">'.($this->label_left?($label.$input):($input.$label)).'</div>';
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
		if ($this->db_join) {
			$save = Array();
			foreach ($this->options as $value => $label) {
				if ($values[$value]) {
					$save []= $label;
				}
			}
			if ($this->other && $values[$this->other_name]) {
				$save []= "[".$this->other_label."] ".$values[$this->other_name];
			}

			$dbForm->addItem($dbForm->dbName($this->label()), join(" | ", $save));
		} else {
			foreach ($this->options as $value => $label) {
				$dbForm->addItem($dbForm->dbName($label), ($values[$value]?'X':''));
			}
		}
	}


/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail () {
		$html = '';
		$values = $this->value();
		foreach ($this->options as $value => $label) {
			if ($values[$value]) {
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