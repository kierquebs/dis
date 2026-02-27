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
	if(!$('.form-col').hasClass('hide')){
		formSubmit();
	}
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
	url: "order/re_update/get_released?page="+page+search,
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
						.addClass('fa fa-commenting '+(value.comments != 0 ? 'red' : ''))
						.attr({'aria-hidden':'true', 'data-toggle':'tooltip', 'title':value.comments, 'href':'order/comment/'+$TRANSID});
				$button = $('<a></a>')
						.addClass('redit-btn glyphicon glyphicon-pencil')
						.attr({'title':'EDIT', 'href':'javascript:void(0);', 'id':$TRANSID});		
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
							break;
						case 3:
							$dataNAME = 'fin';
							$valSTATNAME = value.fin_stat_name;
							$valSTAT = value.fin_stat;
							$valTIME = value.fin_time;
							break;
						case 4:
							$dataNAME = 'treas';
							$valSTATNAME = value.treas_stat_name;
							$valSTAT = value.treas_stat;
							$valTIME = value.treas_time;
							break;
						case 5:
							$dataNAME = 'logist';
							$valSTATNAME = value.log_stat_name;
							$valSTAT = value.logs_stat;
							$valTIME = value.logs_time;
							break;
					}	
					$orderBTN = $('<span></span>')
						.addClass('td-btn')
						.text($valSTATNAME);			
					$orderTIME = $('<span></span>')
						.addClass('date')
						.text($valTIME);				
					if(i == 2 && value.location_name != ''){
						$locName = $('<span></span>')
								.addClass('td-loc')
								.text('LOCATION: '+value.location_name);	
					}
					$binloc = value.bin_info;
					if($binloc != ''){
						$locName = $('<span></span>')
								.addClass('td-loc')
								.text($binloc['location']+' ['+$binloc['status']+']');	
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
								.find('td:nth-child(2)').addClass('order').attr('data-order', $orderID).append($orderTXT).append($commentBTN).append($button).end()
								.find('td:nth-child(3)').addClass('queue-tbl-span company').append($cname).append($cperson).end()
								.find('td:nth-child(4)').addClass('queue-tbl-span prod').attr('data-prod', value.prod_stat).append($prodBtn).append($prodTIME).append($locName).end()
								.find('td:nth-child(5)').addClass('queue-tbl-span fin').attr('data-fin', value.fin_stat).append($finBtn).append($finTIME).end()
								.find('td:nth-child(6)').addClass('queue-tbl-span treas').attr('data-treas', value.treas_stat).append($treasBtn).append($treasTIME).end()
								.find('td:nth-child(7)').addClass('queue-tbl-span logist').attr('data-logist', value.logs_stat).append($logBtn).append($logTIME).end()
								.find('td:nth-child(8)').addClass('queue-tbl-span trans').append($date1).append($date2).end()
								.find('td:nth-child(9)').append($tat);
			});					
			pagination(data.totalData, data.per_page, false);
		}		
		editButton();
	}
	});
}

function formSubmit(){	
	$me = $('form[name="re_update"]');	
	dropStatus($("#prod-type"), 2 , false, $me.find('#prod-type').attr('data-prod')); 
	dropStatus($("#fin-type"), 3 , false, $me.find('#fin-type').attr('data-fin')); 
	dropStatus($("#treas-type"), 4 , false, $me.find('#treas-type').attr('data-treas')); 
	dropStatus($("#logs-type"), 5 , false, $me.find('#logist-type').attr('data-logist')); 
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
}

function editForm($me){	
	$transID = $me.attr('id');
	$me = $me.closest('[data-id='+$transID+'].queue-tr');
	$me.addClass('active');
	
	dropStatus($("#prod-type"), 2 , false, $me.find('.prod').attr('data-prod')); 
	dropStatus($("#fin-type"), 3 , false, $me.find('.fin').attr('data-fin')); 
	dropStatus($("#treas-type"), 4 , false, $me.find('.treas').attr('data-treas')); 
	dropStatus($("#logs-type"), 5 , false, $me.find('.logist').attr('data-logist')); 
	
	$me.find('.prod').attr('data-prod')
	$form = $('form.re_update');
	$form.find('input[name="id"]').val($transID);
	$form.find('input[name="orderid"]').val($me.find('.order').attr('data-order'));
	$form.find('input[name="cperson"]').val($me.find('.cperson').html());
	$form.find('input[name="cname"]').val($me.find('.cname').html());
	$form.find('.re-comment').attr({'href':$me.find('.fa-commenting').attr('href')});
	$form.find('.re-comment').find('span').html($me.find('.fa-commenting').attr('data-original-title'));
	$form.find('.bin-loc').html($me.find('.td-loc').html());	
}
