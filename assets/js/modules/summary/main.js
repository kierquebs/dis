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
		$mid = $('#search-form input[name="mid"]').val();
			if($mid != '') $searchTxt += '&mid='+$mid;
		$mname = $('#search-form input[name="mname"]').val();
			if($mname != '') $searchTxt += '&mname='+$mname;
		
		if($searchTxt != ''){
			page = 1; search = $searchTxt;
			PopulateItemsTable();
		} console.log($searchTxt);
	});
	
	$('#print_pa').click(function(e){
		$( "#printForm" ).submit();		
		setTimeout(function(){ $('.loadingoverlay').hide(); $.LoadingOverlay("hide");}, 3000);
	});
}
/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
	$.ajax({
	type: "GET",
	url: "summary/get_item?page="+page+search,
	dataType: "json",
	beforeSend: $.LoadingOverlay("show"),
	success: function (data) {
		myTable.rows().remove().draw();
		$('#'+tblID+'_paginate, .export').remove();
		$('#print_pa').attr('disabled');
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
					$TEMPID = value.pa_id;			
					
					var result = [];
					for (x = 1; x <= 6; x++) { 
						result.push('');
					}		
		
					$checkBox = $('<input />').attr({'type': 'checkBox', 'name':'process[]', 'value':$TEMPID , 'class':'checkProcess'});
					$linkA = $('<a></a>').attr({'href': BASEURL + 'transaction?panum='+$TEMPID, 'data-id': $TEMPID}).text($TEMPID);				
					var newRow = myTable.row.add(result).draw().node(); 
					$(newRow).addClass('queue-tr')
							.attr('data-id', $TEMPID) 
								.find('td:nth-child(1)').append($checkBox).end()
								.find('td:nth-child(2)').append($linkA).end()
								.find('td:nth-child(3)').text(value.m_id).end()
								.find('td:nth-child(4)').text(value.legalname).end()
								.find('td:nth-child(5)').addClass('number').text(value.TOTAL_FV).end()
								.find('td:nth-child(6)').text(value.pa_duedate);
				});				
				pagination(data.total, data.per_page, true, '');
			}
		}
		$.LoadingOverlay("hide");
	}
	});
}
function genReport() {
	url = BASEURL + 'summary/export?' + search;
	$('.export').attr('href', url).click();
}

