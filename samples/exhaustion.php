<?php

require_once(dirname(__FILE__).'/../lib/classes/class.form.php');
require_once(dirname(__FILE__).'/../lib/data/statesprovinces.php');
require_once(dirname(__FILE__).'/../lib/data/countries.php');

$form = new Form('contact');
$form->required_attr = false;

#$form->addItem(new FI_Flip('year', 'Year:', array('options'=>range(1990, 2021), 'inc_text'=>'', 'dec_text'=>'')));

$form->addItem(new FI_Bool('boolradio', 'Bool:Radio', array('help'=>'I am a bool', 'post'=>'I agree with you')));
$form->addItem(new FI_Bool('boolcheck', 'Bool:Check', array('mode'=>'check', 'help'=>'I am a bool', 'post'=>'I agree with you')));
$form->addItem(new FI_Bool('boolselect', 'Bool:Select', array('mode'=>'select', 'help'=>'I am a bool', 'post'=>'I agree with you')));

$form->addItem(new FI_Checkbox('checkbox', 'Checkbox', array('options'=>Array('a', 'b', 'c'), 'help'=>'CHECKBOOOOX')));

$form->addItem(new FI_CSV('csv', 'CSV', array('help'=>'CSV!')));

$form->setButtons(Form::BU('Send', 'send'));

if ($form->action() && $form->validate()) {
} else if (! $form->action() ) {
	$form->value(array(
		'year'=>7
	));
}



?>

<style type="text/css">

	#fi_popUp { width:300px; min-height:200px; z-index:100000; position:fixed; left:50%; margin-left:-150px; top:25%; box-shadow: 5px 5px 5px #333; border:1px solid #333; background-color:#fff; font-family:Arial, sans-serif;  }
	#fi_popUp .header { font-weight:bold; color:#fff; background-color:#333; font-size:14px; padding:3px; }
	#fi_popUp .message { padding:5px; }
	#fi_popUpModal { width:100%; height:100%; background:#333; z-index:99999; opacity:0.3; position:fixed; top:0px; left:0px;}
	.fi_icon { float:left; padding:2px; }
	.fi_close { float:right; padding:2px; }

	.form, .noteMessage, .welcome, .success, .error, .warning { font-family:Arial, sans-serif; font-size:12px;}

	h1, .sepLabel { border-bottom:1px solid #ccc; color:#17345C; font-family:Arial, sans-serif; }

	.sepLabel {  font-weight:bold; padding-top:20px; font-size:14px; text-transform:uppercase; }
	.fldName { font-weight:bold; padding-top:10px; }


	#css_stateprovince select { width:200px; }
	#css_postal input { width: 140px;   }
	.fbu  { padding-top:10px; }

	textarea { width: 350px; height:150px; }


	#css_captcha {
		margin-top:10px;
	}
	#css_captcha .fldName {
		padding-top:0px;
		border-bottom:1px solid #ccc;
		margin-bottom:5px;
	}

	span.required, .required span, .noteMessage span { color:#c00; font-weight:bold;  }
	.noteMessage { font-style:italic; }

	.fldValue .descr { font-style:italic; }
	.success { background-color:#D6EBFF; padding:25px; border:1px solid #99CCFF; color:#000; font-weight:bold; }
	.error { background-color:#FFCCCC; padding:5px; border:1px solid #FF0000; color:#c00; font-weight:bold; }
	.error ul, .warning ul { margin:5px 0px; color:#000; }
	.error li, .warning li { font-weight:normal; }
	.warning { background-color:#FFFFCC; padding:5px; border:1px solid #CC9900; color:#000; font-weight:bold; }

	/* Round */
	#css_captcha, .warning, .error, .success {
		border-radius:5px;
		-webkit-border-radius:5px;
		-moz-border-radius:5px;
		-o-border-radius:5px;
	}

	/* Clear lefts */
	#css_country, .fbu, #css_email, .sepLabel, #css_company { clear: left; }

	/* Float lefts */
	#css_postal, #css_stateprovince, #css_firstname, #css_lastname, #css_phone, #css_fax { float:left;  }
	/* left elements */
	#css_stateprovince, #css_firstname, #css_phone { margin-right:10px;  }

	/* Full width inputs */
	#css_email input, #css_address1 input, #css_address2 input, #css_country select, #css_city input, #css_company input { width:350px; }
	#css_email, #css_country, #css_captcha { width:350px; }

	/* half widths */
	#css_firstname input, #css_lastname input, #css_phone input, #css_fax input { width:170px; }
	.css_fi_flip_inc:active { background:#333; opacity:0.3;}
	.css_fi_flip_dec:active {  background:#333; opacity:0.3; }

	#css_year .css_fi_flip_outer { background:url('../resources/flipleft.png') left no-repeat; height:50px; width:100px; position:relative;  }
	#css_year .css_fi_flip_inc { padding:0px; margin:0px; height:25px; margin-top:-50px; width:100px; position:relative; z-index:2; }
	#css_year .css_fi_flip_dec { padding:0px; margin:0px; height:25px; width:100px; position:relative; z-index:2; }

	#css_year #year_text { background:url('../resources/flipright.png') right no-repeat; padding:15px; margin-right:-5px; line-height:20px; font-size:21px; font-family:Helvetica, Arial, sans-serif; color:#17345C;}

</style>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script>
</script>
<h1>Contact</h1>

<pre><?php echo htmlentities(print_r($form->value(), true)); ?></pre>

<?php echo $form->printForm(); ?>