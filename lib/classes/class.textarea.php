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

require_once('class.text.php');

class FI_TextArea extends FI_Text {

	protected $wysiwyg;


/**
 * Creates a new FI_Textarea
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);
		global $__WYSIWYG_TEXTAREAS;
		if (! $__WYSIWYG_TEXTAREAS) {
			$__WYSIWYG_TEXTAREAS = Array();
		}
		$defaultValues = Array(
			'wysiwyg'=>false,
			'wysiwygInputClass'=>'ckeditor',
			'wysiwyg_global'=>'<script type="text/javascript" src="'.WYSIWYG_SCRIPT.'"></script>'
		);

		$this->merge($options, $defaultValues);
		if ($this->wysiwyg) {
			$__WYSIWYG_TEXTAREAS []= $this->name().'_w';
		}
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Textarea";
 */
	public static function getType() {
		return "TextArea";
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Bool $wysiwyg Whether or not to use a WYSIWYG editor; Default:false
 * @param String $wysiwygInputClass The class to append to the textarea to identify it as a WYSIWYG; Default:'ckeditor'
 * @param String $wysiwyg_global The script tag to be added to include the WYSIWYG script; Default:'<script type="text/javascript" src="'.WYSIWYG_SCRIPT.'"></script>'
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'wysiwyg'=>self::DE('bool', 'Whether or not to use a WYSIWYG editor', 'false'),
			'wysiwygInputClass'=>self::DE('string', 'The class to append to the textarea to identify it as a WYSIWYG', '\'ckeditor\''),
			'wysiwyg_global'=>self::DE('string', 'The script tag to be added to include the WYSIWYG script', '\'<script type="text/javascript" src="\'.WYSIWYG_SCRIPT.\'"></script>\''),
		);
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		global $__WYSIWYG_TEXTAREAS, $__WYSIWYG_PRINT;
		$script = '';
		if ($this->wysiwyg && ! $__WYSIWYG_PRINT) {
			$names = join(', ', $__WYSIWYG_TEXTAREAS);
			$script = $this->wysiwyg_global;
			$__WYSIWYG_PRINT = true;
		}

		return $script.'<textarea '.(($this->wysiwyg && $this->wysiwygInputClass)?('class="'.$this->wysiwygInputClass.'" '):'').'id="'.$this->name().'_w" cols="40" rows="4" '.($this->required()?'required ':'') .'name="'.$this->name().'">'.$this->value().'</textarea>';
	}
}