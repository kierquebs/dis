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
	url: "order/get_released?page="+page+search,
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
				$TRANSID = value.transac_id;
				$date2 = $button2 = $button = '';
				var result = [];
					result.push($i++); //numbering
					result.push(''); //order id
					result.push(''); //company info
					result.push(''); //production 
					result.push(''); //finance 
					result.push(''); //treasury 
					result.push(''); //logistics 
					result.push(''); //transaction time 
					result.push(''); //tat
								
				$cname = $('<span></span>')
							.addClass('cname')
							.attr({'data-toggle':'tooltip', 'title':value.company_name})
							.text(value.company_name);
				$cperson = $('<span></span>')
							.addClass('cperson')
							.attr({'data-toggle':'tooltip', 'title':value.contact_person})
							.text(value.contact_person);
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
						.addClass('fa fa-commenting '+(value.comments != 0 ? 'red' : '')) //COMMENTS (
						.attr({'aria-hidden':'true', 'data-toggle':'tooltip', 'title':(value.comments == 0 ? 0 : 'COMMENTS('+value.comments+')'), 'href':'order/comment/'+$TRANSID});
						
				/*
				* utype status and btn
				*/
				$locName = $prodBtn = $finBtn = $treasBtn = $logBtn = ' '; 
				for (i = 2; i < 6; i++) { 
					switch(i) {
						case 2:
							$dataNAME = 'prod';
							$valSTATNAME = value.prod_stat_name;
							$valSTAT = value.prod_stat;
							$valTIME = value.prod_time;
							if(value.logs_stat == 0)$valBTN = data.prod_btn;
							else $valBTN = false;
							break;
						case 3:
							$dataNAME = 'fin';
							$valSTATNAME = value.fin_stat_name;
							$valSTAT = value.fin_stat;
							$valTIME = value.fin_time;
							if(value.logs_stat == 0)$valBTN = data.fin_btn;
							else $valBTN = false;
							break;
						case 4:
							$dataNAME = 'treas';
							$valSTATNAME = value.treas_stat_name;
							$valSTAT = value.treas_stat;
							$valTIME = value.treas_time;
							if(value.logs_stat == 0)$valBTN = data.treas_btn;
							else $valBTN = false;
							break;
						case 5:
							$dataNAME = 'logist';
							$valSTATNAME = value.log_stat_name;
							$valSTAT = value.logs_stat;
							$valTIME = value.logs_time;
							if(value.prod_stat != 0 && value.fin_stat != 0 && value.logs_stat == 0) $valBTN = data.logs_btn;
							else $valBTN = false;
							break;
					}
					$orderTxt = 'SELECT';			
					$orderBTN = $('<span></span>')
						.addClass('td-btn')
						.text($valSTATNAME);			
					$orderTIME =	$('<span></span>')
						.addClass('date')
						.text($valTIME);
					if($valBTN == true){
							if($valSTAT != 0) $orderTxt = $valSTATNAME;
							var orderBtn = $('<button></button>').addClass('btn btn-info').attr({'data-name': $dataNAME, 'data-id': i, 'data-stat':$valSTAT}).text($orderTxt);
						$($orderBTN).text('').append(orderBtn);
					}				
					if(i == 2 && value.location_name != ''){
						$locName = $('<span></span>')
								.addClass('td-loc')
								.text('LOCATION: '+value.location_name);	
					}
					$binloc = value.bin_info;
					
					$locName = $('<span></span>')
							.addClass('td-loc').text('');
					if($binloc != ''){
						$locName = $($locName).text($binloc['location']+' ['+$binloc['status']+']');	
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
						case 4:
							$treasBtn = $orderBTN;
							$treasTIME = $orderTIME;
							break;
						case 5:
							$logBtn = $orderBTN;
							$logTIME = $orderTIME;
							break;
					}
				}
											
				var newRow = myTable.row.add(result).draw().node(); 
					$(newRow).addClass('queue-tr')
							.attr('data-id', $TRANSID) 
								.find('td:nth-child(2)').addClass('order').attr('data-order', $orderID).append($orderTXT).append($commentBTN).end()
								.find('td:nth-child(3)').addClass('queue-tbl-span company').append($cname).append($cperson).end()
								.find('td:nth-child(4)').addClass('queue-tbl-span prod').append($prodBtn).append($prodTIME).append($locName).end()
								.find('td:nth-child(5)').addClass('queue-tbl-span fin').append($finBtn).append($finTIME).end()
								.find('td:nth-child(6)').addClass('queue-tbl-span treas').append($treasBtn).append($treasTIME).end()
								.find('td:nth-child(7)').addClass('queue-tbl-span logist').append($logBtn).append($logTIME).end()
								.find('td:nth-child(8)').addClass('queue-tbl-span trans').append($date1).append($date2).end()
								.find('td:nth-child(9)').append($tat);
			});				
			pagination(data.totalData, data.per_page, false);
			orderModal();	
		}
	}
	});
}

function orderModal(){
	var modalID = 'orderModal';
	setModal(modalID, 'PROCESS ORDER', 'OK');
	$('.td-btn button').attr({'data-toggle':'modal' , 'data-target':'#'+modalID});


	$('.td-btn button').click(function(e){		
	$me = $(e.currentTarget); 
	/* CHECK FORM TITLE */
	if($me.attr('data-name') == 'prod') $Title = 'PRODUCTION';
	else if($me.attr('data-name') == 'fin') $Title = 'FINANCE';
	else if($me.attr('data-name') == 'treas') $Title = 'TREASURY';
	else $Title = 'LOGISTICS';
	
	$queueTR = $me.closest('.queue-tr');

		$('.modal .modal-title#'+modalID).html($Title);
		$('.modal .modal-body#'+modalID).html(orderForm());	
		$('.modal .orderid').html($queueTR.find('.order').attr('data-order'));	// show order id
		$('.modal .cname').html($queueTR.find('.company span.cname').text());		
		$('.modal #transid').val($queueTR.attr('data-id'));
		$('.modal .modal-btn').attr({'data-name':$me.attr('data-name'), 'data-id':$me.attr('data-id')});
		dropStatus($("#form-status"), $me.attr('data-id') , true, $me.attr('data-stat'));
	});

	$('#orderModal .close').click(function(e){
		$me = $(e.currentTarget);
		$('.modal .modal-title#'+modalID, '.modal .modal-body#'+modalID).html('');
	});
	$('.modal-btn').click(function(e){
		$me = $(e.currentTarget);	
		$('.search-div').find('span.error').remove;		
		process(e, modalID);
	});
}
function process(e, modalID){
	$btnDataID = $(e.currentTarget).attr('data-id');
	$dataID = $('.modal #'+modalID).find('.div-form span #transid').val();
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
			url: 'order/process',  
			data: { transid: $dataID, stat: $statID, other: $statOther, btnid: $btnDataID},	
			error: function() {
				failureCallback();
			},
			success: function(data) {
					$('.modal #'+modalID+' .search-div').find('.error').remove();
				if(data.update_process == true){
					$dataName = $('.modal .modal-btn').attr('data-name');
					$row_update = $('.queue-tr[data-id='+$dataID+']').find('td.'+$dataName ).find('.td-btn');
					/*if(data.stat_id == 1){
						$row_update.find('button[data-name='+$dataName+']').remove();
						$row_update.html(data.stat_name);
					}else{*/
						$statName = $('.modal #'+modalID).find('.div-form span #form-status option[value='+$statID+']').text();
						$row_update.find('button[data-name='+$dataName+']').text($statName);
					//}
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
	$formDiv.append('<div class="div-form">COMPANY NAME: <span class="cname"></span></div>');
	$formDiv.append('<div class="div-form">STATUS: <span><input type="hidden" name="transid" id="transid"/><select class="form-control" name="stat" id="form-status"></select></span></div>'); 	
	return $formDiv;
}
