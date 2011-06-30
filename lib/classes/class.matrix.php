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
		global $MATRIX_JS_INCLUDED;
		$js = '<script type="text/javascript">';
		if (! $MATRIX_JS_INCLUDED) {
			$js .= '
jQuery.fn.exists = function(){return jQuery(this).length>0;}


var _MatrixControllers = Array();

function MatrixController(id, options, status, selected) {
	this.id = id;
	this.options = options;
	this.status = status;
	this.hidden = $("#"+id+"_hi");

	for(var i in options) {
		var o = $("#"+options[i]);
		o.attr("index", i);
		o.attr("status", 0);
		o.click(function() { _MatrixControllers[id].click(this); });
		o.addClass("mc_"+this.status[0]);
	}

	for(var i in selected) {
		var o = $("#"+i);
		o.attr("status", selected[i]);
		o.removeClass("mc_"+this.status[0]);
		o.addClass("mc_"+this.status[selected[i]]);
	}

	this.click = function(o) {
		var index = $(o).attr("index");
		var old_status = Math.floor($(o).attr("status"));
		var next_status = (old_status + 1);//
		if (next_status >= this.status.length) { next_status -= this.status.length; }
		$(o).attr("status", next_status);
		var old_class = "mc_"+this.status[old_status];
		var new_class = "mc_"+this.status[next_status];
		$(o).removeClass(old_class);
		$(o).addClass(new_class);
		this.update();
	}

	this.update = function () {
		var matrix = new Array();
		var row;
		var col;
		var o;
		for (var i in this.options) {
			o = $("#"+options[i]);
			row = o.attr("row");
			col = o.attr("col");
			if (! matrix[row]) {
				matrix[row] = new Array();
			}
			matrix[row][col] = o.attr("status");
		}
		var str = "";
		for (var row in matrix) {
			str += "[";
			var first = true;
			for (var col in matrix[row]) {
				if (! first) {
					str += ";";
				}
				str += matrix[row][col];
				first = false;
			}
			str += "]";
		}

		this.hidden.val(str);
	}

	this.update();

	_MatrixControllers[this.id] = this;
}

';


			$MATRIX_JS_INCLUDED = true;
		}
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