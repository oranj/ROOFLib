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

class FI_Bool extends FormItem {


/**
 * Creates a new FI_Bool
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'mode'=>'radio', // Options are 'check', 'select', or 'radio'
			'yes'=>'Yes',
			'no'=>'No',
			'dependent_strings' => Array(),
			'dependents'=>Array(),
			'antidependents'=>Array(),
		);

		$this->merge($options, $defaultValues);

		$this->_value = false;
		if (isset($_POST[$this->name()])) {
			$this->value($_POST[$this->name()]);
		}

	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Bool";
 */
	public static function getType() {
		return "Bool";
	}

/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Bool $value The default true or false data; Default:false
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
		);
	}

/**
 * Gets or Sets the value of the FormItem
 *
 * @param Bool $input Providing an input indicates that the FormItem should be printed with that default.
 *
 * @return Bool If using this function as a Getter, gets the value of the item.
 */
	public function value($input = NULL){
		if ($input !== NULL) {
			$this->_value = (bool)$input;
		} else {
			return (bool)$this->_value;
		}
	}


	public function addToDB(&$dbForm) {
		$string = ($this->value()?'X':'');
		$dbForm->addItem($dbForm->dbName($this->label), $string);
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		$html  = '';
		$checked = $this->value();
		switch($this->mode) {
			case 'check':
				$html = '<label>'.$this->printPre().'<input type="checkbox" name="'.$this->name().'" value="1"'.($checked?' checked="checked"':'').' />'.$this->printPost().'</label>';
				break;
			case 'select':
				$nocheck = $checked?"":' selected="selected"';
				$yescheck = $checked?' selected="selected"':'';
				$html = '<select name="'.$this->name().'"><option value="0"'.$nocheck.'>'.$this->no.'</option><option value="1"'.$yescheck.'>'.$this->yes.'</option></select>';
				break;
			case 'radio':
				$nocheck = $checked?"":' checked="checked"';
				$yescheck = $checked?' checked="checked"':'';
				$html = '<label><input type="radio" name="'.$this->name().'" value="0"'.$nocheck.'/>'.$this->no.'</label><label><input type="radio" name="'.$this->name().'" value="1"'.$yescheck.'/>'.$this->yes.'</label>';
				break;
		}
		foreach ($this->dependents as $dependent) {
			$this->makeDependent($dependent);
		}
		foreach ($this->antidependents as $antidependent) {
			$this->makeDependent($antidependent, true);
		}
		$html .= $this->printDescription();
		$html .= '<script type="text/javascript">$(function() { '.join("\n", $this->dependent_strings).'} );</script>';
		return $html;
	}

	public function makeDependent($fi_id, $anti = false) {
		$condition = 'false';
		$bang = $anti?'!':'';
		$target = 'null';
			switch ($this->mode) {
			case 'check':
				$target = '$("[name='.$this->name().']")';
				$condition =  $bang.'($("[name='.$this->name().']").attr("checked"))';
				break;
			case 'select':
				$target = '$("[name='.$this->name().'] option")';
				$condition =  $bang.'($("[name='.$this->name().']:selected").attr("value"))';
				break;
			case 'radio':
				$target = '$("[name='.$this->name().']")';
				$condition =  $bang.'($("[name='.$this->name().']:checked").attr("value") == 1)';
				break;
		}
		$this->dependent_strings []= $target.'.change(function() { if('.$condition.') { $("#'.$this->cfg('prefix_id').$fi_id.'").css("display", "table-row"); } else { $("#css_'.$fi_id.'").css("display", "none"); } }).change();';
	}

/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail() {
		$html = $this->value()?'Yes':'No';
		return $html;
	}
}

?>