<?php

error_reporting(E_ALL ^ E_NOTICE);

require_once (dirname(__FILE__).'/../lib/classes/class.form.php');
include_once (dirname(__FILE__).'/../lib/data/states.php');
include_once (dirname(__FILE__).'/../lib/data/countries.php');

$FORM_DEBUG = true;



$form = new Form('Sample 1');
$form->setSuccessMessage("Thank you for your interest!");

$text_items = new FI_Group('texts', 'Text Items');

// returns bool to continue
function assertion($fielditem, &$errors, &$warnings) {
	if (strlen($fielditem->value()) < 3) {
		$errors []= 'Less than three... wtf?!?';
	}
	return true;
}

$form->addItem($nameField = new FI_Text('name', 'Name', Array('required'=>true)));
$form->addItem(new FI_Text('company', 'Company', Array('required'=>true)));
$form->addItem(new FI_Select('title', 'Title', Array('options'=>Array('Purchasing', 'Engineering', 'Other'))));
$form->addItem(new FI_Text('address1', 'Address 1', Array('required'=>true)));
$form->addItem(new FI_Text('address2', 'Address 2'));
$form->addItem(new FI_Text('city', 'City', Array('required'=>true)));

$form->addItem(new FI_Select('states', 'State/Province', Array('options'=>$STATES, 'required'=>true)));
$form->addItem(new FI_Text('zip', 'Postal/Zip Code', Array('required'=>true)));
$form->addItem(new FI_Select('country', 'Country', Array('options'=>$COUNTRIES, 'required'=>true)));
$form->addItem(new FI_Text('phone', 'Phone', Array('required'=>true, 'description'=>'e.g. 555-555-5555 X5555')));

$form->addItem(new FI_Text('fax', 'Contact Fax', Array('description'=>'e.g. 555-555-5555 X5555')));
$form->addItem($emailField = new FI_Email('email', 'Email', Array('required'=>true)));

$form->addItem(new FI_Select('how', 'How did you find us?', Array('options'=>Array(0=>'- Please Choose -', 'Google', 'Yahoo', 'ThomasNet', 'MSN', 'Other'))));
$form->addItem(new FI_Text('keyword', 'Keyword or phrase used to find us'));

$form->addItem(new FI_Separator('sep', ''));

$form->addItem($g1 = new FI_Group('part1', NULL, Array('forceChildNameAbove'=>true)));
$form->addItem($g2 = new FI_Group('part2', NULL, Array('forceChildNameAbove'=>true)));
$form->addItem($g3 = new FI_Group('part3', NULL, Array('forceChildNameAbove'=>true)));

$g1->addItem(new FI_Text('number', 'Part Number'));
$g1->addItem(new FI_Text('quantity', 'Quantity'));

$g2->addItem(new FI_Text('number', 'Part Number'));
$g2->addItem(new FI_Text('quantity', 'Quantity'));

$g3->addItem(new FI_Text('number', 'Part Number'));
$g3->addItem(new FI_Text('quantity', 'Quantity'));

$form->addItem($file = new FI_File('upload', 'Upload File (2MB)', Array('maxSize'=>2097152)));

$form->addItem(new FI_TextArea('comments', 'Comments'));

$form->addItem(new FI_Captcha('validation', 'Validation Code'));

if (($action = $form->action()) && $form->validate()) {

	/*$form->sendEmail(
					'Ecreativeworks Contact Us Form',
					$emailField->value(),
					$nameField->value(),
					'rminge@ecreativeworks.com',
					"<div>This is a header</div>",
					"<div> THIS IS A FOOTER</div>",
					Array('DO NOT REPLY'=> 'donotreply@example.com'),
					Array('CC' => 'cc1@example.com', 'CC2' => 'cc2@example.com'),
					Array('BCC1'=>'bcc1@example.com', 'BCC2'=>'bcc2@example.com')
	);*/

	mysql_connect('localhost', 'ecw', 'dbman');
	mysql_selectdb('ecw_newforms_base');
	$form->storeEntry();
	header('Location: ?thankyou');
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<style>
.success { background-color:#D6EBFF; padding:25px; border:1px solid #99CCFF; color:#000; font-weight:bold; }
.error { background-color:#FFCCCC; padding:5px; border:1px solid #FF0000; color:#f00; font-weight:bold; }
.error ul { margin:5px 0px; color:#000;}
.error li, .warning li { font-weight:normal; }
.warning { background-color:#FFFFCC; padding:5px; border:1px solid #CC9900; color:#c90; font-weight:bold; }
.warning ul { margin:5px 0px; color:#000; }
.fldName { vertical-align:top; padding-right:15px; }
.required .fldName strong { color:#c00; font-weight:bold; }
.required .fldValue input, .required .fldValue textarea { border-left: 2px solid #c00; }
.sepLabel { font-weight:bold; color:#c00; }
.css_testbool .fldValue div { float:left; }
.css_fi_group fieldset > div > div, .css_fi_group fieldset > div > div > div { float:left; }
</style>
</head>
<?php

echo $form->printForm();


?>


<body>
</body>
</html>
