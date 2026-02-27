var terms ,
	day,
	date = '';
(function () {
	setDatatbl();
	searchBox();
})();

//*** GET LIST OF MERCHANTS ****//
function searchBox(){		
	$('.p_date, .p_day').addClass('hide').prop("disabled", true).removeClass('error-select').val('');	
	
	$('#search-form button[type=submit]').click(function(){
		$(".branch_li, #list_tbl").hide();
		$searchTxt = '';

		$terms = $('#search-form select[name="terms"]').val();
			if($terms != '' && $terms != null) $searchTxt += '&terms='+$terms;
		$search = $('#search-form input[name="search"]').val();
			if($search != '') $searchTxt += '&search='+$search;
		$day = $('#search-form .p_day select[name="day"]').val();
			if($day != '' && $day != undefined) $searchTxt += '&day='+$day;
		$date = $('#search-form .p_date select[name="date"]').val();
			if($date != '' && $date != undefined) $searchTxt += '&date='+$date;
		
		if($terms == '' || $terms == null){
			$('#search-form select[name="terms"], #search-form input[name="search"]').addClass('error-select'); 
		}else{
			if($terms == 3 && ($day == '' || $day == undefined)) $('#search-form .p_day select[name="day"]').addClass('error-select'); 
			else if ($terms != 3 && ($date == '' || $date == undefined)) $('#search-form .p_date select[name="date"]').addClass('error-select');
			else{		
				if($searchTxt != ''){
					page = 1; search = $searchTxt;				
					$('#list_tbl').show();
					PopulateItemsTable();
				}
			}
		}		
	});
	$('#p_terms').change(function(e){
        e.stopImmediatePropagation();
		$p_terms = $(e.currentTarget).val();
		
		$('.p_date, .p_day').addClass('hide').prop("disabled", true).val('');
		if($p_terms == 3){
			$('.p_day').removeClass('hide').prop("disabled", false);
		}else{
			$('.p_date').removeClass('hide').prop("disabled", false);
		}
	})
	
	$('.btn-pdf').click(function(e){
		$('.pdf_pa').remove();
	});
}

function PopulateItemsTable() {	
	$page_i = $('#search-form').attr('data-page');
	if($page_i != null && $page_i != undefined) $page_i = $page_i+"/";

	$.ajax({
	type: "GET",
	url: "process/"+$page_i+"get_item?page="+page+search,
	dataType: "json",
	beforeSend: $.LoadingOverlay("show"),
	success: function (data) {
		$('#checkAll').attr('disabled', 'disabled');
		myTable.rows().remove().draw();
		$('#'+tblID+'_paginate, .export').remove();
		$alert = false; 
		if(data.total != 0){
			$jsonReturn = data.result; 
			$i = 1; 
			if(data.offset > 0) $i = data.offset + 1;
			$.each(data.result, function(key, value) {	
			var result = []; 
				for (x = 1; x <= 11; x++) { 
					result.push('');
				}
			
			$TEMPID = value.MID;	
			$checkBox = $('<input />').attr({'type': 'checkBox', 'name':'process[]', 'value':$TEMPID , 'class':'checkProcess'});
			$btnView = $btnUnknown = '';			
			if(value.validTransac != 0){
				$btnView = $('<button></button>')
								.addClass('btn btn-info btn-view')
								.attr({'id': $TEMPID, 'title':'VIEW DETAILS'})
								.append($('<span></span>').addClass('glyphicon glyphicon-list').attr('aria-hidden', true));
			}
			if(value.unkownTransac != 0){
			$btnUnknown = $('<button></button>')
								.addClass('btn btn-warning btn-unknown')
								.attr({'id': $TEMPID, 'title':'UNKNOWN TRANSACTIONS'})
								.append($('<span></span>').addClass('glyphicon glyphicon-exclamation-sign').attr('aria-hidden', true));
			} 
			if(value.ExpectedDueDateCon < data.date_today){
				$alert = true; 
				$expectedSpan = $('<span></span>').addClass('error').text(value.ExpectedDueDate); 
			}else  $expectedSpan = $('<span></span>').text(value.ExpectedDueDate);

			var newRow = myTable.row.add(result).draw().node(); 
				$(newRow).addClass('queue-tr')
						.attr('data-id', $TEMPID) 
							.find('td:nth-child(1)').addClass('center').append($checkBox).end()
							.find('td:nth-child(2)').addClass('center').text(value.CPID).end()
							.find('td:nth-child(3)').addClass('center').text(value.MID).end()
							.find('td:nth-child(4)').text(value.LegalName).end()
							.find('td:nth-child(5)').addClass('number amountU')
													.attr({'data-uknown':value.uAmount, 'data-amount':value.totalAmount})
													.text(value.totalAmount).end()
							.find('td:nth-child(6)').addClass('number center').text(value.totalBranch).end()
							.find('td:nth-child(7)').addClass('number center').text(value.totalTransaction).end()
							.find('td:nth-child(8)').addClass('number center').text(value.totalRefTransac).end()
							.find('td:nth-child(9)').addClass('number center').text(value.refundAmount).end()
							.find('td:nth-child(10)').addClass('center').append($expectedSpan).end()
							.find('td:nth-child(11)').append($btnView).append($btnUnknown);
			});				
			terms = data.terms;
			day = data.day;
			date = data.date;			
			$('#submitForm input[name="terms"]').val(terms);
			$('#submitForm input[name="day"]').val(day);
			$('#submitForm input[name="date"]').val(date);		
			$('#checkAll').removeAttr('disabled');			
			processModal();
			pagination(data.total, data.per_page, false);
		}else activateLinks();		
		$('#date_coverage').text(data.date_coverage);	
		$.LoadingOverlay("hide");
		setTimeout(function(){if($alert == true) alert("You are trying to process PA with OVERDUE PAYMENT!");}, 200);
		
	}
	});
}
//*** **** *****//

//*** GET LIST OF BRANCHES *****//
function get_branches($dataID, $validate){		
	$page_i = $('#search-form').attr('data-page');
	if($page_i != null && $page_i != undefined) $page_i = $page_i+"/";	

	$.ajax({
		type: 'POST',
		url: "process/"+$page_i+"get_branches", 
		dataType: "json", 
		data: { id: $dataID, validate:$validate, terms:terms, day:day, date:date},		
		success: function(data) {
			$(".process-tbl").hide();
			$(".branch_li").show();
			$(".branch-tbl").find("tr:gt(0)").remove();				
			if(data.total != 0){
				$merchantName = '';
				$jsonReturn = data.result; 
				$.each(data.result, function(key, value) {
					$merchantName = value.LegalName;
					$td1 = $('<td></td>').attr({'width': 200}).text(value.MID);	
					$td2 = $('<td></td>').attr({'width': 200}).text(value.BRANCH_ID);	
					$td3 = $('<td></td>').text(value.BRANCH_NAME);	
					$td4 = $('<td></td>').addClass('number').attr({'width': 150}).text(value.totalAmount);		
					$td5 = $('<td></td>').addClass('center').attr({'width': 150}).text(value.totalTransaction); 
					$tblROW = $('<tr></tr>').append($td1).append($td2).append($td3).append($td4).append($td5);
					$($tblROW).insertAfter("#comment-tbl #tbl-head");					
				});	
				$(".merchant_name").text(' : '+ $merchantName);				
			}	
		}
	});	
}

$( document ).on( 'click', '.btn-view', function ( event ) {
	event.preventDefault();
	$me = $(this);//$(e.currentTarget);			
	$dataID = $me.attr('id');				
	if($dataID != ''){	
		event.stopImmediatePropagation();	
		get_branches($dataID, 1);		
	}else{
		$('.modal .error').text('INCOMPLETE DETAILS!');
	}
});

$( document ).on( 'click', '.btn-unknown', function ( event ) {
	event.preventDefault();
	$me = $(this);//$(e.currentTarget);			
	$dataID = $me.attr('id');				
	if($dataID != ''){	
		event.stopImmediatePropagation();	
		get_branches($dataID, 2);		
	}else{
		$('.modal .error').text('INCOMPLETE DETAILS!');
	}
});
$( document ).on( 'click', '.close-branch', function ( event ) {
	event.preventDefault();
	$(".branch_li").hide();
	processBTN();
});

//*** **** *****//


//*** PROCESS  - GENERATE PAYMENT ADVICE *****//

function processModal(){	
	var modalID = 'processModal';
	setModal(modalID, 'GENERATE PAYMENT ADVICE', 'YES');
	$('#create-req').attr({'data-toggle':'modal' , 'data-target':'#'+modalID});

	$('#create-req').click(function(e){	
		$(".modal .modal-cancel").remove();
		$('.modal .modal-body').html("Are you sure you want to proceed?");
		$('.modal .modal-btn').attr({'id':'process'}).show();
		$('.modal .modal-btn').after('<button type="button" class="modal-cancel btn btn-danger" data-dismiss="modal">CANCEL</button>');
		processClick();
	});
}

function processClick(){
	console.log($( "#submitForm" ));
	$('#process').click(function(e){
		setTimeout(function(){ $('.loadingoverlay').hide(); $.LoadingOverlay("hide");}, 3000);
		$( "#submitForm" ).submit();
		/*$me = $(e.currentTarget);
		$('.modal .close').click();		
		$.ajax({
			type: 'POST',
			url: 'process/gen_pa',  
			beforeSend: $.LoadingOverlay("show"),
			error: function() {
				//failureCallback();
			},
			data:$('#submitForm').serialize(),
			success: function(data) {
				console.log(data);
				$('.modal .modal-cancel').remove();
				if(data.success == false){
					$('.modal .modal-btn').hide();
					$('.modal .modal-btn').after('<button type="button" class="modal-cancel btn btn-danger" data-dismiss="modal">CLOSE</button>');
					$('.modal .modal-body').html("Something went wrong!");				
				}else{				
					$('.modal .close').click();
					$('.modal .modal-body').html("Something went wrong!");					
					//PopulateItemsTable();
					//window.location.href = 'http://example.com';
				}	
				$.LoadingOverlay("hide");	
			},
			dataType: 'json'
		});*/
	});
}

//*** **** *****//

