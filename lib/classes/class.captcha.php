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

class FI_Captcha extends FormItem {

	protected $img_url;


/**
 * Creates a new FI_Captcha
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */

	public function __construct($name, $label, $options = Array()) {
		$defaultValues = Array(
			'img_url' => "../lib/validation_png.php"
		);
		parent::__construct($name, $label, true, Array(), $description);
		$this->merge($options, $defaultValues);
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param String $img_url The path to the Validation Image generator; Default: '../lib/validation_png.php';
 *
 * @return Array The optional parameters which describe this class.
 */

	public static function description () {
		return Array(
			'img_url'=>self::DE('text', 'The path to the validation image to use', '\'../lib/validation_png.php\'')
		);
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Captcha";
 */
	public static function getType() {
		return "Captcha";
	}

/**
 * Gets or Sets the value of the FormItem
 *
 * @param mixed $input Does nothing in FI_Captchas.
 *
 * @return Array The user's input
 */
	public function value($input = NULL) {
		if ($input !== NULL) {
		} else {
			$value = isset($_POST[$this->name])?$_POST[$this->name]:'';
			if (get_magic_quotes_gpc()) { $value = stripslashes($value);}
			$value = strip_tags($value);
			return trim($value);
		}
	}


/**
 * Performs native validation within the FormItem.
 *
 * @param Array $errors An array in which to place errors
 * @param Array $warnings An array in which to place warnings
 * @param Bool $continue A Bool to indicate whether or not the containing FI_Group or Form should break upon completion
 */
	public function check(&$errors, &$warnings, &$continue) {
		global $FORM_DEBUG;
		session_start();
		if (! isset($_SESSION) || $_SESSION['security_code'] != strtolower($this->value())) {
			$errors [] = 'There seems to be a problem with your security code'.(($FORM_DEBUG)?(strtolower(' ("'.$this->value()).'" vs "'.$_SESSION['security_code'].'")'):'');
		} else {
			$warnings [] = 'Please re-enter the security code';
		}
	}

/**
 * Adds the form info to the DatabaseForm object()
 *
 * @param DatabaseForm $dbForm The DatabaseForm to add fields to
 */
	public function addToDB(&$dbForm) {

	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		global $config;
		return '<img src="'.$this->img_url.'" /><input style="vertical-align:top; margin-top:6px;" '.($this->required()?'required ':'') .'name="'.$this->name().'" type="text" />';
	}


/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail() {
		return '';
	}


/**
 * Prints the Form Item and associated label.
 *
 * @param Bool $email Whether to print the form item for Email or the Form
 * @param Bool $nameAbove Whether to display the form item using Divs rather than Tables
 *
 * @return String the HTML to
 */
	public function printRow($email = false, $nameAbove = false) {
		if ($email) {
			return '';
		} else {
			return parent::printRow($email, $nameAbove);
		}
	}
}