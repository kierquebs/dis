var modalID = 'orderModal';
(function () {
setForm();
datePick();
setDatatbl();
filterStatus();

$('#search-form button[type=submit]').click(function(){
	$searchTxt = '';
	$search = $('#search-form input[name="search"]').val();
		if($search != '') $searchTxt = '&search='+$search;			
	$loc = $('#search-form select[name="location"]').val();
		if($loc != '' && $loc != undefined) $searchTxt += '&loc='+$loc;	
	$stat = $('#search-form select[name="status"]').val();
		if($stat != '' && $stat != undefined) $searchTxt += '&stat='+$stat;		
	$datestat = $('#search-form select[name="date_stat"]').val();
		if($datestat != '' && $datestat != undefined) $searchTxt += '&date_stat='+$datestat;		
	$dater = $('#search-form input[name="dater"]').val();
		if($dater != '') $searchTxt += '&dater='+$dater;
	$datep = $('#search-form input[name="datep"]').val();
		if($datep != '') $searchTxt += '&datep='+$datep;
	actionFilter($searchTxt);
});

})();

function setForm(){	
setModal(modalID, 'BIN LOCATION', 'SAVE CHANGES');
$('.modal .modal-btn').attr('id', 'bin-save').after('<button type="button" class="cancel btn btn-danger" data-dismiss="modal">CANCEL</button>');

$formDiv = $('<div></div>').addClass('row-div edit-div');
	$binid = $('<input />').attr({'type': 'hidden', 'name': 'binid', 'id':'binid'});
	$orderDIV = $('<div></div>').addClass('form-div form-half');
		$($orderDIV).append($('<div></div>').text('ORDER ID:'));
		$($orderDIV).append( $('<input />').addClass('order-input required form-control').attr({'type': 'text', 'name': 'orderid'}) );
	$locDIV = $('<div></div>').addClass('form-div form-half');
		$($locDIV).append($('<div></div>').text('LOCATION:'));
		$($locDIV).append($('.add-form select[name="location"]').clone().addClass('bin-stat'));
	$statDIV = $('<div></div>').addClass('form-div form-half');
		$($statDIV).append($('<div></div>').text('STATUS:'));
		$($statDIV).append($('.add-form select[name="status"]').clone().addClass('bin-stat'));
	
	$dateDIV = $('<div></div>').addClass('form-div form-half');
		$($dateDIV).append($('<div></div>').text('RELEASE:'));
		$($dateDIV).append( $('<input />').addClass('datetimepicker required form-control').attr({'type': 'text', 'name': 'dater', 'placeholder':'RELEASE'}) );
	$date2DIV = $('<div></div>').addClass('form-div form-half');
		$($date2DIV).append($('<div></div>').text('PICKUP:'));
		$($date2DIV).append( $('<input />').addClass('datetimepicker required form-control').attr({'type': 'text', 'name': 'datep', 'placeholder':'PICKUP'}) );

$formError = $('<div></div>').addClass('error');
$($formDiv).append($formError).append($binid).append($orderDIV).append($locDIV).append($statDIV).append($dateDIV).append($date2DIV);	
$('.modal .modal-body#'+modalID).append($formDiv);		


$('.upload-btndiv .show-upload, .bin-upload[name=cancel]').click(function(){
	$('.add-form').slideToggle('slow');
	$('.upload-form').slideToggle('slow');
})	
}

/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
$.ajax({
type: "GET",
url: "binloc/get_binloc?page="+page+search,
dataType: "json",
success: function (data) {
	myTable.rows().remove().draw();
	$('#'+tblID+'_paginate, .export').remove();
	if(data.total != 0){
		$jsonReturn = data.result;
		if(data.offset > 0) $i = data.offset + 1;
		$.each(data.result, function(key, value) {						
		var result = [];
			result.push(''); //ORDER ID
			result.push(''); //COMPANY
			result.push(''); //LOCATION
			result.push(''); //ACTION
			result.push(''); //DATE PICK UP / RELEASE
			result.push(''); //LAST UPATE USER / DATE
			result.push(''); //ACTION
		
		$binID = value.binloc_id;
		$actionName = 'NO ACTION';
			if(value.statorder_id != 0 ) $actionName = value.statorder_name;
			
		$orderTXT = $('<span></span>')
				.addClass('form-span')
				.text(value.order_id);
		$cNAME = $('<span></span>')
				.addClass('form-span')
				.text(value.company_name);		
		$locTXT = $('<span></span>')
				.addClass('form-span')
				.text(value.location_name);	
		$actionTXT = $('<span></span>')
				.addClass('form-span')
				.text($actionName);	
				
		$date1 = $('<span></span>')
					.addClass('datep form-span')
					.text(value.dater);	
		$date2 = $('<span></span>')
				.addClass('dater form-span')
				.text(value.datep);
										
		$userLUp = $('<span></span>')
					.addClass('form-span')
					.text(value.user);	
		$dateLUP = $('<span></span>')
				.addClass('form-span')
				.text(value.date_created);
			
		if(data.btn_access == true){
			$btnEdit = $('<button></button>').addClass('bin-edit btn btn-info glyphicon glyphicon-pencil').attr({'data-id': $binID, 'data-toggle':'modal' , 'data-target':'#'+modalID}).text(' EDIT');
		}else $btnEdit = '';
	
		var newRow = myTable.row.add(result).draw().node(); 
			$(newRow).addClass('bin-row').attr({'data-id': $binID})					
			.find('td:nth-child(1)').attr({'data-name':'order'}).append($orderTXT).end()
			.find('td:nth-child(2)').attr({'data-name':'company'}).append($cNAME).end() 
			.find('td:nth-child(3)').attr({'data-name':'location', 'data-id':value.location_id}).append($locTXT).end()
			.find('td:nth-child(4)').attr({'data-name':'action', 'data-id':value.statorder_id}).append($actionTXT).end()
			.find('td:nth-child(5)').addClass('queue-th-span text-center').attr({'data-name':'datetime', 'time-r':value.dater, 'time-p':value.datep}).append($date1).append($date2).end()
			.find('td:nth-child(6)').addClass('queue-th-span text-center').append($userLUp).append($dateLUP).end()
			.find('td:nth-child(7)').addClass('td-action').append($btnEdit);
		});				
		pagination(data.total, data.per_page, true, 'LAST DATE UPDATE - ');
		binlocBTN();
	}
}
});
}
function binlocBTN(){
$('.bin-edit').click(function(e){
	$me = $(e.currentTarget);
	$formRow = $me.closest('.bin-row');	
		$dataID = $me.attr('data-id');		
	$('.modal #binid').val($dataID);
	$('.modal input[name="orderid"]').val($formRow.find('td[data-name="order"] span').text());
	$('.modal select[name="location"]').val($formRow.find('td[data-name="location"]').attr('data-id'));
	$('.modal select[name="status"]').val($formRow.find('td[data-name="action"]').attr('data-id'));
	$('.modal input[name="dater"]').val($formRow.find('td[data-name="datetime"]').attr('time-r'));
	$('.modal input[name="datep"]').val($formRow.find('td[data-name="datetime"]').attr('time-p'));
	
	console.log($formRow.find('td[data-name="datetime"]').attr('time-p'));
	
})
$('.btn-danger').click(function(e){		
e.stopPropagation();	
$('.modal .error').text('');
$('.modal .close').click();
});

$('#bin-save').click(function(e){
	e.stopPropagation();	
	$dataID = $('.modal #binid').val();
	$orderTxt = $('.modal input[name="orderid"]').val();
	$loc = $('.modal select[name="location"]').val();
	$stat = $('.modal select[name="status"]').val();
	$dater = $('.modal input[name="dater"]').val();
	$datep = $('.modal input[name="datep"]').val();		
	
	//console.log($dataID+' '+$orderTxt+' '+$loc+' '+$stat+' '+$dater+' '+$datep);
	if($orderTxt != ''){		
	$.ajax({
		type: 'POST',
		url: 'binloc/binsave',  
		data: { id: $dataID, orderid: $orderTxt, loc: $loc, stat: $stat, dater: $dater, datep: $datep },		
		error: function() {
			failureCallback();
		},
		success: function(data) {
			$('.modal .close').click();
			$('.modal #binid, .modal input[name="dater"], .modal input[name="datep"]').val('');
			PopulateItemsTable();
		},
		dataType: 'json'
	});			
	$('.modal .error').text('');
	}else{
		$('.modal .error').text('ORDER ID IS REQUIRED!');
	}
})
}

function genReport() {
	url = BASEURL + 'binloc/export?' + search;
	$('.export').attr('href', url).click();
}
