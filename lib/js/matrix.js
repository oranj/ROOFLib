jQuery.fn.exists = function(){return jQuery(this).length>0;}


var _MatrixControllers = Array();

function MatrixController(id, options, status, selected) {
	this.id = id;
	this.options = options;
	this.status = status;
	this.hidden = $("#"+id+"_hi");

	for(var i in options) {
		var o = $("#"+options[i]);
		o.attr("index", i);
		o.attr("status", 0);
		o.click(function() { _MatrixControllers[id].click(this); });
		o.addClass("mc_"+this.status[0]);
	}

	for(var i in selected) {
		var o = $("#"+i);
		o.attr("status", selected[i]);
		o.removeClass("mc_"+this.status[0]);
		o.addClass("mc_"+this.status[selected[i]]);
	}

	this.click = function(o) {
		var index = $(o).attr("index");
		var old_status = Math.floor($(o).attr("status"));
		var next_status = (old_status + 1);//
		if (next_status >= this.status.length) { next_status -= this.status.length; }
		$(o).attr("status", next_status);
		var old_class = "mc_"+this.status[old_status];
		var new_class = "mc_"+this.status[next_status];
		$(o).removeClass(old_class);
		$(o).addClass(new_class);
		this.update();
	}

	this.update = function () {
		var matrix = new Array();
		var row;
		var col;
		var o;
		for (var i in this.options) {
			o = $("#"+options[i]);
			row = o.attr("row");
			col = o.attr("col");
			if (! matrix[row]) {
				matrix[row] = new Array();
			}
			matrix[row][col] = o.attr("status");
		}
		var str = "";
		for (var row in matrix) {
			str += "[";
			var first = true;
			for (var col in matrix[row]) {
				if (! first) {
					str += ";";
				}
				str += matrix[row][col];
				first = false;
			}
			str += "]";
		}

		this.hidden.val(str);
	}

	this.update();

	_MatrixControllers[this.id] = this;
}

';