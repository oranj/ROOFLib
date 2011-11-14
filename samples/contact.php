<?php

#error_reporting(E_ALL);

require_once(dirname(__FILE__).'/../lib/classes/class.form.php');
require_once(dirname(__FILE__).'/../lib/data/statesprovinces.php');
require_once(dirname(__FILE__).'/../lib/data/countries.php');

mysql_connect('localhost', 'ecw', 'dbman');
mysql_select_db('ecw_newforms_base');

$form = new Form('contact');
$form->required_attr = false;

#$form->addItem(new FI_Flip('year', 'Year:', array('options'=>range(1990, 2021), 'inc_text'=>'', 'dec_text'=>'')));
$form->setSuccessMessage('Success!');

$form->addItem(new FI_Separator('yearsep', 'Contact Information', array('separator'=>'', 'help'=>'<strong>Your information is safe with us!</strong><p>We will not distribute any personal information we receive from you.</p>')));


$form->addItem($firstname = new FI_Text('firstname', 'First Name', array('required'=>true)));
$form->addItem(new FI_Text('lastname', 'Last Name', array('required'=>true)));
$form->addItem(new FI_Text('company', 'Company'));
$form->addItem(new FI_Email('email', 'Email', array('required'=>true)));
$form->addItem(new FI_Phone('phone', 'Telephone Number', array('required'=>true)));
$form->addItem(new FI_Phone('fax', 'Fax Number'));



$form->addItem(new FI_Separator('locationsep', 'Location', array('separator'=>'')));
$form->addItem(new FI_Text('address1', 'Street Address'));
$form->addItem(new FI_Text('address2', 'Address 2', array('hide_label'=>true)));
$form->addItem(new FI_Text('city', 'City'));
$form->addItem(new FI_Select('stateprovince', 'State / Province', array('options'=>$STATESPROVINCES, 'required'=>true)));
$form->addItem(new FI_Text('postal', 'Zip  / Postal Code'));
$form->addItem(new FI_Select('country', 'Country', array('options'=>$COUNTRIES, 'required'=>true)));


$form->addItem(new FI_Separator('messagesep', 'Message', array('separator'=>'')));
$form->addItem(new FI_Textarea('message', 'Comments, questions or details'));

$form->addItem(new FI_File('file', 'Upload a document', Array('maxFiles'=>5, 'allowMultiple'=>true)));

$form->addItem(new FI_Captcha('captcha', 'Are you human?'));

$form->setButtons(Form::BU('Send', 'send'));

if ($form->action() && $form->validate()) {
	$form->storeEntry();
	header('Location: ?success');
	exit();
} else if (! $form->action() ) {
	$form->value(array(
		'year'=>7
	));
}



?>

<style type="text/css">

	#fi_popUp { width:400px; min-height:150px; z-index:100000; position:fixed; left:50%; margin-left:-200px; top:50%; margin-top:-100px; box-shadow: 0px 4px 10px #000; border:1px solid #333; background-color:#fff; font-family:Arial, sans-serif; background-image: -webkit-gradient(
	linear,
	left bottom,
	left top,
	color-stop(0.33, rgb(224,224,224)),
	color-stop(0.84, rgb(255,255,255))
);
background-image: -moz-linear-gradient(
	center bottom,
	rgb(224,224,224) 33%,
	rgb(255,255,255) 84%
); }
	#fi_popUp .header { font-weight:bold; color:#fff; background-color:#333; font-size:14px; padding:3px; }
	#fi_popUp .message { padding:5px; }
	#fi_popUpModal {
		width:100%; height:100%; /*background:url('../resources/pinstripe.png') repeat; z-index:99999; */
		background: -moz-radial-gradient(50% 50%, ellipse closest-side, #666 0%,#111 100%);
		background: -o-radial-gradient(50% 50%, ellipse closest-side, #666 0%,#111 100%);
		background: -ms-radial-gradient(50% 50%, ellipse closest-side, #666 0%,#111 100%);
		background: -webkit-gradient(radial, 50% 50%, 0, 40% 40%, 60 from (#666), to (#111));
		background: radial-gradient(50% 50%, ellipse closest-side, #666 0%,#111 100%);
		opacity:0.8; position:fixed; top:0px; left:0px;

	}



	.fi_icon { float:left; padding:2px; }
	.fi_close { float:right; padding:2px; }

	.rf_form, .rf_note, .rf_welcome, .rf_success, .rf_error, .rf_warning { font-family:Arial, sans-serif; font-size:12px;}

	h1, .sepLabel { border-bottom:1px solid #ccc; color:#17345C; font-family:Arial, sans-serif; }

	.sepLabel {  font-weight:bold; padding-top:20px; font-size:14px; text-transform:uppercase; }
	.rf_name, .rff_name { font-weight:bold; padding-top:5px; margin-top:10px; vertical-align:top; border-bottom:1px solid #ccc; }
	.rf_value { padding-top:5px; vertical-align:top; }


	#rfi_stateprovince select { width:200px; }
	#rfi_postal input { width: 140px;   }
	.rf_fbu  {clear:both; padding-top:10px; }

	textarea { width: 350px; height:150px; }


	#rfi_captcha {
		margin-top:10px;
	}
	#rfi_captcha .rf_name {
		padding-top:0px;
		border-bottom:1px solid #ccc;
		margin-bottom:5px;
	}

	.rf_req { color:#c00; font-weight:bold;  }
	.rf_note { font-style:italic; font-weight:normal; }

	.rf_value .rf_desc { font-style:italic; font-weight:normal;  }
	.rf_name .rf_desc { font-style:italic; font-weight:normal; }
	.rf_success { background-color:#D6EBFF; padding:25px; border:1px solid #99CCFF; color:#000; font-weight:bold; }
	.rf_error { background-color:#FFCCCC; padding:5px; border:1px solid #FF0000; color:#c00; font-weight:bold; }
	.rf_error ul, .rf_warning ul { margin:5px 0px; color:#000; }
	.rf_error li, .rf_warning li { font-weight:normal; }
	.rf_warning { background-color:#FFFFCC; padding:5px; border:1px solid #CC9900; color:#000; font-weight:bold; }

	/* Round */
	#rfi_captcha, .rf_warning, .rf_error, .rf_success {
		border-radius:5px;
		-webkit-border-radius:5px;
		-moz-border-radius:5px;
		-o-border-radius:5px;
	}

	/* Clear lefts */
	#rfi_country, .fbu, #rfi_email, .sepLabel, #rfi_company { clear: left; }

	/* Float lefts */
	#rfi_postal, #rfi_stateprovince, #rfi_firstname, #rfi_lastname, #rfi_phone, #rfi_fax { float:left;  }
	/* left elements */
	#rfi_stateprovince, #rfi_firstname, #rfi_phone { margin-right:10px;  }

	/* Full width inputs */
	#rfi_email input, #rfi_address1 input, #rfi_address2 input, #rfi_country select, #rfi_city input, #rfi_company input { width:350px; }
	#rfi_email, #rfi_country, #rfi_captcha { width:350px; }

	/* half widths */
	#rfi_firstname input, #rfi_lastname input, #rfi_phone input, #rfi_fax input { width:170px; }
	.rfc_fi_flip_inc:active { background:#333; opacity:0.3;}
	.rfc_fi_flip_dec:active {  background:#333; opacity:0.3; }

	#rfi_year .rfc_fi_flip_outer { background:url('../resources/flipleft.png') left no-repeat; height:50px; width:100px; position:relative;  }
	#rfi_year .rfc_fi_flip_inc { padding:0px; margin:0px; height:25px; margin-top:-50px; width:100px; position:relative; z-index:2; }
	#rfi_year .rfc_fi_flip_dec { padding:0px; margin:0px; height:25px; width:100px; position:relative; z-index:2; }

	#rfi_year #rfi_text { background:url('../resources/flipright.png') right no-repeat; padding:15px; margin-right:-5px; line-height:20px; font-size:21px; font-family:Helvetica, Arial, sans-serif; color:#17345C;}

</style>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script>
</script>
<h1>Contact</h1>

<?php echo $form->printForm(true); ?>