(function () {
	setDatatbl();
	datePick();
	$('#search-form button[type=submit]').click(function(){
		$searchTxt = '';
		$search = $('#search-form input[name="search"]').val();
			if($search != '') $searchTxt = '&search='+$search;
		$datef = $('#search-form input[name="datef"]').val();
			if($datef != '') $searchTxt += '&datef='+$datef;
		$datet = $('#search-form input[name="datet"]').val();
			if($datet != '') $searchTxt += '&datet='+$datet;		
		
		actionFilter($searchTxt);
	});
})();
/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
	$.ajax({
	type: "POST",
	url: "adsoa/get_adsoa?page="+page+search,
	dataType: "json",
	success: function (data) {
		myTable.rows().remove().draw();
		$('#'+tblID+'_paginate').remove();		
		if(data.totalData != 0){
			console.log('add result');
			$jsonReturn = data.result;
			$i = 1; $finTIME =	$finBtn = '';
			if(data.offset > 0) $i = data.offset + 1;
			$.each(data.result, function(key, value) {
				$orderID = value.ORDERID; 	
				if($orderID == 0) $orderID = '';
				$TEMPID = value.TEMPID;
				$delBTN = $finBtn = $finTIME = $date2 = $button2 = $button = ''; 
				
				var result = [];
					result.push($i++); //numbering
					result.push(''); // order
					result.push(''); //company
					result.push('');
					result.push('');
					if(data.temp == true) result.push(''); //btn edit
							
				$cname = $('<span></span>')
							.addClass('cname')
							.attr({'data-toggle':'tooltip', 'title':value.COMPNAME})
							.text(value.COMPNAME);
				$cperson = $('<span></span>')
							.addClass('cperson')
							.attr({'data-toggle':'tooltip', 'title':value.CONTACTNAME})
							.text(value.CONTACTNAME);
				$date1 = $('<span></span>')
							.addClass('date')
							.text(value.DATETEMP);	
				$date2 = $('<span></span>')
						.addClass('date')
						.text(value.DATETEMP_RE);			
				if(data.temp == false && data.btn_access == true){
					$finTIME =	$('<span></span>')
					.addClass('date')
					.text(value.DATETEMP_RE);
					
					$finBtn = $('<span></span>')
					.addClass('td-btn');
					
					if(value.STATUS == 0){
						var orderBtn = $('<button></button>').addClass('btn btn-info').attr({'data-name': 'fin', 'data-id': 3}).text(value.SELECTNAME);
						$($finBtn).text('').append(orderBtn);
					}
				}
				if(data.temp == true && data.btn_access == true){
					if(value.STATUS == 0){
						$button = $('<button></button>')
									.addClass('btn-edit btn btn-info glyphicon glyphicon-pencil cs-btn')
									.attr({'id': $TEMPID, 'title':'EDIT'});	
						$delBTN = $('<button></button>')
									.addClass('btn-remove btn btn-danger glyphicon glyphicon-trash cs-btn')
									.attr({'id': $TEMPID, 'title':'DELETE'});			
						if($orderID != ''){
							$button2 = $('<button></button>')
										.addClass('btn-process btn btn-success cs-process')
										.attr('id', $TEMPID)
										.text('PROCESS');
						}
					}else{			
					$finTIME =	$('<span></span>')
						.addClass('date')
						.text(value.FIN_TIME);
						
						$finBtn = $('<span></span>')
						.addClass('td-btn')
						.text(value.FIN_STAT);
					}
				}
				$num = 0; 
				if(data.temp == true) $num = 1;	
				$orderNUM = 2 + $num; $compNUM = 3 + $num; $finNUM = 4 + $num;	$transNUM = 5 + $num;	
				var newRow = myTable.row.add(result).draw().node(); 
					$(newRow).addClass('queue-tr').attr('data-id', $TEMPID)				
					.find('td:nth-child('+$orderNUM+')').addClass('order').attr({'data-order': $orderID}).text($orderID).end()
					.find('td:nth-child('+$compNUM+')').addClass('queue-tbl-span company').append($cname).append($cperson).end()
					.find('td:nth-child('+$finNUM+')').addClass('queue-tbl-span fin').append($finBtn).append($finTIME).end()
					.find('td:nth-child('+$transNUM+')').addClass('queue-tbl-span trans').append($date1).append($date2);
				if(data.temp == true) $(newRow).find('td:nth-child(2)').append($button).append($delBTN).append($button2);
				console.log($orderNUM);
			});				
			pagination(data.totalData, data.per_page, false);
			
			if(data.temp == true) queueModal();
			else  orderModal();
		}
	}
	});
}
/*finance adsoa*/
function orderModal(){
	var modalID = 'orderModal';
	setModal(modalID, 'PROCESS ORDER', 'OK');
	$('.td-btn button').attr({'data-toggle':'modal' , 'data-target':'#'+modalID});


	$('.td-btn button').click(function(e){		
	$me = $(e.currentTarget); 
		$queueTR = $me.closest('.queue-tr');
		$formDiv = $('<div class="row-div search-div"></div>');
			$formDiv.append('<div class="div-form">ORDER ID: <span class="orderid"></span></div>');
			$formDiv.append('<div class="div-form">COMPANY NAME: <span class="cname"></span></div>');
			$formDiv.append('<div class="div-form">STATUS: <span><input type="hidden" name="transid" id="transid"/><select class="form-control" name="stat" id="form-status"></select></span></div>'); 	

		$('.modal .modal-title#'+modalID).html('FINANCE');
		$('.modal .modal-body#'+modalID).html($formDiv);	
		$('.modal .orderid').html($queueTR.find('.order').attr('data-order'));	// show order id
		$('.modal .cname').html($queueTR.find('.company span.cname').text());		
		$('.modal #transid').val($queueTR.attr('data-id'));
		$('.modal .modal-btn').attr({'data-id':$me.attr('data-name')});
		dropStatus($("#form-status"), $me.attr('data-id') , true);
	});

	$('#orderModal .close').click(function(e){
		$me = $(e.currentTarget);
		$('.modal .modal-title#'+modalID, '.modal .modal-body#'+modalID).html('');
	});
	$('.modal-btn').click(function(e){
		$me = $(e.currentTarget);	
		$('.search-div').find('span.error').remove;		
		finSoa(modalID);
	});
}
	function finSoa(modalID){
		$dataID = $('.modal #'+modalID).find('.div-form span #transid').val();
		$statID = $('.modal #'+modalID).find('.div-form span #form-status').val();
		$statOther = $('.modal #'+modalID).find('.div-form .form-other').val();

		if($dataID == ''){
		$('.modal #'+modalID+' .search-div').append('<span class="error">INVALID REQUEST DATA!</span>');
		}else if($statID == '' || ($statID == '999' && $statOther == '')){	
		$('.modal #'+modalID+' .search-div').append('<span class="error">INVALID STATUS!</span>');
		}else{
			$.ajax({
				type: 'POST',
				url: 'adsoa/order_process',  
				data: { adsoaid: $dataID, stat: $statID, other: $statOther },		
				error: function() {
					failureCallback();
				},
				success: function(data) {
					if(data.update_process == true){
						$dataName = $('.modal .modal-btn').attr('data-id');
						$row_update = $('.queue-tr[data-id='+$dataID+']').find('td.'+$dataName ).find('.td-btn');
						/*if(data.stat_id == 1){
							$row_update.find('button[data-name='+$dataName+']').remove();
							$row_update.html(data.stat_name);
						}else{*/
							$statName = $('.modal #'+modalID).find('.div-form span #form-status option[value='+$statID+']').text();
							$row_update.find('button[data-name='+$dataName+']').text($statName).removeClass('btn-info').addClass('btn-warning');
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
/* temp adsoa*/
function queueModal(){	
    var modalID = 'queueModal';
	setModal(modalID, 'ADVANCE SOA ORDER', 'YES');
	$('.btn-process, #form-process, .btn-remove').attr({'data-toggle':'modal' , 'data-target':'#'+modalID});
	/*
	* click btn td-process
	*/
	$('.btn-process').click(function(e){
		$(".modal .modal-cancel").remove();		
		$me = $(e.currentTarget); 
		$dataID = $me.closest('.queue-tr').attr('data-id');
		$orderID = $('.queue-tr[data-id='+$dataID+']').find('td.order').text();
		
		if($orderID==''){
			$('.today-error, .error').html('no order id');
			if(jQuery.trim($orderID).length > 0){
			   console.log('hey hey');
			}
			return false;
		}else{
			$('.modal .modal-body').html("Are you sure you want to <strong>PROCESS</strong> Order ID: "+$orderID);
			$('.modal .modal-btn').attr({'data-id':$dataID, 'data-order':$orderID});
			$('.modal .modal-btn').after('<button type="button" class="modal-cancel btn btn-danger" data-dismiss="modal">CANCEL</button>');
			que_formprocess();
		}
	});
	/*
	* click form btn-process
	*/
	$('#form-process').click(function(e){
		// stop form submit
		$me = $(e.currentTarget);
		$dataID = $me.closest('.queue-tr').attr('data-id');
		$orderID = $('.order-input').val();
		
		if($orderID == ''){
			$('.today-error, .error').html('no order id');
			if(jQuery.trim($orderID).length > 0){
			   console.log('hey hey');
			}
			return false;
		}else{
			$('.modal .modal-body').html("Are you sure you want to <strong>SAVE and PROCESS</strong> Order ID: "+$orderID);
			$('.modal .modal-btn').attr({'data-id':$dataID, 'data-order':$orderID});
			que_btnprocess();
		}
		console.log('form process');
	});
	
	/*
	* click form btn-remove
	*/
	$('.btn-remove').click(function(e){
		$(".modal .modal-cancel").remove();
		$me = $(e.currentTarget); 
		$dataID = $me.closest('.queue-tr').attr('data-id');
		$orderID = $('.queue-tr[data-id='+$dataID+']').find('td.order').text();
		
		$('.modal .modal-body').html("Are you sure you want to <strong>REMOVE</strong> "+($orderID != '' ? "ORDER ID : "+$orderID : "this item")+"?");
		$('.modal .modal-btn').attr({'data-id':$dataID, 'data-order':$orderID, 'id':'remove-me'});
		$('.modal .modal-btn').after('<button type="button" class="modal-cancel btn btn-danger" data-dismiss="modal">CANCEL</button>');
		que_formremove();
	});
	
	
	/*
	* EDIT FORM
	*/
	
	$('.btn-edit').click(function(e){
		$('.form-wrapper input').val(''); $('.multi-div:not(:last)').remove();
		
		 $('html, body').animate({
			scrollTop: $(".form-wrapper").offset().top
		}, 500);

		$me = $(e.currentTarget);
		$('.form-col').removeClass('hide');

		$dataID = $me.closest('.queue-tr').attr('data-id');	
		$formTR = $('.queue-tr[data-id='+$dataID+']');	
		$('.form-wrapper #rid').val($dataID);
		$('.form-wrapper #orderid').val($formTR.find('td.order').text());
		$('.form-wrapper .order-input').val($formTR.find('td.order').text());
		$('.form-wrapper #cname').val($formTR.find('td.company .cname').text());
		$('.form-wrapper #cperson').val($formTR.find('td.company .cperson').text());
		$('.form-wrapper #order-id').val($dataID);
		$('.today-error, .error').html('');
	})
	$('.btn-close').click(function(e){
		$me = $(e.currentTarget);
		$('.form-col').addClass('hide');
		$('.form-wrapper .order-input, .form-wrapper #cname, .form-wrapper #cperson, .form-wrapper #order-id').val('');
		$(".modal .modal-cancel").remove();
	})
}
	function que_btnprocess(){
		$('.modal-btn').click(function(e){
			$('form[name=edit-order]').find('button[type="submit"]').attr('name', 'process');
			$me = $(e.currentTarget);
			$dataID = $me.attr('data-id');
			$dataForm = $me.attr('data-form');
			$dataOrder = $me.attr('data-order');
			$('.modal .close').click();
			$('form[name=edit-order]').find('button[type="submit"]').click();
			console.log('form submit');
		});
	}
	function que_formprocess(){
		$('.modal-btn').click(function(e){
			$me = $(e.currentTarget);
			$dataID = $me.attr('data-id');
			$dataForm = $me.attr('data-form');
			$dataOrder = $me.attr('data-order');
			$('.modal .close').click();
				window.location.href = 'adsoa/process_soa/'+$dataID;	
		});
	}
	function que_formremove(){
		$('#remove-me').click(function(e){
			$me = $(e.currentTarget);
			$dataID = $me.attr('data-id');
			$dataForm = $me.attr('data-form');
			$dataOrder = $me.attr('data-order');
			$('.modal .close').click();
				window.location.href = 'adsoa/remove_soa/'+$dataID;	
		});
	}
