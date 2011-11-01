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

require_once('class.formitem.php');

class FI_File extends FormItem {

	protected $acceptableExtensions;

	protected $maxSize;
	protected $includeInEmail;
	protected $uploadDir;
	protected $allowMultiple;
	protected $maxFiles;

	protected $successMessage;

	protected $error_types;

	protected $previousFiles;

	protected $move_map;


/**
 * Creates a new FI_File
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'maxSize'=>0,
			'includeInEmail'=>true,
			'uploadDir' => 'uploads/',
			'uploadDirFS' => $this->cfg('dir_uploads'),
			'rel' => NULL,
			'allowMultiple' => false,
			'maxFiles' => '1',
			'acceptableExtensions' => Array(),
			'target' => '_blank',
			'error_types' => Array(
				1=>'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
				2=>'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
				3=>'The uploaded file was only partially uploaded.',
				4=>'No file was uploaded.',
				6=>'Missing a temporary folder.',
				7=>'Failed to write file to disk.',
				8=>'A PHP extension stopped the file upload.'
			),
			'formAttributes' => Array('enctype'=>'multipart/form-data')
		);

		$this->move_map = Array();

		$this->previousFiles = Array();
		$this->merge($options, $defaultValues);
		foreach($this->acceptableExtensions as $key => $value) {
			$this->acceptableExtensions[$key] = strtoupper($value);
		}
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Number $maxSize The max file size in bytes. 0 indicates there is no limit; Default:'0'
 * @param Bool $includeInEmail Send this file to the email recipients as an attachment; Default:true
 * @param String $upoadDir The path to upload the file(s) to; Default:'uploads'
 * @param Bool $allowMultiple Allows this form item to upload multiple files; Default:false
 * @param Number $maxFiles If allowMultiple is allowed, the number of files to allow; Default:1
 * @param Array $acceptableExtensions The default true or false data; Default:Array()
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'maxSize'=>self::DE('integer', 'The max file size in bytes. 0 indicates there is no limit', '0'),
			'includeInEmail'=>self::DE('bool', 'Send this file to the email recipients as an attachment', 'true'),
			'uploadDir'=>self::DE('path', 'The path to upload the file(s) to', '\'uploads/\''),
			'allowMultiple'=>self::DE('bool', 'Allows this form item to upload multiple files.', 'false'),
			'maxFiles'=>self::DE('integer', 'If allowMultiple is allowed, the number of files to allow', '1'),
			'acceptableExtensions'=>self::DE('array', 'A list of acceptable extensions (case insensitive)', 'Array()'),
		);
	}



/**
 * Prints the Javascript required to allow for advanced manipulation
 */
	public function print_js() {
		global $BASE_SCRIPT_ADDED;
		$this->form->js_files []= 'file.js';
		$script = '<script type="text/javascript">var file_uploader_'.$this->name().' = new file_uploader("'.$this->name().'", '.$this->maxFiles.', "'.$this->rel.'", "'.$this->target.'");';
		foreach ($this->previousFiles as $id => $IN) {
			$script .= "\n".'file_uploader_'.$this->name().'.add_existing_file("'.$IN->filename.'", "'.$this->uploadDir.$IN->filename.'", "'.$IN->id.'");';
		}
		$script .= '</script>';
		return $script;
	}


/**
 * Unlinks files, as well as (optionally) clears information from the table
 *
 * @param String $table The name of the table to remove information from (optional)
 * @param String $id_field The name of the field which uniquely identifies the file (optional)
 */
	public function remove($table = NULL, $id_field = NULL) {
		$file_remove = $this->value();
		$file_remove = $file_remove['removed'];

		$ids = Array();
		$files = Array();
		foreach ($file_remove as $file_IN) {
			if (@unlink($file_IN->dir.$file_IN->filename)) {
				$ids []= (int)$file_IN->id;
			}
		}
		if ($table !== NULL && $ids) {
			$sql = 'DELETE FROM '.$table.' WHERE '.$id_field.' IN ('.join(', ', $ids).')';
			tep_db_query($sql);
		}
	}


/**
 * Description for function move()
 *
 * @param String $directory Moves the files to the target directory;
 *
 * @return Array The array of additional files successfully moved.
 */
	public function move($directory = NULL) {
		$file_uploads = $this->value();
		$file_uploads = $file_uploads['added'];

		if (! $file_uploads) {
			return false;
		}
		if (! is_array($file_uploads)) {
			$file_uploads = Array($file_uploads);
		}

		if ($directory === NULL) {
			$directory = $this->uploadDir;
		}



		$filenames = Array();
		foreach ($file_uploads as $file_upload) {

			$tmp = $file_upload['tmp_name'];
			if (isset($this->move_map[$tmp])) {
				$tmp = $this->move_map[$tmp];
			}
			$filename = basename($file_upload['name']);

			if (! get_magic_quotes_gpc()) {
				$filename = addslashes($filename);
			}

			$path = $directory.$filename;
			$ext = $this->extension($filename, $base);

			$pre = 0;
			while (file_exists($directory.$filename)) {
				$pre++;
				$filename = $base.$pre.".".$ext;
			}

			$path = $directory.$filename;

			if (move_uploaded_file($tmp, $path)) {
				if (file_exists($path)) {
					$this->move_map[$tmp] = $path;
					$filenames [] = $filename;
				}
			}
		}

		$this->moved_to = $directory;

		return $filenames;
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "File";
 */
	public static function getType() {
		return "File";
	}



/**
 * Struct for containing file information.
 *
 * @param mixed $filename
 * @param mixed $dir
 * @param mixed $id
 *
 * @return Object The struct containing file information
 */
	public static function IN($filename, $dir, $id) {
		return (object)Array(
			'filename'	=> $filename,
			'dir'		=> $dir,
			'id'		=> $id,
			'is_in'		=> true
		);
	}


/**
 * Gets or Sets the value of the FormItem
 *
 * @param Bool $input Providing an input indicates that the FormItem should be printed with that default.
 *
 * @return Bool If using this function as a Getter, gets the value of the item.
 */
	public function value($input = NULL) {
		if ($input === NULL) { // GET
			$value = '';
			if ($this->allowMultiple) {
				$value = Array();
				$hidden_name = $this->name().'_hidden';
				$updated_old_files = Array();
				if ($_POST[$hidden_name]) {
					$updated_old_files = split(';', $_POST[$hidden_name]);
					$updated_old_files = array_flip($updated_old_files);
				}
				$removed_files = Array();
				$all_files = Array();
				foreach ($this->previousFiles as $previousFile) {
					if (! isset($updated_old_files[$previousFile->id])) {
						$removed_files []= $previousFile;
					} else {
						$all_files []= $previousFile;
					}
				}

				$added_files = Array();
				foreach ($_FILES as $name => $file) {
					if (str_replace($this->name(), '', $name) !== $name && $file['name']) {
						$added_files []= $file;
						$all_files []= $file['name'];
					}
				}

				$value = Array('removed'=>$removed_files, 'added'=>$added_files, 'all'=>$all_files);
				return $value;
			} else {
//				print_r($_FILES);
//				print_r($this->name());
				$added = isset($_FILES[$this->name()])&&$_FILES[$this->name()]?Array($_FILES[$this->name()]):Array();
				$value = Array('removed'=>Array(), 'added'=>$added, 'all'=>$added);
			}
			return $value;
		} else { // SET
			$this->previousFiles = Array();
			foreach ($input as $in) {
				if (! $in->is_in) {
					echo 'Please set FI_File values using an array of FI_File::IN($filename, $dir, $id) objects<br/>';
					exit();
				}
				if ($in->filename) {
					$this->previousFiles[$in->id] = $in;
				}
			}
		}
	}

/**
 * Adds the form info to the DatabaseForm object()
 *
 * @param DatabaseForm $dbForm The DatabaseForm to add fields to
 */
	public function addToDB(&$dbForm) {
		global $config;
		$added = $this->value();
		$added = $added['added'];
		$filenames = Array();
		foreach ($added as $file) {
			$filename = $file['tmp_name'];
			while (isset($this->move_map[$filename])) {
				$filename = $this->move_map[$filename];
			}
			$filenames [] = Array('src'=>$filename, 'name'=>$file['name']);
		}
		$dbForm->addFile($dbForm->dbName($this->label), $filenames, $this->uploadDirFS, $this->uploadDir);
		if (mysql_error()) {
			echo mysql_error();
		}
	}


/**
 * Adds the files to the Email
 *
 * @param Array $files The list of files to add to
 */
	public function addFiles(&$files) {
		$value = $this->value();

		if ($this->includeInEmail) {
			foreach ($value['added'] as $file_info) {
				if (isset($this->move_map[$file_info['tmp_name']])) {
					$files[$this->move_map[$file_info['tmp_name']]] = $file_info['name'];
				} else {
					$files[$file_info['tmp_name']] = $file_info['name'];
				}
			}
		}
	}


/**
 * Formats the unit to display in an easily readable format
 *
 * @param Integer $size The size of the file
 *
 * @return String The formatted string
 */
	private function nearestUnitSize($size) {
		$units = Array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
		$unit_index = 0;
		while ($size > 100 && isset($units[$unit_index])) {
			$size /= 1024;
			$unit_index ++;
		}
		return round($size, 2).$units[$unit_index];
	}


/**
 * Gets the description for the php error code
 *
 * @param Integer $error_code The error code as provided by $_FILES
 *
 * @return String The error string of
 */
	public function get_error($error_code) {
		return $this->error_types[$error_code];
	}


/**
 * Description for function extension()
 *
 * @param mixed $filename
 * @param mixed &$base
 *
 * @return
 */
	public function extension($filename, &$base = NULL) {
		$split = split('\.', $filename);
		$last = '';
		$accum = '';
		foreach ($split as $str) {
			if ($last) {
				$accum .= $last . ".";
			}
			$last = $str;
		}
		$base = $accum;
		return strtoupper($last);
	}


/**
 * Performs native validation within the FormItem.
 *
 * @param Array $errors An array in which to place errors
 * @param Array $warnings An array in which to place warnings
 * @param Bool $continue A Bool to indicate whether or not the containing FI_Group or Form should break upon completion
 */
	public function check(&$errors, &$warnings, &$continue) {
		$values = $this->value();

		parent::check($errors, $warnings, $continue);

		foreach ($values['added'] as $value) {

			if ($value['name'] && $this->acceptableExtensions) {
				$extension_list = array_flip($this->acceptableExtensions);
				$extension = $this->extension($value['name'], $base);


				if (! array_key_exists($extension, $extension_list)) {
					$errors [] = Form::ME('error', 'Invalid extension: <em>'.$extension.'</em>. Acceptable extensions include: '.join(', ', $this->acceptableExtensions), $this);
				}
			}
			if ($value['name'] && $this->maxSize !== 0) {
				if ($value['size'] > $this->maxSize) {
					$errors [] = Form::ME('error', 'Image of size <em>'.$this->nearestUnitSize($value['size']).'</em> exceeds maximum size of <em>'.$this->nearestUnitSize($this->maxSize).'</em>', $this);
				}
			} else if ($value['name'] && $value['error'] != 0) {

				$errors [] = Form::ME('error', 'Error uploading file: ' . $this->get_error($value['error']), $this);
				// Perhaps this should be reported to the admin....
			}
		}
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		$html = $this->printPre().'<input type="file" id="'.$this->name().'" name="'.$this->name().'" />'.$this->printPost().$this->printDescription();

		if ($this->allowMultiple) {
			$html .= $this->print_js();
		}
		return $html;
	}


/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail() {
		$value = $this->value();

		if ($value['added']) {
			function get_name($file) { return $file['name']; }
			$names = array_map('get_name', $value['added']);
			return '<em>'.join('</em><br/><em>', $names).'</em>';
		} else {
			return '<em>None</em>';
		}
	}

}