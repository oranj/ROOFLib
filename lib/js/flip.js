var _FlipControllers = Array();
function FlipController(_name, _inc, _dec) {
	this.name = _name;
	this.inc_text = _inc;
	this.dec_text = _dec;
	this.options = Array();
	this.selected;

	_FlipControllers[this.name] = this;

	this.getOptions = function() {
		var options = Array();
		var previous_key = false;
		var first_key = false;
		$("[name=\'"+this.name+"\']").find("option").each( function() {
			var key = $(this).attr("value");
			var value = $(this).text();
			if (! first_key) {
				first_key = key;
			}
			options[key] = Array();
			if (previous_key) {
				options[key]["previous"] = previous_key;
				options[previous_key]["next"] = key;
			}
			options[key]["value"] = value;
			previous_key = key;
		});
		// Allows looping;
		options[previous_key]["next"] = first_key;
		options[first_key]["previous"] = previous_key;
		return options;
	}

	this.getSelected = function() {
		return $("select[name=\'"+this.name+"\']").val();
	}

	this.build = function () {
		this.options = this.getOptions();
		this.selected = this.getSelected();

		var element = $("<div class=\'css_fi_flip_outer\'><div id=\'"+this.name+"_text\'>"+this.options[this.selected]["value"]+"</div></div><div class=\'css_fi_flip_inc\' target=\'"+this.name+"\'>"+this.inc_text+"</div><div class=\'css_fi_flip_dec\'  target=\'"+this.name+"\'>"+this.dec_text+"</div><input type=\'hidden\' name=\'"+this.name+"\' value=\'"+this.getSelected()+"\'/>");

		$("select[name=\'"+this.name+"\']")
			.before(element)
			.remove();


		$(".css_fi_flip_inc[target=\'"+this.name+"\']").click(function () { _FlipControllers[$(this).attr("target")].increment(); });
		$(".css_fi_flip_dec[target=\'"+this.name+"\']").click(function () { _FlipControllers[$(this).attr("target")].decrement(); });
	}

	this.change_value = function(key) {
		this.selected = key;
		$("[name=\'"+this.name+"\']").attr("value", this.selected);
		$("#"+this.name+"_text").text(this.options[this.selected]["value"]);
	}

	this.increment = function() {
		this.change_value(this.options[this.selected].next);
//		alert(this.name + " increment");

	}

	this.decrement = function() {
		this.change_value(this.options[this.selected].previous);
//		alert(this.name + " decrement");
	}

	this.build();
}