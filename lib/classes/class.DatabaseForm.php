<?php
class DatabaseForm {
	var $darray = array();
	var $farray = array();
	public $uploadFiles = array();
	var $table;
	var $prefix;
	var $harray = array();

	var $rowID;

	function DatabaseForm($table, $prefix='') {
		// CONSTRUCT HERE
		$this->table = $table;
		$this->prefix = $prefix;
	}

	function addItem($key, $value, $type='varchar') { // can also be boolean
		if($type=='boolean') {
			if($value == 0) $value = 'No'; else $value = 'Yes';
		}
		while (isset($this->darray[$key])) {
			preg_match_all('/^(.*?)(_?)([0-9]*)$/', $key, $matches);
			list($text, $name, $ul, $num) = $matches;
			$num = $num[0];
			if (! $num) { $num = 1; }
			$num ++;
			$key = $name[0].'_'.$num;
		}
		$this->darray[$key] = array('value'=>$value, 'type'=>$type);
	}

	function dbName($name) {
		$name = strip_tags($name);
		$name = preg_replace('/\(+.*\)/', '', $name); // get rid of anything in parens
		$name = preg_replace('/[\!\&\#]/', '', $name); // get rid of special chars

		$tokens = preg_split('([\ ,\/\-_\(_\):\?])', $name);


		foreach ($tokens as $id => $val) {
			if ($val == "&") {
				$tokens[$id] = 'and';
			} else if ($val == '') {
				unset($tokens[$id]);
			} else {
				$tokens[$id] = ucfirst($tokens[$id]);
			}
		}

		$name= join('_', $tokens);
		
		$max_length = 40;
		
		if (strlen($name) > $max_length) {
			$name = substr($name, 0, $max_length);
			$components = preg_split('/_/', $name);
			unset($components[sizeof($components) - 1]);
			$name = implode('_', $components);
		}
		return $name;
	}

	function addFile($key, $files, $upload_dir_fs, $upload_dir = 'uploads/') {
		if(!preg_match('/\/$/',$upload_dir)) $upload_dir.='/';
		$this->darray[$key] = array('value'=>'', 'type'=>'varchar');
		$this->farray[$key] = array('files'=>$files, 'upload_dir'=>$upload_dir_fs, 'web_dir'=>$upload_dir);
	}
	function addHeader($key, $value) {
		$this->harray[$key] = $value;
	}
	function getItem($key) {
		$values = mysql_query("SELECT * FROM ".$this->table." WHERE ".$this->table."_id = '".$this->rowID."'");
		if(mysql_num_rows($values)>0) {
			$val = mysql_fetch_assoc($values);
			return $val[$key];
		} else return FALSE;
	}

	function storeEntry($force_create=true) {
		$this->addItem('submit_timestamp',date('Y-m-d H:i:s'),'datetime');
		$keys = array_keys($this->darray);
		if($this->createTable()===false) {
			die("The form could not be databased because the table could not be created. ". mysql_error());
		}

		$res = mysql_query("SHOW COLUMNS FROM ".$this->table);
		$exist_cols = array();
		$create_cols = array();
		while($row = mysql_fetch_assoc($res)) {
			$exist_cols[] = $row['Field'];
			$exist_col_type[$row['Field']] = $row['Type'];
		}
		$fields_sql = array();
		foreach($this->darray as $key=>$val) {
			if($val['type']=='text') {
				$colType = 'TEXT';
			} elseif($val['type']=='datetime') {
				$colType = 'DATETIME';
			} else {
				$colType = 'VARCHAR(255)';
			}
			if(!in_array($this->prefix.$key, $exist_cols)) {
				mysql_query("ALTER TABLE ".$this->table." ADD ".$this->prefix . $key." ".$colType." NOT NULL");
			} elseif(strtolower($colType) != $exist_col_type[$this->prefix.$key]) {
				mysql_query("ALTER TABLE `".$this->table."` CHANGE `".$this->prefix . $key."` `".$this->prefix . $key."` ".$colType." ");
			}
			$fields_sql[$key] = mysql_real_escape_string( stripslashes($val['value']) );
		}

		foreach($this->farray as $key=>$data) {
			$files = $data['files'];
			$upload_dir = $data['upload_dir'];
			$web_dir = $data['web_dir'];

			if (! is_array($files)) {
				$files = array($files);
			}
			$fields_sql[$key] = '';
			foreach ($files as $val) {

				$file = time().'-'.basename($val['name']);
				$target_path = $upload_dir . $file;
				$web_path = $web_dir . $file;

				if(move_uploaded_file($val['src'], $target_path)) {
					/* Upload was successful */
					$fields_sql[$key] .= 'FILE:'.$web_path.';';
					$this->uploadFiles[] = $file;
				}
			}
		}


		// ALL COLUMNS ARE NOW CREATED AND WE KNOW THAT THE TABLE EXISTS. INSERT THE VALUES INTO THE DATABASE.
		$sql = "INSERT INTO ".$this->table." (".implode(',', array_keys($fields_sql)).") VALUES ('".implode("','", $fields_sql)."')";
		//die($sql);
		if(mysql_query($sql)) {
			$this->rowID = mysql_insert_id();
			return $this->rowID;
		} else {
			die(mysql_error() . ' : ' . $sql);
			return FALSE;
		}

	}

	function getTable($fields='', $orderby='', $where='', $dialog='') {
		$entries = $this->getEntries($fields, $orderby, $where, $dialog);
		$output = '<script type="text/javascript">$(document).ready(function(){ $(".link").click(function(e) { $(this).next("div").clone().appendTo("body").dialog( { height:$(this).next("div").height()+120, width:300 } ).show(); } ) }); </script>';
		$output .= '<table class="tablesorter" cellpadding="4" cellspacing="1" width="100%" bgcolor="#ededed"><thead><tr>';
		$titles = explode(',',$fields);
		foreach($titles as $key) {
			$key = trim($key);
			$name = ($this->harray[$key]!='') ? $this->harray[$key] : ucfirst($key);
			$output .= '<th>'.$name.'</th>';
		}
		$output .= '</tr></thead><tbody>';
		foreach($entries as $row) {
			$output .= $this->printRow($row, $fields, true);
		}
		$output .= '</tbody></table>';
		$output .= '<div id="dialog_div" style="display:none; "><h2>Loading...</h2></div>';
		return $output;
	}

	function getEntries($fields='', $orderby='', $where='', $dialog='') {
		$select = '*';
		if($orderby=='') $sort = ''; else $sort = 'ORDER BY '.$orderby;
		if($where=='') $cond = ''; else $cond = 'WHERE '.$where;

		$result = mysql_query("SELECT ".$select." FROM ".$this->table." ".$cond." ".$sort);
		$object = array();
		while($row = mysql_fetch_assoc($result)) $object[] = $row;

		return $object;
	}

	function getEntry($id, $fields='') {
		if($fields=='') $select = '*'; else $select = $fields;

		$result = mysql_query("SELECT ".$select." FROM ".$this->table." WHERE ".$this->table."_id=".$id." LIMIT 1");
		if(mysql_num_rows($result)==1) {
			return mysql_fetch_assoc($result);
		} else {
			return FALSE;
		}
	}
	function getEntryRow($id, $fields) {
		$row = $this->getEntry($id, $fields);
		return $this->printRow($row);
	}


	function printRow($array, $fields='', $add_js_view=false, $dialog=NULL) {
		$output = '<tr class="table_row">';
		if($fields==''){
			foreach($array as $field) {
				$output .= '<td>'.$field.'</td>';
			}
		} else {
			$farray = explode(',',$fields);
			foreach($farray as $show_field) {
				$output .= '<td>'.$array[trim($show_field)].'</td>';
			}
		}
		if($add_js_view) {  /** jQuery must be included for this to work */
			$output .= '<td><a href="javascript:void(0);" class="link">View</a>';
			if(isset($dialog)) {
				$dtitle = $array[$dialog[0]];
				$diags = explode(',',$dialog[1]);
				foreach($diags as $diag) {
					$dcontent .= (($this->harray[$diag]!='') ? $this->harray[$diag] : ucfirst($diag)).': '.$array[$diag].'<br />';
				}
			} else {
				$dtitle = $array['name'];
				foreach($array as $diag=>$value) {
					if($value!='' && !eregi('_id$',$diag)) {
						$dcontent .= '<b>'.(($this->harray[$diag]!='') ? $this->harray[$diag] : ucfirst($diag)).':</b> '.$value.'<br />';
					}
				}
			}
			$output .= '<div title="Contact: '.$dtitle.'" style="display:none; overflow:auto; ">'.$dcontent.'</div></td>';
		}
		$output .= '</tr>';
		return $output;

	}

	function createTable() {
		$fields = '';
		foreach($this->darray as $key=>$val) {
			if($val['type']=='text') {
				$colType = 'TEXT';
			} elseif($val['type']=='datetime') {
				$colType = 'DATETIME';
			} else {
				$colType = 'VARCHAR(255)';
			}
			$fields .= '`'.$this->prefix.$key.'` '.$colType.' NOT NULL, '."\n";
		}
		$sql = "CREATE TABLE IF NOT EXISTS `".$this->table."` (
		`".$this->table."_id` int(11) NOT NULL auto_increment,
		".substr($fields,0,-1)."
		PRIMARY KEY  (`".$this->table."_id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1;";
		return mysql_query($sql);
	}

	function exportExcel() {
		$rows = $this->getEntries('', 'submit_timestamp DESC');

		$output = '';
		foreach($rows[0] as $key=>$val) {
			$output .= '="'.ucfirst(eregi_replace('_',' ',$key)).'"'."\t";
		}
		$output .= "\n";
		foreach($rows as $row) {
			foreach($row as $key=>$field) {
				$output .= '="'.eregi_replace('"','""',str_replace('<br />'," ",$field)).'"'."\t";
			}
			$output .= "\n";
		}
		//        echo '<pre>';
		//        print_r($rows);
		//
		header("Pragma: public");
		header("Expires: 0"); // set expiration time
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		// browser must download file from server instead of cache

		header('Content-Type: application/vnd.ms-excel;');
		header("Content-Disposition: attachment; filename=web_contacts_" . date("dmY") . ".xls");  //*/
		echo $output;
		exit;
	}
}