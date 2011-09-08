var _SwitchControllers = Array();

function SwitchController(id, options, start) {
this.id 		= id;
this.options 	= options;

this.switchIndex = function(index) {
	this.switch(this.options[index]);
}

this.switch = function(id) {
	for (var i in this.options) {
		$("#"+this.options[i]).css("display", "none");
	}
	$("#"+id).css("display", "block");
}
_SwitchControllers[this.id] = this;
$value = $(\'[name=\'+id+\']\').val();
start = $(\'option[value=\'+$value+\']\').attr("target");
this.switch(start);
}
';
