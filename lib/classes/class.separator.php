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

include_once('class.formitem.php');

class FI_Separator extends FormItem {

	protected $separator;

/**
 * Description for function __construct()
 *
 * @param mixed $name
 * @param mixed $label
 * @param mixed $options
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);
		$this->merge($options, Array('separator' => '<hr/>'));
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Separator";
 */
	public static function getType() {
		return "Separator";
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
		$html .= (($nameAbove)?'<div '.$this->attrString().'>':'<tr '.$this->attrString().'><td colspan="2">');
		if ($this->separator) {
			$html .= $this->separator."\n";
		}
		if ($email ) {
			$html .= '<strong>'.$this->label.'</strong>';
		} else {
			$html .= '<div class="sepLabel">'.$this->label.$this->printHelp().'</div>';
		}

		$html .= (($nameAbove)?'</div>':'</td></tr>')."\n";

		return $html;
	}
}
