<?php

class crm {

	public $aProperties = array();
	public $aRecords = array() ;

	public function loadUser () {
		//$user =& \user\User::getInstance();
		$this->aProperties["user_id"]   = 1;
		$this->aProperties["user_fullname"] = "D ibarra";
		$this->aProperties["user_photo"]="/users/user128.jpg" ;
	}

	public function g($field , $shownotfound = true) {
		$value= "";
		
		$afield= explode (".",$field) ;
		if (count($afield)==1 ) {  // it's just a property 
				
			if ( ! isset ( $this->aProperties[$field] )) {
				$value = "not found property for ". $afield ;
				$found = false;
			} else {
				$found = true;
				$value = $this->aProperties[$field] ;
			}	

			if ( ! $shownotfound &&  !$found )  $value  ='';

		} else {  // it's a value in records
			$tableid = $afield[0];
			$field  = $afield[1];
			
			if (count($afield)>2 ) { // third parameter is the row; default row 0
				$rownumber = intval ($afield[2]  )	;
			} else  {
				$rownumber = 0;
			}
			$found = false;
			if ( ! isset ( $this->aRecords[$tableid] )) {
				$value = "not found record for ". $tableid ;
			} elseif  ( ! isset ( $this->aRecords[$tableid][$rownumber] )) { 
				$value = "not found row $rownumber for  ". $tableid ;
			} elseif   ( ! isset ( $this->aRecords[$tableid][$rownumber][$field] )) { 
				$value = "not found field $field for  ". $tableid ;
			} else {
				$found = true;
				$value = $this->aRecords[$tableid][$rownumber][$field] ;
			}	

			if ( ! $shownotfound &&  !$found )  $value  ='';
			
		}
		return $value;
	}
}
?>