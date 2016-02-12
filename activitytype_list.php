<?php 

$pagetitle =  "Type Activity"; 
$pagesubtitle = "";
$appCrmListControllers["listactivity"] = array (
				"service"=>"object=activitytype&action=list"
			);
require ('includes/setinit.php');    
require ('templates/list_activitytype.html');    
require ('includes/setend.php');?>