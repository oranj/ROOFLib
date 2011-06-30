<?php
//set config vars
$config = array();

$config['forms'] = array(
	'contact' => array('file'=>'contact.php','db'=>'contact','name'=>'Contact Us'),
	'sample1' => array('file'=>'sample1.php','db'=>'sample_1','name'=>'Sample 1'),
);

$config['remote_url'] = 'http://' . $_SERVER['SERVER_NAME'] . '/';
$config['local_url'] = 'http://' . $_SERVER['SERVER_NAME'] . '/test/ray/forms_db/';

$config['database_host'] = 'localhost';
$config['database_user'] = 'ecw';
$config['database_pass'] = 'dbman';
$config['database_base'] = 'ecw_newforms_base';

$config['results_per_page'] = 20;
$config['admin_user'] = 'admin';
$config['admin_pass'] = 'ecwquality';

//counteract magic quotes if they are enabled
if (get_magic_quotes_gpc()) {
	 function undoMagicQuotes($array, $topLevel=true) {
		  $newArray = array();
		  foreach($array as $key => $value) {
				if (!$topLevel) {
					 $key = stripslashes($key);
				}
				if (is_array($value)) {
					 $newArray[$key] = undoMagicQuotes($value, false);
				}
				else {
					 $newArray[$key] = stripslashes($value);
				}
		  }
		  return $newArray;
	 }
	 $_GET = undoMagicQuotes($_GET);
	 $_POST = undoMagicQuotes($_POST);
	 $_COOKIE = undoMagicQuotes($_COOKIE);
	 $_REQUEST = undoMagicQuotes($_REQUEST);
}
