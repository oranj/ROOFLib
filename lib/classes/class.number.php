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

require_once('class.phone.php');

class FI_Number extends FI_Phone {


/**
 * Creates a new FI_Number
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct ($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'lower_limit' => NULL,
			'upper_limit' => NULL,
		);

		$this->merge($options, $defaultValues);
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Number";
 */
	public static function getType() {
		return "Number";
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Number $lower_limit The minimum value to allow; Default:NULL
 * @param Number $upper_limit The maximum value to allow; Default:NULL
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'lower_limit'=>self::DE('number', 'The minimum value to allow', 'NULL'),
			'upper_limit'=>self::DE('number', 'The maximum value to allow', 'NULL'),
		);
	}

/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		global $NUM_PICKER_SCRIPT;
		$html = '';
		if (false && ! $NUM_PICKER_SCRIPT) {

			$html .= '<script type="text/javascript">
var _NumberManagers = Array();

function NumberManager(_name, _upper, _lower) {

	this.name = _name;
	this.lower_limit = _lower;
	this.upper_limit = _upper;

	this.element = $("[name="+this.name+"]");

	this.increase = 1;

	this.element.keypress(function (e) { _NumberManagers[$(this).attr("name")].keypress(e) });

	this.element.keyup(function (e) { _NumberManagers[$(this).attr("name")].keyup(e) });

	this.element.keydown(function (e) { _NumberManagers[$(this).attr("name")].keydown(e) });

	this.element.change(function (e) { _NumberManagers[$(this).attr("name")].change(e) });

	_NumberManagers[this.name] = this;

	this.fetchVal = function () {
		var val = parseInt(this.element.val());
		if (isNaN(val)) {
			val = 0;
		}
		return val;
	}

	this.keypress = function (e) {
		switch (e.keyCode) {
			case 38:
				this.increment(false);
				break;
			case 40:
				this.increment(true);
				break;
			default:
				break;
		}
		this.increase += 0.06;
	}

	this.keydown = function (e) {
		var c= String.fromCharCode(e.which).charCodeAt(0);
		if (! ((c >= 48 && c <= 57) || c == 46 || c == 8 || (c >= 38 && c <= 40) ||  (c >= 96 && c <= 105))) {
			this.element.attr("readonly", "readonly");
		}
	}

	this.increment = function (negative) {
		var change = parseInt(this.increase);
		if (negative) {
			change *= -1;
		}
		var val = this.fetchVal();
		val += change;
		if (this.upper_limit != null && val > this.upper_limit) {
			val = this.upper_limit;
		} else if (this.lower_limit != null && val < this.lower_limit) {
			val = this.lower_limit;
		}
		this.element.val(val);
		this.val = val;
	}

	this.change = function () {
		var val = this.fetchVal();
		if (this.upper_limit != null && val > this.upper_limit) {
			val = this.upper_limit;
		} else if (this.lower_limit != null && val < this.lower_limit) {
			val = this.lower_limit;
		}
		this.element.val(val);
		this.val = val;

	}

	this.keyup = function (e) {
		this.increase = 1;
		this.element.removeAttr("readonly");
	}

	this.val = this.fetchVal();
}

			</script>';
			$NUM_PICKER_SCRIPT = true;
		}

		$html .= '<script type="text/javascript">$(function () {new NumberManager("'.$this->name().'", '.(! is_null($this->upper_limit)?$this->upper_limit:'null').', '.(! is_null($this->lower_limit)?$this->lower_limit:'null').');})</script>';
		$html .= parent::printForm();
		return $html;
	}

}