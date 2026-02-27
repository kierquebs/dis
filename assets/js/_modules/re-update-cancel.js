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
		$('.btn-close').click();
	});
	$('#search-form button.clear-all').click(function(){
		$('.btn-close').click();		
	})
})();

/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
	$.ajax({
	type: "POST", 
	url: "order_cancel/re_update/get_released?page="+page+search,
	dataType: "json",
	success: function (data) {
		myTable.rows().remove().draw();		
		$('#'+tblID+'_paginate, .export').remove();		
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
							.attr({'data-prod':$orderINFO['coprod'], 'data-prod-id':$orderINFO['coprod_id'], 
							'data-reason':$orderINFO['coreason'],  'data-reason-id':$orderINFO['coreason_id'], 
							'data-charge':$orderINFO['cocharge'], 'data-charge-id':$orderINFO['cocharge_id'], 
							'data-amount':$orderINFO['amount'], 'data-date':$orderINFO['date'], 'data-qnty':$orderINFO['coqnty'], 'data-amntchgr':$orderINFO['cochargeamt'], 'data-rsnum':$orderINFO['cors'], 
							'data-type':$orderINFO['cotype'], 'data-type-id':$orderINFO['cotype_id'], 'data-em':$orderINFO['coem']})				
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
				
				$button = $('<a></a>')
						.addClass('redit-btn glyphicon glyphicon-pencil')
						.attr({'title':'EDIT', 'href':'javascript:void(0);', 'id':$TRANSID});		
				/*
				* utype status and btn
				*/
				$reimBtn2 = $reimBtn = $prodBtn = $finBtn = $csBtn = $logBtn = ' '; 
				$reimTIME = $prodTIME = $finTIME = $csTIME = $logTIME = ' '; 
				for (i = 2; i <= 7; i++) { 
					switch(i) {
						case 2:
							$dataNAME = 'prod';
							$valSTATNAME = value.prod_stat_name;
							$valSTAT = value.prod_stat;
							$valTIME = value.prod_time;
							break;
						case 3:
							$dataNAME = 'fin';
							$valSTATNAME = value.fin_stat_name;
							$valSTAT = value.fin_stat;
							$valTIME = value.fin_time;
							break;
						case 5:
							$dataNAME = 'logist';
							$valSTATNAME = value.logs_stat_name;
							$valSTAT = value.logs_stat;
							$valTIME = value.logs_time;
							break;						
						case 6:
							$dataNAME = 'cs';
							$valSTATNAME = value.cs_stat_name;
							$valSTAT = value.cs_stat;
							$valTIME = value.cs_time;
							break;
						case 7:
							$dataNAME = 'reim';
							$valSTATNAME = value.reim_stat_name;
							$valSTAT = value.reim_stat;
							$valTIME = value.reim_time;
					}	
					$orderBTN = $('<span></span>')
						.addClass('td-btn')
						.text($valSTATNAME);			
					$orderTIME = $('<span></span>')
						.addClass('date')
						.text($valTIME);	
						
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
							$csBtn =  $orderBTN;
							$csTIME = $orderTIME;
							break;
						case 7:
							$reimBtn2 = (data.reim_btn == true && value.rowcheck == 0 ? $('<button></button>').addClass('btn fa fa-undo undo-btn').attr({'title':'UNDO', 'data-id':$TRANSID}).text(''): '');					
							$reimBtn = $orderBTN;
							$reimTIME = $orderTIME;
							break;
					}
				}
											
				var newRow = myTable.row.add(result).draw().node(); 
					$(newRow).addClass('queue-tr'+($orderINFO['cotype_id'] != 0 ? ' yellow-tag' : '')).attr('data-id', $TRANSID) 
								.find('td:nth-child(2)').addClass('order').attr('data-order', $orderID).append($orderTXT).append($commentBTN).append($button).end()
								.find('td:nth-child(3)').addClass('rsnum').attr('data-rsnum', $orderINFO['cors']).text($orderINFO['cors']).end()
								.find('td:nth-child(4)').addClass('queue-tbl-span co-company').append($cname).append($orderinfo).end()
								.find('td:nth-child(5)').addClass('queue-tbl-span co-cs').attr('data-cs', value.cs_stat).append($csBtn).append($csTIME).end()
								.find('td:nth-child(6)').addClass('queue-tbl-span co-prod').attr('data-prod', value.prod_stat).append($prodBtn).append($prodTIME).end()
								.find('td:nth-child(7)').addClass('queue-tbl-span co-fin').attr('data-fin', value.fin_stat).append($finBtn).append($finTIME).end()
								.find('td:nth-child(8)').addClass('queue-tbl-span co-logist').attr('data-logist', value.logs_stat).append($logBtn).append($logTIME).end()
								.find('td:nth-child(9)').addClass('queue-tbl-span co-reim').attr('data-reim', value.reim_stat).append($reimBtn).append($reimTIME);
			});					
			pagination(data.totalData, data.per_page, false);
		}		
		editButton();
		viewInfo();
	}
	});
}
var modalID2 = 'orderInfoModal';
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
function modalClick(){
	$('#orderInfoModal .modal-btn').click(function(e){
		$me = $(e.currentTarget);
		$('.modal .modal-title#'+modalID2, '.modal .modal-body#'+modalID2).html('');
		$('#orderInfoModal .close').click();
	});
}

function editButton(){	
	$('.btn-close').click(function(e){
		$me = $(e.currentTarget);
		$('.form-col').addClass('hide');
		$('.queue-tr').removeClass('active');
		$('form.re_update input').val('');
		$('form.re_update .bin-loc').html('');
		
	})
	
	$('.redit-btn').click(function(e){	
        e.stopImmediatePropagation();
		$me = $(e.currentTarget);
		$('.form-col').removeClass('hide');
		$('.queue-tr').removeClass('active');
		$('form.re_update input').val('');
		$('form.re_update .bin-loc').html('');
		editForm($me);
		
		$('html, body').animate({scrollTop: $(".re_update").offset().top}, 1000);
	})
	
	$('select[name="charge"]').change(function(e){
		$me = $(e.currentTarget);	
		
		if($me.val() == 1){
			if($('input.form-other2[name="em_name"]').length == 0) $('<input type="text" name="em_name" class="form-control form-other2" placeholder="enter employee name">').insertAfter($me);
		}else $('input.form-other2[name="em_name"]').remove();
	})	
	
}

function editForm($me){	
	$transID = $me.attr('id');
	$me = $me.closest('[data-id='+$transID+'].queue-tr');
	$me.addClass('active');
	
	dropStatus($("#cs-type"), 6 , false, $me.find('.co-cs').attr('data-cs'), 2);
	dropStatus($("#prod-type"), 2 , false, $me.find('.co-prod').attr('data-prod'), 2); 
	dropStatus($("#fin-type"), 3 , false, $me.find('.co-fin').attr('data-fin'), 2) ; 
	dropStatus($("#logs-type"), 5 , false, $me.find('.co-logist').attr('data-logist'), 2); 
	dropStatus($("#reim-type"), 7 , false, $me.find('.co-reim').attr('data-reim'), 2); 

	
	//$me.find('.prod').attr('data-prod');
	$form = $('form.re_update');
	$form.find('input[name="id"]').val($transID);
	$form.find('input[name="orderid"]').val($me.find('.order').attr('data-order'));
	$form.find('input[name="rsnum"]').val($me.find('.rsnum').attr('data-rsnum'));
	$form.find('input[name="company_name"]').val($me.find('.co-company').find('.cname').html());
	$form.find('input[name="amount"]').val($me.find('.co-company').find('.orderinfo').attr('data-amount'));
	$form.find('input[name="amtcharge"]').val($me.find('.co-company').find('.orderinfo').attr('data-amntchgr'));
	$form.find('select[name="type"]').val($me.find('.co-company').find('.orderinfo').attr('data-type-id'));
	$form.find('select[name="product"]').val($me.find('.co-company').find('.orderinfo').attr('data-prod-id'));
	$form.find('select[name="reason"]').val($me.find('.co-company').find('.orderinfo').attr('data-reason-id'));
	$form.find('select[name="charge"]').val($me.find('.co-company').find('.orderinfo').attr('data-charge-id'));
	
	var attr = $form.find('select[name="charge"]').attr('readonly');
		if($form.find('select[name="charge"]').val() == 1) $('<input type="text" name="em_name" class="form-control form-other2" placeholder="enter employee name" value="'+$me.find('.co-company').find('.orderinfo').attr('data-em')+'" '+(typeof attr !== typeof undefined && attr !== false? 'readonly="readonly"' : '')+'>').insertAfter($('.form-wrapper select[name="charge"]'))
		else $('input.form-other2[name="em_name"]').remove();
	
	var qntydeno = $me.find('.co-company').find('.orderinfo').attr('data-qnty').split( '<br />' ); 
	for($i=0; $i< qntydeno.length; $i++){
		var qntydenoSplit =  qntydeno[$i].split( ' - ' );
		
		var field = $form.find('.qnty-deno').last();
			field.find('.deno').val(qntydenoSplit[0]);
			field.find('.qnty').val(qntydenoSplit[1]);
			
		var field_new = field.clone();
			field.find('.btn-add')
				.toggleClass( 'btn-default' )
				.toggleClass( 'btn-add' )
				.toggleClass( 'btn-danger' )
				.toggleClass( 'btn-remove' )
				.html( '&ndash;' );
			field_new.find( 'input' ).val('').removeClass('error');
			field_new.insertAfter( field );
	}
	$form.find('.re-comment').attr({'href':$me.find('.fa-commenting').attr('href')});
	$form.find('.re-comment').find('span').html($me.find('.fa-commenting').attr('data-original-title'));
}
