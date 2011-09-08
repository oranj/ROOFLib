var _NumberManagers = Array();

function NumberManager(_name, _upper, _lower) {

	this.name = _name;
	this.lower_limit = _lower;
	this.upper_limit = _upper;

	this.element = $("[name="+this.name+"]");

	this.increase = 1;

	this.element.keypress(function (e) { _NumberManagers[$(this).attr("name")].keypress(e) });

	this.element.keyup(function (e) { _NumberManagers[$(this).attr("name")].keyup(e) });

	this.element.keydown(function (e) { _NumberManagers[$(this).attr("name")].keydown(e) });

	this.element.change(function (e) { _NumberManagers[$(this).attr("name")].change(e) });

	_NumberManagers[this.name] = this;

	this.fetchVal = function () {
		var val = parseInt(this.element.val());
		if (isNaN(val)) {
			val = 0;
		}
		return val;
	}

	this.keypress = function (e) {
		switch (e.keyCode) {
			case 38:
				this.increment(false);
				break;
			case 40:
				this.increment(true);
				break;
			default:
				break;
		}
		this.increase += 0.06;
	}

	this.keydown = function (e) {
		var c= String.fromCharCode(e.which).charCodeAt(0);
		if (! ((c >= 48 && c <= 57) || c == 46 || c == 8 || (c >= 38 && c <= 40) ||  (c >= 96 && c <= 105))) {
			this.element.attr("readonly", "readonly");
		}
	}

	this.increment = function (negative) {
		var change = parseInt(this.increase);
		if (negative) {
			change *= -1;
		}
		var val = this.fetchVal();
		val += change;
		if (this.upper_limit != null && val > this.upper_limit) {
			val = this.upper_limit;
		} else if (this.lower_limit != null && val < this.lower_limit) {
			val = this.lower_limit;
		}
		this.element.val(val);
		this.val = val;
	}

	this.change = function () {
		var val = this.fetchVal();
		if (this.upper_limit != null && val > this.upper_limit) {
			val = this.upper_limit;
		} else if (this.lower_limit != null && val < this.lower_limit) {
			val = this.lower_limit;
		}
		this.element.val(val);
		this.val = val;

	}

	this.keyup = function (e) {
		this.increase = 1;
		this.element.removeAttr("readonly");
	}

	this.val = this.fetchVal();
}