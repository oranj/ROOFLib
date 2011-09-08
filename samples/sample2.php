<?php

error_reporting(E_ALL ^ E_NOTICE);

require_once (dirname(__FILE__).'/../lib/classes/class.form.php');

$FORM_DEBUG = true;

$form = new Form('formname');
$form->setWelcomeMessage('Thank you for contacting us. Please fill out the following form and we will be contacting you shortly.');
$form->setSuccessMessage('Thanks again for your interest. We will be in contact with you soon. Have a great day.');
$form->setNoteMessage('Required Fields <span class="required">*</span>');

$form->setButtons(Form::BU('Submit', 'submit'));

// ----- Add the form items here. -----

$form->addItem(new FI_Text("name", "Name", Array('required'=>true)));
$form->addItem($toggle_end = new FI_Bool("bool", "Toggle End"));
$toggle_end->makeDependent('end');
$form->addItem(new FI_Bool("bool2", "Bool2", Array('mode'=>'select')));
$form->addItem(new FI_Bool("bool3", "Bool3", Array('mode'=>'check')));
$form->addItem(new FI_Email("end", "End", Array()));

$form->addItem($switch = new FI_Switch('switcher', 'Switcher', Array('mode'=>'radio')));
$switch->addItem(new FI_HTML('yes', 'Yes', Array('html'=>'YES YES YES YES YES')));
$switch->addItem(new FI_HTML('no', 'No', Array('html'=>'NO NO NO NO NO')));
$switch->addItem(new FI_HTML('maybe', 'Maybe', Array('html'=>'MAYBE MAYBE MAYBE MAYBE')));

$form->setButtons(Form::BU('Button', 'button'));


// --------------- End ----------------

if (($action = $form->action()) && $form->validate()) {
	$value = $form->value();
 //   $form->storeEntry();
//    $form->sendEmail("Subject", $value['email'], $value['name'], Array('John Smith' => 'johnsmith@example.com'));
	header('Location: ?success');
}


?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script>
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

$format = <<<FORMAT
<{form[messages]}/>
<{form[nosuccess][otheratt]}>
<table>
	<tr>
		<td>Name: </td><td><{name}/></td>
	</tr>
	<tr><td colspan="4"><table><{switcher}/></table></td></tr>
	<tr>
		<td>Start: </td>
		<td><{start}/></td>
		<td>End: </td>
		<td><{end}/></td>
	</tr>
	<tr>
		<td></td>
		<td><{form[bu=button]}/></td>
	</tr>
</table>
</{form[nosuccess]}>
FORMAT;

$start = microtime(true);

echo $form->printForm();//format($format , false); /**/
$finish = microtime(true);

?>


<body>
</body>
</html>
