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

class FI_Group extends FormItem {

	protected $items;
	protected $field_tag;
	protected $label_tag;

	protected $name_cache;
	protected $forceChildNameAbove;


/**
 * Creates a new FI_Group
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'field_tag' => 'fieldset',
			'label_tag' => 'legend',
			'forceChildNameAbove' => false
		);


		$this->items = Array();
		$this->name_cache = Array();



		$this->merge($options, $defaultValues);

	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param String $field_tag The tag that will wrap the group; Default:'fieldset'
 * @param String $label_tag The tag that will wrap the label; Default:'legend'
 * @param String $forceChildNameAbove Force all child elements to print in a div layout rather than tabular layout; Default: 'false'
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'field_tag'=>self::DE('integer', 'The tag that will wrap the group', 'fieldset'),
			'label_tag'=>self::DE('text', 'The tag that will wrap the label', 'legend'),
			'forceChildNameAbove'=>self::DE('bool', 'Force all child elements to print in a div layout rather than tabular layout', 'false'),
		);
	}



/**
 * Gets the type of the FormItem
 *
 * @return String "Group";
 */
	public static function getType() {
		return "Group";
	}


/**
 * Adds an item to the Group
 *
 * @param FormItem $formItem The item to add to the form
 */
	public function addItem($formItem) {
		$old_name = $formItem->name();
		$new_name = $this->name . '_' . $old_name;

		$this->name_cache[$new_name] = $old_name;
		$formItem->name($new_name);

		if (isset($this->items[$formItem->name()])) {
			return false; // Duplicate name
		}
		$this->attr('id', 'css_'.strtolower($this->name()), true);
		$this->items[$formItem->name()] = $formItem;
	}


/**
 * Adds the Child form items to the database.
 *
 * @param DatabaseForm $form The form which contains the database.
 */
	public function addToDB(&$dbForm) {
		foreach ($this->items as $name => $item) {
			$item->addToDB($dbForm);
		}
	}


/**
 * Gets an item by name
 *
 * @param String $name The name of the Item
 *
 * @return FormItem the Form Item to get
 */
	public function getItem($name) {
		$real_name = $this->name . '_' . $name;
		if (isset($this->items[$real_name])) {
			return $this->items[$real_name];
		}
	}


/**
 * Add files of sub items to the form for email purposes.
 *
 * @param Array &$files A hash of files from path to name.
 */
	public function addFiles(&$files) {
		foreach ($this->items as $name => $item) {
			$item->addFiles($files);
		}
	}


/**
 * Recursively checks all form items.
 *
 * @param Array $errors An array in which to place errors
 * @param Array $warnings An array in which to place warnings
 * @param Bool $continue A Bool to indicate whether or not the containing FI_Group or Form should break upon completion
 */
	public function check(&$errors, &$warnings, &$continue) {
		foreach ($this->items as $item) {
			if (! $continue) {
				break;
			}
			$item->check($errors, $warnings, $continue);
		}
	}


/**
 * Gets or sets the value of the form item.
 *
 * @param mixed $input The input.
 *
 * @return mixed The output
 */
	public function value($input = NULL) {
		if ($input !== NULL) { // set
			$inverse_cache = array_flip($this->name_cache);
			foreach ($input as $old_name => $value) {
				if (isset($inverse_cache[$old_name])) {
					$this->items[$inverse_cache[$old_name]]->value($value);
				}
			}
		} else {
			$out = Array();
			foreach ($this->items as $path_name => $item) {
				$out[$this->name_cache[$path_name]] = $item->value();
			}
			return $out;
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
		$html = '';
		if (! $email) {
			$html .= (($nameAbove)?('<div '.$this->attrString().'>'):('<tr '.$this->attrString().'><td colspan="2">'))."\n";
			$html .= '<'.$this->field_tag.'>'."\n";

			if (! $this->hide_label && $this->label !== NULL) {
				$html .= '<'.$this->label_tag.'>'.$this->label.'</'.$this->label_tag.'>'."\n";
			}
			$form_tag = ($naveAbove || $this->forceChildNameAbove)?'div':'table';

			$html .= '<'.$form_tag.'>'."\n";;
			foreach ($this->items as $item) {
				$html .= $item->printRow($email, $nameAbove || $this->forceChildNameAbove)."\n";
			}
			$html .= '</'.$form_tag.'>'."\n";
			$html .= '</'.$this->field_tag.'>'."\n";
			$html .= ($nameAbove?'</div>':'</td></tr>')."\n";
		} else {
			$html .= (($nameAbove)?'<div>':'<tr><td colspan="2">').($this->hide_label?'':('<strong>'.$this->label.'</strong>'));
			$html .= (($nameAbove)?'</div>':'</td></tr>')."\n";
			foreach ($this->items as $item) {
				$html .= $item->printRow($email, $nameAbove || $this->forceChildNameAbove)."\n";
			}
			$html .= (($nameAbove)?'':'<tr><td colspan="2">&nbsp;</td></tr>')."\n";
		}
		return $html;
	}
}
