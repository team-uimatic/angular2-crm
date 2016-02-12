<?php

define( "PERSISTENT_CONNECTION", true );

//include_once( dirname( __FILE__ ) . "/../config/init.php" );
/*
 * @static
 */
 
class DBUtil{
	
	//--------------------------------------------------------------------
	/**
	 * @var ADODB_PDO
	 */
	private static $con;
	
	/**
	 * @access private
	 * @var array $parameters
	 */
	private static $parameters = array();
	
	/**
	 * @access private
	 * @var array $adminParameters
	 */
	private static $adminParameters = array();
	
	//--------------------------------------------------------------------
	
	/**
	 * Retourne une référence vers une connection ADO
	 * @static
	 * @return pear_ADOConnection
	 */
	public static function &getConnection(){

		if( !isset( self::$con ) ){
			
			self::$con =   new mysqli("localhost", "root", "holaktal", "crm") ;
			
			if (self::$con->connect_errno) {
				echo "Echec lors de la connexion à MySQL : (" . self::$con->connect_errno . ") " . self::$con->connect_error;
			}			
			
		}
		
		return self::$con;
		
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Récupère la valeur d'une colonne donnée dans une table donnée pour un critère donné
	 * Insensible à la case
	 * Exécuté une requête SQL du type : SELECT `$fieldname` FROM `$table` WHERE `$uniqueKey` = '$uniqueKeyValue' LIMIT 1
	 * @param string $fieldname le nom de la colonne à sélectionner
	 * @param string $table la table à sélectionné
	 * @param string $uniqueKey le nom de la clé unique
	 * @param mixed $uniqueKeyValue la valeur de la clé unique
	 * @static
	 * @return string le résultat de la recherche ou false si aucun résultat retourné
	 */
	 
	public static function getDBValue( $fieldname, $table, $uniqueKey, $uniqueKeyValue, $allowEmptyResultset = true ){
		
		$query = "
		SELECT `$fieldname`
		FROM `$table`
		WHERE `$uniqueKey` = " . self::quote( $uniqueKeyValue ) . "
		LIMIT 1";
		
		$rs = self::query( $query );
		
		if( $rs === false || ( !$rs->RecordCount() && !$allowEmptyResultset ) )
			trigger_error( "Erreur SQL : $query", E_USER_ERROR );

		if( !$rs->RecordCount() )
			return false;
			
		return $rs->fields( $fieldname );
		
	}	
	
	//--------------------------------------------------------------------
	
	/**
	 * Récupère la valeur d'une colonne donnée dans une table donnée pour un critère donné
	 * Sensible à la case
	 * Exécuté une requête SQL du type : SELECT `$fieldname` FROM `$table` WHERE `$uniqueKey` = '$uniqueKeyValue' LIMIT 1
	 * @param string $fieldname le nom de la colonne à sélectionner
	 * @param string $table la table à sélectionné
	 * @param string $uniqueKey le nom de la clé unique
	 * @param mixed $uniqueKeyValue la valeur de la clé unique
	 * @static
	 * @return string le résultat de la recherche ou false si aucun résultat retourné
	 */
	 
	public static function getBinaryDBValue( $fieldname, $table, $uniqueKey, $uniqueKeyValue, $allowEmptyResultset = true ){
		
		$query = "
		SELECT `$fieldname`
		FROM `$table`
		WHERE BINARY `$uniqueKey` = " . self::quote( $uniqueKeyValue ) . "
		LIMIT 1";
		
		$rs = self::query( $query );
		
		if( $rs === false || ( !$rs->RecordCount() && !$allowEmptyResultset ) )
			trigger_error( "Erreur SQL : $query", E_USER_ERROR );

		if( !$rs->RecordCount() )
			return false;
			
		return $rs->fields( $fieldname );
		
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Retourne le résultat d'une requête SQL donnée
	 * @param string $query la requête SQL à éxécuter
	 * @param bool $allowEmptyResultset autorise ou non l'abscence de résultat ( optionnel, true par défaut )
	 * @param bool $dieOnError interrompt l'éxécution du script PHP en cas d'erreur ou non ( optionnel, true par défaut )
	 * @param string $errorMessage message d'erreur affiché si $dieOnError vaut true et que l'éxécution de la requête produit une erreur
	 * @return ADODB_PDO_ResultSet
	 * la méthode retourne false $dieOnError vaut false, sinon rien 
	 */
	public static function query( $query, $allowEmptyResultset = true ){
		$rs = self::getConnection()->query( $query );
		if (self::getConnection()->error) {
			try {   
				throw new Exception("MySQL error ".self::getConnection()->error." <br> Query:<br> $query", self::getConnection()->errno);   
			} catch(Exception $e ) {
				echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
				echo nl2br($e->getTraceAsString());
			}
		}
		
		return $rs;
		
	}
	
	//--------------------------------------------------------------------

	/**
	 * Retourne l'instance avec la requete préparée
	 * @param type $sql
	 * @return type
	 */
	public static function prepare( $sql ){
		
		$statement = self::getConnection()->DoPrepare( $sql );
		return $statement;
		
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Retourne l'instance avec la requete préparée
	 * @param statement créé avec DBUtil::prepare
	 * @param vars array de variables array( ':var' => $value )
	 * @return recordset | false
	 */
	public static function executePreparedStatement( $statement , $vars ){
	
		$prepared = self::getConnection()->ExecutePreparedStatement( $statement , $vars );
		return $prepared;
		
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Retourne la plus petite valeur de $fieldname disponible ( non utilisée ) dans la table $tableName
	 * @static
	 * @param string $tableName le nom de la table
	 * @param string $fieldname le nom du champ
	 * @return int
	 */
	public static function getUniqueKeyValue( $tablename, $fieldname ){

		$rs = self::query( "SELECT MAX( `$fieldname` ) + 1 AS `$fieldname` FROM `$tablename`" );
		
		if( $rs === false || !$rs->RecordCount() )
			trigger_error( "Impossible de récupérer une nouvellle valeur pour la clé $keyname dans la table $tablename", E_USER_ERROR );
			
		return $rs->fields( $fieldname ) == null ? 1 : $rs->fields( $fieldname );
		
	}
	
	//--------------------------------------------------------------------
	
	public static function stdclassFromDB( $table, $uniqueKey, $uniqueKeyValue ){
		
		$query = "
		SELECT *
		FROM `$table`
		WHERE `$uniqueKey` = " . self::quote( $uniqueKeyValue ) . "
		LIMIT 1";
		$rs = self::query( $query );
		
		if( !$rs->RecordCount() )
			return false;

		return ( object )$rs->fields;

	}
	
	//----------------- 10 June by David
	
	public static function getRowsToArray( $sql ,$fieldname =""){ // get array from  table records
		
		$rs = DBUtil::query( $sql );
		
		$table = array();
		
		$i = 0;
		$rs->	data_seek(0);
		while( $row = $rs->fetch_assoc()) {
			if ($fieldname!="" )  return  $row[$fieldname];
			$table[ $i ] = $row;
			
			$i++;
			
		}
		if ($fieldname!="" && $i == 0 ) return "";
		return $table;
		
	}	
	
	public static function f_insert($table,$aFields,$mode="") { // build intelligent sql insert for table  depending type fields value
		$fields="";
		$values="";
		foreach($aFields as $key=>$val) {
					$fields .= ($fields!=""?",":"").$key;
					if (substr($val,0,3)=="<+>" || substr($val,0,3)=="<->" || substr($val,0,3)=="<@>") {
							if (substr($val,0,3)=="<@>")  $val="'".substr($val,3). "'";  
							if (substr($val,0,3)=="<+>")   $val="+".substr($val,3);
							if (substr($val,0,3)=="<->")    $val="-".substr($val,3);
					} else {
							if (is_null($val)) {
									$val = " NULL";
							} else {
								if (is_string($val) & substr($val,0,1)!="'" )  $val = "'" . addslashes($val) . "'";
							}
					}

					$values .= ($values!=""?",":"").$val;
		}
		$csql1= "insert into $table (" .$fields . " ) values (" .$values . ")";
		return $csql1;

	}
		
	
	
	//--------------------------------------------------------------------
	
	public static function tableFromDB( $table, $index = false ){
		
		$rs = DBUtil::query( "SELECT * FROM `$table`" );
		
		$table = array();
		
		$i = 0;
		while( !$rs->EOF() ){
			
			$key = $index ? $rs->fields( $index ) : $i;
			$table[ $key ] = $rs->fields;
			
			$rs->MoveNext();
			$i++;
			
		}

		return $table;
		
	}
	
	//--------------------------------------------------------------------
	/**
	 * @acces public
	 * @static
	 * @param string $table
	 * @param string $uniqueKey
	 * @param string $uniqueKeyValue
	 * @return DomDocument
	 */
	public static function xmlFromDB( $table, $uniqueKey, $uniqueKeyValue ){
		
		$query = "
		SELECT *
		FROM `$table`
		WHERE `$uniqueKey` = " . self::quote( $uniqueKeyValue ) . "
		LIMIT 1";
		
		$rs = self::query( $query );
		
		if( !$rs->RecordCount() )
			return false;

		$document = new DomDocument( "1.0", "utf-8" );
		
		$root = $document->createElement( utf8_encode( "resultset" ) );
	
		foreach( $rs->fields as $key => $value ){
			
			
			$element 	= $document->createElement( $key );
			$attribute  = $document->createAttribute( "value" ); 
			
			$attribute->appendChild( $document->createTextNode( htmlentities( $value ) ) );
			$element->appendChild( $attribute );
			
			$root->appendChild( $element );
			
		}
		
		$document->appendChild( $root );
		
		return $document;

	}
	
	//--------------------------------------------------------------------
	/**
	 * Recherche un paramètre
	 * @access public
	 * @static
	 * @return string
	 */
	public static function getParameter( $ParamName ){

		if( isset( self::$parameters[ $ParamName ] ) )
			return self::$parameters[ $ParamName ];
			
		$query = "SELECT `paramvalue` FROM `parameter_cat` WHERE `idparameter` = '$ParamName'";
		$rs = self::query( $query );

		if( $rs === false )
			die( "Impossible de récupérer la valeur du paramètre $ParamName." );

		if( !$rs->RecordCount() )
			return false;

		self::$parameters[ $ParamName ] = $rs->fields( "paramvalue" );

		return self::$parameters[ $ParamName ];
		
	}

	//--------------------------------------------------------------------
	/**
	 * @access public
	 * @static
	 * @return void
	 */
	public static function setParameterAdmin($paramName, $value)
	{
		$ret = self::query('UPDATE parameter_admin SET paramvalue='.self::quote($value).' WHERE idparameter ='.self::quote($paramName));
		
		if( $ret !== false )
			self::$adminParameters[ $paramName ] = $value;
		
		return $ret;
		
	}

	//--------------------------------------------------------------------
	/**
	 * Recherche un paramètre de l'administration
	 * @access public
	 * @static
	 * @return string
	 */
	public static function getParameterAdmin( $ParamName ){

		if( isset( self::$adminParameters[ $ParamName ] ) )
			return self::$adminParameters[ $ParamName ];
		
		$query = "SELECT `paramvalue` FROM `parameter_admin` WHERE `idparameter` = '$ParamName'";
		$rs = self::query( $query );

		if( $rs === false )
			die( "Impossible de récupérer la valeur du paramètre $ParamName." );

		if( !$rs->RecordCount() )
			return false;

		self::$adminParameters[ $ParamName ] = $rs->fields( "paramvalue" );

		return self::$adminParameters[ $ParamName ];
		
	}

	//--------------------------------------------------------------------

	/**
	 * Retourne la valeur par défaut d'un champ donné d'une table donnée
	 * @static
	 * @param string $tableName le nom de la table
	 * @param string $fieldName le nom du champ
	 * @return string
	 */
	public static function getDefaultValue( $tableName, $fieldName ){
		
		$query = "SELECT default_value FROM desc_field WHERE tablename LIKE " . self::quote( $tableName ) . " AND fieldname LIKE " . self::quote( $fieldName ) . " LIMIT 1";
		
		$rs = self::query( $query );
		
		if( !$rs->RecordCount() )
			return false;
			
		return $rs->fields( "default_value" );
		
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Retourne le nombre de lignes affectées par la dernière requête SQL
	 * @return int
	 */
	public static function getAffectedRows(){
		
		return self::getConnection()->Affected_Rows();
		
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * @return string
	 */
	public static function quote( $string ){
		
		return self::getConnection()->quote( $string );
		
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * @deprecated préférer DBUtil::quote( $string )
	 * @return string
	 */
	public static function real_escape_string( $string ){
		
		return substr( self::quote( $string ), 1, -1 );
		
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Retourne la dernière valeur d'autoincrement mise à jour
	 * @return int
	 */
	public static function getInsertID(){
		
		return self::getConnection()->Insert_ID();
		
	}
	
	//--------------------------------------------------------------------
	
}
