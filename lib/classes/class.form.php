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

$FORM_DEBUG = false;

if (! function_exists('dump')) {
	function dump($var, $return = false) {
		$str = "<pre>".htmlentities(print_r($var, true))."</pre>";
		if ($return) { return $str; } else { echo $str; }
	}
}

$FORMITEMS = Array(
	'Bool' => 'Allows the user to select a true / false value',
	'Captcha' => 'Verifies the user is a human',
	'CSV' => 'A list of values separated by a delimiter, a comma by default',
	'Checkbox' => 'Allows the users to choose multiple values in a set',
	'Date' => 'Allows the user to pick a date. Requires javascript',
	'Email' => 'Enforces that the input text is a valid email address',
	'File' => 'Allows the user to upload one or more files. Multiple files require javascript.',
	'FormItem' => 'The abstract class for all form items.',
	'Flip' => 'Allows the user to scroll through a LOV.',
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
include_once (dirname(__FILE__).'/../../config.php');


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
		$this->attributes = Array('method' => 'post', 'action'=>$_SERVER['REQUEST_URI'], 'id'=>$this->cfg('prefix_form').$this->name);
		$this->successMessage = "";
		$this->welcomeMessage = "";
		$this->noteMessage = $this->cfg('text_note');
		$this->required_str = $this->cfg('text_required');
		$this->required_attr = $this->cfg('attr_required');
		$this->message_inline = true;
		$this->status_messages_printed = false;
		$this->setButtons(self::BU('Submit', 'submit'));
		$this->resources = $this->cfg('dir_resources');
		$this->messages_underneath = true;

		$this->js_files = Array();
		$this->js_dir = dirname(__FILE__).'/../js/';

		$this->icos = Array(
			'error'		=> $this->cfg('ico_error'),
			'warning' 	=> $this->cfg('ico_warning'),
			'help'		=> $this->cfg('ico_help'),
			'close'		=> $this->cfg('ico_close'),
		);

		$this->cache = $this->cfg('cache');
		$this->cacheDir = self::cfg('file_root').self::cfg('web_formroot').self::cfg('dir_cache');

$css = "
	.".$this->cfg('class_fieldvalue').", .".$this->cfg('class_fieldname')." { vertical-align:top; padding-bottom:3px; font:12px/1.6em Arial, sans-serif; color:#333;  border-top:1px solid #efefef; }
	.".$this->cfg('class_fieldname')." { padding-right:25px; color:#7A3D00; }
	table { border-collapse: collapse; }
	.".$this->cfg('prefix_class')."separator { font:15px/1.6em Arial, sans-serif; }
	h1 { font:18px/1.6em Arial, sans-serif; margin:10px 0px 0px; font-weight:bold; }
	a { color:#17345c; }

";

		$this->setMailCSS($css);

		$this->__useFormat = NULL;
		$this->__action = NULL;
		$this->__sessioned = NULL;

	}

	public static function cfg() {
		$keys = func_get_args();
		global $ROOFL_Config;
		$node = $ROOFL_Config;
		foreach ($keys as $key) {
			if (isset($node[$key])) {
				$node = $node[$key];
			} else {
				return NULL;
			}
		}
		return $node;
	}


/**
 * Use a format to print the email in. Uses HTML, but with <[formItemName]>, <[parentFormItem::formItemName]> (to any depth) calling the specified FormItem's printRow function
 *
 * @param String $format The formatted HTML string
 */
	public function useFormat($format) {
		$this->__useFormat = $format;
	}

	public function getIco($key, $alt = '', $title = '') {
		if ($this->icos[$key]) {
			return '<img border="0px" src="'.$this->cfg('web_catalog').$this->cfg('web_formroot').$this->cfg('dir_resources').'icons/'.$this->icos[$key].'" alt="'.htmlentities($alt).'" title="'.htmlentities($title).'" />';
		} else {
			return '';
		}
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

	public static function get_data($key) {
		$filepath = self::cfg('file_root').self::cfg('web_catalog').self::cfg('web_formroot').self::cfg('dir_data').$key.'.php';
		require_once($filepath);
		$data = $key();
		return $data;
	}

/**
 * Struct for storing the button data. For a standard button, use "Form::BU('Submit', 'submit'), For an 'onclick' button, use "Form::BU('Text', 'foo()', 'script')", For a javascript redirect: "Form::BU('Text', 'http://url', 'link')", For a sprite (auto text), use "Form::BU('Hello World', 'foo', 'sprite'), or For an image, use "Form::BU('button.png', 'foo', 'image')"
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
* Struct for sending an error message('warning' or 'error') to the parent form on validation
*
* @param String $type Your options are 'warning' or 'error' => indicating what class of message to belong to
* @param String $message The message to display
* @param FormItem $formItem the FormItem to attach the error to (NULL attaches it to the form)
*/
	static public function ME($type, $message, &$formItem = NULL, $inline = '') {
		return (object) Array(
			'type'		=> $type,
			'message' 	=> $message,
			'formItem'	=> $formItem,
			'inline' 	=> ($inline?$inline:$message),
			'is_fme'	=> true,
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
			$this->buttonHTML .= $this->getButtonHTML($bu);
			$this->postActions[$bu->value] = $bu;
		}
	}

	private function getButtonHTML($bu) {
		switch ($bu->type) {
			case 'submit':
				$html = $this->_getButtonHTML($bu->label, $bu->value);
				break;
			case 'image':
				$html = $this->_getButtonImageHTML($bu->label, $bu->value);
				break;
			case 'link':
				$html = $this->_getButtonLinkHTML($bu->label, $bu->value);
				break;
			case 'script':
				$html = $this->_getButtonScriptHTML($bu->label, $bu->value);
				break;
			case 'sprite':
				$html = $this->_getButtonSpriteHTML($bu->label, $bu->value);
				break;
			case 'custom':
				$html = $this->_getButtonCustomHTML($bu->label, $bu->value);
				break;
			default:
		}
		return $html;
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
			} else if ($bu->type == 'image' || $bu->type == 'sprite') {
				$name = $this->_getButtonPrefix().$bu->value.'_x';
				if (isset($_POST[$name])) {
					$_label = $bu->label;
					$action = $bu->value;
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


	private function _getButtonCustomHTML($pre_post, $name) {
		return $pre_post['pre'].$this->_getButtonHTML($pre_post['label'], $name).$pre_post['post'];
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
 * Gets the HTML for the Image Button
 *
 * @param String $url The location of the desired image.
 * @param String $name The name of the image button
 *
 * @return String The generated HTML
 */
	private function _getButtonSpriteHTML($text, $name='submit') {
		$url = $this->cfg('web_catalog').$this->cfg('web_formroot').$this->cfg('dir_resources').$this->cfg('file_sprite').'?text='.urlencode($text);
		$name = $this->_getButtonPrefix().$name;
		$sp = $this->cfg('sprite', '__std', 'height');
		$css = '<style type="text/css">
			#'.$name.' {
				overflow-y:hidden;
				height:'.$sp.'px;
			}
			#'.$name.'_in {
				cursor:pointer;
				background-color:#ff0;
			}
            #'.$name.'_in:hover, #'.$name.'_in:focus {
            	margin-top:-'.$sp.'px;
			}
            #'.$name.'_in:active {
            	margin-top:-'.(2 * $sp).'px;
			}
	        #'.$name.'_in:disabled {
            	margin-top:-'.(3 * $sp).'px;
            	cursor:default;
			}
		</style>';
		return preg_replace('/\s+/', ' ', $css).'<span><div id="'.$name.'"><input style="" id="'.$name.'_in" name="'.$name.'" type="image" src="'.$url.'" /></ div></span>';
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

		if (preg_match('/\?/', $url)) {
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
		}

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

	private function printTag() {
		$attributes = Array();
		foreach ($this->attributes as $key => $value) {
			$attributes []= $key.'="'.$value.'"';
		}
		$html = '<form '.join(' ', $attributes).'>';
		return $html;
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
				foreach ($this->errors as $me) {
					if (isset($me->formItem->name) && ! ($me->formItem->message_inline || $this->message_inline)) {
						$html .= '<li>'.$me->message.'</li>';
					}
				}
				if ($html) {
					$html .= '<div class="'.$this->cfg('class_error').'">Please correct the following error'.((sizeof($this->errors) > 1)?'s':'').': <ul>'.$html.'</ul></div>';
				}
			}
			if ($this->warnings) {

				$html = '';
				foreach ($this->warnings as $me) {
					if (isset($me->formItem->name) && ! ($me->formItem->message_inline || $this->message_inline)) {
						$html .= '<li>'.$me->message.'</li>';
					}
				}

				if ($html) {
					$html = '<div class="'.$this->cfg('class_warning').'">Notice: <ul>'.$html.'</ul></div>';
				}
			}
			if ($this->cfg('debug')) {
				$html .= '<div class="'.self::cfg('class_warning').'"><em>DEBUG MODE</em> is enabled. Disable this in <em>'.self::cfg('file_root').self::cfg('web_formroot').'config.php</em></div>';
			}
			$this->status_messages_printed = true;
		}
		return $html;
	}

	private function parseFormat($format) {
		preg_match_all('/\<(\/?)\{(.*?)\}(\/?)\>/', $format, $matches, PREG_OFFSET_CAPTURE);

		$inline = Array();

		$start = 0;
		$length = $matches[0][0][1];
		$index = 0;
		while ($index < sizeof($matches[0])) {
			$tag_desc = $matches[0][$index];
			if ($start != $tag_desc[1]) {
				$length = $tag_desc[1] - $start;
				$inline []= substr($format, $start, $length);
			} else {
				$length = strlen($tag_desc[0]);
				preg_match_all('/(.*?)(\[(.*?)\])/', $matches[2][$index][0], $data);
				if (! sizeof($data[1])) {
					$name = $matches[2][$index][0];
				} else {
					$name = $data[1][0];
				}
				$_attrs = $data[3];
				$attrs = Array();
				foreach ($_attrs as $str) {
					$s = split('=', $str);
					if (sizeof($s) > 1) {
						$attrs[$s[0]] = $s[1];
					} else {
						$attrs[$s[0]] = $s[0];
					}
				}

				$type = $matches[3][$index][0]?'single':($matches[1][$index][0]?'close':'open');
				$inline []= (object)Array('text'=>$text, 'name'=>$name, 'attributes'=>$attrs, 'type'=>$type);
				$index++;
			}
			$start += $length;
		}
		if ($start < strlen($format)) {
			$inline []= substr($format, $start);
		}

		$root = (object)Array('type'=>'root', 'children'=>Array(), 'attributes'=>Array());
		$stack = Array(&$root);
		foreach ($inline as $value) {
			$parent = $stack[sizeof($stack) - 1];
			if (! is_object($value)) {
				$parent->children []= (object)Array('type'=>'text', 'value'=>$value);
			} else {
				switch($value->type) {
					case 'open':
						$node = (object)Array('type'=>'node',  'attributes'=>$value->attributes, 'tag'=>$value->name, 'children'=>Array());
						$parent->children []= &$node;
						$stack []= &$node;
						break;
					case 'close':
						array_pop($stack);
						break;
					case 'single':
						$parent->children []= (object)Array('type'=>'node',  'attributes'=>$value->attributes, 'tag'=>$value->name, 'children'=>Array());
						break;
				}
			}
		}

		return $root;
	}



	private function format_r($node, $email) {
		switch ($node->type) {
			case 'text':
				return $node->value;
				break;
			case 'node':
			case 'root':
				$text = '';

				if ($node->tag == 'form') {
					foreach ($node->attributes as $attribute => $value) {
						switch($attribute) {
							case 'bu':
								$text .= $this->getButtonHTML($this->postActions[$value]);
								break;
							case 'nosuccess':
								if (isset($_GET['success'])) {
									return '';
								}
								break;
							case 'messages':
								if (! $email) {
									switch($value) {
										case 'welcome':
											if (! isset($_GET['success'])) {
												$text .= '<div class="'.$this->cfg('class_welcome').'">'.$this->welcomeMessage.'</div>';
											}
											break;
										case 'note':
											if (! isset($_GET['success'])) {
												$text .= '<div class="'.$this->cfg('class_note').'">'.$this->noteMessage.'</div>';
											}
											break;
										case 'status':
											$text .= $this->print_status_messages();
											break;
										case 'success':
											if (isset($_GET['success'])) {
												$text .= $this->onSuccess();
											}
										default:
											if (! isset($_GET['success'])) {
												$text .= '<div class="'.$this->cfg('class_welcome').'">'.$this->welcomeMessage.'</div>';
											} else {
												$text .= $this->onSuccess();
											}
											$text .= $this->print_status_messages();
											if (! isset($_GET['success'])) {
												$text .= ($this->noteMessage)?('<div class="'.$this->cfg('class_note').'">'.$this->noteMessage.'</div>'):'';
											}
											break;
									}
								}
								break;
						}
					}
				} else if ($node->type != 'root') {
					$fi = $this;
					$path = preg_split('/::/', $node->tag);
					for ($i = 0; $i < sizeof($path); $i++) {
						if ($next_item = $fi->getItem($path[$i])) {
							$fi = $next_item;
						} else {
							die('No item with name "'.$path[$i].'" in '.($i == 0?('form::'.$this->name):('group::'.$path[$i-1].'"')));
						}
					}
					foreach ($node->attributes as $key => $value) {
						switch ($key) {

						}
					}
					$text .= $fi->printForm($email);

				}
				foreach ($node->children as $child) {
					$text .= $this->format_r($child, $email);
				}


				return $text;
				break;
		}
		return '';
	}

/**
 * Formats the form data given a formatted string
 *
 * @param String $format The formatted string template
 *
 * @return String the formatted result
 */
	public function format($format, $email = true) {

		if ($this->cache) {
			$key = md5($format);
			$filename = ".roofl_".$key;
			$path = $this->cacheDir.$filename;
			$tree = $this->parseFormat($format);

			if (file_exists($path)) {
				$start = microtime(true);
				$tree = json_decode(file_get_contents($path));
				$end = microtime(true);

				echo "Decode time: ".($end - $start)." seconds. <br/>";

			} else {
				$tree = $this->parseFormat($format);
				if (is_dir($this->cacheDir)) {
					$handler = fopen($path, "w");
					fwrite($handler, json_encode($tree));
					fclose($handler);
				}
			}
		} else {
			$tree = $this->parseFormat($format);
		}

		$start = microtime(true);
		$format = $this->format_r($tree, $email);
		$end = microtime(true);

		echo "Format time: " . ($end - $start) ." seconds: ";
//		exit();

		if (! $email) {
			$format = $this->printTag().$format.'</form>';
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
		$formItem->setForm($this);
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
		$_errors = $_warnings = $_successes = Array();
		foreach ($this->validators as $name => $validator) {
			if (! $continue) {
				break;
			}

			$continue = $validator($this, $_errors, $_warnings, $_successes);
		}

		foreach ($_errors as $e) {
			$errors []= Form::ME('error', $e);
		}
		foreach ($_warnings as $w) {
			$warnings []= Form::ME('warning', $w);
		}
		foreach ($_successes as $s) {
			$successes []= Form::ME('success', $s);
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



		foreach ($this->errors as $me) {
			$me->formItem->errors []= $me;
		}

		foreach ($this->warnings as $me) {
			$me->formItem->warnings []= $me;
		}

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
		return '<div class="'.$this->cfg('class_success').'">'.$this->successMessage.'</div>';
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

		if (isset($_GET['success'])) {
			return $this->onSuccess();
		} else {
			$html .= '<div class="'.$this->cfg('class_welcome').'">'.$this->welcomeMessage.'</div>';

			$html .= $this->print_status_messages();

			$html .= ($this->noteMessage)?('<div class="'.$this->cfg('class_note').'">'.$this->noteMessage.'</div>'):'';

			$html .= $this->printTag();

			$html .= '<'.(($nameAbove)?'div':'table').' class="'.$this->cfg('class_form').'">';
			foreach ($this->items as $name => $item) {
				$html .= $item->printRow(false, $nameAbove)."\n";
			}
			$html .= ((! $nameAbove)?('<tr class="'.$this->cfg('class_buttongroup').'"><td></td><td>'.$this->buttonHTML.'</td></tr></table>'):('<div class="'.$this->cfg('class_buttongroup').'">'.$this->buttonHTML.'</div></div>')).'</form>';

			if ($this->js_files) {
				$js = '<script type="text/javascript">';
				$this->js_files = array_unique($this->js_files);
				foreach ($this->js_files as $js_file) {
					$js .= file_get_contents($this->js_dir.$js_file)."\n";
				}
				$js .= '</script>';
				$html = $js.$html;
			}

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
		if ($this->mailCSS) {
			$html .= '<style>'.$this->mailCSS.'</style>';
		}
		return $html;
	}

	public function sendHTMLEmail($subject, $fromAddress, $fromName, $html, $to, $replyTo=Array(), $cc=Array(), $bcc = Array()) {

		$mail = new PHPMailer();
		$mail->isHTML(false);

		$mail->Subject = $subject;
		$mail->Body = $html;

		$nostyle = preg_replace('/<style(.*)\/style>/s', '', $html);
		$spaced = preg_replace('/(class="'.$this->cfg('class_fieldname').'".*?\>)(.*?)(<\/)/', '$1$2   $3', $nostyle); // add spacing for the alt body.

		$mail->AltBody = strip_tags($spaced);

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

		$mail->Send();
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

		$nostyle = preg_replace('/<style(.*)\/style>/s', '', $html);
		$spaced = preg_replace('/(class="'.$this->cfg('class_fieldname').'".*?\>)(.*?)(<\/)/', '$1$2   $3', $nostyle); // add spacing for the alt body.

		$mail->AltBody = strip_tags($spaced);

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