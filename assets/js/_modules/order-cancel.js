(function () {
	datePick();
	setDatatbl();
	$('#search-form button[type=submit]').click(function(){
		$searchTxt = '';
		$search = $('#search-form input[name="search"]').val();
			if($search != '') $searchTxt = '&search='+$search;
		$cat = $('#search-form select[name="cat"]').val();
			if($cat != ''&& $cat != undefined) $searchTxt += '&cat='+$cat;
		$stat = $('#search-form select[name="stat"]').val();
			if($stat != '' && $stat != undefined) $searchTxt += '&stat='+$stat;

		actionFilter($searchTxt);
	});
})();

/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
	$.ajax({
	type: "POST",
	url: "order_cancel/get_act?page="+page+search,
	dataType: "json",
	success: function (data) {
		myTable.rows().remove().draw();		
		$('#'+tblID+'_paginate').remove();		
		if(data.totalData != 0){
			$jsonReturn = data.result;
			$i = 1;
			if(data.offset > 0) $i = data.offset + 1;
			$.each(data.result, function(key, value) {
				$orderID = value.order_id; 	
				if($orderID == 0) $orderID = '';
				$TRANSID = value.co_id;
				$date2 = $button2 = $button = '';
				var result = [];
					result.push($i++); //numbering
					result.push(''); //order id
					result.push(''); //order id
					result.push(''); //company / order info
					result.push(''); //cs 
					result.push(''); //prod 
					result.push(''); //fin 
					result.push(''); //logistics 
					result.push(''); //reimbursement
								
				$cname = $('<span></span>')
							.addClass('cname')
							.attr({'data-toggle':'tooltip', 'title':value.company_name})
							.text(value.company_name);
				$orderINFO = value.orderinfo;
				$orderinfo = $('<span></span>')				
							.attr({'data-prod':$orderINFO['coprod'], 'data-reason':$orderINFO['coreason'], 'data-charge':$orderINFO['cocharge'], 'data-amount':$orderINFO['amount'], 'data-date':$orderINFO['date'], 'data-qnty':$orderINFO['coqnty'], 'data-amntchgr':$orderINFO['cochargeamt'], 'data-rsnum':$orderINFO['cors'], 'data-type':$orderINFO['cotype'], 'data-em':$orderINFO['coem']})				
							.addClass('orderinfo')
							.text('VIEW INFO');
				$date1 = $('<span></span>')
							.addClass('date')
							.text(value.date_received);	
				$date2 = $('<span></span>')
						.addClass('date')
						.text(value.date_release);				
				$tat =	$('<i></i>')
							.addClass('fa '+value.faceTAT+' fa-2x')
							.attr({'aria-hidden':'true', 'data-toggle':'tooltip', 'title':value.timeTAT+' mins'});
							
				$orderTXT = $('<span></span>')
						.text($orderID);
						
				$commentBTN = $('<a></a>')
						.addClass('fa fa-commenting '+(value.comments != 0 ? 'red' : '')) //COMMENTS 
						.attr({'aria-hidden':'true', 'data-toggle':'tooltip', 'title':(value.comments == 0 ? 0 : 'COMMENTS('+value.comments+')'), 'href':'order_cancel/comment/'+$TRANSID});
						
				/*
				* utype status and btn
				*/
				$reimBtn = $prodBtn = $finBtn = $csBtn = $logBtn = ' '; 
				$reimTIME = $prodTIME = $finTIME = $csTIME = $logTIME = ' '; 
				for (i = 2; i <= 7; i++) { 
					switch(i) {
						case 2:
							$dataNAME = 'prod';
							$valSTATNAME = value.prod_stat_name;
							$valSTAT = value.prod_stat;
							$valTIME = value.prod_time;
							if(value.reim_stat == 0)$valBTN = data.prod_btn;
							else $valBTN = false;
							break;
						case 3:
							$dataNAME = 'fin';
							$valSTATNAME = value.fin_stat_name;
							$valSTAT = value.fin_stat;
							$valTIME = value.fin_time;
							if(value.reim_stat == 0)$valBTN = data.fin_btn;
							else $valBTN = false;
							break;
						case 5:
							$dataNAME = 'logist';
							$valSTATNAME = value.logs_stat_name;
							$valSTAT = value.logs_stat;
							$valTIME = value.logs_time;
							if(value.reim_stat == 0) $valBTN = data.logs_btn;
							else $valBTN = false;
							break;							
						case 6:
							$dataNAME = 'cs';
							$valSTATNAME = value.cs_stat_name;
							$valSTAT = value.cs_stat;
							$valTIME = value.cs_time;
							if(value.reim_stat == 0)$valBTN = data.cs_btn;
							else $valBTN = false;
							break;
						case 7:
							$dataNAME = 'reim';
							$valSTATNAME = value.reim_stat_name;
							$valSTAT = value.reim_stat;
							$valTIME = value.reim_time;
							if(value.cs_stat != 0 &&  value.prod_stat != 0 && value.fin_stat != 0 && value.logs_stat != 0 && value.reim_stat == 0) $valBTN = data.reim_btn;
							else $valBTN = false;
							break;
					}
					$orderTxt = 'SELECT';			
					$orderBTN = $('<span></span>')
						.addClass('td-btn')
						.text($valSTATNAME);			
					$orderTIME = $('<span></span>')
						.addClass('date')
						.text($valTIME);
					
					if($valBTN == true){
							if($valSTAT != 0) $orderTxt = $valSTATNAME;
							var orderBtn = $('<button></button>').addClass('btn btn-info').attr({'data-name': $dataNAME, 'data-id': i, 'data-stat':$valSTAT}).text($orderTxt);
						$($orderBTN).text('').append(orderBtn);
					}				
					
					switch(i) {
						case 2:
							$prodBtn = $orderBTN;
							$prodTIME = $orderTIME;
							break;
						case 3:
							$finBtn = $orderBTN;
							$finTIME = $orderTIME;
							break;
						case 5:
							$logBtn = $orderBTN;
							$logTIME = $orderTIME;
							break;
						case 6:
							$csBtn = $orderBTN;
							$csTIME = $orderTIME;
							break;
						case 7:
							$reimBtn = $orderBTN;
							$reimTIME = $orderTIME;
							break;
					}
				}
											
				var newRow = myTable.row.add(result).draw().node(); 
					$(newRow).addClass('queue-tr'+($orderINFO['cotype_id'] != 0 ? ' yellow-tag' : ''))
							.attr('data-id', $TRANSID) 
								.find('td:nth-child(2)').addClass('order').attr('data-order', $orderID).append($orderTXT).append($commentBTN).end()
								.find('td:nth-child(3)').addClass('order').attr('data-rsnum', $orderINFO['cors']).text($orderINFO['cors']).end()
								.find('td:nth-child(4)').addClass('queue-tbl-span co-company').append($cname).append($orderinfo).end()
								.find('td:nth-child(5)').addClass('queue-tbl-span co-cs').append($csBtn).append($csTIME).end()
								.find('td:nth-child(6)').addClass('queue-tbl-span co-prod').append($prodBtn).append($prodTIME).end()
								.find('td:nth-child(7)').addClass('queue-tbl-span co-fin').append($finBtn).append($finTIME).end()
								.find('td:nth-child(8)').addClass('queue-tbl-span co-logist').append($logBtn).append($logTIME).end()
								.find('td:nth-child(9)').addClass('queue-tbl-span co-reim').append($reimBtn).append($reimTIME);
			});				
			pagination(data.totalData, data.per_page, false);
			orderModal();	
			viewInfo();
		}
	}
	});
}

var modalID2 = 'orderInfoModal';
var modalID = 'orderModal';
function viewInfo(){	
	$('span.orderinfo').attr({'data-toggle':'modal' , 'data-target':'#'+modalID2});
	$('.orderinfo').click(function(e){	
		setModal(modalID2, 'ORDER CANCELLATION: DETAILS', 'OK');
		
		 $me = $(e.currentTarget); 
		$queueTR = $me.closest('.queue-tr');
		
		$formDiv = $('<div class="row-div cor-view"></div>');
		$formDiv.append('<div class="div-form">COMPANY NAME: <span class="break">'+$queueTR.find('.co-company span.cname').text()+'</span></div>'		
			+'<div class="div-form">DATE RECEIVED | RELEASED : <span class="break">'+$me.attr('data-date')+'</span></div>' );	
		$formDiv.append('<div class="row-half">'
			+'<h5>ORDER INFO</h5>'
			+'<div class="div-form">CANCELLATION TYPE: <span class="orderid">'+$me.attr('data-type')+'</span></div>'
			+'<div class="div-form">PRODUCT NAME: <span class="orderid">'+$me.attr('data-prod')+'</span></div>'
			+'<div class="div-form">ORDER ID: <span class="orderid">'+$queueTR.find('.order').attr('data-order')+'</span></div>'
			+'<div class="div-form">RS NUMBER: <span class="orderid">'+$me.attr('data-rsnum')+'</span></div>'
			+'</div>');
		$formDiv.append('<div class="row-half">'
			+'<h5>CANCELLATION INFO</h5>'
			+'<div class="div-form">REASON FOR CANCELLATION: <span class="break">'+$me.attr('data-reason')+'</span></div>'
			+'<div class="div-form">CHARGE TO: <span class="orderid">'+$me.attr('data-charge')+'</span></div>'
			+($me.attr('data-em') != '' ? '<div class="div-form">EMPLOYEE NAME: <span class="break">'+$me.attr('data-em')+'</span></div>' : '') 
			+'</div>');
		$formDiv.append('<div class="row-half">'
			+'<h5>AMOUNT DETAILS</h5>'
			+'<div class="div-form">DENO-QUANTITY TOTAL AMOUNT: <span class="break right">'+$me.attr('data-amount')+'</span></div>'
			+'<div class="div-form">CANCELLATION CHARGE AMOUNT: <span class="break right">'+$me.attr('data-amntchgr')+'</span></div>'
			+'</div>');
		$formDiv.append('<div class="row-half">'
			+'<h5>DENO-QUANTITY</h5>'
			+'<div class="div-form div-denoqnty">'+$me.attr('data-qnty')+'</div>'
			+'</div>');
		
		$('.modal .modal-title#'+modalID2).html('ORDER INFO');
		$('.modal .modal-body#'+modalID2).html($formDiv);	
		modalClick();		
	});	
	
}
function orderModal(){
	$('.td-btn button').attr({'data-toggle':'modal' , 'data-target':'#'+modalID});
	
	$('.td-btn button').click(function(e){	
		setModal(modalID, 'PROCESS ORDER CANCELLATION', 'OK');	
		$me = $(e.currentTarget); 
		/* CHECK FORM TITLE */
		if($me.attr('data-name') == 'prod') $Title = 'PRODUCTION';
		else if($me.attr('data-name') == 'fin') $Title = 'FINANCE';
		else if($me.attr('data-name') == 'cs') $Title = 'CUSTOMER SERVICE';
		else if($me.attr('data-name') == 'logist') $Title = 'LOGISTICS';
		else $Title = 'REIMBURSEMENT';
	
		$queueTR = $me.closest('.queue-tr');

		$('.modal .modal-title#'+modalID).html($Title);
		$('.modal .modal-body#'+modalID).html(orderForm());	
		$('.modal .orderid').html($queueTR.find('.order').attr('data-order'));	// show order id
		$('.modal .rsnum').html($queueTR.find('span[data-target="#orderInfoModal"]').attr('data-rsnum'));	// show order id
		$('.modal .cname').html($queueTR.find('.co-company span.cname').text());		
		$('.modal #cotransid').val($queueTR.attr('data-id'));
		$('.modal .modal-btn').attr({'data-name':$me.attr('data-name'), 'data-id':$me.attr('data-id')});
		dropStatus($("#form-status"), $me.attr('data-id') , true, $me.attr('data-stat'));
		
		modalClick();
	});
}

function modalClick(){
	$('#orderInfoModal .modal-btn').click(function(e){
		$me = $(e.currentTarget);
		$('.modal .modal-title#'+modalID2, '.modal .modal-body#'+modalID2).html('');
		$('#orderInfoModal .close').click();
	});
	
	$('#orderModal .close').click(function(e){
		$me = $(e.currentTarget);
		$('.modal .modal-title#'+modalID, '.modal .modal-body#'+modalID).html('');
	});
	$('#orderModal .modal-btn').click(function(e){
		$me = $(e.currentTarget);	
		$('.search-div').find('span.error').remove;	
		process(e, modalID);
	});
}

function process(e, modalID){
	$btnDataID = $(e.currentTarget).attr('data-id');
	$dataID = $('.modal #'+modalID).find('.div-form span #cotransid').val();
	$statID = $('.modal #'+modalID).find('.div-form span #form-status').val();
	$statOther = $('.modal #'+modalID).find('.div-form .form-other').val();

	if($dataID == ''){
		$('.modal #'+modalID+' .search-div').append('<span class="error">INVALID REQUEST DATA!</span>');
	}else if($statID == '' || ($statID == '999' && $statOther == '')){	
		$('.modal #'+modalID+' .search-div').append('<span class="error">INVALID STATUS!</span>');
	}else{		
        e.stopImmediatePropagation();
		$.ajax({
			type: 'POST',
			url: 'order_cancel/process',  
			data: { cotransid: $dataID, stat: $statID, other: $statOther, btnid: $btnDataID},	
			error: function() {
				failureCallback();
			},
			success: function(data) {
					$('.modal #'+modalID+' .search-div').find('.error').remove();
				if(data.update_process == true){
					$dataName = $('.modal .modal-btn').attr('data-name');
					$row_update = $('.queue-tr[data-id='+$dataID+']').find('td.'+$dataName ).find('.td-btn');

					$statName = $('.modal #'+modalID).find('.div-form span #form-status option[value='+$statID+']').text();
					$row_update.find('button[data-name='+$dataName+']').text($statName);
					
					$('.modal .modal-title#'+modalID, '.modal .modal-body#'+modalID).html('');
					$('.modal .close').click();	
					PopulateItemsTable();	
				}else{
					$('.modal #'+modalID+' .search-div').append('<span class="error">'+data.msg+'</span>');
				}					
			},
			dataType: 'json'
		});
	}
}

function orderForm(){
	$formDiv = $('<div class="row-div search-div"></div>');
	$formDiv.append('<div class="div-form">ORDER ID: <span class="orderid"></span></div>');
	$formDiv.append('<div class="div-form">RS NUMBER: <span class="rsnum"></span></div>');
	$formDiv.append('<div class="div-form">COMPANY NAME: <span class="cname"></span></div>');
	$formDiv.append('<div class="div-form">STATUS: <span><input type="hidden" name="cotransid" id="cotransid"/><select class="form-control" name="stat" id="form-status"></select></span></div>'); 	
	return $formDiv;
}
