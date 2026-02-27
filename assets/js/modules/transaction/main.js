(function () {
	datePick();
	setDatatbl();
	searchBox();
	pageClick();
})();

function pageClick(){	
	if($('.form-wrapper').find('span.error').length == 0) $('.add-form').hide();
	else $('.add-order').hide();			
	
	$('.close-button').click(function(){
		$('.add-form').slideUp();
		$('.add-order').show();
		$('.form-wrapper').find('span.error').remove(); $('.form-wrapper input').val(''); 
	})
	$('.add-order').click(function(){
		$('.add-form').slideDown();
		$('.add-order').hide();				
	})
}
function searchBox(){		
	$('#search-form button[type=submit]').click(function(){
		$searchTxt = '';
		$search = $('#search-form input[name="search"]').val();
			if($search != '') $searchTxt = '&search='+$search;
		$branch = $('#search-form input[name="branch"]').val();
			if($branch != '') $searchTxt += '&branch='+$branch;
		$panumber = $('#search-form input[name="panumber"]').val();
			if($panumber != '') $searchTxt += '&panumber='+$panumber;
		$voucher = $('#search-form input[name="voucher"]').val();
			if($voucher != '') $searchTxt += '&voucher='+$voucher;
		$stat = $('#search-form select[name="stat"]').val();
			if($stat != undefined) $searchTxt += '&stat='+$stat;
		$datef = $('#search-form input[name="datef"]').val();
			if($datef != '') $searchTxt += '&datef='+$datef;
		$datet = $('#search-form input[name="datet"]').val();
			if($datet != '') $searchTxt += '&datet='+$datet;
		
		if($searchTxt != ''){
			page = 1; search = $searchTxt;
			PopulateItemsTable();			
			//window.history.pushState({}, document.title, BASEURL + 'transaction');
		}
	});
}
/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
	$.ajax({
	type: "GET",
	url: "transaction/get_item?page="+page+search,
	dataType: "json",
	beforeSend: $.LoadingOverlay("show"),
	success: function (data) {
		myTable.rows().remove().draw();
		$('#'+tblID+'_paginate, .export').remove();
		if(search == ''){
			$('.search-notif').show();
			$('#queue-tbl').hide();
		}else{
			$('#queue-tbl').show();
			$('.search-notif').hide();
			if(data.total != 0){
				$jsonReturn = data.result; console.log($jsonReturn);
				$i = 1; 
				if(data.offset > 0) $i = data.offset + 1;
				$.each(data.result, function(key, value) {	
				var result = [];
					for (x = 1; x <= 21; x++) { 
						result.push('');
					}
				$REVPASTAT = $PASTAT = "";
				$TEMPID = value.redeem_id;	
				if(value.pa_id != 0 && value.pa_id != null) $PASTAT = "BILLED";
				if(value.ref_paid != 0 && value.ref_paid != null) $REVPASTAT = "REVERSED";
				var newRow = myTable.row.add(result).draw().node(); 
					$(newRow).addClass('queue-tr transac-tr')
							.attr('data-id', $TEMPID) 
								.find('td:nth-child(1)').text(value.cp_id).end()
								.find('td:nth-child(2)').text(value.m_id).end()
								.find('td:nth-child(3)').text(value.legalname).end()
								.find('td:nth-child(4)').text(value.tin).end()
								.find('td:nth-child(5)').text(value.br_id).end()
								.find('td:nth-child(6)').text(value.br_name).end()
								.find('td:nth-child(7)').text(value.voucher_code).end()
								.find('td:nth-child(8)').text(value.prod_name).end()
								.find('td:nth-child(9)').text(value.pos_id).end()
								.find('td:nth-child(10)').addClass('number').text(value.redeem_fv).end()
								.find('td:nth-child(11)').text(value.redeem_id).end()
								.find('td:nth-child(12)').addClass('queue-tbl-span trans')
														.append($('<span></span>').text(value.redeem_status))
														.append($('<span></span>').addClass('date').text(value.redeem_date)).end()
								.find('td:nth-child(13)').text(value.recon_id).end()
								.find('td:nth-child(14)').text(value.recon_date).end()
								.find('td:nth-child(15)').text(value.pa_id).end()
								.find('td:nth-child(16)').addClass('queue-tbl-span trans')
														.append($('<span></span>').text($PASTAT))
														.append($('<span></span>').addClass('date').text(value.pa_date)).end()
								.find('td:nth-child(17)').text(value.pa_duedate).end()
								.find('td:nth-child(18)').text(value.ref_id).end()
								.find('td:nth-child(19)').text(value.ref_date).end()
								.find('td:nth-child(20)').text(value.ref_paid).end()
								.find('td:nth-child(21)').addClass('queue-tbl-span trans')
														.append($('<span></span>').text($REVPASTAT))
														.append($('<span></span>').addClass('date').text(value.ref_padate)).end();
				});				
				pagination(data.total, data.per_page, true, 'REDEMPTION DATE - ');
			}
		}
		$.LoadingOverlay("hide");
		
		if (window.location.href.indexOf("panum") > -1){		 
			$panumber = $('#search-form input[name="panumber"]').val();
			if($panumber != '') search = '&panumber='+$panumber;
			page = 1; PopulateItemsTable();	
			window.history.pushState({}, document.title, BASEURL + 'transaction');
			console.log('hey reload');
		}
	}
	});
}
function genReport() {
	url = BASEURL + 'transaction/export?' + search;
	$('.export').attr('href', url).click();
}

