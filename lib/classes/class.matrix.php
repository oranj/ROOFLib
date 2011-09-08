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

class FI_Matrix extends FormItem {


/**
 * Creates a new FormItem
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'data' => Array(),
			'status' => Array('false', 'true'),
			'selected' => Array()
		);

		$this->merge($options, $defaultValues);
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Matrix";
 */
	public static function getType() {
		return "Matrix";
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Array $data An array representing the grid structure- 2d or 1d; Default:Array()
 * @param Array $status A list of values available in the data (in sequential order of clicking); Default:Array('false', 'true')
 * @param Array $selected The default values; Default:Array()
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'data'=>self::DE('array', 'An array representing the grid structure- 2d or 1d', 'Array()'),
			'status'=>self::DE('array', 'A list of values available in the data (in sequential order of clicking)', 'Array(\'false\', \'true\')'),
			'selected'=>self::DE('array', 'The default values.', 'Array()'),
		);
	}


/**
 * Performs native validation within the FormItem.
 *
 * @param Array $errors An array in which to place errors
 * @param Array $warnings An array in which to place warnings
 * @param Bool $continue A Bool to indicate whether or not the containing FI_Group or Form should break upon completion
 */
	public function check(&$errors, &$warnings, &$continue) {
	}


/**
 * Gets or Sets the value of the FormItem
 *
 * @param Bool $input Providing an input indicates that the FormItem should be printed with that default.
 *
 * @return Bool If using this function as a Getter, gets the value of the item.
 */
	public function value($input = NULL) {
		if ($input === NULL) { // GET
			$value = isset($_POST[$this->name()])?((get_magic_quotes_gpc())?(stripslashes($_POST[$this->name])):$_POST[$this->name]):'';
			$values = Array();
			preg_match_all('/\[(.*?)\]/', $value, $matches);
			foreach ($matches[1] as $match) {
				$_values = (preg_split('/;/', $match));
				$__v = Array();
				foreach ($_values as $data) {
					$__v []= $this->status[$data];
				}
				$values []= $__v;
			}
			return $values;
		} else {
			$this->selected = $input;
		}
	}


/**
 * Prints the Form Item for the Form
 *
 * @return String The HTML string
 */
	public function printForm() {
		$this->form->js_files []= 'matrix.js';
		$js = '<script type="text/javascript">';
		$options = Array();
		$html .= '<input id="'.$this->name().'_hi" name="'.$this->name().'" value="" type="hidden"/>';
		$html .= '<table>';
		foreach ($this->data as $index => $row) {
			$html .= '<tr>';
			foreach ($row as $id => $value) {
				$id = $this->name().'_mc-r-'.$index.'c-'.$id;
				$options []= $id;
				$html .= '<td style="text-align:center"><div row="'.$index.'" col="'.$id.'" class="fi_mc" id="'.$id.'">'.$value .'</div></td>';
			}
			$html .= '</tr>';
		}

		$row_strs = Array();
		$inv_status = array_flip($this->status);
		foreach ($this->selected as $row => $data) {
			foreach ($data as $col => $status) {
				$id = $this->name().'_mc-r-'.$row.'c-'.$col;
				$row_strs []= '"'.$id.'":'.$inv_status[$status];
			}
		}

		$js .= '$(function () { new MatrixController(\''.$this->name().'\', ["'.join('", "', $options).'"], ["'.join('", "', $this->status).'"], {'.join(',', $row_strs).'});});</script>';
		$html .= '</table>'.$js;
		return $html;
	}
}