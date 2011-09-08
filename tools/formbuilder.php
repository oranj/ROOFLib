<?php

include_once(dirname(__FILE__).'/../lib/classes/class.form.php');

session_start();

if (! isset($_SESSION['insert_no'])) {
	$_SESSION['insert_no'] = 0;
}

function dump($var, $return = false) {
	$str = "<pre>".htmlentities(print_r($var, true))."</pre>";
	if ($return) { return $str; } else { echo $str; }
}

$addform = new Form('add');

$formitems = Array();
foreach ($FORMITEMS as $name => $info) {
	$formitems[$name] = $name;
}

$addform->addItem($fi_class = new FI_Select('class', 'Class', Array('required'=>true, 'options'=>$formitems, 'post'=>'<span id="info"></span>')));
$fi_class->value('Text');
$addform->addItem(new FI_Bool('required', 'Required'));
$addform->addItem(new FI_Text('name', 'Name', Array('required'=>true, 'post'=>'Unique identifier for the element. They are namespaced within each form or group.')));
$addform->addItem(new FI_Text('label', 'Label', Array('required'=>true, 'post'=>'Label for the element. This should be human readable text.')));
$addform->addItem(new FI_Text('description', 'Description', Array('post'=>'Optional text to appear underneath the form item.')));

$addform->setButtons(Form::BU('Insert', 'insertRow()', 'script'));

if (isset($_REQUEST['class'])) {
	$class = $_REQUEST['class'];
	$class = (($class !== 'FormItem')?'FI_':'').$class;
	$valid_insert = ($_REQUEST['name'] && $_REQUEST['label']);
	if (isset($_REQUEST['info'])) {
		echo $FORMITEMS[$_REQUEST['class']];
	} else if ($valid_insert) {
		$label = trim($_REQUEST['label']);
		$name = trim($_REQUEST['name']);
		if (isset($_REQUEST['code'])) {
			$_SESSION['insert_no'] ++;
			$insert_form = new Form('insert_'.$_SESSION['insert_No']);
			$description = FormItem::describe($class);
			foreach ($description as $key => $data) {
				$c = '';

				if ($key == 'required' && $_REQUEST['bool']) {
					$data['default'] = 'true';
					$c = ' attr_enabled';
				}
				if ($key == 'description' && $_REQUEST['description']) {
					$data['default'] = '"'.$_REQUEST['description'].'"';

					$c = ' attr_enabled';
				}
				$strs [] = '<span class="attr'.$c.'">"<a href="javascript:void(0);" class="hoverhide">'.$key.'<span class="hoverhidden"><strong>'.$data['type'].':</strong> '.$data['info'].'</span></a>"=>'.$data['default'].', </span>';
			}

			echo json_encode(Array('id'=>'formitem_'.$_SESSION['insert_no'], 'div'=>'<div id="formitem_'.$_SESSION['insert_no'].'" class="formitem">$form->addItem(new '.$class.'("<span class="formname">'.strtolower(str_replace(' ', '', $name)).'</span>", "<span class="formlabel">'.$label.'"</span>, <span class="attrlist">Array('.join('', $strs).')</span>));'."</div>\n"));
		} else {
			$_SESSION['insert_no'] ++;
			$insert_form = new Form('insert_'.$_SESSION['insert_No']);
			$description = FormItem::describe($class);

			echo "<tr><td>".$class."</td><td>".strtolower(str_replace(' ', '', $name))."</td><td>".$label."</td><td>".dump($description, true)."</td></tr>";
		}
	}
	exit();
}


?>

<html>
<head>
<style>

	[contenteditable="true"] {
		background-color:#ffefef;
		border-radius:6px;
		cursor:text;
	}

	#code {
		font-family:monospace;
		border:2px inset;
		overflow:scroll;
		height:600px;
		padding:5px;
		white-space:nowrap;
	}

	.formitem .close {
/*		display:none; /**/
	}

	.formitem:hover .close {
		display:block;
	}
/*
	.hoverhide {
		font-style:italic;
		position:relative;
	}

	.hoverhide .hoverhidden {
		font-style:normal;
		border: 1px solid #DDDDDD;
		background-color:#fff;
		display: block;
		padding: 5px;
		position: absolute;
		width: 200px;
/*		display:none; //
	}*/

.hoverhide {
	cursor: help;
	position: relative;
	text-decoration: none;
	border-bottom:1px dashed #88f;
	color:#000;

}
.hoverhide > .hoverhidden {
	background: none repeat scroll 0 0 #FFFFCC;
	border: 1px solid #880;
	box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.4);
	display: none; /**/
	left: 0;
	font-family:Helvetica, Arial, sans-serif;
	margin-left: -5px;
	max-height: 300px;
	position: absolute;
	width: 300px;
	color:#000;
	z-index: 3000;
	padding:5px;
	font-weight:normal;
	white-space:normal;
}

.togglable {
	cursor:pointer;
	border-bottom:1px dashed #c00;
}

.hoverhide:hover > .hoverhidden {
	display: block;
}



.attrlist.hover { width:300px; background-color:#efefff; border-radius:8px; position:absolute; /*padding:30px; margin:-30px; */ box-shadow:5px 5px 5px #335; z-index:2;}
.attr.hover { border:1px solid #669; border-radius:4px; padding:0px; cursor:pointer; color:#fff; background-color:#333; }
.attr.hover a { color:#fff !important; }

.attr { display:none; padding:1px; color:#333; }
.attrlist.hover .attr { display:block; }
.attr.attr_enabled { display:inline; }
.attrlist.hover .attr_enabled { display:block; }
.attrlist.hover .attr_enabled { font-weight:bold; color:#000; text-decoration:underline; }

.attr_enabled.hover { border:1px solid #003; border-radius:4px; padding:0px; cursor:pointer; color:#003; background-color:#ddd; }

.hoverhide:hover {
	color:#00c;
}

.attrlist.hover .attr:before, .attrlist.hover .attr.hover:before { border:1px solid #000; color:#000; font-weight:bold; background-color:#fff; width:10px; height:10px; content:"[ ]" }
.attrlist.hover .attr.attr_enabled:before {  content:"[X]" }

</style>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.1.min.js"></script>
<script type="text/javascript">

function insertRow() {
	getRow($('#f_add #css_class select option:selected').attr('value'), $('#f_add #css_name input').attr('value'), $('#f_add #css_label input').attr('value'));
}

function getRow(_class, _name, _label) {
	$.get('formbuilder.php', {'code':true, 'bool':$('[name="required"]:checked').attr('value'), 'class':_class, 'description':$('#css_description input').attr('value'), 'name':_name, 'label':_label}, codeSuccess);
}

function rowSuccess(data) {
	clear();
	$('#insert').append($(data));
}

function codeSuccess(data) {
	clear();
	data = $.parseJSON(data);

	$('#code_insert')[0].innerHTML += data.div;
	setupLine($('#'+data.id));
	$('#css_name input').focus();
}

function clear() {
	$('#f_add #css_name input').attr('value', '');
	$('#f_add #css_label input').attr('value', '');
	$('#f_add #css_description input').attr('value', '');
}

function onClassChange() {
	var _class = $('#f_add #css_class select option:selected').attr('value');
	$.get('formbuilder.php', {'info':true, 'class':_class, }, onClassChangeSuccess);
}

function onClassChangeSuccess(data) {
	$('#info').text(data);
}

function setup() {
	$('#f_add #css_class select').change(onClassChange);
	$('#validator_click').click(function() { $('#validator').toggle(); });
	onClassChange();
}

function setupLine(div) {
	$('.attr').click(function() { $(this).toggleClass('attr_enabled'); });
	$('.attrlist').mouseover(function() { $(this).addClass('hover'); });
	$('.attrlist').mouseout(function() { $(this).removeClass('hover'); });
	$('.attr').mouseover(function() { $(this).addClass('hover'); });
	$('.attr').mouseout(function() { $(this).removeClass('hover'); });
//	div.css('background-color', '#f00');
}

$(setup);

</script>
<title>Form Builder</title>
</head>
<body>

<table id="insert" style="display:none">
	<tr><th>Class</th><th>Name</th><th>Label</th><th>Options</th></tr>
</table>

<div id="code">
	&lt;?php
	<div>&nbsp;</div>
	<div>// <a href="javascript:void(0);" class="hoverhide">ROOFLib Builder<span class="hoverhidden">Ray's Object Oriented Forms Library<br/> Ray Minge - rminge@ecreativeworks.com</span></a>  - Generated <?php echo date('r'); ?></div>
	<div>// Copyright 2011 Ecreativeworks</div>
	<div>&nbsp;</div>
	<div><a href="javascript:void(0);" class="hoverhide">require_once("path/to/forms_rm/lib/classes/class.form.php");<span class="hoverhidden">The only file you should need to include is class.form.php</span></a></div>
	<div>//<a href="javascript:void(0);" class="hoverhide">require_once("path/to/forms_rm/lib/data/states.php");<span class="hoverhidden">Check the data folder for commonly used datasets.</span></a></div>
	<div>&nbsp;</div>
	<div>// ------- <span id="validator_click" class = "togglable" >Add validators here.</span> -------</div>
<pre id="validator" style="display:none;" contenteditable="true">
/**
 * Example validator:
 *
 * Custom validators must take in the Form or FormItem to validate,
 * a reference to the error array, and a reference to the warning array.
 *
 */
function check_password($fi_password, &$errors, &$warnings) {
	// Get the value of the formitem. If the validator is attached to the form instead, the value is all form item values.
	$password = $fi_password->value();
	if ($password == 'password') {
		// pushing to the warning array will not cause validation to fail, but it will be reported to the user.
		$warnings []= "You thought it would be that easy?";
	} else if ($password != 'p455w0rD') {
		// pushing to the error array will tell the form that validation has failed, and it will report it to the user
		$errors []= "Incorrect password";
	}
	return true; // This tells the form or group whether or not to break upon evaluating this function. Most cases should return true;
}

	</pre>
	<div>// --------------- End --------------</div>
	<div>&nbsp;</div>
	<div>$form = <a href="javascript:void(0);" class="hoverhide">new Form(<span class="hoverhidden">This value is the unique identifier for the table. It is used as the name of the database table.</span></a>'<span contenteditable="true">formname</span>'); </div>
	<div>$form-><a href="javascript:void(0);" class="hoverhide">setWelcomeMessage<span class="hoverhidden">This text is displayed above the form.</span></a>('<span contenteditable="true">Thank you for contacting us.  Please fill out the following form and we will be contacting you shortly.</span>');</div>
	<div>$form-><a href="javascript:void(0);" class="hoverhide">setSuccessMessage<span class="hoverhidden">This text is displayed upon validated submission of the form.</span></a>('<span contenteditable="true">Thanks again for your interest.  We will be in contact with you soon.  Have a great day.</span>');</div>
	<div>$form-><a href="javascript:void(0);" class="hoverhide">setNoteMessage<span class="hoverhidden">This text is displayed underneath the welcome text. It is optional.</span></a>('<span contenteditable="true">Required Fields &lt;span class="required"&gt;*&lt;/span&gt;'</span>);</div>
	<div>&nbsp;</div>
	<div>$form-><a href="javascript:void(0);" class="hoverhide">setButtons<span class="hoverhidden">Using this function, you may set multiple buttons, and use their respective values (found by the function <code>action()</code> below). For advanced use, you may call <code>Form::BU('Text', 'foo()', 'script')</code> to have it execute javascript, <code>Form::BU('Text', 'http://url', 'link')</code>To have the button redirect without submitting the form, or <code>Form::BU('button.png', 'foo', 'image')</code> to use an image button</span></a>(Form::BU('<a href="javascript:void(0);" class="hoverhide">Submit<span class="hoverhidden">Button text</span></a>', '<a href="javascript:void(0);" class="hoverhide">submit<span class="hoverhidden">Button value- returned by <code>Form::action()</code> upon submission</span></a>'));</div>
	<div>&nbsp;</div>
	<div>// ----- Add the form items here. -----</div>
	<div>&nbsp;</div>
	<div id="code_insert">
	</div>
	<div>&nbsp;</div>
	<div>// --------------- End ----------------</div>
	<div>&nbsp;</div>
	<div>if (($action = $form-><a href="javascript:void(0);" class="hoverhide">action()<span class="hoverhidden">Indicates that the form was submitted. It returns the value of the button which was clicked</span></a>) && $form-><a href="javascript:void(0);" class="hoverhide">validate()<span class="hoverhidden">Checks that all of the fields have been validated. If it does not pass validation, the errors are </span></a>) { </div>
	<div>&nbsp;&nbsp;&nbsp;&nbsp;$value = $form-><a href="javascript:void(0);" class="hoverhide">value();<span class="hoverhidden">Retrieves the value of the form as an associative array tree</span></a></div>
	<div>&nbsp;&nbsp;&nbsp;&nbsp;$form-><a href="javascript:void(0);" class="hoverhide">storeEntry();<span class="hoverhidden">Stores the form data to a database. For simple forms, use this option. Otherwise, for complex forms, manage the database manually. <br/> Ensure you have a connection and selected database before using this function by using <code>mysql_connect</code> and <code>mysql_select_db</code></span></a></div>
	<div>&nbsp;&nbsp;&nbsp;&nbsp;$form-><a href="javascript:void(0);" class="hoverhide">sendEmail<span class="hoverhidden">Sends the form as a message to the specified user(s). Be sure to use associative arrays for to, cc, or bcc lists</span></a>("<span contenteditable="true">Subject</span>", <span contenteditable="true">$value['email']</span>, <span contenteditable="true">$value['name']</span>, Array('<span contenteditable="true">John Smith</span>' => '<span contenteditable="true">johnsmith@example.com</span>'));</div>

	<div>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" class="hoverhide">header('Location: ?success');<span class="hoverhidden">The token <code>?thankyou</code> Tells the form that the submission was approved and no longer prints the form, but rather the success message</span></a></div>
	<div>} </div>
	<div>&nbsp;</div>
	<div>?&gt;</div>
	<div>&lt;style&gt;</div>
	<pre contenteditable="true">
span.required, .required span { color:#c00; font-weight:bold; font-size:20px; }
.fldValue, .fldName { vertical-align:top; font-size:12px; padding-bottom:3px; }
.fldValue input, .fldValue select, .fldValue textarea { border:1px solid; border-color:#777 #ccc #ccc #777; padding:3px; }
.css_fi_text input { width:200px; }
.css_fi_captcha input { width:50px; }
.fldValue .descr { font-style:italic; }
.success { background-color:#D6EBFF; padding:25px; border:1px solid #99CCFF; color:#000; font-weight:bold; }
.error { background-color:#FFCCCC; padding:5px; border:1px solid #FF0000; color:#f00; font-weight:bold; }
.error ul { margin:5px 0px; color:#000;}
.error li, .warning li { font-weight:normal; }
.warning { background-color:#FFFFCC; padding:5px; border:1px solid #CC9900; color:#000; font-weight:bold; }
</pre>
	<div>&lt;/style&gt;</div>
	<div>&nbsp;</div>
	<div>&lt;?php echo $form-><a href="javascript:void(0);" class="hoverhide">printForm();<span class="hoverhidden">Prints the form data to the page. Any formatting can be done with CSS. By default, the form prints in a tabular layout, but if necessary, passing the function <code>true</code> tells the form to print in a div based layout. This function should always come after the validation.</span></a>?&gt;</div>
</div>
<fieldset><legend>Add Form item</legend>
<?php echo $addform->printForm(); ?>
</fieldset>
</body>
</html>