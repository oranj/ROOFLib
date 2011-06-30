<?
/*
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*   Awesome XML Excel Exporter By Jesse Donat on December 5th, 2007
*				V 1.5 Updated July 29, 2008
*                     --Don't be Evil--
* 
* 		- donatj@oasisband.net / jdonat@ecreativeworks.com
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*/

class XSpreadsheet {
	
	var $WorksheetData = array();
	var $typeTranslation = array(
					'string' => 	'String',
					'blob' => 		'String',
					'datetime' => 	'String',
					'timestamp' => 	'String',
					'date' => 		'String',
					'int' => 		'Number',
					'real' => 		'Number',
					'' => 			'String', //the old fallback
					 );
	var $filename;
	var $xmlDoc = '';
	var $zip = false;
	var $debug = false;
	
	/*
	* Constructor
	* @param string $filename
	* @param bool $debug
	*/			 
	public function __construct($filename = 'export.xml.xls', $debug = false) {
		$this->filename = $filename;
		$this->debug = $debug;
	}
	
	/*
	* Public: Method to add a workbook, 
	* @param string $name  name to give the workbook (alphanumeric only, especially no \:/'s)
	* @param mixed $data  an active query resource or an array.  
	* @param mixed $headers  array of header titles, if not specified and $data is $qry, will display column names. False prevents display
	*/
	public function AddWorkbook($name, $data, $headers=0) {
		$types = array();
		if(is_array($data)) {
			$qData = $data;
		}elseif(@get_resource_type($data)){
			while($qRow = mysql_fetch_row($data)) $qData[] = $qRow;
			if($headers === 0) { $headers = array(); $readHeaders = true; }
			for ($j = 0; $j < mysql_num_fields($data); $j++) {
				if($readHeaders) $headers[] = mysql_field_name($data, $j);
				$types[] = mysql_field_type($data, $j);
			}
			mysql_data_seek($data,0); //reset the pointer so the qry can continue to be used
		}else{
			$qData = array();
		}
		$wData = array('name' => $name, 'data' => $qData, 'dataType' => $types);
		if(is_array($headers)) $wData['headers'] = $headers;
		$this->WorksheetData[] = $wData;
		return $this;
	}
	
	public function RunCallbacksOn($worksheet, $callbacks) {
		$worksheet -= 1;
		foreach($callbacks as $ckey => $cvalue) {
			if( !is_numeric($ckey) ) {
				$ckey = array_search($ckey, $this->WorksheetData[$worksheet]['headers']);
				if($ckey === false) break;
			}
			foreach($this->WorksheetData[$worksheet]['data'] as &$data) {
				$data[$ckey] = call_user_func($cvalue, $data[$ckey]);
			}
		}
		return $this;
	}
	
	/*
	* Public: Method to Generate the document once all the workbooks have been added
	*/
	public function Generate(){
		
		$doc = new DOMDocument('1.0');
		$doc->formatOutput = true;
		$doc->appendChild($doc->createProcessingInstruction('mso-application', 'progid="Excel.Sheet"'));

		$Workbook = $doc->createElement('Workbook');
		$Workbook->setAttribute('xmlns','urn:schemas-microsoft-com:office:spreadsheet');
		$Workbook->setAttribute('xmlns:o','urn:schemas-microsoft-com:office:office');
		$Workbook->setAttribute('xmlns:x','urn:schemas-microsoft-com:office:excel');
		$Workbook->setAttribute('xmlns:ss','urn:schemas-microsoft-com:office:spreadsheet');
		$Workbook->setAttribute('xmlns:html','http://www.w3.org/TR/REC-html40');
		$Workbook = $doc->appendChild($Workbook);

		$DocumentProperties = $doc->createElement('DocumentProperties');
		$DocumentProperties = $Workbook->appendChild($DocumentProperties);
		$DocumentProperties->setAttribute('xmlns', 'urn:schemas-microsoft-com:office:office');
		$DocumentProperties->appendChild($doc->createElement('Author', 'Jesse Donat'));
		$DocumentProperties->appendChild($doc->createElement('LastAuthor', 'Jesse Donat'));
		$DocumentProperties->appendChild($doc->createElement('Created', '2007-08-14T15:42:13Z'));
		$DocumentProperties->appendChild($doc->createElement('Company', 'Ecreativeworks'));
		$DocumentProperties->appendChild($doc->createElement('Version', '11.8132'));

		$ExcelWorkbook = $doc->createElement('ExcelWorkbook');
		$ExcelWorkbook = $Workbook->appendChild($ExcelWorkbook);
		$ExcelWorkbook->setAttribute('xmlns', 'urn:schemas-microsoft-com:office:excel');
		$ExcelWorkbook->appendChild($doc->createElement('WindowHeight', '12705'));
		$ExcelWorkbook->appendChild($doc->createElement('WindowWidth', '15165'));
		$ExcelWorkbook->appendChild($doc->createElement('WindowTopX', '480'));
		$ExcelWorkbook->appendChild($doc->createElement('WindowTopY', '60'));
		$ExcelWorkbook->appendChild($doc->createElement('ActiveSheet', '0'));
		$ExcelWorkbook->appendChild($doc->createElement('ProtectStructure', 'False'));
		$ExcelWorkbook->appendChild($doc->createElement('ProtectWindows', 'False'));

		$Styles = $doc->createElement('Styles');
		$Styles = $Workbook->appendChild($Styles);

		$Style = $doc->createElement('Style');
		$Style = $Styles->appendChild($Style);
		$Style->setAttribute('ss:ID', 'Default');
		$Style->setAttribute('ss:Name', 'Normal');
		$Style->appendChild($doc->createElement('Alignment'))->setAttribute('ss:Vertical','Bottom');
		$Style->appendChild($doc->createElement('Borders'));
		$Style->appendChild($doc->createElement('Font'));
		$Style->appendChild($doc->createElement('Interior'));
		$Style->appendChild($doc->createElement('NumberFormat'));
		$Style->appendChild($doc->createElement('Protection'));

		$Style= $doc->createElement('Style');
		$Style = $Styles->appendChild($Style);
		$Style->setAttribute('ss:ID', 's21');
		$Style->appendChild($doc->createElement('Font'))->setAttribute('ss:Bold','1');

		foreach($this->WorksheetData as $WData) {

			$Worksheet = $doc->createElement('Worksheet');
			$Worksheet = $Workbook->appendChild($Worksheet);
			$Worksheet->setAttribute('ss:Name', $WData['name']);
			
			$Table = $doc->createElement('Table');
			$Table = $Worksheet->appendChild($Table);
			
			if(isset($WData['headers']) && is_array($WData['headers'])) {
				$Row = $doc->createElement('Row');
				$Row = $Table->appendChild($Row);
				$Row->setAttribute('ss:StyleID', 's21');

				foreach ($WData['headers'] as $header) {
					$Cell = $Row->appendChild($doc->createElement('Cell'));
					$Data = $Cell->appendChild($doc->createElement('Data'));
					$Data->setAttribute('ss:Type', 'String');
					$Data->appendChild( $doc->createTextNode($header) );

				}
			}

			foreach($WData['data'] as $dataRow) {

				$Row = $doc->createElement('Row');
				$Row = $Table->appendChild($Row);
				$cell_index = 0;
				$wasEmpty = false;

				foreach($dataRow as $value) {
					if( $this->not_null($value) ) {
						$Cell = $Row->appendChild($doc->createElement('Cell'));
						if($wasEmpty) $Cell->setAttribute( 'ss:Index', $cell_index + 1 );
						$Data = $Cell->appendChild($doc->createElement('Data'));
						$Data->setAttribute( 'ss:Type',  $this->typeTranslation[ $WData['dataType'][$cell_index] ] );
						
						$Data->appendChild(	$doc->createTextNode( utf8_encode( $value ) ) );
						$wasEmpty = false;
					}else{
						$wasEmpty = true;
					}
					$cell_index++;
				}
			
			}
		}
		
		$this->xmlDoc = $doc->saveXML();
		return $this;
	}
	
	/*
	* Public: Method to Set Headers and Return Generated Data
	* @param bool $zip whether or not to archive spreadsheet, currently broken
	*/
	public function Send($zip = false, $zipFilename = false) {
		if(!$this->debug) {
			if(!headers_sent()){ 
				if(!$zip) {
					header("Content-type: application/vnd.ms-excel");
					header("Content-Disposition: attachment; filename=".$this->filename);
					header("Pragma: public");
					header("Expires: 0");
				}else{
					if(!$zipFilename) $zipFilename = $this->filename . ".zip";
					header("Content-type: application/zip");
					header("Content-Disposition: attachment; filename=".$zipFilename);
					header("Pragma: no-cache");
					header("Expires: 0");

					echo $this->gzip($this->xmlDoc, 6, $this->filename, "Woot Woot");
					
					//the above doesn't work on freyr, back to the old way.
					
					/*
					$zip = new ZipArchive();
					$filename = "../cache/".(int)(time() / 100).".zip";

					if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
					    exit("cannot open <$filename>\n");
					}
					$zip->addFromString($this->filename, $this->xmlDoc);
					$zip->close();
					$fp = fopen($filename, 'rb');

					fpassthru($fp);
					fclose($fp);
					unlink($filename);
					*/
					
					//old way didn't work on freyr either
					die();
					//need not go further;
				}
			}else{
				die('Error, headers already sent');				
			}
		}else{
			header("Content-type: application/xml; charset=UTF-8");
		}

		echo $this->xmlDoc;
		
	}
	
	private function gzip($data = "", $level = 6, $filename = "", $comments = "") {
	    $flags = (empty($comment)? 0 : 16) + (empty($filename)? 0 : 8);
	    $mtime = time();
	   
	    return (pack("C1C1C1C1VC1C1", 0x1f, 0x8b, 8, $flags, $mtime, 2, 0xFF) .
	                (empty($filename) ? "" : $filename . "\0") .
	                (empty($comment) ? "" : $comment . "\0") .
	                gzdeflate($data, $level) .
	                pack("VV", crc32($data), strlen($data)));
	}
	
	private function not_null($value) {
		if (is_array($value)) {
			if (sizeof($value) > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			if ((is_string($value) || is_int($value)) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
				return true;
			} else {
				return false;
			}
		}
	}
	
}
?>