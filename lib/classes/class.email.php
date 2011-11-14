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
				require_once(dirname(__FILE__).'/whois.class.php');

				list($name, $domain) = split('@', $string);
				$whois = new Whois();
				$fail = false;
				if (! $whois->ValidDomain($domain) || ($domain == 'example.com' && ! $FORM_DEBUG)) {
					$fail = true;
				}

			} else {
				$fail = true;
			}

			if ($fail) {
				$errors []= Form::ME('error', "Invalid email address: <em>$string</em>", $this);
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
		return $this->printPre().'<input type="email" '.($this->required() && ($this->required_attr || $this->form->required_attr)?'required ':'') .'name="'.$this->name().'" value="'.$this->value().'"/>'.$this->printPost().$this->printDescription();
	}
}