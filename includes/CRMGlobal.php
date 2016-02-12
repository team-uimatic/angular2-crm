<?php
include_once( "DBUtil.php" );


class CRMGlobal {

	public $basetype = array (
		0 => array (
				"description"=>"Autres",
				"properties"=> ""
			),
		1 => array (
				"description"=>"Appel téléphonique",
				"properties"=> ""
			),
		5 => array (
				"description"=>"Email",
				"properties"=>""
			),
		10 => array (
				"description"=>"Rendez-vous",
				"properties"=>""
			)
	);
	
	public $fieldtype = array (
	
		1 => array (
				"description"=> "Char"
			),
		2 => array (
				"description"=> "Numeric"
			),
		3 => array (
				"description"=> "Price"
			),
		4 => array (
				"description"=> "Quantity"
			),
		5 => array (
				"description"=> "Entity"
			),
		6 => array ( 
				"description"=> "Text"
			),
		7 => array (
				"description"=> "Logic"
			),
		8 => array (
				"description"=> "Int"
			),
		9 => array (
				"description"=> "Phone"
			),
		10 => array (
				"description"=> "Email"
			)			
			
	
	);
	
	public $statusreasons = array ();

	
	public $jscript = "";
	public $aFields = array();
	public $aFieldsSave = array();
	public $aTables = array();
	public $paremeters = array();
	public $aRecords = array(); // records execution with a line for each result
	public $aRecordsCache = array();
	public $aTablesToUpdate  = array ();
	public $aFieldsSent  = array ();
	public $aRecordsLastRun = array (); // records with last execution 
	public $urlicons="/modules/crm/icons/";

	public $aResult = array(); // json array reponse
	public $aMonths = array( 1 => "Janvier", 2 => "Février", 3 => "Mars", 4 => "Avril", 5 => "Mai", 6 => "Juin", 7 => "Juillet", 8 => "Août", 9 => "Septembre", 10 => "Octobre", 11 => "Novembre", 12 => "Décembre" );

	public $aMonthsShort = array( 1 => "Jan", 2 => "Fév", 3 => "Mar", 4 => "Avr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Aoû", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Déc" );

	function __construct( $parameteres){
		$this->parameters= $parameteres;

		$this->loadInitVars ();
		
		$this->tablesInitialize();
		
		if (isset($_REQUEST['formsave']) && $_REQUEST['formsave'] =="1") {
			$this->formsave();
			//echo "saved";
			die;
		}
		
		$table = $this->getTableNameFromObject ($this->parameters["object"]);	
		
		$keyfield = $this->aTables[$table]["keyfield"] ;
		if ( $this->parameters["action"]=="list" ) {
			$this->jobRecord($table,"get");
		}  elseif ( $this->parameters["action"]=="get" ) {

			$this->jobRecord($table,"get", array ( $keyfield =>  $_GET[$keyfield] ) );
		}  elseif ( $this->parameters["action"]=="update" ) {	
			$this->formsave( "update" , array ( $keyfield =>  $_GET[$keyfield] ) );
		}  elseif ( $this->parameters["action"]=="add" ) {	
			$this->formsave( "add" );

		}
	
		
		
		
	}
	function startNewRecord ( $Table) {
		
		$this->initFields( $Table);

		foreach ($this->aFields[$Table] as $keyField=>$aField ) {
				$fieldValue =  '';
				$aRecord[$aField["field"]] = $fieldValue;
		}
		
		$this->aRecords[$Table] = array($aRecord) ;
		
	}
	
	function arrayjsonencode ($array) {
		
		array_walk_recursive($array, function(&$item, $key){
			$item = utf8_encode($item);
		});
	
		$array = json_encode ( $array , JSON_FORCE_OBJECT)	;
		return $array;
	}

	
	function getTableNameFromObject ( $object)  {
		if ($object =="tablevirtuel") { // use for table alias
			return "tablereal";
		}
		return $object;
	}	
	function formsave ( $action ) {
		$this->parameters['action'] = $action ; // redondant; but make it sure action
		$this->aTablesToUpdate[] = $this->getTableNameFromObject ($this->parameters['object']);
		$this->fieldsSent();
		$this->setFields();
		$this->saveFields();
	}
	
	function fieldsSent() {
		$this->aFieldsSent = $_REQUEST ;
	}
	function initFields ($Table) {  // Update Fields Structure accord defaults 
			
		$aFields = $this->aFields[$Table];
		$aNewFields = array ();
		foreach ($aFields as $keyField=>$aField ) {
			
			$aDefaultField = array (
				"update" => "",
				"field" => "",
				"globalvar" => "",
				"title"=> "",
				"type" => "Int",
				"length" => "",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
			);
			
			$aField  = 	array_merge ($aDefaultField , $aField) ; 
			if ($aField["field"]=="" ) $aField["field"] = $keyField ; // set field name same key field if it'is not stored
			$aNewFields[$keyField] = $aField;
		}
		$this->aFields[$Table]=  $aNewFields;
			
	}
	function setFields () {	
		$action = $this->parameters['action'] ;

		foreach ( $this->aTablesToUpdate as $Table ) {
			$this->initFields ($Table);
			$this->aFieldsSave[$Table] = array ();
			foreach ($this->aFields[$Table] as $keyField=>$aField ) {
				
				//echo "<br>action $action "	 . $aField["field"]  ;
				$fieldValue ="";
				$includeField = true;
				// just take in  ->aFieldsSave , the fields that are sent 
				if (strpos ($aField["update"], "auto" ) !==false) { // auto take value from array; not from request
					$includeField = false;
					$fieldValue =  $aField["value"];
					if ( strpos ($aField["update"] , "add")    !==false && $action =="add"    )  $includeField = true;
					if ( strpos ($aField["update"] , "update") !==false && $action =="update" )  $includeField = true;
				} elseif (strpos ($aField["update"], "noupdate")  !==false) {
					$includeField = false;
				} else { // look for request value  sent*/
					if (isset($this->aFieldsSent[$keyField])) {
						//echo " isset ";
						//$fieldValue =  utf8_decode($this->aFieldsSent[$keyField]); // decode because it's need to save in DB latin page
						
						if (strpos( $aField["type"] ,"date")!== false ) {
							// convierte date to format YYYY/mm/dd
							if ($fieldValue!="") $fieldValue = dmyTOyyymmdd ($fieldValue); // only for not empty date
						}	
					} else {
						if ( $aField["globalvar"]!=""  && isset($aField["globalvar"]) && isset($this->aFieldsSent[ $aField["globalvar"]])  ) { // review case primary key fields , sent wit "add" action
							$fieldValue = $this->aFieldsSent[ $aField["globalvar"] ];
							//echo " globalvar ".$aField["globalvar"];
						} else {
							$includeField = false;
						}
					}
				}

				if ($includeField) {
					//echo " yes field:" . $aField["field"]. "  value:". $fieldValue;
					$this->aFieldsSave[$Table][$aField["field"]] = $fieldValue;
				}
			}
		}
	}
	function saveFields () {

		foreach ( $this->aTablesToUpdate as $Table ) {
			//$tablename = $this->aTables[$Table]["tablename"];
			$tablename = $Table;
			
			$aKeys = explode (",",$this->aTables[$Table]["keyfield"]);
			
			$where =" 1=1";
			/*echo "<pre>";
			var_dump ($this->aFieldsSave[$Table]);
			echo "</pre>";*/

			if ( $this->parameters['action'] =="add" ) {
				 $this->recordSave ("add", $tablename, $this->aFieldsSave[$Table] );
			} else {	
				foreach ($aKeys as $key ) {  // key are stored in hidde inputs , but they are not in the update liste fields; then take from global parameter
					//$where.= " and ". $key. " ="  .$this->aFieldsSave[$Table][$key] ;
					$where.= " and ". $key. " ="  .  $_GET [ $key] 	 ;
					
				}
				 $this->recordSave ("update",$tablename, $this->aFieldsSave[$Table] , $where) ;
			}
		}
		
	}
	function recordSave ( $action,$table, $aFields,$where="") {
		/*echo "<pre>";
		var_dump($_REQUEST);
		echo "</pre>";*/
		
		$fields="";
		$values="";
		$fieldsvalues="";
		foreach($aFields as $key=>$val) {
			$keyField = $key;
			$aField = $this->aFields[$table][$keyField ];
			
			$includeField = true;

			if (strpos ($aField["update"], "key" ) !==false) { // auto take value from array; not from request
				$includeField = false;
			} elseif (strpos ($aField["update"], "noupdate")  !==false) {
				$includeField = false;
			} 

			if ($includeField) {

				
				$val = "'" .  addslashes($val) . "'"; 

				$fields .= ($fields!=""?",":"").$key;
				$values .= ($values!=""?",":"").$val;
				
				$fieldsvalues.=  ($fieldsvalues!=""?",":""). $key ." = ". $val;
			}
		}
		if ($action =="add") {
			$csql1= "insert into $table (" .$fields . " ) values (" .$values . ")";
		}
		if ($action =="update") {
			if ($where=="") die ("error no keys");
			$csql1= "update   $table set ".$fieldsvalues ." where $where";
		}

		//echo $csql1 ; 	
		$rs = DBUtil::query( $csql1 );

		


		// just for tables with one key
		$keyfield = $this->aTables[$table]["keyfield"] ;

		if( $rs === false ) {
			$this->aResult["error"] = 1 ;
			$this->aResult["errormessage"] = "Erreur SQL : $csql1 : Erreur: ". E_USER_ERROR ;
		} else {
			if ($action =="add") {
				$this->aResult[$keyfield] = DBUtil::getConnection()->insert_id;
			}	else {
				$this->aResult[$keyfield] = $this->parameters[$keyfield];
			}
			$this->aResult["id"] =  $this->aResult[$keyfield];
		}
		
		
		
	
	}

	function sqlCount( $tableid,$aKeys = array() )  {  // get total records
		$sqlCount=$this->getSql ($tableid,"count",$aKeys);
		$count = DBUtil::getRowsToArray( $sqlCount ,"count");
		return $count;
	}
	function getSql ( $tableid,$task,$aKeys = array() )   {  // get sql string

		if ( !isset ($this->sql[$tableid]) ) {
			die ("error not foud [sql] for table:".$tableid);
		} elseif ( !isset ($this->sql[$tableid]["from"]) ) {
			if ( !isset ($this->sql[$tableid][$task]) ) {
				die ("error not foud [sql] for table:".$tableid. " , task:".$task);
			} else {
				$sql = $this->sql[$tableid][$task] ;
			}	
		} else {
			$sql= "SELECT ";
			if ($task=="count") {
				$sql.= " count(*) as count ";
			} else {
				$sql.= $this->sql[$tableid]["fields"] ;
			}
			$sql.= $this->sql[$tableid]["from"]
			. " WHERE 1=1 {where} ";
			
			if ($task!="count") {
				$sql.= " ORDER BY ". $this->sql[$tableid]["order"];
			}
		}

		$where ="";
		foreach ($aKeys as $aKey => $value) {
			$where =" and ". $aKey . " = " . $value ;
		}	
		
		$sql = str_replace (  "{where}", $where , $sql );
		return $sql;
		
	}


	function jobRecord($tableid,$task,$aKeys = array() , $cache= false) {

	
		$sql_limit="";

		if ( $this->parameters["action"]=="list"  ) {
			$this->aRecordsLastCount = $this->sqlCount ($tableid,$aKeys);
			if (isset($_GET['page'])) {
				$pagenum = $_GET['page'];
				$pagesize = $_GET['size'];
				$offset = ($pagenum - 1) * $pagesize;
				$sql_limit =  " LIMIT $offset, $pagesize";
			}
		}

		if ( $this->parameters["action"]=="get"  ) {
			
		}
		
		$sql = $this->getSql ($tableid,$task,$aKeys);
		$sql.= $sql_limit ;

		//$sql = $this->sql[$tableid][$task] ;


		//echo $sql;		
		 
		$aRecords= DBUtil::getRowsToArray( $sql );

		
		// store record in array table structure
		
		$tablerealname =  $tableid; //$this->aTables[$tableid]["tablename"];
		
		
		$aRecordsProcessed= array ();
		
		foreach ($aRecords as $aRecord ) {
			/*if (isset ($this->aFields[$tableid]) ) {
				foreach ($aRecord as $fieldname=>$fieldvalue ) {
					$fieldrealname = f_getfromARow ( $this->aFields[$tableid]  , "field", $fieldname, -1) ;
					//echo "<br> $fieldname = ". $fieldrealname. " : ". $fieldvalue;
					$aRecord[$fieldrealname]= $fieldvalue;
				}	
			}*/
			$aRecordsProcessed[]=$aRecord;
		}
		$this->aRecords[$tableid] = $aRecordsProcessed;
		if ($cache) {
			$this->aRecordsCache[$tableid] = $aRecordsProcessed;
		}
		$this->aRecordsLastRun = $aRecordsProcessed;
	
	}
	function g($field , $shownotfound = false ) {
		$value= "";

		$afield= explode (".",$field) ;
		if (count($afield)>1 ) {
			$tableid = $afield[0];
			$field  = $afield[1];
			
			//-- look for field , to get format and textbox ; like dates
			if  ( isset ($this->aFields[$tableid][$field]) ) {
				$aField =   $this->aFields[$tableid][$field];
			} else {
				$aField = "";
			}
			//-- 
			
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
				if (is_array($aField)) { // field is defined in structure
					if ( isset($aField["type"]) && strpos( $aField["type"] ,"date")!== false ) {
						$value = format_datetoinput($value);
					}	
				}
				
			}	
			if ( ! $shownotfound &&  !$found )  $value  ='';
			
		}
		
		return $value;
	
	}
	function loadInitVars () {
		
		$this->aResult["error"] = 0 ;
		$this->aResult["errormessage"] = "" ;

		$this->aTables= array();
		$this->aTables["account"] =   array (
			"tablename"=> "account",
			"keyfield"=> "id_account"
		);
		$this->aTables["dossier"] =   array (
			"tablename"=> "dossier",
			"keyfield"=> "id_dossier"
		);
		$this->aTables["dossiertype"] =   array (
			"tablename"=> "dossiertype",
			"keyfield"=> "id_dossiertype"
		);
		$this->aTables["activity"] =   array (
			"tablename"=> "activity",
			"keyfield"=> "id_activity"
		);
		$this->aTables["activitytype"] =   array (
			"tablename"=> "activitytype",
			"keyfield"=> "id_activitytype"
		);
		$this->aTables["contact"] =   array (
			"tablename"=> "contact",
			"keyfield"=> "id_contact"
		);
		$this->aTables["country"] =   array (
			"tablename"=> "country",
			"keyfield"=> "id_country"
		);
		
		
		
		foreach ($this->aTables as $key=>$table) {
				$this->aRecords[ $key]= array ()	;
		}
		
		
		
		// -- STATUS DOSSIER CUSTOMER
		$this->statusheader = array (  
				0 => array (
					"description"=>"Ouvert",
					"properties"=>"",
					"status"=> 0
					),
				1 => array (
					"description"=>"Terminé",
					"properties"=>"",
					"status"=> 1
					),
				2 => array (
					"description"=>"Annulé",
					"properties"=>"",
					"status"=> 2
					)
			);


		// -- STATUS REASONS ACTIVITIES
		// Other activities
		// Appel téléphonique
		$this->statusextrafields["Duration"] =  array (
				"title" => "Duration",
				"type"  => "Time",
			);

			
		$this->statusreasons[0] = array (   
				0 => array (    // Example :  key "0" -> real id status for table crm_actitivity;  but "statusbase" it's for stastics ;  basestatus; simply open , close  or cancelled
					"description"=>"Ouvert",
					"properties"=>"",
					"statusbase"=> 0
					),
				3 => array (
					"description"=>"Terminé",
					"properties"=>"",
					"statusbase"=> 1
					),
				4 => array (
					"description"=>"Annulé",
					"properties"=>"",
					"statusbase"=> 2
					)
			);

		$this->statusreasons[1] = array (  
				/*1 => array (
					"description"=>"Ouvert",
					"properties"=>"",
					"status"=> 0
					),*/
				2 => array (
					"description"=>"Appel Sortant",
					"icon"=> "phonecall.png",
					"properties"=>"",
					"fields"=> array (
							"Duration" => $this->statusextrafields["Duration"]
						),
					"statusbase"=> 1
					),
				3 => array (
					"description"=>"Appel Entrant",
					"properties"=>"",
					"icon"=>"phonereceived.png",
					"fields"=> array (
							"Duration" => $this->statusextrafields["Duration"]
						),
					"statusbase"=> 1
					),
				/*3 => array (
					"description"=>"Annulé",
					"properties"=>"",
					"statusbase"=> 2
					)*/
			);
	
		// Rendez-vous status
		$this->statusreasons[10] = array (  
				1 => array (
					"description"=>"Planifié",
					"properties"=>"",
					"statusbase"=> 0
					),
				3 => array (
					"description"=>"Terminé",
					"properties"=>"",
					"statusbase"=> 1
					),
				4 => array (
					"description"=>"Annulé",
					"properties"=>"",
					"statusbase"=> 2
					)
			);
		// Email Status
		$this->statusreasons[5] = array (  
				1 => array (
					"description"=>"Rédiger",
					"properties"=>"",
					"statusbase"=> 0,
					"icon"=>"emailcompose32.png",

					),
				2 => array (
					"description"=>"Envoyé",
					"properties"=>"",
					"statusbase"=> 1,
					"icon"=>"emailsent.png"
					),
				3 => array (
					"description"=>"Reçu",
					"properties"=>"",
					"statusbase"=> 1,
					"icon"=>"emailreceveid.png",
					)/*,
				4 => array (
					"description"=>"Annulé",
					"properties"=>"",
					"statusbase"=> 2
					)*/
			);

		// Communication
		$this->statusreasons[7] = array (  
				/*1 => array (
					"description"=>"Ouvert",
					"properties"=>"",
					"statusbase"=> 0
					),*/
				2 => array (
					"description"=>"Effectué",
					"properties"=>"",
					"icon"=>"",
					"statusbase"=> 1
					),
				3 => array (
					"description"=>"Reçu",
					"properties"=>"",
					"icon"=>"",
					"statusbase"=> 1
					),
				/*3 => array (
					"description"=>"Annulé",
					"properties"=>"",
					"statusbase"=> 2
					)*/
			);


		// Records por default empty	
			// Translate title fields	
		$this->aTranslate[ "[customer.fullname]"]= "Raison Sociale";
		$this->aTranslate[ "[customer.tradename]"]= "Nom Commercial";
		$this->aTranslate[ "[customer.address_line1]"]= "Adresse";
		$this->aTranslate[ "[customer.address_line2]"]= "Adresse comp.";
		$this->aTranslate[ "[customer.zipcode]"]= "Code Postal";
		$this->aTranslate[ "[customer.city]"]= "Ville";
		$this->aTranslate[ "[customer.countryname]"]= "Pays";


	}
	function getTableSql($table) {
		$csql = $this->sql[$table]["get"]  =  "SELECT "
			. $this->sql[$table]["fields"] 
			. $this->sql[$table]["from"]
			. " WHERE 1=1 {where} "
			. " ORDER BY ". $this->sql[$table]["order"];
		return $csql;	
	}
	function tablesInitialize ( ){

		//-- account
		$table = "account";
		$this->sql[$table]["order"]  =  " id_account desc";
		$this->sql[$table]["fields"]  =  $table.".*,countryname ";
		$this->sql[$table]["from"] = "			
			FROM `".$table."` 
				LEFT JOIN `country`  ON countryid = country.id_country ";

		$this->sql[$table]["get"]  =  $this->getTableSql($table);

			
		// dossier
		$table = "dossier";
		$this->sql[$table]["order"]  =  " id_dossier desc";
		$this->sql[$table]["fields"]  =  $table.".*,accountname,user.shortname,dossiertypename,contact.firstname,contact.lastname  ";
		$this->sql[$table]["from"]  =  "
			FROM `".$table."` 
				LEFT JOIN `dossiertype`  ON dossiertypeid  = dossiertype.id_dossiertype
				LEFT JOIN `account`  on  accountid  = account.id_account
				LEFT JOIN `contact`  ON  dossier.contactid  = id_contact
				LEFT JOIN `user`      ON dossier.createiduser  = user.iduser
		";
		$this->sql[$table]["get"]  =  $this->getTableSql($table);

		// contact 	
		$table = "contact";
		$this->sql[$table]["order"]  =  " id_contact desc";
		$this->sql[$table]["fields"]  =  " contact.*, accountname , countryname";
		$this->sql[$table]["from"] = "
		FROM `".$table."` 
			LEFT JOIN `account` f ON  accountid = id_account
			LEFT JOIN `country`   ON  contact.countryid = country.id_country ";
		$this->sql[$table]["get"]  =  $this->getTableSql($table);

		// activity
		$table = "activity";
		$this->sql[$table]["fields"] = $table.".*, activitytypename, account.accountname, contact.firstname, contact.lastname , dossiertypename";

		$this->sql[$table]["from"] = "	
			FROM `".$table."` 
				LEFT JOIN `dossier` ON id_dossier  = id_dossier
				LEFT JOIN `dossiertype` ON dossiertypeid  = id_dossiertype
				LEFT JOIN `activitytype` ON activitytypeid  = id_activitytype
				LEFT JOIN `user` u  ON activitytype.createiduser  = u.iduser
				LEFT JOIN `account`  ON activityaccountid = id_account
				LEFT JOIN `contact`  ON activitycontactid = id_contact ";

		$this->sql[$table]["order"] = "id_activity desc" ;		
		$this->sql[$table]["get"]  =  $this->getTableSql($table);

		// activitytype
		$table = "activitytype";
		$this->sql[$table]["fields"] = $table.".*";

		$this->sql[$table]["from"] = "	
			FROM `".$table."` 
			";

		$this->sql[$table]["order"] = "createdate desc" ;		
		$this->sql[$table]["get"]  =  $this->getTableSql($table);

		// dossiertype
		$table = "dossiertype";
		$this->sql[$table]["fields"] = $table.".*";

		$this->sql[$table]["from"] = "	
			FROM `".$table."` 
			";

		$this->sql[$table]["order"] = "createdate desc" ;		
		$this->sql[$table]["get"]  =  $this->getTableSql($table);

		// country
		$table = "country";
		$this->sql[$table]["fields"] = $table.".*";

		$this->sql[$table]["from"] = "	
			FROM `".$table."` 
			";

		$this->sql[$table]["order"] = "countryname" ;		
		$this->sql[$table]["get"]  =  $this->getTableSql($table);


		$this->aFields["dossier"]  =  array (
			"id_dossier" =>  array (
				"update" => "key",
				"globalvar" =>  "id_dossier"
				),
			"dossiertypeid" =>  array (
				),

			"accountid" =>  array (
				"globalvar" =>  "accountid",
				"relation" => "account.id_account"
				),
			"createdate" =>  array (
				"update" => "auto,add",
				"type" => "datetime",
				"value"=>date( "Y-m-d H:i:s" )
				),
			"createiduser" =>  array (
				"update" => "auto,add",
				"value"=>  0
				),
			"lastupdate" =>  array (
				"update" => "auto,update",
				"type" => "datetime",
				"value"=>date( "Y-m-d H:i:s" )
				),
			"updateiduser" =>  array (
				"update" => "auto,update",
				"value"=>  0
				),
			"contactid" =>  array (
				),
			"comments"	=>  array (
				"type" =>"text"
				),				
			"result_crmheaderid" =>  array (
				),
			"result_activitytype" =>  array (
				),
			"status" =>  array (
				),
			"erased" =>  array (
				),
			"erasediduser" =>  array (
				"update" => "auto,delete"
				)
		);
		$this->aFields["activity"]  =  array (
			"id_activity" =>  array (
				"update" => "key",
				"globalvar" =>  "idactivity"
				),
			"dossierid"	 =>  array (
				"globalvar" =>  "dossierid"
				),
			"customerid"	 =>  array (
				"globalvar" =>  "activityaccountid"
				),
			"createdate" =>  array (
				"update" => "auto,add",
				"type" => "datetime",
				"value"=>date( "Y-m-d H:i:s" )
				),
			"createiduser" =>  array (
				"update" => "auto,add",
				"value"=>  0
				),
			"lastupdate" =>  array (
				"update" => "auto,update",
				"type" => "datetime",
				"value"=>date( "Y-m-d H:i:s" )
				),
			"updateiduser" =>  array (
				"update" => "auto,update",
				"value"=>  0
				),
			"activitycontactid"	=>  array (
				"relation" => "contact.id_contact=this"
				),
			"activitytypeid"	=>  array (
				"relation"=>"crm_activitytype."
				),

			"activity_subject"	=>  array (
				"type" =>"text"
				),
			"duration" =>   array (
				"type" =>"int"
				),
			"taskdate" =>   array (
				"type" =>"date"
				),
			"recalltimes" =>   array (
				"type" =>"text"
				),
			"result" =>   array (
				"type" =>"text"
				),
			"activity_content"	=>  array (
				"type" =>"text"
				),
			"activity_comments"	=>  array (
				"type" =>"text"
				),
			"activitystatus"	=>  array (
				"type" =>"array",
				"values" => '$crmGlobal->statusreasons[$basetype]',
				"default"=> '0'
				),
			"start_date"	=>  array (
				"type" =>"datetime"
				),
			"end_date"	=>  array (
				"type" =>"datetime"
				),
			"activity_result_type"	=>  array (
				),
			"activity_result_activityid"	=>  array (
				)
		   ) ;


			
			
		$this->aFields["account"]  =  array (
			"id_account" =>  array (
				"update" => "key",
				"globalvar" =>  "id_account"
				),
			"accountname" =>  array (
				"field" => "accountname",
				"title"=> "[customer.fullname]",
				"type" => "Char",
				"length" => 100,
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"phone" =>  array (
				"field" => "phone",
				"type" => "Char",
				"length" => 100,
				"title"=> "[customer.address1]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"email" =>  array (
				"field" => "email",
				"type" => "Char",
				"length" => 100,
				"title"=> "[customer.address1]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"address1" =>  array (
				"field" => "address1",
				"type" => "Char",
				"length" => 100,
				"title"=> "[customer.address1]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"address2" =>  array (
				"field" => "address2",
				"type" => "Char",
				"length" => 40,
				"title"=> "[customer.address2]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"zipcode" =>  array (
				"field" => "zipcode",
				"type" => "Char",
				"length" => 20,
				"title"=> "[customer.zipcode]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"city" =>  array (
				"field" => "city",
				"type" => "Char",
				"length" => 50,
				"title"=> "[customer.city]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"countryid" =>  array (
				"field" => "countryid",
				"relation" => "country.id_country",
				"relationtype" => "ManyToOne",
				"type" => "Int",
				"length" => 40,
				"title"=> "",
				"value"=> "",
				"visible"=> false,
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"country__name" =>  array (
				"field" => "country___name",
				"fieldsource" => "idstate",
				"type" => "Char",
				"length" => 40,
				"title" =>"[customer.countryname]",
				"value"=> "",
				"store"=> false,
				"validator" => "
					",
				"properties"=> "
					
					"
				)
		);
				
		$this->aFields["contact"]  =  array (
			"id_contact" =>  array (
				"update" => "key",
				"globalvar" =>  "id_contact"
				),
			"accountid" =>  array (
				"field" => "accountid",
				"relation" => "account.id_account",
				"relationtype" => "ManyToOne",
				"type" => "Int",
				"length" => 40,
				"title"=> "",
				"value"=> "",
				"visible"=> false,
				"validator" => "
					",
				"properties"=> "
					
					"
				),				
			"firstname" =>  array (
				"field" => "firstname",
				"title"=> "[customer.fullname]",
				"type" => "Char",
				"length" => 50,
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"lastname" =>  array (
				"field" => "lastname",
				"title"=> "[customer.fullname]",
				"type" => "Char",
				"length" => 50,
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"phone" =>  array (
				"field" => "phone",
				"type" => "Char",
				"length" => 100,
				"title"=> "[customer.address1]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"email" =>  array (
				"field" => "email",
				"type" => "Char",
				"length" => 100,
				"title"=> "[customer.address1]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"address1" =>  array (
				"field" => "address1",
				"type" => "Char",
				"length" => 50,
				"title"=> "[customer.address1]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"address2" =>  array (
				"field" => "address2",
				"type" => "Char",
				"length" => 50,
				"title"=> "[customer.address2]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"zipcode" =>  array (
				"field" => "zipcode",
				"type" => "Char",
				"length" => 20,
				"title"=> "[customer.zipcode]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"city" =>  array (
				"field" => "city",
				"type" => "Char",
				"length" => 50,
				"title"=> "[customer.city]",
				"value"=> "",
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"countryid" =>  array (
				"field" => "countryid",
				"relation" => "country.id_country",
				"relationtype" => "ManyToOne",
				"type" => "Int",
				"length" => 40,
				"title"=> "",
				"value"=> "",
				"visible"=> false,
				"validator" => "
					",
				"properties"=> "
					
					"
				),
			"country__name" =>  array (
				"field" => "country___name",
				"fieldsource" => "idstate",
				"type" => "Char",
				"length" => 40,
				"title" =>"[customer.countryname]",
				"value"=> "",
				"store"=> false,
				"validator" => "
					",
				"properties"=> "
					
					"
				)
		);
							
	}
	
	
	public function getlink ( $parameters , $parametersDeleted="action",$autoparent = true ) {
		
		
		$aParameters = http_explode ($parameters) ;
		$aParametersDeleted = explode ("&",$parametersDeleted) ;
		$globalParemeters= $this->parameters;
		
		if ( $autoparent ) {
			$aParameters["parentform"]=$this->formid;
			$aParameters["parentobject"]=$this->parameters["object"];
			$aParameters["parentaction"]=$this->parameters["action"];
			$aParameters["parentidentifier"]=$this->parameters["identifier"];

			}
		
		foreach ( $globalParemeters as $parameter=>$value ) { // delete parameters from global accord to paramsdeleted
			if ( in_array  ($parameter , $aParametersDeleted ) ) { 
				unset ($globalParemeters[$parameter] );
			}
		}
		$aLinkParameters = 	array_merge ($globalParemeters , $aParameters ) ;  // just merge global parameters with parameters sent
		$link ="nothing=nothing&";
		foreach ( $aLinkParameters as $parameter=>$value ) {
			if ( ! isset ($aParametersDeleted[$parameter]) ) { 
				$link.= "&".$parameter."=".$value;
			}
		}
		return $link;

	}
	
	public function formGlobalVariables( $form ="") {
		$content ="";
		foreach ( $this->parameters as $parameter=>$value ) {
				$content.= "<input type='hidden' name='". $parameter. "'  value='".$value."'>";	
		}
		return $content;
	}

	
	
}
