<?php 
function f_drawicon ( $icon, $height='25',$fillcolor ='white') {
	global $svgIcon;
	$html = '<svg height="'. $height . '" version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="-2 0 30.971 30.971" style="enable-background:new 0 0 27.971 27.971;" xml:space="preserve" fill="'.$fillcolor.'">';
	$html.= $svgIcon[$icon]["html"] ; 
	$html.= '</svg>';
	return $html;
}
function f_showicon ( $icon, $parameters = array() ) {
	$html='';
	return $html;
}


function f_getfromARow (&$aFields , $fieldSearched, $fieldSearchedValue, $getField=null,$mode="") {

	$nfield= 0;

	if (strpos($mode,"-friendly-")!==false)  $fieldSearchedValue=url_slug($fieldSearchedValue);

	if (is_null($aFields)) {
		echo "<br>null aFields :";
		echo parse_backtrace(debug_backtrace()); 
		return "";
	} 
	$existcol=get_param($mode,"existcol");
	foreach ($aFields as $key=>$aField) {
		if (strpos($mode,"-debug-")!==false) var_dump($aField);
		$found=false;
		//echo parse_backtrace(debug_backtrace()); 
		if (array_key_exists($fieldSearched ,$aField)) {
			$fieldValue= ( strpos($mode,"-friendly-")!==false   ? url_slug($aField[$fieldSearched]):$aField[$fieldSearched]);
			if (strpos($mode,"-debug-")!==false) echo "<br>$nfield)  fieldValue:".$fieldValue . " &nbsp;&nbsp;&nbsp;recup: " . $aField[$getField ] . " ppp:" .$aField[0] ."<br>";

			if (strpos($mode,"-trim-")!==false) {
				if ($fieldSearchedValue== $fieldValue ) $found=true;
			} else {
				if (trim($fieldSearchedValue)== trim($fieldValue) ) $found=true;
			}
		}
		if ($found && $existcol!="" &&  !array_key_exists($existcol ,$aField)) $found=false;
		if ($found) {
			if (strpos($mode,"-debug-")!==false)  echo " ***encontrado."  ;
			if ($getField===null) {
				return $aField;
			} elseif ($getField==-1) {
				return  $key; //$nfield; // devuelva la fila donde está
			} else {
				if (array_key_exists ( $getField, $aField ) ) {
					return $aField[$getField ];
				} else {
					return "";
				}
			}

		}
		$nfield ++;
	}

	return "" ; 
}

function f_setValuefromARow (&$aFields_sv , $fieldSearchedValue, $fieldAfectado, $fieldValue, $fieldSearched  ,$sumarizar="",$aValues=null) {
	$nfield= 0;
	foreach ($aFields_sv as $key=>$aField) {

		if ($fieldSearchedValue== $aField[$fieldSearched] )  {
			//echo "<br>yes." . $fieldValue;
			if ($aValues==null) {
				if ( $sumarizar=="") {
					$aFields_sv[$key][$fieldAfectado] = $fieldValue;
				} else { // 
					if ( is_string($aFields_sv[$key][$fieldAfectado])) {
						$aFields_sv[$key][$fieldAfectado] .= $fieldValue;
					} else {
						$aFields_sv[$key][$fieldAfectado] += $fieldValue;
					}
				}
			} else {
				$ASubArray = $aFields_sv[$key][$fieldAfectado] ;
				f_setValueSubRow ($ASubArray , $aValues) ;
				$aFields_sv[$key][$fieldAfectado] = $ASubArray ; // restaura valor

			}
			return "";
		}
		$nfield +=1;
	}

	return "" ;
}
function url_slug($str, $options = array()) {
	// Make sure string is in UTF-8 and strip invalid UTF-8 characters
	$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
	
	$defaults = array(
		'delimiter' => '-',
		'limit' => null,
		'lowercase' => true,
		'replacements' => array(),
		'transliterate' => false,
	);
	
	// Merge options
	$options = array_merge($defaults, $options);
	
	$char_map = array(
		// Latin
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'O' => 'O', 
		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'U' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
		'ß' => 'ss', 
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'o' => 'o', 
		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'u' => 'u', 'ý' => 'y', 'þ' => 'th', 
		'ÿ' => 'y',
		// Latin symbols
		'©' => '(c)',
		// Greek
		'?' => 'A', '?' => 'B', 'G' => 'G', '?' => 'D', '?' => 'E', '?' => 'Z', '?' => 'H', 'T' => '8',
		'?' => 'I', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => '3', '?' => 'O', '?' => 'P',
		'?' => 'R', 'S' => 'S', '?' => 'T', '?' => 'Y', 'F' => 'F', '?' => 'X', '?' => 'PS', 'O' => 'W',
		'?' => 'A', '?' => 'E', '?' => 'I', '?' => 'O', '?' => 'Y', '?' => 'H', '?' => 'W', '?' => 'I',
		'?' => 'Y',
		'a' => 'a', 'ß' => 'b', '?' => 'g', 'd' => 'd', 'e' => 'e', '?' => 'z', '?' => 'h', '?' => '8',
		'?' => 'i', '?' => 'k', '?' => 'l', 'µ' => 'm', '?' => 'n', '?' => '3', '?' => 'o', 'p' => 'p',
		'?' => 'r', 's' => 's', 't' => 't', '?' => 'y', 'f' => 'f', '?' => 'x', '?' => 'ps', '?' => 'w',
		'?' => 'a', '?' => 'e', '?' => 'i', '?' => 'o', '?' => 'y', '?' => 'h', '?' => 'w', '?' => 's',
		'?' => 'i', '?' => 'y', '?' => 'y', '?' => 'i',
		// Turkish
		'S' => 'S', 'I' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'G' => 'G',
		's' => 's', 'i' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'g' => 'g', 
		// Russian
		'?' => 'A', '?' => 'B', '?' => 'V', '?' => 'G', '?' => 'D', '?' => 'E', '?' => 'Yo', '?' => 'Zh',
		'?' => 'Z', '?' => 'I', '?' => 'J', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => 'O',
		'?' => 'P', '?' => 'R', '?' => 'S', '?' => 'T', '?' => 'U', '?' => 'F', '?' => 'H', '?' => 'C',
		'?' => 'Ch', '?' => 'Sh', '?' => 'Sh', '?' => '', '?' => 'Y', '?' => '', '?' => 'E', '?' => 'Yu',
		'?' => 'Ya',
		'?' => 'a', '?' => 'b', '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'yo', '?' => 'zh',
		'?' => 'z', '?' => 'i', '?' => 'j', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'n', '?' => 'o',
		'?' => 'p', '?' => 'r', '?' => 's', '?' => 't', '?' => 'u', '?' => 'f', '?' => 'h', '?' => 'c',
		'?' => 'ch', '?' => 'sh', '?' => 'sh', '?' => '', '?' => 'y', '?' => '', '?' => 'e', '?' => 'yu',
		'?' => 'ya',
		// Ukrainian
		'?' => 'Ye', '?' => 'I', '?' => 'Yi', '?' => 'G',
		'?' => 'ye', '?' => 'i', '?' => 'yi', '?' => 'g',
		// Czech
		'C' => 'C', 'D' => 'D', 'E' => 'E', 'N' => 'N', 'R' => 'R', 'Š' => 'S', 'T' => 'T', 'U' => 'U', 
		'Ž' => 'Z', 
		'c' => 'c', 'd' => 'd', 'e' => 'e', 'n' => 'n', 'r' => 'r', 'š' => 's', 't' => 't', 'u' => 'u',
		'ž' => 'z', 
		// Polish
		'A' => 'A', 'C' => 'C', 'E' => 'e', 'L' => 'L', 'N' => 'N', 'Ó' => 'o', 'S' => 'S', 'Z' => 'Z', 
		'Z' => 'Z', 
		'a' => 'a', 'c' => 'c', 'e' => 'e', 'l' => 'l', 'n' => 'n', 'ó' => 'o', 's' => 's', 'z' => 'z',
		'z' => 'z',
		// Latvian
		'A' => 'A', 'C' => 'C', 'E' => 'E', 'G' => 'G', 'I' => 'i', 'K' => 'k', 'L' => 'L', 'N' => 'N', 
		'Š' => 'S', 'U' => 'u', 'Ž' => 'Z',
		'a' => 'a', 'c' => 'c', 'e' => 'e', 'g' => 'g', 'i' => 'i', 'k' => 'k', 'l' => 'l', 'n' => 'n',
		'š' => 's', 'u' => 'u', 'ž' => 'z'
	);
	
	// Make custom replacements
	$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
	
	// Transliterate characters to ASCII
	if ($options['transliterate']) {
		$str = str_replace(array_keys($char_map), $char_map, $str);
	}
	
	// Replace non-alphanumeric characters with our delimiter
	$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
	
	// Remove duplicate delimiters
	$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
	
	// Truncate slug to max. characters
	$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
	
	// Remove delimiter from ends
	$str = trim($str, $options['delimiter']);
	
	return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
}


function f_getfromARowOne ($aField ,  $aFieldSearched ) {
	if ($aField == null) return "";
	// extraen del contenedor de arrays el valor para el campo espeficado
	$valordev="";
	if (array_key_exists ($aFieldSearched ,$aField ))  {
		$valordev= $aField[$aFieldSearched ];
	}
	return $valordev;
}

function f_setValueSubRow (&$aFields  , s$aValues) {
	// sumarizador de un array que está dentro de otro array

	if (count($aFields)==0 ) {
		array_push ( $aFields , $aValues);
		return;
	}
	$Found= false;
	foreach ($aFields as $key=>$aField) {
		if ($aValues[0]== $aField[0] )  { //  encontró, añade valores
			$Found= true;
			for ($i = 1; $i<count($aValues) ; $i++) {
				$aFields[$key][$i] += $aValues[$i];
			}
			return "";
		}
	}
	if ($Found== false)  array_push ( $aFields , $aValues);
	//echo "<br>." . var_dump($aValues);
	return "" ;
}

function get_param($cparam , $ckey,$delimiter= '|',  $convert=true) {
	if (is_null($delimiter)) $delimiter= '|';
	if ($delimiter=="array") {
		$delimiter="|";
		$cparam =implode("|",$cparam);
	} 
	$a_parameters = http_explode( $cparam, $delimiter);
	if ($a_parameters == null) return "";
	$lvalue= f_getfromARowOne ($a_parameters ,  $ckey);
	if ($convert==true) $lvalue=str_replace(":","=",$lvalue);
	return $lvalue; 
}


function http_explode($stringInput, $delimeter= '&') {
   if ( $stringInput=="" || strpos($stringInput,'=')===false)    return null; 
   $args=preg_split('~(?<!\\\)' . preg_quote($delimeter, '~') . '~', $stringInput);
   $delimetervalue="=";

   $arr_query=array();
   foreach($args as $arg) {
	
   	 $parts=preg_split('~(?<!\\\)' . preg_quote($delimetervalue, '~') . '~', $arg);
 	 if ( count($parts)>1)   {
	 	 $parts[1]=  stripslashes  ($parts[1]);
		 $arr_query[trim($parts[0])]= $parts[1]; 
	  }
   }

   return $arr_query;
}
function strposa($haystack, $needles=array(), $offset=0) {
        $chr = array();
        foreach($needles as $needle) {
                $res = strpos($haystack, $needle, $offset);
                if ($res !== false) $chr[$needle] = $res;
        }
        if(empty($chr)) return false;
        return min($chr);
}
function _json_encode($val)
{
    if (is_string($val)) return '"'.addslashes($val).'"';
    if (is_numeric($val)) return $val;
    if ($val === null) return 'null';
    if ($val === true) return 'true';
    if ($val === false) return 'false';

    $assoc = false;
    $i = 0;
    foreach ($val as $k=>$v){
        if ($k !== $i++){
            $assoc = true;
            break;
        }
    }
    $res = array();
    foreach ($val as $k=>$v){
        $v = _json_encode($v);
        if ($assoc){
            $k = '"'.addslashes($k).'"';
            $v = $k.':'.$v;
        }
        $res[] = $v;
    }
    $res = implode(',', $res);
    return ($assoc)? '{'.$res.'}' : '['.$res.']';
}
function empty_date($var){
    if (empty($var) or $var === '0000-00-00' or $var === '0000-00-00 00:00:00')
        return true;
    else
        return false;
}
function format_datetoinput ($dateoriginal , $mode=""  ){
	if (empty_date ($dateoriginal)) return "";
	$date=strtotime( $dateoriginal);

	$format = "d/m/Y";
	$year = date( "Y" ,$date) ;
	$month = date( "n" ,$date) ;
	
	if (strpos($mode,"month")!==false ) {
			$format = "d \m\o\\n\\t\h";
	}
	if (strlen($dateoriginal)>10 ) {
		$format.= " H:i:s";
	}

	$cdate = date( $format ,$date) ;
	return $cdate;

}

function format_date($dateoriginal , $mode=""  ){
	global $crmGlobal;
	
	if (strpos($mode,"monthshort")!==false ) {
		$aMonths = $crmGlobal->aMonthsShort	;
	}  else {
		$aMonths = $crmGlobal->aMonths	;
	}
	
	if (empty_date ($dateoriginal)) return "";
	
	$date=strtotime( $dateoriginal);

	$format = "d/m/Y";
	$year = date( "Y" ,$date) ;
	$month = date( "n" ,$date) ;
	
	if (strpos($mode,"month")!==false ) {
			$format = "d \m\o\\n\\t\h";
	}
	if (strlen($dateoriginal)>10 && strpos($mode,"notime")===false ) {
		$format.= " à H:i"; //
		if (strpos($mode,"seconds")!==false ) {
			$format.= ":s";
		}

	}	
	$cdate = date( $format ,$date) ;
	
	// month name
	if (strpos($mode,"month")!==false ) {
		$cdate = str_replace ( "month",  $aMonths[intval($month)] , $cdate) ;
		if ( date( "Y") !=  $year ) {
			$cdate.=" ".$year;
		}
		
	}
	return $cdate;
}
function dmyTOyyymmdd ( $var ) {
	$date = str_replace('/', '-', $var);
	return date('Y-m-d H:i:s', strtotime($date));
	
}
function cropText  ( $text, $size = 30  ) {
	if (strlen($text)> $size ) {
		$text = substr ($text,0,$size). "...";
	}
	return $text;
}

 function CRMphonenumberFormat( $phonenumber, $webcall=false ){   // function modified copiend from \bao\objects\Util.php  function:phonenumberFormat

	if( strlen( str_replace( array( " ", "." ), "", $phonenumber ) ) != 10 && !$webcall )
		return $phonenumber;

	$phonenumber = str_replace( array( " ", "." ), "", $phonenumber );
	$hrphonenumber = "";
	$i = 0;
	while( $i < strlen( $phonenumber ) / 2 ){

		$hrphonenumber .= substr( $phonenumber, $i * 2, 2 ) . " ";

		$i++;

	}

	/*
	 * Dans le cas d'un click-to-call, si l'utilisateur est à un poste sur une ip interne
	 * on appellement l'évènement du onclick (qui interrompt l'appel du href)
	 * sinon, c'est le href qui est appelé (fonction d'appel depuis un smartphone par ex.)
	 */
	if ($webcall) {
		global $GLOBAL_START_URL;
		$arrInternalIp = explode(',', INTERNAL_IP);
		if (in_array($_SERVER["REMOTE_ADDR"], $arrInternalIp)) {
			$link = $GLOBAL_START_URL.'/templates/back_office/phonecall?phonenumber='. $phonenumber;
		} else {
			$link = "tel:'.$phonenumber.'" ;
		}
		return $link;
	}
	return trim($hrphonenumber);

}

function fphonereadlog () {
	$dateto = date('Y-m-d H:i:s');
	$datefrom = strtotime ( '-10 minute' , strtotime ( $dateto ) ) ;
	$datefrom = date ( 'Y-m-d H:i:s' , $datefrom );


    $result = $phone->journalRead( $datefrom, $dateto ); // temporal line
}



function time_elapsed_string($ptime)
{
    $etime = time() - $ptime;

    if ($etime < 1)
    {
        return '0 seconds';
    }

    $a = array( 365 * 24 * 60 * 60  =>  'an',
                 30 * 24 * 60 * 60  =>  'mois',
                      24 * 60 * 60  =>  'jour',
                           60 * 60  =>  'heure',
                                60  =>  'minute',
                                 1  =>  'second'
                );
    $a_plural = array( 'year'   => 'années',
                       'month'  => 'mois',
                       'day'    => 'jourrs',
                       'hour'   => 'heures',
                       'minute' => 'minutes',
                       'second' => 'secondes'
                );

    foreach ($a as $secs => $str)
    {
        $d = $etime / $secs;
        if ($d >= 1)
        {
            $r = round($d);
            //return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
			return ' Il y a ' . $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) ;
        }
    }
}


?>
