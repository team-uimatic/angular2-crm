<?php 
$pagetitle =  "Tableau de bord"; 
$pagesubtitle = "Panneau de Commande";

$headerinclude_add = "
  <link rel='stylesheet' href='plugins/fullcalendar/fullcalendar.min.css'>
  <link rel='stylesheet' href='plugins/fullcalendar/fullcalendar.print.css' media='print'>";

/*$endinclude_add ="
<!-- Morris.js charts -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js'></script>
<script src='plugins/morris/morris.min.js'></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src='dist/js/pages/dashboard.js'></script>";*/


$appCrmListControllers["listcrmheader"] = array (
				"service"=>"object=customerheader&action=list&idheader=all"
			);

$appCrmListControllers["listactivity"] = array (
				"service"=>"object=crm_activity&action=list&idheader=all"
			);

			
require ('includes/setinit.php');    
//include ('includes/homepage_smallboxes.php') 

?>      
      <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">
			<? require ('templates/list_crmheader_mini.html');?>
        </section>
        <!-- /.Left col -->
        <!-- right col (We are only adding the ID to make the widgets sortable)-->
        <section class="col-lg-5 connectedSortable">
			<? require ('templates/list_activity_mini.html'); ?>
        </section>
        <!-- right col -->
      </div>
      <!-- /.row (main row) -->
<?php include ('includes/setend.php') 
?>