var _file_uploaders = Array();

function file_uploader(_id, _maxFiles, _rel, _target) {

	this.descr_box;
	this.id = _id;
	this.rel = _rel;
	this.target = _target;

	_file_uploaders[this.id] = this;
	this.descr_box_id = this.id+"_descr";
	this.descr_box = document.createElement('div');
	this.descr_box.setAttribute('id', this.descr_box_id);

	this.hidden_id = this.id+"_descr";
	this.hidden = document.createElement('input');
	this.hidden.setAttribute('type', 'hidden');
	this.hidden.setAttribute('id', this.hidden_id);
	this.hidden.setAttribute('name', this.id+"_hidden");

	document.getElementById(this.id).parentNode.appendChild(this.descr_box);
	document.getElementById(this.id).parentNode.appendChild(this.hidden);

	this.elements = Array();
	this.count = 0;
	this.numFiles = 0;
	this.maxFiles = _maxFiles;

	this.existing = Array();

	this.next_e = document.getElementById(this.id);

	this.add_file = function (element) {
		var id = this.id+'_'+this.count;
		if (navigator.userAgent.toString().match(/MSIE/)) {
			element.style.setAttribute('cssText', 'left:-10000px;position:absolute;', 0);
		} else {
			element.style.position='absolute';
			element.style.left='-10000px';
		}
		var next_e = document.createElement('input');
		this.count++;
		this.numFiles++;
		next_e.setAttribute('id', id);
		next_e.setAttribute('name', id);
		var eid = this.id;

		next_e.onchange = function () { _file_uploaders[eid].add_file(this); };
		next_e.setAttribute('type', 'file');
		element.parentNode.insertBefore(next_e, element);
		if (this.numFiles >= this.maxFiles) {
			next_e.disabled=true;
		}
		this.elements[id] = element;
		this.next_e = next_e;
		this.update_descr();
	}

	this.add_existing_file = function(filename, url, id) {
		var key = this.id + '_'+this.count;
		this.existing[key] = {'id':id, 'filename':filename, 'url':url, 'key':key};
		this.numFiles++;
		this.count++;
		this.update_descr();
	}


/**
 * Description for function ove = function()
 *
 * @param mixed id
 * @param mixed existing
 */
	this.remove = function(id, existing) {
		this.numFiles--;
		if (existing) {
			this.existing[id] = false;
		} else {
			this.elements[id].parentNode.removeChild(this.elements[id]);
			this.elements[id] = false;
		}
		if (this.numFiles < this.maxFiles) {
			this.next_e.disabled = false;
		}
		this.update_descr();
	}

	this.update_hidden = function() {
		var str = '';
		for (var i in this.existing) {
			if (this.existing.hasOwnProperty(i) && this.existing[i]) {
				if (! str) {
					str += this.existing[i].id;
				} else {
					str += ';'+this.existing[i].id;
				}
			}
		}
		this.hidden.setAttribute('value', str);
	}

	this.update_descr = function () {

		if (this.numFiles >= this.maxFiles) {
			if (this.next_e) {
				this.next_e.disabled = true;
			}
		}


		if (! this.descr_box) {
			this.descr_box = document.createElement('div');
			this.descr_box.setAttribute('id', this.descr_box_id);
			this.elements
		}
		if (this.numFiles == 0) {
			var str = '';
		} else {
			var empty = true;
			var str= '<div>Files:</div>';
			for (var i in this.existing) {
				if (this.existing.hasOwnProperty(i) && this.existing[i]) {
					str += '<div><a rel="'+this.rel+'" href="'+this.existing[i].url+'" target="'+this.target+'">'+this.existing[i].filename + "</a> <a href='javascript:_file_uploaders[\""+this.id+"\"].remove(\""+i+"\", true)'>Remove</a></div>";
					empty = false;
				}
			}
			for (var i in this.elements) {
				if (this.elements.hasOwnProperty(i) && this.elements[i]) {
					str += "<div>"+this.elements[i].value + " <a href='javascript:_file_uploaders[\""+this.id+"\"].remove(\""+i+"\", false)'>Remove</a></div>";
					empty = false;
				}
			}
		}
		this.descr_box.innerHTML = str;
		this.update_hidden();
	}



	document.getElementById(_id).setAttribute('onchange', '_file_uploaders["'+_id+'"].add_file(this);');
}