// jquery 1.7.2
var aStatusReasons ;
var crmInterval;
//crmInterval = false;
function crmCustomerActivities ( idcustomer) {
	dialogCRM ( 'object=customerautoheader&idcustomer='+  idcustomer , {dtitle :'Client Dossier'} );
}
function dialogCRM ( url,options) {
	var defaults = {
        width : 1000,
		dtitle : 'CRM Clients',
		modecrud : '',
		qfmodal: true
    }
    var datapost= {};
	identifier = uniqId();
	formid = 'form_'. identifier;
	dialogid = 'dialog_'+identifier;
	
    var options = $.extend({}, defaults, options || {});

	dialogOpenning = ( typeof dialogOpenning != 'undefined' && dialogOpenning instanceof Array ) ? dialogOpenning  : 0;
	if (dialogOpenning==1){ return false};
	crmurlprocess  = '/modules/crm/crmgetdata.php?'+url+'&identifier='+identifier+'&typereponse=php';
	var dialog = $('<div style="display:hidden"></div>').appendTo('body');
	dialogOpenning=1;
	$('.crmloading').show();
	dialog.load(
		crmurlprocess, 
		datapost,
		function (responseText, textStatus, XMLHttpRequest) {
			dialogOpenning=0;
			$('.crmloading').hide();
			dialog.dialog({title:options.dtitle, width: options.width, bgiframe: true,modal: options.qfmodal,  
				close: function(ev, ui) { 
					if (crmInterval) clearInterval (crmInterval);
					$(this).remove(); 
				} 
			});
			$(dialog).attr("id", dialogid ); //jquery <1.6
			//$(dialog).prop('id','dialog_'+dobject ); // jquery >=1.6
		}
	);
	//prevent the browser to follow the link
	return false;
}

function changerContact (formid) {

	idheader = $('#'+formid+' input[name=idheader]').val();	
	identifier = formid.replace("form_","")
	console.log (idheader);
	if (idheader=='')	{
		alert ('Il faut creer un dossier pour ajouter une activité');
		return false;
	}
	idcustomer =  $('#'+formid+' input[name=idcustomer]').val();	
	idheader =  $('#'+formid+' input[name=idheader] ').val();	
	parentobject = $('#'+formid+' input[name=object]').val();	
	parentform = formid ;	
	curl ='divstoupdate=HeaderInfo&object=customerheader&action=ContactList&idcustomer='+idcustomer+'&idheader='+idheader+'&parentform='+parentform +'&parentobject='+parentobject+'&parentidentifier='+identifier;
	console.log (curl);
	dialogCRM(curl);

				
}				
function f_Onchange_activitytypeid ( formid, nameobj )  {
	obcform ='#'+formid;
	activityid = $(obcform+' select[name='+nameobj + ']').val();
	
	// charge objets status accord to selecttion type activity
	cobjact  = obcform +' select[name=activitystatus]' ;
	objstatus = $(cobjact) ;
	$(cobjact+' option').remove();
	objstatus.append ( 	   $("<option></option>").text('...').val('') );	
	
	if (activityid=='') return ;
	aStatus = aStatusReasons[0];
	
	basestatusid ='';
	$.each(aCrm_activitytype[0], function(arrayID,arrayCol) {
		if ( arrayCol.idcrmactivitytype == activityid ) {
			basestatusid = arrayCol.basetypeid ;
		}
	});

	
	$.each(aStatus, function(arrayID,arrayCol) {
		if (arrayID==basestatusid) {
			//alert (arrayID + ' ' + arrayCol);
			$.each(arrayCol, function	(id,data) {
				objstatus.append ( 	   $("<option></option>").text(data.description).val(id) );	
			});
		}
	});
}

function form_submit ( formid, data) {
	dialogoid = formid.replace("form", "dialog");
	parentformid =  $( '#'+formid + ' input[name=parentform]').val() ;
	parentobject =  $( '#'+formid + ' input[name=parentobject]').val() ;
	parentidentifier =  $( '#'+formid + ' input[name=parentidentifier]').val() ;
	object =  $( '#'+formid + ' input[name=object]').val() ;
	action =  $( '#'+formid + ' input[name=action]').val() ;
	parentaction =  $( '#'+formid + ' input[name=parentaction]').val() ;
	identifier = $( '#'+formid + ' input[name=identifier]').val() ;
	divstoupdate  =  $( '#'+formid + ' input[name=divstoupdate]').val() ;
	

	$( '#'+dialogoid ).dialog('close');

	console.log ( 'formsubmit! formid:' +formid );
	console.log (' ,divstupdate:'+divstoupdate) ;
	
	var myelements = divstoupdate.split("|");
	console.log (' length:'+myelements.length );
	for (var n=0; n < myelements.length ; n++ ) {
		divsection = myelements[n];
		console.log ( n + ' divsection:' + divsection );
		curl = "object="+ parentobject +"&action="+divsection;
		if ( parentobject=="customerheader"	) {
			if (action =="add" && object=="customerheader" ) {
				idheader = data.id;
				$( '#'+parentformid + ' input[name=idheader]').val( idheader) ;
			} else {
				idheader = 	$( '#'+parentformid + ' input[name=idheader]').val( ) ;
			}
			curl = curl + '&idheader=' + idheader;
		}
		if (parentidentifier!="") curl = curl + '&identifier=' + parentidentifier;	

		console.log ('after save try update , element:'+myelements[n]+' , parentformid:'+parentformid+ '  ,curl:' +curl);
		updateSector(divsection,parentformid,curl);
		console.log ( ' n : ' + n  );
	}

	
	//ahref_update1=  parentformid.replace("form", "update1");
	//console.log ( ahref_update1) ;
	//$('#'+ahref_update1 ).click() ;
	
}

function f_pickrecallok ( formid) {
	
	parentform = $('#'+formid + ' input[name=parentform]').val();
	dialogoid = formid.replace("form", "dialog");

	
	
	taskdate  =  $('#periodday').val()+'/'+$('#periodmonthyear').val();
	recalltimes =  $('#periodtimes').val();
	$('#'+parentform+' input[name=taskdate]').val ( taskdate);
	$('#'+parentform+' input[name=recalltimes]').val ( recalltimes);
	
	$( '#'+dialogoid ).dialog('close');
	
}

function uniqId() {
  return Math.round(new Date().getTime() + (Math.random() * 100));
}


function f_headerupdatefield ( field, value, formid ) {
	dialogoid = formid.replace("form", "dialog");
	$( '#'+formid + ' input[name='+field+']').val(value) ;
	$('#'+formid).submit();

	
}
	
function updateDossier( formid ) {
	identifier = formid.replace("form_","")
	idheader = 	$( '#'+formid + ' input[name=idheader]').val( ) ;
	curl = "object=customerheader&action=HeaderInfo&identifier="+identifier+"&idheader="+idheader ;
	console.log ('update dossier , ' +'#'+formid + ' input[name=idheader], idheader : '+idheader +' ,url:'+curl);
	updateSector("HeaderInfo",formid,curl);
}
function editDossier ( formid) {
	identifier = formid.replace("form_","")
	idheader = 	$( '#'+formid + ' input[name=idheader]').val( ) ;

	if (idheader=='')	{
		alert ('Il faut creer un dossier pour ajouter une activité');
		return false;
	}

	idcustomer =  $('#'+formid+' input[name=idcustomer]').val();	
	idheader =  $('#'+formid+' input[name=idheader] ').val();	
	parentobject = $('#'+formid+' input[name=object]').val();	
	parentform = formid ;	
	curl ='divstoupdate=HeaderInfo&object=customerheader&action=edit&idcustomer='+idcustomer+'&idheader='+idheader+'&parentform='+parentform +'&parentobject='+parentobject+'&parentidentifier='+identifier;
	console.log (curl);
	dialogCRM(curl);
	
	
	
	
}

function updateDossierActivities( formid ) {
	identifier = formid.replace("form_","")
	idheader = 	$( '#'+formid + ' input[name=idheader]').val( ) ;
	curl = "object=customerheader&action=HeaderActivities&identifier="+identifier+"&idheader="+idheader ;
	console.log ('update dossier , ' +'#'+formid + ' input[name=idheader], idheader : '+idheader +' ,url:'+curl);
	updateSector("HeaderActivities",formid,curl);
}

function updateSector( idelement , formid , url) {
	var myelements = idelement.split("|");
	for (var n=0; n < myelements.length ; n++ ) {
		idelement = formid.replace("form", myelements[n]);
		console.log (idelement);
		crmurlprocess  = '/modules/crm/crmgetdata.php?'+url+'&typereponse=php';
		console.log ('update div:'+idelement + ' ,url:'+crmurlprocess);
		$('.crmloading').show();
		
		$.ajax({
		  url: crmurlprocess,
		  dataType: 'html',
		  success: function(html) {
			$('#'+idelement).html ( html);
			$('.crmloading').hide();
			$('#'+idelement).fadeIn( "slow", function() {
				// Animation complete
			});
		  }
		});
	}
	
}
function f_select_month ( labelperiod, period, maxdays) {
	$('#divperiodtime .periodselect').css("background-color","#ededed");
	$('#periodmonth').html(labelperiod);
	period= period.toString();
	console.log(period);
	console.log(period.substr(4,2 ));
	$('#periodmonthyear').val( period.substr(4,2 ) + '/'+period.substr(0,4 )  ) ;
	
	$('#period'+period).css("background-color","yellow");
	f_update_date ();
}
function f_select_day ( dayselect ) {
	$('#divperiodtime .dayselect').css("background-color","#f5f5f5");
	$('#periodday'+dayselect ).css("background-color","yellow");
	$('#periodday').val(dayselect);
	f_update_date ();
}

function f_update_date () {
	perioddate  = $('#periodday').val()+'/'+$('#periodmonthyear').val();
	$('#divselecteddate').html(perioddate) ;
}

function f_select_hours ( hour, type ) {
	chour  = hour.replace(":","_");
	//$('#divperiodtime .dayselect').css("background-color","#f5f5f5");
	cperiodtime ='#periodtime'+type+'_'+chour;
	
	color = $(cperiodtime).css('color');
	if (color.indexOf('220')==-1 ) { // it's not red
		$(cperiodtime  ).css("color","#DC0000");
	} else {
		$(cperiodtime  ).css("color","#000000");
	}
	
	aRangeFrom = new Array();
	aRangeTo = new Array();
	for (var n=1; n<=2 ; n++ ) {
		if (n==1) {
			classhour ='.periodtimefrom';
		} else {
			classhour ='.periodtimeto';
		}
		$('#divperiodtime '+classhour).filter(function() {
			color = $(this).css('color');
			if (color.indexOf('220')>-1 ) { // it's  red
				cHour = $(this).html();
				if (n==1) {
					aRangeFrom.push ( cHour);
				} else {	
					aRangeTo.push ( cHour);
				}	
			}
			return true;
		});
	 }

	lError = false; 
	aRangeFrom.sort(function(a, b){ if(a < b) return -1; if(a > b) return 1;return 0;})
	aRangeTo.sort(function(a, b){ if(a < b) return -1;	if(a > b) return 1;	return 0;})

	 if (aRangeTo.length > aRangeFrom.length) {
		 // Error 
		 lError  = true;
	 }
	 cHoursRange ='';
	 for (var n=0; n < aRangeFrom.length ; n++ ) {
		hourFrom =  aRangeFrom[n].replace(':','');
		if (n +1 > aRangeTo.length ) {
			cHoursRange = cHoursRange + aRangeFrom[n]  +'\n';
		} else {
			hourTo   =  aRangeTo[n].replace(':','');
			if (hourTo < hourFrom) {
				lError  = true;
			} else {
				cHoursRange = cHoursRange + aRangeFrom[n] + ' - '+ aRangeTo[n] +'\n';
			}
		}
		 // Error 
	 }
	 if (lError   ) {
		 cHoursRange ="<span style='color:red'>Erreur</span>";
	 }
	 $('#periodtimes').val (cHoursRange);
	 cHoursRange =replaceAll (cHoursRange,'\n','<br/>');
	 $('#divselectedtimes').html(cHoursRange) ;
	
}


function f_reset_hours ( ) {
	$('#divperiodtime .hoursselect').css("color","#000000");
}

function escapeRegExp(str) {
    return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}

function replaceAll(str, find, replace) {
  return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}

function editClient (formid) {
	idcustomer  = $('#'+formid+' input[name=idcustomer]').val();	
	console.log (idcustomer);
	
	if (idcustomer=='') {
		curl = "/sales_force/customer.php" ;
	} else {
		curl = "/sales_force/customer.php?idcustomer="+ idcustomer;
	}
	var win=window.open(curl, '_blank');
	win.focus();

	
}
function f_addactivity ( formid, basetype , options )  {
	var defaults = {
        urladd : ''
    }
	
    var options = $.extend({}, defaults, options || {});
	idheader = $('#'+formid+' input[name=idheader]').val();	
	identifier = formid.replace("form_","")
	console.log (idheader);
	if (idheader=='')	{
		alert ('Il faut creer un dossier pour ajouter une activité');
		return false;
	}
	idcustomer =  $('#'+formid+' input[name=idcustomer]').val();	
	idheader =  $('#'+formid+' input[name=idheader] ').val();	
	parentobject = $('#'+formid+' input[name=object]').val();	
	parentform = formid ;	
	curl ='divstoupdate=HeaderActivities&object=crm_activity&action=add&idcustomer='+idcustomer+'&idheader='+idheader+'&parentform='+parentform +'&parentobject='+parentobject+'&basetype='+basetype+'&parentidentifier='+identifier  +options.urladd;
	console.log (curl);
	dialogCRM(curl);
}

function f_changeselecheader (obj) {
	var idheader = obj.value;  
	formid = $(obj).closest("form").get(0).id;
	parentidentifier = formid.replace("form_", ""); 
	console.log ( 'formid:'+ formid);
	
	var myelements = new Array ("HeaderInfo","HeaderActivities") ;
	
	for (var n=0; n < myelements.length ; n++ ) {
		
		curl = "object=customerheader";
		curl = curl +"&action=" + myelements[n];
		$( '#'+formid + ' input[name=idheader]').val( idheader) ;
		curl = curl + '&identifier=' + parentidentifier;	
		curl = curl + '&idheader=' + idheader;
		
		console.log ('try curl:' +curl);
		updateSector(myelements[n],formid,curl);
	}	

}
function f_phonecall ( formid, contactid , phone ) {
	console.log ('form:'+formid+' , contact:' + contactid + ' ,phone:' + phone );
	urladd = '&activitycontactid='+contactid+'&phonecall=' + phone;
	f_addactivity ( formid, 1,  {  urladd : urladd } ) ; 

	//window.location.href = "tel:mail@example.org";
	//webCall('http://bao-david/templates/back_office/phonecall?phonenumber=0635362279')
}

function f_phonecallservice ( url ) {
	if (url.substr(0,4)=='tel:') {	
		window.location.href = url;	
	} else {
		console.log (url);
		//webCall(url);
	}
}


function f_mailto  ( formid, contactid  ) {
	window.location.href = "mailto:mail@example.org";

}

function pretty_time_string(num) {
    return ( num < 10 ? "0" : "" ) + num;
  }

function get_elapsed_time_string(total_seconds) {
  
  var hours = Math.floor(total_seconds / 3600);
  total_seconds = total_seconds % 3600;

  var minutes = Math.floor(total_seconds / 60);
  total_seconds = total_seconds % 60;

  var seconds = Math.floor(total_seconds);

  // Pad the minutes and seconds with leading zeros, if required
  hours = pretty_time_string(hours);
  minutes = pretty_time_string(minutes);
  seconds = pretty_time_string(seconds);

  // Compose the string for display
  var currentTimeString = hours + ":" + minutes + ":" + seconds;

  return currentTimeString;
}

function f_chronostop  () {
	clearInterval (crmInterval);
	return false;
}

