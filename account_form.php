<?php 
$pagetitle =  "Accounts"; 
$pagesubtitle = "";
$appCrmListControllers["listactivity"] = array (
				"service"=>"object=account&action=list&idheader=all"
			);
require ('includes/setinit.php');    
require ('templates/list_account.html');    
require ('includes/setend.php');?>