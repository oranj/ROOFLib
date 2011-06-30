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

require_once('class.text.php');

class FI_Email extends FI_Text {


/**
 * Gets the type of the FormItem
 *
 * @return String "Email";
 */
	public static function getType() {
		return "Email";
	}


/**
 * Performs native validation within the FormItem.
 *
 * @param Array $errors An array in which to place errors
 * @param Array $warnings An array in which to place warnings
 * @param Bool $continue A Bool to indicate whether or not the containing FI_Group or Form should break upon completion
 */
	public function check(&$errors, &$warnings, &$continue) {
		parent::check($errors, $warnings, $continue);
		global $FORM_DEBUG;
		$fail = false;
		$string = $this->value();
		if ($string || $string === '0') {
			$matches = preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $string);
			if ($matches) {
				list($name, $domain) = split('@', $string);
				$ip = gethostbyname($domain);
				$nums = split('\.', $ip);
				$fails = false;
				if (! (($domain == 'example.com') && $FORM_DEBUG)) {
					foreach ($nums as $num) {
						if (! ((int)$num > 0 && (int)$num < 255)) {
							$fail = true;
							break;
						}
					}
				}
			} else {
				$fail = true;
			}

			if ($fail) {
				$errors []= "Invalid email address: <em>$string</em>";
			}
		}
		return true;
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		return $this->printPre().'<input type="email" '.($this->required()?'required ':'') .'name="'.$this->name().'" value="'.$this->value().'"/>'.$this->printPost().$this->printDescription();
	}
}