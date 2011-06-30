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

$FORM_DEBUG = false;

$FORMITEMS = Array(
	'Bool' => 'Allows the user to select a true / false value',
	'Captcha' => 'Verifies the user is a human',
	'CSV' => 'A list of values separated by a delimiter, a comma by default',
	'Checkbox' => 'Allows the users to choose multiple values in a set',
	'Date' => 'Allows the user to pick a date. Requires javascript',
	'Email' => 'Enforces that the input text is a valid email address',
	'File' => 'Allows the user to upload one or more files. Multiple files require javascript.',
	'FormItem' => 'The abstract class for all form items.',
	'Group' => 'Groups form items',
	'Hidden' => 'A hidden input',
	'HTML' => 'Allows customizable text between the form items',
	'Matrix' => 'Advanced functionality- allows the user to select states for a grid of items',
	'MultiCheck' => 'Similar functionality to Checkbox, but allows advanced formatting in columns',
	'Number' => 'With Javascript enabled, limits the user to inputting numbers. Plays nicely with mobiles as well',
	'Password' => 'Password field',
	'Phone' => 'On mobile platforms, the on screen keyboard is the phone.',
	'Radio' => 'Allows the user to choose one value of a set. Similar to Select',
	'Script' => 'Retrieves values from the client via Javascript. Advanced functionality. Javascript should return the desired value',
	'Select' => 'Allows the user to choose one value of a set. Similar to Radio. No functionality in place for multi select yet.',
	'Separator' => 'A visual separation in the form and email',
	'Switch' => 'Allows the user to switch between different Groups, displaying only currently selected option. Advanced functionality',
	'Text' => 'Basic FormItem allows for a single line of text',
	'TextArea' => 'Basic FormItem allows for multiline of text. Advanced functionality includes WYSIWYG capabilities',
	'Toggle' => 'Allows the user to switch between different Form Items, disabling all non selected options. Advanced functionality',
);

foreach ($FORMITEMS as $filename => $description) {
	$class = (($filename == 'FormItem')?'':'FI_').$filename;
	require_once(dirname(__FILE__).'/class.'.strtolower($filename).'.php');
}

include_once (dirname(__FILE__).'/class.phpmailer.php');
include_once (dirname(__FILE__).'/class.DatabaseForm.php');


if (! isset($_SESSION)) {
	session_start();
}

class Form {

	protected $name;

	protected $items;
	protected $fieldsets;
	protected $validators;

	// We can accrue messages here on submission failures
	protected $errors;
	protected $warnings;
	protected $attributes;

	protected $successMessage;
	protected $welcomeMessage;

	protected $noteMessage;
	protected $buttonHTML;
	protected $postActions;

	protected $status_messages_printed;

	protected $__action;
	protected $__sessioned;

	protected $mailCSS;
	protected $useFormat;


/**
 * Creates a Form- the main element when using ROOFLib
 *
 * @param String $name The unique of the Form - the name of the table if databasing
 */
	public function __construct($name) {

		$this->name = preg_replace('/\s+/', '_', strtolower($name));
		$this->items = Array();
		$this->validators = Array();
		$this->attributes = Array('method' => 'post', 'action'=>$_SERVER['REQUEST_URI'], 'id'=>'f_'.$this->name);
		$this->successMessage = "";
		$this->welcomeMessage = "";
		$this->noteMessage = "Required Fields <span>*</span>";
		$this->status_messages_printed = false;
		$this->setButtons(self::BU('Submit', 'submit'));

$css = <<<CSS
	.fldValue, .fldName { vertical-align:top; padding-bottom:3px; font:12px/1.6em Arial, sans-serif; color:#333;  border-top:1px solid #efefef; }
	.fldName { padding-right:25px; color:#7A3D00; }
	table { border-collapse: collapse; }
	a { color:#17345c; }
CSS;

		$this->setMailCSS($css);

		$this->__useFormat = NULL;
		$this->__action = NULL;
		$this->__sessioned = NULL;

	}


/**
 * Use a format to print the email in. Uses HTML, but with <[formItemName]>, <[parentFormItem::formItemName]> (to any depth) calling the specified FormItem's printRow function
 *
 * @param String $format The formatted HTML string
 */
	public function useFormat($format) {
		$this->__useFormat = $format;
	}


/**
 * Sets the CSS in the Email
 *
 * @param String $css The CSS Styles to include;
 */
	public function setMailCSS($css) {
		$this->mailCSS = $css;
	}


/**
 * Gets or Sets a tree of values by the layout of the form, formItems, and groups
 *
 * @param Array $input A Tree of values to set the form to.
 *
 * @return Array A Tree of values based on the user's submission
 */
	public function value($input = NULL) {
		if ($input !== NULL) {
			foreach ($input as $name => $item) {
				if (isset($this->items[$name])) {
					$this->items[$name]->value($item);
				}
			}
		} else {
			$out = Array();
			foreach ($this->items as $name => $item) {
				$out[$name] = $item->value();
			}
			return $out;
		}
	}


/**
 * Sets the message to be displayed on form success
 *
 * @param String The message to be displayed
 */
	public function setSuccessMessage($message) {
		$this->successMessage = $message;
	}


/**
 * Struct for storing the button data. For a standard button, use "Form::BU('Submit', 'submit'), For an 'onclick' button, use "Form::BU('Text', 'foo()', 'script')", For a javascript redirect: "Form::BU('Text', 'http://url', 'link')", or For an image, use "Form::BU('button.png', 'foo', 'image')"
 *
 * @param String $label
 * @param String $value
 * @param String $type Your options are NULL, 'script', 'link', or 'image'
 *
 * @return Array The button data
 */
	static public function BU($label, $value, $type = 'submit') {
		return (object) Array(
			'label'		=> $label, // if is_img, this is the URL
			'value' 	=> $value,
			'type' 		=> $type,
			'is_fbu'	=> true,
		);
	}


/**
 * Sets the buttons of the form. Accepts any number of Form::BU items.
 */
	public function setButtons() {
		$this->buttonHTML = '';
		$this->postActions = Array();
		$bu_array = func_get_args();
		foreach ($bu_array as $bu) {
			if (! $bu->is_fbu) {
				echo 'If you are using setButtons, construct them using Form::BU($value, $label, [$is_img = false]);';
				exit();
			}
			switch ($bu->type) {
				case 'submit':
					$this->buttonHTML .= $this->_getButtonHTML($bu->label, $bu->value);
					break;
				case 'image':
					$this->buttonHTML .= $this->_getButtonImageHTML($bu->label, $bu->value);
					break;
				case 'link':
					$this->buttonHTML .= $this->_getButtonLinkHTML($bu->label, $bu->value);
					break;
				case 'script':
					$this->buttonHTML .= $this->_getButtonScriptHTML($bu->label, $bu->value);
				default:
			}
			$this->postActions[$bu->value] = $bu;
		}
	}



/**
 * Sets attributes to the <form> element itself
 *
 * @param String $name The name of the attribute
 * @param String $attribute The value of the attribute
 */
	public function setAttribute($name, $attribute) {
		$this->attributes[$name] = $attribute;
	}


/**
 * Determines if and what button was pressed upon page submission
 *
 * @param mixed $_sessioned Will return whether or not the action came from a sessioned form
 *
 * @return String the value of the button pushed, assuming they were a submit or image button.
 */
	public function action(&$_sessioned = false) {

		$action = false;

		foreach ($this->postActions as $bu) {
			if ($bu->type == 'link') {
				preg_match_all('/[a-z]+/', strtolower($bu->label), $matches);
				$suff = join('_', $matches[0]);
				$name = $this->_getButtonPrefix().$suff;
				if (isset($_GET[$name])) {
					$_label = $bu->label;
					$action = $_GET[$name];
					break;
				}
			} else {

				$name = $this->_getButtonPrefix().$bu->value;
				if (isset($_POST[$name])) {
					$_label = $bu->label;
					$action = $bu->value;
					break;
				}
			}
		}
		if (! $action) {
			if ($sess_data = $this->getSession()) {
				$action = $sess_data['__action'];
				$this->__sessioned = true;
				$_sessioned = true;
			}
		}

		$this->__action = $action;
		return $action;
	}


/**
 * Gets the session key for the form if using the form session functions
 *
 * @return String the session key.
 */
	public function getFormSessionKey() {
		$key = md5($this->name);
		return $key;
	}


/**
 * Gets the data from a sessioned form
 *
 * @return String the data from a sessioned form
 */
	public function getSession() {
		global $form_fields_values;

		$key = $this->getFormSessionKey();
		if (isset($form_fields_values[$key])) {
			$value = unserialize($form_fields_values[$key]);
			return $value;
		}
		return false;
	}


/**
 * Stores the data from a sessioned form
 */
	public function storeSession() {

		global $form_fields_values;
		tep_session_register('form_fields_values');

		$key = $this->getFormSessionKey();
		$value = $this->value();
		$value['__action'] = $this->action();

		$value = serialize($value);

		$form_fields_values[$key] = $value;
	}


/**
 * Gets a prefix for the buttons' name
 *
 * @return String The button prefix
 */
	private function _getButtonPrefix() {
		return preg_replace('/\s+/', '_', $this->name).'_bu_';
	}


/**
 * Gets the HTML for the Image Button
 *
 * @param String $url The location of the desired image.
 * @param String $name The name of the image button
 *
 * @return String The generated HTML
 */
	private function _getButtonImageHTML($url, $name='submit') {
		return '<input name="'.$this->_getButtonPrefix().$name.'" type="image" src="'.$url.'" />';
	}

/**
 * Gets the HTML for the Script Button.
 *
 * @param String $label The label of the button
 * @param String $script The script to be run upon clicking
 *
 * @return String The generated HTML
 */
	private function _getButtonScriptHTML($label, $script) {
		return '<input type="button" value="'.$label.'" onclick="'.$script.'"/>';
	}


/**
 * Gets the HTML for the Link Button.
 *
 * @param String $label The label of the button
 * @param String $url The location to be sent to
 *
 * @return String  The generated HTML
 */
	private function _getButtonLinkHTML($label, $url = NULL) {
		if ($url === NULL) {
			$url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		}

		preg_match_all('/[a-z]+/', strtolower($label), $components);
		$value = join('_', $components[0]);
		$name = $this->_getButtonPrefix().$value;

		list($base, $param_str) = split('\?', $url);
		$params = split('&', $param_str);
		$out_params = Array();
		foreach ($params as $param) {
			list($_k, $_v) = split('=', $param);
			$out_params[$_k] = $_v;
		}
		$out_params [$name]= $value;
		$params = Array();
		foreach ($out_params as $_n => $_v) {
			$params[] = $_n.(($_v)?('='.$_v):'');
		}

		$url = $base.'?'.join('&', $params);

		return '<input type="button" name="'.$name.'" value="'.$label.'" onclick="window.location = \''.$url.'\';" />';
	}

/**
 * Gets the HTML for a standard submit button
 *
 * @param String $label The label of the button
 * @param String $name The name of the input
 *
 * @return
 */
	private function _getButtonHTML($label, $name='submit') {
		return '<input name="'.$this->_getButtonPrefix().$name.'" type="submit" value="'.$label.'" />';
	}

/**
 * Sets the message to be displayed above the form and the messages.
 *
 * @param String $message the message to be displayed
 */
	public function setWelcomeMessage($message) {
		$this->welcomeMessage = $message;
	}


/**
 * Sets the message to be displayed below the error and warning messages. Useful for required indications
 *
 * @param String $note The message to be displayed
 */
	public function setNoteMessage($note) {
		$this->noteMessage = $note;
	}


/**
 * Passed a name, indicates whether or not it is contained in this form
 *
 * @param String $name The name to check for
 *
 * @return Bool Whether or not the form contains the indicated item
 */
	public function hasItem($name) {
		return (isset($this->items[$name]));
	}


/**
 * Gets an item by name
 *
 * @param String $name The name of the Item
 *
 * @return FormItem the Form Item to get
 */
	public function getItem($name) {
		if ($this->hasItem($name)) {
			return $this->items[$name];
		}
	}


/**
 * Prints the status message to the screen
 *
 * @return String The status messages
 */
	public function print_status_messages() {
		$html = '';
		if(! $this->status_messages_printed) {
			if ($this->errors) {
				$html .= '<div class="error">The following error'.((sizeof($this->errors) > 1)?'s':'').' occurred: <ul><li>'.join('</li><li>', $this->errors).'</li></ul></div>';
			}
			if ($this->warnings) {
				$html .= '<div class="warning">Notice: <ul><li>'.join('</li><li>', $this->warnings).'</li></ul></div>';
			}
			if ($this->successes) {
				$html .= '<div class="success"><ul><li>'.join('</li><li>', $this->successes).'</li></ul></div>';
			}
			$this->status_messages_printed = true;
		}
		return $html;
	}


/**
 * Formats the form data given a formatted string
 *
 * @param String $format The formatted string template
 *
 * @return String the formatted result
 */
	private function format($format) {

		preg_match_all('/\<\[(.*?)\]\>/', $format, $matches, PREG_OFFSET_CAPTURE);

		foreach ($matches[0] as $key => $value) {
			$pre = substr($format, 0, $value[1]);
			$name = $matches[1][$key][0];
			$path = preg_split('/::/', $name);
			$fi = $this;
			for ($i = 0; $i < sizeof($path); $i++) {
				if ($next_item = $fi->getItem($path[$i])) {
					$fi = $next_item;
				} else {
					die('No item with name "'.$path[$i].'" in '.($i == 0?('form::'.$this->name):('group::'.$path[$i-1].'"')));
				}
			}

			$format = preg_replace('/\<\['.$matches[1][$key][0].'\]\>/', '<span class="fi">'.$fi->printEmail()."</span>", $format);
		}
		return $format;
	}


/**
 * Adds an item to the form
 *
 * @param FormItem $formItem The item to add to the form
 */
	public function addItem($formItem) {
		if ($this->hasItem($formItem->name())) {
			// Duplicate names- throw an error-> names MUST be unique
			return false;
		} else if ($formItem->name() == '__general') {
			// Reserved name- throw an error
			return false;
		}
		// if it's required, automatically add the 'required' assertion
		$this->attributes = array_merge($this->attributes, $formItem->formAttributes());
		$this->items[$formItem->name()] = $formItem;
	}



/**
 * Adds a validator to the form.
 *
 * @param String $validator The name of the validator
 */
	public function addValidator($validator) {
		$this->validators []= $validator;
	}


/**
 * Checks the form for errors
 *
 * @return Bool indicates whether or not the form has passed validation.
 */
	public function validate() {

		$errors = Array();
		$warnings = Array();
		$continue = true;

		$successes = Array();

		$success = true;

		foreach ($this->validators as $name => $validator) {
			if (! $continue) {
				break;
			}
			$continue = $validator($this, $errors, $warnings, $successes);
		}
		foreach ($this->items as $name => $item) {
			if (! $continue) {
				break;
			}

			$item->check($errors, $warnings, $continue);
		}
		$this->errors = $errors;
		$this->warnings = $warnings;
		$this->successes = $successes;

		if ($this->errors) {
			foreach ($this->items as $name => $item) {
				$item->onFailure();
			}
			$success = false;
		} else {
			foreach ($this->items as $name => $item) {
				$item->onValidation();
			}
		}
		return $success;

	}



/**
 * Prints the success message
 *
 * @return String The success message
 */
	public function onSuccess() {
		return '<div class="success">'.$this->successMessage.'</div>';
	}


/**
 * Prints the form to HTML
 *
 * @param Bool $nameAbove indicates that the form should be printed using <div>s rather than <table>s
 *
 * @return String the HTML to be printed
 */
	public function printForm($nameAbove = false) {

		$html = '';

		if (isset($_GET['thankyou'])) {
			return $this->onSuccess();
		} else {
			$html .= '<div class="welcome">'.$this->welcomeMessage.'</div>';

			$html .= $this->print_status_messages();

			$html .= ($this->noteMessage)?('<div class="noteMessage">'.$this->noteMessage.'</div>'):'';


			foreach ($this->attributes as $key => $value) {
				$attributes []= $key.'="'.$value.'"';
			}
			$html .= '<form '.join(' ', $attributes).'>';

			$html .= '<'.(($nameAbove)?'div':'table').' class="form">';
			foreach ($this->items as $name => $item) {
				$html .= $item->printRow(false, $nameAbove)."\n";
			}
			$html .= ((! $nameAbove)?('<tr class="fbu"><td></td><td>'.$this->buttonHTML.'</td></tr></table>'):('<div class="fbu">'.$this->buttonHTML.'</div></div>')).'</form>';

		}
		return $html;
	}


/**
 * Prints the form specifically for Email purposes.
 *
 * @return String the HTML to be printed
 */
	public function printEmail() {
		$html = '';
		if ($this->mailCSS) {
			$html .= '<style>'.$this->mailCSS.'</style>';
		}
		if ($this->__useFormat) {
			$html = $this->format($this->__useFormat);
		} else {
			$html .= '<table>';
			foreach ($this->items as $name => $item) {
				if ($item->email) {
					$html .= $item->printRow(true);
				}
			}
			$html .= "</table>";
		}
		return $html;
	}


/**
 * Description for function sendEmail()
 *
 * @param String $subject The subject of the Email
 * @param String $fromAddress The email address to send from
 * @param String $fromName The name to send from
 * @param mixed mixed $to An associative array of Name to Email information to include in the message, or a single string indicating the Email message
 * @param String mixed $header An optional header to add to the email
 * @param String mixed $footer An optional footer to add to the email
 * @param Array mixed $replyTo An associative array of Name to Email information to Reply To
 * @param Array mixed $cc An associative array of Name to Email information to CC To
 * @param Array mixed $bcc An associative array of Name to Email information to BCC To
 */
	public function sendEmail($subject, $fromAddress, $fromName, $to,  $header='', $footer='', $replyTo = Array(), $cc = Array(), $bcc = Array()) {

		$html = $header.$this->printEmail().$footer;

		$files = Array();

		foreach ($this->items as $name => $item) {
			$item->addFiles($files);
		}

		$mail = new PHPMailer();
		$mail->isHTML(false);

		$mail->Subject = $subject;
		$mail->Body = $html;
		$mail->AltBody = strip_tags($html);

		$mail->From = $fromAddress; // the email field of the form
		$mail->FromName = $fromName; // the name field of the form

		if (is_array($to)) {
			foreach ($to as $name => $email) {
				$mail->AddAddress($email, $name);
			}
		} else {
			$mail->AddAddress($to);
		}

		foreach ($replyTo as $name => $email) {
			$mail->AddReplyTo($email, $name);
		}

		foreach ($cc as $name => $email) {
			$mail->AddCC($email, $name);
		}

		foreach ($bcc as $name => $email) {
			$mail->AddBCC($email, $name);
		}

		foreach ($files as $tmp_name => $name) {
			$mail->AddAttachment($tmp_name, $name);
		}

		$mail->Send();
	}


/**
 * Stores the Data in the database. Make sure to have an open Mysql Connection
 *
 */
	public function storeEntry() {
		$dbForm = new DatabaseForm($this->name);
		foreach ($this->items as $name => $item) {
			$item->addToDB($dbForm);
		}
		$dbForm->addItem('_archived', 0);
		$dbForm->storeEntry();
	}
}