<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);

// default variables
if (!isset($appCrmListControllers))  $appCrmListControllers = array();


/*//--
include_once( dirname( __FILE__ ) . '/../../config/init.php' );

if ( !\user\User::getInstance()->getId() ){
    exit();
}*/
include_once( 'crm.php' );


$crm  = new crm ($parameters) ; 	
$crm->loadUser();
$jscript_end="";
$headerinclude = "<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Content-type' content='text/html; charset=UTF-8'>
  <meta http-equiv='X-UA-Compatible' content='IE=edge'>
  <title>Axess CRM | ". $pagetitle . "></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
  <!-- Bootstrap 3.3.5 -->
  <link rel='stylesheet' href='bootstrap/css/bootstrap.min.css'>
  <!-- Font Awesome -->
  <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css'>
  <!-- Ionicons -->
  <link rel='stylesheet' href='https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css'>
  <!-- Theme style -->
  <link rel='stylesheet' href='dist/css/AdminLTE.min.css'>
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel='stylesheet' href='dist/css/skins/_all-skins.min.css'>
  <!-- iCheck -->
  <link rel='stylesheet' href='plugins/iCheck/flat/blue.css'>
  <!-- Morris chart -->
  <link rel='stylesheet' href='plugins/morris/morris.css'>
  <!-- jvectormap -->
  <link rel='stylesheet' href='plugins/jvectormap/jquery-jvectormap-1.2.2.css'>
  <!-- Date Picker -->
  <link rel='stylesheet' href='plugins/datepicker/datepicker3.css'>
  <!-- Daterange picker -->
  <link rel='stylesheet' href='plugins/daterangepicker/daterangepicker-bs3.css'>
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel='stylesheet' href='plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css'>
";

$headerinclude.="<link rel='stylesheet' href='libs/css/crmback.css'>";


if ( isset($headerinclude_add) )  $headerinclude.= $headerinclude_add ;

$headerinclude.= "
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src='https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js'></script>
  <script src='https://oss.maxcdn.com/respond/1.4.2/respond.min.js'></script>
  <![endif]-->
</head>";




$headerinclude.="<body class='hold-transition skin-blue sidebar-mini'>
<div class='wrapper'>";

echo $headerinclude;

?>