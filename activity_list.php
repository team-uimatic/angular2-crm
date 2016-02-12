<?php 
$pagetitle =  "Activités"; 
$pagesubtitle = "";
$appCrmListControllers["listactivity"] = array (
				"service"=>"object=activity&action=list"
			);
require ('includes/setinit.php');    
require ('templates/list_activity.html');    
require ('includes/setend.php');?>