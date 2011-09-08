<?php

error_reporting(E_ALL ^ E_NOTICE);

require_once (dirname(__FILE__).'/../lib/classes/class.form.php');

$FORM_DEBUG = true;

$form = new Form('formname');
$form->setWelcomeMessage('');
$form->setSuccessMessage('');
$form->setNoteMessage('Required Fields <span class="required">*</span>');

$form->setButtons(Form::BU('Submit', 'submit'));

// ----- Add the form items here. -----

$form->addItem($dategroup = new FI_Group('date', 'Date'));


$dategroup->addItem(new FI_Date('starttime', 'Start:'));
$dategroup->addItem(new FI_Date('endtime', 'End:'));
$dategroup->addItem($bool = new FI_Bool('repeats', 'This event will repeat', Array('mode'=>'check', 'dependants'=>Array('date_rrule', 'date_until'))));

$dategroup->addItem($rrule = new FI_Group('rrule', 'Recurrences', Array('form_class'=>'inset')));

$rrule->addItem($switch_fi = new FI_Switch('switch', '', Array('mode'=>'radio')));

$switch_fi->addItem($repeat_daily 	= new FI_Group('daily', 'Daily', Array('field_tag'=>'div', 'label_tag'=>'div', 'hide_label'=>true)));
$repeat_daily->addItem($daily_toggle = new FI_Toggle('rd', 'Repeat'));
$daily_toggle->addItem($every_n_day = new FI_Select('devery', 'Every', Array('options'=>range(1, 20), 'hide_label'=>true, 'post'=>' Day(s)')));
$daily_toggle->addItem($every_w_day = new FI_HTML('weekday', 'Every Weekday'));


$switch_fi->addItem($repeat_weekly = new FI_Group('weekly', 'Weekly', Array('field_tag'=>'div', 'label_tag'=>'div', 'hide_label'=>true)));

$repeat_weekly->addItem(new FI_Select('wevery', 'Every', Array('options'=>range(1, 52), 'post'=>' Week(s)')));

$weekday_list = Array('SU'=>'Sun', 'MO'=>'Mon', 'TU'=> 'Tue', 'WE'=> 'Wed', 'TH'=>'Thu', 'FR'=>'Fri', 'SA'=>'Sat');
$repeat_weekly->addItem($weekday_list = new FI_Matrix('wdays', 'On:', Array('data'=>Array($weekday_list))));

$month_every_options = Array('' => 'Every', '1'=>'The First', '2'=>'The Second', '3'=>'The Third', '4'=>'The Fourth', '5'=>'The Fifth', '-1'=>'The Last');
$day_names = Array('SU'=>'Sunday', 'MO'=>'Monday', 'TU'=>'Tuesday', 'WE'=>'Wednesday', 'TH'=>'Thursday', 'FR'=>'Friday', 'SA'=>'Saturday');
$month_names = Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

$switch_fi->addItem($repeat_monthly = new FI_Group('monthly', 'Monthly', Array('field_tag'=>'div', 'label_tag'=>'div', 'hide_label'=>true)));
$repeat_monthly->addItem(new FI_Select('mevery', 'Every Month(s)', Array('options'=>range(1, 12),'hide_label'=>true, 'pre'=>'Every', 'post'=>'Month(s)')));
$repeat_monthly->addItem($monthly_toggle = new FI_Toggle('rm', 'Repeat'));
$monthly_toggle->addItem($month_comp = new FI_Group('meverycomp', 'Every', Array('field_tag'=>'div', 'label_tag'=>'div', 'hide_label'=>true)));
$month_comp->addItem(new FI_Select('mdayevery', '', Array('options'=>$month_every_options)));
$month_comp->addItem(new FI_Select('mdayname', '', Array('options'=>$day_names)));
$monthly_toggle->addItem($monthday_list = new FI_Matrix('mdays', 'On Days:', Array('hide_label'=>true, 'data'=>Array(
	Array('1', '2', '3', '4', '5', '6', '7'),
	Array('8', '9', '10', '11', '12', '13', '14'),
	Array('15', '16', '17', '18', '19', '20', '21'),
	Array('22', '23', '24', '25', '26', '27', '28'),
	Array('29', '30', '31')
))));

$switch_fi->addItem($repeat_annually = new FI_Group('yearly', 'Annually', Array('field_tag'=>'div', 'label_tag'=>'div', 'hide_label'=>true)));
$repeat_annually->addItem(new FI_Select('yevery', 'Every Year(s)', Array('options'=>range(1, 10), 'pre'=>'Every', 'post'=>'Years(s)', 'hide_label'=>true)));
$repeat_annually->addItem($yearly_toggle = new FI_Toggle('ry', 'Repeat'));
$yearly_toggle->addItem($year_comp = new FI_Group('yeverycomp', 'Every', Array('field_tag'=>'div', 'label_tag'=>'div', 'hide_label'=>true)));

$year_comp->addItem(new FI_Select('ymonth', '', Array('options'=>$month_names)));
$year_comp->addItem(new FI_Select('yday', '', Array('options'=>range(1, 31))));

$yearly_toggle->addItem($year_the = new FI_Group('ythe', '', Array('field_tag'=>'div', 'label_tag'=>'div', 'hide_label'=>true)));
$year_the->addItem(new FI_Select('nth', '', Array('options'=>$month_every_options)));
$year_the->addItem(new FI_Select('day', '', Array('options'=>$day_names)));
$year_the->addItem(new FI_Select('month', '', Array('options'=>$month_names)));



$dategroup->addItem($until = new FI_Group('until', 'Until', Array('form_class'=>'inset')));

$until->addItem($until_fi = new FI_Switch('until', 'Repeats Until', Array('mode'=>'radio')));
$until_fi->addItem(new FI_HTML('noend', 'No end date'));
$until_fi->addItem(new FI_Select('numrepeats', 'Number of repeats', Array('hide_label'=>true, 'pre'=>'End After', 'post'=>'Occurrences', 'options'=>range(1, 20))));
$until_fi->addItem(new FI_Date('enddate', 'End Date', Array('hide_label'=>true, 'pre'=>'Repeat Until: ')));


$form->setButtons(Form::BU('Button', 'button'));


// --------------- End ----------------

if (($action = $form->action()) && $form->validate()) {
	$value = $form->value();
	dump($value);
//	header('Location: ?success');
}


?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="http://test.ecreativeworks.com/com.eventssure.201104/includes/css/main.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script>
<style>
.success { background-color:#D6EBFF; padding:25px; border:1px solid #99CCFF; color:#000; font-weight:bold; }
.error { background-color:#FFCCCC; padding:5px; border:1px solid #FF0000; color:#f00; font-weight:bold; }
.error ul { margin:5px 0px; color:#000;}
.error li, .warning li { font-weight:normal; }<td></td>
.warning { background-color:#FFFFCC; padding:5px; border:1px solid #CC9900; color:#c90; font-weight:bold; }
.warning ul { margin:5px 0px; color:#000; }
.fldName { vertical-align:top; padding-right:15px; }
.required .fldName strong { color:#c00; font-weight:bold; }
.required .fldValue input, .required .fldValue textarea { border-left: 2px solid #c00; }
.sepLabel { font-weight:bold; color:#c00; }
.css_testbool .fldValue div { float:left; }
.css_fi_group fieldset > div > div, .css_fi_group fieldset > div > div > div { float:left; }
#css_date_rrule_switch label, #css_date_until_until label { display:block; }
.form { width:700px; }
.form table { width:100%; }
#css_date_rrule_switch > .fldName, #css_date_until_until > .fldName { border-right: 1px inset #333; width:150px;  }


</style>
</head>
<?php

echo $form->printForm();//format($format , false); /**/

?>


<body>
</body>
</html>
