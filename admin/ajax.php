<?php
include('includes/init.php');

if(isset($_REQUEST['table']) && isset($config['forms'][$_REQUEST['table']])) {
	$table = $config['forms'][$_REQUEST['table']]['db'];
} else {
	$formid = key($config['forms']);
	$table = $config['forms'][$formid]['db'];
}

if(empty($_REQUEST['page'])) $_REQUEST['page'] = 0;


if(isset($_POST['updateTableData'])) {

	if(!empty($_POST['date_start']) && !empty($_POST['date_end'])) {
		$date_start = date('Y-m-d', strtotime($_POST['date_start']));
		$date_end = date('Y-m-d', strtotime($_POST['date_end']));
		$whereDates = " AND DATE(submit_timestamp) >= '".$date_start."' AND DATE(submit_timestamp) <= '".$date_end."' ";
	} else {
		$whereDates = '';
	}

	/* BEGIN PARSE SORT DATA */
	if(!empty($_REQUEST['sort'])) {
		list($sort_col, $sort_dir) = split('-\|-',$_REQUEST['sort']);
		$sort_qry=' ORDER BY '.$sort_col.' ';
		if($sort_dir=='d') {
			$sort_qry.=' DESC ';
			$extra_class = ' headerSortUp';
		} else
			$extra_class = ' headerSortDown';
	} else {
		$sort_qry=' ORDER BY submit_timestamp DESC ';
		$extra_class = '';
	}
	/* END PARSE SORT */

	if($_SESSION['archive_mode']==true) {
		$counting = mysql_query("SELECT COUNT(*) as count FROM ".$table ." WHERE _archived=1".$whereDates);
		$count = mysql_fetch_assoc($counting);
		$qry_str = "SELECT * FROM ".$table ." WHERE _archived=1".$whereDates;
	} else {
		$counting = mysql_query("SELECT COUNT(*) as count FROM ".$table ." WHERE _archived!=1".$whereDates);
		$count = mysql_fetch_assoc($counting);
		$qry_str ="SELECT * FROM ".$table ." WHERE _archived!=1".$whereDates;
	}
	$qry_str .= $sort_qry;
	if($count['count']>$config['results_per_page']) {
		if(!isset($_REQUEST['page']) || $_REQUEST['page']<0) $_REQUEST['page']=0;
		$qry_str .= " LIMIT ".($_REQUEST['page']*$config['results_per_page']).",".$config['results_per_page']."";
		$page_counter = '';
		$startCount = (($_REQUEST['page']*$config['results_per_page'])+1);
		$finalCount = ($startCount+$config['results_per_page']-1);
		if($finalCount>$count['count'])
			$finalCount = $count['count'];

		$page_counter .= '<strong>'.$startCount.'-'.$finalCount.' of '.$count['count'] . ' &nbsp;&nbsp;&nbsp; ';

		$num_pages = ceil($count['count']/$config['results_per_page']);
		if($_REQUEST['page']>0)
			$page_counter .= '<a href="javascript:gotoPage('.($_REQUEST['page']-1).',\''.$_REQUEST['sort'].'\');">&laquo; Prev</a> ';
		for($x=0; $x<$num_pages; $x++) {
			if($_REQUEST['page']==$x) {
				$page_counter .= '<strong>' . ($x+1) . '</strong> ';
			} else {
				$page_counter .= '<a href="javascript:gotoPage('.$x.',\''.$_REQUEST['sort'].'\');">'.($x+1).'</a> ';
			}
		}
		if($_REQUEST['page']<($num_pages-1))
			$page_counter .= '<a href="javascript:gotoPage('.($_REQUEST['page']+1).',\''.$_REQUEST['sort'].'\');">Next &raquo;</a>';
	}

	$qry = mysql_query($qry_str) or die(mysql_error());

	$fields = mysql_num_fields($qry);
	if($_GET['init']=='true') {
		$_POST['fields'] = manipulateFields($_POST['fields']);
	}
	echo '<thead><tr>';
	echo '<th>&nbsp;</th>';
	$dialog_fields = array();
	//foreach($_POST['fields'] as $field) {
	for ($i = 0; $i < $fields; $i++) {
		$dbfield = mysql_field_name($qry, $i);
		if ($dbfield !== '_archived') {
			if(in_array($dbfield,$_POST['fields']))	echo '<th class="header'.( ($sort_col==$dbfield) ? $extra_class : '' ).'" onclick="beginSort('.$_REQUEST['page'].',\''.$dbfield.'-|-'.( ($sort_dir=='d' && $sort_col==$dbfield) ? 'a' : 'd' ).'\'); ">'.cleanName( $dbfield ).'</th>';
			$dialog_fields[] = cleanName( $dbfield );
		}
	}
	echo '<th class="header">&nbsp;</th>';
	echo '</tr></thead><tbody>';

	while($row = mysql_fetch_assoc($qry)) {
		$dialog_values = array();
		$rowid = $row[$config['forms'][$table]['db'].'_id'];
		echo '<tr '.(++$ca % 6 == 0 ? 'class="alt"' : (($ca + 3) % 6 == 0?'class="alt2"':'') ).' id="row_'.$rowid.'">';
		echo '<td><input type="checkbox" name="check[]" value="'.$rowid.'" /></td>';
		$cols = 0;
		foreach($row as $field=>$na) {
			if ($field !== '_archived') {
				$row[$field] = strip_tags($row[$field], '<br>');
				if(preg_match_all('/FILE:(.*?);/',$row[$field],$files)) {
					$print_val = '';//print_r($files[1], true);
					foreach ($files[1] as $file_name) {
						if (! $file_name) { continue; }
	//					$print_val .= "<b>".$file_name."</b>";
						$matches = preg_match('/^DELETED:/', $file_name, $out);
//						$print_val .= "<h1>match $matches</h1>";
						if ($matches) {
							$print_val .= '<div style="white-space:nowrap"><a style="white-space:nowrap" title="'.$file_name.'">[deleted]</a></div>';
						} else {
							$print_val .= $dialog_values[] = '<div style="white-space:nowrap"><a href="../'.$file_name.'" target="_blank">Download File</a> [<a href="javascript:void(0);" onclick="deleteFileOnly('.$row[$table.'_id'].', \''.$file_name.'\', this);">Delete</a>]</div>';
						}
					}
				} elseif( preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',$row[$field]) ) {
					$print_val = $dialog_values[] = date('M j, Y g:i a',strtotime($row[$field]));
				} elseif(preg_match('/https?:\/\/(www\.)?(.*)(\.com|\.org|\.net|.[a-z]{2,3})$/i',$row[$field])) {
					$print_val = $dialog_values[] = '<a target="_blank" href="'.$row[$field].'">'.$row[$field].'</a>';
				} else {
					$print_val = $dialog_values[] = $row[$field];
				}
				if(in_array($field,$_POST['fields'])) {
					echo '<td>'.$print_val.'&nbsp;</td>'."\r\n\t\t";
				}

				$cols++;
			}
		}
		echo '<td><a href="javascript:void(0);" onclick="openDialog('.$rowid.'); ">View Details</a>';
		/* DIALOG */
		echo '<div id="dialog_'.$rowid.'" style="display:none; ">';
			echo '<small><a href="javascript:sendEmail('.$rowid.');">Send Email</a> | <a href="javascript:printEntry('.$rowid.');">Print</a></small>';
			echo '<table id="emailTable_'.$rowid.'">';
			foreach($dialog_fields as $key=>$title) {
				if ($title !== 'Archived') {
					echo '<tr valign="top"><td><b>'.$title.': </b></td><td>'.$dialog_values[$key].'</td></tr>';
				}
			}
			echo '</table>';
		echo '</div>';

		/* Assign Vars on parent page */
		?>
		<script type="text/javascript">currentSort = '<?php echo $_REQUEST['sort']; ?>'; currentPage = '<?php echo $_REQUEST['page']; ?>'; </script>
		<?php
		echo '</td>';

		echo '</tr>';
	}

	echo '</tbody>';
	if(!empty($page_counter)) {
		echo '<tfoot>';
		echo '<tr><td colspan="'.($cols+2).'">'.$page_counter.'</td></tr>';
		echo '</tfoot>';
	}

	exit;
}



?>