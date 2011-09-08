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

abstract class FormItem {

	protected $name;
	protected $label;
	protected $description;
	protected $required;
	protected $formAttributes;
	protected $validators;
	protected $attributes;

	protected $form;

/**
 * Creates a new FormItem
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {

		$defaultValues = Array(
			'description' 	=> '',
			'required'	  	=> false,
			'validators'	=> Array(),
			'hide_label'	=> false,
			'email'			=> true,
			'pre'=>'',
			'post'=>'',
			'required_str' 	=> '',
			'desc_in_label' => false,
			'required_attr' => false,
			'message_inline'=> true, // Options are Inline or Top
			'help'			=> NULL,
		);

		$this->errors 		= Array();
		$this->warnings		= Array();


		$this->name 			= $name;
		$this->label 			= $label;
		$this->formAttributes 	= Array();
		$this->attributes		= Array();

		$this->merge($options, $defaultValues);


		$this->attr('id', 		$this->cfg('prefix_id').strtolower($this->name()));
		$this->attr('class', 	$this->cfg('prefix_class').strtolower($this->getType()));
		$this->attr('class',	($this->required)?$this->cfg('class_required'):$this->cfg('class_not_required'));

		if (! is_array($this->validators)) {
			$this->validators = Array($this->validators);
		}
	}
	
	public function cfg($key) {
		global $ROOFL_Config;
		if (isset($ROOFL_Config[$key])) {
			return $ROOFL_Config[$key];
		} else {
			return NULL;
		}
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param String $description Text describing the table. Appears underneath the text by default; Default:''
 * @param String $required Indicates that the field is required. This will force the user into entering data; Default:false
 * @param String $validators A list of php functions that inform the form if the fields are correct; Default:Array()
 * @param String $hide_label Indicates that the field label should be hidden; Default:false
 * @param String $pre Text preceding the input element; Default:''
 * @param String $post Text following the input element; Default:''
 * @param String $required_str Text following the field name if the field is required; Default:''
 * @param String $email Include the field in the email; Default:true
 *
 * @return Array The optional parameters which describe this class.
 */

	public static function description() {
		$description = Array(
			'description' => self::DE('text', 'Text describing the table. Appears underneath the text by default.', '\'\''),
			'required'=>self::DE('bool', 'Indicates that the field is required. This will force the user into entering data.', 'false'),
			'validators'=>self::DE('textarea', 'A list of php functions that inform the form if the fields are correct', 'Array()'),
			'hide_label'=>self::DE('bool', 'Indicates that the field label should be hidden', 'false'),
			'pre'=>self::DE('text', 'Text preceding the input element.', '\'\''),
			'post'=>self::DE('text', 'Text following the input element.', '\'\''),
			'required_str'=>self::DE('text', 'Text following the field name if the field is required.', '\'\''),
			'email'=>self::DE('bool', 'Include the field in the email', 'true'),
			'desc_in_label'=>self::DE('bool', 'Print the description underneath the label', 'false'),
			'required_attr'=>self::DE('bool', 'Adds the "required" attribute the the input', 'true'),
		);
		return $description;
	}

	public function printHelp() {
		if ($this->help) {
			global $HELP_SCRIPT_PRINTED;
			$js = '';
			if (! $HELP_SCRIPT_PRINTED) {
				$js .= '<script type="text/javascript">

function popUp(_title, _text) {
	if (! $("#fi_popUp").length) {
		$("body").append($("<div id=\'fi_popUp\'><span class=\'fi_icon\'>'.addslashes($this->form->getIco('help', 'Help')).'</span><a class=\'fi_close\' href=\'javascript:popDown()\'>'.addslashes($this->form->getIco('close', 'Close')).'</a><div class=\'header\'></div><div class=\'message\'></div>"));
	}
	if (! $("#fi_popUpModal").length) {
		$("body").append($("<div id=\'fi_popUpModal\' onclick=\'popDown()\'></div>"));
	}

	$("#fi_popUp").css("display", "block");
	$("#fi_popUpModal").css("display", "block");
	$("#fi_popUp .header").text(_title);
	$("#fi_popUp .message").html("").append(_text);
}

function popDown() {
	$("#fi_popUp").css("display", "none");
	$("#fi_popUpModal").css("display", "none");
}

				</script>

				';
			}
			$ico = $js.$this->form->getIco('help', 'Help', 'View help information');
			return ' <a href="javascript:popUp(\''.htmlentities($this->label).'\', \''.addslashes($this->help).'\');">'.$ico.'</a>';
		}
	}

/**
 * Struct for ease of use in the FormItem::description() function
 *
 * @param mixed $type
 * @param mixed $info
 * @param mixed $default
 *
 * @return Array
 */
	protected static function DE($type, $info, $default) {
		return Array('type'=>$type, 'info'=>$info, 'default'=>$default);
	}


/**
 * Merges options in derived classes with the base class
 *
 * @param Array $options The options to merge
 */
	private function mergeOptions($options) {
		foreach ($this->defaultValues as $param => $value) {
			if (isset($options[$param])) {
				$this->$param = $options[$param];
			} else {
				$this->$param = $value;
			}
		}
	}


	public function setForm(&$form) {
		$this->form = $form;
	}

/**
 * Merges the default values from the derived classes to the base class
 *
 * @param Author $defaultValues
 * @param Bool $overwrite Whether or not to overwrite the values
 */
	private function mergeDefaults($defaultValues, $overwrite = true) {
		foreach ($defaultValues as $param => $value) {
			if (($overwrite || ! isset($this->defaultValues[$param]))) {
				$this->defaultValues[$param] = $value;
			}
		}
	}


/**
 * Merges descriptions from the derived classes to the base class (formItem)
 *
 * @param String $class The FormItem class to describe
 *
 * @return Array A merged array of option descriptions
 */
	public static function describe($class) {
		$str = $class.'::description();';
		eval('$description = '.$class.'::description();');
		$parent = get_parent_class($class);
		if ($parent) {
			$base = FormItem::describe($parent);
			$description = array_merge($base, $description);
		}
		return $description;
	}


/**
 * Merges the options and default values between FormItems and their parents.
 *
 * @param Array $options The available options
 * @param Array $defaultValues The default values of the options.
 * @param Array $overwrite Whether or not to overwrite the parent's options
 */
	public function merge($options, $defaultValues, $overwrite = true) {
		$this->mergeDefaults($defaultValues, $overwrite);
		$this->mergeOptions($options);
	}



/**
 * Prints the description of the form item
 *
 * @return String The description string.
 */
	public function printDescription() {
		if ($this->description) {
			return '<div class="'.$this->cfg('class_description').'">'.$this->description.'</div>';
		}
		return '';
	}


/**
 * Gets or sets the form item's name
 *
 * @param String $name The form item's name
 *
 * @return String the form item's name.
 */
	public function name($name = false) {
		if ($name !== false) {
			$this->name = $name;
		} else {
			return $this->name;
		}
	}


/**
 * Adds the form item to the database.
 *
 * @param DatabaseForm $form The DatabaseForm
 */
	public function addToDB(&$form) {
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "FormItem";
 */
	public static function getType() {
		return "FormItem";
	}

 /**
 * Prints the status messages after validation
 *
 * @return String The Status Messages
 */
	public function printMessages() {
	 	$html = '';
		if ($this->errors) {
			$html .= '<div class="'.$this->cfg('class_error').' '.$this->cfg('class_inline').'">';
			$first = true;
			foreach ($this->errors as $me) {
				if (! $first) {
					$html .= '<br/>';
				}
				$html .= $this->form->getIco('error', 'Error', $me->inline).$me->inline;
				$first = false;
			}
			$html .= '</div>';
		}
		if ($this->warnings) {
			$html .= '<div class="'.$this->cfg('class_warning').' '.$this->cfg('class_inline').'">';
			$first = true;
			foreach ($this->warnings as $me) {
				if (! $first) {
					$html .= '<br/>';
				}
				$html .= $this->form->getIco('warning', 'Warning', $me->inline).$me->inline;
				$first = false;
			}
			$html .= '</div>';
		}
		return $html;
	}


/**
 * Add files to the form for email purposes.
 *
 * @param Array &$files A hash of files from path to name.
 */
	public function addFiles(&$files) {
	}



/**
 * Prints the text following the field input.
 *
 * @return String the post text
 */
	public function printPost() {
		if ($this->post) {
			return '<span class="'.$this->cfg('class_pre').'">'.$this->post.'</span>';
		}
		return '';
	}


/**
 * Prints the text preceding the field input
 *
 * @return String the Pre text
 */
	public function printPre() {
		if ($this->pre) {
			return '<span class="'.$this->cfg('class_pre').'">'.$this->pre.'</span>';
		}
		return '';
	}


/**
 * Adds a custom validator to the Form Item
 *
 * @param String $callback The function name to call.
 */
	public function addValidator($callback) {
		$this->validators [] = $callback;
	}


/**
 * Returns the form item's label
 *
 * @return String the label
 */
	public function label() {
		return $this->label.($this->desc_in_label?$this->printDescription():'');
	}


/**
 * Whether or not the FormItem is required.
 *
 * @return Bool The field is required.
 */
	public function required() {
		return $this->required;
	}


/**
 * Generates a string containing the TR's attributes
 *
 * @return String The FormItem's TR's attributes
 */
	public function attrString() {
		$strings = Array();
		foreach ($this->attributes as $name => $values) {
			$strings []= $name.'="'.join(' ', $values).'"';
		}
		return join(' ', $strings);
	}


/**
 * Gets or sets an attribute for the FormItem's TR
 *
 * @param String $name The name of the attribute
 * @param String $value The value of the attribute
 * @param Bool $overwrite Whether or not to overwrite the attribute if it already exists.
 *
 * @return String The value when using the function as a getter
 */
	public function attr($name, $value = NULL, $overwrite = false) {
		if ($value === NULL) {
			return $this->attributes[$name];
		} else if (! isset($this->attributes[$name]) || $overwrite) {
			$this->attributes[$name] = Array($value);
		} else if ( ! $overwrite) {
			$this->attributes[$name] []=$value;
		} else {
			return $this->attributes[$name];
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
		if ($email) {
			if ($nameAbove) {
				return '<div class="'.$this->cfg('class_fieldname').'">'.($this->hide_label?'':$this->label()).'</div><div class="'.$this->cfg('class_fieldvalue').'">'.$this->printEmail().'</div>'."\n";
			} else {
				return '<tr><td class="'.$this->cfg('class_fieldname').'">'.$this->label().'</td><td class="'.$this->cfg('class_fieldvalue').'">'.$this->printEmail().'</td></tr>'."\n";
			}
		} else {
			$messages = ($this->message_inline || $this->form->message_inline)?$this->printMessages():'';
			if ($messages) { $messages = (($nameAbove?'<div ':'<td ').' class="'.$this->cfg('class_fieldmessages').'">'.$messages.'</'.($nameAbove?'div>':'td>')); }
			if ($nameAbove) {
				return '<div '.$this->attrString().'>'.($this->hide_label?'':('<div class="'.$this->cfg('class_fieldname').'">'.$this->label().$this->printRequired().$this->printHelp().'</div>')).'<div class="'.$this->cfg('class_fieldvalue').'">'.$this->printForm().'</div>'.$messages.'</div>';
			} else {
				return '<tr '.$this->attrString().'><td class="'.$this->cfg('class_fieldname').'">'.($this->hide_label?'':($this->label().$this->printRequired().$this->printHelp().'</div>')).'</td><td class="'.$this->cfg('class_fieldvalue').'">'.$this->printForm().'</td>'.$messages.'</tr>';
			}
		}
	}


/**
 * Gets the FormItems specific attributes to the form
 *
 * @return Array The form Attributes
 */
	public function formAttributes() {
		return $this->formAttributes;
	}


/**
 * This function is called by the form upon form validation success.
 */
	public function onValidation() {

	}


/**
 * This function is called by the form upon form validation failure.
 */
	public function onFailure() {

	}

/**
 * Performs native validation within the FormItem.
 *
 * @param Array $errors An array in which to place errors
 * @param Array $warnings An array in which to place warnings
 * @param Bool $continue A Bool to indicate whether or not the containing FI_Group or Form should break upon completion
 */
	public function check(&$errors, &$warnings, &$continue) {
		$value = trim($this->value());
		if ($this->required && ! $value && $value !== '0') {
			$errors []= Form::ME('error', sprintf($this->cfg('text_error_head'), $this->label()), $this, sprintf($this->cfg('text_error_inline'), $this->label()));
		}
		$this->checkValidators($errors, $warnings, $continue);
	}


/**
 * Checks the passed validators
 *
 * @param Array $errors A list of errors.
 * @param Array $warnings A list of warnings
 * @param Bool $continue Tells the parent to not break
 */
	public function checkValidators(&$errors, &$warnings, &$continue) {
		$_errors = Array();
		$_warnings = Array();
		foreach ($this->validators as $validator) {
			if (! $continue) {
				break;
			}
			$continue = $validator($this, $_errors, $_warnings);
		}
		foreach ($_errors as $_error) {
			$errors []= Form::ME('error', $_error, $this);
		}
		foreach ($_warnings as $_warning) {
			$warnings []= Form::ME('warning', $_warning, $this);
		}
	}


/**
 * Gets the required string to append to the label
 *
 * @return String The HTML
 */
	public function printRequired() {
		if ($this->required()) {
			if ($this->required_str) {
				return ' '.$this->required_str;
			} else {
				return ' '.$this->form->required_str;
			}
		} else {
			return '';
		}
	}


/**
 * Gets or sets the value of the form item.
 */
	public function value() {
	}


/**
 * Prints the Form Item for the Form
 */
	public function printForm() {

	}


/**
 * Prints the Form Item for Email
 */
	public function printEmail() {

	}


}