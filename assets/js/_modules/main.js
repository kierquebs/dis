var navCheck = true;
var actvID = 0;
var adsoaID = 0;
var queueID = 0;
var queuesoaID = 0;
var commentID = 0;
var actcoID = 0;
var actcorID = 0;
var commentCancelID = 0;
var commentReplaceID = 0;

(function () {
	$('[data-toggle="tooltip"]').tooltip(); 
	filterStatus();
	suggestionBox();
	today_pickup();
	loader();
	navLoad();
})();
window.onload = function(){
    if (window.jQuery){
        console.log('jQuery is loaded');
    }else{
        alert('jQuery is not loaded');
    }
}
function failureCallback(){
	window.location.href = BASEURL+'/login';
};
function callUpdate(){
	navCheck = true;
	navLoad();
}
function navLoad(){
	if(navCheck == false) return false;

	$.getJSON("home/check_notif", 
	{
		actvID : actvID, 
		adsoaID : adsoaID, 
		queueID : queueID,
		 queuesoaID: queuesoaID, 
		 commentID: commentID, 
		 actcoID: actcoID, 
		 actcorID: actcorID, 
		 commentCancelID: commentCancelID, 
		 commentReplaceID: commentReplaceID
	}, function(data) {
		if(data.act != 0) $('.actnum span').text('('+data.act+')');
		else $('.actnum span').text('');
		
		if(data.queue != 0) $('.quenum span').text('('+data.queue+')');
		else $('.quenum span').text('');
		
		if(data.adsoa != 0) $('span.adsoa-num').text('('+data.adsoa+')');
		else $('span.adsoa-num').text('');
		
		if(data.co_queue != 0) $('span.co-num').text('('+data.co_queue+')');
		else $('span.co-num').text('');

		if(data.cor_queue != 0) $('span.cor-num').text('('+data.cor_queue+')');
		else $('span.cor-num').text('');

		if(actvID != data.actvID && data.actvID != 0){
			actvID = data.actvID;
			notification('ACTIVITY BOARD','received new order transaction!', 'order');
		}
		if(adsoaID != data.adsoaID && data.adsoaID != 0){
			adsoaID = data.adsoaID;
			notification('ADVANCE SOA','received advance soa request!', 'adsoa');
		}
		if(queueID != data.queueID && data.queueID != 0){
			queueID = data.queueID;
			notification('QUEUE BOARD','new client on queue board!', 'que');
		}
		if(queuesoaID != data.queuesoaID && data.queuesoaID != 0){
			queuesoaID = data.queuesoaID;
			notification('QUEUE FOR ADVANCE SOA','new client on queue board!', '');
		}
		if(commentID != data.commentID && data.commentID != 0){
			commentID = data.commentID;
			notification('ORDER COMMENT','new comment added!', 'order/comment/'+commentID);
		}
		if(commentCancelID != data.commentCancelID && data.commentCancelID != 0){
			commentCancelID = data.commentCancelID;
			notification('ORDER CANCELLATION COMMENT','new comment added!', 'order_cancel/comment/'+commentCancelID, 'info');
		}
		if(commentReplaceID != data.commentReplaceID && data.commentReplaceID != 0){
			commentReplaceID = data.commentReplaceID;
			notification('REPLACEMENT/REVALIDATION COMMENT','new comment added!', 'replacement/comment/'+commentReplaceID, 'info');
		}
		if(actcoID != data.actcoID && data.actcoID != 0){
			actcoID = data.actcoID;
			notification('ORDER CANCELLATION','received new order cancellation!', 'order_cancel');
		}
		if(actcorID != data.actcorID && data.actcorID != 0){
			actcorID = data.actcorID;
			notification('ORDER REPLACEMENT/REVALIDATION','received new replacement/revalidation!', 'replacement');
		}
	}); 
}
function notification($title, $msg, $url = 'que', $type = 'warning'){
	if($msg == '' || $title == '') return false;
	
	var notify = $.notify({
		icon: 'glyphicon glyphicon-'+$type+'-sign',
		title: $title,
		message: $msg,
		url: BASEURL+$url,
		target: '_self'
	},{
		type: $type,
		allow_dismiss: true,
		newest_on_top: true,
		placement: {
			from: "top",
			align: "right"
		},
		offset: {
			x: 20,
			y: 20
		},
		spacing: 10,
		z_index: 1031,
		delay: 20000
	});	
	$.notifyPush($msg, {title: $title});
	return false;
}
function loader(){
	$(".form-loader").submit(function(e){
		$.LoadingOverlay("show");
	});
}
function datePick(date_input = '', $startDate = false){
		if(date_input == '')  date_input = $('.datetimepicker'); //our date input has the name "date"
	var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";	
	var startD = '';
		if($startDate == true) startD = new Date();
	date_input.datepicker({
		format: 'yyyy-mm-dd',
		container: container,
		todayHighlight: true,
		autoclose: true,
		startDate: startD
	})
}
function suggestionBox(){
	$('.auto-company').autocomplete({
		serviceUrl: 'home/companies',
		onSelect: function (suggestion) {
			$('.auto-company').attr('data-id', suggestion.data);
		}
	});
	$('.auto-company, .datetimepicker').attr({'autocomplete':'off'});
}
function today_pickup(){
	$('#today-pickup').click(function(e){

        e.stopImmediatePropagation();
		$multCount = $('.multi-div').length;
		$utype = $('.auto-company').attr('data-id');
		if(!$utype) $utype = $('.auto-company').val();
		$.getJSON("home/pickup_order?cname="+$utype, function(data) {
			if(data.success == true){
				$.each(data.result, function(key, value) {
					if($('.order-input').val() != value.order_id){
						$('.multi-div').last().find('.btn-add').click();
						$('.multi-div').last().prev().find('input').val(value.order_id);
					}
				});	
			}else{
				$('.today-error').html('NO RESULT FOUND!');
				$('.form-wrapper .multi-div').find('.order-input').val('');
			}
		});
	});
}

function setModal(id, title, btn, body){
	if(!title) title = id;
	if(!btn) btn = 'OK';
	if(!body) body = '';
	$('.modal').attr({'id':id, 'aria-labelledby': '#'+id});
	$('.modal .modal-title').attr('id', id).html(title);
	$('.modal .modal-btn').html(btn);
	$('.modal .modal-body').attr('id', id).html(body);
}
function filterStatus(){
	$('.dropType').change(function(e){
        e.stopImmediatePropagation();
		$me = $(e.currentTarget);	
		dropStatus($('.dropStat'), $me.val());
	})
}
function dropStatus($element, $utype, $form_other = false, $selected = 0, $indexURL = 1){
	$element.empty();
	$.getJSON("home/get_status?type="+$utype+"&mod="+urlIndex($indexURL), function(data) {
		$element.append("<option disabled "+($selected == 0 ? 'selected' : '')+">--- GET STATUS ---</option>");
		$.each(data.stat_mo, function(key, value) {
			if($selected != 0 && value.id == $selected) $element.append("<option value='" + value.id + "' selected>" + value.name + "</option>");
			else $element.append("<option value='" + value.id + "'>" + value.name + "</option>");
		});
		if($form_other == true && $utype != 7){
			//console.log($form_other);
			$element.append("<option value='999'>OTHER</option>");
			add_dropOther($element);
		}
	}); 
}
function dropCat($element, $utype, $form_other = false, $selected = 0, $indexURL = 1, $jsonURL = 'get_cat'){
	$element.empty();
	$.getJSON("home/"+$jsonURL+"?type="+$utype+"&mod="+urlIndex($indexURL), function(data) {
		$element.append("<option disabled "+($selected == 0 ? 'selected' : '')+">--- SELECT OPTION ---</option>");
		$.each(data.stat_cat, function(key, value) {
			if($selected != 0 && value.id == $selected) $element.append("<option value='" + value.id + "' selected>" + value.name + "</option>");
			else $element.append("<option value='" + value.id + "'>" + value.name + "</option>");
		});
		if($form_other == true && $utype != 7){
			//console.log($form_other);
			$element.append("<option value='999'>OTHER</option>");
			add_dropOther($element);
		}
		console.log($selected);
	}); 
}
function urlIndex($segment){
	$url = window.location.pathname;
	if($segment == 2) $return = $url.split('/').reverse()[1];
	else $return = $url.split('/').reverse()[0];
	
	if($return == 'filter') return $url.split('/').reverse()[1];
	else return $return;
}
function add_dropOther($element){
	$element.change(function(e){
		$me = $(e.currentTarget);	
		if($me.val() == 999) $('<input type="text" name="other" class="form-control form-other" placeholder="new status"/>').insertAfter($element);
		else $('.form-other').remove();
	})
}

/*
* DataTable SETUP w/ pagination
*/
function setDatatbl($ID = '#queue-tbl'){
	tblID = $ID.replace("#", "");
	page = 1;
	myTable = $($ID).DataTable({        
		"searching": false,
		"ordering": false,
		"bLengthChange" : false, //thought this line could hide the LengthMenu
		"bInfo":false,
		"deferRender": true,
		"paging": false,
		"lengthChange": false,
		"autoWidth": false,
		"sDom": 'lfrtip',
		"oLanguage": { 
			"sEmptyTable": "No data available in table" 
		} 
	});	
	PopulateItemsTable(); 
}
$(document).ajaxComplete(function() {
	console.log('Ajax call completed');
	//tblTimeOut();
});
function tblTimeOut(){
	//setInterval(function(){
		$.getJSON("login/db_update", function(data) {
			if(data.log == false) failureCallback() ;
			else{
				if(data.stat == true){
					navCheck = true;
					PopulateItemsTable(); 
				}else navCheck = false;		
			}		
			navLoad();
		}); 
	//}, 10000); 
}
function activateLinks(){
	var x = $('.paginate_button');
	for(var i=0;i<x.length;i++){
		$(x[i]).click(function(e){
			page = $(this).attr('data-dt-idx');
			PopulateItemsTable();
			//console.log(page);
		});
	}	
	$('[data-toggle="tooltip"]').tooltip(); 
	
	$('#search-form .clear-all').click(function(e){
        e.stopImmediatePropagation();
		search = '&all'; 
		$('#search-form input, #search-form select').val('');
		PopulateItemsTable();
	});
	$('.export').click(function(e){
		e.stopPropagation();	
		genReport();
	});
	$('.select-order select[name="order"]').change(function(e){
        e.stopImmediatePropagation();
			search = search.replace("&order=asc", "").replace("&order=desc", "");	
		search = search+'&order='+$(e.currentTarget).val(); 
		PopulateItemsTable();
	});
	$('select[name="tat_stat"].selectpicker').change(function(e){
        e.stopImmediatePropagation();
		$tatVal = $(e.currentTarget).val();
			search = search.replace("&tat_time=1", "").replace("&tat_time=2", "").replace("&tat_time=3", "");	
		search = ($tatVal == '' ? search : search+'&tat_time='+$tatVal); 
		PopulateItemsTable();
	});
}

function pagination($totalRow, $per_page = 5, $export = true, $orderName = 'TRANSACTION TIME'){
	if($export == true){
		$('<a class="export" aria-label="EXPORT"><span class="glyphicon glyphicon-export" aria-hidden="true"></span>EXPORT REPORT</a>').insertBefore('#'+tblID);
			
		if($('.select-order').length == 0){
			$('<span class="select-order">'+$orderName+' ORDER BY <select name="order">'
			+'<option value="asc">ASC</option><option value="desc">DESC</option>'
			+'</select></span>').insertBefore('#'+tblID);
		}
	}
		
		$tblID = tblID;
		$page = parseInt(page);
		$paginate = parseInt($totalRow);
		$total_pages = parseInt($paginate);
		$adjacents = 2;
		
		if ($page == 0) $page = 1;
		$prev = $page - 1;
		$next = $page + 1;				
		$lastpage = Math.ceil($total_pages/$per_page);
		$lpm1 = $lastpage - 1;
		
		$pageID = $tblID+'_paginate';
		$prevID = $tblID+'_previous';
		$nextID = $tblID+'_next';
		
		//console.log('hey');
		$('<div class="dataTables_paginate" id="'+$pageID+'"></div>').insertAfter('#'+$tblID);
		
		$("#"+$pageID).append('<div class="dataTables_info">Total Rows of '+$totalRow+'</div>');

		if($lastpage > 1){	
			//PREV BUTTON
			if ($page > 1) { 			
				$("#"+$pageID).append('<a class="paginate_button previous disabled" aria-controls="'+$tblID+'" data-dt-idx="'+$prev+'" tabindex="0" id="'+$prevID+'">Previous</a>');
			}
			
			// list of pages
			if ($lastpage < 6 + ($adjacents * 2)){	
				for ($counter = 1; $counter <= $lastpage; $counter++){
					if ($counter == $page){
						$("#"+$pageID).append('<a class="paginate_button disabled current" aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
					}else{
						$("#"+$pageID).append('<a class="paginate_button " aria-controls="queue-tbl" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');	
					}						
				}
			}else if($lastpage > 5 + ($adjacents * 2)){
				//hide active button
				if($page < 1 + ($adjacents * 2)){
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++){
						if ($counter == $page){
							$("#"+$pageID).append('<a class="paginate_button disabled current" aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
						}else{
							$("#"+$pageID).append('<a class="paginate_button " aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
						}							
					}					
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="'+$lpm1+'" tabindex="0">'+$lpm1+'</a>');
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="'+$lastpage+'" tabindex="0">'+$lastpage+'</a>');	
				}else if($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)){ //hide front or back	
					$("#"+$pageID).append('<a class="paginate_button page_dark " aria-controls="'+$tblID+'" data-dt-idx="1" tabindex="0">1</a>');
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="2" tabindex="0">2</a>');
					
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++){
						if ($counter == $page){
							$("#"+$pageID).append('<a class="paginate_button disabled current" aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
						}else{
							$("#"+$pageID).append('<a class="paginate_button" aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
						}							
					}
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="'+$lpm1+'" tabindex="0">'+$lpm1+'</a>');
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="'+$lastpage+'" tabindex="0">'+$lastpage+'</a>');	
				}else{// hide prev page		
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="1" tabindex="0">1</a>');
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="2" tabindex="0">2</a>');

					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++){
						if ($counter == $page){
							$("#"+$pageID).append('<a class="paginate_button disabled current" aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
						}else{
							$("#"+$pageID).append('<a class="paginate_button " aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');	
						}							
					}
				}
			}
			// next button	
			if ($page < $counter - 1){
				$("#"+$pageID).append('<a class="paginate_button next" aria-controls="'+$tblID+'" data-dt-idx="'+$next+'" tabindex="0" id="'+$nextID+'">Next</a>');
			}	
		} 
	activateLinks(); // click event
}

/*For search tab function*/
function actionFilter($searchTxt){
	if($searchTxt != ''){
		search = $searchTxt; page = 1;
		PopulateItemsTable();
	}
}
