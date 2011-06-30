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
?><?php

require_once('class.checkbox.php');

class FI_MultiCheck extends FI_Checkbox {

	protected $options;
	protected $selected;


/**
 * Creates a new FI_Multicheck
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);
		$defaultValues = Array(
			'columns'=>2,
			'sort'=>'asc', // asc, desc, or NULL by label
			'direction'=>'vertical' // horizontal or vertical-> the ordering of the values
		);
		$this->merge($options, $defaultValues);

	}

/**
 * Gets the type of the FormItem
 *
 * @return String "MultiCheck";
 */
	public static function getType() {
		return "MultiCheck";
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Number $columns The number of vertical columns of checkboxes to display; Default:2
 * @param Number $sort The order to sort the options by (options are asc, desc, or NULL by the label); Default:'asc'
 * @param Number $direction The direction to sort the options by (options are horizontal or vertical); Default:'vertical'
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'columns'=>self::DE('integer', 'The number of vertical columns of checkboxes to display', '2'),
			'sort'=>self::DE('string', 'The order to sort the options by (options are asc, desc, or NULL by the label)', '\'asc\''),
			'direction'=>self::DE('string', 'The direction to sort the options by (options are horizontal or vertical)', '\'vertical\''),
		);
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		$html  = '';

		$values = Array();
		for ($i = 0; $i < $this->columns; $i++) {
			$values[$i] = Array();
		}

		$options = $this->options;

		if ($this->sort == 'asc') {
			asort($options);
		} else if ($this->sort == 'desc') {
			arsort($options);
		}

		if ($this->direction == 'horizontal') {
			$column = 0;
			foreach ($options as $value => $label) {
				$values[$column][$value] = $label;
				$column = ($column + 1) % $this->columns;
			}
		} else {
			$count = sizeof($options);
			$col_size = ceil($count / $this->columns);
			$column = 0;
			$i = 0;
			foreach ($options as $value => $label) {
				$values[$column][$value] = $label;
				$i++;
				$column = floor($this->columns * $i / $count);
			}
		}

		$html .= '<table><tr>';

		for ($i = 0; $i < $this->columns; $i++) {
			$html .= '<td>';
				foreach ($values[$i] as $value => $label) {
					$id = $this->name().'_'.$value;
					$html .= '<div><input type="checkbox" id="'.$id.'" name="'.$id.'" value="'.$value.'" '.((isset($this->selected[$id]) && $this->selected[$id])?' checked':'').'/><label for="'.$id.'">'.$label.'</label></div>'."\n";
				}
			$html .= '</td>';
		}
		$html .= '</tr></table>';

		return $html;
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
			if (isset($selected_values[$value]) && $selected_values[$value]) {
				$html .= $label."<br/>\n";
			}
		}
		if (! $html) {
			$html .= '<em>None</em>';
		}

		return $html;
	}

}


?>