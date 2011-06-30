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

require_once(dirname(__FILE__).'/class.form.php');

class FI_Toggle extends FI_Group {


/**
* Creates a new FI_Toggle
*
* @param String $name The name of the form item. Must be unique within the group or form.
* @param String $label The label of the form item as printed to the page.
* @param Array $options An array of parameters and their values. See description()
*/
	function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'selected'=>NULL,
		);

		$this->items = Array();
		$this->merge($options, $defaultValues);
	}


/**
* Gets the type of the FormItem
*
* @return String "Toggle";
*/
	public static function getType() {
		return "Toggle";
	}

/**
* Gets or Sets the value of the FormItem
*
* @param String $input Providing an input indicates that the FormItem should be printed with that default.
*
* @return Array If using this function as a Getter, gets the value of the item.
*/
	public function value($input = NULL) {
		if ($input === NULL) { // get
			$out = parent::value();

			$selected = (get_magic_quotes_gpc())?(stripslashes($_POST[$this->name()])):$_POST[$this->name()];
			$matches = preg_split('/_/', $selected);
			$selected_index = array_pop($matches);
			return Array($selected_index => $out[$selected_index]);
		} else {
			$this->selected = key($input);
			parent::value($input);
		}
	}



/**
* Prints the form item including label and input.
*
* @param Bool $email Indicates if this is being printed for email purposes
* @param Bool $nameAbove Indicates if this is being printed in div layout.
*
* @return String The HTML string.
*/
	public function printRow($email = false, $nameAbove = false) {
		global $TOGGLE_JS_INCLUDED;
		$html = '';
		$js = '';
		if (! $TOGGLE_JS_INCLUDED) {
			$TOGGLE_JS_INCLUDED = true;
			$js = '

var _ToggleControllers = Array();

function ToggleController(id, options, start) {
	this.id 		= id;
	this.options 	= options;

	this.switch = function(name) {
		for (var i in this.options) {
			$("#"+this.options[i]).find(":input").attr("disabled", "disabled");
			$("#"+this.options[i]).find(".css_fi_matrix").attr("disabled", "disabled");
		}
		$("#"+name).find(":input").removeAttr("disabled");
		$("#"+name).find(".css_fi_matrix").removeAttr("disabled");
	}
	_ToggleControllers[this.id] = this;
	var value = $(\'[name=\'+id+\']:checked\').val();
	_start = value+\'_tci\';
	this.switch(_start);
}
';
		}

		$selected = is_null($this->selected)?'':($this->name().'_'.$this->selected);
		$selected_id = $selected?reset($this->items[$selected]->attr('id')):'';


		$options = Array();
		foreach ($this->items as $name => $fi_item) {
			$eid = $name.'_tci';
			$rid = $name.'_tcr';
			$options []= $eid;
			$html .= '<tr '.$fi_item->attrString().'><td class="fldName"><input id="'.$rid.'" type="radio" onchange = "_ToggleControllers[\''.$this->name().'\'].switch(\''.$eid.'\');" name="'.$this->name().'" value="'.$name.'"'.($name == $selected?'checked="checked"':'').'/><label for="'.$rid.'">'.$fi_item->label().'</label></td>';
			$fi_html = $fi_item->printRow(false, true);
			$html .= '<td class="fldValue" id="'.$eid.'">'.$fi_html.'</td></tr>';
			$first = (isset($first)?$first:$eid);
		}
		$js .= '$(function () { new ToggleController("'.$this->name().'", ["'.join('", "', $options).'"], "'.($selected?$selected_id:$first).'") });';
		$a = $this->attrString();
		$html = '<script type="text/javascript">'.$js.'</script>'.($nameAbove?'<div '.$a.'>':'<tr '.$a.'><td colspan="2">').'<table>'.$html.'</table>'.($nameAbove?'</div>':'</td></tr>');
		return $html;
	}
}
?>
