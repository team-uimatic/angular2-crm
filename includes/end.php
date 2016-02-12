<?php
$endinclude="
</div>
<!-- ./wrapper -->
<!--<script src='libs/angular/angular.js'></script>-->
<!-- jQuery 2.1.4 -->
<script src='plugins/jQuery/jQuery-2.1.4.min.js'></script>
<!-- jQuery 2.1.4 -->
<!-- jQuery UI 1.11.4 -->
<script src='https://code.jquery.com/ui/1.11.4/jquery-ui.min.js'></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.5 -->
<script src='bootstrap/js/bootstrap.min.js'></script>
<!-- Sparkline -->
<script src='plugins/sparkline/jquery.sparkline.min.js'></script>
<!-- jvectormap -->
<script src='plugins/jvectormap/jquery-jvectormap-1.2.2.min.js'></script>
<script src='plugins/jvectormap/jquery-jvectormap-world-mill-en.js'></script>
<!-- jQuery Knob Chart -->
<script src='plugins/knob/jquery.knob.js'></script>
<!-- daterangepicker -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js'></script>
<script src='plugins/daterangepicker/daterangepicker.js'></script>
<!-- datepicker -->
<script src='plugins/datepicker/bootstrap-datepicker.js'></script>
<!-- Bootstrap WYSIHTML5 -->
<script src='plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js'></script>
<!-- Slimscroll -->
<script src='plugins/slimScroll/jquery.slimscroll.min.js'></script>
<!-- FastClick -->
<script src='plugins/fastclick/fastclick.js'></script>
<!-- AdminLTE App -->
<script src='dist/js/app.min.js'></script>
<!-- AdminLTE for demo purposes -->
<script src='dist/js/demo.js'></script>";

$endinclude.="<script src='libs/js/crmglobal.js'></script>";

/*-----Start of anguar js-------*/
$endinclude.='<script src="libs/angular/angular2beta/es6-shim.min.js"></script>
<script src="libs/angular/angular2beta/system-polyfills.js"></script>
<script src="libs/angular/angular2beta/system.js"></script>
<script src="libs/angular/angular2beta/typescript.js"></script>
<script src="libs/angular/angular2beta/angular2-polyfills.js"></script>
<script src="libs/angular/angular2beta/Rx.js"></script>
<script src="libs/angular/angular2beta/angular2.dev.js"></script>
<script src="libs/angular/angular2beta/http.dev.js"></script>';
/*-----End of anguar js-------*/


// load controles for lists
$endinclude.="<script>";
$endinclude.= "System.config({
                transpiler: 'typescript',
                typescriptOptions: {emitDecoratorMetadata: true},
                packages: {'app': {defaultExtension: 'ts'}}
            });";

foreach ($appCrmListControllers as $key=>$aControler) {
    //$endinclude.= "System.import('app/".$key.".ts')";
    switch ($key){
        case 'listaccount';
            $endinclude.="System.import('app/main-account').then(null, console.error.bind(console));";
            break;
    }
}

$endinclude.="</script>";

    
$endinclude.="</body>
</html>";

echo $endinclude;

?>